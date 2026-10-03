import * as instance from "../../../public/js/instance.js";
document.getElementById("link_config").classList.add("active")

document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("form_config_cotizacion");
    var buttonSave = document.getElementById("button_save");
    var buttonLoadSave = document.getElementById("button_loadSave");
    var loader = document.getElementById("loader");

    $('#termscond_cotizacion').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strikethrough']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontsize', ['fontsize']],
            ['font', ['fontname']],
            ['height', ['height']]
        ],
        lineHeights: ['0', '0.3', '0.5', '0.7', '1.0', '1.2', '1.5', '2.0', '3.0'],
        placeholder: 'Escribe los mensajes personalizados para el cotizacion...',
        tabsize: 2,
        height: 150
    });

    loader.classList.remove('d-none');

    fetch(instance._URL_ + 'configuracion/get_data_config_cotizacion')
        .then(function (res) {
            if (!res.ok) throw new Error("Error de red");
            return res.json();
        })
        .then(function (data) {
            if (!data.success) return;
            var message = data.message;
            if (message.terminos_condiciones) {
                $('#termscond_cotizacion').summernote('code', message.terminos_condiciones);
            }
        })
        .catch(function () {
            Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar la configuración." });
        })
        .finally(function () {
            loader.classList.add('d-none');
        });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        let terminos_cotizacion = $('#termscond_cotizacion').summernote('code');

        var formData = new FormData(form);
        formData.append("termscond_cotizacion", terminos_cotizacion);

        var dataToValidate = [
            formData.get("termscond_cotizacion")
        ];


        if (!instance.Validate.validateData(dataToValidate)) {
            Swal.fire({ icon: "error", title: "Campos inválidos", text: "Rellene correctamente los campos." });
            return;
        }

        buttonSave.classList.add("d-none");
        buttonLoadSave.classList.remove("d-none");

        fetch(instance._URL_ + 'configuracion/register_config_cotizacion', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                Swal.fire({ icon: "success", title: "Operación exitosa", text: data.message })
                    .then(function (result) {
                        if (result.isConfirmed) location.reload();
                    });
            })
            .catch(error => {
                console.error(error);
                Swal.fire({ icon: "error", title: "Error de red", text: "No se pudo guardar la configuración." });
            })
            .finally(function () {
                buttonSave.classList.remove("d-none");
                buttonLoadSave.classList.add("d-none");
            });
    });
});