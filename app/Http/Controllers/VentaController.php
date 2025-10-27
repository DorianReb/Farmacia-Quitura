<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $productoEncontrado = null;
        $itemsEnVenta = [];
        $totalVenta = 0;

        if ($request->q) {
            $productoEncontrado = Producto::with(['lotes', 'marca', 'formaFarmaceutica', 'presentacion', 'categoria'])
                ->where('codigo_barras', $request->q)
                ->first();

            if ($productoEncontrado) {
                $productoEncontrado->existencias_calculadas = $productoEncontrado->lotes->sum('cantidad');

                // Asignar información de lote FEFO si existe
                $productoEncontrado->lotes = $productoEncontrado->lotes->sortBy('fecha_caducidad')->values();
            }
        }

        return view('venta.index', compact('productoEncontrado', 'itemsEnVenta', 'totalVenta'));
    }

    public function buscarProductoPorCodigo($codigo)
    {
        $producto = Producto::with([
            'lotes',
            'marca',
            'presentacion',
            'formaFarmaceutica',
            'categoria',
            'asignaUbicaciones.nivel.pasillo',
            'asignaComponentes.nombreCientifico'
        ])
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Calcular existencias
        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');

        // Ordenar lotes por fecha de caducidad (FEFO)
        $producto->lotes = $producto->lotes->sortBy('fecha_caducidad')->values();

        // Asegurar estructura del JSON
        $producto->asigna_ubicaciones = $producto->asignaUbicaciones ?? collect();
        $producto->asigna_componentes = $producto->asignaComponentes ?? collect();
        $producto->forma_farmaceutica = $producto->formaFarmaceutica ?? null;
        //Extraer nombre científico desde la primera asignación si existe
        $primerComponente = $producto->asigna_componentes->first();
        $producto->nombre_cientifico = $primerComponente?->nombreCientifico?->nombre ?? null;

        //  Extraer ubicación (pasillo/nivel)
        $primeraUbicacion = $producto->asigna_ubicaciones->first();
        $nivel = $primeraUbicacion?->nivel?->nombre;
        $pasillo = $primeraUbicacion?->nivel?->pasillo?->nombre;
        $producto->ubicacion_texto = $nivel && $pasillo ? "{$pasillo} / {$nivel}" : ($nivel ?? $pasillo ?? null);

        return response()->json($producto);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.codigo_barras' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.lote' => 'required|integer'
        ]);

        $venta = Venta::create([
            'usuario_id' => Auth::id(),
            'fecha' => now(),
            'total' => 0
        ]);

        $total = 0;

        foreach ($request->productos as $p) {
            $lote = Lote::find($p['lote']);
            if (!$lote) continue;

            // Obtener producto relacionado
            $producto = $lote->producto;

            // Calcular subtotal usando precio de venta del producto
            $subtotal = $p['cantidad'] * $producto->precio_venta;

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'lote_id' => $lote->id,
                'cantidad' => $p['cantidad'],
                'subtotal' => $subtotal
            ]);

            $total += $subtotal;

            // Reducir stock del lote
            $lote->cantidad -= $p['cantidad'];
            $lote->save();
        }

        // Guardar total de la venta
        $venta->total = $total;
        $venta->save();

        return response()->json([
            'message' => 'Venta registrada correctamente',
            'venta_id' => $venta->id
        ]);
    }

}
