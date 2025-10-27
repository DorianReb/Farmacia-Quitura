<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cuenta pendiente | {{ config('app.name','Farmacia Quitura') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Tus assets --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>

{{-- Barra lateral azul marino --}}
<div class="position-fixed top-0 start-0 h-100 bg-azul-marino" style="width: 160px;"></div>

{{-- Contenido principal --}}
<div class="container-fluid" style="margin-left: 160px;">
    <div class="row min-vh-100">
        <div class="col-12 d-flex flex-column justify-content-center align-items-center text-center">

            {{-- Icono de reloj o alerta (opcional si tienes FontAwesome) --}}
            <div class="mb-4">
                <i class="fa-solid fa-hourglass-half fa-4x text-secondary"></i>
            </div>

            {{-- Mensaje principal --}}
            <h1 class="display-5 fw-semibold text-dark mb-3">Tu cuenta está en revisión</h1>

            <p class="fs-5 text-muted mb-4" style="max-width: 480px;">
                Tu registro ha sido recibido correctamente.
                Un <span class="fw-semibold text-dark">superadministrador</span> revisará tu solicitud y aprobará tu acceso al sistema.
            </p>

            {{-- Botón para regresar al inicio de sesión --}}
            <a href="{{ route('login') }}" class="btn btn-azul-marino px-5 py-2 mt-3">
                Volver al inicio de sesión
            </a>

        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
