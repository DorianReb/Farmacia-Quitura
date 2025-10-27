@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    let listaVenta = [];
    const tbody = document.querySelector('#listaVentaTable tbody');
    const totalSpan = document.getElementById('totalVentaSpan');

    const formBuscarProducto = document.getElementById('formBuscarProducto');
    const inputCodigo = document.getElementById('codigo_barras_input');
    
    const modalElement = document.getElementById('modalAgregarManual');
    const formAgregarManual = document.getElementById('formAgregarManual');
    const modalManual = modalElement ? new window.bootstrap.Modal(modalElement) : null; 
    const inputCodigoManual = document.getElementById('manual_codigo_barras');
    const inputCantidadManual = document.getElementById('manual_cantidad');

    // ========================================================
    // FUNCIÓN: Renderizar la tabla de venta
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

    function mostrarProductoUI(producto) {
        try {
            document.getElementById('infoProducto').classList.remove('d-none');
            document.getElementById('placeholderProducto').classList.add('d-none');

            // Imagen del producto
            document.getElementById('producto_imagen').src = producto.imagen_url ?? 'https://via.placeholder.com/250x150.png?text=Producto';

            // Ubicación (ahora con tres niveles: asigna_ubicaciones → nivel → pasillo)
            let ubicacionTexto = '-';
            const ubicaciones = producto.asigna_ubicaciones ?? producto.asignaUbicaciones ?? [];
            if (ubicaciones.length > 0) {
                const nivel = ubicaciones[0]?.nivel;
                const pasillo = nivel?.pasillo;
                const nombrePasillo = pasillo?.nombre ?? '-';
                const nombreNivel = nivel?.nombre ?? '-';
                ubicacionTexto = `${nombrePasillo} / ${nombreNivel}`;
            }
            document.getElementById('producto_ubicacion').textContent = ubicacionTexto;

            // Nombre científico (seguro)
            const componentes = producto.asigna_componentes ?? producto.asignaComponentes ?? [];
            const nombreCientifico = componentes[0]?.nombre_cientifico?.nombre ?? '-';
            document.getElementById('producto_nombre_cientifico').textContent = nombreCientifico;
            
            // Forma farmacéutica
            document.getElementById('producto_forma').textContent = producto.forma_farmaceutica?.nombre ?? '-';

            // Marca, presentación, categoría
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
    // FUNCIÓN: Buscar y agregar producto
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
    // EVENTOS
    // ========================================================

    // Buscar producto por código
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
