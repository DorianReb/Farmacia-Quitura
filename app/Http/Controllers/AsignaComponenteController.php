<?php

namespace App\Http\Controllers;

use App\Models\AsignaComponente;
use App\Models\Producto;
use App\Models\NombreCientifico;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AsignaComponenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));
        $perPage = 25;
        $page = (int) ($request->input('page', 1));

        // 1) Filtra por producto o componente y obtén los IDs de producto que tienen asignaciones
        $productoIds = AsignaComponente::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->whereHas('producto', fn($p) => $p->where('nombre_comercial', 'like', "%{$q}%"))
                        ->orWhereHas('componente', fn($c) => $c->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->pluck('producto_id')
            ->unique()
            ->values();

        // 2) Obtén los productos filtrados y ordénalos por nombre
        $productosAll = $productoIds->isEmpty()
            ? collect()
            : Producto::whereIn('id', $productoIds)
                ->orderBy('nombre_comercial')
                ->get(['id','nombre_comercial']);

        // 3) Pagina a nivel producto
        $total = $productosAll->count();
        $productosPage = $productosAll->forPage($page, $perPage)->values();

        $productosPaginator = new LengthAwarePaginator(
            $productosPage, $total, $perPage, $page,
            ['path' => route('asigna_componentes.index'), 'query' => $request->query()]
        );

        // 4) Trae TODAS las asignaciones solo de los productos visibles en esta página y agrúpalas por producto_id
        $pageIds = $productosPage->pluck('id');

        $asignacionesPorProducto = $pageIds->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $pageIds)
                ->orderBy('nombre_cientifico_id') // opcional: ordena por componente
                ->get()
                ->groupBy('producto_id');

        // 5) Meta por producto (conteo + nombres únicos para badge/preview/popover)
        $metaPorProducto = $asignacionesPorProducto->map(function (Collection $rows) {
            $nombres = $rows->pluck('componente.nombre')->filter()->unique()->sort()->values();
            return [
                'total'   => $nombres->count(),
                'nombres' => $nombres->all(),
            ];
        });

        // 6) Para los modales (create/edit)
        $productos   = Producto::orderBy('nombre_comercial')->get(['id','nombre_comercial']);
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        return view('producto.index', [
            'q'                     => $q,
            'productosPaginator'    => $productosPaginator,   // ← paginador de productos
            'asignacionesPorProducto' => $asignacionesPorProducto, // ← grupos por producto_id
            'metaPorProducto'       => $metaPorProducto,
            'productos'             => $productos,
            'componentes'           => $componentes,
            'unidades'              => $unidades,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos   = Producto::orderBy('nombre_comercial')->get(['id','nombre_comercial']); // ← CAMBIADO
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        return view('asigna_componentes.create', compact('productos','componentes','unidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'producto_id'          => ['required','exists:productos,id'],
            'nombre_cientifico_id' => ['required','exists:nombres_cientificos,id'],
            'fuerza_cantidad'      => ['required','numeric','min:0'],
            'fuerza_unidad_id'     => ['required','exists:unidades_medida,id'],
            'base_cantidad'        => ['required','numeric','min:0'],
            'base_unidad_id'       => ['required','exists:unidades_medida,id'],
        ]);

        // 👇 página desde el formulario (o 1 por defecto)
        $page = (int) $request->input('page', 1);

        $existe = AsignaComponente::where('producto_id', $data['producto_id'])
            ->where('nombre_cientifico_id', $data['nombre_cientifico_id'])
            ->exists();

        if ($existe) {
            return back()
                ->withInput()
                ->withErrors(['nombre_cientifico_id' => 'Este componente ya está asignado a ese producto.']);
        }

        AsignaComponente::create($data);

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Componente asignado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AsignaComponente $asignaComponente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $asignacion = AsignaComponente::with([
            'producto:id,nombre_comercial', // ← CAMBIADO
            'componente:id,nombre',
            'fuerzaUnidad:id,nombre',
            'baseUnidad:id,nombre'
        ])->findOrFail($id);

        $productos   = Producto::orderBy('nombre_comercial')->get(['id','nombre_comercial']); // ← CAMBIADO
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        return view('asigna_componentes.edit', [
            'row' => $asignacion,
            'productos' => $productos,
            'componentes' => $componentes,
            'unidades' => $unidades,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $asignacion = AsignaComponente::findOrFail($id);

        $data = $request->validate([
            'producto_id'          => ['required','exists:productos,id'],
            'nombre_cientifico_id' => ['required','exists:nombres_cientificos,id'],
            'fuerza_cantidad'      => ['required','numeric','min:0'],
            'fuerza_unidad_id'     => ['required','exists:unidades_medida,id'],
            'base_cantidad'        => ['required','numeric','min:0'],
            'base_unidad_id'       => ['required','exists:unidades_medida,id'],
        ]);

        // 👇 página desde el formulario (o 1 por defecto)
        $page = (int) $request->input('page', 1);

        $existe = AsignaComponente::where('producto_id', $data['producto_id'])
            ->where('nombre_cientifico_id', $data['nombre_cientifico_id'])
            ->where('id', '<>', $asignacion->id)
            ->exists();

        if ($existe) {
            return back()
                ->withInput()
                ->withErrors(['nombre_cientifico_id' => 'Este componente ya está asignado a ese producto.']);
        }

        $asignacion->update($data);

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Asignación actualizada correctamente.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $asignacion = AsignaComponente::findOrFail($id);

        // 👇 página desde el formulario (o 1 por defecto)
        $page = (int) $request->input('page', 1);

        $asignacion->delete();

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Asignación eliminada.');
    }
}
