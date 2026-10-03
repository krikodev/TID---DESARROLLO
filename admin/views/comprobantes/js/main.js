import * as instance from "../../../public/js/instance.js"
let data = '';
document.getElementById("link_comprobantes").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_comprobantes").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_comprobantes").closest(".submenu").classList.add("show")
document.getElementById("link_comprobantes").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    let tp_c = '';
    let fecha_inicio = document.querySelector("#fecha_inicio")
    let fecha_fin = document.querySelector("#fecha_fin")
    let ruc = document.querySelector("#ruc")
    let table;
    table = $('#table_comprobantes').DataTable({
        "ajax": {
            'url': instance._URL_ + 'comprobantes/dataTable',
            'method': 'POST',
            'data': function (d) {
                // Agregar filtros a la petición
                d.fecha_inicio = filtrosComp.fecha_inicio;
                d.fecha_fin = filtrosComp.fecha_fin;
                d.tp_comprobante = filtrosComp.tp_comprobante;
                d.tipo_venta = filtrosComp.tipo_venta;
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
                "data": "fecha_emision",
            },
            {
                "data": "id_cliente",
                render: (data, type, row) => {
                    return `${row.cliente_nombres} ${row.cliente_apellidos}<br>${row.cliente_num_docu}`
                }
            },
            {
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_FONDO[row.estado.toUpperCase()]}; color:${instance.CONSTS.COLORES.ESTADO_VENTA_TEXT[row.estado.toUpperCase()]}">${row.estado}</span>`
                }
            },
            {
                "data": "tipo_display",
                render: (data, type, row) => {
                    switch (data.toUpperCase()) {
                        case 'PASAJE':
                            return '<span class="badge badge-soft badge-primary-soft">PASAJE</span>';

                        case 'ENCOMIENDA':
                            return '<span class="badge badge-soft badge-success-soft">ENCOMIENDA</span>';

                        case 'FACTURADOR':
                            return '<span class="badge badge-soft badge-warning-soft">FACTURADOR</span>';

                        case 'PAGO DE NOTAS EN BLOQUE':
                            return '<span class="badge badge-soft badge-info-soft">NOTAS BLOQUE</span>';

                        case 'FLETE':
                            return '<span class="badge badge-soft badge-purple-soft">FLETE</span>';

                        case 'VALORIZACION':
                            return '<span class="badge badge-soft badge-orange-soft">VALORIZACIÓN</span>';

                        case 'OTRO':
                            return '<span class="badge badge-soft badge-secondary-soft">OTRO</span>';

                        default:
                            return `<span class="badge badge-soft badge-secondary-soft">${data}</span>`;
                    }
                }
            },
            {
                "data": "op_gravada",
            },
            {
                "data": "op_exonerada",
            },
            {
                "data": "op_inafecta",
            },
            {
                "data": "op_igv",
            },
            {
                "data": "total",
            },
            {
                "data": "envio_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.envio_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.envio_sunat]}</span>`
                }
            },
            {
                "data": "forma_pago",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.FORMA_PAGO[row.forma_pago]}">${row.forma_pago}</span>`
                }
            },
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let plazo_vencido = 1;
                    let fecha_actual = new Date();
                    let fecha_e = new Date(row.fecha_emision)
                    fecha_e.setDate(fecha_e.getDate() + 7);
                    if (fecha_actual > fecha_e) {
                        plazo_vencido = 0
                    }

                    let link_anular = "";
                    switch (row.tp_servicio) {
                        case 'PASAJE':
                            link_anular = instance.CONSTS.URL.ANULACION.PASAJE
                            break;
                        case 'ENCOMIENDA':
                            link_anular = instance.CONSTS.URL.ANULACION.ENCOMIENDA
                            break;
                        default:
                            link_anular = instance.CONSTS.URL.ANULACION.FACTURADOR
                            break;
                    }

                    let html_anular = `<li><a class="dropdown-item btn_dropdown btnAnular" data-link="${link_anular}" title="Anular">Anular</a></li>`;
                    let html_nota = '';
                    if ((row.envio_sunat === 1 || row.envio_sunat === 2) && row.estado !== "ANULADO") {
                        html_nota = `<li><a class="dropdown-item btn_dropdown btnNota" title="Nota">Crear Nota</a></li>`;
                    }
                    let html_xml = row.file_xml ? `<li><a class="dropdown-item btn_dropdown btnXML" href="${row.file_xml}" target="_blank" title="XML">XML</a></li>` : '';
                    let html_cdr = row.envio_sunat == 1 ? `<li><a class="dropdown-item btn_dropdown btnCDR" href="${row.file_cdr}" target="_blank" title="CDR">CDR</a></li>` : '';
                    let link_impresion = "";
                    if (row.tp_servicio == "PASAJE") {
                        link_impresion = instance.CONSTS.URL.IMPRESION_PASAJE.COMPROBANTE
                    } else if (row.tp_servicio == "ENCOMIENDA") {
                        link_impresion = instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE;
                    } else if (row.id_tp_venta == 4 || row.id_tp_venta == 6) {
                        link_impresion = instance.CONSTS.URL.IMPRESION_NOTA_VENTA.COMPROBANTE;
                    } else {
                        link_impresion = instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE;
                    }
                    let html_reenviar = row.envio_sunat == 0 ? `<li><a class="dropdown-item btn_dropdown btnReenviar" id="reenviarVenta" data-id="${row.id_venta}" title="Reenviar">Reenviar</a></li>` : '';

                    // Logica para el boton de pago de facturas a credito
                    let html_pagar_cuota = row.forma_pago == 'CREDITO' && row.estado == 'PENDIENTE' ? `<li><a class="dropdown-item btn_dropdown btnPagarComp">Pagar comprobante</a></li>` : '';

                    let btn_detalle_PC = row.forma_pago == 'CREDITO' && row.estado == 'PAGADO' ? `<li><a class="dropdown-item btn_dropdown btnDetallePC">Ver detalle de pago</a></li>` : '';
                    let btn_consultar_comprobante = `<a id="consultarSUNAT" data-id="${row.id_venta}" class="dropdown-item btn_dropdown btnConsultarSUNAT">Consultar SUNAT</a>`

                    let newRow = `
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu">
                        <li><a href="${instance._URL_ + link_impresion + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown" title="Comprobante">Comprobante</a></li>
                        ${row.estado != "ANULADO" && row.envio_sunat && instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE == 1 ? html_anular : ''}
                        ${html_xml}
                        ${html_cdr}
                        ${plazo_vencido ? html_reenviar : ''}
                        ${html_nota}
                        ${html_pagar_cuota}
                        ${btn_detalle_PC}
                        ${btn_consultar_comprobante}
                        </ul>
                    </div>`;
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
    })

    const table_resumen = $('#table_resumen').DataTable({
        "ajax": {
            'url': instance._URL_ + 'comprobantes/get_dataTableResumen',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = filtrosRes.fecha_inicio;
                d.fecha_fin = filtrosRes.fecha_fin;
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
                "data": "fecha_envio",
                render: (data, type, row) => {
                    return row.fecha_envio.split("-").reverse().join("-")
                }
            },
            {
                "data": "fecha_referencia",
                render: (data, type, row) => {
                    return row.fecha_referencia.split("-").reverse().join("-")
                }
            },
            {
                "data": "nombre_xml",
                render: (data, type, row) => {
                    return row.nombre_xml ? row.nombre_xml : '-----'
                }
            },
            {
                "data": "ticket",
                render: (data, type, row) => {
                    return row.ticket ? row.ticket : '-----'
                }
            },
            {
                "data": "mensaje_sunat",
                render: (data, type, row) => {
                    return row.mensaje_sunat ? row.mensaje_sunat : '-----'
                }
            },
            {
                "data": "codigo_sunat",
                render: (data, type, row) => {
                    return row.codigo_sunat ? row.codigo_sunat : '-----'
                }
            },
            {
                "data": "id_resumen",
                render: (data, type, row) => {
                    let html_xml = row.file_xml ? `<a class="btn btnXML text-white mb-1" href="${row.file_xml}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.XML}" title="XML"><i class="fa-light fa-file-xmark"></i></a>` : '';
                    let html_cdr = row.file_cdr ? `<a class="btn btnCDR text-white mb-1" href="${row.file_cdr}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.CDR}" title="CDR"><i class="fa-light fa-file-zipper"></i></a>` : '';
                    let html_envio = (row.codigo_sunat !== '' && row.codigo_sunat !== '0127') ? `<a class="btn btnEnvio text-white mb-1" data-id="${row.id_resumen}" style="background-color: blue; font-size: 8px;">Consultar CDR</a>` : '';
                    let newRow = `${html_xml} ${html_cdr} ${html_envio}`;

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
    })

    //Selects en caso sea necesario
    instance.select.createSelect("#medio_pago_pc", "#div_parentMedioPagoPc");

    const fechaActual = new Date();
    const fecha7DiasAtras = new Date();
    fecha7DiasAtras.setDate(fechaActual.getDate() - 7);

    const fechaActualStr = fechaActual.toISOString().split('T')[0];
    const fecha7DiasAtrasStr = fecha7DiasAtras.toISOString().split('T')[0];

    // Configurar inputs de fecha
    $("#fecha_inicio").attr({
        'min': fecha7DiasAtrasStr,
        'max': fechaActualStr,
        'value': fecha7DiasAtrasStr
    });

    $("#fecha_fin").attr({
        'min': fecha7DiasAtrasStr,
        'max': fechaActualStr,
        'value': fechaActualStr
    });

    const table_reenvio = $('#table_reenvio').DataTable({
        "ajax": {
            'url': instance._URL_ + 'comprobantes/get_dataTableReenvio',
            'method': 'POST',
            'data': {
                ruc: ruc.value,
                tp_comprobante: [3],
                fecha_inicio: fecha_inicio.value,
                fecha_fin: fecha_fin.value
            }
        },
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        select: {
            style: 'multi',
            selector: 'td:not(:first-child)'
        },
        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "id_cliente",
                render: (data, type, row) => {
                    return `${row.cliente_nombres} ${row.cliente_apellidos}<br>${row.cliente_num_docu}`
                }
            },
            {
                "data": "tipo_display",
                render: (data, type, row) => {
                    if (!data) {
                        // Si no hay tipo_display, intentar determinarlo de otra manera
                        if (row.id_tp_venta) {
                            switch (row.id_tp_venta) {
                                case 1: data = 'PASAJE'; break;
                                case 2: data = 'ENCOMIENDA'; break;
                                case 3: data = 'FACTURADOR'; break;
                                case 4: data = 'PAGO DE NOTAS EN BLOQUE'; break;
                                default: data = 'OTRO';
                            }
                        } else {
                            return '<span class="badge bg-secondary">---</span>';
                        }
                    }

                    // Aplicar estilos según el tipo
                    switch (data.toUpperCase()) {
                        case 'PASAJE':
                            return '<span class="badge bg-primary" style="pointer-events:none">PASAJE</span>';
                        case 'ENCOMIENDA':
                            return '<span class="badge bg-success" style="pointer-events:none">ENCOMIENDA</span>';
                        case 'FACTURADOR':
                            return '<span class="badge bg-warning text-dark" style="pointer-events:none">FACTURADOR</span>';
                        case 'PAGO DE NOTAS EN BLOQUE':
                            return '<span class="badge bg-info" style="pointer-events:none">NOTAS BLOQUE</span>';
                        case 'OTRO':
                            return '<span class="badge bg-secondary" style="pointer-events:none">OTRO</span>';
                        default:
                            return `<span class="badge bg-secondary" style="pointer-events:none">${data}</span>`;
                    }
                }
            },
            {
                "data": "op_gravada",
            },
            {
                "data": "op_exonerada",
            },
            {
                "data": "op_inafecta",
            },
            {
                "data": "op_igv",
            },
            {
                "data": "total",
            },
            {
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `<span style="color: black; font-weight:700; pointer-events:none">${row.fecha_emision}</span>`
                }
            },
            {
                "data": "envio_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.envio_sunat]}; pointer-events:none">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.envio_sunat]}</span>`
                }
            },
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let link_impresion = '';
                    if (row.tp_servicio == "PASAJE") {
                        link_impresion = instance.CONSTS.URL.IMPRESION_PASAJE.COMPROBANTE
                    } else if (row.tp_servicio == "ENCOMIENDA") {
                        link_impresion = instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE;
                    } else if (row.id_tp_venta == 4) {
                        link_impresion = instance.CONSTS.URL.IMPRESION_NOTA_VENTA.COMPROBANTE;
                    } else {
                        link_impresion = instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE;
                    }
                    let newRow = `
                               <a href="${instance._URL_ + link_impresion + row.id_venta}" target="_blank" class="btn btnColorViolet mb-1" title="Comprobante"><i class="fa-light fa-print"></i></a>
                           `
                    return newRow
                }
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch1", "manual")
    instance.Datatable.inputSearch(table_resumen, ".inputSearchResumen", ".btnSearch2", "manual")
    instance.Datatable.inputSearch(table_reenvio, ".inputSearchReenvio", ".btnSearch3", "manual")

    let tp_servicio_comp = document.querySelector("#tp_servicio");
    $('#table_comprobantes tbody').on('click', '.btnNota', function (e) {
        data = table.row($(this).parents()).data();
        tp_servicio_comp.value = data.tp_servicio ? data.tp_servicio : 'FACTURADOR';
        crear_nota(data.id_venta)
    });

    const crear_nota = (id_venta) => {
        let formData = new FormData();

        formData.append('id_venta', id_venta);
        fetch(instance._URL_ + 'comprobantes/get_comprobante_n', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log(response)
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    let objData = data.message;
                    if ('serie' in objData && 'correlativo' in objData) {
                        document.querySelector('#txtserie').value = objData.serie;
                        document.querySelector('#txtcorrelativo').value = objData.correlativo;
                        document.querySelector('#txtcliente').value = objData.cliente;
                        document.querySelector('#txt_moneda').value = objData.moneda;
                        document.querySelector('#id_venta').value = objData.id_venta;
                        tp_c = objData.codigo;
                        MostrarSerieNota(tp_c);
                        if (tp_servicio_comp.value == "PASAJE") {
                            items_comprobante = [{ ...objData, _uid: 0 }];
                            iniciar_tablaP(items_comprobante);
                            calcularTotales(items_comprobante);
                        } else if (tp_servicio_comp.value == "ENCOMIENDA") {
                            consultar_items_comprobantes('comprobantes/get_items_n', id_venta);
                        } else {
                            consultar_items_comprobantes('facturador/get_items_comprobante', id_venta);
                        }
                        $('#modal_nota').modal('show');
                    } else {
                        Swal.fire("Error", "Datos de comprobante incompletos", 'error');
                    }
                } else {
                    Swal.fire("Error", "Error al cargar los datos", 'error');
                }

            })
            .catch(error => {
                console.error('❌ Error:', error);
            });
    };

    let items_comprobante = [];
    let tabla_productos = null;
    let filaSeleccionadaUid = null
    let save_nota = document.querySelector("#button_save")
    let save_precio = document.querySelector("#btn_save")
    let btn_cancel = document.querySelector("#button_cancel")
    let btn_load = document.querySelector("#button_loadSave")
    let form_nota = document.querySelector('#form_notas');
    let form_precio = document.querySelector('#form_precio');
    let txt_precio = "";

    let form_pc = document.querySelector("#form_pc")
    let id_comprobante_pc = document.querySelector("#id_comprobante_pc")
    let button_cancel_pc = document.querySelector("#button_cancel_pc")
    let button_save_pc = document.querySelector("#button_save_pc")
    let button_loadSave_pc = document.querySelector("#button_loadSave_pc")

    function consultar_items_comprobantes(ruta = '', id_venta) {
        let formData = new FormData();
        formData.append('id_venta', id_venta);
        fetch(instance._URL_ + ruta, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    items_comprobante = data.message.map((item, index) => ({
                        ...item,
                        _uid: index
                    }));
                    iniciar_tablaP(items_comprobante);
                    calcularTotales(items_comprobante);
                } else {
                    Swal.fire("Error", "Error al recopilar información de ítems de comprobantes", 'error');
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
            });
    }

    //Iniciar tabala de productos
    function iniciar_tablaP(datos) {
        if (tabla_productos && $.fn.DataTable.isDataTable('#table_productos')) {
            tabla_productos.clear().rows.add(datos).draw();
            return;
        }

        tabla_productos = $('#table_productos').DataTable({
            data: datos,
            responsive: false,
            fixedHeader: true,
            dom: 'rtip',
            paging: false,
            info: false,
            ordering: false,

            columns: [
                { data: "descripcion" },
                { data: "unidad_medida" },
                {
                    data: "cantidad",
                    render: d => d > 0 ? d : 1
                },
                {
                    data: "precio_unitario",
                    render: d => Number(d).toFixed(10)
                },
                {
                    data: "descuento",
                    render: d => Number(d).toFixed(2)
                },
                {
                    data: "total",
                    render: d => Number(d).toFixed(2)
                },
                {
                    data: null,
                    orderable: false,
                    render: row => `
                    <a class="btn btnEditRegis" data-bs-whatever="${row.total}">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a class="btn btnDeleteRegis" data-id="${row.id_venta}">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                `
                },
                {
                    data: "valor_unitario",
                    visible: false
                },
                {
                    data: "igv",
                    visible: false
                },
                {
                    data: "op_gravada",
                    visible: false
                },
                {
                    data: "op_exonerada",
                    visible: false
                },
                {
                    data: "op_inafecta",
                    visible: false
                }
            ],
            language: {
                url: './public/plugins/datatable/language/es_es.json'
            }
        });
    }
    function calcularTotales(items) {
        let opGravada = 0;
        let opExonerada = 0;
        let opInafecta = 0;
        let opTotal = 0;
        let igv = 0;

        items.forEach(item => {
            opGravada += Number(item.op_gravada) || 0;
            opExonerada += Number(item.op_exonerada) || 0;
            opInafecta += Number(item.op_inafecta) || 0;
            opTotal += Number(item.total) || 0;
            igv += Number(item.igv) || 0;
        });

        $('#op_gravada').text(opGravada.toFixed(2));
        $('#op_exonerada').text(opExonerada.toFixed(2));
        $('#op_inafecta').text(opInafecta.toFixed(2));
        $('#igv_total').text(igv.toFixed(2));
        $('#total_pagar').text(opTotal.toFixed(2));
    }

    function toggleBtnSaveNota() {
        if (items_comprobante.length > 0) {
            save_nota.style.display = 'block';
        } else {
            save_nota.style.display = 'none';
        }
    }

    $(document).on('click', '.btnDeleteRegis', function () {
        const fila = tabla_productos.row($(this).closest('tr'));
        const index = fila.index(); // Índice de la fila

        // Eliminar por índice del array
        items_comprobante.splice(index, 1);

        iniciar_tablaP(items_comprobante);
        calcularTotales(items_comprobante);
        toggleBtnSaveNota();
    });

    $("#modal_nota").on("shown.bs.modal", function () {
        if (tabla_productos) {
            tabla_productos.columns.adjust().responsive.recalc();
        }
    });

    let filaSeleccionada = null;

    // Evento al ABRIR el modal (mejor que al hacer click)
    $('#modal_precio').on('show.bs.modal', function (e) {
        if (filaSeleccionada) {
            let precio = Number(filaSeleccionada.data().total);

            let inputPrecio = document.getElementById('precio_total');
            inputPrecio.value = precio;

            // Limpiar validación
            inputPrecio.classList.remove('is-invalid', 'is-valid');
            form_precio.classList.remove('was-validated');
        }
    });

    // Evento para seleccionar fila
    $('#table_productos tbody').on('click', '.btnEditRegis', function (e) {
        filaSeleccionada = tabla_productos.row($(this).closest('tr'));

        tabla_productos.$('tr.selected').removeClass('selected');
        $(filaSeleccionada.node()).addClass('selected');
        filaSeleccionadaUid = filaSeleccionada.data()._uid;
        $('#modal_precio').modal('show');
    });

    // Evento al CERRAR el modal
    $('#modal_precio').on('hidden.bs.modal', function () {

        let inputPrecio = document.getElementById('precio_total');
        inputPrecio.value = '';
        inputPrecio.classList.remove('is-invalid', 'is-valid');

        form_precio.classList.remove('was-validated');

        filaSeleccionada = null;
        filaSeleccionadaUid = null
        tabla_productos.$('tr.selected').removeClass('selected');
        $('#modal_nota').removeClass('overlay-background');

    });

    $("#modal_nota").on("hidden.bs.modal", (e) => {
        document.getElementById("form_notas").reset();
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_nota) {
            form_nota.classList.remove("was-validated")
        })
        save_nota.style.display = 'block';
        document.getElementById('list_comp').value = "07";
        tp_servicio_comp.value = "";
        tabla_productos.clear();
    })


    $('#table_comprobantes tbody').on('click', '.btnAnular', function (e) {
        const data = table.row($(this).parents()).data()
        const link = $(this).data("link");
        anular(data.id_venta, data.id_tp_comprobante, link)
    });

    $('#table_resumen tbody').on('click', '.btnEnvio', function (e) {
        const data = table_resumen.row($(this).parents()).data()
        consultar_cdr(data.id_resumen)
    });

    $('#table_comprobantes tbody').on('click', '.btnReenviar', function (e) {
        const data = table.row($(this).parents()).data()
        reenviar(data.id_venta, data.id_tp_comprobante, data.estado, data.tp_servicio)
    });

    $('#table_comprobantes tbody').on('click', '.btnPagarComp', function (e) {
        const data = table.row($(this).parents()).data()
        id_comprobante_pc.value = data.id_venta
        $("#modal_pc").modal("show");
    });

    $('#table_comprobantes tbody').on('click', '.btnConsultarSUNAT', function (e) {
        const data = table.row($(this).parents()).data();
        document.getElementById('loader').classList.remove('d-none');

        let formData = new FormData();
        formData.set('id_venta', data.id_venta);
        fetch(instance._URL_ + 'encomienda/consultar_comprobanteSUNAT', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                let icon = 'error';
                switch (data.message) {
                    case 'El comprobante existe y está aceptado.':
                        icon = 'success';
                        break;
                    case 'El comprobante existe pero está de baja.':
                        icon = 'info';
                        break;
                    default:
                        icon = 'error';
                        break;
                }
                Swal.fire({
                    title: 'Respuesta SUNAT',
                    html: `
                                <p><strong>Mensaje:</strong> ${data.message}</p>
                            `,
                    icon: icon
                });
            })
            .catch(error => {
                console.error('❌ Error:', error);
            })
            .finally(() => {
                document.getElementById('loader').classList.add('d-none');
            });
    });

    $('#table_comprobantes tbody').on('click', '.btnDetallePC', function (e) {
        const data = table.row($(this).parents()).data()

        let formData = new FormData();
        formData.set("id_comprobante", data.id_venta);

        fetch(instance._URL_ + 'comprobantes/detalle_pago_cuota', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const item = data.data[0];

                    // Construir la fila del archivo según su tipo
                    let filaArchivo = '';
                    if (item.pago_file) {
                        const extension = item.pago_file.split('.').pop().toLowerCase();
                        const esImagen = ['jpg', 'jpeg', 'png'].includes(extension);

                        if (esImagen) {
                            filaArchivo = `
                            <tr>
                                <td class="label">Comprobante de pago</td>
                                <td>
                                    <a href="${item.pago_file}" target="_blank">
                                        <img src="${item.pago_file}" alt="Comprobante de pago"
                                            style="max-width:150px; max-height:150px; border-radius:4px;">
                                    </a>
                                </td>
                            </tr>`;
                        } else {
                            filaArchivo = `
                            <tr>
                                <td class="label">Comprobante de pago</td>
                                <td>
                                    <a href="${item.pago_file}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-arrow-down"></i> Ver archivo
                                    </a>
                                </td>
                            </tr>`;
                        }
                    } else {
                        filaArchivo = `
                        <tr>
                            <td class="label">Comprobante de pago</td>
                            <td><span class="text-muted">No adjuntado</span></td>
                        </tr>`;
                    }

                    const tbody = document.querySelector('#tabla_detalle_pago tbody');
                    tbody.innerHTML = `
                    <tr>
                        <td class="label">Serie-Correlativo</td>
                        <td>${item.serie_corr}</td>
                    </tr>
                    <tr>
                        <td class="label">Importe</td>
                        <td>S/ ${parseFloat(item.importe).toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha vencimiento</td>
                        <td>${item.fecha_vencimiento}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha de pago</td>
                        <td>${item.fecha_pago}</td>
                    </tr>
                    <tr>
                        <td class="label">Medio pago</td>
                        <td>${item.descripcion}</td>
                    </tr>
                    <tr>
                        <td class="label">Observación</td>
                        <td>${item.observacion}</td>
                    </tr>
                    ${filaArchivo}
                `;

                    $("#modal_detallePC").modal("show");
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Ooops..',
                        text: 'No se ha encontrado ningun detalle',
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: '¡Atención!',
                    text: 'Comuniquese con el área de soporte',
                });
            });
    });
    //Funcion para consultar el CDR del resumen enviado 
    const consultar_cdr = (id_resumen = null) => {
        Swal.showLoading();
        let paginaActual = table_resumen.page();
        let formData = new FormData();
        formData.set("id_resumen", id_resumen);

        fetch(instance._URL_ + "pasaje/consultar_cdr", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Network response was not ok.');
                }
            })
            .then(data => {
                if (data.success == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Consulta exitosa',
                        text: data.message,
                    });
                    table_resumen.ajax.reload(null, false);
                    table_resumen.page(paginaActual).draw(false);
                } else if (data.success == false) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de consulta',
                        text: data.message,
                    });
                }

            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de consulta',
                    text: data.message,
                });
            })
            .finally(() => {

            });
    };

    //Reenvio por resumen
    $(document).ready(function () {

        let todosSeleccionados = false;
        let accionProgramatica = false;

        $('#btnSeleccionarTodos').on('click', function () {
            accionProgramatica = true;

            if (!todosSeleccionados) {
                table_reenvio.rows().select();
                $(this).html('<i class="fa-light fa-xmark"></i> Deseleccionar todos');
                $(this).removeClass('btn-outline-primary').addClass('btn-outline-danger');
                todosSeleccionados = true;
            } else {
                table_reenvio.rows().deselect();
                $(this).html('<i class="fa-light fa-check-double"></i> Seleccionar todos');
                $(this).removeClass('btn-outline-danger').addClass('btn-outline-primary');
                todosSeleccionados = false;
            }

            accionProgramatica = false;
        });

        table_reenvio.on('deselect', function () {
            if (accionProgramatica) return;

            if (table_reenvio.rows({ selected: true }).count() === 0) {
                todosSeleccionados = false;
                $('#btnSeleccionarTodos')
                    .html('<i class="fa-light fa-check-double"></i> Seleccionar todos')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-primary');
            }
        });


        $('#btnCrearResumen').on('click', function () {
            const ids = table_reenvio.rows({ selected: true }).data().pluck('id_venta').toArray();

            if (ids.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Selecciona al menos una boleta para crear el resumen.',
                });
                return;
            }

            Swal.fire({
                title: `¿Crear resumen con ${ids.length} boleta(s)?`,
                text: 'No podrá revertir los cambios',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, crear!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tu lógica de fetch aquí, usando los ids seleccionados
                    console.log('IDs seleccionados:', ids);
                    let frd = new FormData();
                    frd.set('tipo_ruc', ruc.value);
                    frd.set('ids', ids);
                    fetch(instance._URL_ + "pasaje/reenvio_porResumen", {
                        method: "POST",
                        body: frd
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('La solicitud no fue exitosa');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success == true) {
                                const mensajeSunat = data.message_sunat;

                                if (mensajeSunat) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Exito',
                                        text: mensajeSunat,
                                    });
                                } else {
                                    instance.Toast.operacion_erronea("Hubo un problema con la conexión");
                                }
                                instance.Datatable.reloadTable(table_resumen);
                                recargar_tabla();
                                instance.Datatable.reloadTable(table);
                            } else {
                                instance.Toast.operacion_erronea("No se recibió una respuesta válida de SUNAT");
                            }
                        })
                        .catch(error => {
                            instance.Toast.operacion_erronea(error);
                        });
                }
            });
        });

    });

    form_pc.addEventListener("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(form_pc);

        let data = [
            formData.get("id_comprobante_pc"),
            formData.get("medio_pago_pc"),
            formData.get("fecha_pc"),
            formData.get("caja_chica_pc"),
        ];

        // Validar el archivo SOLO si el usuario adjuntó uno
        let file = formData.get("pago_file");
        let hayArchivo = file && file.size > 0;

        if (hayArchivo) {
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Atención',
                    text: 'El archivo no debe superar los 5MB',
                });
                return;
            }

            const extensionesValidas = ['jpg', 'jpeg', 'png', 'pdf', 'pptx', 'txt', 'xlsx', 'xls', 'p12'];
            const extension = file.name.split('.').pop().toLowerCase();
            if (!extensionesValidas.includes(extension)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Atención',
                    text: 'El formato de archivo no es válido',
                });
                return;
            }
        }

        if (instance.Validate.validateData(data)) {
            button_cancel_pc.classList.add('d-none');
            button_save_pc.classList.add('d-none');
            button_loadSave_pc.classList.remove('d-none');

            fetch(instance._URL_ + 'comprobantes/pagar_comprobante', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error('Error: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    button_cancel_pc.classList.remove('d-none');
                    button_save_pc.classList.remove('d-none');
                    button_loadSave_pc.classList.add('d-none');

                    $('#modal_pc').modal('hide');

                    if (data.success) {
                        form_pc.reset();
                        form_pc.classList.remove('was-validated');
                        instance.Datatable.reloadTable(table);
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Atención!!',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    button_cancel_pc.classList.remove('d-none');
                    button_save_pc.classList.remove('d-none');
                    button_loadSave_pc.classList.add('d-none');
                    Swal.fire({
                        icon: 'error',
                        title: 'Atención!!',
                        text: 'Algo no ha salido bien comunícate con el personal de soporte',
                    });
                });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación errónea',
                text: 'Rellene correctamente los campos',
            });
        }
    });
    //Guardar los datos y demas procesos
    form_nota.addEventListener("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(form_nota);

        // Agregar productos
        items_comprobante.forEach((row, index) => {
            formData.append(`productos[${index}][descripcion]`, row.descripcion);
            formData.append(`productos[${index}][cantidad]`, row.cantidad);
            formData.append(`productos[${index}][precio_unitario]`, row.precio_unitario);
            formData.append(`productos[${index}][valor_unitario]`, row.valor_unitario || row.precio_unitario);
            formData.append(`productos[${index}][op_exonerada]`, row.op_exonerada);
            formData.append(`productos[${index}][op_gravada]`, row.op_gravada);
            formData.append(`productos[${index}][op_inafecta]`, row.op_inafecta);
            formData.append(`productos[${index}][descuento]`, row.descuento);
            formData.append(`productos[${index}][igv]`, row.igv);
            formData.append(`productos[${index}][total]`, row.total);
            formData.append(`productos[${index}][id_venta]`, row.id_venta);
            formData.append(`productos[${index}][unidad_medida]`, row.unidad_medida);
        });

        formData.append("tp_servicio", tp_servicio_comp.value);

        formData.append("op_gravada", $('#op_gravada').text() || '0.00');
        formData.append("op_exonerada", $('#op_exonerada').text() || '0.00');
        formData.append("op_inafecta", $('#op_inafecta').text() || '0.00');
        formData.append("igv_total", $('#igv_total').text() || '0.00');
        formData.append("total_pagar", $('#total_pagar').text() || '0.00');

        let data = [
            formData.get("list_comp"),
            formData.get("txtserie_nota"),
            formData.get("txtcorrelativo_nota"),
            formData.get("txtserie"),
            formData.get("txtcorrelativo"),
            formData.get("list_motivo"),
            formData.get("txt_descripcion"),
            formData.get("txt_fecha"),
            formData.get("id_venta")
        ];

        if (!instance.Validate.validateData(data)) {
            Swal.fire({
                icon: 'error',
                title: 'Operación errónea',
                text: 'Rellene correctamente los campos',
            });
            return;
        }

        // Mostrar loading
        save_nota.classList.add("d-none");
        btn_cancel.classList.add("d-none");
        btn_load.classList.remove("d-none");

        fetch(instance._URL_ + 'comprobantes/set_notas', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log(response)
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(objData => {
                save_nota.classList.remove("d-none");
                btn_cancel.classList.remove("d-none");
                btn_load.classList.add("d-none");

                if (objData.success === true) {
                    if (objData.message['message_sunat']) {
                        $('#modal_nota').modal('hide');

                        if (objData.message['estado_sunat'] === "1") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: objData.message['message_sunat'],
                            });
                        } else if (objData.message['estado_sunat'] === "2") {
                            Swal.fire({
                                icon: 'info',
                                title: 'Ops...',
                                text: objData.message['message_sunat'],
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación errónea',
                            text: 'No se recibió respuesta de SUNAT',
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operación errónea',
                        text: objData.message || 'Ha ocurrido un error',
                    });
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);

                save_nota.classList.remove("d-none");
                btn_cancel.classList.remove("d-none");
                btn_load.classList.add("d-none");

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                });
            });
    });
    //Guardar nuevo precio
    form_precio.addEventListener("submit", function (e) {
        e.preventDefault();

        if (filaSeleccionadaUid === null) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No hay fila seleccionada' });
            return;
        }

        let formData = new FormData(form_precio);
        let precio_total = parseFloat(formData.get("precio_total"));

        if (isNaN(precio_total) || precio_total <= 0) {
            Swal.fire({ icon: 'error', title: 'Precio inválido', text: 'Ingrese un precio válido mayor a 0' });
            return;
        }

        precio_total = Math.round(precio_total * 100) / 100;

        let item = items_comprobante.find(i => i._uid === filaSeleccionadaUid);
        let rowIndex = items_comprobante.indexOf(item);

        if (!item) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontró el ítem seleccionado' });
            return;
        }

        let cantidad = Number(item.cantidad) || 1;

        if (parseFloat(item.igv) === 0) {
            let precio_por_unidad = precio_total / cantidad;

            item.precio_unitario = Math.round(precio_por_unidad * 100) / 100;
            item.valor_unitario = Math.round(precio_por_unidad * 100) / 100;
            item.op_exonerada = precio_total;
            item.op_gravada = 0;
            item.op_inafecta = 0;
            item.igv = 0;
        } else {
            let precio_por_unidad_con_igv = precio_total / cantidad;
            let precio_por_unidad_sin_igv = precio_por_unidad_con_igv / 1.18;

            let base_imponible_total = precio_total / 1.18;
            let igv_calculado = precio_total - base_imponible_total;

            item.precio_unitario = Math.round(precio_por_unidad_con_igv * 100) / 100;
            item.valor_unitario = Math.round(precio_por_unidad_sin_igv * 100) / 100;
            item.igv = Math.round(igv_calculado * 100) / 100;
            item.op_gravada = Math.round(base_imponible_total * 100) / 100;
            item.op_exonerada = 0;
            item.op_inafecta = 0;
        }

        item.total = precio_total;
        item.op_total = precio_total;

        items_comprobante[rowIndex] = item;
        iniciar_tablaP(items_comprobante);
        calcularTotales(items_comprobante);

        $('#modal_precio').modal('hide');

        Swal.fire({ icon: 'success', title: 'Precio actualizado', timer: 1500, showConfirmButton: false });
    });

    //Reenvio individual 
    const reenviar = (id_venta = null, id_tp_comprobante = null, estad_comp = null, tp_servicio_comp = null) => {
        Swal.fire({
            title: '¿Esta seguro de reenviar el comprobante?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Reenviar!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData()
                formData.set("id_ventaPasaje", id_venta ? id_venta : id_ventaPasaje.value)
                formData.set("tp_comprobante", id_tp_comprobante ? id_tp_comprobante : tp_comprobante.value)
                let url_reenvio = "";
                if (tp_servicio_comp == "PASAJE") {
                    url_reenvio = "pasaje/reenviar_venta"
                } else if (tp_servicio_comp == "ENCOMIENDA") {
                    url_reenvio = "encomienda/reenviar_venta"
                } else {
                    url_reenvio = "facturador/reenviar_venta"
                }

                fetch(instance._URL_ + url_reenvio, {
                    method: "POST",
                    body: formData
                })
                    .then(response => {
                        if (response.ok)
                            return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message.message)
                            instance.Datatable.reloadTable(table)
                        } else {
                            instance.Toast.operacion_erronea(data.message.message)
                        }
                    })
                    .catch(error => {
                        // Mostrar detalles adicionales del error
                        const errorMessage = error.message || 'Error desconocido';
                        instance.Toast.operacion_erronea(errorMessage);
                        console.error('Error en la solicitud:', error);
                    });
            }
        })
    }

    $("#modal_pc").on("hidden.bs.modal", (e) => {
        $("#modal_pc").find("form").trigger("reset")
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_pc) {
            form_pc.classList.remove("was-validated")
        })
        $("#medio_pago_pc").val("").trigger("change")
    })

    window.addEventListener('load', function () {
        fntMotivosNota();
        document.getElementById('list_comp').addEventListener('change', function () {
            actualizarMotivos();
            MostrarSerieNota(tp_c);
        });
    }, false);

    let motivos = [];

    function fntMotivosNota() {
        if (document.querySelector('#list_motivo')) {
            let ajaxUrl = instance._URL_ + 'comprobantes/getSelectMotivos';
            let request = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
            request.open("GET", ajaxUrl, true);
            request.send();
            request.onreadystatechange = function () {
                if (request.readyState == 4 && request.status == 200) {
                    motivos = JSON.parse(request.responseText);
                    actualizarMotivos();
                    MostrarSerieNota();
                }
            }
        }
    }

    function MostrarSerieNota(tp_c) {
        let list_comp = document.getElementById('list_comp');
        let txtserie_nota = document.getElementById('txtserie_nota');
        let formData = new FormData();
        formData.set('tp_comprobante', list_comp.value);

        fetch(instance._URL_ + "comprobantes/get_serieForTpComprobante", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (response.ok) return response.json();
            })
            .then(data => {
                txtserie_nota.innerHTML = "";
                if (data.success) {
                    if (typeof data.message === 'object' && data.message !== null) {
                        if (tp_c === "01") {
                            // Buscar serie con "F" en su nombre
                            let serieSeleccionada = data.message.find(serie => serie.serie.includes("F"));
                            if (serieSeleccionada) {
                                txtcorrelativo_nota.value = serieSeleccionada.correlativo;
                                txtserie_nota.insertAdjacentHTML('beforeend', `<option value="${serieSeleccionada.serie}">${serieSeleccionada.serie}</option>`);
                            }
                        } else if (tp_c === "03") {
                            // Buscar serie con "B" en su nombre
                            let serieSeleccionada = data.message.find(serie => serie.serie.includes("B"));
                            if (serieSeleccionada) {
                                txtcorrelativo_nota.value = serieSeleccionada.correlativo;
                                txtserie_nota.insertAdjacentHTML('beforeend', `<option value="${serieSeleccionada.serie}">${serieSeleccionada.serie}</option>`);
                            }
                        }
                    } else {
                        console.error('La respuesta del servidor no es válida:', data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
            });
    }


    function actualizarMotivos() {

        let list_comp = document.getElementById('list_comp');
        let selectMotivo = document.getElementById('list_motivo');
        let descripcion = document.getElementById('txt_descripcion');

        let tipoSeleccionado = list_comp.value;
        if (tipoSeleccionado == "07") {
            tipoSeleccionado = "C";
        } else {
            tipoSeleccionado = "D";
        }

        selectMotivo.innerHTML = '';
        descripcion.innerHTML = '';

        let motivosFiltrados = motivos.filter(motivo => motivo.tipo === tipoSeleccionado);

        if (motivosFiltrados.length === 0) {
            let option = document.createElement('option');
            option.text = 'No hay opciones disponibles';
            selectMotivo.add(option);
        } else {

            let descripcion01 = motivosFiltrados.find(motivo => motivo.codigo === "01");
            motivosFiltrados.forEach((motivo) => {
                let option = document.createElement('option');
                option.value = motivo.codigo;
                option.text = motivo.descripcion;
                selectMotivo.add(option);
                descripcion.value = descripcion01.descripcion;
            });
        }
    }

    // ANULAR
    const anular = (id_venta = null, id_tp_comprobante = null, link = null) => {
        Swal.fire({
            title: '¿Esta seguro que desea anular la venta?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Anular!',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Anulando la venta",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                });
                Swal.showLoading()
                let formData = new FormData()
                formData.set("id_ventaPasaje", id_venta)
                formData.set("id_venta", id_venta)
                formData.set("tp_comprobante", id_tp_comprobante)

                fetch(instance._URL_ + link, {
                    method: "POST",
                    body: formData
                })
                    .then(response => { if (response.ok) return response.json() })
                    .then(data => {
                        if (data.success) {
                            console.log(data)
                            Swal.close()
                            // Anulando la venta
                            Swal.fire({
                                title: "Exito",
                                text: data.message,
                                icon: "success"
                            });
                            instance.Datatable.reloadTable(table)
                            instance.Datatable.reloadTable(table_resumen)
                        } else {
                            instance.Toast.operacion_erronea(data.message)
                        }
                    })
                    .catch(error => {
                        instance.Toast.operacion_erronea(error.message)
                    })
            }
        })

    }

    // Cambios en rangos de fechas
    $("#fecha_inicio").on('change', function (e) {
        e.preventDefault();
        const fecha_i = $(this).val();
        recargar_tabla();
    });

    $("#fecha_fin").on('change', function (e) {
        e.preventDefault();
        const fecha_inicio_val = $("#fecha_inicio").val();
        if (!fecha_inicio_val) {
            Swal.fire({
                icon: 'error',
                title: 'Atencion',
                text: "Se necesita completar la fecha de inicio para el filtro",
            })
            return false;
        }
        recargar_tabla();
    });

    $("#ruc").on('change', function (e) {
        e.preventDefault();
        recargar_tabla();
    });


    // Función para recargar la tabla usando fetch con FormData
    async function recargar_tabla() {
        try {
            const formData = new FormData();
            if (ruc.value == '') {
                return;
            }
            formData.append('tp_comprobante[]', 3);
            formData.append('ruc', ruc.value);

            const fecha_inicio = $("#fecha_inicio").val();
            const fecha_fin = $("#fecha_fin").val();

            if (fecha_inicio) {
                formData.append('fecha_inicio', fecha_inicio);
            }
            if (fecha_fin) {
                formData.append('fecha_fin', fecha_fin);
            }

            const response = await fetch(instance._URL_ + 'comprobantes/get_dataTableReenvio', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            table_reenvio.clear();
            table_reenvio.rows.add(data.data || data);
            table_reenvio.draw();

        } catch (error) {
            console.error('Error al recargar la tabla:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al cargar los datos. Por favor, inténtalo de nuevo.',
            });
        }
    }

    limitarFechaDesdeHoy("fecha_pc")

    // Agregar al final del archivo main.js

    // Variables para almacenar estado de filtros
    let filtrosComp = {
        fecha_inicio: '',
        fecha_fin: '',
        tp_comprobante: '',
        tipo_venta: ''
    };

    let filtrosRes = {
        fecha_inicio: '',
        fecha_fin: ''
    };

    // Mostrar/ocultar filtros según pestaña activa
    document.querySelectorAll('#pillsTab_contrato .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            if (e.target.id === 'tab_comprobante') {
                document.getElementById('filtrosResumen').style.display = 'none';
            } else if (e.target.id === 'tab_resumen') {
                document.getElementById('filtrosResumen').style.display = 'block';
            } else if (e.target.id === 'tab_reenvio') {
            }
        });
    });

    // ============================================
    // FILTROS PARA COMPROBANTES
    // ============================================

    // Aplicar filtros
    document.getElementById('btnFiltrarComp').addEventListener('click', function () {
        filtrosComp.fecha_inicio = document.getElementById('filtro_fecha_inicio_comp').value;
        filtrosComp.fecha_fin = document.getElementById('filtro_fecha_fin_comp').value;
        filtrosComp.tp_comprobante = document.getElementById('filtro_tipo_comp').value;
        filtrosComp.tipo_venta = document.getElementById('filtro_tipo_venta').value;

        aplicarFiltrosComprobantes();
    });

    // Limpiar filtros
    document.getElementById('btnLimpiarFiltrosComp').addEventListener('click', function () {
        document.getElementById('filtro_fecha_inicio_comp').value = '';
        document.getElementById('filtro_fecha_fin_comp').value = '';
        document.getElementById('filtro_tipo_comp').value = '';
        document.getElementById('filtro_tipo_venta').value = '';

        filtrosComp = {
            fecha_inicio: '',
            fecha_fin: '',
            tp_comprobante: '',
            tipo_venta: ''
        };

        aplicarFiltrosComprobantes();
    });

    function aplicarFiltrosComprobantes() {
        table.ajax.url(instance._URL_ + 'comprobantes/dataTable').load();

        // Mostrar indicador de filtros activos
        let filtrosActivos = [];
        if (filtrosComp.fecha_inicio && filtrosComp.fecha_fin) filtrosActivos.push('Fecha');
        if (filtrosComp.tp_comprobante) filtrosActivos.push('Comprobante');
        if (filtrosComp.tipo_venta) filtrosActivos.push('Tipo');

        if (filtrosActivos.length > 0) {
            Swal.fire({
                icon: 'info',
                title: 'Filtros aplicados',
                text: filtrosActivos.join(', '),
                timer: 2000,
                showConfirmButton: false
            });
        }
    }

    // ============================================
    // FILTROS PARA RESÚMENES
    // ============================================

    // Aplicar filtros
    document.getElementById('btnFiltrarRes').addEventListener('click', function () {
        filtrosRes.fecha_inicio = document.getElementById('filtro_fecha_inicio_res').value;
        filtrosRes.fecha_fin = document.getElementById('filtro_fecha_fin_res').value;

        aplicarFiltrosResumenes();
    });

    // Limpiar filtros
    document.getElementById('btnLimpiarFiltrosRes').addEventListener('click', function () {
        document.getElementById('filtro_fecha_inicio_res').value = '';
        document.getElementById('filtro_fecha_fin_res').value = '';

        filtrosRes = {
            fecha_inicio: '',
            fecha_fin: ''
        };

        aplicarFiltrosResumenes();
    });

    function aplicarFiltrosResumenes() {
        table_resumen.ajax.url(instance._URL_ + 'comprobantes/get_dataTableResumen').load();

        if (filtrosRes.fecha_inicio && filtrosRes.fecha_fin) {
            Swal.fire({
                icon: 'info',
                title: 'Filtros aplicados',
                text: 'Período: ' + filtrosRes.fecha_inicio + ' al ' + filtrosRes.fecha_fin,
                timer: 2000,
                showConfirmButton: false
            });
        }
    }

    // ============================================
    // EXPORTACIÓN A EXCEL
    // ============================================

    // Exportar comprobantes
    document.getElementById('btnExportarExcelComp').addEventListener('click', function () {
        // ✅ CORREGIDO: Llamar al controlador, no a la vista
        let url = instance._URL_ + 'comprobantes/exportarExcel?';
        let params = [];
        const search = $('#table_comprobantes').DataTable().search();

        if (filtrosComp.fecha_inicio) params.push('fecha_inicio=' + filtrosComp.fecha_inicio);
        if (filtrosComp.fecha_fin) params.push('fecha_fin=' + filtrosComp.fecha_fin);
        if (filtrosComp.tp_comprobante) params.push('tp_comprobante=' + filtrosComp.tp_comprobante);
        if (filtrosComp.tipo_venta) params.push('tipo_venta=' + encodeURIComponent(filtrosComp.tipo_venta));
        if (search){params.push('search=' + encodeURIComponent(search))}

        url += params.join('&');

        // Mostrar loading
        Swal.fire({
            title: 'Generando Excel',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Redirigir para descarga - AHORA PASA POR EL CONTROLADOR
        window.location.href = url;

        setTimeout(() => {
            Swal.close();
        }, 2000);
    });

    // Exportar resúmenes
    document.getElementById('btnExportarExcelRes').addEventListener('click', function () {
        let url = instance._URL_ + 'comprobantes/exportarExcelResumenes?';
        let params = [];

        if (filtrosRes.fecha_inicio) params.push('fecha_inicio=' + filtrosRes.fecha_inicio);
        if (filtrosRes.fecha_fin) params.push('fecha_fin=' + filtrosRes.fecha_fin);

        url += params.join('&');

        // Mostrar loading
        Swal.fire({
            title: 'Generando Excel',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Redirigir para descarga
        window.location.href = url;

        // Cerrar loading después de 2 segundos
        setTimeout(() => {
            Swal.close();
        }, 2000);
    });

})
