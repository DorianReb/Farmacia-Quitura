@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{ background:var(--bs-success); border-color:var(--bs-success); }
        .col-resumen{ max-width:520px; white-space:normal; word-break:break-word; }
        @media (max-width: 1400px){ .col-resumen{ max-width:420px; } }
    </style>

    @php
        $rol = Auth::user()->rol ?? null;
        $isAdmin = in_array($rol, ['Administrador','Superadmin']);
    @endphp

    <div class="container-xxl">
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-boxes"></i>
                    <h1 class="h4 m-0">Productos</h1>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Agregar nuevo: SOLO Admin / Superadmin --}}
            @if($isAdmin)
                <button type="button"
                        class="btn btn-success btn-icon shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createProductoModal"
                        title="Agregar nuevo">
                    <i class="fa-solid fa-plus"></i>
                </button>
            @endif

            <form method="GET" action="{{ route('producto.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar producto…">
                    @if(request('q'))
                        <a href="{{ route('producto.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" title="Buscar">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-width: 100vw; overflow-x: auto; scrollbar-width: thin; scrollbar-color: #c7d2fe transparent;">
                    <table class="table table-hover align-middle mb-0" style="min-width: 1100px; font-size: 0.95rem;">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th> {{-- resumen concatenado --}}
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Código de Barras</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Exist.</th>
                            <th class="text-end">Stock mín.</th>
                            <th>Receta</th>
                            <th class="text-center">Comp.</th>
                            @if($isAdmin)
                                <th class="text-end" style="width:220px">Acciones</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($productos as $producto)
                            @php
                                $grupo = ($asignaciones[$producto->id] ?? collect());
                                $meta  = $metaPorProducto[$producto->id] ?? null;
                                $total = $meta['total'] ?? 0;
                                $nombres = $meta['nombres'] ?? [];
                                $preview = array_slice($nombres, 0, 3);
                                $restantes = max(0, $total - count($preview));
                                $listaCompleta = collect($nombres)->map(fn($x) => e($x))->implode('<br>');
                            @endphp
                            <tr>
                                {{-- IMAGEN --}}
                                <td class="text-center" style="width:72px;">
                                    <img src="{{ $producto->imagen_url }}"
                                         alt="{{ $producto->alt_imagen ?? $producto->nombre_comercial }}"
                                         class="img-thumbnail" style="max-height: 60px; width: auto;">
                                </td>

                                {{-- RESUMEN CONCATENADO --}}
                                <td class="fw-semibold col-resumen">
                                    {{ $producto->resumen ?? $producto->nombre_comercial }}
                                </td>

                                {{-- CAMPOS NO REDUNDANTES --}}
                                <td>{{ $producto->marca->nombre ?? '—' }}</td>
                                <td>{{ $producto->categoria->nombre ?? $producto->categoria->nombre_categoria ?? '—' }}</td>
                                <td>{{ $producto->codigo_barras ?? '—' }}</td>
                                <td class="text-end">${{ number_format($producto->precio_venta, 2) }}</td>
                                <td class="text-end">{{ $producto->existencias }}</td>
                                <td class="text-end">{{ $producto->stock_minimo }}</td>
                                <td>
                                    @if($producto->requiere_receta)
                                        <span class="badge bg-danger-subtle text-danger border">Sí</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border">No</span>
                                    @endif
                                </td>

                                {{-- COMPONENTES: badge y popover (sin columna de "Detalle" para ahorrar ancho) --}}
                                <td class="text-center">
                                    @if($total > 0)
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
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- ACCIONES --}}
                                @if($isAdmin)
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Editar --}}
                                            <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProductoModal{{ $producto->id }}"
                                                    title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            {{-- Eliminar --}}
                                            <form action="{{ route('producto.destroy', $producto->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este producto?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                        type="submit" title="Eliminar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                            {{-- Asignar componente --}}
                                            <button class="btn btn-success shadow-sm rounded-pill btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#createAsignaComponenteModal"
                                                    data-producto-id="{{ $producto->id }}"
                                                    title="Asignar componente">
                                                <i class="fa-solid fa-diagram-project"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>

                            {{-- Modal de edición (inline) --}}
                            @if($isAdmin)
                                @include('producto.edit', ['producto' => $producto])
                            @endif
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

        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $productos->firstItem() ?? 0 }}–{{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }}
                    @if(request('q')) • Filtro: “{{ request('q') }}” @endif
                </small>

                @if($productos->hasPages())
                    <nav aria-label="Paginación de productos" class="order-1 order-md-2">
                        {{ $productos->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal crear: SOLO Admin / Superadmin --}}
    @if($isAdmin)
        @include('producto.create')
    @endif

    {{-- Modal crear asigna_componentes (usa productos de la página actual) --}}
    @if($isAdmin && isset($componentes, $unidades))
        @include('asigna_componentes.create', [
            'productos'   => $productos->getCollection(),
            'componentes' => $componentes,
            'unidades'    => $unidades
        ])
    @endif

    {{-- Reapertura de modales tras validación --}}
    @if ($isAdmin && $errors->any() && session('from_modal') === 'create_producto')
        <script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal('#createProductoModal').show());</script>
    @endif

    @if ($isAdmin && $errors->any() && session('from_modal') === 'edit_producto' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editProductoModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Popovers (componentes)
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                    new bootstrap.Popover(el, { container: 'body' });
                });

                // Preseleccionar producto al abrir el modal de crear asignación
                const createModalEl = document.getElementById('createAsignaComponenteModal');
                if (createModalEl) {
                    document.querySelectorAll('[data-bs-target="#createAsignaComponenteModal"][data-producto-id]')
                        .forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.getAttribute('data-producto-id');
                                const select = createModalEl.querySelector('select[name="producto_id"]');
                                if (select) {
                                    select.value = id;
                                    if (window.$ && $(select).hasClass('select2')) {
                                        $(select).val(id).trigger('change');
                                    }
                                }
                                const hidden = createModalEl.querySelector('input[name="producto_id"]');
                                if (hidden) hidden.value = id;
                            });
                        });
                }
            });
        </script>
    @endpush
@endsection
