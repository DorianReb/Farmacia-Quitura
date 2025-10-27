<div class="modal fade" id="modalAgregarManual" tabindex="-1" aria-labelledby="modalAgregarManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                {{-- CORRECCIÓN 1: Se añadió el ID aquí --}}
                <h5 class="modal-title" id="modalAgregarManualLabel">Añadir Producto Manualmente</h5>
                
                {{-- CORRECCIÓN 2: Se añadió el aria-label --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarManual">
                    <div class="mb-3">
                        <label for="manual_codigo_barras" class="form-label">Código de Barras</label>
                        
                        {{-- CORRECCIÓN 3: Se quitó el 'autofocus' --}}
                        <input type="text" class="form-control" id="manual_codigo_barras" required>
                    </div>
                    <div class="mb-3">
                        <label for="manual_cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="manual_cantidad" value="1" min="1" required>
                    </div>
                    <div id="modalManualError" class="alert alert-danger d-none mt-3"></div>
                </form>
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