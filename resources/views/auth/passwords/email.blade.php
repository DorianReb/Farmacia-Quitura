<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Recuperar contraseña | {{ config('app.name','Farmacia Quitura') }}</title>

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
                Recuperar contraseña
            </h1>

            {{-- Mensaje de estado (por ejemplo, “te hemos enviado el enlace…”) --}}
            @if (session('status'))
                <div class="alert alert-info w-100" style="max-width: 520px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Tarjeta del formulario --}}
            <div class="w-100" style="max-width: 520px;">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-azul-marino text-white fw-semibold">
                        Ingrese su correo para recibir el enlace de restablecimiento
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('password.email') }}" autocomplete="off">
                            @csrf

                            {{-- IMPORTANTE: aquí usamos "correo" porque tu ForgotPasswordController
                                 valida 'correo' y lo mapea internamente a 'email'. --}}
                            <div class="mb-3">
                                <label for="correo" class="form-label fw-semibold">
                                    *Correo electrónico
                                </label>
                                <input
                                    id="correo"
                                    type="email"
                                    name="correo"
                                    class="form-control @error('correo') is-invalid @enderror"
                                    value="{{ old('correo') }}"
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

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-azul-marino px-5">
                                    Enviar enlace
                                </button>
                            </div>

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
