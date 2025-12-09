@php
    use Carbon\Carbon;

    $usuario = $venta->usuario;
    $nombreVendedor = 'Sistema';
    if ($usuario) {
        $nombreVendedor = trim(
            ($usuario->nombre ?? '') . ' ' .
            ($usuario->apellido_paterno ?? '') . ' ' .
            ($usuario->apellido_materno ?? '')
        );
        $nombreVendedor = $nombreVendedor !== '' ? $nombreVendedor : 'Sistema';
    }

    $totalAhorro    = 0;
    $totalArticulos = 0;
@endphp

<style>
    .ticket-container {
        width: 260px;
        margin: 0 auto;
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
    }

    .ticket-container * {
        box-sizing: border-box;
    }

    .centrado { text-align: center; }
    .titulo   { font-weight: bold; font-size: 13px; }
    .linea    { border-top: 1px dashed #000; margin: 4px 0; }
    .linea-gruesa { border-top: 1px solid #000; margin: 6px 0; }
    .texto-small  { font-size: 9px; }

    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 6px; }
    .mt-2 { margin-top: 6px; }

    .producto-nombre { font-weight: bold; }

    table {
        width: 100%;
        border-collapse: collapse;
    }
    td, th {
        padding: 0;
        margin: 0;
        border: 0;
    }
    .text-right { text-align: right; }
    .text-left  { text-align: left; }
</style>

<div class="ticket-container">
    {{-- ENCABEZADO --}}
    <div class="centrado mb-2">
        <div class="titulo">FARMACIA QUITURA</div>
        <div>2734 Francisco Pérez Ríos</div>
        <div>Colorines, Estado de México</div>
    </div>

    <div class="linea"></div>

    {{-- DATOS GENERALES --}}
    <table class="mb-2">
        <tr>
            <td class="text-left">Ticket:</td>
            <td class="text-right">#{{ $venta->id }}</td>
        </tr>
        <tr>
            <td class="text-left">Fecha/Hora:</td>
            <td class="text-right">
                {{ Carbon::parse($venta->fecha)->format('d/m/Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <td class="text-left">Vendedor:</td>
            <td class="text-right">{{ $nombreVendedor }}</td>
        </tr>
    </table>

    <div class="linea-gruesa"></div>

    {{-- ARTÍCULOS --}}
    <div class="mb-2 centrado">
        <strong>DETALLE DE ARTÍCULOS</strong>
    </div>

    @foreach($venta->detalles as $detalle)
        @php
            // Cantidad
            $cant = max((int) $detalle->cantidad, 0);

            // % de descuento (viene del controlador en $detalle->descuento)
            $porcDesc = max((float) ($detalle->descuento ?? 0), 0);

            // Subtotal final de la línea (ya con descuento), viene de la BD / procedure
            $subtotalFinal = (float) ($detalle->subtotal ?? 0);

            // Variables a calcular
            $precio        = 0.0; // precio unitario de lista
            $subtotalAntes = 0.0; // antes del descuento
            $montoDesc     = 0.0; // cantidad descontada

            if ($subtotalFinal > 0 && $cant > 0) {
                if ($porcDesc > 0 && $porcDesc < 100) {
                    // Recuperamos el subtotal ANTES del descuento
                    $subtotalAntes = $subtotalFinal / (1 - $porcDesc / 100);

                    // Monto de descuento
                    $montoDesc = $subtotalAntes - $subtotalFinal;
                } else {
                    // Sin promoción: antes y después son iguales
                    $subtotalAntes = $subtotalFinal;
                    $montoDesc     = 0.0;
                }

                // Precio unitario de lista
                $precio = $subtotalAntes / $cant;

                // Redondeos a 2 decimales
                $subtotalAntes = round($subtotalAntes, 2);
                $montoDesc     = round($montoDesc, 2);
                $precio        = round($precio, 2);
            }

            // Acumulados del ticket
            $totalAhorro    += $montoDesc;
            $totalArticulos += $cant;
        @endphp


        <div class="mb-1">
            <div class="producto-nombre">
                {{ $detalle->producto_nombre ?? ($detalle->lote->producto->nombre_comercial ?? 'Producto') }}
            </div>

            @if(!empty($detalle->lote_codigo))
                <div class="texto-small">
                    Lote: {{ $detalle->lote_codigo }}
                </div>
            @endif

            <div class="texto-small">
                Cant: {{ $cant }} x ${{ number_format($precio, 2) }}
            </div>

            @if($porcDesc > 0)
                <div class="texto-small">
                    Precio antes desc.: ${{ number_format($subtotalAntes, 2) }}
                </div>
                <div class="texto-small">
                    Descuento ({{ number_format($porcDesc, 2) }}%): -${{ number_format($montoDesc, 2) }}
                </div>
            @else
                <div class="texto-small">
                    Sin descuento aplicado
                </div>
            @endif

            <table>
                <tr>
                    <td class="text-left texto-small">Subtotal final:</td>
                    <td class="text-right texto-small">
                        ${{ number_format($subtotalFinal, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="linea"></div>
    @endforeach

    {{-- TOTALES --}}
    <div class="mt-2">
        <table>
            <tr>
                <td class="text-left"><strong>Núm. de artículos:</strong></td>
                <td class="text-right"><strong>{{ $totalArticulos }}</strong></td>
            </tr>
            <tr>
                <td class="text-left"><strong>Total productos:</strong></td>
                <td class="text-right">
                    <strong>${{ number_format($venta->total, 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td class="text-left">Monto recibido:</td>
                <td class="text-right">
                    ${{ number_format($venta->monto_recibido ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="text-left">Cambio:</td>
                <td class="text-right">
                    ${{ number_format($venta->cambio ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="text-left">Usted ahorró:</td>
                <td class="text-right">
                    ${{ number_format($totalAhorro, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="linea-gruesa"></div>

    <div class="centrado texto-small mt-2">
        Todos los precios ya incluyen IVA.
    </div>
    <div class="centrado texto-small mt-1">
        Para cualquier aclaración es necesario presentar este ticket.
    </div>
    <div class="centrado texto-small mt-1">
        ¡Gracias por su compra!
    </div>

    <div class="centrado texto-small mt-2">
        <a href="{{ route('venta.ticket.pdf', $venta->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
            Descargar PDF
        </a>
    </div>
</div>
