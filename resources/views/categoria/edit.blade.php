<div class="modal fade" id="editCategoriaModal{{ $categoria->id }}" tabindex="-1"
     aria-labelledby="editCategoriaLabel{{ $categoria->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editCategoriaLabel{{ $categoria->id }}">
                    <i class="fa-solid fa-tags me-1"></i> Editar categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'edit_categoria' && session('edit_id') == $categoria->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('categoria.update', $categoria->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_categoria">
                    <input type="hidden" name="edit_id" value="{{ $categoria->id }}">

                    <div class="mb-3">
                        <label for="nombre_categoria_{{ $categoria->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @if(session('edit_id') == $categoria->id) @error('nombre') is-invalid @enderror @endif"
                               id="nombre_categoria_{{ $categoria->id }}"
                               name="nombre"
                               value="{{ old('nombre', $categoria->nombre) }}"
                               placeholder="Ej. Analgésicos"
                               maxlength="200"
                               required>
                        @if(session('edit_id') == $categoria->id)
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
