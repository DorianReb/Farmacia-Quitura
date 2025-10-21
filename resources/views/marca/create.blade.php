{{-- resources/views/marca/create.blade.php --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createMarcaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header del modal (estilo similar a tu ejemplo) --}}
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createMarcaLabel">
                    <i class="fa-solid fa-industry me-1"></i> Crear Marca
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Mensaje de errores (opcional) --}}
                @if ($errors->any() && session('from_modal') === 'create_marca')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marca.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_marca">

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="nombre_marca" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('nombre') is-invalid @enderror"
                            id="nombre_marca"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Genfar"
                            required
                            autofocus
                            maxlength="200">
                        @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="ayudaNombreMarca" class="form-text">
                            Usa un nombre único y claro. Máximo 200 caracteres.
                        </div>
                    </div>

                    {{-- Footer con acciones --}}
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
