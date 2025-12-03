@extends('layouts.sidebar-admin')

@section('content')

    <style>
        .kpi{border:0;border-radius:14px;box-shadow:0 6px 18px rgba(10,31,68,.06);}
        .kpi .icon{width:48px;height:48px;display:grid;place-items:center;border-radius:12px;background:rgba(255,255,255,.18);}

        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .section-title{font-weight:800;letter-spacing:.04em;color:#0a2e63;}

        .subtle{opacity:.8}

        /* Compactar tablas */
        .table thead th{background:#0a2e63;color:white;border:0;}
        .table-hover tbody tr:hover{background:#f7f9ff;}

        /* Paginación */
        .pagination { gap:.25rem; }
        .pagination .page-link{ padding:.25rem .55rem; font-size:.85rem; border:0; }
        .pagination .page-item.active .page-link{
            background:var(--bs-primary);
            border-color:var(--bs-primary);
        }
    </style>

    <div class="container-xxl">

        {{-- ==================================
             ENCABEZADO
        ================================== --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <h1 class="h5 m-0">Reporte de Caducidad</h1>
                </div>
                <span class="subtle ms-2">Lotes próximos a caducar y productos vencidos</span>
            </div>
        </div>

        {{-- ==================================
             FILTRO PRINCIPAL (DÍAS)
        ================================== --}}
        <div class="card card-soft mb-4">
            <div class="card-body">

                <form method="GET" action="{{ route('reportes.caducidad') }}" class="d-flex align-items-center flex-wrap gap-3">

                    <div class="input-group" style="max-width:250px;">
                        <label class="input-group-text">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Días
                        </label>
                        <select name="dias" class="form-select">
                            <option value="30" {{ $dias==30 ? 'selected' : '' }}>30 días</option>
                            <option value="60" {{ $dias==60 ? 'selected' : '' }}>60 días</option>
                            <option value="90" {{ $dias==90 ? 'selected' : '' }}>90 días</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i>Aplicar
                    </button>

                </form>

            </div>
        </div>

        {{-- ==================================
             SECCIÓN: PRÓXIMOS A CADUCAR
        ================================== --}}
        <div class="card card-soft mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title m-0">PRÓXIMOS A CADUCAR (≤ {{ $dias }} días)</h6>
                    <span class="subtle small">
                    Mostrando {{ $proximos->firstItem() }}–{{ $proximos->lastItem() }} de {{ $proximos->total() }}
                </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Producto</th>
                            <th class="text-end">Unidades restantes</th>
                            <th class="text-end">Fecha caducidad</th>
                            <th class="text-end">Días restantes</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($proximos as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->lote }}</td>
                                <td>{{ $row->producto }}</td>
                                <td class="text-end">{{ number_format($row->unidades_restantes) }}</td>
                                <td class="text-end">{{ \Carbon\Carbon::parse($row->fecha_caducidad)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ $row->dias_restantes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No hay productos próximos a caducar en este periodo.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINACIÓN --}}
                <div class="mt-3 d-flex justify-content-end">
                    {{ $proximos->appends(['dias'=>$dias])->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

        {{-- ==================================
             SECCIÓN: YA CADUCADOS
        ================================== --}}
        <div class="card card-soft mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title m-0 text-danger">PRODUCTOS CADUCADOS</h6>
                    <span class="subtle small">
                    Mostrando {{ $caducados->firstItem() }}–{{ $caducados->lastItem() }} de {{ $caducados->total() }}
                </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Producto</th>
                            <th class="text-end">Unidades restantes</th>
                            <th class="text-end">Fecha caducidad</th>
                            <th class="text-end">Días vencidos</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($caducados as $row)
                            <tr>
                                <td class="fw-semibold text-danger">{{ $row->lote }}</td>
                                <td>{{ $row->producto }}</td>
                                <td class="text-end">{{ number_format($row->unidades_restantes) }}</td>
                                <td class="text-end">{{ \Carbon\Carbon::parse($row->fecha_caducidad)->format('d/m/Y') }}</td>
                                <td class="text-end text-danger">{{ $row->dias_vencidos }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No hay productos caducados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINACIÓN --}}
                <div class="mt-3 d-flex justify-content-end">
                    {{ $caducados->appends(['dias'=>$dias])->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>

@endsection
