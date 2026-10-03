import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    // Variables Form
    let form_pasajero = document.querySelector("#form_pasajero")
    let form_celular = document.getElementById("form_celular");
    let btn_guardar_celular = document.getElementById("btn_guardar_celular");
    let load_guardar_celular = document.getElementById("load_guardar_celular");
    let celular_usuario = document.getElementById("celular_usuario");
    let id_pasajero = document.querySelector("#id_pasajero")
    let tp_docu = document.querySelector("#tp_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let remitente = document.querySelector("#remitente")
    let destinatario = document.querySelector("#destinatario")
    let remitente_id = document.querySelector("#remitente_id")
    let destinatario_id = document.querySelector("#destinatario_id")
    let pagante = document.querySelector("#pagante")
    let pagante_id = document.querySelector("#pagante_id")
    let remitenteEntregar = document.querySelector("#remitenteEntregar")
    let remitenteEntregar_id = document.querySelector("#remitenteEntregar_id")
    let receptor = document.querySelector("#receptor")
    let receptor_id = document.querySelector("#receptor_id")
    let celular = document.querySelector("#celular")
    let email = document.querySelector("#email")
    let estado_sunat = document.querySelector("#estado_sunat")
    let condicion_sunat = document.querySelector("#condicion_sunat")
    let estado = document.querySelector("#estado")
    let operacion = document.querySelector('#operacion')
    let table_contacto = document.querySelector("#table_contacto")
    let tp_comprobante = document.querySelector('#tp_comprobante')
    let button_save_c = document.querySelector("#button_save_c")
    let button_cancel_c = document.querySelector("#button_cancel_c")
    let button_loadSave_c = document.querySelector("#button_loadSave_c")
    let button_loadSearch = document.querySelector("#button_loadSearch")
    let num_docuc = document.querySelector("#num_docu")
    let buscar_remitente = document.querySelector("#buscar_remitente")
    let buscar_pagante = document.querySelector("#buscar_pagante")
    let loader_buscar_r = document.querySelector("#loader_buscar")
    let loader_buscar_p = document.querySelector("#loader_buscar_p")
    let buscar_destinatario = document.querySelector("#buscar_destinatario")
    let loader_buscar_d = document.querySelector("#loader_buscar")
    let button_saveR = document.querySelector("#button_save_c")
    let buscar_remitenteEntregar = document.querySelector("#buscar_remitenteEntregar")
    let loader_buscar_rE = document.querySelector("#loader_buscar_rE")
    let button_loadRegistrarR = document.querySelector("#button_loadRegistrarR")
    let d_extra = document.querySelector("#d_extra")
    let obs_destinatario = document.querySelector("#obs_destinatario")
    let dato_destinatario = document.querySelector("#dato_destinatario")
    let permiso_celular = document.getElementById("permiso_celular");
    let celularGuardado = false;
    let origenModalCelular = null;

    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.Validate.allowInputNum(["#num_docu", "#celular"])
    instance.Validate.allowInputStringSpace(["#nombres", "#apellidos"])

    // ============================================================
    // FUNCIONES REUSABLES (llamadas por Enter, click "Buscar" y autocomplete)
    // ============================================================

    function buscarRemitente(numDocu) {

        buscar_remitente.classList.add("d-none");
        loader_buscar_r.classList.remove("d-none");

        var matched = false;
        const formData = new FormData();
        formData.append('cliente', numDocu);

        fetch(instance._URL_ + 'pasaje/cliente_get', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {
                    matched = true;

                    if (data.message.num_docu && tp_comprobante.value == 3) {
                        if (data.message.id_tp_docu == 6) {
                            remitente_id.value = data.message.id_usuario;
                            $('#remitente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                        } else {
                            remitente_id.value = data.message.id_usuario;
                            $('#remitente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                            $('#destinatario').focus();
                        }
                    } else if (data.message.num_docu && tp_comprobante.value == 1) {
                        remitente_id.value = data.message.id_usuario;
                        $('#remitente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                        $('#destinatario').focus();
                    } else if (data.message.num_docu && tp_comprobante.value == 2) {
                        remitente_id.value = data.message.id_usuario;
                        $('#remitente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                        $('#destinatario').focus();
                    }

                    if (data.message.id_tp_docu == 1 && permiso_celular.value == '1') {
                        const celular = data.message.celular ? data.message.celular.trim() : '';

                        if (!celular || celular === '000000000' || celular === '999999999') {

                            celularGuardado = false;
                            origenModalCelular = "remitente";

                            const modalElement = document.getElementById('modal_celular');
                            $("#id_usuario_celular").val(data.message.id_usuario);
                            const modal_celular = new bootstrap.Modal(modalElement, {
                                backdrop: 'static',
                                keyboard: false
                            });

                            modalElement.addEventListener('shown.bs.modal', () => {
                                document.getElementById('celular_usuario').focus();
                            }, { once: true });

                            modal_celular.show();
                        }
                    }

                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 1, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_remitente.classList.remove("d-none");
                loader_buscar_r.classList.add("d-none");
            });
    }

    function buscarDestinatario(numDocu) {

        buscar_destinatario.classList.add("d-none");
        loader_buscar_d.classList.remove("d-none");

        var matched = false;
        const formData = new FormData();
        formData.append('cliente', numDocu);

        fetch(instance._URL_ + 'pasaje/cliente_get', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {
                    matched = true;
                    destinatario_id.value = data.message.id_usuario;
                    $('#destinatario').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);

                    if (d_extra.value == "1") {
                        $('#obs_destinatario').focus();
                    } else {
                        if (tp_comprobante.value == 31) {
                            $('#pagante').focus();
                        } else {
                            dato_destinatario.classList.add('d-none');
                            selectizeDestinoTerminal.open();
                            $('#destino_terminal').focus();
                        }
                    }

                    if (data.message.id_tp_docu == 1 && permiso_celular.value == '1') {
                        const celular = data.message.celular ? data.message.celular.trim() : '';

                        if (!celular || celular === '000000000' || celular === '999999999') {

                            celularGuardado = false;
                            origenModalCelular = "destinatario";

                            const modalElement = document.getElementById('modal_celular');
                            $("#id_usuario_celular").val(data.message.id_usuario);
                            const modal_celular = new bootstrap.Modal(modalElement, {
                                backdrop: 'static',
                                keyboard: false
                            });

                            modalElement.addEventListener('shown.bs.modal', () => {
                                document.getElementById('celular_usuario').focus();
                            }, { once: true });

                            modal_celular.show();
                        }
                    }

                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 2, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_destinatario.classList.remove("d-none");
                loader_buscar_d.classList.add("d-none");
                d_extra.value = 0;
            });
    }

    function buscarPagante(numDocu) {

        buscar_pagante.classList.add("d-none");
        loader_buscar_p.classList.remove("d-none");

        var matched = false;
        const formData = new FormData();
        formData.append('cliente', numDocu);

        fetch(instance._URL_ + 'pasaje/cliente_get', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {
                    matched = true;
                    pagante_id.value = data.message.id_usuario;
                    $('#pagante').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                    $('#pass').focus();
                    selectizeDestinoTerminal.open();
                    $('#destino_terminal').focus();
                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 3, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_pagante.classList.remove("d-none");
                loader_buscar_p.classList.add("d-none");
            });
    }

    function buscarRemitenteEntregar(numDocu) {

        buscar_remitenteEntregar.classList.add("d-none");
        loader_buscar_rE.classList.remove("d-none");

        var matched = false;
        const formData = new FormData();
        formData.append('cliente', numDocu);

        fetch(instance._URL_ + 'pasaje/cliente_get', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {
                    matched = true;

                    if (data.message.num_docu && tp_comprobante.value == 3) {
                        remitenteEntregar_id.value = data.message.id_usuario;
                        $('#remitenteEntregar').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                    }
                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 4, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_remitenteEntregar.classList.remove("d-none");
                loader_buscar_rE.classList.add("d-none");
            });
    }

    function buscarReceptor(numDocu) {

        buscar_receptor.classList.add("d-none");
        button_loadRegistrarR.classList.remove("d-none");

        var matched = false;
        const formData = new FormData();
        formData.append('cliente', numDocu);

        fetch(instance._URL_ + 'pasaje/cliente_get', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {
                    matched = true;
                    receptor_id.value = data.message.id_usuario;
                    $('#receptor').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 5, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_receptor.classList.remove("d-none");
                button_loadRegistrarR.classList.add("d-none");
            });
    }

    // ============================================================
    // HANDLERS DE ENTER (validan y delegan; ceden el paso al autocomplete
    // si el dropdown está abierto con opciones)
    // ============================================================

    $('#remitente').on('keydown', function (e) {
        let params = [];

        if (tp_comprobante.value == 1) {
            params = [11];
        } else {
            params = [11, 8];
        }

        if (e.key === 'Enter') {

            if (remitenteAuto.isOpen && remitenteAuto.items.length > 0) {
                e.preventDefault();
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarRemitente(inputValue);
            }

            e.preventDefault();
        }
    });

    $('#destinatario').on('keydown', function (e) {
        let params = [11, 8];

        if (e.key === 'Enter') {

            if (destinatarioAuto.isOpen && destinatarioAuto.items.length > 0) {
                e.preventDefault();
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarDestinatario(inputValue);
            }

            e.preventDefault();
        }
    });

    $('#pagante').on('keydown', function (e) {
        let params = [11, 8];

        if (e.key === 'Enter') {

            if (paganteAuto.isOpen && paganteAuto.items.length > 0) {
                e.preventDefault();
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarPagante(inputValue);
            }

            e.preventDefault();
        }
    });

    $('#remitenteEntregar').on('keydown', function (e) {
        let params = [11, 8];

        if (e.key === 'Enter') {

            if (remitenteEntregarAuto.isOpen && remitenteEntregarAuto.items.length > 0) {
                e.preventDefault();
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarRemitenteEntregar(inputValue);
            }

            e.preventDefault();
        }
    });

    $('#receptor').on('keydown', function (e) {
        let params = [11, 8];

        if (e.key === 'Enter') {

            if (receptorAuto.isOpen && receptorAuto.items.length > 0) {
                e.preventDefault();
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarReceptor(inputValue);
            }

            e.preventDefault();
        }
    });

    // ============================================================
    // HANDLERS DE CLICK EN "BUSCAR" (misma validación, mismas funciones)
    // ============================================================

    document.querySelector("#buscar_remitente").addEventListener("click", (e) => {
        e.preventDefault();

        let params = [];

        if (tp_comprobante.value == 1) {
            params = [11];
        } else {
            params = [11, 8];
        }

        var inputValue = $("#remitente").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarRemitente(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_destinatario").addEventListener("click", (e) => {
        e.preventDefault();

        let params = [11, 8];

        var inputValue = $("#destinatario").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarDestinatario(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_pagante").addEventListener("click", (e) => {
        e.preventDefault();

        // Unificado a [11, 8] para que coincida con la validación del Enter
        // (antes el click solo aceptaba 8 dígitos).
        let params = [11, 8];

        var inputValue = $("#pagante").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarPagante(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_remitenteEntregar").addEventListener("click", (e) => {
        e.preventDefault();

        // Unificado a [11, 8] (antes el click solo aceptaba 8 dígitos).
        let params = [11, 8];

        var inputValue = $("#remitenteEntregar").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarRemitenteEntregar(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_receptor").addEventListener("click", (e) => {
        e.preventDefault();

        let params = [11, 8];

        var inputValue = $("#receptor").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarReceptor(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    // ============================================================
    // AUTOCOMPLETE: llama directamente a las funciones, sin simular eventos
    // ============================================================

    const remitenteAuto = new instance.NoxAutoComplete({
        input: "#remitente",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarRemitente(item.num_docu);
        }
    });

    const destinatarioAuto = new instance.NoxAutoComplete({
        input: "#destinatario",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarDestinatario(item.num_docu);
        }
    });

    const paganteAuto = new instance.NoxAutoComplete({
        input: "#pagante",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarPagante(item.num_docu);
        }
    });

    const remitenteEntregarAuto = new instance.NoxAutoComplete({
        input: "#remitenteEntregar",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarRemitenteEntregar(item.num_docu);
        }
    });

    const receptorAuto = new instance.NoxAutoComplete({
        input: "#receptor",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarReceptor(item.num_docu);
        }
    });

    // ============================================================
    // MODAL "NUEVO CLIENTE" (sin cambios respecto a tu versión original)
    // ============================================================

    function abrirModal(tituloText, operacionValue, dni = null, t_d = null) {
        let operacion = document.querySelector('#operacion');
        let titulo = document.getElementById('titulo');
        titulo.textContent = tituloText;
        operacion.value = operacionValue;

        if (dni && t_d) {
            $("#tp_docu").val(t_d).trigger("change");
            num_docuc.value = dni;
            $('#button_search').click();
        }

        $('#num_docu').focus();

        $("#modal_pasajero").modal("show");
    }

    $('#nombres').on('keydown', function (e) {
        if (e.key === 'Enter') {
            if (tp_docu.value == 1) {
                $('#apellidos').focus();
            }
        }
    });

    $('#apellidos').on('keydown', function (e) {
        if (e.key === 'Enter') {
            if (tp_docu.value == 1) {
                $('#genero').focus();
            }
        }
    });

    $("#genero").on("change", function (e) {
        $("#button_save_c").focus();
    });

    // Reset formulario al Cerrar modal
    $("#modal_pasajero").on("hidden.bs.modal", (e) => {
        $("#modal_pasajero").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_pasajero.value = ""
        //$("#ubigeo").val("").trigger("change")
        num_docuc.setAttribute("minlength", 8)
        num_docuc.setAttribute("maxlength", 8)
        button_search.classList.remove("d-none")
        $('#table_contacto tbody tr').remove();
        nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
        nombres.parentElement.classList.remove("d-none")
        apellidos.parentElement.classList.remove("d-none")
        fecha_nacimiento.parentElement.classList.remove("d-none")
        genero.parentElement.classList.remove("d-none")
        estado_sunat.parentElement.classList.add("d-none")
        condicion_sunat.parentElement.classList.add("d-none")
        selectNacionalidad.setValue("PERÚ");
    })

    // Envio de regitro
    form_pasajero.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form_pasajero)
        let data = [
            {
                label: "Número de documento",
                value: formData.get("num_docu")
            },
            {
                label: "Nombres",
                value: formData.get("nombres")
            },
            {
                label: "Ubigeo",
                value: formData.get("ubigeo")
            },
            {
                label: "Dirección",
                value: formData.get("direccion")
            },
            {
                label: "Terminal",
                value: formData.get("terminal")
            },
            {
                label: "Estado",
                value: formData.get("estado")
            }
        ];

        if (tp_docu.value == 1) {
            data.push({
                label: "Apellidos",
                value: formData.get("apellidos")
            });
        }

        if (permiso_celular.value == '1') {
            data.push({
                label: "Celular",
                value: formData.get("celular")
            });
        }
        const errores = instance.Validate.validateFields(data);

        if (errores.length > 0) {
            instance.Toast.operacion_informativa(
                "Falta completar: " + errores.join(", ")
            );
            return;
        }

        let nombres_contacto = document.querySelectorAll(".nombres_contacto")
        let apellidos_contacto = document.querySelectorAll(".apellidos_contacto")
        let celular_contacto = document.querySelectorAll(".celular_contacto")
        let observacion_contacto = document.querySelectorAll(".observacion_contacto")
        let contactos = []
        Object.values(nombres_contacto).map((e, i, array) => {
            return contactos.push([e.value, apellidos_contacto[i].value, celular_contacto[i].value, observacion_contacto[i].value])
        })

        formData.set("contactos", JSON.stringify(contactos))
        button_save_c.classList.add("d-none")
        button_cancel_c.classList.add("d-none")
        button_loadSave_c.classList.remove("d-none")
        fetch(instance._URL_ + "pasajero/crud_register", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message)
                    let formData = new FormData();
                    formData.append('cliente', data.usuario);

                    fetch(instance._URL_ + 'pasaje/cliente_get', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.status);
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                remitenteAuto.clearCache();
                                destinatarioAuto.clearCache();
                                paganteAuto.clearCache();
                                remitenteEntregarAuto.clearCache();
                                receptorAuto.clearCache();
                                if (operacion.value == 1) {
                                    remitente_id.value = data.message.id_usuario;
                                    $('#remitente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                    $('#destinatario').focus();
                                } else if (operacion.value == 2) {
                                    destinatario_id.value = data.message.id_usuario;
                                    $('#destinatario').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                    if (tp_comprobante.value == 31) {
                                        $('#pagante').focus();
                                    } else {
                                        dato_destinatario.classList.add('d-none')
                                        selectizeDestinoTerminal.open();
                                        $('#destino_terminal').focus();
                                    }
                                } else if (operacion.value == 3) {
                                    pagante_id.value = data.message.id_usuario;
                                    $('#pagante').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                    selectizeDestinoTerminal.open();
                                    $('#destino_terminal').focus();
                                } else if (operacion.value == 4) {
                                    remitenteEntregar_id.value = data.message.id_usuario;
                                    $('#remitenteEntregar').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                } else if (operacion.value == 5) {
                                    remitenteEntregar_id.value = data.message.id_usuario;
                                    $('#receptor').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                }
                            } else {
                                instance.Toast.operacion_erronea("No se encontro al usuario");
                            }
                        })
                        .catch(error => instance.Toast.operacion_erronea(error.message));
                    $('#modal_pasajero').modal('toggle')
                } else instance.Toast.operacion_erronea(data.message)
            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message)
            })
            .finally(() => {
                button_save_c.classList.remove("d-none")
                button_cancel_c.classList.remove("d-none")
                button_loadSave_c.classList.add("d-none")
                //resetToInitialState();
            })
    })

    // Consulta RENIEC o SUNAT
    document.querySelector("#button_search").addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        $('#button_loadSearch').focus();
        const reply_val = instance.Validate.validateData([num_docuc.value]);

        if (reply_val && (num_docuc.value.length === 8 || num_docuc.value.length === 11)) {
            const ruta_query = tp_docu.value == 1 ? 'reniec' : 'sunat';
            const formData = new FormData();
            formData.set("docu", num_docuc.value);

            button_search.disabled = true;
            button_search.classList.add("d-none");
            button_loadSearch.classList.remove("d-none");

            fetch(instance._URL_ + 'api/' + ruta_query, {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        // Mejor manejo de errores HTTP
                        throw new Error(`Error ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (tp_docu.value == 6) { // Si es documento SUNAT
                            nombres.value = data.data.nombre_o_razon_social || "";
                            estado_sunat.value = data.data.estado || "";
                            condicion_sunat.value = data.data.condicion || "";

                            if (data.data.departamento || data.data.provincia || data.data.distrito) {
                                $("#ubigeo").val(data.data.ubigeo[2] || "").trigger("change");
                                direccion.value = data.data.direccion || "";
                                $('#button_save_c').focus();
                            }
                        } else { // Si es documento RENIEC
                            nombres.value = data.data.nombres || "";
                            apellidos.value = `${data.data.apellido_paterno || ""} ${data.data.apellido_materno || ""}`.trim();
                        }
                        instance.Toast.operacion_exitosa('Datos encontrados');
                        $('#button_save_c').focus();
                    } else {
                        instance.Toast.operacion_erronea('Datos no hallados :c');
                        $("#nombres").focus();
                    }
                })
                .catch(error => {
                    // Manejo general de errores
                    instance.Toast.operacion_erronea(error.message || "Error desconocido.");
                })
                .finally(() => {
                    button_search.disabled = false;
                    button_search.classList.remove("d-none");
                    button_loadSearch.classList.add("d-none");
                });
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    $("#tp_docu").on("change", function (e) {
        num_docu.value = ""
        nombres.value = ""
        apellidos.value = ""
        estado_sunat.value = ""
        condicion_sunat.value = ""
        //$("#ubigeo").val("").trigger("change")
        direccion.value = "S/D"
        if (tp_docu.value == 6) {
            nombres.closest("div").querySelector("label").innerHTML = `Razon social<span class="requiredField">*</span>`
            // nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.add("d-none")
            // apellidos.setAttribute("readonly", false)
            num_docu.setAttribute("minlength", 11)
            num_docu.setAttribute("maxlength", 11)
            estado_sunat.parentElement.classList.remove("d-none")
            condicion_sunat.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.add("d-none")
            genero.parentElement.classList.add("d-none")
            button_search.disabled = false
        } else if (tp_docu.value == 4) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true
        } else if (tp_docu.value == 7) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true
        } else if (tp_docu.value == 0) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true
        } else {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            // nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            // apellidos.setAttribute("readonly", true)
            num_docu.setAttribute("minlength", 8)
            num_docu.setAttribute("maxlength", 8)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = false
        }
    })

    // Licencia
    $('#table_contacto thead').on('click', '#btn_add_contacto', function (e) {
        let tbody = table_contacto.getElementsByTagName("tbody")[0]
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input type='text' class='form-control nombres_contacto' required></td>
                <td><input type='text' class='form-control apellidos_contacto' required></td>
                <td><input type='text' class='form-control celular_contacto' min-length='9' max-length='9' required></td>
                <td><textarea class='form-control observacion_contacto' row='3' column='10'></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `)
    })

    $('#table_contacto tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_contacto tbody").removeChild(this.closest("tr"))
    });

    const set_contactos = (data) => {
        let tbody = table_contacto.getElementsByTagName("tbody")[0]
        Object.values(data).forEach((e, i, a) => {
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input type='text' class='form-control nombres_contacto' value='${e.nombres}' required></td>
                <td><input type='text' class='form-control apellidos_contacto' value='${e.apellidos}' required></td>
                <td><input type='text' class='form-control celular_contacto' min-length='9' max-length='9' value='${e.celular}' required></td>
                <td><textarea class='form-control observacion_contacto' row='3' column='10' value='${e.observacion}'></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `)
        })
    }

    const get_clientes = (targets = []) => {
        fetch(instance._URL_ + "encomienda/get_clientes", { method: "GET" })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    targets.forEach(elemnt => {
                        document.querySelector(elemnt).innerHTML = ""
                        Object.values(data.message).forEach((e, i, array) => [
                            document.querySelector(elemnt).insertAdjacentHTML('beforeend', `
                                <option value="${e.id_usuario}">${e.nombres} ${e.apellidos} - ${e.num_docu}</option>
                            `)
                        ])
                    });
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    }

    const mostrar_clientes = (targets = [], valor) => {
        var consul = ""
        if (valor !== 0) {
            consul = "?tp_doc=6";
        }
        fetch(instance._URL_ + "pasaje/get_clientes" + consul, { method: "GET" })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    targets.forEach(elemnt => {
                        const selectElement = document.querySelector(elemnt);
                        selectElement.innerHTML = ""
                        Object.values(data.message).forEach((e, i, array) => [
                            document.querySelector(elemnt).insertAdjacentHTML('beforeend', `
                                <option value="${e.id_usuario}">${e.nombres} ${e.apellidos} - ${e.num_docu}</option>
                            `)
                        ])
                    });
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    }

    form_celular.addEventListener("submit", (e) => {
        e.preventDefault();

        let formData = new FormData(form_celular);

        const celular = celular_usuario.value.trim();

        if (!/^\d{9}$/.test(celular)) {
            instance.Toast.operacion_informativa("Ingrese un número de celular válido");
            return;
        }

        let data = [
            formData.get("celular_usuario"),
            formData.get("id_usuario_celular")
        ];

        formData.set("celular", celular);

        if (instance.Validate.validateData(data)) {

            btn_guardar_celular.classList.add("d-none");
            load_guardar_celular.classList.remove("d-none");

            fetch(instance._URL_ + "pasajero/registrar_celular_usuario", {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message);
                        celularGuardado = true;
                        const modal_celular = bootstrap.Modal.getInstance(document.getElementById('modal_celular'));
                        modal_celular.hide();
                    } else {
                        instance.Toast.operacion_erronea(data.message);
                    }
                })
                .catch(error => {
                    instance.Toast.operacion_erronea(error.message);
                })
                .finally(() => {
                    btn_guardar_celular.classList.remove("d-none");
                    load_guardar_celular.classList.add("d-none");
                });

        } else {
            instance.Toast.operacion_informativa('Rellene correctamente los campos');
        }
    });

    // Bloquear teclas inválidas
    celular_usuario.addEventListener("keydown", function (e) {
        const allowed = ["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab", "Home", "End"];

        // Permitir combinaciones con Ctrl o Cmd (copiar, pegar, seleccionar todo, etc.)
        if (e.ctrlKey || e.metaKey) return;

        if (!allowed.includes(e.key) && !/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });

    // Limpiar si logran pegar texto (clic derecho, drag & drop)
    celular_usuario.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
    });

    // Bloquear paste con texto no numérico
    celular_usuario.addEventListener("paste", function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData("text");
        const soloNumeros = pasted.replace(/[^0-9]/g, '').slice(0, 9);
        this.value = soloNumeros;
    });

    // Bloquear drag & drop de texto
    celular_usuario.addEventListener("drop", function (e) {
        e.preventDefault();
        const dropped = e.dataTransfer.getData("text");
        const soloNumeros = dropped.replace(/[^0-9]/g, '').slice(0, 9);
        this.value = soloNumeros;
    });

    $("#modal_celular").on("hidden.bs.modal", () => {
        document.querySelector("#modal_celular form").reset();

        document.querySelectorAll("#modal_celular .needs-validation").forEach(form => {
            form.classList.remove("was-validated");
        });

        if (!celularGuardado) {

            if (origenModalCelular === "remitente") {
                remitente.value = "";
                remitente_id.value = "";
                $("#remitente").focus();
            }

            if (origenModalCelular === "destinatario") {
                destinatario.value = "";
                destinatario_id.value = "";
                $("#destinatario").focus();
            }
        }

        celularGuardado = false;
        origenModalCelular = null;
        id_usuario_celular.value = "";
    });
})