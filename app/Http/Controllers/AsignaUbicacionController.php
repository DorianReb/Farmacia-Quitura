<?php

namespace App\Http\Controllers;

use App\Models\AsignaUbicacion;
use App\Models\Producto;
use App\Models\Nivel;
use App\Models\Pasillo;
use Illuminate\Http\Request;

class AsignaUbicacionController extends Controller
{
    /**
     * Mostrar listado de asignaciones con paginación
     */
    public function index(Request $request)
    {
        // Paginación para pasillos
        $pasillos = Pasillo::orderBy('codigo')->paginate(10);

        // Paginación para niveles
        $niveles = Nivel::orderBy('numero')->paginate(10);

        // Paginación para asignaciones con búsqueda
        $query = AsignaUbicacion::with(['producto', 'nivel']);
        if ($request->q) {
            $query->whereHas('producto', fn($q) => $q->where('nombre_comercial', 'like', "%{$request->q}%"))
                  ->orWhereHas('nivel', fn($q) => $q->where('numero', 'like', "%{$request->q}%"));
        }
        $ubicaciones = $query->paginate(15);

        // Productos para los selects (no se paginan)
        $productos = Producto::all();

        return view('ubicacion.index', compact('pasillos', 'niveles', 'ubicaciones', 'productos'));
    }

    /**
     * Guardar nueva asignación
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'nivel_id' => 'required|exists:niveles,id',
        ]);

        AsignaUbicacion::create($request->only('producto_id', 'nivel_id'));

        return redirect()->route('ubicacion.index')->with('success', 'Asignación creada correctamente.');
    }

    /**
     * Actualizar asignación
     */
    public function update(Request $request, AsignaUbicacion $asignaUbicacion)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'nivel_id' => 'required|exists:niveles,id',
        ]);

        $asignaUbicacion->update($request->only('producto_id', 'nivel_id'));

        return redirect()->route('ubicacion.index')->with('success', 'Asignación actualizada correctamente.');
    }

    /**
     * Eliminar asignación
     */
    public function destroy(AsignaUbicacion $asignaUbicacion)
    {
        $asignaUbicacion->delete();

        return redirect()->route('ubicacion.index')->with('success', 'Asignación eliminada correctamente.');
    }
}
