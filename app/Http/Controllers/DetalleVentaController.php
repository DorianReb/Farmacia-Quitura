<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta; // Usamos tu modelo
use App\Models\Venta; // Necesario si quieres filtrar/mostrar datos de la venta principal
use Illuminate\Http\Request;

class DetalleVentaController extends Controller
{
    /**
     * Muestra la lista de todos los detalles de ventas (index del CRUD).
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));
        
        // 1. Consulta Principal: Carga los detalles de venta.
        $query = DetalleVenta::query();
        
        // 2. Cargar Relaciones Necesarias:
        $query->with([
            'venta.usuario',       // Necesitas la Venta y quién la hizo (el Usuario).
            'lote.producto.marca'  // Necesitas el Lote, su Producto y la Marca para detalles.
        ]);
        
        // 3. Aplicar Filtro de Búsqueda (ejemplo: buscar por ID de venta)
        if (!empty($q)) {
            $query->where('venta_id', $q)
                  ->orWhereHas('lote.producto', function($p) use ($q) {
                      $p->where('nombre_comercial', 'LIKE', "%{$q}%");
                  });
        }

        // 4. Paginación y Ordenamiento
        $detallesVenta = $query->orderByDesc('venta_id')->paginate(20);
        
        // Pasamos la variable $detallesVenta a la vista
        return view('detalleventa.index', compact('detallesVenta', 'q'));
    }
    
}