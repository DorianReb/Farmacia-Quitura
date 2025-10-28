<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $productoEncontrado = null;
        $itemsEnVenta = [];
        $totalVenta = 0;
        $q = trim($request->q);

        // Lógica para obtener productos basados en el nombre comercial (búsqueda principal)
        $productosBuscados = Producto::query()
            ->with(['lotes']) 
            ->when($q, function ($query) use ($q) {
                $query->where('nombre_comercial', 'LIKE', "%{$q}%")
                    ->orWhere('codigo_barras', 'LIKE', "{$q}");
            })
            ->orderBy('nombre_comercial', 'asc')
            ->paginate(10, ['*'], 'productos_page');

        // Bandera para la apertura del modal (solo si hay query y resultados)
        $abrirModalMenu = ($q && $productosBuscados->count() > 0) ? true : false;
        
        return view('venta.index', compact('productosBuscados', 'productoEncontrado', 'itemsEnVenta', 'totalVenta', 'q', 'abrirModalMenu'));
    }

    public function buscarProductoPorCodigo($codigo)
    {
        $producto = Producto::with([
            'lotes',
            'marca',
            'presentacion',
            'formaFarmaceutica',
            'categoria',
            'asignaUbicaciones.nivel.pasillo',
            'asignaComponentes.componente'
        ])
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');
        $producto->lotes = $producto->lotes->sortBy('fecha_caducidad')->values();

        $producto->asigna_ubicaciones = $producto->asignaUbicaciones ?? collect();
        $producto->asigna_componentes = $producto->asignaComponentes ?? collect();
        $producto->forma_farmaceutica = $producto->formaFarmaceutica ?? null;

        $primerComponente = $producto->asigna_componentes->first();
        $producto->nombre_cientifico = $primerComponente?->componente?->nombre ?? null;

        $ubicacionesFormateadas = [];
        foreach ($producto->asigna_ubicaciones as $asignacion) {
            $nivelNombreCompleto = $asignacion->nivel?->nombre;
            if ($nivelNombreCompleto) {
                $ubicacionesFormateadas[] = $nivelNombreCompleto;
            }
        }

        $producto->ubicaciones_texto = implode(', ', array_unique($ubicacionesFormateadas));
        
        $producto->unsetRelation('asignaUbicaciones');
        $producto->unsetRelation('asignaComponentes');

        return response()->json($producto);
    }

    /**
     * Store a newly created resource in storage.
     * Recibe JSON del front-end.
     */
    public function store(Request $request)
    {
        // 🚨 CAMBIO CLAVE: Asumimos que los datos vienen en formato JSON en el cuerpo.
        // Si usamos fetch con Content-Type: application/json, Laravel pone el cuerpo en $request->json().
        $data = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.codigo_barras' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.lote' => 'required|integer' // El ID del lote
        ]);
        
        // Iniciamos la transacción de base de datos
        DB::beginTransaction();

        try {
            $venta = Venta::create([
                'usuario_id' => Auth::id(),
                'fecha' => now(),
                'total' => 0
            ]);

            $total = 0;
            $productosVendidos = $data['productos']; // Usamos el array validado

            foreach ($productosVendidos as $p) {
                // 1. Encontrar el lote y verificar stock
                $lote = Lote::find($p['lote']);
                if (!$lote || $lote->cantidad < $p['cantidad']) {
                     DB::rollBack();
                     return response()->json(['message' => 'Stock insuficiente para lote ' . $p['lote'] . '.'], 422);
                }

                // 2. Obtener producto relacionado
                $producto = $lote->producto;

                // 3. Calcular y crear detalle
                $subtotal = $p['cantidad'] * $producto->precio_venta;

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'lote_id' => $lote->id,
                    'cantidad' => $p['cantidad'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;

                // 4. Reducir stock del lote
                $lote->cantidad -= $p['cantidad'];
                $lote->save();
            }

            // 5. Guardar total de la venta y commit
            $venta->total = $total;
            $venta->save();
            
            DB::commit();

            return response()->json([
                'message' => 'Venta registrada correctamente',
                'venta_id' => $venta->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Esto atrapará errores como el de SQLSTATE (si no se reduce el stock)
            return response()->json(['message' => 'Fallo interno al procesar la venta: ' . $e->getMessage()], 500);
        }
    }

    public function historial(Request $request)
    {
        $q = trim($request->input('q',''));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $ventas = Venta::with([
            'usuario',
            'detalles.lote.producto', // producto via lote
        ])
            ->when($desde, fn($qq)=>$qq->whereDate('fecha','>=',$desde))
            ->when($hasta, fn($qq)=>$qq->whereDate('fecha','<=',$hasta))
            ->when($q !== '', function($qq) use ($q){
                $qq->where('id',$q)
                    ->orWhereHas('usuario', function($u) use ($q){
                        $u->where('nombre','like',"%$q%")
                            ->orWhere('apellido_paterno','like',"%$q%")
                            ->orWhere('apellido_materno','like',"%$q%");
                    });
            })
            ->orderByDesc('fecha')
            ->paginate(20)
            ->appends(compact('q','desde','hasta'));

        $collection = $ventas->getCollection();

        $loteIds = $collection
            ->flatMap(fn($venta) => $venta->detalles->pluck('lote_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $promos = collect();
        if (!empty($loteIds)) {
            $promos = DB::table('asigna_promociones')
                ->join('promociones','promociones.id','=','asigna_promociones.promocion_id')
                ->whereIn('asigna_promociones.lote_id', $loteIds)
                ->whereDate('promociones.fecha_inicio','<=', now())
                ->whereDate('promociones.fecha_fin','>=', now())
                ->select('asigna_promociones.lote_id','promociones.porcentaje')
                ->get()
                ->groupBy('lote_id')
                ->map(fn($rows)=>(float)$rows->max('porcentaje'));
        }

        foreach ($collection as $venta) {
            foreach ($venta->detalles as $d) {
                $lote = $d->lote;
                $prod = $lote?->producto;

                $d->producto_nombre = $prod->nombre ?? '—';
                $d->lote_codigo = $lote->numero ?? $lote->codigo ?? $lote->lote ?? null;
                $d->precio_unitario = (float)($lote->precio_venta ?? 0);
                $d->descuento = (float)($promos[$lote->id] ?? 0); // %
            }
        }

        $ventas->setCollection($collection);

        return view('venta.historial', compact('ventas','q','desde','hasta'));
    }
}