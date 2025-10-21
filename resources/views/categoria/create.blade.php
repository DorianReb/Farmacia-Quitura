<div class="modal fade" id="createCategoriaModal" tabindex="-1" aria-labelledby="createCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createCategoriaLabel">
                    <i class="fa-solid fa-tags me-1"></i> Crear categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'create_categoria')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('categoria.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_categoria">

                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre_categoria"
                               name="nombre"
                               value="{{ old('nombre') }}"
                               placeholder="Ej. Analgésicos"
                               maxlength="200"
                               required
                               autofocus>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Usa un nombre único y claro. Máximo 200 caracteres.</div>
                    </div>

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
