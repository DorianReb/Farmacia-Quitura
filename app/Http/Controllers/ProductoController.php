<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Marca;
use App\Models\FormaFarmaceutica;
use App\Models\Presentacion;
use App\Models\UnidadMedida;
use App\Models\Categoria;
use App\Models\AsignaComponente;
use App\Models\NombreCientifico;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query()->with(['marca','formaFarmaceutica','presentacion','unidadMedida','categoria']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('nombre_comercial', 'like', "%{$q}%");
        }

        $productos = $query->orderBy('nombre_comercial')->paginate(10);

        // ===== AÑADIDO: datos de asigna_componentes para los productos mostrados en esta página =====
        $pageIds = $productos->getCollection()->pluck('id');

        $asignaciones = $pageIds->isEmpty()
            ? collect()
            : AsignaComponente::with([
                'componente:id,nombre',
                'fuerzaUnidad:id,nombre',
                'baseUnidad:id,nombre',
            ])
                ->whereIn('producto_id', $pageIds)
                ->orderBy('nombre_cientifico_id') // opcional
                ->get()
                ->groupBy('producto_id');

        $metaPorProducto = $asignaciones->map(function ($rows) {
            $nombres = $rows->pluck('componente.nombre')->filter()->unique()->sort()->values();
            return [
                'total'   => $nombres->count(),
                'nombres' => $nombres->all(),
            ];
        });

        // === Crear una propiedad temporal para mostrar texto compacto ===
        // === Crear propiedad temporal "resumen" SIN presentación ===
        foreach ($productos as $producto) {
            // Componentes de asigna_componentes como: "Paracetamol 500 mg / 1 tableta, Cafeína 65 mg / 1 tableta"
            $componentesTxt = '';
            if (isset($asignaciones[$producto->id])) {
                $componentesTxt = $asignaciones[$producto->id]
                    ->map(function ($a) {
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 2, '.', ''), '0'), '.');
                        $base   = rtrim(rtrim(number_format($a->base_cantidad, 2, '.', ''), '0'), '.');
                        $fu     = $a->fuerzaUnidad->nombre ?? '';   // mg, ml, etc.
                        $bu     = $a->baseUnidad->nombre ?? '';     // tableta, cápsula, ml, etc.
                        $comp   = $a->componente->nombre ?? '';
                        return trim($comp.' '.trim($fuerza.' '.($fu ?: '')).' / '.trim($base.' '.($bu ?: '')));
                    })
                    ->implode(', ');
                $componentesTxt = $componentesTxt ? " {$componentesTxt}" : '';
            }

            // Partes base (SIN presentación)
            $nombre      = trim($producto->nombre_comercial ?? '');
            $descripcion = trim($producto->descripcion ?? '');                 // p.ej. "Laxante", "Analgésico"
            $contenido   = trim($producto->contenido ?? '');                   // p.ej. "20 tabletas", "125 ml"
            $forma       = trim($producto->formaFarmaceutica->nombre ?? '');   // p.ej. "Tabletas", "Jarabe"

            // Orden final: nombre + descripción + contenido + forma + componentes
            $partes = array_filter([$nombre, $descripcion ?: null, $contenido ?: null, $forma ?: null]);
            $producto->resumen = trim(implode(' ', $partes).$componentesTxt);
        }



        // Para modales (crear/editar asignación)
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        // Ya los tenías:
        $marcas         = Marca::all();
        $formas         = FormaFarmaceutica::all();
        $presentaciones = Presentacion::all();
        $unidadesMed    = UnidadMedida::all(); // si tu vista los usa así
        $categorias     = Categoria::all();

        return view('producto.index', compact(
            'productos',
            'marcas','formas','presentaciones','unidadesMed','categorias',
            'asignaciones','metaPorProducto','componentes','unidades' // 👈 AÑADIDOS
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'presentacion_id' => 'required|exists:presentaciones,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
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

        return redirect()->route('producto.index')->with('success', 'Producto creado correctamente')->with('from_modal', 'create_producto');
    }

    public function edit(Producto $producto)
    {
        $marcas = Marca::all();
        $formas = FormaFarmaceutica::all();
        $presentaciones = Presentacion::all();
        $unidades = UnidadMedida::all();
        $categorias = Categoria::all();

        return view('producto.edit', compact('producto', 'marcas', 'formas', 'presentaciones', 'unidades', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'presentacion_id' => 'required|exists:presentaciones,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
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

        return redirect()->route('producto.index')->with('success', 'Producto actualizado correctamente')->with('from_modal', 'edit_producto')->with('edit_id', $producto->id);
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('producto.index')->with('success', 'Producto eliminado correctamente');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function menu(Request $request)
    {
        $q = trim($request->q);

        // La consulta se inicia
        $productosBuscados = Producto::query()
            ->with(['lotes', 'marca'])
            ->when($q, function ($query) use ($q) {
                // Aplicar un grupo de cláusulas WHERE OR
                $query->where(function($q_inner) use ($q) {
                    // Búsqueda por Nombre Comercial (flexible)
                    $q_inner->where('nombre_comercial', 'LIKE', "%{$q}%");

                    // Opcional: Búsqueda por Código de Barras (si se ingresó un código parcial/completo)
                    $q_inner->orWhere('codigo_barras', 'LIKE', "%{$q}%");
                });
            })
            // Aseguramos el ordenamiento
            ->orderBy('nombre_comercial', 'asc')
            // Paginación fija de 10 resultados por modal
            ->paginate(10, ['*'], 'productos_page');

        // DEVOLVER SOLO EL HTML PARCIAL (REQUERIDO POR AJAX)
        return view('producto.menu', [
            'productos' => $productosBuscados,
            'q' => $q
        ])->render();
    }
}
