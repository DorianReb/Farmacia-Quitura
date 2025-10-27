@extends('layouts.sidebar-admin')

@section('content')
    {{-- Estilos (puedes moverlos a tu archivo CSS/SCSS principal) --}}
    <style>
        .card-soft { border: 0; border-radius: 14px; box-shadow: 0 8px 20px rgba(16,24,40,.06); }
        .table-hover tbody tr:hover { background: #f7f9ff; }
        .btn-icon { padding: .45rem .6rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; }
        .card-header { background-color: #f8f9fa; font-weight: 600; border-bottom: 1px solid #dee2e6; }
        .product-data-item { margin-bottom: 0.85rem; }
        .product-data-item small { color: #6c757d; display: block; font-size: 0.8rem; line-height: 1.3; }
        .product-data-item strong { font-size: 0.95rem; line-height: 1.3; }
        /* Estilo opcional para feedback de error en modal */
        #modalManualError:not(.d-none) { animation: shake 0.5s; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); } 20%, 40%, 60%, 80% { transform: translateX(5px); } }
    </style>

    <div class="container-xxl">

        {{-- Encabezado: Título + Buscador --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 mb-md-4 gap-3">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-cash-register"></i><h1 class="h4 m-0">Vender</h1>
            </div>
            <form id="formBuscarProducto" method="GET" action="{{ route('venta.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" id="codigo_barras_input" name="q" value="{{ request('q') }}" class="form-control" placeholder="Escanear código o buscar..." autofocus>
                    <button class="btn btn-success" type="submit" data-bs-toggle="tooltip" title="Buscar manualmente"><i class="fa-solid fa-search"></i></button>
                </div>
            </form>
        </div>

        {{-- Tarjeta 1: Datos del producto escaneado --}}
        <div class="card card-soft mb-4">
            <div class="card-header">Datos del producto escaneado</div>
            <div class="card-body" id="datosProductoArea">
                <div id="placeholderProducto" class="text-center py-4 text-muted"><i class="fa-solid fa-barcode fa-2x mb-2"></i><p class="m-0">Escanee un código de barras para comenzar.</p></div>
                <div id="infoProducto" class="d-none">
                    <div class="row">
                        <div class="col-lg-3 text-center d-flex flex-column align-items-center">
                            <strong class="d-block mb-2">Imagen producto</strong>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 150px; width: 100%; max-width: 250px;">
                                <img src="https://via.placeholder.com/250x150.png?text=Producto" id="producto_imagen" alt="Producto" class="img-fluid rounded p-2" style="max-height: 160px;">
                            </div>
                        </div>
                        <div class="col-lg-9"><div class="row mt-3 mt-lg-0">
                            <div class="col-md-4"><div class="product-data-item"><small>Nombre</small><strong id="producto_nombre">---</strong></div><div class="product-data-item"><small>Código Barras</small><strong id="producto_codigo">---</strong></div><div class="product-data-item"><small>Nombre científico</small><strong id="producto_nombre_cientifico">---</strong></div></div>
                            <div class="col-md-4"><div class="product-data-item"><small>Forma Farmacéutica</small><strong id="producto_forma">---</strong></div><div class="product-data-item"><small>Unidad Medida</small><strong id="producto_unidad">---</strong></div><div class="product-data-item"><small>Contenido</small><strong id="producto_contenido">---</strong></div></div>
                            <div class="col-md-4"><div class="product-data-item"><small>Requerir Receta</small><strong id="producto_receta">---</strong></div><div class="product-data-item"><small>Marca</small><strong id="producto_marca">---</strong></div><div class="product-data-item"><small>Presentación</small><strong id="producto_presentacion">---</strong></div><div class="product-data-item"><small>Categoría</small><strong id="producto_categoria">---</strong></div></div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Lista de Venta --}}
        <div class="card card-soft">
            <div class="card-header">Lista de venta</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="listaVentaTable" class="table table-hover align-middle m-0">
                        <thead class="bg-azul-marino text-white">
                            <tr><th>Código</th><th>Producto</th><th>Precio</th><th style="width: 100px;">Cantidad</th><th>Stock</th><th>Lote (FEFO)</th><th>Promo</th><th>Subtotal</th><th class="text-end" style="width: 80px;">Acción</th></tr>
                        </thead>
                        <tbody></tbody> {{-- El contenido se genera con JS --}}
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-3 pb-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    {{-- Botón que abre el modal --}}
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarManual">
                        <i class="fa-solid fa-plus-circle"></i> Añadir código de forma manual
                    </button>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-primary shadow-sm" style="min-width: 120px;"><i class="fa-solid fa-cash-coin"></i> Total</button>
                        <span id="totalVentaSpan" class="h3 fw-bold m-0 text-dark">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- Fin container-xxl --}}

    {{-- ============================================= --}}
    {{--         Modal para Añadir Manualmente         --}}
    {{-- ============================================= --}}
    <div class="modal fade" id="modalAgregarManual" tabindex="-1" aria-labelledby="modalAgregarManualLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="modalAgregarManualLabel">Añadir Producto Manualmente</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <form id="formAgregarManual">
                        <div class="mb-3"><label for="manual_codigo_barras" class="form-label">Código de Barras</label><input type="text" class="form-control" id="manual_codigo_barras" required autofocus></div>
                        <div class="mb-3"><label for="manual_cantidad" class="form-label">Cantidad</label><input type="number" class="form-control" id="manual_cantidad" value="1" min="1" required></div>
                        <div id="modalManualError" class="alert alert-danger d-none mt-3" role="alert"></div>
                        <div id="modalManualInfoProducto" class="alert alert-info d-none p-2 mt-3 small"></div>
                    </form>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="formAgregarManual" class="btn btn-success"><i class="fa-solid fa-plus-circle me-1"></i> Añadir a la Lista</button></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    let listaVenta = [];
    const tbody = document.querySelector('#listaVentaTable tbody');
    const totalSpan = document.getElementById('totalVentaSpan');
    const modalElement = document.getElementById('modalAgregarManual');
    const modalManual = modalElement ? new bootstrap.Modal(modalElement) : null;
    const formAgregarManual = document.getElementById('formAgregarManual');

    function renderTabla(){
        tbody.innerHTML = '';
        let total = 0;

        listaVenta.forEach((item, index) => {
            total += item.subtotal;
            tbody.innerHTML += `
                <tr>
                    <td>${item.codigo_barras}</td>
                    <td>${item.nombre}</td>
                    <td>$${item.precio.toFixed(2)}</td>
                    <td>${item.cantidad}</td>
                    <td>${item.stock}</td>
                    <td>${item.lote}</td>
                    <td>$${item.subtotal.toFixed(2)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">Eliminar</button>
                    </td>
                </tr>
            `;
        });

        totalSpan.textContent = `$${total.toFixed(2)}`;
    }

    window.eliminarProducto = function(index){
        listaVenta.splice(index,1);
        renderTabla();
    }

    async function agregarProducto(codigo, cantidad=1){
        try {
            const res = await fetch(`/venta/buscarProducto/${codigo}`);
            if(!res.ok) throw new Error('Producto no encontrado');
            const producto = await res.json();

            let loteId = producto.lotes[0]?.id ?? null;
            if(!loteId){
                alert('No hay lote disponible');
                return;
            }

            let itemExistente = listaVenta.find(i => i.codigo_barras === producto.codigo_barras);
            if(itemExistente){
                itemExistente.cantidad += cantidad;
                itemExistente.subtotal = itemExistente.cantidad * producto.precio_venta;
            } else {
                listaVenta.push({
                    codigo_barras: producto.codigo_barras,
                    nombre: producto.nombre_comercial,
                    precio: producto.precio_venta,
                    cantidad: cantidad,
                    stock: producto.existencias_calculadas,
                    lote: loteId,
                    subtotal: cantidad * producto.precio_venta
                });
            }

            renderTabla();
        } catch(err){
            alert(err.message);
        }
    }

    formAgregarManual.addEventListener('submit', function(e){
        e.preventDefault();
        const codigo = document.getElementById('manual_codigo_barras').value;
        const cantidad = parseInt(document.getElementById('manual_cantidad').value);
        agregarProducto(codigo, cantidad);
        modalManual.hide();
        formAgregarManual.reset();
    });

    // Si se cargó producto por parámetro q en URL
    @if($productoEncontrado)
        agregarProducto("{{ $productoEncontrado->codigo_barras }}", 1);
    @endif

});
</script>
@endpush


