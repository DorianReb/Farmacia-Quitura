<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="modalPagoLabel">
                    <i class="fa-solid fa-cash-register me-1"></i> Cobro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Total a pagar --}}
                <div class="mb-3 text-center">
                    <span class="text-muted small d-block">Total a pagar</span>
                    <strong id="modalTotalPagar" class="h4 m-0">$0.00</strong>
                </div>

                {{-- Monto recibido --}}
                <div class="mb-3">
                    <label for="monto_recibido_input" class="form-label">
                        Monto recibido del cliente
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            id="monto_recibido_input"
                            placeholder="Ej. 200">
                    </div>
                </div>

                {{-- Cambio / faltante --}}
                <div class="mb-1">
                    <span class="text-muted small d-block">Cambio a entregar</span>
                    <div class="h5 fw-bold" id="cambioCalculadoSpan">$0.00</div>
                </div>

                <small class="text-muted d-block mt-2">
                    El monto recibido debe ser mayor o igual al total para poder registrar la venta.
                </small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                {{-- Este botón llama en JS a confirmarVenta() --}}
                <button type="button" class="btn btn-success" id="btnConfirmarVenta">
                    Confirmar y cobrar
                </button>
            </div>
        </div>
    </div>
</div>
