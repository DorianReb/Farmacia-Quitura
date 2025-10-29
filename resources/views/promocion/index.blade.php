@extends('layouts.sidebar-admin')

@section('content')
<style>
    .card-soft {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(16, 24, 40, .06);
    }
    .section-title {
        font-weight: 800;
        letter-spacing: .04em;
    }
    .table-hover tbody tr:hover {
        background: #f7f9ff;
    }
    .table thead {
        background: #002b5b; /* azul marino */
        color: white;
    }
    .btn-sm {
        padding: .25rem .5rem !important;
        line-height: 1;
    }
</style>

<div class="container-xxl">
    @php
        use Carbon\Carbon;
        $fmtFecha = function($v) {
            return $v ? Carbon::parse($v)->format('d/m/y') : '—';
        };
    @endphp


    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-12 text-center">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-tag"></i>
                <h1 class="h4 m-0">Promociones</h1>
            </div>
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="d-flex justify-content-end align-items-center mb-4">
        <form method="GET" action="{{ route('promocion.index') }}" class="d-flex" style="min-width:300px; max-width:480px; width:100%;">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar…">
                @if(request('q'))
                    <a href="{{ route('promocion.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </a>
                @endif
                <button class="btn btn-success" title="Buscar"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>

    {{-- TABLA DE PROMOCIONES --}}
    <div class="card card-soft mb-4">
        <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
            <h2 class="h6 m-0 section-title text-uppercase">Promociones</h2>
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#createPromocionModal">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>%</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Autorizada por</th>
                            <th class="text-end" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promociones as $promo)
                            <tr>
                                <td>{{ number_format($promo->porcentaje, 2) }}%</td>
                                <td>{{ $fmtFecha($promo->fecha_inicio) }}</td>
                                <td>{{ $fmtFecha($promo->fecha_fin) }}</td>
                                <td>{{ $promo->usuario->nombre_completo ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPromocionModal{{ $promo->id }}"
                                                title="Editar">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('promocion.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta promoción?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm rounded-pill shadow-sm" type="submit">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No hay promociones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-2">
                {{ $promociones->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    {{-- TABLA DE ASIGNACIONES --}}
    <div class="card card-soft mb-4">
        <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
            <h2 class="h6 m-0 section-title text-uppercase">Asignaciones de Promoción</h2>
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#createAsignaModal">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Producto</th>
                            <th>% Promoción</th>
                            <th class="text-end" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asigna)
                            <tr>
                                <td>{{ $asigna->lote->codigo ?? '—' }}</td>
                                <td>{{ $asigna->lote->producto->nombre_comercial ?? '—' }}</td>
                                <td>{{ $asigna->promocion->porcentaje ?? '—' }}%</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editAsignaModal{{ $asigna->id }}"
                                                title="Editar">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('asignapromocion.destroy', $asigna->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta asignación?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm rounded-pill shadow-sm" type="submit">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay asignaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-2">
                {{ $asignaciones->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

{{-- MODALES PROMOCIONES --}}
@include('promocion.create', ['id' => 'createPromocionModal', 'usuarios' => $usuarios])
@foreach($promociones as $promo)
    @include('promocion.edit', ['id' => 'editPromocionModal'.$promo->id, 'promocion' => $promo, 'usuarios' => $usuarios])
@endforeach

{{-- MODALES ASIGNACIONES --}}
@include('asignapromocion.create', [
    'id' => 'createAsignaModal',
    'lotes' => $lotes ?? collect(),
    'promociones' => $promociones_all ?? collect()
])

@foreach($asignaciones as $asigna)
    @include('asignapromocion.edit', [
        'id' => 'editAsignaModal'.$asigna->id,
        'lotes' => $lotes,
        'promociones' => $promociones_all,
        'asigna' => $asigna
    ])
@endforeach

@endsection
