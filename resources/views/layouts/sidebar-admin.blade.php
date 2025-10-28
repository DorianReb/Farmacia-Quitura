<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <title>{{ config('app.name', 'Farmacia Quitura') }}</title>

    {{-- Tu compilación (SCSS + JS) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Ajustes visuales del sidebar */
        .sidebar {
            width: 260px;
        }
        .sidebar .nav-link {
            display: flex; align-items: center; gap: .65rem;
            font-weight: 600; padding: .65rem .75rem; border-radius: .5rem;
            color: rgba(255,255,255,.9);
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .sidebar .section-title {
            font-size: .75rem; letter-spacing: .06em; opacity: .85;
            text-transform: uppercase; margin-top: .75rem;
        }
        .sidebar .user-name { font-weight: 800; }
        .sidebar .user-role { opacity:.9; margin-top:-.35rem; }
        .sidebar hr {
            border-color: rgba(255,255,255,.25);
            opacity: .7;
        }
        .content-wrap {
            min-height: 100vh;
            background: #f7f8fb;
        }

        /* Chevron animado en el sidebar */
        .sidebar .chev{
            display:inline-flex;
            transition:transform .2s ease;
        }
        /* Cuando el colapsable está abierto, gira 90° hacia abajo */
        .sidebar a.nav-link[aria-expanded="true"] .chev{
            transform:rotate(90deg);
        }

    </style>
</head>
<body>
@php
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $nombreCompleto = $user?->nombre_completo ?? trim(($user->nombre ?? '').' '.($user->apellido_paterno ?? '').' '.($user->apellido_materno ?? ''));
    $rol = $user?->rol;

    // Permisos de inventario
    $canInventory        = in_array($rol, ['Superadmin','Administrador','Vendedor']);
    $canManageInventory  = in_array($rol, ['Superadmin','Administrador']);

    // Rutas activas (para abrir el acordeón correctamente)
    $isInventarioRoute = request()->routeIs(
        'producto.*','lote.*','asigna_componentes.*','ubicacion.*'
    );

@endphp

<div class="d-flex">
    {{-- Sidebar izquierdo --}}
    <aside class="sidebar bg-azul-marino text-white min-vh-100 d-flex flex-column p-3">
        {{-- Header usuario --}}
        <div class="mb-4">
            <div class="user-name">{{ $nombreCompleto ?: 'Usuario' }}</div>
            <div class="user-role small">
                {{ $user?->rol ?? 'Sin rol' }}
            </div>
        </div>

        {{-- === BOTÓN DASHBOARD (solo Superadmin y Administrador) === --}}
        @if(in_array($user?->rol, ['Superadmin','Administrador']))
            <div class="mb-3">
                <a
                    href="{{ url('/home') }}"
                    class="btn w-100 rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm
                        {{ request()->is('home') ? 'btn-light text-dark' : 'btn-outline-light' }}"
                    aria-label="Ir al Dashboard"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </div>
        @endif

        {{-- === MENÚ LATERAL === --}}
        <nav class="flex-grow-1">
            <ul id="sidebarMenu" class="nav nav-pills flex-column gap-1">

                {{-- ================== VENTAS (igual que ya lo tienes) ================== --}}
                @php
                    $isVentasRoute = request()->routeIs('venta.index') || request()->routeIs('venta.historial');
                @endphp
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isVentasRoute ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" data-bs-target="#menuVentas"
                       aria-expanded="{{ $isVentasRoute ? 'true' : 'false' }}" aria-controls="menuVentas" href="javascript:void(0)">
    <span class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-dollar-sign"></i><span>Ventas</span>
    </span>
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                    <div id="menuVentas" class="collapse {{ $isVentasRoute ? 'show' : '' }} ms-2" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column mt-1">
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('venta.index') ? 'active' : '' }}" href="{{ route('venta.index') }}"><i class="fa-solid fa-cash-register"></i><span class="ms-2">Realizar venta</span></a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('venta.historial') ? 'active' : '' }}" href="{{ route('venta.historial') }}"><i class="fa-regular fa-clock"></i><span class="ms-2">Historial de Transacciones</span></a></li>
                        </ul>
                    </div>
                </li>

                {{-- ===================== INVENTARIO (Vendedor + Admin + Superadmin) ===================== --}}
                @php
                    $canInventory = in_array($rol, ['Vendedor','Administrador','Superadmin']);
                    $isInventarioRoute = request()->routeIs('producto.*','lote.*','asigna_componentes.*','ubicacion.*');
                @endphp

                @if($canInventory)
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isInventarioRoute ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse" data-bs-target="#menuInventario"
                           aria-expanded="{{ $isInventarioRoute ? 'true' : 'false' }}"
                           aria-controls="menuInventario" href="javascript:void(0)">
    <span class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-bag-shopping"></i><span>Inventario</span>
    </span>
                            <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>

                        <div id="menuInventario" class="collapse {{ $isInventarioRoute ? 'show' : '' }} ms-2" data-bs-parent="#sidebarMenu">
                            <ul class="nav flex-column mt-1">
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('producto.*') ? 'active' : '' }}" href="{{ route('producto.index') }}"><i class="fa-solid fa-pills"></i><span class="ms-2">Productos</span></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('lote.*') ? 'active' : '' }}" href="{{ route('lote.index') }}"><i class="fa-solid fa-box"></i><span class="ms-2">Lotes</span></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('asigna_componentes.*') ? 'active' : '' }}" href="{{ route('asigna_componentes.index') }}"><i class="fa-solid fa-diagram-project"></i><span class="ms-2">Asignar componentes</span></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('ubicacion.*') ? 'active' : '' }}" href="{{ route('ubicacion.index') }}"><i class="fa-solid fa-location-dot"></i><span class="ms-2">Ubicaciones</span></a></li>
                            </ul>
                        </div>
                    </li>
                @endif


                {{-- ================== SOLO SUPERADMIN / ADMIN ================== --}}
                @if(in_array($user?->rol, ['Superadmin','Administrador']))


                    {{-- Promociones --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('promocion.*') ? 'active' : '' }}"
                           href="{{ route('promocion.index') }}">
                            <i class="fa-solid fa-tag"></i>
                            <span>Promociones</span>
                        </a>
                    </li>

                    {{-- Catálogos --}}
                    @php
                        $isCatalogRoute = request()->routeIs(
                            'marca.*','formaFarmaceutica.*','presentacion.*',
                            'unidad_medida.*','categoria.*','nombreCientifico.*'
                        );
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isCatalogRoute ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           data-bs-target="#menuCatalogos"
                           aria-expanded="{{ $isCatalogRoute ? 'true' : 'false' }}"
                           aria-controls="menuCatalogos"
                           href="javascript:void(0)">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-barcode"></i>
                                <span>Catálogos</span>
                            </span>
                            <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>

                        <div id="menuCatalogos" class="collapse {{ $isCatalogRoute ? 'show' : '' }} ms-2" data-bs-parent="#sidebarMenu">
                            <ul class="nav flex-column mt-1">
                                <li class="nav-item"><a class="nav-link" href="{{ route('marca.index') }}"><i class="fa-solid fa-industry"></i><span class="ms-2">Marcas</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('formaFarmaceutica.index') }}"><i class="fa-solid fa-capsules"></i><span class="ms-2">Formas farmacéuticas</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('presentacion.index') }}"><i class="fa-solid fa-box-open"></i><span class="ms-2">Presentaciones</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('unidad_medida.index') }}"><i class="fa-solid fa-ruler"></i><span class="ms-2">Unidades de medida</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('categoria.index') }}"><i class="fa-solid fa-tags"></i><span class="ms-2">Categorías</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('nombreCientifico.index') }}"><i class="fa-solid fa-dna"></i><span class="ms-2">Nombres científicos</span></a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Reportes --}}
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center collapsed"
                           data-bs-toggle="collapse"
                           data-bs-target="#menuReportes"
                           aria-expanded="false"
                           aria-controls="menuReportes"
                           href="javascript:void(0)">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-chart-pie"></i><span>Reportes</span>
                            </span>
                            <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>

                        <div id="menuReportes" class="collapse ms-2" data-bs-parent="#sidebarMenu">
                            <ul class="nav flex-column mt-1">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-line"></i><span class="ms-2">Rentabilidad</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-trophy"></i><span class="ms-2">Ranking</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-calendar-xmark"></i><span class="ms-2">Caducidad</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-triangle-exclamation"></i><span class="ms-2">Stock Bajo</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-ban"></i><span class="ms-2">Sin Ventas</span></a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Administración (solo Superadmin) --}}
                    @if($user->rol === 'Superadmin')
                        @php
                            $isAdminRoute = request()->routeIs('usuarios.*','solicitudes.*');
                        @endphp

                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center {{ $isAdminRoute ? '' : 'collapsed' }}"
                               data-bs-toggle="collapse"
                               data-bs-target="#menuAdmin"
                               aria-expanded="{{ $isAdminRoute ? 'true' : 'false' }}"
                               aria-controls="menuAdmin"
                               href="javascript:void(0)">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-users"></i>
                                    <span>Administración</span>
                                </span>
                                <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                            </a>

                            <div id="menuAdmin" class="collapse {{ $isAdminRoute ? 'show' : '' }} ms-2" data-bs-parent="#sidebarMenu">
                                <ul class="nav flex-column mt-1">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                                           href="{{ route('superadmin.usuarios.index') }}">
                                            <i class="fa-solid fa-user-gear"></i>
                                            <span class="ms-2">Usuarios</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('solicitudes.*') ? 'active' : '' }}"
                                           href="{{ route('superadmin.solicitudes.index') }}">
                                            <i class="fa-solid fa-user-plus"></i>
                                            <span class="ms-2">Solicitudes de acceso</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                @endif
            </ul>
        </nav>

        {{-- === SEPARADOR + SALIR === --}}
        <div class="mt-3">
            <hr>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-outline-light d-flex align-items-center gap-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Salir</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <main class="flex-grow-1 content-wrap p-4">
        @yield('content')
    </main>
</div>

    @stack('scripts')
    @stack('modals')


</body>
</html>
