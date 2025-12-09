<div class="modal fade" id="editProductoModal{{ $producto->id }}" tabindex="-1" aria-labelledby="editProductoModalLabel{{ $producto->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editProductoModalLabel{{ $producto->id }}">Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @php
                    /** @var \Illuminate\Support\Collection $coleccionUnidades */
                    $coleccionUnidades = collect();

                    if (isset($unidadesMed) && $unidadesMed instanceof \Illuminate\Support\Collection) {
                        $coleccionUnidades = $unidadesMed;
                    } elseif (isset($unidades) && $unidades instanceof \Illuminate\Support\Collection) {
                        $coleccionUnidades = $unidades;
                    }
                @endphp

                <form action="{{ route('producto.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page" value="{{ request('page', $productos->currentPage() ?? 1) }}">

                    {{-- Fila 1: Nombre, Marca, Requiere receta --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="nombre_comercial_{{ $producto->id }}" class="form-label">
                                Nombre del producto <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('nombre_comercial') is-invalid @enderror"
                                   id="nombre_comercial_{{ $producto->id }}"
                                   name="nombre_comercial"
                                   value="{{ old('nombre_comercial', $producto->nombre_comercial) }}"
                                   required>
                            @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marca_id_{{ $producto->id }}" class="form-label">
                                Marca <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('marca_id') is-invalid @enderror"
                                    id="marca_id_{{ $producto->id }}"
                                    name="marca_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id }}"
                                        {{ (old('marca_id', $producto->marca_id) == $marca->id) ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marca_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="requiere_receta_{{ $producto->id }}" class="form-label">
                                Requiere receta <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('requiere_receta') is-invalid @enderror"
                                    id="requiere_receta_{{ $producto->id }}"
                                    name="requiere_receta"
                                    required>
                                <option value="">Seleccionar</option>
                                <option value="1" {{ (old('requiere_receta', $producto->requiere_receta) == 1) ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ (old('requiere_receta', $producto->requiere_receta) == 0) ? 'selected' : '' }}>No</option>
                            </select>
                            @error('requiere_receta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Fila 2: Forma, Contenido, Unidad de medida --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="forma_farmaceutica_id_{{ $producto->id }}" class="form-label">
                                Forma del fármaco <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('forma_farmaceutica_id') is-invalid @enderror"
                                    id="forma_farmaceutica_id_{{ $producto->id }}"
                                    name="forma_farmaceutica_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($formas as $forma)
                                    <option value="{{ $forma->id }}"
                                        {{ (old('forma_farmaceutica_id', $producto->forma_farmaceutica_id) == $forma->id) ? 'selected' : '' }}>
                                        {{ $forma->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('forma_farmaceutica_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="contenido_{{ $producto->id }}" class="form-label">
                                Contenido
                            </label>
                            <input type="text"
                                   class="form-control @error('contenido') is-invalid @enderror"
                                   id="contenido_{{ $producto->id }}"
                                   name="contenido"
                                   value="{{ old('contenido', $producto->contenido) }}"
                                   placeholder="Ej. 20 tabletas">
                            @error('contenido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="unidad_medida_id_{{ $producto->id }}" class="form-label">
                                Unidad de medida <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('unidad_medida_id') is-invalid @enderror"
                                    id="unidad_medida_id_{{ $producto->id }}"
                                    name="unidad_medida_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($coleccionUnidades as $u)
                                    <option value="{{ $u->id }}"
                                        {{ (old('unidad_medida_id', $producto->unidad_medida_id) == $u->id) ? 'selected' : '' }}>
                                        {{ $u->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unidad_medida_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Fila 3: Código de barras, Presentación, Categoría --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigo_barras_{{ $producto->id }}" class="form-label">
                                Código de barras
                            </label>
                            <input type="text"
                                   class="form-control @error('codigo_barras') is-invalid @enderror"
                                   id="codigo_barras_{{ $producto->id }}"
                                   name="codigo_barras"
                                   value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                                   placeholder="Código de barras">
                            @error('codigo_barras') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="presentacion_id_{{ $producto->id }}" class="form-label">
                                Presentación <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('presentacion_id') is-invalid @enderror"
                                    id="presentacion_id_{{ $producto->id }}"
                                    name="presentacion_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($presentaciones as $presentacion)
                                    <option value="{{ $presentacion->id }}"
                                        {{ (old('presentacion_id', $producto->presentacion_id) == $presentacion->id) ? 'selected' : '' }}>
                                        {{ $presentacion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('presentacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="categoria_id_{{ $producto->id }}" class="form-label">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('categoria_id') is-invalid @enderror"
                                    id="categoria_id_{{ $producto->id }}"
                                    name="categoria_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}"
                                        {{ (old('categoria_id', $producto->categoria_id) == $categoria->id) ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Fila 4: Precio venta, Stock mínimo, Existencias --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="precio_venta_{{ $producto->id }}" class="form-label">
                                Precio venta <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('precio_venta') is-invalid @enderror"
                                   id="precio_venta_{{ $producto->id }}"
                                   name="precio_venta"
                                   value="{{ old('precio_venta', $producto->precio_venta) }}"
                                   required>
                            @error('precio_venta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="stock_minimo_{{ $producto->id }}" class="form-label">
                                Stock mínimo <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   min="0"
                                   class="form-control @error('stock_minimo') is-invalid @enderror"
                                   id="stock_minimo_{{ $producto->id }}"
                                   name="stock_minimo"
                                   value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                   required>
                            @error('stock_minimo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="existencias_{{ $producto->id }}" class="form-label">
                                Existencias actuales
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="existencias_{{ $producto->id }}"
                                   value="{{ $producto->existencias }}"
                                   disabled>
                            <small class="form-text text-muted">
                                Las existencias se actualizan automáticamente al registrar lotes.
                            </small>
                        </div>
                    </div>

                    {{-- Fila 5: Descripción, Imagen, ALT imagen --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="descripcion_{{ $producto->id }}" class="form-label">
                                Descripción
                            </label>
                            <input type="text"
                                   class="form-control @error('descripcion') is-invalid @enderror"
                                   id="descripcion_{{ $producto->id }}"
                                   name="descripcion"
                                   value="{{ old('descripcion', $producto->descripcion) }}"
                                   placeholder="Descripción breve">
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="imagen_{{ $producto->id }}" class="form-label">
                                Imagen
                            </label>
                            <input type="file"
                                   class="form-control @error('imagen') is-invalid @enderror"
                                   id="imagen_{{ $producto->id }}"
                                   name="imagen">
                            @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="alt_imagen_{{ $producto->id }}" class="form-label">
                                ALT imagen
                            </label>
                            <input type="text"
                                   class="form-control @error('alt_imagen') is-invalid @enderror"
                                   id="alt_imagen_{{ $producto->id }}"
                                   name="alt_imagen"
                                   value="{{ old('alt_imagen', $producto->alt_imagen) }}"
                                   placeholder="Texto alternativo accesible">
                            @error('alt_imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning me-2">
                            <i class="bi bi-pencil-fill"></i> Guardar cambios
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle-fill"></i> Cancelar
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
