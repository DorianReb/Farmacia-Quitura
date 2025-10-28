@extends('layouts.sidebar-admin')

@section('content')
<div class="container-xxl">

    {{-- ENCABEZADO Y TÍTULO --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 mb-md-4 gap-3">
        <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h1 class="h4 m-0">Historial de Transacciones</h1>
        </div>
        
        {{-- Botón de Regreso a la Vista de Venta --}}
        <a href="{{ route('venta.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-cash-register me-1"></i> Ir a Vender
        </a>
    </div>

    {{-- FILTROS DE BÚSQUEDA AVANZADA --}}
    <div class="card card-soft mb-4 p-3">
        <form method="GET" action="{{ route('venta.historial') }}" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label for="q" class="form-label mb-1"><small>Buscar (ID Venta/Vendedor)</small></label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $q ?? '' }}" placeholder="ID, nombre o apellido">
            </div>
            <div class="col-md-3 col-lg-2">
                <label for="desde" class="form-label mb-1"><small>Fecha Desde</small></label>
                <input type="date" name="desde" id="desde" class="form-control" value="{{ $desde ?? '' }}">
            </div>
            <div class="col-md-3 col-lg-2">
                <label for="hasta" class="form-label mb-1"><small>Fecha Hasta</small></label>
                <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $hasta ?? '' }}">
            </div>
            <div class="col-md-2 col-lg-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i></button>
            </div>
            @if(isset($q) || isset($desde) || isset($hasta))
            <div class="col-1">
                <a href="{{ route('venta.historial') }}" class="btn btn-outline-secondary w-100" title="Limpiar Filtros">
                    <i class="fa-regular fa-circle-xmark"></i>
                </a>
            </div>
            @endif
        </form>
    </div>

    {{-- TABLA PRINCIPAL DE VENTAS --}}
    <div class="card card-soft">
        <div class="card-header">Ventas Registradas (Resumen)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>ID Venta</th>
                            <th>Fecha</th>
                            <th>Vendedor</th>
                            <th class="text-end">Total</th>
                            <th class="text-end" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                        <tr>
                            <td class="fw-bold">#{{ $venta->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('Y-m-d H:i') }}</td>
                            <td>{{ $venta->usuario->nombreCompleto ?? 'N/A' }}</td>
                            <td class="text-end h6 m-0">${{ number_format($venta->total, 2) }}</td>
                            <td class="text-end">
                                
                                {{-- ACCIÓN 1: VER TICKET (Activa) --}}
                                    <a href="#" onclick="event.preventDefault(); cargarTicketEnModal({{ $venta->id }})" 
                                    class="btn btn-sm btn-info text-white rounded-pill shadow-sm" title="Ver Ticket">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                {{-- ACCIÓN 2: ANULAR VENTA (Formulario DELETE) --}}
                                <form action="{{ route('venta.anular', $venta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de anular la venta #{{ $venta->id }}? Esto restaurará el stock.')">
                                    @csrf 
                                    @method('DELETE') {{-- Usamos DELETE para anular --}}
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill shadow-sm" title="Anular Venta">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-magnifying-glass-arrow-right fa-2x mb-2"></i>
                                <p class="m-0">No se encontraron ventas que coincidan con los criterios de búsqueda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginación --}}
        @if(isset($ventas) && method_exists($ventas, 'links'))
        <div class="card-footer bg-white border-0 pt-3 pb-2 d-flex justify-content-center">
            {{ $ventas->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

@endsection
{{-- MODAL PARA VISUALIZAR E IMPRIMIR TICKET --}}
@push('modals')
<div class="modal fade" id="modalVerTicket" tabindex="-1" aria-labelledby="modalVerTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="modalVerTicketLabel">Ticket de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="ticketContent">
                {{-- Aquí se inyectará el contenido HTML del ticket --}}
                <div class="text-center py-5">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnPrintTicket">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalVerTicketElement = document.getElementById('modalVerTicket');
    const ticketContent = document.getElementById('ticketContent');
    const btnPrintTicket = document.getElementById('btnPrintTicket');

    // 1. FUNCIÓN PRINCIPAL PARA CARGAR EL TICKET
    window.cargarTicketEnModal = async function(ventaId) {
        // Muestra el spinner de carga
        ticketContent.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando ticket #` + ventaId + `...</p></div>`;
        const modalInstance = new bootstrap.Modal(modalVerTicketElement);
        modalInstance.show();

        try {
            // Llama a la ruta de Laravel que devuelve el HTML renderizado del ticket
            const url = '{{ route('venta.ticket', ['venta' => 'VENTA_ID']) }}'.replace('VENTA_ID', ventaId);
            const response = await fetch(url);

            if (!response.ok) throw new Error('No se pudo obtener la vista del ticket. Estado: ' + response.status);

            const htmlContent = await response.text();
            
            // Inyectar el HTML dentro del modal
            ticketContent.innerHTML = htmlContent;

        } catch (error) {
            console.error('[ERROR cargarTicketEnModal]', error);
            ticketContent.innerHTML = `<div class="alert alert-danger">Error al cargar el ticket. ${error.message}</div>`;
        }
    }

    // 2. EVENTO PARA EL BOTÓN DE IMPRESIÓN DENTRO DEL MODAL
    if (btnPrintTicket) {
        btnPrintTicket.addEventListener('click', function() {
            // Abre el diálogo de impresión para el contenido específico
            const content = ticketContent.innerHTML;
            const printWindow = window.open('', '', 'height=500,width=500');
            printWindow.document.write('<html><head><title>Imprimir Ticket</title>');
            // Opcional: añade CSS de impresión aquí si es necesario
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        });
    }

});
</script>
@endpush