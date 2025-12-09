<div class="modal fade" id="modalAgregarManual" tabindex="-1" aria-labelledby="modalAgregarManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAgregarManualLabel">Añadir Producto Manualmente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="formAgregarManual">
                    <div class="mb-3">
                        <label for="manual_codigo_barras" class="form-label">Código de Barras</label>
                        <input type="text" class="form-control" id="manual_codigo_barras" required>
                    </div>
                    <div class="mb-3">
                        <label for="manual_cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="manual_cantidad" value="1" min="1" required>
                    </div>

                    <div id="modalManualError" class="alert alert-danger d-none mt-3"></div>
                </form>

                {{-- 🔹 Ficha del producto (similar a "Datos del producto escaneado") --}}
                <div id="manualInfoProducto" class="mt-3 d-none">
                    <h6 class="fw-bold mb-2">Información del producto</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                        <tr><th style="width:40%;">Nombre</th><td id="manual_producto_nombre">---</td></tr>
                        <tr><th>Código Barras</th><td id="manual_producto_codigo">---</td></tr>
                        <tr><th>Ubicación</th><td id="manual_producto_ubicacion">---</td></tr>
                        <tr><th>Componentes</th><td id="manual_producto_componentes">---</td></tr>
                        <tr><th>Forma Farmacéutica</th><td id="manual_producto_forma">---</td></tr>
                        <tr><th>Dosis</th><td id="manual_producto_contenido">---</td></tr>
                        <tr><th>Marca</th><td id="manual_producto_marca">---</td></tr>
                        <tr><th>Presentación</th><td id="manual_producto_presentacion">---</td></tr>
                        <tr><th>Requiere Receta</th><td id="manual_producto_receta">---</td></tr>
                        <tr><th>Categoría</th><td id="manual_producto_categoria">---</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formAgregarManual" class="btn btn-success">
                    <i class="fa-solid fa-plus-circle me-1"></i> Añadir a la Lista
                </button>
            </div>
        </div>
    </div>
</div>
