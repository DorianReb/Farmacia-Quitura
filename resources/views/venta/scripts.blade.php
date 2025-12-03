@push('scripts')
    <script>
        function getProductoImgUrl(p) {
            if (!p) return '/images/no-image.png';
            if (p.imagen_url) return p.imagen_url;
            if (p.imagen) {
                if (/^https?:\/\//i.test(p.imagen)) return p.imagen;
                return '/storage/' + p.imagen.replace(/^\/+/, '');
            }
            return '/images/no-image.png';
        }

        function setImgWithFallback(imgEl, url, altText) {
            imgEl.src = url || '/images/no-image.png';
            imgEl.alt = altText || 'Producto';
            imgEl.onerror = () => { imgEl.src = '/images/no-image.png'; };
        }

        // 🔔 Helpers genéricos para SweetAlert
        function showAlert(title, text = '', icon = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title, text, icon });
            } else {
                alert(text || title);
            }
        }

        function showInfo(title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({ icon: 'info', title, text });
            } else {
                alert(text);
                return Promise.resolve();
            }
        }

        function showError(title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({ icon: 'error', title, text });
            } else {
                alert(text);
                return Promise.resolve();
            }
        }

        document.addEventListener('DOMContentLoaded', async function () {
            console.log('📌 DOM cargado, inicializando venta JS...');

            let listaVenta = [];
            let stockMaxManual = null; // tope dinámico según el producto escrito en el modal

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
            const modalManualError = document.getElementById('modalManualError');

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

            // Bandera para apertura automática del menú
            const abrirModalMenuAutomatico = {{ Js::from(isset($abrirModalMenu) ? $abrirModalMenu : false) }};

            // RUTA API
            const RUTA_BUSCAR_API = '{{ route('venta.buscar.api', ['codigo' => '0']) }}';

            // CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                : '';

            // ===================== INICIO =====================
            if (abrirModalMenuAutomatico && modalMenu) {
                modalMenu.show();
            }

            // ===================== UTILIDADES =====================
            function renderTabla() {
                try {
                    tbody.innerHTML = '';
                    let total = 0;

                    listaVenta.forEach((item, index) => {
                        total += item.subtotal;
                        tbody.innerHTML += `
                        <tr>
                            <td>${item.codigo_barras}</td>
                            <td>${item.nombre}</td>
                            <td>$${item.precio.toFixed(2)}</td>
                            <td>${item.cantidad}</td>
                            <td>${item.stock}</td>
                            <td>${item.lote_codigo || item.lote_id || '-'}</td>
                            <td>-</td>
                            <td>$${item.subtotal.toFixed(2)}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">
                                    Eliminar
                                </button>
                            </td>
                        </tr>`;
                    });

                    totalSpan.textContent = `$${total.toFixed(2)}`;
                } catch (e) {
                    console.error('[ERROR] renderTabla()', e);
                }
            }

            // Eliminar con SweetAlert
            window.eliminarProducto = function (index) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: 'Esta línea se quitará de la lista de venta.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            listaVenta.splice(index, 1);
                            renderTabla();
                            Swal.fire('Eliminado', 'El producto fue eliminado de la lista.', 'success');
                        }
                    });
                } else {
                    if (confirm('¿Eliminar este producto de la lista?')) {
                        listaVenta.splice(index, 1);
                        renderTabla();
                    }
                }
            };

            function mostrarProductoUI(producto) {
                try {
                    document.getElementById('infoProducto').classList.remove('d-none');
                    document.getElementById('placeholderProducto').classList.add('d-none');

                    setImgWithFallback(
                        document.getElementById('producto_imagen'),
                        getProductoImgUrl(producto),
                        producto.nombre_comercial
                    );

                    document.getElementById('producto_ubicacion').textContent = producto.ubicaciones_texto ?? '-';
                    document.getElementById('producto_nombre_cientifico').textContent =
                        producto.componentes_texto ?? producto.nombre_cientifico ?? '-';

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

            // ===================== DETALLES EN MODAL =====================
            window.mostrarDetallesProducto = async function (codigoBarras) {
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
                    document.getElementById('detalles_producto_cientifico').textContent =
                        producto.componentes_texto ?? producto.nombre_cientifico ?? 'N/A';
                    document.getElementById('detalles_producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_contenido').textContent = producto.contenido ?? 'N/A';
                    document.getElementById('detalles_producto_marca').textContent = producto.marca?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_presentacion').textContent = producto.presentacion?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_receta').textContent = producto.requiere_receta ? 'Sí' : 'No';
                    document.getElementById('detalles_producto_categoria').textContent = producto.categoria?.nombre ?? 'N/A';

                    setImgWithFallback(
                        document.getElementById('detalles_producto_imagen'),
                        getProductoImgUrl(producto),
                        producto.nombre_comercial
                    );

                    if (modalDetalles) {
                        modalDetalles.show();
                    }
                } catch (err) {
                    console.error('[ERROR mostrarDetallesProducto()]', err);
                    showError('Error al cargar detalles', err.message);
                }
            };

            // ===================== AGREGAR PRODUCTO (ESCÁNER + MANUAL) =====================
            async function agregarProducto(codigo, cantidadSolicitada = 1) {
                if (!codigo) {
                    await showError('Código inválido', 'Ingrese un código de barras válido.');
                    return;
                }

                try {
                    const url = RUTA_BUSCAR_API.replace('/0', '/' + codigo);
                    const res = await fetch(url);

                    if (res.status === 404) throw new Error('Producto no encontrado (404). Verifica la ruta.');
                    if (!res.ok) throw new Error(`Error en la API: ${res.status}`);

                    const producto = await res.json();

                    if (producto.error) throw new Error(producto.error);
                    if (!producto.lotes || producto.lotes.length === 0) {
                        await showInfo('Sin lotes', 'El producto existe, pero no tiene lotes registrados.');
                        return;
                    }

                    mostrarProductoUI(producto);

                    const precio = parseFloat(producto.precio_venta);
                    const hoyStr = new Date().toISOString().slice(0, 10); // "YYYY-MM-DD"

                    let lotesVigentes = producto.lotes
                        .filter(l => {
                            if (!l.cantidad || l.cantidad <= 0) return false;
                            if (!l.fecha_caducidad) return true;
                            return l.fecha_caducidad > hoyStr;
                        })
                        .sort((a, b) => {
                            if (!a.fecha_caducidad && !b.fecha_caducidad) return 0;
                            if (!a.fecha_caducidad) return 1;
                            if (!b.fecha_caducidad) return -1;
                            return (new Date(a.fecha_caducidad)) - (new Date(b.fecha_caducidad));
                        });

                    if (lotesVigentes.length === 0) {
                        await showInfo(
                            'Sin stock vigente',
                            'El producto tiene lotes, pero todos están caducados o sin existencias.'
                        );
                        return;
                    }

                    // Stock total disponible REAL
                    let stockTotalDisponible = 0;

                    lotesVigentes.forEach(lote => {
                        const yaEnCarrito = listaVenta
                            .filter(i => i.codigo_barras === producto.codigo_barras && i.lote_id === lote.id)
                            .reduce((acc, i) => acc + i.cantidad, 0);

                        const disponible = (lote.cantidad || 0) - yaEnCarrito;
                        if (disponible > 0) {
                            stockTotalDisponible += disponible;
                        }
                    });

                    if (stockTotalDisponible <= 0) {
                        await showInfo(
                            'Sin stock disponible',
                            'El producto existe, pero no hay unidades disponibles considerando lo ya agregado a la lista.'
                        );
                        return;
                    }

                    // Ajustar cantidad si pide más de lo que hay
                    let cantidadFinal = cantidadSolicitada;

                    if (cantidadSolicitada > stockTotalDisponible) {
                        cantidadFinal = stockTotalDisponible;

                        await showInfo(
                            'Stock insuficiente',
                            `Solicitaste ${cantidadSolicitada} unidades, pero solo hay ${stockTotalDisponible} disponibles. ` +
                            `Se agregarán automáticamente esas ${stockTotalDisponible} unidades a la lista.`
                        );
                    }

                    // Repartir la cantidad FINAL entre lotes (FEFO)
                    let restante = cantidadFinal;

                    for (const lote of lotesVigentes) {
                        if (restante <= 0) break;

                        const yaEnCarrito = listaVenta
                            .filter(i => i.codigo_barras === producto.codigo_barras && i.lote_id === lote.id)
                            .reduce((acc, i) => acc + i.cantidad, 0);

                        let disponible = (lote.cantidad || 0) - yaEnCarrito;
                        if (disponible <= 0) continue;

                        const tomar = Math.min(restante, disponible);
                        if (tomar <= 0) continue;

                        let itemExistente = listaVenta.find(i =>
                            i.codigo_barras === producto.codigo_barras &&
                            i.lote_id === lote.id
                        );

                        if (itemExistente) {
                            itemExistente.cantidad += tomar;
                            itemExistente.subtotal = itemExistente.cantidad * precio;
                        } else {
                            listaVenta.push({
                                codigo_barras: producto.codigo_barras,
                                nombre: producto.nombre_comercial,
                                precio: precio,
                                cantidad: tomar,
                                stock: lote.cantidad,
                                lote_id: lote.id,
                                lote_codigo: lote.codigo,
                                subtotal: tomar * precio
                            });
                        }

                        restante -= tomar;
                    }

                    renderTabla();
                    inputCodigo.value = '';
                    inputCodigo.focus();

                } catch (err) {
                    console.error('[ERROR agregarProducto()]', err);
                    await showError('Error al agregar producto', err.message);
                }
            }

            // ===================== BUSCAR POR NOMBRE =====================
            async function buscarProductoPorNombre(query) {
                if (!modalMenuBody) return;

                modalMenuBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Buscando productos...</p>
                </div>`;
                modalMenu?.show();

                try {
                    const url = '{{ route('producto.menu') }}' + `?q=${encodeURIComponent(query)}`;
                    const response = await fetch(url);

                    if (!response.ok) throw new Error('No se pudo cargar el menú: ' + response.status);

                    const html = await response.text();
                    modalMenuBody.innerHTML = html;

                    if (!modalMenu?.isShown) {
                        modalMenu?.show();
                    }

                } catch (error) {
                    console.error('[ERROR buscarProductoPorNombre()]', error);
                    modalMenuBody.innerHTML = `<p class="text-center text-danger">Error al buscar: ${error.message}</p>`;
                }
            }

            // ===================== CONFIRMAR VENTA =====================
            async function confirmarVenta() {
                if (listaVenta.length === 0) {
                    showAlert('Lista vacía', 'No hay productos en la lista de venta.', 'warning');
                    return;
                }

                modalPago?.hide();

                const productosParaEnvio = listaVenta.map(item => ({
                    codigo_barras: item.codigo_barras,
                    cantidad: item.cantidad,
                    lote: item.lote_id
                }));

                const jsonBody = JSON.stringify({
                    productos: productosParaEnvio
                });

                try {
                    const url = formProcesarVenta.action;

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: jsonBody
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        const errorMsg = result.message || 'Error de red o de servidor.';

                        if (result.errors) {
                            const firstError = Object.values(result.errors).flat()[0];
                            throw new Error(firstError || errorMsg);
                        }
                        throw new Error(errorMsg);
                    }

                    listaVenta = [];
                    renderTabla();
                    mostrarProductoUI({});

                    showAlert('Venta registrada', `Venta registrada con éxito. ID: ${result.venta_id}`, 'success');

                } catch (error) {
                    console.error('Error al registrar la venta:', error);
                    if (error.message.includes('Unexpected token')) {
                        showAlert(
                            'Error de sesión',
                            'Error de Sesión/Seguridad. Por favor, recargue la página (F5) e intente de nuevo.',
                            'error'
                        );
                    } else {
                        showAlert('Error al procesar la venta', error.message, 'error');
                    }
                }
            }

            // ===================== STOCK EN MODAL MANUAL =====================
            async function actualizarStockEnModal() {
                const codigo = inputCodigoManual.value.trim();
                const errorBox = modalManualError;

                stockMaxManual = null;
                inputCantidadManual.removeAttribute('max');
                errorBox.classList.add('d-none');
                errorBox.textContent = '';

                if (!codigo) {
                    return;
                }

                try {
                    const url = RUTA_BUSCAR_API.replace('/0', '/' + codigo);
                    const res = await fetch(url);

                    if (res.status === 404) {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = 'Producto no encontrado. Verifique el código de barras.';
                        return;
                    }
                    if (!res.ok) throw new Error(`Error en la API: ${res.status}`);

                    const producto = await res.json();

                    if (!producto.lotes || producto.lotes.length === 0) {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = 'El producto existe, pero no tiene lotes registrados.';
                        return;
                    }

                    const hoyStr = new Date().toISOString().slice(0, 10);

                    let lotesVigentes = producto.lotes
                        .filter(l => {
                            if (!l.cantidad || l.cantidad <= 0) return false;
                            if (!l.fecha_caducidad) return true;
                            return l.fecha_caducidad > hoyStr;
                        });

                    if (lotesVigentes.length === 0) {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = 'Todos los lotes de este producto están caducados o sin stock.';
                        return;
                    }

                    let totalDisponible = 0;

                    lotesVigentes.forEach(lote => {
                        const yaEnCarrito = listaVenta
                            .filter(i => i.codigo_barras === producto.codigo_barras && i.lote_id === lote.id)
                            .reduce((acc, i) => acc + i.cantidad, 0);

                        const disponible = (lote.cantidad || 0) - yaEnCarrito;
                        if (disponible > 0) {
                            totalDisponible += disponible;
                        }
                    });

                    if (totalDisponible <= 0) {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = 'No hay stock disponible de este producto (considerando lo ya agregado a la lista).';
                        return;
                    }

                    stockMaxManual = totalDisponible;
                    inputCantidadManual.setAttribute('max', totalDisponible);

                    let actual = parseInt(inputCantidadManual.value || '1', 10);
                    if (actual > totalDisponible) {
                        inputCantidadManual.value = totalDisponible;
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Stock detectado',
                            text: `Hay ${totalDisponible} unidades disponibles de este producto. La cantidad en el campo se limitará a ese máximo.`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }

                } catch (err) {
                    console.error('[ERROR actualizarStockEnModal()]', err);
                    errorBox.classList.remove('d-none');
                    errorBox.textContent = 'Error al consultar el producto. Intente de nuevo.';
                }
            }

            // ===================== EVENTOS =====================
            if (formBuscarProducto) {
                formBuscarProducto.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const query = inputCodigo.value.trim();

                    if (!isNaN(query) && query.length >= 10) {
                        await agregarProducto(query, 1);
                    } else if (query.length > 0) {
                        buscarProductoPorNombre(query);
                    } else {
                        showAlert('Búsqueda vacía', 'Por favor, ingrese un nombre o código para buscar.', 'warning');
                    }
                });
            }

            if (modalMenuBody) {
                modalMenuBody.addEventListener('click', async function (e) {
                    const card = e.target.closest('.producto-card');
                    if (card) {
                        const codigo = card.dataset.codigoBarras;
                        if (codigo) {
                            await window.mostrarDetallesProducto(codigo);
                        }
                    }
                });

                modalMenuBody.addEventListener('submit', function (e) {
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

            if (formAgregarManual) {
                formAgregarManual.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const codigo = inputCodigoManual.value.trim();
                    let cantidad = parseInt(inputCantidadManual.value) || 1;

                    if (!codigo) {
                        await showError('Código requerido', 'Ingrese un código de barras para continuar.');
                        return;
                    }

                    if (stockMaxManual !== null && cantidad > stockMaxManual) {
                        cantidad = stockMaxManual;
                        inputCantidadManual.value = stockMaxManual;

                        await showInfo(
                            'Cantidad ajustada',
                            `La cantidad solicitada supera el stock disponible. ` +
                            `Se ajustó automáticamente a ${stockMaxManual} unidades.`
                        );
                    }

                    await agregarProducto(codigo, cantidad);
                    modalManual?.hide();
                    formAgregarManual.reset();
                    stockMaxManual = null;
                });

                inputCodigoManual.addEventListener('change', actualizarStockEnModal);
                inputCodigoManual.addEventListener('blur', actualizarStockEnModal);
            }

            if (modalElement) {
                modalElement.addEventListener('shown.bs.modal', function () {
                    inputCodigoManual.focus();
                });
            }

            if (modalPagoElement) {
                modalPagoElement.addEventListener('show.bs.modal', function () {
                    const totalActual = totalSpan.textContent;
                    modalTotalPagar.textContent = totalActual;
                });
            }

            if (btnConfirmarVenta) {
                btnConfirmarVenta.addEventListener('click', confirmarVenta);
            }
        });
    </script>
@endpush
