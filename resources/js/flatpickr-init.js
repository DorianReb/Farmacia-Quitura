document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr === 'undefined') return;

    // Localizar a español
    if (flatpickr.l10ns && flatpickr.l10ns.es) {
        flatpickr.localize(flatpickr.l10ns.es);
    }

    // 1) FECHA DE CADUCIDAD DE LOTES
    flatpickr(".js-date-caducidad", {
        dateFormat: "d-m-Y",
        allowInput: false,
        minDate: "today",
        maxDate: new Date().fp_incr(365 * 5), // máx. 5 años adelante
    });

    // 2) FECHAS DE PROMOCIONES (INICIO / FIN)
    const inicioPickers = document.querySelectorAll(".js-date-promo-inicio");

    inicioPickers.forEach((inicioInput) => {
        const container = inicioInput.closest('.modal') || document;
        const finInput = container.querySelector(".js-date-promo-fin");

        const maxPromoDate = new Date().fp_incr(365 * 5); // si quieres más rango que 2027

        const fpInicio = flatpickr(inicioInput, {
            dateFormat: "d-m-Y",
            allowInput: false,
            minDate: "today",
            maxDate: maxPromoDate,
            disableMobile: true,          // fuerza calendario de escritorio
            onChange: function (selectedDates) {
                if (selectedDates.length && finInput && finInput._flatpickr) {
                    finInput._flatpickr.set('minDate', selectedDates[0]);
                }
            }
        });

        if (finInput) {
            flatpickr(finInput, {
                dateFormat: "d-m-Y",
                allowInput: false,
                minDate: "today",
                maxDate: maxPromoDate,
                disableMobile: true,
            });
        }
    });
});
