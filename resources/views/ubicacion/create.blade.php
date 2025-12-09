{{-- resources/views/ubicacion/create.blade.php --}}
@php
    // ID del modal, por si lo pasas desde la vista padre
    $modalId = $id ?? 'createUbicacionModal';
@endphp

<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-labelledby="{{ $modalId }}Label"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header del modal --}}
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fa-solid fa-location-dot me-1"></i> Asignar Ubicación
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Mensaje de errores --}}
                @if ($errors->any() && session('from_modal') === 'create_ubicacion')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario --}}
                <form action="{{ route('ubicacion.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_ubicacion">

                    {{-- Producto --}}
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">
                            Producto <span class="text-danger">*</span>
                        </label>
                        <select name="producto_id"
                                id="producto_id"
                                class="form-select @error('producto_id') is-invalid @enderror"
                                required>
                            <option value="">Selecciona un producto...</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}"
                                    {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->resumen ?? $producto->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- Nivel --}}
                    <div class="mb-3">
                        <label for="nivel_id" class="form-label">
                            Nivel <span class="text-danger">*</span>
                        </label>
                        <select name="nivel_id"
                                id="nivel_id"
                                class="form-select @error('nivel_id') is-invalid @enderror"
                                required>
                            <option value="">Selecciona un nivel...</option>
                            @foreach($niveles as $nivel)
                                @php
                                    $pas = $nivel->pasillo->codigo ?? '—';
                                @endphp
                                <option value="{{ $nivel->id }}"
                                    {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $pas }} – Nivel {{ $nivel->numero }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Footer con acciones --}}
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-check"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

{{-- Abrir modal automáticamente si hay errores --}}
@if ($errors->any() && session('from_modal') === 'create_ubicacion')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById(@json($modalId));
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif
