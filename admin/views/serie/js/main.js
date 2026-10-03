import * as instance from "../../../public/js/instance.js"

document.getElementById("link_serie").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_serie').DataTable({
        "ajax": {
            'url': $('#url').val() + 'serie/dataTable',
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
                render: (data, type, row) => {
                    return `
                    ${row.terminal_nombre}<br>
                    <span class="text-secondary">${row.terminal_tipo}</span>

                    `
                }
            },
            {
                "data": "cod_domicilio_fiscal",
            },
            {
                "data": "tp_comprobante",
            },
            {
                "data": "serie",
            },
            {
                "data": "correlativo",
            },
            {
                "data": "id_serie",
                render: (data, type, row) => {
                    let newRow = `
                        <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR SERIE | ${row["serie"]}">Editar</a>
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
    let id_serie = document.querySelector("#id_serie")
    let terminal = document.querySelector("#terminal")
    let tp_comprobante = document.querySelector("#tp_comprobante")
    let serie = document.querySelector("#serie")
    let correlativo_inicial = document.querySelector("#correlativo_inicial")

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
        id_serie.value = ""
        $("#tp_comprobante").val("").trigger("change")
        $("#terminal").val("").trigger("change")
    })

    // Envio de registro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = '';
        //Validacion de campos
        const isEditMode = id_serie.value !== ""; // Verifica si estás en modo edición

        if (!isEditMode) {
            if (tp_comprobante.value !== "7" && tp_comprobante.value !== "8") {
                data = [
                    formData.get("terminal"), formData.get("tp_comprobante"),
                    formData.get("serie"), formData.get("correlativo")
                ];
            } else {
                data = [
                    formData.get("terminal"), formData.get("tp_comprobante"),
                    formData.get("serie2"), formData.get("correlativo2"),
                    formData.get("serie3"), formData.get("correlativo3")
                ];
            }
        } else {
            data = [
                formData.get("terminal"), formData.get("tp_comprobante"),
                formData.get("serie"), formData.get("correlativo")
            ];
        }

        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + 'serie/crud_register', {
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
    $('#table_serie tbody').on('click', '.btnEditRegis', function (e) {
        CampoPrimero.style.display = 'block';
        CamposOcultos.style.display = 'none';
        const data = table.row($(this).parents()).data()
        id_serie.value = data.id_serie
        $("#terminal").val(data.id_terminal).trigger("change")
        $("#tp_comprobante").val(data.id_tp_comprobante).trigger("change")
        serie.value = data.serie
        correlativo.value = data.correlativo
    })

    //Delete
    $('#table_serie tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.serie}</span>
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
                formData.set("id_serie", data.id_serie)
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

    // Función para mostrar u ocultar el grupo de campos
    function toggleGrupoCampos() {
        const isEditMode = id_serie.value !== ""; // Verifica si estás en modo edición

        if (!isEditMode) {
            const isComprobante7or8 = tp_comprobante.value === "7" || tp_comprobante.value === "8";

            // Mostrar u ocultar campos según la condición
            CampoPrimero.style.display = isComprobante7or8 ? 'none' : 'block';
            CamposOcultos.style.display = isComprobante7or8 ? 'block' : 'none';
        }
    }

    toggleGrupoCampos();

    $("#tp_comprobante").on('change', toggleGrupoCampos);
})