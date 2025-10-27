<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        //
        $query = User::query();

        // 🔎 Filtro de búsqueda opcional
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        // 🕒 Ordenar de más reciente a más antiguo
        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('superadmin.solicitudes.index', compact('solicitudes'));
    }

    public function aprobar($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->estado = 'Activo';
        $usuario->save();

        return back()->with('success', 'Usuario aprobado y activado correctamente.');
    }

    public function rechazar($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->estado = 'Rechazado';
        $usuario->save();

        return back()->with('error', 'Solicitud rechazada correctamente.');
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $usuario = User::findOrFail($id);

        // 🔒 Solo se puede eliminar si NO está pendiente
        if ($usuario->estado === 'Pendiente') {
            return back()->with('error', 'No puedes eliminar una solicitud que aún está pendiente.');
        }

        $usuario->delete();

        return back()->with('success', 'Solicitud eliminada correctamente.');
    }
}
