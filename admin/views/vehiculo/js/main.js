import * as instance from "../../../public/js/instance.js"

document.getElementById("link_vehiculo").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_vehiculo').DataTable({
        "ajax": {
            'url': $('#url').val() + 'vehiculo/dataTable',
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
                "data": "placa",
            },
            {
                "data": "marca",
            },
            {
                "data": "modelo",
            },
            {
                "data": "soat",
            },
            {
                "data": "num_piso",
            },
            {
                "data": "num_asiento",
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
                "data": "id_vehiculo",
                render: function (data, type, row) {
                    let newRow = `
                        <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR MEDIO DE PAGO | ${row["descripcion"]}">Editar</a>
                        <a class="btn btnDeleteRegis">Eliminar</a>
                        <a class="btn btnConfigRegis" data-bs-toggle="modal" data-bs-target="#modal_configuracion">Configuración</a>
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
    let id_vehiculo = document.querySelector("#id_vehiculo")
    let show_tarjeta_propiedad = document.querySelector("#show_tarjeta_propiedad")
    let link_tarjeta_propiedad = document.querySelector("#link_tarjeta_propiedad")
    let tarjeta_propiedad = document.querySelector("#tarjeta_propiedad")
    let tarjeta_propiedad_before = document.querySelector("#tarjeta_propiedad_before")
    let descripcion = document.querySelector("#descripcion")
    let placa = document.querySelector("#placa")
    let marca = document.querySelector("#marca")
    let modelo = document.querySelector("#modelo")
    let soat = document.querySelector("#soat")
    let serie_motor = document.querySelector("#serie_motor")
    let num_ejes = document.querySelector("#num_ejes")
    let num_piso = document.querySelector("#num_piso")
    let num_asiento = document.querySelector("#num_asiento")
    let tuc = document.querySelector("#tuc")
    let num_poliza = document.querySelector("#num_poliza")
    let estado = document.querySelector("#estado")
    let n_mtc = document.querySelector("#n_mtc")
    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    let id_vehiculo_selected = document.querySelector("#id_vehiculo_selected")

    instance.Modal.change_name(["#modal"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")
    instance.Validate.allowInputNum(["#num_piso", "#num_asiento"])
    instance.Upload.uploadFile(tarjeta_propiedad, show_tarjeta_propiedad, "pdf")

    instance.Validate.allowInputNum(["#asiento_config"])

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_vehiculo.value = ""
        show_tarjeta_propiedad.style.backgroundImage = `url("./public/image/upload/notupload_filepdf.png")`
        link_tarjeta_propiedad.classList.add("d-none")
        link_tarjeta_propiedad.href = ""
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("descripcion"), formData.get("placa"),
            formData.get("soat"), formData.get("num_piso"),
            formData.get("num_asiento"), formData.get("estado")
        ]
        if (num_piso.value <= 0 || num_piso.value > 2) {
            instance.Toast.operacion_erronea('El piso máximo es de 2.')
        } else {
            if (instance.Validate.validateData(data)) {
                button_save.classList.add("d-none")
                button_cancel.classList.add("d-none")
                button_loadSave.classList.remove("d-none")
                fetch(instance._URL_ + "vehiculo/crud_register", {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status)
                        return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message)
                            $('#modal').modal('toggle')
                            instance.Datatable.reloadTable(table)
                        } else {
                            instance.Toast.operacion_erronea(data.message)
                        }
                        button_save.classList.remove("d-none")
                        button_cancel.classList.remove("d-none")
                        button_loadSave.classList.add("d-none")
                    })
                    .catch(error => instance.Toast.operacion_erronea(error.message))
            } else {
                instance.Toast.operacion_erronea('Rellene correctamente los campos')
                button_save.classList.remove("d-none")
                button_cancel.classList.remove("d-none")
                button_loadSave.classList.add("d-none")
            }
        }
    })

    // EDIT REGISTER
    $('#table_vehiculo tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        if (data.file_tarjeta_propiedad) {
            show_tarjeta_propiedad.style.backgroundImage = `url(./public/image/upload/upload_filepdf.png)`
            link_tarjeta_propiedad.href = data.file_tarjeta_propiedad
            link_tarjeta_propiedad.classList.remove("d-none")
            tarjeta_propiedad_before.value = data.file_tarjeta_propiedad
        }
        id_vehiculo.value = data.id_vehiculo
        descripcion.value = data.descripcion
        placa.value = data.placa
        marca.value = data.marca
        modelo.value = data.modelo
        soat.value = data.soat
        serie_motor.value = data.serie_motor
        num_ejes.value = data.num_ejes
        num_piso.value = data.num_piso
        num_asiento.value = data.num_asiento
        tuc.value = data.tuc
        num_poliza.value = data.num_poliza
        estado.value = data.estado
        n_mtc.value = data.n_mtc
    })

    //Delete
    $('#table_vehiculo tbody').on('click', '.btnDeleteRegis', function (e) {
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
                let formData = new FormData()
                formData.set("id_vehiculo", data.id_vehiculo)
                fetch(instance._URL_ + "vehiculo/delete_register", {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status)
                        return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message)
                            instance.Datatable.reloadTable(table)
                        } else {
                            instance.Toast.operacion_erronea(data.message)
                        }
                    })
                    .catch(error => instance.Toast.operacion_erronea(error.message))
            }
        })
    })

    // Config
    $('#table_vehiculo tbody').on('click', '.btnConfigRegis', function (e) {
        const data = table.row($(this).parents()).data()
        let piso_config = document.querySelector("#piso_config")
        let parent_vehiculo = document.querySelector("#parent_vehiculo")
        id_vehiculo_selected.value = data.id_vehiculo
        parent_vehiculo.style.height = `${data.height}`
        if (data.num_piso) {
            for (let i = 0; i < data.num_piso; i++) {
                piso_config.insertAdjacentHTML('beforeend', `
                    <option value="${i + 1}">${i + 1}</option>
                `)
            }
        }

        let formData = new FormData()
        formData.set("id_vehiculo", data.id_vehiculo)
        fetch(instance._URL_ + "vehiculo/get_obj_vehiculo", {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    Object.values(data.message).forEach((e, i, array) => {
                        set_objeto(e.icon_obj, e.text_obj, e.tp_obj, e)
                    })
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    })

    const set_objeto = (icon_obj, span_text_content = "", tp_obj = "obj", data_obj) => {
        const parent_vehiculo = document.querySelector("#parent_vehiculo");
        let piso_config = document.querySelector("#piso_config");
        let div = document.createElement("div");
        div.className = `draggable position-absolute objeto z-3`
        div.setAttribute("floor", data_obj.piso)
        div.setAttribute("tp_obj", tp_obj)
        div.id = data_obj.id_obj_vehiculo
        tp_obj == "asiento" ? div.setAttribute("tp_asiento", data_obj.tp_asiento) : null
        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}`
        div.style.transform = `rotate(${data_obj.rotate_obj})`;
        div.insertAdjacentHTML("beforeend",
            `
                <i class="${icon_obj} icon_obj" class-icon="${icon_obj}" style="font-style: normal; font-size:${instance.CONSTS.OBJETOS.TAMANO}; color: ${data_obj.tp_obj == 'asiento' ? instance.CONSTS.COLORES.TIPO_ASIENTO[data_obj.tp_asiento.toUpperCase()] : instance.CONSTS.COLORES.OBJETO_DEFAULT}">
                    <span class="position-absolute start-50 translate-middle text_obj fw-bolder mt-3" style = "font-size:${instance.CONSTS.OBJETOS.TAMANO_TEXT}" > ${span_text_content}</span>
                </i>
                
                <i class="bi bi-dash position-absolute top-0 start-100 translate-middle icon_delete d-none"></i>
                <i class="bi bi-arrow-clockwise position-absolute top-0 start-0 translate-middle icon_rotar d-none"></i>
    `
        )

        parent_vehiculo.appendChild(div)

        // Se valida el piso actual para mostrar u ocultar el obj 
        if (piso_config.value != data_obj.piso) {
            $("div[floor='" + data_obj.piso + "']").addClass("d-none")
        }

        // Eliminar div al dar click
        div.querySelector(".icon_delete").addEventListener("click", (e) => {
            Swal.fire({
                title: '¿Está seguro que desea eliminar el objeto seleccionado?',
                text: "No podrá revertir los cambios",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    parent_vehiculo.removeChild(document.getElementById(div.id))
                }
            })
        })

        // Mostrar y ocultar span_rotar al hacer hover
        div.addEventListener("mouseover", (e) => div.querySelector(".icon_rotar").classList.remove("d-none"))
        div.addEventListener("mouseout", (e) => div.querySelector(".icon_rotar").classList.add("d-none"))

        // Mostrar y ocultar span_delete al hacer hover
        div.addEventListener("mouseover", (e) => div.querySelector(".icon_delete").classList.remove("d-none"))
        div.addEventListener("mouseout", (e) => div.querySelector(".icon_delete").classList.add("d-none"))

        let angle = 0;

        // Girar el div al dar click
        div.querySelector(".icon_rotar").addEventListener("click", (e) => {
            angle = (angle + 45) % 360;
            div.style.cssText = `
            transform: rotate(${angle}deg);
            left: ${div.style.left};
            top: ${div.style.top};
            `
        })
    }
})