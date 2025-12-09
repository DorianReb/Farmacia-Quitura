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

        // Helper numérico para truncar a 2 decimales (sin redondear)
        function trunc2(x) {
            x = Number(x) || 0;
            return Math.trunc(x * 100) / 100;
        }

        document.addEventListener('DOMContentLoaded', async function () {
            console.log('📌 DOM cargado, inicializando venta JS...');

            // 🔹 Cargar carrito inicial desde Laravel (sesión)
            let listaVenta = {{ Js::from($itemsEnVenta ?? []) }};
            if (!Array.isArray(listaVenta)) {
                listaVenta = [];
            }
            let stockMaxManual = null; // tope dinámico según el producto escrito en el modal
            let productoDetallesActual = null;

            const tbody     = document.querySelector('#listaVentaTable tbody');
            const totalSpan = document.getElementById('totalVentaSpan');

            const formBuscarProducto = document.getElementById('formBuscarProducto');
            const inputCodigo        = document.getElementById('codigo_barras_input');

            // --- Modales y Botones ---
            const modalElement        = document.getElementById('modalAgregarManual');
            const formAgregarManual   = document.getElementById('formAgregarManual');
            const modalManual         = modalElement ? new bootstrap.Modal(modalElement) : null;
            const inputCodigoManual   = document.getElementById('manual_codigo_barras');
            const inputCantidadManual = document.getElementById('manual_cantidad');
            const modalManualError    = document.getElementById('modalManualError');

            const modalDetallesElement = document.getElementById('modalDetallesProducto');
            const modalDetalles        = modalDetallesElement ? new bootstrap.Modal(modalDetallesElement) : null;

            const modalMenuElement = document.getElementById('menuProductosModal');
            const modalMenu        = modalMenuElement ? new bootstrap.Modal(modalMenuElement) : null;
            const modalMenuBody    = modalMenuElement ? modalMenuElement.querySelector('.modal-body') : null;

            // Elementos de Pago
            const formProcesarVenta   = document.getElementById('formProcesarVenta');
            const modalPagoElement    = document.getElementById('modalPago');
            const modalPago           = modalPagoElement ? new bootstrap.Modal(modalPagoElement) : null;
            const modalTotalPagar     = document.getElementById('modalTotalPagar');
            const btnConfirmarVenta   = document.getElementById('btnConfirmarVenta');
            const inputMontoRecibido  = document.getElementById('monto_recibido_input');
            const cambioCalculadoSpan = document.getElementById('cambioCalculadoSpan');

            // Bandera para apertura automática del menú
            const abrirModalMenuAutomatico = {{ Js::from(isset($abrirModalMenu) ? $abrirModalMenu : false) }};
            const codigoVentaRapida        = {{ Js::from($codigoVentaRapida ?? null) }};

            // RUTA API
            const RUTA_BUSCAR_API = '{{ route('venta.buscar.api', ['codigo' => '0']) }}';
            const RUTA_SYNC_CARRITO = '{{ route('venta.syncCarrito') }}';


            // ===================== INICIO =====================
            if (abrirModalMenuAutomatico && modalMenu) {
                modalMenu.show();
            }

            // ===================== VENTA RÁPIDA DESDE HOME =====================
            if (codigoVentaRapida) {
                if (modalManual && inputCodigoManual) {
                    modalManual.show();
                    inputCodigoManual.value = codigoVentaRapida;
                    actualizarStockEnModal();
                } else {
                    // Fallback: agregar directamente
                    agregarProducto(codigoVentaRapida, 1);
                }
            }

            // ===================== UTILIDADES =====================
            function renderTabla() {
                try {
                    tbody.innerHTML = '';
                    let total = 0;

                    listaVenta.forEach((item, index) => {
                        const subtotalBruto   = trunc2(item.subtotal_bruto ?? (item.precio * item.cantidad));
                        const subtotalFinal   = trunc2(item.subtotal ?? subtotalBruto);
                        const promoPorcentaje = Number(item.promo_porcentaje || 0);

                        total += subtotalFinal;

                        tbody.innerHTML += `
                        <tr>
                            <td>${item.codigo_barras}</td>
                            <td>${item.nombre}</td>
                            <td>${item.ubicacion || '-'}</td>
                            <td>$${trunc2(item.precio).toFixed(2)}</td>
                            <td>${item.cantidad}</td>
                            <td>${item.stock}</td>
                            <td>${item.lote_codigo || item.lote_id || '-'}</td>
                            <td>$${subtotalBruto.toFixed(2)}</td>
                            <td>${
                            promoPorcentaje > 0
                                ? promoPorcentaje.toFixed(2) + '%'
                                : '-'
                        }</td>
                            <td>$${subtotalFinal.toFixed(2)}</td>
                            <td class="text-end">
                                <button class="btn btn-danger shadow-sm rounded-pill btn-icon ms-1"
                                        onclick="eliminarProducto(${index})"
                                        title="Eliminar">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>`;
                    });

                    totalSpan.textContent = `$${trunc2(total).toFixed(2)}`;
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

                    document.getElementById('producto_forma').textContent        = producto.forma_farmaceutica?.nombre ?? '-';
                    document.getElementById('producto_marca').textContent        = producto.marca?.nombre ?? '-';
                    document.getElementById('producto_presentacion').textContent = producto.presentacion?.nombre ?? '-';
                    document.getElementById('producto_categoria').textContent   = producto.categoria?.nombre ?? '-';
                    document.getElementById('producto_nombre').textContent      = producto.nombre_comercial ?? '-';
                    document.getElementById('producto_codigo').textContent      = producto.codigo_barras ?? '-';

// Contenido + unidad (si existe), si no, el contenido "pelón"
                    document.getElementById('producto_contenido').textContent =
                        producto.contenido_formateado
                        ?? producto.contenido
                        ?? '-';

                    document.getElementById('producto_receta').textContent      = producto.requiere_receta ? 'Sí' : 'No';
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

                    // Guardamos el producto actual para poder agregarlo luego
                    productoDetallesActual = producto;

                    document.getElementById('detalles_producto_nombre').textContent       = producto.nombre_comercial ?? 'N/A';
                    document.getElementById('detalles_producto_codigo').textContent       = producto.codigo_barras ?? 'N/A';
                    document.getElementById('detalles_producto_ubicacion').textContent    = producto.ubicaciones_texto ?? 'N/A';
                    document.getElementById('detalles_producto_cientifico').textContent   =
                        producto.componentes_texto ?? producto.nombre_cientifico ?? 'N/A';
                    document.getElementById('detalles_producto_forma').textContent        = producto.forma_farmaceutica?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_contenido').textContent =
                        producto.contenido_formateado
                        ?? producto.contenido
                        ?? 'N/A';
                    document.getElementById('detalles_producto_marca').textContent        = producto.marca?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_presentacion').textContent = producto.presentacion?.nombre ?? 'N/A';
                    document.getElementById('detalles_producto_receta').textContent       = producto.requiere_receta ? 'Sí' : 'No';
                    document.getElementById('detalles_producto_categoria').textContent    = producto.categoria?.nombre ?? 'N/A';

                    setImgWithFallback(
                        document.getElementById('detalles_producto_imagen'),
                        getProductoImgUrl(producto),
                        producto.nombre_comercial
                    );

                    // Resetear cantidad a 1 cada vez que se abre
                    const inputCantDet = document.getElementById('detalles_cantidad');
                    if (inputCantDet) inputCantDet.value = 1;

                    if (modalDetalles) {
                        modalDetalles.show();
                    }
                } catch (err) {
                    console.error('[ERROR mostrarDetallesProducto()]', err);
                    showError('Error al cargar detalles', err.message);
                }
            };

            // ===================== AGREGAR PRODUCTO (ESCÁNER + MANUAL + DETALLES) =====================
            async function agregarProducto(codigo, cantidadSolicitada = 1, opciones = {}) {
                const { silenciarAvisoStock = false } = opciones;

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

                        if (!silenciarAvisoStock) {
                            await showInfo(
                                'Stock insuficiente',
                                `Solicitaste ${cantidadSolicitada} unidades, pero solo hay ${stockTotalDisponible} disponibles. ` +
                                `Se agregarán automáticamente esas ${stockTotalDisponible} unidades a la lista.`
                            );
                        }
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

                        // === PROMOCIÓN PARA ESTE LOTE (desde la API) ===
                        const promo = parseFloat(lote.promo_porcentaje || 0);

                        // === Cálculo tipo procedure (TRUNCATE a 2 decimales) ===
                        const subtotalBruto = Math.trunc(precio * tomar * 100) / 100;
                        const descMonto     = Math.trunc(subtotalBruto * (promo / 100) * 100) / 100;
                        const subtotalLinea = subtotalBruto - descMonto;

                        let itemExistente = listaVenta.find(i =>
                            i.codigo_barras === producto.codigo_barras &&
                            i.lote_id === lote.id
                        );

                        if (itemExistente) {
                            itemExistente.cantidad += tomar;

                            const nuevoSubtotalBruto = Math.trunc(precio * itemExistente.cantidad * 100) / 100;
                            const nuevoDescMonto     = Math.trunc(nuevoSubtotalBruto * (promo / 100) * 100) / 100;

                            itemExistente.subtotal_bruto   = nuevoSubtotalBruto;
                            itemExistente.subtotal         = nuevoSubtotalBruto - nuevoDescMonto;
                            itemExistente.promo_porcentaje = promo;
                        } else {
                            listaVenta.push({
                                codigo_barras:    producto.codigo_barras,
                                nombre:           producto.resumen || producto.nombre_comercial,
                                ubicacion:        producto.ubicaciones_texto || '-',
                                precio:           precio,
                                cantidad:         tomar,
                                stock:            lote.cantidad,
                                lote_id:          lote.id,
                                lote_codigo:      lote.codigo,
                                promo_porcentaje: promo,
                                subtotal_bruto:   subtotalBruto,
                                subtotal:         subtotalLinea
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

            // ===================== CONFIRMAR VENTA (FORM NORMAL) =====================
            async function confirmarVenta() {
                if (listaVenta.length === 0) {
                    await showAlert('Lista vacía', 'No hay productos en la lista de venta.', 'warning');
                    return;
                }

                // Total actual (texto → número)
                const textoTotal = totalSpan.textContent || '$0.00';
                const total      = parseFloat(textoTotal.replace(/[^\d.-]/g, '')) || 0;

                const monto = inputMontoRecibido
                    ? parseFloat(inputMontoRecibido.value || '0')
                    : 0;

                if (!inputMontoRecibido || isNaN(monto) || monto <= 0) {
                    await showError(
                        'Monto requerido',
                        'Capture el monto recibido del cliente para continuar.'
                    );
                    return;
                }

                if (monto < total) {
                    const faltante = trunc2(total - monto).toFixed(2);
                    await showError(
                        'Monto insuficiente',
                        `El monto recibido es menor al total. Faltan $${faltante}.`
                    );
                    return;
                }

                const cambio = trunc2(monto - total);

                // Cerrar modal de pago
                modalPago?.hide();

                // 1) El formulario YA tiene @csrf desde Blade.
                //    NO lo borres. Solo elimina inputs dinámicos de vueltas anteriores.
                const dinamicos = formProcesarVenta.querySelectorAll(
                    'input[name^="lotes["], input[name="monto_recibido"]'
                );
                dinamicos.forEach(el => el.remove());

                // 2) Agregar lotes
                listaVenta.forEach((item, index) => {
                    const inputLote = document.createElement('input');
                    inputLote.type  = 'hidden';
                    inputLote.name  = `lotes[${index}][lote_id]`;
                    inputLote.value = item.lote_id;
                    formProcesarVenta.appendChild(inputLote);

                    const inputCant = document.createElement('input');
                    inputCant.type  = 'hidden';
                    inputCant.name  = `lotes[${index}][cantidad]`;
                    inputCant.value = item.cantidad;
                    formProcesarVenta.appendChild(inputCant);
                });

                // 3) Monto recibido
                const inputMonto = document.createElement('input');
                inputMonto.type  = 'hidden';
                inputMonto.name  = 'monto_recibido';
                inputMonto.value = monto.toFixed(2);
                formProcesarVenta.appendChild(inputMonto);

                // 4) Enviar formulario
                formProcesarVenta.submit();
            }

            // ===================== STOCK EN MODAL MANUAL =====================
            async function actualizarStockEnModal() {
                const codigo   = inputCodigoManual.value.trim();
                const errorBox = modalManualError;
                const infoBox  = document.getElementById('manualInfoProducto');

                function limpiarFichaManual() {
                    if (!infoBox) return;
                    infoBox.classList.add('d-none');
                    document.getElementById('manual_producto_nombre').textContent       = '---';
                    document.getElementById('manual_producto_codigo').textContent       = '---';
                    document.getElementById('manual_producto_ubicacion').textContent    = '---';
                    document.getElementById('manual_producto_componentes').textContent  = '---';
                    document.getElementById('manual_producto_forma').textContent        = '---';
                    document.getElementById('manual_producto_contenido').textContent    = '---';
                    document.getElementById('manual_producto_marca').textContent        = '---';
                    document.getElementById('manual_producto_presentacion').textContent = '---';
                    document.getElementById('manual_producto_receta').textContent       = '---';
                    document.getElementById('manual_producto_categoria').textContent    = '---';
                }

                stockMaxManual = null;
                inputCantidadManual.removeAttribute('max');
                errorBox.classList.add('d-none');
                errorBox.textContent = '';
                limpiarFichaManual();

                if (!codigo) return;

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

                    await showInfo(
                        'Stock detectado',
                        `Hay ${totalDisponible} unidades disponibles de este producto.\n\n` +
                        `La cantidad que capture no podrá superar ese máximo.`
                    );

                    if (infoBox) {
                        const componentesTexto = producto.componentes_texto ?? producto.nombre_cientifico ?? '-';

                        document.getElementById('manual_producto_nombre').textContent       = producto.nombre_comercial ?? '---';
                        document.getElementById('manual_producto_codigo').textContent       = producto.codigo_barras ?? '---';
                        document.getElementById('manual_producto_ubicacion').textContent    = producto.ubicaciones_texto ?? '-';
                        document.getElementById('manual_producto_componentes').textContent  = componentesTexto;
                        document.getElementById('manual_producto_forma').textContent        = producto.forma_farmaceutica?.nombre ?? '-';
                        document.getElementById('manual_producto_contenido').textContent    = producto.contenido ?? '-';
                        document.getElementById('manual_producto_marca').textContent        = producto.marca?.nombre ?? '-';
                        document.getElementById('manual_producto_presentacion').textContent = producto.presentacion?.nombre ?? '-';
                        document.getElementById('manual_producto_receta').textContent       = producto.requiere_receta ? 'Sí' : 'No';
                        document.getElementById('manual_producto_categoria').textContent    = producto.categoria?.nombre ?? '-';

                        infoBox.classList.remove('d-none');
                    }
                } catch (err) {
                    console.error('[ERROR actualizarStockEnModal()]', err);
                    errorBox.classList.remove('d-none');
                    errorBox.textContent = 'Error al consultar el producto. Intente de nuevo.';
                    limpiarFichaManual();
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
                    }

                    // Aquí indicamos que no queremos el 2º Swal de “stock insuficiente”
                    await agregarProducto(codigo, cantidad, { silenciarAvisoStock: true });

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

            // Al abrir modal de pago: mostrar total y resetear campos
            if (modalPagoElement) {
                modalPagoElement.addEventListener('show.bs.modal', function () {
                    const totalActual = totalSpan.textContent || '$0.00';
                    modalTotalPagar.textContent = totalActual;

                    if (inputMontoRecibido) inputMontoRecibido.value = '';
                    if (cambioCalculadoSpan) cambioCalculadoSpan.textContent = '$0.00';
                });
            }

            // Calcular cambio en tiempo real
            if (inputMontoRecibido) {
                inputMontoRecibido.addEventListener('input', function () {
                    const textoTotal = totalSpan.textContent || '$0.00';
                    const total      = parseFloat(textoTotal.replace(/[^\d.-]/g, '')) || 0;
                    const monto      = parseFloat(inputMontoRecibido.value || '0') || 0;

                    let textoCambio = '$0.00';

                    if (monto >= total && total > 0) {
                        const cambio = trunc2(monto - total);
                        textoCambio  = '$' + cambio.toFixed(2);
                    } else if (monto > 0 && total > 0 && monto < total) {
                        const faltante = trunc2(total - monto);
                        textoCambio    = 'Faltan $' + faltante.toFixed(2);
                    }

                    if (cambioCalculadoSpan) {
                        cambioCalculadoSpan.textContent = textoCambio;
                    }
                });
            }

            if (btnConfirmarVenta) {
                btnConfirmarVenta.addEventListener('click', confirmarVenta);
            }

            // Formulario de filtros (el que hace GET a venta.index)
            const formFiltrosProductos = document.querySelector('form[action="{{ route('venta.index') }}"]');
            let enviandoFiltros = false;

            if (formFiltrosProductos) {
                formFiltrosProductos.addEventListener('submit', function (e) {
                    // Si ya estamos reenviando, no interceptar de nuevo
                    if (enviandoFiltros) return;

                    e.preventDefault();

                    // Sincronizar carrito actual en sesión antes de recargar
                    fetch(RUTA_SYNC_CARRITO, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ items: listaVenta }),
                    })
                        .then(res => res.ok ? res.json() : Promise.reject(res))
                        .then(() => {
                            enviandoFiltros = true;
                            formFiltrosProductos.submit(); // ahora sí se envía de verdad
                        })
                        .catch(err => {
                            console.error('[ERROR] syncCarrito antes de filtros', err);
                            // Como fallback, enviamos el formulario aunque falle el sync
                            enviandoFiltros = true;
                            formFiltrosProductos.submit();
                        });
                });
            }


            // ===================== AGREGAR DESDE EL MODAL DE DETALLES =====================
            const btnAgregarDesdeDetalles = document.getElementById('btnAgregarDesdeDetalles');
            const inputCantidadDetalles   = document.getElementById('detalles_cantidad');

            if (btnAgregarDesdeDetalles) {
                btnAgregarDesdeDetalles.addEventListener('click', async function () {
                    if (!productoDetallesActual) {
                        await showError('Sin producto', 'No hay ningún producto cargado en el panel de detalles.');
                        return;
                    }

                    let cantidad = 1;
                    if (inputCantidadDetalles) {
                        cantidad = parseInt(inputCantidadDetalles.value || '1', 10);
                        if (isNaN(cantidad) || cantidad <= 0) {
                            cantidad = 1;
                            inputCantidadDetalles.value = '1';
                        }
                    }

                    await agregarProducto(
                        productoDetallesActual.codigo_barras,
                        cantidad
                    );

                    if (modalDetalles) {
                        modalDetalles.hide();
                    }
                });
            }

            renderTabla();

        });
    </script>
@endpush
