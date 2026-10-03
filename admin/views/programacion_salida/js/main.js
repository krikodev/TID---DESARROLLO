import * as instance from "../../../public/js/instance.js"

document.getElementById("link_programacion_salida").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_programacion').DataTable({
        "ajax": {
            'url': $('#url').val() + 'programacion_salida/dataTable',
            'method': 'POST',
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
                "data": "origen",
            },
            {
                "data": "direccion_origen",
            },
            {
                "data": "destino",
            },
            {
                "data": "direccion_destino",
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
                "data": "estado",
                render: function (data, type, row) {
                    if (row.estado) {
                        return `<span class="badge bg-success">HABILITADO</span>`
                    } else {
                        return `<span class="badge bg-danger">DESHABILITADO</span>`
                    }
                }
            },
            {
                "data": "id_salida",
                render: function (data, type, row) {
                    let html_btnEditar = `<li><a class="dropdown-item btn_dropdown btnSmallEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-mode="edit" data-bs-whatever="EDITAR PROGRAMACIÓN">Editar</a></li>`
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
    instance.Validate.allowInputMoney(["#precio_primer_piso", "#precio_segundo_piso"])
    instance.select.createSelect("#terminal_origen", "#div_parentTerminalOrigen")
    instance.select.createSelect("#terminal_destino", "#div_parentTerminalDestino")
    instance.select.createSelect("#vehiculo", "#div_parentVehiculo")
    instance.select.createSelect("#conductor", "#div_parentConductor")
    instance.select.createSelect("#personal", "#div_parentPersonal")
    instance.select.createSelect("#terminal_ruta", "#div_parentRuta")
    instance.select.createSelect("#ruta_destino", "#div_parentDestinoRuta")
    instance.select.createSelect("#tp_servicio_pasaje", "#div_parentTpServicioPasaje")

    function initUbigeoSelect(selectId) {
        let ts = new TomSelect(selectId, {
            valueField: "cod_ubigeo",
            labelField: "nombre",
            searchField: "nombre",
            placeholder: "Buscar ubigeo...",
            load: function (query, callback) {
                if (!query.length) return callback();
                let FormD = new FormData();
                FormD.set("q", query);
                fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
                    method: "POST",
                    body: FormD
                })
                    .then(res => res.json())
                    .then(json => {
                        if (json.success) {
                            callback(json.items);
                        } else {
                            callback();
                        }
                    })
                    .catch(() => { callback(); });
            }
        });
        return ts;
    }

    // Función para asignar valor al ubigeo (modo edición)
    function setUbigeoValue(ts, codUbigeo) {
        if (!codUbigeo) {
            ts.clear(); // limpia si no hay valor
            return;
        }

        let FormD = new FormData();
        FormD.set("pre", codUbigeo);

        fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
            method: "POST",
            body: FormD
        })
            .then(res => res.json())
            .then(json => {
                if (json.success && json.items.length) {
                    let item = json.items[0];
                    ts.addOption(item);          // añade la opción
                    ts.setValue(item.cod_ubigeo); // selecciona el valor
                }
            });
    }

    let ubigeoOrigen = initUbigeoSelect("#ubigeo_origen");
    let ubigeoDestino = initUbigeoSelect("#ubigeo_destino");


    $("#modal").on("show.bs.modal", (e) => {
        let mode = $("#modal").data("mode") || $(e.relatedTarget).data("mode");

        // Solo si es nuevo le ponemos el valor de sesión
        if (mode === "new") {
            setUbigeoValue(ubigeoOrigen, instance._UB_TERMINAL_SESION);
            $("#direccion_origen").val(instance._DIR_TERMINAL_SESION).trigger("change");
        }
    });



    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_salida.value = ""
        $("#direccion_origen").val("").trigger("change")
        $("#direccion_destino").val("").trigger("change")
        $("#vehiculo").val("").trigger("change")
        $("#conductor").val("").trigger("change")
        if (typeof ubigeoOrigen !== 'undefined') ubigeoOrigen.clear(true);
        if (typeof ubigeoDestino !== 'undefined') ubigeoDestino.clear(true);
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


    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("ubigeo_origen"), formData.get("ubigeo_destino"), formData.get("direccion_destino"),
            formData.get("vehiculo"), formData.get("fecha_salida"),
            formData.get("hora_salida"), formData.get("estado")
        ]

        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + "programacion_salida/crud_register", {
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

    // EDIT REGISTER
    $('#table_programacion tbody').on('click', '.btnSmallEditRegis, .btnSmallCopiarRegis', function (e) {
        const data = table.row($(this).parents()).data();
        if ($(this).hasClass('btnSmallEditRegis')) {
            id_salida.value = data.id_salida;
        } else {
            id_salida.value = '';
        }
        setUbigeoValue(ubigeoOrigen, data.ubigeo_origen);
        $("#direccion_origen").val(data.direccion_origen).trigger("change")
        setUbigeoValue(ubigeoDestino, data.ubigeo_destino);
        $("#direccion_destino").val(data.direccion_destino).trigger("change")
        $("#vehiculo").val(data.id_vehiculo).trigger("change")
        $("#conductor").val(data.id_conductor).trigger("change")
        $("#tipo_programacion").val(data.tipo_programacion).trigger("change")
        fecha_salida.value = data.fecha_salida
        hora_salida.value = data.hora_salida
        estado.value = data.estado
    });


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
                formData.set("id_salida", data.id_salida)
                fetch(instance._URL_ + "programacion_salida/delete_register", {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (!response.ok) throw new Error()
                    return response.json()
                }).then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message)
                        instance.Datatable.reloadTable(table)
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                    }
                }).catch(error => instance.Toast.operacion_erronea(error.message))
            }
        })
    })

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
})