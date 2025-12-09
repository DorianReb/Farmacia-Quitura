<div class="modal fade" id="menuProductosModal" tabindex="-1" aria-labelledby="menuProductosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-azul-marino text-white">
                <h5 class="modal-title" id="menuProductosModalLabel">Menú de Productos (Resultados)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if (isset($productosBuscados) && $productosBuscados->count())
                    @include('producto.menu', ['productos' => $productosBuscados])
                @elseif (request('q'))
                    <p class="text-center text-muted">No se encontraron productos para "{{ request('q') }}".</p>
                @else
                    <p class="text-center text-muted">Ingresa un nombre para buscar productos.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
