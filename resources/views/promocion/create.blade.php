<div class="modal fade" id="createPromocionModal" tabindex="-1" aria-labelledby="createPromocionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createPromocionLabel">
                    <i class="fa-solid fa-tag me-1"></i> Crear Promoción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Mensaje de errores --}}
                @if ($errors->any() && session('from_modal') === 'create_promocion')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('promocion.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_promocion">

                    {{-- Porcentaje --}}
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">Porcentaje <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control @error('porcentaje') is-invalid @enderror"
                               id="porcentaje"
                               name="porcentaje"
                               value="{{ old('porcentaje') }}"
                               placeholder="Ej. 10"
                               min="0" max="100"
                               required>
                        @error('porcentaje')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('fecha_inicio') is-invalid @enderror"
                               id="fecha_inicio"
                               name="fecha_inicio"
                               value="{{ old('fecha_inicio') }}"
                               required>
                        @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha fin --}}
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de fin <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('fecha_fin') is-invalid @enderror"
                               id="fecha_fin"
                               name="fecha_fin"
                               value="{{ old('fecha_fin') }}"
                               required>
                        @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Autorizada por --}}
                    <div class="mb-3">
                        <label for="autorizada_por" class="form-label">Autorizada por <span class="text-danger">*</span></label>
                        <select name="autorizada_por" id="autorizada_por" class="form-select @error('autorizada_por') is-invalid @enderror" required>
                            <option value="">Selecciona un usuario</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" {{ old('autorizada_por', $promocion->autorizada_por ?? '') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        @error('autorizada_por')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Footer --}}
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
