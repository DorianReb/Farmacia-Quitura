{{-- resources/views/marca/edit.blade.php --}}
<div class="modal fade" id="editModal{{ $marca->id }}" tabindex="-1" aria-labelledby="editMarcaLabel{{ $marca->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editMarcaLabel{{ $marca->id }}">
                    <i class="fa-solid fa-industry me-1"></i> Editar marca
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Errores del form (solo cuando falló esta misma marca) --}}
                @if ($errors->any() && session('from_modal') === 'edit_marca' && session('edit_id') == $marca->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marca.update', $marca->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_marca">
                    <input type="hidden" name="edit_id" value="{{ $marca->id }}">

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="nombre_marca_{{ $marca->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @if(session('edit_id') == $marca->id) @error('nombre') is-invalid @enderror @endif"
                            id="nombre_marca_{{ $marca->id }}"
                            name="nombre"
                            value="{{ old('nombre', $marca->nombre) }}"
                            placeholder="Ej. Genfar"
                            maxlength="200"
                            required
                        >
                        @if(session('edit_id') == $marca->id)
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
