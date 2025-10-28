@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    console.log('📌 DOM cargado, inicializando venta JS...');

    let listaVenta = [];
    const tbody = document.querySelector('#listaVentaTable tbody');
    const totalSpan = document.getElementById('totalVentaSpan');

    const formBuscarProducto = document.getElementById('formBuscarProducto');
    const inputCodigo = document.getElementById('codigo_barras_input');

    // --- Modales ---
    const modalElement = document.getElementById('modalAgregarManual');
    const formAgregarManual = document.getElementById('formAgregarManual');
    const modalManual = modalElement ? new bootstrap.Modal(modalElement) : null;
    const inputCodigoManual = document.getElementById('manual_codigo_barras');
    const inputCantidadManual = document.getElementById('manual_cantidad');

    // MODAL DE DETALLES
    const modalDetallesElement = document.getElementById('modalDetallesProducto');
    const modalDetalles = modalDetallesElement ? new bootstrap.Modal(modalDetallesElement) : null;

    // MODAL DEL MENÚ DE PRODUCTOS
    const modalMenuElement = document.getElementById('menuProductosModal');
    const modalMenu = modalMenuElement ? new bootstrap.Modal(modalMenuElement) : null;
    const modalMenuBody = modalMenuElement ? modalMenuElement.querySelector('.modal-body') : null;

    // Bandera para la apertura automática del modal de menú (viene de VentaController@index)
    const abrirModalMenuAutomatico = {{ Js::from(isset($abrirModalMenu) ? $abrirModalMenu : false) }};
    
    // RUTA API GENERADA CON BLADE (para detalles y agregar - el endpoint VentaController@buscarProductoPorCodigo)
    const RUTA_BUSCAR_API = '{{ route('venta.buscar.api', ['codigo' => '0']) }}';
    
    // ========================================================
    // LÓGICA DE INICIO - Abrir Modal de Menú si hay resultados
    // ========================================================
    if (abrirModalMenuAutomatico && modalMenu) {
        modalMenu.show();
    }


    // ========================================================
    // FUNCIONES DE UTILIDAD
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
    
    window.eliminarProducto = function(index) {
        listaVenta.splice(index, 1);
        renderTabla();
    }

    function mostrarProductoUI(producto) {
        try {
            document.getElementById('infoProducto').classList.remove('d-none');
            document.getElementById('placeholderProducto').classList.add('d-none');

            document.getElementById('producto_imagen').src = producto.imagen_url ?? producto.imagen ?? '/img/placeholder.png';
            document.getElementById('producto_ubicacion').textContent = producto.ubicaciones_texto ?? '-';
            document.getElementById('producto_nombre_cientifico').textContent = producto.nombre_cientifico ?? '-';
            document.getElementById('producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? '-';
            document.getElementById('producto_marca').textContent = producto.marca?.nombre ?? '-';
            document.getElementById('producto_presentacion').textContent = producto.presentacion?.nombre ?? '-';
            document.getElementById('producto_categoria').textContent = producto.categoria?.nombre ?? '-';
            document.getElementById('producto_nombre').textContent = producto.nombre_comercial ?? '-';
            document.getElementById('producto_codigo').textContent = producto.codigo_barras ?? '-';
            document.getElementById('producto_contenido').textContent = producto.contenido ?? '-';
            document.getElementById('producto_receta').textContent = producto.requiere_receta ? 'Sí' : 'No';
        } catch (e) {
            console.error('[ERROR] mostrarProductoUI()', e);
        }
    }
    
    // ========================================================
    // FUNCIÓN PARA MOSTRAR DETALLES EN MODAL (Al hacer clic en el menú)
    // ========================================================
    window.mostrarDetallesProducto = async function(codigoBarras) {
        if (modalMenu) {
            modalMenu.hide();
        }

        try {
            const url = RUTA_BUSCAR_API.replace('/0', '/' + codigoBarras);
            const res = await fetch(url); 
            
            if (res.status === 404) throw new Error('Producto no encontrado (404). Verifica la ruta.');
            if (!res.ok) throw new Error(`Error en la API: ${res.status}`);
            
            const producto = await res.json();
            
            // 1. Rellenar los campos del modal de detalles
            document.getElementById('detalles_producto_nombre').textContent = producto.nombre_comercial ?? 'N/A';
            document.getElementById('detalles_producto_codigo').textContent = producto.codigo_barras ?? 'N/A';
            document.getElementById('detalles_producto_ubicacion').textContent = producto.ubicaciones_texto ?? 'N/A';
            document.getElementById('detalles_producto_cientifico').textContent = producto.nombre_cientifico ?? 'N/A';
            document.getElementById('detalles_producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? 'N/A';
            document.getElementById('detalles_producto_contenido').textContent = producto.contenido ?? 'N/A'; 
            document.getElementById('detalles_producto_marca').textContent = producto.marca?.nombre ?? 'N/A';
            document.getElementById('detalles_producto_presentacion').textContent = producto.presentacion?.nombre ?? 'N/A';
            document.getElementById('detalles_producto_receta').textContent = producto.requiere_receta ? 'Sí' : 'No';
            document.getElementById('detalles_producto_categoria').textContent = producto.categoria?.nombre ?? 'N/A';
            document.getElementById('detalles_producto_imagen').src = producto.imagen ?? 'https://via.placeholder.com/250x150.png?text=Producto';

            // 2. Mostrar el modal de detalles
            if (modalDetalles) {
                modalDetalles.show();
            }
        } catch (err) {
            console.error('[ERROR mostrarDetallesProducto()]', err);
            alert(`Error al cargar detalles: ${err.message}`);
        }
    }

    // ========================================================
    // Agregar producto a venta (Comportamiento de Escáner)
    // ========================================================
    async function agregarProducto(codigo, cantidad = 1) {
        if (!codigo) return alert('Ingrese un código válido');

        try {
            const url = RUTA_BUSCAR_API.replace('/0', '/' + codigo);
            const res = await fetch(url); 
            
            if (res.status === 404) throw new Error('Producto no encontrado (404). Verifica la ruta.');
            if (!res.ok) throw new Error(`Error en la API: ${res.status}`);
            
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
            inputCodigo.value = ''; 
            inputCodigo.focus(); 
            
        } catch (err) {
            console.error('[ERROR agregarProducto()]', err);
            alert(`Error: ${err.message}`);
        }
    }


    // ========================================================
    // FUNCIÓN: Carga y muestra el modal de menú (vía AJAX)
    // ========================================================
    async function buscarProductoPorNombre(query) {
        if (!modalMenuBody) return;

        // Mostrar spinner de carga
        modalMenuBody.innerHTML = `<div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Buscando productos...</p>
        </div>`;
        modalMenu?.show(); // Abre el modal inmediatamente

        try {
            // Usamos la ruta 'producto.menu'. El controlador ProductoController@menu debe devolver HTML parcial.
            const url = '{{ route('producto.menu') }}' + `?q=${encodeURIComponent(query)}`;
            const response = await fetch(url);
            
            if (!response.ok) throw new Error('No se pudo cargar el menú: ' + response.status);

            const html = await response.text();
            modalMenuBody.innerHTML = html; // Inyectar el HTML del partial
            
            // Si por alguna razón el modal se cerró antes, lo volvemos a mostrar.
            if (!modalMenu?.isShown) {
                 modalMenu?.show();
            }

        } catch (error) {
            console.error('[ERROR buscarProductoPorNombre()]', error);
            modalMenuBody.innerHTML = `<p class="text-center text-danger">Error al buscar: ${error.message}</p>`;
        }
    }


    // ========================================================
    // Eventos
    // ========================================================
    
    // El click en el input principal fue eliminado para dejar que el submit maneje el flujo.
    if (formBuscarProducto) {
        formBuscarProducto.addEventListener('submit', async function(e) {
            e.preventDefault(); // Siempre prevenimos el submit para controlar el flujo
            
            const query = inputCodigo.value.trim();
            
            // Lógica de Escaneo Rápido (Prioridad 1)
            // Si el valor parece ser un CÓDIGO DE BARRAS (solo números y >= 10 dígitos), 
            if (!isNaN(query) && query.length >= 10) { 
                 await agregarProducto(query, 1); // Agrega directamente
            } 
            // Búsqueda por Nombre / Menú (Prioridad 2)
            else if (query.length > 0) {
                 // Abrimos el modal con la búsqueda AJAX
                 buscarProductoPorNombre(query);
            } else {
                 // Si está vacío
                 alert('Por favor, ingrese un nombre o código para buscar.');
            }
        });
    }

    // Escuchar clics dentro del cuerpo del modal de menú para abrir detalles
    if (modalMenuBody) {
        // 1. Manejar clics en tarjetas de producto (abrir detalles)
        modalMenuBody.addEventListener('click', async function(e) {
            const card = e.target.closest('.producto-card');
            if (card) {
                const codigo = card.dataset.codigoBarras; // Captura el código de barras
                if (codigo) {
                    // Clic en el menú -> mostrar detalles en el Modal 2
                    await window.mostrarDetallesProducto(codigo);
                }
            }
        });
        
        // 2. 🚨 Manejar el submit del formulario interno (formBuscarEnMenu)
        modalMenuBody.addEventListener('submit', function(e) {
            // Buscamos si el evento de submit viene del formulario interno del menú
            if (e.target.id === 'formBuscarEnMenu') {
                e.preventDefault(); // <-- ¡CRUCIAL! Evita que el navegador recargue la página.
                
                const input = e.target.querySelector('#inputBuscarEnMenu');
                const query = input ? input.value : '';
                
                if (query.length > 0) {
                    // Si hay query, llamamos a la función AJAX para recargar el cuerpo del modal
                    buscarProductoPorNombre(query);
                }
            }
        });
    }

    // Agregar producto manualmente desde modal
    if (formAgregarManual) {
        formAgregarManual.addEventListener('submit', async function(e) {
            e.preventDefault();
            const codigo = inputCodigoManual.value.trim();
            const cantidad = parseInt(inputCantidadManual.value) || 1;

            if (!codigo) {
                alert('Ingrese un código válido');
                return;
            }

            // Aquí SÍ usamos el código de barras para agregar directamente
            await agregarProducto(codigo, cantidad);
            modalManual?.hide();
            formAgregarManual.reset();
        });
    }

    // Foco automático al abrir el modal manual
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function () {
            inputCodigoManual.focus();
        });
    }

});
</script>
@endpush