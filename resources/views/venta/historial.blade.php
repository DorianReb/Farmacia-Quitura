@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft{border:0;border-radius:14px;box-shadow:0 8px 20px rgba(16,24,40,.06);}
        .dt-control{cursor:pointer;text-align:center;width:42px}
        .dt-control i{transition:transform .2s ease}
        tr.shown .dt-control i{transform:rotate(90deg)}
    </style>

    <div class="container-xxl">
        {{-- Encabezado --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-receipt"></i>
                    <h1 class="h4 m-0">Historial de ventas</h1>
                </div>
            </div>
        </div>

        {{-- Filtros por fecha + búsqueda --}}
        <form method="GET" action="{{ route('venta.historial') }}" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Buscar (folio o usuario)</label>
                <div class="input-group">
                    <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Folio o nombre…">
                    @if($q || $desde || $hasta)
                        <a href="{{ route('venta.historial') }}" class="btn btn-outline-secondary">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="card card-soft">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaHistorial" class="table table-hover align-middle w-100">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th></th>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ventas as $venta)
                            @php
                                $usuario = trim(($venta->usuario->nombre ?? '').' '.($venta->usuario->apellido_paterno ?? '').' '.($venta->usuario->apellido_materno ?? ''));
                                $detalles = $venta->detalles->map(fn($d)=>[
                                    'producto'  => $d->producto->nombre ?? '—',
                                    'lote'      => $d->lote->numero ?? null,
                                    'precio'    => (float)$d->precio_unitario,
                                    'descuento' => (float)$d->descuento, // %
                                    'cantidad'  => (int)$d->cantidad,
                                    'subtotal'  => (float)$d->subtotal,
                                ]);
                            @endphp
                            <tr data-detalles='@json($detalles)'>
                                <td class="dt-control"><i class="fa-solid fa-angle-right"></i></td>
                                <td>#{{ $venta->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                <td>{{ $usuario }}</td>
                                <td class="text-end">${{ number_format($venta->total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- paginación de Laravel --}}
                <div class="mt-3 d-flex justify-content-end">
                    {{ $ventas->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- DataTables 2 (vanilla, SIN jQuery) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.1.7/datatables.min.css"/>
    <script src="https://cdn.datatables.net/v/bs5/dt-2.1.7/datatables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nf = new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'});

            function detalleHTML(detalles){
                const rows = detalles.map(d => `
            <tr>
                <td class="fw-semibold">${d.producto ?? '—'} ${d.lote ? `<span class="badge bg-secondary ms-2">Lote ${d.lote}</span>`:''}</td>
                <td class="text-end">${nf.format(d.precio ?? 0)}</td>
                <td class="text-end">${(d.descuento ?? 0)}%</td>
                <td class="text-end">${d.cantidad ?? 0}</td>
                <td class="text-end">${nf.format(d.subtotal ?? 0)}</td>
            </tr>
        `).join('');
                return `
            <div class="p-2 ps-5">
                <table class="table table-sm table-borderless m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Desc.</th>
                            <th class="text-end">Cant.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
            }

            const table = new DataTable('#tablaHistorial', {
                paging: false,      // usamos paginación de Laravel
                searching: false,   // filtros vienen del form GET
                info: false,
                order: [[2,'desc']], // por fecha
                columnDefs: [
                    { orderable:false, targets:0 },
                    { className:'text-end', targets:4 },
                ]
            });

            document.querySelector('#tablaHistorial tbody').addEventListener('click', (e)=>{
                const cell = e.target.closest('td.dt-control');
                if (!cell) return;
                const tr  = cell.closest('tr');
                const row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide(); tr.classList.remove('shown');
                } else {
                    const detalles = JSON.parse(tr.dataset.detalles || '[]');
                    row.child(detalleHTML(detalles)).show();
                    tr.classList.add('shown');
                }
            });
        });
    </script>
@endsection
