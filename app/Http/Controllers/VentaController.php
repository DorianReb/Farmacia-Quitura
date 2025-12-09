<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AsignaComponente;
use App\Models\Categoria;


class VentaController extends Controller
{
    public function index(Request $request)
    {
        $productoEncontrado = null;

// Recuperar el carrito desde sesión:
        $itemsEnVenta = session('venta_actual.items', []);
        $totalVenta   = session('venta_actual.total', 0);

        // Texto libre del buscador
        $q = trim($request->input('q', ''));

        // Filtros adicionales (si decides poner selects en la vista de venta)
        $categoriaId = $request->input('categoria_id');
        $receta      = $request->input('receta', '');

        // 👉 Parámetro para venta rápida desde el dashboard
        $codigoVentaRapida = trim($request->codigo_venta_rapida ?? '');

        // ==========================
        // BASE DE LA CONSULTA
        // ==========================
        $query = Producto::query()
            ->with(['lotes', 'marca', 'categoria', 'formaFarmaceutica', 'unidadMedida', 'presentacion']);

        // ==========================
        // FILTRO GLOBAL (como en productos.index)
        // ==========================
        if ($q !== '') {
            $lowerQ = mb_strtolower($q, 'UTF-8');

            // 1) Productos que tengan un componente cuyo nombre coincida
            $productoIdsPorComponente = AsignaComponente::whereHas('componente', function ($c) use ($q) {
                $c->where('nombre', 'like', "%{$q}%");
            })
                ->pluck('producto_id')
                ->unique()
                ->values();

            // 2) Traducir texto a filtro de receta (sí/no)
            $recetaFiltro = null;
            if (in_array($lowerQ, ['si', 'sí', 'si receta', 'sí receta', 'requiere receta', 'con receta'])) {
                $recetaFiltro = 1;
            } elseif (in_array($lowerQ, ['no', 'sin receta', 'no receta'])) {
                $recetaFiltro = 0;
            }

            // 3) Aplicar búsqueda sobre varios campos
            $query->where(function ($sub) use ($q, $productoIdsPorComponente, $recetaFiltro) {
                $sub->where('nombre_comercial', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%")
                    // Marca
                    ->orWhereHas('marca', function ($m) use ($q) {
                        $m->where('nombre', 'like', "%{$q}%");
                    })
                    // Categoría
                    ->orWhereHas('categoria', function ($c) use ($q) {
                        $c->where('nombre', 'like', "%{$q}%");
                    });

                // Coincidencia por componente
                if ($productoIdsPorComponente->isNotEmpty()) {
                    $sub->orWhereIn('id', $productoIdsPorComponente);
                }

                // Coincidencia por “requiere receta”
                if (!is_null($recetaFiltro)) {
                    $sub->orWhere('requiere_receta', $recetaFiltro);
                }
            });
        }

        // ==========================
        // FILTROS EXPLÍCITOS (selects)
        // ==========================
        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($receta !== '' && in_array($receta, ['0', '1'], true)) {
            $query->where('requiere_receta', (int) $receta);
        }

        // ==========================
        // RESULTADOS PAGINADOS
        // ==========================
        $productosBuscados = $query
            ->orderBy('nombre_comercial', 'asc')
            ->paginate(10, ['*'], 'productos_page')
            ->appends($request->only('q', 'categoria_id', 'receta'));

        // Bandera para abrir el modal de selección de producto
        $abrirModalMenu = (
                ($q !== '') ||
                $categoriaId ||
                ($receta !== '')
            ) && $productosBuscados->count() > 0;

        // Catálogo de categorías (si quieres pintar un filtro como en productos.index)
        $categorias = Categoria::orderBy('nombre')->get();

        return view('venta.index', compact(
            'productosBuscados',
            'productoEncontrado',
            'itemsEnVenta',
            'totalVenta',
            'q',
            'abrirModalMenu',
            'codigoVentaRapida',
            'categorias',
            'categoriaId',
            'receta'
        ));
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

        // === PROMOCIÓN VIGENTE POR LOTE ===
        $loteIds = $producto->lotes->pluck('id')->filter()->unique()->values()->all();

        $promos = collect();
        if (!empty($loteIds)) {
            $promos = DB::table('asigna_promociones')
                ->join('promociones', 'promociones.id', '=', 'asigna_promociones.promocion_id')
                ->whereIn('asigna_promociones.lote_id', $loteIds)
                ->whereNull('asigna_promociones.deleted_at')
                ->whereNull('promociones.deleted_at')
                ->whereDate('promociones.fecha_inicio', '<=', now())
                ->whereDate('promociones.fecha_fin', '>=', now())
                ->select('asigna_promociones.lote_id', 'promociones.porcentaje')
                ->get()
                ->groupBy('lote_id')
                ->map(fn($rows) => (float) $rows->max('porcentaje'));
        }

        // Orden FEFO y asignar promo_porcentaje a cada lote
        $producto->lotes = $producto->lotes
            ->sortBy('fecha_caducidad')
            ->values()
            ->map(function ($lote) use ($promos) {
                $lote->promo_porcentaje = (float) ($promos[$lote->id] ?? 0);
                return $lote;
            });

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

        // === Componentes + dosis (para "Nombre científico" / concatenación) ===
        $componentesTxt = $producto->asigna_componentes
            ->map(function ($a) {
                $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                $fu     = $a->fuerzaUnidad->nombre ?? '';   // mg, ml, etc.
                $bu     = $a->baseUnidad->nombre ?? '';     // tableta, cápsula, ml, etc.
                $comp   = $a->componente->nombre ?? '';

                // Ejemplo: "Paracetamol 500 mg / 1 tableta"
                return trim(
                    $comp . ' ' .
                    trim($fuerza . ' ' . ($fu ?: '')) .
                    ' / ' .
                    trim($base . ' ' . ($bu ?: ''))
                );
            })
            ->filter()
            ->implode(', ');

        $producto->componentes_texto = $componentesTxt ?: null;
        $producto->nombre_cientifico = $producto->componentes_texto;

        // === Contenido numérico formateado si aplica ===
        if (is_numeric($producto->contenido)) {
            $producto->contenido = rtrim(
                rtrim(number_format($producto->contenido, 2, '.', ''), '0'),
                '.'
            );
        }

        // === RESUMEN COMPLETO PARA LISTAS (nombre + desc + contenido + forma + componentes) ===
        $nombre      = trim($producto->nombre_comercial ?? '');
        $descripcion = trim($producto->descripcion ?? '');
        $contenido   = trim($producto->contenido ?? '');
        $forma       = trim($producto->formaFarmaceutica->nombre ?? '');

        $partes = array_filter([
            $nombre,
            $descripcion ?: null,
            $contenido   ?: null,
            $forma       ?: null,
        ]);

        $baseResumen = implode(' ', $partes);

        // Añadir componentes al final si existen
        if (!empty($componentesTxt)) {
            $baseResumen .= ' ' . $componentesTxt;
        }

        $producto->resumen = trim($baseResumen);

        // Limpiar relaciones para no mandar demasiada info cruda
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
        $data = $request->validate([
            'lotes'           => ['required', 'array', 'min:1'],
            'lotes.*.lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'lotes.*.cantidad'=> ['required', 'integer', 'min:1'],
            'monto_recibido'  => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'usuario_id'     => Auth::id(),
                'fecha'          => now(),
                'total'          => 0,
                'monto_recibido' => 0,
                'cambio'         => 0,
            ]);

            $totalVenta = 0.0;

            foreach ($data['lotes'] as $linea) {
                $loteId   = (int) $linea['lote_id'];
                $cantidad = (int) $linea['cantidad'];

                $res = DB::select('CALL registrar_detalle_venta(?, ?, ?)', [
                    $venta->id,
                    $loteId,
                    $cantidad,
                ]);

                $row = $res[0] ?? null;
                if ($row && isset($row->total_venta)) {
                    $totalVenta = (float) $row->total_venta;
                }
            }

            $montoRecibido = (float) $data['monto_recibido'];

            if ($montoRecibido < $totalVenta) {
                throw new \RuntimeException(
                    'Monto recibido insuficiente. Total: $' .
                    number_format($totalVenta, 2) .
                    ', recibido: $' .
                    number_format($montoRecibido, 2)
                );
            }

            $cambio = $montoRecibido - $totalVenta;

            $venta->update([
                'total'          => $totalVenta,
                'monto_recibido' => $montoRecibido,
                'cambio'         => $cambio,
            ]);

            DB::commit();

// 🔹 Limpiar carrito en sesión al finalizar la venta
            session()->forget('venta_actual');

            return redirect()
                ->route('venta.historial')
                ->with('success',
                    'Venta registrada correctamente. ID: ' . $venta->id .
                    ' | Total: $' . number_format($venta->total, 2) .
                    ' | Recibido: $' . number_format($venta->monto_recibido, 2) .
                    ' | Cambio: $' . number_format($venta->cambio, 2)
                );


        } catch (\Throwable $e) {
            DB::rollBack();

            // 🔍 LOG para ver el error en storage/logs/laravel.log
            \Log::error('Error al registrar venta', [
                'message'   => $e->getMessage(),
                'class'     => get_class($e),
                'errorInfo' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null,
            ]);

            if ($e instanceof \Illuminate\Database\QueryException) {
                // ⚠️ MOSTRAR MENSAJE REAL DE MYSQL MIENTRAS DEPURAS
                $msg = $e->errorInfo[2] ?? $e->getMessage();

                return back()
                    ->withErrors(['venta' => $msg])
                    ->withInput();
            }

            // Otros errores (RuntimeException, etc.)
            return back()
                ->withErrors(['venta' => $e->getMessage()])
                ->withInput();
        }
    }

    public function historial(Request $request)
    {
        $q     = trim($request->input('q', ''));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $ventas = Venta::with([
            'usuario',
            'detalles.lote.producto', // producto via lote
        ])
            ->when($desde, fn($qq) => $qq->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($qq) => $qq->whereDate('fecha', '<=', $hasta))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('id', $q)
                    ->orWhereHas('usuario', function ($u) use ($q) {
                        $u->where('nombre', 'like', "%$q%")
                            ->orWhere('apellido_paterno', 'like', "%$q%")
                            ->orWhere('apellido_materno', 'like', "%$q%");
                    });
            })
            ->orderByDesc('fecha')
            ->paginate(20)
            ->appends(compact('q', 'desde', 'hasta'));

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
                ->join('promociones', 'promociones.id', '=', 'asigna_promociones.promocion_id')
                ->whereIn('asigna_promociones.lote_id', $loteIds)
                ->whereDate('promociones.fecha_inicio', '<=', now())
                ->whereDate('promociones.fecha_fin', '>=', now())
                ->select('asigna_promociones.lote_id', 'promociones.porcentaje')
                ->get()
                ->groupBy('lote_id')
                ->map(fn($rows) => (float) $rows->max('porcentaje'));
        }

        foreach ($collection as $venta) {
            foreach ($venta->detalles as $d) {
                $lote = $d->lote;
                $prod = $lote?->producto;

                // Si quieres, aquí también podrías construir el mismo resumen
                $d->producto_nombre = $prod->nombre ?? '—';
                $d->lote_codigo     = $lote->numero ?? $lote->codigo ?? $lote->lote ?? null;
                $d->precio_unitario = (float) ($lote->precio_venta ?? 0);
                $d->descuento       = (float) ($promos[$lote->id] ?? 0); // %
            }
        }

        $ventas->setCollection($collection);

        return view('venta.historial', compact('ventas', 'q', 'desde', 'hasta'));
    }

    public function ticketHtml(Venta $venta)
    {
        $venta->load('detalles.lote.producto', 'usuario');

        // Obtener IDs de lotes de esta venta
        $loteIds = $venta->detalles->pluck('lote_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Consultar promociones vigentes
        $promos = collect();
        if (!empty($loteIds)) {
            $promos = DB::table('asigna_promociones')
                ->join('promociones', 'promociones.id', '=', 'asigna_promociones.promocion_id')
                ->whereIn('asigna_promociones.lote_id', $loteIds)
                ->whereDate('promociones.fecha_inicio', '<=', now())
                ->whereDate('promociones.fecha_fin', '>=', now())
                ->select('asigna_promociones.lote_id', 'promociones.porcentaje')
                ->get()
                ->groupBy('lote_id')
                ->map(fn($rows) => (float) $rows->max('porcentaje'));
        }

        // Añadir info a cada detalle
        foreach ($venta->detalles as $d) {
            $lote = $d->lote;
            $prod = $lote?->producto;

            $d->producto_nombre = $prod->nombre_comercial ?? '—';
            $d->lote_codigo     = $lote->codigo ?? $lote->numero ?? $lote->lote ?? null;
            $d->precio_unitario = (float) ($lote->precio_venta ?? 0);
            $d->descuento       = (float) ($promos[$lote->id] ?? 0); // %
        }

        // ⬅️ ESTA vista es SOLO fragmento HTML para el modal
        return view('venta.ticket_html', compact('venta'));
    }

    public function ticketPdf(Venta $venta)
    {
        $venta->load('detalles.lote.producto', 'usuario');

        $loteIds = $venta->detalles->pluck('lote_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $promos = collect();
        if (!empty($loteIds)) {
            $promos = DB::table('asigna_promociones')
                ->join('promociones', 'promociones.id', '=', 'asigna_promociones.promocion_id')
                ->whereIn('asigna_promociones.lote_id', $loteIds)
                ->whereDate('promociones.fecha_inicio', '<=', now())
                ->whereDate('promociones.fecha_fin', '>=', now())
                ->select('asigna_promociones.lote_id', 'promociones.porcentaje')
                ->get()
                ->groupBy('lote_id')
                ->map(fn($rows) => (float) $rows->max('porcentaje'));
        }

        foreach ($venta->detalles as $d) {
            $lote = $d->lote;
            $prod = $lote?->producto;

            $d->producto_nombre = $prod->nombre_comercial ?? '—';
            $d->lote_codigo     = $lote->codigo ?? $lote->numero ?? $lote->lote ?? null;
            $d->precio_unitario = (float) ($lote->precio_venta ?? 0);
            $d->descuento       = (float) ($promos[$lote->id] ?? 0); // %
        }

        // ⬅️ ESTA vista sí es documento completo para Dompdf
        $pdf = Pdf::loadView('venta.ticket_pdf', compact('venta'));

        return $pdf->stream("ticket_{$venta->id}.pdf");
    }

    public function agregarItem(Request $request)
    {
        $data = $request->validate([
            'codigo'   => 'required|string',
            'nombre'   => 'required|string',
            'precio'   => 'required|numeric|min:0',
            'cantidad' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'lote_id'  => 'required|integer',
            'stock'    => 'required|integer|min:0',
            'ubicacion'=> 'nullable|string',
            'promo'    => 'nullable|numeric|min:0',
        ]);

        // Obtener carrito actual
        $carrito = session('venta_actual.items', []);

        // Agregar línea
        $carrito[] = $data;

        // Recalcular total
        $total = array_sum(array_column($carrito, 'subtotal'));

        // Guardar en sesión
        session([
            'venta_actual.items' => $carrito,
            'venta_actual.total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'items' => $carrito,
            'total' => $total
        ]);
    }


    public function eliminarItem(Request $request)
    {
        $index = $request->validate(['index' => 'required|integer'])['index'];

        $carrito = session('venta_actual.items', []);

        if (isset($carrito[$index])) {
            unset($carrito[$index]);
            $carrito = array_values($carrito); // Reindexar
        }

        $total = array_sum(array_column($carrito, 'subtotal'));

        session([
            'venta_actual.items' => $carrito,
            'venta_actual.total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'items' => $carrito,
            'total' => $total
        ]);
    }


    public function limpiarCarrito()
    {
        session()->forget('venta_actual');
        return response()->json(['success' => true]);
    }

    public function syncCarrito(Request $request)
    {
        $data = $request->validate([
            'items'                      => ['required','array'],
            'items.*.codigo_barras'      => ['required','string'],
            'items.*.nombre'             => ['required','string'],
            'items.*.precio'             => ['required','numeric'],
            'items.*.cantidad'           => ['required','integer','min:1'],
            'items.*.subtotal'           => ['required','numeric'],
            'items.*.subtotal_bruto'     => ['nullable','numeric'],
            'items.*.stock'              => ['nullable','integer'],
            'items.*.ubicacion'          => ['nullable','string'],
            'items.*.lote_id'            => ['required','integer'],
            'items.*.lote_codigo'        => ['nullable','string'],
            'items.*.promo_porcentaje'   => ['nullable','numeric'],
        ]);

        $items = $data['items'];

        // Recalcular total a partir de los subtotales
        $total = array_sum(array_map(
            fn($i) => (float)($i['subtotal'] ?? 0),
            $items
        ));

        session([
            'venta_actual.items' => $items,
            'venta_actual.total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'total'   => $total,
        ]);
    }



}
