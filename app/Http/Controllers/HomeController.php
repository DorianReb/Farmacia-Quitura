<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Producto;
use App\Models\AsignaComponente;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $hoy   = Carbon::today();
        $desde = Carbon::now()->subDays(30);

        // ===== KPIs =====
        $ingresosHoy = DB::table('ventas')
            ->whereDate('fecha', $hoy)
            ->sum('total');

        $stockBajoCount = DB::table('productos')
            ->whereColumn('existencias', '<=', 'stock_minimo')
            ->count();

        $porCaducar30d = DB::table('lotes')
            ->where('cantidad', '>', 0)
            ->whereBetween('fecha_caducidad', [$hoy, $hoy->copy()->addDays(30)])
            ->sum('cantidad');

        $caducadas = DB::table('lotes')
            ->where('cantidad', '>', 0)
            ->where('fecha_caducidad', '<', $hoy)
            ->count();

        $kpis = [
            'ingresos_hoy'    => '$' . number_format($ingresosHoy, 2),
            'stock_bajo'      => $stockBajoCount,
            'por_caducar_30d' => $porCaducar30d,
            'caducadas'       => $caducadas,
        ];

        // ===== Próximos 5 a caducar (SOLO futuros / hoy) =====
        $proximosACaducar = DB::table('lotes as l')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->where('l.cantidad', '>', 0)
            ->whereDate('l.fecha_caducidad', '>=', $hoy) // evita caducados
            ->orderBy('l.fecha_caducidad')
            ->limit(5)
            ->get([
                'l.id',
                'p.id as producto_id',
                'p.nombre_comercial as producto',
                'l.codigo as lote',
                'l.cantidad as unidades_restantes',
                'l.fecha_caducidad',
                DB::raw('DATEDIFF(l.fecha_caducidad, CURDATE()) as dias_restantes')
            ]);

        // ===== Top 10 más vendidos (últimos 30 días) =====
        $topVendidos = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->where('v.fecha', '>=', $desde)
            ->groupBy('p.id', 'p.nombre_comercial')
            ->orderByDesc(DB::raw('SUM(dv.cantidad)'))
            ->limit(10)
            ->get([
                'p.id as producto_id',
                'p.nombre_comercial as producto',
                DB::raw('SUM(dv.cantidad) as unidades')
            ]);

        // ===== Top 10 menos vendidos (últimos 30 días, >0) =====
        $menosVendidos = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('lotes as l', 'l.id', '=', 'dv.lote_id')
            ->join('productos as p', 'p.id', '=', 'l.producto_id')
            ->where('v.fecha', '>=', $desde)
            ->groupBy('p.id', 'p.nombre_comercial')
            ->havingRaw('SUM(dv.cantidad) > 0')
            ->orderBy(DB::raw('SUM(dv.cantidad)'))
            ->limit(10)
            ->get([
                'p.id as producto_id',
                'p.nombre_comercial as producto',
                DB::raw('SUM(dv.cantidad) as unidades')
            ]);

        // ===== 5 con más alerta de stock bajo =====
        $stockBajo = DB::table('productos')
            ->whereColumn('existencias', '<=', 'stock_minimo')
            ->orderByRaw('(existencias - stock_minimo) ASC')
            ->limit(5)
            ->get([
                'id',
                'nombre_comercial as producto',
                'existencias',
                'stock_minimo'
            ]);

        // ===== Construir "resumen" de producto igual que en ProductoController =====

        // 1) Juntar todos los IDs de producto que aparecen en el dashboard
        $idsProductos = collect()
            ->merge($proximosACaducar->pluck('producto_id'))
            ->merge($topVendidos->pluck('producto_id'))
            ->merge($menosVendidos->pluck('producto_id'))
            ->merge($stockBajo->pluck('id')) // en stockBajo el id es de productos
            ->filter()
            ->unique()
            ->values();

        $resumenPorProducto = [];

        if ($idsProductos->isNotEmpty()) {
            // 2) Cargar productos con sus relaciones necesarias
            $productosInfo = Producto::with(['formaFarmaceutica'])
                ->whereIn('id', $idsProductos)
                ->get()
                ->keyBy('id');

            // 3) Cargar asignaciones de componentes para esos productos
            $asignaciones = AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $idsProductos)
                ->get()
                ->groupBy('producto_id');

            // 4) Repetir la misma lógica de resumen que en ProductoController
            foreach ($productosInfo as $producto) {
                $componentesTxt = '';
                if (isset($asignaciones[$producto->id])) {
                    $componentesTxt = $asignaciones[$producto->id]
                        ->map(function ($a) {
                            $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                            $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                            $fu     = $a->fuerzaUnidad->nombre ?? '';   // mg, ml, etc.
                            $bu     = $a->baseUnidad->nombre ?? '';     // tableta, cápsula, etc.
                            $comp   = $a->componente->nombre ?? '';
                            return trim($comp.' '.trim($fuerza.' '.($fu ?: '')).' / '.trim($base.' '.($bu ?: '')));
                        })
                        ->implode(', ');
                    $componentesTxt = $componentesTxt ? " {$componentesTxt}" : '';
                }

                $nombre      = trim($producto->nombre_comercial ?? '');
                $descripcion = trim($producto->descripcion ?? '');
                $contenido   = trim($producto->contenido ?? '');
                $forma       = trim($producto->formaFarmaceutica->nombre ?? '');

                $partes = array_filter([$nombre, $descripcion ?: null, $contenido ?: null, $forma ?: null]);

                $resumenPorProducto[$producto->id] = trim(implode(' ', $partes).$componentesTxt);
            }
        }

        // 5) Adjuntar el resumen a cada colección usada en el dashboard
        $proximosACaducar->transform(function ($row) use ($resumenPorProducto) {
            $row->producto_resumen = $resumenPorProducto[$row->producto_id] ?? $row->producto;
            return $row;
        });

        $topVendidos->transform(function ($row) use ($resumenPorProducto) {
            $row->producto_resumen = $resumenPorProducto[$row->producto_id] ?? $row->producto;
            return $row;
        });

        $menosVendidos->transform(function ($row) use ($resumenPorProducto) {
            $row->producto_resumen = $resumenPorProducto[$row->producto_id] ?? $row->producto;
            return $row;
        });

        $stockBajo->transform(function ($row) use ($resumenPorProducto) {
            $row->producto_resumen = $resumenPorProducto[$row->id] ?? $row->producto;
            return $row;
        });

        return view('home', compact(
            'kpis',
            'proximosACaducar',
            'topVendidos',
            'menosVendidos',
            'stockBajo'
        ));
    }
}
