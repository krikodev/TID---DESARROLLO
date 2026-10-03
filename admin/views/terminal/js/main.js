import * as instance from "../../../public/js/instance.js"

document.getElementById("link_terminal").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    instance.Toast.info_lateral("Atención!", "Recuerde que los cambios realizados en las terminales u oficinas afectarán en la emisión de comprobantes y otros módulos del sistema, por lo que tendra que reiniciar sesión para que se apliquen los cambios.");
    const table = $('#table_terminal').DataTable({
        "ajax": {
            'url': instance._URL_ + 'terminal/dataTable',
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
        scrollX: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [5, 25, 50, -1],
            ['5 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": true,

        "columns": [
            {
                "data": "nombre",
            },
            {
                "data": "tipo",
                render: (data, type, row) => {
                    return `<span class="badge text-white" style="background-color:${instance.CONSTS.COLORES.TIPO_ESTABLECIMIENTO[row.tipo]}">${row.tipo}</span>`
                }
            },
            {
                "data": "ubigeo",
                render: (data, type, row) => {
                    return `${row.ubigeo}-${row.ubi_depa}-${row.ubi_provi}-${row.ubi_distri}`
                }
            },
            {
                "data": "cod_domicilio_fiscal",
            },
            {
                "data": "direccion_fiscal",
            },
            {
                "data": "direccion_comercial",
            },
            {
                "data": "celular",
            },
            {
                "data": "email",
            },
            {
                "data": "color",
                render: (data, type, row) => {
                    return `<i class="fa-solid fa-cube fs-3" style="color:${row.color}"></i>`
                }
            },
            {
                "data": "id_terminal",
                render: (data, type, row) => {
                    return `
                        <a class="btn btnEditRegis mb-1" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR TERMINAL / OFICINA | ${row["nombre"]} ${row["tipo"]}">Editar</a>
                        <a class="btn btnDeleteRegis mb-1">Eliminar</a>
                        ${row.siteweb ? `<a class="btn btnVisitarPageWeb mb-1" href="${row.siteweb}" target="_blank">Visitar web</a>` : ''}
                    `
                },
            }
        ],
        initComplete: function () {
            this.api()
                .columns([1])
                .every(function () {
                    var column = this;
                    var select = $('<select class="form-select"><option value="">Mostrar todo</option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                });
        },
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
        "deferRender": true,
        "stateSave": false,
        "pageLength": 20,
    });

    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")
    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#t_personal", "#div_parentTPersonal")
    instance.Validate.allowInputStringSpace(["#descripcion"])
    instance.Modal.change_name(["#modal"]);

    // Variables Form
    let form = document.querySelector("#form");
    let id_terminal = document.querySelector("#id_terminal");
    let show_logo = document.querySelector("#show_logo");
    let logo = document.querySelector("#logo");
    let link_logo = document.querySelector("#link_logo");
    let logo_before = document.querySelector("#logo_before");
    let nombre = document.querySelector("#nombre");
    let tipo = document.querySelector("#tipo");
    let cod_domicilioFiscal = document.querySelector("#cod_domicilioFiscal");
    let dir_domicilioFiscal = document.querySelector("#dir_domicilioFiscal");
    let dir_domicilioComercial = document.querySelector("#dir_domicilioComercial");
    let celular = document.querySelector("#celular");
    let email = document.querySelector("#email");
    let siteweb = document.querySelector("#siteweb");
    let link_siteweb = document.querySelector("#link_siteweb");
    let color = document.querySelector("#color");
    let t_personal = document.querySelector("#t_personal")
    let Div_encargado = document.querySelector("#Div_encargado")
    let c_selva = document.querySelector("#c_selva")

    let button_save = document.querySelector("#button_save");
    let button_cancel = document.querySelector("#button_cancel");
    let button_loadSave = document.querySelector("#button_loadSave");

    instance.Upload.uploadImage(logo, show_logo)
    instance.Validate.allowInputNum(["#celular"])

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset");
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation");
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated");
        });
        Div_encargado.classList.add("d-none")
        id_terminal.value = "";
        $("#ubigeo").val("").trigger("change");
        link_logo.href = null   
        link_logo.classList.add("d-none")
        show_logo.style.backgroundImage = `url(${instance._URL_}public/image/upload/notupload_img.png)`
        link_siteweb.classList.add("d-none")
        link_siteweb.href = null
    });

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(form);
        let reply_val = instance.Validate.validateData([
            formData.get("nombre"), formData.get("tipo"),
            formData.get("ubigeo"), formData.get("cod_domicilioFiscal"),
            formData.get("dir_domicilioFiscal"), formData.get("dir_domicilioComercial"),
            formData.get("celular"), formData.get("email"),
            formData.get("color")
        ]);
        if (reply_val) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "terminal/crud_register",
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
                beforeSend: function (e) {
                    button_save.classList.add("d-none");
                    button_cancel.classList.add("d-none");
                    button_loadSave.classList.remove("d-none");
                },
                success: function (response) {
                    try {
                        let reply = JSON.parse(response);
                        if (reply["success"]) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Operación exitosa',
                                text: reply["message"],
                            })
                            $('#modal').modal('toggle');
                            instance.Datatable.reloadTable(table)
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Operación erronea',
                                text: reply["message"],
                            })
                        }
                    } catch {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación erronea',
                            text: "Ha ocurrido un error, intentalo más tarde."
                        })
                    }
                }, complete: function (e) {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none");
                    button_loadSave.classList.add("d-none");
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene correctamente los campos',
            })
        }
    })

    // Edit Register
    $('#table_terminal tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data();
        Div_encargado.classList.remove("d-none")
        if (data.logo) {
            show_logo.style.backgroundImage = `url('${instance._URL_ + data.logo}')`
            logo_before.value = data.logo
            link_logo.classList.remove("d-none")
            link_logo.href = instance._URL_ + data.logo
        }
        id_terminal.value = data.id_terminal;
        nombre.value = data.nombre;
        tipo.value = data.tipo
        $("#ubigeo").val(data.ubigeo).trigger("change")
        cod_domicilioFiscal.value = data.cod_domicilio_fiscal
        dir_domicilioFiscal.value = data.direccion_fiscal
        dir_domicilioComercial.value = data.direccion_comercial
        celular.value = data.celular
        email.value = data.email
        siteweb.value = data.siteweb
        let encargado = data.encargado
        if (data.siteweb) {
            link_siteweb.classList.remove("d-none")
            link_siteweb.href = data.siteweb
        }
        c_selva.checked = data.c_selva == 1 ? true : false;
        $("#serie_manifiesto").val(data.id_serie_manifiesto); 

        //Personal asignar
        let formData = new FormData()
        formData.set("id_terminal", data.id_terminal)
        fetch(instance._URL_ + 'terminal/get_terminal_personal', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            if (data.success) {
                t_personal.innerHTML = ""
                Object.values(data.message).forEach((e) => {
                    t_personal.insertAdjacentHTML('beforeend', `<option value="${e.id_usuario}">${e.nombres} ${e.apellidos} - ${e.num_docu}</option>`)
                })
                $("#t_personal").val(encargado).trigger("change");
            } else {
                instance.Toast.operacion_informativa(data.message);
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
        color.value = data.color
    });

    // Delete Register
    $('#table_terminal tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data();
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se eliminará del sistema el registro:</label><br>
                <span class="mt-1 fw-bolder">${data.nombre}</span>
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
                    url: instance._URL_ + "terminal/delete_register",
                    data: {
                        id_terminal: data.id_terminal,
                        logo: data.logo,
                    },
                    success: function (response) {
                        try {
                            let reply = JSON.parse(response);
                            if (reply["success"]) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Operación exitosa',
                                    text: reply['message'],
                                })
                                instance.Datatable.reloadTable(table)
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Operación errónea',
                                    text: reply['message'],
                                })
                            }
                        } catch {
                            Swal.fire({
                                icon: 'error',
                                title: 'Operación errónea',
                                text: "Ha ocurrido un error, intentalo más tarde.",
                            })
                        }
                    }
                });
            }
        })
    });
});