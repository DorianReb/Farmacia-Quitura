<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AsignaComponente;

class ReporteController extends Controller
{
    // ===================== RENTABILIDAD / UTILIDAD =====================
    // app/Http/Controllers/ReporteController.php

    public function rentabilidad(Request $request)
    {
        // ====== 1. RANGOS DE FECHAS ======
        // Productos (utilidad por producto)
        $fromProd = $request->input('from_prod');
        $toProd   = $request->input('to_prod');

        if (!$fromProd || !$toProd) {
            $fromProd = Carbon::today()->subDays(30)->toDateString();
            $toProd   = Carbon::today()->toDateString();
        }

        // Usuarios (ventas por usuario)
        $fromUser = $request->input('from_user');
        $toUser   = $request->input('to_user');

        if (!$fromUser || !$toUser) {
            $fromUser = Carbon::today()->subDays(30)->toDateString();
            $toUser   = Carbon::today()->toDateString();
        }

        // Búsqueda por texto en productos
        $qProd = $request->input('q_prod');

        // ====== 2. BASE QUERY: UTILIDAD POR PRODUCTO ======
        $baseProdQuery = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('marcas as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('formas_farmaceuticas as ff', 'ff.id', '=', 'p.forma_farmaceutica_id')
            ->whereNull('v.deleted_at')
            ->whereBetween('v.fecha', [$fromProd, $toProd]);

        if ($qProd) {
            $baseProdQuery->where(function ($q) use ($qProd) {
                $q->where('p.nombre_comercial', 'like', "%{$qProd}%")
                    ->orWhere('c.nombre', 'like', "%{$qProd}%")
                    ->orWhere('m.nombre', 'like', "%{$qProd}%");
            });
        }

        // Query agrupada por producto (incluyendo datos base para concatenar)
        $prodAggQuery = (clone $baseProdQuery)
            ->groupBy(
                'p.id',
                'p.nombre_comercial',
                'p.descripcion',
                'p.contenido',
                'ff.nombre',
                'c.nombre',
                'm.nombre'
            )
            ->selectRaw('
            p.id               as producto_id,
            p.nombre_comercial as nombre_comercial,
            p.descripcion      as descripcion,
            p.contenido        as contenido,
            ff.nombre          as forma_farmaceutica,
            c.nombre           as categoria,
            m.nombre           as marca,
            SUM(dv.cantidad)   as unidades,
            SUM(dv.subtotal)   as ingresos,
            SUM(dv.cantidad * l.precio_compra) as costo,
            SUM(dv.subtotal) - SUM(dv.cantidad * l.precio_compra) as utilidad
        ');

        // ====== 3. KPIs GLOBALES ======
        $global = (clone $baseProdQuery)
            ->selectRaw('
            SUM(dv.subtotal)                as ingresos,
            SUM(dv.cantidad * l.precio_compra) as costos
        ')
            ->first();

        $ingresosTotal = (float)($global->ingresos ?? 0);
        $costosTotal   = (float)($global->costos ?? 0);
        $utilidadTotal = $ingresosTotal - $costosTotal;

        $margenPromedio = $ingresosTotal > 0
            ? ($utilidadTotal / $ingresosTotal) * 100
            : 0;

        $kpis = [
            'utilidad_total'  => '$' . number_format($utilidadTotal, 2),
            'ingresos'        => '$' . number_format($ingresosTotal, 2),
            'costos'          => '$' . number_format($costosTotal, 2),
            'margen_promedio' => number_format($margenPromedio, 2) . '%',
        ];

        // ====== 4. TOP 10 PRODUCTOS POR UTILIDAD (para gráfica) ======
        $topProd = (clone $prodAggQuery)
            ->orderByDesc('utilidad')
            ->limit(10)
            ->get();

        // ====== 5. TABLA: UTILIDAD POR PRODUCTO (PAGINADA) ======
        $utilidadPorProducto = (clone $prodAggQuery)
            ->orderByDesc('utilidad')
            ->paginate(15);

        // ====== 6. CARGAR COMPONENTES (asigna_componentes) PARA ESOS PRODUCTOS ======
        $idsProductos = $utilidadPorProducto->getCollection()
            ->pluck('producto_id')
            ->merge($topProd->pluck('producto_id'))
            ->unique()
            ->filter();

        $asignaciones = $idsProductos->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $idsProductos)
                ->get()
                ->groupBy('producto_id');

        // Helper para construir el nombre concatenado (igual estilo que en ProductoController)
        $buildNombreConcatenado = function ($row) use ($asignaciones) {
            $componentesTxt = '';
            if (isset($asignaciones[$row->producto_id])) {
                $componentesTxt = $asignaciones[$row->producto_id]
                    ->map(function ($a) {
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                        $base   = $a->base_cantidad !== null
                            ? rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.')
                            : null;

                        $fu   = $a->fuerzaUnidad->nombre ?? ''; // mg, ml, etc.
                        $bu   = $a->baseUnidad->nombre ?? '';   // tableta, cápsula, ml, etc.
                        $comp = $a->componente->nombre ?? '';

                        $parteFuerza = trim($fuerza . ' ' . ($fu ?: ''));
                        $parteBase   = $base !== null ? trim($base . ' ' . ($bu ?: '')) : '';

                        if ($parteBase !== '') {
                            return trim($comp . ' ' . $parteFuerza . ' / ' . $parteBase);
                        }
                        return trim($comp . ' ' . $parteFuerza);
                    })
                    ->implode(', ');

                $componentesTxt = $componentesTxt ? ' ' . $componentesTxt : '';
            }

            $nombre      = trim($row->nombre_comercial ?? '');
            $descripcion = trim($row->descripcion ?? '');
            $contenido   = $row->contenido !== null
                ? rtrim(rtrim(number_format($row->contenido, 2, '.', ''), '0'), '.')
                : '';
            $forma       = trim($row->forma_farmaceutica ?? '');

            $partes = array_filter([
                $nombre,
                $descripcion ?: null,
                $contenido ?: null,
                $forma ?: null,
            ]);

            return trim(implode(' ', $partes) . $componentesTxt);
        };

        // ====== 7. COMPLETAR CAMPOS DERIVADOS (margen_pct, pct_total, nombre concatenado) ======
        $utilidadPorProducto->getCollection()->transform(function ($row) use ($utilidadTotal, $buildNombreConcatenado) {
            $ingresos = (float)($row->ingresos ?? 0);
            $utilidad = (float)($row->utilidad ?? 0);

            $row->margen_pct = $ingresos > 0
                ? ($utilidad / $ingresos) * 100
                : null;

            $row->pct_total_utilidad = $utilidadTotal > 0
                ? ($utilidad / $utilidadTotal) * 100
                : null;

            // 👇 Aquí sustituimos el nombre "simple" por el concatenado
            $row->producto = $buildNombreConcatenado($row);

            return $row;
        });

        // ====== 8. CHART: TOP 10 PRODUCTOS POR UTILIDAD (USANDO NOMBRE CONCATENADO) ======
        $chartProdLabels = [];
        $chartProdData   = [];

        foreach ($topProd as $row) {
            $chartProdLabels[] = $buildNombreConcatenado($row);
            $chartProdData[]   = round((float)$row->utilidad, 2);
        }

        $chartProd = [
            'labels' => $chartProdLabels,
            'data'   => $chartProdData,
        ];

        // ====== 9. VENTAS POR USUARIO ======
        $baseUserQuery = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->join('usuarios as u', 'u.id', '=', 'v.usuario_id')
            ->whereNull('v.deleted_at')
            ->whereBetween('v.fecha', [$fromUser, $toUser]);

        $ventasPorUsuarioQuery = (clone $baseUserQuery)
            ->groupBy('u.id', 'u.nombre', 'u.apellido_paterno', 'u.apellido_materno')
            ->selectRaw('
            CONCAT(u.nombre, " ", u.apellido_paterno, " ", u.apellido_materno) as usuario,
            COUNT(DISTINCT v.id) as ventas,
            SUM(dv.subtotal) as ingresos,
            SUM(dv.subtotal) - SUM(dv.cantidad * l.precio_compra) as utilidad
        ');

        $ventasPorUsuario = $ventasPorUsuarioQuery
            ->orderByDesc('utilidad')
            ->paginate(10);

        // ====== 10. CHART: TOP USUARIOS POR UTILIDAD ======
        $topUsr = (clone $ventasPorUsuarioQuery)
            ->orderByDesc('utilidad')
            ->limit(10)
            ->get();

        $chartUsr = [
            'labels' => $topUsr->pluck('usuario')->values()->all(),
            'data'   => $topUsr->pluck('utilidad')->map(fn($v) => round((float)$v, 2))->values()->all(),
        ];

        // ====== 11. RETORNAR A LA VISTA ======
        return view('reportes.rentabilidad', [
            'kpis'               => $kpis,
            'utilidadPorProducto'=> $utilidadPorProducto,
            'ventasPorUsuario'   => $ventasPorUsuario,
            'chartProd'          => $chartProd,
            'chartUsr'           => $chartUsr,
            'fromProd'           => $fromProd,
            'toProd'             => $toProd,
            'fromUser'           => $fromUser,
            'toUser'             => $toUser,
        ]);
    }



    // ===================== RANKING (más / menos vendidos) =====================
    public function ranking(Request $request)
    {
        // ====== 1. Parámetros de filtro ======
        $from = $request->input('from');
        $to   = $request->input('to');

        if (!$from || !$to) {
            $from = Carbon::today()->subDays(30)->toDateString();
            $to   = Carbon::today()->toDateString();
        }

        $q     = trim($request->input('q', ''));
        $order = $request->input('order', 'mas'); // mas | menos

        // ====== 2. Base query (productos + ventas) ======
        $base = DB::table('productos as p')
            ->leftJoin('lotes as l', 'l.producto_id', '=', 'p.id')
            ->leftJoin('detalles_ventas as dv', 'dv.lote_id', '=', 'l.id')
            ->leftJoin('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('marcas as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('formas_farmaceuticas as ff', 'ff.id', '=', 'p.forma_farmaceutica_id')
            ->whereNull('p.deleted_at')
            // incluir productos sin ventas en el periodo
            ->where(function ($qDates) use ($from, $to) {
                $qDates->whereBetween('v.fecha', [$from, $to])
                    ->orWhereNull('v.id');
            });

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('p.nombre_comercial', 'like', "%{$q}%")
                    ->orWhere('p.descripcion', 'like', "%{$q}%")
                    ->orWhere('c.nombre', 'like', "%{$q}%")
                    ->orWhere('m.nombre', 'like', "%{$q}%");
            });
        }

        // ====== 3. Agregación por producto ======
        $agg = $base
            ->groupBy(
                'p.id',
                'p.nombre_comercial',
                'p.descripcion',
                'p.contenido',
                'ff.nombre',
                'c.nombre',
                'm.nombre'
            )
            ->selectRaw('
            p.id               as producto_id,
            p.nombre_comercial as nombre_comercial,
            p.descripcion      as descripcion,
            p.contenido        as contenido,
            ff.nombre          as forma_farmaceutica,
            c.nombre           as categoria,
            m.nombre           as marca,
            COALESCE(SUM(dv.cantidad), 0)  as unidades,
            COALESCE(SUM(dv.subtotal), 0)  as ventas,
            COUNT(DISTINCT v.id)           as num_ventas
        ');

        // ====== 4. Orden según ranking (más / menos vendidos) ======
        if ($order === 'menos') {
            $agg->orderBy('unidades', 'asc')->orderBy('nombre_comercial');
        } else {
            $order = 'mas';
            $agg->orderBy('unidades', 'desc')->orderBy('nombre_comercial');
        }

        // ====== 5. Paginación ======
        $ranking = $agg->paginate(15)->appends($request->query());

        // ====== 6. Cargar componentes (asigna_componentes) para concatenar nombre ======
        $productoIds = $ranking->getCollection()
            ->pluck('producto_id')
            ->unique()
            ->filter();

        $asignaciones = $productoIds->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $productoIds)
                ->get()
                ->groupBy('producto_id');

        $buildNombreConcatenado = function ($row) use ($asignaciones) {
            // Componentes
            $componentesTxt = '';
            if (isset($asignaciones[$row->producto_id])) {
                $componentesTxt = $asignaciones[$row->producto_id]
                    ->map(function ($a) {
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                        $base   = $a->base_cantidad !== null
                            ? rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.')
                            : null;

                        $fu   = $a->fuerzaUnidad->nombre ?? '';
                        $bu   = $a->baseUnidad->nombre ?? '';
                        $comp = $a->componente->nombre ?? '';

                        $parteFuerza = trim($fuerza . ' ' . ($fu ?: ''));
                        $parteBase   = $base !== null ? trim($base . ' ' . ($bu ?: '')) : '';

                        if ($parteBase !== '') {
                            return trim($comp . ' ' . $parteFuerza . ' / ' . $parteBase);
                        }
                        return trim($comp . ' ' . $parteFuerza);
                    })
                    ->implode(', ');

                $componentesTxt = $componentesTxt ? ' ' . $componentesTxt : '';
            }

            $nombre      = trim($row->nombre_comercial ?? '');
            $descripcion = trim($row->descripcion ?? '');

            // contenido puede ser decimal o string, lo hacemos "bonito"
            if ($row->contenido !== null && $row->contenido !== '') {
                $contenido = rtrim(rtrim(number_format((float)$row->contenido, 2, '.', ''), '0'), '.');
            } else {
                $contenido = '';
            }

            $forma = trim($row->forma_farmaceutica ?? '');

            $partes = array_filter([
                $nombre,
                $descripcion ?: null,
                $contenido ?: null,
                $forma ?: null,
            ]);

            return trim(implode(' ', $partes) . $componentesTxt);
        };

        // Aplicar nombre concatenado en el paginator
        $ranking->getCollection()->transform(function ($row) use ($buildNombreConcatenado) {
            $row->producto = $buildNombreConcatenado($row);
            return $row;
        });

        // ====== 7. KPIs simples del ranking ======
// 1) Total de productos (del catálogo, sin borrar)
        $totalProductos = DB::table('productos')
            ->whereNull('deleted_at')
            ->count();

// 2) Productos con al menos una venta en el periodo
        $totalConVentas = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->whereNull('p.deleted_at')
            ->whereBetween('v.fecha', [$from, $to])
            ->distinct('p.id')
            ->count('p.id');

// 3) Unidades totales vendidas en el periodo
        $totalUnidades = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->whereNull('p.deleted_at')
            ->whereBetween('v.fecha', [$from, $to])
            ->sum('dv.cantidad');

        $kpis = [
            'total_productos'      => $totalProductos,
            'productos_con_ventas' => $totalConVentas,
            'unidades_vendidas'    => $totalUnidades,
        ];


        // ====== 8. Top 10 para gráficas ======
        $topMas = (clone $agg)
            ->having('unidades', '>', 0)
            ->orderBy('unidades', 'desc')
            ->limit(10)
            ->get();

        $topMenos = (clone $agg)
            ->having('unidades', '>', 0)
            ->orderBy('unidades', 'asc')
            ->limit(10)
            ->get();

        $chartMasLabels   = [];
        $chartMasData     = [];
        foreach ($topMas as $row) {
            $chartMasLabels[] = $buildNombreConcatenado($row);
            $chartMasData[]   = (int)$row->unidades;
        }

        $chartMenosLabels = [];
        $chartMenosData   = [];
        foreach ($topMenos as $row) {
            $chartMenosLabels[] = $buildNombreConcatenado($row);
            $chartMenosData[]   = (int)$row->unidades;
        }

        $chartMas = [
            'labels' => $chartMasLabels,
            'data'   => $chartMasData,
        ];

        $chartMenos = [
            'labels' => $chartMenosLabels,
            'data'   => $chartMenosData,
        ];

        // ====== 9. Enviar a la vista ======
        return view('reportes.ranking', [
            'ranking'    => $ranking,
            'kpis'       => $kpis,
            'from'       => $from,
            'to'         => $to,
            'order'      => $order,
            'q'          => $q,
            'chartMas'   => $chartMas,
            'chartMenos' => $chartMenos,
        ]);
    }

    // ===================== CADUCIDAD =====================
    public function caducidad(Request $request)
    {
        $dias = (int) $request->query('dias', 60);
        if (! in_array($dias, [30, 60, 90])) {
            $dias = 60;
        }

        $hoy      = Carbon::today();
        $finRango = $hoy->copy()->addDays($dias);

        // Próximos a caducar (entre hoy y hoy + N días)
        $proximos = DB::table('lotes')
            ->join('productos', 'productos.id', '=', 'lotes.producto_id')
            ->whereNull('lotes.deleted_at')
            ->where('lotes.cantidad', '>', 0)
            ->whereBetween('lotes.fecha_caducidad', [
                $hoy->toDateString(),
                $finRango->toDateString(),
            ])
            ->orderBy('lotes.fecha_caducidad')
            ->select(
                'lotes.id',
                'productos.nombre_comercial as producto',
                'lotes.codigo as lote',
                'lotes.cantidad as unidades_restantes',
                'lotes.fecha_caducidad',
                DB::raw('DATEDIFF(lotes.fecha_caducidad, CURDATE()) as dias_restantes')
            )
            ->paginate(10, ['*'], 'proximos_page');

        // Caducados (antes de hoy)
        $caducados = DB::table('lotes')
            ->join('productos', 'productos.id', '=', 'lotes.producto_id')
            ->whereNull('lotes.deleted_at')
            ->where('lotes.cantidad', '>', 0)
            ->where('lotes.fecha_caducidad', '<', $hoy->toDateString())
            ->orderBy('lotes.fecha_caducidad')
            ->select(
                'lotes.id',
                'productos.nombre_comercial as producto',
                'lotes.codigo as lote',
                'lotes.cantidad as unidades_restantes',
                'lotes.fecha_caducidad',
                DB::raw('DATEDIFF(CURDATE(), lotes.fecha_caducidad) as dias_vencidos')
            )
            ->paginate(10, ['*'], 'caducados_page');

        return view('reportes.caducidad', compact('proximos', 'caducados', 'dias'));
    }

    // ===================== STOCK BAJO =====================
    public function stockBajo()
    {
        $productos = DB::table('productos')
            ->whereNull('deleted_at')
            ->whereColumn('existencias', '<=', 'stock_minimo')
            ->orderBy('existencias')
            ->paginate(20);

        return view('reportes.stock_bajo', compact('productos'));
    }

    // ===================== SIN VENTAS =====================
    public function sinVentas(Request $request)
    {
        $inicio = $request->input('inicio', Carbon::today()->subDays(60)->toDateString());
        $fin    = $request->input('fin', Carbon::today()->toDateString());

        $sinVentas = DB::table('productos as p')
            ->leftJoin('lotes as l', 'l.producto_id', '=', 'p.id')
            ->leftJoin('detalles_ventas as dv', 'dv.lote_id', '=', 'l.id')
            ->leftJoin('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->whereNull('p.deleted_at')
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereNull('v.id') // nunca se ha vendido
                ->orWhereNotBetween('v.fecha', [$inicio, $fin]); // o no se vendió en el periodo
            })
            ->groupBy('p.id', 'p.nombre_comercial', 'p.existencias', 'p.stock_minimo')
            ->select(
                'p.id',
                'p.nombre_comercial',
                'p.existencias',
                'p.stock_minimo'
            )
            ->paginate(20);

        return view('reportes.sin_ventas', compact('sinVentas', 'inicio', 'fin'));
    }
}
