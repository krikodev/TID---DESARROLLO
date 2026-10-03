import * as instance from "../../../public/js/instance.js"

document.getElementById("link_personal").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_personal").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_personal").closest(".submenu").classList.add("show")
document.getElementById("link_personal").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    const table = $('#table_personal').DataTable({
        "ajax": {
            'url': $('#url').val() + 'personal/dataTable',
            'method': 'POST',
            data: function (d) {
                d.terminal_personal = $("#filtro_terminal_personal").val();
                d.tp_usuario_personal = $("#filtro_tp_usuario_personal").val();
                d.genero_personal = $("#filtro_genero_personal").val();
                d.estado_personal = $("#filtro_estado_personal").val();
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
        fixedHeader: true,
        dom: 'rtip',
        scrollX: true,
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
                "data": "fecha_nacimiento",
            },
            {
                "data": "num_docu",
            },
            {
                "data": "genero",
            },
            {
                "data": "celular",
            },
            {
                "data": "email",
            },
            {
                "data": "direccion",
            },
            {
                "data": "ubigeo",
            },
            {
                "data": "tp_usuario",
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
                        <a class="btn btnEditRegis mb-1" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR PERSONAL | ${row["nombres"]} ${row["apellidos"]}">Editar</a>
                        <a class="btn btnDeleteRegis mb-1">Eliminar</a>
                        <a class="btn btnSendEmail mb-1">Enviar credenciales</a>
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
    let id_personal = document.querySelector("#id_personal")
    let tp_docu = document.querySelector("#tp_docu")
    let num_docu = document.querySelector("#num_docu")
    let button_search = document.querySelector("#button_search")
    let nombres = document.querySelector("#nombres")
    let apellidos = document.querySelector("#apellidos")
    let f_naci = document.querySelector("#f_naci")
    let estado_civil = document.querySelector("#estado_civil")
    let genero = document.querySelector("#genero")
    let direccion = document.querySelector("#direccion")
    let celular = document.querySelector("#celular")
    let email = document.querySelector("#email")
    let pass = document.querySelector("#pass")
    let generar_pass = document.querySelector("#generar_pass")
    let generrar_passLoad = document.querySelector("#generrar_passLoad")
    let nacionalidad = document.querySelector("#nacionalidad")
    let tp_usuario = document.querySelector("#tp_usuario")
    let estado = document.querySelector("#estado")
    // ===== NUEVO: Variables para comisiones =====
    let comision_info_text = document.querySelector("#comision_info_text");

    // ===== NUEVO: Cargar porcentajes de comisión desde configuración =====
    let comisiones = { nivel1: 0, nivel2: 0 };
    // Permisos
    let p_pasaje = document.querySelector("#p_pasaje")
    let p_flete = document.querySelector("#p_flete")
    let p_encomienda = document.querySelector("#p_encomienda")
    let p_listado_comprobante = document.querySelector("#p_listado_comprobante")
    let p_listado_nota_venta = document.querySelector("#p_listado_nota_venta")
    let p_guia_transportista = document.querySelector("#p_guia_transportista")
    let p_contra_maestra = document.getElementById("p_contra_maestra")
    let p_cotizacion = document.getElementById("p_cotizacion")
    let p_valorizacion = document.getElementById("p_valorizacion")
    let p_programacion = document.querySelector("#p_programacion")
    let p_programacion_salida = document.querySelector("#p_programacion_salida")
    let p_vehiculo = document.querySelector("#p_vehiculo")
    let p_caja_chica = document.querySelector("#p_caja_chica")
    let p_comprobantesreport = document.querySelector("#p_comprobantesreport")
    let p_encomiendasreport = document.querySelector("#p_encomiendasreport")
    let p_viaje_report = document.querySelector("#p_viaje_report")
    let p_notas = document.querySelector("#p_notas")
    let p_pasajero = document.querySelector("#p_pasajero")
    let p_personal = document.querySelector("#p_personal")
    let p_proveedor = document.querySelector("#p_proveedor")
    let p_conductor = document.querySelector("#p_conductor")
    let p_terminal = document.querySelector("#p_terminal")
    let p_almacen = document.querySelector("#p_almacen")
    let p_empresa = document.querySelector("#p_empresa")
    let p_producto_encomienda = document.querySelector("#p_producto_encomienda")
    let p_tp_servicio_pasaje = document.querySelector("#p_tp_servicio_pasaje")
    let p_serie = document.querySelector("#p_serie")
    let p_medio_pago = document.querySelector("#p_medio_pago")
    let p_config = document.querySelector("#p_config")
    let p_anular_comprobante = document.querySelector("#p_anular_comprobante");
    let p_anular_notaventa = document.getElementById("p_anular_notaventa");
    let p_posponer_pasaje = document.querySelector("#p_posponer_pasaje")
    let p_cambiar_asiento = document.querySelector("#p_cambiar_asiento")
    let p_enviar_resumen = document.querySelector("#p_enviar_resumen")
    let permiso_m_precio = document.getElementById("permiso_m_precio")
    let p_desbloquear_reservado = document.getElementById("chk_p_desbloquear_reservado")
    let p_facturador = document.querySelector("#p_facturador")
    let p_pago_bloque = document.querySelector("#p_pago_bloque")
    let p_cupon = document.querySelector("#p_cupon")

    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")
    let button_loadSearch = document.querySelector("#button_loadSearch");

    let ContraMaestraParent = document.getElementById("ContraMaestraParent");

    instance.Modal.change_name(["#modal"])
    instance.select.createSelect("#ubigeo", "#div_parentUbigeo")
    instance.select.createSelect("#terminal", "#div_parentTerminal")
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual")

    instance.select.createSelect("#filtro_terminal_personal", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_tp_usuario_personal", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_genero_personal", null, "Todos")
    instance.select.createSelectSinSearch("#filtro_estado_personal", null, "Todos")

    document.querySelector("#btnFiltrarPersonal").addEventListener("click", function () {
        table.ajax.reload();
    });
    document.querySelector("#btnExportarExcelPersonal").addEventListener("click", function () {
        exportarExcelPersonal();
    });
    document.querySelector("#btnExportarPDFPersonal").addEventListener("click", function () {
        exportarPDFPersonal();
    });

    document.querySelector("#btnLimpiarPersonal").addEventListener("click", function () {
        $("#filtro_terminal_personal").val("").trigger("change");
        $("#filtro_tp_usuario_personal").val("").trigger("change");
        $("#filtro_genero_personal").val("").trigger("change");
        $("#filtro_estado_personal").val("").trigger("change");

        $(".inputSearch").val("");

        table.search("").draw();
    });

    fetch(instance._URL_ + "personal/get_tipos_usuario")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(tipo => {
                    $("#filtro_tp_usuario_personal").append(
                        new Option(tipo.descripcion, tipo.id_tp_usuario)
                    );
                });
            }
        })
        .catch(error => console.error("Error al cargar tipos de usuario:", error));

    window.exportarExcelPersonal = function () {
        const terminal = $("#filtro_terminal_personal").val();
        const tp_usuario = $("#filtro_tp_usuario_personal").val();
        const genero = $("#filtro_genero_personal").val();
        const estado = $("#filtro_estado_personal").val();
        const search = table.search();

        let url = instance._URL_ + 'personal/exportarExcelPersonal?';

        const params = [];
        if (terminal) {
            params.push('terminal_personal=' + encodeURIComponent(terminal));
        }
        if (tp_usuario) {
            params.push('tp_usuario_personal=' + encodeURIComponent(tp_usuario));
        }
        if (genero) {
            params.push('genero_personal=' + encodeURIComponent(genero));
        }
        if (estado !== '') {
            params.push('estado_personal=' + encodeURIComponent(estado));
        }
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }

        window.open(url + params.join('&'), '_blank');
    }

    window.exportarPDFPersonal = function () {
        const terminal = $("#filtro_terminal_personal").val();
        const tp_usuario = $("#filtro_tp_usuario_personal").val();
        const genero = $("#filtro_genero_personal").val();
        const estado = $("#filtro_estado_personal").val();
        const search = table.search();

        let url = instance._URL_ + 'personal/exportarPDFPersonal?';

        const params = [];
        if (terminal) {
            params.push('terminal_personal=' + encodeURIComponent(terminal));
        }
        if (tp_usuario) {
            params.push('tp_usuario_personal=' + encodeURIComponent(tp_usuario));
        }
        if (genero) {
            params.push('genero_personal=' + encodeURIComponent(genero));
        }
        if (estado !== '') {
            params.push('estado_personal=' + encodeURIComponent(estado));
        }
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }

        window.open(url + params.join('&'), '_blank');
    };

    instance.Validate.allowInputNum(["#num_docu", "#celular"])
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
            fetch(instance._URL_ + "personal/buscar_nacionalidad?q=" + encodeURIComponent(query))
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
        id_personal.value = ""
        $("#ubigeo").val("").trigger("change")
        $("#terminal").val("").trigger("change")
        button_search.classList.remove("d-none")

        document.querySelectorAll(".form-check-input").forEach((e) => e.removeAttribute("checked"));
        $("#ContraMaestraParent").addClass("d-none");
        selectNacionalidad.setValue("PERÚ");
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        const permisos = document.querySelectorAll(".permiso-modulo");

        let formData = new FormData(form);

        // Módulos dinámicos
        document.querySelectorAll(".permiso-modulo").forEach(chk => {
            formData.set(chk.id, chk.checked ? 1 : 0);
        });

        // Permisos especiales
        formData.set("p_anular_comprobante", p_anular_comprobante.checked ? 1 : 0);
        formData.set("p_anular_notaventa", p_anular_notaventa.checked ? 1 : 0);
        formData.set("p_posponer_pasaje", p_posponer_pasaje.checked ? 1 : 0);
        formData.set("p_cambiar_asiento", p_cambiar_asiento.checked ? 1 : 0);
        formData.set("p_enviar_resumen", p_enviar_resumen.checked ? 1 : 0);
        formData.set("permiso_m_precio", permiso_m_precio.checked ? 1 : 0);
        formData.set("p_desbloquear_reservado", p_desbloquear_reservado.checked ? 1 : 0);

        let data = [
            formData.get("num_docu"), formData.get("nombres"),
            formData.get("apellidos"), formData.get("ubigeo"),
            formData.get("direccion"), formData.get("celular"),
            formData.get("email"), formData.get("terminal"),
            formData.get("tp_usuario"), formData.get("estado"),
            formData.get("nacionalidad")
        ]
        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + "personal/crud_register", {
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

    // EDIT REGISTER
    $('#table_personal tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents()).data()
        id_personal.value = data.id_usuario
        num_docu.value = data.num_docu
        nombres.value = data.nombres
        apellidos.value = data.apellidos
        f_naci.value = data.fecha_nacimiento
        estado_civil.value = data.estado_civil
        genero.value = data.genero
        $("#ubigeo").val(data.ubigeo).trigger("change")
        direccion.value = data.direccion
        celular.value = data.celular
        email.value = data.email
        pass.value = data.contrasena ? data.contrasena : ""
        $("#terminal").val(data.id_terminal).trigger("change")
        cargarNacionalidad(data.nacionalidad);
        tp_usuario.value = data.id_tp_usuario
        estado.value = data.estado;

        if (instance._ID_USUARIO_SESION == 1 && data.id_tp_usuario == 2) {
            $("#ContraMaestraParent").removeClass("d-none");
        }

        let formData = new FormData()
        formData.set("id_usuario", data.id_usuario)
        fetch(instance._URL_ + "personal/get_otrosDatos", {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status())
            return response.json()
        }).then(data => {
            if (data.success) {
                // Permisos
                if (data.message.permisos) {
                    data.message.permisos.p_pasaje ? p_pasaje.setAttribute("checked", true) : null
                    data.message.permisos.p_flete ? p_flete.setAttribute("checked", true) : null
                    data.message.permisos.p_encomienda ? p_encomienda.setAttribute("checked", true) : null
                    data.message.permisos.p_listado_comprobante ? p_listado_comprobante.setAttribute("checked", true) : null
                    data.message.permisos.p_listado_nota_venta ? p_listado_nota_venta.setAttribute("checked", true) : null
                    data.message.permisos.p_guia_transportista ? p_guia_transportista.setAttribute("checked", true) : null
                    data.message.permisos.p_contra_maestra ? p_contra_maestra.setAttribute("checked", true) : null
                    data.message.permisos.p_cotizacion ? p_cotizacion.setAttribute("checked", true) : null
                    data.message.permisos.p_valorizacion ? p_valorizacion.setAttribute("checked", true) : null
                    data.message.permisos.p_programacion ? p_programacion.setAttribute("checked", true) : null
                    data.message.permisos.p_programacion_salida ? p_programacion_salida.setAttribute("checked", true) : null
                    data.message.permisos.p_vehiculo ? p_vehiculo.setAttribute("checked", true) : null
                    data.message.permisos.p_caja_chica ? p_caja_chica.setAttribute("checked", true) : null
                    data.message.permisos.p_comprobantesreport ? p_comprobantesreport.setAttribute("checked", true) : null
                    data.message.permisos.p_encomiendasreport ? p_encomiendasreport.setAttribute("checked", true) : null
                    data.message.permisos.p_viaje_report ? p_viaje_report.setAttribute("checked", true) : null
                    data.message.permisos.p_notas ? p_notas.setAttribute("checked", true) : null
                    data.message.permisos.p_pasajero ? p_pasajero.setAttribute("checked", true) : null
                    data.message.permisos.p_personal ? p_personal.setAttribute("checked", true) : null
                    data.message.permisos.p_proveedor ? p_proveedor.setAttribute("checked", true) : null
                    data.message.permisos.p_conductor ? p_conductor.setAttribute("checked", true) : null
                    data.message.permisos.p_terminal ? p_terminal.setAttribute("checked", true) : null
                    data.message.permisos.p_almacen ? p_almacen.setAttribute("checked", true) : null
                    data.message.permisos.p_empresa ? p_empresa.setAttribute("checked", true) : null
                    data.message.permisos.p_producto_encomienda ? p_producto_encomienda.setAttribute("checked", true) : null
                    data.message.permisos.p_tp_servicio_pasaje ? p_tp_servicio_pasaje.setAttribute("checked", true) : null
                    data.message.permisos.p_serie ? p_serie.setAttribute("checked", true) : null
                    data.message.permisos.p_medio_pago ? p_medio_pago.setAttribute("checked", true) : null
                    data.message.permisos.p_config ? p_config.setAttribute("checked", true) : null
                    data.message.permisos.p_anular_comprobante ? p_anular_comprobante.setAttribute("checked", true) : null
                    data.message.permisos.p_anular_notaventa ? p_anular_notaventa.setAttribute("checked", true) : null
                    data.message.permisos.p_posponer_pasaje ? p_posponer_pasaje.setAttribute("checked", true) : null
                    data.message.permisos.p_cambiar_asiento ? p_cambiar_asiento.setAttribute("checked", true) : null
                    data.message.permisos.p_enviar_resumen ? p_enviar_resumen.setAttribute("checked", true) : null
                    data.message.permisos.p_desbloquear_reservado ? p_desbloquear_reservado.setAttribute("checked", true) : null
                    data.message.permisos.permiso_m_precio ? permiso_m_precio.setAttribute("checked", true) : null
                    data.message.permisos.p_facturador ? p_facturador.setAttribute("checked", true) : null
                    data.message.permisos.p_pago_bloque ? p_pago_bloque.setAttribute("checked", true) : null
                    data.message.permisos.p_cupon ? p_cupon.setAttribute("checked", true) : null
                }
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
    })

    //Delete
    $('#table_personal tbody').on('click', '.btnDeleteRegis', function (e) {
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
                let formData = new FormData()
                formData.set("id_personal", data.id_usuario)
                fetch(instance._URL_ + "personal/delete_register", {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                }).then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message)
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                    }
                    instance.Datatable.reloadTable(table)
                }).catch(error => instance.Toast.operacion_erronea(error.message))
            }
        })
    })

    //correo electrónico 
    $('#table_personal tbody').on('click', '.btnSendEmail', function (e) {
        const data = table.row($(this).parents()).data()
        let dataForm = new FormData()
        dataForm.set("email", data.email)
        dataForm.set("pass", data.pass)
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                <label>Se enviará un correo electrónico con sus credenciales para el acceso al sistema, al usuario:</label><br>
                <span class="mt-1 fw-bolder">${data.nombres}  ${data.apellidos}</span>
                <p class="fs-6 mt-3 mb-0 text-success">¿Está Usted de Acuerdo?</p>
                </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#429ef5',
            cancelButtonColor: '#ff3b3b',
            cancelButtonText: 'No!',
            confirmButtonText: 'Si, Adelante!',
            showLoaderOnConfirm: true,
            preConfirm: (login) => {
                return fetch(`${instance._URL_}usuario/send_email`, {
                    method: "POST",
                    body: dataForm
                }).then(response => {
                    if (!response.ok) {
                        throw new Error("Ha ocurrido un error, intentalo más tarde.")
                    }
                    return response.json()

                }).catch(error => Swal.showValidationMessage(`Ha ocurrido un error, intentalo más tarde.`))
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    instance.Toast.operacion_exitosa(result.value.message)
                    instance.Datatable.reloadTable(table)
                } else {
                    instance.Toast.operacion_erronea(result.value.message)
                }
            }
        })
    })

    tp_docu.addEventListener("change", (e) => {
        num_docu.value = ""
        nombres.value = ""
        apellidos.value = ""
        $("#ubigeo").val("").trigger("change")
        direccion.value = "S/D"
        if (tp_docu.value == 4) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            button_search.disabled = true
        } else if (tp_docu.value == 7) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            button_search.disabled = true
        } else if (tp_docu.value == 0) {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.removeAttribute("readonly")
            apellidos.parentElement.classList.remove("d-none")
            apellidos.removeAttribute("readonly")
            num_docu.setAttribute("minlength", 12)
            num_docu.setAttribute("maxlength", 12)
            button_search.disabled = true
        } else {
            nombres.closest("div").querySelector("label").innerHTML = `Nombres<span class="requiredField">*</span>`
            nombres.setAttribute("readonly", true)
            apellidos.parentElement.classList.remove("d-none")
            apellidos.setAttribute("readonly", true)
            num_docu.setAttribute("minlength", 8)
            num_docu.setAttribute("maxlength", 8)
            button_search.disabled = false
        }
    })

    // Consulta RENIEC
    document.querySelector("#button_search").addEventListener("click", (e) => {
        let reply_val = instance.Validate.validateData([num_docu.value])
        if (reply_val && num_docu.value.length == 8) {
            let formData = new FormData()
            formData.set("docu", num_docu.value)
            button_search.classList.add("d-none")
            button_loadSearch.classList.remove("d-none")
            fetch(instance._URL_ + "api/reniec", {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    nombres.value = data.data.nombres
                    apellidos.value = `${data.data.apellido_paterno} ${data.data.apellido_materno}`
                    $("#ubigeo").val(data.data.ubigeo_sunat).trigger("change")
                } else {
                    instance.Toast.operacion_erronea('Rellene el campo correctamente')
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_search.classList.remove("d-none")
                    button_loadSearch.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Rellene el campo correctamente')
        }
    })

    // Generar contraseña
    generar_pass.addEventListener("click", (e) => {
        generar_pass.classList.add("d-none")
        generrar_passLoad.classList.remove("d-none")
        fetch(instance._URL_ + "api/generate_pass")
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => pass.value = data)
            .catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                generar_pass.classList.remove("d-none")
                generrar_passLoad.classList.add("d-none")
            })
    })

    function cargarComisiones() {
        fetch(instance._URL_ + "personal/get_comisiones")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    comisiones = data.data;
                    console.log("Comisiones cargadas:", comisiones);
                }
            })
            .catch(error => console.error("Error cargando comisiones:", error));
    }

    // ===== NUEVO: Cargar comisiones al iniciar =====
    cargarComisiones();
})