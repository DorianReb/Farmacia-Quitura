<div class="modal fade" id="createAsignaComponenteModal" tabindex="-1" aria-labelledby="createAsignaComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createAsignaComponenteLabel">
                    <i class="fa-solid fa-diagram-project me-1"></i> Nueva asignación de componente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && session('from_modal') === 'create_asigna_componente')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('asigna_componentes.store') }}" method="POST" autocomplete="off" class="row g-3">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_asigna_componente">

                    {{-- Producto (nombre comercial) --}}
                    <div class="col-12 col-md-6">
                        <label for="producto_id_create" class="form-label">Producto (nombre comercial) <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id_create"
                                class="form-select @error('producto_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Selecciona un producto…</option>
                            @foreach($productos as $p)
                                <option value="{{ $p->id }}" {{ old('producto_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Componente (nombre científico) --}}
                    <div class="col-12 col-md-6">
                        <label for="nombre_cientifico_id_create" class="form-label">Componente (nombre científico) <span class="text-danger">*</span></label>
                        <select name="nombre_cientifico_id" id="nombre_cientifico_id_create"
                                class="form-select @error('nombre_cientifico_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Selecciona un componente…</option>
                            @foreach($componentes as $c)
                                <option value="{{ $c->id }}" {{ old('nombre_cientifico_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('nombre_cientifico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Fuerza --}}
                    <div class="col-6 col-md-3">
                        <label for="fuerza_cantidad_create" class="form-label">Fuerza (cantidad) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('fuerza_cantidad') is-invalid @enderror"
                               id="fuerza_cantidad_create" name="fuerza_cantidad"
                               value="{{ old('fuerza_cantidad') }}" placeholder="Ej. 500" required>
                        @error('fuerza_cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fuerza_unidad_id_create" class="form-label">Fuerza (unidad) <span class="text-danger">*</span></label>
                        <select name="fuerza_unidad_id" id="fuerza_unidad_id_create"
                                class="form-select @error('fuerza_unidad_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Selecciona…</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('fuerza_unidad_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('fuerza_unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Base --}}
                    <div class="col-6 col-md-3">
                        <label for="base_cantidad_create" class="form-label">Base (cantidad) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('base_cantidad') is-invalid @enderror"
                               id="base_cantidad_create" name="base_cantidad"
                               value="{{ old('base_cantidad') }}" placeholder="Ej. 5" required>
                        @error('base_cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="base_unidad_id_create" class="form-label">Base (unidad) <span class="text-danger">*</span></label>
                        <select name="base_unidad_id" id="base_unidad_id_create"
                                class="form-select @error('base_unidad_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Selecciona…</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('base_unidad_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('base_unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
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
