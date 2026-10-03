import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    let form_cliente = document.querySelector("#form_cliente")
    let num_docu = document.querySelector("#num_docu")
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
                                    nuevo_cliente_id.value = data.message.id_usuario;
                                    actualizar_tp_comprobantes(data.message.id_tp_docu);
                                    $("#nuevo_cliente").val(data.message.num_docu).trigger("change");
                                    $('#nuevo_cliente_nombres').val(data.message.nombres_cliente)
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
                })
        } else instance.Toast.operacion_informativa('Rellene correctamente los campos')
    })

    // Consulta RENIEC o SUNAT
    document.querySelector("#button_search").addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        $('#button_loadSearch').focus();
        const reply_val = instance.Validate.validateData([num_docu.value]);

        if (reply_val && (num_docu.value.length === 8 || num_docu.value.length === 11)) {
            const ruta_query = tp_docu.value == 1 ? 'reniec' : 'sunat';
            const formData = new FormData();
            formData.set("docu", num_docu.value);

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

    // Reset formulario al Cerrar modal
    $("#modal_cliente").on("hidden.bs.modal", (e) => {
        $("#modal_cliente").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_pasajero.value = ""
        num_docu.setAttribute("maxlength", 8)
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

    function actualizar_tp_comprobantes(tp_documento) {
        var $select = $('#tp_comprobante');

        if (tp_documento == 6) {
            $select.select2('destroy');
            $select.empty();

            $select.append('<option value="1">FACTURA ELECTRÓNICA</option>');
            $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');

        } else if (tp_documento == 1) {
            $select.select2('destroy');
            $select.empty();

            $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');
        }

        instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
        $select.trigger('change');

        // Si se desea seleccionar automáticamente la primera opción y disparar el change:
        // $select.val($select.find('option:first').val()).trigger('change');
    }

});