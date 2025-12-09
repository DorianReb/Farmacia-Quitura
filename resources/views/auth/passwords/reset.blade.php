<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Restablecer contraseña | {{ config('app.name','Farmacia Quitura') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Tus assets (bg-azul-marino, btn-azul-marino, etc.) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>

{{-- Barra lateral azul (decorativa, fija) --}}
<div class="position-fixed top-0 start-0 h-100 bg-azul-marino" style="width: 160px;"></div>

{{-- Contenido principal desplazado a la derecha de la barra --}}
<div class="container-fluid" style="margin-left: 160px;">
    <div class="row min-vh-100">
        <div class="col-12 d-flex flex-column justify-content-center align-items-center">

            {{-- Título principal --}}
            <h1 class="display-5 fw-semibold mb-4 text-dark">
                Restablecer contraseña
            </h1>

            {{-- Mensajes de estado (por ejemplo, enlace inválido, etc.) --}}
            @if (session('status'))
                <div class="alert alert-info w-100" style="max-width: 520px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Tarjeta del formulario --}}
            <div class="w-100" style="max-width: 520px;">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-azul-marino text-white fw-semibold">
                        Ingrese los datos para restablecer su contraseña
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
                            @csrf

                            {{-- Token de restablecimiento --}}
                            <input type="hidden" name="token" value="{{ $token }}">

                            {{-- Correo electrónico (campo "correo") --}}
                            <div class="mb-3">
                                <label for="correo" class="form-label fw-semibold">
                                    *Correo electrónico
                                </label>
                                <input
                                    id="correo"
                                    type="email"
                                    name="correo"
                                    class="form-control @error('correo') is-invalid @enderror"
                                    value="{{ old('correo', $email ?? '') }}"
                                    required
                                    autocomplete="email"
                                    autofocus
                                    placeholder="Ingrese su correo electrónico"
                                >
                                @error('correo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Nueva contraseña (campo "contrasena") --}}
                            <div class="mb-3">
                                <label for="contrasena" class="form-label fw-semibold">
                                    *Nueva contraseña
                                </label>
                                <input
                                    id="contrasena"
                                    type="password"
                                    name="contrasena"
                                    class="form-control @error('contrasena') is-invalid @enderror"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ingrese su nueva contraseña"
                                >
                                @error('contrasena')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Confirmación de contraseña (contrasena_confirmation) --}}
                            <div class="mb-4">
                                <label for="contrasena_confirmation" class="form-label fw-semibold">
                                    *Confirmar contraseña
                                </label>
                                <input
                                    id="contrasena_confirmation"
                                    type="password"
                                    name="contrasena_confirmation"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirme su nueva contraseña"
                                >
                            </div>

                            {{-- Botón --}}
                            <div class="text-center">
                                <button type="submit" class="btn btn-azul-marino px-5">
                                    Restablecer contraseña
                                </button>
                            </div>

                            {{-- Enlace para volver al login --}}
                            <div class="text-center mt-3">
                                <a href="{{ route('login') }}" class="text-decoration-none">
                                    Volver a iniciar sesión
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div> {{-- col --}}
    </div> {{-- row --}}
</div> {{-- container-fluid --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>

</body>
</html>
