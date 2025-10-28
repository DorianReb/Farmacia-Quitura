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

    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-12 text-center">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-location-dot"></i>
                <h1 class="h4 m-0">Asignación de Ubicaciones</h1>
            </div>
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="d-flex justify-content-end align-items-center mb-4">
        <form method="GET" action="{{ route('ubicacion.index') }}"
              class="d-flex" style="min-width: 300px; max-width: 480px; width: 100%;">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar…">
                @if(request('q'))
                    <a href="{{ route('ubicacion.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </a>
                @endif
                <button class="btn btn-success" title="Buscar"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>

    {{-- TABLAS DE PASILLOS Y NIVELES --}}
    <div class="row g-3 mb-4">

        {{-- PASILLOS --}}
        <div class="col-md-6">
            <div class="card card-soft h-100">
                <div class="card-header bg-azul-marino text-white d-flex justify-content-between align-items-center py-2">
                    <h2 class="h6 m-0 section-title text-uppercase">Pasillos</h2>
                    <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#createPasilloModal">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">ID</th>
                                    <th>Código</th>
                                    <th class="text-end" style="width: 35%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pasillos as $pasillo)
                                    <tr>
                                        <td class="fw-semibold">{{ $pasillo->id }}</td>
                                        <td>{{ $pasillo->nombre }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPasilloModal{{ $pasillo->id }}"
                                                        title="Editar">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <form action="{{ route('pasillo.destroy', $pasillo->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('¿Eliminar este pasillo?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm rounded-pill shadow-sm" type="submit">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-2 text-muted">No hay pasillos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- NIVELES --}}
        <div class="col-md-6">
            <div class="card card-soft h-100">
                <div class="card-header bg-azul-marino text-white d-flex justify-content-between align-items-center py-2">
                    <h2 class="h6 m-0 section-title text-uppercase">Niveles</h2>
                    <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#createNivelModal">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">ID</th>
                                    <th>Nombre</th>
                                    <th class="text-end" style="width: 35%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($niveles as $nivel)
                                    <tr>
                                        <td class="fw-semibold">{{ $nivel->id }}</td>
                                        <td>{{ $nivel->nombre }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editNivelModal{{ $nivel->id }}"
                                                        title="Editar">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <form action="{{ route('nivel.destroy', $nivel->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('¿Eliminar este nivel?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm rounded-pill shadow-sm" type="submit">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-2 text-muted">No hay niveles.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- BOTÓN AGREGAR UBICACIÓN --}}
    <div class="d-flex justify-content-start mb-3">
        <button type="button" class="btn btn-success shadow-sm rounded-pill"
                data-bs-toggle="modal" data-bs-target="#createModalUbicacion" title="Agregar nueva asignación">
            <i class="fa-solid fa-plus"></i> Agregar Ubicación
        </button>
    </div>

    {{-- TABLA PRINCIPAL DE UBICACIONES --}}
    <div class="card card-soft">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Nivel</th>
                            <th class="text-end" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ubicaciones as $ubicacion)
                            <tr>
                                <td>{{ $ubicacion->producto->nombre_comercial ?? '—' }}</td>
                                <td>{{ $ubicacion->nivel->nombre ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModalUbicacion{{ $ubicacion->id }}"
                                                title="Editar">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('ubicacion.destroy', $ubicacion->id) }}"
                                              method="POST" onsubmit="return confirm('¿Eliminar esta asignación?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm rounded-pill shadow-sm" type="submit" title="Eliminar">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No hay asignaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- MODALES DE CREACIÓN --}}
@include('pasillo.create', ['id' => 'createPasilloModal'])
@include('nivel.create', ['id' => 'createNivelModal', 'pasillos' => $pasillos])
@include('ubicacion.create', ['id' => 'createUbicacionModal', 'productos' => $productos])

{{-- MODALES DE EDICIÓN --}}
@if(isset($pasillos))
    @foreach($pasillos as $pasillo)
        @include('pasillo.edit', ['pasillo' => $pasillo, 'id' => 'editPasilloModal'.$pasillo->id])
    @endforeach
@endif

@if(isset($niveles))
    @foreach($niveles as $nivel)
        @include('nivel.edit', ['nivel' => $nivel, 'id' => 'editNivelModal'.$nivel->id, 'pasillos' => $pasillos])
    @endforeach
@endif

@if(isset($ubicaciones))
    @foreach($ubicaciones as $ubicacion)
        @include('ubicacion.edit', [
            'ubicacion' => $ubicacion, 
            'id' => 'editModalUbicacion'.$ubicacion->id,
            'productos' => $productos,
            'niveles' => $niveles,
            'pasillos' => $pasillos
        ])
    @endforeach
@endif

@endsection
