<?php

namespace App\Http\Controllers;

use App\Models\AsignaComponente;
use App\Models\Producto;
use App\Models\NombreCientifico;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignaComponenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        $asignaciones = AsignaComponente::query()
            ->with([
                'producto:id,nombre_comercial',
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
            ->leftJoin('productos', 'asigna_componentes.producto_id', '=', 'productos.id')
            ->leftJoin('nombres_cientificos', 'asigna_componentes.nombre_cientifico_id', '=', 'nombres_cientificos.id')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('productos.nombre_comercial', 'like', "%{$q}%")
                        ->orWhere('nombres_cientificos.nombre', 'like', "%{$q}%");
                });
            })
            ->orderBy('productos.nombre_comercial')
            ->orderBy('nombres_cientificos.nombre')
            ->select('asigna_componentes.*')
            ->paginate(25)
            ->withQueryString();

        // IDs de producto presentes en ESTA página
        $productoIds = $asignaciones->getCollection()->pluck('producto_id')->unique()->values();

        // Mapa: producto_id => ['total' => N, 'nombres' => [...]]
        $metaPorProducto = AsignaComponente::with(['componente:id,nombre'])
            ->whereIn('producto_id', $productoIds)
            ->get()
            ->groupBy('producto_id')
            ->map(function ($rows) {
                $nombres = $rows->pluck('componente.nombre')->filter()->unique()->sort()->values();
                return [
                    'total'   => $nombres->count(),
                    'nombres' => $nombres->all(),
                ];
            });

        // Para los modales
        $productos   = Producto::orderBy('nombre_comercial')->get(['id','nombre_comercial']);
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        return view('asigna_componentes.index', compact(
            'asignaciones','q','productos','componentes','unidades','metaPorProducto'
        ));
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

        $existe = AsignaComponente::where('producto_id', $data['producto_id'])
            ->where('nombre_cientifico_id', $data['nombre_cientifico_id'])
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['nombre_cientifico_id' => 'Este componente ya está asignado a ese producto.']);
        }

        AsignaComponente::create($data);

        return redirect()->route('asigna_componentes.index')
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

        return view('asigna_componentes.edit', compact('asignacion','productos','componentes','unidades'));
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

        $existe = AsignaComponente::where('producto_id', $data['producto_id'])
            ->where('nombre_cientifico_id', $data['nombre_cientifico_id'])
            ->where('id', '<>', $asignacion->id)
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['nombre_cientifico_id' => 'Este componente ya está asignado a ese producto.']);
        }

        $asignacion->update($data);

        return redirect()->route('asigna_componentes.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $asignacion = AsignaComponente::findOrFail($id);
        $asignacion->delete();

        return redirect()->route('asigna_componentes.index')
            ->with('success', 'Asignación eliminada.');
    }
}
