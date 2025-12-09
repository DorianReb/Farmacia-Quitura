<div class="modal fade" id="createProductoModal" tabindex="-1" aria-labelledby="createProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createProductoModalLabel">Añadir Producto</h5>
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

                <form action="{{ route('producto.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="page" value="{{ request('page', $productos->currentPage() ?? 1) }}">

                    {{-- Fila 1: Nombre, Marca, Requiere receta --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="nombre_comercial" class="form-label">
                                Nombre del producto <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('nombre_comercial') is-invalid @enderror"
                                   id="nombre_comercial"
                                   name="nombre_comercial"
                                   value="{{ old('nombre_comercial') }}"
                                   placeholder="Nombre del producto"
                                   required>
                            @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marca_id" class="form-label">
                                Marca <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('marca_id') is-invalid @enderror"
                                    id="marca_id"
                                    name="marca_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marca_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="requiere_receta" class="form-label">
                                Requiere receta <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('requiere_receta') is-invalid @enderror"
                                    id="requiere_receta"
                                    name="requiere_receta"
                                    required>
                                <option value="">Seleccionar</option>
                                <option value="1" {{ old('requiere_receta') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('requiere_receta') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('requiere_receta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Fila 2: Forma, Contenido, Unidad de medida --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="forma_farmaceutica_id" class="form-label">
                                Forma del fármaco <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('forma_farmaceutica_id') is-invalid @enderror"
                                    id="forma_farmaceutica_id"
                                    name="forma_farmaceutica_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($formas as $forma)
                                    <option value="{{ $forma->id }}" {{ old('forma_farmaceutica_id') == $forma->id ? 'selected' : '' }}>
                                        {{ $forma->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('forma_farmaceutica_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="contenido" class="form-label">
                                Contenido
                            </label>
                            <input type="text"
                                   class="form-control @error('contenido') is-invalid @enderror"
                                   id="contenido"
                                   name="contenido"
                                   value="{{ old('contenido') }}"
                                   placeholder="Ej. 20 tabletas">
                            @error('contenido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="unidad_medida_id" class="form-label">
                                Unidad de medida <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('unidad_medida_id') is-invalid @enderror"
                                    id="unidad_medida_id"
                                    name="unidad_medida_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($coleccionUnidades as $u)
                                    <option value="{{ $u->id }}" {{ old('unidad_medida_id') == $u->id ? 'selected' : '' }}>
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
                            <label for="codigo_barras" class="form-label">
                                Código de barras
                            </label>
                            <input type="text"
                                   class="form-control @error('codigo_barras') is-invalid @enderror"
                                   id="codigo_barras"
                                   name="codigo_barras"
                                   value="{{ old('codigo_barras') }}"
                                   placeholder="Código de barras">
                            @error('codigo_barras') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="presentacion_id" class="form-label">
                                Presentación <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('presentacion_id') is-invalid @enderror"
                                    id="presentacion_id"
                                    name="presentacion_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($presentaciones as $presentacion)
                                    <option value="{{ $presentacion->id }}" {{ old('presentacion_id') == $presentacion->id ? 'selected' : '' }}>
                                        {{ $presentacion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('presentacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="categoria_id" class="form-label">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('categoria_id') is-invalid @enderror"
                                    id="categoria_id"
                                    name="categoria_id"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
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
                            <label for="precio_venta" class="form-label">
                                Precio venta <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('precio_venta') is-invalid @enderror"
                                   id="precio_venta"
                                   name="precio_venta"
                                   value="{{ old('precio_venta') }}"
                                   placeholder="Precio de venta"
                                   required>
                            @error('precio_venta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="stock_minimo" class="form-label">
                                Stock mínimo <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   min="0"
                                   class="form-control @error('stock_minimo') is-invalid @enderror"
                                   id="stock_minimo"
                                   name="stock_minimo"
                                   value="{{ old('stock_minimo') }}"
                                   placeholder="Stock mínimo"
                                   required>
                            @error('stock_minimo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="existencias" class="form-label">
                                Existencias
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="existencias"
                                   value="0"
                                   disabled>
                            <small class="form-text text-muted">
                                Las existencias se actualizan automáticamente al registrar lotes.
                            </small>
                        </div>
                    </div>

                    {{-- Fila 5: Descripción, Imagen, ALT imagen --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="descripcion" class="form-label">
                                Descripción
                            </label>
                            <input type="text"
                                   class="form-control @error('descripcion') is-invalid @enderror"
                                   id="descripcion"
                                   name="descripcion"
                                   value="{{ old('descripcion') }}"
                                   placeholder="Descripción breve">
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="imagen" class="form-label">
                                Imagen
                            </label>
                            <input type="file"
                                   class="form-control @error('imagen') is-invalid @enderror"
                                   id="imagen"
                                   name="imagen">
                            @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="alt_imagen" class="form-label">
                                ALT imagen
                            </label>
                            <input type="text"
                                   class="form-control @error('alt_imagen') is-invalid @enderror"
                                   id="alt_imagen"
                                   name="alt_imagen"
                                   value="{{ old('alt_imagen') }}"
                                   placeholder="Texto alternativo accesible">
                            @error('alt_imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-plus-circle-fill"></i> Añadir
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

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
