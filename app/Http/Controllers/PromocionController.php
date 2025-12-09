<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\AsignaComponente;
use App\Models\AsignaPromocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PromocionController extends Controller
{
    public function __construct()
    {
        // Solo Admin/Superadmin pueden crear/editar/eliminar
        $this->middleware('role:Administrador,Superadmin')->except(['index']);
    }

    public function index(Request $request)
    {
        // Búsquedas independientes
        $promoQ  = trim($request->input('promo_q'));
        $asignaQ = trim($request->input('asigna_q'));

        // ===== PROMOCIONES =====
        $query = Promocion::query()->with('usuario');

        if ($promoQ !== '') {
            $query->where(function ($q) use ($promoQ) {
                $q->where('porcentaje', 'like', "%{$promoQ}%")
                    ->orWhereHas('usuario', function ($qq) use ($promoQ) {
                        $qq->whereRaw(
                            "CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) LIKE ?",
                            ["%{$promoQ}%"]
                        );
                    });
            });
        }

        $promociones  = $query
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10)
            ->appends([
                'promo_q'  => $promoQ,
                'asigna_q' => $asignaQ,
            ]);

        // ===== ASIGNACIONES =====
        $asigQuery = AsignaPromocion::with(['promocion', 'lote.producto.formaFarmaceutica']);

        if ($asignaQ !== '') {
            $asigQuery->where(function ($q) use ($asignaQ) {
                // Filtrar por porcentaje de la promoción
                $q->whereHas('promocion', function ($qq) use ($asignaQ) {
                    $qq->where('porcentaje', 'like', "%{$asignaQ}%");
                })
                    // O por código de lote / nombre de producto
                    ->orWhereHas('lote', function ($qq) use ($asignaQ) {
                        $qq->where('codigo', 'like', "%{$asignaQ}%")
                            ->orWhereHas('producto', function ($pp) use ($asignaQ) {
                                $pp->where('nombre_comercial', 'like', "%{$asignaQ}%")
                                    ->orWhere('descripcion', 'like', "%{$asignaQ}%");
                            });
                    });
            });
        }

        $asignaciones = $asigQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends([
                'promo_q'  => $promoQ,
                'asigna_q' => $asignaQ,
            ]);

        // ===== LOTES PARA MODALES =====
        $hoy    = Carbon::today();
        $limite = $hoy->copy()->addDays(90);

        $lotes = \App\Models\Lote::with(['producto.formaFarmaceutica'])
            ->whereNotNull('fecha_caducidad')
            ->whereBetween('fecha_caducidad', [$hoy->toDateString(), $limite->toDateString()])
            ->orderBy('fecha_caducidad', 'asc')
            ->get();

        // ================== RESUMEN DE PRODUCTO PARA LOTES Y ASIGNACIONES ==================
        $productoIds = collect()
            ->merge($lotes->pluck('producto_id'))
            ->merge(
                $asignaciones->pluck('lote')
                    ->filter()
                    ->pluck('producto_id')
            )
            ->filter()
            ->unique();

        $asignacionesComponentes = $productoIds->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $productoIds)
                ->get()
                ->groupBy('producto_id');

        $buildResumen = function($producto) use ($asignacionesComponentes) {
            if (!$producto) return;

            $componentesTxt = '';
            if (isset($asignacionesComponentes[$producto->id])) {
                $componentesTxt = $asignacionesComponentes[$producto->id]
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
        };

        foreach ($lotes as $lote) {
            if ($lote->producto) {
                $buildResumen($lote->producto);
            }
        }

        foreach ($asignaciones as $asigna) {
            if ($asigna->lote && $asigna->lote->producto) {
                $buildResumen($asigna->lote->producto);
            }
        }
        // =======================================================================

        $promociones_all = Promocion::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->orderBy('fecha_fin', 'asc')
            ->orderBy('porcentaje', 'asc')
            ->get();

        return view('promocion.index', compact(
            'promociones',
            'asignaciones',
            'lotes',
            'promociones_all'
        ));
    }

    public function store(Request $request)
    {
        // (Opcional) seguridad por rol adicional
        if (!in_array(Auth::user()->rol ?? null, ['Administrador','Superadmin']))
        {
            abort(403);
        }

        $data = $request->validate([
            'porcentaje'    => ['required','numeric','between:10,40'],

            'fecha_inicio'  => ['required','date_format:d-m-Y'],

            'fecha_fin'     => [
                'required',
                'date_format:d-m-Y',
                function ($attribute, $value, $fail) use ($request) {
                    $inicio = Carbon::createFromFormat('d-m-Y', $request->fecha_inicio);
                    $fin    = Carbon::createFromFormat('d-m-Y', $value);

                    if ($fin->lt($inicio)) {
                        $fail('La fecha de fin debe ser mayor o igual a la fecha de inicio.');
                    }
                }
            ],
        ]);

        // Convertir a formato BD (Y-m-d)
        $data['fecha_inicio'] = Carbon::createFromFormat('d-m-Y', $data['fecha_inicio'])->format('Y-m-d');
        $data['fecha_fin']    = Carbon::createFromFormat('d-m-Y', $data['fecha_fin'])->format('Y-m-d');

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

            'fecha_inicio'  => ['required','date_format:d-m-Y'],

            'fecha_fin'     => [
                'required',
                'date_format:d-m-Y',
                function ($attribute, $value, $fail) use ($request) {
                    $inicio = Carbon::createFromFormat('d-m-Y', $request->fecha_inicio);
                    $fin    = Carbon::createFromFormat('d-m-Y', $value);

                    if ($fin->lt($inicio)) {
                        $fail('La fecha de fin debe ser mayor o igual a la fecha de inicio.');
                    }
                }
            ],
        ]);

        // Convertir a formato BD
        $data['fecha_inicio'] = Carbon::createFromFormat('d-m-Y', $data['fecha_inicio'])->format('Y-m-d');
        $data['fecha_fin']    = Carbon::createFromFormat('d-m-Y', $data['fecha_fin'])->format('Y-m-d');

        // Registrar quién modificó
        $data['autorizada_por'] = Auth::id();

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
