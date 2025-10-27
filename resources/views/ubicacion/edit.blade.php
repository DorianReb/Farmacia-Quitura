<div class="modal fade" id="editModalUbicacion{{ $ubicacion->id }}" tabindex="-1" aria-labelledby="editUbicacionLabel{{ $ubicacion->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editUbicacionLabel{{ $ubicacion->id }}">
                    <i class="fa-regular fa-pen-to-square me-1"></i> Editar Asignación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Errores --}}
                @if ($errors->any() && session('from_modal') === 'edit_ubicacion' && session('edit_id') == $ubicacion->id)
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
                <form action="{{ route('ubicacion.update', $ubicacion->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_ubicacion">
                    <input type="hidden" name="edit_id" value="{{ $ubicacion->id }}">

                    {{-- Producto --}}
                    <div class="mb-3">
                        <label for="producto_id_{{ $ubicacion->id }}" class="form-label">Producto <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id_{{ $ubicacion->id }}" class="form-select @error('producto_id') is-invalid @enderror" required>
                            <option value="">Selecciona un producto...</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" {{ old('producto_id', $ubicacion->producto_id) == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nivel --}}
                    <div class="mb-3">
                        <label for="nivel_id_{{ $ubicacion->id }}" class="form-label">Nivel <span class="text-danger">*</span></label>
                        <select name="nivel_id" id="nivel_id_{{ $ubicacion->id }}" class="form-select @error('nivel_id') is-invalid @enderror" required>
                            <option value="">Selecciona un nivel...</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}" {{ old('nivel_id', $ubicacion->nivel_id) == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-check"></i> Guardar cambios
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
@if ($errors->any() && session('from_modal') === 'edit_ubicacion' && session('edit_id') == $ubicacion->id)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('editModalUbicacion{{ $ubicacion->id }}');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif
