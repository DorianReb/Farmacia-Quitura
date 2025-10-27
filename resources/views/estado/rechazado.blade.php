<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cuenta rechazada | {{ config('app.name','Farmacia Quitura') }}</title>

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

            {{-- Icono de rechazo --}}
            <div class="mb-4">
                <i class="fa-solid fa-circle-xmark fa-4x text-danger"></i>
            </div>

            {{-- Mensaje principal --}}
            <h1 class="display-5 fw-semibold text-dark mb-3">Tu cuenta fue rechazada</h1>

            <p class="fs-5 text-muted mb-4" style="max-width: 480px;">
                Lamentablemente, tu solicitud no ha sido aprobada.
                Si consideras que se trata de un error, por favor comunícate con un
                <span class="fw-semibold text-dark">superadministrador</span> o con el soporte del sistema.
            </p>

            {{-- Botón para regresar al login --}}
            <a href="{{ route('login') }}" class="btn btn-outline-danger px-5 py-2 mt-3">
                Volver al inicio de sesión
            </a>

        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
