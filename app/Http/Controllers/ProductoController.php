<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Marca;
use App\Models\FormaFarmaceutica;
use App\Models\Presentacion;
use App\Models\UnidadMedida;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query()->with(['marca', 'formaFarmaceutica', 'presentacion', 'unidadMedida', 'categoria']);

        // Filtro de búsqueda por nombre comercial
        if ($request->has('q') && $request->q != '') {
            $query->where('nombre_comercial', 'like', "%{$request->q}%");
        }

        $productos = $query->orderBy('nombre_comercial')->paginate(10);

        // Datos para selects en modales
        $marcas = Marca::all();
        $formas = FormaFarmaceutica::all();
        $presentaciones = Presentacion::all();
        $unidades = UnidadMedida::all();
        $categorias = Categoria::all();

        return view('producto.index', compact('productos', 'marcas', 'formas', 'presentaciones', 'unidades', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'forma_farmaceutica_id' => 'required|exists:forma_farmaceuticas,id',
            'presentacion_id' => 'required|exists:presentaciones,id',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
            'categoria_id' => 'required|exists:categorias,id',
            'nombre_comercial' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'contenido' => 'nullable|string|max:255',
            'requiere_receta' => 'required|boolean',
            'stock_minimo' => 'required|integer|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'existencias' => 'required|integer|min:0',
            'codigo_barras' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'alt_imagen' => 'nullable|string|max:255'
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente')->with('from_modal', 'create_producto');
    }

    public function edit(Producto $producto)
    {
        $marcas = Marca::all();
        $formas = FormaFarmaceutica::all();
        $presentaciones = Presentacion::all();
        $unidades = UnidadMedida::all();
        $categorias = Categoria::all();

        return view('productos.edit', compact('producto', 'marcas', 'formas', 'presentaciones', 'unidades', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'forma_farmaceutica_id' => 'required|exists:forma_farmaceuticas,id',
            'presentacion_id' => 'required|exists:presentaciones,id',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
            'categoria_id' => 'required|exists:categorias,id',
            'nombre_comercial' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'contenido' => 'nullable|string|max:255',
            'requiere_receta' => 'required|boolean',
            'stock_minimo' => 'required|integer|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'existencias' => 'required|integer|min:0',
            'codigo_barras' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'alt_imagen' => 'nullable|string|max:255'
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente')->with('from_modal', 'edit_producto')->with('edit_id', $producto->id);
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente');
    }
}
