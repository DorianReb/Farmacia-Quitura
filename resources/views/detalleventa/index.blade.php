@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(16, 24, 40, .06);
        }
        .section-title {
            font-weight: 800;
            letter-spacing: .04em;
        }
        .table-hover tbody tr:hover {
            background: #f7f9ff;
        }
        .table thead {
            background: #002b5b; /* azul marino */
            color: white;
        }
    </style>

    <div class="container-xxl">
        {{-- Encabezado --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 mb-md-4 gap-3">
            <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-list-check"></i>
                <h1 class="h4 m-0">Registro de Detalle de Ventas</h1>
            </div>

            {{-- BOTÓN DE REGRESO (Volver a Vender) --}}
            <a href="{{ route('venta.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver a vender
            </a>
        </div>

        {{-- Tabla Principal de Detalle de Ventas --}}
        <div class="card card-soft">
            <div class="card-header text-dark d-flex justify-content-between align-items-center py-2">
                <h2 class="h6 m-0 section-title text-uppercase">
                    Detalles de productos vendidos
                </h2>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Lote / Caducidad</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Vendedor</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($detallesVenta as $detalle)
                            @php
                                $lote = $detalle->lote;
                                $producto = $lote->producto ?? null;
                                $fechaCad = $lote?->fecha_caducidad
                                    ? \Carbon\Carbon::parse($lote->fecha_caducidad)->format('d/m/Y')
                                    : 'N/A';
                            @endphp
                            <tr>
                                <td>{{ $producto->nombre_comercial ?? 'N/A' }}</td>
                                <td>{{ $producto->marca->nombre ?? 'N/A' }}</td>
                                <td>
                                    {{ $lote->codigo ?? 'N/A' }}
                                    /
                                    {{ $fechaCad }}
                                </td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>${{ number_format($detalle->subtotal, 2) }}</td>
                                <td>{{ $detalle->venta->usuario->nombre ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-box-open fa-2x mb-2"></i>
                                    <p class="m-0">No se encontraron detalles de ventas.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(isset($detallesVenta) && method_exists($detallesVenta, 'links'))
                <div class="card-footer bg-white border-0 pt-3 pb-2 d-flex justify-content-center">
                    {{ $detallesVenta->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@endsection
