<?php

namespace App\Http\Controllers;

use App\Models\Venta;
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
            $productoEncontrado = Producto::with(['lotes'])
                ->where('codigo_barras', $request->q)
                ->first();

            if ($productoEncontrado) {
                $productoEncontrado->existencias_calculadas = $productoEncontrado->lotes->sum('cantidad');
            }
        }

        return view('venta.index', compact('productoEncontrado','itemsEnVenta','totalVenta'));
    }

    public function buscarProductoPorCodigo($codigo)
    {
        $producto = Producto::with(['lotes'])
            ->where('codigo_barras', $codigo)
            ->first();

        if(!$producto){
            return response()->json(['message'=>'Producto no encontrado'], 404);
        }

        $producto->existencias_calculadas = $producto->lotes->sum('cantidad');
        return response()->json($producto);
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
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.codigo_barras' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.lote' => 'required'
        ]);

        $venta = Venta::create([
            'usuario_id' => Auth::id(),
            'fecha' => now(),
            'total' => 0
        ]);

        $total = 0;

        foreach($request->productos as $p){
            $lote = Lote::find($p['lote']);
            $subtotal = $p['cantidad'] * $lote->precio_venta;

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'lote_id' => $lote->id,
                'cantidad' => $p['cantidad'],
                'subtotal' => $subtotal
            ]);

            $total += $subtotal;
        }

        $venta->total = $total;
        $venta->save();

        return response()->json(['message'=>'Venta registrada correctamente']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        //
    }
}
