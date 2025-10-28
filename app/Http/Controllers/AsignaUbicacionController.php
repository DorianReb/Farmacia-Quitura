<?php

namespace App\Http\Controllers;

use App\Models\AsignaUbicacion;
use App\Models\Producto;
use App\Models\Nivel;
use App\Models\Pasillo; // ⬅️ AGREGADO
use Illuminate\Http\Request;

class AsignaUbicacionController extends Controller
{
    /**
     * Mostrar listado de asignaciones
     */
    public function index(Request $request)
    {
        $query = AsignaUbicacion::with(['producto', 'nivel']);

        // Buscador por producto o nivel
        if ($request->q) {
            $query->whereHas('producto', fn($q) => $q->where('nombre_comercial', 'like', "%{$request->q}%"))
                  ->orWhereHas('nivel', fn($q) => $q->where('nombre', 'like', "%{$request->q}%"));
        }

        $ubicaciones = $query->paginate(15);

        // Colecciones necesarias para los SELECTS y las mini-tablas
        $productos = Producto::all();
        $niveles = Nivel::all();
        $pasillos = Pasillo::all(); // ⬅️ AGREGADO

        return view('ubicacion.index', compact('ubicaciones', 'productos', 'niveles', 'pasillos')); // ⬅️ PASANDO PASILLOS
    }

    /**
     * Guardar nueva asignación
     */
    public function store(Request $request)
    {
        // El resto de la lógica de store es correcta.
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
        // El resto de la lógica de update es correcta.
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
        // El resto de la lógica de destroy es correcta.
        $asignaUbicacion->delete();

        return redirect()->route('ubicacion.index')->with('success', 'Asignación eliminada correctamente.');
    }
}
