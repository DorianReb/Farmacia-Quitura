<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\AsignaPromocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromocionController extends Controller
{
    public function __construct()
    {
        // Solo Admin/Superadmin pueden crear/editar/eliminar
        $this->middleware('role:Administrador,Superadmin')->except(['index']);
    }

    public function index(Request $request)
    {
        // Búsqueda simple por porcentaje; si quieres por nombre del autorizador,
        // crea la relación y haz whereHas sobre usuarios
        $query = Promocion::query();

        if ($request->q) {
            $q = $request->q;
            $query->where('porcentaje', 'like', "%{$q}%");
            // ->orWhereHas('usuario', fn($qq) => $qq->where('nombre_completo','like',"%{$q}%"));
        }

        $promociones     = $query->orderBy('fecha_inicio', 'desc')->paginate(10);
        $asignaciones    = AsignaPromocion::with(['promocion', 'lote.producto'])->paginate(10);
        $lotes           = \App\Models\Lote::with('producto')->get();
        $promociones_all = Promocion::all();

        return view('promocion.index', compact('promociones','asignaciones','lotes','promociones_all'));
    }

    public function store(Request $request)
    {
        // (Opcional) seguridad por rol adicional
        if (!in_array(Auth::user()->rol ?? null, ['Administrador','Superadmin'])) {
            abort(403);
        }

        $data = $request->validate([
            'porcentaje'    => ['required','numeric','between:10,40'],
            'fecha_inicio'  => ['required','date'],
            'fecha_fin'     => ['required','date','after:fecha_inicio'], // o after_or_equal
            // 'autorizada_por' NO se toma del request
        ]);

        // Forzar el autorizador desde la sesión
        $data['autorizada_por'] = Auth::id();

        Promocion::create($data);

        return redirect()
            ->route('promocion.index')
            ->with('success', 'Promoción creada correctamente')
            ->with('from_modal', 'create_promocion');
    }

    public function edit(Promocion $promocion)
    {
        return redirect()->route('promocion.index')
            ->withInput()
            ->with('from_modal', 'edit_promocion')
            ->with('edit_id', $promocion->id);
    }

    public function update(Request $request, Promocion $promocion)
    {
        if (!in_array(Auth::user()->rol ?? null, ['Administrador','Superadmin'])) {
            abort(403);
        }

        $data = $request->validate([
            'porcentaje'    => ['required','numeric','between:10,40'],
            'fecha_inicio'  => ['required','date'],
            'fecha_fin'     => ['required','date','after:fecha_inicio'],
            // No aceptamos 'autorizada_por' del cliente
        ]);

        // Decisión de negocio:
        // a) Mantener el autorizador original, o
        // b) Registrar al usuario que hizo la última modificación:
        $data['autorizada_por'] = Auth::id(); // opción (b)

        $promocion->update($data);

        return redirect()->route('promocion.index')
            ->with('success', 'Promoción actualizada correctamente');
    }

    public function destroy(Promocion $promocion)
    {
        if (!in_array(Auth::user()->rol ?? null, ['Administrador','Superadmin'])) {
            abort(403);
        }

        $promocion->delete();

        return redirect()->route('promocion.index')
            ->with('success', 'Promoción eliminada correctamente');
    }
}
