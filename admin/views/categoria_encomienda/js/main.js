import * as instance from "../../../public/js/instance.js"

document.getElementById("link_ctg_encomienda").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_ctg_encomienda').DataTable({
        "ajax": {
            'url': $('#url').val() + 'categoria_encomienda/dataTable',
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
                "data": "descripcion",
            },
            {
                "data": "precio",
                render: (data, type, row) => {
                    return `S/ ${row.precio ? row.precio : '0.00'}`
                }
            },
            {
                "data": "afectacion",
            },
            {
                "data": "codigo",
                render: (data, type, row) => {
                    let codigo = row.codigo ? row.codigo : '---'
                    return `${codigo}`
                }
            },
            {
                "data": "id_ctg_encomienda",
                render: function (data, type, row) {
                    let newRow = `
                        <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR DESCRIPCIÓN ENCOMIENDA | ${row["descripcion"]}">Editar</a>
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
    let id_ctg_encomienda = document.querySelector("#id_ctg_encomienda")
    let descripcion = document.querySelector("#descripcion")
    let precio = document.querySelector("#precio")
    let afectacion = document.querySelector("#afectacion")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    instance.Modal.change_name(["#modal"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual")
    instance.Validate.allowInputMoney(["#precio"])

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_ctg_encomienda.value = ""
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("descripcion"), formData.get("precio"),
            formData.get("afectacion")
        ]
        if (instance.Validate.validateData(data)) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "categoria_encomienda/crud_register",
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
    $('#table_ctg_encomienda tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_ctg_encomienda.value = data.id_ctg_encomienda
        descripcion.value = data.descripcion
        precio.value = data.precio
        afectacion.value = data.afectacion
    })

    //Delete
    $('#table_ctg_encomienda tbody').on('click', '.btnDeleteRegis', function (e) {
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
                    url: instance._URL_ + "categoria_encomienda/delete_register",
                    data: {
                        id_ctg_encomienda: data.id_ctg_encomienda,
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