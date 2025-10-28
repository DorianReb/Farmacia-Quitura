<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index(Request $request)
    {
        $query = Nivel::with('pasillo'); // si quieres mostrar pasillo relacionado

        if ($request->q) {
            $query->where('numero', 'like', "%{$request->q}%")
                  ->orWhereHas('pasillo', fn($q) => $q->where('codigo', 'like', "%{$request->q}%"));
        }

        $niveles = $query->orderBy('numero')->paginate(10); // paginación de 10 por página

        return view('nivel.index', compact('niveles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pasillo_id' => 'required|exists:pasillos,id',
            'numero' => 'required|integer|min:1',
        ]);

        $nivelEliminado = Nivel::withTrashed()
            ->where('pasillo_id', $request->pasillo_id)
            ->where('numero', $request->numero)
            ->first();

        if ($nivelEliminado) {
            $nivelEliminado->restore();
            return redirect()->route('ubicacion.index')->with('success', 'Nivel restaurado correctamente.');
        }

        Nivel::create($request->only('pasillo_id','numero'));

        return redirect()->route('ubicacion.index')->with('success', 'Nivel creado correctamente.');
    }

    public function update(Request $request, Nivel $nivel)
    {
        $request->validate([
            'pasillo_id' => 'required|exists:pasillos,id',
            'numero' => 'required|integer|min:1',
        ]);

        $duplicado = Nivel::withTrashed()
            ->where('pasillo_id', $request->pasillo_id)
            ->where('numero', $request->numero)
            ->where('id', '!=', $nivel->id)
            ->first();

        if ($duplicado) {
            return redirect()->back()->withErrors(['numero' => 'Este nivel ya existe para este pasillo.']);
        }

        $nivel->update($request->only('pasillo_id','numero'));

        return redirect()->route('ubicacion.index')->with('success', 'Nivel actualizado correctamente.');
    }

    public function destroy(Nivel $nivel)
    {
        $nivel->delete();
        return redirect()->route('ubicacion.index')->with('success', 'Nivel eliminado correctamente.');
    }
}
