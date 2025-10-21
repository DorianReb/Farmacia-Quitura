<?php

namespace App\Http\Controllers;

use App\Models\Pasillo;
use Illuminate\Http\Request;

class PasilloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $pasillos = Pasillo::all();
        return view('pasillo.index', compact('pasillos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('pasillo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'codigo' => 'required|string|min:3|max:50|unique:pasillos,codigo',
        ]);

        Pasillo::create([
            'codigo'=>$request->codigo
        ]);

        return redirect()->route('pasillo.index')->with('success','Pasillo creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pasillo $pasillo)
    {
        //
        return view('pasillo.show', compact('pasillo'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_pasillo)
    {
        //
        $pasillo = Pasillo::findOrFail($id_pasillo);
        return view('pasillo.edit', compact('pasillo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pasillo $pasillo)
    {
        //
        $request->validate([
            'codigo' => 'required|string|min:3|max:50|unique:pasillos,codigo',
        ]);

        return redirect()->route('pasillo.index')->with('success','Pasillo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_pasillo)
    {
        //
        $pasillo = Pasillo::findOrFail($id_pasillo);
        $pasillo->delete();
        return redirect()->route('pasillo.index')->with('success','Pasillo eliminado correctamente');
    }
}
