{{-- resources/views/auth/_register_modal.blade.php --}}

{{-- Botón de disparo (úsalo donde quieras, p.ej. en tu login) --}}
{{-- <button type="button" class="btn btn-outline-success px-4" data-bs-toggle="modal" data-bs-target="#registerModal">
     Crear cuenta
   </button> --}}

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

                {{-- Registro público: fija el rol a Vendedor --}}
                <input type="hidden" name="rol" value="Vendedor">

                <div class="modal-body">
                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="reg_nombre" class="form-label fw-semibold"><span class="text-danger">*</span>Nombre</label>
                        <input
                            id="reg_nombre"
                            type="text"
                            name="nombre"
                            class="form-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre') }}"
                            required
                        >
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Apellido paterno --}}
                    <div class="mb-3">
                        <label for="reg_apellido_paterno" class="form-label fw-semibold"><span class="text-danger">*</span>Apellido paterno</label>
                        <input
                            id="reg_apellido_paterno"
                            type="text"
                            name="apellido_paterno"
                            class="form-control @error('apellido_paterno') is-invalid @enderror"
                            value="{{ old('apellido_paterno') }}"
                            required
                        >
                        @error('apellido_paterno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Apellido materno --}}
                    <div class="mb-3">
                        <label for="reg_apellido_materno" class="form-label fw-semibold"><span class="text-danger">*</span>Apellido materno</label>
                        <input
                            id="reg_apellido_materno"
                            type="text"
                            name="apellido_materno"
                            class="form-control @error('apellido_materno') is-invalid @enderror"
                            value="{{ old('apellido_materno') }}"
                            required
                        >
                        @error('apellido_materno')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- Correo --}}
                    <div class="mb-3">
                        <label for="reg_correo" class="form-label fw-semibold"><span class="text-danger">*</span>Correo electrónico</label>
                        <input
                            id="reg_correo"
                            type="email"
                            name="correo"
                            class="form-control @error('correo') is-invalid @enderror"
                            value="{{ old('correo') }}"
                            required
                            autocomplete="username"
                        >
                        @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <label for="reg_contrasena" class="form-label fw-semibold"><span class="text-danger">*</span>Contraseña</label>
                        <input
                            id="reg_contrasena"
                            type="password"
                            name="contrasena"
                            class="form-control @error('contrasena') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                        >
                        @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Confirmación --}}
                    <div class="mb-2">
                        <label for="reg_contrasena_confirmation" class="form-label fw-semibold"><span class="text-danger">*</span>Confirmar contraseña</label>
                        <input
                            id="reg_contrasena_confirmation"
                            type="password"
                            name="contrasena_confirmation"
                            class="form-control"
                            required
                            autocomplete="new-password"
                        >
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

{{-- Auto-abrir el modal si hubo errores al registrar --}}
@if ($errors->any() && old('_form') === 'register')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        });
    </script>
@endif
