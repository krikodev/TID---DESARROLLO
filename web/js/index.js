import { Select } from "../../admin/public/js/lib/select.js";
document.addEventListener('DOMContentLoaded', function () {
    setupOrigenDestinoHandlers();
    loadSavedOrigenDestino();
    setupSearchFormSubmit();

    // const selectInstance = new Select(); // Crea una instancia
    // selectInstance.createSelectSinParent("#origen");
    // selectInstance.createSelect("#destino");
});

function setupOrigenDestinoHandlers() {
    const origenSelect = document.querySelector('[name="origen"]');
    const destinoSelect = document.querySelector('[name="destino"]');

    if (origenSelect) {
        origenSelect.addEventListener('change', () => handleOrigenDestinoChange(origenSelect, destinoSelect, 'origen'));
    }
    if (destinoSelect) {
        destinoSelect.addEventListener('change', () => handleOrigenDestinoChange(origenSelect, destinoSelect, 'destino'));
    }
}

function handleOrigenDestinoChange(origenSelect, destinoSelect, tipo) {
    const value = tipo === 'origen' ? origenSelect.value : destinoSelect.value;
    localStorage.setItem(tipo, value);
    validateOrigenDestino(origenSelect, destinoSelect);
}

function loadSavedOrigenDestino() {
    const origenSelect = document.querySelector('[name="origen"]');
    const destinoSelect = document.querySelector('[name="destino"]');

    if (origenSelect) origenSelect.value = localStorage.getItem('origen') || '';
    if (destinoSelect) destinoSelect.value = localStorage.getItem('destino') || '';
}

function validateOrigenDestino(origenSelect, destinoSelect) {
    if (origenSelect?.value && destinoSelect?.value && origenSelect.value === destinoSelect.value) {
        Swal.fire({ icon: "warning", title: "Atención", text: "El origen y destino no pueden ser iguales" });
        destinoSelect.value = "";
        localStorage.removeItem('destino');
        updateVentaData({ destino: null });
    }
}

function setupSearchFormSubmit() {
    document.querySelectorAll(".buscar-buses-form").forEach((form) => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            showLoadingSpinner();

            const origen = form.querySelector('[name="origen"]').value;
            const destino = form.querySelector('[name="destino"]').value;
            const fechaIda = form.querySelector('[name="fecha_ida"]').value;

            if (!origen || !destino || !fechaIda) {
                hideLoadingSpinner();
                return Swal.fire({ icon: "error", title: "Error", text: "Por favor, complete todos los campos" });
            }

            updateVentaData({ origen, destino, fechaIda, tiempoInicio: new Date().toISOString() });
            buscarProgramaciones(origen, destino, fechaIda);
        });
    });
}

function updateVentaData(update) {
    const ventaData = JSON.parse(localStorage.getItem('venta')) || {};
    Object.assign(ventaData, update);
    localStorage.setItem('venta', JSON.stringify(ventaData));
}

function buscarProgramaciones(origen, destino, fechaIda) {
    const formData = new FormData();
    formData.append('origen', origen);
    formData.append('destino', destino);
    formData.append('fecha_ida', fechaIda);

    fetch($("#url_web").val() + "home/buscar_programaciones", {
        method: "POST",
        body: formData
    })
        .then(response => response.ok ? response.json() : Promise.reject("Error en la solicitud"))
        .then(response => {
            hideLoadingSpinner();
            response.existe ? window.location.href = $("#url_web").val() + "carrito" : Swal.fire({ icon: "error", title: "Sin disponibilidad", text: "No se encontraron buses disponibles para esa fecha." });
        })
        .catch(error => {
            hideLoadingSpinner();
            console.error("Error:", error);
            Swal.fire({ icon: "error", title: "Error", text: "Ocurrió un error al buscar programaciones. Intente nuevamente." });
        });
}

function showLoadingSpinner() {
    $('#spinner').addClass('show');
    const cargaElement = document.getElementById("carga");
    if (cargaElement) {
        cargaElement.style.display = "block";
    }
}

function hideLoadingSpinner() {
    $('#spinner').removeClass('show');
    const cargaElement = document.getElementById("carga");
    if (cargaElement) {
        cargaElement.style.display = "none";
    }
}
