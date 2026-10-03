import * as instance from "../../../public/js/instance.js"

document.getElementById("link_contra_maestra").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_contra_maestra').DataTable({
        "ajax": {
            'url': $('#url').val() + 'contra_maestra/dataTable',
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
                "data": "genero_usuario",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "fecha_generacion",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "clave",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "estado",
                render: (data) => {
                    if (!data) {
                        return '<span class="text-muted">—</span>';
                    }

                    const estado = String(data).toUpperCase();

                    const estados = {
                        ACTIVA: {
                            clase: 'bg-success-subtle text-success border border-success-subtle',
                            icono: 'fa-solid fa-circle-check'
                        },
                        USADA: {
                            clase: 'bg-primary-subtle text-primary border border-primary-subtle',
                            icono: 'fa-solid fa-check'
                        },
                        VENCIDA: {
                            clase: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            icono: 'fa-solid fa-clock'
                        },
                        ANULADA: {
                            clase: 'bg-danger-subtle text-danger border border-danger-subtle',
                            icono: 'fa-solid fa-ban'
                        }
                    };

                    const config = estados[estado] || {
                        clase: 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        icono: 'fa-solid fa-circle-info'
                    };

                    return `
            <span class="badge rounded-pill ${config.clase} px-3 py-2">
                <i class="${config.icono} me-1"></i>
                ${data}
            </span>
            `;
                }
            },
            {
                "data": "fecha_uso",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "uso_usuario",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "encomienda",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "observacion",
                render: (data) => data ? data : '<span class="text-muted">—</span>'
            },
            {
                "data": "id_clave_maestra",
                render: (data, type, row) => {
                    let btn_eliminar = `<button type="button"class="btn-delete-circle btnDeleteRegis" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>`;
                    return `
                    ${btn_eliminar}
                    `;
                }
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
        "deferRender": true,
        "stateSave": false,
        "pageLength": 20,
    });

    $('#button_generar_contrasena').on('click', function () {
        generarContrasenaMaestra();
    });

    function generarContrasenaMaestra() {
        const boton = $('#button_generar_contrasena');

        // Evita doble click mientras se procesa
        boton.prop('disabled', true);
        boton.html(`
        <span class="spinner-border spinner-border-sm" role="status"></span>
        Generando...
        `);

        const formData = new FormData();

        // Si luego necesitas mandar la encomienda:
        // formData.append('id_encomienda', id_encomienda);

        fetch(instance._URL_ + 'contra_maestra/generar_contrasena_maestra', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message);

                    // $('#clave_generada').val(data.clave);
                    instance.Datatable.reloadTable(table)
                    console.log("llegando");

                } else {
                    instance.Toast.operacion_informativa(
                        data.message || 'No se pudo generar la contraseña.'
                    );
                }
            })
            .catch(error => {
                console.error('Error al generar contraseña:', error);

                instance.Toast.operacion_error(
                    'Ocurrió un error al generar la contraseña.'
                );
            })
            .finally(() => {
                boton.prop('disabled', false);
                boton.html(`
                <i class="fa-solid fa-key"></i>
                Generar contraseña
            `);
            });
    }

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
    $('#table_contra_maestra tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.clave}</span>
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
                formData.set("id_clave_maestra", data.id_clave_maestra)
                fetch(instance._URL_ + 'contra_maestra/delete_register', {
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