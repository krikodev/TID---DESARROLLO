import * as instance from "../../../public/js/instance.js"

document.getElementById("link_almacen").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_almacen").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_almacen").closest(".submenu").classList.add("show")
document.getElementById("link_almacen").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_almacen').DataTable({
        "ajax": {
            'url': $('#url').val() + 'almacen/dataTable',
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
                "data": "terminal_nombre",
            },
            {
                "data": "terminal_tipo",
            },
            {
                "data": "descripcion",
            },
            {
                "data": "estado",
                render: function (data, type, row) {
                    if (row.estado) {
                        return `<span class="badge bg-success">Habilitado</span>`
                    } else {
                        return `<span class="badge bg-danger">Deshabilitado</span>`
                    }
                }
            },
            {
                "data": "id_almacen",
                render: function (data, type, row) {
                    let newRow = `
                    <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR ALMACEN | ${row["descripcion"]}">Editar</a>
                    <a class="btn btnDeleteRegis">Eliminar</a>
                    `
                    return newRow
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
    let id_almacen = document.querySelector("#id_almacen")
    let descripcion = document.querySelector("#descripcion")
    let estado = document.querySelector("#estado")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    instance.Modal.change_name(["#modal"])
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual")

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_almacen.value = ""
        $("#terminal").val("").trigger("change")
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("terminal"), formData.get("descripcion"),
            formData.get("estado")
        ]
        if (instance.Validate.validateData(data)) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "almacen/crud_register",
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
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene correctamente los campos',
            })
        }
    })

    // EDIT REGISTER
    $('#table_almacen tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_almacen.value = data.id_almacen
        $("#terminal").val(data.id_terminal).trigger("change")
        descripcion.value = data.descripcion
        estado.value = data.estado
    })

    //Delete
    $('#table_almacen tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.descripcion}</span>
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
                    url: instance._URL_ + "almacen/delete_register",
                    data: {
                        id_almacen: data.id_almacen,
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
})