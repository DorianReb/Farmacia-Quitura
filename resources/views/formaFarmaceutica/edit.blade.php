<div class="modal fade" id="editFormaModal{{ $forma->id }}" tabindex="-1"
     aria-labelledby="editFormaLabel{{ $forma->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editFormaLabel{{ $forma->id }}">
                    <i class="fa-solid fa-capsules me-1"></i> Editar forma farmacéutica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'edit_forma' && session('edit_id') == $forma->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('formaFarmaceutica.update', $forma->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_forma">
                    <input type="hidden" name="edit_id" value="{{ $forma->id }}">

                    <div class="mb-3">
                        <label for="nombre_forma_{{ $forma->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @if(session('edit_id') == $forma->id) @error('nombre') is-invalid @enderror @endif"
                               id="nombre_forma_{{ $forma->id }}"
                               name="nombre"
                               value="{{ old('nombre', $forma->nombre) }}"
                               placeholder="Ej. Tableta, Cápsula, Jarabe"
                               maxlength="200"
                               required>
                        @if(session('edit_id') == $forma->id)
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
