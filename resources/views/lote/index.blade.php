@extends('layouts.sidebar-admin')

@section('content')
<style>
    .card-soft {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(16,24,40,.06);
    }
    .section-title {
        font-weight: 800;
        letter-spacing: .04em;
    }
    .table-hover tbody tr:hover {
        background: #f7f9ff;
    }
    .btn-icon {
        padding: .45rem .6rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@php
    $rol = Auth::user()->rol ?? null;
@endphp

<div class="container-xxl">

    {{-- ENCABEZADO --}}
    <div class="row mb-2">
        <div class="col-12 text-center">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-boxes-stacked"></i>
                <h1 class="h4 m-0">Lotes</h1>
            </div>
        </div>
    </div>

    {{-- BOTÓN AGREGAR + BUSCADOR --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        {{-- Botón Agregar --}}
        <button type="button"
                class="btn btn-success btn-icon shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#createModal"
                title="Agregar nuevo lote">
            <i class="fa-solid fa-plus"></i>
        </button>

        {{-- Buscador --}}
        <form method="GET" action="{{ route('lote.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por código o producto…">
                @if(request('q'))
                    <a href="{{ route('lote.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </a>
                @endif
                <button class="btn btn-success" title="Buscar">
                    <i class="fa-solid fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- ALERTA DE ÉXITO --}}
    @if(session('success'))
        <div class="row">
            <div class="col-lg-6 col-xl-5">
                <div class="alert alert-success shadow-sm mb-3">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    {{-- TABLA DE LOTES --}}
    <div class="card card-soft">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="width:120px;">Fecha caducidad</th> {{-- Reducido --}}
                            <th style="width:100px;">Cantidad</th>         {{-- Reducido --}}
                            <th>Precio compra</th>
                            <th>Fecha entrada</th>
                            <th style="width:200px;">Registrado por</th>  {{-- Ampliado --}}
                            <th class="text-end" style="width:220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $lote)
                            <tr>
                                <td class="fw-semibold">{{ $lote->codigo }}</td>
                                <td>{{ $lote->producto->nombre_comercial ?? '—' }}</td>
                                <td>{{ $lote->fecha_caducidad ?? '—' }}</td>
                                <td>{{ $lote->cantidad ?? '0' }}</td>
                                <td>${{ number_format($lote->precio_compra ?? 0, 2) }}</td>
                                <td>{{ $lote->fecha_entrada ?? '—' }}</td>
                                <td>{{ $lote->usuario->nombre_completo ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $lote->id }}"
                                                title="Editar" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        {{-- Eliminar: SOLO Admin + Superadmin (mismo estilo que Edit) --}}
                                        @if(in_array($rol, ['Administrador','Superadmin']))
                                            <form action="{{ route('lote.destroy', $lote) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar lote?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger shadow-sm rounded-pill btn-icon ms-1"
                                                        type="submit"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Eliminar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No hay lotes registrados.</td>
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
                Mostrando {{ $lotes->firstItem() ?? 0 }}–{{ $lotes->lastItem() ?? 0 }} de {{ $lotes->total() }}
                @if(request('q')) • Filtro: "{{ request('q') }}" @endif
            </small>

            @if($lotes->hasPages())
                <nav aria-label="Paginación de lotes" class="order-1 order-md-2">
                    {{ $lotes->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                </nav>
            @endif
        </div>
    </div>
</div>

{{-- Modal de creación --}}
@include('lote.create')

{{-- Modales de edición --}}
@foreach($lotes as $lote)
    @include('lote.edit', ['lote' => $lote])
@endforeach

@if ($errors->any() && session('from_modal') === 'edit_lote' && session('edit_id'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('editModal{{ session('edit_id') }}');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif
@endsection
