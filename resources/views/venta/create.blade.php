{{-- ============================================= --}}
{{--         Modal para Añadir Manualmente         --}}
{{-- ============================================= --}}
<div class="modal fade" id="modalAgregarManual" tabindex="-1" aria-labelledby="modalAgregarManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                {{-- Título del modal --}}
                <h5 class="modal-title" id="modalAgregarManualLabel">Añadir Producto Manualmente</h5>
                {{-- Botón para cerrar (X) --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Formulario dentro del modal --}}
                {{-- El ID 'formAgregarManual' es importante para el JavaScript --}}
                <form id="formAgregarManual">
                    {{-- Campo para Código de Barras --}}
                    <div class="mb-3">
                        <label for="manual_codigo_barras" class="form-label">Código de Barras</label>
                        {{-- El ID 'manual_codigo_barras' es importante --}}
                        <input type="text" class="form-control" id="manual_codigo_barras" required autofocus>
                        {{-- Puedes añadir aquí un input oculto si necesitas guardar el ID del producto encontrado --}}
                        {{-- <input type="hidden" id="manual_producto_id"> --}}
                    </div>

                    {{-- Campo para Cantidad --}}
                    <div class="mb-3">
                        <label for="manual_cantidad" class="form-label">Cantidad</label>
                        {{-- El ID 'manual_cantidad' es importante --}}
                        <input type="number" class="form-control" id="manual_cantidad" value="1" min="1" required>
                    </div>

                     {{-- Área para mostrar errores dentro del modal --}}
                     {{-- El ID 'modalManualError' es importante --}}
                     <div id="modalManualError" class="alert alert-danger d-none mt-3" role="alert">
                         {{-- Los mensajes de error se insertarán aquí con JavaScript --}}
                     </div>

                     {{-- Área para mostrar info breve del producto (opcional) --}}
                     {{-- El ID 'modalManualInfoProducto' es importante --}}
                     <div id="modalManualInfoProducto" class="alert alert-info d-none p-2 mt-3 small">
                        {{-- La información del producto se insertará aquí con JavaScript --}}
                     </div>

                </form> {{-- Fin del formulario --}}
            </div>
            <div class="modal-footer">
                {{-- Botón para cerrar/cancelar --}}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                {{-- Botón que envía el formulario del modal (usando el atributo 'form') --}}
                {{-- Este botón NO recarga la página gracias al JavaScript que intercepta el 'submit' --}}
                <button type="submit" form="formAgregarManual" class="btn btn-success">
                    <i class="fa-solid fa-plus-circle me-1"></i> Añadir a la Lista
                </button>
            </div>
        </div>
    </div>
</div>