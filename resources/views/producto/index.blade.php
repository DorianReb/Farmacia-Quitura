@extends('layouts.sidebar-admin')

@section('content')

    <style>
        .card-soft{
            border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);
        }
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{
            padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center
        }
        .pagination { gap:.25rem; }
        .pagination .page-link{
            padding:.25rem .55rem; font-size:.85rem; border:0;
        }
        .pagination .page-item.active .page-link{
            background:var(--bs-success); border-color:var(--bs-success);
        }
        .col-resumen{
            max-width:520px; white-space:normal; word-break:break-word;
        }
        @media (max-width: 1400px){
            .col-resumen{ max-width:420px; }
        }
        .chip-comp{
            background:#eef2ff; border:1px solid #e0e7ff; border-radius:999px;
            padding:.15rem .5rem; display:inline-flex; align-items:center; gap:.35rem;
        }
        .chip-actions .btn{
            padding:.1rem .35rem; border-radius:999px;
        }
        .comp-list{ max-height: 180px; overflow:auto; }
    </style>

    @php
        $rol = Auth::user()->rol ?? null;
        $isAdmin = in_array($rol, ['Administrador','Superadmin']);
    @endphp


    <div class="container-xxl">

        {{-- TÍTULO --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-boxes"></i>
                    <h1 class="h4 m-0">Productos</h1>
                </div>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="card card-soft mb-3">
            <div class="card-header text-dark py-2">
                <h2 class="h6 m-0 text-uppercase" style="font-weight:700; letter-spacing:.04em;">
                    Filtros de búsqueda
                </h2>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('producto.index') }}" class="row g-3 align-items-end">

                    {{-- Buscar --}}
                    <div class="col-md-4 col-lg-4">
                        <label class="form-label mb-1"><small>Buscar (nombre, descripción, código, marca…)</small></label>
                        <input type="text" name="q" id="q" class="form-control"
                               value="{{ request('q') }}" placeholder="Paracetamol, jarabe, 750 mg, código...">
                    </div>

                    {{-- Categoría --}}
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label mb-1"><small>Categoría</small></label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (string)request('categoria_id') === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Receta --}}
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label mb-1"><small>Requiere receta</small></label>
                        <select name="receta" id="receta" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('receta') === '1' ? 'selected':'' }}>Sólo con receta</option>
                            <option value="0" {{ request('receta') === '0' ? 'selected':'' }}>Sin receta</option>
                        </select>
                    </div>

                    {{-- Buscar --}}
                    <div class="col-md-2 col-lg-1">
                        <button type="submit" class="btn btn-success w-100" title="Buscar">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>

                    {{-- Limpiar filtros --}}
                    @if(request('q') || request('categoria_id') || (request()->has('receta') && request('receta') !== ''))
                        <div class="col-md-2 col-lg-1">
                            <a href="{{ route('producto.index') }}" class="btn btn-outline-secondary w-100" title="Limpiar filtros">
                                <i class="fa-regular fa-circle-xmark"></i>
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- ALERTA SUCCESS --}}
        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        {{-- BOTÓN AGREGAR (debajo de filtros, encima de tabla) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-2 gap-2">
            @if($isAdmin)
                <button type="button"
                        class="btn btn-success btn-icon shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createProductoModal"
                        title="Agregar nuevo">
                    <i class="fa-solid fa-plus"></i>
                </button>
            @endif
        </div>

        {{-- TABLA --}}
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-width:100vw;overflow-x:auto;scrollbar-width:thin;scrollbar-color:#c7d2fe transparent;">
                    <table class="table table-hover align-middle mb-0" style="min-width:1100px;font-size:0.95rem;">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Código de Barras</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Exist.</th>
                            <th class="text-end">Stock mín.</th>
                            <th>Receta</th>
                            <th class="text-center">Componentes</th>
                            @if($isAdmin)
                                <th class="text-end" style="width:220px">Acciones</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>

                        {{-- ======================= --}}
                        {{--   LISTADO DE PRODUCTOS  --}}
                        {{-- ======================= --}}

                        @forelse($productos as $producto)

                            @php
                                $grupo = ($asignaciones[$producto->id] ?? collect());
                                $meta = $metaPorProducto[$producto->id] ?? null;
                                $total = $meta['total'] ?? 0;
                                $nombres = $meta['nombres'] ?? [];
                                $listaCompleta = collect($nombres)->map(fn($x)=>e($x))->implode('<br>');

                                if ($grupo->isNotEmpty()) {
                                    $detallesComponentes = $grupo->map(function ($row){
                                        $f = rtrim(rtrim(number_format($row->fuerza_cantidad,3,'.',''),'0'),'.');
                                        $b = rtrim(rtrim(number_format($row->base_cantidad,3,'.',''),'0'),'.');
                                        $fu = $row->fuerzaUnidad->nombre ?? '';
                                        $bu = $row->baseUnidad->nombre ?? '';
                                        $cn = $row->componente->nombre ?? '—';
                                        return trim("$cn $f $fu / $b $bu");
                                    })->implode(' | ');
                                } else {
                                    $detallesComponentes = '—';
                                }

                                $contenidoValor = $producto->contenido;
                                if(is_numeric($contenidoValor)){
                                    $contenidoValor = rtrim(rtrim(number_format($contenidoValor,3,'.',''),'0'),'.');
                                }
                                $unidadNombre = $producto->unidadMedida->nombre ?? '';
                                $contenidoMostrar = trim(($contenidoValor ?? '') . ' ' . $unidadNombre);
                                if($contenidoMostrar === '') $contenidoMostrar = '—';
                            @endphp

                            <tr>

                                {{-- Imagen --}}
                                <td class="text-center" style="width:72px;">
                                    <img src="{{ $producto->imagen_url }}"
                                         alt="{{ $producto->alt_imagen ?? $producto->nombre_comercial }}"
                                         class="img-thumbnail"
                                         style="max-height:60px;width:auto;cursor:pointer;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#modalDetallesProducto"
                                         data-imagen="{{ $producto->imagen_url }}"
                                         data-alt="{{ $producto->alt_imagen ?? $producto->nombre_comercial }}"
                                         data-nombre="{{ $producto->nombre_comercial }}"
                                         data-codigo="{{ $producto->codigo_barras ?? '—' }}"
                                         {{-- 🔹 AQUÍ USAMOS LA UBICACIÓN CALCULADA --}}
                                         data-ubicacion="{{ $producto->ubicaciones_texto ?? '—' }}"
                                         data-componentes="{{ $detallesComponentes }}"
                                         data-forma="{{ $producto->formaFarmaceutica->nombre ?? '—' }}"
                                         data-contenido="{{ $contenidoMostrar }}"
                                         data-marca="{{ $producto->marca->nombre ?? '—' }}"
                                         data-presentacion="{{ $producto->presentacion->nombre ?? '—' }}"
                                         data-categoria="{{ $producto->categoria->nombre ?? '—' }}"
                                         data-receta="{{ $producto->requiere_receta ? 'Sí':'No' }}">
                                </td>

                                {{-- Resumen --}}
                                <td class="fw-semibold col-resumen">
                                    {{ $producto->resumen ?? $producto->nombre_comercial }}
                                </td>

                                <td>{{ $producto->marca->nombre ?? '—' }}</td>
                                <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                                <td>{{ $producto->codigo_barras ?? '—' }}</td>

                                <td class="text-end">
                                    ${{ number_format($producto->precio_venta, 2) }}
                                </td>

                                <td class="text-end">{{ $producto->existencias_vigentes ?? 0 }}</td>
                                <td class="text-end">{{ $producto->stock_minimo }}</td>

                                {{-- Receta --}}
                                <td>
                                    @if($producto->requiere_receta)
                                        <span class="badge bg-danger-subtle text-danger border">Sí</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border">No</span>
                                    @endif
                                </td>

                                {{-- Componentes --}}
                                <td>
                                    @if($total === 0)

                                        <span class="text-muted">—</span>

                                    @else

                                        @if($isAdmin)

                                            <div class="comp-list">
                                                @foreach($grupo as $row)

                                                    @php
                                                        $f = rtrim(rtrim(number_format($row->fuerza_cantidad,3,'.',''),'0'),'.');
                                                        $b = rtrim(rtrim(number_format($row->base_cantidad,3,'.',''),'0'),'.');
                                                        $fu = $row->fuerzaUnidad->nombre ?? '';
                                                        $bu = $row->baseUnidad->nombre ?? '';
                                                        $cn = $row->componente->nombre ?? '—';
                                                    @endphp

                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="chip-comp">
                                                            <i class="fa-solid fa-dna"></i>
                                                            <span class="small">
                                                                {{ $cn }} {{ $f }} {{ $fu }} / {{ $b }} {{ $bu }}
                                                            </span>
                                                        </span>

                                                        <span class="chip-actions">

                                                            {{-- Editar --}}
                                                            <button class="btn btn-warning btn-sm"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editAsignaComponenteModal{{ $row->id }}">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </button>

                                                            {{-- Eliminar --}}
                                                            <form action="{{ route('asigna_componentes.destroy', $row->id) }}"
                                                                  method="POST"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('¿Eliminar el componente {{ $cn }}?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fa-regular fa-trash-can"></i>
                                                                </button>
                                                            </form>
                                                        </span>
                                                    </div>

                                                    {{-- Modal editar --}}
                                                    @include('asigna_componentes.edit', [
                                                        'row' => $row,
                                                        'productos' => collect([$producto]),
                                                        'componentes' => $componentes,
                                                        'unidades' => $unidades
                                                    ])
                                                @endforeach
                                            </div>

                                        @else

                                            {{-- Badge con popover --}}
                                            <a href="#"
                                               class="badge bg-primary text-decoration-none"
                                               data-bs-toggle="popover"
                                               data-bs-html="true"
                                               data-bs-trigger="focus hover"
                                               data-bs-placement="top"
                                               title="Componentes"
                                               data-bs-content="{!! $listaCompleta !!}">
                                                {{ $total }}
                                            </a>

                                        @endif
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                @if($isAdmin)
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">

                                            {{-- Editar --}}
                                            <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProductoModal{{ $producto->id }}">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('producto.destroy', $producto->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este producto?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="page" value="{{ $productos->currentPage() }}">
                                                <button class="btn btn-danger shadow-sm rounded-pill btn-icon">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>

                                            {{-- Asignar componente --}}
                                            <button class="btn btn-success shadow-sm rounded-pill btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#createAsignaComponenteModal"
                                                    data-producto-id="{{ $producto->id }}">
                                                <i class="fa-solid fa-diagram-project"></i>
                                            </button>

                                        </div>
                                    </td>

                                    {{-- Modal editar producto --}}
                                    @include('producto.edit', ['producto' => $producto])
                                @endif
                            </tr>

                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 11 : 10 }}" class="text-center py-4 text-muted">
                                    No hay productos registrados.
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $productos->firstItem() ?? 0 }}–{{ $productos->lastItem() ?? 0 }}
                    de {{ $productos->total() }}
                    @if(request('q'))
                        • Filtro: “{{ request('q') }}”
                    @endif
                </small>

                @if($productos->hasPages())
                    <nav aria-label="Paginación de productos" class="order-1 order-md-2">
                        {{ $productos->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif

            </div>
        </div>

    </div>

    {{-- MODALES --}}
    @if($isAdmin)
        @include('producto.create')
    @endif

    @if($isAdmin && isset($componentes, $unidades))
        @include('asigna_componentes.create', [
            'productos' => $productos->getCollection(),
            'componentes' => $componentes,
            'unidades' => $unidades
        ])
    @endif

    @include('producto.detalles-modal')

    {{-- REAPERTURA DE MODALES --}}
    @if($isAdmin && $errors->any() && session('from_modal') === 'create_producto')
        <script>
            document.addEventListener('DOMContentLoaded',() =>
                new bootstrap.Modal('#createProductoModal').show()
            );
        </script>
    @endif

    @if($isAdmin && $errors->any() && session('from_modal') === 'edit_producto' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded',() => {
                const el = document.getElementById('editProductoModal{{ session('edit_id') }}');
                if(el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif

    {{-- SCRIPTS --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                // Popovers
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                    new bootstrap.Popover(el, { container:'body' });
                });

                // Preseleccionar producto para asignación
                const createModalEl = document.getElementById('createAsignaComponenteModal');
                if(createModalEl){
                    document.querySelectorAll('[data-bs-target="#createAsignaComponenteModal"][data-producto-id]')
                        .forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.getAttribute('data-producto-id');
                                const select = createModalEl.querySelector('select[name="producto_id"]');
                                if(select){
                                    select.value = id;
                                    if(window.$ && $(select).hasClass('select2')){
                                        $(select).val(id).trigger('change');
                                    }
                                }
                                const hidden = createModalEl.querySelector('input[name="producto_id"]');
                                if(hidden) hidden.value = id;
                            });
                        });
                }

                // Modal de detalles
                const modalDetalles = document.getElementById('modalDetallesProducto');
                if(modalDetalles){
                    modalDetalles.addEventListener('show.bs.modal', event => {
                        const img = event.relatedTarget;
                        if(!img) return;

                        modalDetalles.querySelector('#detalles_producto_imagen').src         = img.dataset.imagen;
                        modalDetalles.querySelector('#detalles_producto_imagen').alt         = img.dataset.alt;
                        modalDetalles.querySelector('#detalles_producto_nombre').textContent = img.dataset.nombre;
                        modalDetalles.querySelector('#detalles_producto_codigo').textContent = img.dataset.codigo;
                        modalDetalles.querySelector('#detalles_producto_ubicacion').textContent = img.dataset.ubicacion;
                        modalDetalles.querySelector('#detalles_producto_cientifico').textContent= img.dataset.componentes;
                        modalDetalles.querySelector('#detalles_producto_forma').textContent   = img.dataset.forma;
                        modalDetalles.querySelector('#detalles_producto_contenido').textContent= img.dataset.contenido;
                        modalDetalles.querySelector('#detalles_producto_marca').textContent   = img.dataset.marca;
                        modalDetalles.querySelector('#detalles_producto_presentacion').textContent = img.dataset.presentacion;
                        modalDetalles.querySelector('#detalles_producto_categoria').textContent = img.dataset.categoria;
                        modalDetalles.querySelector('#detalles_producto_receta').textContent  = img.dataset.receta;
                    });
                }
            });
        </script>
    @endpush

@endsection
