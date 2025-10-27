<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoteController extends Controller
{
    /**
     * Muestra la lista de lotes.
     */
    public function index(Request $request)
    {
        $query = Lote::query()->with('producto');
        $buscar = $request->input('q');

        if ($buscar) {
            $query->whereHas('producto', function($q) use ($buscar) {
                $q->where('nombre_comercial', 'LIKE', "%{$buscar}%");
            })->orWhere('codigo', 'LIKE', "%{$buscar}%");
        }

        $lotes = $query->orderByDesc('created_at')->paginate(10);
        $productos = \App\Models\Producto::orderBy('nombre_comercial')->get();

        return view('lote.index', compact('lotes', 'productos'));
    }


    /**
     * Guarda un nuevo lote.
     */
    public function store(Request $request)
    {
        $request->merge([
            'from_modal' => 'create_lote'
        ]);

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'codigo' => 'required|string|max:100|unique:lotes,codigo',
            'fecha_caducidad' => 'nullable|date',
            'precio_compra' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|integer|min:0',
            'fecha_entrada' => 'nullable|date',
        ]);

        $validated['usuario_id'] = Auth::id() ?? 1;

        Lote::create($validated);

        return redirect()->route('lote.index')
            ->with('success', 'Lote creado correctamente.');
    }

    /**
     * Actualiza un lote existente.
     */
    public function update(Request $request, Lote $lote)
    {
        $request->merge([
            'from_modal' => 'edit_lote',
            'edit_id' => $lote->id
        ]);

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'codigo' => 'required|string|max:100|unique:lotes,codigo,' . $lote->id,
            'fecha_caducidad' => 'nullable|date',
            'precio_compra' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|integer|min:0',
            'fecha_entrada' => 'nullable|date',
        ]);

        $lote->update($validated);

        return redirect()->route('lote.index')
            ->with('success', 'Lote actualizado correctamente.');
    }

    /**
     * Elimina un lote (soft delete).
     */
    public function destroy(Lote $lote)
    {
        $lote->delete();

        return redirect()->route('lote.index')
            ->with('success', 'Lote eliminado correctamente.');
    }
}
