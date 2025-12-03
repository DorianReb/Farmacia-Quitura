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
                        <label for="fecha_inicio" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="form-control js-date-promo-inicio @error('fecha_inicio') is-invalid @enderror"
                            value="{{ old('fecha_inicio') }}"
                            readonly
                            required>
                        @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha fin --}}
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de fin <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="form-control js-date-promo-fin @error('fecha_fin') is-invalid @enderror"
                            value="{{ old('fecha_fin') }}"
                            readonly
                            required>
                        @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- Autorizada por (informativo) --}}
                    <div class="mb-3">
                        <label class="form-label">Autorizada por</label>
                        <input type="text" class="form-control"
                               value="{{ Auth::user()->nombre_completo ?? Auth::user()->email }}" readonly>
                        <small class="text-muted">Se asignará automáticamente al crear.</small>
                    </div>


                    {{-- Enviado al servidor (oculto) --}}
                        <input type="hidden" name="autorizada_por" value="{{ auth()->id() }}">

                        <div class="form-text">Este valor se toma automáticamente del usuario con sesión activa.</div>
                        @error('autorizada_por')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inicio = document.getElementById('fecha_inicio');
            const fin = document.getElementById('fecha_fin');

            inicio.addEventListener('change', () => {
                fin.min = inicio.value;  // 👈 fecha mínima igual a inicio
            });
        });
    </script>
@endpush

