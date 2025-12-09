@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .kpi{border:0;border-radius:14px;box-shadow:0 6px 18px rgba(10,31,68,.06);}
        .kpi .icon{width:48px;height:48px;display:grid;place-items:center;border-radius:12px;background:rgba(255,255,255,.18);}
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .section-title{font-weight:800;letter-spacing:.04em;color:#0a2e63;}
        .table thead th{background:#0a2e63;color:#fff;border:0;}
        .table-hover tbody tr:hover{background:#f7f9ff;}
        .subtle{opacity:.8}
        .badge-pill{border-radius:999px;}
        .chart-wrapper-ranking{height:220px;}
        /* Compacta la paginación como en Marcas/Formas */
        .pagination { gap:.25rem; }
        .pagination .page-link{
            padding:.25rem .55rem;
            font-size:.85rem;
            border:0;
        }
        .pagination .page-item.active .page-link{
            background:var(--bs-primary);
            border-color:var(--bs-primary);
        }

    </style>

    <div class="container-xxl">

        {{-- ======= ENCABEZADO ======= --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-trophy"></i>
                    <h1 class="h5 m-0">Ranking de Productos</h1>
                </div>
                <span class="subtle ms-2">Productos más y menos vendidos en el periodo seleccionado</span>
            </div>
        </div>

        {{-- ======= KPIs ======= --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="kpi p-3 bg-primary text-white d-flex align-items-center gap-3">
                    <div class="icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div>
                        <div class="small opacity-75">Productos en catálogo</div>
                        <div class="h4 m-0 fw-bold">{{ number_format($kpis['total_productos'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="kpi p-3 bg-success text-white d-flex align-items-center gap-3">
                    <div class="icon"><i class="fa-solid fa-star"></i></div>
                    <div>
                        <div class="small opacity-75">Con al menos una venta</div>
                        <div class="h4 m-0 fw-bold">{{ number_format($kpis['productos_con_ventas'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="kpi p-3 bg-warning d-flex align-items-center gap-3">
                    <div class="icon bg-white"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div>
                        <div class="small">Unidades vendidas en el periodo</div>
                        <div class="h4 m-0 fw-bold">{{ number_format($kpis['unidades_vendidas'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======= RANKING + FILTROS + BUSCADOR ======= --}}
        <div class="card card-soft mb-4">
            <div class="card-body">

                {{-- Header de sección --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="section-title m-0">
                        RANKING DE PRODUCTOS
                        <span class="badge bg-light text-muted ms-2 badge-pill small">
                            {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}
                            –
                            {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                        </span>
                    </h6>

                    {{-- Filtros de periodo + orden compactos --}}
                    <form class="d-flex flex-wrap align-items-center gap-2" method="GET" action="{{ route('reportes.ranking') }}">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input
                                type="text"
                                class="form-control js-fecha-desde"
                                name="from"
                                value="{{ $from }}"
                                placeholder="Desde"
                                autocomplete="off">
                        </div>
                        <span class="small">a</span>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input
                                type="text"
                                class="form-control js-fecha-hasta"
                                name="to"
                                value="{{ $to }}"
                                placeholder="Hasta"
                                autocomplete="off">
                        </div>


                        <div class="input-group input-group-sm">
                            <label class="input-group-text">
                                <i class="fa-solid fa-arrow-up-wide-short me-1"></i>Orden
                            </label>
                            <select class="form-select" name="order">
                                <option value="mas"   {{ $order === 'mas'   ? 'selected' : '' }}>Más vendidos primero</option>
                                <option value="menos" {{ $order === 'menos' ? 'selected' : '' }}>Menos vendidos primero</option>
                            </select>
                        </div>

                        @if($q)
                            <input type="hidden" name="q" value="{{ $q }}">
                        @endif

                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-filter me-1"></i>Aplicar
                        </button>
                    </form>
                </div>

                {{-- Buscador debajo de los filtros --}}
                <form method="GET" action="{{ route('reportes.ranking') }}" class="mb-3">
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <input type="hidden" name="order" value="{{ $order }}">

                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Buscar por nombre, descripción, categoría o marca…">
                        <button class="btn btn-success">Buscar</button>
                        @if($q)
                            <a href="{{ route('reportes.ranking', ['from'=>$from,'to'=>$to,'order'=>$order]) }}"
                               class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </form>

                {{-- Tabla de ranking --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th class="text-end">Unidades vendidas</th>
                            <th class="text-end">Nº ventas</th>
                            <th class="text-end">Ventas ($)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ranking as $i => $row)
                            <tr>
                                <td>{{ $ranking->firstItem() + $i }}</td>
                                <td class="fw-semibold">
                                    {{ $row->producto }}
                                    @if(($row->unidades ?? 0) == 0)
                                        <span class="badge bg-secondary ms-1">Sin ventas en el periodo</span>
                                    @endif
                                </td>
                                <td>{{ $row->categoria }}</td>
                                <td>{{ $row->marca }}</td>
                                <td class="text-end">{{ number_format($row->unidades ?? 0) }}</td>
                                <td class="text-end">{{ number_format($row->num_ventas ?? 0) }}</td>
                                <td class="text-end">{{ number_format($row->ventas ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No se encontraron productos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- PAGINACIÓN (igual estilo que Formas/Marcas) --}}
                <div class="mt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <small class="text-muted order-2 order-md-1 text-center text-md-start">
                            Mostrando {{ $ranking->firstItem() ?? 0 }}–{{ $ranking->lastItem() ?? 0 }} de {{ $ranking->total() }}
                            @if($q) • Filtro: “{{ $q }}” @endif
                        </small>

                        @if($ranking->hasPages())
                            <nav aria-label="Paginación ranking de productos" class="order-1 order-md-2">
                                {{ $ranking->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- ======= GRÁFICAS TOP 10 (más compactas) ======= --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h6 class="section-title small mb-2">Top 10 más vendidos</h6>
                        <div class="chart-wrapper-ranking">
                            <canvas id="chartTopMas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h6 class="section-title small mb-2">Top 10 menos vendidos (con ventas)</h6>
                        <div class="chart-wrapper-ranking">
                            <canvas id="chartTopMenos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Flatpickr (calendarios) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    {{-- Tema opcional (puedes cambiarlo por otro de Flatpickr si quieres) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuración común
            const opcionesFecha = {
                dateFormat: 'Y-m-d',      // Formato que espera el controlador
                allowInput: true,
                locale: flatpickr.l10ns.es
            };

            flatpickr('.js-fecha-desde', {
                ...opcionesFecha,
                defaultDate: '{{ $from }}'
            });

            flatpickr('.js-fecha-hasta', {
                ...opcionesFecha,
                defaultDate: '{{ $to }}'
            });
        });
    </script>


    {{-- ======= Chart.js ======= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const labelsMas   = {!! json_encode(($chartMas['labels']   ?? []), JSON_UNESCAPED_UNICODE) !!};
            const dataMas     = {!! json_encode(($chartMas['data']     ?? []), JSON_UNESCAPED_UNICODE) !!};
            const labelsMenos = {!! json_encode(($chartMenos['labels'] ?? []), JSON_UNESCAPED_UNICODE) !!};
            const dataMenos   = {!! json_encode(($chartMenos['data']   ?? []), JSON_UNESCAPED_UNICODE) !!};

            const elMas   = document.getElementById('chartTopMas');
            const elMenos = document.getElementById('chartTopMenos');

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // barras horizontales -> se lee mejor
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: { ticks: { precision: 0 } },
                    y: { ticks: { autoSkip: true, maxTicksLimit: 10 } }
                }
            };

            if (elMas && labelsMas.length) {
                new Chart(elMas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelsMas,
                        datasets: [{ label: 'Unidades vendidas', data: dataMas }]
                    },
                    options: commonOptions
                });
            }

            if (elMenos && labelsMenos.length) {
                new Chart(elMenos.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelsMenos,
                        datasets: [{ label: 'Unidades vendidas', data: dataMenos }]
                    },
                    options: commonOptions
                });
            }
        })();
    </script>
@endsection
