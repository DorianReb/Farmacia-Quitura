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
    </style>

    <div class="container-xxl">

        {{-- ======= ENCABEZADO + KPIs ======= --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-line"></i>
                    <h1 class="h5 m-0">Reporte de Rentabilidad</h1>
                </div>
                <span class="subtle ms-2">Utilidad, márgenes y desempeño por periodo</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="kpi p-3 bg-azul-marino text-white d-flex align-items-center gap-3">
                    <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div>
                        <div class="small opacity-75">Utilidad total</div>
                        <div class="h4 m-0 fw-bold">{{ $kpis['utilidad_total'] ?? '$0.00' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="kpi p-3 bg-success text-white d-flex align-items-center gap-3">
                    <div class="icon"><i class="fa-solid fa-percent"></i></div>
                    <div>
                        <div class="small opacity-75">Margen promedio</div>
                        <div class="h4 m-0 fw-bold">{{ $kpis['margen_promedio'] ?? '0%' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="kpi p-3 bg-primary text-white d-flex align-items-center gap-3">
                    <div class="icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    <div>
                        <div class="small opacity-75">Ingresos</div>
                        <div class="h4 m-0 fw-bold">{{ $kpis['ingresos'] ?? '$0.00' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="kpi p-3 bg-warning d-flex align-items-center gap-3">
                    <div class="icon bg-white"><i class="fa-solid fa-cart-flatbed"></i></div>
                    <div>
                        <div class="small">Costos</div>
                        <div class="h4 m-0 fw-bold">{{ $kpis['costos'] ?? '$0.00' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======= UTILIDAD POR PRODUCTO ======= --}}
        <div class="card card-soft mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="section-title m-0">UTILIDAD POR PRODUCTO</h6>

                    {{-- Filtros por sección (periodo) --}}
                    <form class="d-flex align-items-center gap-2" method="GET" action="{{ route('reportes.rentabilidad') }}">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input class="form-control" type="date" name="from_prod" value="{{ request('from_prod') }}">
                        </div>
                        <span class="small">a</span>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input class="form-control" type="date" name="to_prod" value="{{ request('to_prod') }}">
                        </div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-filter"></i></button>
                    </form>
                </div>

                {{-- Search local de la sección --}}
                <form method="GET" action="{{ route('reportes.rentabilidad') }}" class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control" name="q_prod" value="{{ request('q_prod') }}" placeholder="Buscar por producto, categoría o marca…">
                        <button class="btn btn-success">Buscar</button>
                        @if(request('q_prod')) <a href="{{ route('reportes.rentabilidad') }}" class="btn btn-outline-secondary">Limpiar</a> @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th class="text-end">Unidades</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">Utilidad</th>
                            <th class="text-end">Margen</th>
                            <th class="text-end">% del total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($utilidadPorProducto ?? [] as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->producto }}</td>
                                <td>{{ $row->categoria }}</td>
                                <td>{{ $row->marca }}</td>
                                <td class="text-end">{{ number_format($row->unidades ?? 0) }}</td>
                                <td class="text-end">{{ number_format($row->ingresos ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($row->costo ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($row->utilidad ?? 0, 2) }}</td>
                                <td class="text-end">{{ isset($row->margen_pct) ? number_format($row->margen_pct, 2).'%' : '—' }}</td>
                                <td class="text-end">{{ isset($row->pct_total_utilidad) ? number_format($row->pct_total_utilidad, 2).'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Sin datos para el periodo/criterio seleccionado.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación si llega un paginator --}}
                @if(isset($utilidadPorProducto) && $utilidadPorProducto instanceof \Illuminate\Contracts\Pagination\Paginator)
                    <div class="d-flex justify-content-end">{{ $utilidadPorProducto->appends(request()->query())->links() }}</div>
                @endif

                {{-- Gráfica (opcional) --}}
                <div class="mt-4">
                    <h6 class="section-title small mb-2">Top 10 por utilidad</h6>
                    <canvas id="chartTopUtilidad" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- ======= VENTAS POR USUARIO ======= --}}
        <div class="card card-soft mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="section-title m-0">VENTAS POR USUARIO</h6>

                    {{-- Filtro por periodo (sección usuarios) --}}
                    <form class="d-flex align-items-center gap-2" method="GET" action="{{ route('reportes.rentabilidad') }}">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input class="form-control" type="date" name="from_user" value="{{ request('from_user') }}">
                        </div>
                        <span class="small">a</span>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input class="form-control" type="date" name="to_user" value="{{ request('to_user') }}">
                        </div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-filter"></i></button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nombre del usuario</th>
                            <th class="text-end">No. de ventas</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Utilidad</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ventasPorUsuario ?? [] as $i => $u)
                            <tr>
                                <td>{{ is_int($i) ? $i+1 : $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $u->usuario }}</td>
                                <td class="text-end">{{ number_format($u->ventas ?? 0) }}</td>
                                <td class="text-end">{{ number_format($u->ingresos ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($u->utilidad ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Sin ventas en el periodo.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($ventasPorUsuario) && $ventasPorUsuario instanceof \Illuminate\Contracts\Pagination\Paginator)
                    <div class="d-flex justify-content-end">{{ $ventasPorUsuario->appends(request()->query())->links() }}</div>
                @endif

                {{-- Gráfica (opcional) --}}
                <div class="mt-4">
                    <h6 class="section-title small mb-2">Top usuarios por utilidad</h6>
                    <canvas id="chartUsuarios" height="120"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ======= Chart.js (opcional) ======= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function(){
            // Datos para "Top 10 por utilidad" (sección productos)
            const labelsProd = {!! json_encode(($chartProd['labels'] ?? []), JSON_UNESCAPED_UNICODE) !!};
            const dataProd   = {!! json_encode(($chartProd['data']   ?? []), JSON_UNESCAPED_UNICODE) !!};

            if (document.getElementById('chartTopUtilidad') && labelsProd.length) {
                new Chart(document.getElementById('chartTopUtilidad').getContext('2d'), {
                    type: 'bar',
                    data: { labels: labelsProd, datasets: [{ label: 'Utilidad', data: dataProd }]},
                    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
                });
            }

            // Datos para "Top usuarios por utilidad"
            const labelsUsr = {!! json_encode(($chartUsr['labels'] ?? []), JSON_UNESCAPED_UNICODE) !!};
            const dataUsr   = {!! json_encode(($chartUsr['data']   ?? []), JSON_UNESCAPED_UNICODE) !!};

            if (document.getElementById('chartUsuarios') && labelsUsr.length) {
                new Chart(document.getElementById('chartUsuarios').getContext('2d'), {
                    type: 'bar',
                    data: { labels: labelsUsr, datasets: [{ label: 'Utilidad', data: dataUsr }]},
                    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
                });
            }
        })();
    </script>
@endsection
