@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .kpi{border:0;border-radius:14px;box-shadow:0 6px 18px rgba(10,31,68,.06);}
        .kpi .icon{width:48px;height:48px;display:grid;place-items:center;border-radius:12px;background:rgba(255,255,255,.18);}
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .section-title{font-weight:800;letter-spacing:.04em;color:#0a2e63;}
        .subtle{opacity:.8}

        .table thead th{background:#0a2e63;color:#fff;border:0;}
        .table-hover tbody tr:hover{background:#f7f9ff;}

        /* Paginación compacta */
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

        {{-- ENCABEZADO --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <h1 class="h5 m-0">Reporte de Stock Bajo</h1>
                </div>
                <span class="subtle ms-2">
                    Productos cuya existencia es menor o igual al stock mínimo configurado
                </span>
            </div>
        </div>

        {{-- KPI SENCILLO --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="kpi p-3 bg-warning d-flex align-items-center gap-3">
                    <div class="icon bg-white">
                        <i class="fa-solid fa-box-open text-warning"></i>
                    </div>
                    <div>
                        <div class="small opacity-75">Productos con stock bajo</div>
                        <div class="h4 m-0 fw-bold">{{ number_format($productos->total()) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA PRINCIPAL --}}
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Stock mínimo</th>
                            <th class="text-end">Existencias actuales</th>
                            <th class="text-end">Diferencia</th>
                            <th class="text-end">Nivel de alerta</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($productos as $p)
                            @php
                                $diferencia = ($p->stock_minimo ?? 0) - ($p->existencias ?? 0);
                                // Regla sencilla de colores
                                if ($p->existencias <= 0) {
                                    $badgeClass = 'bg-danger';
                                    $label = 'Sin existencias';
                                } elseif ($diferencia >= ($p->stock_minimo * 0.5)) {
                                    $badgeClass = 'bg-danger';
                                    $label = 'Crítico';
                                } else {
                                    $badgeClass = 'bg-warning text-dark';
                                    $label = 'Bajo';
                                }
                            @endphp
                            <tr>
                                <td class="fw-semibold">
                                    {{ $p->nombre_comercial ?? '—' }}
                                    @if(!empty($p->descripcion))
                                        <div class="text-muted small">{{ $p->descripcion }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($p->stock_minimo ?? 0) }}</td>
                                <td class="text-end">{{ number_format($p->existencias ?? 0) }}</td>
                                <td class="text-end">
                                    {{ number_format($diferencia, 0) }}
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No hay productos con stock bajo en este momento.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $productos->firstItem() ?? 0 }}–{{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }}
                </small>

                @if($productos->hasPages())
                    <nav aria-label="Paginación de productos con stock bajo" class="order-1 order-md-2">
                        {{ $productos->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>

    </div>
@endsection
