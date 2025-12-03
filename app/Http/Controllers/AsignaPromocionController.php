<?php

namespace App\Http\Controllers;

use App\Models\AsignaPromocion;
use App\Models\Promocion;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class AsignaPromocionController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today();

        // ===== PROMOCIONES PARA LA TABLA (TODAS, PAGINADAS) =====
        $promociones = Promocion::with('usuario')
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        // ===== PROMOCIONES VIGENTES SOLO PARA LOS MODALES =====
        // fecha_inicio <= hoy <= fecha_fin
        $promociones_all = Promocion::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->orderBy('fecha_fin', 'asc')     // se muestran primero las que vencen antes
            ->orderBy('porcentaje', 'asc')
            ->get();

        // ===== ASIGNACIONES EXISTENTES =====
        $asignaciones = AsignaPromocion::with(['promocion', 'lote.producto'])
            ->orderBy('created_at', 'desc') //MÁS RECIENTES PRIMERO
            ->paginate(10);

        // ===== LOTES (vence entre hoy y 90 días) =====
        $limite = $hoy->copy()->addDays(90);

        $lotes = Lote::with(['producto.formaFarmaceutica'])
            ->whereBetween('fecha_caducidad', [$hoy, $limite])
            ->orderBy('fecha_caducidad')
            ->get();

        // Resumen de producto para los lotes
        foreach ($lotes as $lote) {
            if (!$lote->producto) continue;

            $p = $lote->producto;
            $nombre      = trim($p->nombre_comercial ?? '');
            $descripcion = trim($p->descripcion ?? '');
            $contenido   = trim($p->contenido ?? '');
            $forma       = trim($p->formaFarmaceutica->nombre ?? '');

            $partes = array_filter([$nombre, $descripcion ?: null, $contenido ?: null, $forma ?: null]);
            $p->resumen = implode(' ', $partes);
        }

        return view('promocion.index', compact(
            'asignaciones',
            'promociones',      // tabla principal
            'promociones_all',  // SOLO modales (vigentes)
            'lotes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'promocion_id' => 'required|exists:promociones,id',
            'lote_id'      => 'required|exists:lotes,id',
        ]);

        try {
            AsignaPromocion::create([
                'promocion_id' => $request->promocion_id,
                'lote_id'      => $request->lote_id,
            ]);

            return redirect()
                ->route('promocion.index')
                ->with('success', 'Asignación creada correctamente');
        } catch (QueryException $e) {
            $mensaje = $e->errorInfo[2] ?? 'Error al asignar la promoción.';

            return back()
                ->withErrors(['asigna_promocion' => $mensaje])
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'promocion_id' => 'required|exists:promociones,id',
            'lote_id'      => 'required|exists:lotes,id',
        ]);

        $asigna = AsignaPromocion::findOrFail($id);

        try {
            $asigna->update([
                'promocion_id' => $request->promocion_id,
                'lote_id'      => $request->lote_id,
            ]);

            return redirect()
                ->route('promocion.index')
                ->with('success', 'Asignación actualizada correctamente');
        } catch (QueryException $e) {
            $mensaje = $e->errorInfo[2] ?? 'Error al actualizar la asignación.';

            return back()
                ->withErrors(['asigna_promocion' => $mensaje])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $asigna = AsignaPromocion::findOrFail($id);
        $asigna->delete();

        return redirect()
            ->route('promocion.index')
            ->with('success', 'Asignación eliminada correctamente');
    }
}
