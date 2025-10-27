<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Creamos la consulta base
        $query = \App\Models\User::query();

        // Filtro de búsqueda opcional
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        // Paginación ordenada alfabéticamente
        $usuarios = $query->orderBy('nombre')->paginate(10);

        // Retornar vista con variable $usuarios
        return view('superadmin.usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Usuario $usuario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_usuario)
    {
        //
        $usuario =  User::findOrFail($id_usuario);
        return view('superadmin.usuarios.edit', compact('usuario'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        //
        $request->validate([
            'rol'=>'required|in:Administrador,Vendedor',
            'estado'=>'required|in:Activo,Pendiente,Rechazado',
        ]);

        $usuario->rol = $request->rol;
        $usuario->estado = $request->estado;
        $usuario->save();

        return redirect()
            ->route('superadmin.usuarios.index')
            ->with('success', 'Rol y estado del usuario actualizados correctamente.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_usuario)
    {
        //
        $usuario = User::find($id_usuario);
        $usuario->delete();
        return redirect()->route('superadmin.usuarios.index')->with('success', 'Usuario eliminado');

    }
}
