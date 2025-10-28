@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .accordion-button{font-weight:700}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
    </style>

    @php($q = $q ?? '')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-diagram-project"></i>
                <h1 class="h5 m-0">Asignar componentes</h1>
            </div>
            @if($q !== '')
                <span class="badge bg-secondary">Filtro: “{{ $q }}”</span>
            @endif
        </div>

        {{-- Botón abre modal crear (igual que Categorías: success + circular icon-only) --}}
        <button type="button"
                class="btn btn-success btn-icon shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#createAsignaComponenteModal"
                title="Agregar nuevo">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>

    {{-- Buscador: Buscar = success, Limpiar = outline-secondary --}}
    <form class="row g-2 mb-3" method="get" action="{{ route('asigna_componentes.index') }}">
        <div class="col-12 col-md-6">
            <input type="text" name="q" value="{{ $q }}" class="form-control"
                   placeholder="Busca por producto (nombre comercial) o por nombre científico…">
        </div>
        <div class="col-6 col-md-3 d-grid">
            <button class="btn btn-success" data-bs-toggle="tooltip" title="Buscar">
                <i class="fa-solid fa-search"></i> <span class="d-none d-sm-inline">Buscar</span>
            </button>
        </div>
        <div class="col-6 col-md-3 d-grid">
            <a href="{{ route('asigna_componentes.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Limpiar búsqueda">
                <i class="fa-regular fa-circle-xmark"></i> <span class="d-none d-sm-inline">Limpiar</span>
            </a>
        </div>
    </form>

    {{-- Alert éxito --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- Sin datos --}}
    @if(($grupos ?? collect())->isEmpty())
        <div class="alert alert-light border">
            No hay asignaciones registradas @if($q) para “<strong>{{ $q }}</strong>” @endif.
        </div>
    @else
        {{-- Acordeón por Producto (nombre_comercial) --}}
        <div class="accordion" id="accProductos">
            @foreach($grupos as $productoNombre => $asignaciones)
                <div class="accordion-item mb-2 border-0 shadow-sm card-soft">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first && $q === '' ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#p{{ $loop->index }}"
                                aria-expanded="{{ $loop->first && $q === '' ? 'true' : 'false' }}"
                                aria-controls="p{{ $loop->index }}">
                            {{ $productoNombre }}
                            <span class="ms-2 badge bg-primary">{{ $asignaciones->count() }}</span>
                        </button>
                    </h2>

                    <div id="p{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first && $q === '' ? 'show' : '' }}"
                         data-bs-parent="#accProductos">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-azul-marino text-white">
                                    <tr>
                                        <th style="width: 42%">Componente (Nombre científico)</th>
                                        <th style="width: 38%">Fuerza / Base</th>
                                        <th class="text-end" style="width: 20%">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($asignaciones as $row)
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $row->componente->nombre ?? '—' }}
                                            </td>
                                            <td>
                                                {{-- Ejemplo: 500 mg / 5 ml (sin ceros a la derecha) --}}
                                                {{ rtrim(rtrim(number_format($row->fuerza_cantidad, 2, '.', ''), '0'), '.') }}
                                                {{ $row->fuerzaUnidad->nombre ?? '' }}
                                                /
                                                {{ rtrim(rtrim(number_format($row->base_cantidad, 2, '.', ''), '0'), '.') }}
                                                {{ $row->baseUnidad->nombre ?? '' }}
                                            </td>
                                            <td class="text-end">
                                                {{-- Acciones: igual que Categorías (icon-only redondeadas) --}}
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- Editar (modal) --}}
                                                    <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editAsignaComponenteModal{{ $row->id }}"
                                                            title="Editar">
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </button>

                                                    {{-- Eliminar --}}
                                                    <form action="{{ route('asigna_componentes.destroy', $row->id) }}"
                                                          method="post" class="d-inline"
                                                          onsubmit="return confirm('¿Eliminar esta asignación?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger shadow-sm rounded-pill btn-icon" type="submit" title="Eliminar">
                                                            <i class="fa-regular fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Modal de edición (incluido por fila) --}}
                                        @isset($productos, $componentes, $unidades)
                                            @include('asigna_componentes.edit', [
                                                'row' => $row,
                                                'productos' => $productos,
                                                'componentes' => $componentes,
                                                'unidades' => $unidades
                                            ])
                                        @endisset
                                    @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- /table-responsive --}}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal de creación --}}
    @isset($productos, $componentes, $unidades)
        @include('asigna_componentes.create', [
            'productos' => $productos,
            'componentes' => $componentes,
            'unidades' => $unidades
        ])
    @endisset

    {{-- Reabrir modal si hubo errores --}}
    @if ($errors->any())
        @if (session('from_modal') === 'create_asigna_componente')
            <script>
                document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal('#createAsignaComponenteModal').show());
            </script>
        @elseif (session('from_modal') === 'edit_asigna_componente' && session('edit_id'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const el = document.getElementById('editAsignaComponenteModal{{ session('edit_id') }}');
                    if (el) new bootstrap.Modal(el).show();
                });
            </script>
        @endif
    @endif
@endsection