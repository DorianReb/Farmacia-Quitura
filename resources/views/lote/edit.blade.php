<div class="modal fade" id="editModal{{ $lote->id }}" tabindex="-1" aria-labelledby="editLoteLabel{{ $lote->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            {{-- Header del modal --}}
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editLoteLabel{{ $lote->id }}">
                    <i class="fa-regular fa-pen-to-square me-1"></i> Editar Lote
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Mensaje de errores --}}
                @if ($errors->any() && session('from_modal') === 'edit_lote' && session('edit_id') == $lote->id)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('lote.update', $lote->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_lote">
                    <input type="hidden" name="edit_id" value="{{ $lote->id }}">

                    {{-- Producto --}}
                    <div class="mb-3">
                        <label for="producto_id_{{ $lote->id }}" class="form-label">Producto <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id_{{ $lote->id }}" class="form-select @error('producto_id') is-invalid @enderror" required>
                            <option value="">Selecciona un producto...</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" {{ old('producto_id', $lote->producto_id) == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Código --}}
                    <div class="mb-3">
                        <label for="codigo_{{ $lote->id }}" class="form-label">Código <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="codigo"
                            id="codigo_{{ $lote->id }}"
                            class="form-control @error('codigo') is-invalid @enderror"
                            value="{{ old('codigo', $lote->codigo) }}"
                            maxlength="100"
                            required>
                        @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha de caducidad --}}
                    <div class="mb-3">
                        <label for="fecha_caducidad_{{ $lote->id }}" class="form-label">Fecha de caducidad</label>
                        <input
                            type="date"
                            name="fecha_caducidad"
                            id="fecha_caducidad_{{ $lote->id }}"
                            class="form-control @error('fecha_caducidad') is-invalid @enderror"
                            value="{{ old('fecha_caducidad', $lote->fecha_caducidad) }}">
                        @error('fecha_caducidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Precio de compra --}}
                    <div class="mb-3">
                        <label for="precio_compra_{{ $lote->id }}" class="form-label">Precio de compra ($)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="precio_compra"
                            id="precio_compra_{{ $lote->id }}"
                            class="form-control @error('precio_compra') is-invalid @enderror"
                            value="{{ old('precio_compra', $lote->precio_compra) }}">
                        @error('precio_compra')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="mb-3">
                        <label for="cantidad_{{ $lote->id }}" class="form-label">Cantidad</label>
                        <input
                            type="number"
                            min="0"
                            name="cantidad"
                            id="cantidad_{{ $lote->id }}"
                            class="form-control @error('cantidad') is-invalid @enderror"
                            value="{{ old('cantidad', $lote->cantidad) }}">
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha de entrada --}}
                    <div class="mb-3">
                        <label for="fecha_entrada_{{ $lote->id }}" class="form-label">Fecha de entrada</label>
                        <input
                            type="date"
                            name="fecha_entrada"
                            id="fecha_entrada_{{ $lote->id }}"
                            class="form-control @error('fecha_entrada') is-invalid @enderror"
                            value="{{ old('fecha_entrada', $lote->fecha_entrada) }}">
                        @error('fecha_entrada')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning text-white">
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
