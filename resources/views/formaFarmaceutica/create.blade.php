<div class="modal fade" id="createFormaModal" tabindex="-1" aria-labelledby="createFormaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createFormaLabel">
                    <i class="fa-solid fa-capsules me-1"></i> Crear forma farmacéutica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'create_forma')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('formaFarmaceutica.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_forma">

                    <div class="mb-3">
                        <label for="nombre_forma" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre_forma"
                               name="nombre"
                               value="{{ old('nombre') }}"
                               placeholder="Ej. Tableta, Cápsula, Jarabe"
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
