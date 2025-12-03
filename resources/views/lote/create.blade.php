<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createLoteLabel">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Crear Lote
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">
                {{-- ERRORES --}}
                @if ($errors->any() && session('from_modal') === 'create_lote')
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ERROR DEL PROCEDURE --}}
                @if ($errors->has('procedimiento') && session('from_modal') === 'create_lote')
                    <div class="alert alert-danger">
                        <strong>Error del sistema:</strong>
                        {{ $errors->first('procedimiento') }}
                    </div>
                @endif


                {{-- FORMULARIO --}}
                <form action="{{ route('lote.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from_modal" value="create_lote">

                    {{-- PRODUCTO --}}
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">Producto <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id"
                                class="form-select @error('producto_id') is-invalid @enderror"
                                required>
                            <option value="">-- Selecciona un producto --</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CÓDIGO --}}
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código del lote <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('codigo') is-invalid @enderror"
                               id="codigo"
                               name="codigo"
                               value="{{ old('codigo') }}"
                               placeholder="Ej. LOTE-2025-001"
                               required>
                        @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHA DE CADUCIDAD --}}
                    <div class="mb-3">
                        <label for="fecha_caducidad" class="form-label">
                            Fecha de caducidad <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="fecha_caducidad"
                            name="fecha_caducidad"
                            class="form-control js-date-caducidad @error('fecha_caducidad') is-invalid @enderror"
                            value="{{ old('fecha_caducidad') }}"
                            readonly
                            required
                        >

                        @error('fecha_caducidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>



                    {{-- PRECIO DE COMPRA --}}
                    <div class="mb-3">
                        <label for="precio_compra" class="form-label">Precio de compra <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('precio_compra') is-invalid @enderror"
                               id="precio_compra"
                               name="precio_compra"
                               value="{{ old('precio_compra') }}"
                               required>
                        @error('precio_compra')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CANTIDAD --}}
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               id="cantidad"
                               name="cantidad"
                               value="{{ old('cantidad') }}"
                               min="0"
                               required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHA DE ENTRADA (solo informativa) --}}
                    <div class="mb-3">
                        <label class="form-label">Fecha de entrada</label>
                        <input type="text"
                               class="form-control"
                               value="{{ now()->format('Y-m-d H:i') }}"
                               disabled>
                        <div class="form-text">
                            Esta fecha se registra automáticamente al guardar el lote.
                        </div>
                    </div>

                    {{-- FOOTER --}}
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
@php
    $fromModal = old('from_modal') ?? session('from_modal');
@endphp
{{-- ERRORES GENERALES DE VALIDACIÓN --}}
@if ($errors->any() && $fromModal === 'create_lote')
    <div class="alert alert-danger">
        <strong>Revisa los campos:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ERROR DEL PROCEDURE --}}
@if ($errors->has('procedimiento') && $fromModal === 'create_lote')
    <div class="alert alert-danger mt-2">
        <strong>Error del sistema:</strong>
        {{ $errors->first('procedimiento') }}
    </div>
@endif
