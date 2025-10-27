<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Promocion::query();

        if ($request->q) {
            $q = $request->q;
            $query->where('porcentaje', 'like', "%{$q}%")
                ->orWhere('autorizada_por', 'like', "%{$q}%");
        }

        $promociones = $query->orderBy('fecha_inicio', 'desc')->paginate(10);

        // Aquí agregamos todos los usuarios para los select de "autorizada por"
        $usuarios = \App\Models\User::all();

        return view('promocion.index', compact('promociones', 'usuarios'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'porcentaje' => 'required|numeric|min:10|max:40',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'autorizada_por' => 'required|string|max:200',
        ]);

        Promocion::create($request->all());

        return redirect()->route('promocion.index')
                         ->with('success', 'Promoción creada correctamente')
                         ->with('from_modal', 'create_promocion');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promocion $promocion)
    {
        return redirect()->route('promocion.index')
                         ->withInput()
                         ->with('from_modal', 'edit_promocion')
                         ->with('edit_id', $promocion->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promocion $promocion)
    {
        $request->validate([
            'porcentaje' => 'required|numeric|min:10|max:40',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'autorizada_por' => 'required|string|max:200',
        ]);

        $promocion->update($request->all());

        return redirect()->route('promocion.index')
                         ->with('success', 'Promoción actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promocion $promocion)
    {
        $promocion->delete();

        return redirect()->route('promocion.index')
                         ->with('success', 'Promoción eliminada correctamente');
    }
}
