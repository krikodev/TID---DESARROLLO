import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    // Variables Form
    let form_pasajero = document.querySelector("#form_pasajero")
    let form_edad = document.querySelector("#form_edad")
    let id_usuario_edad = document.querySelector("#id_usuario_edad")
    let btn_guardar_edad = document.querySelector("#btn_guardar_edad")
    let load_guardar_edad = document.querySelector("#load_guardar_edad")
    let edad_usuario = document.querySelector("#edad_usuario")
    let id_pasajero = document.querySelector("#id_pasajero")
    let tp_docu = document.querySelector("#tp_docu")
    let num_docu = document.querySelector("#num_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let celular = document.querySelector("#celular")
    let email = document.querySelector("#email")
    let estado_sunat = document.querySelector("#estado_sunat")
    let condicion_sunat = document.querySelector("#condicion_sunat")
    let estado = document.querySelector("#estado")
    let fecha_nacimiento = document.querySelector("#fecha_nacimiento")
    let nacionalidad = document.querySelector("#nacionalidad")
    let operacion = document.querySelector('#operacion')
    let table_contacto = document.querySelector("#table_contacto")
    let cantidad_entrada = 2;

    let button_save = document.querySelector("#button_save_p")
    let button_cancel = document.querySelector("#button_cancel_p")
    let button_loadSave = document.querySelector("#button_loadSave_p")
    let button_loadSearch = document.querySelector("#button_loadSearch")

    let tp_comprobante = document.getElementById("tp_comprobante");
    let cliente_id = document.getElementById("cliente_id");
    let tipo_documento_client = document.getElementById("tipo_documento_client");

    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")

    instance.Validate.allowInputNum(["#celular"])
    instance.Validate.allowInputStringSpace(["#nombres", "#apellidos"])

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
        //$("#terminal").val("").trigger("change")
        num_docu.setAttribute("minlength", 8)
        num_docu.setAttribute("maxlength", 8)
        button_search.classList.remove("d-none")
        $('#table_contacto tbody tr').remove();
        nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
        nombres.parentElement.classList.remove("d-none")
        apellidos.parentElement.classList.remove("d-none")
        fecha_nacimiento.parentElement.classList.remove("d-none")
        genero.parentElement.classList.remove("d-none")
        estado_sunat.parentElement.classList.add("d-none")
        condicion_sunat.parentElement.classList.add("d-none");
        $("#tp_docu").val("1").trigger("change");
        selectNacionalidad.setValue("PERÚ");
    })

    //Cambio de fecha en el input o tipo de entrada
    $(document).on("click", ".cambiar_entrada", function (e) {
        e.preventDefault();
        if ($(this).text() === " [Edad]") {
            cantidad_entrada = 2;
            $(this).text(" [Anio]");
            $("#fecha_nacimiento")
                .attr("maxlength", "3")
                .attr("type", "text")
                .val("");
        } else {
            cantidad_entrada = 4;
            $(this).text(" [Edad]");
            $("#fecha_nacimiento")
                .attr("type", "text")
                .attr("maxlength", "4")
                .val("");
        }
    });

    function resetToInitialState() {
        $(".cambiar_entrada").text(" [Anio]");
        cantidad_entrada = 2;
        $("#fecha_nacimiento")
            .attr("maxlength", "3")
            .attr("type", "text")
            .val("");
    }

    //Focus de selects
    $("#genero").on("change", function (e) {
        $("#fecha_nacimiento").focus();
    });

    $('#fecha_nacimiento').on('keydown', function (e) {
        if (e.key === 'Enter') {
            button_save.focus();
        }
    });

    // Envio de regitro
    form_pasajero.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form_pasajero)
        let data = [
            formData.get("num_docu"), formData.get("nombres"),
            formData.get("ubigeo"), formData.get("direccion"),
            formData.get("terminal"), formData.get("estado"),
            formData.get("nacionalidad")
        ]
        if (tp_docu.value == 1) {
            var tipoEntrada = $("#fecha_nacimiento").attr("type");
            if (tipoEntrada === "text") {
                var valor = $("#fecha_nacimiento").val();
                if (cantidad_entrada == 4) {
                    if (/^\d{4}$/.test(valor)) {
                        valor = valor + "-01-01";
                    } else {
                        instance.Toast.operacion_erronea("El año ingresado no es valido")
                        return false;
                    }
                } else if (cantidad_entrada == 2) {
                    if (valor != '') {
                        var anio_actual = new Date().getFullYear();
                        var anio = anio_actual - parseInt(valor, 10);
                        valor = anio + "-01-01";
                    } else {
                        instance.Toast.operacion_erronea("Ingrese una edad valida")
                        return false;
                    }
                }
                data.push(valor);
                formData.set("fecha_nacimiento", valor);
            }
            data.push(formData.get("apellidos"))
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
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + "pasajero/crud_register", {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status)
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
                                    clienteAuto.clearCache();
                                    pasajeroAuto.clearCache();
                                    ninoAuto.clearCache();
                                    if (operacion.value == 1) {
                                        if (data.message.id_tp_docu == 1) {
                                            tipo_documento_client.value = data.message.id_tp_docu;
                                            $('#cliente_id').val(data.message.id_usuario).trigger('change');
                                            $('#pasajero_id').val(data.message.id_usuario).trigger('change');
                                            $("#cliente").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                                            $("#pasajero").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                                            window.selectizeMedioPago.open();
                                            $('#medio_pago').focus();
                                        } else if (data.message.id_tp_docu == 6) {
                                            tipo_documento_client.value = data.message.id_tp_docu;
                                            $('#cliente_id').val(data.message.id_usuario).trigger('change');
                                            $("#cliente").val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                            $('#pasajero').focus();
                                        } else if (data.message.id_tp_docu == 4 || data.message.id_tp_docu == 7 || data.message.id_tp_docu == 0) {
                                            tipo_documento_client.value = data.message.id_tp_docu;
                                            $('#cliente_id').val(data.message.id_usuario).trigger('change');
                                            $('#pasajero_id').val(data.message.id_usuario).trigger('change');
                                            $("#cliente").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                                            $("#pasajero").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                                            window.selectizeMedioPago.open();
                                            $('#medio_pago').focus();
                                        }
                                    } else if (operacion.value == 2) {
                                        $('#pasajero_id').val(data.message.id_usuario).trigger('change');
                                        $("#pasajero").val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                        window.selectizeMedioPago.open();
                                        $('#medio_pago').focus();
                                    } else if (operacion.value == 3) {
                                        nino_id.value = data.message.id_usuario;
                                        $("#nino").val(data.message.num_docu + ' - ' + data.message.nombres_cliente)
                                        $('#desc_motivo').focus();
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
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                    resetToInitialState();
                })
        } else instance.Toast.operacion_erronea('Rellene correctamente los campos')
    });

    // ============================================================
    // AUTOCOMPLETE: llama directamente a las funciones, sin simular eventos
    // ============================================================

    const clienteAuto = new instance.NoxAutoComplete({
        input: "#cliente",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarCliente(item.num_docu);
        }
    });

    const pasajeroAuto = new instance.NoxAutoComplete({
        input: "#pasajero",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarPasajero(item.num_docu);
        }
    });

    const ninoAuto = new instance.NoxAutoComplete({
        input: "#nino",
        url: instance._URL_ + "pasaje/buscar_pasajeros",
        valueField: "id_usuario",
        textField: "nombres_cliente",
        onSelect: function (item) {
            buscarNino(item.num_docu);
        }
    });

    // Nueva funcion
    function abrirModal(tituloText, operacionValue, dni = null, t_d = null) {
        let operacion = document.querySelector('#operacion');
        let titulo = document.getElementById('titulo');
        titulo.textContent = tituloText;
        operacion.value = operacionValue;

        if (dni && t_d) {
            $("#tp_docu").val(t_d).trigger("change");
            $("#num_docu").val(dni);
            $('#button_search').click();
        }
        $('#num_docu').focus();
        $("#modal_pasajero").modal("show");
    }

    function limpiar_datos_posponer() {
        aplicar_pospuesto.removeAttribute("disabled")
        aplicar_pospuesto.value = 0;
        pospuesto_id.value = '';
        pospuesto_id_venta.value = '';
        pospuesto_saldo.value = '';
        precio_original.value = '';
        aplicar_pospuesto.value = 0;
        $("#div_aplicar_pospuesto").addClass("d-none");
        $("#div_devolucion_restante").addClass('d-none');
    }

    // ============================================================
    // FUNCIONES REUSABLES (llamadas tanto por Enter como por el autocomplete)
    // ============================================================

    function buscarCliente(numDocu) {

        limpiar_datos_posponer();

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
                    if (tp_comprobante.value == '1' && data.message.id_tp_docu != '6') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Antención',
                            text: 'No puede emitir una FACTURA, ingrese un RUC'
                        }).then(() => {
                            cliente_id.value = '';
                            tipo_documento_client.value = '';
                            $('#cliente').val('');
                            $('#cliente').focus();
                            return false;
                        });
                    }
                    if (data.message.num_docu && tp_comprobante.value == 3) {
                        $('#cliente_id').val(data.message.id_usuario).trigger('change');
                        tipo_documento_client.value = data.message.id_tp_docu;
                        $('#cliente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);

                        if (data.message.id_tp_docu == 6) {
                            $('#pasajero').focus();
                        } else {
                            $('#pasajero_id').val(data.message.id_usuario).trigger('change');
                            $("#pasajero").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                            if (data.mensaje_pospuestos.success) {
                                $('#precio_venta').focus().select();
                            } else {
                                selectizeMedioPago.open();
                                $('#medio_pago').focus();
                            }
                        }
                    } else if (data.message.num_docu && tp_comprobante.value == 1) {
                        $('#cliente_id').val(data.message.id_usuario).trigger('change');
                        tipo_documento_client.value = data.message.id_tp_docu;
                        $('#cliente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                        $('#pasajero').focus();
                    } else if (data.message.num_docu && tp_comprobante.value == 2) {
                        $('#cliente_id').val(data.message.id_usuario).trigger('change');
                        tipo_documento_client.value = data.message.id_tp_docu;
                        $('#cliente').val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                        if (data.mensaje_pospuestos.success) {
                            $('#precio_venta').focus().select();
                        } else {
                            selectizeMedioPago.open();
                            $('#medio_pago').focus();
                        }
                    }

                    if (data.message.id_tp_docu == 1) {
                        if (data.message.fecha_nacimiento == null || data.message.fecha_nacimiento === '0000-00-00') {
                            const modalElement = document.getElementById('modal_edad');
                            const modal_edad = new bootstrap.Modal(document.getElementById('modal_edad'), {
                                backdrop: 'static',
                                keyboard: false
                            });
                            $("#id_usuario_edad").val(data.message.id_usuario);
                            modalElement.addEventListener('shown.bs.modal', () => {
                                document.getElementById('edad_usuario').focus();
                            }, { once: true });
                            modal_edad.show();
                        }
                    }

                    if (data.mensaje_pospuestos.success) {
                        const pospuesto = data.mensaje_pospuestos;

                        $('#pospuesto_id').val(pospuesto.id);
                        $('#pospuesto_id_venta').val(pospuesto.id_venta);
                        $('#pospuesto_saldo').val(pospuesto.saldo);

                        $('#section_pospuesto').removeClass('d-none');
                        $('#label_saldo_favor').text('Saldo a favor: S/ ' + parseFloat(pospuesto.saldo).toFixed(2));
                        $("#div_aplicar_pospuesto").removeClass("d-none");

                        Swal.fire({
                            icon: "warning",
                            title: "Pasaje Pospuesto",
                            text: data.mensaje_pospuestos.mensaje,
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#f39c12"
                        });
                    }

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

    function buscarPasajero(numDocu) {

        buscar_pasajero.classList.add("d-none");
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
                    $('#pasajero_id').val(data.message.id_usuario).trigger('change');
                    $("#pasajero").val(data.message.num_docu + ' - ' + data.message.nombres_cliente).trigger("change");
                    selectizeMedioPago.open();
                    $('#medio_pago').focus();

                    if (data.message.fecha_nacimiento == null || data.message.fecha_nacimiento === '0000-00-00') {
                        const modalElement = document.getElementById('modal_edad');
                        const modal_edad = new bootstrap.Modal(document.getElementById('modal_edad'), {
                            backdrop: 'static',
                            keyboard: false
                        });
                        $("#id_usuario_edad").val(data.message.id_usuario);
                        modalElement.addEventListener('shown.bs.modal', () => {
                            document.getElementById('edad_usuario').focus();
                        }, { once: true });
                        modal_edad.show();
                    }
                } else {
                    if (!matched) {
                        abrirModal("NUEVO PASAJERO", 2, numDocu, 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_pasajero.classList.remove("d-none");
                loader_buscar_p.classList.add("d-none");
            });
    }

    function buscarNino(numDocu) {

        buscar_nino.classList.add("d-none");
        loader_buscar_n.classList.remove("d-none");

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
                    nino_id.value = data.message.id_usuario;
                    $("#nino").val(data.message.num_docu + ' - ' + data.message.nombres_cliente);
                    $('#desc_motivo').focus();
                } else {
                    if (!matched) {
                        abrirModal("NUEVO PASAJERO", 3, numDocu, 1);
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                buscar_nino.classList.remove("d-none");
                loader_buscar_n.classList.add("d-none");
            });
    }

    // ============================================================
    // HANDLERS DE ENTER (ahora solo validan y delegan)
    // ============================================================

    $('#cliente').on('keydown', function (e) {
        let params = [];

        if (tp_comprobante.value == 1) {
            params = [11];
        } else if (tp_comprobante.value == 3 || tp_comprobante.value == 2) {
            params = [11, 8];
        }

        if (e.key === 'Enter') {
            console.log(clienteAuto.isOpen)
            // Si el autocomplete tiene sugerencias abiertas, dejamos que él
            // maneje el Enter (seleccionar), y no ejecutamos la búsqueda manual.
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

    $('#pasajero').on('keydown', function (e) {

        if (e.key === 'Enter') {

            console.log(pasajeroAuto)
            console.log(pasajeroAuto.isOpen)
            console.log(pasajeroAuto.items.length)

            if (pasajeroAuto.isOpen && pasajeroAuto.items.length > 0) {
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (isNumber && inputValue.length === 8) {
                buscarPasajero(inputValue);
            } else {
                instance.Toast.operacion_erronea("Ingrese un DNI valido con 8 numeros");
            }

            e.preventDefault();
        }
    });

    $('#nino').on('keydown', function (e) {

        if (e.key === 'Enter') {

            if (ninoAuto.isOpen && ninoAuto.items.length > 0) {
                console.log('etntando a la primera condicion')
                return;
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (isNumber && inputValue.length === 8) {
                console.log('pasando directo a la otra parte de  la impelkemnyt')

                buscarNino(inputValue);
            } else {
                instance.Toast.operacion_erronea("Ingrese un DNI valido con 8 numeros");
            }

            e.preventDefault();
        }
    });

    // ============================================================
    // HANDLERS DE CLICK EN "BUSCAR" (misma validación, mismas funciones)
    // ============================================================

    document.querySelector("#buscar_cliente").addEventListener("click", (e) => {
        e.preventDefault();

        let params = [];

        if (tp_comprobante.value == 1) {
            params = [11];
        } else if (tp_comprobante.value == 3 || tp_comprobante.value == 2) {
            params = [11, 8];
        }

        var inputValue = $("#cliente").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (params.includes(inputValue.length) && isNumber) {
            buscarCliente(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_pasajero").addEventListener("click", (e) => {
        e.preventDefault();

        var inputValue = $("#pasajero").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (isNumber && inputValue.length === 8) {
            buscarPasajero(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

    document.querySelector("#buscar_nino").addEventListener("click", (e) => {
        e.preventDefault();

        var inputValue = $("#nino").val().trim();
        var isNumber = /^\d+$/.test(inputValue);

        if (isNumber && inputValue.length === 8) {
            buscarNino(inputValue);
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente.');
        }
    });

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

    // Consulta RENIEC o SUNAT
    document.querySelector("#button_search").addEventListener("click", (e) => {
        let reply_val = instance.Validate.validateData([num_docu.value])
        if (reply_val && (num_docu.value.length == 8 || num_docu.value.length == 11)) {
            let ruta_query = tp_docu.value == 1 ? 'reniec' : 'sunat'
            let formData = new FormData();
            formData.set("docu", num_docu.value)
            button_search.classList.add("d-none")
            button_loadSearch.classList.remove("d-none")
            fetch(instance._URL_ + 'api/' + ruta_query, {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        if (tp_docu.value == 6) {
                            nombres.value = data.data.nombre_o_razon_social
                            estado_sunat.value = data.data.estado
                            condicion_sunat.value = data.data.condicion
                            if (data.data.departamento !== "" || data.data.provincia !== "" || data.data.distrito !== "") {
                                $("#ubigeo").val(data.data.ubigeo[2]).trigger("change")
                                direccion.value = data.data.direccion
                                $('#button_save_p').focus();
                            }
                        } else {
                            nombres.value = data.data.nombres
                            apellidos.value = `${data.data.apellido_paterno} ${data.data.apellido_materno}`
                            $("#fecha_nacimiento").focus();
                        }
                        //$("#ubigeo").val(data.data.ubigeo_sunat).trigger("change")
                        instance.Toast.operacion_exitosa('Datos encontrados')
                    } else {
                        instance.Toast.operacion_erronea('Datos no hallados :c')
                        $("#nombres").focus();
                    }
                })
                .catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_search.classList.remove("d-none")
                    button_loadSearch.classList.add("d-none")
                })
        } else instance.Toast.operacion_erronea('Rellene el campo correctamente.')
    })

    $("#tp_docu").on("change", function (e) {
        num_docu.value = "";
        nombres.value = "";
        apellidos.value = "";
        estado_sunat.value = "";
        condicion_sunat.value = "";
        direccion.value = "S/D";

        if ($(this).val() == 6) {
            nombres.closest("div").querySelector("label").innerHTML = `Razon social<span class="requiredField">*</span>`;
            //nombres.setAttribute("readonly", true);
            apellidos.parentElement.classList.add("d-none");
            //apellidos.setAttribute("readonly", false);
            num_docu.setAttribute("minlength", 11);
            num_docu.setAttribute("maxlength", 11);
            estado_sunat.parentElement.classList.remove("d-none");
            condicion_sunat.parentElement.classList.remove("d-none");
            fecha_nacimiento.parentElement.classList.add("d-none");
            genero.parentElement.classList.add("d-none");
            button_search.disabled = false;
            num_docu.setAttribute("inputmode", "numeric");
            num_docu.oninput = function () {
                this.value = this.value.replace(/\D/g, "");
            };
        } else if ($(this).val() == 4) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`;
            nombres.removeAttribute("readonly");
            apellidos.parentElement.classList.remove("d-none");
            fecha_nacimiento.parentElement.classList.remove("d-none");
            genero.parentElement.classList.remove("d-none");
            apellidos.removeAttribute("readonly");
            num_docu.setAttribute("minlength", 12);
            num_docu.setAttribute("maxlength", 12);
            estado_sunat.parentElement.classList.add("d-none");
            condicion_sunat.parentElement.classList.add("d-none");
            button_search.disabled = true;
            num_docu.removeAttribute("inputmode");
            num_docu.oninput = null;
        } else if ($(this).val() == 7 || $(this).val() == 0) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`;
            nombres.removeAttribute("readonly");
            apellidos.parentElement.classList.remove("d-none");
            fecha_nacimiento.parentElement.classList.remove("d-none");
            genero.parentElement.classList.remove("d-none");
            apellidos.removeAttribute("readonly");
            num_docu.setAttribute("minlength", 12);
            num_docu.setAttribute("maxlength", 12);
            estado_sunat.parentElement.classList.add("d-none");
            condicion_sunat.parentElement.classList.add("d-none");
            button_search.disabled = true;
            num_docu.removeAttribute("inputmode");
            num_docu.oninput = null;
        } else {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`;
            //nombres.setAttribute("readonly", true);
            apellidos.parentElement.classList.remove("d-none");
            fecha_nacimiento.parentElement.classList.remove("d-none");
            genero.parentElement.classList.remove("d-none");
            //apellidos.setAttribute("readonly", true);
            num_docu.setAttribute("minlength", 8);
            num_docu.setAttribute("maxlength", 8);
            estado_sunat.parentElement.classList.add("d-none");
            condicion_sunat.parentElement.classList.add("d-none");
            button_search.disabled = false;
            num_docu.setAttribute("inputmode", "numeric");
            num_docu.oninput = function () {
                this.value = this.value.replace(/\D/g, "");
            };
        }
    });

    $("#tp_docu").trigger("change");
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

    const get_clientes = (targets = []) => {
        fetch(instance._URL_ + "pasaje/get_clientes", { method: "GET" })
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

    form_edad.addEventListener("submit", (e) => {
        e.preventDefault();

        let formData = new FormData(form_edad);

        const edad = parseInt(edad_usuario.value, 10);

        if (isNaN(edad) || edad < 1 || edad > 120) {
            instance.Toast.operacion_informativa("Ingrese una edad válida (1 - 120 años)");
            return;
        }

        let data = [
            formData.get("edad_usuario"),
            formData.get("id_usuario_edad")
        ];

        var anio_actual = new Date().getFullYear();
        var anio = anio_actual - edad;
        var edad_us = anio + "-01-01";

        formData.set("fecha_nacimiento", edad_us);

        if (instance.Validate.validateData(data)) {

            btn_guardar_edad.classList.add("d-none");
            load_guardar_edad.classList.remove("d-none");

            fetch(instance._URL_ + "pasajero/registrar_edad_usuario", {
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

                        const modal_edad = bootstrap.Modal.getInstance(document.getElementById('modal_edad'));
                        modal_edad.hide();
                        window.selectizeMedioPago.open();
                    } else {
                        instance.Toast.operacion_erronea(data.message);
                    }
                })
                .catch(error => {
                    instance.Toast.operacion_erronea(error.message);
                })
                .finally(() => {
                    btn_guardar_edad.classList.remove("d-none");
                    load_guardar_edad.classList.add("d-none");
                });

        } else {
            instance.Toast.operacion_informativa('Rellene correctamente los campos');
        }
    });

    edad_usuario.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $("#modal_edad").on("hidden.bs.modal", () => {
        document.querySelector("#modal_edad form").reset();

        let forms = document.querySelectorAll("#modal_edad .needs-validation");
        forms.forEach(form => {
            form.classList.remove("was-validated");
        });

        id_usuario_edad.value = "";
    });
})