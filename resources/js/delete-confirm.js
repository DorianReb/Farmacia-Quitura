// Si usas SweetAlert2 por CDN en el layout, NO necesitas importar nada.
// Si lo instalas por npm, entonces podrías usar:
// import Swal from 'sweetalert2';

function initDeleteConfirmGlobal() {
    // Tomamos TODOS los forms del documento
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // 1) Permitir excluir formularios específicos
        if (form.dataset.noSwal === '1') {
            return;
        }

        // 2) Detectar:
        //    a) Formularios con _method = DELETE (Laravel)
        //    b) Formularios con class="form-delete"
        let esDeleteLaravel = false;
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value && methodInput.value.toUpperCase() === 'DELETE') {
            esDeleteLaravel = true;
        }

        const tieneClaseFormDelete = form.classList.contains('form-delete');

        // Si no es DELETE ni tiene la clase especial, lo ignoramos
        if (!esDeleteLaravel && !tieneClaseFormDelete) {
            return;
        }

        // 3) Evitar registrar el listener más de una vez
        if (form.dataset.swalBound === '1') {
            return;
        }
        form.dataset.swalBound = '1';

        // 4) Eliminar cualquier confirm nativo que tenga en onsubmit
        if (form.hasAttribute('onsubmit')) {
            form.removeAttribute('onsubmit');
        }

        // 5) Interceptar el submit y mostrar SweetAlert
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation(); // por si hubiera otros listeners

            Swal.fire({
                title: '¿Seguro que deseas eliminar?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Importante: form.submit() NO vuelve a disparar el evento submit
                    form.submit();
                }
            });
        }, true); // usamos captura para interceptar antes que otros handlers
    });
}

// Aseguramos que se ejecute con el DOM listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDeleteConfirmGlobal);
} else {
    initDeleteConfirmGlobal();
}
