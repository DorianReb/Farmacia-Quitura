<div class="modal fade" id="editPresentacionModal{{ $presentacion->id }}" tabindex="-1"
     aria-labelledby="editPresentacionLabel{{ $presentacion->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPresentacionLabel{{ $presentacion->id }}">
                    <i class="fa-solid fa-box-open me-1"></i> Editar presentación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'edit_presentacion' && session('edit_id') == $presentacion->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('presentacion.update', $presentacion->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_presentacion">
                    <input type="hidden" name="edit_id" value="{{ $presentacion->id }}">

                    <div class="mb-3">
                        <label for="nombre_presentacion_{{ $presentacion->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @if(session('edit_id') == $presentacion->id) @error('nombre') is-invalid @enderror @endif"
                               id="nombre_presentacion_{{ $presentacion->id }}"
                               name="nombre"
                               value="{{ old('nombre', $presentacion->nombre) }}"
                               placeholder="Ej. Caja, Blíster, Frasco, Tubo"
                               maxlength="200"
                               required>
                        @if(session('edit_id') == $presentacion->id)
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

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
