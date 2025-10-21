<?php

namespace App\Http\Controllers;

use App\Models\Pasillo;
use App\Models\Presentacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresentacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        $presentaciones = Presentacion::query()
            ->when($q !== '', fn($query) =>
            $query->where('nombre', 'like', "%{$q}%")
            )
            ->orderBy('nombre')
            ->paginate(25); // paginado
        // ->withQueryString(); // opcional, ya lo hacemos en la vista

        return view('presentacion.index', compact('presentaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('presentacion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nombre' => [
                'required','string','max:200',
                Rule::unique('presentaciones','nombre')->whereNull('deleted_at') // ← ignora soft-deleted
            ],
        ]);

        Presentacion::create([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('presentacion.index')->with('success', 'Presentación agregada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Presentacion $presentacion)
    {
        //
        return view('presentacion.show', compact('presentacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_presentacion)
    {
        //
        $presentacion = Presentacion::findOrFail($id_presentacion);
        return view('presentacion.edit', compact('presentacion'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_presentacion)
    {
        //
        $request->validate([
            'nombre' => [
                'required','string','max:200',
                Rule::unique('presentaciones','nombre')
                    ->ignore($id_presentacion)                  // ← ignora la actual
                    ->whereNull('deleted_at'),     // ← ignora soft-deleted
            ],
        ]);

        $presentacion = Presentacion::findOrFail($id_presentacion);

        $presentacion->update([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('presentacion.index')->with('success', 'Presentación actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_presentacion)
    {
        //
        $presentacion = Presentacion::findOrFail($id_presentacion);
        $presentacion->delete();
        return redirect()->route('presentacion.index')->with('success', 'Presentación eliminada correctamente');
    }

    /**
     * Mostrar registros eliminados
     */
    public function eliminados()
    {
        $presentaciones = Presentacion::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(25);

        return view('presentacion.eliminados', compact('presentaciones'));
    }

    /**
     * Restaurar un registro eliminado
     */
    public function restaurar($id_presentacion)
    {
        $presentacion = Presentacion::onlyTrashed()->findOrFail($id_presentacion);
        $presentacion->restore();

        return redirect()->route('presentacion.eliminados')
            ->with('success', 'Presentación restaurada correctamente');
    }

    /**
     * Eliminar permanentemente
     */
    public function forzarEliminacion($id_presentacion)
    {
        $presentacion = Presentacion::onlyTrashed()->findOrFail($id_presentacion);
        $presentacion->forceDelete();

        return redirect()->route('presentacion.eliminados')
            ->with('success', 'Presentación eliminada permanentemente');
    }
}
