@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        /* Paginación compacta igual que Marcas */
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{ background:var(--bs-success); border-color:var(--bs-success); }
    </style>

    <div class="container-xxl">

        {{-- Título centrado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-ruler"></i>
                    <h1 class="h4 m-0">Unidades de medida</h1>
                </div>
            </div>
        </div>

        {{-- Botón + (izquierda) + Buscador (derecha) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button"
                    class="btn btn-success btn-icon shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#createUnidadModal"
                    title="Agregar nuevo">
                <i class="fa-solid fa-plus"></i>
            </button>

            <form method="GET" action="{{ route('unidad_medida.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar unidad…">
                    @if(request('q'))
                        <a href="{{ route('unidad_medida.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
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

        {{-- Tabla --}}
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
                        @forelse($unidad as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->nombre }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUnidadModal{{ $u->id }}"
                                                title="Editar" data-bs-placement="top">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('unidad_medida.destroy', $u->id) }}"
                                              method="POST"
                                              class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                    type="submit" title="Eliminar" data-bs-placement="top">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal de edición --}}
                            @include('unidad_medida.edit', ['unidad' => $u])
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No hay unidades de medida registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación compacta + info --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $unidad->firstItem() ?? 0 }}–{{ $unidad->lastItem() ?? 0 }} de {{ $unidad->total() }}
                    @if(request('q')) • Filtro: “{{ request('q') }}” @endif
                </small>

                @if($unidad->hasPages())
                    <nav aria-label="Paginación de unidades" class="order-1 order-md-2">
                        {{ $unidad->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de creación --}}
    @include('unidad_medida.create')

    {{-- Reabrir modal si hubo errores en create --}}
    @if ($errors->any() && session('from_modal') === 'create_unidad')
        <script>
            document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal('#createUnidadModal').show());
        </script>
    @endif

    {{-- Reabrir modal si hubo errores en edit --}}
    @if ($errors->any() && session('from_modal') === 'edit_unidad' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editUnidadModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif
@endsection
