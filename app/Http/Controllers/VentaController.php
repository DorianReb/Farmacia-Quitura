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

        if ($request->q) {
            $productoEncontrado = Producto::with(['lotes', 'marca', 'formaFarmaceutica', 'presentacion', 'categoria'])
                ->where('codigo_barras', $request->q)
                ->first();

            if ($productoEncontrado) {
                $productoEncontrado->existencias_calculadas = $productoEncontrado->lotes->sum('cantidad');

                // Asignar información de lote FEFO si existe
                $productoEncontrado->lotes = $productoEncontrado->lotes->sortBy('fecha_caducidad')->values();
            }
        }

        return view('venta.index', compact('productoEncontrado', 'itemsEnVenta', 'totalVenta'));
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
            'asignaComponentes.nombreCientifico'
        ])
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Calcular existencias
        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');

        // Ordenar lotes por fecha de caducidad (FEFO)
        $producto->lotes = $producto->lotes->sortBy('fecha_caducidad')->values();

        // Asegurar estructura del JSON
        $producto->asigna_ubicaciones = $producto->asignaUbicaciones ?? collect();
        $producto->asigna_componentes = $producto->asignaComponentes ?? collect();
        $producto->forma_farmaceutica = $producto->formaFarmaceutica ?? null;
        //Extraer nombre científico desde la primera asignación si existe
        $primerComponente = $producto->asigna_componentes->first();
        $producto->nombre_cientifico = $primerComponente?->nombreCientifico?->nombre ?? null;

        //  Extraer ubicación (pasillo/nivel)
        $primeraUbicacion = $producto->asigna_ubicaciones->first();
        $nivel = $primeraUbicacion?->nivel?->nombre;
        $pasillo = $primeraUbicacion?->nivel?->pasillo?->nombre;
        $producto->ubicacion_texto = $nivel && $pasillo ? "{$pasillo} / {$nivel}" : ($nivel ?? $pasillo ?? null);

        return response()->json($producto);
    }



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

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        //
    }

}
