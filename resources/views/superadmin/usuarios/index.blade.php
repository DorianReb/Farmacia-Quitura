@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .btn-icon{padding:.45rem .6rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center}
        .estado-activo{color:#198754;font-weight:600;}
        .estado-pendiente{color:#ffc107;font-weight:600;}
        .estado-rechazado{color:#dc3545;font-weight:600;}
    </style>

    <div class="container-xxl">
        {{-- Encabezado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-user-gear"></i>
                    <h1 class="h4 m-0">Gestión de Usuarios</h1>
                </div>
            </div>
        </div>

        {{-- Botón crear + Buscador --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button"
                    class="btn btn-success btn-icon shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#createUsuarioModal"
                    title="Agregar nuevo usuario">
                <i class="fa-solid fa-plus"></i>
            </button>

            <form method="GET" action="{{ route('superadmin.usuarios.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre o correo…">
                    @if(request('q'))
                        <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" title="Buscar">
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
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-end" style="width:200px">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td class="fw-semibold">{{ $usuario->nombre_completo }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>{{ $usuario->rol }}</td>
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
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUsuarioModal{{ $usuario->id }}"
                                                title="Editar usuario">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        @include('superadmin.usuarios.edit', ['usuario' => $usuario])
                                        {{-- Eliminar --}}
                                        <form action="{{ route('superadmin.usuarios.destroy', $usuario->id ?? 0) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger shadow-sm rounded-pill btn-icon" type="submit" title="Eliminar">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal editar --}}
                            {{-- Puedes crear un include similar al de categorías --}}
                            {{-- @include('superadmin.usuarios.edit', ['usuario' => $usuario]) --}}
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
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
                    Mostrando {{ $usuarios->firstItem() ?? 0 }}–{{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }}
                    @if(request('q')) • Filtro: “{{ request('q') }}” @endif
                </small>

                @if($usuarios->hasPages())
                    <nav aria-label="Paginación de usuarios" class="order-1 order-md-2">
                        {{ $usuarios->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal crear usuario --}}
    {{-- @include('superadmin.usuarios.create') --}}
@endsection
