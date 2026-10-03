import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    // Variables Form
    let form_cliente = document.querySelector("#form_cliente")
    let id_pasajero = document.querySelector("#id_pasajero")
    let tp_docu = document.querySelector("#tp_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let remitente = document.querySelector("#remitente")
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
    let loader_buscar_c = document.querySelector("#loader_buscar")
    let loader_buscar_p = document.querySelector("#loader_buscar_p")
    let buscar_destinatario = document.querySelector("#buscar_destinatario")
    let loader_buscar_d = document.querySelector("#loader_buscar")
    let button_saveR = document.querySelector("#button_save_c")
    let buscar_remitenteEntregar = document.querySelector("#buscar_remitenteEntregar")
    let button_loadRegistrarR = document.querySelector("#button_loadRegistrarR")
    let d_extra = document.querySelector("#d_extra")
    let obs_destinatario = document.querySelector("#obs_destinatario")
    let dato_destinatario = document.querySelector("#dato_destinatario")

    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.Validate.allowInputNum(["#num_docu", "#celular"])
    instance.Validate.allowInputStringSpace(["#nombres", "#apellidos"])

    $(".open_modal_cliente").click(function (e) {
        e.preventDefault();
        $("#modal_cliente").modal("show")
    });

    function buscarCliente(numDocu) {

        buscar_cliente.classList.add("d-none");
        loader_buscar.classList.remove("d-none");

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

                    cliente_id.value = data.message.id_usuario;
                    $('#cliente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente)

                } else {
                    if (!matched) {
                        abrirModal("NUEVO CLIENTE", 1, numDocu, (numDocu.length === 11) ? 6 : 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_cliente.classList.remove("d-none");
                loader_buscar.classList.add("d-none");
            });
    }

    //Meotodo para  la busqueda de cliente
    $('#cliente').on('keydown', function (e) {
        let params = [11, 8];
        let doc = '';

        if (e.key === 'Enter') {

            if (clienteAuto.isOpen && clienteAuto.items.length > 0) {
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.includes(inputValue.length) && isNumber) {
                buscarCliente(inputValue);
            }
            e.preventDefault();
        }
    });

    //Abrir modal 
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

        $("#modal_cliente").modal("show");
    }

    //Buscar pasajero 
    document.querySelector("#buscar_cliente").addEventListener("click", (e) => {
        e.preventDefault();

        let params = [11, 8];

        var inputValue = $("#cliente").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarCliente(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    const clienteAuto = new instance.NoxAutoComplete({
        input: "#cliente",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarCliente(item.num_docu);
        }
    });


    // Reset formulario al Cerrar modal
    $("#modal_cliente").on("hidden.bs.modal", (e) => {
        $("#modal_cliente").find("form").trigger("reset")
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
    form_cliente.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form_cliente)
        let data = [
            formData.get("num_docu"), formData.get("nombres"),
            formData.get("ubigeo"), formData.get("direccion"),
            formData.get("terminal"), formData.get("estado")
        ]
        if (tp_docu.value == 1) {
            data.push(formData.get("apellidos"),)
        }

        let nombres_contacto = document.querySelectorAll(".nombres_contacto")
        let apellidos_contacto = document.querySelectorAll(".apellidos_contacto")
        let celular_contacto = document.querySelectorAll(".celular_contacto")
        let observacion_contacto = document.querySelectorAll(".observacion_contacto")
        let contactos = []
        Object.values(nombres_contacto).map((e, i, array) => {
            return contactos.push([e.value, apellidos_contacto[i].value, celular_contacto[i].value, observacion_contacto[i].value])
        })

        if (instance.Validate.validateData(data)) {
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
                                    cliente_id.value = data.message.id_usuario;
                                    $("#cliente").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                                } else {
                                    instance.Toast.operacion_erronea("No se encontro al usuario");
                                }
                            })
                            .catch(error => instance.Toast.operacion_erronea(error.message));
                        $('#modal_cliente').modal('toggle')
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
        } else instance.Toast.operacion_erronea('Rellene correctamente los campos')
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

    // Contactos
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
});