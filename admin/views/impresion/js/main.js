import * as instance from "../../../public/js/instance.js";

document.addEventListener("DOMContentLoaded", function (event) {

    let form = document.querySelector("#form");
    let button_save = document.querySelector("#button_save");
    let button_loadSave = document.querySelector("#button_loadSave");
    let div_impresora = document.querySelector("#div_impresora")
    let tipo_impresion = document.querySelector("#tipo_impresion")
    let nombre_impresora = document.querySelector("#nombre_impresora")
    let nombre_pc = document.querySelector("#nombre_pc")
    let ip_pc = document.querySelector("#ip_pc")
    let impresion_d = document.querySelector("#impresion_d")
    let check_impresion = document.querySelector('input[type="hidden"][name="impresion_d"]')

    // Nuevos elementos para Bluetooth
    let ip_celular = document.querySelector("#ip_celular")
    let impresora_bth = document.getElementById("impresora_bth");

    //Lógica existente
    if (impresion_d.value === "1") {
        div_impresora.classList.remove("d-none");
    }

    impresion_d.addEventListener('change', function () {
        check_impresion.value = this.checked ? '1' : '0';
        if (check_impresion.value == 1) {
            div_impresora.classList.remove("d-none");
        } else {
            div_impresora.classList.add("d-none");
            // Limpiar campos de PC
            nombre_impresora.value = ''
            nombre_pc.value = ''
            ip_pc.value = ''
        }
    });

    instance.Validate.allowInputMoney(["#igv"]);

    // Cargar datos existentes
    (function () {
        $.ajax({
            type: "get",
            url: instance._URL_ + "impresion/get_data",
            success: function (response) {
                let reply = JSON.parse(response)
                if (reply.success) {
                    // Datos existentes de PC
                    tipo_impresion.value = reply.message.tipo || '';
                    tipo_impresion.dispatchEvent(new Event('change'));
                    nombre_impresora.value = reply.message.nombre_impresora || ''
                    nombre_pc.value = reply.message.nombre_pc || ''
                    ip_pc.value = reply.message.ip_pc || ''
                    impresion_d.value = reply.message.estado
                    check_impresion.value = reply.message.estado
                    ip_celular.value = reply.message.ip_pc || ''

                    // Datos de Bluetooth (si existen)
                    if (reply.message.nombre_impresora_bt) {

                        // Si hay tipo de impresión guardado, seleccionarlo
                        if (reply.message.tipo_impresion) {
                            document.getElementById('tipo_impresion').value = reply.message.tipo_impresion;
                            // Mostrar la sección correspondiente
                            if (reply.message.tipo_impresion === 'bluetooth') {
                                impresora_bth.classList.remove('d-none');
                                mostrarImpresoraSeleccionada();
                            } else if (reply.message.tipo_impresion === 'escritorio') {
                                document.getElementById('impresora_pc').classList.remove('d-none');
                            }
                        }
                    }

                    if (impresion_d.value == "1") {
                        impresion_d.checked = true;
                        div_impresora.classList.remove("d-none");
                    }
                }
            }
        });
    }())

    // Envío del formulario
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(form);
        let data = [];

        if (check_impresion.value == 1) {
            let tipoImpresion = formData.get("tipo_impresion");

            if (tipoImpresion === 'escritorio') {
                data.push(formData.get("nombre_pc"));
                data.push(formData.get("ip_pc"));
            } else if (tipoImpresion === 'bluetooth') {
                data.push(ip_celular.value);
            }

            // Agregar los datos de Bluetooth al FormData
            if (tipoImpresion === 'bluetooth') {
                formData.append("ip_celular", ip_celular.value);
            }
        }

        if (instance.Validate.validateData(data)) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "impresion/register",
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
                beforeSend: () => {
                    button_loadSave.classList.remove("d-none");
                    button_save.classList.add("d-none");
                },
                success: function (response) {
                    try {
                        let reply = JSON.parse(response)
                        if (reply.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Exitoso',
                                text: reply.message,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false
                            })
                            // Redirigir a logout
                            setTimeout(() => {
                                window.location.href = instance._URL_ + "dashboard/logout";
                            }, 2000);

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erróneo',
                                text: reply.message,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false
                            })
                        }
                    } catch {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ha ocurrido un error, inténtalo más tarde.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false
                        })
                    }
                },
                complete: () => {
                    button_loadSave.classList.add("d-none");
                    button_save.classList.remove("d-none");
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: 'Rellene correctamente los campos',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            })
        }
    })

    // Cambio de tipo de impresión
    document.getElementById('tipo_impresion').addEventListener('change', function () {
        let tipo = this.value;
        document.getElementById('impresora_bth').classList.add('d-none');
        document.getElementById('impresora_pc').classList.add('d-none');

        if (tipo === 'bluetooth') {
            document.getElementById('impresora_bth').classList.remove('d-none');
        } else if (tipo === 'escritorio') {
            document.getElementById('impresora_pc').classList.remove('d-none');
        }
    });

});