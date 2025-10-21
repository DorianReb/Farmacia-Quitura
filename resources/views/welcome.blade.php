@extends('layouts.sidebar-admin')

@section('content')
    <div class="container-fluid py-4">

        {{-- ===== KPIs ===== --}}
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Ventas hoy</div>
                        <div class="fs-3 fw-bold">
                            ${{ number_format($kpi['ventas_hoy'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Tickets hoy</div>
                        <div class="fs-3 fw-bold">{{ $kpi['tickets_hoy'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Ticket promedio</div>
                        <div class="fs-3 fw-bold">
                            ${{ number_format($kpi['ticket_promedio_hoy'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">% tickets con promoción</div>
                        <div class="fs-3 fw-bold">
                            {{ number_format($kpi['pct_tickets_con_promo'] ?? 0, 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Gráficas (Ventas 14/30 días / Top productos) ===== --}}
        <div class="row g-3 mt-1">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">Ventas últimos días</span>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Rango">
                                <button type="button" class="btn btn-outline-secondary" data-range="14">14 días</button>
                                <button type="button" class="btn btn-outline-secondary active" data-range="30">30 días</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartVentas" height="120" aria-label="Gráfica de ventas" role="img"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold">Top productos por unidades (últimos 30 días)</span>
                    </div>
                    <div class="card-body">
                        <canvas id="chartTop" height="120" aria-label="Top productos" role="img"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Tablas de acción: Caducidades (FEFO) / Stock bajo ===== --}}
        <div class="row g-3 mt-1">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold">Caducidades próximas (≤ 30 días)</span>
                    </div>
                    <div class="card-body">
                        @if(($caducidades ?? collect())->isEmpty())
                            <div class="text-center text-muted py-4">Sin lotes próximos a caducar.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-nowrap">Lote</th>
                                        <th class="text-nowrap">Caduca</th>
                                        <th class="text-end">Cant.</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($caducidades as $row)
                                        <tr>
                                            <td>{{ $row->producto->nombre ?? '—' }}</td>
                                            <td>{{ $row->codigo }}</td>
                                            <td class="text-nowrap">
                                                {{ \Illuminate\Support\Carbon::parse($row->fecha_caducidad)->format('Y-m-d') }}
                                            </td>
                                            <td class="text-end">{{ $row->cantidad }}</td>
                                            <td class="text-end">${{ number_format($row->producto->precio_venta ?? 0, 2) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('productos.show', $row->producto_id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                                <a href="{{ route('promociones.create', ['producto' => $row->producto_id]) }}" class="btn btn-sm btn-outline-warning">Promoción</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold">Stock bajo</span>
                    </div>
                    <div class="card-body">
                        @if(($stockBajo ?? collect())->isEmpty())
                            <div class="text-center text-muted py-4">Sin productos con stock bajo.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end">Existencias</th>
                                        <th class="text-end">Stock mín.</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($stockBajo as $p)
                                        <tr>
                                            <td>{{ $p->nombre }}</td>
                                            <td class="text-end">
                      <span class="{{ ($p->existencias ?? 0) <= 0 ? 'text-danger' : 'text-warning' }}">
                        {{ $p->existencias ?? 0 }}
                      </span>
                                            </td>
                                            <td class="text-end">{{ $p->stock_minimo ?? 0 }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('lotes.create', ['producto' => $p->id]) }}" class="btn btn-sm btn-success">Nueva entrada</a>
                                                <a href="{{ route('productos.show', $p->id) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Sidebar extra: Promociones activas / Mini Rotación ===== --}}
        <div class="row g-3 mt-1">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold">Promociones activas hoy</span>
                    </div>
                    <div class="card-body">
                        @if(($promosActivas ?? collect())->isEmpty())
                            <div class="text-center text-muted py-4">No hay promociones activas.</div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($promosActivas as $promo)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="me-2">
                                            <div class="fw-semibold">
                                                {{ $promo->porcentaje }}% —
                                                <small class="text-muted">
                                                    {{ \Illuminate\Support\Carbon::parse($promo->fecha_inicio)->format('Y-m-d') }}
                                                    &rarr;
                                                    {{ \Illuminate\Support\Carbon::parse($promo->fecha_fin)->format('Y-m-d') }}
                                                </small>
                                            </div>
                                            @if($promo->lotes?->count())
                                                <div class="small text-muted">
                                                    {{ $promo->lotes->pluck('producto.nombre')->unique()->join(', ') }}
                                                </div>
                                            @endif
                                        </div>
                                        <a href="{{ route('promociones.show', $promo->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">Rotación (Entradas vs Salidas)</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary active" data-serie="salidas">Salidas</button>
                                <button type="button" class="btn btn-outline-secondary" data-serie="entradas">Entradas</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartRotacion" height="90" aria-label="Rotación" role="img"></canvas>
                        @if(!isset($salidasSerie) && !isset($entradasSerie))
                            <div class="text-muted small mt-2">* Provee <code>$salidasSerie</code> y <code>$entradasSerie</code> (colecciones con <code>dia</code>, <code>cantidad</code>) para mostrar esta gráfica.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Chart.js CDN (puedes mover a tu layout) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ===== Helper: formateo de datasets desde Blade =====
        const ventasSerie   = @json($ventasSerie ?? []);
        const topProductos  = @json($topProductos ?? []);
        const salidasSerie  = @json($salidasSerie ?? []);
        const entradasSerie = @json($entradasSerie ?? []);

        // ===== Gráfica Ventas (monto/tickets) =====
        const ctxV = document.getElementById('chartVentas').getContext('2d');
        const labelsVentas = ventasSerie.map(i => i.dia);
        const dataMonto    = ventasSerie.map(i => Number(i.monto ?? 0));
        const dataTickets  = ventasSerie.map(i => Number(i.tickets ?? 0));

        new Chart(ctxV, {
            type: 'line',
            data: {
                labels: labelsVentas,
                datasets: [
                    { label: 'Monto',  data: dataMonto,  tension: .25, borderWidth: 2, pointRadius: 2 },
                    { label: 'Tickets',data: dataTickets,tension: .25, borderWidth: 2, pointRadius: 2 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: true } }
            }
        });

        // ===== Gráfica Top productos =====
        const ctxTop = document.getElementById('chartTop').getContext('2d');
        const labelsTop = topProductos.map(i => i.nombre);
        const dataTop   = topProductos.map(i => Number(i.unidades ?? 0));

        new Chart(ctxTop, {
            type: 'bar',
            data: { labels: labelsTop, datasets: [{ label: 'Unidades', data: dataTop, borderWidth: 1 }] },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });

        // ===== Gráfica Rotación =====
        const ctxR = document.getElementById('chartRotacion').getContext('2d');
        const labelsR = (salidasSerie.length ? salidasSerie : entradasSerie).map(i => i.dia);
        const datosSalidas  = salidasSerie.map(i => Number(i.cantidad ?? 0));
        const datosEntradas = entradasSerie.map(i => Number(i.cantidad ?? 0));

        const chartR = new Chart(ctxR, {
            type: 'line',
            data: {
                labels: labelsR,
                datasets: [{ label: 'Salidas', data: datosSalidas, tension: .25, borderWidth: 2, pointRadius: 2 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: true } }
            }
        });

        // Toggle serie Rotación
        document.querySelectorAll('[data-serie]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-serie]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const serie = btn.getAttribute('data-serie');
                chartR.data.datasets = [{
                    label: serie === 'entradas' ? 'Entradas' : 'Salidas',
                    data:  serie === 'entradas' ? datosEntradas : datosSalidas,
                    tension: .25, borderWidth: 2, pointRadius: 2
                }];
                chartR.update();
            });
        });

        // Toggle rango 14/30 días (solo UI; el backend debe filtrar la colección)
        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Opcional: redirige con querystring ?rango=14|30 para regenerar $ventasSerie
                // location.search = new URLSearchParams({ rango: btn.dataset.range }).toString();
            });
        });
    </script>
@endpush
