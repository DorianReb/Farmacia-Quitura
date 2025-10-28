    @extends('layouts.sidebar-admin')

@section('content')
<div class="container-xxl">
    {{-- Encabezado --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 mb-md-4 gap-3">
        <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-cash-register"></i>
            <h1 class="h4 m-0">Vender</h1>
        </div>
        <form id="formBuscarProducto" class="d-inline-block w-100" style="min-width:300px;max-width:480px;">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" id="codigo_barras_input" class="form-control" placeholder="Escanear código o buscar...">
                <button class="btn btn-success" type="submit"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>

    {{-- Datos del producto escaneado --}}
    <div class="card card-soft mb-4">
        <div class="card-header">Datos del producto escaneado</div>
        <div class="card-body" id="datosProductoArea">
            <div id="placeholderProducto" class="text-center py-4 text-muted">
                <i class="fa-solid fa-barcode fa-2x mb-2"></i>
                <p class="m-0">Escanee un código de barras para comenzar.</p>
            </div>
            <div id="infoProducto" class="d-none">
                <div class="row">
                    <div class="col-lg-3 text-center">
                        <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="min-height:150px;width:100%;max-width:250px;">
                            <img src="https://via.placeholder.com/250x150.png?text=Producto" id="producto_imagen" class="img-fluid rounded p-2" style="max-height:160px;">
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr><th>Nombre</th><td id="producto_nombre">---</td></tr>
                                <tr><th>Código Barras</th><td id="producto_codigo">---</td></tr>
                                <tr><th>Ubicación</th><td id="producto_ubicacion">---</td></tr>
                                <tr><th>Nombre Científico</th><td id="producto_nombre_cientifico">---</td></tr>
                                <tr><th>Forma Farmacéutica</th><td id="producto_forma">---</td></tr>
                                <tr><th>Contenido / Dosis</th><td id="producto_contenido">---</td></tr>
                                <tr><th>Marca</th><td id="producto_marca">---</td></tr>
                                <tr><th>Presentación</th><td id="producto_presentacion">---</td></tr>
                                <tr><th>Requiere Receta</th><td id="producto_receta">---</td></tr>
                                <tr><th>Categoría</th><td id="producto_categoria">---</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de venta --}}
    <div class="card card-soft">
        <div class="card-header">Lista de venta</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="listaVentaTable" class="table table-hover align-middle m-0">
                    <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Código</th><th>Producto</th><th>Precio</th><th style="width:100px;">Cantidad</th>
                            <th>Stock</th><th>Lote (FEFO)</th><th>Promo</th><th>Subtotal</th><th class="text-end" style="width:80px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3 pb-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarManual">
                    <i class="fa-solid fa-plus-circle"></i> Añadir código de forma manual
                </button>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-primary shadow-sm" style="min-width:120px;">
                        <i class="fa-solid fa-cash-coin"></i> Total
                    </button>
                    <span id="totalVentaSpan" class="h3 fw-bold m-0 text-dark">$0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetallesProducto" tabindex="-1" aria-labelledby="modalDetallesProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="modalDetallesProductoLabel">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Información de Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="modalDetallesContenido">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                <img src="" id="detalles_producto_imagen" class="img-fluid rounded p-2" style="max-height: 180px;">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-sm table-striped mb-0">
                                <tbody>
                                    <tr><th>Nombre Comercial</th><td id="detalles_producto_nombre"></td></tr>
                                    <tr><th>Código Barras</th><td id="detalles_producto_codigo"></td></tr>
                                    <tr><th>Ubicación</th><td id="detalles_producto_ubicacion"></td></tr>
                                    <tr><th>Nombre Científico</th><td id="detalles_producto_cientifico"></td></tr>
                                    <tr><th>Forma Farmacéutica</th><td id="detalles_producto_forma"></td></tr>
                                    <tr><th>Contenido / Dosis</th><td id="detalles_producto_contenido"></td></tr>
                                    <tr><th>Marca</th><td id="detalles_producto_marca"></td></tr>
                                    <tr><th>Presentación</th><td id="detalles_producto_presentacion"></td></tr>
                                    <tr><th>Categoría</th><td id="detalles_producto_categoria"></td></tr>
                                    <tr><th>Requiere Receta</th><td id="detalles_producto_receta"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Partials --}}
@include('venta.create') {{-- modal --}}
@include('venta.scripts') {{-- JS --}}

@endsection
