<?php

namespace App\Http\Controllers;

use App\Models\NombreCientifico;
use Illuminate\Http\Request;

class NombreCientificoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        $nombresCientificos = NombreCientifico::query()
            ->when($q !== '', fn ($query) =>
            $query->where('nombre', 'like', "%{$q}%")
            )
            ->orderBy('nombre')
            ->paginate(25); // <- paginador

        return view('nombreCientifico.index', compact('nombresCientificos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('nombreCientifico.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
           'nombre' => 'required|string|max:200|unique:nombres_cientificos,nombre',
        ]);

        NombreCientifico::create([
           'nombre'=>$request->nombre
        ]);

        return redirect()->route('nombreCientifico.index')->with('success', 'Nombre Cientifico agregado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(NombreCientifico $nombreCientifico)
    {
        //
        return view('nombreCientifico.show', compact('nombreCientifico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_nombreCientifico)
    {
        //
        $nombreCientifico = NombreCientifico::findOrFail($id_nombreCientifico);
        return view('nombreCientifico.edit', compact('nombreCientifico'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_nombreCientifico)
    {
        //
        $request->validate([
            'nombre' => 'required|string|max:200|unique:nombres_cientificos,nombre,' . $id_nombreCientifico,
        ]);


        $nombreCientifico = NombreCientifico::findOrFail($id_nombreCientifico);

        $nombreCientifico->update([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('nombreCientifico.index')->with('success', 'Nombre Cientifico editado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_nombreCientifico)
    {
        //
        $nombreCientifico = NombreCientifico::findOrFail($id_nombreCientifico);
        $nombreCientifico->delete();
        return redirect()->route('nombreCientifico.index')->with('success', 'Nombre Cientifico eliminado correctamente');

    }
}
