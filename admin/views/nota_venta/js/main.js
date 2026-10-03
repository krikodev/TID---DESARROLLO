import * as instance from "../../../public/js/instance.js"

document.getElementById("link_nota_venta").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_nota_venta").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_nota_venta").closest(".submenu").classList.add("show")
document.getElementById("link_nota_venta").classList.add("active")

// Variables globales para la tabla y filtros
let table_notaventa = null;
let table_pagar_notas = null;

// Variables para almacenar los filtros actuales
let filtrosActuales = {
    fecha_inicio: '',
    fecha_fin: '',
    tipo: '',
    estado: ''
};

// Obtener filtros del formulario
function getFiltros() {
    return {
        fecha_inicio: document.getElementById('filtro_fecha_inicio') ? document.getElementById('filtro_fecha_inicio').value : '',
        fecha_fin: document.getElementById('filtro_fecha_fin') ? document.getElementById('filtro_fecha_fin').value : '',
        tipo: document.getElementById('filtro_tipo') ? document.getElementById('filtro_tipo').value : '',
        estado: document.getElementById('filtro_estado') ? document.getElementById('filtro_estado').value : ''
    };
}

// Validar fechas
function validarFechas(fecha_inicio, fecha_fin) {
    if (fecha_inicio && fecha_fin && fecha_inicio > fecha_fin) {
        instance.Toast.operacion_erronea('La fecha de inicio no puede ser mayor a la fecha fin');
        return false;
    }
    return true;
}

// Actualizar DataTable con filtros
window.filtrarTabla = function () {
    if (!table_notaventa) {
        console.error('La tabla no está inicializada');
        return;
    }

    filtrosActuales = getFiltros();

    if (!validarFechas(filtrosActuales.fecha_inicio, filtrosActuales.fecha_fin)) {
        return;
    }

    table_notaventa.ajax.reload();
};

// Limpiar todos los filtros
window.limpiarFiltros = function () {
    if (!table_notaventa) {
        console.error('La tabla no está inicializada');
        return;
    }

    var fechaInicio = document.getElementById('filtro_fecha_inicio');
    var fechaFin = document.getElementById('filtro_fecha_fin');
    var tipo = document.getElementById('filtro_tipo');
    var estado = document.getElementById('filtro_estado');

    if (fechaInicio) fechaInicio.value = '';
    if (fechaFin) fechaFin.value = '';
    if (tipo) tipo.value = '';
    if (estado) estado.value = '';

    $(".inputSearch").val("");
    table_notaventa.search("");

    filtrosActuales = getFiltros();
    table_notaventa.ajax.reload();
};
//Exportar Excel
window.exportarExcel = function () {
    var filtros = getFiltros();
    var search = table_notaventa.search();

    // LOG TEMPORAL - Ver qué filtros se están obteniendo
    console.log('Filtros a exportar:', filtros);
    console.log('Search a exportar:', search);

    if (!validarFechas(filtros.fecha_inicio, filtros.fecha_fin)) {
        return;
    }

    Swal.fire({
        title: 'Generando Excel',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: function () {
            Swal.showLoading();
        }
    });

    // Crear o reutilizar el formulario
    var form = document.getElementById('form_exportar_excel');
    if (!form) {
        form = document.createElement('form');
        form.id = 'form_exportar_excel';
        form.method = 'POST';
        form.action = instance._URL_ + 'nota_venta/exportar_excel';
        form.target = '_blank';
        document.body.appendChild(form);

        var campos = ['fecha_inicio', 'fecha_fin', 'tipo', 'estado', 'search'];
        for (var i = 0; i < campos.length; i++) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = campos[i];
            input.id = 'export_' + campos[i];
            form.appendChild(input);
        }
    }

    // Actualizar valores
    document.getElementById('export_fecha_inicio').value = filtros.fecha_inicio;
    document.getElementById('export_fecha_fin').value = filtros.fecha_fin;
    document.getElementById('export_tipo').value = filtros.tipo;
    document.getElementById('export_estado').value = filtros.estado;
    document.getElementById('export_search').value = table_notaventa.search();

    // LOG TEMPORAL - Verificar que los inputs se actualizaron
    console.log('Datos enviados al Excel:', {
        fecha_inicio: filtros.fecha_inicio,
        fecha_fin: filtros.fecha_fin,
        tipo: filtros.tipo,
        estado: filtros.estado,
        search: search
    });

    form.submit();

    setTimeout(function () {
        Swal.close();
    }, 2000);
};

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
        fetch(instance._URL_ + "nota_venta/buscar_nacionalidad?q=" + encodeURIComponent(query))
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

document.addEventListener("DOMContentLoaded", function () {
    // Inicializar DataTable principal
    table_notaventa = $('#table_notaventa').DataTable({
        "ajax": {
            'url': instance._URL_ + 'nota_venta/dataTable',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = filtrosActuales.fecha_inicio;
                d.fecha_fin = filtrosActuales.fecha_fin;
                d.tipo = filtrosActuales.tipo;
                d.estado = filtrosActuales.estado;
            },
            beforeSend: function () {
                var loader = document.getElementById('loader');
                if (loader) loader.classList.remove('d-none');
            },
            complete: function () {
                var loader = document.getElementById('loader');
                if (loader) loader.classList.add('d-none');
            }
        },
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [[20, 25, 50, -1], ['20 filas', '25 filas', '50 filas', 'Mostrar todo']],
        processing: true,
        serverSide: true,
        ordering: false,
        columns: [
            { data: "fecha_emision" },
            {
                data: "id_cliente",
                render: function (data, type, row) {
                    var nombres = row.cliente_nombres || '';
                    var apellidos = row.cliente_apellidos || '';
                    var doc = row.cliente_num_docu || '';
                    return nombres + ' ' + apellidos + '<br>' + doc;
                }
            },
            {
                data: "serie",
                render: function (data, type, row) {
                    return (row.serie && row.correlativo) ? row.serie + '-' + row.correlativo : '---';
                }
            },
            { data: "tp_moneda_codigo" },
            {
                data: "total",
                render: function (data) {
                    return parseFloat(data || 0).toFixed(2);
                }
            },
            {
                data: "estado",
                render: function (data, type, row) {
                    var estado = row.estado || '';
                    var estadoUpper = estado.toUpperCase();
                    var color = '#6c757d';
                    var textColor = '#ffffff';

                    if (instance.CONSTS && instance.CONSTS.COLORES && instance.CONSTS.COLORES.ESTADO_VENTA_FONDO) {
                        color = instance.CONSTS.COLORES.ESTADO_VENTA_FONDO[estadoUpper] || '#6c757d';
                    }
                    if (instance.CONSTS && instance.CONSTS.COLORES && instance.CONSTS.COLORES.ESTADO_VENTA_TEXT) {
                        textColor = instance.CONSTS.COLORES.ESTADO_VENTA_TEXT[estadoUpper] || '#ffffff';
                    }

                    return '<span class="badge" style="background-color:' + color + '; color:' + textColor + '">' + estado + '</span>';
                }
            },
            {
                data: "id_tp_venta",
                render: function (data, type, row) {
                    var tipoTexto = '';
                    var tipoClase = 'badge bg-secondary';

                    if (row.id_tp_venta) {
                        switch (parseInt(row.id_tp_venta)) {
                            case 1: tipoTexto = 'PASAJE'; tipoClase = 'badge bg-info'; break;
                            case 2: tipoTexto = 'ENCOMIENDA'; tipoClase = 'badge bg-success'; break;
                            case 3: tipoTexto = 'FACTURADOR'; tipoClase = 'badge bg-warning text-dark'; break;
                            case 4: tipoTexto = 'PAGO BLOQUE'; tipoClase = 'badge bg-primary'; break;
                            default: tipoTexto = row.tp_venta_nombre || 'OTRO';
                        }
                    } else if (row.tp_servicio) {
                        var servicio = row.tp_servicio.toUpperCase();
                        if (servicio === 'PASAJE') {
                            tipoTexto = 'PASAJE';
                            tipoClase = 'badge bg-info';
                        } else if (servicio === 'ENCOMIENDA') {
                            tipoTexto = 'ENCOMIENDA';
                            tipoClase = 'badge bg-success';
                        } else {
                            tipoTexto = row.tp_servicio;
                            tipoClase = 'badge bg-secondary';
                        }
                    } else {
                        tipoTexto = 'FACTURADOR';
                        tipoClase = 'badge bg-warning text-dark';
                    }

                    return '<span class="' + tipoClase + '">' + tipoTexto + '</span>';
                }
            },
            {
                data: "id_venta",
                render: function (data, type, row) {

                    const permisos = instance.CONSTS.PERMISOS || {};
                    const urls = instance.CONSTS.URL || {};

                    let link_impresion = "facturador/impresion/nota_venta/";
                    let link_anular = "";

                    // ==============================
                    // LINK DE IMPRESIÓN
                    // ==============================
                    if (row.tp_servicio === "PASAJE") {
                        link_impresion = urls.IMPRESION_PASAJE && urls.IMPRESION_PASAJE.I_NOTA_VENTA
                            ? urls.IMPRESION_PASAJE.I_NOTA_VENTA
                            : link_impresion;

                        link_anular = urls.ANULACION && urls.ANULACION.PASAJE
                            ? urls.ANULACION.PASAJE
                            : "";

                    } else if (row.tp_servicio === "ENCOMIENDA") {
                        link_impresion = urls.IMPRESION_ENCOMIENDA && urls.IMPRESION_ENCOMIENDA.NOTA_VENTA
                            ? urls.IMPRESION_ENCOMIENDA.NOTA_VENTA
                            : link_impresion;

                        link_anular = urls.ANULACION && urls.ANULACION.ENCOMIENDA
                            ? urls.ANULACION.ENCOMIENDA
                            : "";

                    } else {
                        link_impresion = urls.IMPRESION_FACTURADOR && urls.IMPRESION_FACTURADOR.NOTA_VENTA
                            ? urls.IMPRESION_FACTURADOR.NOTA_VENTA
                            : link_impresion;

                        link_anular = urls.ANULACION && urls.ANULACION.FACTURADOR
                            ? urls.ANULACION.FACTURADOR
                            : "";
                    }

                    // ==============================
                    // BOTONES
                    // ==============================
                    const btn_impresion = `
            <li>
                <a href="${instance._URL_}${link_impresion}${row.id_venta}" 
                   target="_blank" 
                   class="dropdown-item btn_dropdown" 
                   title="Comprobante">
                    Formato ticket
                </a>
            </li>
        `;

                    const puedeAnular = row.estado !== "ANULADO"
                        && Number(permisos.P_ANULAR_NOTAVENTA) === 1
                        && link_anular !== "";

                    const btn_anular = puedeAnular
                        ? `
                <li>
                    <a class="dropdown-item btn_dropdown btnAnular" 
                       data-id_venta="${row.id_venta}"
                       data-link="${link_anular}" 
                       title="Anular">
                        Anular
                    </a>
                </li>
            `
                        : "";

                    // ==============================
                    // HTML FINAL
                    // ==============================
                    return `
            <div class="dropdown">
                <a class="dropdown-toggle" 
                   data-bs-toggle="dropdown" 
                   href="javascript:void(0)" 
                   role="button" 
                   aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </a>

                <ul class="dropdown-menu">
                    ${btn_impresion}
                    ${btn_anular}
                </ul>
            </div>
            `;
                }
            }
        ],
        language: {
            url: './public/plugins/datatable/language/es_es.json'
        },
        deferRender: true,
        stateSave: false,
        pageLength: 20
    });

    instance.Datatable.inputSearch(table_notaventa, ".inputSearch", ".btnSearchTable", "manual");

    $('#table_notaventa tbody').on('click', '.btnAnular', function (e) {
        e.preventDefault();

        let tr = $(this).closest('tr');

        if (tr.hasClass('child')) {
            tr = tr.prev();
        }

        const rowData = table_notaventa.row(tr).data();
        const link = $(this).data("link");

        if (!rowData) {
            instance.Toast.operacion_erronea("No se pudo obtener la información de la fila");
            return;
        }

        if (!link) {
            instance.Toast.operacion_erronea("No se encontró la ruta de anulación");
            return;
        }

        anular(rowData.id_venta, rowData.id_tp_comprobante, link);
    });


    const anular = (id_venta = null, id_tp_comprobante = null, link = null) => {
        Swal.fire({
            title: '¿Está seguro que desea anular la venta?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'No',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: "Anulando la venta",
                icon: "info",
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            });

            Swal.showLoading();

            let formData = new FormData();
            formData.set("id_ventaPasaje", id_venta);
            formData.set("id_venta", id_venta);
            formData.set("tp_comprobante", id_tp_comprobante);

            fetch(instance._URL_ + link, {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error HTTP: " + response.status);
                    }

                    return response.json();
                })
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        Swal.fire({
                            title: "Éxito",
                            text: data.message,
                            icon: "success"
                        });

                        instance.Datatable.reloadTable(table_notaventa);

                    } else {
                        instance.Toast.operacion_erronea(data.message || "No se pudo anular la venta");
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error(error);
                    instance.Toast.operacion_erronea(error.message || "Error al anular la venta");
                });
        });
    };

    // Inicializar DataTable de notas a pagar
    table_pagar_notas = $('#table_pagar_notas').DataTable({
        destroy: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [[10, 25, 50, -1], ['10 filas', '25 filas', '50 filas', 'Mostrar todo']],
        select: { style: 'multi' },
        ordering: false,
        columns: [
            {
                data: "id_encomienda",
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: "numero_nota" },
            { data: "fecha_emision" },
            {
                data: "monto",
                render: function (data) {
                    return 'S/ ' + parseFloat(data || 0).toFixed(2);
                }
            },
            {
                data: "detalle",
                render: function (data) {
                    if (!data || data.length === 0) return 'Sin detalle';
                    return '<div class="center-icon btnVerProd"><i class="fa-solid fa-eye fa-lg" style="color: #2350afff;" data-productos=\'' + JSON.stringify(data) + '\'></i></div>';
                }
            }
        ],
        language: {
            url: './public/plugins/datatable/language/es_es.json'
        }
    });

    // Configurar event listeners para filtros
    var btnFiltrar = document.getElementById('btn_filtrar');
    var btnLimpiar = document.getElementById('btn_limpiar_filtros');
    var btnExportar = document.getElementById('btn_exportar_excel');

    if (btnFiltrar) btnFiltrar.addEventListener('click', window.filtrarTabla);
    if (btnLimpiar) btnLimpiar.addEventListener('click', window.limpiarFiltros);
    if (btnExportar) btnExportar.addEventListener('click', window.exportarExcel);

    // Permitir filtrar con Enter
    var filtrosIds = ['filtro_fecha_inicio', 'filtro_fecha_fin', 'filtro_tipo', 'filtro_estado'];
    for (var i = 0; i < filtrosIds.length; i++) {
        var elemento = document.getElementById(filtrosIds[i]);
        if (elemento) {
            elemento.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    window.filtrarTabla();
                }
            });
        }
    }

    /*============== LÓGICA CLIENTE ==========================*/
    var btn_buscarNotas = document.querySelector("#btn_buscarNotas");
    var buscar_cliente = document.querySelector("#buscar_cliente");
    var loader_buscar = document.querySelector("#loader_buscar");
    var cliente_id = document.querySelector("#cliente_id");
    var nuevo_cliente_id = document.querySelector("#nuevo_cliente_id");
    var buscar_nuevo_cliente = document.querySelector("#buscar_nuevo_cliente");
    var loader_buscar_nc = document.querySelector("#loader_buscar_nc");
    var buscar_notas = document.querySelector("#buscar_notas");
    var loader_buscar_notas = document.querySelector("#loader_buscar_notas");
    var num_docu = document.querySelector("#num_docu");
    var div_generarNuevoCliente = document.querySelector("#div_generarNuevoCliente");
    var tipo_cliente_elem = document.querySelector("input[name='tipo_cliente']:checked");
    var tipo_cliente = tipo_cliente_elem ? tipo_cliente_elem.value : 'cliente_actual';
    var button_pagar = document.querySelector("#button_pagar");
    var button_loadPagar = document.querySelector("#button_loadPagar");
    var div_facturacion = document.querySelector("#div_facturacion");
    var loading = document.querySelector("#loader");
    var div_cuota = document.querySelector("#div_cuota");
    var forma_pago = document.querySelector("#forma_pago");
    var tp_comprobante = document.querySelector("#tp_comprobante");
    var serie = document.querySelector("#serie");
    var form_pago_nota = document.querySelector("#form_pago_nota");

    // Inicializar selectores
    if (document.querySelector("#tp_comprobante")) {
        instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
    }

    // Inicializar Selectize para medio de pago
    var medioPagoElement = $('#medio_pago');
    if (medioPagoElement.length) {
        var medio_pagoSelect = medioPagoElement.selectize({
            create: false,
            maxItems: 1,
            onDropdownOpen: function ($dropdown) {
                $dropdown.addClass('selectize-dropdown--single');
            }
        });
        window.selectizeMedioPago = medio_pagoSelect[0] ? medio_pagoSelect[0].selectize : null;
    }

    // Función para buscar cliente
    function buscarCliente(inputValue, callback) {
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
                callback(data);
            })
            .catch(function (error) {
                instance.Toast.operacion_erronea(error.message);
                callback(null);
            });
    }

    // Event listener para búsqueda de cliente
    if (buscar_cliente) {
        buscar_cliente.addEventListener("click", function (e) {
            e.preventDefault();

            if (table_pagar_notas) table_pagar_notas.clear().draw();
            limpiar_campos_facturacion();
            if (div_facturacion) div_facturacion.classList.add("d-none");

            var params = [8, 11];
            var inputValue = $("#cliente").val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (!params.includes(inputValue.length) || !isNumber) {
                instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                return;
            }

            if (btn_buscarNotas) btn_buscarNotas.classList.add('d-none');
            if (buscar_cliente) buscar_cliente.classList.add("d-none");
            if (loader_buscar) loader_buscar.classList.remove("d-none");

            buscarCliente(inputValue, function (data) {
                if (data && data.success) {
                    if (cliente_id) {
                        cliente_id.value = data.message.id_usuario;
                        cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                    }
                    actualizar_tp_comprobantes(data.message.id_tp_docu);
                    $('#cliente_nombres').val(data.message.nombres_cliente);
                    if (btn_buscarNotas) btn_buscarNotas.classList.remove('d-none');
                } else {
                    if (cliente_id) {
                        cliente_id.value = '';
                        cliente_id.dataset.tp_docu = '';
                    }
                    $('#cliente_nombres').val('');
                    if (btn_buscarNotas) btn_buscarNotas.classList.add('d-none');
                    Swal.fire({
                        icon: 'info',
                        title: 'Ooops...',
                        text: 'El cliente que estas buscando no existe...'
                    });
                }

                if (buscar_cliente) buscar_cliente.classList.remove("d-none");
                if (loader_buscar) loader_buscar.classList.add("d-none");
            });
        });
    }

    // Evento Enter para cliente
    $('#cliente').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            if (table_pagar_notas) table_pagar_notas.clear().draw();
            limpiar_campos_facturacion();
            if (div_facturacion) div_facturacion.classList.add("d-none");

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);
            var params = [8, 11];

            if (!params.includes(inputValue.length) || !isNumber) {
                instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                return;
            }

            if (btn_buscarNotas) btn_buscarNotas.classList.add('d-none');
            if (buscar_cliente) buscar_cliente.classList.add("d-none");
            if (loader_buscar) loader_buscar.classList.remove("d-none");

            buscarCliente(inputValue, function (data) {
                if (data && data.success) {
                    if (cliente_id) {
                        cliente_id.value = data.message.id_usuario;
                        cliente_id.dataset.tp_docu = data.message.id_tp_docu;
                    }
                    actualizar_tp_comprobantes(data.message.id_tp_docu);
                    $('#cliente_nombres').val(data.message.nombres_cliente);
                    if (btn_buscarNotas) btn_buscarNotas.classList.remove('d-none');
                } else {
                    if (cliente_id) {
                        cliente_id.value = '';
                        cliente_id.dataset.tp_docu = '';
                    }
                    $('#cliente_nombres').val('');
                    if (btn_buscarNotas) btn_buscarNotas.classList.add('d-none');
                    Swal.fire({
                        icon: 'info',
                        title: 'Ooops...',
                        text: 'El cliente que estas buscando no existe...'
                    });
                }

                if (buscar_cliente) buscar_cliente.classList.remove("d-none");
                if (loader_buscar) loader_buscar.classList.add("d-none");
            });
        }
    });

    // Búsqueda de nuevo cliente
    if (buscar_nuevo_cliente) {
        buscar_nuevo_cliente.addEventListener("click", function (e) {
            e.preventDefault();

            var params = [8, 11];
            var inputValue = $("#nuevo_cliente").val().trim();
            var isNumber = /^\d+$/.test(inputValue);

            if (!params.includes(inputValue.length) || !isNumber) {
                instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                return;
            }

            var tp_d = (inputValue.length === 11) ? 6 : 1;

            if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.add("d-none");
            if (loader_buscar_nc) loader_buscar_nc.classList.remove("d-none");

            buscarCliente(inputValue, function (data) {
                if (data && data.success) {
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
                    $('#num_docu').focus();
                    $("#modal_cliente").modal("show");
                }

                if (loader_buscar_nc) loader_buscar_nc.classList.add("d-none");
                if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.remove("d-none");
            });
        });
    }

    // Evento Enter para nuevo cliente
    $('#nuevo_cliente').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            var inputValue = $(this).val().trim();
            var isNumber = /^\d+$/.test(inputValue);
            var params = [8, 11];

            if (!params.includes(inputValue.length) || !isNumber) {
                instance.Toast.operacion_informativa('Rellene el campo correctamente. <br> *DNI => 8 numeros <br> *RUC => 11 numeros');
                return;
            }

            var tp_d = (inputValue.length === 11) ? 6 : 1;

            if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.add("d-none");
            if (loader_buscar_nc) loader_buscar_nc.classList.remove("d-none");

            buscarCliente(inputValue, function (data) {
                if (data && data.success) {
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
                    $('#num_docu').focus();
                    $("#modal_cliente").modal("show");
                }

                if (loader_buscar_nc) loader_buscar_nc.classList.add("d-none");
                if (buscar_nuevo_cliente) buscar_nuevo_cliente.classList.remove("d-none");
            });
        }
    });

    // Cambio de forma de pago
    $("#forma_pago").on('change', function () {
        var fp = $(this).val();
        if (fp == 2) {
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

    // Búsqueda de notas a pagar
    if (buscar_notas) {
        buscar_notas.addEventListener("click", function () {
            if (buscar_notas) buscar_notas.classList.add("d-none");
            if (loader_buscar_notas) loader_buscar_notas.classList.remove("d-none");

            var formData = new FormData();
            formData.set("id_cliente", cliente_id ? cliente_id.value : '');

            fetch(instance._URL_ + 'nota_venta/get_notasxpagar', {
                method: 'POST',
                body: formData
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (table_pagar_notas) table_pagar_notas.clear();
                    limpiar_campos_facturacion();
                    if (div_facturacion) div_facturacion.classList.add("d-none");

                    if (data.success && data.data && data.data.length > 0) {
                        var notasValidas = data.data.filter(function (item) {
                            return item.numero_nota;
                        });

                        if (notasValidas.length > 0) {
                            table_pagar_notas.rows.add(notasValidas).draw();
                            $("#modal_detallePC").modal("show");
                        } else {
                            if (table_pagar_notas) table_pagar_notas.draw();
                            Swal.fire({
                                icon: 'info',
                                title: 'Ooops..',
                                text: 'No se ha encontrado ninguna nota de venta válida del cliente'
                            });
                        }
                    } else {
                        if (table_pagar_notas) table_pagar_notas.draw();
                        Swal.fire({
                            icon: 'info',
                            title: 'Ooops..',
                            text: 'No se ha encontrado ninguna nota de venta del cliente'
                        });
                    }
                })
                .catch(function (error) {
                    if (table_pagar_notas) table_pagar_notas.clear().draw();
                    instance.Toast.operacion_erronea(error.message);
                })
                .finally(function () {
                    if (buscar_notas) buscar_notas.classList.remove("d-none");
                    if (loader_buscar_notas) loader_buscar_notas.classList.add("d-none");
                });
        });
    }

    // Tooltip para productos
    function mostrarTooltip(element) {
        var icon = $(element).find('i');
        var productosAttr = icon.attr('data-productos');
        if (!productosAttr) return;

        try {
            var productos = JSON.parse(productosAttr);
            var tooltip = document.getElementById('productosTooltip');
            if (!tooltip) return;

            var contenido = '';
            for (var j = 0; j < productos.length; j++) {
                contenido += '<div class="productos-tooltip-item"><span class="producto-nombre">*' + (productos[j].producto || '') + '</span></div>';
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
        } catch (e) {
            console.error('Error al mostrar tooltip:', e);
        }
    }

    function ocultarTooltip() {
        var tooltip = document.getElementById('productosTooltip');
        if (tooltip) tooltip.style.display = 'none';
    }

    var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (isTouchDevice) {
        $('#table_pagar_notas tbody').on('click', '.btnVerProd', function (e) {
            e.stopPropagation();
            var tooltip = document.getElementById('productosTooltip');
            if (tooltip && tooltip.style.display === 'block') {
                ocultarTooltip();
            } else {
                mostrarTooltip(this);
            }
        });

        $(document).on('click', function (e) {
            var tooltip = document.getElementById('productosTooltip');
            if (tooltip && !$(e.target).closest('.btnVerProd').length && !$(e.target).closest('#productosTooltip').length) {
                ocultarTooltip();
            }
        });
    } else {
        $('#table_pagar_notas tbody').on('mouseenter', '.btnVerProd', function () {
            mostrarTooltip(this);
        });

        $('#table_pagar_notas tbody').on('mouseleave', function () {
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

        instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
        $select.trigger('change');
    }

    $(".open_modal_cliente").click(function (e) {
        e.preventDefault();
        $("#modal_cliente").modal("show");
    });

    $("input[name='tipo_cliente']").on("change", function () {
        tipo_cliente = $(this).val();
        if (tipo_cliente == 'cliente_nuevo') {
            if (div_generarNuevoCliente) div_generarNuevoCliente.classList.remove("d-none");
        } else {
            if (div_generarNuevoCliente) div_generarNuevoCliente.classList.add("d-none");
        }
    });

    $("#tp_comprobante").change(function () {
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
            });
    });

    $("#tp_comprobante").trigger('change');

    $("#modal").on("hidden.bs.modal", function () {
        $("#modal").find("form").trigger("reset");
        limpiar_campos_facturacion();
        if (cliente_id) cliente_id.value = '';
        if (nuevo_cliente_id) nuevo_cliente_id.value = '';
        $('#cliente').val('');
        $('#cliente_nombres').val('');
        if (btn_buscarNotas) btn_buscarNotas.classList.add('d-none');
        if (table_pagar_notas) table_pagar_notas.clear().draw();
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
        for (var i = 0; i < forms.length; i++) {
            forms[i].classList.remove("was-validated");
        }
    }

    if (table_pagar_notas) {
        table_pagar_notas.on('select deselect', function () {
            var selectedRows = table_pagar_notas.rows({ selected: true });
            var count = selectedRows.count();

            if (count > 0) {
                if (button_pagar) button_pagar.classList.remove("d-none");
                if (div_facturacion) div_facturacion.classList.remove("d-none");
            } else {
                if (button_pagar) button_pagar.classList.add("d-none");
                if (div_facturacion) div_facturacion.classList.add("d-none");
            }

            var total = 0;
            selectedRows.data().each(function (row) {
                total += parseFloat(row.monto || 0);
            });

            $('#total_pagar').val(total.toFixed(2));
            $('#monto_credito').val(total.toFixed(2));
        });
    }

    $("#tiempo_credito").on('change', function () {
        var tiempo_credito = $(this).val();
        var dias = 0;

        switch (tiempo_credito) {
            case "1": dias = 30; break;
            case "2": dias = 0; break;
            case "3": dias = 15; break;
            case "4": dias = 45; break;
            case "5": dias = 60; break;
            default: dias = 0;
        }

        var hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);

        var yyyy = hoy.getFullYear();
        var mm = String(hoy.getMonth() + 1).padStart(2, '0');
        var dd = String(hoy.getDate()).padStart(2, '0');

        $("#fecha_credito").val(yyyy + '-' + mm + '-' + dd);
    });

    $(document).ready(function () {
        $("#tiempo_credito").trigger('change');
        $("#forma_pago").trigger('change');
    });

    if (button_pagar) {
        button_pagar.addEventListener("click", function (event) {
            event.preventDefault();

            if (!form_pago_nota) return;

            var formData = new FormData(form_pago_nota);

            var dataValidacion = [
                formData.get("total_pagar"),
                formData.get("fecha_emision"),
                formData.get("serie"),
                formData.get("caja_chica")
            ];

            if (forma_pago && forma_pago.value == 1) {
                dataValidacion.push(formData.get("medio_pago"));
            }

            var idCliente = cliente_id ? cliente_id.value : '';

            if (tipo_cliente === 'cliente_nuevo' && nuevo_cliente_id) {
                dataValidacion.push(formData.get("nuevo_cliente_id"));
                dataValidacion.push(formData.get("nuevo_cliente_nombres"));
                idCliente = nuevo_cliente_id.value;
            }

            formData.set("id_cliente", idCliente);

            var selectedRows = table_pagar_notas.rows({ selected: true });
            var ids_notas = [];

            selectedRows.data().each(function (row) {
                ids_notas.push(row.id_encomienda);
            });

            formData.set("ids_encomiendas", JSON.stringify(ids_notas));

            if (ids_notas.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin notas seleccionadas',
                    text: 'Debe seleccionar al menos una nota para pagar'
                });
                return;
            }

            if (instance.Validate.validateData(dataValidacion)) {
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

                        fetch(instance._URL_ + 'nota_venta/pagar_notas_bloque', {
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
                                    if (table_notaventa) instance.Datatable.reloadTable(table_notaventa);
                                } else {
                                    instance.Toast.operacion_erronea(data.message);
                                }
                                if (button_pagar) button_pagar.classList.remove("d-none");
                                if (button_loadPagar) button_loadPagar.classList.add("d-none");
                            })
                            .catch(function (error) {
                                instance.Toast.operacion_erronea(error.message);
                                if (button_pagar) button_pagar.classList.remove("d-none");
                                if (button_loadPagar) button_loadPagar.classList.add("d-none");
                            })
                            .finally(function () {
                                if (loading) loading.classList.add('d-none');
                            });
                    }
                });
            } else {
                instance.Toast.operacion_erronea("Rellene correctamente los campos");
            }
        });
    }

    function modal_printComprobante(title, link_pdf, estado_sunat, message_sunat) {
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

        instance.Validate.allowInputNum(["#celular_clienteWsp"]);

        var celular_clienteWsp = document.querySelector("#celular_clienteWsp");
        var btn_sendComprobanteWsp = document.querySelector("#btn_sendComprobanteWsp");

        if (btn_sendComprobanteWsp) {
            btn_sendComprobanteWsp.addEventListener("click", function () {
                if (celular_clienteWsp && celular_clienteWsp.value.length == 9) {
                    var url = instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                        .replace('$number$', '+51' + celular_clienteWsp.value)
                        .replace('$message$', instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE.replace('$link_comprobante$', link_pdf));
                    window.open(url, "_blank");
                } else {
                    instance.Toast.operacion_erronea('Por favor ingrese un número de celular valido');
                }
            });
        }
    }

    // Asignar funciones globales
    window.filtrarTabla = window.filtrarTabla || window.filtrarTabla;
    window.limpiarFiltros = window.limpiarFiltros || window.limpiarFiltros;
    window.exportarExcel = window.exportarExcel || window.exportarExcel;
});