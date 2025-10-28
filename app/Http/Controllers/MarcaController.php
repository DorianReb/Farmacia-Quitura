<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        $marcas = Marca::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->appends(['q' => $q]);

        return view('marca.index', compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('marca.create');
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
                Rule::unique('marcas','nombre')->where(fn($q) => $q->whereNull('deleted_at')),
            ],
        ]);

        Marca::create([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('marca.index')->with('success', 'Marca agregada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        //
        return view('marca.show', compact('marca'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_marca)
    {
        //
        $marca = Marca::findOrFail($id_marca);
        return view('marca.edit', compact('marca'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_marca)
    {
        //
        $request->validate([
            'nombre' => [
                'required','string','max:200',
                Rule::unique('marcas','nombre')
                    ->ignore($id_marca)
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
        ]);

        $marca = Marca::findOrFail($id_marca);

        $marca->update([
            'nombre'=>$request->nombre
        ]);


        return redirect()->route('marca.index')->with('success', 'Marca actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_marca)
    {
        //
        $marca = Marca::findOrFail($id_marca);
        $marca->delete();
        return redirect()->route('marca.index')->with('success', 'Marca eliminada correctamente');

    }

    public function eliminados(Request $request)
    {
        $q = trim($request->input('q', ''));

        $marcas = Marca::onlyTrashed()
            ->when($q !== '', fn($b) => $b->where('nombre', 'like', "%{$q}%"))
            ->orderByDesc('deleted_at')
            ->paginate(25)
            ->appends(['q' => $q]);

        return view('marca.eliminados', compact('marcas'));
    }

    /** Restaurar una marca eliminada */
    public function restaurar($id)
    {
        $marca = Marca::onlyTrashed()->findOrFail($id);

        // Evitar choque de nombre al restaurar si ya existe una activa con el mismo nombre
        $existeActiva = Marca::where('nombre', $marca->nombre)->whereNull('deleted_at')->exists();
        if ($existeActiva) {
            return back()->with('error', 'No se puede restaurar: ya existe una marca activa con ese nombre.');
        }

        $marca->restore();

        return redirect()->route('marca.eliminados')
            ->with('success', 'Marca restaurada correctamente');
    }

    /** Eliminar permanentemente */
    public function forzarEliminacion($id)
    {
        $marca = Marca::onlyTrashed()->findOrFail($id);
        $marca->forceDelete();

        return redirect()->route('marca.eliminados')
            ->with('success', 'Marca eliminada permanentemente');
    }

}
