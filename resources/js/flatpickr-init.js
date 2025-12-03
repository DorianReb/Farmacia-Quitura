document.addEventListener('DOMContentLoaded', function () {
    // Si por algo no cargó flatpickr (CDN), no hacer nada
    if (typeof flatpickr === 'undefined') return;

    // Localizar a español
    if (flatpickr.l10ns && flatpickr.l10ns.es) {
        flatpickr.localize(flatpickr.l10ns.es);
    }

    // =========================================================
    // 1) FECHA DE CADUCIDAD DE LOTES
    //    Inputs con class="js-date-caducidad"
    // =========================================================
    flatpickr(".js-date-caducidad", {
        dateFormat: "d-m-Y",   // lo que ve el usuario
        allowInput: false,     // no escribir, solo calendario
        minDate: "today",      // no permitir fechas pasadas
        maxDate: new Date().fp_incr(365 * 5), // máx. 5 años adelante
    });

    // =========================================================
    // 2) FECHAS DE PROMOCIONES (INICIO / FIN)
    //    Inputs:
    //      - class="js-date-promo-inicio"
    //      - class="js-date-promo-fin"
    //    Soporta el modal de CREATE y todos los EDIT
    // =========================================================
    const inicioPickers = document.querySelectorAll(".js-date-promo-inicio");

    inicioPickers.forEach((inicioInput) => {
        // Buscar el input de fecha_fin en el mismo modal (o contenedor más cercano)
        const container = inicioInput.closest('.modal') || document;
        const finInput = container.querySelector(".js-date-promo-fin");

        // flatpickr para fecha_inicio
        const fpInicio = flatpickr(inicioInput, {
            dateFormat: "d-m-Y",
            allowInput: false,
            minDate: "today",
            maxDate: new Date().fp_incr(365 * 2), // hasta 2 años adelante
            onChange: function (selectedDates) {
                // Cuando cambie fecha_inicio, ajustar minDate de fecha_fin
                if (selectedDates.length && finInput && finInput._flatpickr) {
                    finInput._flatpickr.set('minDate', selectedDates[0]);
                }
            }
        });

        // flatpickr para fecha_fin (si existe en ese modal)
        if (finInput) {
            flatpickr(finInput, {
                dateFormat: "d-m-Y",
                allowInput: false,
                minDate: "today",
                maxDate: new Date().fp_incr(365 * 2),
            });
        }
    });
});
