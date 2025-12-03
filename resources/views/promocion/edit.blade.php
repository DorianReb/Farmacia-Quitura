<div class="modal fade" id="editPromocionModal{{ $promocion->id }}" tabindex="-1" aria-labelledby="editPromocionLabel{{ $promocion->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPromocionLabel{{ $promocion->id }}">
                    <i class="fa-regular fa-pen-to-square me-1"></i> Editar Promoción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Mensaje de errores --}}
                @if ($errors->any() && session('from_modal') === 'edit_promocion' && session('edit_id') == $promocion->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('promocion.update', $promocion->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_promocion">
                    <input type="hidden" name="edit_id" value="{{ $promocion->id }}">

                    {{-- Porcentaje --}}
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">
                            Porcentaje <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control @error('porcentaje') is-invalid @enderror"
                               id="porcentaje"
                               name="porcentaje"
                               value="{{ old('porcentaje') }}"
                               placeholder="Ej. 10"
                               min="10" max="40" step="1.00"
                               required>
                        <div class="form-text text-muted">El porcentaje debe estar entre 10% y 40%</div>
                        @error('porcentaje')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- Fecha inicio --}}
                    <div class="mb-3">
                        <label for="fecha_inicio{{ $promocion->id }}" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="fecha_inicio{{ $promocion->id }}"
                            name="fecha_inicio"
                            class="form-control js-date-promo-inicio @error('fecha_inicio') is-invalid @enderror"
                            value="{{ old('fecha_inicio', \Carbon\Carbon::parse($promocion->fecha_inicio)->format('d-m-Y')) }}"
                            readonly
                            required>
                        @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha fin --}}
                    <div class="mb-3">
                        <label for="fecha_fin{{ $promocion->id }}" class="form-label">Fecha de fin <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="fecha_fin{{ $promocion->id }}"
                            name="fecha_fin"
                            class="form-control js-date-promo-fin @error('fecha_fin') is-invalid @enderror"
                            value="{{ old('fecha_fin', \Carbon\Carbon::parse($promocion->fecha_fin)->format('d-m-Y')) }}"
                            readonly
                            required>
                        @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Autorizada por (solo lectura) --}}
                    <div class="mb-3">
                        <label class="form-label">Autorizada por</label>
                        <input type="text" class="form-control"
                               value="{{ $promocion->usuario?->nombre_completo ?? '—' }}" readonly>
                        <small class="text-muted">
                            Al guardar, se actualizará automáticamente con tu usuario actual.
                        </small>
                    </div>

                    {{-- (Opcional) Mantener el id actual como hidden, aunque update() lo reescribe con Auth::id() --}}
                    <input type="hidden" name="autorizada_por" value="{{ $promocion->autorizada_por }}">



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
