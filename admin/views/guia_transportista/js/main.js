import * as instance from "../../../public/js/instance.js";

document.getElementById("link_guia_transportista").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_guia_transportista").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_guia_transportista").closest(".submenu").classList.add("show")
document.getElementById("link_guia_transportista").classList.add("active");

document.addEventListener("DOMContentLoaded", function (event) {
    // Variable para almacenar los filtros actuales
    let filtrosActuales = {
        fecha_inicio: '',
        fecha_fin: '',
        estado: ''
    };

    // Variable para almacenar la instancia de la tabla
    let table = $('#table_guiatransportista').DataTable({
        "ajax": {
            'url': instance._URL_ + 'guia_transportista/dataTable',
            'method': 'POST',
            'data': function (d) {
                // Agregar los filtros a la petición AJAX
                d.filtro_fecha_inicio = filtrosActuales.fecha_inicio;
                d.filtro_fecha_fin = filtrosActuales.fecha_fin;
                d.filtro_estado = filtrosActuales.estado;
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
        "processing": true,
        "serverSide": true,
        "ordering": false,

        "columns": [
            {
                "data": "fecha_emision",
                render: function (data) {
                    if (data) {
                        return data.split(' ')[0]; // Mostrar solo la fecha sin hora
                    }
                    return '';
                }
            },
            {
                "data": "id_cliente",
                render: function (data, type, row) {
                    return (row.cliente_nombres || '') + ' ' + (row.cliente_apellidos || '') + '<br>' + (row.cliente_num_docu || '');
                }
            },
            {
                "data": "serie",
                render: function (data, type, row) {
                    return row.serie ? row.serie + '-' + row.correlativo : '---';
                }
            },
            {
                "data": "tp_moneda_codigo",
            },
            {
                "data": "total",
                render: function (data) {
                    return parseFloat(data || 0).toFixed(2);
                }
            },
            {
                "data": "estado",
                render: function (data, type, row) {
                    var estado = row.estado || '';
                    var color = '#6c757d';
                    var textColor = '#ffffff';

                    if (instance.CONSTS && instance.CONSTS.COLORES && instance.CONSTS.COLORES.ESTADO_VENTA_FONDO) {
                        color = instance.CONSTS.COLORES.ESTADO_VENTA_FONDO[estado.toUpperCase()] || '#6c757d';
                    }
                    if (instance.CONSTS && instance.CONSTS.COLORES && instance.CONSTS.COLORES.ESTADO_VENTA_TEXT) {
                        textColor = instance.CONSTS.COLORES.ESTADO_VENTA_TEXT[estado.toUpperCase()] || '#ffffff';
                    }

                    return '<span class="badge" style="background-color:' + color + '; color:' + textColor + '">' + estado + '</span>';
                }
            },
            {
                "data": "pago_e",
                render: function (data, type, row) {
                    if (row.pago_e) {
                        var ruta_impresion = '';
                        if (instance.CONSTS && instance.CONSTS.URL && instance.CONSTS.URL.IMPRESION_GUIA_TRANSPORTISTA) {
                            ruta_impresion = instance.CONSTS.URL.IMPRESION_GUIA_TRANSPORTISTA.COMPROBANTE;
                        }
                        return '<span class="badge" style="background-color:white; color:black; font-weight: 700">Pagado con: <a href="' + instance._URL_ + ruta_impresion + row.id_pago + '" target="_blank" style="color:blue;">' + row.pago_e + '</a></span>';
                    } else {
                        return '<span class="badge" style="background-color:white; color:black; font-weight: 700">-</span>';
                    }
                }
            },
            {
                "data": "id_venta",
                render: function (data, type, row) {
                    var html_xml = '';
                    var html_cdr = '';
                    var link_impresion_ticket = '';
                    var link_impresion_a4 = 'encomienda/impresion/guia_remisionA4/';

                    if (instance.CONSTS && instance.CONSTS.URL && instance.CONSTS.URL.IMPRESION_ENCOMIENDA) {
                        link_impresion_ticket = instance.CONSTS.URL.IMPRESION_ENCOMIENDA.GUIA_REMISION;
                    }

                    if (row.codigo_sunat_guia == 1) {
                        var xmlColor = '#6c757d';
                        var cdrColor = '#28a745';

                        if (instance.CONSTS && instance.CONSTS.COLORES && instance.CONSTS.COLORES.BUTTONS) {
                            xmlColor = instance.CONSTS.COLORES.BUTTONS.XML || '#6c757d';
                            cdrColor = instance.CONSTS.COLORES.BUTTONS.CDR || '#28a745';
                        }

                        html_xml = '<a class="btn mb-1 text-white" style="background-color: ' + xmlColor + '; font-size:12px" href="' + row.guia_xml + '" target="_blank" title="XML"><i class="fas fa-file-code"></i></a>';
                        html_cdr = '<a class="btn mb-1 text-white" style="background-color: ' + cdrColor + '; font-size:12px" href="' + row.guia_cdr + '" target="_blank" title="CDR"><i class="fas fa-file-check"></i></a>';
                    }

                    var newRow = `
                        <a href="` + instance._URL_ + link_impresion_ticket + row.id_guia_remision + `" target="_blank" class="btn btnColorViolet mb-1" title="Ticket (Formato pequeño)">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="` + instance._URL_ + link_impresion_a4 + row.id_guia_remision + `" target="_blank" class="btn btn-primary mb-1" title="A4 (Formato carta)">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        ` + html_xml + `
                        ` + html_cdr + `
                    `;
                    return newRow;
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
                fetch(instance._URL_ + "guia_transportista/buscar_nacionalidad?q=" + encodeURIComponent(query))
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

    // Configurar buscador general
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual");

    // Evento para aplicar filtros
    var btnFiltrar = document.getElementById('btn_filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function () {
            aplicarFiltros();
        });
    }

    // Evento para limpiar filtros
    var btnLimpiar = document.getElementById('btn_limpiar_filtros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            limpiarFiltros();
        });
    }

    // Permitir filtrar con Enter en los inputs
    var filtroIds = ['filtro_fecha_inicio', 'filtro_fecha_fin', 'filtro_estado'];
    for (var i = 0; i < filtroIds.length; i++) {
        var element = document.getElementById(filtroIds[i]);
        if (element) {
            element.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    aplicarFiltros();
                }
            });
        }
    }

    // Evento para el botón de Excel (estructura básica)
    var btnExportarExcel = document.getElementById('btn_exportar_excel');
    if (btnExportarExcel) {
        btnExportarExcel.addEventListener('click', function () {
            exportarExcel();
        });
    }

    // Función para aplicar filtros
    function aplicarFiltros() {
        // Obtener valores de los filtros
        var fechaInicio = document.getElementById('filtro_fecha_inicio');
        var fechaFin = document.getElementById('filtro_fecha_fin');
        var estado = document.getElementById('filtro_estado');

        filtrosActuales.fecha_inicio = fechaInicio ? fechaInicio.value : '';
        filtrosActuales.fecha_fin = fechaFin ? fechaFin.value : '';
        filtrosActuales.estado = estado ? estado.value : '';

        // Validar fechas si ambas están presentes
        if (filtrosActuales.fecha_inicio && filtrosActuales.fecha_fin) {
            if (filtrosActuales.fecha_inicio > filtrosActuales.fecha_fin) {
                if (instance.Toast && instance.Toast.operacion_informativa) {
                    instance.Toast.operacion_informativa('La fecha de inicio no puede ser mayor que la fecha de fin');
                }
                return;
            }
        }

        // Recargar la tabla con los nuevos filtros
        table.ajax.reload();

        if (instance.Toast && instance.Toast.operacion_exitosa) {
            instance.Toast.operacion_exitosa('Filtros aplicados correctamente');
        }
    }

    // Función para limpiar filtros
    function limpiarFiltros() {
        // Restablecer valores vacíos
        var fechaInicio = document.getElementById('filtro_fecha_inicio');
        var fechaFin = document.getElementById('filtro_fecha_fin');
        var estado = document.getElementById('filtro_estado');

        if (fechaInicio) fechaInicio.value = '';
        if (fechaFin) fechaFin.value = '';
        if (estado) estado.value = '';

        // Actualizar filtrosActuales
        filtrosActuales.fecha_inicio = '';
        filtrosActuales.fecha_fin = '';
        filtrosActuales.estado = '';

        // Recargar la tabla
        table.ajax.reload();

        if (instance.Toast && instance.Toast.operacion_exitosa) {
            instance.Toast.operacion_exitosa('Filtros limpiados correctamente');
        }
    }

    // Función para exportar a Excel (estructura básica)
    function exportarExcel() {
        // Mostrar loader
        var loader = document.getElementById('loader');
        if (loader) {
            loader.classList.remove('d-none');
        }

        // Obtener los valores actuales de los filtros
        var fechaInicio = document.getElementById('filtro_fecha_inicio');
        var fechaFin = document.getElementById('filtro_fecha_fin');
        var estado = document.getElementById('filtro_estado');

        // Crear formulario con los filtros actuales
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = instance._URL_ + 'guia_transportista/exportar_excel';  // <- Llamada correcta

        // Agregar filtros como campos ocultos
        var campos = [
            { name: 'filtro_fecha_inicio', value: fechaInicio ? fechaInicio.value : '' },
            { name: 'filtro_fecha_fin', value: fechaFin ? fechaFin.value : '' },
            { name: 'filtro_estado', value: estado ? estado.value : '' }
        ];

        for (var i = 0; i < campos.length; i++) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = campos[i].name;
            input.value = campos[i].value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        // Ocultar loader después de un tiempo
        setTimeout(function () {
            if (loader) {
                loader.classList.add('d-none');
            }
        }, 2000);
    }

    // Función para formatear fecha (definida UNA SOLA VEZ)
    function formatDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    // Tabla de guías a pagar
    var table_pagar_guias = $('#table_pagar_guias').DataTable({
        destroy: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        select: {
            style: 'multi',
        },
        "ordering": false,

        "columns": [
            {
                "data": "id_encomienda",
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": "numero_guia",
            },
            {
                "data": "fecha_emision",
            },
            {
                "data": "monto",
                render: function (data) {
                    return 'S/ ' + parseFloat(data || 0).toFixed(2);
                }
            },
            {
                "data": "detalle",
                render: function (data, type, row) {
                    if (!data || data.length === 0) return 'Sin detalle';
                    return '<div class="center-icon btnVerProd"><i class="fa-solid fa-eye fa-lg" style="color: #2350afff;" data-productos=\'' + JSON.stringify(data) + '\'></i></div>';
                }
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    });

    var hoy = new Date();

    // Fecha de emisión: 3 días antes hasta hoy
    var tresDiasAntes = new Date(hoy);
    tresDiasAntes.setDate(hoy.getDate() - 3);

    $('#fecha_emision').attr({
        'min': formatDate(tresDiasAntes),
        'max': formatDate(hoy)
    });

    /*==============APARTADO DE LOGICA CLIENTE ==========================*/
    var btn_buscarGuias = document.querySelector("#btn_buscarGuias");
    var buscar_cliente = document.querySelector("#buscar_cliente");
    var loader_buscar = document.querySelector("#loader_buscar");
    var cliente_id = document.querySelector("#cliente_id");

    var nuevo_cliente_id = document.querySelector("#nuevo_cliente_id");
    var buscar_nuevo_cliente = document.querySelector("#buscar_nuevo_cliente");
    var loader_buscar_nc = document.querySelector("#loader_buscar_nc");

    var buscar_guias = document.querySelector("#buscar_guias");
    var loader_buscar_guias = document.querySelector("#loader_buscar_guias");

    var num_docu = document.querySelector("#num_docu");
    var div_generarNuevoCliente = document.querySelector("#div_generarNuevoCliente");

    var tipoClienteRadios = document.querySelectorAll("input[name='tipo_cliente']");
    var tipo_cliente = 'cliente_actual';
    for (var j = 0; j < tipoClienteRadios.length; j++) {
        if (tipoClienteRadios[j].checked) {
            tipo_cliente = tipoClienteRadios[j].value;
            break;
        }
    }

    var button_pagar = document.querySelector("#button_pagar");
    var button_loadPagar = document.querySelector("#button_loadPagar");
    var div_facturacion = document.querySelector("#div_facturacion");
    var loading = document.querySelector("#loader");
    var div_cuota = document.querySelector("#div_cuota");

    var form_pago_guia = document.querySelector("#form_pago_guia");
    var tp_comprobante = document.querySelector("#tp_comprobante");
    var serie = document.querySelector("#serie");
    var forma_pago = document.querySelector("#forma_pago");

    if (instance.select && instance.select.createSelect) {
        instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
    }

    var medio_pagoSelect = $('#medio_pago').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        }
    });

    window.selectizeMedioPago = null;
    if (medio_pagoSelect && medio_pagoSelect[0] && medio_pagoSelect[0].selectize) {
        window.selectizeMedioPago = medio_pagoSelect[0].selectize;
    }

    if (document.querySelector("#buscar_cliente")) {
        document.querySelector("#buscar_cliente").addEventListener("click", function (e) {
            e.preventDefault();
            table_pagar_guias.clear().draw();
            limpiar_campos_facturacion();
            if (div_facturacion) {
                div_facturacion.classList.add("d-none");
            }

            var params = [8, 11];
            var inputValue = $("#cliente").val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.indexOf(inputValue.length) === -1 || !isNumber) {
                if (instance.Toast && instance.Toast.operacion_informativa) {
                    instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                }
                return;
            }

            if (btn_buscarGuias) btn_buscarGuias.classList.add('d-none');
            if (buscar_cliente) buscar_cliente.classList.add("d-none");
            if (loader_buscar) loader_buscar.classList.remove("d-none");

            var formData = new FormData();
            formData.append('cliente', inputValue);

            fetch(instance._URL_ + 'pasaje/cliente_get', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        if (cliente_id) {
                            cliente_id.value = data.message.id_usuario;
                            cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                        }
                        actualizar_tp_comprobantes(data.message.id_tp_docu);
                        $('#cliente_nombres').val(data.message.nombres_cliente);
                        if (btn_buscarGuias) btn_buscarGuias.classList.remove('d-none');
                    } else {
                        if (cliente_id) {
                            cliente_id.value = '';
                            cliente_id.dataset.tp_docu = '';
                        }
                        $('#cliente_nombres').val('');
                        if (btn_buscarGuias) btn_buscarGuias.classList.add('d-none');
                        Swal.fire({
                            'icon': 'info',
                            'title': 'Ooops...',
                            'text': 'El cliente que estas buscando no existe...',
                        });
                    }
                })
                .catch(function (error) {
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea(error.message);
                    }
                })
                .finally(function () {
                    if (buscar_cliente) buscar_cliente.classList.remove("d-none");
                    if (loader_buscar) loader_buscar.classList.add("d-none");
                });
        });
    }

    $('#cliente').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            table_pagar_guias.clear().draw();
            limpiar_campos_facturacion();
            if (div_facturacion) {
                div_facturacion.classList.add("d-none");
            }

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);
            var params = [8, 11];

            if (params.indexOf(inputValue.length) === -1 || !isNumber) {
                if (instance.Toast && instance.Toast.operacion_informativa) {
                    instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                }
                return;
            }

            if (btn_buscarGuias) btn_buscarGuias.classList.add('d-none');
            if (buscar_cliente) buscar_cliente.classList.add("d-none");
            if (loader_buscar) loader_buscar.classList.remove("d-none");

            var formData = new FormData();
            formData.append('cliente', inputValue);

            fetch(instance._URL_ + 'pasaje/cliente_get', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        if (cliente_id) {
                            cliente_id.value = data.message.id_usuario;
                            cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                        }
                        actualizar_tp_comprobantes(data.message.id_tp_docu);
                        $('#cliente_nombres').val(data.message.nombres_cliente);
                        if (btn_buscarGuias) btn_buscarGuias.classList.remove('d-none');
                    } else {
                        if (cliente_id) {
                            cliente_id.value = '';
                            cliente_id.dataset.tp_docu = '';
                        }
                        $('#cliente_nombres').val('');
                        if (btn_buscarGuias) btn_buscarGuias.classList.add('d-none');
                        Swal.fire({
                            'icon': 'info',
                            'title': 'Ooops...',
                            'text': 'El cliente que estas buscando no existe...',
                        });
                    }
                })
                .catch(function (error) {
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea(error.message);
                    }
                })
                .finally(function () {
                    if (buscar_cliente) buscar_cliente.classList.remove("d-none");
                    if (loader_buscar) loader_buscar.classList.add("d-none");
                });
        }
    });

    if (document.querySelector("#buscar_nuevo_cliente")) {
        document.querySelector("#buscar_nuevo_cliente").addEventListener("click", function (e) {
            e.preventDefault();

            var params = [8, 11];
            var inputValue = $("#nuevo_cliente").val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (params.indexOf(inputValue.length) === -1 || !isNumber) {
                if (instance.Toast && instance.Toast.operacion_informativa) {
                    instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                }
                return;
            }

            var tp_d = (inputValue.length === 11) ? 6 : 1;

            if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.add("d-none");
            if (loader_buscar_nc) loader_buscar_nc.classList.remove("d-none");

            var formData = new FormData();
            formData.append('cliente', inputValue);

            fetch(instance._URL_ + 'pasaje/cliente_get', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        if (nuevo_cliente_id) {
                            nuevo_cliente_id.value = data.message.id_usuario;
                            nuevo_cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                        }
                        actualizar_tp_comprobantes(data.message.id_tp_docu);
                        $('#nuevo_cliente_nombres').val(data.message.nombres_cliente);
                    } else {
                        if (nuevo_cliente_id) {
                            nuevo_cliente_id.value = '';
                            nuevo_cliente_id.dataset.tp_docu = '';
                        }
                        $('#nuevo_cliente_nombres').val('');

                        $("#tp_docu").val(tp_d).trigger("change");
                        if (num_docu) num_docu.value = inputValue;
                        $('#button_search').click();
                        if (num_docu) num_docu.focus();
                        $("#modal_cliente").modal("show");
                    }
                })
                .catch(function (error) {
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea(error.message);
                    }
                })
                .finally(function () {
                    if (loader_buscar_nc) loader_buscar_nc.classList.add("d-none");
                    if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.remove("d-none");
                });
        });
    }

    $('#nuevo_cliente').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);
            var params = [8, 11];

            if (params.indexOf(inputValue.length) === -1 || !isNumber) {
                if (instance.Toast && instance.Toast.operacion_informativa) {
                    instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                }
                return;
            }

            var tp_d = (inputValue.length === 11) ? 6 : 1;

            if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.add("d-none");
            if (loader_buscar_nc) loader_buscar_nc.classList.remove("d-none");

            var formData = new FormData();
            formData.append('cliente', inputValue);

            fetch(instance._URL_ + 'pasaje/cliente_get', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        if (nuevo_cliente_id) {
                            nuevo_cliente_id.value = data.message.id_usuario;
                            nuevo_cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                        }
                        actualizar_tp_comprobantes(data.message.id_tp_docu);
                        $('#nuevo_cliente_nombres').val(data.message.nombres_cliente);
                    } else {
                        if (nuevo_cliente_id) {
                            nuevo_cliente_id.value = '';
                            nuevo_cliente_id.dataset.tp_docu = '';
                        }
                        $('#nuevo_cliente_nombres').val('');

                        $("#tp_docu").val(tp_d).trigger("change");
                        if (num_docu) num_docu.value = inputValue;
                        $('#button_search').click();
                        if (num_docu) num_docu.focus();
                        $("#modal_cliente").modal("show");
                    }
                })
                .catch(function (error) {
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea(error.message);
                    }
                })
                .finally(function () {
                    if (loader_buscar_nc) loader_buscar_nc.classList.add("d-none");
                    if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.remove("d-none");
                });
        }
    });

    // Nuevo comprobantes a credito
    $("#forma_pago").on('change', function () {
        var forma_pago_val = $(this).val();
        if (forma_pago_val == 2) {
            if (window.selectizeMedioPago) {
                window.selectizeMedioPago.setValue('', true);
                window.selectizeMedioPago.disable();
            }
            if (div_cuota) div_cuota.classList.remove('d-none');
        } else {
            if (window.selectizeMedioPago) {
                window.selectizeMedioPago.enable();
            }
            if (div_cuota) div_cuota.classList.add('d-none');
        }
    });

    // Logica del boton de busqueda de guias
    if (buscar_guias) {
        buscar_guias.addEventListener("click", function (event) {
            if (buscar_guias) buscar_guias.classList.add("d-none");
            if (loader_buscar_guias) loader_buscar_guias.classList.remove("d-none");

            var formData = new FormData();
            formData.set("id_cliente", cliente_id ? cliente_id.value : '');

            fetch(instance._URL_ + 'guia_transportista/get_guiasxpagar', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    // Limpiar DataTable
                    table_pagar_guias.clear();
                    limpiar_campos_facturacion();
                    if (div_facturacion) div_facturacion.classList.add("d-none");

                    if (data.success && data.data && data.data.length > 0) {
                        var guiasValidas = [];
                        for (var k = 0; k < data.data.length; k++) {
                            if (data.data[k].numero_guia) {
                                guiasValidas.push(data.data[k]);
                            }
                        }

                        if (guiasValidas.length > 0) {
                            table_pagar_guias.rows.add(guiasValidas).draw();
                            $("#modal_detallePC").modal("show");
                        } else {
                            table_pagar_guias.draw();
                            Swal.fire({
                                icon: 'info',
                                title: 'Ooops..',
                                text: 'No se ha encontrado ninguna guia transportista pendiente de pago del cliente',
                            });
                        }
                    } else {
                        table_pagar_guias.draw();
                        Swal.fire({
                            icon: 'info',
                            title: 'Ooops..',
                            text: 'No se ha encontrado ninguna guia transportista del cliente',
                        });
                    }
                })
                .catch(function (error) {
                    table_pagar_guias.clear().draw();
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea(error.message);
                    }
                })
                .finally(function () {
                    if (buscar_guias) buscar_guias.classList.remove("d-none");
                    if (loader_buscar_guias) loader_buscar_guias.classList.add("d-none");
                });
        });
    }

    // Función para mostrar tooltip
    function mostrarTooltip(element, event) {
        var icon = $(element).find('i');
        var productosAttr = icon.attr('data-productos');
        if (!productosAttr) return;

        var productos = JSON.parse(productosAttr);
        var tooltip = document.getElementById('productosTooltip');
        if (!tooltip) return;

        var contenido = '';
        for (var p = 0; p < productos.length; p++) {
            contenido += '<div class="productos-tooltip-item"><span class="producto-nombre">*' + (productos[p].producto || '') + '</span></div>';
        }

        tooltip.innerHTML = contenido;
        tooltip.style.display = 'block';

        var iconElement = icon[0];
        var iconRect = iconElement.getBoundingClientRect();

        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        var top = iconRect.top + scrollTop - (tooltip.offsetHeight / 2) + (iconRect.height / 2);
        var left = iconRect.right + scrollLeft + 10;

        if (left + tooltip.offsetWidth > window.innerWidth + scrollLeft) {
            left = iconRect.left + scrollLeft - tooltip.offsetWidth - 10;
        }

        if (top < scrollTop) {
            top = scrollTop + 10;
        }

        if (top + tooltip.offsetHeight > window.innerHeight + scrollTop) {
            top = window.innerHeight + scrollTop - tooltip.offsetHeight - 10;
        }

        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
    }

    function ocultarTooltip() {
        var tooltip = document.getElementById('productosTooltip');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }

    // Detectar si es dispositivo táctil
    var isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

    if (isTouchDevice) {
        $('#table_pagar_guias tbody').on('click', '.btnVerProd', function (e) {
            e.stopPropagation();
            var tooltip = document.getElementById('productosTooltip');

            if (tooltip && tooltip.style.display === 'block') {
                ocultarTooltip();
            } else {
                mostrarTooltip(this, e);
            }
        });

        $(document).on('click', function (e) {
            var tooltip = document.getElementById('productosTooltip');
            if (tooltip && !$(e.target).closest('.btnVerProd').length && !$(e.target).closest('#productosTooltip').length) {
                ocultarTooltip();
            }
        });
    } else {
        $('#table_pagar_guias tbody').on('mouseenter', '.btnVerProd', function (e) {
            mostrarTooltip(this, e);
        });

        $('#table_pagar_guias tbody').on('mouseleave', '.btnVerProd', function () {
            ocultarTooltip();
        });
    }

    function actualizar_tp_comprobantes(tp_documento) {
        var $select = $('#tp_comprobante');

        if (tp_documento == 6) {
            $select.select2('destroy');
            $select.empty();

            $select.append('<option value="1">FACTURA ELECTRÓNICA</option>');
            $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');

        } else if (tp_documento == 1) {
            $select.select2('destroy');
            $select.empty();

            $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');
        }

        if (instance.select && instance.select.createSelect) {
            instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
        }
        $select.trigger('change');
    }

    $(".open_modal_cliente").click(function (e) {
        e.preventDefault();
        $("#modal_cliente").modal("show");
    });

    $("input[name='tipo_cliente']").on("change", function (e) {
        tipo_cliente = $(this).val();
        if (tipo_cliente == 'cliente_nuevo') {
            if (div_generarNuevoCliente) div_generarNuevoCliente.classList.remove("d-none");
        } else {
            if (div_generarNuevoCliente) div_generarNuevoCliente.classList.add("d-none");
        }
    });

    if ($("#tp_comprobante").length) {
        $("#tp_comprobante").change(function (e) {
            e.preventDefault();
            if (!tp_comprobante) return;

            var formData = new FormData();
            formData.set('tp_comprobante', tp_comprobante.value);

            fetch(instance._URL_ + "encomienda/get_serieForTpComprobante", {
                method: "POST",
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (serie) serie.innerHTML = "";
                    if (data && data.success && serie) {
                        serie.insertAdjacentHTML('beforeend', '<option value="' + data.message.id_serie + '">' + data.message.serie + '</option>');
                    }
                })
                .catch(function (error) {
                    console.error('Error:', error);
                });
        });

        $("#tp_comprobante").trigger('change');
    }

    /* Hidden modal */
    $("#modal").on("hidden.bs.modal", function (e) {
        $("#modal").find("form").trigger("reset");
        //Eliminando el was-validated
        limpiar_campos_facturacion();
        if (cliente_id) cliente_id.value = '';
        if (nuevo_cliente_id) nuevo_cliente_id.value = '';
        $('#cliente').val('');
        $('#cliente_nombres').val('');
        if (btn_buscarGuias) btn_buscarGuias.classList.add('d-none');
        table_pagar_guias.clear().draw();
        $("#tp_comprobante").val("3").trigger('change');
        $("#medio_pago").val("").trigger('change');
        $("#cliente_actual").prop('checked', true).trigger('change');
        if (div_facturacion) div_facturacion.classList.add("d-none");
        if (button_pagar) button_pagar.classList.add("d-none");
        $("#forma_pago").trigger('change');
        $("#tiempo_credito").trigger('change');
    });

    function limpiar_campos_facturacion() {
        var forms = document.querySelectorAll(".needs-validation");
        for (var f = 0; f < forms.length; f++) {
            forms[f].classList.remove("was-validated");
        }
    }

    /* Eventos de seleccion y deseleccion */
    table_pagar_guias.on('select deselect', function () {
        var selectedRows = table_pagar_guias.rows({ selected: true });
        var count = selectedRows.count();

        // Habilitar/deshabilitar botones según selección
        if (count > 0) {
            if (button_pagar) button_pagar.classList.remove("d-none");
            if (div_facturacion) div_facturacion.classList.remove("d-none");
        } else {
            if (button_pagar) button_pagar.classList.add("d-none");
            if (div_facturacion) div_facturacion.classList.add("d-none");
        }

        // Calcular y mostrar total
        var total = 0;
        selectedRows.data().each(function (row) {
            total += parseFloat(row.monto || 0);
        });
        $('#total_pagar').val(total.toFixed(2));
        $('#monto_credito').val(total.toFixed(2));
    });

    // Logica de tiempo credito 
    $("#tiempo_credito").on('change', function () {
        var tiempo_credito_val = $(this).val();
        var dias = 0;

        switch (tiempo_credito_val) {
            case "1":
                dias = 30;
                break;
            case "2":
                dias = 0;
                break;
            case "3":
                dias = 15;
                break;
            case "4":
                dias = 45;
                break;
            case "5":
                dias = 60;
                break;
            default:
                dias = 0;
        }

        var hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);

        var yyyy = hoy.getFullYear();
        var mm = String(hoy.getMonth() + 1).padStart(2, '0');
        var dd = String(hoy.getDate()).padStart(2, '0');

        var fecha_resultado = yyyy + '-' + mm + '-' + dd;

        $("#fecha_credito").val(fecha_resultado);
    });

    $(document).ready(function () {
        $("#tiempo_credito").trigger('change');
        $("#forma_pago").trigger('change');
    });

    /*=========================== LOGICA DE BOTON PAGAR Y GENERAR NUEVO COMPROBANTE ======================================= */
    if (button_pagar) {
        button_pagar.addEventListener("click", function (event) {
            event.preventDefault();

            if (!form_pago_guia) return;

            var formData = new FormData(form_pago_guia);

            var data = [
                formData.get("total_pagar"),
                formData.get("fecha_emision"),
                formData.get("serie"),
                formData.get("caja_chica"),
            ];

            if (forma_pago && forma_pago.value == 1) {
                data.push(formData.get("medio_pago"));
            }

            var id_cliente = cliente_id ? cliente_id.value : '';

            if (tipo_cliente === 'cliente_nuevo') {
                data.push(formData.get("nuevo_cliente_id"), formData.get("nuevo_cliente_nombres"));
                id_cliente = nuevo_cliente_id ? nuevo_cliente_id.value : '';
            }

            formData.set("id_cliente", id_cliente);

            // ============ OBTENER IDS DE FILAS SELECCIONADAS ============
            var selectedRows = table_pagar_guias.rows({ selected: true });
            var ids_guias = [];

            selectedRows.data().each(function (row) {
                ids_guias.push(row.id_encomienda);
            });

            formData.set("ids_encomiendas", JSON.stringify(ids_guias));

            if (ids_guias.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin guias seleccionadas',
                    text: 'Debe seleccionar al menos una guia para pagar'
                });
                return;
            }

            var dataValid = true;
            for (var d = 0; d < data.length; d++) {
                if (!data[d] || data[d] === '') {
                    dataValid = false;
                    break;
                }
            }

            if (dataValid) {
                Swal.fire({
                    title: '¿Esta seguro que quiere realizar el pago?',
                    text: "No podrá revertir los cambios",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, Pagar!'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        if (loading) loading.classList.remove('d-none');
                        if (button_pagar) button_pagar.classList.add("d-none");
                        if (button_loadPagar) button_loadPagar.classList.remove("d-none");

                        fetch(instance._URL_ + 'guia_transportista/pagar_guias_bloque', {
                            method: 'POST',
                            body: formData
                        })
                            .then(function (response) {
                                if (!response.ok) throw new Error(response.status);
                                return response.json();
                            })
                            .then(function (data) {
                                if (data.success) {
                                    $("#modal").modal("toggle");
                                    modal_printComprobante('¡Pago completado!', data.link_comprobante, data.estado_sunat, data.message_sunat);
                                    if (instance.Datatable && instance.Datatable.reloadTable) {
                                        instance.Datatable.reloadTable(table);
                                    }
                                } else {
                                    if (instance.Toast && instance.Toast.operacion_erronea) {
                                        instance.Toast.operacion_erronea(data.message);
                                    }
                                }
                                if (button_pagar) button_pagar.classList.remove("d-none");
                                if (button_loadPagar) button_loadPagar.classList.add("d-none");
                            })
                            .catch(function (error) {
                                if (instance.Toast && instance.Toast.operacion_erronea) {
                                    instance.Toast.operacion_erronea(error.message);
                                }
                                if (button_pagar) button_pagar.classList.remove("d-none");
                                if (button_loadPagar) button_loadPagar.classList.add("d-none");
                            })
                            .finally(function () {
                                if (loading) loading.classList.add('d-none');
                            });
                    }
                });
            } else {
                if (instance.Toast && instance.Toast.operacion_erronea) {
                    instance.Toast.operacion_erronea("Rellene correctamente los campos");
                }
            }
        });
    }

    var modal_printComprobante = function (title, link_pdf, estado_sunat, message_sunat) {
        estado_sunat = estado_sunat || 0;
        message_sunat = message_sunat || "";

        var html_rspt_sunat = '';
        if (estado_sunat) {
            html_rspt_sunat = '<p class="fw-bolder fs-6">' + message_sunat + '</p>';
        }

        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            html: '<div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">' +
                '<i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>' +
                '<label></label>' +
                '<h5>' + title + '</h5>' +
                '<a href="' + link_pdf + '" class="d-flex flex-column text-decoration-none cursor-pointer py-4 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">' +
                '<i class="fa-light fa-receipt fs-2 text-secondary"></i>' +
                '<label class="text-secondary cursor-pointer mt-2">Imprimir</label>' +
                '</a>' +
                '<section>' +
                html_rspt_sunat +
                '<p class="text-secondary">Enviar comprobante por WhatsApp</p>' +
                '<div class="input-group mb-3">' +
                '<span class="input-group-text py-0">+51</span>' +
                '<input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">' +
                '<button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></button>' +
                '</div>' +
                '</section>' +
                '</div>',
            confirmButtonText: 'Continuar'
        });

        if (instance.Validate && instance.Validate.allowInputNum) {
            instance.Validate.allowInputNum(["#celular_clienteWsp"]);
        }

        var celular_clienteWsp = document.querySelector("#celular_clienteWsp");
        var btn_sendComprobanteWsp = document.querySelector("#btn_sendComprobanteWsp");

        if (btn_sendComprobanteWsp) {
            btn_sendComprobanteWsp.addEventListener("click", function (event) {
                if (celular_clienteWsp && celular_clienteWsp.value.length == 9) {
                    var url = '';
                    if (instance.CONSTS && instance.CONSTS.URL && instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP) {
                        url = instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP;
                    }
                    var texto = '';
                    if (instance.CONSTS && instance.CONSTS.TEXTO && instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE) {
                        texto = instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE;
                    }
                    window.open(url.replace('$number$', '+51' + celular_clienteWsp.value).replace('$message$', texto.replace('$link_comprobante$', link_pdf)), "_blank");
                } else {
                    if (instance.Toast && instance.Toast.operacion_erronea) {
                        instance.Toast.operacion_erronea('Por favor ingrese un número de celular valido');
                    }
                }
            });
        }
    };
});