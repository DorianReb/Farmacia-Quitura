@extends('layouts.sidebar-admin') {{-- o el layout de admin que uses --}}

@section('content')
    <style>
        /* Pequeños toques visuales para el dashboard */
        .kpi {
            border: 0; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(10, 31, 68, .06);
        }
        .kpi .icon {
            width: 48px; height: 48px; display: grid; place-items: center;
            border-radius: 12px; background: rgba(255,255,255,.18);
        }
        .table thead th {
            background: #0a2e63;  /* azul marino fuerte */
            color: #fff; border: 0;
        }
        .table-hover tbody tr:hover { background: #f7f9ff; }
        .section-title {
            font-weight: 800; letter-spacing:.04em; color:#0a2e63;
        }
        .card-soft {
            border: 0; border-radius: 14px;
            box-shadow: 0 8px 20px rgba(16,24,40,.06);
        }
    </style>

    {{-- ================= KPIs ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="kpi p-3 bg-azul-marino text-white d-flex align-items-center gap-3">
                <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <div class="small opacity-75">Ingresos hoy</div>
                    <div class="h4 m-0 fw-bold">
                        {{ $kpis['ingresos_hoy'] ?? '$0.00' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="kpi p-3 bg-warning text-dark d-flex align-items-center gap-3">
                <div class="icon bg-white"><i class="fa-solid fa-box-open"></i></div>
                <div>
                    <div class="small">Productos con stock bajo</div>
                    <div class="h4 m-0 fw-bold">{{ $kpis['stock_bajo'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="kpi p-3 bg-orange text-white d-flex align-items-center gap-3" style="--bs-bg-opacity:1;background:#ff7a1a;">
                <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="small opacity-90">Próximas a caducar (30 días)</div>
                    <div class="h4 m-0 fw-bold">{{ $kpis['por_caducar_30d'] ?? 0 }} uds.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="kpi p-3 bg-danger text-white d-flex align-items-center gap-3">
                <div class="icon"><i class="fa-solid fa-ban"></i></div>
                <div>
                    <div class="small opacity-90">Total caducadas</div>
                    <div class="h4 m-0 fw-bold">{{ $kpis['caducadas'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== Próximos a caducar ========== --}}
    <div class="card card-soft mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="section-title m-0">PRÓXIMOS 5 PRODUCTOS A CADUCAR</h6>
                <a href={{route("producto.index")}} class="btn btn-primary btn-sm">
                    Ver todos los productos
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Unidades restantes</th>
                        <th>Fecha de caducidad</th>
                        <th>Días restantes</th>
                        <th class="text-center" style="width:160px;">Acciones rápidas</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($proximosACaducar ?? [] as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->producto }}</td>
                            <td>{{ $item->lote }}</td>
                            <td>{{ number_format($item->unidades_restantes) }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->fecha_caducidad)->format('d/m/Y') }}</td>
                            <td>{{ $item->dias_restantes }} días</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('lote.index', $item->id) }}" class="btn btn-outline-secondary" title="Ver">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('lote.edit', $item->id) }}" class="btn btn-outline-secondary" title="Editar">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <a href="{{ route('venta.index', ['producto' => $item->producto_id ?? null]) }}" class="btn btn-outline-secondary" title="Venta rápida">
                                        <i class="fa-solid fa-bolt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Sin productos próximos a caducar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========== Top / Bottom vendidos (últimos 30 días) ========== --}}
    <h6 class="section-title text-center mb-3">10 PRODUCTOS ESTRELLA/MENOS VENDIDOS POR UNIDAD (Últimos 30 días)</h6>
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card card-soft h-100">
                <div class="card-header bg-light fw-bold">Más vendidos</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Producto</th>
                                <th class="text-end">Unidades vendidas</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($topVendidos ?? []) as $i => $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $row->producto }}</td>
                                    <td class="text-end">{{ number_format($row->unidades) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Si usas paginador: --}}
                    @if(isset($topVendidos) && $topVendidos instanceof \Illuminate\Contracts\Pagination\Paginator)
                        <div class="d-flex justify-content-end">{{ $topVendidos->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card card-soft h-100">
                <div class="card-header bg-light fw-bold">Menos vendidos</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Producto</th>
                                <th class="text-end">Unidades vendidas</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($menosVendidos ?? []) as $i => $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $row->producto }}</td>
                                    <td class="text-end">{{ number_format($row->unidades) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(isset($menosVendidos) && $menosVendidos instanceof \Illuminate\Contracts\Pagination\Paginator)
                        <div class="d-flex justify-content-end">{{ $menosVendidos->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========== Stock bajo ========== --}}
    <h6 class="section-title text-center mb-2">5 PRODUCTOS CON MÁS STOCK BAJO</h6>
    <div class="card card-soft">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Producto</th>
                        <th class="text-end">Existencias totales</th>
                        <th class="text-end">Stock mínimo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($stockBajo ?? []) as $row)
                        <tr @class(['table-warning'=> ($row->existencias ?? 0) <= ($row->stock_minimo ?? 0)])>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $row->producto }}</td>
                            <td class="text-end">{{ number_format($row->existencias) }}</td>
                            <td class="text-end">{{ number_format($row->stock_minimo) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Sin alertas de stock</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
