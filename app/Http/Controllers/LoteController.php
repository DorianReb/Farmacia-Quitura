<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\AsignaPromocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\AsignaComponente;
use Carbon\Carbon;

class LoteController extends Controller
{
    /**
     * Muestra la lista de lotes.
     */
    public function index(Request $request)
    {
        $buscar = trim($request->input('q'));

        // Consulta base de lotes con joins para poder buscar por producto y usuario
        $query = Lote::query()
            ->with(['producto.formaFarmaceutica', 'usuario']) // 👈 asegúrate de tener formaFarmaceutica
            ->leftJoin('productos as prod', 'prod.id', '=', 'lotes.producto_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'lotes.usuario_id')
            ->select('lotes.*');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('lotes.codigo', 'LIKE', "%{$buscar}%")
                    ->orWhere('prod.nombre_comercial', 'LIKE', "%{$buscar}%")
                    ->orWhere('lotes.fecha_caducidad', 'LIKE', "%{$buscar}%")
                    ->orWhere('lotes.cantidad', 'LIKE', "%{$buscar}%")
                    ->orWhere('lotes.precio_compra', 'LIKE', "%{$buscar}%")
                    ->orWhere('lotes.fecha_entrada', 'LIKE', "%{$buscar}%")
                    ->orWhereRaw("
                    CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno)
                    LIKE ?
                ", ["%{$buscar}%"]);
            });
        }

        // Ordenar por lote más reciente
        $lotes = $query
            ->orderByDesc('lotes.created_at')
            ->paginate(10)
            ->withQueryString();

        // ================== RESUMEN DE PRODUCTO (MISMA LÓGICA QUE EN ProductoController) ==================
        $productoIdsPagina = $lotes->pluck('producto_id')->filter()->unique();

        $asignaciones = $productoIdsPagina->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $productoIdsPagina)
                ->get()
                ->groupBy('producto_id');

        foreach ($lotes as $lote) {
            $producto = $lote->producto;
            if (!$producto) {
                continue;
            }

            // Componentes tipo: "Paracetamol 500 mg / 1 tableta, Cafeína 65 mg / 1 tableta"
            $componentesTxt = '';
            if (isset($asignaciones[$producto->id])) {
                $componentesTxt = $asignaciones[$producto->id]
                    ->map(function ($a) {
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                        $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                        $fu     = $a->fuerzaUnidad->nombre ?? '';
                        $bu     = $a->baseUnidad->nombre ?? '';
                        $comp   = $a->componente->nombre ?? '';
                        return trim($comp.' '.trim($fuerza.' '.($fu ?: '')).' / '.trim($base.' '.($bu ?: '')));
                    })
                    ->implode(', ');

                $componentesTxt = $componentesTxt ? " {$componentesTxt}" : '';
            }

            $nombre      = trim($producto->nombre_comercial ?? '');
            $descripcion = trim($producto->descripcion ?? '');
            $contenido   = trim($producto->contenido ?? '');
            $forma       = trim($producto->formaFarmaceutica->nombre ?? '');

            $partes = array_filter([
                $nombre,
                $descripcion ?: null,
                $contenido   ?: null,
                $forma       ?: null,
            ]);

            $producto->resumen = trim(implode(' ', $partes).$componentesTxt);
        }
        // ==================================================================================================

        // Productos para los modales
        $productos = Producto::orderBy('nombre_comercial')->get();

        // PROMOCIONES POR LOTE
        $promosPorLote = AsignaPromocion::with('promocion')
            ->whereIn('lote_id', $lotes->pluck('id'))
            ->get()
            ->groupBy('lote_id');

        return view('lote.index', compact('lotes', 'productos', 'promosPorLote'));
    }



    /**
     * Guarda un nuevo lote.
     */
    public function store(Request $request)
    {
        // Para saber desde qué modal viene
        $request->merge(['from_modal' => 'create_lote']);

        $validated = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'codigo'          => 'required|string|max:100',
            'fecha_caducidad' => [
                'required',
                'date_format:d-m-Y',
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::createFromFormat('d-m-Y', $value);
                    if ($fecha->isPast()) {
                        $fail('La fecha de caducidad no puede estar en el pasado.');
                    }
                }
            ],
            'precio_compra'   => 'required|numeric|min:0.01',
            'cantidad'        => 'required|integer|min:1',
        ]);

        $fecha_caducidad = Carbon::createFromFormat('d-m-Y', $validated['fecha_caducidad'])
            ->format('Y-m-d');

        $usuarioId = Auth::id() ?? 1;

        try {
            DB::statement("CALL registrar_lote(?, ?, ?, ?, ?, ?)", [
                $validated['producto_id'],
                $validated['codigo'],
                $fecha_caducidad,
                $validated['precio_compra'],
                $validated['cantidad'],
                $usuarioId,
            ]);

            return redirect()->route('lote.index')
                ->with('success', 'Lote registrado correctamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('lote.index')
                ->withErrors(['procedimiento' => $e->getMessage()])
                ->withInput()
                ->with('from_modal', 'create_lote');
        }
    }

    /**
     * Actualiza un lote existente.
     */
    public function update(Request $request, $id)
    {
        $lote = Lote::findOrFail($id);

        $validated = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'codigo'          => 'required|string|max:100|unique:lotes,codigo,' . $lote->id,
            'fecha_caducidad' => [
                'required',
                'date_format:d-m-Y',
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::createFromFormat('d-m-Y', $value);
                    if ($fecha->isPast()) {
                        $fail('La fecha de caducidad no puede estar en el pasado.');
                    }
                }
            ],
            'precio_compra'   => 'required|numeric|min:0.01',
            'cantidad'        => 'required|integer|min:1',
        ]);

        $validated['fecha_caducidad'] = Carbon::createFromFormat('d-m-Y', $validated['fecha_caducidad'])
            ->format('Y-m-d');

        $lote->update($validated);

        return redirect()->route('lote.index')
            ->with('success', 'Lote actualizado correctamente.');
    }

    public function destroy($id)
    {
        $lote = Lote::findOrFail($id);
        $lote->delete();

        return redirect()->route('lote.index')
            ->with('success', 'Lote eliminado correctamente.');
    }
}
