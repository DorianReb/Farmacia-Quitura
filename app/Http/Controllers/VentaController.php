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
            'asignaComponentes.componente',
            'asignaComponentes.fuerzaUnidad',
            'asignaComponentes.baseUnidad',
        ])
            ->where('codigo_barras', $codigo)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // === Existencias calculadas por lotes ===
        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');

        // Orden FEFO: por fecha de caducidad
        $producto->lotes = $producto->lotes
            ->sortBy('fecha_caducidad')
            ->values();

        // === Ubicaciones ===
        $producto->asigna_ubicaciones = $producto->asignaUbicaciones ?? collect();
        $producto->asigna_componentes = $producto->asignaComponentes ?? collect();

        $ubicacionesFormateadas = [];
        foreach ($producto->asigna_ubicaciones as $asignacion) {
            $nivelNombreCompleto = $asignacion->nivel?->nombre;
            if ($nivelNombreCompleto) {
                $ubicacionesFormateadas[] = $nivelNombreCompleto;
            }
        }
        $producto->ubicaciones_texto = implode(', ', array_unique($ubicacionesFormateadas));

        // === Componentes + dosis (para "Nombre científico") ===
        $componentesTxt = $producto->asigna_componentes
            ->map(function ($a) {
                $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                $fu     = $a->fuerzaUnidad->nombre ?? '';   // mg, ml, etc.
                $bu     = $a->baseUnidad->nombre ?? '';     // tableta, cápsula, ml, etc.
                $comp   = $a->componente->nombre ?? '';

                // Ej: "Paracetamol 500 mg / 1 tableta"
                return trim($comp.' '.trim($fuerza.' '.($fu ?: '')).' / '.trim($base.' '.($bu ?: '')));
            })
            ->filter()
            ->implode(', ');

        $producto->componentes_texto = $componentesTxt ?: null;

        // Si quieres, también puedes sobreescribir nombre_cientifico con eso:
        $producto->nombre_cientifico = $producto->componentes_texto;

        // === Contenido (solo "20 tabletas", "40 cápsulas", etc.) ===
        // Aquí simplemente devolvemos el campo tal cual lo tengas en BD.
        // Si actualmente lo guardas como "20.00" puedes ajustar los datos
        // en BD o formatear:
        if (is_numeric($producto->contenido)) {
            $producto->contenido = rtrim(rtrim(number_format($producto->contenido, 2, '.', ''), '0'), '.');
        }

        // Limpiar relaciones para no mandar demasiada info
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
        // 1) Validar que vengan las líneas de productos del formulario
        $data = $request->validate([
            'productos'                 => 'required|array|min:1',
            'productos.*.codigo_barras' => 'required|string',
            'productos.*.cantidad'      => 'required|integer|min:1',
            'productos.*.lote_id'       => 'required|integer|exists:lotes,id',
        ]);

        // 2) Convertir el arreglo PHP a la estructura que espera el procedure:
        //    [{lote_id, cantidad}, ...]
        $lineas = [];
        foreach ($data['productos'] as $p) {
            $lineas[] = [
                'lote_id'  => (int) $p['lote_id'],
                'cantidad' => (int) $p['cantidad'],
            ];
        }

        try {
            // 3) Llamar al procedure con el usuario actual y el JSON de líneas
            $resultado = DB::select(
                'CALL registrar_venta_multilote(?, ?)',
                [
                    Auth::id(),
                    json_encode($lineas),
                ]
            );

            $row = $resultado[0] ?? null;
            $ventaId = $row->venta_id ?? null;
            $total   = $row->total ?? null;

            return redirect()
                ->route('venta.index')
                ->with('success', "Venta registrada correctamente. ID: {$ventaId}, Total: $".number_format($total, 2));

        } catch (\Illuminate\Database\QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $msg      = $e->errorInfo[2] ?? $e->getMessage();

            // Errores de negocio disparados por SIGNAL SQLSTATE '45000'
            if ($sqlState === '45000') {
                return redirect()
                    ->back()
                    ->withErrors(['venta' => $msg])
                    ->withInput();
            }

            // Otros errores (sintaxis, conexión, etc.)
            return redirect()
                ->back()
                ->withErrors(['venta' => 'Error interno al registrar la venta: '.$msg])
                ->withInput();
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

    public function ticket(Venta $venta)
    {
        $venta->load('detalles.lote.producto', 'usuario');
        // CAMBIO CLAVE: Devolver la vista como HTML puro (renderizado) para inyectar en AJAX.
        return view('venta.ticket', compact('venta'))->render();
    }
}
