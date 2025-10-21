<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        //
        $q = trim($request->input('q', ''));

        $unidad = UnidadMedida::query()
            ->when($q !== '', fn($query) =>
            $query->where('nombre', 'like', "%{$q}%")
            )
            ->orderBy('nombre')
            ->paginate(25);
        return view('unidad_medida.index', compact('unidad'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('unidad_medida.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nombre' => 'required|string|max:200|unique:unidades_medida,nombre',
        ]);

        UnidadMedida::create([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('unidad_medida.index')->with('success', 'Unidad de medida agregada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnidadMedida $unidadMedida)
    {
        //
        return view('unidad_medida.show', compact('unidadMedida'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_unidadMedida)
    {
        //
        $unidad = UnidadMedida::findOrFail($id_unidadMedida);
        return view('unidad_medida.edit', compact('unidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_unidadMedida)
    {
        //
        $request->validate([
            'nombre'=>'required|string|max:200|unique:unidades_medida,nombre,'.$id_unidadMedida,
        ]);

        $unidad = UnidadMedida::findOrFail($id_unidadMedida);

        $unidad->update([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('unidad_medida.index')->with('success', 'Unidad de medida actualizada correctamente');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_unidadMedida)
    {
        //
        $unidad = UnidadMedida::findOrFail($id_unidadMedida);
        $unidad->delete();
        return redirect()->route('unidad_medida.index')->with('success','Unidad de medida eliminada correctamente');
    }
}
