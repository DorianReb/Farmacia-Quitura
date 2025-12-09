<?php

namespace App\Http\Controllers;

use App\Models\AsignaUbicacion;
use App\Models\Producto;
use App\Models\Nivel;
use App\Models\Pasillo;
use App\Models\AsignaComponente;
use Illuminate\Http\Request;

class AsignaUbicacionController extends Controller
{
    /**
     * Mostrar listado de asignaciones con paginación
     */
    public function index(Request $request)
    {
        // ================= PASILLOS =================
        $pasillosQuery = Pasillo::query()->orderBy('codigo');

        if ($request->filled('q_pasillo')) {
            $qPasillo = trim($request->q_pasillo);
            $pasillosQuery->where('codigo', 'like', "%{$qPasillo}%");
        }

        $pasillos = $pasillosQuery->paginate(10, ['*'], 'page_pasillos');

        // ================= NIVELES ==================
        $nivelesQuery = Nivel::with('pasillo')->orderBy('numero');

        if ($request->filled('q_nivel')) {
            $qNivel = trim($request->q_nivel);

            $nivelesQuery->where('numero', 'like', "%{$qNivel}%")
                ->orWhereHas('pasillo', function ($q) use ($qNivel) {
                    $q->where('codigo', 'like', "%{$qNivel}%");
                });
        }

        $niveles = $nivelesQuery->paginate(10, ['*'], 'page_niveles');

        // ============= ASIGNACIONES =================
        $ubicacionesQuery = AsignaUbicacion::with([
            'producto.marca',
            'producto.formaFarmaceutica',
            'producto.presentacion',
            'nivel.pasillo',
        ]);

        if ($request->filled('q_ubicacion')) {
            $qUbic = trim($request->q_ubicacion);

            $ubicacionesQuery
                ->whereHas('producto', function ($q) use ($qUbic) {
                    $q->where('nombre_comercial', 'like', "%{$qUbic}%")
                        ->orWhere('descripcion', 'like', "%{$qUbic}%");
                })
                ->orWhereHas('nivel', function ($q) use ($qUbic) {
                    $q->where('numero', 'like', "%{$qUbic}%")
                        ->orWhereHas('pasillo', function ($qq) use ($qUbic) {
                            $qq->where('codigo', 'like', "%{$qUbic}%");
                        });
                });
        }

        $ubicaciones = $ubicacionesQuery->paginate(15, ['*'], 'page_ubicaciones');

        // ================= RESUMEN DE PRODUCTO (IGUAL QUE EN ProductoController) =================
        $pageProductoIds = $ubicaciones->getCollection()
            ->pluck('producto_id')
            ->filter()
            ->unique();

        $asignaciones = $pageProductoIds->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $pageProductoIds)
                ->orderBy('nombre_cientifico_id')
                ->get()
                ->groupBy('producto_id');

        foreach ($ubicaciones->getCollection() as $ubicacion) {
            $producto = $ubicacion->producto;
            if (!$producto) {
                continue;
            }

            // Componentes iguales que en ProductoController
            $componentesTxt = '';
            if (isset($asignaciones[$producto->id])) {
                $componentesTxt = $asignaciones[$producto->id]
                    ->map(function ($a) {
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                        $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                        $fu     = $a->fuerzaUnidad->nombre ?? '';
                        $bu     = $a->baseUnidad->nombre ?? '';
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

            $partes = array_filter([
                $nombre,
                $descripcion ?: null,
                $contenido ?: null,
                $forma ?: null,
            ]);

            $producto->resumen = trim(implode(' ', $partes).$componentesTxt);
        }

        // Productos para selects de los modales
        // Productos para selects de los modales (con resumen concatenado)
        $productos = Producto::with([
            'formaFarmaceutica',
            'asignaComponentes.componente',
            'asignaComponentes.fuerzaUnidad',
            'asignaComponentes.baseUnidad',
        ])
            ->orderBy('nombre_comercial')
            ->get();

// Construir resumen para cada producto
        foreach ($productos as $producto) {
            $componentesTxt = $producto->asignaComponentes
                ->map(function ($a) {
                    $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                    $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                    $fu     = $a->fuerzaUnidad->nombre ?? '';
                    $bu     = $a->baseUnidad->nombre ?? '';
                    $comp   = $a->componente->nombre ?? '';

                    return trim($comp.' '.trim($fuerza.' '.($fu ?: '')).' / '.trim($base.' '.($bu ?: '')));
                })
                ->filter()
                ->implode(', ');

            $componentesTxt = $componentesTxt ? ' '.$componentesTxt : '';

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

            $producto->resumen = trim(implode(' ', $partes).$componentesTxt);
        }

        return view('ubicacion.index', compact('pasillos', 'niveles', 'ubicaciones', 'productos'));

    }


    /**
     * Guardar nueva asignación
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'nivel_id' => 'required|exists:niveles,id',
        ]);

        AsignaUbicacion::create($request->only('producto_id', 'nivel_id'));

        return redirect()->route('ubicacion.index')->with('success', 'Asignación creada correctamente.');
    }

    /**
     * Actualizar asignación
     */
    public function update(Request $request, AsignaUbicacion $ubicacion)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'nivel_id'    => 'required|exists:niveles,id',
        ]);

        $ubicacion->update($request->only('producto_id', 'nivel_id'));

        return redirect()->route('ubicacion.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    /**
     * Eliminar asignación
     */
    public function destroy(AsignaUbicacion $ubicacion)
    {
        $ubicacion->delete();

        return redirect()->route('ubicacion.index')
            ->with('success', 'Asignación eliminada correctamente.');
    }
}
