<div class="modal fade" id="createPresentacionModal" tabindex="-1" aria-labelledby="createPresentacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createPresentacionLabel">
                    <i class="fa-solid fa-box-open me-1"></i> Crear presentación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'create_presentacion')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('presentacion.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_presentacion">

                    <div class="mb-3">
                        <label for="nombre_presentacion" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre_presentacion"
                               name="nombre"
                               value="{{ old('nombre') }}"
                               placeholder="Ej. Caja, Blíster, Frasco, Tubo"
                               maxlength="200"
                               required
                               autofocus>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Máximo 200 caracteres. Evita duplicados.</div>
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
