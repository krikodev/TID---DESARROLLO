import * as instance from "../../../public/js/instance.js"

document.getElementById("link_caja_chica").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_caja_chica").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_caja_chica").closest(".submenu").classList.add("show")
document.getElementById("link_caja_chica").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {

    let button_rep_cajas = document.getElementById("button_rep_cajas");
    if (instance._TP_USUARIO_SESION == 1 || instance._TP_USUARIO_SESION == 2) {
        button_rep_cajas.classList.remove("d-none");
    }

    const table = $('#table_caja_chica').DataTable({
        "ajax": {
            'url': $('#url').val() + 'caja_chica/dataTable',
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
                "data": "referencia",
            },
            {
                "data": "id_usuario",
                render: function (data, type, row) {
                    return `
                    <label class="m-0 p-0">${row["usuario_nombres"]} ${row["usuario_apellidos"]}</label><br>
                    <label class="m-0 p-0 text-secondary">${row["tp_usuario"]}</label>
                    `
                }
            },
            {
                "data": "fecha_inicio",
            },
            {
                "data": "fecha_fin",
            },
            {
                "data": "saldo_inicial",
                render: function (data, type, row) {
                    return `S/ ${row["saldo_inicial"]}`
                }
            },
            {
                "data": "saldo_final",
                render: function (data, type, row) {
                    return `S/ ${row["saldo_final"]}`
                }
            },
            {
                "data": "saldo_real",
                render: function (data, type, row) {
                    return `S/ ${row["saldo_real"]}`
                }
            },
            {
                "data": "estado",
                render: function (data, type, row) {
                    if (row.estado) {
                        return `<span class="badge bg-success">Abierto</span>`
                    } else {
                        return `<span class="badge bg-info">Cerrado</span>`
                    }
                }
            },
            {
                "data": "id_caja_chica",
                render: function (data, type, row) {
                    let html_cerrar_caja = `<li><a class="dropdown-item btn_dropdown btnSmallCloseCajaRegis">Cerrar caja</a></li>`;
                    let html_editar = `<li><a class="dropdown-item btn_dropdown btnSmallEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR CAJA CHICA | ${row["referencia"]}">Editar</a></li>`
                    let html_eliminar = `<li><a class="dropdown-item btn_dropdown btnSmallDeleteRegis">Eliminar</a></li>`
                    let html_liquidar = `<li><a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_CAJA_CHICA.LIQUIDACION_USUARIO + row.id_caja_chica}" target="_blank" class="dropdown-item btn_dropdown">Liquidación</a></li>`

                    let cerrar_caja = row.estado && id_usuario_sesion.value == row.id_usuario
                        ? html_cerrar_caja
                        : "";
                    let editar = row.estado && id_usuario_sesion.value == row.id_usuario
                        ? html_editar
                        : "";
                    let eliminar = row.estado && id_usuario_sesion.value == row.id_usuario
                        ? html_eliminar
                        : "";
                    let liquidacion_usuario = !row.estado && id_usuario_sesion.value == row.id_usuario
                        ? html_liquidar
                        : "";

                    return `
                        <div class="dropdown">
                            <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </a>
                            <ul class="dropdown-menu">
                                ${editar}
                                ${eliminar}
                                ${cerrar_caja}
                                <li><a href="${instance.CONSTS.URL.IMPRESION_CAJA_CHICA.CIERRE.A4 + row.id_caja_chica}" target="_blank" class="dropdown-item btn_dropdown btnPDFA4">PDF 4</a></li>
                                <li><a href="${instance.CONSTS.URL.IMPRESION_CAJA_CHICA.CIERRE.TICKET + row.id_caja_chica}" target="_blank" class="dropdown-item btn_dropdown btnPDFTicket80">PDF ticket 80</a></li>
                                ${liquidacion_usuario}
                            </ul>
                        </div>
                     `
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
    let form_cierre = document.querySelector("#form_cierre")
    let id_caja_chica = document.querySelector("#id_caja_chica")
    let id_caja = document.querySelector("#id_caja")
    let saldo_inicial = document.querySelector("#saldo_inicial")
    let referencia = document.querySelector("#referencia")

    let btn_generarReporte = document.querySelector("#btn_generarReporte")
    let div_imprimir = document.querySelector("#div_imprimir")
    let terminal = document.querySelector("#terminal")
    let fecha_inicio_reporte = document.querySelector("#fecha_inicio_reporte")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    let button_save_c = document.querySelector("#button_save_c")
    let button_cancel_c = document.querySelector("#button_cancel_c")
    let button_loadSave_c = document.querySelector("#button_loadSave_c")

    instance.Modal.change_name(["#modal"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")

    instance.Validate.allowInputMoney(["#saldo_inicial"])
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.select.createSelect("#id_terminal_reporte", "#div_parentTerminalR");
    instance.select.createSelect("#id_usuario_reporte", "#div_parentPersonalR");

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_caja_chica.value = ""
        div_imprimir.classList.add("d-none")
        div_imprimir.innerHTML = ""
    })

    $("#modal_reporte").on("hidden.bs.modal", (e) => {
        $("#terminal").val("").trigger("change")
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("saldo_inicial"), formData.get("referencia")
        ]
        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + 'caja_chica/crud_register', {
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
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos')
        }
    })

    // Cierre de caja form
    form_cierre.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form_cierre)
        let data = [
            formData.get("monto_real")
        ]

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

        if (instance.Validate.validateData(data)) {
            formData.set("egresos", JSON.stringify(detalles))
            button_save_c.classList.add("d-none")
            button_cancel_c.classList.add("d-none")
            button_loadSave_c.classList.remove("d-none")
            fetch(instance._URL_ + 'caja_chica/cerrar_caja', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    $('#modal_cierre_caja').modal('toggle')
                    instance.Datatable.reloadTable(table)
                    instance.Toast.operacion_exitosa(data.message.message)
                    modal_printReporte(data.message.message, data.message.links)
                } else {
                    instance.Toast.operacion_erronea(data.message.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save_c.classList.remove("d-none")
                    button_cancel_c.classList.remove("d-none")
                    button_loadSave_c.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Indique el monto real en mano')
        }
    })

    // EDIT REGISTER
    $('#table_caja_chica tbody').on('click', '.btnSmallEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_caja_chica.value = data.id_caja_chica
        saldo_inicial.value = data.saldo_inicial
        referencia.value = data.referencia
    })

    //Delete
    $('#table_caja_chica tbody').on('click', '.btnSmallDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.referencia}</span>
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
                $.ajax({
                    type: "post",
                    url: instance._URL_ + "caja_chica/delete_register",
                    data: {
                        id_caja_chica: data.id_caja_chica,
                    },
                    success: (response) => {
                        try {
                            let reply = JSON.parse(response)
                            if (reply.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Operación exitosa',
                                    text: reply.message,
                                })
                                instance.Datatable.reloadTable(table)
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Operación errónea',
                                    text: reply.message,
                                })
                            }
                        } catch {
                            Swal.fire({
                                icon: 'error',
                                title: 'Operación errónea',
                                text: 'Ha ocurrido un error, intentalo más tarde.',
                            })
                        }
                    }
                })
            }
        })
    })

    // Funcion para cerrado de modal de liquidacion de caja chica
    $("#modal_cierre_caja").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_liquidar) {
            form_liquidar.classList.remove("was-validated")
        })
        id_caja.value = ''
        $('#table_egreso tbody tr').remove()
        $('#table_egreso_e tbody tr').remove()
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
                <select class='form-control tp_comprobante_e' required style="width: 100%;>
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

    // Cerrar caja chica
    $('#table_caja_chica tbody').on('click', '.btnSmallCloseCajaRegis', function (e) {
        const dataRow = table.row($(this).parents()).data();
        id_caja.value = dataRow.id_caja_chica
        $("#modal_cierre_caja").modal("show")
    });

    const modal_printReporte = (title, links = []) => {
        let html_links = ""
        links.forEach((e, i, array) => {
            html_links += `
                <a href="${e.link}" class="d-flex flex-column text-decoration-none py-4 mx-2 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
                    <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                    <label class="text-secondary cursor-pointer mt-2">${e.nombre}</label>
                </a>
            `
        })

        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            html: `
                <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
                    <i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>
                    <label></label>
                    <h5>${title}</h5>
                    <section class="d-flex justify-content-center align-items-center">
                        ${html_links}
                    </section>
                    <section>
                        <p class="text-secondary">Enviar comprobante por WhatsApp</p>
                        <div class="input-group mb-3">
                            <span class="input-group-text py-0">+51</span>
                            <input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">
                            <button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></buton>
                        </div >
                    </section >
                </div >
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

    // Reporte
    btn_generarReporte.addEventListener("click", (event) => {
        if (terminal.value && fecha_inicio_reporte.value) {

            let formData = new FormData()
            formData.set('terminal', terminal.value)
            formData.set('fecha_inicio_reporte', fecha_inicio_reporte.value)
            fetch(instance._URL_ + 'caja_chica/consultarCajasLiquidacionTerminal', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            }).then(data => {
                if (data.success) {
                    div_imprimir.classList.remove("d-none")
                    div_imprimir.innerHTML = ""
                    div_imprimir.insertAdjacentHTML('beforeend', `
                        <a href = "${instance.CONSTS.URL.IMPRESION_CAJA_CHICA.LIQUIDACION_TERMINAL}${terminal.value}/${fecha_inicio_reporte.value.split("-").join("_")}" class="d-flex flex-column justify-content-center align-items-center text-decoration-none pt-4 mx-2 mt-3 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
                            <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                            <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
                        </a>
                    `)
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos.')
        }
    })

    $("#terminal").change(function (e) { div_imprimir.innerHTML = "" });
    $("#fecha_inicio_reporte").change(function (e) { div_imprimir.innerHTML = "" });


    const modalReporte = $('#modal_reporte_cajas');
    const selectTerminal = $('#id_terminal_reporte');
    const selectUsuario = $('#id_usuario_reporte');
    const resultado = document.getElementById('resultado_reporte_cajas');

    selectTerminal.select2({
        dropdownParent: modalReporte,
        width: '100%',
        placeholder: 'Seleccione una terminal'
    });

    selectUsuario.select2({
        dropdownParent: modalReporte,
        width: '100%',
        placeholder: 'Seleccione un vendedor'
    });

    selectUsuario.prop('disabled', true);

    selectTerminal.on('change', async function () {

        const idTerminal = this.value;

        resultado.innerHTML = '';

        if (idTerminal == 0 || idTerminal === '') {
            selectUsuario
                .html('<option value="0">Todos los vendedores</option>')
                .prop('disabled', true)
                .trigger('change');

            return;
        }

        selectUsuario
            .html('<option value="0">Cargando vendedores...</option>')
            .prop('disabled', true)
            .trigger('change');

        try {
            const formData = new FormData();
            formData.append('id_terminal', idTerminal);

            const response = await fetch(instance._URL_ + 'personal/get_personalxterminal', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            let options = '<option value="0">Todos los vendedores</option>';

            if (data.success && Array.isArray(data.message)) {
                data.message.forEach(usuario => {
                    options += `
                        <option value="${usuario.id_usuario}">
                            ${usuario.nombre_personal}
                        </option>
                    `;
                });
            }

            selectUsuario
                .html(options)
                .prop('disabled', false)
                .trigger('change');

        } catch (error) {
            console.error(error);

            selectUsuario
                .html('<option value="0">Error al cargar vendedores</option>')
                .prop('disabled', true)
                .trigger('change');
        }
    });

    document.getElementById('form_reporte_cajas').addEventListener('submit', async function (e) {
        e.preventDefault();

        const fecha = document.getElementById('fecha_cierre').value;
        const idTerminal = document.getElementById('id_terminal_reporte').value;
        const idUsuario = document.getElementById('id_usuario_reporte').value;

        resultado.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="fa-solid fa-spinner fa-spin"></i>
                Buscando cajas cerradas...
            </div>
        `;

        try {
            const formData = new FormData();
            formData.append('fecha_cierre', fecha);
            formData.append('id_terminal', idTerminal);
            formData.append('id_usuario', idUsuario);

            const response = await fetch(instance._URL_ + 'caja_chica/buscar_cajas_cerradas_reporte', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!data.success) {
                resultado.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        ${data.message}
                    </div>
                `;
                return;
            }

            const formatMoney = (value) => {
                return 'S/ ' + parseFloat(value || 0).toFixed(2);
            };

            let html = `
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <div>
            Se encontraron <b>${data.total_cajas}</b> caja(s) cerrada(s).
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="border rounded p-2 bg-light">
                <small class="text-muted d-block">Total ventas</small>
                <strong>${formatMoney(data.totales.total_ventas)}</strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-2 bg-light">
                <small class="text-muted d-block">Monto empresa</small>
                <strong>${formatMoney(data.totales.monto_empresa)}</strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-2" style="background:#e9fffb;border-color:#3FCCBA!important;">
                <small class="text-muted d-block">Dinero a entregar</small>
                <strong class="fs-6">${formatMoney(data.totales.dinero_entregar)}</strong>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-3">
            <thead class="table-light">
                <tr>
                    <th>Caja</th>
                    <th>Terminal</th>
                    <th>Vendedor</th>
                    <th class="text-end">Ventas</th>
                    <th class="text-end">Empresa</th>
                    <th class="text-end">Dinero entregar</th>
                </tr>
            </thead>
            <tbody>
`;

            data.cajas.forEach(caja => {
                html += `
        <tr>
            <td>#${caja.id_caja_chica}</td>
            <td>${caja.terminal || '-'}</td>
            <td>${caja.usuario || '-'}</td>
            <td class="text-end">${formatMoney(caja.total_ventas)}</td>
            <td class="text-end">${formatMoney(caja.monto_empresa)}</td>
            <td class="text-end fw-bold">${formatMoney(caja.dinero_entregar)}</td>
        </tr>
    `;
            });

            html += `
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="3">Totales</td>
                    <td class="text-end">${formatMoney(data.totales.total_ventas)}</td>
                    <td class="text-end">${formatMoney(data.totales.monto_empresa)}</td>
                    <td class="text-end">${formatMoney(data.totales.dinero_entregar)}</td>
                </tr>
            </tfoot>
        </table>
    </div>
`;

            let url = instance._URL_ + 'caja_chica/impresion/reporte_caja_cerrada_a4/' + fecha;
            let urlTicket = instance._URL_ + 'caja_chica/impresion/reporte_caja_cerrada_ticket/' + fecha;

            url += '/' + (idTerminal > 0 ? idTerminal : 0);
            urlTicket += '/' + (idTerminal > 0 ? idTerminal : 0);

            url += '/' + (idUsuario > 0 ? idUsuario : 0);
            urlTicket += '/' + (idUsuario > 0 ? idUsuario : 0);

            html += `
    <div class="d-flex justify-content-end gap-2 flex-wrap">
        <a href="${urlTicket}" target="_blank" class="btn btn-outline-secondary">
            <i class="fa-solid fa-receipt"></i>
            Ticket POS
        </a>

        <a href="${url}" target="_blank" class="btn text-white" style="background:#3FCCBA;">
            <i class="fa-solid fa-file-pdf"></i>
            Reporte A4
        </a>
    </div>
`;

            resultado.innerHTML = html;

        } catch (error) {
            console.error(error);

            resultado.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fa-solid fa-circle-xmark"></i>
                    Ocurrió un error al buscar las cajas.
                </div>
            `;
        }
    });

    document.getElementById('modal_reporte_cajas')
        .addEventListener('hidden.bs.modal', function () {

            $('#id_terminal_reporte')
                .val('0')
                .trigger('change');

            $('#id_usuario_reporte')
                .html('<option value="0">Todos los vendedores</option>')
                .val('0')
                .trigger('change');

            document.getElementById('fecha_cierre').value =
                new Date().toISOString().split('T')[0];

            document.getElementById('resultado_reporte_cajas').innerHTML = '';

        });
})