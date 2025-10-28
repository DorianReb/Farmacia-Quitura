@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    let listaVenta = [];
    const tbody = document.querySelector('#listaVentaTable tbody');
    const totalSpan = document.getElementById('totalVentaSpan');

    const formBuscarProducto = document.getElementById('formBuscarProducto');
    const inputCodigo = document.getElementById('codigo_barras_input');
    
    // NOTA: Inicialización segura de los Modales para evitar el error 'backdrop'
    const modalElement = document.getElementById('modalAgregarManual');
    const formAgregarManual = document.getElementById('formAgregarManual');
    const modalManual = modalElement ? new window.bootstrap.Modal(modalElement) : null; 
    const inputCodigoManual = document.getElementById('manual_codigo_barras');
    const inputCantidadManual = document.getElementById('manual_cantidad');

    // --- SELECTOR DEL MODAL DE DETALLES ---
    const modalDetallesElement = document.getElementById('modalDetallesProducto');
    const modalDetalles = modalDetallesElement ? new window.bootstrap.Modal(modalDetallesElement) : null;
    
    const inputScanner = document.getElementById('codigo_barras_input');
    const modalMenu = new window.bootstrap.Modal(document.getElementById('modalMenuProductos'));
    const modalMenuBody = document.getElementById('modalMenuProductosBody');


    // ========================================================
    // FUNCIÓN: Renderizar la tabla de venta (RENDERTA LA LISTA DE VENTA)
    // ========================================================
    function renderTabla() {
        try {
            tbody.innerHTML = ''; 
            let total = 0;
            
            listaVenta.forEach((item, index) => { 
                total += item.subtotal;
                tbody.innerHTML += `<tr>
                    <td>${item.codigo_barras}</td>
                    <td>${item.nombre}</td>
                    <td>$${item.precio.toFixed(2)}</td> 
                    <td>${item.cantidad}</td>
                    <td>${item.stock}</td>
                    <td>${item.lote}</td>
                    <td>-</td>
                    <td>$${item.subtotal.toFixed(2)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">Eliminar</button>
                    </td>
                </tr>`; 
            });
            
            totalSpan.textContent = `$${total.toFixed(2)}`;

        } catch (e) {
            console.error('[ERROR] renderTabla()', e);
        }
    }

    // ========================================================
    // FUNCIÓN: Eliminar producto
    // ========================================================
    window.eliminarProducto = function(index){
        listaVenta.splice(index, 1);
        renderTabla();
    }

    // ========================================================
    // FUNCIÓN: Mostrar datos en la tarjeta principal (RESTAURADA)
    // ========================================================
    function mostrarProductoUI(producto) {
        try {
            document.getElementById('infoProducto').classList.remove('d-none');
            document.getElementById('placeholderProducto').classList.add('d-none');

            // Imagen
            document.getElementById('producto_imagen').src = producto.imagen_url ?? producto.imagen ?? 'https://via.placeholder.com/250x150.png?text=Producto';

            // UBICACIÓN (Lee el Accesor del modelo)
            document.getElementById('producto_ubicacion').textContent = producto.ubicaciones_texto ?? '-';

            // Nombre científico
            document.getElementById('producto_nombre_cientifico').textContent = producto.nombre_cientifico ?? '-';
            
            // Forma farmacéutica, Marca, Presentación, Categoría
            document.getElementById('producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? '-';
            document.getElementById('producto_marca').textContent = producto.marca?.nombre ?? '-';
            document.getElementById('producto_presentacion').textContent = producto.presentacion?.nombre ?? '-';
            document.getElementById('producto_categoria').textContent = producto.categoria?.nombre ?? '-';
            
            // Datos base
            document.getElementById('producto_nombre').textContent = producto.nombre_comercial ?? '-';
            document.getElementById('producto_codigo').textContent = producto.codigo_barras ?? '-';
            document.getElementById('producto_contenido').textContent = producto.contenido ?? '-';
            document.getElementById('producto_receta').textContent = producto.requiere_receta ? 'Sí' : 'No';

        } catch (e) {
            console.error('[ERROR] mostrarProductoUI()', e);
        }
    }
    
    // ========================================================
    // FUNCIÓN: Llenar el Modal de Detalles (detalles_producto_)
    // ========================================================
    function mostrarProductoDetalles(producto) {
        if (!modalDetalles) return;

        try {
            // Llenar campos del modal
            document.getElementById('detalles_producto_imagen').src = producto.imagen_url ?? producto.imagen ?? 'https://via.placeholder.com/250x150.png?text=Producto';

            // UBICACIÓN (Lee el Accesor del modelo)
            document.getElementById('detalles_producto_ubicacion').textContent = producto.ubicaciones_texto ?? '-';
            
            // NOMBRE CIENTÍFICO 
            document.getElementById('detalles_producto_cientifico').textContent = producto.nombre_cientifico ?? '-';
            
            // Forma farmacéutica, Marca, Presentación, Categoría
            document.getElementById('detalles_producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? '-';
            document.getElementById('detalles_producto_marca').textContent = producto.marca?.nombre ?? '-';
            document.getElementById('detalles_producto_presentacion').textContent = producto.presentacion?.nombre ?? '-';
            document.getElementById('detalles_producto_categoria').textContent = producto.categoria?.nombre ?? '-';

            // Datos base
            document.getElementById('detalles_producto_nombre').textContent = producto.nombre_comercial ?? '-';
            document.getElementById('detalles_producto_codigo').textContent = producto.codigo_barras ?? '-';
            document.getElementById('detalles_producto_contenido').textContent = producto.contenido ?? '-';
            document.getElementById('detalles_producto_receta').textContent = producto.requiere_receta ? 'Sí' : 'No';
            
            // Mostrar el modal
            modalDetalles.show();

        } catch (e) {
            console.error('[ERROR] mostrarProductoDetalles()', e);
        }
    }


    // ========================================================
    // FUNCIÓN: 1. CONSULTAR Producto (SOLO INFORMACIÓN)
    // ========================================================
    async function consultarProducto(codigo) {
        if (!codigo) return;
        
        try {
            const res = await fetch(`/venta/buscarProducto/${codigo}`);
            if (!res.ok) throw new Error('Producto no encontrado');

            const producto = await res.json();
            
            // Llenar el nuevo modal y mostrarlo
            mostrarProductoDetalles(producto); 
            
            // Limpiar el input de búsqueda principal
            document.getElementById('codigo_barras_input').value = '';
            
        } catch (err) {
            console.error('[ERROR consultarProducto()]', err);
        }
    }


    // ========================================================
    // FUNCIÓN: 2. AGREGAR Producto (VENTA)
    // ========================================================
    async function agregarProducto(codigo, cantidad = 1) {
        if (!codigo) return alert('Ingrese un código válido');

        try {
            const res = await fetch(`/venta/buscarProducto/${codigo}`);
            if (!res.ok) throw new Error('Producto no encontrado');

            const producto = await res.json();

            if (producto.error) throw new Error(producto.error);
            if (!producto.lotes || producto.lotes.length === 0) {
                alert('Producto encontrado, pero no tiene lotes registrados.');
                return;
            }

            const loteDisponible = producto.lotes.find(l => l.cantidad > 0);
            if (!loteDisponible) {
                alert('Producto encontrado, pero sin stock disponible.');
                return;
            }

            // Muestra info en la tarjeta principal (restaurado)
            mostrarProductoUI(producto); 

            const precio = parseFloat(producto.precio_venta);
            let itemExistente = listaVenta.find(i => i.codigo_barras === producto.codigo_barras && i.lote === loteDisponible.id);
            
            if (itemExistente) {
                itemExistente.cantidad += cantidad;
                itemExistente.subtotal = itemExistente.cantidad * precio;
            } else {
                listaVenta.push({
                    codigo_barras: producto.codigo_barras,
                    nombre: producto.nombre_comercial,
                    precio: precio,
                    cantidad: cantidad,
                    stock: loteDisponible.cantidad,
                    lote: loteDisponible.id,
                    subtotal: cantidad * precio
                });
            }

            renderTabla();

        } catch (err) {
            console.error('[ERROR agregarProducto()]', err);
            alert(`Error: ${err.message}`);
        }
    }

    // ========================================================
    // LÓGICA DEL MODAL DE MENÚ DE PRODUCTOS
    // ========================================================

    const inputScanner = document.getElementById('codigo_barras_input');
    const modalMenu = new window.bootstrap.Modal(document.getElementById('modalMenuProductos'));
    const modalMenuBody = document.getElementById('modalMenuProductosBody');

    // --- Función para cargar el menú (con o sin búsqueda) ---
    async function loadMenu(query = '') {
        modalMenuBody.innerHTML = `<div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando menú...</p>
        </div>`;
        
        try {
            const url = `/producto/menu-venta?q=${encodeURIComponent(query)}`; 
            const response = await fetch(url);
            if (!response.ok) throw new Error('No se pudo cargar el menú');
            
            const html = await response.text();
            modalMenuBody.innerHTML = html;
            
        } catch (error) {
            console.error(error);
            modalMenuBody.innerHTML = `<p class="text-center text-danger">${error.message}</p>`;
        }
    }

    // --- 1. Abrir el modal cuando se hace clic en el buscador ---
    inputScanner.addEventListener('click', function() {
        modalMenu.show();
        loadMenu(); 
    });
    
    // --- 2. Escucha de Clics en las tarjetas (Delegación) ---
    modalMenuBody.addEventListener('click', async function(e) {
        const card = e.target.closest('.producto-card');
        if (card) {
            const codigo = card.dataset.codigoBarras;
            if (codigo) {
                // LLAMA A LA FUNCIÓN DE CONSULTA DE INFORMACIÓN
                await consultarProducto(codigo); 
                
                modalMenu.hide(); // Cierra el menú, se abre el modal de detalles
            }
        }
    });

    // --- 3. Escucha de SUBMIT del formulario de búsqueda del menú (SOLUCIÓN FINAL) ---
    modalMenuBody.addEventListener('submit', function(e) {
        if (e.target.id === 'formBuscarEnMenu') {
            e.preventDefault(); 
            
            const input = e.target.querySelector('#inputBuscarEnMenu');
            const query = input ? input.value : '';

            loadMenu(query); 
        }
    });

    // ========================================================
    // EVENTOS (Los demás)
    // ========================================================

    // Buscar producto por código (Submit del buscador principal)
    if (formBuscarProducto) {
        formBuscarProducto.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            const codigo = inputCodigo.value.trim();
            if (codigo) await agregarProducto(codigo, 1);
            inputCodigo.value = ''; 
        });
    }

    // Agregar manualmente
    if (formAgregarManual) {
        formAgregarManual.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            const codigo = inputCodigoManual.value.trim();
            const cantidad = parseInt(inputCantidadManual.value);
            if (!codigo) return alert('Ingrese un código válido');

            await agregarProducto(codigo, cantidad);
            modalManual?.hide();
            formAgregarManual.reset();
        });
    }

    // Foco automático al abrir el modal
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function () {
            inputCodigoManual.focus(); 
        });
    }

});
</script>
@endpush