<?php

namespace App\Http\Controllers;

use App\Models\FormaFarmaceutica;
use Illuminate\Http\Request;

class FormaFarmaceuticaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $q = trim($request->input('q', ''));
        $formaFarmaceuticas=FormaFarmaceutica::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->appends(['q' => $q]);

        return view('formaFarmaceutica.index',compact('formaFarmaceuticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('formaFarmaceutica.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nombre'=>'required|string|max:200|unique:formas_farmaceuticas,nombre',
        ]);

        FormaFarmaceutica::create([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('formaFarmaceutica.index')->with('success','Forma Farmaceutica agregada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(FormaFarmaceutica $formaFarmaceutica)
    {
        //
        return view('formaFarmaceutica.show',compact('formaFarmaceutica'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_formaFarmaceutica)
    {
        //
        $formaFarmaceutica = FormaFarmaceutica::findOrFail($id_formaFarmaceutica);
        return view('formaFarmaceutica.edit',compact('formaFarmaceutica'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_formaFarmaceutica)
    {
        //
        $request->validate([
            'nombre'=>'required|string|max:200|unique:formas_farmaceuticas,nombre,'.$id_formaFarmaceutica,
        ]);

        $formaFarmaceutica = FormaFarmaceutica::findOrFail($id_formaFarmaceutica);

        $formaFarmaceutica->update([
            'nombre'=>$request->nombre
        ]);

        return redirect()->route('formaFarmaceutica.index')->with('success','Forma Farmaceutica actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_formaFarmaceutica)
    {
        //
        $formaFarmaceutica = FormaFarmaceutica::findOrFail($id_formaFarmaceutica);
        $formaFarmaceutica->delete();
        return redirect()->route('formaFarmaceutica.index')->with('success','Forma Farmaceutica eliminada correctamente');
    }
}
