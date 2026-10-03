import * as instance from "../../../public/js/instance.js"

document.getElementById("link_programacion").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {

    function cargarTerminalesParaFiltroProgramacion() {
        fetch(instance._URL_ + "programacion/get_allTerminalesParaFiltro")
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    instance.Toast.operacion_erronea(
                        data.message
                    );
                    return;
                }
                const origen =
                    $("#filtro_origen_programacion");
                const destino =
                    $("#filtro_destino_programacion");

                data.message.forEach(terminal => {
                    const optionOrigen =
                        new Option(
                            terminal.nombre,
                            terminal.id_terminal
                        );
                    const optionDestino =
                        new Option(
                            terminal.nombre,
                            terminal.id_terminal
                        );
                    origen.append(optionOrigen);
                    destino.append(optionDestino);
                });
            })
            .catch(error => {
                instance.Toast.operacion_erronea(
                    error.message
                );
            });
    }

    cargarTerminalesParaFiltroProgramacion();

    const table = $('#table_programacion').DataTable({
        "ajax": {
            'url': $('#url').val() + 'programacion/dataTable',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = $("#filtro_fecha_inicio_programacion").val();
                d.fecha_fin = $("#filtro_fecha_fin_programacion").val();
                d.tipo_programacion = $("#filtro_tipo_programacion").val();
                d.origen = $("#filtro_origen_programacion").val();
                d.destino = $("#filtro_destino_programacion").val();
                d.estado_programacion = $("#filtro_estado_programacion").val();
                d.fecha_liquidacion = $("#filtro_fecha_liquidacion_programacion").val();
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        "processing": true,
        "serverSide": true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "terminal_origen_nombre",
            },
            {
                "data": "terminal_destino_nombre",
            },
            {
                "data": "tipo_programacion",
                render: (data, type, row) => {
                    let color = ''
                    if (row.tipo_programacion == 2) {
                        color = 'badge-purple-soft';
                    } else {
                        color = 'badge-primary-soft';
                    }
                    let tp_programacion = `<span class="badge badge-soft ${color}">${instance.CONSTS.TEXTO.TP_PROGRAMACION[row.tipo_programacion]}</span>`;
                    return tp_programacion;
                }
            },
            {
                "data": "conductor_nombres",
                render: (data, type, row) => {
                    if (row.id_conductor == 2) {
                        return `Sin conductor`
                    } else {
                        return `
                        ${row.conductor_nombres}<br>
                        <span class="text-secondary">${row.conductor_num_docu}</span>
                        `
                    }
                }
            },
            {
                "data": "vehiculo_placa",
            },
            {
                "data": "fecha_salida",
            },
            {
                "data": "hora_salida",
            },
            {
                "data": "precio_primer_piso",
                render: function (data, type, row) {
                    let p_primer_piso = row.precio_primer_piso ? `S/ ` + row.precio_primer_piso : '-';
                    return `${p_primer_piso}`
                }
            },
            {
                "data": "precio_segundo_piso",
                render: function (data, type, row) {
                    let p_segundo_piso = row.precio_segundo_piso ? `S/ ` + row.precio_segundo_piso : '-';
                    return `${p_segundo_piso}`
                }
            },
            {
                "data": "estado",
                render: function (data, type, row) {
                    if (row.estado) {
                        return `<span class="badge badge-soft badge-success-soft">HABILITADO</span>`
                    } else {
                        return `<span class="badge badge-soft badge-danger-soft">DESHABILITADO</span>`
                    }
                }
            },
            {
                "data": "liquidado",
                render: function (data, type, row) {
                    if (row.liquidado) {
                        return `<span class="badge badge-soft badge-info-soft">LIQUIDADO</span>`
                    } else {
                        return `<span class="badge badge-soft badge-warning-soft">SIN LIQUIDAR</span>`
                    }
                }
            },
            {
                "data": "fecha_liquidacion",
            },
            {
                "data": "id_programacion",
                render: function (data, type, row) {
                    let html_btnEditar = `<li><a class="dropdown-item btn_dropdown btnSmallEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR PROGRAMACIÓN">Editar</a></li>`
                    let html_btnVerventas = `<li><a class="dropdown-item btn_dropdown btnSmallVerVentas" data-bs-toggle="modal" data-bs-target="#modal_ventas" data-bs-whatever="VENTAS DE LA PROGRAMACIÓN">Ver ventas</a></li>`
                    let html_btnEgreso = `<li><a class="dropdown-item btn_dropdown btnSmallEgreso" data-bs-toggle="modal" data-bs-target="#modal_egreso" data-bs-whatever="NUEVO EGRESO">Egreso</a></li>`
                    let html_btnCopiar = `<li><a class="dropdown-item btn_dropdown btnSmallCopiarRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="NUEVA PROGRAMACIÓN">Duplicar</a></li>`
                    let html_btnEliminar = `<li><a class="dropdown-item btn_dropdown btnSmallDeleteRegis">Eliminar</a></li>`
                    let html_liquidar = `<li><a target="_blank" class="dropdown-item btn_dropdown btnLiquidarRegis" id="btn_liquidacion">Liquidar</a></li>`
                    let html_btnLiquidacion = `<li><a target="_blank" class="dropdown-item btn_dropdown mb-2 btn-liquidacion" data-id-programacion="${row.id_programacion}">Liquidación General</a></li>`
                    let html_btnLiquidacionPU = `<li><a target="_blank" class="dropdown-item btn_dropdown mb-2 btn-liquidacionPU" data-id-programacion="${row.id_programacion}">Liquidación por usuario</a></li>`
                    let html_btnLiquidacionU = `<li><a target="_blank" class="dropdown-item btn_dropdown mb-2 btn-liquidacionU" data-id-programacion="${row.id_programacion}">Liquidación Usuario</a></li>`
                    let html_btnLiquidacionT = `<li><a target="_blank" class="dropdown-item btn_dropdown mb-2 btn-liquidacionT" data-id-programacion="${row.id_programacion}">Liquidación Terminal</a></li>`
                    let html_btnManifiesto = row.tipo_programacion != 2 ? `<li><a id="btn_manifiesto" target="_blank" data-id-programacion="${row.id_programacion}" class="dropdown-item btn_dropdown btn-manifiesto">Manifiesto</a></li>` : '';
                    let html_btnLiquidacionTicket = `<li><a target="_blank" class="dropdown-item btn_dropdown mb-2 btn-liquidacionTicket" data-id-programacion="${row.id_programacion}">Liquidación Ticket</a></li>`;
                    if (row.tp_usuario == 1 || row.tp_usuario == 2) {
                        if (row.liquidado) {
                            if (row.tipo_programacion != 2) {
                                return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                ${html_btnVerventas}
                                ${html_btnManifiesto}
                                ${html_btnLiquidacion}
                                ${html_btnLiquidacionPU}
                                ${html_btnLiquidacionU}
                                ${html_btnLiquidacionT}
                                ${html_btnCopiar}
                                </ul>
                            </div>
                             `
                            }
                            else {
                                return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                ${html_btnLiquidacion}
                                </ul>
                            </div>
                         `
                            }
                        } else {
                            return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                ${html_liquidar}
                                ${html_btnEditar}
                                ${html_btnCopiar}
                                ${html_btnEliminar}
                                ${html_btnEgreso}
                                </ul>
                            </div>
                         `
                        }
                    } else {
                        if (row.liquidado) {
                            if (row.tipo_programacion != 2) {
                                return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                ${html_btnManifiesto}
                                ${html_btnLiquidacionU}
                                </ul>
                            </div>
                         `
                            } else {
                                return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                </ul>
                            </div>
                         `
                            }
                        } else {
                            return `
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu">
                                ${html_btnLiquidacionU}
                                ${html_btnLiquidacionTicket}
                                </ul>
                            </div>
                         `
                        }
                    }
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
        "deferRender": true,
        "stateSave": false,
        "pageLength": 20,
    })

    // Variables Form
    let form = document.querySelector("#form")
    let form_liquidar = document.querySelector("#form_liquidar")
    let id_programacion = document.querySelector("#id_programacion")
    let id_programacion_l = document.querySelector("#id_programacion_l")
    let terminal_origen = document.querySelector("#terminal_origen")
    let terminal_destino = document.querySelector("#terminal_destino")
    let vehiculo = document.querySelector("#vehiculo")
    let conductor = document.querySelector("#conductor")
    let fecha_salida = document.querySelector("#fecha_salida")
    let hora_salida = document.querySelector("#hora_salida")
    let precio_primer_piso = document.querySelector("#precio_primer_piso")
    let precio_segundo_piso = document.querySelector("#precio_segundo_piso")
    let precio_minimo = document.querySelector("#precio_minimo")
    let estado = document.querySelector("#estado")

    let add_personal = document.querySelector("#add_personal")
    let table_personal = document.querySelector("#table_personal")
    let personal = document.querySelector("#personal")
    let add_terminalRuta = document.querySelector("#add_terminalRuta")
    let add_destinoRuta = document.querySelector("#add_destinoRuta")
    let table_terminalRuta = document.querySelector("#table_terminalRuta")
    let table_destinoRuta = document.querySelector("#table_destinoRuta")
    let terminal_ruta = document.querySelector("#terminal_ruta")
    let ruta_destino = document.querySelector("#ruta_destino")
    let hora_salida_ruta = document.querySelector("#hora_salida_ruta")
    let precio_rd_piso_1 = document.querySelector("#precio_rd_piso_1")
    let precio_rd_piso_2 = document.querySelector("#precio_rd_piso_2")
    let mani = document.querySelector("#manifi")
    let id_procond = document.querySelector("#id_program")
    let tipo_programacion = document.querySelector("#tipo_programacion")
    let div_parentTpServicioPasaje = document.querySelector("#div_parentTpServicioPasaje")
    let Parent_PrPrimerPiso = document.querySelector("#Parent_PrPrimerPiso")
    let Parent_PrSegundoPiso = document.querySelector("#Parent_PrSegundoPiso")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    let button_save_l = document.querySelector("#button_save_l")
    let button_cancel_l = document.querySelector("#button_cancel_l")
    let button_loadSave_l = document.querySelector("#button_loadSave_l")

    instance.Modal.change_name(["#modal"])
    instance.Modal.change_name(["#modal_egreso"])
    instance.Modal.change_name(["#modal_ventas"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")
    instance.Validate.allowInputMoney(["#precio_primer_piso", "#precio_segundo_piso", "#precio_minimo"])
    instance.select.createSelect("#terminal_origen", "#div_parentTerminalOrigen")
    instance.select.createSelect("#terminal_destino", "#div_parentTerminalDestino")
    instance.select.createSelect("#vehiculo", "#div_parentVehiculo")
    instance.select.createSelect("#conductor", "#div_parentConductor")
    instance.select.createSelect("#personal", "#div_parentPersonal")
    instance.select.createSelect("#terminal_ruta", "#div_parentRuta")
    instance.select.createSelect("#ruta_destino", "#div_parentDestinoRuta")
    instance.select.createSelect("#tp_servicio_pasaje", "#div_parentTpServicioPasaje")

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_programacion.value = ""
        $("#terminal_origen").val("").trigger("change")
        $("#terminal_destino").val("").trigger("change")
        $("#vehiculo").val("").trigger("change")
        $("#conductor").val("").trigger("change")
        $("#tp_servicio_pasaje").val("").trigger("change")
        $("#tipo_programacion").val("1").trigger("change")

        $('#table_personal tbody tr').remove()
        $('#table_terminalRuta tbody tr').remove()
        $('#table_destinoRuta tbody tr').remove()
        Parent_PrPrimerPiso.classList.remove("d-none");
        Parent_PrSegundoPiso.classList.remove("d-none");
        div_parentTpServicioPasaje.classList.remove("d-none");
    })

    $("#modal_liquidacion").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_liquidar) {
            form_liquidar.classList.remove("was-validated")
        })
        $("#tp_porcen").val("1").trigger("change")
        $("#tp_porcen_e").val("1").trigger("change")
        $('#table_egreso tbody tr').remove()
        $('#table_egreso_e tbody tr').remove()
    })

    //Boton de liquidacion
    $(document).on('click', '.btn-liquidacion', function (e) {
        e.preventDefault();

        let id_programacion = $(this).data('id-programacion');
        fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_VEHICULO + id_programacion, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.blob(); // Obtener el PDF como Blob
        }).then(blob => {
            var url = URL.createObjectURL(blob);

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none'; // Ocultar el iframe
            iframe.src = url;

            document.body.appendChild(iframe);
            iframe.onload = function () {
                iframe.contentWindow.print(); // Imprimir el PDF
            };
        }).catch(error => {
            instance.Toast.operacion_erronea(error.message);
        });
    });

    //Boton liquidacion por usuario
    $(document).on('click', '.btn-liquidacionPU', function (e) {
        e.preventDefault();

        let id_programacion = $(this).data('id-programacion');
        fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_PUSUARIO + id_programacion, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.blob(); // Obtener el PDF como Blob
        }).then(blob => {
            var url = URL.createObjectURL(blob);

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none'; // Ocultar el iframe
            iframe.src = url;

            document.body.appendChild(iframe);
            iframe.onload = function () {
                iframe.contentWindow.print(); // Imprimir el PDF
            };
        }).catch(error => {
            instance.Toast.operacion_erronea(error.message);
        });
    });

    //Boton de liquidacion por usuario
    $(document).on('click', '.btn-liquidacionU', function (e) {
        e.preventDefault();
        let id_programacion = $(this).data('id-programacion');

        fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_USUARIO + id_programacion + '/' + instance._ID_USUARIO_SESION, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.blob(); // Obtener el PDF como Blob
        }).then(blob => {
            var url = URL.createObjectURL(blob);

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none'; // Ocultar el iframe
            iframe.src = url;

            document.body.appendChild(iframe);
            iframe.onload = function () {
                iframe.contentWindow.print(); // Imprimir el PDF
            };
        }).catch(error => {
            instance.Toast.operacion_erronea(error.message);
        });
    });

    $(document).on('click', '.btn-liquidacionTicket', function (e) {
        e.preventDefault();

        let id_programacion = $(this).data('id-programacion');
        let id_usuario = instance._ID_USUARIO_SESION;

        const url = instance._URL_ + 'pasaje/impresion/liquidacion_usuarioticket/' + id_programacion + '/' + id_usuario;

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text || response.statusText);
                    });
                }
                return response.blob();
            })
            .then(blob => {
                if (blob.size === 0) {
                    instance.Toast.operacion_erronea('El PDF generado está vacío');
                    return;
                }

                const url = URL.createObjectURL(blob);
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = url;
                document.body.appendChild(iframe);

                iframe.onload = function () {
                    iframe.contentWindow.print();
                };
            })
            .catch(error => {
                instance.Toast.operacion_erronea('Error: ' + error.message);
            });
    });

    $(document).on('click', '.btn-liquidacionT', function (e) {
        e.preventDefault();
        let id_programacion = $(this).data('id-programacion');

        fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_TERMINAL + id_programacion + '/' + instance._ID_TERMINAL_SESION, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.blob(); // Obtener el PDF como Blob
        }).then(blob => {
            var url = URL.createObjectURL(blob);

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none'; // Ocultar el iframe
            iframe.src = url;

            document.body.appendChild(iframe);
            iframe.onload = function () {
                iframe.contentWindow.print(); // Imprimir el PDF
            };
        }).catch(error => {
            instance.Toast.operacion_erronea(error.message);
        });
    });

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("terminal_origen"), formData.get("terminal_destino"),
            formData.get("vehiculo"), formData.get("fecha_salida"),
            formData.get("hora_salida"), formData.get("estado")
        ]

        if (tipo_programacion.value != '2') {
            data.push(formData.get("tp_servicio_pasaje"))
            data.push(formData.get("precio_primer_piso"))
            if ($("#vehiculo option:selected").attr("num-piso") > 1) {
                data.push(formData.get("precio_segundo_piso"))
            }
        }

        // personal
        let id_personal_selected = document.querySelectorAll(".id_personal_selected")
        let personal = []
        Object.values(id_personal_selected).map((e, i, array) => {
            return personal.push([e.value])
        })

        // ruta
        let id_terminalRutaSelected = document.querySelectorAll(".id_terminalRutaSelected")
        let horaTerminalRuta = document.querySelectorAll(".horaTerminalRuta")
        let terminalRuta = []
        Object.values(id_terminalRutaSelected).map((e, i, array) => {
            return terminalRuta.push([e.value, horaTerminalRuta[i].value])
        })

        // Rutas Destino
        let id_terminalDRutaSelected = document.querySelectorAll(".id_rutaDestinoSelected");
        let horaTerminalDRuta = document.querySelectorAll(".horaRutaDestino");
        let precioTerminalDRutaPiso1 = document.querySelectorAll(".precioRutaDestinoPiso1");
        let precioTerminalDRutaPiso2 = document.querySelectorAll(".precioRutaDestinoPiso2");

        let terminalDRuta = [];
        id_terminalDRutaSelected.forEach((e, i) => {
            terminalDRuta.push({
                id_terminal: e.value,
                hora: horaTerminalDRuta[i].value,
                precio_primer_piso: precioTerminalDRutaPiso1[i].value,
                precio_segundo_piso: precioTerminalDRutaPiso2[i].value,
            });
        });

        if (instance.Validate.validateData(data)) {
            formData.set("personal", JSON.stringify(personal))
            formData.set("terminalRuta", JSON.stringify(terminalRuta))
            formData.set("terminalDRuta", JSON.stringify(terminalDRuta))
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + "programacion/crud_register", {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message)
                    $('#modal').modal('toggle')
                    instance.Datatable.reloadTable(table)
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message()))
                .finally(() => {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos')
        }
    })

    $(document).ready(function () {
        $("#tp_porcen").val(1).trigger("change");
        $("#tp_porcen_e").val(1).trigger("change");
    });

    $("#tp_porcen").on('change', function () {
        let tp_porcen = $(this).val();
        let monto_p = document.querySelector("#div_porcen")
        let porcen = document.querySelector("#div_monto")
        let monto_por = document.querySelector("#div_porcen")
        let porcentaje = document.querySelector("#div_monto")
        if (tp_porcen == 1) {
            monto_p.classList.remove('d-none');
            monto_por.setAttribute("required", true);
            porcentaje.removeAttribute("required");
            porcen.classList.add('d-none');
            $("#porcen").val(0);
        } else if (tp_porcen == 2) {
            porcen.classList.remove('d-none');
            monto_p.classList.add('d-none');
            monto_por.removeAttribute("required");
            porcentaje.setAttribute("required", true);
            $("#monto").val(0);
        }
    });


    $("#tp_porcen_e").on('change', function () {
        let tp_porcen_e = $(this).val();
        let monto_p_e = document.querySelector("#div_porcen_e")
        let porcen_e = document.querySelector("#div_monto_e")
        let monto_por_e = document.querySelector("#div_porcen_e")
        let porcentaje_e = document.querySelector("#div_monto_e")
        if (tp_porcen_e == 1) {
            monto_p_e.classList.remove('d-none');
            monto_por_e.setAttribute("required", true);
            porcentaje_e.removeAttribute("required");
            porcen_e.classList.add('d-none');
            $("#porcen_e").val(0);
        } else if (tp_porcen_e == 2) {
            porcen_e.classList.remove('d-none');
            monto_p_e.classList.add('d-none');
            monto_por_e.removeAttribute("required");
            porcentaje_e.setAttribute("required", true);
            $("#monto_e").val(0);
        }
    });

    //Tabla de egresos
    $('#table_egreso thead').on('click', '#btn_add_egreso', function (e) {
        let tbody = table_egreso.getElementsByTagName("tbody")[0];
        tbody.insertAdjacentHTML('afterbegin', `
            <tr>
                <td>
                <select class='form-control tp_comprobante' required style="width: 100%;">
                 <option value="FACTURA">FACTURA</option>
                 <option value="BOLETA">BOLETA</option>
                 <option value="RECIBO">RECIBO</option>
                </select>
                </td>
                <td><input type='text' class='form-control serie' required style="width: 120px;" oninput="this.value = this.value.toUpperCase()"></td>
                <td><input type='text' class='form-control correlativo' required style="width: 90px;"></td>
                <td><input type='number' class='form-control monto_u' required value='0.00' style="width: 100px;"></td>
                <td><textarea class='form-control concepto' rows='3' style="width: 230px;"></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `);
    });

    //Borrar registros de tabla
    $('#table_egreso tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_egreso tbody").removeChild(this.closest("tr"))
        let tbody = table_egreso.getElementsByTagName("tbody")[0]
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total").textContent = total;
    });

    //Actualizar
    $('#table_egreso').on('change', '.monto_u', function (e) {
        let tbody = table_egreso.getElementsByTagName("tbody")[0];
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total").textContent = total;
    });


    //Tabla de egresos
    $('#table_egreso_e thead').on('click', '#btn_add_egreso_e', function (e) {
        let tbody = table_egreso_e.getElementsByTagName("tbody")[0];
        tbody.insertAdjacentHTML('afterbegin', `
            <tr>
                <td>
                <select class='form-control tp_comprobante_e' required style="width: 100%;">
                 <option value="FACTURA">FACTURA</option>
                 <option value="BOLETA">BOLETA</option>
                 <option value="RECIBO">RECIBO</option>
                </select>
                </td>
                <td><input type='text' class='form-control serie_e' required style="width: 120px;" oninput="this.value = this.value.toUpperCase()"></td>
                <td><input type='text' class='form-control correlativo_e' required style="width: 90px;"></td>
                <td><input type='number' class='form-control monto_u_e' required value='0.00' style="width: 100px;"></td>
                <td><textarea class='form-control concepto_e' rows='3' style="width: 230px;"></textarea></td>
                <td><button type='button' class='btn button_deleteItem_e p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `);
    });

    //Borrar registros de tabla
    $('#table_egreso_e tbody').on('click', '.button_deleteItem_e', function (e) {
        e.preventDefault();
        document.querySelector("#table_egreso_e tbody").removeChild(this.closest("tr"))
        let tbody = table_egreso_e.getElementsByTagName("tbody")[0]
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total_e").textContent = total;
    });

    //Actualizar
    $('#table_egreso_e').on('change', '.monto_u_e', function (e) {
        let tbody = table_egreso_e.getElementsByTagName("tbody")[0];
        let precios = Array.from(tbody.querySelectorAll(".monto_u_e")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total_e").textContent = total;
    });
    //Envio de registro de liquidacion
    form_liquidar.addEventListener("submit", (e) => {
        e.preventDefault()
        if (id_programacion_l.value == '') {
            Swal.fire({
                title: 'Error',
                text: 'No se ha seleccionado un programa de liquidación',
                icon: 'error',
            })
        }
        let formData = new FormData(form_liquidar)

        let tp_com = document.querySelectorAll(".tp_comprobante")
        let serie = document.querySelectorAll(".serie")
        let correlativo = document.querySelectorAll(".correlativo")
        let monto_u = document.querySelectorAll(".monto_u")
        let concepto = document.querySelectorAll(".concepto")
        let detalles = []
        let valid_2 = true;

        Object.values(serie).forEach((e, i) => {
            // Validar campos vacíos
            if (tp_com[i].value.trim() === "" ||
                e.value.trim() === "" ||
                correlativo[i].value.trim() === "" ||
                monto_u[i].value.trim() === "" ||
                concepto[i].value.trim() === "") {
                valid_2 = false;
            }

            // Validar que el monto no sea 0
            if (parseFloat(monto_u[i].value) === 0) {
                valid_2 = false;
                instance.Toast.operacion_informativa("El monto no puede ser 0. Verifica los registros.")
                return false;
            }

            // Agregar tp_com al array de detalles
            detalles.push([tp_com[i].value, e.value, correlativo[i].value, monto_u[i].value, concepto[i].value]);
        });

        if (!valid_2) {
            instance.Toast.operacion_informativa("Ningún campo de la tabla egresos debe estar vacío. Verifica los registros.")
            return false;
        }

        // Apartado de encomiendas
        let tp_com_e = document.querySelectorAll(".tp_comprobante_e")
        let serie_e = document.querySelectorAll(".serie_e")
        let correlativo_e = document.querySelectorAll(".correlativo_e")
        let monto_u_e = document.querySelectorAll(".monto_u_e")
        let concepto_e = document.querySelectorAll(".concepto_e")
        let detalles_e = []
        let valid_2_e = true;

        Object.values(serie_e).forEach((e, i) => {
            // Validar campos vacíos
            if (tp_com_e[i].value.trim() === "" ||
                e.value.trim() === "" ||
                correlativo_e[i].value.trim() === "" ||
                monto_u_e[i].value.trim() === "" ||
                concepto_e[i].value.trim() === "") {
                valid_2_e = false;
            }

            // Validar que el monto no sea 0
            if (parseFloat(monto_u_e[i].value) === 0) {
                valid_2_e = false;
                instance.Toast.operacion_informativa("El monto no puede ser 0. Verifica los registros.")
                return false;
            }

            // Agregar tp_com al array de detalles
            detalles_e.push([tp_com_e[i].value, e.value, correlativo_e[i].value, monto_u_e[i].value, concepto_e[i].value]);
        });

        if (!valid_2_e) {
            instance.Toast.operacion_informativa("Ningún campo de la tabla egresos debe estar vacío. Verifica los registros.")
            return false;
        }

        formData.set("detalles_e", JSON.stringify(detalles_e))
        formData.set("detalles", JSON.stringify(detalles))
        formData.set("id_programacion", id_programacion_l.value)
        button_save_l.classList.add("d-none")
        button_cancel_l.classList.add("d-none")
        button_loadSave_l.classList.remove("d-none")
        fetch(instance._URL_ + 'pasaje/liquidar_vehiculo', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status);
            return response.json();
        }).then(data => {
            if (data.success) {
                $("#modal_liquidacion").modal('toggle')
                fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_VEHICULO + id_programacion_l.value, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.blob(); // Obtener el PDF como Blob
                }).then(blob => {
                    var url = URL.createObjectURL(blob);

                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none'; // Ocultar el iframe
                    iframe.src = url;

                    document.body.appendChild(iframe);
                    iframe.onload = function () {
                        iframe.contentWindow.print(); // Imprimir el PDF
                    };
                    instance.Toast.operacion_exitosa(data.message)
                    instance.Datatable.reloadTable(table)
                }).catch(error => {
                    instance.Toast.operacion_erronea(error.message);
                }).finally(() => {
                    button_save_l.classList.remove("d-none")
                    button_cancel_l.classList.remove("d-none")
                    button_loadSave_l.classList.add("d-none")
                });
            } else {
                instance.Toast.operacion_erronea(data.message);
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message));
    })

    // Select conductor y personal
    $("#conductor, #personal").on('change', function () {
        actualizar_selects();
    });

    //Boton manifiesto
    $('#table_programacion tbody').on('click', '.btn-manifiesto', function (e) {
        const datos = table.row($(this).parents()).data()
        id_procond.value = datos.id_programacion;
        let ruta_manifiesto = instance._URL_ + instance.CONSTS.URL.MANIFIESTO.PASAJE + datos.id_programacion
        mani.value = 1;
        $.ajax({
            url: ruta_manifiesto,
            success: function (response) {
                if (response == 1) {
                    let formData = new FormData()
                    formData.set("id_programacion", datos.id_programacion)
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
                            $.ajax({
                                url: ruta_manifiesto,
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
                                            url: ruta_manifiesto,
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
                                                console.log(ruta_manifiesto)
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
                    })
                }
            },
            error: function (xhr, status, error) {
            }
        });
    });

    //Boton de liquidacion 
    $('#table_programacion tbody').on('click', '.btnLiquidarRegis', function (e) {
        const datos = table.row($(this).parents()).data()
        //Abrir modal de liquidacion para especificar comision gastos y otros
        id_programacion_l.value = datos.id_programacion;
        id_procond.value = datos.id_programacion;
        let ruta_manifiesto = instance._URL_ + instance.CONSTS.URL.MANIFIESTO.PASAJE + datos.id_programacion
        mani.value = 0;

        $.ajax({
            url: ruta_manifiesto,
            success: function (response) {
                if (response == 1) {
                    let formData = new FormData()
                    formData.set("id_programacion", datos.id_programacion)
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
                    $('#modal_liquidacion').modal('show');
                }
            },
            error: function (xhr, status, error) {
            }
        });
    });

    function actualizar_selects() {
        const conductor = $("#conductor").val();
        const personal = $("#personal").val();

        // Mostrar todas las opciones
        $("#conductor option, #personal option").each(function () {
            $(this).prop('disabled', false).show();
        });

        // Ocultar las opciones seleccionadas en el otro select
        if (conductor) {
            $("#personal option[value='" + conductor + "']").prop('disabled', true).hide();
        }

        if (personal) {
            $("#conductor option[value='" + personal + "']").prop('disabled', true).hide();
        }

        // Refrescar Select2 para reflejar los cambios sin recargar
        $('#conductor').trigger('change.select2');
        $('#personal').trigger('change.select2');
    }

    // Inicializar el comportamiento en caso de que ya haya selecciones
    actualizar_selects();

    // EDIT REGISTER
    $('#table_programacion tbody').on('click', '.btnSmallEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_programacion.value = data.id_programacion
        $("#terminal_origen").val(data.id_terminal_origen).trigger("change")
        setTimeout(() => {
            $("#terminal_destino").val(data.id_terminal_destino).trigger("change")
        }, 1000)
        $("#vehiculo").val(data.id_vehiculo).trigger("change")
        $("#conductor").val(data.id_conductor).trigger("change")
        $("#tipo_programacion").val(data.tipo_programacion).trigger("change")
        fecha_salida.value = data.fecha_salida
        hora_salida.value = data.hora_salida
        precio_primer_piso.value = data.precio_primer_piso
        precio_segundo_piso.value = data.precio_segundo_piso
        precio_minimo.value = data.precio_minimo
        $("#tp_servicio_pasaje").val(data.id_tp_servicio_pasaje).trigger("change")
        estado.value = data.estado

        let formData = new FormData()
        formData.set("id_programacion", data.id_programacion)
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
        //Rutas origen
        fetch(instance._URL_ + "programacion/get_terminalRuta", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    set_terminalRuta(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al cargar las rutas.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))

        //Rutas Destino
        fetch(instance._URL_ + "programacion/get_terminalDRuta", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    set_rutaDestino(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al cargar las rutas.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })

    $('#table_programacion tbody').on('click', '.btnSmallVerVentas', function (e) {
        const data = table.row($(this).parents()).data()
        id_programacion.value = data.id_programacion
        let formData = new FormData()
        formData.set("id_programacion", data.id_programacion)
        fetch(instance._URL_ + "pasaje/get_resumenVendedores", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    cargarTablaVendedores(data.data)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })

    function cargarTablaVendedores(vendedores) {
        const tbody = document.getElementById('cuerpo_tabla_vendedores')
        tbody.innerHTML = ''

        let totalVendidos = 0
        let totalReservados = 0
        let totalAnulados = 0
        let totalNotaVenta = 0
        let totalBoleta = 0
        let totalFactura = 0
        let totalMonto = 0

        vendedores.forEach(v => {
            totalVendidos += v.total_vendidos
            totalReservados += v.total_reservados
            totalAnulados += v.total_anulados
            totalNotaVenta += v.total_nota_venta
            totalBoleta += v.total_boleta
            totalFactura += v.total_factura
            totalMonto += parseFloat(v.monto_total)

            const tr = document.createElement('tr')
            tr.innerHTML = `
            <td>
                <a href="${v.link}" target="_blank"><span class="fw-semibold">${v.nombres} ${v.apellidos}</span><br>
                <small class="text-muted">${v.tipo_usuario} · ${v.num_docu}</small></a>
            </td>
            <td>${v.total_vendidos}</td>
            <td>${v.total_reservados}</td>
            <td>${v.total_anulados}</td>
            <td>${v.total_nota_venta}</td>
            <td>${v.total_boleta}</td>
            <td>${v.total_factura}</td>
            <td>${parseFloat(v.monto_total).toFixed(2)}</td>
        `
            tbody.appendChild(tr)
        })

        document.getElementById('total_vendidos').textContent = totalVendidos
        document.getElementById('total_reservados').textContent = totalReservados
        document.getElementById('total_anulados').textContent = totalAnulados
        document.getElementById('total_nota_venta').textContent = totalNotaVenta
        document.getElementById('total_boleta').textContent = totalBoleta
        document.getElementById('total_factura').textContent = totalFactura
        document.getElementById('total_monto').textContent = totalMonto.toFixed(2)
    }

    // Copiar programacion -> esto con base a no estar poniendo de todo lo de un dia y eso 
    $('#table_programacion tbody').on('click', '.btnSmallCopiarRegis', function (e) {
        const data = table.row($(this).parents()).data()
        $("#terminal_origen").val(data.id_terminal_origen).trigger("change")
        setTimeout(() => {
            $("#terminal_destino").val(data.id_terminal_destino).trigger("change")
        }, 1000)
        $("#vehiculo").val(data.id_vehiculo).trigger("change")
        $("#conductor").val(data.id_conductor).trigger("change")
        $("#tipo_programacion").val(data.tipo_programacion).trigger("change")
        // fecha_salida.value = data.fecha_salida
        hora_salida.value = data.hora_salida
        precio_primer_piso.value = data.precio_primer_piso
        precio_segundo_piso.value = data.precio_segundo_piso
        precio_minimo.value = data.precio_minimo
        $("#tp_servicio_pasaje").val(data.id_tp_servicio_pasaje).trigger("change")
        estado.value = data.estado

        let formData = new FormData()
        formData.set("id_programacion", data.id_programacion)
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
        //Rutas origen
        fetch(instance._URL_ + "programacion/get_terminalRuta", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    set_terminalRuta(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al cargar las rutas.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))

        //Rutas Destino
        fetch(instance._URL_ + "programacion/get_terminalDRuta", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            try {
                if (data.success) {
                    set_rutaDestino(data.message)
                }
            } catch {
                instance.Toast.operacion_erronea('Ha ocurrido un error al cargar las rutas.')
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })


    //Delete
    $('#table_programacion tbody').on('click', '.btnSmallDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro seleccionado</label><br>
                <p class="fs-6 mt-3 mb-0 text-success">¿Está Usted de Acuerdo?</p>
                </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#429ef5',
            cancelButtonColor: '#ff3b3b',
            cancelButtonText: 'No!',
            confirmButtonText: 'Si, Adelante!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData()
                formData.set("id_programacion", data.id_programacion)
                fetch(instance._URL_ + "programacion/delete_register", {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (!response.ok) throw new Error()
                    return response.json()
                }).then(data => {
                    if (data.success) {
                        if (data.action === "deleted") {

                            instance.Toast.operacion_exitosa(data.message);
                            instance.Datatable.reloadTable(table);

                        } else if (data.action === "show_records") {

                            cargarRegistrosProgramacion(data.data);
                            $("#modalProgramacionRegistros").modal("show");

                        }
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                    }
                }).catch(error => instance.Toast.operacion_erronea(error.message))
            }
        })
    });

    function cargarRegistrosProgramacion(registros) {

        const tbody = document.getElementById("tbProgramacionRegistros");
        tbody.innerHTML = "";

        registros.forEach(registro => {

            let badgeEstado = "";
            let cliente = registro.cliente || "-";

            if (registro.tipo === "VENTA") {
                badgeEstado = '<span class="badge bg-success">Vendido</span>';
            } else {
                badgeEstado = '<span class="badge bg-warning text-dark">Seleccionado</span>';
            }

            tbody.insertAdjacentHTML("beforeend", `
            <tr>
                <td>${registro.asiento}</td>
                <td>${badgeEstado}</td>
                <td>${cliente}</td>
                <td class="text-center">
                    <button
                        class="btn btn-outline-danger btn-sm btnEliminarRegistro"
                        data-tipo="${registro.tipo}"
                        data-id-venta="${registro.id_venta ? registro.id_venta : ''}"
                        data-id-tp-comprobante="${registro.id_tp_comprobante ? registro.id_tp_comprobante : ''}"
                        data-id-programacion-obj="${registro.id_programacion_obj}"
                        data-id-programacion="${registro.id_programacion}"
                        data-id-vehiculo-obj="${registro.id_obj_vehiculo}">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `);

        });
    }

    document.querySelector("#tbProgramacionRegistros").addEventListener("click", function (e) {

        const btn = e.target.closest(".btnEliminarRegistro");
        if (!btn) return;

        const tipo = btn.dataset.tipo;
        const idVenta = btn.dataset.idVenta;
        const idTpComprobante = btn.dataset.idTpComprobante;
        const idProgramacionObj = btn.dataset.idProgramacionObj;
        const idProgramacion = btn.dataset.idProgramacion;
        const idVehiculoObj = btn.dataset.idVehiculoObj;
        if (tipo === "VENTA") {
            Swal.showLoading()
            let formData = new FormData();
            formData.set("id_ventaPasaje", idVenta)
            formData.set("tp_comprobante", idTpComprobante)

            fetch(instance._URL_ + "pasaje/anular_venta", {
                method: "POST",
                body: formData
            })
                .then(response => { if (response.ok) return response.json() })
                .then(data => {
                    if (data.success) {
                        btn.closest("tr").remove();
                        Swal.close()
                        if (idTpComprobante == 1 || idTpComprobante == 3) {
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
                        }
                    } else {
                        Swal.close();
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

        } else {
            let fData = new FormData();
            fData.set("id_programacion", idProgramacion);
            fData.set("id_obj_vehiculo", idVehiculoObj);

            fetch(instance._URL_ + 'pasaje/delete_estadoProcesoAsiento', {
                method: 'POST',
                body: fData
            })
                .then(response => {
                    if (!response.ok) throw new Error('Error: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        btn.closest("tr").remove();
                        instance.Toast.operacion_exitosa(data.message);
                    } else {
                        instance.Toast.operacion_erronea(data.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                });
        }

    });

    $("#terminal_origen").change(function (e) {
        e.preventDefault();
        terminal_destino.innerHTML = ""
        terminal_destino.insertAdjacentHTML('beforeend', `<option value="">Seleccione</option>`)
        let formData = new FormData()
        formData.set("id_terminal_origen", $("#terminal_origen").val())
        fetch(instance._URL_ + "programacion/get_allTerminalDestino", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            if (data.success) {
                Object.values(data.message).forEach(e => {
                    terminal_destino.insertAdjacentHTML('beforeend', `<option value="${e.id_terminal}">${e.nombre}</option>`)
                })
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    });

    add_personal.addEventListener("click", (e) => {
        const selectedValue = personal.value;
        const selectedText = personal.options[personal.selectedIndex].text;

        // Función para verificar si el texto contiene "PILOTO" o "COPILOTO"
        function esPilotoCopiloto(texto) {
            return texto.includes("CONDUCTOR") || texto.includes("COPILOTO");
        }

        // Verificar si se está intentando agregar un "piloto" o "copiloto"
        if (esPilotoCopiloto(selectedText)) {
            // Verificar si ya existe un "piloto" o "copiloto" en la tabla
            const existePilotoCopiloto = Array.from(document.querySelectorAll(".personal_selected"))
                .some(element => esPilotoCopiloto(element.innerText));

            // Mostrar mensaje de error si ya hay un "piloto" o "copiloto" registrado
            if (existePilotoCopiloto) {
                instance.Toast.operacion_erronea("Ya hay un piloto o copiloto registrado");
                return; // Salir de la función sin agregar el registro
            }
        }

        // Verificar si el personal ya está agregado
        const personalYaAgregado = Array.from(document.querySelectorAll(".personal_selected"))
            .some(element => element.innerText === selectedText);

        if (selectedValue.length === 0 || personalYaAgregado) {
            instance.Toast.operacion_erronea("El personal ya se encuentra agregado");
        } else {
            let tbody = table_personal.getElementsByTagName("tbody")[0];
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <input type="hidden" class="id_personal_selected" value="${selectedValue}">
                    <td><label class="personal_selected mb-0 mt-2">${selectedText}</label></td>
                    <td><button type='button' class='btn btnDeleteRegis button_deleteItem' style="font-size:10px !important">Eliminar</button></td>
                </tr>
            `);
            $("#personal").val("").trigger("change");
        }
    });


    $('#table_personal tbody').on('click', '.button_deleteItem', function (e) {
        document.querySelector("#table_personal tbody").removeChild(this.closest("tr"))
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

    // terminal / rutas
    $("#terminal_destino").change(function (e) {
        e.preventDefault();
        terminal_ruta.innerHTML = ""
        terminal_ruta.insertAdjacentHTML('beforeend', `<option value=""> Seleccione</option>`)
        let formData = new FormData()
        formData.set("id_terminal_origen", $("#terminal_origen").val())
        formData.set("id_terminal_destino", $("#terminal_destino").val())
        fetch(instance._URL_ + "programacion/get_allTerminalRutaProgramacion", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            if (data.success) {
                Object.values(data.message).forEach(e => terminal_ruta.insertAdjacentHTML('beforeend', `<option value = "${e.id_terminal}">${e.nombre}</option>`))
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    });

    add_terminalRuta.addEventListener("click", (e) => {
        if (terminal_ruta.value.length == 0 || Object.values(document.querySelectorAll(".terminalRutaSelected")).map((e) => { return e.innerHTML }).includes(terminal_ruta.options[terminal_ruta.selectedIndex].text)) {
            instance.Toast.operacion_erronea("La ruta ya se encuentra agregado")
        } else {
            let tbody = table_terminalRuta.getElementsByTagName("tbody")[0]
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <input type="hidden" class="id_terminalRutaSelected" value="${terminal_ruta.value}">
                <td><label class="terminalRutaSelected mb-0 mt-2">${terminal_ruta.options[terminal_ruta.selectedIndex].text}</label></td>
                <td><input type="time" class="form-control horaTerminalRuta"></td>
                <td><button type='button' class='btn btnDeleteRegis button_deleteItem' style="font-size:10px !important">Eliminar</button></td>
            </tr>
                `)
            $("#terminal_ruta").val("").trigger("change")
        }
    })

    $('#table_terminalRuta tbody').on('click', '.button_deleteItem', function (e) {
        document.querySelector("#table_terminalRuta tbody").removeChild(this.closest("tr"))
    });

    const set_terminalRuta = (data) => {
        let tbody = table_terminalRuta.getElementsByTagName("tbody")[0]
        Object.values(data).forEach((e, i, a) => {
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <input type="hidden" class="id_terminalRutaSelected" value="${e.id_terminal}">
                <td><label class="terminalRutaSelected mb-0 mt-2">${e.terminal_nombre}</label></td>
                <td><input type="time" class="form-control horaTerminalRuta" value="${e.hora}"></td>
                <td><button type='button' class='btn btnDeleteRegis button_deleteItem' style="font-size:10px !important">Eliminar</button></td>
            </tr>
            `)
        })
    }

    //Proceso para la carga de rutas destino // Cargar rutas de destino cuando cambie terminal o ruta de origen
    $("#terminal_destino, #terminal_ruta").change(function (e) {
        e.preventDefault();
        ruta_destino.innerHTML = "";
        ruta_destino.insertAdjacentHTML('beforeend', `<option value=""> Seleccione</option>`);

        // Solo cargar si ambos terminales están seleccionados
        if (!$("#terminal_origen").val() || !$("#terminal_destino").val()) return;

        let formData = new FormData();
        formData.set("id_terminal_origen", $("#terminal_origen").val());
        formData.set("id_terminal_destino", $("#terminal_destino").val());
        // Obtener todas las rutas de origen seleccionadas
        formData.set("rutas_origen", Array.from(document.querySelectorAll(".id_terminalRutaSelected"))
            .map(input => input.value));

        fetch(instance._URL_ + "programacion/get_allTerminalRutaDestino", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status());
            return response.json();
        }).then(data => {
            if (data.success) {
                Object.values(data.message).forEach(e =>
                    ruta_destino.insertAdjacentHTML('beforeend',
                        `<option value="${e.id_terminal}">${e.nombre}</option>`));
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message));
    });

    // Agregar ruta de destino
    add_destinoRuta.addEventListener("click", (e) => {
        const rutaVal = ruta_destino.value.trim();
        const horaVal = hora_salida_ruta.value.trim();
        const precioVal1 = precio_rd_piso_1.value.trim();
        const precioVal2 = precio_rd_piso_2.value.trim();

        if (rutaVal.length === 0) {
            instance.Toast.operacion_erronea("Seleccione una ruta de destino.");
            return;
        }
        if (horaVal.length === 0) {
            instance.Toast.operacion_erronea("Ingrese la hora de salida.");
            return;
        }
        if (precioVal1.length === 0 || isNaN(precioVal1) || Number(precioVal1) < 1) {
            instance.Toast.operacion_erronea("Ingrese un precio válido para el primer piso (mayor o igual a 1).");
            return;
        }
        if (precioVal2.length === 0 || isNaN(precioVal2) || Number(precioVal2) < 1) {
            instance.Toast.operacion_erronea("Ingrese un precio válido para el segundo piso (mayor o igual a 1).");
            return;
        }

        let rutasSeleccionadas = [...document.querySelectorAll(".rutaDestinoSelected")]
            .map((el) => el.innerHTML.trim());

        let rutaActual = ruta_destino.options[ruta_destino.selectedIndex].text.trim();

        if (rutasSeleccionadas.includes(rutaActual)) {
            instance.Toast.operacion_erronea("La ruta de destino ya se encuentra agregada.");
            return;
        }

        let tbody = table_destinoRuta.getElementsByTagName("tbody")[0];
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <input type="hidden" class="id_rutaDestinoSelected" value="${rutaVal}">
            <input type="hidden" class="horaRutaDestino"        value="${horaVal}">
            <input type="hidden" class="precioRutaDestinoPiso1" value="${Number(precioVal1).toFixed(2)}">
            <input type="hidden" class="precioRutaDestinoPiso2" value="${Number(precioVal2).toFixed(2)}">
            <td><label class="rutaDestinoSelected mb-0 mt-2">${rutaActual}</label></td>
            <td><label class="mb-0 mt-2">${horaVal}</label></td>
            <td><label class="mb-0 mt-2">S/. ${Number(precioVal1).toFixed(2)}</label></td>
            <td><label class="mb-0 mt-2">S/. ${Number(precioVal2).toFixed(2)}</label></td>
            <td>
                <button type='button' class='btn btnDeleteRegis button_deleteItem' 
                    style="font-size:10px !important">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `);

        $("#ruta_destino").val("").trigger("change");
        hora_salida_ruta.value = "";
        precio_rd_piso_1.value = "";
        precio_rd_piso_2.value = "";
    });

    // Eliminar ruta de destino
    $('#table_destinoRuta tbody').on('click', '.button_deleteItem', function () {
        document.querySelector("#table_destinoRuta tbody").removeChild(this.closest("tr"));
        $("#terminal_destino").trigger("change");
    });

    const set_rutaDestino = (data) => {
        let tbody = table_destinoRuta.getElementsByTagName("tbody")[0];
        Object.values(data).forEach((e) => {
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <input type="hidden" class="id_rutaDestinoSelected" value="${e.id_terminal}">
                <input type="hidden" class="horaRutaDestino"        value="${e.hora}">
                <input type="hidden" class="precioRutaDestinoPiso1" value="${parseFloat(e.precio_primer_piso ? e.precio_primer_piso : 0).toFixed(2)}">
                <input type="hidden" class="precioRutaDestinoPiso2" value="${parseFloat(e.precio_segundo_piso ? e.precio_segundo_piso : 0).toFixed(2)}">
                <td><label class="rutaDestinoSelected mb-0 mt-2">${e.terminal_nombre}</label></td>
                <td><label class="mb-0 mt-2">${e.hora}</label></td>
                <td><label class="mb-0 mt-2">S/. ${parseFloat(e.precio_primer_piso ? e.precio_primer_piso : 0).toFixed(2)}</label></td>
                <td><label class="mb-0 mt-2">S/. ${parseFloat(e.precio_segundo_piso ? e.precio_segundo_piso : 0).toFixed(2)}</label></td>
                <td>
                    <button type='button' class='btn btnDeleteRegis button_deleteItem' 
                        style="font-size:10px !important">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        });
    };

    // $('#table_programacion tbody').on('click', '.btnLiquidarRegis', function (e) {
    //     const datos = table.row($(this).parents()).data()
    //     Swal.fire({
    //         title: '¿Está seguro que desea liquidar el vehículo?',
    //         text: "No podrá revertir los cambios",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Sí, Liquidar!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             let formData = new FormData();
    //             formData.set('id_programacion', datos.id_programacion);

    //             fetch(instance._URL_ + 'pasaje/liquidar_vehiculo', {
    //                 method: 'POST',
    //                 body: formData
    //             }).then(response => {
    //                 if (!response.ok) throw new Error(response.status);
    //                 return response.json();
    //             }).then(data => {
    //                 if (data.success) {
    //                     fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_VEHICULO + datos.id_programacion, {
    //                         method: 'GET',
    //                         headers: {
    //                             'Content-Type': 'application/json'
    //                         }
    //                     }).then(response => {
    //                         if (!response.ok) throw new Error(response.statusText);
    //                         return response.blob(); // Obtener el PDF como Blob
    //                     }).then(blob => {
    //                         var url = URL.createObjectURL(blob);

    //                         var iframe = document.createElement('iframe');
    //                         iframe.style.display = 'none'; // Ocultar el iframe
    //                         iframe.src = url;

    //                         document.body.appendChild(iframe);
    //                         iframe.onload = function () {
    //                             iframe.contentWindow.print(); // Imprimir el PDF
    //                         };
    //                         instance.Toast.operacion_exitosa(data.message)
    //                         instance.Datatable.reloadTable(table)
    //                     }).catch(error => {
    //                         instance.Toast.operacion_erronea(error.message);
    //                     });
    //                 } else {
    //                     instance.Toast.operacion_erronea(data.message);
    //                 }
    //             }).catch(error => instance.Toast.operacion_erronea(error.message));
    //         }
    //     });

    // })

    // Evento para tipo de programacion
    $('#tipo_programacion').on('change', function () {
        let tipo_pro = $(this).val();
        if (tipo_pro == '2') {
            Parent_PrPrimerPiso.classList.add("d-none");
            Parent_PrSegundoPiso.classList.add("d-none");
            div_parentTpServicioPasaje.classList.add("d-none");
        } else {
            Parent_PrPrimerPiso.classList.remove("d-none");
            Parent_PrSegundoPiso.classList.remove("d-none");
            div_parentTpServicioPasaje.classList.remove("d-none");
        }
    });
});

window.aplicarFiltrosProgramacion = function () {
    const table = $('#table_programacion').DataTable();
    table.ajax.reload();
}

window.limpiarFiltrosProgramacion = function () {
    $('#filtro_fecha_inicio_programacion').val('');
    $('#filtro_fecha_fin_programacion').val('');
    $('#filtro_tipo_programacion').val('');
    $('#filtro_origen_programacion').val('');
    $('#filtro_destino_programacion').val('');
    $('#filtro_estado_programacion').val('');
    $('#filtro_fecha_liquidacion_programacion').val('');
    
    $('#table_programacion').DataTable().ajax.reload();
};

window.exportarExcelProgramacion = function () {

    const fecha_inicio = $('#filtro_fecha_inicio_programacion').val();
    const fecha_fin = $('#filtro_fecha_fin_programacion').val();
    const tipo_programacion = $('#filtro_tipo_programacion').val();
    const origen = $('#filtro_origen_programacion').val();
    const destino = $('#filtro_destino_programacion').val();
    const estado_programacion = $('#filtro_estado_programacion').val();
    const fecha_liquidacion = $('#filtro_fecha_liquidacion_programacion').val();
    const search = $('#table_programacion').DataTable().search();

    let url = instance._URL_ + 'programacion/exportar_excel_programacion?';
    const params = [];
    if (fecha_inicio) {params.push('fecha_inicio=' + fecha_inicio);}
    if (fecha_fin) {params.push('fecha_fin=' + fecha_fin);}
    if (tipo_programacion) {params.push('tipo_programacion=' + tipo_programacion);}
    if (origen) {params.push('origen=' + origen);}
    if (destino) {params.push('destino=' + destino);}
    if (estado_programacion) {params.push('estado_programacion=' + estado_programacion);}
    if (fecha_liquidacion) {params.push('fecha_liquidacion=' + fecha_liquidacion);}
    if (search) {params.push('search=' + search);}
    window.open(url + params.join('&'), '_blank');
};