<div class="modal fade" id="editProductoModal{{ $producto->id }}" tabindex="-1" aria-labelledby="editProductoModalLabel{{ $producto->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="editProductoModalLabel{{ $producto->id }}">Editar Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('producto.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="nombre_comercial_{{ $producto->id }}" class="form-label">Nombre del producto</label>
              <input type="text" class="form-control" id="nombre_comercial_{{ $producto->id }}" name="nombre_comercial" value="{{ old('nombre_comercial', $producto->nombre_comercial) }}">
            </div>
            <div class="col-md-4">
              <label for="marca_id_{{ $producto->id }}" class="form-label">Marca</label>
              <select class="form-select" id="marca_id_{{ $producto->id }}" name="marca_id">
                <option value="">Seleccionar</option>
                @foreach($marcas as $marca)
                  <option value="{{ $marca->id }}" {{ (old('marca_id', $producto->marca_id) == $marca->id) ? 'selected' : '' }}>{{ $marca->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="contenido_{{ $producto->id }}" class="form-label">Contenido</label>
              <input type="text" class="form-control" id="contenido_{{ $producto->id }}" name="contenido" value="{{ old('contenido', $producto->contenido) }}">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="descripcion_{{ $producto->id }}" class="form-label">Descripción</label>
              <input type="text" class="form-control" id="descripcion_{{ $producto->id }}" name="descripcion" value="{{ old('descripcion', $producto->descripcion) }}">
            </div>
            <div class="col-md-4">
              <label for="forma_farmaceutica_id_{{ $producto->id }}" class="form-label">Forma del fármaco</label>
              <select class="form-select" id="forma_farmaceutica_id_{{ $producto->id }}" name="forma_farmaceutica_id">
                <option value="">Seleccionar</option>
                @foreach($formas as $forma)
                  <option value="{{ $forma->id }}" {{ (old('forma_farmaceutica_id', $producto->forma_farmaceutica_id) == $forma->id) ? 'selected' : '' }}>{{ $forma->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="requiere_receta_{{ $producto->id }}" class="form-label">Requiere receta</label>
              <select class="form-select" id="requiere_receta_{{ $producto->id }}" name="requiere_receta">
                <option value="">Seleccionar</option>
                <option value="1" {{ (old('requiere_receta', $producto->requiere_receta) == 1) ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ (old('requiere_receta', $producto->requiere_receta) == 0) ? 'selected' : '' }}>No</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="codigo_barras_{{ $producto->id }}" class="form-label">Código de barras</label>
              <input type="text" class="form-control" id="codigo_barras_{{ $producto->id }}" name="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}">
            </div>
            <div class="col-md-4">
              <label for="presentacion_id_{{ $producto->id }}" class="form-label">Presentación</label>
              <select class="form-select" id="presentacion_id_{{ $producto->id }}" name="presentacion_id">
                <option value="">Seleccionar</option>
                @foreach($presentaciones as $presentacion)
                  <option value="{{ $presentacion->id }}" {{ (old('presentacion_id', $producto->presentacion_id) == $presentacion->id) ? 'selected' : '' }}>{{ $presentacion->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="precio_venta_{{ $producto->id }}" class="form-label">Precio Venta</label>
              <input type="text" class="form-control" id="precio_venta_{{ $producto->id }}" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="imagen_{{ $producto->id }}" class="form-label">Imagen</label>
              <input type="file" class="form-control" id="imagen_{{ $producto->id }}" name="imagen">
            </div>
            <div class="col-md-4">
              <label for="unidad_medida_id_{{ $producto->id }}" class="form-label">Unidad Medida</label>
              <select class="form-select" id="unidad_medida_id_{{ $producto->id }}" name="unidad_medida_id">
                <option value="">Seleccionar</option>
                @foreach($unidades as $unidad)
                  <option value="{{ $unidad->id }}" {{ (old('unidad_medida_id', $producto->unidad_medida_id) == $unidad->id) ? 'selected' : '' }}>{{ $unidad->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="stock_minimo_{{ $producto->id }}" class="form-label">Stock Mínimo</label>
              <input type="text" class="form-control" id="stock_minimo_{{ $producto->id }}" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="alt_imagen_{{ $producto->id }}" class="form-label">ALT Imagen</label>
              <input type="text" class="form-control" id="alt_imagen_{{ $producto->id }}" name="alt_imagen" value="{{ old('alt_imagen', $producto->alt_imagen) }}">
            </div>
            <div class="col-md-4">
              <label for="categoria_id_{{ $producto->id }}" class="form-label">Categoría</label>
              <select class="form-select" id="categoria_id_{{ $producto->id }}" name="categoria_id">
                <option value="">Seleccionar</option>
                @foreach($categorias as $categoria)
                  <option value="{{ $categoria->id }}" {{ (old('categoria_id', $producto->categoria_id) == $categoria->id) ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4"></div>
          </div>

          <div class="modal-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-warning me-2"><i class="bi bi-pencil-fill"></i> Guardar cambios</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle-fill"></i> Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
