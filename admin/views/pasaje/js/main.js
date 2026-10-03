
import * as instance from "../../../public/js/instance.js"
//import connetor_plugin from '../js/impresion.js'; 
document.getElementById("link_pasaje").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    window.addEventListener("load", function () {
        let w = window.innerWidth;
        if (w >= 1200) {
            document.getElementById("sidepanel-toggler").click();
        }
    });

    let selec_progra = 0;

    // Validación de rango de fechas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    function validarRangoFechas() {
        if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
            if (fechaInicio.value > fechaFin.value) {
                instance.Toast.operacion_erronea('La fecha de inicio no puede ser mayor a la fecha de fin');
                fechaFin.value = fechaInicio.value;
                return false;
            }
        }
        return true;
    }

    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', validarRangoFechas);
        fechaFin.addEventListener('change', validarRangoFechas);
    }

    const table_comprobante = $('#table_comprobante').DataTable({
        "ajax": {
            'url': instance._URL_ + 'pasaje/get_dataTable',
            'method': 'POST',
            'data': function (d) {
                d.tp_comprobante = '1, 3';
                d.id_programacion = selec_progra || 0;
                return d;
            }
        },
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "asiento",
                render: (data, type, row) => {
                    return `<label class="" style="color:black; font-weight:700;">${row.asiento}</label>`
                }
            },
            {
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "programacion_hora_salida",
                render: (data, type, row) => {
                    return `${row.programacion_fecha_salida.split("-").reverse().join("-")}<br>${row.programacion_hora_salida}`
                }
            },

            {
                "data": "id_cliente",
                render: (data, type, row) => {
                    return `${row.cliente_nombres} ${row.cliente_apellidos}<br>${row.cliente_num_docu}`
                }
            },
            {
                "data": "op_gravada",
            },
            {
                "data": "op_exonerada",
            },
            {
                "data": "op_inafecta",
            },
            {
                "data": "op_igv",
            },
            {
                "data": "total",
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ASIENTO[row.estado.toUpperCase()]}">${row.estado}</span>`
                }
            },
            {
                "data": "envio_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.envio_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.envio_sunat]}</span>`

                }
            },
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let plazo_vencido = 1;
                    let fecha_actual = new Date();
                    let fecha_e = new Date(row.fecha_emision)
                    fecha_e.setDate(fecha_e.getDate() + 7);
                    if (fecha_actual > fecha_e) {
                        plazo_vencido = 0
                    }
                    let html_anular = `<a class="btn btnAnular mb-1" title="Anular"><i class="fa-solid fa-trash"></i></a>`;
                    let html_xml = row.file_xml ? `<a class="btn btnXML text-white mb-1" href="${row.file_xml}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.XML}" title="XML"><i class="fa-light fa-file-xmark"></i></a>` : '';
                    let html_cdr = row.envio_sunat == 1 ? `<a class="btn btnCDR text-white mb-1" href="${row.file_cdr}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.CDR}" title="CDR"><i class="fa-light fa-file-zipper"></i></a>` : '';
                    let html_reenviar = row.envio_sunat == 0 ? `<a class="btn btnReenviar text-white mb-1" id="reenviarVenta" data-id="${row.id_venta}" title="Reenviar" style="background-color:red;"><i class="fa-light fa-arrow-circle-up"></i></a>` : '';

                    let newRow = `
                        <a data-link="${instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.COMPROBANTE + row.id_venta}" target="_blank" class="btn btnColorViolet mb-1 btn-print" title="Comprobante"><i class="fa-light fa-print"></i></a>
                        ${row.estado != "ANULADO" && row.envio_sunat && instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE == 1 ? html_anular : ''}
                        ${html_xml}
                        ${html_cdr}
                        ${plazo_vencido ? html_reenviar : ''}
                    `;
                    return newRow;
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    const table_notaVenta = $('#table_notaVenta').DataTable({
        "ajax": {
            'url': instance._URL_ + 'pasaje/get_dataTable',
            'method': 'POST',
            'data': function (d) {
                d.tp_comprobante = '2';
                d.id_programacion = selec_progra || 0;
                return d;
            }
        },
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "asiento",
                render: (data, type, row) => {
                    return `<label class="" style="color:black; font-weight:700; font-family: 16px;">${row.asiento}</label>`
                }
            },
            {
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "programacion_hora_salida",
                render: (data, type, row) => {
                    return `${row.programacion_fecha_salida.split("-").reverse().join("-")}<br>${row.programacion_hora_salida}`
                }
            },
            {
                "data": "id_cliente",
                render: (data, type, row) => {
                    return `${row.cliente_nombres} ${row.cliente_apellidos}<br>${row.cliente_num_docu}`
                }
            },
            {
                "data": "total",
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ASIENTO[row.estado.toUpperCase()]}">${row.estado}</span>`
                }
            },
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let html_anular = `<a class="btn btnAnular mb-1" title="Anular"><i class="fa-solid fa-trash"></i></a>`
                    let newRow = `
                        <a data-link="${instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.NOTA_VENTA + row.id_venta}" target="_blank" class="btn btnColorViolet mb-1 btn-print" title="Comprobante"><i class="fa-light fa-print"></i></a>
                        ${row.estado != "ANULADO" && instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE == 1 ? html_anular : ''}
                    `
                    return newRow
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    // Variables Form
    let form_filtro = document.querySelector("#form_filtro")
    let form_asien = document.querySelector("#form_asien")
    let hora_salida = document.querySelector("#hora_salida")
    let fecha_salida = document.querySelector("#fecha_salida")
    let table_programacion = document.querySelector("#table_programacion")
    let button_searchFilter = document.querySelector("#button_searchFilter")
    let button_search = document.querySelector("#button_search")
    let button_loadSearchFilter = document.querySelector("#button_loadSearchFilter")

    const superParentVehiculo = document.querySelector("#superParentVehiculo")
    const parent_vehiculo = document.querySelector(".parent_vehiculo")
    const piso = document.querySelector("#piso")
    // Elementos del DOM para la tabla de vendedores
    const resumenVendedoresContainer = document.querySelector("#resumen_vendedores_container");
    const cuerpoTablaVendedores = document.querySelector("#cuerpo_tabla_vendedores");

    // Elementos para totales en el footer
    const totalVendidos = document.querySelector("#total_vendidos");
    const totalReservados = document.querySelector("#total_reservados");
    const totalPospuestos = document.querySelector("#total_pospuestos");
    const totalNotaVenta = document.querySelector("#total_nota_venta");
    const totalBoleta = document.querySelector("#total_boleta");
    const totalFactura = document.querySelector("#total_factura");
    const totalMonto = document.querySelector("#total_monto");

    // Elementos para la tabla de destinos (agregar después de las variables de vendedores)
    const tabVendedoresBtn = document.getElementById('tab_vendedores_btn');
    const tabDestinosBtn = document.getElementById('tab_destinos_btn');
    const resumenDestinosContainer = document.getElementById('resumen_destinos_container');
    const cuerpoTablaDestinos = document.getElementById('cuerpo_tabla_destinos');

    // Elementos para totales de destinos

    const destinosTotalGeneral = document.getElementById('destinos_total_general');

    // form venta
    let id_venta_postponer = document.querySelector("#id_venta_postponer")
    let id_ventaPasaje = document.querySelector("#id_ventaPasaje")
    let id_asiento_selected = document.querySelector("#id_asiento_selected")
    let form_ventaPasaje = document.querySelector("#form_ventaPasaje")
    let tp_comprobante = document.querySelector("#tp_comprobante")
    let num_asiento = document.querySelector("#num_asiento")
    let num_piso_venta = document.querySelector("#num_piso_venta")
    let estado_venta = document.querySelector("#estado_venta")
    let serie_venta = document.querySelector("#serie_venta")
    let precio_venta = document.querySelector("#precio_venta")
    let monto_descuento = document.querySelector("#monto_descuento")
    let datos_venta = document.querySelector("#datos_venta")
    let valor_op = "";
    let consultar = 1;
    let cliente = document.querySelector("#cliente")
    let cliente_Parent = document.querySelector("#div_parentCliente")
    let pasajero_Parent = document.querySelector("#div_parentPasajero")
    let nino_Parent = document.querySelector("#div_parentNino")
    let c_nino = document.querySelector("#c_nino")
    let pasajero = document.querySelector("#pasajero")
    let nino = document.querySelector("#nino")
    let parent_pagos = document.querySelector("#parent_pagos")
    let medio_pago = document.querySelector("#medio_pago")
    let destino = document.querySelector("#destino")
    let referencia = document.querySelector("#referencia")
    let cod_operacion = document.querySelector("#cod_operacion")
    let parentCodOperacion = document.querySelector("#parentCodOperacion")
    let email_cliente = document.querySelector("#email_cliente")
    let celular_cliente = document.querySelector("#celular_cliente")
    let buscar_cliente = document.querySelector("#buscar_cliente")
    let loader_buscar = document.querySelector("#loader_buscar")
    let buscar_pasajero = document.querySelector("#buscar_pasajero")
    let buscar_nino = document.querySelector("#buscar_nino")
    let loader_buscar_p = document.querySelector("#loader_buscar_p")
    let loader_buscar_n = document.querySelector("#loader_buscar_n")
    let asientos_reserva = document.querySelector("#asientos_reserva")

    let button_save = document.querySelector("#button_save")
    let button_loadSave = document.querySelector("#button_loadSave")
    let button_anular = document.querySelector("#button_anular")
    let button_cambiarA = document.querySelector("#button_cambiarA")
    let button_aplicarPostponer = document.querySelector("#button_aplicarPostponer")

    let button_cancelarPostponer = document.querySelector("#button_cancelarPostponer")
    let button_comprobante = document.querySelector("#button_comprobante")

    let terminal_origen = document.querySelector("#terminal_origen")
    let terminal_destino = document.querySelector("#terminal_destino")

    let btn_cambiar_carro = document.querySelector("#btn_cambiar_carro")
    let btn_manifiesto = document.querySelector("#btn_manifiesto")
    let btn_manifiesto_sunat = document.getElementById("btn_manifiesto_sunat")
    let permiso_manifiesto_sunat = document.getElementById("permiso_manifiesto_sunat")
    let btn_control_pasajero = document.querySelector("#btn_control_pasajero")
    let btn_liquidacionSeguimiento = document.querySelector("#btn_liquidacionSeguimiento")
    let btn_liquidacionSeguimientoUser = document.querySelector("#btn_liquidacionSeguimientoUser")
    let btn_liquidacion = document.querySelector("#btn_liquidacion")
    let btn_liquidacion_usuario = document.querySelector("#btn_liquidacion_usuario")
    let btn_reserva_grupal = document.querySelector("#btn_reserva_grupal")
    let btn_reserva_todos = document.querySelector("#btn_reserva_todos")
    let btn_liberar_reserva = document.querySelector("#btn_liberar_reserva")
    let reserva_grupal = document.querySelector("#reserva_grupal")
    let resumenContainer = document.querySelector("#resumen_container");
    let caja = document.querySelector("#id_caja")
    let ruta_manifiesto = "";
    let ruta_manifiesto_sunat = '';
    let ruta_control_pasajero = "";
    let id_progra = "";
    let id_v = "";
    let progra_id = 0;
    let id_procond = document.querySelector("#id_program")
    let mani = document.querySelector("#manifi")
    let cliente_id = document.querySelector("#cliente_id")
    let pasajero_id = document.querySelector("#pasajero_id")
    let asientos_bloqueados = document.querySelector("#asientos_bloqueados")
    let info_OD = document.querySelector("#info_OD")
    let destino_pasajero = document.querySelector("#destino_pasajero")
    let destino_pasajero_seleccionado = null
    let div_formVentaPasaje = document.querySelector("#div_formVentaPasaje")
    let div_sin_asientos = document.querySelector("#div_sin_asientos")
    let id_tp_usuario_sesion = document.getElementById("id_tp_usuario_sesion")
    let porcentaje_empresa = document.getElementById("porcentaje_empresa");
    let table_egresos_venta = document.getElementById('table_egresos_venta');
    const aplicar_pospuesto = document.getElementById('aplicar_pospuesto');
    const monto_aplicar_input = document.getElementById('monto_aplicar_pospuesto');
    const precio_original_input = document.getElementById('precio_original');
    //Modal pasajero
    let num_docuc = document.querySelector("#num_docu")

    let deleteVentaProceso = 1

    instance.Datatable.inputSearch(table_comprobante, ".inputSearchPasajeComprobantes")
    instance.Datatable.inputSearch(table_notaVenta, ".inputSearchPasajeNotaVenta")
    instance.select.createSelect("#terminal_origen", "#div_parentTerminalOrigen")
    instance.select.createSelect("#terminal_destino", "#div_parentTerminalDestino")
    instance.select.createSelect("#piso", "#div_parentPiso")
    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante")
    let medioSelect = $('#medio_pago').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        }
    });
    window.selectizeMedioPago = medioSelect[0].selectize;
    instance.select.createSelect("#serie_venta", "#div_parentSerie", 'Seleccione')
    instance.Validate.allowInputMoney(["#precio_venta"]);

    // validacion de permiso de cambio de precio venta
    precio_venta.readOnly = Number(instance.CONSTS.PERMISOS.P_CAMBIAR_PRECIO_ASIENTO) !== 1;

    // Focus en el modal
    $('#desc_motivo').on('keydown', function (e) {
        if (e.key === 'Enter') {
            selectizeMedioPago.open();
            $('#medio_pago').focus();
            e.preventDefault();
        }
    });

    // Verificación caja 
    if (caja.value == 0) {
        Swal.fire({
            title: "Ooops..... No tiene una caja abierta",
            text: "Desea abrir una caja?",
            icon: "error",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: "Abrir caja",
            denyButtonText: `No quiero`,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = instance._URL_ + 'caja_chica';
            } else if (result.isDenied) {
                window.location.href = instance._URL_ + 'dashboard';
            }
        });
    }

    // Pasajero
    let id_pasajero = document.querySelector("#id_pasajero");

    (function () {
        document.querySelector(".asiento_normal").style.color = instance.CONSTS.COLORES.TIPO_ASIENTO.NORMAL
        document.querySelector(".asiento_premium").style.color = instance.CONSTS.COLORES.TIPO_ASIENTO.PREMIUM
        document.querySelector(".asiento_reservado").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.RESERVADO
        document.querySelector(".asiento_vendido").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.VENDIDO
        document.querySelector(".asiento_seleccionado").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.SELECCIONADO
        document.querySelector(".asiento_postponer").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.POSTPONER
        document.querySelector(".asiento_proceso_web").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.PROCESO_WEB
        document.querySelector(".asiento_venta_web").style.color = instance.CONSTS.COLORES.ESTADO_ASIENTO.VENTA_WEB
    }())


    $('#precio_venta').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#referencia').focus();
        }
    });

    $('#referencia').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            selectizeMedioPago.open();
            $('#medio_pago').focus();
        }
    });

    $('#cod_operacion').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#cliente').focus();
        }
    });

    $("#modal_pasaje").on("hidden.bs.modal", (e) => {
        id_ventaPasaje.value = ""

        id_asiento_selected.value = ""
        button_save.classList.remove("d-none")
        button_anular.classList.add("d-none")
        button_cambiarA.classList.add("d-none")
        button_cancelarPostponer.classList.add("d-none")
        button_aplicarPostponer.classList.add("d-none")
        button_comprobante.classList.add("d-none")

        $("#num_asiento_venta").text("")
        $("#destino_bajada").text("")
        $("#origen_embarque").text("")
        $("#n_piso_venta").text("")
        $("#venta_tp_comprobante").text("")
        $("#badge_estado").text("")
        $("#venta_precio").text("")
        $("#venta_num_docu").text("")
        $("#venta_nombres_cliente").text("")
        $("#venta_pasajero_num").text("")
        $("#venta_nombres_pasajero").text("")
        $("#venta_nino_num").text("")
        $("#venta_nombres_nino").text("")
        $("#venta_motivo_nino").text("")
        $("#venta_medio_pago").text("")
        $("#venta_email").text("");
        $("#venta_celular").text("");
        $("#venta_medio_pago").text("");
        $("#venta_caja_chica").text("");
        $("#venta_forma_pago").text("");
        $("#venta_cod_operacion").text("");
        $("#div_cupon").addClass("d-none");
        $("#div_egresos").addClass("d-none");
    })

    function limpiar_campos_venta_pasaje() {
        document.getElementById("form_ventaPasaje").reset();
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        forms.forEach(form => form.classList.remove("was-validated"))

        id_ventaPasaje.value = ""
        id_pasajero.value = ""
        consultar = 1;
        monto_descuento = 0.00;

        $("#num_asiento").text("")
        $("#num_piso_venta").text("")

        id_asiento_selected.value = ""
        cliente_id.value = ''
        pasajero_id.value = ''
        nino_id.value = ''
        c_nino.value = 0;
        selectizeMedioPago.setValue('');
        tp_comprobante.parentElement.classList.remove("d-none")
        serie_venta.parentElement.classList.remove("d-none")
        pasajero_Parent.classList.add("d-none")
        nino_Parent.classList.add("d-none")
        parentCodOperacion.classList.remove("d-none")
        $("#estado_venta").val("VENDIDO").trigger("change")
        $("#serie_venta").val("").trigger("change")
        button_save.classList.remove("d-none")
        button_anular.classList.add("d-none")
        button_cambiarA.classList.add("d-none")
        button_cancelarPostponer.classList.add("d-none")
        button_aplicarPostponer.classList.add("d-none")
        button_comprobante.classList.add("d-none")

        tp_comprobante.removeAttribute("disabled")
        serie_venta.removeAttribute("disabled")
        estado_venta.removeAttribute("disabled")
        precio_venta.removeAttribute("readonly")
        c_nino.removeAttribute("disabled")
        cliente.removeAttribute("disabled")
        buscar_cliente.classList.remove("d-none")
        buscar_pasajero.classList.remove("d-none")
        buscar_nino.classList.remove("d-none")
        cliente_Parent.querySelector("label a").classList.remove("d-none")
        pasajero_Parent.querySelector("label a").classList.remove("d-none")
        pasajero.removeAttribute("disabled")
        nino.removeAttribute("disabled")
        cod_operacion.removeAttribute("readonly")
        $('#medio_pago').prop('disabled', false);
        selectizeMedioPago.enable();
        destino.removeAttribute("disabled")
        referencia.removeAttribute("readonly")
        email_cliente.parentElement.classList.add("d-none")
        email_cliente.removeAttribute("disabled")
        celular_cliente.parentElement.classList.add("d-none")
        celular_cliente.removeAttribute("disabled")
        table_egresos_venta.querySelector("tbody").innerHTML = '';
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

    //Resetear  al modal de pasaje
    $("#modal_asien").on("hidden.bs.modal", (e) => {
        $("#modal_asien").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_asien) {
            form_asien.classList.remove("was-validated")
        })
        $("#id_venta_a").val("");
        $("#id_pro_a").val("");
        $("#id_vehi_a").val("");
        $("#asien").val("");
    })

    form_ventaPasaje.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (tp_comprobante.value == '1' && tipo_documento_client.value != '6') {

            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: 'No puede emitir una FACTURA, ingrese un RUC'
            }).then(() => {
                limpiar_cliente();
                setTimeout(() => $('#cliente').focus(), 0);
            });

            return;
        }

        let formData = new FormData(form_ventaPasaje)

        let val_reply = [formData.get("cliente_id"), formData.get("estado_venta"), formData.get("precio_venta")]

        if (estado_venta.value != "RESERVADO") {
            val_reply.push(
                formData.get("tp_comprobante"), formData.get("serie_venta"),
                formData.get("destino")
            )

            if (aplicar_pospuesto.value !== '1') {
                val_reply.push(
                    formData.get("medio_pago")
                )
            }
        }

        if (estado_venta.value == "VENTA_WEB") {
            val_reply.push(
                formData.get("cod_operacion"),
            )
        }

        if (tp_comprobante.value != 2) {
            val_reply.push(formData.get("pasajero_id"))
        }

        if (c_nino.value == 1) {
            val_reply.push(formData.get("nino_id"))
            val_reply.push(formData.get("desc_motivo"))
        }

        if (instance.Validate.validateData(val_reply)) {
            button_save.disabled = true;
            formData.set("id_programacion", document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value)
            formData.set("num_asiento", $("#num_asiento").text().trim())
            formData.set("num_piso", $("#num_piso_venta").text())
            formData.set("origen", terminal_origen.value)
            formData.set("egresos", JSON.stringify(obtenerEgresos()))
            if (!c_nino.checked) {
                formData.set("c_nino", "0");
            }

            button_save.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            await fetch(instance._URL_ + "pasaje/set_ventaPasaje", {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status)
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        limpiar_campos_venta_pasaje();
                        instance.Toast.operacion_exitosa(data.message.message)
                        // clear_vehiculo()
                        // show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value)
                        //     .then(() => {
                        //         // Después de actualizar el vehículo, actualizar los resúmenes

                        //     });
                        const idProgramacion = document.querySelector(".btn_selection_programacion.active")
                            .closest("tr").querySelector(".id_programacion").value;
                        actualizarResumenes(idProgramacion, true);
                        // instance.Datatable.reloadTable(table_comprobante)
                        // instance.Datatable.reloadTable(table_notaVenta)
                        if (data.message.show_link && data.message.estado_sunat == 1) {
                            if (instance._P_IMPRIMIR_ == 1) {
                                imprimir_comprobante(data.message.link_comprobante, data.message.tipo_impresion);
                                instance.Toast.operacion_exitosa(data.message.message)
                            } else {
                                modal_printComprobante('¡Venta completada!', data.message.link_comprobante, data.message.estado_sunat, data.message.message_sunat)
                            }
                        } else if (data.message.show_link && data.message.estado_sunat == 0 && ![1, 3].includes(tp_comprobante.value)) {
                            if (instance._P_IMPRIMIR_ == 1) {
                                imprimir_comprobante(data.message.link_comprobante, data.message.tipo_impresion);
                                instance.Toast.operacion_exitosa(data.message.message)
                            } else {
                                modal_printComprobante('¡Venta completada!', data.message.link_comprobante)
                            }
                        } else {
                            if ([1, 3].includes(tp_comprobante.value)) instance.Toast.operacion_erronea(data.message.message_sunat)
                            else instance.Toast.operacion_exitosa(data.message.message)
                            const links = data.links;

                            if (links && Object.keys(links).length > 0) {

                                const botonesHtml = Object.entries(links).map(([nombre, url]) => `
                            <button class="btn btn-outline-primary btn-sm m-1 btn-imprimir" data-url="${url}" data-nombre="${nombre}">
                               <i class="fas fa-print me-1"></i>${nombre}
                              </button>
                            `).join('');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Venta pospuesta aplicada',
                                    html: `
                                <p class="mb-3">¿Desea imprimir los comprobantes?</p>
                                 <div class="d-flex flex-wrap justify-content-center">
                                 ${botonesHtml}
                                 </div>
                                `,
                                    showConfirmButton: false,
                                    showCloseButton: true,
                                    didOpen: () => {
                                        document.querySelectorAll('.btn-imprimir').forEach(btn => {
                                            btn.addEventListener('click', () => {
                                                imprimir_comprobante(btn.dataset.url);
                                            });
                                        });
                                    }

                                });
                            }
                        }
                        siguienteAsientoTrasVenta();
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                        limpiar_campos_venta_pasaje();
                    }
                })
                .catch(error => {
                    instance.Toast.operacion_erronea(error.message)
                }).finally(() => {
                    button_save.classList.remove("d-none")
                    button_save.disabled = false;
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos')
        }
    })

    let filtrosActivos = null;
    let paginaActual = 1;

    async function buscarProgramaciones(page = 1) {
        if (!filtrosActivos) return;
        clear_vehiculo();
        limpiarAsientosSeleccionados(); 
        const formData = new FormData();
        formData.set('id_terminal_origen', filtrosActivos.origen);
        formData.set('id_terminal_destino', filtrosActivos.destino);
        formData.set('fecha_inicio', filtrosActivos.fechaInicio);
        formData.set('fecha_fin', filtrosActivos.fechaFin);
        formData.set('estado', filtrosActivos.estado);
        formData.set('page', page);

        button_searchFilter.classList.add("d-none");
        button_loadSearchFilter.classList.remove("d-none");

        try {
            const response = await fetch(instance._URL_ + "pasaje/get_programacionesForIdTerminal", {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            table_programacion.querySelector("tbody").innerHTML = "";
            renderPaginacion(data.pagination ? data.pagination : null);

            if (data.success && data.message.length > 0) {
                data.message.forEach((e) => {
                    table_programacion.querySelector("tbody").insertAdjacentHTML('beforeend', `
                    <tr>
                        <input type="hidden" class="id_programacion"       value="${e.id_programacion}">
                        <input type="hidden" class="id_terminal_destino"   value="${e.id_terminal_destino}">
                        <input type="hidden" class="id_vehiculo"           value="${e.id_vehiculo}">
                        <input type="hidden" class="precio_premium"        value="${e.precio_primer_piso}">
                        <input type="hidden" class="precio_normal"         value="${e.precio_segundo_piso}">
                        <input type="hidden" class="precio_minimo"         value="${e.precio_minimo}">
                        <input type="hidden" class="terminal_origen_activo" value="${e.terminal_origen_nombre}">
                        <input type="hidden" class="terminal_destino_activo" value="${e.terminal_destino_nombre}">
                        <input type="hidden" class="estado_programacion" value="${e.estado}">
                        <td><button type="button" class="btn bg-success text-white btn_selection_programacion"><i class="bi bi-check-lg"></i></button></td>
                        <td><label style="color:red;font-weight:700;">${e.terminal_destino_nombre}</label></td>
                        <td><label style="color:black;font-weight:600;">${e.vehiculo_placa}</label></td>
                        <td><label style="color:black;font-weight:600;">${e.fecha_salida}</label></td>
                        <td><label style="color:black;font-weight:600;">${e.hora_salida}</label></td>
                    </tr>
                `);
                });
            } else {
                Swal.fire({
                    title: 'Atención',
                    text: 'No se han encontrado programaciones para el rango de fechas seleccionado.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar',
                    background: '#fff',
                    color: '#212529',
                    confirmButtonColor: '#1c356b',
                });
                ocultarBotones();
            }
        } catch (error) {
            instance.Toast.operacion_erronea(error.message);
        } finally {
            button_searchFilter.classList.remove("d-none");
            button_loadSearchFilter.classList.add("d-none");
        }
    }

    function renderPaginacion(pagination) {
        const container = document.getElementById('paginacion_programacion');
        if (!container) return;
        container.innerHTML = "";

        if (!pagination || pagination.pages <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm mb-0 justify-content-center mt-2';

        // Anterior
        ul.insertAdjacentHTML('beforeend', `
        <li class="page-item ${pagination.page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.page - 1}">‹</a>
        </li>
        `);

        // Números
        for (let i = 1; i <= pagination.pages; i++) {
            ul.insertAdjacentHTML('beforeend', `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
        }

        ul.insertAdjacentHTML('beforeend', `
        <li class="page-item ${pagination.page === pagination.pages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.page + 1}">›</a>
        </li>
        `);

        ul.addEventListener('click', (e) => {
            e.preventDefault();
            const link = e.target.closest('[data-page]');
            if (!link) return;
            const p = parseInt(link.dataset.page);
            if (p < 1 || p > pagination.pages) return;
            paginaActual = p;
            buscarProgramaciones(paginaActual);
        });

        container.appendChild(ul);
    }

    window.selectNacionalidad = new TomSelect("#nacionalidad", {
        valueField: "nombre_pais",
        labelField: "nombre_pais",
        searchField: "nombre_pais",
        maxOptions: 5,
        plugins: ['clear_button'],
        preload: 'focus',

        shouldLoad: function (query) {
            return query.length >= 2;
        },

        load: function (query, callback) {
            fetch(instance._URL_ + "pasaje/buscar_nacionalidad?q=" + encodeURIComponent(query))
                .then(res => res.json())
                .then(json => callback(json))
                .catch(() => callback());
        },

        onInitialize: function () {
            this.addOption({
                cod_pais: "174",
                nombre_pais: "PERÚ"
            });

            this.setValue("PERÚ");
        }
    });

    form_filtro.addEventListener("submit", async (e) => {
        e.preventDefault();

        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const estado_p = document.getElementById('estado_programacion');

        if (fechaInicio.value > fechaFin.value) {
            instance.Toast.operacion_erronea('La fecha de inicio no puede ser mayor a la fecha de fin');
            return;
        }

        superParentVehiculo.querySelector("div").innerHTML = "";
        reserva_grupal.classList.add("d-none");
        if (resumenVendedoresContainer) resumenVendedoresContainer.classList.add("d-none");

        if (!terminal_destino.value) return;

        filtrosActivos = {
            origen: terminal_origen.value,
            destino: terminal_destino.value,
            fechaInicio: fechaInicio.value,
            fechaFin: fechaFin.value,
            estado: estado_p.value,
        };
        div_formVentaPasaje.classList.add("d-none");
        div_sin_asientos.classList.remove("d-none");
        clear_vehiculo();
        limpiarAsientosSeleccionados(); 
        ocultarBotones();
        paginaActual = 1;
        await buscarProgramaciones(1);
    });

    function ocultarBotones() {
        btn_cambiar_carro.classList.add("d-none");
        btn_manifiesto.classList.add("d-none");
        btn_manifiesto_sunat.classList.add("d-none");
        btn_control_pasajero.classList.add("d-none");
        btn_liquidacion.classList.add("d-none");
        btn_liquidacion_usuario.classList.add("d-none");
        btn_liquidacionSeguimiento.classList.add("d-none");
        btn_liquidacionSeguimientoUser.classList.add("d-none");
        reserva_grupal.classList.add("d-none");
    }

    form_filtro.addEventListener("reset", (e) => {
        table_programacion.querySelector("tbody").innerHTML = ""
        superParentVehiculo.querySelector("div").innerHTML = ""
        btn_cambiar_carro.classList.add("d-none")
        btn_manifiesto.classList.add("d-none");
        btn_manifiesto_sunat.classList.add("d-none");
        btn_control_pasajero.classList.add("d-none")
        btn_liquidacion_usuario.classList.add("d-none")
        btn_liquidacion.classList.add("d-none")
        btn_liquidacionSeguimiento.classList.add("d-none")
        reserva_grupal.classList.add("d-none")

        if (resumenContainer) {
            resumenContainer.classList.add("d-none")
        }
        if (resumenVendedoresContainer) {
            resumenVendedoresContainer.classList.add("d-none")
        }
        if (resumenDestinosContainer) {
            resumenDestinosContainer.classList.add("d-none")
        }

        btn_liquidacionSeguimientoUser.classList.add("d-none")
        $("#terminal_origen").val("").trigger("change")
        $("#terminal_destino").val("").trigger("change")
        terminal_destino.innerHTML = ""
    })

    $("#table_programacion tbody").on("click", ".btn_selection_programacion", function (e) {
        let tr = this.closest("tr")
        let id_vehiculo = tr.querySelector(".id_vehiculo")
        let id_programacion = tr.querySelector(".id_programacion")
        let nombre_p_origen = tr.querySelector(".terminal_origen_activo")
        let nombre_p_destino = tr.querySelector(".terminal_destino_activo")
        let estado_pro = tr.querySelector(".estado_programacion")
        selec_progra = id_programacion.value;
        $(".btn_selection_programacion").removeClass("bg-info")
        $(".btn_selection_programacion").addClass("bg-success")
        if (this.classList.contains("active")) {
            $(".btn_selection_programacion").removeClass("active")
            this.classList.remove("active")
            this.classList.replace("bg-info", "bg-success")
            clear_vehiculo();
            limpiarAsientosSeleccionados();
            btn_cambiar_carro.classList.add("d-none")
            btn_manifiesto.classList.add("d-none");
            btn_manifiesto_sunat.classList.add("d-none");
            btn_control_pasajero.classList.add("d-none")
            btn_liquidacion_usuario.classList.add("d-none")
            btn_liquidacion.classList.add("d-none")
            btn_liquidacionSeguimiento.classList.add("d-none")
            btn_liquidacionSeguimientoUser.classList.add("d-none")
            reserva_grupal.classList.add("d-none")
            if (resumenContainer) {
                resumenContainer.classList.add("d-none");
            }
            if (resumenVendedoresContainer) {
                resumenVendedoresContainer.classList.add("d-none");
            }
            if (resumenDestinosContainer) {
                resumenDestinosContainer.classList.add("d-none");
            }
            const divFormVenta = document.querySelector("#div_formVentaPasaje");
            if (divFormVenta) divFormVenta.classList.add("d-none");
        } else {
            $(".btn_selection_programacion").removeClass("active")
            this.classList.add("active")
            this.classList.replace("bg-success", "bg-info")
            clear_vehiculo();
            limpiarAsientosSeleccionados();
            show_vehiculo(id_vehiculo.value, window.innerWidth < 992)
            id_v = id_vehiculo.value
            if (estado_pro.value == 1) {
                id_tp_usuario_sesion.value == 1 || id_tp_usuario_sesion.value == 2 ? btn_cambiar_carro.classList.remove("d-none") : btn_cambiar_carro.classList.add("d-none");
                id_tp_usuario_sesion.value == 1 || id_tp_usuario_sesion.value == 2 ? btn_liquidacionSeguimiento.classList.remove("d-none") : btn_liquidacionSeguimiento.classList.add("d-none");
                instance._L_USUARIOS_ == '1' ? $("#btn_liquidacion_usuario").removeClass("d-none") : $("#btn_liquidacion_usuario").addClass("d-none");
                instance._L_TERMINALES_ == '1' ? $("#btn_liquidacion").removeClass("d-none") : $("#btn_liquidacion").addClass("d-none");
                reserva_grupal.classList.remove("d-none")
            }
            btn_manifiesto.classList.remove("d-none");
            if (permiso_manifiesto_sunat.value == '1') {
                btn_manifiesto_sunat.classList.remove("d-none");
            }
            ruta_control_pasajero = instance._URL_ + 'pasaje/impresion/control_pasajeros/' + id_programacion.value
            btn_control_pasajero.classList.remove("d-none")
            btn_control_pasajero.href = ruta_control_pasajero
            ruta_manifiesto = instance._URL_ + instance.CONSTS.URL.MANIFIESTO.PASAJE + id_programacion.value
            ruta_manifiesto_sunat = instance._URL_ + instance.CONSTS.URL.MANIFIESTO.PASAJE_SUNAT + id_programacion.value
            document.querySelector('#id_program').value = id_programacion.value;
            id_progra = id_programacion.value;
            id_procond = id_programacion.value;
            btn_liquidacionSeguimiento.href = instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_VEHICULO + id_programacion.value
            btn_liquidacionSeguimientoUser.classList.remove("d-none")
            btn_liquidacionSeguimientoUser.href = instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_USUARIO + id_programacion.value + "/" + instance._ID_USUARIO_SESION

            //Asignando el valor al select 
            info_OD.innerHTML = `
            <label class="fw-bolder p-0 m-0 me-3">Origen: <span class="fw-bold ms-2">${nombre_p_origen.value}</span></label>
            <label class="fw-bolder p-0 m-0">Destino: <span class="fw-bold ms-2">${nombre_p_destino.value}</span></label>
            `;

            //Rutas Destino
            let formD = new FormData();
            formD.set("id_programacion", id_programacion.value);
            fetch(instance._URL_ + "pasaje/get_DRutas", {
                method: 'POST',
                body: formD
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                try {
                    if (data.success) {
                        destino_pasajero.innerHTML = '';
                        destino_pasajero_seleccionado = null;
                        Object.values(data.message).forEach(e => {
                            const seleccionado = String(e.id_terminal) === String(terminal_destino.value);
                            if (seleccionado) {
                                destino_pasajero_seleccionado = e.id_ruta_destino ? e.id_ruta_destino : 'null';
                            }
                            destino_pasajero.insertAdjacentHTML('beforeend',
                                `<option value="${e.id_ruta_destino ? e.id_ruta_destino : 'null'}" ${seleccionado ? 'selected' : ''}>${e.nombre} </option>`);
                        });
                    }
                } catch {
                    instance.Toast.operacion_erronea('Ha ocurrido un error al cargar las rutas.')
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
        }

        //Cargar la tabla con los datos que vendio el carro boletas y facturas
        async function fetchTableData(url, idProgramacion, tpComprobante, tableInstance) {
            try {
                const formData = new FormData();
                formData.append('id_programacion', idProgramacion.value);
                formData.append('tp_comprobante', tpComprobante);

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error(response.status);

                const data = await response.json();

                if (data.success) {
                    tableInstance.clear().rows.add(data.data || []).draw();
                } else {
                    instance.Toast.operacion_erronea(data.message);
                }
            } catch (error) {
                instance.Toast.operacion_erronea(error.message);
            }
        }

        // Usage
        fetchTableData(
            instance._URL_ + 'pasaje/get_dataTable',
            id_programacion,
            '1, 3',
            table_comprobante
        );

        fetchTableData(
            instance._URL_ + 'pasaje/get_dataTable',
            id_programacion,
            '2',
            table_notaVenta
        );
        actualizarResumenes(id_programacion.value);
    });

    const set_personal = (data) => {
        let tbody = table_personal.getElementsByTagName("tbody")[0]
        Object.values(data).forEach((e, i, a) => {
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
            <input type="hidden" class="id_personal_selected" value="${e.id_usuario}">
                <td><label class="personal_selected mb-0 mt-2">${e.personal_nombres} ${e.personal_apellidos} - ${e.personal_num_docu} - ${e.personal_tp_usuario}</label></td>
                <td><button type='button' class='btn btnDeleteRegis button_deleteItem' style="font-size:10px !important">Eliminar</button></td>
            </tr>
            `)
        })
    }

    //Proceso para limpiar los campo de entrada en la vista principal de pasajes
    $('#c_limpiar').on('click', function () {
        instance.Datatable.reloadTable(table_comprobante)
        instance.Datatable.reloadTable(table_notaVenta)
    });

    //Seleccion de listado de pasajes loagica aqui 
    $('#pills-list_pasaje-tab').on('click', function () {
        if (selec_progra == 0) {
            Swal.fire({
                title: '!!Atencion!!',
                text: 'No se ha seleccionado ninguna programacion',
                icon: 'error',
            })
            return false;
        }
    });

    //Envio del form de cambio de asiento
    form_asien.addEventListener("submit", async (e) => {
        e.preventDefault();
        let formdata = new FormData(form_asien)
        if (formdata.get('asien') == '') {
            Swal.fire({
                title: '!!Atencion!!',
                text: 'Seleccione un asiento',
                icon: 'error',
            });
            return false;
        }
        fetch(instance._URL_ + 'pasaje/cambiar_asiento', {
            method: 'POST',
            body: formdata
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Exito',
                        text: data.message,
                        icon: 'success',
                    })
                    $("#modal_pasaje").modal("hide")
                    $("#modal_asien").modal("hide")
                    show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value, window.innerWidth < 992)
                    //Mostrar ventana de impresion con la condicional de que si esta o no activado la impresion rapida
                    if (instance._P_IMPRIMIR_ == 1) {
                        fetch(data.comprobante, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                            .then(response => {
                                if (!response.ok) throw new Error(response.statusText);

                                const contentType = response.headers.get('Content-Type');

                                if (contentType.includes('application/pdf')) {
                                    return response.blob().then(blob => ({ type: 'pdf', blob }));
                                } else if (contentType.includes('application/json')) {
                                    return response.json().then(data => ({ type: 'json', data }));
                                } else {
                                    throw new Error("Tipo de contenido no esperado");
                                }
                            })
                            .then(result => {
                                if (result.type === 'pdf') {
                                    var url = URL.createObjectURL(result.blob);

                                    var iframe = document.createElement('iframe');
                                    iframe.style.display = 'none';
                                    iframe.src = url;

                                    document.body.appendChild(iframe);
                                    iframe.onload = function () {
                                        iframe.contentWindow.print();
                                    };
                                } else if (result.type === 'json') {
                                    let iframe = document.createElement('iframe');
                                    iframe.style.display = 'none'; // Asegúrate de que esté oculto
                                    iframe.src = result.data.link;
                                    document.body.appendChild(iframe);

                                    // Eliminar el iframe después de 10 segundos
                                    setTimeout(function () {
                                        document.body.removeChild(iframe);
                                    }, 5000); // 10 segundos
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message,
                                });
                            });
                    } else {
                        imprimir_comprobante(data.comprobante);
                    }
                } else {
                    Swal.fire({
                        title: 'Atencion',
                        text: data.message,
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        showCancelButton: false,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2a9d8f',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            return false;
                        }
                    })
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    })

    //Boton para cambiar de asiento
    $('#contenedor-botones').on('click', '.btnCambiarA', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id_venta');
        $("#id_venta_a").val(id);
        $("#id_pro_a").val($(".btn_selection_programacion.active").closest("tr").find(".id_programacion").val());
        $("#id_vehi_a").val(id_v);
        $("#modal_asien").modal("show");
    });

    //Boton de cambiado de carro 
    $('#btn_cambiar_carro').on('click', function (e) {
        e.preventDefault();

        const btnActiva = document.querySelector(".btn_selection_programacion.active");
        const filaActiva = btnActiva ? btnActiva.closest("tr") : null;
        const inputProgramacion = filaActiva
            ? filaActiva.querySelector(".id_programacion")
            : null;
        if (!inputProgramacion) {
            instance.Toast.operacion_erronea("Selecciona primero una programación");
            return;
        }

        const id_programacion = inputProgramacion.value;

        if (typeof id_v === 'undefined' || !id_v) {
            instance.Toast.operacion_erronea("No se ha definido el vehículo actual");
            return;
        }

        const $btn = $(this);
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);

        const params = new URLSearchParams({
            id_vehiculo: id_v,
            id_programacion: id_programacion
        });

        fetch(instance._URL_ + "vehiculo/get_n_asientos", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Error HTTP " + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    $('#vehiculo').empty();
                    $('#vehiculo').append($('<option>', {
                        value: '',
                        text: 'Seleccione'
                    }));

                    Object.values(data.vehiculos).forEach(function (vehiculo) {
                        $('#vehiculo').append($('<option>', {
                            value: vehiculo.id_vehiculo,
                            text: vehiculo.placa
                        }));
                    });

                    $('#modal_vehiculo').modal('show');
                } else {
                    instance.Toast.operacion_erronea(data.message);
                }
            })
            .catch(function (error) {
                instance.Toast.operacion_erronea("Ocurrió un error al buscar vehículos disponibles");
                console.error(error);
            })
            .finally(function () {
                $btn.prop('disabled', false);
            });
    });


    // Intercepta el clic en el botón
    $('#btn_manifiesto').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Qué deseas hacer?',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Vista Previa',
            denyButtonText: 'Vista Completa',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // ---- VISTA PREVIA: pregunta notas, luego salta validación de conductor ----
                preguntarNotasYGenerar(true);
            } else if (result.isDenied) {
                // ---- VISTA COMPLETA: tu flujo ORIGINAL, sin cambios ----
                flujoCompletoOriginal();
            }
        });
    });

    function preguntarNotasYGenerar(esPreview) {
        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: "Cancelar",
            html: `
            <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
               <div class="radio-input-wrapper">
               <label class="label">
               <input value="1" name="seleccion" id="seleccion" class="radio-input" type="radio">
               <div class="radio-design"></div>
               <div class="label-text">Incluir Notas</div>
               </label>
               <label class="label">
               <input value="2" name="seleccion" id="seleccion" class="radio-input" type="radio">
               <div class="radio-design"></div>
               <div class="label-text">Solo Notas</div>
               </label>
               </div>
            </div>
            `,
            confirmButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                let label = document.querySelector('input[name="seleccion"]:checked')
                let condicion = label ? label.value : 0;

                if (esPreview) {
                    generarVistaPrevia(condicion);
                } else {
                    generarPDFCompleto(condicion);
                }
            }
        });
    }

    function generarVistaPrevia(condicion) {
        $.ajax({
            url: ruta_manifiesto,
            method: 'POST',
            data: {
                condicion: condicion,
                preview: 1
            },
            xhrFields: { responseType: 'blob' },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var url = URL.createObjectURL(blob);
                window.open(url, '_blank');
            },
            error: function (xhr, status, error) {
                console.error("Error al recuperar la vista previa:", error);
                instance.Toast.operacion_erronea('No se pudo generar la vista previa.');
            }
        });
    }

    function generarPDFCompleto(condicion) {
        $.ajax({
            url: ruta_manifiesto,
            method: 'POST',
            data: { condicion: condicion },
            xhrFields: { responseType: 'blob' },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var url = URL.createObjectURL(blob);
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = url;
                document.body.appendChild(iframe);
                iframe.onload = function () {
                    iframe.contentWindow.print();
                };
            },
            error: function (xhr, status, error) {
                console.error("Error al recuperar el PDF:", error);
            }
        });
    }

    function flujoCompletoOriginal() {
        $.ajax({
            url: ruta_manifiesto,
            success: function (response) {
                if (response == 1) {
                    let formData = new FormData()
                    formData.set("id_programacion", id_progra)
                    fetch(instance._URL_ + "programacion/get_personalProgramacion", {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (!response.ok) throw new Error(response.status())
                        return response.json()
                    }).then(data => {
                        try {
                            if (data.success) {
                                set_personal(data.message)
                            }
                        } catch {
                            instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
                        }
                    }).catch(error => instance.Toast.operacion_erronea(error.message))
                    $('#modal_conductor').modal('show');
                } else {
                    // Reutilizamos la función ya factorizada:
                    preguntarNotasYGenerar(false);
                }
            },
            error: function (xhr, status, error) { }
        });
    }

    $('#btn_manifiesto_sunat').on('click', function (e) {
        e.preventDefault();
        mani.value = 1;

        Swal.fire({
            title: '¿Qué deseas hacer?',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Vista Previa',
            denyButtonText: 'Vista Completa',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                generarVistaPreviaSunat();
            } else if (result.isDenied) {
                flujoCompletoOriginalSunat();
            }
        });
    });

    function generarVistaPreviaSunat() {
        $.ajax({
            url: ruta_manifiesto_sunat,
            method: 'POST',
            data: {
                preview: 1
            },
            xhrFields: { responseType: 'blob' },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var url = URL.createObjectURL(blob);
                window.open(url, '_blank');
            },
            error: function (xhr, status, error) {
                console.error("Error al recuperar la vista previa:", error);
                instance.Toast.operacion_erronea('No se pudo generar la vista previa.');
            }
        });
    }

    function flujoCompletoOriginalSunat() {
        $.ajax({
            url: ruta_manifiesto_sunat,
            success: function (response) {
                if (response == 1) {
                    let formData = new FormData()
                    formData.set("id_programacion", id_progra)
                    fetch(instance._URL_ + "programacion/get_personalProgramacion", {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (!response.ok) throw new Error(response.status())
                        return response.json()
                    }).then(data => {
                        try {
                            if (data.success) {
                                set_personal(data.message)
                            }
                        } catch {
                            instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
                        }
                    }).catch(error => instance.Toast.operacion_erronea(error.message))
                    $('#modal_conductor').modal('show');
                } else {
                    let label = document.querySelector('input[name="seleccion"]:checked')
                    let condicion = label ? label.value : 0;
                    $.ajax({
                        url: ruta_manifiesto_sunat,
                        success: function (response) {
                            if (response == 1) {
                                let formData = new FormData()
                                formData.set("id_programacion", id_progra)
                                formData.set("condicion", 0)
                                fetch(instance._URL_ + "programacion/get_personalProgramacion", {
                                    method: 'POST',
                                    body: formData
                                }).then(response => {
                                    if (!response.ok) throw new Error(response.status())
                                    return response.json()
                                }).then(data => {
                                    try {
                                        if (data.success) {
                                            set_personal(data.message)
                                        }
                                    } catch {
                                        instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
                                    }
                                }).catch(error => instance.Toast.operacion_erronea(error.message))
                                $('#modal_conductor').modal('show');
                            } else {
                                $.ajax({
                                    url: ruta_manifiesto_sunat,
                                    method: 'POST',
                                    data: {
                                        condicion: condicion
                                    },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        var blob = new Blob([response], { type: 'application/pdf' });
                                        var url = URL.createObjectURL(blob);

                                        var iframe = document.createElement('iframe');
                                        iframe.style.display = 'none';
                                        iframe.src = url;

                                        document.body.appendChild(iframe);
                                        iframe.onload = function () {
                                            iframe.contentWindow.print();
                                        };
                                    },
                                    error: function (xhr, status, error) {
                                        console.error("Error al recuperar el PDF:", error);
                                    }
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
            }
        });
    }


    const show_vehiculo = async (id_vehiculo, isMobile = false) => {
        const divFormVenta = document.querySelector("#div_formVentaPasaje");
        const divSinAsientos = document.querySelector("#div_sin_asientos");

        if (divFormVenta) divFormVenta.classList.add("d-none");
        if (divSinAsientos) divSinAsientos.classList.remove("d-none");
        const estado_progra = document.querySelector(".btn_selection_programacion.active")
            .closest("tr").querySelector(".estado_programacion").value;

        const formData = new FormData();
        formData.set('id_vehiculo', id_vehiculo);
        formData.set('id_programacion', document.querySelector(".btn_selection_programacion.active")
            .closest("tr").querySelector(".id_programacion").value);

        await fetch(instance._URL_ + "pasaje/get_vehiculoForID", {
            method: "POST",
            body: formData
        })
            .then(response => { if (response.ok) return response.json() })
            .then(data => {
                if (!data.success) return;

                superParentVehiculo.classList.remove("d-none");
                superParentVehiculo.querySelector("div").innerHTML = "";

                // ── SVGs según dispositivo ──────────────────────────────────────
                const svgs = isMobile ? {
                    delantera: `
                <svg width="300" height="125" viewBox="0 0 300 125" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 0 125 L 0 25 Q 0 5 20 5 L 280 5 Q 300 5 300 25 L 300 125"
                          fill="none" stroke="#cfcfcf" stroke-width="3"/>
                    <rect x="20" y="18" width="60" height="25" rx="12" ry="12" fill="#bfbfbf"/>
                    <rect x="220" y="18" width="60" height="25" rx="12" ry="12" fill="#bfbfbf"/>
                    <circle cx="70" cy="88" r="25" fill="none" stroke="#bfbfbf" stroke-width="4"/>
                    <circle cx="70" cy="88" r="11" fill="none" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="81" y1="88" x2="93" y2="88" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="63" y1="77" x2="53" y2="67" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="63" y1="99" x2="53" y2="109" stroke="#bfbfbf" stroke-width="3"/>
                </svg>`,
                    trasera: `
                <svg width="300" height="41" viewBox="0 0 300 40" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 0 0 L 0 28 Q 0 40 12 40 L 288 40 Q 300 40 300 28 L 300 0 Z" fill="white"/>
                    <path d="M 0 0 L 0 28 Q 0 40 12 40 L 288 40 Q 300 40 300 28 L 300 0"
                          fill="none" stroke="#c0c0c0" stroke-width="3"/>
                </svg>`
                } : {
                    delantera: `
                <svg width="125" height="300" viewBox="0 0 125 300" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 125 0 L 25 0 Q 5 0 5 20 L 5 280 Q 5 300 25 300 L 125 300"
                          fill="none" stroke="#cfcfcf" stroke-width="3"/>
                    <rect x="20" y="30" width="25" height="60" rx="12" ry="12" fill="#bfbfbf"/>
                    <rect x="20" y="210" width="25" height="60" rx="12" ry="12" fill="#bfbfbf"/>
                    <circle cx="85" cy="230" r="25" fill="none" stroke="#bfbfbf" stroke-width="4"/>
                    <circle cx="85" cy="230" r="11" fill="none" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="85" y1="220" x2="85" y2="208" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="75" y1="237" x2="65" y2="247" stroke="#bfbfbf" stroke-width="3"/>
                    <line x1="95" y1="237" x2="105" y2="247" stroke="#bfbfbf" stroke-width="3"/>
                </svg>`,
                    trasera: `
                <svg width="55" height="300" viewBox="0 0 40 220" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 0 0 L 28 0 Q 40 0 40 12 L 40 208 Q 40 220 28 220 L 0 220 Z" fill="white"/>
                    <path d="M 0 0 L 28 0 Q 40 0 40 12 L 40 208 Q 40 220 28 220 L 0 220"
                          fill="none" stroke="#c0c0c0" stroke-width="3"/>
                </svg>`
                };

                // ── Cálculo de alturas (idéntico en ambos) ──────────────────────
                const heightPiso1 = parseInt(data.message[0].height.replace("px", ""));
                const heightPiso2 = data.message[0].num_piso > 1
                    ? parseInt(data.message[0].height_piso2.replace("px", ""))
                    : 0;
                const alturaTotal = heightPiso1 + heightPiso2;

                // ── Estructura del contenedor según dispositivo ─────────────────
                const sectionStyle = isMobile
                    ? `flex-column`
                    : `flex-row`;

                const contenedorStyle = isMobile
                    ? `width: 300px; height: ${alturaTotal}px; border-left: 3px solid #cfcfcf; border-right: 3px solid #cfcfcf;`
                    : `width: ${alturaTotal}px; height: 300px; border-top: 3px solid #cfcfcf; border-bottom: 3px solid #cfcfcf;`;

                superParentVehiculo.querySelector("div").insertAdjacentHTML('beforeend', `
            <section class="position-relative d-flex ${sectionStyle} align-items-center px-0 mx-auto" style="gap: 0;">
                ${svgs.delantera}
                <div class="position-relative parent_vehiculo_unificado" style="${contenedorStyle}"></div>
                ${svgs.trasera}
            </section>
        `);

                const contenedorUnificado = superParentVehiculo.querySelector('.parent_vehiculo_unificado');
                const originalWidth = 250;

                // ── Indicadores de piso según dispositivo ──────────────────────
                const crearIndicadorPiso = (texto, posicion) => {
                    const div = document.createElement("div");
                    const posStyle = isMobile
                        ? `top: ${posicion}px; left: 50%; transform: translate(-50%, 0);`
                        : `top: 50%; left: ${posicion}px; transform: translate(-100%, -50%) rotate(-90deg);`;

                    div.style.cssText = `
                position: absolute; ${posStyle}
                font-size: 12px; font-weight: 700; letter-spacing: 2px; color: #6c757d;
                background: rgba(255,255,255,0.85); padding: 4px 8px; border-radius: 6px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);
                z-index: 10; white-space: nowrap; pointer-events: none; user-select: none;
                transition: all 0.2s ease-in-out;`;
                    div.textContent = texto;
                    contenedorUnificado.appendChild(div);
                };

                crearIndicadorPiso("PISO 1", 0);
                if (data.message[0].num_piso > 1) {
                    crearIndicadorPiso("PISO 2", heightPiso1);
                }

                // ── Renderizado de asientos según dispositivo ───────────────────
                Object.values(data.message[1]).forEach((e) => {
                    let topOriginal = parseInt(e.top_obj.replace("px", ""));
                    let leftOriginal = parseInt(e.left_obj.replace("px", ""));

                    if (e.piso == 2) topOriginal += heightPiso1;

                    if (isMobile) {
                        e.top_obj = `${topOriginal}px`;
                        e.left_obj = `${leftOriginal}px`;
                        crear_objeto_unificado_mobile(e, contenedorUnificado, estado_progra);
                    } else {
                        e.left_obj = `${topOriginal}px`;
                        e.top_obj = `${originalWidth - leftOriginal}px`;
                        crear_objeto_unificado(e, contenedorUnificado, estado_progra);
                    }
                });

                // ── Ajuste dinámico de altura (solo mobile) ─────────────────────
                if (isMobile) {
                    const maxTop = Math.max(
                        ...Object.values(data.message[1]).map(e => parseInt(e.top_obj.replace("px", "")))
                    );
                    contenedorUnificado.style.height = `${maxTop + 55}px`;
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message));
    };

    const clear_vehiculo = () => {
        superParentVehiculo.classList.add("d-none")
        $(".parent_vehiculo div").remove()
    }

    // tippy 
    instance.tippy.init(".asiento_normal", {
        "content": `Asiento Normal`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_premium", {
        "content": `Asiento Premium`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_reservado", {
        "content": `Asiento Reservado`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_seleccionado", {
        "content": `Asiento Seleccionado`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_vendido", {
        "content": `Asiento Vendido`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_postponer", {
        "content": `Asiento por Postponer`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_proceso_web", {
        "content": `Asiento en Proceso Web`,
        "animation": "scale",
        "placement": "left",
    });

    instance.tippy.init(".asiento_venta_web", {
        "content": `Asiento Vendido Web`,
        "animation": "scale",
        "placement": "left",
    });

    //WebSocket configuración
    // ──────────────────────────────────────────────────────────
    // Configuración — ajusta estos valores según tu empresa/módulo
    // ──────────────────────────────────────────────────────────
    const WS_URL = "wss://tid.net.pe/ws";
    const EMPRESA_ID = instance._UUID_WS_SESION;
    const MODULO = "pasaje";

    // ──────────────────────────────────────────────────────────
    // Estado interno
    // ──────────────────────────────────────────────────────────
    let ws;
    let wsIntentosReconexion = 0;
    const WS_MAX_INTENTOS = 10;

    // ──────────────────────────────────────────────────────────
    // Conectar
    // ──────────────────────────────────────────────────────────
    function conectarWebSocket() {
        ws = new WebSocket(WS_URL);

        ws.onopen = function () {
            console.log("✅ WebSocket conectado");
            wsIntentosReconexion = 0;

            ws.send(JSON.stringify({
                tipo: "init",
                empresa_id: EMPRESA_ID,
                modulo: MODULO,
            }));
        };

        ws.onmessage = function (event) {
            let data;
            try {
                data = JSON.parse(event.data);
            } catch (e) {
                console.error("❌ Mensaje WS inválido:", e);
                return;
            }

            if (data.tipo === "init_ok") {
                return;
            }
            if (data.tipo === "pong" || data.tipo === "error") return;
            console.log(data)

            switch (data.tipo) {

                // ── Eventos que solo actualizan asientos ──────────────
                case "venta_pasaje":
                case "pago_reserva":
                case "reserva_grupal":
                case "reservar_todos":
                case "liberar_reserva":
                case "asiento_en_proceso":
                case "asiento_liberado":
                case "asientos_liberados":
                case "pasaje_postergado":
                case "venta_anulada":
                case "abordaje_marcado":
                case "concretar_venta_web":
                    procesarEventoAsientos(data); // ← 0 fetches si el payload trae asientos
                    break;

                // ── Eventos que actualizan comisiones ─────────────────
                case "nueva_comision":
                case "comision_actualizada":
                    actualizar_tabla_comisiones();
                    break;

                default:
                    console.warn("⚠️ Tipo de evento desconocido:", data.tipo);
            }
        };

        ws.onclose = function (event) {
            console.warn(`🔌 WebSocket cerrado (code: ${event.code}). Reconectando...`);
            programarReconexion();
        };

        ws.onerror = function (error) {
            console.error("⚠️ Error WebSocket:", error);
            // onclose se dispara justo después, allí se maneja la reconexión
        };
    }

    // ──────────────────────────────────────────────────────────
    // Reconexión automática con backoff exponencial
    // 1s → 2s → 4s → 8s → 16s → 30s (tope)
    // ──────────────────────────────────────────────────────────
    function programarReconexion() {
        if (wsIntentosReconexion >= WS_MAX_INTENTOS) {
            console.error("🚫 No se pudo reconectar al WebSocket tras varios intentos.");
            return;
        }

        const delay = Math.min(1000 * 2 ** wsIntentosReconexion, 30000);
        wsIntentosReconexion++;

        console.log(`🔄 Reintentando en ${delay / 1000}s... (intento ${wsIntentosReconexion})`);
        setTimeout(conectarWebSocket, delay);
    }

    conectarWebSocket();
    // ══════════════════════════════════════════════════════════
    // TUS FUNCIONES (sin cambios, solo referencia)
    // ══════════════════════════════════════════════════════════

    // ── Helper compartido ──────────────────────────────────────────
    const _getEstadoProgramacion = () => {
        const el = document.querySelector(".btn_selection_programacion.active");
        if (!el) return null;
        const tr = el.closest("tr");
        if (!tr) return null;
        const input = tr.querySelector(".estado_programacion");
        return input ? input.value : null;
    };

    // ─────────────────────────────────────────────────────────────────
const actualizarAsientoDOM = (
  id_obj_vehiculo,
  nuevoEstado,
  id_venta = null,
  terminal = null,
  terminal_color = null,
  id_usuario_reserva = null,
) => {
  const div = document.getElementById(id_obj_vehiculo);
  if (!div || div.getAttribute("tp_obj") !== "asiento") return;
console.log("🎫 ACTUALIZAR ASIENTO", {
    id_obj_vehiculo: id_obj_vehiculo,
    nuevoEstado: nuevoEstado,
    id_venta: id_venta,
    terminal: terminal,
    terminal_color: terminal_color,
    id_usuario_reserva: id_usuario_reserva
});
  const tp_asiento =
    div._data_obj && div._data_obj.tp_asiento
      ? div._data_obj.tp_asiento
      : div.getAttribute("tp_asiento") || "";

  const esMiReserva =
    nuevoEstado === "RESERVADO" &&
    id_usuario_reserva == instance._ID_USUARIO_SESION;

  // ── 1. Actualizar atributos del div ──────────────────────────
  div.setAttribute(
    "estado_asiento",
    nuevoEstado !== undefined && nuevoEstado !== null ? nuevoEstado : "",
  );
  div.setAttribute(
    "id_venta",
    id_venta !== undefined && id_venta !== null ? id_venta : "undefined",
  );
  div.setAttribute("seleccion", esMiReserva ? "true" : "");

  // ── 2. Actualizar _data_obj ──────────────────────────────────
  if (div._data_obj) {
    div._data_obj.estado_asiento =
      nuevoEstado !== undefined && nuevoEstado !== null ? nuevoEstado : null;
    div._data_obj.id_venta =
      id_venta !== undefined && id_venta !== null ? id_venta : null;
    div._data_obj.seleccion = esMiReserva ? "true" : "";
  }

  // ── 3. Recalcular color ──────────────────────────────────────
  let color_asiento;
  if (nuevoEstado) {
    color_asiento = instance.CONSTS.COLORES.ESTADO_ASIENTO[nuevoEstado];
  } else if (tp_asiento) {
    color_asiento =
      instance.CONSTS.COLORES.TIPO_ASIENTO[tp_asiento.toUpperCase()];
  } else {
    color_asiento = instance.CONSTS.COLORES.OBJETO_DEFAULT;
  }

  let color_text;
  if (nuevoEstado) {
    color_text = instance.CONSTS.COLORES.ASIENTO_TEXT.OCUPADO;
  } else {
    color_text =
      instance.CONSTS.COLORES.TIPO_ASIENTO[tp_asiento] ||
      instance.CONSTS.COLORES.ASIENTO_TEXT.LIBRE ||
      "#000000";
  }

  // ── 4. Aplicar colores al DOM ────────────────────────────────
  const icon = div.querySelector(".icon_obj");
  const text = div.querySelector(".text_obj");

  if (icon) {
    icon.style.color = color_asiento;
    if (nuevoEstado) {
      icon.classList.add("fw-bold");
    } else {
      icon.classList.remove("fw-bold");
    }
  }
  if (text) text.style.color = color_text;

  // ── 5. Badge superior ────────────────────────────────────────
  const badgeExistente = div.querySelector(".text_obj_terminal");
  if (badgeExistente) {
    badgeExistente.remove();
  }

  const wrapper = div.querySelector("div[style*='position:relative']");

  if (wrapper) {
    if (terminal) {
      wrapper.insertAdjacentHTML(
        "afterbegin",
        '<span class="badge text_obj_terminal" ' +
          'style="position:absolute; bottom:100%; left:50%; transform:translateX(-50%); ' +
          "font-size:" +
          instance.CONSTS.OBJETOS.TAMANO_TEXT_TERMINAL +
          "; " +
          "background-color:" +
          terminal_color +
          "; color:white; " +
          'white-space:nowrap; margin-bottom:2px; z-index:1;" ' +
          'title="' +
          terminal +
          '">' +
          terminal +
          "</span>",
      );
    } else if (nuevoEstado === "VENTA_WEB") {
      wrapper.insertAdjacentHTML(
        "afterbegin",
        '<span class="badge text_obj_terminal" ' +
          'style="position:absolute; bottom:100%; left:50%; transform:translateX(-50%); ' +
          "font-size:8px; background-color:black; color:white; " +
          'white-space:nowrap; margin-bottom:2px; z-index:1;" ' +
          'title="VENTA WEB">VENTA WEB</span>',
      );
    } else if (nuevoEstado === "RESERVADO") {
      wrapper.insertAdjacentHTML(
        "afterbegin",
        '<span class="badge text_obj_terminal" ' +
          'style="position:absolute; bottom:100%; left:50%; transform:translateX(-50%); ' +
          "font-size:" +
          instance.CONSTS.OBJETOS.TAMANO_TEXT_TERMINAL +
          ";" +
          "background-color:" +
          terminal_color +
          "; color:white; " +
          'white-space:nowrap; margin-bottom:2px; z-index:1;" ' +
          'title="' +
          terminal +
          '">' +
          terminal +
          "</span>",
      );
    }
  }

  // ── 6. Re-evaluar click listener ─────────────────────────────
  const estadoProgramacion = _getEstadoProgramacion();

  if (div._clickHandler) {
    div.removeEventListener("click", div._clickHandler);
    div._clickHandler = null;
  }

  const debeSerClickeable =
    estadoProgramacion == 1 &&
    (div.getAttribute("id_venta") === "undefined" || esMiReserva);

  if (debeSerClickeable) {
    div._clickHandler = async (e) => manejarClickAsiento(e.currentTarget);
    div.addEventListener("click", div._clickHandler);
  }
};
// ─────────────────────────────────────────────────────────────────
const procesarEventoAsientos = (data) => {

    if (TIPOS_LIBERACION.includes(data.tipo)) {
        quitarLiberadosDeMiSeleccion(data);
    }
    const btnActivo = document.querySelector(".btn_selection_programacion.active");
    if (!btnActivo) return;

    const tr = btnActivo.closest("tr");
    const idProgramacionActiva = tr && tr.querySelector(".id_programacion")
        ? tr.querySelector(".id_programacion").value
        : null;

    if (data.id_programacion && data.id_programacion != idProgramacionActiva) return;

    if (data.asientos && data.asientos.length > 0) {
        data.asientos.forEach(function (item) {
            var terminalLabel = null;
            var terminalColor = null;

            if (data.tipo === "concretar_venta_web") {
                terminalLabel = "VENTA WEB";
                terminalColor = "black";
            } else {
                terminalLabel = (item.terminal !== undefined && item.terminal !== null) ? item.terminal : null;
                terminalColor = (item.terminal_color !== undefined && item.terminal_color !== null) ? item.terminal_color : null;
            }

            actualizarAsientoDOM(
                item.id_obj_vehiculo,
                (item.estado !== undefined && item.estado !== null) ? item.estado : null,
                (item.id_venta !== undefined && item.id_venta !== null) ? item.id_venta : null,
                terminalLabel,
                terminalColor,
                item.id_usuario !== undefined ? item.id_usuario : null
            );
        });
        return;
    }

    console.warn("⚠️ Payload sin asientos, haciendo fallback a show_vehiculo");
    const idVehiculo = tr && tr.querySelector(".id_vehiculo")
        ? tr.querySelector(".id_vehiculo").value
        : null;

    if (idVehiculo) {
        show_vehiculo(idVehiculo).then(function () {
            actualizarResumenes(idProgramacionActiva, true);
        });
    }
};

    function actualizar_tabla_comisiones() {
        const btnSeleccion = document.querySelector(".btn_selection_programacion.active");
        if (btnSeleccion) {
            const idProgramacion = btnSeleccion.closest("tr").querySelector(".id_programacion").value;

            // Usar la función unificada
            actualizarResumenes(idProgramacion);
        }
    }

    function manejarReservaDobleClick(data_obj) {

        const esMiReserva = data_obj.seleccion === 'true';
        const permisoDesbloquearReservado = Number(instance.CONSTS.PERMISOS.P_DESBLOQUEAR_RESERVADO);
        const puedeDesbloquearReserva = esMiReserva || permisoDesbloquearReservado === 1;

        const formData = new FormData();
        formData.set("id_venta", data_obj.id_venta);

        fetch(instance._URL_ + "pasaje/getDataVenta", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (!data.success) return;

            const nombreReservante = data.message.venta.nombresU;

            if (puedeDesbloquearReserva) {

                Swal.fire({
                    title: 'Asiento reservado',
                    text: `Asiento reservado por ${nombreReservante}. ¿Desea desbloquear esta reserva?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desbloquear',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#2a9d8f',
                    cancelButtonColor: '#6c757d'
                }).then((resultado) => {

                    if (!resultado.isConfirmed) return;

                    const formData = new FormData();
                    formData.set("numeros_asientos", data_obj.text_obj);
                    formData.set("id_vehiculo", id_v);
                    formData.set(
                        "id_programacion",
                        document.querySelector(".btn_selection_programacion.active")
                            .closest("tr")
                            .querySelector(".id_programacion").value
                    );

                    fetch(instance._URL_ + "pasaje/liberar_reservas", {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message);
                        } else {
                            instance.Toast.operacion_erronea(data.message);
                        }
                    })
                    .catch(error => {
                        instance.Toast.operacion_erronea(error.message);
                    });
                });

            } else {

                Swal.fire({
                    title: 'Asiento reservado',
                    html: `
                            Este asiento fue reservado por <strong>${nombreReservante}</strong>.<br>
                            <strong>Usted no tiene permisos para desbloquear.</strong><br>
                            Comuníquese con el reservante o personal administrativo.
                        `,
                    icon: 'info',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }

    const crear_objeto_unificado = (data_obj, contenedor, estado_progra = 1) => {
        let div = document.createElement("div");
        let objeto_cursorPointer = data_obj.tp_obj == "asiento" ? "cursor-pointer" : null;
        div.className = `position-absolute objeto ${objeto_cursorPointer}`
        div.setAttribute("floor", data_obj.piso)
        div.setAttribute("tp_obj", data_obj.tp_obj)
        div.setAttribute("id_venta", data_obj.id_venta)
        div.setAttribute("estado_asiento", data_obj.estado_asiento)
        div.setAttribute("seleccion", data_obj.seleccion ? data_obj.seleccion : '')
        div.setAttribute("id_usuario_reserva", data_obj.id_usuario_reserva ? data_obj.id_usuario_reserva : (data_obj.id_usuario ? data_obj.id_usuario : ''))
        div.id = data_obj.id_obj_vehiculo
        div.setAttribute("text_obj", data_obj.text_obj)
        data_obj.tp_obj == "asiento" ? div.setAttribute("tp_asiento", data_obj.tp_asiento) : null
        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}`

        // Rotación del contenedor (asiento)
        let rotacionOriginal = parseInt(data_obj.rotate_obj.replace("deg", "")) || 0;
        let rotacionTotal = rotacionOriginal + 90;
        div.style.transform = `rotate(${rotacionTotal}deg)`;

        // Contra-rotación para el texto
        let contraRotacion = -rotacionTotal;

        // estableciendo el color del asiento
        let color_asiento
        if (data_obj.estado_asiento) {
            color_asiento = instance.CONSTS.COLORES.ESTADO_ASIENTO[data_obj.estado_asiento]
        } else if (data_obj.tp_obj == "asiento") {
            color_asiento = instance.CONSTS.COLORES.TIPO_ASIENTO[data_obj.tp_asiento.toUpperCase()]
        } else {
            color_asiento = instance.CONSTS.COLORES.OBJETO_DEFAULT
        }
        let color_text = data_obj.estado_asiento ? instance.CONSTS.COLORES.ASIENTO_TEXT.OCUPADO : instance.CONSTS.COLORES.TIPO_ASIENTO[data_obj.tp_asiento]
        let weight = data_obj.estado_asiento ? 'fw-bold' : ''

        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}; position:absolute; display:inline-block;`
        let span_superior = '';
        if (data_obj.terminal) {
            span_superior = `<span class="badge text_obj_terminal" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); font-size: ${instance.CONSTS.OBJETOS.TAMANO_TEXT_TERMINAL}; background-color: ${data_obj.terminal_color}; color: white; white-space: nowrap; margin-bottom: 2px; z-index: 1;" title="${data_obj.terminal}">${data_obj.terminal}</span>`;
        } else if (data_obj.estado_asiento && data_obj.estado_asiento == "VENTA_WEB") {
            span_superior = `<span class="badge text_obj_terminal" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); font-size: 8px; background-color: black; color: white; white-space: nowrap; margin-bottom: 2px; z-index: 1;" title="VENTA WEB">VENTA WEB</span>`;
        }
        div.insertAdjacentHTML("beforeend", `
            <div style="position:relative; display:inline-block;">
                ${span_superior}
                <i class="${data_obj.icon_obj} icon_obj ${weight}" class-icon="${data_obj.icon_obj}" style=" font-style: normal; font-size: ${instance.CONSTS.OBJETOS.TAMANO}; color: ${color_asiento}; display: block; position: relative; transform: rotate(${rotacionTotal}deg);">
                <span class="text_obj fw-bolder" style="position: absolute; top: 33%; left: 50%; font-size:12px; color:${color_text}; transform: translate(-50%, -50%) rotate(${contraRotacion}deg); transform-origin: center center; font-weight:600 !important;">${data_obj.text_obj}</span>
                </i>
            </div>
        `)

        // Agregar al contenedor unificado
        div._data_obj = data_obj;
        contenedor.appendChild(div)

        // Evento dblclick
        if (data_obj.tp_obj == "asiento") {
if (
    estado_progra == 1 &&
    (div.getAttribute("id_venta") == 'undefined' || data_obj.seleccion === 'true')
) {
    div._clickHandler = async (e) => {
        manejarClickAsiento(e.currentTarget);
    };
    div.addEventListener("click", div._clickHandler);
}
            div.addEventListener("dblclick", (e) => {
                if (data_obj.estado_asiento == 'PROCESO_WEB') {
                    Swal.fire({
                        title: 'Atencion!',
                        text: 'Este asiento esta en proceso de venta web, dejar que culmine',
                        icon: 'warning',
                    });
                    return;
                }

                if (data_obj.estado_asiento == 'RESERVADO') {
                    manejarReservaDobleClick(data_obj);
                    return;
                }

                if (div.getAttribute("id_venta") != 'undefined') {
                    $("#modal_pasaje").modal("show");
                    id_asiento_selected.value = div.id;
                    showDataVenta(div.getAttribute("id_venta"));
                }
            });
        }
    }

    const crear_objeto_unificado_mobile = (data_obj, contenedor, estado_progra = 1) => {
        let div = document.createElement("div");
        let objeto_cursorPointer = data_obj.tp_obj == "asiento" ? "cursor-pointer" : null;
        div.className = `position-absolute objeto ${objeto_cursorPointer}`
        div.setAttribute("floor", data_obj.piso)
        div.setAttribute("tp_obj", data_obj.tp_obj)
        div.setAttribute("id_venta", data_obj.id_venta)
        div.setAttribute("estado_asiento", data_obj.estado_asiento)
        div.setAttribute("seleccion", data_obj.seleccion ? data_obj.seleccion : '')
        div.setAttribute("id_usuario_reserva", data_obj.id_usuario_reserva ? data_obj.id_usuario_reserva : (data_obj.id_usuario ? data_obj.id_usuario : ''))
        div.id = data_obj.id_obj_vehiculo
        div.setAttribute("text_obj", data_obj.text_obj)
        data_obj.tp_obj == "asiento" ? div.setAttribute("tp_asiento", data_obj.tp_asiento) : null
        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}`

        // estableciendo el color del asiento
        let color_asiento
        if (data_obj.estado_asiento) {
            color_asiento = instance.CONSTS.COLORES.ESTADO_ASIENTO[data_obj.estado_asiento]
        } else if (data_obj.tp_obj == "asiento") {
            color_asiento = instance.CONSTS.COLORES.TIPO_ASIENTO[data_obj.tp_asiento.toUpperCase()]
        } else {
            color_asiento = instance.CONSTS.COLORES.OBJETO_DEFAULT
        }
        let color_text = data_obj.estado_asiento ? instance.CONSTS.COLORES.ASIENTO_TEXT.OCUPADO : instance.CONSTS.COLORES.TIPO_ASIENTO[data_obj.tp_asiento]
        let weight = data_obj.estado_asiento ? 'fw-bold' : ''

        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}; position:absolute; display:inline-block;`
        let span_superior = '';
        if (data_obj.terminal) {
            span_superior = `<span class="badge text_obj_terminal" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); font-size: ${instance.CONSTS.OBJETOS.TAMANO_TEXT_TERMINAL}; background-color: ${data_obj.terminal_color}; color: white; white-space: nowrap; margin-bottom: 2px; z-index: 1;" title="${data_obj.terminal}">${data_obj.terminal}</span>`;
        } else if (data_obj.estado_asiento && data_obj.estado_asiento == "VENTA_WEB") {
            span_superior = `<span class="badge text_obj_terminal" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); font-size: 8px; background-color: black; color: white; white-space: nowrap; margin-bottom: 2px; z-index: 1;" title="VENTA WEB">VENTA WEB</span>`;
        }
        div.insertAdjacentHTML("beforeend", `
            <div style="position:relative; display:inline-block;">
                ${span_superior}
                <i class="${data_obj.icon_obj} icon_obj ${weight}" class-icon="${data_obj.icon_obj}" style=" font-style: normal; font-size: ${instance.CONSTS.OBJETOS.TAMANO}; color: ${color_asiento}; display: block; position: relative;">
                <span class="text_obj fw-bolder" style="position: absolute; top: 33%; left: 50%; font-size:12px; color:${color_text}; transform: translate(-50%, -50%); transform-origin: center center; font-weight:600 !important;">${data_obj.text_obj}</span>
                </i>
            </div>
        `)

        // Agregar al contenedor unificado
        div._data_obj = data_obj;
        contenedor.appendChild(div)

        // Evento dblclick
        if (data_obj.tp_obj == "asiento") {
            // El click solo aplica bajo condiciones específicas
if (
    estado_progra == 1 &&
    (div.getAttribute("id_venta") == 'undefined' || data_obj.seleccion === 'true')
) {
    div._clickHandler = async (e) => {
        manejarClickAsiento(e.currentTarget);
    };
    div.addEventListener("click", div._clickHandler);
}

            div.addEventListener("dblclick", (e) => {
                if (data_obj.estado_asiento == 'PROCESO_WEB') {
                    Swal.fire({
                        title: 'Atencion!',
                        text: 'Este asiento esta en proceso de venta web, dejar que culmine',
                        icon: 'warning',
                    });
                    return;
                }

                if (data_obj.estado_asiento == 'RESERVADO') {
                    manejarReservaDobleClick(data_obj);
                    return;
                }

                if (div.getAttribute("id_venta") != 'undefined') {
                    $("#modal_pasaje").modal("show");
                    id_asiento_selected.value = div.id;
                    showDataVenta(div.getAttribute("id_venta"));
                }
            });
        }
    }

    let asientosSeleccionados = [];

    // Inicializar UNA SOLA VEZ los eventos de la tabla
    function inicializarEventosTablaAsientos() {
        const tabla = document.getElementById("table_asientos");
        if (!tabla) return;

        const tbody = tabla.querySelector("tbody");
        if (!tbody) return;

        tbody.addEventListener("click", function (e) {
            const btnEliminar = e.target.closest(".btn-eliminar-asiento");
            if (btnEliminar) {
                const index = parseInt(btnEliminar.dataset.index);
                deseleccionarAsiento(index);
                return;
            }

            const btnReservar = e.target.closest(".btn-reservar-asiento");
            if (btnReservar) {
                const index = parseInt(btnReservar.dataset.index);
                const asientoData = asientosSeleccionados[index];
                reservar_asiento(asientoData);
            }
        });
    }

    inicializarEventosTablaAsientos();

    function renderTablaAsientos({ recargarVista = true } = {}) {
        const tabla = document.getElementById("table_asientos");
        if (!tabla) return;

        const tbody = tabla.querySelector("tbody");
        if (!tbody) return;

        tbody.innerHTML = "";

        if (asientosSeleccionados.length === 0) {
            tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted">
                    No hay asientos seleccionados
                </td>
            </tr>
        `;
            actualizarScrollAsientos(false);
            return;
        }

        asientosSeleccionados.forEach((item, index) => {
            const claseNumero = index === 0
                ? "numero_asiento numero_asiento_seleccionado"
                : "numero_asiento";

            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td>
                <a class="${claseNumero}">
                    ${item.text_obj != null ? item.text_obj : item.id}
                </a>
            </td>
            <td>
                <button class="btn redondo btn-sm btn-danger btn-eliminar-asiento" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            </td>
            <td>
                <button class="btn redondo btn-sm btn-warning btn-reservar-asiento" data-index="${index}">
                    <i class="fas fa-bookmark"></i>
                </button>
            </td>
        `;
            tbody.appendChild(tr);
        });

        if (asientosSeleccionados.length > 0 && recargarVista) {
           cargarVistaVentaPrimerAsiento();
        }
        // Activar scroll condicional (true si hay 3 o más asientos)
        actualizarScrollAsientos(asientosSeleccionados.length >= 3);
    }

    // Solo estado local: NO llama al servidor
function limpiarSeleccionLocal() {
    asientosSeleccionados.length = 0;
    deleteVentaProceso = 0;
    document.querySelectorAll(".asiento-seleccionado").forEach(el => {
        el.classList.remove("asiento-seleccionado");
        el.dataset.procesando = "0";
    });
    renderTablaAsientos();
}

async function limpiarAsientosSeleccionados() {
    const seleccionados = [...asientosSeleccionados];
    limpiarSeleccionLocal(); // primero lo local
    if (seleccionados.length === 0) return;

    try {
        const formData = new FormData();
        formData.set("id_programacion", seleccionados[0].id_programacion);
        seleccionados.forEach(a => formData.append("ids_obj_vehiculo[]", a.id));
        await fetch(instance._URL_ + 'pasaje/desbloquear_asientos', { method: 'POST', body: formData });
    } catch (e) {
        console.error("Error liberando asientos", e);
    }
}

    const TIPOS_LIBERACION = ["asiento_liberado", "asientos_liberados", "liberar_reserva"];

    function quitarLiberadosDeMiSeleccion(data) {
    if (!asientosSeleccionados.length || !Array.isArray(data.asientos)) return;

    const idsLiberados = new Set(
        data.asientos
            .filter(a => a.estado === null || a.estado === undefined)
            .map(a => String(a.id_obj_vehiculo))
    );
    if (idsLiberados.size === 0) return;

    const primeroId = asientosSeleccionados[0].id;
    let quitados = 0;

    for (let i = asientosSeleccionados.length - 1; i >= 0; i--) {
        const a = asientosSeleccionados[i];
        if (String(a.id_programacion) !== String(data.id_programacion)) continue;
        if (!idsLiberados.has(String(a.id))) continue;

        const el = document.getElementById(a.id);
        if (el) {
            el.classList.remove("asiento-seleccionado");
            el.dataset.procesando = "0";
        }
        asientosSeleccionados.splice(i, 1);
        quitados++;
    }

    if (quitados === 0) return; // ninguno era mío

    if (asientosSeleccionados.length === 0) {
        deleteVentaProceso = 0;
        limpiar_campos_venta_pasaje();
        div_formVentaPasaje.classList.add("d-none");
        div_sin_asientos.classList.remove("d-none");
        renderTablaAsientos();
    } else {
        const cambioElPrimero = asientosSeleccionados[0].id !== primeroId;
        renderTablaAsientos({ recargarVista: cambioElPrimero });
    }

    Swal.fire({
        toast: true, position: "top-end", icon: "info", showConfirmButton: false, timer: 3500,
        title: data.motivo === "tiempo_agotado"
            ? "Tu selección expiró y el asiento fue liberado"
            : "El asiento fue liberado"
    });
}

    // Nueva función para controlar el scroll condicional
    function actualizarScrollAsientos(activarScroll) {
        const wrapper = document.getElementById('asientosSeleccionadosWrapper');
        if (!wrapper) return;

        if (activarScroll) {
            wrapper.classList.remove('sin-scroll');
            wrapper.classList.add('con-scroll');

            // Scroll automático al último asiento
            const container = wrapper.querySelector('.asientos-seleccionados-container');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 50);
            }
        } else {
            wrapper.classList.remove('con-scroll');
            wrapper.classList.add('sin-scroll');
        }
    }

    // Deselecciona un asiento por su índice en el array,
    // lo quita visualmente y libera el proceso en backend

async function deseleccionarAsiento(index) {
    const item = asientosSeleccionados[index];
    if (!item) return;

    const divAsiento = document.getElementById(item.id);
    const estado_asiento = divAsiento ? divAsiento.getAttribute("estado_asiento") : null;
    if (divAsiento) divAsiento.classList.remove("asiento-seleccionado");

    // 1) Estado local primero
    asientosSeleccionados.splice(index, 1);
    if (asientosSeleccionados.length === 0) {
        limpiar_campos_venta_pasaje();
        div_formVentaPasaje.classList.add("d-none");
        div_sin_asientos.classList.remove("d-none");
    }
    renderTablaAsientos();

    // 2) Servidor después
    if (estado_asiento != "RESERVADO") {
        try {
            const fData = new FormData();
            fData.set("id_programacion", item.id_programacion);
            fData.set("id_obj_vehiculo", item.id);
            await fetch(instance._URL_ + 'pasaje/delete_estadoProcesoAsiento', { method: 'POST', body: fData });
        } catch (error) {
            console.warn("No se pudo liberar el proceso del asiento:", error);
        }
    }
}

    /* Reservar individual */
    async function reservar_asiento(asientoData) {

        // Hacer un swal alert con un input que se llame tiempo_reserva para mandar a back es un campo opcional
        const { value: tiempo_reserva } = await Swal.fire({
            title: 'Tiempo de Reserva',
            input: 'select',
            inputLabel: 'Seleccione el tiempo de reserva',
            inputOptions: {
                '00:15': '15 minutos',
                '00:30': '30 minutos',
                '00:45': '45 minutos',
                '01:00': '1 hora',
                '01:30': '1 hora 30 minutos',
                '02:00': '2 horas',
            },
            inputPlaceholder: 'Seleccione una opción',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Reservar'
        });

        const index = asientosSeleccionados.findIndex(a => a.id === asientoData.id);

        let formData = new FormData();
        formData.set(
            "id_programacion",
            document.querySelector(".btn_selection_programacion.active")
                .closest("tr").querySelector(".id_programacion").value
        );

        if (tiempo_reserva) {
            formData.set("tiempo_reserva", tiempo_reserva);
        }

        let asiento_datos = [{
            id_obj_vehiculo: asientoData.id.trim(),
            piso: asientoData.piso,
            num_asiento: asientoData.text_obj.trim()
        }];

        formData.set("numeros_asientos", JSON.stringify(asiento_datos));

        try {
            const response = await fetch(instance._URL_ + 'pasaje/reservar_todos', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Error: ' + response.status);

            const data = await response.json();

            if (data.success) {
                instance.Toast.operacion_exitosa(data.message.message);

                if (index !== -1) {
                    await deseleccionarAsiento(index);
                }

            } else {
                instance.Toast.operacion_erronea(data.message);
            }

        } catch (error) {
            instance.Toast.operacion_erronea('Ha ocurrido un error al reservar los asientos.');
        }
    }

    /**
     * Maneja el evento de click en un asiento
     * @param {HTMLElement} asiento - Elemento del asiento clickeado
     */
    async function manejarClickAsiento(asiento) {
        if (!asiento) return;

        let seleccion_asiento = asiento.getAttribute('seleccion');
        let estado_asiento = asiento.getAttribute("estado_asiento");
        if (estado_asiento == 'RESERVADO' && !seleccion_asiento) return;

        // Evitar doble procesamiento
        if (asiento.dataset.procesando === "1") return;

        const asientoId = asiento.id;

        // Obtener programación
        let programacionId = null;
        const dataProgramacion = asiento.getAttribute('data-id-programacion');

        if (dataProgramacion != null) {
            programacionId = dataProgramacion;
        } else {
            const btnActive = document.querySelector(".btn_selection_programacion.active");
            if (btnActive) {
                const fila = btnActive.closest("tr");
                if (fila) {
                    const inputProg = fila.querySelector(".id_programacion");
                    if (inputProg) {
                        programacionId = inputProg.value;
                    }
                }
            }
        }

        if (!programacionId) return;

        // --- Toggle ---
        const indexExistente = asientosSeleccionados.findIndex(function (a) {
            return a.id === asientoId;
        });

        if (indexExistente !== -1) {
            deseleccionarAsiento(indexExistente);
            return;
        }

        // Obtener data_obj seguro
        const dataObj = asiento._data_obj ? asiento._data_obj : null;

        if (dataObj && dataObj.estado_asiento === 'PROCESO_WEB') {
            Swal.fire({
                title: 'Atención!',
                text: 'Este asiento está en proceso de venta web, dejar que culmine',
                icon: 'warning',
            });
            return;
        }

        asiento.dataset.procesando = "1";
        asiento.classList.add("asiento-seleccionado");

        // Obtener precio
        let precio = '';

        const btnActive = document.querySelector(".btn_selection_programacion.active");
        if (btnActive) {
            const fila = btnActive.closest("tr");
            if (fila && dataObj && dataObj.tp_asiento) {
                const inputPrecio = fila.querySelector(".precio_" + dataObj.tp_asiento);
                if (inputPrecio && inputPrecio.value != null) {
                    precio = inputPrecio.value;
                }
            }
        }

        // Set proceso
        if (id_asiento_selected) id_asiento_selected.value = asientoId;

        let formdata = new FormData();
        formdata.set("id_programacion", programacionId);
        formdata.set("id_obj_vehiculo", asientoId);
        formdata.set("estado_venta", '');

        try {
            if (estado_asiento != "RESERVADO") {
                const response = await fetch(instance._URL_ + 'pasaje/set_estadoProcesoAsiento', {
                    method: 'POST',
                    body: formdata
                });

                if (!response.ok) throw new Error(response.status);

                const data = await response.json();

                if (data.success) {

                    deleteVentaProceso = 0;

                    if (data.desbloquear) {

                        const result = await Swal.fire({
                            title: 'Atención',
                            text: data.message + ' por ' + data.vendedor,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Desbloquear',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#2a9d8f',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                        });

                        if (result.isConfirmed) {

                            let fData = new FormData();
                            fData.set("id_programacion", programacionId);
                            fData.set("id_obj_vehiculo", asientoId);

                            const resp2 = await fetch(instance._URL_ + 'pasaje/desbloquear_asiento', {
                                method: 'POST',
                                body: fData
                            });

                            const data2 = await resp2.json();

                            if (data2.success) {
                                instance.Toast.operacion_exitosa(data2.message);
                                asiento.dataset.procesando = "0";
                                manejarClickAsiento(asiento);
                                return;
                            } else {
                                instance.Toast.operacion_erronea(data2.message);
                                asiento.classList.remove("asiento-seleccionado");
                            }

                        } else {

                            let fData = new FormData();
                            fData.set("id_programacion", programacionId);
                            fData.set("id_obj_vehiculo", asientoId);

                            await fetch(instance._URL_ + "pasaje/delete_estadoProcesoAsiento", {
                                method: 'POST',
                                body: fData
                            });

                            asiento.classList.remove("asiento-seleccionado");
                            asiento.dataset.procesando = "0";

                            manejarClickAsiento(asiento);
                            return;
                        }

                    } else {
                        Swal.fire({
                            title: 'Atencion',
                            text: data.message + ' por el vendedor ' + data.vendedor,
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            showCancelButton: false,
                            confirmButtonText: 'Vender otro asiento',
                            confirmButtonColor: '#2a9d8f',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $("#modal_pasaje").modal("hide")
                                show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value)
                            }
                        })
                    }

                } else {

                    deleteVentaProceso = 1;

                    asientosSeleccionados.push({
                        id: asientoId,
                        id_programacion: programacionId,
                        id_venta: asiento.getAttribute("id_venta"),
                        text_obj: dataObj ? dataObj.text_obj : null,
                        piso: dataObj ? dataObj.piso : null,
                        tp_asiento: dataObj ? dataObj.tp_asiento : null,
                        precio: precio
                    });

                    renderTablaAsientos();
                }
            } else {
                deleteVentaProceso = 1;

                asientosSeleccionados.push({
                    id: asientoId,
                    id_programacion: programacionId,
                    id_venta: asiento.getAttribute("id_venta"),
                    text_obj: dataObj ? dataObj.text_obj : null,
                    piso: dataObj ? dataObj.piso : null,
                    tp_asiento: dataObj ? dataObj.tp_asiento : null,
                    precio: precio
                });

                renderTablaAsientos();
            }

            $("#cliente").focus();

        } catch (error) {

            instance.Toast.operacion_erronea(error.message);
            asiento.classList.remove("asiento-seleccionado");

        } finally {

            asiento.dataset.procesando = "0";
        }
    }


    /**
     * Carga en tu sección de venta los datos del primer asiento del array
    */
    function cargarVistaVentaPrimerAsiento() {
        if (asientosSeleccionados.length === 0) {
            div_formVentaPasaje.classList.add("d-none");
            div_sin_asientos.classList.remove("d-none");
            return;
        } else {
            div_formVentaPasaje.classList.remove("d-none");
            div_sin_asientos.classList.add("d-none");
        };

        const primero = asientosSeleccionados[0];
        // Setear los campos de tu sección de venta (ajusta los selectores a los tuyos)
        if (!serie_venta.value) {
            $("#tp_comprobante").val("3").trigger("change");
        }

        destino_pasajero.value = destino_pasajero_seleccionado
        precio_venta.focus();

        id_asiento_selected.value = primero.id;
        precio_venta.value = primero.precio;
        num_asiento.textContent = primero.text_obj
        num_piso_venta.textContent = primero.piso

        if (primero.id_venta && primero.id_venta !== 'undefined') {
            id_ventaPasaje.value = primero.id_venta
        }

        // Si el primer asiento ya tiene venta, cargar sus datos
        if (primero.id_venta && primero.id_venta !== 'undefined') {
            consultar = 2;
            valor_op = 1;
        } else {
            valor_op = 0;
            // Puedes limpiar los campos de venta aquí si lo necesitas
        }
    }

    function siguienteAsientoTrasVenta() {
        if (asientosSeleccionados.length === 0) {
            div_formVentaPasaje.classList.add("d-none");
            div_sin_asientos.classList.remove("d-none");
            actualizarScrollAsientos(false);
            return;
        }

        // Quitar el asiento recién vendido (siempre es el primero)
        const vendido = asientosSeleccionados.shift(); // elimina índice 0

        if (asientosSeleccionados.length === 0) {
            div_formVentaPasaje.classList.add("d-none");
            div_sin_asientos.classList.remove("d-none");
        }

        // Quitar el estilo de seleccionado del elemento DOM
        const elementoDOM = document.getElementById(vendido.id);
        if (elementoDOM) elementoDOM.classList.remove("asiento-seleccionado");

        // Re-renderizar — esto ya llamará a cargarVistaVentaPrimerAsiento internamente
        renderTablaAsientos();
    }


    let originalOptions = [];

    // Store initial options
    $(document).ready(function () {
        $("#terminal_destino option").each(function () {
            originalOptions.push({
                value: $(this).val(),
                text: $(this).text()
            });
        });
    });

    $("#terminal_origen").change(function (e) {
        e.preventDefault();
        const selectedValue = $(this).val();

        // Reset destination options
        $("#terminal_destino").empty();

        // Add all options except selected
        originalOptions.forEach(option => {
            if (option.value !== selectedValue) {
                $("#terminal_destino").append(
                    `<option value="${option.value}">${option.text}</option>`
                );
            }
        });
    });

    $("#cliente_id").change(function () {
        console.log(tp_comprobante.value)
        if (tp_comprobante.value == '2') {

            const id_cliente = $(this).val();
            const fila = document.querySelector(".btn_selection_programacion.active");
            if (!fila) return;
            const id_programacion = fila.closest("tr").querySelector(".id_programacion").value;
            const formData = new FormData();
            formData.append("id_pasajero", id_cliente);
            formData.append("id_programacion", id_programacion);

            fetch(instance._URL_ + "pasaje/revisar_pasajero", {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error: " + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Información',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            return false;
        }
    });

    $("#pasajero_id").change(function () {
        const id_pasajero = $(this).val();

        const fila = document.querySelector(".btn_selection_programacion.active");
        if (!fila) return;

        const id_programacion = fila.closest("tr").querySelector(".id_programacion").value;

        const formData = new FormData();
        formData.append("id_pasajero", id_pasajero);
        formData.append("id_programacion", id_programacion);

        fetch(instance._URL_ + "pasaje/revisar_pasajero", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error: " + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error(error);
            });

    });

    $("#estado_venta").change(function (e) {
        tp_comprobante.parentElement.classList.remove("d-none")
        serie_venta.parentElement.classList.remove("d-none")
        //parent_pagos.classList.remove("d-none") Es el parent del acordeaon borrado
        cod_operacion.parentElement.classList.add("d-none")
        switch (estado_venta.value) {
            case "RESERVADO":
            case "VENTA_WEB":
                tp_comprobante.parentElement.classList.add("d-none")
                serie_venta.parentElement.classList.add("d-none")
                pasajero_Parent.classList.add("d-none")
                //parent_pagos.classList.add("d-none")
                if (estado_venta.value == "VENTA_WEB") {
                    cod_operacion.parentElement.classList.remove("d-none")
                }
                break;
            case "VENDIDO":
                button_save.classList.remove("d-none")
                if (cod_operacion.value) {
                    cod_operacion.parentElement.classList.remove("d-none")
                }
                break;
            default:
                break;
        }
    });

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
                        selectElement.insertAdjacentHTML('beforeend', '<option value="">Seleccione</option>')
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

    let solicitudSerie = 0;

    $("#tp_comprobante").change(async function (e) {
        e.preventDefault();

        const tipoComprobante = tp_comprobante.value;
        const tipoDocumento = tipo_documento_client.value;
        const solicitudActual = ++solicitudSerie;

        serie_venta.innerHTML = `
        <option value="">Cargando serie...</option>
       `;
        serie_venta.disabled = true;

        ["1", "3"].includes(tipoComprobante)
            ? pasajero_Parent.classList.remove("d-none")
            : pasajero_Parent.classList.add("d-none");

        if (tipoComprobante == '1' && tipoDocumento && tipoDocumento != '6') {

            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: 'No puede emitir una FACTURA, ingrese un RUC'
            }).then(() => {
                limpiar_cliente();
                setTimeout(() => $('#cliente').focus(), 0);
            });

            serie_venta.innerHTML = `
            <option value="">Seleccione una serie</option>
        `;

            serie_venta.disabled = true;

            return;
        }

        const formData = new FormData();
        formData.set('tp_comprobante', tipoComprobante);

        try {

            const response = await fetch(
                instance._URL_ + "pasaje/get_serieForTpComprobante",
                {
                    method: "POST",
                    body: formData
                }
            );

            if (!response.ok) {
                throw new Error("Error al obtener la serie");
            }

            const data = await response.json();

            if (solicitudActual !== solicitudSerie) {
                return;
            }

            if (tp_comprobante.value != tipoComprobante) {
                return;
            }

            serie_venta.innerHTML = "";

            if (data.success) {

                serie_venta.insertAdjacentHTML(
                    'beforeend',
                    `<option value="${data.message.id_serie}">
                    ${data.message.serie}
                </option>`
                );

                serie_venta.disabled = false;

            } else {

                serie_venta.innerHTML = `
                <option value="">No hay serie disponible</option>
            `;

                serie_venta.disabled = true;
            }

        } catch (error) {

            console.error("Error obteniendo serie:", error);

            if (solicitudActual !== solicitudSerie) {
                return;
            }

            serie_venta.innerHTML = `
            <option value="">Error al obtener serie</option>
             `;

            serie_venta.disabled = true;

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener la serie del comprobante.'
            });
        }
    });

    function limpiar_cliente() {
        cliente_id.value = '';
        tipo_documento_client.value = '';
        $('#cliente').val('');
    }

    let operacion = document.querySelector('#operacion');
    let titulo = document.getElementById('titulo');
    $(".open_modal_pasajero").click(function (e) {
        titulo.textContent = "NUEVO PASAJERO";
        operacion.value = 2;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_nino").click(function (e) {
        titulo.textContent = "NUEVO PASAJERO";
        operacion.value = 3;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_cliente").click(function (e) {
        titulo.textContent = "NUEVO CLIENTE";
        operacion.value = 1;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    // Mostrar Data de la venta
    const showDataVenta = async (id_venta) => {
        let formData = new FormData();
        formData.set("id_venta", id_venta);

        try {
            const response = await fetch(instance._URL_ + "pasaje/getDataVenta", {
                method: "POST",
                body: formData
            });

            if (!response.ok) throw new Error("Error al consultar la venta");

            const data = await response.json();

            if (!data.success) return;

            const venta_datos = data.message;
            const venta = venta_datos.venta;
            const dt = venta_datos.dt_venta;

            const idTpComprobante = Number(venta.id_tp_comprobante);
            const envioSunat = Number(venta.envio_sunat);
            const permisoAnularC = Number(instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE);
            const permisoAnularN = Number(instance.CONSTS.PERMISOS.P_ANULAR_NOTAVENTA);
            const permisoPostponer = Number(instance.CONSTS.PERMISOS.P_POSPONER_PASAJE);
            const permisoCambiarAsiento = Number(instance.CONSTS.PERMISOS.P_CAMBIAR_ASIENTO);
            const permisoDesbloquearReservado = Number(instance.CONSTS.PERMISOS.P_DESBLOQUEAR_RESERVADO);

            // ==============================
            // LIMPIAR ESTADOS ANTERIORES
            // ==============================
            button_save.classList.remove("d-none");

            button_comprobante.classList.add("d-none");
            button_anular.classList.add("d-none");
            button_anular.setAttribute("disabled", true);

            $("#button_aplicarPostponer").addClass("d-none");
            $("#button_cambiarA").addClass("d-none");

            $("#div_nino_info").addClass("d-none");
            $("#div_contacto_info").addClass("d-none");
            $("#div_cupon").addClass("d-none");
            $("#div_egresos").addClass("d-none");

            $("#venta_cod_operacion").text("");
            $("#venta_cupon").text("");
            $("#venta_descuento").text("");

            // ==============================
            // ESTADO DE PROGRAMACIÓN
            // ==============================
            const btnActive = document.querySelector(".btn_selection_programacion.active");

            const pro_estado = btnActive
                ? btnActive.closest("tr").querySelector(".estado_programacion").value
                : 0;

            // ==============================
            // DATA PRINCIPAL
            // ==============================
            id_ventaPasaje.value = venta.id_venta;

            button_cambiarA.setAttribute("data-id_venta", venta.id_venta);
            button_aplicarPostponer.setAttribute("data-id_venta", venta.id_venta);

            $("#num_asiento_venta").text(dt.num_asiento || "");
            $("#destino_bajada").text(dt.destino || "");
            $("#origen_embarque").text(dt.origen || "");
            $("#n_piso_venta").text(dt.piso ? `${dt.piso}° Piso` : "");
            $("#venta_tp_comprobante").text(venta.tp_comprobante || "");
            $("#badge_estado").text(dt.estado_asiento || "");
            $("#venta_precio").text("S/ " + (dt.precio || "0.00"));

            $("#venta_num_docu").text(venta.num_docu || "");
            $("#venta_nombres_cliente").text(venta.nombres_cliente || "");

            $("#venta_pasajero_num").text(dt.pasajero_num || "");
            $("#venta_nombres_pasajero").text(dt.nombres_pasajero || "");
            if (dt.obs_pasajero && dt.obs_pasajero !== '') {
                $("#div_obs_pasajero").removeClass('d-none');
                $("#venta_obs_pasajero").text(dt.obs_pasajero || "");
            } else {
                $("#div_obs_pasajero").addClass('d-none');
            }

            $("#venta_nino_num").text(dt.nino_num || "");
            $("#venta_nombres_nino").text(dt.nombres_nino || "");
            $("#venta_motivo_nino").text(dt.motivo || "");

            $("#venta_medio_pago").text(venta.medio_pago || "");
            $("#venta_email").text(venta.email_cliente || "");
            $("#venta_celular").text(venta.celular_cliente || "");
            $("#venta_caja_chica").text(venta.caja_chica || "");
            $("#venta_forma_pago").text(venta.forma_pago || "");

            // ==============================
            // CUPÓN
            // ==============================
            if (venta.cupon) {
                $("#venta_cupon").text(venta.cupon);
                $("#venta_descuento").text(venta.monto_cupon || "0.00");
                $("#div_cupon").removeClass("d-none");
            }

            // ==============================
            // EGRESOS
            // ==============================
            const llenarTablaEgresos = (egresos = []) => {
                const tbody = document.querySelector("#tabla_egresos tbody");
                const totalEl = document.getElementById("total_egresos");

                tbody.innerHTML = "";

                if (!Array.isArray(egresos) || egresos.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align:center; color:#999;">
                            Sin egresos
                        </td>
                    </tr>
                `;
                    totalEl.textContent = "0.00";
                    return;
                }

                let total = 0;

                egresos.forEach(egreso => {
                    const comprobante = `${egreso.serie || ""}-${egreso.correlativo || ""}`;
                    const monto = parseFloat(egreso.monto) || 0;
                    const concepto = egreso.concepto || "";

                    total += monto;

                    tbody.insertAdjacentHTML("beforeend", `
                    <tr>
                        <td>${comprobante}</td>
                        <td>${monto.toFixed(2)}</td>
                        <td>${concepto}</td>
                    </tr>
                `);
                });

                totalEl.textContent = total.toFixed(2);
            };

            if (Array.isArray(venta_datos.egresos) && venta_datos.egresos.length > 0) {
                llenarTablaEgresos(venta_datos.egresos);
                $("#div_egresos").removeClass("d-none");
            } else {
                llenarTablaEgresos([]);
                $("#div_egresos").addClass("d-none");
            }

            // ==============================
            // CONTACTO
            // ==============================
            if (venta.email_cliente || dt.estado_asiento === "VENTA_WEB") {
                $("#div_contacto_info").removeClass("d-none");
            }

            // ==============================
            // DATOS DE COMPROBANTE
            // ==============================
            if (dt.estado_asiento === "VENTA_WEB" || dt.estado_asiento === "VENDIDO") {
                $("#venta_serie_correlativo").text(`${venta.serie || ""} - ${venta.correlativo || ""}`);
                $("#venta_fecha_emision").text(venta.fecha_emision || "");
                $("#venta_nombresU").text(venta.nombresU || "");
                $("#venta_terminalN").text(venta.terminalN || "");
            }

            // ==============================
            // ESTADO VENDIDO / RESERVADO
            // ==============================
            if (dt.estado_asiento === "VENDIDO") {
                button_save.classList.add("d-none");
                button_comprobante.classList.remove("d-none");

                const tipoComprobanteUrl = instance.CONSTS.TIPO_COMPROBANTE_FOR_ID[idTpComprobante];
                const urlImpresion = instance.CONSTS.URL.IMPRESION_PASAJE[tipoComprobanteUrl];

                button_comprobante.href = `${instance._URL_}${urlImpresion}${venta.id_venta}/3`;

                if (Number(dt.c_nino) === 1) {
                    $("#div_nino_info").removeClass("d-none");
                }

            } else if (dt.estado_asiento === "RESERVADO") {
                button_save.classList.add("d-none");
            }

            // ==============================
            // CÓDIGO DE OPERACIÓN
            // ==============================
            if (venta.cod_operacion) {
                $("#venta_cod_operacion").text(venta.cod_operacion);
            }

            // ==============================
            // BOTÓN ANULAR
            // ==============================
            if (Number(pro_estado) === 1) {
                button_anular.classList.remove("d-none");

                const esComprobanteElectronico = [1, 3].includes(idTpComprobante);
                const esNotaVenta = idTpComprobante === 2;

                if (esComprobanteElectronico && envioSunat === 1 && permisoAnularC === 1) {
                    button_anular.removeAttribute("disabled");

                } else if (esNotaVenta && permisoAnularN === 1) {
                    button_anular.removeAttribute("disabled");

                } else {
                    button_anular.setAttribute("disabled", true);
                }
            }

            // ==============================
            // PERMISOS
            // ==============================
            if (permisoPostponer === 1) {
                $("#button_aplicarPostponer").removeClass("d-none");
            }

            if (permisoCambiarAsiento === 1) {
                $("#button_cambiarA").removeClass("d-none");
            }

            return venta;

        } catch (error) {
            console.error(error);
            if (instance.Toast && instance.Toast.operacion_error) {
                instance.Toast.operacion_error("No se pudo obtener la información de la venta");
            }
        }
    };

    // Función para marcar abordaje
    async function marcarAbordaje(idDtVenta, idVenta) {
        try {
            const formData = new FormData();
            formData.append('id_dt_venta', idDtVenta);
            formData.append('id_venta', idVenta);

            const response = await fetch(instance._URL_ + 'pasaje/marcarAbordaje', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                instance.Toast.operacion_exitosa('Abordaje marcado: ' + result.abordaje);
                // Actualizar la vista para mostrar que ya abordó
                actualizarVistaAbordaje(idDtVenta);
            } else {
                instance.Toast.operacion_erronea(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            instance.Toast.operacion_erronea('Error al marcar abordaje');
        }
    }

    // ANULAR
    const anular = (id_venta = null, id_tp_comprobante = null) => {
        Swal.fire({
            title: '¿Esta seguro que desea anular la venta?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Anular!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Anulando la venta",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                });
                Swal.showLoading()
                let formData = new FormData()
                formData.set("id_ventaPasaje", id_venta ? id_venta : id_ventaPasaje.value)
                formData.set("tp_comprobante", id_tp_comprobante ? id_tp_comprobante : tp_comprobante.value)

                fetch(instance._URL_ + "pasaje/anular_venta", {
                    method: "POST",
                    body: formData
                })
                    .then(response => { if (response.ok) return response.json() })
                    .then(data => {
                        if (data.success) {
                            Swal.close()
                            // Anulando la venta
                            if ((tp_comprobante.value == 1 || tp_comprobante.value == 3)) {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    text: data.message_sunat,
                                })
                            } else {
                                Swal.fire({
                                    title: "Exito",
                                    text: data.message,
                                    icon: "success"
                                });
                                //instance.Toast.operacion_exitosa(data.message)
                            }
                            if (document.querySelector(".btn_selection_programacion.active")) {
                                clear_vehiculo();
                                show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value, window.innerWidth < 992)
                                const idProgramacion = document.querySelector(".btn_selection_programacion.active")
                                    .closest("tr").querySelector(".id_programacion").value;
                                actualizarResumenes(idProgramacion, true);
                            }
                            instance.Datatable.reloadTable(table_comprobante)
                            instance.Datatable.reloadTable(table_notaVenta)
                            !id_venta ? $("#modal_pasaje").modal("toggle") : null
                        } else {
                            Swal.close()
                            Swal.fire({
                                title: "Atencion!",
                                text: data.message,
                                icon: "info"
                            });
                        }
                    })
                    .catch(error => {
                        instance.Toast.operacion_erronea(error.message)
                    })
            }
        })

    }

    document.getElementById('button_comprobante').addEventListener('click', function (event) {
        event.preventDefault();
        fetch(this.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: {
                tp_impresion: 3
            }
        })
            .then(response => {
                if (!response.ok) throw new Error(response.statusText);

                const contentType = response.headers.get('Content-Type');

                if (contentType.includes('application/pdf')) {
                    return response.blob().then(blob => ({ type: 'pdf', blob }));
                } else if (contentType.includes('application/json')) {
                    return response.json().then(data => ({ type: 'json', data }));
                } else {
                    throw new Error("Tipo de contenido no esperado");
                }
            })
            .then(result => {
                if (result.type === 'pdf') {
                    var url = URL.createObjectURL(result.blob);

                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = url;

                    document.body.appendChild(iframe);
                    iframe.onload = function () {
                        iframe.contentWindow.print();
                    };
                } else if (result.type === 'json') {
                    instance.Toast.operacion_exitosa(result.data.message)
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
            });
    });

    const reenviar = (id_venta = null, id_tp_comprobante = null) => {
        Swal.fire({
            title: '¿Esta seguro de reenviar el comprobante?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Reenviar!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData()
                formData.set("id_ventaPasaje", id_venta ? id_venta : id_ventaPasaje.value)
                formData.set("tp_comprobante", id_tp_comprobante ? id_tp_comprobante : tp_comprobante.value)

                fetch(instance._URL_ + "pasaje/reenviar_venta", {
                    method: "POST",
                    body: formData
                })
                    .then(response => {
                        if (response.ok)
                            return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message.message)
                            instance.Datatable.reloadTable(table_comprobante)
                        } else {
                            instance.Toast.operacion_erronea(data.message.message)
                        }
                    })
                    .catch(error => {
                        // Mostrar detalles adicionales del error
                        const errorMessage = error.message || 'Error desconocido';
                        instance.Toast.operacion_erronea(errorMessage);
                        console.error('Error en la solicitud:', error);
                    });
            }
        })
    }

    button_anular.addEventListener("click", (event) => anular())

    $('#table_comprobante tbody').on('click', '.btnAnular', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        anular(data.id_venta, data.id_tp_comprobante)
    });

    $('#table_notaVenta tbody').on('click', '.btnAnular', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        anular(data.id_venta)
    });

    $('#table_comprobante tbody').on('click', '.btnReenviar', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        reenviar(data.id_venta, data.id_tp_comprobante, data.estado)
    });

    $(".cancelar_postponer").click(function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Esta seguro que desea cancelar la venta a postponer?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Cancelar!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#modal_pasaje').modal('hide')
                alert_postponer.classList.add("d-none")
                clear_vehiculo()
                show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value, window.innerWidth < 992)
                instance.Toast.operacion_exitosa('Venta por postponer CANCELADA')
            }
        })
    });

    button_aplicarPostponer.addEventListener("click", (event) => {
        let formData = new FormData();
        const idVenta = button_aplicarPostponer.getAttribute('data-id_venta');
        formData.set("id_venta", idVenta);
        Swal.fire({
            title: '¿Desea postponer esta venta?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Posponer!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('loader').classList.remove('d-none');
                fetch(instance._URL_ + "pasaje/set_postponerPasaje", {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status)
                        return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message.message)
                            $('#modal_pasaje').modal('toggle')
                            instance.Datatable.reloadTable(table_comprobante)
                            instance.Datatable.reloadTable(table_notaVenta)
                            instance.Toast.operacion_exitosa('Venta POSPUESTA!!')
                        }
                    })
                    .catch(error => instance.Toast.operacion_erronea(error.message))
                    .finally(() => {
                        document.getElementById('loader').classList.add('d-none');
                    })
            }
        })
    })

    const modal_printComprobante = (title, link_pdf, estado_sunat = 0, message_sunat = "") => {
        let html_rspt_sunat = ''
        if (estado_sunat) {
            html_rspt_sunat = `<p class="fw-bolder fs-6">${message_sunat}</p>`
        }
        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            html: `
                <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
                    <i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>
                    <label></label>
                    <h5>${title}</h5>
                    <a data-link="${link_pdf}" class="d-flex flex-column text-decoration-none py-4 btn-print" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
                        <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                        <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
                    </a>
                    <section>
                        ${html_rspt_sunat}                        
                        <p class="text-secondary">Enviar comprobante por WhatsApp</p>
                        <div class="input-group mb-3">
                            <span class="input-group-text py-0">+51</span>
                            <input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">
                            <button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></buton>
                        </div>
                    </section>
                </div>
        `,
            confirmButtonText: 'Continuar'
        })
        instance.Validate.allowInputNum(["#celular_clienteWsp"])
        let celular_clienteWsp = document.querySelector("#celular_clienteWsp")
        let btn_sendComprobanteWsp = document.querySelector("#btn_sendComprobanteWsp")
        btn_sendComprobanteWsp.addEventListener("click", (event) => {
            if (celular_clienteWsp.value.length == 9) {
                window.open(instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP.replace('$number$', `+51${celular_clienteWsp.value}`).replace('$message$', instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE.replace('$link_comprobante$', link_pdf)), "_blank")
            } else {
                instance.Toast.operacion_erronea('Por favor ingrese un número de celular valido')
            }
        })
    }

    function imprimir_comprobante(link, tipoImpresora = 'escritorio') {
        fetch(link, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {

                console.log(response)
                if (!response.ok) throw new Error(response.statusText);

                const contentType = response.headers.get('Content-Type');

                if (contentType.includes('application/pdf')) {
                    return response.blob().then(blob => ({ type: 'pdf', blob }));
                } else if (contentType.includes('application/json')) {
                    return response.json().then(data => ({ type: 'json', data }));
                } else {
                    throw new Error("Tipo de contenido no esperado");
                }
            })
            .then(result => {
                if (result.type === 'pdf') {
                    if (tipoImpresora === 'bluetooth') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Formato incompatible',
                            text: 'Las impresoras Bluetooth requieren formato de texto. Contacta al administrador.',
                        });
                    } else {
                        imprimirPC(result.blob);
                    }
                } else if (result.type === 'json') {
                    if (result.data.data.tipo_impresora === 'bluetooth') {
                        imprimirBluetooth(result.data);
                        console.log(result.data)
                    } else {
                        imprimirPCRapida(result.data);
                    }
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
            });
    }
    function imprimirPC(blob) {

        const url = URL.createObjectURL(blob);

        const iframe = document.createElement('iframe');

        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';

        iframe.style.width = '1px';
        iframe.style.height = '1px';

        iframe.style.border = '0';

        iframe.src = url;

        document.body.appendChild(iframe);

        iframe.onload = function () {

            setTimeout(() => {

                iframe.contentWindow.focus();

                iframe.contentWindow.print();

            }, 500);

        };

        // Limpiar después de bastante tiempo
        setTimeout(() => {

            if (iframe.parentNode) {
                iframe.parentNode.removeChild(iframe);
            }

            URL.revokeObjectURL(url);

        }, 60000);
    }

    // Función para impresión PC rápida (tu método actual)
    function imprimirPCRapida(data) {
        let iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = data.link;
        document.body.appendChild(iframe);

        setTimeout(function () {
            document.body.removeChild(iframe);
        }, 5000);
    }

    function cargarResumenVendedores(id_programacion, forzarRecarga = false) {
        if (!id_programacion || id_programacion <= 0) {
            if (resumenVendedoresContainer) {
                resumenVendedoresContainer.classList.add('d-none');
            }
            return;
        }

        if (forzarRecarga || !cuerpoTablaVendedores || cuerpoTablaVendedores.children.length === 0) {
            if (cuerpoTablaVendedores) {
                cuerpoTablaVendedores.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Cargando datos...</td></tr>';
            }
        }

        let formData = new FormData();
        formData.set('id_programacion', id_programacion);
        formData.set('_t', Date.now());

        fetch(instance._URL_ + 'pasaje/get_resumenVendedores', {
            method: 'POST',
            body: formData,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderizarTablaVendedores(data.data);
                    if (resumenContainer) {
                        resumenContainer.classList.remove('d-none');
                    }
                    if (resumenVendedoresContainer) {
                        resumenVendedoresContainer.classList.remove('d-none');
                    }
                    if (tabVendedoresBtn && !tabVendedoresBtn.classList.contains('active')) {
                        tabVendedoresBtn.classList.add('active');
                        tabDestinosBtn.classList.remove('active');
                        resumenDestinosContainer.style.display = 'none';
                        resumenVendedoresContainer.style.display = 'block';
                    }
                } else {
                    console.error('Error al cargar vendedores:', data.message);
                    if (cuerpoTablaVendedores) {
                        cuerpoTablaVendedores.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">' +
                            (data.message || 'Error al cargar datos') + '</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                if (cuerpoTablaVendedores) {
                    cuerpoTablaVendedores.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Error de conexión al servidor</td></tr>';
                }
            });
    }

    function renderizarTablaVendedores(vendedores) {
        if (!cuerpoTablaVendedores) return;

        cuerpoTablaVendedores.innerHTML = '';

        // Objeto para acumular totales
        const totales = {
            vendidos: 0,
            reservados: 0,
            pospuestos: 0,
            nota_venta: 0,
            boleta: 0,
            factura: 0,
            monto: 0,
        };

        // Obtener el tipo de usuario de la sesión (desde el input hidden)
        const idTpUsuarioSesion = parseInt(document.getElementById("id_tp_usuario_sesion").value);
        const esAdminSoporte = (idTpUsuarioSesion === 1 || idTpUsuarioSesion === 2);

        if (vendedores && vendedores.length > 0) {
            vendedores.forEach(v => {
                // Convertir a números para sumar correctamente
                const vendidos = parseInt(v.total_vendidos) || 0;
                const reservados = parseInt(v.total_reservados) || 0;
                const pospuestos = parseInt(v.total_pospuestos) || 0;
                const notaVenta = parseInt(v.total_nota_venta) || 0;
                const boleta = parseInt(v.total_boleta) || 0;
                const factura = parseInt(v.total_factura) || 0;
                const monto = parseFloat(v.monto_total) || 0;

                // Acumular totales
                totales.vendidos += vendidos;
                totales.reservados += reservados;
                totales.pospuestos += pospuestos;
                totales.nota_venta += notaVenta;
                totales.boleta += boleta;
                totales.factura += factura;
                totales.monto += monto;

                // Determinar el color del badge según el tipo de usuario
                let badgeColor = 'secondary';
                let badgeClass = '';

                // Asignar colores según el tipo de usuario
                switch (v.id_tp_usuario) {
                    case 2: // ADMINISTRADOR
                        badgeColor = 'danger';
                        break;
                    case 3: // VENDEDOR
                        badgeColor = 'primary';
                        break;
                    case 14: // COMISIONISTA 1
                    case 15: // COMISIONISTA 2
                        badgeColor = 'success';
                        break;
                    default:
                        badgeColor = 'secondary';
                }

                // Crear badge con clase de Bootstrap
                badgeClass = `badge bg-${badgeColor}`;
                let nombreVendedor = `${v.nombres || ''} ${v.apellidos || ''}`;

                // Solo mostrar enlace a liquidación si es ADMIN o SOPORTE
                let htmlNombre = `<a href="${v.link}" target="_blank"><span class="fw-bold">${nombreVendedor}</span></a>`;

                // Crear fila
                const fila = document.createElement('tr');
                fila.classList.add('small');
                fila.innerHTML = `
                <td>${htmlNombre}</td>
                <td class="text-center fw-bold text-success">${vendidos}</td>
                <td class="text-center text-warning fw-bold">${reservados}</td>
                <td class="text-center text-danger fw-bold">${pospuestos}</td>
                <td class="text-center">${notaVenta}</td>
                <td class="text-center">${boleta}</td>
                <td class="text-center">${factura}</td>
                <td class="text-end fw-bold">${monto.toFixed(2)}</td>
            `;

                // Agregar efecto hover
                fila.addEventListener('mouseenter', function () {
                    this.style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
                });
                fila.addEventListener('mouseleave', function () {
                    this.style.backgroundColor = '';
                });

                cuerpoTablaVendedores.appendChild(fila);
            });
        } else {
            // Mostrar mensaje si no hay datos
            const fila = document.createElement('tr');
            fila.innerHTML = '<td colspan="8" class="text-center py-4 text-muted">No hay vendedores disponibles para esta programación</td>';
            cuerpoTablaVendedores.appendChild(fila);
        }

        // Actualizar totales en el footer con formato
        if (totalVendidos) {
            totalVendidos.textContent = totales.vendidos;
            totalVendidos.classList.add('fw-bold');
        }
        if (totalReservados) {
            totalReservados.textContent = totales.reservados;
            totalReservados.classList.add('fw-bold', 'text-warning');
        }
        if (totalPospuestos) {
            totalPospuestos.textContent = totales.pospuestos;
            totalPospuestos.classList.add('fw-bold', 'text-danger');
        }
        if (totalNotaVenta) totalNotaVenta.textContent = totales.nota_venta;
        if (totalBoleta) totalBoleta.textContent = totales.boleta;
        if (totalFactura) totalFactura.textContent = totales.factura;
        if (totalMonto) {
            totalMonto.textContent = totales.monto.toFixed(2);
            totalMonto.classList.add('fw-bold');
        }

        if (resumenVendedoresContainer) {
            // Forzar un pequeño reflow para asegurar que el scroll se active
            resumenVendedoresContainer.style.display = 'none';
            resumenVendedoresContainer.offsetHeight;
            resumenVendedoresContainer.style.display = 'block';
        }
    }

    // Función para cambiar entre pestañas
    function cambiarTabResumen(tab) {
        if (tab === 'vendedores') {
            tabVendedoresBtn.classList.add('active');
            tabDestinosBtn.classList.remove('active');

            resumenDestinosContainer.style.display = 'none';
            resumenVendedoresContainer.style.display = 'block';
        } else {
            tabDestinosBtn.classList.add('active');
            tabVendedoresBtn.classList.remove('active');

            resumenVendedoresContainer.style.display = 'none';
            resumenDestinosContainer.style.display = 'block';

            // Cargar datos de destinos si hay una programación seleccionada
            const btnSeleccion = document.querySelector(".btn_selection_programacion.active");
            if (btnSeleccion) {
                const idProgramacion = btnSeleccion.closest("tr").querySelector(".id_programacion").value;
                cargarResumenDestinos(idProgramacion);
            }
        }
    }

    if (tabVendedoresBtn && tabDestinosBtn) {
        // Remover cualquier event listener previo
        tabVendedoresBtn.replaceWith(tabVendedoresBtn.cloneNode(true));
        tabDestinosBtn.replaceWith(tabDestinosBtn.cloneNode(true));

        // Volver a obtener las referencias después del clone
        const newTabVendedoresBtn = document.getElementById('tab_vendedores_btn');
        const newTabDestinosBtn = document.getElementById('tab_destinos_btn');

        if (newTabVendedoresBtn && newTabDestinosBtn) {
            newTabVendedoresBtn.addEventListener('click', () => cambiarTabResumen('vendedores'));
            newTabDestinosBtn.addEventListener('click', () => cambiarTabResumen('destinos'));

            // Actualizar las referencias globales
            window.tabVendedoresBtn = newTabVendedoresBtn;
            window.tabDestinosBtn = newTabDestinosBtn;
        }
    }

    // Función para cargar el resumen de destinos (simplificada)
    function cargarResumenDestinos(id_programacion, forzarRecarga = false) {
        if (!id_programacion || id_programacion <= 0) {
            if (cuerpoTablaDestinos) {
                cuerpoTablaDestinos.innerHTML = '<tr><td colspan="2" class="text-center py-4 text-muted">Seleccione una programación</td></tr>';
            }
            return;
        }

        if (forzarRecarga || !cuerpoTablaDestinos || cuerpoTablaDestinos.children.length === 0) {
            if (cuerpoTablaDestinos) {
                cuerpoTablaDestinos.innerHTML = '<tr><td colspan="2" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';
            }
        }

        let formData = new FormData();
        formData.set('id_programacion', id_programacion);
        formData.set('_t', Date.now()); // Evitar caché

        fetch(instance._URL_ + 'pasaje/get_resumenDestinos', {
            method: 'POST',
            body: formData,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderizarTablaDestinos(data.data, data.total_general);
                    if (resumenContainer) {
                        resumenContainer.classList.remove('d-none');
                    }
                } else {
                    console.error('Error al cargar destinos:', data.message);
                    if (cuerpoTablaDestinos) {
                        cuerpoTablaDestinos.innerHTML = '<tr><td colspan="2" class="text-center text-danger py-4">' +
                            (data.message || 'Error al cargar datos') + '</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                if (cuerpoTablaDestinos) {
                    cuerpoTablaDestinos.innerHTML = '<tr><td colspan="2" class="text-center text-danger py-4">Error de conexión</td></tr>';
                }
            });
    }

    // Función para renderizar la tabla de destinos (simplificada)
    function renderizarTablaDestinos(destinos, totalGeneral) {
        if (!cuerpoTablaDestinos) return;

        cuerpoTablaDestinos.innerHTML = '';

        if (!destinos || destinos.length === 0) {
            cuerpoTablaDestinos.innerHTML = '<tr><td colspan="2" class="text-center py-4 text-muted">No hay destinos disponibles para esta programación</td></tr>';
            return;
        }

        destinos.forEach(destino => {
            const vendidos = parseInt(destino.total_vendidos) || 0;

            const fila = document.createElement('tr');
            fila.innerHTML = `
            <td class="fw-bold" style="padding-left: 20px;">
                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                ${destino.nombre_destino}
            </td>
            <td class="text-center fw-bold text-success">${vendidos}</td>
        `;

            fila.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            });
            fila.addEventListener('mouseleave', function () {
                this.style.backgroundColor = '';
            });

            cuerpoTablaDestinos.appendChild(fila);
        });

        // Actualizar total general
        if (destinosTotalGeneral) {
            destinosTotalGeneral.textContent = totalGeneral || 0;
            destinosTotalGeneral.classList.add('fw-bold');
        }

        if (resumenDestinosContainer) {
            resumenDestinosContainer.style.display = 'none';
            resumenDestinosContainer.offsetHeight;
            resumenDestinosContainer.style.display = 'block';
        }
    }

    // Variable para controlar la última actualización
    let ultimaActualizacionVendedores = 0;
    let timeoutActualizacion = null;

    // Función unificada para actualizar ambos resúmenes con debounce
    function actualizarResumenes(id_programacion, forzar = false) {
        if (!id_programacion || id_programacion <= 0) {
            if (resumenContainer) {
                resumenContainer.classList.add('d-none');
            }
            return;
        }

        // Control de llamadas muy seguidas (debounce)
        const ahora = Date.now();
        if (!forzar && ahora - ultimaActualizacionVendedores < 500) {
            // Si ya hay un timeout pendiente, lo cancelamos
            if (timeoutActualizacion) {
                clearTimeout(timeoutActualizacion);
            }
            // Programamos la actualización para dentro de 500ms
            timeoutActualizacion = setTimeout(() => {
                ejecutarActualizacionResumenes(id_programacion);
            }, 500);
            return;
        }

        ejecutarActualizacionResumenes(id_programacion);
    }

    function ejecutarActualizacionResumenes(id_programacion) {
        ultimaActualizacionVendedores = Date.now();

        // Limpiar timeout si existe
        if (timeoutActualizacion) {
            clearTimeout(timeoutActualizacion);
            timeoutActualizacion = null;
        }

        // Mostrar el contenedor principal
        if (resumenContainer) {
            resumenContainer.classList.remove('d-none');
        }

        // Siempre actualizar vendedores (con un pequeño retraso para asegurar que la venta se haya registrado)
        setTimeout(() => {
            cargarResumenVendedores(id_programacion, true);
        }, 300);

        // Actualizar destinos solo si la pestaña está activa
        if (tabDestinosBtn && tabDestinosBtn.classList.contains('active')) {
            setTimeout(() => {
                cargarResumenDestinos(id_programacion, true);
            }, 300);
        }
    }

    // ========== FUNCIÓN DE IMPRESIÓN MEJORADA ==========

    // Función principal
    async function imprimirBluetooth(data) {
        try {
            await enviarTicketAImpresora(data);

            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Ticket enviado correctamente',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de Impresión',
                text: error.message,
            });
        }
    }

    // Ahora SOLO envía al celular
    async function enviarTicketAImpresora(data) {
        try {

            const ticketCompleto = generarTicketESCPOS(data.data);
            // Convertir string ESC/POS a bytes reales
            const encoder = new TextEncoder("iso-8859-1");
            const bytes = encoder.encode(ticketCompleto);

            // Convertir a base64
            let binary = '';
            bytes.forEach(b => binary += String.fromCharCode(b));
            const base64Ticket = btoa(binary);
            let formData = new FormData();
            formData.append("ticket", base64Ticket);
            let ip = data.data.ip_celular;

            if (!ip.startsWith("http")) {
                ip = "http://" + ip;
            }

            const response = await fetch(`${ip}/imprimir`, {
                method: 'POST',
                body: formData
            });
            if (!response.status === 200) {
                throw new Error('Error: ' + response.status);
            }

            const result = await response.text();
            console.log('Respuesta del puente:', result);

        } catch (error) {
            console.error('Error enviando ticket:', error);
            throw error;
        }
    }

    function decodificarHTML(html) {
        // Crear un elemento temporal para decodificar entidades HTML
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        let texto = txt.value;

        // Eliminar tags HTML básicos (como <b>, <i>, <strong>, etc.)
        texto = texto.replace(/<[^>]*>/g, '');

        // Limpiar espacios extras
        texto = texto.replace(/\s+/g, ' ').trim();

        return texto;
    }

    // ========== INICIALIZACIÓN AUTOMÁTICA ==========
    function generarTicketESCPOS(data) {
        const ESC = '\x1B';
        const GS = '\x1D';
        const LF = '\n';

        let ticket = '';

        // Función para dividir texto largo en múltiples líneas (32 caracteres para 58mm)
        function dividirTexto(texto, maxCaracteres = 32) {
            if (texto.length <= maxCaracteres) return [texto];

            const palabras = texto.split(' ');
            const lineas = [];
            let lineaActual = '';

            for (const palabra of palabras) {
                if ((lineaActual + (lineaActual ? ' ' : '') + palabra).length <= maxCaracteres) {
                    lineaActual += (lineaActual ? ' ' : '') + palabra;
                } else {
                    if (lineaActual) lineas.push(lineaActual);
                    lineaActual = palabra;
                }
            }
            if (lineaActual) lineas.push(lineaActual);
            return lineas;
        }

        function convertirNumeroALetras(numero) {
            const unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
            const decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];

            const num = Math.floor(numero);
            if (num < 10) return unidades[num] + ' SOLES';
            if (num < 100) return decenas[Math.floor(num / 10)] + ' ' + unidades[num % 10] + ' SOLES';
            return 'CANTIDAD EN LETRAS SOLES';
        }

        // === INICIALIZACIÓN ===
        ticket += ESC + '@'; // Reset impresora
        ticket += ESC + '!' + String.fromCharCode(5); // Reset formato
        ticket += ESC + '3' + String.fromCharCode(10); // Espaciado entre líneas reducido (10/216 pulgadas)
        ticket += GS + 'a' + String.fromCharCode(0); // Alineación por defecto
        ticket += GS + '!' + String.fromCharCode(0); // Tamaño carácter 1x1
        ticket += ESC + 'l' + String.fromCharCode(0); // Margen izquierdo 0
        ticket += GS + 'L' + String.fromCharCode(0) + String.fromCharCode(0); // Margen izquierdo global 0
        ticket += GS + 'W' + String.fromCharCode(128) + String.fromCharCode(1); // Ancho imprimible 384 puntos (58mm)
        ticket += ESC + '!' + String.fromCharCode(5); // Reset formato
        ticket += ESC + '3' + String.fromCharCode(10); // Espaciado entre líneas reducido (10/216 pulgadas)
        ticket += GS + 'a' + String.fromCharCode(0); // Alineación por defecto
        ticket += GS + '!' + String.fromCharCode(0); // Tamaño carácter 1x1
        ticket += ESC + 'l' + String.fromCharCode(0); // Margen izquierdo 0
        ticket += GS + 'L' + String.fromCharCode(0) + String.fromCharCode(0); // Margen izquierdo global 0
        ticket += GS + 'W' + String.fromCharCode(128) + String.fromCharCode(1); // Ancho imprimible 384 puntos (58mm)
        ticket += GS + 'a' + String.fromCharCode(0); // Alineación por defecto

        // === ENCABEZADO EMPRESA ===
        ticket += ESC + 'a' + String.fromCharCode(1); // Centrar
        ticket += '================================' + LF;

        // Datos de la empresa con negrita
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON

        // Dividir razón social si es muy larga
        const razonSocial = normalizarEscPos(data.EMISOR.empresa_razon_social);
        const razonSocialLineas = dividirTexto(razonSocial, 32);
        razonSocialLineas.forEach(linea => {
            ticket += linea + LF;
        });

        ticket += 'RUC: ' + data.EMISOR.empresa_ruc + LF;

        // Dividir dirección si es muy larga
        const direccionLineas = dividirTexto('DOM. FISCAL: ' + data.EMISOR.direccion, 32);
        direccionLineas.forEach(linea => {
            ticket += linea + LF;
        });

        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF
        // Frase de la empresa (si existe)
        if (data.EMISOR.frase_empresa) {
            // Decodificar entidades HTML y eliminar tags HTML
            const fraseTextoPlano = decodificarHTML(data.EMISOR.frase_empresa);
            const fraseLineas = dividirTexto(fraseTextoPlano, 32);

            fraseLineas.forEach(linea => {
                ticket += linea + LF;
            });
            ticket += LF; // Espacio adicional
        }
        // Separador
        ticket += '--------------------------------' + LF;

        // === DATOS DEL COMPROBANTE ===
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
        ticket += data.CABECERA.tp_comprobante + LF;
        const correlativoFormateado = String(data.CABECERA.correlativo).padStart(8, '0');
        ticket += data.CABECERA.serie + ' - ' + correlativoFormateado + LF;
        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF

        ticket += '--------------------------------' + LF;

        // === DATOS DEL CLIENTE/PASAJERO ===
        ticket += ESC + 'a' + String.fromCharCode(0); // Alinear izquierda

        if (data.tipo_impresion == 1) {
            ticket += data.CLIENTE.cliente_tp_docu + ': ' + data.CLIENTE.cliente_num_docu + LF;

            const tipoLabel = data.CLIENTE.cliente_tp_docu == "DNI" ? 'CLIENTE: ' : 'R. SOCIAL: ';
            const nombreCompleto = normalizarEscPos(tipoLabel + data.CLIENTE.cliente_nombres + ' ' + data.CLIENTE.cliente_apellidos);

            // Dividir nombre si es muy largo
            const nombreLineas = dividirTexto(nombreCompleto, 32);
            nombreLineas.forEach(linea => {
                ticket += linea + LF;
            });

            // Dividir dirección si es muy larga
            const direccionClienteLineas = dividirTexto('DIRECCION: ' + data.CLIENTE.cliente_direccion, 32);
            direccionClienteLineas.forEach(linea => {
                ticket += linea + LF;
            });

            if (data.CLIENTE.pasajero_nombres) {
                const fechaNacimiento = data.CLIENTE.pasajero_fecha_nacimiento;
                const edad = calcularEdad(fechaNacimiento);

                const nombrePasajero = normalizarEscPos('PASAJERO: ' + data.CLIENTE.pasajero_nombres + ' ' + data.CLIENTE.pasajero_apellidos);
                const pasajeroLineas = dividirTexto(nombrePasajero, 32);
                pasajeroLineas.forEach(linea => {
                    ticket += linea + LF;
                });

                const datosExtra = data.CLIENTE.pasajero_tp_docu + ': ' + data.CLIENTE.cliente_num_docu + ' Edad: ' + edad + ' ' + normalizarEscPos(data.CLIENTE.pasajero_nacionalidad);
                const datosExtraLineas = dividirTexto(datosExtra, 32);
                datosExtraLineas.forEach(linea => {
                    ticket += linea + LF;
                });
            }
        } else if (data.tipo_impresion == 2) {
            const fechaNacimiento = data.CLIENTE.cliente_fecha_nacimiento;
            const edad = fechaNacimiento && fechaNacimiento.split('-')[0] != "00"
                ? new Date().getFullYear() - parseInt(fechaNacimiento.split('-')[0])
                : '---';

            const nombrePasajero = normalizarEscPos('PASAJERO: ' + data.CLIENTE.cliente_nombres + ' ' + data.CLIENTE.cliente_apellidos);
            const pasajeroLineas = dividirTexto(nombrePasajero, 32);
            pasajeroLineas.forEach(linea => {
                ticket += linea + LF;
            });

            const datosExtra = data.CLIENTE.cliente_tp_docu + ': ' + data.CLIENTE.cliente_num_docu + ' Edad: ' + edad + ' ' + data.CLIENTE.cliente_nacionalidad;
            const datosExtraLineas = dividirTexto(datosExtra, 32);
            datosExtraLineas.forEach(linea => {
                ticket += linea + LF;
            });
        }

        // === SERVICIO DE TRANSPORTE ===
        ticket += ESC + 'a' + String.fromCharCode(1); // Centrar
        ticket += '--------------------------------' + LF;
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
        const servicioLineas = dividirTexto('SERVICIO DE TRANSPORTE DE PASAJEROS', 32);
        servicioLineas.forEach(linea => {
            ticket += linea + LF;
        });
        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF
        ticket += ESC + 'a' + String.fromCharCode(0); // Alinear izquierda
        ticket += 'Unid. Med: Servicio ' + ' Cant: 1' + LF;

        // === ORIGEN Y DESTINO ===
        const origenLineas = dividirTexto('ORIGEN: ' + data.CABECERA.terminal_origen.toUpperCase(), 32);
        origenLineas.forEach(linea => {
            ticket += linea + LF;
        });

        const destinoLineas = dividirTexto('DESTINO: ' + data.CABECERA.terminal_destino.toUpperCase(), 32);
        destinoLineas.forEach(linea => {
            ticket += linea + LF;
        });

        // === ASIENTO ===
        ticket += 'Asiento: ';
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
        ticket += data.ITEMS[0].num_asiento + ' - ' + data.ITEMS[0].piso.toString().trim() + LF;
        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF

        // === FECHA Y HORA DE VIAJE ===
        const fechaViaje = formatearFecha(data.CABECERA.programacion_fecha_salida);
        const horaViaje = formatearHora(data.CABECERA.programacion_hora_salida);

        ticket += 'Fecha-Hora Viaje:' + LF;
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
        ticket += fechaViaje + ' ' + horaViaje + LF;
        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF

        // === DATOS FINANCIEROS ===
        if (data.tipo_impresion == 1) {
            ticket += 'Exonerado: S/. ' + data.CABECERA.op_exonerada + LF;

            const gravedadLineas = dividirTexto('Gravado: S/. 0.00', 32);
            gravedadLineas.forEach(linea => {
                ticket += linea + LF;
            });

            const igvLineas = dividirTexto('IGV: S/. 0.00', 32);
            igvLineas.forEach(linea => {
                ticket += linea + LF;
            });

            ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
            ticket += 'IMPORTE: S/. ' + data.CABECERA.total + LF;
            ticket += 'SON: ' + convertirNumeroALetras(data.CABECERA.total) + LF;
            ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF

            const resolucionLineas = dividirTexto('Resol. de Superintendencia N. 182-2016-SUNAT/N.318-2017-SUNAT', 32);
            resolucionLineas.forEach(linea => {
                ticket += linea + LF;
            });
        } else {
            ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
            ticket += 'IMPORTE: S/. ' + data.CABECERA.total + LF;
            ticket += 'SON: ' + convertirNumeroALetras(data.CABECERA.total) + LF;
            ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF
        }

        // === DATOS DE PAGO Y EMISIÓN ===
        ticket += 'Forma de pago: ' + data.CABECERA.forma_pago.toUpperCase() + LF;
        ticket += 'Metodo de pago: ' + data.CABECERA.medio_pago.toUpperCase() + LF;
        const fechaEmision = formatearFechaHora(data.CABECERA.fecha_emision);
        ticket += 'F.H Emision: ' + fechaEmision + LF;

        const vendedorLineas = dividirTexto('Vendedor(a): ' + data.CABECERA.vendedor_nombres + ' ' + data.CABECERA.vendedor_apellidos, 32);
        vendedorLineas.forEach(linea => {
            ticket += linea + LF;
        });

        // === CONDICIONES DE SERVICIO ===
        ticket += '--------------------------------' + LF;
        ticket += ESC + 'a' + String.fromCharCode(1); // Centrar
        ticket += ESC + 'E' + String.fromCharCode(1); // Negrita ON
        ticket += ESC + 'E' + String.fromCharCode(0); // Negrita OFF
        ticket += ESC + 'a' + String.fromCharCode(0); // Alinear izquierda

        let activar_condiciones = 0;
        if (data.EMISOR.terminos_condiciones_pasaje && activar_condiciones == 1) {
            let textoCondiciones = decodificarHTML(data.EMISOR.terminos_condiciones_pasaje);
            textoCondiciones = limpiarTexto(textoCondiciones);
            textoCondiciones = normalizarEscPos(textoCondiciones);
            const condicionesTextoLineas = dividirTexto(textoCondiciones, 32);
            condicionesTextoLineas.forEach(linea => {
                ticket += linea + LF;
            });
        }

        ticket += URL_PAGE_WEB + LF;

        if (data.CABECERA.num_poliza) {
            ticket += normalizarEscPos('Servicio cubierto por el Seguro Obligatorio de Accidentes de Tránsito / Accidentes Personales. Póliza N° : ') + data.CABECERA.num_poliza + LF;
        }
        ticket += LF + LF + LF + LF + LF + LF + LF;
        ticket += '\x0C';

        return ticket;
    }


    function normalizarEscPos(texto) {
        return texto
            // Minúsculas
            .replace(/á/g, 'a').replace(/é/g, 'e')
            .replace(/í/g, 'i').replace(/ó/g, 'o')
            .replace(/ú/g, 'u').replace(/ñ/g, 'n')
            .replace(/ü/g, 'u')
            // Mayúsculas — ahora sí se procesan correctamente
            .replace(/Á/g, 'A').replace(/É/g, 'E')
            .replace(/Í/g, 'I').replace(/Ó/g, 'O')
            .replace(/Ú/g, 'U').replace(/Ñ/g, 'N')
            .replace(/Ü/g, 'U');
    }

    function formatearFechaHora(fecha) {
        const d = new Date(fecha);
        const dia = String(d.getDate()).padStart(2, '0');
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const anio = d.getFullYear();
        const h = d.getHours();
        const min = String(d.getMinutes()).padStart(2, '0');
        const seg = String(d.getSeconds()).padStart(2, '0');
        const ampm = h >= 12 ? 'p.m.' : 'a.m.';
        const h12 = h % 12 || 12;
        return `${dia}/${mes}/${anio}, ${h12}:${min}:${seg} ${ampm}`;
    }

    function limpiarTexto(texto) {
        return texto
            .replace(/<br\s*\/?>/gi, ' ')
            .replace(/<[^>]+>/g, '')
            .replace(/\r?\n/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return '---';

        try {
            const anio = parseInt(fechaNacimiento.split('T')[0].split('-')[0]);

            if (!anio || anio === 0 || anio < 1900) return '---';

            const edad = new Date().getFullYear() - anio;

            return (edad >= 0 && edad < 120) ? edad : '---';
        } catch (e) {
            return '---';
        }
    }

    function formatearFecha(fechaStr) {
        if (!fechaStr) return '--/--/----';

        try {
            // Forzar parseo sin zona horaria (evita desfase de día)
            const partes = fechaStr.split('T')[0].split('-');
            if (partes.length !== 3) return '--/--/----';

            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        } catch (e) {
            return '--/--/----';
        }
    }

    function formatearHora(horaStr) {
        if (!horaStr) return '--:--';

        try {
            // Soporta "HH:MM", "HH:MM:SS", "HH:MM:SS.000"
            const partes = horaStr.split(':');
            if (partes.length < 2) return '--:--';

            const h = parseInt(partes[0]);
            const min = String(parseInt(partes[1])).padStart(2, '0');

            if (isNaN(h) || isNaN(parseInt(partes[1]))) return '--:--';

            const ampm = h >= 12 ? 'p.m.' : 'a.m.';
            const h12 = String(h % 12 || 12).padStart(2, '0');

            return `${h12}:${min} ${ampm}`;
        } catch (e) {
            return '--:--';
        }
    }


    // Función auxiliar para convertir números a letras (simplificada)
    function convertirNumeroALetras(numero) {
        // Esta es una versión muy simplificada
        // En producción deberías usar una librería completa como tu num_letras.php
        const num = parseFloat(numero);
        if (num === 0) return 'CERO CON 00/100 SOLES';

        // Para este ejemplo, retornamos un formato básico
        const entero = Math.floor(num);
        const decimal = Math.round((num - entero) * 100);

        // Aquí deberías implementar la conversión completa a letras
        // Por ahora retornamos un formato simple
        return entero.toString().toUpperCase() + ' CON ' + decimal.toString().padStart(2, '0') + '/100 SOLES';
    }

    // Botón de impresión
    $(document).on('click', '.btn-print', function (e) {
        e.preventDefault();

        let link = $(this).data('link');

        fetch(link, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) throw new Error(response.statusText);

                const contentType = response.headers.get('Content-Type');

                if (contentType.includes('application/pdf')) {
                    return response.blob().then(blob => ({ type: 'pdf', blob }));
                } else if (contentType.includes('application/json')) {
                    return response.json().then(data => ({ type: 'json', data }));
                } else {
                    throw new Error("Tipo de contenido no esperado");
                }
            })
            .then(result => {
                if (result.type === 'pdf') {
                    var url = URL.createObjectURL(result.blob);

                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = url;

                    document.body.appendChild(iframe);
                    iframe.onload = function () {
                        iframe.contentWindow.print();
                    };
                } else if (result.type === 'json') {
                    let iframe = document.createElement('iframe');
                    iframe.style.display = 'none'; // Asegúrate de que esté oculto
                    iframe.src = result.data.link;
                    document.body.appendChild(iframe);

                    // Eliminar el iframe después de 10 segundos
                    setTimeout(function () {
                        document.body.removeChild(iframe);
                    }, 5000); // 10 segundos
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
            });
    });

    /*================================================================== [ Cart ]*/
    $('.js-show-cart').on('click', function () {
        $('.js-panel-cart').addClass('show-header-cart');
        //mostrar_asientos_bloqueados(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value);
    });

    $('.js-hide-cart').on('click', function () {
        $('.js-panel-cart').removeClass('show-header-cart');
    });

    btn_liquidacion.addEventListener("click", (e) => {
        mani.value = 0;
        $.ajax({
            url: ruta_manifiesto,
            success: function (response) {
                if (response == 1) {
                    let formData = new FormData()
                    formData.set("id_programacion", id_progra)
                    fetch(instance._URL_ + "programacion/get_personalProgramacion", {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (!response.ok) throw new Error(response.status())
                        return response.json()
                    }).then(data => {
                        try {
                            if (data.success) {
                                set_personal(data.message)
                            }
                        } catch {
                            instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
                        }
                    }).catch(error => instance.Toast.operacion_erronea(error.message))
                    $('#modal_conductor').modal('show');
                } else {
                    $("#modal_liquidacion_terminal").modal("show")
                }
            },
            error: function (xhr, status, error) {
            }
        });
    })

    btn_liquidacion_usuario.addEventListener("click", (e) => {
        $("#modal_liquidacion_usuario").modal("show")
        porcentaje_empresa.readOnly = id_tp_usuario_sesion.value == 2 || id_tp_usuario_sesion.value == 1 ? false : true;
        if (id_tp_usuario_sesion.value == 2 || id_tp_usuario_sesion.value == 1) {
            div_porcentaje_empresa.classList.remove('d-none');
        } else {
            div_porcentaje_empresa.classList.add('d-none');
            div_observaciones_LU.classList.remove("col-md-9");
            div_observaciones_LU.classList.add("col-md-12");
        }
    })


    btn_reserva_grupal.addEventListener("click", (e) => {
        if (asientos_reserva.value == '') {
            Swal.fire({
                title: 'Error',
                text: 'No se ha seleccionado ningún asiento',
                icon: 'error',
            })
            return false;
        }
        let formData = new FormData()
        formData.set("numeros_asientos", asientos_reserva.value)
        formData.set("id_vehiculo", id_v)
        formData.set("id_programacion", document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value)
        fetch(instance._URL_ + "pasaje/reserva_grupal", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message.message)
                    // clear_vehiculo()
                    // show_vehiculo(document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_vehiculo").value)
                    asientos_reserva.value = ''
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al reservar los asientos.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })

    btn_reserva_todos.addEventListener("click", async (e) => {

        if (asientosSeleccionados.length === 0) {
            Swal.fire({
                title: 'Ooops...',
                text: 'No se ha seleccionado ningún asiento, seleccione al menos un asiento...',
                icon: 'info',
            });
            return;
        }

        const { value: tiempo_reserva } = await Swal.fire({
            title: 'Tiempo de Reserva',
            input: 'select',
            inputLabel: 'Seleccione el tiempo de reserva',
            inputOptions: {
                '00:15': '15 minutos',
                '00:30': '30 minutos',
                '00:45': '45 minutos',
                '01:00': '1 hora',
                '01:30': '1 hora 30 minutos',
                '02:00': '2 horas',
            },
            inputPlaceholder: 'Seleccione una opción',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Reservar'
        });

        // Si canceló el swal, tiempo_reserva es undefined — salir
        if (tiempo_reserva === undefined) return;

        let formData = new FormData();

        let asientosData = asientosSeleccionados.map(asiento => ({
            id_obj_vehiculo: asiento.id.trim(),
            piso: asiento.piso,
            num_asiento: asiento.text_obj.trim()
        }));

        formData.set("numeros_asientos", JSON.stringify(asientosData));
        if (tiempo_reserva) formData.set("tiempo_reserva", tiempo_reserva);
        formData.set("id_vehiculo", id_v);
        formData.set(
            "id_programacion",
            document.querySelector(".btn_selection_programacion.active")
                .closest("tr").querySelector(".id_programacion").value
        );

        try {
            const response = await fetch(instance._URL_ + "pasaje/reservar_todos", {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(response.status);

            const data = await response.json();

            if (data.success) {
                instance.Toast.operacion_exitosa(data.message.message);

                const copiaAsientos = [...asientosSeleccionados];
                for (const asiento of copiaAsientos) {
                    const index = asientosSeleccionados.findIndex(a => a.id === asiento.id);
                    if (index !== -1) await deseleccionarAsiento(index);
                }

            } else {
                instance.Toast.operacion_erronea(data.message);
            }

        } catch (error) {
            instance.Toast.operacion_erronea(error.message);
        }
    });

    // Evento para mostrar los campos de entrada de nino
    c_nino.addEventListener("click", (e) => {
        if (c_nino.checked) {
            nino_Parent.classList.remove('d-none');
            $("#nino").focus();
            c_nino.value = 1;
        } else {
            nino_Parent.classList.add('d-none');
            c_nino.value = 0;
        }
    });

    devolucion_restante.addEventListener('change', function () {
        this.value = this.checked ? 1 : 0;
    });

    aplicar_pospuesto.addEventListener('change', function () {
        const saldo = parseFloat(document.getElementById('pospuesto_saldo').value) || 0;
        const precio = parseFloat(precio_original_input.value) || parseFloat(precio_venta.value) || 0;

        if (precio <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Sin precio',
                text: 'Selecciona un asiento con precio antes de aplicar el saldo.',
                confirmButtonText: 'Entendido'
            });
            this.checked = false;
            this.value = 0;
            return;
        }

        if (this.checked) {
            this.value = 1;
            precio_original_input.value = precio;
            selectizeMedioPago.disable();
            const maxAplicar = Math.min(saldo, precio);
            const diferencia = parseFloat((precio - maxAplicar).toFixed(2));
            const saldoRestante = parseFloat((saldo - maxAplicar).toFixed(2));

            precio_venta.value = diferencia.toFixed(2);

            $("#div_devolucion_restante").addClass('d-none');
            devolucion_restante.checked = false;
            devolucion_restante.value = 0;

            let icon, titulo, mensaje;

            if (diferencia === 0 && saldoRestante === 0) {
                icon = 'success';
                titulo = 'Canje exacto';
                mensaje = 'Se reimprimirá el comprobante original.';

            } else if (diferencia > 0) {
                icon = 'warning';
                titulo = 'Pago adicional requerido';
                mensaje = `El cliente debe pagar S/ ${diferencia.toFixed(2)} adicionales. Se generará una nota de débito.`;

            } else {
                icon = 'info';
                titulo = 'Saldo restante';
                mensaje = `Debe devolver al cliente el total de S/ ${saldoRestante.toFixed(2)} , se generará una nota de crédito.`;

                $('#label_monto_devolucion').text(`S/ ${saldoRestante.toFixed(2)}?`);
                $("#div_devolucion_restante").removeClass('d-none');
                devolucion_restante.checked = true;
                devolucion_restante.value = 1;
            }

            Swal.fire({ icon, title: titulo, text: mensaje, confirmButtonText: 'Entendido' });

        } else {
            this.value = 0;
            precio_venta.value = parseFloat(precio_original_input.value).toFixed(2);
            precio_original_input.value = '';
            selectizeMedioPago.enable();
            $("#div_devolucion_restante").addClass('d-none');
            $('#label_monto_devolucion').text('');
            devolucion_restante.checked = false;
            devolucion_restante.value = 0;
        }
    });

    btn_liberar_reserva.addEventListener("click", (e) => {
        if (asientos_reserva.value == '') {
            Swal.fire({
                title: 'Error',
                text: 'No se ha seleccionado ningún asiento',
                icon: 'error',
            })
            return false;
        }

        let formData = new FormData()
        formData.set("numeros_asientos", asientos_reserva.value)
        formData.set("id_vehiculo", id_v)
        formData.set(
            "id_programacion",
            document.querySelector(".btn_selection_programacion.active")
                .closest("tr")
                .querySelector(".id_programacion").value
        )

        fetch(instance._URL_ + "pasaje/liberar_reservas", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {

                    let mensaje = '';

                    if (data.liberados && data.liberados.length > 0) {
                        data.liberados.forEach(asiento => {
                            mensaje += `<strong>Asiento ${asiento}</strong>: Desbloqueado<br>`;
                        });
                    }

                    if (data.no_liberados && data.no_liberados.length > 0) {
                        data.no_liberados.forEach(item => {
                            mensaje += `<strong>Asiento ${item.asiento}</strong>: ${item.motivo}<br>`;
                        });
                    }

                    Swal.fire({
                        icon: data.no_liberados && data.no_liberados.length > 0
                            ? 'warning'
                            : 'success',
                        title: 'Liberación de reservas',
                        html: mensaje,
                        confirmButtonText: 'Entendido'
                    });

                    asientos_reserva.value = '';
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea(
                    'Ha ocurrido un error al reservar los asientos.'
                )
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })

    // Función para actualizar el estado del indicador
    function actualizarEstadoBluetooth(estado, nombreImpresora = '') {
        const indicator = document.getElementById('bluetoothStatus');
        const indicatorCompact = document.getElementById('bluetoothStatusCompact');

        // Limpiar clases anteriores
        indicator.className = 'bluetooth-status';
        indicatorCompact.className = 'bluetooth-status compact';

        const icon = indicator.querySelector('.status-icon');
        const iconCompact = indicatorCompact.querySelector('.status-icon');
        const text = indicator.querySelector('.status-text');
        const textCompact = indicatorCompact.querySelector('.status-text');

        switch (estado) {
            case 'conectado':
                indicator.classList.add('conectado');
                indicatorCompact.classList.add('conectado');
                icon.className = 'status-icon conectado';
                iconCompact.className = 'status-icon conectado';
                text.textContent = nombreImpresora || 'Impresora conectada';
                textCompact.textContent = nombreImpresora || 'Conectada';
                break;

            case 'desconectado':
                indicator.classList.add('desconectado');
                indicatorCompact.classList.add('desconectado');
                icon.className = 'status-icon desconectado';
                iconCompact.className = 'status-icon desconectado';
                text.textContent = 'Impresora desconectada';
                textCompact.textContent = 'Desconectada';
                break;

            case 'conectando':
                indicator.classList.add('conectando');
                indicatorCompact.classList.add('conectando');
                icon.className = 'status-icon conectando';
                iconCompact.className = 'status-icon conectando';
                text.textContent = 'Conectando...';
                textCompact.textContent = 'Conectando...';
                break;

            default:
                indicator.classList.add('sin-config');
                indicatorCompact.classList.add('sin-config');
                icon.className = 'status-icon sin-config';
                iconCompact.className = 'status-icon sin-config';
                text.textContent = 'Sin configurar';
                textCompact.textContent = 'Sin configurar';
        }
    }

    // Función para manejar clicks en el indicador
    function manejarClickEstado() {
        // Aquí puedes agregar lógica para reconectar o configurar
        console.log('Click en indicador de estado');
        // Ejemplo: si está desconectado, intentar reconectar
        // Si no está configurado, abrir configuración
    }

    let cuponTimer = null;

    $("#cupon").on("input", function () {
        clearTimeout(cuponTimer);

        let codigo = $(this).val().trim();

        // Si está vacío → restaurar precio
        if (codigo === "") {
            limpiarCupon();
            return;
        }

        cuponTimer = setTimeout(() => {
            calcular_descuento(codigo);
        }, 500);
    });


    function calcular_descuento(codigo) {

        let precioOriginal = parseFloat($("#precio_venta").val());
        if (precioOriginal > 0) {
            let formData = new FormData();
            formData.set("codigo", codigo);
            formData.set("precio", precioOriginal);

            fetch(instance._URL_ + 'cupon/get_precio_cupon', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {

                    if (data.success) {
                        aplicarCupon(data);
                    } else {
                        mostrarErrorCupon(data.mensaje);
                    }

                })
                .catch(() => {
                    mostrarErrorCupon("Error al validar cupón");
                });
        } else {
            return false;
        }
    }

    function aplicarCupon(data) {

        $("#cupon")
            .removeClass("is-invalid")
            .addClass("is-valid");

        $("#cupon-msg")
            .removeClass("text-danger")
            .addClass("text-success")
            .text(`Descuento aplicado: -S/ ${data.descuento}`);

        $("#id_cupon").val(data.id_cupon)
        $("#monto_descuento").val(data.descuento)
        $("#precio_venta").val(parseFloat(data.total_final).toFixed(2));
    }

    function limpiarCupon() {
        $("#cupon").removeClass("is-valid is-invalid");
        $("#cupon-msg").text("");
        limpiarDescuento();
    }

    function limpiarDescuento() {
        $("#precio_final").val();
    }


    function mostrarErrorCupon(msg) {
        $("#cupon")
            .removeClass("is-valid")
            .addClass("is-invalid");

        $("#cupon-msg")
            .removeClass("text-success")
            .addClass("text-danger")
            .text(msg);

        limpiarDescuento();
    }

    const obtenerEgresos = () => {
        const egresos = [];

        const filas = table_egresos_venta.querySelectorAll("tbody tr");

        filas.forEach((row) => {

            const tipoEl = row.querySelector(".tp_comprobante_egreso");
            const serieEl = row.querySelector(".serie_egreso");
            const correlativoEl = row.querySelector(".correlativo_egreso");
            const montoEl = row.querySelector(".monto_egreso");
            const conceptoEl = row.querySelector(".concepto_egreso");

            const tipo = tipoEl ? tipoEl.textContent.trim() : '';
            const serie = serieEl ? serieEl.textContent.trim() : '';
            const correlativo = correlativoEl ? correlativoEl.textContent.trim() : '';
            const monto = montoEl ? montoEl.textContent.trim() : '';
            const concepto = conceptoEl ? conceptoEl.textContent.trim() : '';

            egresos.push([tipo, serie, correlativo, monto, concepto]);
        });

        return egresos;
    };

    precio_venta.addEventListener("blur", validarPrecioVenta);

    function validarPrecioVenta() {
        const btnActive = document.querySelector(".btn_selection_programacion.active");
        if (!btnActive) return;

        const fila = btnActive.closest("tr");

        const precioMinimo = Number(fila.querySelector(".precio_minimo").value);

        // Si el usuario deja el campo vacío, restaurar el precio mínimo
        if (precio_venta.value.trim() === "") {
            precio_venta.value = precioMinimo.toFixed(2);
            return;
        }

        const precioIngresado = Number(precio_venta.value);

        if (isNaN(precioIngresado)) {
            precio_venta.value = precioMinimo.toFixed(2);
            return;
        }

        if (precioIngresado < precioMinimo) {

            Swal.fire({
                icon: "warning",
                title: "Precio no permitido",
                text: `El precio mínimo permitido es S/. ${precioMinimo.toFixed(2)}.`,
                confirmButtonColor: "#2a9d8f"
            }).then(() => {
                precio_venta.focus();
            });

            precio_venta.value = precioMinimo.toFixed(2);
        }
    }
});
