@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        /* Paginación compacta (igual que Marcas) */
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{ background:var(--bs-success); border-color:var(--bs-success); }
    </style>

    <div class="container-xxl">

        {{-- Título centrado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-box-open"></i>
                    <h1 class="h4 m-0">Presentaciones</h1>
                </div>
            </div>
        </div>

        {{-- Botón + (izquierda) + Buscador (derecha) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button type="button"
                        class="btn btn-success btn-icon shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createPresentacionModal"
                        data-bs-placement="right"
                        title="Agregar nuevo">
                    <i class="fa-solid fa-plus"></i>
                </button>

                {{-- Botón para ver eliminados --}}
                <a href="{{ route('presentacion.eliminados') }}"
                   class="btn btn-warning btn-icon shadow-sm ms-2"
                   title="Ver eliminadas">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            </div>

            <form method="GET" action="{{ route('presentacion.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar presentación…">
                    @if(request('q'))
                        <a href="{{ route('presentacion.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
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
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Nombre</th>
                            <th class="text-end" style="width:220px">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($presentaciones as $presentacion)
                            <tr>
                                <td class="fw-semibold">{{ $presentacion->nombre }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPresentacionModal{{ $presentacion->id }}"
                                                title="Editar" data-bs-placement="top">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        {{-- Eliminar --}}
                                        <form action="{{ route('presentacion.destroy', $presentacion->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta presentación?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                    type="submit" title="Eliminar" data-bs-placement="top">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal de edición --}}
                            @include('presentacion.edit', ['presentacion' => $presentacion])
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No hay presentaciones registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación compacta + info (igual que Marcas) --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $presentaciones->firstItem() ?? 0 }}–{{ $presentaciones->lastItem() ?? 0 }} de {{ $presentaciones->total() }}
                    @if(request('q')) • Filtro: “{{ request('q') }}” @endif
                </small>

                @if($presentaciones->hasPages())
                    <nav aria-label="Paginación de presentaciones" class="order-1 order-md-2">
                        {{ $presentaciones->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de creación --}}
    @include('presentacion.create')

    {{-- Reabrir modal si hubo errores en create --}}
    @if ($errors->any() && session('from_modal') === 'create_presentacion')
        <script>
            document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal('#createPresentacionModal').show());
        </script>
    @endif

    {{-- Reabrir modal si hubo errores en edit --}}
    @if ($errors->any() && session('from_modal') === 'edit_presentacion' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editPresentacionModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif
@endsection
