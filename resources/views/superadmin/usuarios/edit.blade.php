{{-- resources/views/superadmin/usuarios/edit.blade.php --}}
<div class="modal fade" id="editUsuarioModal{{ $usuario->id }}" tabindex="-1"
     aria-labelledby="editUsuarioLabel{{ $usuario->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editUsuarioLabel{{ $usuario->id }}">
                    <i class="fa-solid fa-user-gear me-1"></i> Editar rol y estado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Errores (solo para este usuario) --}}
                @if ($errors->any() && session('from_modal') === 'edit_usuario' && session('edit_id') == $usuario->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('superadmin.usuarios.update', $usuario->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- Flags de control --}}
                    <input type="hidden" name="from_modal" value="edit_usuario">
                    <input type="hidden" name="edit_id" value="{{ $usuario->id }}">

                    {{-- Nombre visible (solo referencia) --}}
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="{{ $usuario->nombre_completo }}" disabled>
                    </div>

                    {{-- Correo visible (solo referencia) --}}
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="text" class="form-control" value="{{ $usuario->correo }}" disabled>
                    </div>

                    {{-- Rol --}}
                    <div class="mb-3">
                        <label for="rol_{{ $usuario->id }}" class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="rol"
                                id="rol_{{ $usuario->id }}"
                                class="form-select @if(session('edit_id') == $usuario->id) @error('rol') is-invalid @enderror @endif"
                                required>
                            <option value="">Selecciona un rol</option>
                            <option value="Administrador" {{ old('rol', $usuario->rol) == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                            <option value="Vendedor" {{ old('rol', $usuario->rol) == 'Vendedor' ? 'selected' : '' }}>Vendedor</option>
                        </select>
                        @if(session('edit_id') == $usuario->id)
                            @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    {{-- Estado --}}
                    <div class="mb-3">
                        <label for="estado_{{ $usuario->id }}" class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado"
                                id="estado_{{ $usuario->id }}"
                                class="form-select @if(session('edit_id') == $usuario->id) @error('estado') is-invalid @enderror @endif"
                                required>
                            <option value="">Selecciona estado</option>
                            <option value="Activo" {{ old('estado', $usuario->estado) == 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Pendiente" {{ old('estado', $usuario->estado) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="Rechazado" {{ old('estado', $usuario->estado) == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                        @if(session('edit_id') == $usuario->id)
                            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-check"></i> Actualizar
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
