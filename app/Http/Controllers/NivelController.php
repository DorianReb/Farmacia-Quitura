<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pasillo_id' => 'required|exists:pasillos,id',
            'numero' => 'required|integer|min:1',
        ]);

        // Buscar si ya existe un nivel eliminado con el mismo pasillo y número
        $nivelEliminado = Nivel::withTrashed()
            ->where('pasillo_id', $request->pasillo_id)
            ->where('numero', $request->numero)
            ->first();

        if ($nivelEliminado) {
            $nivelEliminado->restore();
            return redirect()->route('ubicacion.index')->with('success', 'Nivel restaurado correctamente.');
        }

        // Si no existe, crear uno nuevo
        Nivel::create([
            'pasillo_id' => $request->pasillo_id,
            'numero' => $request->numero,
        ]);

        return redirect()->route('ubicacion.index')->with('success', 'Nivel creado correctamente.');
    }

    public function update(Request $request, Nivel $nivel)
    {
        $request->validate([
            'pasillo_id' => 'required|exists:pasillos,id',
            'numero' => 'required|integer|min:1',
        ]);

        // Validar duplicados (ignorando el registro actual)
        $duplicado = Nivel::withTrashed()
            ->where('pasillo_id', $request->pasillo_id)
            ->where('numero', $request->numero)
            ->where('id', '!=', $nivel->id)
            ->first();

        if ($duplicado) {
            return redirect()->back()->withErrors(['numero' => 'Este nivel ya existe para este pasillo.']);
        }

        $nivel->update([
            'pasillo_id' => $request->pasillo_id,
            'numero' => $request->numero,
        ]);

        return redirect()->route('ubicacion.index')->with('success', 'Nivel actualizado correctamente.');
    }

    public function destroy(Nivel $nivel)
    {
        $nivel->delete();
        return redirect()->route('ubicacion.index')->with('success', 'Nivel eliminado correctamente.');
    }
}
