<?php

namespace App\Http\Controllers;

use App\Models\Pasillo;
use Illuminate\Http\Request;

class PasilloController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasillo::query();

        if ($request->q) {
            $query->where('codigo', 'like', "%{$request->q}%");
        }

        $pasillos = $query->orderBy('codigo')->paginate(10);

        return view('pasillo.index', compact('pasillos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|min:3|max:50',
        ]);

        // Buscar pasillo eliminado con mismo código
        $pasilloEliminado = Pasillo::withTrashed()
            ->where('codigo', $request->codigo)
            ->first();

        if ($pasilloEliminado) {
            $pasilloEliminado->restore();
            return redirect()->route('ubicacion.index')->with('success', 'Pasillo restaurado correctamente.');
        }

        Pasillo::create([
            'codigo' => $request->codigo
        ]);

        return redirect()->route('ubicacion.index')->with('success', 'Pasillo creado correctamente.');
    }

    public function update(Request $request, Pasillo $pasillo)
    {
        $request->validate([
            'codigo' => 'required|string|min:3|max:50',
        ]);

        // Validar duplicado ignorando el actual
        $duplicado = Pasillo::withTrashed()
            ->where('codigo', $request->codigo)
            ->where('id', '!=', $pasillo->id)
            ->first();

        if ($duplicado) {
            return redirect()->back()->withErrors(['codigo' => 'Este código de pasillo ya existe.']);
        }

        $pasillo->update(['codigo' => $request->codigo]);

        return redirect()->route('ubicacion.index')->with('success', 'Pasillo actualizado correctamente.');
    }

    public function destroy(Pasillo $pasillo)
    {
        $pasillo->delete();
        return redirect()->route('ubicacion.index')->with('success', 'Pasillo eliminado correctamente.');
    }
}
