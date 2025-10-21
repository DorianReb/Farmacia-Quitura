<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        //
        $q = trim($request->input('q',''));
        $categorias = Categoria::query()
            ->when($q !== '', function($query) use ($q){
                $query->where('nombre','like',"%$q%");
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->appends(['q' => $q]);

        return view('categoria.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('categoria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nombre'=>'required|string|max:200|unique:categorias',
        ]);

        Categoria::create([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('categoria.index')->with('success','Categoría creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $categoria)
    {
        //
        return view('categoria.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $id_categoria)
    {
        //
        $categorias = Categoria::findOrFail($id_categoria);
        return view('categoria.edit', compact('categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_categoria)
    {
        //
        $request->validate([
            'nombre'=>'required|string|max:200|unique:categorias,nombre,'.$id_categoria
        ]);

        $categoria = Categoria::findOrFail($id_categoria);

        $categoria->update([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('categoria.index')->with('success','Categoría actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_categoria)
    {
        //
        $categoria=Categoria::findOrFail($id_categoria);
        $categoria->delete();
        return redirect()->route('categoria.index')->with('success','Categoría eliminada correctamente');
    }
}
