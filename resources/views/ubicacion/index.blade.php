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
        .table thead {
            background: #002b5b;
            color: white;
        }
        .btn-sm {
            padding: .25rem .5rem !important;
            line-height: 1;
        }
        .btn-icon{
            padding:.45rem .6rem;
            border-radius:999px;
            display:inline-flex;
            align-items:center;
            justify-content:center
        }
        .pagination{gap:.25rem;}
        .pagination .page-link{padding:.25rem .55rem;font-size:.85rem;border:0;}
        .pagination .page-item.active .page-link{
            background:var(--bs-success);
            border-color:var(--bs-success);
        }
    </style>

    @php
        $rol = Auth::user()->rol ?? null;
        $isAdmin = in_array($rol, ['Administrador','Superadmin']);
    @endphp

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

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-danger shadow-sm mb-3">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- PASILLOS + NIVELES --}}
        <div class="row g-3 mb-4">

            {{-- PASILLOS --}}
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header text-dark py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h6 m-0 section-title text-uppercase">Pasillos</h2>

                            {{-- Buscador específico de pasillos --}}
                            <form method="GET"
                                  action="{{ route('ubicacion.index') }}"
                                  class="d-none d-sm-block"
                                  style="min-width: 200px;">
                                <input type="hidden" name="q_nivel" value="{{ request('q_nivel') }}">
                                <input type="hidden" name="q_ubicacion" value="{{ request('q_ubicacion') }}">

                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="search"
                                           name="q_pasillo"
                                           value="{{ request('q_pasillo') }}"
                                           class="form-control"
                                           placeholder="Buscar pasillo…">
                                    @if(request('q_pasillo'))
                                        <a href="{{ route('ubicacion.index', [
                                                'q_nivel' => request('q_nivel'),
                                                'q_ubicacion' => request('q_ubicacion'),
                                            ]) }}"
                                           class="btn btn-outline-secondary btn-sm"
                                           title="Limpiar">
                                            <i class="fa-regular fa-circle-xmark"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            @if($isAdmin)
                                <button type="button"
                                        class="btn btn-sm btn-success rounded-pill shadow-sm ms-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createPasilloModal">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle m-0">
                                <thead class="bg-azul-marino text-white">
                                <tr>
                                    <th>Código</th>
                                    @if($isAdmin)
                                        <th class="text-end" style="width: 35%;">Acciones</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($pasillos as $pasillo)
                                    <tr>
                                        <td>{{ $pasillo->codigo }}</td>
                                        @if($isAdmin)
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
                                                        <button class="btn btn-danger btn-sm rounded-pill shadow-sm"
                                                                type="submit" title="Eliminar">
                                                            <i class="fa-regular fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 2 : 1 }}"
                                            class="text-center py-2 text-muted">
                                            No hay pasillos.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-2">
                            @if($pasillos->lastPage() > 1)
                                {{ $pasillos->appends([
                                    'q_nivel'     => request('q_nivel'),
                                    'q_ubicacion' => request('q_ubicacion'),
                                ])->links('pagination::bootstrap-5') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- NIVELES --}}
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header text-dark py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h6 m-0 section-title text-uppercase">Niveles</h2>

                            {{-- Buscador específico de niveles --}}
                            <form method="GET"
                                  action="{{ route('ubicacion.index') }}"
                                  class="d-none d-sm-block"
                                  style="min-width: 200px;">
                                <input type="hidden" name="q_pasillo" value="{{ request('q_pasillo') }}">
                                <input type="hidden" name="q_ubicacion" value="{{ request('q_ubicacion') }}">

                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="search"
                                           name="q_nivel"
                                           value="{{ request('q_nivel') }}"
                                           class="form-control"
                                           placeholder="Buscar nivel…">
                                    @if(request('q_nivel'))
                                        <a href="{{ route('ubicacion.index', [
                                                'q_pasillo' => request('q_pasillo'),
                                                'q_ubicacion' => request('q_ubicacion'),
                                            ]) }}"
                                           class="btn btn-outline-secondary btn-sm"
                                           title="Limpiar">
                                            <i class="fa-regular fa-circle-xmark"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            @if($isAdmin)
                                <button type="button"
                                        class="btn btn-sm btn-success rounded-pill shadow-sm ms-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createNivelModal">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle m-0">
                                <thead class="bg-azul-marino text-white">
                                <tr>
                                    <th>Pasillo</th>
                                    <th>Nivel</th>
                                    @if($isAdmin)
                                        <th class="text-end" style="width: 35%;">Acciones</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($niveles as $nivel)
                                    <tr>
                                        <td>{{ $nivel->pasillo->codigo ?? '—' }}</td>
                                        <td>{{ $nivel->numero }}</td>
                                        @if($isAdmin)
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
                                                        <button class="btn btn-danger btn-sm rounded-pill shadow-sm"
                                                                type="submit" title="Eliminar">
                                                            <i class="fa-regular fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 3 : 2 }}"
                                            class="text-center py-2 text-muted">
                                            No hay niveles.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-2">
                            @if($niveles->lastPage() > 1)
                                {{ $niveles->appends([
                                    'q_pasillo'   => request('q_pasillo'),
                                    'q_ubicacion' => request('q_ubicacion'),
                                ])->links('pagination::bootstrap-5') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- BOTÓN AGREGAR UBICACIÓN + BUSCADOR ASIGNACIONES --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if($isAdmin)
                    <button type="button"
                            class="btn btn-success shadow-sm rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#createUbicacionModal"
                            title="Agregar nueva asignación">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                @endif
            </div>

            <form method="GET"
                  action="{{ route('ubicacion.index') }}"
                  class="d-inline-block"
                  style="min-width:260px;max-width:420px;width:100%;">
                <input type="hidden" name="q_pasillo" value="{{ request('q_pasillo') }}">
                <input type="hidden" name="q_nivel" value="{{ request('q_nivel') }}">

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="search"
                           name="q_ubicacion"
                           value="{{ request('q_ubicacion') }}"
                           class="form-control"
                           placeholder="Buscar asignaciones (producto / nivel / pasillo)…">
                    @if(request('q_ubicacion'))
                        <a href="{{ route('ubicacion.index', [
                                'q_pasillo' => request('q_pasillo'),
                                'q_nivel'   => request('q_nivel'),
                            ]) }}"
                           class="btn btn-outline-secondary"
                           title="Limpiar">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" title="Buscar">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- TABLA PRINCIPAL DE UBICACIONES --}}
        <div class="card card-soft">
            <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
                <h2 class="h6 m-0 section-title text-uppercase">Asignaciones de Ubicación</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Producto</th>
                            <th>Pasillo / Nivel</th>
                            @if($isAdmin)
                                <th class="text-end" style="width:120px;">Acciones</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ubicaciones as $ubicacion)
                            @php
                                $producto = $ubicacion->producto;
                                $productoTexto = $producto
                                    ? ($producto->resumen ?? $producto->nombre_comercial ?? '—')
                                    : '—';

                                $nivel = $ubicacion->nivel;
                                $pasillo = $nivel?->pasillo;
                            @endphp
                            <tr>
                                <td>{{ $productoTexto }}</td>
                                <td>
                                    @if($nivel)
                                        {{ $pasillo->codigo ?? '—' }} – Nivel {{ $nivel->numero }}
                                    @else
                                        —
                                    @endif
                                </td>
                                @if($isAdmin)
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-warning btn-sm rounded-pill shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModalUbicacion{{ $ubicacion->id }}"
                                                    title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('ubicacion.destroy', $ubicacion->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar esta asignación?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm rounded-pill shadow-sm"
                                                        type="submit" title="Eliminar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 3 : 2 }}"
                                    class="text-center py-4 text-muted">
                                    No hay asignaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="p-2">
                        {{ $ubicaciones->appends([
                            'q_pasillo' => request('q_pasillo'),
                            'q_nivel'   => request('q_nivel'),
                        ])->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('modals')
        @if($isAdmin)
            @include('pasillo.create',   ['id' => 'createPasilloModal'])
            @include('nivel.create',     ['id' => 'createNivelModal', 'pasillos' => $pasillos])
            @include('ubicacion.create', ['id' => 'createUbicacionModal', 'productos' => $productos, 'niveles' => $niveles, 'pasillos' => $pasillos])

            @foreach($pasillos as $pasillo)
                @include('pasillo.edit', ['pasillo' => $pasillo, 'id' => 'editPasilloModal'.$pasillo->id])
            @endforeach

            @foreach($niveles as $nivel)
                @include('nivel.edit', ['nivel' => $nivel, 'id' => 'editNivelModal'.$nivel->id, 'pasillos' => $pasillos])
            @endforeach

            @foreach($ubicaciones as $ubicacion)
                @include('ubicacion.edit', [
                    'ubicacion' => $ubicacion,
                    'id'        => 'editModalUbicacion'.$ubicacion->id,
                    'productos' => $productos,
                    'niveles'   => $niveles,
                    'pasillos'  => $pasillos
                ])
            @endforeach
        @endif
    @endpush

@endsection
