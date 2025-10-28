@extends('layouts.sidebar-admin')
@php
    $rol = Auth::user()->rol ?? null;
    $isAdmin = in_array($rol, ['Administrador','Superadmin']);
@endphp

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        /* Paginación compacta (igual que Categorías) */
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{ background:var(--bs-success); border-color:var(--bs-success); }
    </style>

    <div class="container-xxl">

        {{-- Título centrado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-diagram-project"></i>
                    <h1 class="h4 m-0">Asignar componentes</h1>
                </div>
            </div>
        </div>

        {{-- Botón + (izq) + Buscador (der) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            @if($isAdmin)
                <button type="button"
                        class="btn btn-success btn-icon shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createAsignaComponenteModal"
                        title="Agregar nuevo">
                    <i class="fa-solid fa-plus"></i>
                </button>
            @endif

            <form method="GET" action="{{ route('asigna_componentes.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Buscar por producto o componente…">
                    @if(!empty($q))
                        <a href="{{ route('asigna_componentes.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" data-bs-toggle="tooltip" title="Buscar">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- Alert de éxito --}}
        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla en card --}}
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Componente</th>
                            <th>Cantidad</th>
                            @if($isAdmin)
                                <th class="text-end" style="width:140px;">Acciones</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($productosPaginator as $prod)
                            @php
                                $grupo = $asignacionesPorProducto[$prod->id] ?? collect();
                                $meta  = $metaPorProducto[$prod->id] ?? null;

                                $total = $meta['total'] ?? 0;
                                $nombres = $meta['nombres'] ?? [];
                                $preview = array_slice($nombres, 0, 3);
                                $restantes = max(0, $total - count($preview));
                                $listaCompleta = collect($nombres)->map(fn($x) => e($x))->implode('<br>');
                            @endphp

                            <tr>
                                {{-- Producto --}}
                                <td class="fw-semibold align-middle">
                                    {{ $prod->nombre_comercial }}
                                </td>

                                {{-- Componentes asignados (conteo + preview + popover) --}}
                                <td class="align-middle">
                                    @if($meta)
                                        <div class="small text-muted">
                                            <span class="badge bg-primary me-1">{{ $total }}</span>
                                            @if(count($preview))
                                                <span class="me-1">{{ implode(', ', $preview) }}</span>
                                            @endif
                                            @if($restantes > 0)
                                                <a href="#"
                                                   class="text-decoration-none"
                                                   data-bs-toggle="popover"
                                                   data-bs-html="true"
                                                   data-bs-trigger="focus hover"
                                                   data-bs-placement="top"
                                                   title="Componentes"
                                                   data-bs-content="{!! $listaCompleta !!}">
                                                    +{{ $restantes }} más
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Presentaciones (lista por asignación del producto) --}}
                                <td class="align-middle">
                                    @if($grupo->isNotEmpty())
                                        <ul class="list-unstyled small mb-0">
                                            @foreach($grupo as $row)
                                                <li class="mb-1">
                                                    {{ rtrim(rtrim(number_format($row->fuerza_cantidad, 2, '.', ''), '0'), '.') }}
                                                    {{ $row->fuerzaUnidad->nombre ?? '' }} /
                                                    {{ rtrim(rtrim(number_format($row->base_cantidad, 2, '.', ''), '0'), '.') }}
                                                    {{ $row->baseUnidad->nombre ?? '' }}
                                                    — <em>{{ $row->componente->nombre ?? '—' }}</em>

                                                    @if($isAdmin)
                                                        {{-- Editar asignación --}}
                                                        <button class="btn btn-warning btn-sm py-0 px-2 ms-1"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editAsignaComponenteModal{{ $row->id }}"
                                                                title="Editar asignación">
                                                            <i class="fa-regular fa-pen-to-square"></i>
                                                        </button>

                                                        {{-- (si lo usas) Eliminar asignación --}}
                                                        <form action="{{ route('asigna_componentes.destroy', $row->id) }}"
                                                              method="POST" class="d-inline ms-1"
                                                              onsubmit="return confirm('¿Eliminar este componente asignado?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm py-0 px-2" title="Eliminar asignación">
                                                                <i class="fa-regular fa-trash-can"></i>
                                                            </button>
                                                        </form>

                                                        {{-- Modal editar (solo para admin) --}}
                                                        @include('asigna_componentes.edit', [
                                                            'row' => $row,
                                                            'productos' => $productos,
                                                            'componentes' => $componentes,
                                                            'unidades' => $unidades
                                                        ])
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Acciones a nivel producto --}}
                                <td class="text-end align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Agregar: SOLO Admin/Superadmin --}}
                                        @if($isAdmin)
                                            <button class="btn btn-success btn-icon shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#createAsignaComponenteModal"
                                                    title="Agregar componente">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        @endif

                                        {{-- Filtro: visible para todos (incluye Vendedor) --}}
                                        <a class="btn btn-outline-secondary btn-icon"
                                           href="{{ route('asigna_componentes.index', ['q' => $prod->nombre_comercial]) }}"
                                           title="Ver solo este producto">
                                            <i class="fa-solid fa-filter"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No hay productos con componentes asignados @if(!empty($q)) para “{{ $q }}” @endif.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación compacta + info (igual que Categorías) --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $productosPaginator->firstItem() ?? 0 }}–{{ $productosPaginator->lastItem() ?? 0 }}
                    de {{ $productosPaginator->total() }}
                    @if(!empty($q)) • Filtro: “{{ $q }}” @endif
                </small>

                @if($productosPaginator->hasPages())
                    <nav aria-label="Paginación de productos" class="order-1 order-md-2">
                        {{ $productosPaginator->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de creación --}}
    @isset($productos, $componentes, $unidades)
        @include('asigna_componentes.create', [
            'productos' => $productos,
            'componentes' => $componentes,
            'unidades' => $unidades
        ])
    @endisset

    {{-- Reabrir modal si hubo errores (opcional si seteas from_modal en el controlador) --}}
    @if ($errors->any())
        @if (session('from_modal') === 'create_asigna_componente')
            <script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal('#createAsignaComponenteModal').show());</script>
        @elseif (session('from_modal') === 'edit_asigna_componente' && session('edit_id'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const el = document.getElementById('editAsignaComponenteModal{{ session('edit_id') }}');
                    if (el) new bootstrap.Modal(el).show();
                });
            </script>
        @endif
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
            [...popovers].forEach(el => new bootstrap.Popover(el, { container: 'body' }));
        });
    </script>


@endsection
