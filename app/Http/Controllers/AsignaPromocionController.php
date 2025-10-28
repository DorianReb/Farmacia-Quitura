<?php

namespace App\Http\Controllers;

use App\Models\AsignaPromocion;
use App\Models\Promocion;
use App\Models\Lote;
use Illuminate\Http\Request;

class AsignaPromocionController extends Controller
{
    /**
     * Mostrar listado de asignaciones.
     */
    public function index()
    {
        $asignaciones = AsignaPromocion::with(['promocion', 'lote.producto'])
            ->paginate(10);

        $promociones = Promocion::all();
        $lotes = Lote::with('producto')->get();

        return view('promocion.index', compact('asignaciones', 'promociones', 'lotes'));
    }

    /**
     * Guardar nueva asignación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'promocion_id' => 'required|exists:promociones,id',
            'lote_id' => 'required|exists:lotes,id',
        ]);

        AsignaPromocion::create([
            'promocion_id' => $request->promocion_id,
            'lote_id' => $request->lote_id,
        ]);

        return redirect()->route('promocion.index')
                         ->with('success', 'Asignación creada correctamente');
    }

    /**
     * Actualizar asignación existente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'promocion_id' => 'required|exists:promociones,id',
            'lote_id' => 'required|exists:lotes,id',
        ]);

        $asigna = AsignaPromocion::findOrFail($id);

        $asigna->update([
            'promocion_id' => $request->promocion_id,
            'lote_id' => $request->lote_id,
        ]);

        return redirect()->route('promocion.index')
                         ->with('success', 'Asignación actualizada correctamente');
    }

    /**
     * Eliminar asignación.
     */
    public function destroy($id)
    {
        $asigna = AsignaPromocion::findOrFail($id);
        $asigna->delete();

        return redirect()->route('promocion.index')
                         ->with('success', 'Asignación eliminada correctamente');
    }
}
