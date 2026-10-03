import * as instance from "../../../public/js/instance.js";

document.getElementById("link_config").classList.add("active");

document.addEventListener("DOMContentLoaded", function () {

    let form = document.querySelector("#form");
    let button_save = document.querySelector("#button_save");
    let button_loadSave = document.querySelector("#button_loadSave");

    let igv = document.querySelector("#igv");
    let porcent_venta = document.querySelector("#porcent_venta");
    let porcent_venta_e = document.querySelector("#porcent_venta_e");

    let comision_nivel1 = document.querySelector("#comision_nivel1");
    let comision_nivel2 = document.querySelector("#comision_nivel2");

    let tp_comision_nivel1 = document.querySelector("#tp_comision_nivel1");
    let tp_comision_nivel2 = document.querySelector("#tp_comision_nivel2");

    let porcentaje_empresa = document.querySelector("#porcentaje_empresa");
    let porcentaje_empresa_caja = document.querySelector("#porcentaje_empresa_caja");

    let numero_GN = document.getElementById("numero_GN");
    let numero_GN_check = document.getElementById("numero_GN_check");

    let p_empresaxcaja = document.getElementById("p_empresaxcaja");
    let p_empresaxcaja_check = document.getElementById("p_empresaxcaja_check");

    instance.Validate.allowInputMoney([
        "#igv",
        "#porcent_venta",
        "#porcent_venta_e",
        "#comision_nivel1",
        "#comision_nivel2",
        "#porcentaje_empresa",
        "#porcentaje_empresa_caja"
    ]);

    function actualizarPlaceholderComision() {
        comision_nivel1.placeholder = tp_comision_nivel1.value === "MONTO" ? "Ej: 5.00" : "Ej: 2.00";
        comision_nivel2.placeholder = tp_comision_nivel2.value === "MONTO" ? "Ej: 5.00" : "Ej: 2.00";
    }

    tp_comision_nivel1.addEventListener("change", actualizarPlaceholderComision);
    tp_comision_nivel2.addEventListener("change", actualizarPlaceholderComision);

    numero_GN_check.addEventListener("change", function () {
        numero_GN.value = this.checked ? 1 : 0;
    });

    p_empresaxcaja_check.addEventListener("change", function () {
        p_empresaxcaja.value = this.checked ? 1 : 0;
    });

    function loadConfiguracion() {
        $.ajax({
            type: "get",
            url: instance._URL_ + "configuracion/get_data",
            success: function (response) {
                let reply = JSON.parse(response);

                if (reply.success) {
                    igv.value = reply.message.igv || "18.00";
                    porcent_venta.value = reply.message.porcent_venta || "0.00";
                    porcent_venta_e.value = reply.message.porcent_venta_e || "0.00";

                    comision_nivel1.value = reply.message.comision_nivel1 || "0.00";
                    comision_nivel2.value = reply.message.comision_nivel2 || "0.00";

                    tp_comision_nivel1.value = reply.message.tp_comision_nivel1 || "PORCENTAJE";
                    tp_comision_nivel2.value = reply.message.tp_comision_nivel2 || "PORCENTAJE";

                    porcentaje_empresa.value = reply.message.porcentaje_empresa || "0.00";
                    porcentaje_empresa_caja.value = reply.message.porcentaje_empresa_caja || "0.00";

                    numero_GN.value = reply.message.numero_GN || "0";
                    p_empresaxcaja.value = reply.message.p_empresaxcaja || "0";

                    numero_GN_check.checked = numero_GN.value == "1";
                    p_empresaxcaja_check.checked = p_empresaxcaja.value == "1";

                    actualizarPlaceholderComision();
                }
            },
            error: function () {
                console.error("Error al cargar configuración");
            }
        });
    }

    function validarCamposPorcentaje() {
        let campos = [
            { elemento: igv, nombre: "IGV" },
            { elemento: porcent_venta, nombre: "% de ventas Pasaje" },
            { elemento: porcent_venta_e, nombre: "% de ventas Encomienda" },
            { elemento: comision_nivel1, nombre: "Comisión Nivel 1" },
            { elemento: comision_nivel2, nombre: "Comisión Nivel 2" },
            { elemento: porcentaje_empresa, nombre: "Porcentaje empresa - programación" },
            { elemento: porcentaje_empresa_caja, nombre: "Porcentaje empresa - Caja chica" }
        ];

        for (let i = 0; i < campos.length; i++) {
            let campo = campos[i];
            let valor = campo.elemento.value.trim();

            if (valor === "") {
                Swal.fire({
                    icon: "error",
                    title: "Campo requerido",
                    text: "El campo " + campo.nombre + " es obligatorio",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }

            let numero = parseFloat(valor);

            if (isNaN(numero)) {
                Swal.fire({
                    icon: "error",
                    title: "Valor inválido",
                    text: "El campo " + campo.nombre + " debe ser un número válido",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }

            if (numero < 0) {
                Swal.fire({
                    icon: "error",
                    title: "Valor inválido",
                    text: "El campo " + campo.nombre + " no puede ser negativo",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }

            if (
                campo.elemento !== comision_nivel1 &&
                campo.elemento !== comision_nivel2 &&
                numero > 100
            ) {
                Swal.fire({
                    icon: "error",
                    title: "Valor inválido",
                    text: "El campo " + campo.nombre + " no puede ser mayor a 100%",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }

            if (
                campo.elemento === comision_nivel1 &&
                tp_comision_nivel1.value === "PORCENTAJE" &&
                numero > 100
            ) {
                Swal.fire({
                    icon: "error",
                    title: "Valor inválido",
                    text: "La Comisión Nivel 1 no puede ser mayor a 100%",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }

            if (
                campo.elemento === comision_nivel2 &&
                tp_comision_nivel2.value === "PORCENTAJE" &&
                numero > 100
            ) {
                Swal.fire({
                    icon: "error",
                    title: "Valor inválido",
                    text: "La Comisión Nivel 2 no puede ser mayor a 100%",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Entendido"
                });
                campo.elemento.focus();
                return false;
            }
        }

        return true;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!validarCamposPorcentaje()) {
            return;
        }

        let formData = new FormData(form);

        $.ajax({
            type: "post",
            url: instance._URL_ + "configuracion/register",
            contentType: false,
            cache: false,
            processData: false,
            data: formData,
            beforeSend: function () {
                button_loadSave.classList.remove("d-none");
                button_save.classList.add("d-none");
            },
            success: function (response) {
                try {
                    let reply = JSON.parse(response);

                    if (reply.success) {
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: reply.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            confirmButtonText: "Aceptar"
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: reply.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            confirmButtonText: "Aceptar"
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ha ocurrido un error, inténtalo más tarde.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        confirmButtonText: "Aceptar"
                    });
                }
            },
            complete: function () {
                button_loadSave.classList.add("d-none");
                button_save.classList.remove("d-none");
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión con el servidor",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: "Aceptar"
                });

                button_loadSave.classList.add("d-none");
                button_save.classList.remove("d-none");
            }
        });
    });

    loadConfiguracion();
    actualizarPlaceholderComision();
});