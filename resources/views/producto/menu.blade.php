<style>
/* Estilos para las tarjetas del menú */
.producto-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.producto-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}
.producto-card img {
    max-height: 100px;
    width: auto;
    margin: 0 auto 10px;
    object-fit: contain;
}
.producto-card p {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>

{{-- 🔍 Barra de búsqueda --}}
{{-- Esta barra enviará el 'q' al controlador que renderiza este modal dinámicamente --}}
<form id="formBuscarEnMenu" class="mb-3" method="GET" action="{{ route('producto.menu') }}">
    <div class="input-group">
        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="search" id="inputBuscarEnMenu" class="form-control" 
               name="q"
               placeholder="Buscar producto en el menú..." 
               value="{{ request('q') }}">
        <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
</form>

{{-- 🛒 Grid de productos --}}
<div class="row g-3">
    @forelse($productos as $producto)
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="producto-card" 
                 {{-- CAMBIO CLAVE: Usa el código de barras para la función de detalle/API --}}
                 data-codigo-barras="{{ $producto->codigo_barras }}"
                 data-nombre="{{ $producto->nombre_comercial }}"
                 data-precio="{{ $producto->precio_venta ?? $producto->precio ?? 0 }}">
                
                <img src="{{ $producto->imagen_url ?? 'https://via.placeholder.com/150.png?text=Producto' }}" 
                     alt="{{ $producto->nombre_comercial }}">
                <p>{{ $producto->nombre_comercial }}</p>
                
                {{-- Si quieres mostrar el stock mínimo en el menú (opcional) --}}
                @if(isset($producto->existencias))
                    <small class="text-muted">Stock: {{ $producto->existencias }}</small>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <p class="text-center text-muted mt-4">No se encontraron productos.</p>
        </div>
    @endforelse
</div>

{{-- Paginación si es una colección paginada --}}
@if(isset($productos) && method_exists($productos, 'links'))
    <div class="mt-3 d-flex justify-content-center">
        {{ $productos->appends(['q' => request('q')])->links() }} 
        {{-- Usar appends(['q' => request('q')]) para mantener el término de búsqueda al cambiar de página --}}
    </div>
@endif

{{-- 
    LA LÓGICA DE JAVASCRIPT SE ELIMINA DE AQUÍ.
    El evento 'click' en '.producto-card' es manejado por el Event Listener delegado
    en venta.scripts.blade.php (modalMenuBody.addEventListener('click', ...))
--}}