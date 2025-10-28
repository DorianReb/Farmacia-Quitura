<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="modalPagoLabel">Confirmar y Pagar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info text-center" role="alert">
                    Total a pagar: <strong id="modalTotalPagar" class="h4 m-0">$0.00</strong>
                </div>
                
                {{-- Aquí irían más campos de pago (efectivo, tarjeta, cambio, etc.) --}}
                
                <p class="mt-4 text-center">¿Desea proceder con el registro de esta transacción?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                {{-- Este botón es clave para el JS: llama a la función confirmarVenta() --}}
                <button type="button" class="btn btn-success" id="btnConfirmarVenta">Confirmar y Cobrar</button>
            </div>
        </div>
    </div>
</div>