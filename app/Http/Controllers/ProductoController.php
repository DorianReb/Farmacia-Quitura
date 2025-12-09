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
        // Texto libre
        $q = trim($request->input('q', ''));
        // Filtro por categoría (select)
        $categoriaFiltro = $request->input('categoria_id'); // id de la categoría
        // Filtro por receta (select: '', '1', '0')
        $recetaFiltroParam = $request->input('receta');

        // Base de la consulta
        $query = Producto::query()
            ->with([
                'marca',
                'formaFarmaceutica',
                'presentacion',
                'unidadMedida',
                'categoria',
                // 🔹 IMPORTANTE: aquí va la relación correcta
                'asignaUbicaciones.nivel.pasillo',
            ])
            // 🔹 Campo calculado: existencias_vigentes = suma de lotes con stock y sin caducar
            ->withSum([
                'lotes as existencias_vigentes' => function ($q2) {
                    $q2->whereNull('deleted_at')
                        ->where(function ($q3) {
                            // Aceptar lotes sin fecha de caducidad
                            $q3->whereNull('fecha_caducidad')
                                // y lotes con caducidad futura
                                ->orWhere('fecha_caducidad', '>', now()->toDateString());
                        })
                        ->where('cantidad', '>', 0);
                }
            ], 'cantidad');

        // ==========================
        // FILTRO DE BÚSQUEDA GLOBAL
        // ==========================
        if ($q !== '') {
            $lowerQ = mb_strtolower($q, 'UTF-8');

            // 1) Productos que tengan un componente cuyo nombre coincida
            $productoIdsPorComponente = AsignaComponente::whereHas('componente', function ($c) use ($q) {
                $c->where('nombre', 'like', "%{$q}%");
            })
                ->pluck('producto_id')
                ->unique()
                ->values();

            // 2) Traducir texto a filtro de receta (sí/no) SOLO para el buscador de texto
            $recetaFiltroTexto = null;
            if (in_array($lowerQ, ['si', 'sí', 'si receta', 'sí receta', 'requiere receta', 'con receta'])) {
                $recetaFiltroTexto = 1;
            } elseif (in_array($lowerQ, ['no', 'sin receta', 'no receta'])) {
                $recetaFiltroTexto = 0;
            }

            // 3) Aplicar búsqueda sobre varios campos
            $query->where(function ($sub) use ($q, $productoIdsPorComponente, $recetaFiltroTexto) {
                $sub->where('nombre_comercial', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%")
                    // Marca
                    ->orWhereHas('marca', function ($m) use ($q) {
                        $m->where('nombre', 'like', "%{$q}%");
                    })
                    // Categoría (por nombre)
                    ->orWhereHas('categoria', function ($c) use ($q) {
                        $c->where('nombre', 'like', "%{$q}%");
                    });

                // Coincidencia por componente
                if ($productoIdsPorComponente->isNotEmpty()) {
                    $sub->orWhereIn('id', $productoIdsPorComponente);
                }

                // Coincidencia por “requiere receta” según texto
                if (!is_null($recetaFiltroTexto)) {
                    $sub->orWhere('requiere_receta', $recetaFiltroTexto);
                }
            });
        }

        // ==========================
        // FILTROS EXPLÍCITOS (select)
        // ==========================

        // Filtro por categoría (id exacto)
        if (!empty($categoriaFiltro)) {
            $query->whereHas('categoria', function ($c) use ($categoriaFiltro) {
                $c->where('id', $categoriaFiltro);
            });
        }

        // Filtro explícito por receta (1 = con receta, 0 = sin receta)
        if ($recetaFiltroParam !== null && $recetaFiltroParam !== '') {
            $query->where('requiere_receta', (int) $recetaFiltroParam);
        }

        $productos = $query->orderBy('nombre_comercial')->paginate(10)
            ->appends($request->query()); // mantiene filtros en la paginación

        // ===== datos de asigna_componentes para los productos mostrados en esta página =====
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

        // === Crear propiedades temporales: "resumen" y "ubicaciones_texto" ===
        foreach ($productos as $producto) {
            // Componentes de asigna_componentes como:
            // "Paracetamol 500 mg / 1 tableta, Cafeína 65 mg / 1 tableta"
            $componentesTxt = '';
            if (isset($asignaciones[$producto->id])) {
                $componentesTxt = $asignaciones[$producto->id]
                    ->map(function ($a) {
                        // 3 decimales, sin ceros sobrantes
                        $fuerza = rtrim(rtrim(number_format($a->fuerza_cantidad, 3, '.', ''), '0'), '.');
                        $base   = rtrim(rtrim(number_format($a->base_cantidad, 3, '.', ''), '0'), '.');
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

            // 🔹 AQUÍ SE ARMA EL TEXTO DE UBICACIONES
            if ($producto->relationLoaded('asignaUbicaciones') && $producto->asignaUbicaciones->count()) {
                $producto->ubicaciones_texto = $producto->asignaUbicaciones
                    ->map(function ($au) {
                        // usa el accesor getNombreAttribute() de Nivel
                        return $au->nivel ? $au->nivel->nombre : null;
                    })
                    ->filter()
                    ->implode(', ');

                if ($producto->ubicaciones_texto === '') {
                    $producto->ubicaciones_texto = '—';
                }
            } else {
                $producto->ubicaciones_texto = '—';
            }
        }

        // Para modales (crear/editar asignación)
        $componentes = NombreCientifico::orderBy('nombre')->get(['id','nombre']);
        $unidades    = UnidadMedida::orderBy('nombre')->get(['id','nombre']);

        // Catálogos para los modals de producto
        $marcas         = Marca::orderBy('nombre')->get();
        $formas         = FormaFarmaceutica::orderBy('nombre')->get();
        $presentaciones = Presentacion::orderBy('nombre')->get();
        $unidadesMed    = UnidadMedida::orderBy('nombre')->get();
        $categorias     = Categoria::orderBy('nombre')->get();

        return view('producto.index', compact(
            'productos',
            'marcas','formas','presentaciones','unidadesMed','categorias',
            'asignaciones','metaPorProducto','componentes','unidades',
            'q','categoriaFiltro','recetaFiltroParam'
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
            'codigo_barras' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'alt_imagen' => 'nullable|string|max:255'
        ]);

        // 👇 página desde POST o, en su defecto, 1
        $page = $request->input('page', 1);

        // Mejor excluimos cualquier existencias que venga del request
        $data = $request->except('existencias');

        // existencias se controlan por lotes
        $data['existencias'] = 0;

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create($data);

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Producto creado correctamente')
            ->with('from_modal', 'create_producto')
            ->with('edit_id', $producto->id);
    }

    public function edit(Producto $producto)
    {
        $marcas = Marca::orderBy('nombre')->get();
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
            'codigo_barras' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'alt_imagen' => 'nullable|string|max:255'
        ]);

        $page = $request->input('page', 1);

        $data = $request->except('existencias');

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Producto actualizado correctamente')
            ->with('from_modal', 'edit_producto')
            ->with('edit_id', $producto->id);
    }

    public function destroy(Request $request, Producto $producto)
    {
        $page = $request->input('page', 1);

        $producto->delete();

        return redirect()
            ->route('producto.index', ['page' => $page])
            ->with('success', 'Producto eliminado correctamente');
    }

    public function menu(Request $request)
    {
        $q = trim($request->q);

        $productosBuscados = Producto::query()
            ->with(['lotes', 'marca'])
            ->when($q, function ($query) use ($q) {
                $query->where(function($q_inner) use ($q) {
                    $q_inner->where('nombre_comercial', 'LIKE', "%{$q}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$q}%");
                });
            })
            ->orderBy('nombre_comercial', 'asc')
            ->paginate(10, ['*'], 'productos_page');

        return view('producto.menu', [
            'productos' => $productosBuscados,
            'q' => $q
        ])->render();
    }
}
