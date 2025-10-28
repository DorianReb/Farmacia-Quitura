<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venta #{{ $venta->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos específicos para impresión */
        body {
            background-color: #f8f9fa;
        }
        .ticket-container {
            max-width: 400px; /* Ancho típico de un recibo */
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3, h4 { margin-top: 0; }
        .item-row {
            border-bottom: 1px dotted #ccc;
            padding: 5px 0;
        }
        @media print {
            .ticket-container {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <h4 class="text-center mb-4">Farmacia Quitura</h4>
        <p class="text-center mb-4">¡Gracias por su compra!</p>
        
        <p class="mb-1"><strong>Recibo N°:</strong> #{{ $venta->id }}</p>
        <p class="mb-1"><strong>Fecha y Hora:</strong> {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i:s') }}</p>
        <p class="mb-3"><strong>Vendedor:</strong> {{ $venta->usuario->name ?? 'Sistema' }}</p>

        <h6 class="mt-4 mb-2">Artículos Vendidos:</h6>
        <div class="table-responsive">
            <table class="table table-sm border-top">
                <thead>
                    <tr>
                        <th class="p-1">Producto</th>
                        <th class="text-center p-1">Cant.</th>
                        <th class="text-end p-1">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $detalle)
                    <tr class="item-row">
                        <td class="p-1" style="font-size: 0.85rem;">
                            {{ $detalle->lote->producto->nombre_comercial ?? 'Producto Desconocido' }}
                        </td>
                        <td class="text-center p-1">{{ $detalle->cantidad }}</td>
                        <td class="text-end p-1">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <h5 class="fw-bold">TOTAL: ${{ number_format($venta->total, 2) }}</h5>
        </div>

        <p class="text-center mt-4">Página web de la empresa / Redes Sociales</p>
    </div>

    {{-- Botón para Imprimir (solo visible en pantalla, no en el resultado de impresión) --}}
    <div class="text-center no-print mt-3">
        <button onclick="window.print()" class="btn btn-success"><i class="fa-solid fa-print me-1"></i> Imprimir Recibo</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>

</body>
</html>