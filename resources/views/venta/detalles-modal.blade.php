<div class="modal fade" id="modalDetallesProducto" tabindex="-1"
     aria-labelledby="modalDetallesProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="modalDetallesProductoLabel">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Información de Producto
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- BODY (idéntico al de productos) --}}
            <div class="modal-body">
                <div id="modalDetallesContenido">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center"
                                 style="min-height: 200px;">
                                <img src=""
                                     id="detalles_producto_imagen"
                                     class="img-fluid rounded p-2"
                                     style="max-height: 180px;">
                            </div>
                        </div>

                        <div class="col-md-8">
                            <table class="table table-sm table-striped mb-0">
                                <tbody>
                                <tr>
                                    <th>Nombre Comercial</th>
                                    <td id="detalles_producto_nombre"></td>
                                </tr>
                                <tr>
                                    <th>Código Barras</th>
                                    <td id="detalles_producto_codigo"></td>
                                </tr>
                                <tr>
                                    <th>Ubicación</th>
                                    <td id="detalles_producto_ubicacion"></td>
                                </tr>
                                <tr>
                                    <th>Componentes</th>
                                    <td id="detalles_producto_cientifico"></td>
                                </tr>
                                <tr>
                                    <th>Forma Farmacéutica</th>
                                    <td id="detalles_producto_forma"></td>
                                </tr>
                                <tr>
                                    <th>Contenido</th>
                                    <td id="detalles_producto_contenido"></td>
                                </tr>
                                <tr>
                                    <th>Marca</th>
                                    <td id="detalles_producto_marca"></td>
                                </tr>
                                <tr>
                                    <th>Presentación</th>
                                    <td id="detalles_producto_presentacion"></td>
                                </tr>
                                <tr>
                                    <th>Categoría</th>
                                    <td id="detalles_producto_categoria"></td>
                                </tr>
                                <tr>
                                    <th>Requiere Receta</th>
                                    <td id="detalles_producto_receta"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> {{-- .row --}}
                </div> {{-- #modalDetallesContenido --}}
            </div> {{-- .modal-body --}}

            {{-- FOOTER (igual que en tu snippet actual de ventas) --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>

                {{-- Botón para agregar a la lista de venta --}}
                <div class="d-flex align-items-center gap-2">
                    <input type="number"
                           id="detalles_cantidad"
                           class="form-control form-control-sm"
                           style="width:90px;"
                           min="1"
                           value="1">
                    <button type="button"
                            class="btn btn-success"
                            id="btnAgregarDesdeDetalles">
                        <i class="fa-solid fa-cart-plus me-1"></i> Agregar a la venta
                    </button>
                </div>
            </div>

        </div> {{-- .modal-content --}}
    </div> {{-- .modal-dialog --}}
</div> {{-- .modal --}}
