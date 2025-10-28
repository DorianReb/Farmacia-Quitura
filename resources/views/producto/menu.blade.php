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
                 data-id="{{ $producto->id }}"
                 data-nombre="{{ $producto->nombre_comercial }}"
                 data-precio="{{ $producto->precio ?? 0 }}">
                <img src="{{ $producto->imagen_url ?? 'https://via.placeholder.com/150.png?text=Producto' }}" 
                     alt="{{ $producto->nombre_comercial }}">
                <p>{{ $producto->nombre_comercial }}</p>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p class="text-center text-muted mt-4">No se encontraron productos.</p>
        </div>
    @endforelse
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // 🟦 1. La barra de búsqueda redirige normalmente (GET)
    const formBuscar = document.getElementById('formBuscarEnMenu');
    formBuscar.addEventListener('submit', function(e) {
        // Deja que el formulario se envíe normalmente al controlador
        // No hacemos preventDefault aquí
    });

    // 🟩 2. Click en producto -> lo agrega a la lista de venta
    const productos = document.querySelectorAll('.producto-card');
    productos.forEach(card => {
        card.addEventListener('click', () => {
            const id = card.dataset.id;
            const nombre = card.dataset.nombre;
            const precio = parseFloat(card.dataset.precio);

            // Aquí puedes personalizar qué hace al añadir
            agregarAListaDeVenta({ id, nombre, precio });
        });
    });

    // 🛍️ Función que añade un producto a la lista de venta (ejemplo básico)
    function agregarAListaDeVenta(producto) {
        // Puedes reemplazar esto con un fetch/AJAX o manipular tu tabla directamente
        console.log('Producto agregado a venta:', producto);
        alert(`Producto añadido: ${producto.nombre} - $${producto.precio.toFixed(2)}`);
    }

});
</script>
