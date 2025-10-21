<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $promocion = Promocion::all();
        return view('promocion.index', compact('promociones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('promocion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id_promocion)
    {
        //
        $request->validate([
            'porcentaje' => 'required|numeric|min:10|max:40',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            //'autorizado_por' => 'required|numeric|min:0|max:1',
        ]);

        $promocion = Promocion::findOrFail($id_promocion);

        $promocion->update($request->all());

    }

    /**
     * Display the specified resource.
     */
    public function show(Promocion $promocion)
    {
        //
        return view('promocion.show', compact('promocion'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_promocion)
    {
        //
        $promocion = Promocion::findOrFail($id_promocion);
        return view('promocion.edit', compact('promocion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_promocion)
    {
        //
        $request->validate([
            'porcentaje' => 'required|numeric|min:10|max:40',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',

        ]);

        $promocion = Promocion::findOrFail($id_promocion);
        $promocion->update($request->all());

        return redirect()->route('promocion.index')->with('success', 'Promocion actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_promocion)
    {
        //
        $promocion = Promocion::findOrFail($id_promocion);
        $promocion->delete();
        return redirect()->route('promocion.index')->with('success', 'Promocion eliminada correctamente');
    }
}
