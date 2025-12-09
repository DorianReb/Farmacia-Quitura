@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{
            border:0;
            border-radius:14px;
            box-shadow:0 8px 20px rgba(16,24,40,.06);
        }
        .section-title{
            font-weight:800;
            letter-spacing:.04em;
        }
        .table-hover tbody tr:hover{
            background:#f7f9ff;
        }
        .table thead{
            background:#002b5b; /* azul marino */
            color:white;
        }
        .btn-sm{
            padding:.25rem .5rem !important;
            line-height:1;
        }
    </style>

    <div class="container-xxl">
        {{-- Encabezado --}}
        <div class="Ser d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 mb-md-4 gap-3">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-cash-register"></i>
                <h1 class="h4 m-0">Vender</h1>
            </div>

            {{-- BOTÓN DE REDIRECCIÓN AL CRUD/INDEX DE DETALLES DE VENTA
            <a href="{{ route('detalleventa.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-list-check me-1"></i> Ver Detalles de Venta
            </a>

            --}}

            {{-- UNIFICADO: Este formulario será manejado completamente por JavaScript --}}
            <form id="formBuscarProducto" class="d-inline-block w-100" style="min-width:300px;max-width:480px;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" id="codigo_barras_input" class="form-control"
                           placeholder="Buscar producto (nombre) o escanear (código)..." value="{{ request('q') ?? '' }}">
                    <button class="btn btn-success" type="submit"><i class="fa-solid fa-search"></i></button>
                </div>
            </form>
        </div>

        {{-- FILTROS DE BÚSQUEDA DE PRODUCTOS (igual a productos.index) --}}
        <div class="card card-soft mb-3">
            <div class="card-header text-dark py-2">
                <h2 class="h6 m-0 text-uppercase" style="font-weight: 700; letter-spacing: .04em;">
                    Filtros de búsqueda de productos
                </h2>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('venta.index') }}" class="row g-3 align-items-end">

                    {{-- Texto libre (nombre, descripción, código, marca, componente, etc.) --}}
                    <div class="col-md-4 col-lg-4">
                        <label for="q" class="form-label mb-1">
                            <small>Buscar (nombre, descripción, código, marca, componente…)</small>
                        </label>
                        <input
                            type="text"
                            name="q"
                            id="q"
                            class="form-control"
                            value="{{ request('q') }}"
                            placeholder="Paracetamol, jarabe, 750 mg, código...">
                    </div>

                    {{-- Filtro por categoría --}}
                    <div class="col-md-4 col-lg-3">
                        <label for="categoria_id" class="form-label mb-1">
                            <small>Categoría</small>
                        </label>
                        <select
                            name="categoria_id"
                            id="categoria_id"
                            class="form-select">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (string)request('categoria_id') === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por receta --}}
                    <div class="col-md-4 col-lg-3">
                        <label for="receta" class="form-label mb-1">
                            <small>Requiere receta</small>
                        </label>
                        <select
                            name="receta"
                            id="receta"
                            class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('receta') === '1' ? 'selected' : '' }}>Sólo con receta</option>
                            <option value="0" {{ request('receta') === '0' ? 'selected' : '' }}>Sin receta</option>
                        </select>
                    </div>

                    {{-- Botón Buscar --}}
                    <div class="col-md-2 col-lg-1">
                        <button type="submit" class="btn btn-success w-100" title="Buscar">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>

                    {{-- Botón limpiar filtros --}}
                    @if(request('q') || request('categoria_id') || (request()->has('receta') && request('receta') !== ''))
                        <div class="col-md-2 col-lg-1">
                            <a href="{{ route('venta.index') }}"
                               class="btn btn-outline-secondary w-100"
                               title="Limpiar filtros">
                                <i class="fa-regular fa-circle-xmark"></i>
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>


    @if($errors->has('venta'))
            <div class="alert alert-danger mt-3">
                {{ $errors->first('venta') }}
            </div>
        @endif



        {{-- Datos del producto escaneado --}}
        <div class="card card-soft mb-4">
            <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
                <h2 class="h6 m-0 section-title text-uppercase">Datos del producto escaneado</h2>
            </div>
            <div class="card-body" id="datosProductoArea">
                <div id="placeholderProducto" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-barcode fa-2x mb-2"></i>
                    <p class="m-0">Escanee un código de barras para comenzar.</p>
                </div>
                <div id="infoProducto" class="d-none">
                    <div class="row">
                        <div class="col-lg-3 text-center">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center"
                                 style="min-height:150px;width:100%;max-width:250px;">
                                <img src="https://via.placeholder.com/250x150.png?text=Producto"
                                     id="producto_imagen"
                                     class="img-fluid rounded p-2"
                                     style="max-height:160px;">
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                <tr><th>Nombre</th><td id="producto_nombre">---</td></tr>
                                <tr><th>Código Barras</th><td id="producto_codigo">---</td></tr>
                                <tr><th>Ubicación</th><td id="producto_ubicacion">---</td></tr>
                                <tr><th>Componentes</th><td id="producto_nombre_cientifico">---</td></tr>
                                <tr><th>Forma Farmacéutica</th><td id="producto_forma">---</td></tr>
                                <tr><th>Contenido</th><td id="producto_contenido">---</td></tr>
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
            <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
                <h2 class="h6 m-0 section-title text-uppercase">Lista de venta</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="listaVentaTable" class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Ubicación</th>
                            <th>Precio</th>
                            <th style="width:100px;">Cantidad</th>
                            <th>Stock</th>
                            <th>Lote (FEFO)</th>
                            <th>Total sin promo</th>
                            <th>Promo</th>
                            <th>Subtotal</th>
                            <th class="text-end" style="width:80px;">Acción</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-3 pb-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                    {{-- BOTÓN: Añadir código manual --}}
                    <button type="button"
                            class="btn btn-success shadow-sm px-4 py-2 rounded-pill fw-semibold d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#modalAgregarManual"
                            title="Añadir código manual">
                        <i class="fa-solid fa-plus"></i>
                    </button>

                    <div class="d-flex align-items-center gap-3">

                        {{-- BOTÓN PRINCIPAL DE VENTA --}}
                        <button class="btn btn-primary shadow-sm px-4 py-2 rounded-pill fw-semibold d-flex align-items-center gap-2"
                                type="button"
                                data-bs-toggle="modal" data-bs-target="#modalPago"
                                id="btnProcesarVenta"
                                title="Procesar venta">
                            <i class="fa-solid fa-cash-register"></i>
                            Procesar venta
                        </button>

                        {{-- TOTAL --}}
                        <span id="totalVentaSpan" class="h3 fw-bold m-0 text-dark">$0.00</span>

                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- FORMULARIO OCULTO PARA ENVIAR DATOS DE VENTA --}}
    <form id="formProcesarVenta" method="POST" action="{{ route('venta.store') }}" style="display: none;">
        @csrf
    </form>

    {{-- MODALES --}}
    @include('venta.menu-modal')       {{-- Menú de productos --}}
    @include('venta.detalles-modal')   {{-- Detalles de producto --}}
    @include('venta.pago')             {{-- Modal de pago --}}
    @include('venta.create')           {{-- Modal para agregar manual --}}
    @include('venta.scripts')          {{-- JS de la venta --}}
@endsection
