@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        .estado-activo{color:#198754;font-weight:600;}
        .estado-pendiente{color:#ffc107;font-weight:600;}
        .estado-rechazado{color:#dc3545;font-weight:600;}
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{ background:var(--bs-success); border-color:var(--bs-success); }
    </style>

    <div class="container-xxl">

        {{-- === ENCABEZADO === --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-user-clock"></i>
                    <h1 class="h4 m-0">Solicitudes de Acceso</h1>
                </div>
            </div>
        </div>

        {{-- === BUSCADOR === --}}
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="{{ route('superadmin.solicitudes.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre o correo…">
                    @if(request('q'))
                        <a href="{{ route('superadmin.solicitudes.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" title="Buscar">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- === ALERTAS === --}}
        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @elseif(session('error'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-danger shadow-sm mb-3">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- === TABLA PRINCIPAL === --}}
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Rol solicitado</th>
                            <th>Fecha de solicitud</th>
                            <th>Estado</th>
                            <th class="text-end" style="width:220px">Acciones</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($solicitudes as $usuario)
                            <tr>
                                {{-- Nombre --}}
                                <td class="fw-semibold">{{ $usuario->nombre_completo }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>{{ $usuario->rol }}</td>

                                <td>{{ $usuario->created_at->diffForHumans() }}</td>



                                {{-- Estado visual --}}
                                <td>
                                    @switch($usuario->estado)
                                        @case('Activo')
                                            <span class="estado-activo"><i class="fa-solid fa-circle me-1"></i>Activo</span>
                                            @break
                                        @case('Pendiente')
                                            <span class="estado-pendiente"><i class="fa-solid fa-hourglass-half me-1"></i>Pendiente</span>
                                            @break
                                        @case('Rechazado')
                                            <span class="estado-rechazado"><i class="fa-solid fa-ban me-1"></i>Rechazado</span>
                                            @break
                                        @default
                                            <span class="text-muted">Desconocido</span>
                                    @endswitch
                                </td>

                                {{-- === BOTONES DE ACCIÓN === --}}
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">

                                        {{-- Aprobar --}}
                                        @if($usuario->estado === 'Pendiente')
                                            <form action="{{ route('superadmin.solicitudes.aprobar', $usuario->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-success shadow-sm rounded-pill btn-icon"
                                                        title="Aprobar solicitud">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>

                                            {{-- Rechazar --}}
                                            <form action="{{ route('superadmin.solicitudes.rechazar', $usuario->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                        title="Rechazar solicitud">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Eliminar (solo si no está pendiente) --}}
                                        @if($usuario->estado !== 'Pendiente')
                                            <form action="{{ route('superadmin.solicitudes.destroy', $usuario->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar esta solicitud definitivamente?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger shadow-sm rounded-pill btn-icon"
                                                        title="Eliminar registro">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay solicitudes registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- === PAGINACIÓN === --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $solicitudes->firstItem() ?? 0 }}–{{ $solicitudes->lastItem() ?? 0 }} de {{ $solicitudes->total() }}
                    @if(request('q')) • Filtro: “{{ request('q') }}” @endif
                </small>

                @if($solicitudes->hasPages())
                    <nav aria-label="Paginación de solicitudes" class="order-1 order-md-2">
                        {{ $solicitudes->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>

    </div>
@endsection
