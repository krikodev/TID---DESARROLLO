import * as instance from "../../../public/js/instance.js"

document.getElementById("link_cupon").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_cupon').DataTable({
        "ajax": {
            'url': $('#url').val() + 'cupon/dataTable',
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
                "data": "codigo",
            },
            {
                "data": "tipo",
            },
            {
                "data": "valor",
            },
            {
                "data": "tope_maximo",
            },
            {
                "data": "monto_minimo",
            },
            {
                "data": "fecha_inicio",
            },
            {
                "data": "fecha_fin",
            },
            {
                "data": "uso_maximo",
            },
            {
                "data": "uso_actual",
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    let clase = '';
                    switch (row.estado) {
                        case 'ACTIVO':
                            clase = 'badge-success-soft'
                            break;
                        case 'INACTIVO':
                            clase = 'badge-danger-soft'
                            break;
                        case 'VENCIDO':
                            clase = 'badge-secondary-soft';
                            break;
                    }

                    return `<span class="badge badge-soft ${clase}">${row.estado}</span>`
                },
            },
            {
                "data": "id_cupon",
                render: (data, type, row) => {
                    let newRow = `
                        <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR CUPON | ${row["codigo"]}">Editar</a>
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
    let id_cupon = document.querySelector("#id_cupon")
    let codigo = document.querySelector("#codigo")
    let tipo = document.querySelector("#tipo")
    let valor = document.querySelector("#valor")
    let tope_maximo = document.querySelector("#tope_maximo")
    let monto_minimo = document.querySelector("#monto_minimo")
    let fecha_inicio = document.querySelector("#fecha_inicio")
    let fecha_fin = document.querySelector("#fecha_fin")
    let uso_maximo = document.querySelector("#uso_maximo")
    let estado = document.querySelector("#estado")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")
    let CamposOcultos = document.querySelector('.grupo-campos-oculto');
    let CampoPrimero = document.querySelector('.primero');


    instance.Modal.change_name(["#modal"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual")
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante")

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_cupon.value = ""
    })

    // Envio de registro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = '';

        data = [
            formData.get("codigo"), formData.get("tipo"),
            formData.get("valor"), formData.get("tope_maximo"),
            formData.get("monto_minimo"), formData.get("fecha_inicio"),
            formData.get("fecha_fin"), formData.get("uso_maximo"),
            formData.get("estado")
        ];

        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + 'cupon/crud_register', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status)
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
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene correctamente los campos',
            })
        }
    })

    // EDIT REGISTER
    $('#table_cupon tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_cupon.value = data.id_cupon
        codigo.value = data.codigo
        tipo.value = data.tipo
        valor.value = data.valor
        tope_maximo.value = data.tope_maximo
        monto_minimo.value = data.monto_minimo
        fecha_inicio.value = data.fecha_inicio
        fecha_fin.value = data.fecha_fin
        uso_maximo.value = data.uso_maximo
        estado.value = data.estado
    })

    //Delete
    $('#table_cupon tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.codigo}</span>
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
                formData.set("id_cupon", data.id_cupon)
                fetch(instance._URL_ + 'serie/delete_register', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                }).then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message)
                        instance.Datatable.reloadTable(table)
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                    }
                }).catch(error => instance.Toast.operacion_erronea(error.message()))
            }
        })
    })

});