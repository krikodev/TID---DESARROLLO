import * as instance from "../../../public/js/instance.js"

document.getElementById("link_pasajero").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_pasajero").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_pasajero").closest(".submenu").classList.add("show")
document.getElementById("link_pasajero").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    let loader = document.getElementById("loader");
    loader.classList.remove("d-none")

    const table = $('#table_pasajero').DataTable({
        processing: true,
        serverSide: true,
        "ajax": {
            'url': $('#url').val() + 'pasajero/dataTable',
            'method': 'POST',
            data: function (d) {
                d.terminal_pasajeros = $("#filtro_terminal_pasajeros").val();
                d.tp_docu_pasajeros = $("#filtro_tp_docu_pasajeros").val();
                d.estado_pasajeros = $("#filtro_estado_pasajeros").val();
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
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
                "data": "terminal",
            },
            {
                "data": "apellidos",
                render: (data, type, row) => {
                    return `${row.nombres} ${row.apellidos}`
                }
            },
            {
                "data": "genero",
            },
            {
                "data": "tp_docu",
            },
            {
                "data": "num_docu",
            },
            {
                "data": "fecha_nacimiento",
                render: (data, type, row) => {
                    return row.fecha_nacimiento ? row.fecha_nacimiento : '-----'
                }
            },
            {
                "data": "nacionalidad",
                render: (data, type, row) => {
                    return row.nacionalidad ? row.nacionalidad : '-----'
                }
            },
            {
                "data": "celular",
                render: (data, type, row) => {
                    return row.celular ? row.celular : 'S/N'
                }
            },
            {
                "data": "direccion",
            },
            {
                "data": "ubigeo",
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
                    let link_impresion = instance.CONSTS.URL.HISTORIAL.PASAJERO;
                    return `
                <div class="dropdown">
                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu">
                            <a class="dropdown-item btn_dropdown btnSmallEditRegis mb-1" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR PASAJERO | ${row["nombres"]} ${row["apellidos"]}">Editar</a>
                            <a class="dropdown-item btn_dropdown btnSmallDeleteRegis mb-1">Eliminar</a>
                            <a class="dropdown-item btn_dropdown mb-1" href="${instance._URL_ + link_impresion + row.id_usuario}" target="_blank">Historial</a>
                    </ul>
                </div>
             `
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    // Filtros específicos de Pasajeros
    let filtro_terminal_pasajeros = document.querySelector("#filtro_terminal_pasajeros")
    let filtro_tp_docu_pasajeros = document.querySelector("#filtro_tp_docu_pasajeros")
    let filtro_estado_pasajeros = document.querySelector("#filtro_estado_pasajeros")

    let btnFiltrarPasajeros = document.querySelector("#btnFiltrarPasajeros")
    let btnLimpiarPasajeros = document.querySelector("#btnLimpiarPasajeros")

    // Variables Form
    let form = document.querySelector("#form")
    let id_pasajero = document.querySelector("#id_pasajero")
    let tp_docu = document.querySelector("#tp_docu")
    let num_docu = document.querySelector("#num_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let celular = document.querySelector("#celular")
    let email = document.querySelector("#email")
    let fecha_nacimiento = document.querySelector("#fecha_nacimiento")
    let nacionalidad = document.querySelector("#nacionalidad")
    let estado_sunat = document.querySelector("#estado_sunat")
    let condicion_sunat = document.querySelector("#condicion_sunat")
    let estado = document.querySelector("#estado")

    let table_contacto = document.querySelector("#table_contacto")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")
    let button_loadSearch = document.querySelector("#button_loadSearch")

    instance.Modal.change_name(["#modal"])
    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")

    instance.select.createSelect("#filtro_terminal_pasajeros", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_tp_docu_pasajeros", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_estado_pasajeros", null, "Todos")
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")

    btnFiltrarPasajeros.addEventListener("click", function () { table.ajax.reload(); })
    btnLimpiarPasajeros.addEventListener("click", function () {
        $("#filtro_terminal_pasajeros").val("").trigger("change");
        $("#filtro_tp_docu_pasajeros").val("").trigger("change");
        $("#filtro_estado_pasajeros").val("").trigger("change");
        $(".inputSearch").val("");
        table.search("").draw();
    })

    window.exportarExcelPasajeros = function () {
        const terminal = $("#filtro_terminal_pasajeros").val();
        const tp_docu = $("#filtro_tp_docu_pasajeros").val();
        const estado = $("#filtro_estado_pasajeros").val();
        const search = table.search();
        let url = instance._URL_ + 'pasajero/exportarExcelPasajeros?';
        const params = [];
        if (terminal) {
            params.push('terminal_pasajeros=' + encodeURIComponent(terminal));
        }
        if (tp_docu) {
            params.push('tp_docu_pasajeros=' + encodeURIComponent(tp_docu));
        }
        if (estado !== '') {
            params.push('estado_pasajeros=' + encodeURIComponent(estado));
        }
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }
        window.open(url + params.join('&'), '_blank');
    };
    document.querySelector("#btnExportarExcelPasajeros")
        .addEventListener("click", function () {
            exportarExcelPasajeros();
        });

    window.exportarPDFPasajeros = function () {
        const terminal = $("#filtro_terminal_pasajeros").val();
        const tp_docu = $("#filtro_tp_docu_pasajeros").val();
        const estado = $("#filtro_estado_pasajeros").val();
        const search = table.search();
        let url = instance._URL_ + 'pasajero/exportarPDFPasajeros?';
        const params = [];
        if (terminal) {
            params.push(
                'terminal_pasajeros=' + encodeURIComponent(terminal)
            );
        }
        if (tp_docu) {
            params.push(
                'tp_docu_pasajeros=' + encodeURIComponent(tp_docu)
            );
        }
        if (estado !== '') {
            params.push(
                'estado_pasajeros=' + encodeURIComponent(estado)
            );
        }
        if (search) {
            params.push(
                'search=' + encodeURIComponent(search)
            );
        }
        window.open(url + params.join('&'), '_blank');
    };
    document.querySelector("#btnExportarPDFPasajeros")
        .addEventListener("click", function () {
            exportarPDFPasajeros();
        });

    instance.Validate.allowInputNum(["#celular"])
    instance.Validate.allowInputStringSpace(["#nombres", "#apellidos"])

    window.selectNacionalidad = new TomSelect("#nacionalidad", {
        valueField: "nombre_pais",
        labelField: "nombre_pais",
        searchField: "nombre_pais",
        maxOptions: 5,
        plugins: ['clear_button'],
        preload: 'focus',

        shouldLoad: function (query) {
            return query.length >= 2;
        },

        load: function (query, callback) {
            fetch(instance._URL_ + "pasajero/buscar_nacionalidad?q=" + encodeURIComponent(query))
                .then(res => res.json())
                .then(json => callback(json))
                .catch(() => callback());
        },

        onInitialize: function () {
            this.addOption({
                cod_pais: "174",
                nombre_pais: "PERÚ"
            });

            this.setValue("PERÚ");
        }
    });

    function cargarNacionalidad(valor) {
        if (!valor) { return; }
        fetch(
            instance._URL_ +
            "personal/buscar_nacionalidad?q=" +
            encodeURIComponent(valor)
        )
            .then(res => res.json())
            .then(json => {
                if (json.length > 0) {
                    selectNacionalidad.addOption(json);
                    selectNacionalidad.setValue(valor);
                } else {
                    selectNacionalidad.addOption({ nombre_pais: valor });
                    selectNacionalidad.setValue(valor);
                }
            })
            .catch(() => {
                selectNacionalidad.addOption({ nombre_pais: valor });
                selectNacionalidad.setValue(valor);
            });
    }

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_pasajero.value = ""
        $("#ubigeo").val("").trigger("change")
        $("#terminal").val("").trigger("change")
        button_search.classList.remove("d-none")
        $('#table_contacto tbody tr').remove();
        nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
        nombres.parentElement.classList.remove("d-none")
        apellidos.parentElement.classList.remove("d-none")
        fecha_nacimiento.parentElement.classList.remove("d-none");
        genero.parentElement.classList.remove("d-none");
        estado_sunat.parentElement.classList.add("d-none");
        condicion_sunat.parentElement.classList.add("d-none");
        selectNacionalidad.setValue("PERÚ");
        tp_docu.value = 1;
        tp_docu.dispatchEvent(new Event("change"));
    })

    fetch(instance._URL_ + "pasajero/buscar_nacionalidad?q=PERÚ")
        .then(res => res.json())
        .then(data => {

            const peru = data.find(pais => pais.cod_pais == 174);

            if (peru) {
                window.selectNacionalidad.addOption(peru);
                window.selectNacionalidad.setValue(peru.cod_pais);
            }

        })
        .catch(error => {
            console.error("Error cargando nacionalidad por defecto:", error);
        });

    // Envio de registro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("num_docu"), formData.get("nombres"),
            formData.get("ubigeo"), formData.get("direccion"),
            formData.get("terminal"), formData.get("estado"),
            formData.get("nacionalidad")
        ]
        if (tp_docu.value == 1) {
            data.push(formData.get("apellidos"), formData.get("fecha_nacimiento"))
        }

        let nombres_contacto = document.querySelectorAll(".nombres_contacto")
        let apellidos_contacto = document.querySelectorAll(".apellidos_contacto")
        let celular_contacto = document.querySelectorAll(".celular_contacto")
        let observacion_contacto = document.querySelectorAll(".observacion_contacto")
        let contactos = []
        Object.values(nombres_contacto).map((e, i, array) => {
            return contactos.push([e.value, apellidos_contacto[i].value, celular_contacto[i].value, observacion_contacto[i].value])
        })

        if (instance.Validate.validateData(data)) {
            formData.set("contactos", JSON.stringify(contactos))
            $.ajax({
                type: "post",
                url: instance._URL_ + "pasajero/crud_register",
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
    $('#table_pasajero tbody').on('click', '.btnSmallEditRegis', function (e) {
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
        id_pasajero.value = data.id_usuario
        tp_docu.value = data.id_tp_docu
        num_docu.value = data.num_docu
        nombres.value = data.nombres
        apellidos.value = data.apellidos
        fecha_nacimiento.value = data.fecha_nacimiento
        genero.value = data.genero
        $("#ubigeo").val(data.ubigeo).trigger("change")
        direccion.value = data.direccion
        celular.value = data.celular
        email.value = data.email
        $("#terminal").val(data.id_terminal).trigger("change")
        cargarNacionalidad(data.nacionalidad);
        estado_sunat.value = data.estado_sunat
        condicion_sunat.value = data.condicion_sunat
        estado.value = data.estado
        $.ajax({
            type: "post",
            url: instance._URL_ + "pasajero/get_contactosForID",
            data: {
                id_pasajero: data.id_usuario
            },
            success: function (response) {
                try {
                    let reply = JSON.parse(response)
                    if (reply.success) {
                        set_contactos(reply.message)
                    }
                } catch {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operación erronea',
                        text: 'Ha ocurrido un error al cargar los contactos del cliente.',
                    })
                }
            }
        });
    })

    //Delete
    $('#table_pasajero tbody').on('click', '.btnSmallDeleteRegis', function (e) {
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
                    url: instance._URL_ + "pasajero/delete_register",
                    data: {
                        id_pasajero: data.id_usuario,
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

    //Historial
    //Delete
    $('#table_pasajero tbody').on('click', '.btnSmallHistoriaRegis', function (e) {
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
                    url: instance._URL_ + "pasajero/delete_register",
                    data: {
                        id_pasajero: data.id_usuario,
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
            let formData = new FormData();
            formData.set("docu", num_docu.value)
            button_search.classList.add("d-none")
            button_loadSearch.classList.remove("d-none")
            fetch(instance._URL_ + 'api/' + ruta_query, {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        if (tp_docu.value == 6) {
                            nombres.value = data.data.nombre_o_razon_social
                            estado_sunat.value = data.data.estado
                            condicion_sunat.value = data.data.condicion
                            if (data.data.departamento !== "" || data.data.provincia !== "" || data.data.distrito !== "") {
                                $("#ubigeo").val(data.data.ubigeo[2]).trigger("change")
                                direccion.value = data.data.direccion
                                $('#button_save_p').focus();
                            }
                        } else {
                            nombres.value = data.data.nombres
                            apellidos.value = `${data.data.apellido_paterno} ${data.data.apellido_materno}`
                            $("#fecha_nacimiento").focus();
                        }
                        //$("#ubigeo").val(data.data.ubigeo_sunat).trigger("change")
                        instance.Toast.operacion_exitosa('Datos encontrados')
                    } else {
                        instance.Toast.operacion_erronea('Datos no hallados :c')
                        $("#nombres").focus();
                    }
                })
                .catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_search.classList.remove("d-none")
                    button_loadSearch.classList.add("d-none")
                })
        } else instance.Toast.operacion_erronea('Rellene el campo correctamente.')
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
            fecha_nacimiento.parentElement.classList.add("d-none")
            genero.parentElement.classList.add("d-none")
            button_search.disabled = false;
            num_docu.setAttribute("inputmode", "numeric");
            num_docu.oninput = function () {
                this.value = this.value.replace(/\D/g, "");
            };
        } else if (tp_docu.value == 4) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true;
            num_docu.removeAttribute("inputmode");
            num_docu.oninput = null;
        } else if (tp_docu.value == 7) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true;
            num_docu.removeAttribute("inputmode");
            num_docu.oninput = null;
        } else if (tp_docu.value == 0) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = true;
            num_docu.removeAttribute("inputmode");
            num_docu.oninput = null;
        } else {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.remove("d-none")
            fecha_nacimiento.parentElement.classList.remove("d-none")
            genero.parentElement.classList.remove("d-none")
            apellidos.setAttribute("readonly", true)
            num_docu.setAttribute("minlength", 8)
            num_docu.setAttribute("maxlength", 8)
            estado_sunat.parentElement.classList.add("d-none")
            condicion_sunat.parentElement.classList.add("d-none")
            button_search.disabled = false;
            num_docu.setAttribute("inputmode", "numeric");
            num_docu.oninput = function () {
                this.value = this.value.replace(/\D/g, "");
            };
        }
    });

    tp_docu.dispatchEvent(new Event("change"));

    // Licencia
    $('#table_contacto thead').on('click', '#btn_add_contacto', function (e) {
        let tbody = table_contacto.getElementsByTagName("tbody")[0]
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input type='text' class='form-control nombres_contacto' required></td>
                <td><input type='text' class='form-control apellidos_contacto' required></td>
                <td><input type='text' class='form-control celular_contacto' min-length='9' max-length='9' required></td>
                <td><textarea class='form-control observacion_contacto' row='3' column='10'></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `)
    })

    $('#table_contacto tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_contacto tbody").removeChild(this.closest("tr"))
    });

    const set_contactos = (data) => {
        let tbody = table_contacto.getElementsByTagName("tbody")[0]
        Object.values(data).forEach((e, i, a) => {
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input type='text' class='form-control nombres_contacto' value='${e.nombres}' required></td>
                <td><input type='text' class='form-control apellidos_contacto' value='${e.apellidos}' required></td>
                <td><input type='text' class='form-control celular_contacto' min-length='9' max-length='9' value='${e.celular}' required></td>
                <td><textarea class='form-control observacion_contacto' row='3' column='10' value='${e.observacion}'></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `)
        })
    }

    // Cuando todo haya cargado (imágenes, css, etc.)
    window.addEventListener("load", function () {
        loader.classList.add("d-none")
    });
})