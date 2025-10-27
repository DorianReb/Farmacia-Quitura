@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .section-title{
            font-weight:800;letter-spacing:.04em;
        }
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
    </style>

    <div class="container-xxl">

        {{-- Encabezado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-tag"></i>
                    <h1 class="h4 m-0">Promociones</h1>
                </div>
            </div>
        </div>

        {{-- Botón agregar y buscador --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Botón agregar --}}
            <button type="button"
                    class="btn btn-success btn-icon shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#createPromocionModal"
                    data-bs-placement="right"
                    title="Agregar nueva promoción">
                <i class="fa-solid fa-plus"></i>
            </button>

            {{-- Buscador --}}
            <form method="GET" action="{{ route('promocion.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar…">
                    @if(request('q'))
                        <a href="{{ route('promocion.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
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
                            <th>Porcentaje</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Autorizada por</th>
                            <th class="text-end" style="width:220px">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($promociones as $promocion)
                            <tr>
                                <td class="fw-semibold">{{ $promocion->porcentaje }}%</td>
                                <td>{{ $promocion->fecha_inicio }}</td>
                                <td>{{ $promocion->fecha_fin }}</td>
                                <td>{{ $promocion->usuario->nombre_completo ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPromocionModal{{ $promocion->id }}"
                                                title="Editar" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('promocion.destroy', $promocion->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta promoción?')">
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
                            @include('promocion.edit', ['promocion' => $promocion])
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay promociones registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $promociones->firstItem() ?? 0 }}–{{ $promociones->lastItem() ?? 0 }} de {{ $promociones->total() }}
                    @if(request('q'))• Filtro: "{{ request('q') }}"@endif
                </small>

                @if($promociones->hasPages())
                    <nav aria-label="Paginación de promociones" class="order-1 order-md-2">
                        {{ $promociones->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de creación --}}
    @include('promocion.create')

    {{-- Abrir modal de edición si hay errores --}}
    @if ($errors->any() && session('from_modal') === 'edit_promocion' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editPromocionModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif

@endsection
