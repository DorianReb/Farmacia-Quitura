<div class="modal fade" id="editUnidadModal{{ $unidad->id }}" tabindex="-1"
     aria-labelledby="editUnidadLabel{{ $unidad->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editUnidadLabel{{ $unidad->id }}">
                    <i class="fa-solid fa-ruler me-1"></i> Editar unidad de medida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('unidad_medida.update', $unidad->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nombre_unidad_{{ $unidad->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre_unidad_{{ $unidad->id }}"
                               name="nombre"
                               value="{{ old('nombre', $unidad->nombre) }}"
                               placeholder="Ej. ml, mg, g, tabletas"
                               maxlength="200"
                               required>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
