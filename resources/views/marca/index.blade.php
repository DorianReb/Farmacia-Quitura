@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .section-title{
            font-weight:800;letter-spacing:.04em;
            /* Usa tu variable (elige la que tengas definida) */ /* <-- si usas CSS custom property */
            /* color: $azul-marino; */     /* <-- si usas SCSS */
        }
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}

    </style>

    <div class="container-xxl">

        {{-- Encabezado + búsqueda (arriba) + agregar (abajo) --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-industry"></i>
                    <h1 class="h4 m-0">Marcas</h1>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Botón Agregar (izquierda, solo ícono + tooltip) --}}
            <button type="button"
                    class="btn btn-success btn-icon shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#createModal"
                    data-bs-placement="right"
                    title="Agregar nuevo">
                <i class="fa-solid fa-plus"></i>
            </button>
            

            {{-- Buscador (derecha) --}}
            <form method="GET" action="{{ route('marca.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar…">
                    @if(request('q'))
                        <a href="{{ route('marca.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
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
                        @forelse($marcas as $marca)
                            <tr>
                                <td class="fw-semibold">{{ $marca->nombre }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $marca->id }}"
                                                title="Editar" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('marca.destroy', $marca->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta marca?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                    type="submit"
                                                    title="Eliminar" data-bs-toggle="tooltip" data-bs-placement="top">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>

                            {{-- Modal de edición --}}
                            @include('marca.edit', ['marca' => $marca])
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No hay marcas registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINACIÓN CORREGIDA --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                {{-- Información de resultados --}}
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $marcas->firstItem() ?? 0 }}–{{ $marcas->lastItem() ?? 0 }} de {{ $marcas->total() }}
                    @if(request('q'))• Filtro: "{{ request('q') }}"@endif
                </small>

                {{-- Paginación --}}
                @if($marcas->hasPages())
                    <nav aria-label="Paginación de marcas" class="order-1 order-md-2">
                        {{ $marcas->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de creación (archivo separado) --}}
    @include('marca.create')
    @if ($errors->any() && session('from_modal') === 'edit_marca' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif

@endsection
