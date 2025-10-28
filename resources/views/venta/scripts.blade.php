@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    console.log('📌 DOM cargado, inicializando venta JS...');

    let listaVenta = [];
    const tbody = document.querySelector('#listaVentaTable tbody');
    const totalSpan = document.getElementById('totalVentaSpan');

    const formBuscarProducto = document.getElementById('formBuscarProducto');
    const inputCodigo = document.getElementById('codigo_barras_input');

    // --- Modales y Botones ---
    const modalElement = document.getElementById('modalAgregarManual');
    const formAgregarManual = document.getElementById('formAgregarManual');
    const modalManual = modalElement ? new bootstrap.Modal(modalElement) : null;
    const inputCodigoManual = document.getElementById('manual_codigo_barras');
    const inputCantidadManual = document.getElementById('manual_cantidad');

    const modalDetallesElement = document.getElementById('modalDetallesProducto');
    const modalDetalles = modalDetallesElement ? new bootstrap.Modal(modalDetallesElement) : null;

    const modalMenuElement = document.getElementById('menuProductosModal');
    const modalMenu = modalMenuElement ? new bootstrap.Modal(modalMenuElement) : null;
    const modalMenuBody = modalMenuElement ? modalMenuElement.querySelector('.modal-body') : null;
    
    // Elementos de Pago
    const formProcesarVenta = document.getElementById('formProcesarVenta');
    const inputProductosVenta = document.getElementById('inputProductosVenta');
    const modalPagoElement = document.getElementById('modalPago');
    const modalPago = modalPagoElement ? new bootstrap.Modal(modalPagoElement) : null;
    const modalTotalPagar = document.getElementById('modalTotalPagar');
    const btnConfirmarVenta = document.getElementById('btnConfirmarVenta');


    // Bandera para la apertura automática del modal de menú (viene de VentaController@index)
    const abrirModalMenuAutomatico = {{ Js::from(isset($abrirModalMenu) ? $abrirModalMenu : false) }};
    
    // RUTA API GENERADA CON BLADE (endpoint VentaController@buscarProductoPorCodigo)
    const RUTA_BUSCAR_API = '{{ route('venta.buscar.api', ['codigo' => '0']) }}';
    
    // --- CSRF TOKEN (Asegúrate de tener <meta name="csrf-token" content="..."> en tu layout) ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? 
                      document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    // ========================================================
    // LÓGICA DE INICIO - Abrir Modal de Menú si hay resultados
    // ========================================================
    if (abrirModalMenuAutomatico && modalMenu) {
        modalMenu.show();
    }


    // ========================================================
    // FUNCIONES DE UTILIDAD (TU CÓDIGO ORIGINAL)
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
            const url = '{{ route('producto.menu') }}' + `?q=${encodeURIComponent(query)}`;
            const response = await fetch(url);
            
            if (!response.ok) throw new Error('No se pudo cargar el menú: ' + response.status);

            const html = await response.text();
            modalMenuBody.innerHTML = html; // Inyectar el HTML del partial
            
            if (!modalMenu?.isShown) {
                 modalMenu?.show();
            }

        } catch (error) {
            console.error('[ERROR buscarProductoPorNombre()]', error);
            modalMenuBody.innerHTML = `<p class="text-center text-danger">Error al buscar: ${error.message}</p>`;
        }
    }

    // ========================================================
    // FUNCIÓN: Confirma y envía la venta (AJAX)
    // ========================================================
    async function confirmarVenta() {
        if (listaVenta.length === 0) return;

        // 🚨 CAMBIO CLAVE: Enviamos JSON con Content-Type
        
        // 1. Ocultar modal de pago (si está visible)
        modalPago?.hide(); 

        // 2. Preparar los datos para el envío (solo campos requeridos por VentaController@store)
        const productosParaEnvio = listaVenta.map(item => ({
            codigo_barras: item.codigo_barras,
            cantidad: item.cantidad,
            lote: item.lote 
        }));

        // 3. Crear el cuerpo de la petición JSON
        const jsonBody = JSON.stringify({
            productos: productosParaEnvio
        });

        // 4. Enviar la petición al servidor
        try {
            const url = formProcesarVenta.action;
            
            const response = await fetch(url, {
                method: 'POST',
                // --- USAMOS HEADERS PARA ENVIAR EL TOKEN Y DECLARAR CONTENIDO JSON ---
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // Enviamos el token para evitar el 419/HTML error
                },
                body: jsonBody // Enviamos el JSON
            });

            // Intentamos parsear la respuesta como JSON, incluso si hay error
            const result = await response.json();

            if (!response.ok) {
                const errorMsg = result.message || 'Error de red o de servidor.';
                
                if (result.errors) {
                     const firstError = Object.values(result.errors).flat()[0];
                     throw new Error(firstError || errorMsg);
                }
                throw new Error(errorMsg);
            }

            // 5. Éxito: limpiar la interfaz y notificar al usuario
            listaVenta = [];
            renderTabla();
            mostrarProductoUI({}); 
            
            alert(`✅ Venta registrada con éxito. ID: ${result.venta_id}`);

        } catch (error) {
            console.error('Error al registrar la venta:', error);
            // Si el error fue el HTML, mostramos el error genérico
            if (error.message.includes('Unexpected token')) {
                 alert('❌ Error de Sesión/Seguridad. Por favor, recargue la página (F5) e intente de nuevo.');
            } else {
                 alert('❌ Error al procesar la venta: ' + error.message);
            }
        }
    }


    // ========================================================
    // Eventos
    // ========================================================
    
    // CORRECCIÓN: El formulario unificado maneja ambas lógicas
    if (formBuscarProducto) {
        formBuscarProducto.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            const query = inputCodigo.value.trim();
            
            // Lógica de Escaneo Rápido (Prioridad 1)
            if (!isNaN(query) && query.length >= 10) { 
                 await agregarProducto(query, 1); 
            } 
            // Búsqueda por Nombre / Menú (Prioridad 2)
            else if (query.length > 0) {
                 buscarProductoPorNombre(query);
            } else {
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
                const codigo = card.dataset.codigoBarras;
                if (codigo) {
                    await window.mostrarDetallesProducto(codigo);
                }
            }
        });
        
        // 2. Manejar el submit del formulario interno (formBuscarEnMenu)
        modalMenuBody.addEventListener('submit', function(e) {
            if (e.target.id === 'formBuscarEnMenu') {
                e.preventDefault(); 
                
                const input = e.target.querySelector('#inputBuscarEnMenu');
                const query = input ? input.value : '';
                
                if (query.length > 0) {
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

    // Evento para el botón "Total" (ABRE el modal de pago)
    if (modalPagoElement) {
        modalPagoElement.addEventListener('show.bs.modal', function () {
            const totalActual = totalSpan.textContent;
            modalTotalPagar.textContent = totalActual;
        });
    }

    // Evento para el botón "Confirmar y Cobrar" (ENVÍA la venta)
    if (btnConfirmarVenta) {
        btnConfirmarVenta.addEventListener('click', confirmarVenta);
    }

});
</script>
@endpush