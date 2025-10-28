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
        // Incluimos las relaciones necesarias para mostrar la información básica en el modal de menú
        $productosBuscados = Producto::query()
            ->with(['lotes']) // Solo si necesitas saber el stock total para el menú
            ->when($q, function ($query) use ($q) {
                // Priorizamos la búsqueda por nombre comercial
                $query->where('nombre_comercial', 'LIKE', "%{$q}%")
                    ->orWhere('codigo_barras', 'LIKE', "{$q}");
            })
            ->orderBy('nombre_comercial', 'asc')
            ->paginate(10, ['*'], 'productos_page');

        // NUEVO: Bandera para que el JavaScript sepa si debe abrir el modal al cargar la página.
        $abrirModalMenu = ($q && $productosBuscados->count() > 0) ? true : false;
        
        // El resto de variables que usas en la vista:
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
            'asignaComponentes.componente' // Corregida la relación
        ])
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // ... (Tu código para calcular existencias y ordenar lotes) ...
        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');
        $producto->lotes = $producto->lotes->sortBy('fecha_caducidad')->values();

        // --- INICIO DEL PARCHE DE UBICACIONES Y COMPONENTES ---

        // Sincronizamos las colecciones (ya estaba bien)
        $producto->asigna_ubicaciones = $producto->asignaUbicaciones ?? collect();
        $producto->asigna_componentes = $producto->asignaComponentes ?? collect();
        $producto->forma_farmaceutica = $producto->formaFarmaceutica ?? null;

        // Extraer Nombre Científico (ya estaba bien)
        $primerComponente = $producto->asigna_componentes->first();
        $producto->nombre_cientifico = $primerComponente?->componente?->nombre ?? null;


        // --- LÓGICA DE FORMATEO DE UBICACIONES (LA CLAVE) ---
        // La variable $ubicacionesFormateadas se inicializa aquí
        $ubicacionesFormateadas = [];
        
        foreach ($producto->asigna_ubicaciones as $asignacion) {
            
            // Usamos el accesor ->nombre del modelo Nivel que ya trae el Pasillo::codigo (ej: P01 - Nivel 1)
            $nivelNombreCompleto = $asignacion->nivel?->nombre;
            
            if ($nivelNombreCompleto) {
                $ubicacionesFormateadas[] = $nivelNombreCompleto;
            }
        }

        // Creamos la propiedad simple para el JSON
        $producto->ubicaciones_texto = implode(', ', array_unique($ubicacionesFormateadas));
        
        // Finalizamos limpiando las relaciones complejas para un JSON más limpio
        $producto->unsetRelation('asignaUbicaciones');
        $producto->unsetRelation('asignaComponentes');


        return response()->json($producto);
    }
    // ...



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.codigo_barras' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.lote' => 'required|integer'
        ]);

        $venta = Venta::create([
            'usuario_id' => Auth::id(),
            'fecha' => now(),
            'total' => 0
        ]);

        $total = 0;

        foreach ($request->productos as $p) {
            $lote = Lote::find($p['lote']);
            if (!$lote) continue;

            // Obtener producto relacionado
            $producto = $lote->producto;

            // Calcular subtotal usando precio de venta del producto
            $subtotal = $p['cantidad'] * $producto->precio_venta;

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'lote_id' => $lote->id,
                'cantidad' => $p['cantidad'],
                'subtotal' => $subtotal
            ]);

            $total += $subtotal;

            // Reducir stock del lote
            $lote->cantidad -= $p['cantidad'];
            $lote->save();
        }

        // Guardar total de la venta
        $venta->total = $total;
        $venta->save();

        return response()->json([
            'message' => 'Venta registrada correctamente',
            'venta_id' => $venta->id
        ]);
    }


    public function historial(Request $request)
    {
        $q     = trim($request->input('q',''));
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

        // 🔧 IMPORTANTE: trabajar con la colección interna del paginator
        $collection = $ventas->getCollection();

        // (Opcional) buscar descuentos/promos por lote
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

        // Enriquecer detalles para la vista/row-details del DataTable
        foreach ($collection as $venta) {
            foreach ($venta->detalles as $d) {
                $lote = $d->lote;
                $prod = $lote?->producto;

                $d->producto_nombre = $prod->nombre ?? '—';
                $d->lote_codigo     = $lote->numero ?? $lote->codigo ?? $lote->lote ?? null;
                $d->precio_unitario = (float)($lote->precio_venta ?? 0);
                $d->descuento       = (float)($promos[$lote->id] ?? 0); // %
            }
        }

        // Volver a colocar la colección enriquecida dentro del paginator (opcional)
        $ventas->setCollection($collection);

        return view('venta.historial', compact('ventas','q','desde','hasta'));
    }

}
