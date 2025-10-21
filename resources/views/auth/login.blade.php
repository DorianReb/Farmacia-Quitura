<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Iniciar sesión | {{ config('app.name','Farmacia Quitura') }}</title>

    {{-- Favicon (ajusta si usas otros archivos) --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap (si ya lo cargas con Vite, puedes quitar el CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Tus assets (colores, utilidades, clases bg-azul-marino / btn-azul-marino) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>

{{-- Barra lateral azul (decorativa, fija) --}}
<div class="position-fixed top-0 start-0 h-100 bg-azul-marino" style="width: 160px;"></div>

{{-- Contenido principal desplazado a la derecha de la barra --}}
<div class="container-fluid" style="margin-left: 160px;">
    <div class="row min-vh-100">
        <div class="col-12 d-flex flex-column justify-content-center align-items-center">

            <h1 class="display-3 fw-semibold mb-5 text-dark">Bienvenido</h1>

            @if (session('status'))
                <div class="alert alert-info w-100" style="max-width: 520px;">
                    {{ session('status') }}
                </div>
            @endif

            <div class="w-100" style="max-width: 520px;">

                {{-- ======== FORM LOGIN (SOLO ESTE FORM) ======== --}}
                <form method="POST" action="{{ route('login') }}" autocomplete="off" class="px-2">
                    @csrf

                    {{-- Correo --}}
                    <div class="mb-4">
                        <label for="correo" class="form-label fw-semibold">*Correo electrónico</label>
                        <input
                            id="correo"
                            type="email"
                            name="correo"
                            value="{{ old('correo') }}"
                            class="form-control @error('correo') is-invalid @enderror"
                            placeholder="Address email"
                            required
                            autocomplete="username"
                            autofocus
                        >
                        @error('correo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-4">
                        <label for="contrasena" class="form-label fw-semibold">*Contraseña</label>
                        <input
                            id="contrasena"
                            type="password"
                            name="contrasena"
                            class="form-control @error('contrasena') is-invalid @enderror"
                            placeholder="Password"
                            required
                            autocomplete="current-password"
                        >
                        @error('contrasena')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botón iniciar sesión --}}
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-azul-marino px-5">
                            Iniciar Sesión
                        </button>
                    </div>

                    {{-- Link recuperar contraseña --}}
                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                            <a class="text-decoration-none" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                </form>
                {{-- ======== FIN FORM LOGIN ======== --}}

                {{-- Botón que abre el modal de registro (fuera del form de login) --}}
                @if (Route::has('register'))
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-success px-4"
                                data-bs-toggle="modal" data-bs-target="#registerModal">
                            Crear cuenta
                        </button>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- ======== MODAL DE REGISTRO (FUERA DE CUALQUIER FORM) ======== --}}
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title fw-bold" id="registerModalLabel">Crear cuenta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="{{ route('register') }}" autocomplete="off" novalidate>
                @csrf
                {{-- Identificador para reabrir el modal si hay errores --}}
                <input type="hidden" name="_form" value="register">

                <div class="modal-body">
                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="reg_nombre" class="form-label fw-semibold">*Nombre</label>
                        <input id="reg_nombre" type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}" required>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Apellido paterno --}}
                    <div class="mb-3">
                        <label for="reg_apellido_paterno" class="form-label fw-semibold">*Apellido paterno</label>
                        <input id="reg_apellido_paterno" type="text" name="apellido_paterno"
                               class="form-control @error('apellido_paterno') is-invalid @enderror"
                               value="{{ old('apellido_paterno') }}" required>
                        @error('apellido_paterno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Apellido materno (opcional) --}}
                    <div class="mb-3">
                        <label for="reg_apellido_materno" class="form-label fw-semibold">Apellido materno (opcional)</label>
                        <input id="reg_apellido_materno" type="text" name="apellido_materno"
                               class="form-control @error('apellido_materno') is-invalid @enderror"
                               value="{{ old('apellido_materno') }}">
                        @error('apellido_materno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Correo --}}
                    <div class="mb-3">
                        <label for="reg_correo" class="form-label fw-semibold">*Correo electrónico</label>
                        <input id="reg_correo" type="email" name="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo') }}" required autocomplete="username">
                        @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Rol --}}
                    <div class="mb-3">
                        <label for="reg_rol" class="form-label fw-semibold">*Rol</label>
                        <select id="reg_rol" name="rol"
                                class="form-select @error('rol') is-invalid @enderror" required>
                            <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Selecciona un rol</option>
                            <option value="Administrador" {{ old('rol') === 'Administrador' ? 'selected' : '' }}>Administrador</option>
                            <option value="Vendedor" {{ old('rol') === 'Vendedor' ? 'selected' : '' }}>Vendedor</option>
                        </select>
                        @error('rol') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <label for="reg_contrasena" class="form-label fw-semibold">*Contraseña</label>
                        <input id="reg_contrasena" type="password" name="contrasena"
                               class="form-control @error('contrasena') is-invalid @enderror"
                               required autocomplete="new-password">
                        @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Confirmación --}}
                    <div class="mb-2">
                        <label for="reg_contrasena_confirmation" class="form-label fw-semibold">*Confirmar contraseña</label>
                        <input id="reg_contrasena_confirmation" type="password" name="contrasena_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-user-plus me-1"></i> Crear cuenta
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reabrir el modal si hubo errores al registrar --}}
@if ($errors->any() && old('_form') === 'register')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        });
    </script>
@endif

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
