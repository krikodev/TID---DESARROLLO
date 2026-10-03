import * as instance from "../../../public/js/instance.js"

document.getElementById("link_conductor").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_conductor").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_conductor").closest(".submenu").classList.add("show")
document.getElementById("link_conductor").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_conductor').DataTable({
        "ajax": {
            'url': $('#url').val() + 'conductor/dataTable',
            'method': 'POST',
            data: function (d) {
                d.terminal_conductor = $("#filtro_terminal_conductor").val();
                d.categoria_conductor = $("#filtro_categoria_conductor").val();
                d.estado_civil_conductor = $("#filtro_estado_civil_conductor").val();
                d.tp_usuario_conductor = $("#filtro_tp_usuario_conductor").val();
                d.estado_conductor = $("#filtro_estado_conductor").val();
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
        scrollX: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "terminal",
            },
            {
                "data": "num_docu",
            },
            {
                "data": "id_usuario",
                render: (data, type, row) => {
                    return `${row.nombres} ${row.apellidos}`
                }
            },
            {
                "data": "licencia",
            },
            {
                "data": "ctg_licencia",
            },
            {
                "data": "estado_civil",
            },
            {
                "data": "celular",
            },
            {
                "data": "direccion",
            },
            {
                "data": "ubigeo",
            },
            {
                "data": "id_tp_usuario",
                render: (data, type, row) => {
                    return row.id_tp_usuario == 4 ? `<span class="badge bg-info">CONDUCTOR</span>` : `<span class="badge bg-warning">COPILOTO</span>`
                }
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    if (row.estado) {
                        return `<span class="badge bg-success">Habilitado</span>`
                    } else {
                        return `<span class="badge bg-danger">Deshabilitado</span>`
                    }
                }
            },
            {
                "data": "id_usuario",
                render: (data, type, row) => {
                    return `
                <a class="btn btnEditRegis mb-1" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR CONDUCTOR / COPILOTO | ${row["nombres"]} ${row["apellidos"]}">Editar</a>
                <a class="btn btnDeleteRegis mb-1">Eliminar</a>
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
    let id_conductor = document.querySelector("#id_conductor")
    let tp_docu = document.querySelector("#tp_docu")
    let num_docu = document.querySelector("#num_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let fecha_nacimiento = document.querySelector("#fecha_nacimiento")
    let estado_civil = document.querySelector("#estado_civil")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let celular = document.querySelector("#celular")
    let email = document.querySelector("#email")
    let estado_sunat = document.querySelector("#estado_sunat")
    let condicion_sunat = document.querySelector("#condicion_sunat")
    let tp_usuario = document.querySelector("#tp_usuario")
    let estado = document.querySelector("#estado")
    let licencia = document.querySelector("#licencia")
    let ctg_licencia = document.querySelector("#ctg_licencia")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")
    let button_loadSearch = document.querySelector("#button_loadSearch")

    // let table_licencia = document.querySelector("#table_licencia")
    // let btn_add_licencia = document.querySelector("#btn_add_licencia")


    instance.Modal.change_name(["#modal"])
    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")

    instance.select.createSelect("#filtro_terminal_conductor", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_categoria_conductor", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_estado_civil_conductor", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_tp_usuario_conductor", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_estado_conductor", null, "Todos")
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")

    btnFiltrarConductor.addEventListener("click", function () { table.ajax.reload(); })
    btnLimpiarConductor.addEventListener("click", function () {
        $("#filtro_terminal_conductor").val("").trigger("change");
        $("#filtro_categoria_conductor").val("").trigger("change");
        $("#filtro_estado_civil_conductor").val("").trigger("change");
        $("#filtro_tp_usuario_conductor").val("").trigger("change");
        $("#filtro_estado_conductor").val("").trigger("change");
        $(".inputSearch").val("");
        table.search("").draw();
    });

    window.exportarExcelConductor = function () {
        const terminal = $("#filtro_terminal_conductor").val();
        const categoria = $("#filtro_categoria_conductor").val();
        const estado_civil = $("#filtro_estado_civil_conductor").val();
        const tp_usuario = $("#filtro_tp_usuario_conductor").val();
        const estado = $("#filtro_estado_conductor").val();
        const search = table.search();
        let url = instance._URL_ + 'conductor/exportarExcelConductor?';
        const params = [];
        if (terminal) {
            params.push(
                'terminal_conductor=' + encodeURIComponent(terminal)
            );
        }
        if (categoria) {
            params.push(
                'categoria_conductor=' + encodeURIComponent(categoria)
            );
        }
        if (estado_civil) {
            params.push(
                'estado_civil_conductor=' + encodeURIComponent(estado_civil)
            );
        }
        if (tp_usuario) {
            params.push(
                'tp_usuario_conductor=' + encodeURIComponent(tp_usuario)
            );
        }
        if (estado !== '') {
            params.push(
                'estado_conductor=' + encodeURIComponent(estado)
            );
        }
        if (search) {
            params.push(
                'search=' + encodeURIComponent(search)
            );
        }
        window.open(url + params.join('&'), '_blank');
    };
    document.querySelector("#btnExportarExcelConductor")
        .addEventListener("click", function () {
            exportarExcelConductor();
        });

    window.exportarPDFConductor = function () {
        const terminal = $("#filtro_terminal_conductor").val();
        const categoria = $("#filtro_categoria_conductor").val();
        const estado_civil = $("#filtro_estado_civil_conductor").val();
        const tp_usuario = $("#filtro_tp_usuario_conductor").val();
        const estado = $("#filtro_estado_conductor").val();
        const search = table.search();
        let url = instance._URL_ + 'conductor/exportarPDFConductor?';
        const params = [];
        if (terminal) {
            params.push(
                'terminal_conductor=' + encodeURIComponent(terminal)
            );
        }
        if (categoria) {
            params.push(
                'categoria_conductor=' + encodeURIComponent(categoria)
            );
        }
        if (estado_civil) {
            params.push(
                'estado_civil_conductor=' + encodeURIComponent(estado_civil)
            );
        }
        if (tp_usuario) {
            params.push(
                'tp_usuario_conductor=' + encodeURIComponent(tp_usuario)
            );
        }
        if (estado !== '') {
            params.push(
                'estado_conductor=' + encodeURIComponent(estado)
            );
        }
        if (search) {
            params.push(
                'search=' + encodeURIComponent(search)
            );
        }
        window.open(url + params.join('&'), '_blank');
    };
    document.querySelector("#btnExportarPDFConductor")
        .addEventListener("click", function () {
            exportarPDFConductor();
        });

    instance.Validate.allowInputNum(["#num_docu", "#celular"])
    instance.Validate.allowInputStringSpace(["#nombres", "#apellidos"])

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_conductor.value = ""
        $("#ubigeo").val("").trigger("change")
        $("#terminal").val("").trigger("change")
        nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
        apellidos.parentElement.classList.remove("d-none")
        apellidos.setAttribute("readonly", true)
        estado_sunat.parentElement.classList.add("d-none")
        condicion_sunat.parentElement.classList.add("d-none")
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let num_licencia = document.querySelectorAll(".num_licencia")
        let ctg_licencia = document.querySelectorAll(".ctg_licencia")
        let licencias = []
        Object.values(num_licencia).map((e, i, array) => { return licencias.push([e.value, ctg_licencia[i].value]) })
        let formData = new FormData(form)
        let data = [
            formData.get("tp_docu"), formData.get("num_docu"),
            formData.get("nombres"), formData.get("ubigeo"),
            formData.get("direccion"), formData.get("celular"),
            formData.get("email"), formData.get("terminal"),
            formData.get("estado"), formData.get("licencia"),
            formData.get("ctg_licencia"), formData.get("tp_usuario")
        ]
        if (tp_docu.value == 1) {
            data.push(formData.get("apellidos"))
        }
        if (instance.Validate.validateData(data)) {
            if (licencias && !instance.Validate.validateTodoList(licencias)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Operación erronea',
                    text: 'Rellene correctamente los campos de la tabla licencia',
                })
            } else {
                formData.set("licencias", JSON.stringify(licencias))
                $.ajax({
                    type: "post",
                    url: instance._URL_ + "conductor/crud_register",
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: formData,
                    beforeSend: (e) => {
                        button_save.classList.add("d-none")
                        button_cancel.classList.add("d-none")
                        button_loadSave.classList.remove("d-none")
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
                                $('#modal').modal('toggle')
                                instance.Datatable.reloadTable(table)
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Operación erronea',
                                    text: reply.message,
                                })
                            }
                        } catch {
                            Swal.fire({
                                icon: 'error',
                                title: 'Operación erronea',
                                text: 'Ha ocurrido un error, intentalo más tarde.',
                            })
                        }
                    }, complete: (e) => {
                        button_save.classList.remove("d-none")
                        button_cancel.classList.remove("d-none")
                        button_loadSave.classList.add("d-none")
                    }
                })
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene correctamente los campos',
            })
        }
    })

    // EDIT REGISTER
    $('#table_conductor tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        if (data.id_tp_docu == 1) {
            apellidos.parentElement.classList.remove("d-none")
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
        } else {
            apellidos.parentElement.classList.add("d-none")
            estado_sunat.parentElement.classList.remove("d-none")
            condicion_sunat.parentElement.classList.remove("d-none")
        }
        id_conductor.value = data.id_usuario
        tp_docu.value = data.id_tp_docu
        num_docu.value = data.num_docu
        nombres.value = data.nombres
        apellidos.value = data.apellidos
        fecha_nacimiento.value = data.fecha_nacimiento
        estado_civil.value = data.estado_civil
        genero.value = data.genero
        $("#ubigeo").val(data.ubigeo).trigger("change")
        direccion.value = data.direccion
        celular.value = data.celular
        email.value = data.email
        $("#terminal").val(data.id_terminal).trigger("change")
        tp_usuario.value = data.id_tp_usuario
        estado_sunat.value = data.estado_sunat
        condicion_sunat.value = data.condicion_sunat
        estado.value = data.estado
        licencia.value = data.licencia
        ctg_licencia.value = data.ctg_licencia
    })

    //Delete
    $('#table_conductor tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.nombres}  ${data.apellidos}</span>
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
                    url: instance._URL_ + "conductor/delete_register",
                    data: {
                        id_conductor: data.id_usuario,
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

    // Consulta RENIEC o SUNAT
    document.querySelector("#button_search").addEventListener("click", (e) => {
        let reply_val = instance.Validate.validateData([num_docu.value])
        if (reply_val && (num_docu.value.length == 8 || num_docu.value.length == 11)) {
            let ruta_query = tp_docu.value == 1 ? 'reniec' : 'sunat'
            $.ajax({
                type: "post",
                url: instance._URL_ + 'api/' + ruta_query,
                data: {
                    docu: num_docu.value
                },
                beforeSend: () => {
                    button_search.classList.add("d-none")
                    button_loadSearch.classList.remove("d-none")
                },
                success: (response) => {
                    try {
                        let data = JSON.parse(response)
                        if (data.success) {
                            if (tp_docu.value == 6) {
                                nombres.value = data.data.nombre_o_razon_social
                                estado_sunat.value = data.data.estado
                                condicion_sunat.value = data.data.condicion
                            } else {
                                nombres.value = data.data.nombres
                                apellidos.value = `${data.data.apellido_paterno} ${data.data.apellido_materno}`
                            }
                            $("#ubigeo").val(data.data.ubigeo_sunat).trigger("change")
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Operación erronea',
                                text: 'Rellene el campo correctamente',
                            })
                        }
                    } catch {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación erronea',
                            text: 'Ha ocurrido un error, intentalo más tarde.',
                        })
                    }
                },
                complete: () => {
                    button_search.classList.remove("d-none")
                    button_loadSearch.classList.add("d-none")
                }
            })
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene el campo correctamente',
            })
        }
    })

    tp_docu.addEventListener("change", (e) => {
        num_docu.value = ""
        nombres.value = ""
        apellidos.value = ""
        estado_sunat.value = ""
        condicion_sunat.value = ""
        $("#ubigeo").val("").trigger("change")
        direccion.value = "S/D"
        if (tp_docu.value == 6) {
            nombres.closest("div").querySelector("label").innerHTML = `Razon social<span class="requiredField">*</span>`
            nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.add("d-none")
            apellidos.setAttribute("readonly", false)
            num_docu.setAttribute("minlength", 11)
            num_docu.setAttribute("maxlength", 11)
            estado_sunat.parentElement.classList.remove("d-none")
            condicion_sunat.parentElement.classList.remove("d-none")
            button_search.disabled = false
        } else if (tp_docu.value == 4) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
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
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true
        } else {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.remove("d-none")
            apellidos.setAttribute("readonly", true)
            num_docu.setAttribute("minlength", 8)
            num_docu.setAttribute("maxlength", 8)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = false
        }
    })

    // Licencia
    $('#table_licencia thead').on('click', '#btn_add_licencia', function (e) {
        let tbody = table_licencia.getElementsByTagName("tbody")[0]
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td><input type='text' class='form-control num_licencia' required></td>
            <td><input type='text' class='form-control ctg_licencia' required></td>
            <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
        </tr>
        `)
    })

    $('#table_licencia tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_licencia tbody").removeChild(this.closest("tr"))
    });
})