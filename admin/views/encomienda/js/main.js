import * as instance from "../../../public/js/instance.js"

document.getElementById("link_encomienda").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {

    // CARGAR TERMINALES PARA FILTROS
    let permiso_rotulado = document.getElementById("permiso_rotulado");

    function cargarTerminalesParaFiltros() {

        fetch(instance._URL_ + 'encomienda/get_allTerminalesParaFiltro')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Llenar todos los selects de terminal (ORIGEN y DESTINO)
                    const terminalSelects = document.querySelectorAll('.terminal-select');
                    terminalSelects.forEach(select => {
                        // Limpiar opciones existentes (excepto la primera "TODOS")
                        while (select.options.length > 1) {
                            select.remove(1);
                        }
                        data.message.forEach(terminal => {
                            const option = document.createElement('option');
                            option.value = terminal.id_terminal;
                            option.textContent = terminal.nombre;
                            select.appendChild(option);
                        });
                    });
                    // Llenar selects de ubigeo para G.R. - Transportista
                    const ubigeoSelects = document.querySelectorAll('.terminal-ubigeo-select');
                    ubigeoSelects.forEach(select => {
                        // Limpiar opciones existentes (excepto la primera "TODOS")
                        while (select.options.length > 1) {
                            select.remove(1);
                        }
                        // Agregar nuevas opciones
                        data.message.forEach(terminal => {
                            if (terminal.ubigeo) {
                                const option = document.createElement('option');
                                option.value = terminal.ubigeo;
                                option.textContent = terminal.nombre;
                                select.appendChild(option);
                            }
                        });
                    });
                } else {
                    console.error('Error al cargar terminales:', data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
            });
    }

    // Llamar a la función después de que el DOM esté listo
    cargarTerminalesParaFiltros();

    const table_comprobante = $('#table_comprobante').DataTable({
        processing: true,
        serverSide: true,
        rowId: "id_venta",

        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTable',
            'method': 'POST',
            'data': function (d) {
                // Agregar los parámetros por defecto
                d.tp_comprobante = [1, 3];

                // Agregar los filtros personalizados
                d.fecha_inicio = $('#fecha_inicio_comprobante').val();
                d.fecha_fin = $('#fecha_fin_comprobante').val();
                d.tipo = $('#tipo_comprobante').val();
                d.origen = $('#origen_comprobante').val();
                d.destino = $('#destino_comprobante').val();
                d.estado_envio = $('#estado_envio_comprobante').val();
                d.estado_venta = $('#estado_venta_comprobante').val();

                return d;
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
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "remitente_apellidos",
                render: (data, type, row) => {
                    return `${row.remitente_nombres} ${row.remitente_apellidos}<br>${row.remitente_num_docu}`
                }
            },
            {
                "data": "destinatario_apellidos",
                render: (data, type, row) => {
                    return `${row.destinatario_nombres} ${row.destinatario_apellidos}<br>${row.destinatario_num_docu}`
                }
            },
            {
                "data": "origen",
                render: (data, type, row) => {
                    return `${row.origen || '---'}`
                }
            },
            {
                "data": "destino",
                render: (data, type, row) => {
                    return `${row.destino}`
                }
            },
            {
                "data": "encomienda_fecha_salida",
                render: (data, type, row) => {
                    return `${row.encomienda_fecha_salida.split("-").reverse().join("-")}`
                }
            },
            {
                "data": "encomienda_estado_envio",
                render: (data, type, row) => {
                    let estado = row.salida == 1 && row.encomienda_estado_envio != 'ENTREGADO' ? `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA['EN RUTA']}">EN RUTA</span>` : `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[row.encomienda_estado_envio]}">${row.encomienda_estado_envio}</span>`;
                    return `${estado}`;
                }
            },
            {
                "data": "envio_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.envio_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.envio_sunat]}</span>`
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
                render: (data, type, row) => {
                    return row.total ? `S/ ${row.total}` : "0.00"
                }
            },
            {
                "data": "tracking",
                render: (data, type, row) => {
                    return row.tracking ?
                        `<span class="badge" style="background-color: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8;">${row.tracking}</span>` :
                        `<span class="text-muted">---</span>`;
                }
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[row.estado]}">${row.estado}</span>`
                }
            },
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    //------------------------------NUEVO----------------------------------
                    let btn_consultar_comprobante = `<a id="consultarSUNAT" data-id="${row.id_venta}" class="dropdown-item btn_dropdown btnConsultarSUNAT"><i class="fa-light fa-cloud-check me-2 text-success"></i>Consultar SUNAT</a>`
                    let html_anular = `<a class="dropdown-item btn_dropdown btnSmallAnular mb-1" font-size:12px" title="Anular"><i class="fa-light fa-ban me-2 text-danger"></i>Anular</a>`
                    let html_xml = row.file_xml ? `<a class="dropdown-item btn_dropdown btnXML mb-1" href="${row.file_xml}" target="_blank" title="XML"><i class="fa-light fa-code me-2 text-primary"></i>XML</a>` : '';
                    let html_cdr = row.envio_sunat == 1 ? `<a class="dropdown-item btn_dropdown btnCDR mb-1" href="${row.file_cdr}" target="_blank" title="CDR"><i class="fa-light fa-file-certificate me-2 text-success"></i>CDR</a>` : '';
                    let html_reenviar = row.envio_sunat == 0 ? `<a class="dropdown-item btn_dropdown btnReenviar mb-1" id="reenviarVenta" data-id="${row.id_venta}" title="Reenviar"> <i class="fa-light fa-paper-plane-top me-2 text-warning"></i>Reenviar a SUNAT</a>` : '';
                    let html_guia_remision = `<a class="dropdown-item btn_dropdown btnCrearGuiaT mb-1" font-size:12px" title="Crear guia transportista"><i class="fa-light fa-truck-ramp-box me-2 text-info"></i>Crear Guia Transportista</a>`
                    let html_ver_links = ` <a data-bs-toggle="modal" data-bs-target="#modal_links" data-bs-whatever="LINKS MAPS PARTIDA-LLEGADA" class="dropdown-item btn_dropdown btnSmallVerLinks"><i class="fa-light fa-location-dot me-2 text-danger"></i>Ver links Maps</a>`
                    let html_comprobante = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-receipt me-2 text-primary"></i>Comprobante</a>`;
                    let html_comprobante_a4 = `<a href="${instance._URL_ + 'encomienda/impresion/comprobanteA4/' + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-file-lines me-2 text-primary"></i>Comprobante A4</a>`
                    let html_transportista = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.TRANSPORTISTA + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-truck-fast me-2 text-secondary"></i>Transportista</a>`;
                    let html_rotulado = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.ROTULADO + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-barcode me-2"></i>Rotulado</a>`;
                    let html_archivo = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.ARCHIVO + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-box-archive me-2 text-secondary"></i>Archivo</a>`;
                    let btn_destino_erroneo = `<a class="dropdown-item btn_dropdown btnDestinoErroneo mb-1 text-danger" style="font-size:12px;" title="Destino Erróneo"><i class="fa-light fa-triangle-exclamation me-2"></i>Marcar como destino erróneo</a>`;
                    let btn_timeline = `<a class="dropdown-item btn_dropdown btnTimelineEncomienda mb-1"><i class="fa-light fa-route me-2 text-primary"></i>Seguimiento de la encomienda</a>`;

                    return `
                    <div class="dropdown">
                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu">
                       ${row.encomienda_estado_envio == 'EN ORIGEN' && row.e_domicilio == 1 ? html_guia_remision : ''}
                       ${html_comprobante}
                       ${html_comprobante_a4}
                       ${html_transportista}
                       ${permiso_rotulado.value == 1 ? html_rotulado : ''}
                       ${html_archivo}
                       ${row.estado != "ANULADO" && !["EN TRANSITO", "EN DESTINO", "CANCELADO"].includes(row.encomienda_estado_envio) && row.envio_sunat && instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE == 1 && row.id_terminal == instance._ID_TERMINAL_SESION ? html_anular : ''}
                       ${html_xml}
                       ${html_cdr}
                       ${html_reenviar}
                       ${btn_consultar_comprobante}
                       ${row.link_partida || row.link_llegada ? html_ver_links : ''}
                       ${btn_timeline}
                    
                       </ul>
                    </div>`
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    const table_notaVenta = $('#table_notaVenta').DataTable({
        processing: true,
        serverSide: true,
        rowId: "id_venta",
        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTable',
            'method': 'POST',
            'data': function (d) {
                d.tp_comprobante = [2];
                d.fecha_inicio = $('#fecha_inicio_notaventa').val();
                d.fecha_fin = $('#fecha_fin_notaventa').val();
                d.origen = $('#origen_notaventa').val();
                d.destino = $('#destino_notaventa').val();
                d.estado_envio = $('#estado_envio_notaventa').val();
                d.estado_venta = $('#estado_venta_notaventa').val();
                return d;
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
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
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
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "remitente_apellidos",
                render: (data, type, row) => {
                    return `${row.remitente_nombres} ${row.remitente_apellidos}<br>${row.remitente_num_docu}`
                }
            },
            {
                "data": "destinatario_apellidos",
                render: (data, type, row) => {
                    return `${row.destinatario_nombres} ${row.destinatario_apellidos}<br>${row.destinatario_num_docu}`
                }
            },
            {
                "data": "origen",
                render: (data, type, row) => {
                    return `${row.origen || '---'}`
                }
            },
            {
                "data": "destino",
                render: (data, type, row) => {
                    return `${row.destino}`
                }
            },
            {
                "data": "encomienda_fecha_salida",
                render: (data, type, row) => {
                    return `${row.encomienda_fecha_salida.split("-").reverse().join("-")}`
                }
            },
            {
                "data": "encomienda_estado_envio",
                render: (data, type, row) => {
                    let estado = row.salida == 1 && row.encomienda_estado_envio != 'ENTREGADO' ? `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA['EN RUTA']}">EN RUTA</span>` : `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[row.encomienda_estado_envio]}">${row.encomienda_estado_envio}</span>`;
                    return `${estado}`;
                }
            },
            {
                "data": "total",
                render: (data, type, row) => {
                    return `S/ ${row.total}`
                }
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[row.estado]}">${row.estado}</span>`
                }
            },
            {
                "data": "tracking",
                render: (data, type, row) => {
                    return row.tracking ?
                        `<span class="badge" style="background-color: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8;">${row.tracking}</span>` :
                        `<span class="text-muted">---</span>`;
                }
            },
            {
                "data": "pago_e",
                render: (data, type, row) => {
                    if (row.pago_e) {
                        let ruta_impresion = row.estado == "PAGO EN BLOQUE" ? instance.CONSTS.URL.IMPRESION_NOTA_VENTA.COMPROBANTE : instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE;
                        return `<span class="badge" style="background-color:white; color:black; font-weight: 700">Pagado con: <a href="${instance._URL_ + ruta_impresion + row.id_pago}" target="_blank" style="color:blue;">${row.pago_e}</a></span>`
                    } else {
                        return `<span class="badge" style="background-color:white; color:black; font-weight: 700">-</span>`
                    }
                }
            },

            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let html_editar = ` <a data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR ENCOMIENDA" data-mode="edit" class="dropdown-item btn_dropdown btnSmallEditRegis"><i class="fa-light fa-pen-to-square me-2 text-primary"></i>Editar</a>`
                    let html_anular = `<a class="dropdown-item btn_dropdown btnSmallAnular mb-1" style ="font-size:12px" title="Anular"><i class="fa-light fa-ban me-2 text-danger"></i>Anular</a>`
                    let html_convert_b = ` <a class="dropdown-item btn_dropdown btnSmallConvertirB mb-1" style ="font-size:12px" title="Convertir a Boleta"><i class="fa-light fa-receipt me-2 text-success"></i>Convertir a Boleta</a>`
                    let html_convert_f = ` <a class="dropdown-item btn_dropdown btnSmallConvertirF mb-1" style ="font-size:12px" title="Convertir a Factura"><i class="fa-light fa-file-invoice-dollar me-2 text-warning"></i>Convertir a Factura</a>`
                    let html_ver_links = ` <a data-bs-toggle="modal" data-bs-target="#modal_links" data-bs-whatever="LINKS MAPS PARTIDA-LLEGADA" class="dropdown-item btn_dropdown btnSmallVerLinks"><i class="fa-light fa-map-location-dot me-2 text-info"></i>Ver links</a>`
                    let html_comprobante = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.NOTA_VENTA + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-print me-2 text-dark"></i>Nota de venta</a>`;
                    let html_comprobante_a4 = `<a href="${instance._URL_ + 'encomienda/impresion/nota_ventaA4/' + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-file-lines me-2 text-secondary"></i>Nota de Venta A4</a>`
                    let html_transportista = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.TRANSPORTISTA + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-truck-fast me-2 text-success"></i>Transportista</a>`;
                    let html_archivo = ` <a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.ARCHIVO + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-box-archive me-2 text-secondary"></i>Archivo</a>`;
                    let html_rotulado = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.ROTULADO + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-barcode me-2"></i>Rotulado</a>`;
                    let btn_destino_erroneo = `<a class="dropdown-item btn_dropdown btnDestinoErroneo mb-1 text-danger" style="font-size:12px;" title="Destino Erróneo"><i class="fa-light fa-triangle-exclamation me-2"></i>Marcar como destino erróneo</a>`;
                    let btn_timeline = `<a class="dropdown-item btn_dropdown btnTimelineEncomienda mb-1"><i class="fa-light fa-route me-2 text-primary"></i>Seguimiento de la encomienda</a>`;

                    //----------------------------------NUEVO-------------------------
                    return `
         <div class="dropdown">
            <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </a>
            <ul class="dropdown-menu">
                ${row.encomienda_estado_envio == 'EN ORIGEN' ? html_editar : ''}
                ${row.encomienda_estado_envio == 'EN ORIGEN' && row.id_pago == null && (row.tp_docu_cliente == 1 || row.tp_docu_cliente == 6) ? html_convert_b : ''}
                ${row.encomienda_estado_envio == 'EN ORIGEN' && row.id_pago == null && row.tp_docu_cliente == 6 ? html_convert_f : ''}
                ${html_comprobante}
                ${html_comprobante_a4}
                ${permiso_rotulado.value == 1 ? html_rotulado : ''}
                ${html_transportista}
                ${html_archivo}
                ${row.estado != "ANULADO" && !["EN TRANSITO", "EN DESTINO", "CANCELADO"].includes(row.encomienda_estado_envio) && instance.CONSTS.PERMISOS.P_ANULAR_NOTAVENTA == 1 ? html_anular : ''}
                ${row.link_partida || row.link_llegada ? html_ver_links : ''}
                ${btn_timeline}
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

    const table_guiasTransportista = $('#table_guiasTransportista').DataTable({
        processing: true,
        serverSide: true,
        rowId: "id_venta",

        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTable_grt',
            'method': 'POST',
            'data': function (d) {
                d.tp_comprobante = [31];
                d.fecha_inicio = $('#fecha_inicio_guiast').val();
                d.fecha_fin = $('#fecha_fin_guiast').val();
                d.origen = $('#origen_guiast').val();
                d.destino = $('#destino_guiast').val();
                d.estado_envio = $('#estado_envio_guiast').val();
                d.estado_venta = $('#estado_venta_guiast').val();
                return d;
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
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
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
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "remitente_apellidos",
                render: (data, type, row) => {
                    return `${row.remitente_nombres} ${row.remitente_apellidos}<br>${row.remitente_num_docu}`
                }
            },
            {
                "data": "destinatario_apellidos",
                render: (data, type, row) => {
                    return `${row.destinatario_nombres} ${row.destinatario_apellidos}<br>${row.destinatario_num_docu}`
                }
            },
            {
                "data": "origen",
                render: (data, type, row) => {
                    return `${row.origen || '---'}`
                }
            },
            {
                "data": "destino",
                render: (data, type, row) => {
                    return `${row.destino}`
                }
            },
            {
                "data": "encomienda_fecha_salida",
                render: (data, type, row) => {
                    return `${row.encomienda_fecha_salida.split("-").reverse().join("-")}`
                }
            },
            {
                "data": "encomienda_estado_envio",
                render: (data, type, row) => {
                    let estado = row.salida == 1 && row.encomienda_estado_envio != 'ENTREGADO' ? `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA['EN RUTA']}">EN RUTA</span>` : `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[row.encomienda_estado_envio]}">${row.encomienda_estado_envio}</span>`;
                    return `${estado}`;
                }
            },
            {
                "data": "total",
                render: (data, type, row) => {
                    return `S/ ${row.total}`
                }
            },
            {
                "data": "estado",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[row.estado]}">${row.estado}</span>`
                }
            },
            {
                "data": "tracking",
                render: (data, type, row) => {
                    return row.tracking ?
                        `<span class="badge" style="background-color: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8;">${row.tracking}</span>` :
                        `<span class="text-muted">---</span>`;
                }
            },
            {
                "data": "pago_e",
                render: (data, type, row) => {
                    if (row.pago_e) {
                        let ruta_impresion = row.estado == "PAGO EN BLOQUE" ? instance.CONSTS.URL.IMPRESION_NOTA_VENTA.COMPROBANTE : instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE;
                        return `<span class="badge" style="background-color:white; color:black; font-weight: 700">Pagado con: <a href="${instance._URL_ + ruta_impresion + row.id_pago}" target="_blank" style="color:blue;">${row.pago_e}</a></span>`
                    } else {
                        return `<span class="badge" style="background-color:white; color:black; font-weight: 700">-</span>`
                    }
                }
            },
            // Busca esta sección para table_notaVenta (líneas ~300-350 aprox)
            {
                "data": "id_venta",
                render: (data, type, row) => {
                    let html_editar = ` <a data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR ENCOMIENDA" data-mode="edit" class="dropdown-item btn_dropdown btnSmallEditRegis">Editar</a>`
                    let html_anular = `<a class="dropdown-item btn_dropdown btnSmallAnular mb-1" style ="font-size:12px" title="Anular">Anular</a>`
                    let html_convert_b = ` <a class="dropdown-item btn_dropdown btnSmallConvertirB mb-1" style ="font-size:12px" title="Convertir a Boleta">Convertir a Boleta</a>`
                    let html_convert_f = ` <a class="dropdown-item btn_dropdown btnSmallConvertirF mb-1" style ="font-size:12px" title="Convertir a Factura">Convertir a Factura</a>`
                    let html_ver_links = ` <a data-bs-toggle="modal" data-bs-target="#modal_links" data-bs-whatever="LINKS MAPS PARTIDA-LLEGADA" class="dropdown-item btn_dropdown btnSmallVerLinks">Ver links</a>`

                    // NUEVO: Botón Comprobante A4 para Nota de Venta
                    let html_comprobante_a4 = `<a href="${instance._URL_ + 'encomienda/impresion/guia_remisionA4/' + row.id_guia_remision}" target="_blank" class="dropdown-item btn_dropdown">Guia A4</a>`
                    let html_xml
                    let html_cdr
                    if (row.codigo_sunat_guia == 1) {
                        html_xml = `<a class="dropdown-item btn_dropdown mb-1" style ="font-size:12px" href="${row.guia_xml}" target="_blank" title="XML">XML</a>`
                        html_cdr = `<a class="dropdown-item btn_dropdown mb-1" style ="font-size:12px" href="${row.guia_cdr}" target="_blank" title="CDR">CDR</a>`
                    } else {
                        html_xml = ''
                        html_cdr = ''
                    }
                    //----------------------------------NUEVO-------------------------
                    return `
         <div class="dropdown">
            <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </a>
            <ul class="dropdown-menu">
                <a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.GUIA_REMISION + row.id_guia_remision}" target="_blank" class="dropdown-item btn_dropdown">Guia de remisión transportista</a>
                ${html_comprobante_a4} 
                <a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.TRANSPORTISTA + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown">Transportista</a>
                <a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.ARCHIVO + row.id_venta}" target="_blank" class="dropdown-item btn_dropdown">Archivo</a>
                ${row.estado != "ANULADO" && !["EN TRANSITO", "EN DESTINO", "CANCELADO"].includes(row.encomienda_estado_envio) && instance.CONSTS.PERMISOS.P_ANULAR_COMPROBANTE == 1 ? html_anular : ''}
                ${row.link_partida || row.link_llegada ? html_ver_links : ''}
                ${html_xml}
                ${html_cdr}
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

    const table_guias = $('#table_guias').DataTable({
        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTableGuias',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = $('#fecha_inicio_guiasrt').val();
                d.fecha_fin = $('#fecha_fin_guiasrt').val();
                d.partida = $('#partida_guiasrt').val();
                d.destino = $('#destino_guiasrt').val();
                d.vehiculo_placa  = $('#vehiculo_guiasrt').val();
                return d;
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        processing: true,
        serverSide: true,
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
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie ? `${row.serie}-${row.correlativo}` : '---'
                }
            },
            {
                "data": "fecha_emision",
                render: (data, type, row) => {
                    return `${row.fecha_emision}`
                }
            },
            {
                "data": "conductor_nombres",
                render: (data, type, row) => {
                    return `${row.conductor_nombres} ${row.conductor_apellidos}`
                }
            },
            {
                "data": "conductor_licencia", 
                render: function (data, type, row) {
                    return row.conductor_licencia ? row.conductor_licencia : '-';
                }
            },
            {
                "data": "vehiculo_placa",
            },
            {
                "data": "partida_direccion",
            },
            {
                "data": "destino_direccion",
            },
            {
                "data": "codigo_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT_GUIAS[row.codigo_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT_GUIAS[row.codigo_sunat]}</span>`
                }
            },
            {
                "data": "mensaje_sunat", 
                render: (data, type, row) => { 
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT_MENSAJE[row.mensaje_sunat] || instance.CONSTS.COLORES.OBJETO_DEFAULT}">${row.mensaje_sunat || 'SIN MENSAJE'}</span>` 
                }
            },
            {
                "data": "observacion",
                render: (data, type, row) => {
                    return row.observacion ? row.observacion : '-----'
                }
            },
            {
                "data": "id",
                render: (data, type, row) => {
                    let html_xml
                    let html_cdr
                    if (row.codigo_sunat == 1) {
                        html_xml = `<a class="dropdown-item btn_dropdown btnXML" href="${row.xml}" target="_blank" title="XML">XML</a>`
                        html_cdr = `<a class="dropdown-item btn_dropdown btnCDR" href="${row.cdr}" target="_blank" title="CDR">CDR</a>`
                    } else {
                        html_xml = ''
                        html_cdr = ''
                    }
                    let btnTicket = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.GUIA_REMISION + row.id}" target="_blank" class="dropdown-item btn_dropdown mb-1" title="Ticket">Ticket</a>`
                    let btnA4 = `<a href="${instance._URL_ + 'encomienda/impresion/guia_remisionA4/' + row.id}" target="_blank" class="dropdown-item btn_dropdown mb-1" title="Formato A4">Formato A4</a>`
                    let btnAnular = row.observacion ? '' : `<a class="dropdown-item btn_dropdown mb-1 btnAnularGRT" title="Anular">Anular Guia</a>`

                    let newRow = `         
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                          <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu">
                        ${btnTicket}
                        ${btnA4}
                        ${html_xml}
                        ${html_cdr}
                        ${btnAnular}
                        </ul>
                    </div>
                `
                    return newRow
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    const table_historialE = $('#table_historialE').DataTable({
        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTableEmbarcaciones',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = $('#fecha_inicio_embarcaciones').val();
                d.fecha_fin = $('#fecha_fin_embarcaciones').val();
                d.origen = $('#origen_embarcaciones').val();
                d.destino = $('#destino_embarcaciones').val();
                d.id_vehiculo = $('#vehiculo_embarcaciones').val();
                return d;
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "id",
                render: function (data, type, row) {
                    return `<span class="bold">${row.id}</span>`
                }
            },
            {
                "data": "origen",
                render: function (data, type, row) {
                    if (!row.origen) {
                        return "-";
                    }
                    return row.origen;
                }
            },
            {
                "data": "destino",
            },
            {
                "data": "nombre_conductor",
                render: function (data, type, row) {
                    return row.nombre_conductor + ' ' + row.apellidos_conductor
                }
            },
            {
                "data": "licencia", 
                render: function (data, type, row) {
                    return row.licencia ? row.licencia : '-';
                }
            },
            {
                "data": "vehiculo_placa",
            },
            {
                "data": "fecha",
            },
            {
                "data": "hora",
            },
            {
                "data": "usuario",
            },
            {
                "data": "estado",
                "render": function (data) {

                    if (data === 'ACTIVO') {
                        return `
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> ACTIVO
                </span>
                 `;
                    }

                    if (data === 'ANULADO') {
                        return `
                <span class="badge bg-danger">
                    <i class="fas fa-times-circle me-1"></i> ANULADO
                </span>
                  `;
                    }

                    return `
            <span class="badge bg-secondary">
                <i class="fas fa-question-circle me-1"></i> ${data}
            </span>
              `;
                }
            },
            {
                "data": "id",
                render: (data, type, row) => {
                    let html_guia = `<a class="dropdown-item btn_dropdown btnGUIA" data-id_embarque="${row.id}" target="_blank" title="Guia Remision Transportista">Guia R. Transportista</a>`
                    let btn_desembarcar_encomiendas = `<a class="dropdown-item btn_dropdown btnEDITAR" data-id_embarque="${row.id}" title="Editar encomiendas">Editar encomiendas</a>`;
                    let linkembarques = row.tipo_programacion == 'salida' ? `<a class="dropdown-item btn_dropdown" href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.EMBARQUE_RUTA + row.id}" target="_blank" title="Comprobante">Impresión A4 </a>` : `<a class="dropdown-item btn_dropdown" href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.EMBARQUE + row.id}" target="_blank" title="Comprobante">Impresión A4 </a>`;
                    let newRow = `
                    <div class="dropdown">
                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu">

                        ${linkembarques}
                        ${html_guia}
                        ${btn_desembarcar_encomiendas}
                    </ul>
                    </div>
                        `;
                    return row.estado == 'ACTIVO' ? newRow : '';
                },
            },
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    const table_historialD = $('#table_historialD').DataTable({
        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTableDesembarques',
            'method': 'POST',
            'data': function (d) {
                d.fecha_inicio = $('#fecha_inicio_desembarques').val();
                d.fecha_fin = $('#fecha_fin_desembarques').val();
                d.origen = $('#origen_desembarques').val();
                d.destino = $('#destino_desembarques').val();
                d.id_vehiculo = $('#vehiculo_desembarques').val();
                return d;
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "id",
                render: function (data, type, row) {
                    return `<span class="bold">${row.id}</span>`
                }
            },
            {
                "data": "origen",
                render: function (data, type, row) {
                    if (!row.origen) {
                        return "-";
                    }
                    return row.origen;
                }
            },
            {
                "data": "destino",
                render: function (data, type, row) {
                    if (!row.destino) {
                        return "-";
                    }
                    return row.destino;
                }
            },
            {
                "data": "nombre_conductor",
                render: function (data, type, row) {
                    return row.nombre_conductor + ' ' + row.apellidos_conductor
                }
            },
            {
                "data": "licencia", 
                render: function (data, type, row) {
                    return row.licencia ? row.licencia : '-';
                }
            },
            {
                "data": "vehiculo_placa",
            },
            {
                "data": "fecha",
            },
            {
                "data": "hora",
            },
            {
                "data": "usuario",
            },
            {
                "data": "id",
                render: (data, type, row) => {

                    let newRow = `
                        <a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_ENCOMIENDA.DESEMBARQUE + row.id}" target="_blank" class="btn btnColorViolet mb-1" title="Comprobante"><i class="fa-light fa-print"></i></a>
                    `
                    return newRow
                },
            },
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    const table_grupales = $('#table_grupales').DataTable({
        ajax: {
            url: instance._URL_ + 'encomienda/get_dataTableEmbarcacionesGrupales',
            method: 'POST',
            data: function (d) {
                d.fecha_inicio = $('#fecha_inicio_embgrupal').val();
                d.fecha_fin = $('#fecha_fin_embgrupal').val();
                d.origen = $('#origen_embgrupal').val();
                d.destino = $('#destino_embgrupal').val();
                d.id_vehiculo = $('#vehiculo_embgrupal').val();
                return d;
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        ordering: false,
        columns: [
            {
                data: "id_grupo_embarcacion",
                render: function (data, type, row) {
                    // Usar el mismo campo que viene del servidor
                    return `<span class="bold">${row.id_grupo_embarcacion || row.id || 'N/A'}</span>`
                }
            },
            {
                data: "origen",
                render: function (data, type, row) {
                    return row.origen ? row.origen : "-";
                }
            },
            {
                data: "destino",
                render: function (data, type, row) {
                    return row.destino ? row.destino : "-";
                }
            },
            {
                data: "nombre_conductor",
                render: function (data, type, row) {
                    return `${row.nombre_conductor} ${row.apellidos_conductor}`;
                }
            },
            {
                "data": "licencia", 
                render: function (data, type, row) {
                    return row.licencia ? row.licencia : '-';
                }
            },
            {
                data: "vehiculo_placa"
            },
            {
                data: "fecha"
            },
            {
                data: "hora"
            },
            {
                data: "usuario"
            },
            {
                data: "id_grupo_embarcacion",
                render: function (data, type, row) {
                    return `
                    <a href="${instance._URL_}encomienda/impresion/embarque_grupal/${row.id_programacion}/${row.id_grupo_embarcacion}" 
                    target="_blank" 
                    class="btn btnColorViolet mb-1" 
                    title="Imprimir Embarque Grupal">
                    <i class="fa-light fa-print"></i>
                    </a>
                    `;
                }
            }
        ],
        language: {
            url: './public/plugins/datatable/language/es_es.json',
        },
    });

    // form venta
    let id_venta = document.querySelector("#id_venta")
    let caja = document.querySelector("#cj_chica")
    let form_venta = document.querySelector("#form_venta")
    let tp_comprobante = document.querySelector("#tp_comprobante")
    let serie_venta = document.querySelector("#serie_venta")
    let fecha_salida = document.querySelector("#fecha_salida")
    let estado_envio = document.querySelector("#estado_envio")
    let remitente = document.querySelector("#remitente")
    let remitente_id = document.querySelector("#remitente_id")
    let destinatario = document.querySelector("#destinatario")
    let destinatario_id = document.querySelector("#destinatario_id")
    let origen_terminal = document.querySelector("#origen_terminal")
    let destino_terminal = document.querySelector("#destino_terminal")
    let p_partida = document.querySelector("#p_partida")
    let ubigeoP = document.querySelector("#ubigeoP")
    let p_llegada = document.querySelector("#p_llegada")
    let ubigeoLle = document.querySelector("#ubigeoLle")
    let pagante = document.querySelector("#pagante")
    let pagante_id = document.querySelector("#pagante_id")
    let pagante_nombres = document.querySelector("#pagante_nombres")
    let pago = document.querySelector("#pago")
    let div_peso = document.querySelector("#div_peso")
    let pass = document.querySelector("#pass")
    let tp_comprobante_e = document.querySelector("#tp_comprobante_e")
    let serieEP = document.querySelector("#serieEP")
    let correlativoEP = document.querySelector("#correlativoEP")
    let unidad_m = document.querySelector("#unidad_m")
    let div_cuota = document.querySelector("#div_cuota")
    let div_detraccion = document.querySelector("#div_detraccion")
    let div_detraccionEntregar = document.querySelector("#div_detraccionEntregar")
    let monto_credito = document.querySelector("#monto_credito")
    let t_operacion = document.querySelector("#t_operacion")
    let dato_destinatario = document.querySelector("#dato_destinatario")
    let d_extra = document.querySelector("#d_extra")
    let receptor_id = document.querySelector("#receptor_id")
    let ctg_producto = document.querySelector("#ctg_producto")
    let precio_unitario = document.querySelector("#precio_unitario")
    let p_total = document.querySelector("#p_total")
    let peso = document.querySelector("#peso")
    let rotulado_producto = document.querySelector("#rotulado_producto")
    let cantidad = document.querySelector("#cantidad")
    let obs_producto = document.querySelector("#obs_producto")
    let tipo_precio = document.querySelector("#tipo_precio")
    let precio_kg = document.querySelector("#precio_kg")
    let linkP = document.querySelector("#linkP")
    let linkLle = document.querySelector("#linkLle")
    let medio_pago = document.querySelector("#medio_pago")
    let destino = document.querySelector("#destino")
    let referencia = document.querySelector("#referencia")
    let table_productos = document.querySelector("#table_productos")
    let add_producto = document.querySelector("#add_producto")
    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")
    let origen_detraccion = document.querySelector("#origen_detraccion")
    let destino_detraccion = document.querySelector("#destino_detraccion")
    let programacion_guia_t = document.querySelector("#programacion_guia_t")
    let div_parentpagante = document.querySelector("#div_parentpagante")
    let divParentProgra = document.querySelector("#divParentProgra")
    let automatico_p_bloque = document.getElementById("automatico_p_bloque");

    let origen_detraccionEntregar = document.querySelector("#origen_detraccionEntregar")
    let destino_detraccionEntregar = document.querySelector("#destino_detraccionEntregar")
    let tm_detraccionEntregar = document.querySelector("#tm_detraccionEntregar");
    let base_detraccionEntregar = document.querySelector("#base_detraccionEntregar");
    let monto_detraccionEntregar = document.querySelector("#monto_detraccionEntregar");
    let total_operacionEntregar = document.querySelector("#total_operacionEntregar");


    instance.Datatable.inputSearch(table_comprobante, ".inputSearchComprobante", ".btnSearch1", "manual")
    instance.Datatable.inputSearch(table_notaVenta, ".inputSearchNotaVenta", ".btnSearch2", "manual")
    instance.Datatable.inputSearch(table_guiasTransportista, ".inputSearchGuiasT", ".btnSearch6", "manual")
    instance.Datatable.inputSearch(table_guias, ".inputSearchGuiasRemision", ".btnSearch3", "manual")
    instance.Datatable.inputSearch(table_historialE, ".inputSearchHistorial", ".btnSearch4", "manual")
    instance.Datatable.inputSearch(table_historialD, ".inputSearchHistorialD", ".btnSearch5", "manual")
    instance.Datatable.inputSearch(table_grupales, ".inputSearchGrupales", ".btnSearch6", "manual")
    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante")
    // instance.select.createSelect("#serie_venta", "#div_parentSerieVenta", 'Seleccione')
    instance.select.createSelectSinSearch("#almacen", "#div_parentAlmacen")
    instance.select.createSelect("#config_vehiculo", "#ParentConfigVehiculo")
    instance.select.createSelect("#medio_pago_detraccion", "#parentMedioPagoDetraccion")
    instance.select.createSelect("#programacion_guia_t", "#divParentProgra")

    let medioSelect = $('#medio_pago').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        }
    });

    let medioSelectEntregar = $('#medio_pagoEntregar').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        }
    });

    // Medio pago detraccion entregar
    let medioDESelect = $('#medio_pago_detraccionEntregar').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        }
    });

    let destinoSelect = $('#destino_terminal').selectize({
        create: false,
        maxItems: 1,
        onDropdownOpen: function ($dropdown) {
            $dropdown.addClass('selectize-dropdown--single');
        },
        onChange: function (value) {
            if (!value) return;
            let selectize = this;
            let ubigeo_terminal = selectize.options[value].ubigeo;
            let direccion = selectize.options[value].direccion;
            if (tp_comprobante.value == "1" || tp_comprobante.value == "3") {
                setUbigeoValue(ubigeoDestinoD, ubigeo_terminal);
                if (direccion) {
                    destino_detraccion.value = direccion;
                }
            } else if (tp_comprobante.value == '31') {
                buscar_programaciones(value)
            }
        }
    });
    window.selectizeMedioPago = medioSelect[0].selectize;
    window.selectizeMedioPagoDetraccionEntregar = medioDESelect[0].selectize;
    window.selectizeMedioPagoEntregar = medioSelectEntregar[0].selectize;
    window.selectizeDestinoTerminal = destinoSelect[0].selectize;
    instance.Validate.allowInputMoney(["#precio_unitario"]);
    instance.Validate.allowInputNum(["#cantidad"])


    instance.tippy.init(".info_programacion", {
        "content": "Fecha Hora - Placa Vehículo - Tipo Servicio",
        "animation": "scale",
        "placement": "top",
    })

    //Verificacion caja 
    if (caja.value == 0) {
        Swal.fire({
            title: "Ooops..... No tiene una caja abierta",
            text: "Desea abrir una caja?",
            icon: "error",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: "Abrir caja",
            denyButtonText: `No quiero`,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = instance._URL_ + 'caja_chica';
            } else if (result.isDenied) {
                window.location.href = instance._URL_ + 'dashboard';
            }
        });
    }

    function buscar_programaciones(id_destino) {
        let formData = new FormData();
        formData.append("id_terminalDestino", id_destino)
        fetch(instance._URL_ + 'encomienda/get_programacionEmbarcar', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    programacion_guia_t.innerHTML = "";

                    let progras = data.message
                    if (progras.length > 0) {
                        progras.forEach((programacion) => {
                            const rutasJSON = JSON.stringify(programacion.rutas || []);
                            const destinosTodos = JSON.stringify([
                                programacion.id_terminal_destino,
                                ...(programacion.rutas ? programacion.rutas.map(r => r.id_terminal) : [])
                            ]);

                            const option = document.createElement("option");
                            option.value = programacion.id_programacion;
                            option.textContent = `${programacion.placa} - ${programacion.fecha_salida.split("-").reverse().join("-")} - ${programacion.hora_salida}`;

                            option.dataset.final = programacion.id_terminal_destino;
                            option.dataset.rutas = JSON.stringify(programacion.rutas || []);
                            option.dataset.destinos = destinosTodos;

                            // Atributos adicionales
                            option.setAttribute("id-conductor", programacion.id_conductor || "");
                            option.setAttribute("id-vehiculo", programacion.id_vehiculo || "");

                            programacion_guia_t.appendChild(option);
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Ooops.......!!',
                        text: 'No se encontrado ninguna programacion para el destino seleccionado, realice una programacion',
                    });
                    return;
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Ooops.......!!',
                    text: 'Ha ocurrido un error, contacte con soporte',
                });
                return;
            });
    }

    const get_encomienda = async () => {
        let response = await fetch(instance._URL_ + 'encomienda/get_ctgEncomienda')
        if (response.ok) {
            let data = await response.json()
            if (data.success) {
                Object.values(data.message).forEach((e) => {
                    ctg_producto.insertAdjacentHTML('beforeend', `
                        <option value="${e.id_ctg_encomienda}" precio="${e.precio}" afectacion="${e.afectacion}">${e.descripcion}</option>
                    `)
                })
            }
        }
    }

    // Reset formulario al Cerrar modal
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_venta.value = ""
        id_pasajero.value = ""
        tp_comprobante.disabled = false;
        serie_venta.disabled = false;
        $("#remitente").val("").trigger("change")
        $("#destinatario").val("").trigger("change")
        $("#pagante").val("").trigger("change")
        selectizeDestinoTerminal.clear(true);
        $("#medio_pago").val("").trigger("change")
        selectizeMedioPago.enable();
        selectizeMedioPago.setValue('');
        $('#table_productos tbody tr').remove();
        table_productos.getElementsByTagName("tbody")[0].insertAdjacentHTML('beforeend', `
            <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                <td class="fs-6">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td id="peso_total" class="fs-6">0.00</td>
                <td id="costo_total" class="fs-6">S/ 0.00</td>
            </tr>
        `)
        $("#datos_entrega").addClass("d-none");
        ubigeoPartida.clear(true);
        ubigeoLlegada.clear(true);
        ubigeoOrigenD.clear(true);
        ubigeoDestinoD.clear(true);
        ruta_destino.clear(true);
        ruta_origen.setValue('0', true);
        div_cuota.classList.add("d-none");
        div_detraccion.classList.add("d-none");
        dato_destinatario.classList.add('d-none');
        d_extra.value = 0;
        t_operacion.classList.remove("desabilitar_select");
        $("#tp_comprobante_e").val("").trigger("change")
        $('#programacion_guia_t').find('option:not([value=""])').remove().val('').trigger('change');
        $("#tiempo_credito").trigger('change');
        limpiar_campos_detraccion();
        $("#tipo_precio").trigger('change').val('unidad');
        if (observer) {
            observer.disconnect();
        }
        if ($('#pago option[value="PAGADO"]').length === 0) {
            $('#pago').prepend('<option value="PAGADO">PAGADO</option>');
        }
        $("#pago").val('PAGADO');
        $('#salida')
            .empty()
            .trigger('change');
        $("#divParentSalida").addClass("d-none");
        window.destinoSalida.clear(true);
        selectizeDestinoTerminal.enable();
        const primerValor = $('#almacen option:not([value=""]):first').val();
        $('#almacen')
            .val(primerValor)
            .prop('disabled', false)
            .trigger('change');
    })

    function limpiar_campos_detraccion(resetearMetodo = true) {

        // Prevenir cálculos durante la limpieza
        isCalculatingDetraccion = true;

        // Limpiar campos comunes
        $("#config_vehiculo").val("1").trigger("change");
        $("#ubigeo_destino").val("").trigger("change");
        $("#destino_detraccion").val("");
        $("#medio_pago_detraccion").val("1").trigger("change");
        $("#detalle_detraccion").val("");
        $("#total_operacion").val("");
        $("#tm_detraccion").val("");

        // Limpiar valores de referencia y base
        $("#v_ref_carga_efectiva").val("");
        $("#v_ref_carga_util").val("");
        $("#v_ref_servicio").val("");
        $("#base_detraccion").val("");
        $("#monto_detraccion").val("");

        // Limpiar selects personalizados
        if (typeof ubigeoOrigenD !== 'undefined') ubigeoOrigenD.clear(true);
        if (typeof ubigeoDestinoD !== 'undefined') ubigeoDestinoD.clear(true);
        if (typeof ruta_destino !== 'undefined') ruta_destino.clear(true);
        if (typeof ruta_origen !== 'undefined') ruta_origen.setValue('0', true);

        // Limpiar datos de rutas
        rutaDestinoData = null;
        rutaOrigenData = null;

        div_detraccion.classList.add("d-none");

        // Resetear método solo si se solicita
        if (resetearMetodo) {
            $("input[name='metodo_detraccion'][value='sunat']").prop("checked", true);
            $("#div_calculos_detraccion").removeClass("d-none");
        }

        isCalculatingDetraccion = false;
    }

    function limpiar_campos_detraccionEntregar(resetearMetodoEntregar = true) {

        // Prevenir cálculos durante la limpieza
        isCalculatingDetraccionEntregar = true;

        // Limpiar campos comunes
        $("#config_vehiculoEntregar").val("1").trigger("change");
        $("#ubigeo_destinoEntregar").val("").trigger("change");
        $("#destino_detraccionEntregar").val("");
        $("#medio_pago_detraccionEntregar").val("1").trigger("change");
        $("#detalle_detraccionEntregar").val("");
        $("#total_operacionEntregar").val("");
        $("#tm_detraccionEntregar").val("");

        // Limpiar valores de referencia y base
        $("#v_ref_carga_efectivaEntregar").val("");
        $("#v_ref_carga_utilEntregar").val("");
        $("#v_ref_servicioEntregar").val("");
        $("#base_detraccionEntregar").val("");
        $("#monto_detraccionEntregar").val("");

        // Limpiar selects personalizados
        if (typeof ubigeoOrigenDE !== 'undefined') ubigeoOrigenDE.clear(true);
        if (typeof ubigeoDestinoDE !== 'undefined') ubigeoDestinoDE.clear(true);
        if (typeof ruta_destinoE !== 'undefined') ruta_destinoE.clear(true);
        if (typeof ruta_origenE !== 'undefined') ruta_origenE.setValue('0', true);

        // Limpiar datos de rutas
        rutaDestinoDataE = null;
        rutaOrigenDataE = null;

        div_detraccionEntregar.classList.add("d-none");

        // Resetear método solo si se solicita
        if (resetearMetodoEntregar) {
            $("input[name='metodo_detraccionEntregar'][value='sunat']").prop("checked", true);
            $("#div_calculos_detraccionEntregar").removeClass("d-none");
        }

        isCalculatingDetraccionEntregar = false;
    }


    $("#modal").on("show.bs.modal", (e) => {
        let mode = $("#modal").data("mode") || $(e.relatedTarget).data("mode");

        $("#tipo_precio").trigger("change");
        // Validación permisos guías
        let url = instance._URL_ + 'encomienda/permisos_modal';
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    div_peso.classList.remove("d-none")

                    if (data.message.c_selva == 1) {
                        $("#t_operacion").val("EXO").trigger("change");
                        t_operacion.classList.add("desabilitar_select");
                        $("#t_operacion").attr("tabindex", "-1");
                    } else {
                        $("#t_operacion").val("GRA").trigger("change");
                    }
                }
            });

        $("#tp_comprobante").val($("#tp_comprobante").val()).trigger("change");
        $("#ctg_producto").val($("#ctg_producto").val()).trigger("change");

        // Solo si es nuevo le ponemos el valor de sesión
        if (mode === "new") {
            setUbigeoValue(ubigeoPartida, instance._UB_TERMINAL_SESION);
            setUbigeoValue(ubigeoOrigenD, instance._UB_TERMINAL_SESION);
            origen_detraccion.value = instance._DIR_TERMINAL_SESION;
            iniciarObserver();
        } else if (mode === "edit" && !e_domicilio.checked) {
            setUbigeoValue(ubigeoPartida, instance._UB_TERMINAL_SESION);
        }
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
                fetch(instance._URL_ + "encomienda/buscar_nacionalidad?q=" + encodeURIComponent(query))
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

    form_venta.addEventListener("submit", (event) => {
        event.preventDefault()
        let formData = new FormData(form_venta)
        if ((tp_comprobante.value == 1 || tp_comprobante.value == 3) && pago.value == "PAGO EN DESTINO") {
            instance.Toast.operacion_erronea("No puede generar un comprobante electrónico con pago en destino.")
            return
        }

        let val_data = [
            formData.get("fecha_salida"),
            formData.get("remitente"), formData.get("destinatario"), formData.get("pago"), formData.get("pass"),
        ]

        if (id_venta.value == "") {
            val_data.push(formData.get("tp_comprobante"), formData.get("serie_venta"))
        }

        if (e_domicilio.checked) {
            val_data.push(formData.get("p_partida"), formData.get("ubigeoP")),
                val_data.push(formData.get("p_llegada"), formData.get("ubigeoLle")),
                val_data.push(formData.get("pagante"))
        }

        if (e_salida.checked) {
            val_data.push(formData.get("salida"));
        }

        if (pago.value == "PAGADO" && forma_pago.value != 2) {
            val_data.push(formData.get("medio_pago"), formData.get("destino"))
        }

        if (document.querySelectorAll(".ctg_producto").length == 0) {
            instance.Toast.operacion_erronea("Ingrese productos")
            return
        }

        if (destinatario.value === '00000000') {
            val_data.push(formData.get("obs_destinatario"))
        }

        let tp_calculo_element = document.querySelector("input[name='metodo_detraccion']:checked");
        let tp_calculo_value = tp_calculo_element ? tp_calculo_element.value : null;
        if (tp_operacion_venta.value == 2) {
            if (tp_calculo_value === 'sunat') {
                val_data.push(
                    formData.get("ubigeo_origen_d"),
                    formData.get("origen_detraccion"),
                    formData.get("ubigeo_destino_d"),
                    formData.get("destino_detraccion"),
                    formData.get("medio_pago_detraccion"),
                    formData.get("detalle_detraccion"),
                    formData.get("total_operacion"),
                    formData.get("tm_detraccion"),
                    formData.get("config_vehiculo"),
                    formData.get("monto_detraccion"),
                    formData.get("ruta_origen"),
                    formData.get("ruta_destino"),
                    formData.get("v_ref_carga_efectiva"),
                    formData.get("v_ref_carga_util"),
                    formData.get("v_ref_servicio")
                )
            } else if (tp_calculo_value === 'directo') {
                val_data.push(
                    formData.get("ubigeo_origen_d"),
                    formData.get("origen_detraccion"),
                    formData.get("ubigeo_destino_d"),
                    formData.get("destino_detraccion"),
                    formData.get("medio_pago_detraccion"),
                    formData.get("detalle_detraccion"),
                    formData.get("monto_detraccion"),
                    formData.get("v_ref_carga_efectiva"),
                    formData.get("v_ref_carga_util"),
                    formData.get("v_ref_servicio")
                )
            }

        }

        if (tp_comprobante.value == 31 && !e_salida.checked) {
            if (!programacion_guia_t.value) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡¡Atencion!!',
                    text: 'Debe seleccionar una programacion para poder realizar una guia de remision transportista',
                });
                return;
            }
            if (!pagante_id.value) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡¡Atencion!!',
                    text: 'Debe ingresar al pagante ',
                });
                return;
            }
        }

        if (tp_operacion_venta.value == 2) {
            if (v_ref_carga_efectiva.value <= 0 || v_ref_carga_util.value <= 0 || v_ref_servicio.value <= 0 || monto_detraccion.value <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡¡Atencion!!',
                    text: 'Los valores referenciales deben ser mayor a 0.00',
                });
                return;
            }

            if (v_ref_carga_efectiva.value < 1) {
                Swal.fire({
                    icon: 'info',
                    title: '¡¡Atencion!!',
                    text: 'El valor referencial carga efectiva no puede ser menor a 1 revise su TM detraccion',
                });
                return;
            }
        }

        const serieG = serieGR.value.trim();
        const correlativoG = correlativoGR.value.trim();
        const rucG = rucGR.value.trim();

        if (serieG !== '' || correlativoG !== '' || rucG !== '') {
            if (serieG === '' || correlativoG === '' || rucG === '') {
                instance.Toast.operacion_erronea("Debe completar serie, correlativo y ruc de guía de remisión");
                return;
            }

            if (serieG.length < 4) {
                instance.Toast.operacion_erronea("La serie de la guía de remisión debe tener 4 dígitos");
                return;
            }
            if (rucG.length > 11 || rucG.length < 11) {
                instance.Toast.operacion_erronea("El RUC de la guía de remisión debe tener 11 dígitos");
                return;
            }
        }

        if (tp_comprobante_e.value) {
            val_data.push(formData.get("serieEP"), formData.get("correlativoEP"), formData.get("rucEP"))
            if (rucEP.value.length !== 11) {
                instance.Toast.operacion_erronea("El RUC del comprobante relacionado debe tener 11 dígitos");
                return;
            }
        } else {
            serieEP.value = "";
            correlativoEP.value = "";
            rucEP.value = "";
        }

        let ctg_producto = document.querySelectorAll(".ctg_producto");
        let cantidad_producto = document.querySelectorAll(".cantidad_producto");
        let precio_producto = document.querySelectorAll(".precio_producto");
        let precio_Kg = document.querySelectorAll(".precio_x_kg");
        let obs_producto = document.querySelectorAll(".obs_producto");
        let subtotal_producto = document.querySelectorAll(".subtotal_producto");
        let unidad_m = document.querySelectorAll(".unidad_m");
        let peso_producto = document.querySelectorAll(".peso");

        let productos = [];

        Object.values(ctg_producto).forEach((el, i) => {
            const row = el.closest("tr");

            // Get visible elements
            const cantidadEl = cantidad_producto[i] || null;
            const precioEl = precio_producto[i] || null;
            const unidadEl = unidad_m[i] || null;
            const KgprecioEl = precio_Kg[i] || 0.00;
            const pesoEl = peso_producto[i] || null;
            const subtotalEl = subtotal_producto[i] || null;

            // Get hidden inputs directly
            const idProductoInput = row.querySelector('input[name="id_producto[]"]');
            const obsProductoInput = row.querySelector('input[name="obs_producto[]"]');  // Get observation as input
            const serieGuiaInput = row.querySelector('input[name="serie_guia[]"]');
            const corrGuiaInput = row.querySelector('input[name="corr_guia[]"]');
            const rucGuiaInput = row.querySelector('input[name="ruc_guia[]"]');
            const unidadInput = row.querySelector('input[name="unid_medida[]"]');

            productos.push([
                el.getAttribute("id-ctg-producto") || '',
                el.textContent.trim(),
                cantidadEl ? cantidadEl.textContent.trim() : '',
                precioEl ? precioEl.textContent.trim() : '',
                KgprecioEl ? KgprecioEl.textContent.trim() : '',
                obsProductoInput ? obsProductoInput.value : '',  // Use value instead of textContent
                unidadEl ? unidadEl.textContent.trim() : '',
                pesoEl ? pesoEl.textContent.trim() : '',
                subtotalEl ? subtotalEl.textContent.trim() : '',
                serieGuiaInput ? serieGuiaInput.value : '',
                corrGuiaInput ? corrGuiaInput.value : '',
                rucGuiaInput ? rucGuiaInput.value : '',
                unidadInput ? unidadInput.value : ''
            ]);
        });
        if (instance.Validate.validateData(val_data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            formData.set("productos", JSON.stringify(productos))
            formData.set("e_domicilio", e_domicilio.checked ? 1 : 0)
            formData.set("e_salida", e_salida.checked ? 1 : 0)
            formData.set("precio_venta", table_productos.getElementsByTagName("tbody")[0].querySelector("#costo_total").textContent)
            formData.set("peso_venta", table_productos.getElementsByTagName("tbody")[0].querySelector("#peso_total").textContent)
            fetch(instance._URL_ + 'encomienda/crud_encomienda', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status)
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        if (e_domicilio.checked) {
                            if (tp_comprobante.value !== "31" && !e_salida.checked) {
                                $('#modal_embarcar').modal('show')
                            }
                        }
                        instance.Toast.operacion_exitosa(data.message.message)
                        $('#modal').modal('toggle')
                        switch (data.message.tp_comprobante) {
                            case '1':
                            case '3':
                                instance.Datatable.reloadTable(table_comprobante)
                                break;
                            case '2':
                                instance.Datatable.reloadTable(table_notaVenta)
                                break;
                            case '31':
                                instance.Datatable.reloadTable(table_guiasTransportista)
                                break;
                        }

                        if (data.message.salida == 1 || data.message.tp_comprobante == '31') {
                            instance.Datatable.reloadTable(table_historialE)
                            instance.Datatable.reloadTable(table_grupales)
                        }
                        modal_printComprobante('¡Venta completada!', data.message.links, data.message.id_venta, data.message.tp_comprobante)
                    } else {
                        instance.Toast.operacion_erronea(data.message.message)
                    }
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
                .catch(error => {
                    instance.Toast.operacion_erronea(error.message)
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea("Rellene correctamente los campos")
        }
    })

    const mostrar_clientes = (targets = [], valor) => {
        var consul = ""
        if (valor !== 0) {
            consul = "?tp_doc=6";
        }
        fetch(instance._URL_ + "pasaje/get_clientes" + consul, { method: "GET" })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    targets.forEach(elemnt => {
                        const selectElement = document.querySelector(elemnt);
                        selectElement.innerHTML = ""
                        selectElement.insertAdjacentHTML('beforeend', '<option value="">Seleccione</option>')
                        Object.values(data.message).forEach((e, i, array) => [
                            document.querySelector(elemnt).insertAdjacentHTML('beforeend', `
                                <option value="${e.id_usuario}">${e.nombres} ${e.apellidos} - ${e.num_docu}</option>
                            `)
                        ])
                    });
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    }

    // ══════════════════════════════════════════════════════
    //  WebSocket — Encomienda (comprobante + notaVenta)
    //  Con debug completo en cada paso crítico
    // ══════════════════════════════════════════════════════

    const WS_URL = "wss://tid.net.pe/ws";
    const EMPRESA_ID = instance._UUID_WS_SESION;
    const MODULO = "encomienda";

    // ── Mapa de tablas ──────────────────────────────────
    const DT = {
        comprobante: table_comprobante,
        notaVenta: table_notaVenta,
        guiasTransportista: table_guiasTransportista,
    };

    // ── Badges ──────────────────────────────────────────
    const badgesNuevos = Object.fromEntries(
        Object.keys(DT).map(t => [t, 0])
    );

    function mostrarBadgeNuevos(tabla) {
        badgesNuevos[tabla]++;
        const badge = document.querySelector(`[data-badge-tabla="${tabla}"]`);
        if (!badge) {
            console.warn(`⚠️ [BADGE] No se encontró el elemento [data-badge-tabla="${tabla}"] en el HTML`);
            return;
        }
        badge.textContent = `+${badgesNuevos[tabla]} nuevo(s)`;
        badge.style.display = "inline";
    }

    function limpiarBadge(tabla) {
        badgesNuevos[tabla] = 0;
        const badge = document.querySelector(`[data-badge-tabla="${tabla}"]`);
        if (badge) badge.style.display = "none";
    }

    table_comprobante.on('draw', () => limpiarBadge('comprobante'));
    table_notaVenta.on('draw', () => limpiarBadge('notaVenta'));
    table_guiasTransportista.on('draw', () => limpiarBadge('guiasTransportista'));
    // ── Fetch + actualizar fila ──────────────────────────

    async function fetchActualizarComprobante(id_venta) {
        try {
            const formData = new FormData();
            formData.append("id_venta", id_venta);
            formData.append("tp_comprobante[]", 1);
            formData.append("tp_comprobante[]", 3);

            const res = await fetch(
                instance._URL_ + `encomienda/get_registro_comprobante`,
                { method: 'POST', body: formData }
            );

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const response = await res.json();

            if (!response.success) {
                console.warn(`⚠️ [FETCH comprobante] Backend devolvió error: ${response.message}`);
                return;
            }

            actualizarFilaDT('comprobante', id_venta, response.data);

        } catch (err) {
            console.error(`❌ [FETCH comprobante] Error para id_venta=${id_venta}:`, err);
        }
    }

    async function fetchActualizarNotaVenta(id_venta) {
        try {
            const formData = new FormData();
            formData.append("id_venta", id_venta);
            formData.append("tp_comprobante[]", 2);   // nota de venta = tipo 2

            const res = await fetch(
                instance._URL_ + `encomienda/get_registro_comprobante`,
                { method: 'POST', body: formData }
            );

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const response = await res.json();

            if (!response.success) {
                console.warn(`⚠️ [FETCH notaVenta] Backend devolvió error: ${response.message}`);
                return;
            }

            actualizarFilaDT('notaVenta', id_venta, response.data);

        } catch (err) {
            console.error(`❌ [FETCH notaVenta] Error para id_venta=${id_venta}:`, err);
        }
    }

    async function fetchActualizarGuiaTransportista(id_venta) {
        try {
            const formData = new FormData();
            formData.append("id_venta", id_venta);
            formData.append("tp_comprobante[]", 31);   // guía de transporte = tipo 31

            const res = await fetch(
                instance._URL_ + `encomienda/get_registro_comprobante`,
                { method: 'POST', body: formData }
            );

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const response = await res.json();

            if (!response.success) {
                console.warn(`⚠️ [FETCH guiasTransportista] Backend devolvió error: ${response.message}`);
                return;
            }

            actualizarFilaDT('guiasTransportista', id_venta, response.data);

        } catch (err) {
            console.error(`❌ [FETCH guiasTransportista] Error para id_venta=${id_venta}:`, err);
        }
    }

    // ── Helpers DataTables ───────────────────────────────

    function actualizarFilaDT(tabla, id, nuevosDatos) {
        const dt = DT[tabla];
        if (!dt) {
            console.warn(`⚠️ [DT] Tabla desconocida: "${tabla}"`);
            return;
        }

        const row = dt.row(`#${id}`);

        if (!row.length) {
            console.warn(`⚠️ [DT "${tabla}"] La fila #${id} no existe en esta página. ¿rowId configurado?`);
            return;
        }

        // Verifica que el nodo esté en el DOM (página actual)
        const nodo = row.node();
        const visible = nodo && document.body.contains(nodo);

        if (!visible) {
            return;
        }

        row.data(nuevosDatos).invalidate();

        table_notaVenta.columns().every(function (colIdx) {
            const cell = table_notaVenta.cell(row.index(), colIdx);
            const node = cell.node();
            if (node) $(node).html(cell.render('display')); // usa tus render() existentes
        });

        $(row.node()).addClass('fila-actualizada');
        setTimeout(() => $(row.node()).removeClass('fila-actualizada'), 1500);
    }

    function eliminarFilaDT(tabla, id) {
        const dt = DT[tabla];
        if (!dt) {
            console.warn(`⚠️ [DT] Tabla desconocida: "${tabla}"`);
            return;
        }

        const row = dt.row(`#${id}`);

        if (!row.length) {
            console.warn(`⚠️ [DT "${tabla}"] Fila #${id} no encontrada para eliminar.`);
            return;
        }

        $(row.node()).addClass('fila-eliminada');
        setTimeout(() => {
            row.remove().draw(false);
        }, 400);
    }

    // ── Procesador de eventos WS ─────────────────────────

    function procesarEvento(data) {
        const { tabla, id, accion } = data;

        if (!DT[tabla]) {
            console.warn(`⚠️ [WS] Tabla "${tabla}" no está registrada en DT map. Ignorando.`);
            return;
        }

        switch (accion) {
            case "update":
                if (tabla === 'comprobante') fetchActualizarComprobante(id);
                if (tabla === 'notaVenta') fetchActualizarNotaVenta(id);
                if (tabla === 'guiasTransportista') fetchActualizarGuiaTransportista(id);
                break;

            case "insert":
                mostrarBadgeNuevos(tabla);
                break;

            case "delete":
                eliminarFilaDT(tabla, id);
                break;

            default:
                console.warn(`⚠️ [WS] Acción desconocida: "${accion}"`);
        }
    }

    // ── Click en badge → recarga la tabla y limpia el badge ──
    document.querySelector('[data-badge-tabla="comprobante"]')
        .addEventListener('click', function () {
            table_comprobante.ajax.reload(null, false);
        });

    document.querySelector('[data-badge-tabla="notaVenta"]')
        .addEventListener('click', function () {
            table_notaVenta.ajax.reload(null, false);
        });

    document.querySelector('[data-badge-tabla="notaVenta"]')
        .addEventListener('click', function () {
            table_notaVenta.ajax.reload(null, false);
        });

    document.querySelector('[data-badge-tabla="guiasTransportista"]')
        .addEventListener('click', function () {
            table_guiasTransportista.ajax.reload(null, false);
        });

    // ── WebSocket ────────────────────────────────────────
    let ws;
    let wsIntentosReconexion = 0;
    const WS_MAX_INTENTOS = 10;

    function conectarWebSocket() {
        ws = new WebSocket(WS_URL);

        ws.onopen = function () {
            wsIntentosReconexion = 0;
            const payload = { tipo: "init", empresa_id: EMPRESA_ID, modulo: MODULO };
            ws.send(JSON.stringify(payload));
        };

        ws.onmessage = function (event) {

            let data;
            try {
                data = JSON.parse(event.data);
            } catch (e) {
                console.error("❌ [WS] JSON inválido:", event.data, e);
                return;
            }


            if (data.tipo === "init_ok") {
                return;
            }
            if (data.tipo === "pong") { console.log("🏓 [WS] pong recibido"); return; }
            if (data.tipo === "error") { console.error("❌ [WS] Error del servidor:", data); return; }

            if (data.tipo === "encomienda_cambio") {
                procesarEvento(data);
            } else {
            }
        };

        ws.onclose = function (event) {
            programarReconexion();
        };

        ws.onerror = function (err) {
            console.error("❌ [WS] Error de socket:", err);
        };
    }

    function programarReconexion() {
        if (wsIntentosReconexion >= WS_MAX_INTENTOS) {
            return;
        }
        const delay = Math.min(1000 * 2 ** wsIntentosReconexion, 30000);
        wsIntentosReconexion++;
        setTimeout(conectarWebSocket, delay);
    }

    conectarWebSocket();


    function setCheckboxReadonly(id, readonly = true) {
        const chk = document.getElementById(id);
        chk.onclick = readonly ? (e) => e.preventDefault() : null;
    }

    $("#tp_comprobante").change(function (e) {
        e.preventDefault();
        let formData = new FormData();
        formData.set('tp_comprobante', tp_comprobante.value)
        fetch(instance._URL_ + "encomienda/get_serieForTpComprobante", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            })
            .then(data => {
                serie_venta.innerHTML = ""
                if (data && data.success) {
                    serie_venta.insertAdjacentHTML('beforeend', `<option value="${data.message.id_serie}">${data.message.serie}</option>`)
                }
            })

        if (document.getElementById('e_domicilio').checked == true) {
            div_parentpagante.classList.remove("d-none")
        } else {
            div_parentpagante.classList.add("d-none")
        }
        tp_comprobante.value == 31 ? selectizeMedioPago.disable() : selectizeMedioPago.enable();

        tp_comprobante.value == 31 && !e_salida.checked ? divParentProgra.classList.remove("d-none") : divParentProgra.classList.add("d-none");
        // tp_comprobante.value == 31 ? setCheckboxReadonly('e_domicilio', true) : setCheckboxReadonly('e_domicilio', false);

        if (tp_comprobante.value == 2) {
            if ($('#pago option[value="PAGADO"]').length === 0) {
                $('#pago').prepend('<option value="PAGADO">PAGADO</option>');
            }
            $("#pago").val('PAGADO').trigger("change")
            if ($('#pago option[value="PAGO EN DESTINO"]').length === 0) {
                $('#pago').append('<option value="PAGO EN DESTINO">PAGO EN DESTINO</option>');
            }
            if ($('#pago option[value="PAGO EN BLOQUE"]').length === 0) {
                $('#pago').append('<option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>');
            }

            $('#forma_pago option[value="2"]').remove();
            $('#tp_operacion_venta option[value="2"]').remove();
            if (automatico_p_bloque.value == 1) {
                $("#pago").val("PAGO EN BLOQUE").trigger("change");
            }
        } else if (tp_comprobante.value == 31) {

            div_parentpagante.classList.remove("d-none");
            $('#pago option[value="PAGADO"]').remove();
            if (e_salida.checked) {
                $('#pago option[value="PAGO EN DESTINO"]').remove();
            } else {
                if ($('#pago option[value="PAGO EN DESTINO"]').length === 0) {
                    $('#pago').append('<option value="PAGO EN DESTINO">PAGO EN DESTINO</option>');
                }
            }
            if ($('#pago option[value="PAGO EN BLOQUE"]').length === 0) {
                $('#pago').append('<option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>');
            }
            $('#tp_operacion_venta option[value="2"]').remove();
        } else {
            if ($('#forma_pago option[value="2"]').length === 0) {
                $('#forma_pago').append('<option value="2">CREDITO</option>');
            }
            if ($('#tp_operacion_venta option[value="2"]').length === 0) {
                $('#tp_operacion_venta').append('<option value="2">Operación Sujeta a Detracción - Servicios de Transporte Carga</option>');
            }
            $('#pago option[value="PAGO EN BLOQUE"]').remove();
            $('#pago option[value="PAGO EN DESTINO"]').remove();

            if (tp_comprobante.value == 1 || tp_comprobante.value == 3) {
                if ($('#pago option[value="PAGADO"]').length === 0) {
                    $('#pago').prepend('<option value="PAGADO">PAGADO</option>');
                }
            }
        }
    });

    function guia_pago_rutas() {

    }

    $('#table_comprobante tbody').on('click', '.btnSmallAnular', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        anularEncomienda(data)
    });

    $('#table_comprobante tbody').on('click', '.btnTimelineEncomienda', function () {
        const data = table_comprobante.row($(this).parents()).data();
        obtenerTimelineEncomienda(data.id_encomienda);
    });

    $('#table_notaVenta tbody').on('click', '.btnTimelineEncomienda', function () {
        const data = table_notaVenta.row($(this).parents()).data();
        obtenerTimelineEncomienda(data.id_encomienda);
    });

    $('#table_comprobante tbody').on('click', '.btnDestinoErroneo', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        marcarEncomiendaDestinoErroneo(data)
    });

    $('#table_notaVenta tbody').on('click', '.btnSmallAnular', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        anularEncomienda(data)
    });

    $('#table_notaVenta tbody').on('click', '.btnDestinoErroneo', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        marcarEncomiendaDestinoErroneo(data)
    });

    $('#table_guias tbody').on('click', '.btnAnularGRT', function (e) {
        const data = table_guias.row($(this).parents()).data()

        Swal.fire({
            title: '¿Esta seguro que desea anular la guia de remision transportista?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Anular!'
        }).then((result) => {
            let formData = new FormData();
            formData.set("id", data.id)
            if (result.isConfirmed) {
                document.getElementById('loader').classList.remove('d-none');
                fetch(instance._URL_ + 'encomienda/anular_GRT', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Error: ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                text: data.message
                            });
                            instance.Datatable.reloadTable(table_guias)
                        } else {
                            Swal.fire({
                                icon: 'info',
                                text: data.message
                            });
                        }

                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        document.getElementById('loader').classList.add('d-none');
                    });
            }
        });
    })

    $('#table_comprobante tbody').on('click', '.btnReenviar', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        reenviar(data.id_venta, data.id_tp_comprobante, data.estado)
    });

    $('#table_comprobante tbody').on('click', '.btnConsultarSUNAT', function (e) {
        const data = table_comprobante.row($(this).parents()).data();
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

    const anularEncomienda = (data) => {
        Swal.fire({
            title: '¿Esta seguro que desea anular la encomienda?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Anular!'
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
                formData.set("id_venta", data.id_venta)
                formData.set("tipo_ruc", data.tipo_ruc)
                formData.set("id_encomienda", data.id_encomienda)
                formData.set("tp_comprobante", data.id_tp_comprobante)
                fetch(instance._URL_ + 'encomienda/anularVenta', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status())
                        return response.json()
                    })
                    .then(data => {
                        Swal.close()
                        if (data.success) {
                            instance.Datatable.reloadTable(table_comprobante)
                            instance.Datatable.reloadTable(table_notaVenta)
                            Swal.fire({
                                title: "Exito",
                                text: data.message,
                                icon: "success"
                            });
                            //instance.Toast.operacion_exitosa(data.message)
                        } else {
                            instance.Toast.operacion_erronea(data.message)
                        }
                    })
                    .catch(error => instance.Toast.operacion_erronea(error.message))
            }
        })
    }

    const marcarEncomiendaDestinoErroneo = async (data) => {
        const { value: observacion } = await Swal.fire({
            title: 'Marcar como destino erróneo',
            input: 'textarea',
            inputLabel: 'Motivo del error',
            inputPlaceholder: 'Ej: La encomienda llegó por error a Huancayo. El destino correcto es Lima.',
            inputAttributes: {
                'aria-label': 'Ingrese el motivo'
            },
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debe ingresar una observación';
                }
            }
        });

        if (!observacion) return;

        Swal.fire({
            title: "Marcando encomienda",
            icon: "info",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        });

        Swal.showLoading();

        let formData = new FormData();
        formData.set("id_venta", data.id_venta);
        formData.set("id_encomienda", data.id_encomienda);
        formData.set("id_terminal_destino", data.id_terminal_destino);
        formData.set("id_terminal_origen", data.id_terminal_origen);
        formData.set("observacion", observacion);

        fetch(instance._URL_ + 'encomienda/marcar_destino_erroneo', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (data.success) {
                    instance.Datatable.reloadTable(table_comprobante);
                    instance.Datatable.reloadTable(table_notaVenta);

                    Swal.fire({
                        title: "Éxito",
                        text: data.message,
                        icon: "success"
                    });
                } else {
                    instance.Toast.operacion_erronea(data.message);
                }
            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message);
            });
    }

    function obtenerTimelineEncomienda(id_encomienda) {

        // Mostrar modal
        $("#modal_timeline_encomienda").modal("show");

        // Loader
        document.getElementById("timeline_encomienda").innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <div class="mt-3">
                Cargando historial...
            </div>
        </div>
        `;

        let formData = new FormData();
        formData.set("id_encomienda", id_encomienda);

        fetch(instance._URL_ + "encomienda/obtener_timeline_encomienda", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (!data.success) {
                    instance.Toast.operacion_erronea(data.message);

                    document.getElementById("timeline_encomienda").innerHTML = `
                <div class="alert alert-danger">
                    ${data.message}
                </div>
                 `;

                    return;
                }

                pintarTimeline(data);

            })
            .catch(error => {

                instance.Toast.operacion_erronea(error.message);

                document.getElementById("timeline_encomienda").innerHTML = `
            <div class="alert alert-danger">
                Ocurrió un error al obtener el historial.
            </div>
        `;

            });

    }

    function pintarTimeline(data) {

        // Datos generales
        document.getElementById("tl_comprobante").textContent = data.info.comprobante;
        document.getElementById("tl_tracking").textContent = data.info.tracking;
        document.getElementById("tl_origen").textContent = data.info.origen;
        document.getElementById("tl_destino").textContent = data.info.destino;

        let html = `<div class="timeline">`;

        data.movimientos.forEach(item => {

            let icono = "fa-circle";
            let color = "bg-secondary";

            switch (item.tipo_movimiento) {

                case "REGISTRO":
                    icono = "fa-plus";
                    color = "bg-success";
                    break;

                case "SALIDA":
                    icono = "fa-truck";
                    color = "bg-primary";
                    break;

                case "LLEGADA":
                    icono = "fa-location-dot";
                    color = "bg-success";
                    break;

                case "DESTINO_ERRONEO":
                    icono = "fa-triangle-exclamation";
                    color = "bg-warning text-dark";
                    break;

                case "REENVIO":
                    icono = "fa-arrow-right-arrow-left";
                    color = "bg-info";
                    break;

                case "ENTREGA":
                    icono = "fa-box-check";
                    color = "bg-dark";
                    break;

                case "DEVOLUCION":
                    icono = "fa-rotate-left";
                    color = "bg-danger";
                    break;
            }

            html += `
        <div class="timeline-item">

            <div class="timeline-icon ${color}">
                <i class="fa-light ${icono}"></i>
            </div>

            <div class="timeline-card">

                <div class="d-flex justify-content-between">

                    <div class="timeline-title">
                        ${item.tipo_movimiento.replaceAll("_", " ")}
                    </div>

                    <div class="timeline-date">
                        ${item.fecha_registro}
                    </div>

                </div>

                <div class="timeline-terminal mt-2">
                    <strong>Terminal:</strong>
                    ${item.terminal_evento}
                </div>

                ${item.terminal_destino
                    ?
                    `
                    <div class="timeline-terminal">
                        <strong>Destino:</strong>
                        ${item.terminal_destino}
                    </div>
                    `
                    :
                    ''
                }

                ${item.observacion
                    ?
                    `
                    <div class="timeline-observacion">
                        ${item.observacion}
                    </div>
                    `
                    :
                    ''
                }

            </div>

        </div>
        `;

        });

        html += "</div>";

        document.getElementById("timeline_encomienda").innerHTML = html;

    }

    const modal_printComprobante = (title, links = [], id_venta = null, tp_comprobante = null) => {
        let html_links = ""
        links.forEach((e, i) => {
            if (e != '' && e.link != '') {
                html_links += `
        <a href="${e.link}" class="d-flex flex-column text-decoration-none py-4 mx-2 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
            <i class="fa-light fa-receipt fs-2 text-secondary"></i>
            <label class="text-secondary cursor-pointer mt-2">${e.nombre}</label>
        </a>
        `
            }
        })

        // NUEVO: Agregar botón Comprobante A4 DINÁMICO
        if (id_venta && tp_comprobante != 31) {
            let nombre_boton = 'Comprobante A4';
            let ruta_a4 = '';
            let icono = 'fa-regular fa-file-pdf fs-2 text-danger';

            // Determinar ruta según tipo de comprobante
            if (tp_comprobante == 2) {
                // Nota de Venta
                ruta_a4 = `${instance._URL_}encomienda/impresion/nota_ventaA4/${id_venta}`;
                nombre_boton = 'Nota Venta A4';
                icono = 'fa-regular fa-file-lines fs-2 text-primary';
            } else if (tp_comprobante == 1 || tp_comprobante == 3) {
                // Factura (1) o Boleta (3)
                ruta_a4 = `${instance._URL_}encomienda/impresion/comprobanteA4/${id_venta}`;
                nombre_boton = 'Comprobante A4';
                icono = 'fa-regular fa-file-pdf fs-2 text-danger';
            }

            html_links += `
            <a href="${ruta_a4}" 
               class="d-flex flex-column text-decoration-none py-4 mx-2 wow pulse" 
               data-wow-iteration="infinite" 
               data-wow-duration="500ms" 
               target="_blank">
                <i class="${icono}"></i>
                <label class="text-secondary cursor-pointer mt-2">${nombre_boton}</label>
            </a>
        `
        }

        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            html: `
        <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
            <i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>
            <label></label>
            <h5>${title}</h5>
            <section class="d-flex justify-content-center align-items-center">
                ${html_links}
            </section>
            <section>
                <p class="text-secondary">Enviar comprobante por WhatsApp</p>
                <div class="input-group mb-3">
                    <span class="input-group-text py-0">+51</span>
                    <input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">
                    <button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></buton>
                </div >
            </section >
        </div >
    `,
            confirmButtonText: 'Continuar'
        })

        instance.Validate.allowInputNum(["#celular_clienteWsp"])
        let celular_clienteWsp = document.querySelector("#celular_clienteWsp")
        let btn_sendComprobanteWsp = document.querySelector("#btn_sendComprobanteWsp")

        btn_sendComprobanteWsp.addEventListener("click", (event) => {
            if (celular_clienteWsp.value.length == 9) {
                let mensaje = instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE;

                if (links.length > 0) {
                    let mensajeLinks = "COMPROBANTES: ";

                    if (links.length <= 3) {
                        links.forEach((link, index) => {
                            mensajeLinks += `\n${index + 1}: ${link.nombre}=> ${link.link} \n`;
                        });
                        mensaje = mensaje.replace('$link_comprobante$', mensajeLinks);

                        window.open(
                            instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                                .replace('$number$', `+51${celular_clienteWsp.value}`)
                                .replace('$message$', mensaje),
                            "_blank"
                        );
                    } else {
                        let primerMensaje = mensaje.replace('$link_comprobante$', "Los enlaces se enviarán a continuación.");
                        window.open(
                            instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                                .replace('$number$', `+51${celular_clienteWsp.value}`)
                                .replace('$message$', primerMensaje),
                            "_blank"
                        );

                        links.forEach((link, index) => {
                            mensajeLinks += `\n${index + 1}. ${link.nombre}: ${link.link}`;
                        });

                        setTimeout(() => {
                            window.open(
                                instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                                    .replace('$number$', `+51${celular_clienteWsp.value}`)
                                    .replace('$message$', mensajeLinks),
                                "_blank"
                            );
                        }, 500);
                    }
                } else {
                    window.open(
                        instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                            .replace('$number$', `+51${celular_clienteWsp.value}`)
                            .replace('$message$', mensaje.replace('$link_comprobante$', "")),
                        "_blank"
                    );
                }
            } else {
                instance.Toast.operacion_erronea('Por favor ingrese un número de celular valido')
            }
        })
    }

    const reenviar = (id_venta = null, id_tp_comprobante = null) => {
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

                fetch(instance._URL_ + "encomienda/reenviar_venta", {
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
                            instance.Datatable.reloadTable(table_comprobante)
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

    window.selectProducto = new TomSelect("#ctg_producto", {
        valueField: "id_ctg_encomienda",
        labelField: "descripcion",
        searchField: "descripcion",
        maxOptions: 10,
        plugins: ['clear_button'],
        preload: 'focus',

        create: function (input) {
            return {
                id_ctg_encomienda: "",
                descripcion: input
            };
        },

        shouldLoad: function (query) {
            return query.length >= 2;
        },
        load: function (query, callback) {
            fetch(instance._URL_ + "categoria_encomienda/buscar?q=" + encodeURIComponent(query))
                .then(res => res.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
    });

    selectProducto.on("change", function (value) {
        const producto = this.options[value];
        if (!producto) return;
        if (tipo_precio.value === "unidad" || tipo_precio.value === "peso_total") {
            precio_unitario.value = producto.precio || "0.00";
        }
        peso.value = "0.50";
    });

    //Logica para el boton de guias de remision transportista
    $(document).on('click', '.btnGUIA', function (e) {
        e.preventDefault();

        let id_embarque = $(this).data('id_embarque');

        //Crear un sweetlaert para la creacion de una guia de remision transportista para las encomiendas relacionadas al embarque
        Swal.fire({
            title: 'Crear guía de remisión transportista',
            text: '¿Desea crear una guía de remisión transportista para las encomiendas relacionadas al embarque?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear un objeto FormData
                loader.classList.remove('d-none');
                let formData = new FormData();
                formData.append('id_embarque', id_embarque);

                fetch(instance._URL_ + 'encomienda/crear_guia_embarque', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            instance.Datatable.reloadTable(table_guias)
                            modal_printComprobante(response.message, response.link_guia)
                        } else {
                            Swal.fire({
                                title: 'Atencion!!',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ha ocurrido un error al crear la guía de remisión transportista',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        loader.classList.add('d-none');
                    });
            }
        });
    });

    let operacion = document.querySelector('#operacion');
    $(".open_modal_remitente").click(function (e) {
        operacion.value = 1;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_destinatario").click(function (e) {
        operacion.value = 2;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_pagante").click(function (e) {
        operacion.value = 3;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_cliente").click(function (e) {
        operacion.value = 4;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_receptor").click(function (e) {
        operacion.value = 5;
        e.preventDefault();
        $("#modal_pasajero").modal("show")
    });

    $(".open_modal_salida").click(function (e) {
        e.preventDefault();

        let dat_campos = [
            p_partida.value,
            p_llegada.value,
            ubigeoP.value,
            ubigeoLle.value
        ];

        if (instance.Validate.validateData(dat_campos)) {
            $("#modal_salida").modal("show");
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'Por favor complete los campos de partida y llegada para seleccionar la salida.',
                returnFocus: false
            }).then(() => {
                if (!p_partida.value) {
                    p_partida.focus();
                } else if (!p_llegada.value) {
                    p_llegada.focus();
                } else if (!ubigeoP.value) {
                    ubigeoPartida.focus();
                } else if (!ubigeoLle.value) {
                    ubigeoLlegada.focus();
                }
            });
        }
    });

    // Si se da click por primera vez buscar el 00000000 pero si se da nuevo ocultar y limpiar el campo de destinatario
    $(".btn_varios").click(function (e) {
        e.preventDefault();

        if (dato_destinatario.classList.contains('d-none')) {
            destinatario.value = "00000000";
            d_extra.value = 1;
            buscar_destinatario.click();
            dato_destinatario.classList.remove('d-none');
        } else {
            dato_destinatario.classList.add('d-none');
            destinatario.value = "";
            $("#destinatario_nombres").val("");
            d_extra.value = 0;
        }
    });

    $(".open_modal_ctgProducto").click(function (e) {
        e.preventDefault();
        $("#modal_ctgEncomienda").modal("show")
    });

    // Entraga de encomienda
    document.getElementById('e_domicilio').addEventListener('change', function () {
        if (this.checked) {
            $("#datos_entrega").removeClass("d-none");
            $("#div_parentpagante").removeClass("d-none");
        } else {
            $("#datos_entrega").addClass("d-none");
            if (tp_comprobante.value != "31") {
                $("#div_parentpagante").addClass("d-none");
            }
        }
    });

    function setDomicilio(checked) {
        const eDomicilio = document.getElementById('e_domicilio');

        eDomicilio.checked = checked;
        eDomicilio.dispatchEvent(new Event('change'));
    }

    document.getElementById('e_salida').addEventListener('change', function () {
        if (this.checked) {
            $("#divParentSalida").removeClass("d-none");

            selectizeDestinoTerminal.clear();
            selectizeDestinoTerminal.disable();

            $('#almacen')
                .val(null)
                .prop('disabled', true)
                .trigger('change');

            setDomicilio(true);
            if (tp_comprobante.value == '31') {
                divParentProgra.classList.add("d-none");
                $('#pago option[value="PAGO EN DESTINO"]').remove();
            }
        } else {
            $("#divParentSalida").addClass("d-none");

            selectizeDestinoTerminal.clear();
            selectizeDestinoTerminal.enable();

            $('#almacen')
                .val(null)
                .prop('disabled', false)
                .trigger('change');

            setDomicilio(false);
            if (tp_comprobante.value == '31') {
                divParentProgra.classList.remove("d-none");
                if ($('#pago option[value="PAGO EN DESTINO"]').length === 0) {
                    $('#pago option[value="PAGO EN BLOQUE"]').before('<option value="PAGO EN DESTINO">PAGO EN DESTINO</option>');
                }
                $("#pago").val("PAGO EN DESTINO").trigger("change");
            }
        }
    });

    // Logica mostra o no 
    $("#forma_pago").on('change', function () {
        let forma_pago = $(this).val();
        if (forma_pago == 2) {
            div_cuota.classList.remove('d-none')
        } else {
            div_cuota.classList.add('d-none')
        }
    });

    // Logica mostra o no 
    $("#forma_pagoEntregar").on('change', function () {
        let forma_pagoE = $(this).val();
        if (forma_pagoE == 2) {
            selectizeMedioPagoEntregar.disable();
            div_cuotaEntregar.classList.remove('d-none')
        } else {
            selectizeMedioPagoEntregar.enable();
            div_cuotaEntregar.classList.add('d-none')
        }
    });

    let tpOperacionAnterior = $("#tp_operacion_venta").val();

    $("#tp_operacion_venta").on('focus', function () {
        tpOperacionAnterior = $(this).val();
    });

    $("#tp_operacion_venta").on('change', async function () {
        let tp_op = $(this).val();
        let costo_t = document.getElementById('costo_total');
        let costo_valor = parseFloat(costo_t.textContent.replace('S/', '')) || 0;

        if (tp_op != 2) {
            div_detraccion.classList.add('d-none');
            return;
        }

        if (costo_valor <= 400) {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'El monto total de la encomienda debe ser mayor a S/ 400.00 para aplicar detracción.'
            });

            $(this).val(tpOperacionAnterior);
            div_detraccion.classList.add('d-none');
            return;
        }

        const existeCuenta = await consultar_cuenta_detracciones();

        if (!existeCuenta) {
            $(this).val(tpOperacionAnterior);
            div_detraccion.classList.add('d-none');
            return;
        }

        div_detraccion.classList.remove('d-none');
    });

    $("#tp_operacion_ventaEntregar").on('change', async function () {
        let tp_op = $(this).val();
        let costo_t = document.getElementById('total_operacionEntregar');
        let costo_valor = parseFloat(costo_t.value) || 0;

        if (tp_op != 2) {
            div_detraccionEntregar.classList.add('d-none');
            return;
        }

        if (costo_valor <= 400) {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'El monto total de la encomienda debe ser mayor a S/ 400.00 para aplicar detracción.'
            });

            $(this).val(tpOperacionAnterior);
            div_detraccionEntregar.classList.add('d-none');
            return;
        }

        const existeCuenta = await consultar_cuenta_detracciones();

        if (!existeCuenta) {
            $(this).val(tpOperacionAnterior);
            div_detraccionEntregar.classList.add('d-none');
            return;
        }

        div_detraccionEntregar.classList.remove('d-none');
        setUbigeoValue(ubigeoDestinoDE, instance._UB_TERMINAL_SESION);
        destino_detraccionEntregar.value = instance._DIR_TERMINAL_SESION;
    });

    async function consultar_cuenta_detracciones() {
        try {
            const response = await fetch(instance._URL_ + 'encomienda/consultar_cuentaD');
            if (!response.ok) throw new Error('Error del servidor');

            const data = await response.json();

            if (!data.success) {
                instance.Toast.operacion_informativa(data.message);
                return false;
            }

            return true;

        } catch (error) {
            instance.Toast.operacion_erronea(error.message);
            return false;
        }
    }

    // Logica de tiempo credito 
    $("#tiempo_credito").on('change', function () {
        let tiempo_credito = $(this).val();
        let dias = 0;

        switch (tiempo_credito) {
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

        const hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);

        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0'); // Mes empieza en 0
        const dd = String(hoy.getDate()).padStart(2, '0');

        const fecha_resultado = `${yyyy}-${mm}-${dd}`;

        $("#fecha_credito").val(fecha_resultado);
    });

    $("#tiempo_creditoEntregar").on('change', function () {
        let tiempo_credito = $(this).val();
        let dias = 0;

        switch (tiempo_credito) {
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

        const hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);

        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0'); // Mes empieza en 0
        const dd = String(hoy.getDate()).padStart(2, '0');

        const fecha_resultado = `${yyyy}-${mm}-${dd}`;

        $("#fecha_creditoEntregar").val(fecha_resultado);
    });

    $(document).ready(function () {
        $("#tiempo_credito").trigger('change');
        $("#tiempo_creditoEntregar").trigger('change');
        $("#forma_pago").trigger('change');
    });


    // Productos
    add_producto.addEventListener("click", async (event) => {
        const tbody = table_productos.getElementsByTagName("tbody")[0];
        if (parseInt(cantidad.value) === 0) {
            return instance.Toast.operacion_erronea("La cantidad debe ser mayor a 0");
        }
        if (!div_peso.classList.contains("d-none") && parseFloat(peso.value) === 0) {
            return instance.Toast.operacion_erronea("Tiene que ingresar el peso del producto porque se generará una guía");
        }
        if (tipo_precio.value === 'peso' && precio_kg.value <= 0) {
            return instance.Toast.operacion_erronea("Tiene que ingresar el precio por kilo(KG)");
        }
        if (precio_unitario.value <= 0) {
            return instance.Toast.operacion_erronea("El precio unitario del producto debe ser mayor a 0.00");
        }

        const productoSeleccionado = selectProducto.options[ctg_producto.value];

        let producto_id = productoSeleccionado.id_ctg_encomienda ? productoSeleccionado.id_ctg_encomienda : "";
        let producto_texto = (productoSeleccionado.descripcion ? productoSeleccionado.descripcion : "").trim();
        if (!producto_texto) {
            return instance.Toast.operacion_erronea("Debe ingresar o seleccionar un producto");
        }

        if (!producto_id) {
            try {
                add_producto.disabled = true;
                const fd = new FormData();
                fd.append("descripcion", producto_texto);
                fd.append("precio", 0.00);
                fd.append("afectacion", 'IGV');
                fd.append("id_ctg_encomienda", '');

                const response = await fetch(instance._URL_ + 'categoria_encomienda/crud_register', {
                    method: 'POST',
                    body: fd
                });

                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();

                if (data.success) {
                    producto_id = data.data.id_ctg_encomienda;
                } else {
                    return instance.Toast.operacion_erronea("No se pudo crear el producto: " + (data.message || ""));
                }
            } catch (error) {
                console.error('Error creando producto:', error);
                return instance.Toast.operacion_erronea("Error de conexión al crear el producto");
            } finally {
                add_producto.disabled = false;
            }
        }

        const unidadText = unidad_m.options[unidad_m.selectedIndex].text;
        const precio_x_kg = parseFloat(precio_kg.value);

        let precio_prod = tipo_precio.value === 'peso_total'
            ? parseFloat(precio_unitario.value) / parseFloat(cantidad.value)
            : parseFloat(precio_unitario.value);

        let subtotal = 0;
        switch (tipo_precio.value) {
            case 'peso_total': subtotal = parseFloat(precio_unitario.value); break;
            case 'precio_total': subtotal = parseFloat(p_total.value); break;
            default: subtotal = parseFloat(cantidad.value) * parseFloat(precio_unitario.value); break;
        }
        subtotal = subtotal.toFixed(2);

        const peso_final = tipo_precio.value === 'peso'
            ? parseFloat(peso.value) * parseFloat(cantidad.value)
            : parseFloat(peso.value);

        const hiddenInputs = `
        <td class="d-none">
            <input type="hidden" name="id_producto[]"  value="${producto_id}">
            <input type="hidden" name="obs_producto[]" value="${obs_producto.value}">
            <input type="hidden" name="unid_medida[]"  value="${unidad_m.value}">
        </td>
        `;

        const makeRow = () => `
        <tr>
            ${hiddenInputs}
            <td><label class="cantidad_producto">${cantidad.value}</label></td>
            <td><label class="unidad_m">${unidadText}</label></td>
            <td>
                <label class="ctg_producto"
                    data-id-producto="${producto_id}"
                    id-ctg-producto="${producto_id}">
                    ${producto_texto}
                </label>
            </td>
            <td><label class="precio_producto">${precio_prod.toFixed(2)}</label></td>
            <td><label class="precio_x_kg">${precio_x_kg.toFixed(2)}</label></td>
            <td><label class="peso">${peso_final.toFixed(2)}</label></td>
            <td><label class="subtotal_producto">${subtotal}</label></td>
            <td>
                <button type="button" class="btn button_deleteItem p-0 pt-2">
                    <i class="bi bi-trash text-danger fs-5"></i>
                </button>
            </td>
        </tr>
    `;

        tbody.insertAdjacentHTML("afterbegin", makeRow());

        const subtotales = Array.from(tbody.querySelectorAll("tr .subtotal_producto"))
            .map(e => parseFloat(e.textContent));
        const pesos = Array.from(tbody.querySelectorAll("tr .peso"))
            .map(e => parseFloat(e.textContent));

        const total = subtotales.reduce((a, b) => a + b, 0).toFixed(2);
        tbody.querySelector("#costo_total").textContent = total;
        monto_credito.value = total;
        tbody.querySelector("#peso_total").textContent = pesos.reduce((a, b) => a + b, 0).toFixed(2);

        selectProducto.clear();
        selectProducto.clearOptions();
        selectProducto._typed_text = "";
        $("#unidad_m").val("1").trigger("change");
        cantidad.value = 1;
        $("#obs_producto").val("");
        $("#peso").val("0.50");
        $("#precio_kg").val("0.00");
        $("#precio_unitario").val("0.00");
        $("#p_total").val("0.00");
    });

    $('#table_productos tbody').on('click', '.button_deleteItem', function (e) {
        let tbody = table_productos.getElementsByTagName("tbody")[0]
        document.querySelector("#table_productos tbody").removeChild(this.closest("tr"))
        let precios = Object.values(tbody.querySelectorAll("tr .subtotal_producto")).map((e, i, array) => e.textContent)
        let pesos = Array.from(tbody.querySelectorAll("tr .peso"))
            .map(e => parseFloat(e.textContent));
        tbody.querySelector("#costo_total").textContent = precios.reduce((acum, e) => parseFloat(acum) + parseFloat(e), 0).toFixed(2)
        tbody.querySelector("#peso_total").textContent = pesos.reduce((acum, e) => parseFloat(acum) + parseFloat(e), 0).toFixed(2)
        monto_credito.value = precios.reduce((acum, e) => parseFloat(acum) + parseFloat(e), 0).toFixed(2)
    });

    //Enter en pass
    $('#pass').on('keydown', function (e) {
        if (e.key === 'Enter') {
            $('#ctg_producto').focus();
        }
    });

    // Entregar
    instance.Validate.allowInputNum(["#num_docuEntregar"])
    instance.select.createSelect("#tp_comprobanteEntregar", "#div_parentTpComprobanteEntregar")
    instance.select.createSelect("#serie_ventaEntregar", "#div_parentSerieVentaEntregar")

    let form_buscarEncomienda = document.querySelector("#form_buscarEncomienda")
    let datos_destinatarioEntregar = document.querySelector("#datos_destinatarioEntregar")
    let button_buscarEncomienda = document.querySelector("#button_buscarEncomienda")
    let button_loadSaveBuscarEncomienda = document.querySelector("#button_loadSaveBuscarEncomienda")
    let table_encomiendas = document.querySelector("#table_encomiendas")
    let table_productosEntregar = document.querySelector("#table_productosEntregar")
    let message_sinEncomiendas = document.querySelector("#message_sinEncomiendas")
    let button_entregar = document.querySelector("#button_entregar")
    let button_loadEntregar = document.querySelector("#button_loadEntregar")
    // Generar nuevo comprobante
    let habilitarDivGenerarComprobante = document.querySelector("#habilitarDivGenerarComprobante")
    let div_generarComprobante = document.querySelector("#div_generarComprobante")

    let div_generarNuevoComprobante = document.querySelector("#div_generarNuevoComprobante")
    let opcion_dataOrigen = document.querySelector("#opcion_dataOrigen")
    let opcion_dataDestino = document.querySelector("#opcion_dataDestino")
    let opcion_dataNueva = document.querySelector("#opcion_dataNueva")
    let tp_comprobanteEntregar = document.querySelector("#tp_comprobanteEntregar")
    let serie_ventaEntregar = document.querySelector("#serie_ventaEntregar")
    let remitenteEntregar = document.querySelector("#remitenteEntregar")
    let remitenteEntregar_id = document.querySelector("#remitenteEntregar_id")
    let fecha_encomiendas = document.querySelector("#fecha_inicio_embarcar")
    let estado_encomiendas = document.querySelector("#estado_encomiendas")

    let div_imprimirComprobanteOrigen = document.querySelector("#div_imprimirComprobanteOrigen")

    // Pagar
    let section_opcionesEntregar = document.querySelector("#section_opcionesEntregar")
    let div_porPagar = document.querySelector("#div_porPagar")
    let div_datosReceptor = document.querySelector("#div_datosReceptor")
    let button_pagar = document.querySelector("#button_pagar")
    let button_registrarR = document.querySelector("#button_registrarR")
    let button_loadRegistrarR = document.querySelector("#button_loadRegistrarR")
    let button_loadPagar = document.querySelector("#button_loadPagar")
    let medio_pagoEntregar = document.querySelector("#medio_pagoEntregar")
    let destino_entregar = document.querySelector("#destino_entregar")
    let obs_ventaEntregar = document.querySelector("#obs_ventaEntregar")

    let rucEP = document.querySelector("#rucEP")
    let serieGR = document.querySelector("#serieGR")
    let correlativoGR = document.querySelector("#correlativoGR")
    let rucGR = document.querySelector("#rucGR")

    // Variables de detraccion **CALCULO**
    let total_operacion = document.querySelector("#total_operacion")
    let tm_detraccion = document.querySelector("#tm_detraccion")
    let v_ref_carga_efectiva = document.querySelector("#v_ref_carga_efectiva")
    let v_ref_carga_util = document.querySelector("#v_ref_carga_util")
    let v_ref_servicio = document.querySelector("#v_ref_servicio")
    let config_vehiculo = document.querySelector("#config_vehiculo")
    let config_vehiculoEntregar = document.querySelector("#config_vehiculoEntregar")
    let monto_detraccion = document.querySelector("#monto_detraccion")
    let ruta_origen_select = document.querySelector("#ruta_origen")
    let ruta_origen_selectE = document.querySelector("#ruta_origenEntregar")
    let ruta_destino_select = document.querySelector("#ruta_destino")
    let base_detraccion = document.querySelector("#base_detraccion")

    form_buscarEncomienda.addEventListener("submit", (event) => {
        event.preventDefault();
        // Limpiar campos de receptor
        limpiar_receptor();
        let formData = new FormData(form_buscarEncomienda)
        if (instance.Validate.validateData([formData.get("num_docuEntregar")])) {
            button_buscarEncomienda.classList.add("d-none")
            button_loadSaveBuscarEncomienda.classList.remove("d-none")
            button_entregar.classList.add("d-none")
            datos_destinatarioEntregar.innerHTML = ""

            habilitarDivGenerarComprobante.checked = false
            opcion_dataOrigen.checked = true
            opcion_dataDestino.checked = false
            opcion_dataNueva.checked = false
            section_opcionesEntregar.classList.add("d-none")
            div_generarComprobante.classList.add("d-none")
            div_generarNuevoComprobante.classList.add("d-none")
            div_imprimirComprobanteOrigen.classList.add("d-none")
            div_porPagar.classList.add("d-none")
            div_datosReceptor.classList.add("d-none")

            fetch(instance._URL_ + 'encomienda/getEncomiendas', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status)
                    return response.json()
                })
                .then(data => {
                    $('#table_encomiendas tbody tr').remove();
                    $('#table_productosEntregar tbody tr').remove();
                    if (data.success) {
                        datos_destinatarioEntregar.insertAdjacentHTML('beforeend', `
                                    <div class="d-flex flex-column align-items-end">
                                        <label class="text-end">${data.message[0].destinatario_nombres} ${data.message[0].destinatario_apellidos}</label>
                                        <label>${data.message[0].destinatario_num_docu}</label>
                                    <div>
                                `)
                        Object.values(data.message).forEach((e, i, array) => {
                            table_encomiendas.getElementsByTagName("tbody")[0].insertAdjacentHTML('afterbegin', `
                                        <tr>
                                            <!-- NUEVA COLUMNA NÚMERO -->
                                            <td>
                                                <label>${e.serie && e.correlativo ? `${e.serie}-${e.correlativo}` : '---'}</label>
                                            </td>
                                            <!-- FIN NUEVA COLUMNA -->
                                            <td><label>${e.terminal_origen}</label></td>
                                            <td><label>${e.remitente_nombres} ${e.remitente_apellidos}<br>${e.remitente_num_docu}</label></td>
                                            <td><span class="badge estado_envio" style="background-color:${e.salida == 1 ? instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA['EN RUTA'] : instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[e.encomienda_estado_envio]}">${e.salida == 1 ? 'EN RUTA' : e.encomienda_estado_envio}</span></td>
                                            <td><span class="badge estado_venta" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[e.estado]}">${e.estado}</span></td>
                                            <td><label>${e.encomienda_fecha_salida.split('-').reverse().join('-')}</label></td>
                                            <td><label>${e.op_gravada}</label></td>
                                            <td>
                                                <button type='button' class='btn btnColorViolet btnVerDetalle p-0 pt-2' id-encomienda="${e.id_encomienda}" estado-encomienda="${e.encomienda_estado_envio}" id-venta="${e.id_venta}" comprobante="${e.id_tp_comprobante}" 
                                                id-terminal_origen="${e.id_terminal_origen}" 
                                                id-terminal_destino="${e.id_terminal_destino}"  
                                                estado-venta="${e.estado}"
                                                data-dni="${e.destinatario_num_docu}"
                                                data-total="${e.total}"
                                                data-terminal_origen="${e.ubigeo_origen}"
                                                data-terminal_destino="${e.ubigeo_destino}"
                                                data-direccion_destino="${e.direccion_destino}"
                                                data-tp_encomienda="${e.salida == 1 ? 'salida' : 'programacion'}"
                                                data-tp_comprobante="${e.id_tp_comprobante}"><i class="fa-solid fa-arrow-right fs-6"></i></button>
                                                <button class="btn btnLoadVerDetalle btnColorViolet d-none" type="button" disabled>
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    `)
                        })
                        message_sinEncomiendas.classList.add("d-none")
                    } else {
                        $('#table_encomiendas tbody tr').remove();
                        instance.Toast.operacion_informativa('No existe ninguna encomienda para este cliente')
                        message_sinEncomiendas.classList.remove("d-none")
                    }
                    button_buscarEncomienda.classList.remove("d-none")
                    button_loadSaveBuscarEncomienda.classList.add("d-none")
                })
                .catch(error => {
                    instance.Toast.operacion_erronea(error.message)
                    button_buscarEncomienda.classList.remove("d-none")
                    button_loadSaveBuscarEncomienda.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea("Rellene correctamente los campos.")
        }
    });

    // Tipo comprobante
    $('#tp_comprobante_e').change(function (e) {
        let tp = $(this).val();
        if (tp != "") {
            $('#div_parentSerieEP').removeClass("d-none");
        } else {
            $('#div_parentSerieEP').addClass("d-none");
        }
    });

    // Destino 
    destinoSelect[0].selectize.on('change', function (value) {
        $('#pass').focus();
    });

    let tp_comprobanteE = '';
    $('#table_encomiendas tbody').on('click', '.btnVerDetalle', function (e) {
        const row = this.closest("tr");
        const num_receptor = this.getAttribute('data-dni');
        const total_enco = this.getAttribute('data-total');
        const terminal_origen = this.getAttribute('data-terminal_origen');
        const terminal_destino = this.getAttribute('data-terminal_destino');
        const direccion_destino = this.getAttribute('data-direccion_destino');
        setUbigeoValue(ubigeoOrigenDE, terminal_origen);
        setUbigeoValue(ubigeoDestinoDE, terminal_destino);
        origen_detraccionEntregar.value = direccion_destino;
        const id_tp_comprobanteE = this.getAttribute('data-tp_comprobante');
        tp_comprobanteE = id_tp_comprobanteE;
        let formData = new FormData()
        formData.set("id_encomienda", this.getAttribute("id-encomienda"))
        // cambiar el background al activar
        limpiar_receptor();
        $(".btnVerDetalle").removeClass('active')
        this.classList.add("active")
        row.querySelector(".btnLoadVerDetalle").classList.remove("d-none")

        // Haciendo peticion
        fetch(instance._URL_ + "encomienda/getProductosEncomienda", {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    $('#table_productosEntregar tbody tr').remove();
                    div_imprimirComprobanteOrigen.innerHTML = ""
                    div_imprimirComprobanteOrigen.insertAdjacentHTML('beforeend', `
                        <a href="${this.getAttribute("comprobante") == 2 ? instance.CONSTS.URL.IMPRESION_ENCOMIENDA.NOTA_VENTA + this.getAttribute("id-venta") : instance.CONSTS.URL.IMPRESION_ENCOMIENDA.COMPROBANTE + this.getAttribute("id-venta")}" class="d-flex flex-column justify-content-center align-items-center text-decoration-none py-2 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
                            <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                            <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
                        </a>
                    `)
                    Object.values(data.message).forEach((e, i, array) => {
                        table_productosEntregar.getElementsByTagName("tbody")[0].insertAdjacentHTML('afterbegin', `
                        <tr>
                            <td><label>${e.descripcion}</label></td>
                            <td><label>${e.cantidad}</label></td>
                            <td><label>${e.precio}</label></td>
                            <td><label>${e.rotulado == 1 ? 'SI' : 'NO'}</label></td>
                            <td><label>${e.obs}</label></td>
                            <td><label>${(e.precio * e.cantidad).toFixed(2)}</label></td>
                        </tr>
                        `)
                    })
                }
                this.classList.remove("d-none")
                this.closest("tr").querySelector(".btnLoadVerDetalle").classList.add("d-none")
            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message)
                this.classList.add("d-none")
                this.closest("tr").querySelector(".btnLoadVerDetalle").classList.remove("d-none")
            })

        // Obtener valores base
        const estadoVenta = row.querySelector('.estado_venta').textContent.trim();
        const estadoEncomienda = this.getAttribute("estado-encomienda").trim();
        const comprobanteE = this.getAttribute("comprobante");
        const terminalDestino = this.getAttribute("id-terminal_destino");
        const tpEncomienda = this.getAttribute("data-tp_encomienda");
        const receptorInvalido = (num_receptor === "00000000");

        this.classList.add("d-none");
        div_imprimirComprobanteOrigen.classList.remove("d-none");

        if (tpEncomienda == 'salida') {
            if (estadoEncomienda === "EN TRANSITO") {
                div_porPagar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");
                if (receptorInvalido) {
                    button_entregar.classList.add("d-none");
                } else {
                    button_entregar.classList.remove("d-none");
                }

            }
        } else {
            if (terminalDestino != instance._ID_TERMINAL_SESION) {
                div_porPagar.classList.add("d-none");
                button_entregar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");
                div_datosReceptor.classList.add("d-none");
                return;
            }

            if (receptorInvalido) {
                div_datosReceptor.classList.remove("d-none");
                button_entregar.classList.add("d-none");
            } else {
                div_datosReceptor.classList.add("d-none");
                button_entregar.classList.remove("d-none");
            }

            if (estadoVenta === 'PAGADO' && estadoEncomienda === "EN DESTINO") {
                // Encomienda ya pagada y llegó a destino → se puede entregar
                div_porPagar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");
                if (receptorInvalido) {
                    button_entregar.classList.add("d-none");
                } else {
                    button_entregar.classList.remove("d-none");
                }

            } else if (['PAGADO', 'PAGO EN DESTINO'].includes(estadoVenta) && estadoEncomienda !== "EN DESTINO") {
                // Pagado pero aún no llegó al destino → no se entrega ni se paga
                div_porPagar.classList.add("d-none");
                button_entregar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");

            } else if (['PAGO EN BLOQUE'].includes(estadoVenta) && estadoEncomienda === "EN DESTINO") {
                div_porPagar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");
                button_entregar.classList.remove("d-none");
            }
            else if (['CREDITO'].includes(estadoVenta) && estadoEncomienda === "EN DESTINO") {
                div_porPagar.classList.add("d-none");
                section_opcionesEntregar.classList.add("d-none");
                button_entregar.classList.remove("d-none");
            }
            else {
                // Caso general: pendiente o por pagar
                button_entregar.classList.add("d-none");
                $("#monto_creditoEntregar").val(total_enco)
                $("#total_operacionEntregar").val(total_enco)
                if (receptorInvalido) {
                    div_porPagar.classList.add("d-none");
                    section_opcionesEntregar.classList.add("d-none");
                } else {
                    if (comprobanteE == 2 || comprobanteE == 31 && estadoEncomienda === "EN DESTINO" && estadoVenta === "PAGO EN DESTINO") {
                        section_opcionesEntregar.classList.remove("d-none");
                    } else {
                        section_opcionesEntregar.classList.add("d-none");
                    }
                    div_porPagar.classList.remove("d-none");
                }
            }
        }
    })

    $("#modal_entregar").on("show.bs.modal", (e) => {
        $("#modal_entregar").find("form").trigger("reset")
        message_sinEncomiendas.classList.remove("d-none")
        datos_destinatarioEntregar.innerHTML = ""
        $('#table_encomiendas tbody tr').remove()
        $('#table_productosEntregar tbody tr').remove()
        button_entregar.classList.add("d-none")
        // generar comprobante
        habilitarDivGenerarComprobante.checked = false
        opcion_dataOrigen.checked = true
        opcion_dataDestino.checked = false
        opcion_dataNueva.checked = false
        div_generarNuevoComprobante.classList.add("d-none")
        div_generarComprobante.classList.add("d-none")
        section_opcionesEntregar.classList.add("d-none")
        $("#tp_comprobanteEntregar").val($("#tp_comprobanteEntregar").val()).trigger("change")
        // div imprimir
        div_imprimirComprobanteOrigen.classList.add("d-none")
        // div pagar
        div_porPagar.classList.add("d-none")
    });

    $('#modal_entregar').on('hidden.bs.modal', function () {
        clearDivPagar();
        limpiarOpcionesEntregar();
        limpiar_campos_detraccionEntregar();
        $('#tp_operacion_ventaEntregar option[value="2"]').remove();
    });

    function limpiarOpcionesEntregar() {
        $('#habilitarDivGenerarComprobante').prop('checked', false);
        $('#div_generarComprobante').addClass('d-none');
        $('#opcion_dataOrigen').prop('checked', true);
        $('#opcion_dataDestino').prop('checked', false);
        $('#opcion_dataNueva').prop('checked', false);
        $('#tp_comprobanteEntregar').val('3');
        $('#serie_ventaEntregar').empty();
        $('#remitenteEntregar').val('');
        $('#remitenteEntregar_nombres').val('');
        $('#remitenteEntregar_id').val('');
        $('#loader_buscar_rE').addClass('d-none');
        $('#buscar_remitenteEntregar').removeClass('d-none');
        selectizeMedioPagoEntregar.setValue('');
        selectizeMedioPagoEntregar.enable();
        $('#forma_pagoEntregar option[value="2"]').remove();
        div_cuotaEntregar.classList.add("d-none");
        $('#tp_operacion_ventaEntregar').val('1').trigger("change");
    }

    function limpiar_receptor() {
        receptor.value = "";
        receptor_id.valud = "";
    }

    $("#fecha_inicio_embarcar").change(function (e) {
        let fecha_inicio = $(this).val() || "";
        let fecha_fin = $("#fecha_fin").val() || $("#fecha_fin").attr("value") || "";
        let estado = $("#estado_encomiendas").val();

        let data = new FormData();
        data.set("id_terminalDestino", terminal_destinoEmbarcar.value);
        data.set("fecha_inicio", fecha_inicio);
        data.set("fecha_fin", fecha_fin);
        data.set("estado_encomienda", estado);

        if (programacion_destinoEmbarcar.value) {
            const opt = $("#programacion_destinoEmbarcar").find("option:selected");
            const destinos = opt.data("destinos");
            data.set("destinos_programacion", JSON.stringify(destinos));
        }

        fetch(instance._URL_ + "encomienda/get_dataTableEncomiendaEmbarcar", {
            method: "POST",
            body: data
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText || response.status);
            return response.json();
        }).then(responseData => {
            if (responseData.success && Array.isArray(responseData.data)) {
                table_encomiendaEmbarcar.clear().rows.add(responseData.data).draw();
            } else {
                instance.Toast.operacion_informativa("No se encontraron encomiendas");
                table_encomiendaEmbarcar.clear().draw();
            }
        }).catch(error => {
            instance.Toast.operacion_erronea(`Error: ${error.message}`);
        });
    });

    // Evento para fecha_fin
    $("#fecha_fin").change(function (e) {
        // Llamar a la misma función que fecha_inicio_embarcar
        $("#fecha_inicio_embarcar").trigger("change");
    });

    // Evento para estado_encomiendas
    $("#estado_encomiendas").change(function (e) {
        let estado = $(this).val();
        let data = new FormData();

        let fecha_inicio = $("#fecha_inicio_embarcar").val() || "";
        let fecha_fin = $("#fecha_fin").val() || $("#fecha_fin").attr("value") || "";

        data.set("id_terminalDestino", terminal_destinoEmbarcar.value);
        data.set("fecha_inicio", fecha_inicio);
        data.set("fecha_fin", fecha_fin);
        data.set("estado_encomienda", estado);

        if (programacion_destinoEmbarcar.value) {
            const opt = $("#programacion_destinoEmbarcar").find("option:selected");
            const destinos = opt.data("destinos");
            data.set("destinos_programacion", JSON.stringify(destinos));
        }

        fetch(instance._URL_ + "encomienda/get_dataTableEncomiendaEmbarcar", {
            method: "POST",
            body: data
        }).then(response => {
            if (!response.ok) throw new Error(response.statusText || response.status);
            return response.json();
        }).then(responseData => {
            if (responseData.success && Array.isArray(responseData.data)) {
                table_encomiendaEmbarcar.clear().rows.add(responseData.data).draw();
            } else {
                instance.Toast.operacion_informativa("No se encontraron encomiendas con el estado para este terminal de destino");
                table_encomiendaEmbarcar.clear().draw();
            }
        }).catch(error => {
            instance.Toast.operacion_erronea(`Error: ${error.message}`);
        });
    });

    button_entregar.addEventListener("click", (event) => {
        event.preventDefault()
        let formData = new FormData();
        (async () => {
            const { value: cod } = await Swal.fire({
                input: 'password',
                title: "Código de Seguridad",
                inputPlaceholder: 'Código',
                inputAttributes: {
                    'aria-label': 'Digite el código',
                    'required': true,
                    'autocomplete': "off",
                    'minlength': 6,
                    'maxlength': 6,
                },
                cancelButtonColor: "#e61545",
                confirmButtonText: "Verificar",
                allowEscapeKey: false,
                allowEnterKey: false,
                allowOutsideClick: false,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (value.trim().length >= 4) resolve()
                        else resolve('Ingrese el código correctamente')
                    })
                }, preConfirm: (value) => {
                    if (!value.trim().length == 6) {
                        Swal.showValidationMessage(`Ingrese el código correctamente`)
                    }
                }
            })
            if (cod) {
                formData.set("id_encomienda", document.querySelector(".btnVerDetalle.active").getAttribute("id-encomienda"))
                formData.set("codigo", cod)
                formData.set("id_venta", document.querySelector(".btnVerDetalle.active").getAttribute("id-venta"))
                button_entregar.classList.add("d-none")
                button_loadEntregar.classList.remove("d-none")
                fetch(instance._URL_ + 'encomienda/entregarEncomienda', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status)
                        return response.json()
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message.message)
                            modal_printComprobante('¡Encomienda entregado con éxito!', data.message.links)
                            $("#modal_entregar").modal("toggle")
                        } else {
                            instance.Toast.operacion_erronea(data.message.message)
                        }
                        button_entregar.classList.remove("d-none")
                        button_loadEntregar.classList.add("d-none")
                    })
                    .catch(error => {
                        instance.Toast.operacion_erronea(error.message)
                        button_entregar.classList.remove("d-none")
                        button_loadEntregar.classList.add("d-none")
                    })
            }
        })()
    })

    function validarCamposDetraccion() {
        let errores = [];
        let camposConError = [];

        // Verificar si tp_operacion_ventaEntregar es 2
        if (tp_operacion_ventaEntregar.value != 2) {
            return true; // No requiere validación
        }

        // Obtener el método de detracción seleccionado
        let tp_calculo_element = document.querySelector("input[name='metodo_detraccionEntregar']:checked");
        let tp_calculo_value = tp_calculo_element ? tp_calculo_element.value : null;

        // Si no hay método seleccionado
        if (!tp_calculo_value) {
            errores.push("Debe seleccionar un método de detracción");
            return mostrarErrores(errores);
        }

        // Campos comunes para ambos métodos
        const camposComunes = [
            { elemento: ubigeo_origen_dEntregar, nombre: "Ubigeo origen" },
            { elemento: origen_detraccionEntregar, nombre: "Origen detracción" },
            { elemento: ubigeo_destino_dEntregar, nombre: "Ubigeo destino" },
            { elemento: destino_detraccionEntregar, nombre: "Destino detracción" },
            { elemento: medio_pago_detraccionEntregar, nombre: "Medio de pago" },
            { elemento: detalle_detraccionEntregar, nombre: "Detalle de detracción" },
            { elemento: monto_detraccionEntregar, nombre: "Monto de detracción" },
            { elemento: base_detraccionEntregar, nombre: "Base de detracción" },
            { elemento: v_ref_carga_efectivaEntregar, nombre: "Carga efectiva" },
            { elemento: v_ref_carga_utilEntregar, nombre: "Carga útil" },
            { elemento: v_ref_servicioEntregar, nombre: "Servicio" }
        ];

        // Validar campos comunes
        camposComunes.forEach(campo => {
            if (!campo.elemento.value || campo.elemento.value.trim() === '') {
                errores.push(`El campo "${campo.nombre}" es obligatorio`);
                camposConError.push(campo.elemento);
            }
        });

        // Campos adicionales solo para método 'sunat'
        if (tp_calculo_value === 'sunat') {
            const camposSunat = [
                { elemento: total_operacionEntregar, nombre: "Total operación" },
                { elemento: tm_detraccionEntregar, nombre: "TM detracción" },
                { elemento: config_vehiculoEntregar, nombre: "Configuración vehículo" },
                { elemento: ruta_origenEntregar, nombre: "Ruta origen" },
                { elemento: ruta_destinoEntregar, nombre: "Ruta destino" }
            ];

            camposSunat.forEach(campo => {
                if (!campo.elemento.value || campo.elemento.value.trim() === '') {
                    errores.push(`El campo "${campo.nombre}" es obligatorio`);
                    camposConError.push(campo.elemento);
                }
            });
        }

        // Marcar campos con error
        camposConError.forEach(campo => {
            campo.classList.add('is-invalid');
        });

        // Si hay errores
        if (errores.length > 0) {
            return mostrarErrores(errores);
        }

        // Limpiar clases de error si todo está bien
        limpiarErroresDetraccion();
        return true;
    }

    function mostrarErrores(errores) {
        // Puedes personalizar cómo mostrar los errores
        let mensaje = "Por favor complete los siguientes campos:\n\n" + errores.join("\n");

        // Usando SweetAlert2 (si lo tienes)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                html: errores.map(e => `<li>${e}</li>`).join(''),
                confirmButtonText: 'Entendido'
            });
        } else {
            // Usando alert nativo
            alert(mensaje);
        }

        return false;
    }

    function limpiarErroresDetraccion() {
        // Remover clases de error de todos los campos
        const todosCampos = [
            ubigeo_origen_dEntregar,
            origen_detraccionEntregar,
            ubigeo_destino_dEntregar,
            destino_detraccionEntregar,
            medio_pago_detraccionEntregar,
            detalle_detraccionEntregar,
            monto_detraccionEntregar,
            base_detraccionEntregar,
            v_ref_carga_efectivaEntregar,
            v_ref_carga_utilEntregar,
            v_ref_servicioEntregar,
            total_operacionEntregar,
            tm_detraccionEntregar,
            config_vehiculoEntregar,
            ruta_origenEntregar,
            ruta_destinoEntregar
        ];

        todosCampos.forEach(campo => {
            if (campo) {
                campo.classList.remove('is-invalid');
            }
        });
    }

    button_pagar.addEventListener("click", (event) => {
        event.preventDefault()
        let formData = new FormData()
        formData.set("medio_pagoEntregar", medio_pagoEntregar.value)
        formData.set("destino_entregar", destino_entregar.value)
        formData.set("obs_ventaEntregar", obs_ventaEntregar.value)
        formData.set("id_encomienda", document.querySelector(".btnVerDetalle.active").getAttribute("id-encomienda"))
        formData.set("id_venta", document.querySelector(".btnVerDetalle.active").getAttribute("id-venta"))
        formData.set("habilitar_generarComprobante", habilitarDivGenerarComprobante.checked)
        formData.set("type_generarComprobante", $('input[name="options_generarComprobante"]:checked').val())
        formData.set("tp_comprobanteEntregar", tp_comprobanteEntregar.value)
        formData.set("serie_ventaEntregar", serie_ventaEntregar.value)
        formData.set("remitenteEntregar", remitenteEntregar_id.value)
        formData.set("forma_pagoEntregar", forma_pagoEntregar.value)
        formData.set("tiempo_creditoEntregar", tiempo_creditoEntregar.value)
        formData.set("fecha_creditoEntregar", fecha_creditoEntregar.value)
        formData.set("monto_creditoEntregar", monto_creditoEntregar.value)
        formData.set("tp_operacion_venta", tp_operacion_ventaEntregar.value)
        formData.set("ubigeo_origen_d", ubigeo_origen_dEntregar.value)
        formData.set("origen_detraccion", origen_detraccionEntregar.value)
        formData.set("ubigeo_destino_d", ubigeo_destino_dEntregar.value)
        formData.set("destino_detraccion", destino_detraccionEntregar.value)
        formData.set("medio_pago_detraccion", medio_pago_detraccionEntregar.value)
        formData.set("detalle_detraccion", detalle_detraccionEntregar.value)
        formData.set("total_operacion", total_operacionEntregar.value)
        formData.set("tm_detraccion", tm_detraccionEntregar.value)
        formData.set("config_vehiculo", config_vehiculoEntregar.value)
        formData.set("monto_detraccion", monto_detraccionEntregar.value)
        formData.set("base_detraccion", base_detraccionEntregar.value)
        formData.set("ruta_origen", ruta_origenEntregar.value)
        formData.set("ruta_destino", ruta_destinoEntregar.value)
        formData.set("v_ref_carga_efectiva", v_ref_carga_efectivaEntregar.value)
        formData.set("v_ref_carga_util", v_ref_carga_utilEntregar.value)
        formData.set("v_ref_servicio", v_ref_servicioEntregar.value)

        if (tp_operacion_ventaEntregar.value == 2) {
            if (!validarCamposDetraccion()) {
                return false;
            }
        }

        if (tp_comprobanteE == 31 && habilitarDivGenerarComprobante.checked == false) {
            Swal.fire({
                icon: 'info',
                title: 'Importante...!',
                text: 'En caso de las guias de remision transportista tiene que generar un comprobante antes de hacer la entrega'
            })
            return;
        }

        Swal.fire({
            title: '¿Esta seguro que quiere realizar el pago?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Pagar!'
        }).then((result) => {
            if (result.isConfirmed) {
                let data_n = [formData.get("destino_entregar")]
                console.log(data_n)
                if ($('input[name="options_generarComprobante"]:checked').val() == "dataNueva") {
                    data_n.push(formData.get("remitenteEntregar"))
                }
                if (forma_pagoEntregar.value == 1) {
                    data_n.push(formData.get("medio_pagoEntregar"));
                }
                if (instance.Validate.validateData(data_n)) {
                    button_pagar.classList.add("d-none")
                    button_loadPagar.classList.remove("d-none")
                    fetch(instance._URL_ + 'encomienda/pagarEncomienda', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.status)
                            return response.json()
                        })
                        .then(data => {
                            if (data.success) {
                                button_entregar.classList.remove("d-none")
                                div_porPagar.classList.add("d-none")
                                div_generarNuevoComprobante.classList.add("d-none")
                                div_generarComprobante.classList.add("d-none")
                                section_opcionesEntregar.classList.add("d-none")
                                clearDivPagar()
                                instance.Toast.operacion_exitosa(data.message.message)
                                if (tp_comprobanteEntregar.value == 1 || tp_comprobanteEntregar.value == 3) {
                                    instance.Datatable.reloadTable(table_comprobante)
                                } else {
                                    instance.Datatable.reloadTable(table_notaVenta)
                                    instance.Datatable.reloadTable(table_guiasTransportista)
                                }
                                modal_printComprobante("¡Encomienda pagado con éxito!", data.message.links)
                            } else {
                                instance.Toast.operacion_erronea(data.message.message)
                            }
                            button_pagar.classList.remove("d-none")
                            button_loadPagar.classList.add("d-none")
                        })
                        .catch(error => {
                            instance.Toast.operacion_erronea(error.message)
                            button_pagar.classList.remove("d-none")
                            button_loadPagar.classList.add("d-none")
                        })
                } else {
                    instance.Toast.operacion_erronea("Rellene correctamente los campos")
                }
            }
        })
    })

    // Boton de registro de receptor de encomienda en caso sea VARIOS
    button_registrarR.addEventListener("click", (event) => {
        event.preventDefault()
        let formData = new FormData()
        formData.set("id_receptor", receptor_id.value)
        formData.set("id_encomienda", document.querySelector(".btnVerDetalle.active").getAttribute("id-encomienda"))
        const estadoVentaR = document.querySelector(".btnVerDetalle.active").getAttribute("estado-venta")
        Swal.fire({
            title: '¿Esta seguro que quiere realizar el registro?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Registrar!'
        }).then((result) => {
            if (result.isConfirmed) {
                if (instance.Validate.validateData([formData.get("id_receptor"), formData.get("id_encomienda")])) {
                    button_registrarR.classList.add("d-none")
                    button_loadRegistrarR.classList.remove("d-none")
                    fetch(instance._URL_ + 'encomienda/registrar_receptor', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.status)
                            return response.json()
                        })
                        .then(data => {
                            if (data.success) {
                                if (estadoVentaR == 'PAGO EN DESTINO') {
                                    div_porPagar.classList.remove("d-none")
                                    section_opcionesEntregar.classList.remove("d-none")
                                } else {
                                    div_porPagar.classList.add("d-none")
                                    button_entregar.classList.remove("d-none")
                                }
                                div_datosReceptor.classList.add("d-none")
                                instance.Toast.operacion_exitosa(data.message)

                                // div_generarNuevoComprobante.classList.add("d-none")
                                // div_generarComprobante.classList.add("d-none")
                            } else {
                                instance.Toast.operacion_erronea(data.message)
                            }
                            button_registrarR.classList.remove("d-none")
                            button_loadRegistrarR.classList.add("d-none")
                        })
                        .catch(error => {
                            instance.Toast.operacion_erronea(error.message)
                            button_registrarR.classList.remove("d-none")
                            button_loadRegistrarR.classList.add("d-none")
                        })
                } else {
                    instance.Toast.operacion_erronea("Rellene correctamente los campos")
                }
            }
        })
    })

    $("#habilitarDivGenerarComprobante").change(function (e) {
        habilitarDivGenerarComprobante.checked ? div_generarComprobante.classList.remove("d-none") : div_generarComprobante.classList.add("d-none")
        let total_e_op = parseFloat(total_operacionEntregar.value) || 0;
        if (habilitarDivGenerarComprobante.checked) {
            if ($('#forma_pagoEntregar option[value="2"]').length === 0) {
                $('#forma_pagoEntregar').append('<option value="2">CREDITO</option>');
            }
            if (total_e_op > 400) {
                if ($('#tp_operacion_ventaEntregar option[value="2"]').length === 0) {
                    $('#tp_operacion_ventaEntregar').append('<option value="2">Operación Sujeta a Detracción - Servicios de Transporte Carga</option>');
                }
            }
        } else {
            $('#forma_pagoEntregar option[value="2"]').remove();
            $('#tp_operacion_ventaEntregar option[value="2"]').remove();
        }
    });

    $("input[type=radio][name=options_generarComprobante]").change(function (e) {
        e.preventDefault();
        this.value == "dataOrigen" || this.value == 'dataDestino' ? div_generarNuevoComprobante.classList.add("d-none") : div_generarNuevoComprobante.classList.remove("d-none")
    });

    $("#tp_comprobanteEntregar").change(function (e) {
        e.preventDefault();
        let formData = new FormData();
        formData.set('tp_comprobante', tp_comprobanteEntregar.value)
        fetch(instance._URL_ + "encomienda/get_serieForTpComprobante", {
            method: "POST",
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status)
                return response.json()
            })
            .then(data => {
                serie_ventaEntregar.innerHTML = ""
                if (data && data.success) {
                    serie_ventaEntregar.insertAdjacentHTML('beforeend', `<option value="${data.message.id_serie}">${data.message.serie}</option>`)
                }
            })
    });

    const clearDivPagar = () => {
        $("#medio_pagoEntregar").val("").trigger("change")
        obs_ventaEntregar.value = ""
    }

    // Enbarcar
    let terminal_destinoEmbarcar = document.querySelector("#terminal_destinoEmbarcar")
    let programacion_destinoEmbarcar = document.querySelector("#programacion_destinoEmbarcar")
    let pro_destinoCrearGuia = document.querySelector("#pro_destinoCrearGuia")
    let id_encomiendaCrearGuia = document.querySelector("#id_encomiendaCrearGuia")
    let id_destinoCrearGuia = document.querySelector("#id_destinoCrearGuia")
    let btn_seleccionarTodoEncomienda = document.querySelector("#btn_seleccionarTodoEncomienda")
    let button_embarcar = document.querySelector("#button_embarcar")
    let button_crearguia = document.querySelector("#button_crearguia")
    let button_loadEmbarcar = document.querySelector("#button_loadEmbarcar")
    let button_loadCrearGuia = document.querySelector("#button_loadCrearGuia")
    instance.select.createSelect("#terminal_destinoEmbarcar", "#div_parentTerminalDestinoEmbarcar", "SELECCIONE")
    instance.select.createSelect("#programacion_destinoEmbarcar", "#div_parentProgramacionDestinoEmbarcar", "SELECCIONE")
    instance.select.createSelect("#pro_destinoCrearGuia", "#div_parentProDestinoCrearGuia", "SELECCIONE")

    const table_encomiendaEmbarcar = $('#table_encomiendaEmbarcar').DataTable({
        "ajax": {
            'url': instance._URL_ + 'encomienda/get_dataTableEncomiendaEmbarcar',
            'method': 'POST',
        },
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
                "data": "almacen",
                "width": "10%"
            },
            {
                "data": "numero",
                "width": "15%"
            },
            {
                "data": "fecha_emision",
                "width": "15%"
            },
            {
                "data": "estado",
                "width": "20%",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[row.estado]}">${row.estado}</span>`
                }
            },
            {
                "data": "estado_e",
                "width": "20%",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[row.estado_e]}">${row.estado_e}</span>`
                }
            },
            {
                "data": "terminal_destino",
                "width": "10%"
            },
            {
                "data": "e_domicilio",
                "width": "10%",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.TIPO_E[row.e_domicilio]}">${instance.CONSTS.TEXTO.TIPO_E[row.e_domicilio]}</span>`
                }
            },
            {
                "data": "productos",
                "width": "30%",
                render: (data, type, row) => {
                    if (!data || data.length === 0) return 'Sin productos';
                    return `
                    <div class="center-icon btnVerProd">
                        <i class="fa-solid fa-eye fa-lg" style="color: #74C0FC;" data-productos='${JSON.stringify(data)}'></i>
                    </div>`;
                }
            },
            {
                "data": "id_terminal_destino",
                "visible": false,
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
    })

    table_encomiendaEmbarcar
        .on('select', (e, dt, type, indexes) => {
            if (parseInt(table_encomiendaEmbarcar.rows({ selected: true }).count()) == 1) {
                button_embarcar.classList.remove("d-none")
                btn_seleccionarTodoEncomienda.textContent = "Deseleccionar todo"
            }
        })
        .on('deselect', (e, dt, type, indexes) => {
            if (parseInt(table_encomiendaEmbarcar.rows({ selected: true }).count()) == 0) {
                button_embarcar.classList.add("d-none")
                btn_seleccionarTodoEncomienda.textContent = "Seleccionar todo"
            }
        });

    $("#modal_embarcar").on("show.bs.modal", async (e) => {
        try {
            // 1. Inicializar DataTable search
            instance.Datatable.inputSearch(table_encomiendaEmbarcar, '.inputSearchEmbarcar');

            // 2. Resetear fechas a valores por defecto
            const fechaFin = document.getElementById("fecha_fin");
            const fechaInicio = document.getElementById("fecha_inicio_embarcar");
            const estadoFiltro = document.getElementById("estado_encomiendas");

            fechaFin.value = getFechaActual();
            fechaInicio.value = "";
            estadoFiltro.value = "TODOS";

            // 3. Obtener primera terminal disponible
            const $select = $("#terminal_destinoEmbarcar");
            const firstValue = $select.find("option:first").val();

            if (firstValue) {
                // 4. Cambiar a la primera terminal y esperar a que cargue todo
                $select.val(firstValue);
                await handleTerminalChange(firstValue);
            } else {
                // Si no hay terminales, limpiar tabla
                table_encomiendaEmbarcar.clear().draw();
            }

        } catch (error) {
            console.error("Error al abrir modal de embarque:", error);
            instance.Toast.operacion_erronea("Error al cargar datos de embarque");
        }
    });

    // Función helper para fecha actual
    function getFechaActual() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Manejador del hover en el ojo
    $('#table_encomiendaEmbarcar tbody').on('mouseenter', '.btnVerProd', function (e) {
        const productos = JSON.parse($(this).find('i').attr('data-productos'));
        const tooltip = document.getElementById('productosTooltip');

        // Construir el contenido
        let contenido = '';
        productos.forEach(producto => {
            contenido += `
                <div class="productos-tooltip-item">
                    <span class="producto-nombre">*${producto.producto}</span>
                </div>
            `;
        });

        tooltip.innerHTML = contenido;

        // Mejorar el posicionamiento
        const icon = $(this).find('i')[0];
        const iconRect = icon.getBoundingClientRect();
        const tableRect = document.getElementById('table_encomiendaEmbarcar').getBoundingClientRect();

        // Posicionar el tooltip
        tooltip.style.display = 'block';
        const tooltipRect = tooltip.getBoundingClientRect();

        const top = iconRect.top - (tooltipRect.height / 2) + (iconRect.height / 2);
        const left = iconRect.right + 5;

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
    });

    // Mantener el tooltip visible cuando el mouse está sobre él
    $('#productosTooltip').on('mouseenter', function () {
        $(this).show();
    }).on('mouseleave', function () {
        $(this).hide();
    });

    // Ocultar el tooltip cuando el mouse sale del ícono
    $('#table_encomiendaEmbarcar tbody').on('mouseleave', '.btnVerProd', function (e) {
        // Pequeño delay para permitir que el mouse llegue al tooltip
        setTimeout(() => {
            if (!$('#productosTooltip:hover').length) {
                $('#productosTooltip').hide();
            }
        }, 100);
    });

    $("#modal_embarcar").on("hidden.bs.modal", (e) => {
        button_embarcar.classList.add("d-none")
        button_loadEmbarcar.classList.add("d-none")
        btn_seleccionarTodoEncomienda.textContent = "Seleccionar todo"
        // Limpiar tabla de encomiendas
        table_encomiendaEmbarcar.rows().deselect();
        table_encomiendaEmbarcar.clear().draw();
        // Limpiar selects
        $("#terminal_destinoEmbarcar").val("1")
        // programacion_destinoEmbarcar.innerHTML = ""

        $("#fecha_inicio_embarcar").val("");  // Limpiar "Desde"
        $("#fecha_fin").val(getFechaActual());
    })


    /* =============== Inicio implementacion de guia transportista ================= */
    // $("#destino_terminal").change(function (e) {
    //     buscar_programaciones();
    // });
    /* =============== Fin implementacion de guia transportista ================= */

    const get_terminalEmbarcar = async () => {
        await fetch(instance._URL_ + "encomienda/get_terminalDestinoEmbarcar")
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    terminal_destinoEmbarcar.innerHTML = ""
                    Object.values(data.message).forEach((e, i, array) => {
                        terminal_destinoEmbarcar.insertAdjacentHTML('beforeend', `
                            <option value="${e.id_terminal}">${e.nombre}</option>
                        `)
                    })
                }
                $("#terminal_destinoEmbarcar").val($("#terminal_destinoEmbarcar").val()).trigger("change")
            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message)
            })
    }

    // NUEVA FUNCIÓN: Manejar cambio de terminal
    async function handleTerminalChange(terminalId) {
        try {
            // 1. Limpiar programación actual
            programacion_destinoEmbarcar.innerHTML = "";

            // 2. Si hay terminal seleccionada, buscar programaciones
            if (terminalId) {
                await buscarProgramaciones(terminalId);
            }

            // 3. Siempre buscar encomiendas con los filtros actuales
            await buscarEncomiendasConFiltros();

        } catch (error) {
            console.error("Error en cambio de terminal:", error);
            instance.Toast.operacion_erronea("Error al cambiar terminal");
        }
    }

    // NUEVA FUNCIÓN: Buscar programaciones
    async function buscarProgramaciones(terminalId) {
        return new Promise((resolve, reject) => {
            let formData = new FormData();
            formData.set("id_terminalDestino", terminalId);

            fetch(instance._URL_ + "encomienda/get_programacionEmbarcar", {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderProgramaciones(data.message);

                        // Si hay programaciones, seleccionar la primera
                        if (data.message.length > 0) {
                            const primeraProgramacion = data.message[0];
                            programacion_destinoEmbarcar.value = primeraProgramacion.id_programacion;

                            // Guardar datos adicionales como atributos
                            programacion_destinoEmbarcar.setAttribute("id-conductor", primeraProgramacion.id_conductor);
                            programacion_destinoEmbarcar.setAttribute("id-vehiculo", primeraProgramacion.id_vehiculo);
                        }
                    }
                    resolve(data);
                })
                .catch(error => {
                    console.error("Error buscando programaciones:", error);
                    reject(error);
                });
        });
    }

    // NUEVA FUNCIÓN: Renderizar programaciones
    function renderProgramaciones(programaciones) {
        programacion_destinoEmbarcar.innerHTML = "";

        programaciones.forEach((programacion) => {
            const rutasJSON = JSON.stringify(programacion.rutas || []);
            const destinosTodos = JSON.stringify([
                programacion.id_terminal_destino,
                ...(programacion.rutas ? programacion.rutas.map(r => r.id_terminal) : [])
            ]);

            const option = document.createElement("option");
            option.value = programacion.id_programacion;
            option.textContent = `${programacion.placa} - ${programacion.fecha_salida.split("-").reverse().join("-")} - ${programacion.hora_salida}`;

            option.dataset.final = programacion.id_terminal_destino;
            option.dataset.rutas = JSON.stringify(programacion.rutas || []);
            option.dataset.destinos = destinosTodos;

            // Atributos adicionales
            option.setAttribute("id-conductor", programacion.id_conductor || "");
            option.setAttribute("id-vehiculo", programacion.id_vehiculo || "");

            programacion_destinoEmbarcar.appendChild(option);
        });
    }

    // NUEVA FUNCIÓN: Buscar encomiendas con todos los filtros
    async function buscarEncomiendasConFiltros() {
        const filtros = obtenerFiltrosActuales();

        let formData = new FormData();
        formData.set("id_terminalDestino", terminal_destinoEmbarcar.value);
        formData.set("fecha_inicio", filtros.fecha_inicio);
        formData.set("fecha_fin", filtros.fecha_fin);
        formData.set("estado_encomienda", filtros.estado);

        // Si hay programación seleccionada, agregar destinos de la programación
        if (programacion_destinoEmbarcar.value) {
            const opt = programacion_destinoEmbarcar.options[programacion_destinoEmbarcar.selectedIndex];
            const destinos_programacion = opt ? JSON.parse(opt.dataset.destinos || "[]") : [];
            formData.set("destinos_programacion", JSON.stringify(destinos_programacion));
        }

        try {
            const response = await fetch(instance._URL_ + "encomienda/get_dataTableEncomiendaEmbarcar", {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(response.statusText || response.status);

            const data = await response.json();

            if (data.success && Array.isArray(data.data)) {
                table_encomiendaEmbarcar.clear().rows.add(data.data).draw();
            } else {
                instance.Toast.operacion_informativa('No se encontraron encomiendas con los filtros actuales');
                table_encomiendaEmbarcar.clear().draw();
            }

        } catch (error) {
            console.error("Error buscando encomiendas:", error);
            instance.Toast.operacion_erronea(`Error: ${error.message}`);
        }
    }

    // NUEVA FUNCIÓN: Obtener todos los filtros actuales
    function obtenerFiltrosActuales() {
        const estadoSelect = document.getElementById("estado_encomiendas");
        let estadoValue = estadoSelect.value;

        return {
            fecha_inicio: document.getElementById("fecha_inicio_embarcar").value || "",
            fecha_fin: document.getElementById("fecha_fin").value || getFechaActual(),
            estado: estadoValue // Ahora será "POR_PAGAR" en lugar de "PAGO EN DESTINO"
        };
    }

    $("#terminal_destinoEmbarcar").change(function (e) {
        const terminalId = $(this).val();
        handleTerminalChange(terminalId);
    });

    $("#fecha_inicio_embarcar, #fecha_fin, #estado_encomiendas").change(function () {
        buscarEncomiendasConFiltros();
    });

    // Evento para cambio de programación
    $("#programacion_destinoEmbarcar").change(function (e) {
        buscarEncomiendasConFiltros();
    });

    btn_seleccionarTodoEncomienda.addEventListener("click", (event) => {
        if (table_encomiendaEmbarcar.rows({ selected: true }).count() > 0) {
            table_encomiendaEmbarcar.rows().deselect();
            button_embarcar.classList.add("d-none")
            btn_seleccionarTodoEncomienda.textContent = "Seleccionar todo"
        } else {
            table_encomiendaEmbarcar.rows().select();
            btn_seleccionarTodoEncomienda.textContent = "Deseleccionar todo"
            if (parseInt(table_encomiendaEmbarcar.rows({ selected: true }).count()) >= 1) {
                button_embarcar.classList.remove("d-none")
            }
        }
    })

    button_embarcar.addEventListener("click", (event) => {
        let formData = new FormData();
        const rows = table_encomiendaEmbarcar
            .rows({ selected: true })
            .data()
            .toArray();
        const destinos = {};

        rows.forEach(e => {
            const idDestino = e.id_terminal_destino;

            if (!destinos[idDestino]) {
                destinos[idDestino] = [];
            }

            destinos[idDestino].push({
                id_encomienda: e.id_encomienda,
                id_dt_venta: e.id_dt_venta,
                id_venta: e.id_venta,
                tabla: e.tabla,
                e_domicilio: e.e_domicilio,
                estado: e.estado
            });
        });

        formData.set("destinos", JSON.stringify(destinos));
        formData.set("id_encomiendas", Object.values(table_encomiendaEmbarcar.rows({ selected: true }).data().toArray()).map((e) => e.id_encomienda));
        formData.set("id_programacion", programacion_destinoEmbarcar.value);
        formData.set("id_conductor", programacion_destinoEmbarcar.getAttribute("id-conductor"));
        formData.set("id_vehiculo", programacion_destinoEmbarcar.getAttribute("id-vehiculo"));
        formData.set("id_destino", terminal_destinoEmbarcar.value);

        Swal.fire({
            title: '¿Está seguro que desea embarcar las encomiendas seleccionadas?',
            html: `
                <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                    <input type="checkbox" id="Checkguia" style="transform: scale(1.2); margin: 0;">
                    <label for="Checkguia" style="margin: 0; font-size: 16px;">Crear la guía de remisión transportista?</label>
                </div>
            `,
            text: "¡No podrá revertir los cambios!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Embarcar!'
        }).then((result) => {
            if (result.isConfirmed) {
                let crearGuia = document.getElementById('Checkguia').checked ? "1" : "0";
                formData.set("crear_guia", crearGuia); // Guardamos el valor del checkbox

                if (instance.Validate.validateData([formData.get("id_programacion")])) {
                    button_embarcar.classList.add("d-none");
                    button_loadEmbarcar.classList.remove("d-none");
                    loader.classList.remove("d-none");
                    fetch(instance._URL_ + "encomienda/embarcar_encomiendas", {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.status());
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                instance.Toast.operacion_exitosa(data.message.message);
                                instance.Datatable.reloadTable(table_notaVenta);
                                instance.Datatable.reloadTable(table_comprobante);
                                instance.Datatable.reloadTable(table_historialE);
                                instance.Datatable.reloadTable(table_grupales);
                                instance.Datatable.reloadTable(table_guias);
                                $("#modal_embarcar").modal("toggle");
                                modal_printComprobante('¡Productos embarcados!', data.message.links);
                            } else {
                                instance.Toast.operacion_erronea(data.message.message);
                            }
                        })
                        .catch(error => instance.Toast.operacion_erronea(error.message))
                        .finally(() => {
                            button_embarcar.classList.remove("d-none");
                            button_loadEmbarcar.classList.add("d-none");
                            loader.classList.add("d-none");
                        });
                } else {
                    instance.Toast.operacion_erronea("Debe seleccionar un terminal y la programación.");
                }
            }
        });
    });

    button_crearguia.addEventListener("click", (event) => {
        event.preventDefault()
        let formData = new FormData();
        formData.set("id_programacion", pro_destinoCrearGuia.value);
        formData.set("id_encomienda", id_encomiendaCrearGuia.value);
        formData.set("id_destino", id_destinoCrearGuia.value)

        if (!instance.Validate.validateData([formData.get("id_programacion"), formData.get("id_encomienda")])) {
            instance.Toast.operacion_erronea("Rellene correctamente los campos");
            return;
        }
        button_crearguia.classList.add("d-none")
        button_loadCrearGuia.classList.remove("d-none")

        fetch(instance._URL_ + 'encomienda/ExCrear_guia', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message.message);
                    instance.Datatable.reloadTable(table_comprobante);
                    instance.Datatable.reloadTable(table_historialE);
                    instance.Datatable.reloadTable(table_guias);
                    modal_printComprobante('Encomienda embarcada!', data.message.links);
                    $("#modal_crear_guia").modal("toggle")
                } else {
                    instance.Toast.operacion_erronea(data.message.message);
                }

            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message)
            })
            .finally(() => {
                button_crearguia.classList.remove("d-none")
                button_loadCrearGuia.classList.add("d-none")
            });
    });

    // Desembarcar
    let e_domicilio = document.querySelector("#e_domicilio")
    let tableEncomiendaDesembarcar = document.querySelector("#tableEncomiendaDesembarcar")
    let placa_vehiculoDesembarcar = document.querySelector("#placa_vehiculoDesembarcar")
    let button_searchEncomiendaDesembarcar = document.querySelector("#button_searchEncomiendaDesembarcar")
    let button_desembarcar = document.querySelector("#button_desembarcar")
    let button_loadDesembarcar = document.querySelector("#button_loadDesembarcar")
    let btn_selectedTodoEncomiendaDesembarcar = document.querySelector("#btn_selectedTodoEncomiendaDesembarcar")
    instance.select.createSelect("#almacen_desembarcar", "#div_parentAlmacenDesembarcar")

    // Inicialización global (se hace solo una vez)
    window.ubigeoPartida = initUbigeoSelect("#ubigeoP");
    window.ubigeoLlegada = initUbigeoSelect("#ubigeoLle");

    // Selects de detracciones
    let ubigeoOrigenD = initUbigeoSelect("#ubigeo_origen_d");
    let ubigeoDestinoD = initUbigeoSelect("#ubigeo_destino_d");

    // Selects de detracciones entrega
    let ubigeoOrigenDE = initUbigeoSelect("#ubigeo_origen_dEntregar");
    let ubigeoDestinoDE = initUbigeoSelect("#ubigeo_destino_dEntregar");

    // Select de ruta destino ANEXO 2 
    let rutaOrigenData = null;
    let rutaDestinoData = null;
    let rutaOrigenDataE = null;
    let rutaDestinoDataE = null;

    let ruta_origen = initRutaAnexo2Select("#ruta_origen", function (data) {
        rutaOrigenData = data;
        calcular_detraccion_encomienda();
    });

    let ruta_origenE = initRutaAnexo2Select("#ruta_origenEntregar", function (data) {
        rutaOrigenDataE = data;
        calcular_detraccion_encomiendaEntregar();
    });

    let ruta_destino = initRutaAnexo2Select("#ruta_destino", function (data) {
        rutaDestinoData = data;
        calcular_detraccion_encomienda();
    });

    let ruta_destinoE = initRutaAnexo2Select("#ruta_destinoEntregar", function (data) {
        rutaDestinoDataE = data;
        calcular_detraccion_encomiendaEntregar();
    });

    // cuando se cambie el valor de ubigeoP y ubigeoLle y encomienda este en check cambiar el valor de ubigeo_origen_d y ubigeo_destino_d
    $("#ubigeoP").on("change", function () {
        if (document.getElementById('e_domicilio').checked) {
            setUbigeoValue(ubigeoOrigenD, this.value);
        }
    });

    $("#ubigeoLle").on("change", function () {
        if (document.getElementById('e_domicilio').checked) {
            setUbigeoValue(ubigeoDestinoD, this.value);
        }
    });

    // cuando p_partida y p_llegada cambien de valor y encomienda este en check cambiar el valor de origen_detraccion y destino_detraccion
    $("#p_partida").on("change", function () {
        if (document.getElementById('e_domicilio').checked) {
            origen_detraccion.value = this.value;
        }
    });

    $("#p_llegada").on("change", function () {
        if (document.getElementById('e_domicilio').checked) {
            destino_detraccion.value = this.value;
        }
    });

    function initUbigeoSelect(selectId) {
        let ts = new TomSelect(selectId, {
            valueField: "cod_ubigeo",
            labelField: "nombre",
            searchField: "nombre",
            placeholder: "Buscar ubigeo...",

            onInitialize: function () {
                const inputInterno = this.control_input;

                // Bloquear autofill
                const randomSuffix = Date.now() + "_" + Math.random().toString(36).substring(2);
                inputInterno.setAttribute("autocomplete", "off");
                inputInterno.setAttribute("autocorrect", "off");
                inputInterno.setAttribute("autocapitalize", "off");
                inputInterno.setAttribute("spellcheck", "false");
                inputInterno.setAttribute("data-form-type", "other");
                inputInterno.setAttribute("data-lpignore", "true");
                inputInterno.setAttribute("data-1p-ignore", "true");
                inputInterno.setAttribute("name", "no_autofill_" + randomSuffix);
                inputInterno.setAttribute("id", "no_autofill_" + randomSuffix);
                inputInterno.setAttribute("role", "presentation");

                inputInterno.addEventListener('change', function (e) {
                    if (e.isTrusted === false) {
                        e.stopImmediatePropagation();
                    }
                }, true);
            },

            load: function (query, callback) {
                if (!query.length) return callback();
                let FormD = new FormData();
                FormD.set("q", query);
                fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
                    method: "POST",
                    body: FormD
                })
                    .then(res => res.json())
                    .then(json => {
                        if (json.success) callback(json.items);
                        else callback();
                    })
                    .catch(() => callback());
            }
        });

        return ts;
    }

    // Función para asignar valor al ubigeo (modo edición)
    function setUbigeoValue(ts, codUbigeo) {
        if (!codUbigeo) {
            ts.clear(); // limpia si no hay valor
            return;
        }

        let FormD = new FormData();
        FormD.set("pre", codUbigeo);

        fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
            method: "POST",
            body: FormD
        })
            .then(res => res.json())
            .then(json => {
                if (json.success && json.items.length) {
                    let item = json.items[0];
                    ts.addOption(item);          // añade la opción
                    ts.setValue(item.cod_ubigeo); // selecciona el valor
                }
            });
    }

    function initRutaAnexo2Select(selector, onSelect) {
        return new TomSelect(selector, {
            valueField: "id",
            labelField: "nombre",
            searchField: "nombre",
            placeholder: "Buscar ruta...",
            maxItems: 1,
            closeAfterSelect: true,

            onItemAdd(value) {
                const selectedData = this.options[value];
                onSelect(selectedData);
                this.blur();
            },

            load(query, callback) {
                if (!query.length) return callback();

                let fd = new FormData();
                fd.set("q", query);

                fetch(instance._URL_ + "encomienda/buscar_ruta_anexo2", {
                    method: "POST",
                    body: fd
                })
                    .then(res => res.json())
                    .then(json => callback(json.success ? json.items : []))
                    .catch(() => callback([]));
            }
        });
    }

    button_searchEncomiendaDesembarcar.addEventListener("click", (event) => {
        event.preventDefault()
        if (instance.Validate.validateData([placa_vehiculoDesembarcar.value])) {
            document.querySelector("#table_encomiendaDesembarcar").closest("#div_tableEncomiendaDesembarcar").classList.remove("d-none")
            tableEncomiendaDesembarcar = set_tableEncomiendaDesembarcar()
            instance.Datatable.inputSearch(tableEncomiendaDesembarcar, '.inputSearchDesembarcar')
            tableEncomiendaDesembarcar
                .on('select', (e, dt, type, indexes) => {
                    let data_row = tableEncomiendaDesembarcar.rows(indexes).data()[0];
                    if (parseInt(tableEncomiendaDesembarcar.rows({ selected: true }).count()) == 1) {
                        if (data_row.id_terminal_destino == instance._ID_TERMINAL_SESION) {
                            button_desembarcar.classList.remove("d-none")
                        }
                        btn_selectedTodoEncomiendaDesembarcar.textContent = "Deseleccionar todo"
                    }
                })
                .on('deselect', (e, dt, type, indexes) => {
                    if (parseInt(tableEncomiendaDesembarcar.rows({ selected: true }).count()) == 0) {
                        button_desembarcar.classList.add("d-none")
                        btn_selectedTodoEncomiendaDesembarcar.textContent = "Seleccionar todo"
                    }
                });
        } else {
            instance.Toast.operacion_erronea("Rellene la placa")
        }
    })

    const set_tableEncomiendaDesembarcar = () => {
        return $('#table_encomiendaDesembarcar').DataTable({
            "ajax": {
                'url': instance._URL_ + 'encomienda/get_dataTableEncomiendaDesembarcar',
                'method': 'POST',
                data: {
                    placa: placa_vehiculoDesembarcar.value
                }
            },
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
                    "data": "almacen"
                },
                {
                    "data": "numero"
                },
                {
                    "data": "fecha_emision"
                },
                {
                    "data": "estado",
                    render: (data, type, row) => {
                        return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_ENVIO_ENCOMIENDA[row.estado]}">${row.estado}</span>`
                    }
                },
                {
                    "data": "estado_e",
                    render: (data, type, row) => {
                        return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ESTADO_VENTA_ENCOMIENDA[row.estado_e]}">${row.estado_e}</span>`
                    }
                },
                {
                    "data": "productos",
                    render: (data, type, row) => {
                        if (!data || data.length === 0) return 'Sin productos';
                        return `
                    <div class="center-icon btnVerProd">
                        <i class="fa-solid fa-eye fa-lg" style="color: #74C0FC;" data-productos='${JSON.stringify(data)}'></i>
                    </div>`;
                    }
                },
            ],
            "language": {
                url: './public/plugins/datatable/language/es_es.json',
            },
        })
    }

    // Manejador del hover en el ojo
    $('#table_encomiendaDesembarcar tbody').on('mouseenter', '.btnVerProd', function (e) {
        const productos = JSON.parse($(this).find('i').attr('data-productos'));
        const tooltip = document.getElementById('productosTooltip');

        // Construir el contenido
        let contenido = '';
        productos.forEach(producto => {
            contenido += `
                <div class="productos-tooltip-item">
                    <span class="producto-nombre">*${producto.producto}</span>
                </div>
            `;
        });

        tooltip.innerHTML = contenido;

        // Mejorar el posicionamiento
        const icon = $(this).find('i')[0];
        const iconRect = icon.getBoundingClientRect();
        const tableRect = document.getElementById('table_encomiendaDesembarcar').getBoundingClientRect();

        // Posicionar el tooltip
        tooltip.style.display = 'block';
        const tooltipRect = tooltip.getBoundingClientRect();

        const top = iconRect.top - (tooltipRect.height / 2) + (iconRect.height / 2);
        const left = iconRect.right + 5;

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
    });

    // Ocultar el tooltip cuando el mouse sale del ícono
    $('#table_encomiendaDesembarcar tbody').on('mouseleave', '.btnVerProd', function (e) {
        // Pequeño delay para permitir que el mouse llegue al tooltip
        setTimeout(() => {
            if (!$('#productosTooltip:hover').length) {
                $('#productosTooltip').hide();
            }
        }, 100);
    });


    $("#modal_desembarcar").on("hidden.bs.modal", (e) => {
        placa_vehiculoDesembarcar.value = ""
        if ($.fn.DataTable.isDataTable('#tableEncomiendaDesembarcar')) {
            instance.Datatable.reloadTable(tableEncomiendaDesembarcar)
        }
        document.querySelector("#table_encomiendaDesembarcar").closest("#div_tableEncomiendaDesembarcar").classList.add("d-none")
        button_desembarcar.classList.add("d-none")
        button_loadDesembarcar.classList.add("d-none")
        btn_selectedTodoEncomiendaDesembarcar.textContent = "Seleccionar todo"
    })

    $("#modal_crear_guia").on("hidden.bs.modal", (e) => {
        id_encomiendaCrearGuia.value = ""
        id_destinoCrearGuia.value = ""
        pro_destinoCrearGuia.innerHTML = ""
        $("#pro_destinoCrearGuia").val("").trigger("change")
        button_crearguia.classList.remove("d-none")
        button_loadCrearGuia.classList.add("d-none")
    })

    button_desembarcar.addEventListener("click", (event) => {
        let formData = new FormData()
        Swal.fire({
            title: '¿Esta seguro que desea desembarcar las encomiendas?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Desembarcar!'
        }).then((result) => {
            if (result.isConfirmed) {
                const filas_seleccionadas = tableEncomiendaDesembarcar
                    .rows({ selected: true })
                    .data()
                    .toArray();

                formData.set("id_programacion", filas_seleccionadas[0]["id_programacion"]);
                const encomiendas_seleccionadas = tableEncomiendaDesembarcar
                    .rows({ selected: true })
                    .data()
                    .toArray()
                    .map(e => ({
                        id_encomienda: e.id_encomienda,
                        id_venta: e.id_venta,
                        tabla: e.tabla
                    }));
                formData.set("encomiendas", JSON.stringify(encomiendas_seleccionadas));
                if (instance.Validate.validateData([placa_vehiculoDesembarcar.value])) {
                    fetch(instance._URL_ + "encomienda/desembarcar_encomiendas", {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.status())
                            return response.json()
                        })
                        .then(data => {
                            if (data.success) {
                                modal_printComprobante("¡Encomiendas Desembarcadas!", data.message.links)
                                instance.Toast.operacion_exitosa(data.message.message)
                                instance.Datatable.reloadTable(table_guiasTransportista)
                                instance.Datatable.reloadTable(tableEncomiendaDesembarcar)
                                instance.Datatable.reloadTable(table_historialD)

                                button_desembarcar.classList.add("d-none")
                                btn_selectedTodoEncomiendaDesembarcar.textContent = "Seleccionar todo"
                            } else {
                                instance.Toast.operacion_erronea(data.message.message)
                            }
                        })
                        .catch(error => instance.Toast.operacion_erronea(error.message))
                }
            }
        })
    })

    btn_selectedTodoEncomiendaDesembarcar.addEventListener("click", (event) => {
        if (tableEncomiendaDesembarcar.rows({ selected: true }).count() > 0) {
            tableEncomiendaDesembarcar.rows().deselect();
            button_desembarcar.classList.add("d-none")
            btn_selectedTodoEncomiendaDesembarcar.textContent = "Seleccionar todo"
        } else {
            tableEncomiendaDesembarcar.rows().select();
            btn_selectedTodoEncomiendaDesembarcar.textContent = "Deseleccionar todo"
            if (parseInt(tableEncomiendaDesembarcar.rows({ selected: true }).count()) >= 1) {
                button_desembarcar.classList.remove("d-none")
            }
        }
    })

    // Boton de edicion de encomiedna cuando es una nota de venta
    $('#table_notaVenta tbody').on('click', '.btnSmallEditRegis', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        id_venta.value = data.id_venta
        tp_comprobante.value = data.id_tp_comprobante
        tp_comprobante.disabled = true;
        serie_venta.value = data.id_serie
        serie_venta.disabled = true;
        fecha_salida.value = data.encomienda_fecha_salida
        pago.value = data.estado
        e_domicilio.checked = data.e_domicilio == 1 ? true : false
        e_domicilio.dispatchEvent(new Event("change"));
        remitente_id.value = data.id_remitente
        remitente.value = data.remitente_num_docu + ' - ' + data.remitente_nombres + ' ' + data.remitente_apellidos
        destinatario_id.value = data.id_destinatario
        destinatario.value = data.destinatario_num_docu + ' - ' + data.destinatario_nombres + ' ' + data.destinatario_apellidos
        if (e_domicilio.checked) {
            p_partida.value = data.dir_puntopartida
            setUbigeoValue(ubigeoPartida, data.ubi_partida);
            p_llegada.value = data.dir_puntollegada
            setUbigeoValue(ubigeoLlegada, data.ubi_llegada);
            linkP.value = data.link_partida
            linkLle.value = data.link_llegada
            pagante_id.value = data.pagador_flete
            pagante.value = data.pagante + ' - ' + data.pagante_nombres + ' ' + data.pagante_apellidos
        }
        // origen_terminal.value = data.origen_terminal
        selectizeDestinoTerminal.setValue(data.id_terminal_destino);
        pass.value = data.encomienda_pass
        almacen.value = data.id_almacen
        $('#medio_pago')[0].selectize.setValue(data.id_medio_pago);
        forma_pago.value = data.id_forma_pago
        referencia.value = data.referencia
        destino.value = data.id_caja_chica

        serieGR.value = data.guia_serie
        correlativoGR.value = data.guia_correlativo
        rucGR.value = data.guia_ruc
        tp_comprobante_e.value = data.tp_comprobante_ref;
        tp_comprobante_e.dispatchEvent(new Event('change'));
        serieEP.value = data.serie_ref
        correlativoEP.value = data.correlativo_ref
        rucEP.value = data.ruc_ref

        // Extrayendo a productos para la tabla
        $.ajax({
            type: "post",
            url: instance._URL_ + "encomienda/get_productosEncomienda",
            data: {
                id_encomienda: data.id_encomienda
            },
            success: function (response) {
                try {
                    let reply = JSON.parse(response)
                    if (reply.success) {
                        set_productos(reply.message)
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
    });

    // Boton ver links 
    $('#table_notaVenta tbody').on('click', '.btnSmallVerLinks', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        $("#link_p").val(data.link_partida)
        $("#link_lle").val(data.link_llegada)
    });

    $('#table_comprobante tbody').on('click', '.btnSmallVerLinks', function (e) {
        const data = table_comprobante.row($(this).parents()).data()
        $("#link_p").val(data.link_partida)
        $("#link_lle").val(data.link_llegada)
    });

    const set_productos = (data) => {
        let tbody = table_productos.getElementsByTagName("tbody")[0]
        Object.values(data).forEach((e, i, a) => {
            tbody.insertAdjacentHTML('afterbegin', `
            <tr>
             <td class="d-none">
                <input type="hidden" name="id_producto[]"         value="${e.id_ctg_encomienda}">
                <input type="hidden" name="obs_producto[]"         value="${e.obs}">
                <input type="hidden" name="unid_medida[]"           value="${e.unidad_medida}">
            </td>
            <td><label class="cantidad_producto">${e.cantidad}</label></td>
            <td><label class="unidad_m">${e.unid_medida}</label></td>
            <td>
              <label class="ctg_producto" id-ctg-producto="${e.id_ctg_encomienda}">
                ${e.descripcion}
              </label>
            </td>
            <td><label class="precio_producto">${e.precio}</label></td>
            <td>
              <label class="precio_x_kg">
                ${e.precio_kg}
              </label>
            </td>
            <td><label class="peso">${e.peso}</label></td>
            <td><label class="subtotal_producto">${e.precio}</label></td>
            <td>
              <button type="button" class="btn button_deleteItem p-0 pt-2">
                <i class="bi bi-trash text-danger fs-5"></i>
              </button>
            </td>
          </tr>
        `)
        })
        const precios = Array.from(tbody.querySelectorAll("tr .subtotal_producto"))
            .map(e => parseFloat(e.textContent));
        const pesos = Array.from(tbody.querySelectorAll("tr .peso"))
            .map(e => parseFloat(e.textContent));
        tbody.querySelector("#costo_total").textContent = precios
            .reduce((a, b) => a + b, 0).toFixed(2);
        monto_credito.value = precios
            .reduce((a, b) => a + b, 0).toFixed(2);
        tbody.querySelector("#peso_total").textContent = pesos
            .reduce((a, b) => a + b, 0).toFixed(2);
    }

    $('#table_notaVenta tbody').on('click', '.btnSmallConvertirB', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        Swal.fire({
            title: '¿Convertir a boleta?',
            text: 'Esta nota de venta se convertirá en boleta y su monto quedará en 0. No podrá revertir esta acción.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Convertir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                loader.classList.remove('d-none');
                id_venta.value = data.id_venta
                let formData = new FormData()
                formData.set('id_venta', id_venta.value)
                formData.set('tp_comprobante', 3)
                fetch(instance._URL_ + 'encomienda/convert_NXC', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Error: ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Datatable.reloadTable(table_comprobante)
                            instance.Datatable.reloadTable(table_notaVenta)
                            modal_printComprobante(data.message.message, data.message.links)
                        } else {
                            instance.Toast.operacion_erronea(data.message.message)
                        }

                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        loader.classList.add('d-none');
                    });
            }
        });
    });

    $('#table_notaVenta tbody').on('click', '.btnSmallConvertirF', function (e) {
        const data = table_notaVenta.row($(this).parents()).data()
        Swal.fire({
            title: '¿Convertir a factura?',
            text: 'Esta nota de venta se convertirá en factura y su monto quedará en 0. No podrá revertir esta acción.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Convertir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                loader.classList.remove('d-none');
                id_venta.value = data.id_venta
                let formData = new FormData()
                formData.set('id_venta', id_venta.value)
                formData.set('tp_comprobante', 1)
                fetch(instance._URL_ + 'encomienda/convert_NXC', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Error: ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Datatable.reloadTable(table_comprobante)
                            instance.Datatable.reloadTable(table_notaVenta)
                            modal_printComprobante(data.message.message, data.message.links)
                        } else {
                            instance.Toast.operacion_erronea(data.message.message)
                        }

                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        loader.classList.add('d-none');
                    });
            }
        });
    });

    $('#table_comprobante tbody').on('click', '.btnCrearGuiaT', function (e) {
        const data = table_comprobante.row($(this).parents()).data()

        id_encomiendaCrearGuia.value = data.id_encomienda
        id_destinoCrearGuia.value = data.id_terminal_destino
        let formData = new FormData()
        formData.set("id_terminalDestino", data.id_terminal_destino)
        fetch(instance._URL_ + "encomienda/get_programacionEmbarcar", {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    Object.values(data.message).forEach((e, i, array) => {
                        pro_destinoCrearGuia.insertAdjacentHTML('beforeend', `
                            <option value="${e.id_programacion}" id-conductor="${e.id_conductor}" id-vehiculo="${e.id_vehiculo}">${e.placa} - ${e.fecha_salida.split("-").reverse().join("-")} - ${e.hora_salida}</option>
                        `)
                    })
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))

        $('#modal_crear_guia').modal('show')
    });

    // Metodos de calculo de detracciones
    let isCalculatingDetraccion = false;

    function calcular_detraccion_encomienda() {
        if (isCalculatingDetraccion) {
            return;
        }

        isCalculatingDetraccion = true;

        try {
            let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
            let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;


            // ===== MÉTODO DIRECTO 4% =====
            if (tp_calculo_v === 'directo') {

                let precio_valor = document.getElementById('costo_total')
                    .textContent
                    .replace('S/', '')
                    .trim();

                precio_valor = parseFloat(precio_valor);

                $("#v_ref_carga_efectiva").val(precio_valor.toFixed(2));
                $("#v_ref_carga_util").val(precio_valor.toFixed(2));
                $("#v_ref_servicio").val(precio_valor.toFixed(2));

                let m_detraccion = precio_valor * 0.04;

                $("#base_detraccion").val(precio_valor.toFixed(2));
                $("#monto_detraccion").val(m_detraccion.toFixed(2));

                return;
            }

            // ===== MÉTODO SUNAT =====
            if (tp_calculo_v === 'sunat') {
                // Validar que existan los datos necesarios
                if (!rutaDestinoData || !Number.isFinite(rutaDestinoData.id)) {
                    return;
                }

                // VALORES GENERALES
                let v_ref_carga_ef = 0.00;
                let v_ref_carga_utilN = 0.00;
                let v_x_TM = 0.00;

                // Calcular valor por TM
                if (ruta_origen_select.value == 0) {
                    v_x_TM = parseFloat(rutaDestinoData.valor_por_tm) || 0.00;
                } else {
                    // Datos de validacion
                    let id_ruta_destino = rutaDestinoData.ruta_anexo2_id;
                    let id_ruta_origen = rutaOrigenData.ruta_anexo2_id;
                    let misma_tabla = id_ruta_origen == id_ruta_destino ? true : false;
                    let v_x_TM_origen = parseFloat(rutaOrigenData.valor_por_tm) || 0.00;
                    let v_x_TM_destino = parseFloat(rutaDestinoData.valor_por_tm) || 0.00;

                    if (misma_tabla) {
                        const minuendo = Math.max(v_x_TM_origen, v_x_TM_destino);
                        const sustraendo = Math.min(v_x_TM_origen, v_x_TM_destino);
                        v_x_TM = minuendo - sustraendo;
                    } else {
                        v_x_TM = v_x_TM_origen + v_x_TM_destino;
                    }
                }

                let importe_operacion = parseFloat($("#total_operacion").val()) || 0.00;
                let peso_total = parseFloat($("#tm_detraccion").val()) || 0.00;

                let capacidad_carga_UN = parseFloat(
                    config_vehiculo.options[config_vehiculo.selectedIndex]
                        .dataset.cargaUtilTm
                ) || 0.00;

                /* CALCULO VALOR REFERENCIAL EN FUNCION DE LA CARGA EFECTIVA */
                v_ref_carga_ef = v_x_TM * peso_total;
                $("#v_ref_carga_efectiva").val(v_ref_carga_ef.toFixed(2));

                /* CALCULO VALOR REFERENCIAL EN FUNCION A LA CARGA UTIL NOMINAL */
                v_ref_carga_utilN = 0.7 * (v_x_TM * capacidad_carga_UN);
                $("#v_ref_carga_util").val(v_ref_carga_utilN.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR */
                let valor_referencial_final = Math.max(v_ref_carga_ef, v_ref_carga_utilN);
                $("#v_ref_servicio").val(valor_referencial_final.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR VS COSTO DE SERVICIO */
                let monto_calculo = Math.max(valor_referencial_final, importe_operacion);
                let monto_detrac = 0.04 * monto_calculo;

                $("#base_detraccion").val(monto_calculo.toFixed(2));
                $("#monto_detraccion").val(monto_detrac.toFixed(2));

            }

        } catch (error) {
            console.error("Error al calcular detracción:", error);
        } finally {
            isCalculatingDetraccion = false;
        }
    }

    // Metodos de calculo de detracciones
    let isCalculatingDetraccionEntregar = false;

    function calcular_detraccion_encomiendaEntregar() {
        if (isCalculatingDetraccionEntregar) {
            return;
        }

        isCalculatingDetraccionEntregar = true;

        try {
            let tp_calculo_el = document.querySelector("input[name='metodo_detraccionEntregar']:checked");
            let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;

            // ===== MÉTODO DIRECTO 4% =====
            if (tp_calculo_v === 'directo') {

                let precio_valor = document.getElementById('total_operacionEntregar')
                    .value
                    .trim();

                precio_valor = parseFloat(precio_valor);

                $("#v_ref_carga_efectivaEntregar").val(precio_valor.toFixed(2));
                $("#v_ref_carga_utilEntregar").val(precio_valor.toFixed(2));
                $("#v_ref_servicioEntregar").val(precio_valor.toFixed(2));

                let m_detraccion = precio_valor * 0.04;

                $("#base_detraccionEntregar").val(precio_valor.toFixed(2));
                $("#monto_detraccionEntregar").val(m_detraccion.toFixed(2));

                return;
            }

            // ===== MÉTODO SUNAT =====
            if (tp_calculo_v === 'sunat') {
                // Validar que existan los datos necesarios
                if (!rutaDestinoDataE || !Number.isFinite(rutaDestinoDataE.id)) {
                    return;
                }

                // VALORES GENERALES
                let v_ref_carga_ef = 0.00;
                let v_ref_carga_utilN = 0.00;
                let v_x_TM = 0.00;

                // Calcular valor por TM
                if (ruta_origen_selectE.value == 0) {
                    v_x_TM = parseFloat(rutaDestinoDataE.valor_por_tm) || 0.00;
                } else {
                    // Datos de validacion
                    let id_ruta_destino = rutaDestinoDataE.ruta_anexo2_id;
                    let id_ruta_origen = rutaOrigenDataE.ruta_anexo2_id;
                    let misma_tabla = id_ruta_origen == id_ruta_destino ? true : false;
                    let v_x_TM_origen = parseFloat(rutaOrigenDataE.valor_por_tm) || 0.00;
                    let v_x_TM_destino = parseFloat(rutaDestinoDataE.valor_por_tm) || 0.00;

                    if (misma_tabla) {
                        const minuendo = Math.max(v_x_TM_origen, v_x_TM_destino);
                        const sustraendo = Math.min(v_x_TM_origen, v_x_TM_destino);
                        v_x_TM = minuendo - sustraendo;
                    } else {
                        v_x_TM = v_x_TM_origen + v_x_TM_destino;
                    }
                }

                let importe_operacion = parseFloat($("#total_operacionEntregar").val()) || 0.00;
                let peso_total = parseFloat($("#tm_detraccionEntregar").val()) || 0.00;

                let capacidad_carga_UN = parseFloat(config_vehiculoEntregar.options[config_vehiculoEntregar.selectedIndex].dataset.cargaUtilTm) || 0.00;
                /* CALCULO VALOR REFERENCIAL EN FUNCION DE LA CARGA EFECTIVA */
                v_ref_carga_ef = v_x_TM * peso_total;
                $("#v_ref_carga_efectivaEntregar").val(v_ref_carga_ef.toFixed(2));

                /* CALCULO VALOR REFERENCIAL EN FUNCION A LA CARGA UTIL NOMINAL */
                v_ref_carga_utilN = 0.7 * (v_x_TM * capacidad_carga_UN);
                $("#v_ref_carga_utilEntregar").val(v_ref_carga_utilN.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR */
                let valor_referencial_final = Math.max(v_ref_carga_ef, v_ref_carga_utilN);
                $("#v_ref_servicioEntregar").val(valor_referencial_final.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR VS COSTO DE SERVICIO */
                let monto_calculo = Math.max(valor_referencial_final, importe_operacion);
                let monto_detrac = 0.04 * monto_calculo;

                $("#base_detraccionEntregar").val(monto_calculo.toFixed(2));
                $("#monto_detraccionEntregar").val(monto_detrac.toFixed(2));

            }

        } catch (error) {
            console.error("Error al calcular detracción:", error);
        } finally {
            isCalculatingDetraccionEntregar = false;
        }
    }

    $("#total_operacion, #tm_detraccion").on("change", function () {
        calcular_detraccion_encomienda();
    });

    $("#config_vehiculo").on("change", function () {
        calcular_detraccion_encomienda();
    });

    $("#total_operacionEntregar, #tm_detraccionEntregar").on("change", function () {
        calcular_detraccion_encomiendaEntregar();
    });

    $("#config_vehiculoEntregar").on("change", function () {
        calcular_detraccion_encomiendaEntregar();
    });


    let observer = null;

    function iniciarObserver() {
        const pesoTd = document.getElementById('peso_total');
        const costoTd = document.getElementById('costo_total');

        // Si ya existe un observer, desconectarlo primero
        if (observer) {
            observer.disconnect();
        }

        // Verificar que los elementos existen
        if (!pesoTd || !costoTd) {
            console.warn('Elementos no encontrados');
            return;
        }

        observer = new MutationObserver(() => {
            let pesoTotal = parseFloat(
                pesoTd.textContent.replace(/[^\d.]/g, '')
            ) || 0;

            let costoTotal = parseFloat(
                costoTd.textContent.replace('S/', '').replace(/[^\d.]/g, '')
            ) || 0;

            tm_detraccion.value = pesoTotal > 0
                ? (pesoTotal / 1000).toFixed(3)
                : '0.000';

            total_operacion.value = costoTotal;

            if ($("#tp_operacion_venta").val() == 2) {
                calcular_detraccion_encomienda();
            }
        });

        observer.observe(pesoTd, {
            childList: true,
            characterData: true,
            subtree: true
        });

        observer.observe(costoTd, {
            childList: true,
            characterData: true,
            subtree: true
        });
    }

    /* Detracciones directas 4% */
    $("input[name='metodo_detraccion']").on("change", function () {
        let valorSeleccionado = $(this).val();

        if (valorSeleccionado === "sunat") {
            $("#div_calculos_detraccion").removeClass("d-none");

            // Limpiar solo los campos de cálculo
            $("#v_ref_carga_efectiva").val('');
            $("#v_ref_carga_util").val('');
            $("#v_ref_servicio").val('');
            $("#base_detraccion").val('');
            $("#monto_detraccion").val('');

        } else if (valorSeleccionado === "directo") {
            $("#div_calculos_detraccion").addClass("d-none");

            // Limpiar campos de SUNAT
            $("#v_ref_carga_efectiva").val('');
            $("#v_ref_carga_util").val('');
            $("#v_ref_servicio").val('');

            // Calcular con método directo
            calcular_detraccion_encomienda();
        }
    });

    $("input[name='metodo_detraccionEntregar']").on("change", function () {
        let valorSeleccionado = $(this).val();

        if (valorSeleccionado === "sunat") {
            $("#div_calculos_detraccionEntregar").removeClass("d-none");
            // Limpiar solo los campos de cálculo
            $("#v_ref_carga_efectivaEntregar").val('');
            $("#v_ref_carga_utilEntregar").val('');
            $("#v_ref_servicioEntregar").val('');
            $("#base_detraccionEntregar").val('');
            $("#monto_detraccionEntregar").val('');
            calcular_detraccion_encomiendaEntregar();

        } else if (valorSeleccionado === "directo") {
            $("#div_calculos_detraccionEntregar").addClass("d-none");

            // Limpiar campos de SUNAT
            $("#v_ref_carga_efectivaEntregar").val('');
            $("#v_ref_carga_utilEntregar").val('');
            $("#v_ref_servicioEntregar").val('');

            // Calcular con método directo
            calcular_detraccion_encomiendaEntregar();
        }
    });

    $("#tipo_precio").on("change", function () {
        let tp_precio = $(this).val();

        switch (tp_precio) {
            case 'unidad':
                $("#div_precio_peso").addClass("d-none");
                $("#div_precio_total").addClass("d-none");
                precio_unitario.readOnly = false;
                p_total.value = 0.00;
                $("#titulo_peso").text("Peso Unid. (kg)");
                break;
            case 'peso':
                $("#div_precio_peso").removeClass("d-none");
                $("#div_precio_total").addClass("d-none");
                precio_unitario.value = 0.00;
                p_total.value = 0.00;
                precio_unitario.readOnly = true;
                $("#titulo_peso").text("Peso Unid. (kg)");
                break;
            case 'peso_total':
                $("#div_precio_peso").removeClass("d-none");
                $("#div_precio_total").addClass("d-none");
                precio_unitario.value = 0.00;
                p_total.value = 0.00;
                precio_unitario.readOnly = true
                $("#titulo_peso").text("Peso Total (kg)");
                break;
            case 'precio_total':
                $("#div_precio_peso").addClass("d-none");
                precio_unitario.value = 0.00;
                precio_unitario.readOnly = true;
                $("#div_precio_total").removeClass("d-none");
            default:
                return;
        }
    });

    $('#p_total').on('input', function () {
        if (tipo_precio.value == 'precio_total') {
            calcular_p_u_x_precio_total();
        }
    });

    $('#cantidad').on('input', function () {
        if (this.value == '0') {
            this.value = '1';
            return;
        }

        if (tipo_precio.value == 'peso' || tipo_precio.value == 'peso_total') {
            calcular_precio_x_peso();
        } else if (tipo_precio.value == 'precio_total') {
            calcular_p_u_x_precio_total();
        }
    });

    $('#peso').on('input', function () {
        if (tipo_precio.value == 'peso' || tipo_precio.value == 'peso_total') {
            calcular_precio_x_peso();
        }
    });

    $('#precio_kg').on('input', function () {
        if (tipo_precio.value == 'peso' || tipo_precio.value == 'peso_total') {
            calcular_precio_x_peso();
        }
    });

    function calcular_precio_x_peso() {
        let cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
        let precioKG = parseFloat(document.getElementById('precio_kg').value) || 0;
        let peso = parseFloat(document.getElementById('peso').value) || 0;

        if (cantidad === 0) {
            precio_unitario.value = '0.00';
            return;
        }

        let precio_unit_kg = precioKG * peso;

        precio_unitario.value = precio_unit_kg.toFixed(2);
    }

    function calcular_p_u_x_precio_total() {
        let cant = parseFloat(document.getElementById('cantidad').value) || 0;
        let pre_to = parseFloat(document.getElementById('p_total').value) || 0;
        if (cantidad.value === '') {
            precio_unitario.value = '0.00';
            return;
        }
        precio_unitario.value = Number(pre_to / cant).toFixed(2);
    }

    // ============================================
    // FILTROS - CONFIGURACIÓN POR DEFECTO (VACÍO)
    // ============================================

    // Función para limpiar filtros de una tabla específica
    window.limpiarFiltros = function (tabla) {
        // Limpiar campos de fecha
        $(`#fecha_inicio_${tabla}`).val('');
        $(`#fecha_fin_${tabla}`).val('');

        // Limpiar selects según la tabla
        switch (tabla) {
            case 'comprobante':
                $('#tipo_comprobante').val('');
                $('#origen_comprobante').val('');
                $('#destino_comprobante').val('');
                $('#estado_envio_comprobante').val('');
                $('#estado_venta_comprobante').val('');
                break;
            case 'notaventa':
                $('#origen_notaventa').val('');
                $('#destino_notaventa').val('');
                $('#estado_envio_notaventa').val('');
                $('#estado_venta_notaventa').val('');
                break;
            case 'guiast':
                $('#origen_guiast').val('');
                $('#destino_guiast').val('');
                $('#estado_envio_guiast').val('');
                $('#estado_venta_guiast').val('');
                break;
            case 'guiasrt':
                $('#partida_guiasrt').val('');
                $('#destino_guiasrt').val('');
                $('#vehiculo_guiasrt').val('');
                break;
            case 'embarcaciones':
                $('#origen_embarcaciones').val('');
                $('#destino_embarcaciones').val('');
                $('#vehiculo_embarcaciones').val('');
                break;
            case 'embgrupal':
                $('#origen_embgrupal').val('');
                $('#destino_embgrupal').val('');
                $('#vehiculo_embgrupal').val('');
                break;
            case 'desembarques':
                $('#origen_desembarques').val('');
                $('#destino_desembarques').val('');
                $('#vehiculo_desembarques').val('');
                break;
        }

        

        // Recargar la tabla correspondiente
        switch (tabla) {
            case 'comprobante':
                table_comprobante.ajax.reload();
                break;
            case 'notaventa':
                table_notaVenta.ajax.reload();
                break;
            case 'guiast':
                table_guiasTransportista.ajax.reload();
                break;
            case 'guiasrt':
                table_guias.ajax.reload();
                break;
            case 'embarcaciones':
                table_historialE.ajax.reload();
                break;
            case 'embgrupal':
                table_grupales.ajax.reload();
                break;
            case 'desembarques':
                table_historialD.ajax.reload();
                break;
        }
    };

    // NO inicializar fechas por defecto - todos los filtros comienzan vacíos
    $(document).ready(function () {
        // Asegurar que todos los campos de fecha estén vacíos al cargar
        $('#fecha_inicio_comprobante, #fecha_fin_comprobante, ' +
            '#fecha_inicio_notaventa, #fecha_fin_notaventa, ' +
            '#fecha_inicio_guiast, #fecha_fin_guiast, ' +
            '#fecha_inicio_embarcaciones, #fecha_fin_embarcaciones, ' +
            '#fecha_inicio_embgrupal, #fecha_fin_embgrupal, ' +
            '#fecha_inicio_desembarques, #fecha_fin_desembarques, ' +
            '#fecha_inicio_guiasrt, #fecha_fin_guiasrt').val('');


        // Inicializar otros componentes si es necesario
        $("#tiempo_credito").trigger('change');
        $("#tiempo_creditoEntregar").trigger('change');
        $("#forma_pago").trigger('change');

    });

    // FUNCIONES PARA FILTROS Y EXPORTACIÓN EXCEL

    // ===== FACTURA-BOLETA =====
    window.aplicarFiltrosComprobante = function () {
        table_comprobante.ajax.reload();
    };

    window.exportarExcelComprobante = function () {
        const fecha_inicio = $('#fecha_inicio_comprobante').val();
        const fecha_fin = $('#fecha_fin_comprobante').val();
        const tipo = $('#tipo_comprobante').val();
        const origen = $('#origen_comprobante').val();  
        const destino = $('#destino_comprobante').val();
        const estado_envio = $('#estado_envio_comprobante').val();
        const estado_venta = $('#estado_venta_comprobante').val();
        const search = table_comprobante.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_comprobante?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (tipo) params.push('tipo=' + tipo);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (estado_envio) params.push('estado_envio=' + estado_envio);
        if (estado_venta) params.push('estado_venta=' + estado_venta);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== NOTAS DE VENTA =====
    window.aplicarFiltrosNotaVenta = function () {
        table_notaVenta.ajax.reload();
    };

    window.exportarExcelNotaVenta = function () {
        const fecha_inicio = $('#fecha_inicio_notaventa').val();
        const fecha_fin = $('#fecha_fin_notaventa').val();
        const origen = $('#origen_notaventa').val();
        const destino = $('#destino_notaventa').val();
        const estado_envio = $('#estado_envio_notaventa').val();
        const estado_venta = $('#estado_venta_notaventa').val();
        const search = table_notaVenta.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_notaventa?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (estado_envio) params.push('estado_envio=' + estado_envio);
        if (estado_venta) params.push('estado_venta=' + estado_venta);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== GUIAS T. =====
    window.aplicarFiltrosGuiasT = function () {
        table_guiasTransportista.ajax.reload();
    };

    window.exportarExcelGuiasT = function () {
        const fecha_inicio = $('#fecha_inicio_guiast').val();
        const fecha_fin = $('#fecha_fin_guiast').val();
        const origen = $('#origen_guiast').val();
        const destino = $('#destino_guiast').val();
        const estado_envio = $('#estado_envio_guiast').val();
        const estado_venta = $('#estado_venta_guiast').val();
        const search = table_guiasTransportista.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_guiasT?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (estado_envio) params.push('estado_envio=' + estado_envio);
        if (estado_venta) params.push('estado_venta=' + estado_venta);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== HISTORIAL DE EMBARCACIONES =====
    window.aplicarFiltrosEmbarcaciones = function () {
        table_historialE.ajax.reload();
    };

    window.exportarExcelEmbarcaciones = function () {
        const fecha_inicio = $('#fecha_inicio_embarcaciones').val();
        const fecha_fin = $('#fecha_fin_embarcaciones').val();
        const origen = $('#origen_embarcaciones').val();
        const destino = $('#destino_embarcaciones').val();
        const id_vehiculo = $('#vehiculo_embarcaciones').val();
        const search = table_historialE.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_embarcaciones?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (id_vehiculo) params.push('id_vehiculo=' + id_vehiculo);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== EMBARCACIONES GRUPALES =====
    window.aplicarFiltrosEmbGrupal = function () {
        table_grupales.ajax.reload();
    };

    window.exportarExcelEmbGrupal = function () {
        const fecha_inicio = $('#fecha_inicio_embgrupal').val();
        const fecha_fin = $('#fecha_fin_embgrupal').val();
        const origen = $('#origen_embgrupal').val();
        const destino = $('#destino_embgrupal').val();
        const id_vehiculo = $('#vehiculo_embgrupal').val();
        const search = table_grupales.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_embarcacionGrupal?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (id_vehiculo) params.push('id_vehiculo=' + id_vehiculo);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== HISTORIAL DE DESEMBARQUES =====
    window.aplicarFiltrosDesembarques = function () {
        table_historialD.ajax.reload();
    };

    window.exportarExcelDesembarques = function () {
        const fecha_inicio = $('#fecha_inicio_desembarques').val();
        const fecha_fin = $('#fecha_fin_desembarques').val();
        const origen = $('#origen_desembarques').val();
        const destino = $('#destino_desembarques').val();
        const id_vehiculo = $('#vehiculo_desembarques').val();
        const search = table_historialD.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_desembarque?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (origen) params.push('origen=' + origen);
        if (destino) params.push('destino=' + destino);
        if (id_vehiculo) params.push('id_vehiculo=' + id_vehiculo);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // ===== G. R. - TRANSPORTISTA =====
    window.aplicarFiltrosGuiasRT = function () {
        table_guias.ajax.reload();
    };

    window.exportarExcelGuiasRT = function () {
        const fecha_inicio = $('#fecha_inicio_guiasrt').val();
        const fecha_fin = $('#fecha_fin_guiasrt').val();
        const partida = $('#partida_guiasrt').val();
        const destino = $('#destino_guiasrt').val();
        const vehiculo_placa  = $('#vehiculo_guiasrt').val();
        const search = table_guias.search();

        let url = instance._URL_ + 'encomienda/exportar_excel_guiasRT?';
        const params = [];
        if (fecha_inicio) params.push('fecha_inicio=' + fecha_inicio);
        if (fecha_fin) params.push('fecha_fin=' + fecha_fin);
        if (partida) params.push('partida=' + partida);
        if (destino) params.push('destino=' + destino);
        if (vehiculo_placa) params.push('vehiculo_placa=' + vehiculo_placa);
        if (search) {params.push('search=' + search);}

        window.open(url + params.join('&'), '_blank');
    };

    // Función para obtener fecha actual en formato YYYY-MM-DD
    window.getFechaActual = function () {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    //===================APARTADO DE EDICION DE EMBARQUES===============================
    let btnEnEmbarcados = document.getElementById("btnEnEmbarcados");
    let tabla_edicion_embarcacion = $('#table_EnEmbarcados').DataTable({
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        select: { style: 'multi' },
        ordering: false,
        columns: [
            {
                title: 'Comprobante',
                data: null,
                render: (data, type, row) =>
                    `${row.venta_serie}-${String(row.venta_correlativo).padStart(8, '0')}`
            },
            {
                title: 'Remitente',
                data: null,
                render: (data, type, row) =>
                    `${row.remitente_nombres} ${row.remitente_apellidos}`
            },
            {
                title: 'Destinatario',
                data: null,
                render: (data, type, row) =>
                    `${row.destinatario_nombres} ${row.destinatario_apellidos}`
            },
            {
                title: 'Origen → Destino',
                data: null,
                render: (data, type, row) =>
                    `${row.terminal_origen} → ${row.terminal_destino}`
            },
            {
                title: 'Producto',
                data: 'encomienda_producto'
            },
            {
                title: 'Forma de Pago',
                data: null,
                render: (data, type, row) =>
                    `${row.forma_pago} / ${row.medio_pago}`
            },
            {
                title: 'Estado Pago',
                data: 'estado_pago',
                render: (data) => {
                    const colores = {
                        'PAGADO': '#2ecc71',
                        'PENDIENTE': '#e67e22',
                        'ANULADO': '#e74c3c'
                    };
                    return `<span class="badge" style="background-color:${colores[data] ? colores[data] : '#999'}">${data}</span>`;
                }
            },
            {
                title: 'Total',
                data: 'total',
                render: (data) => `S/ ${parseFloat(data).toFixed(2)}`
            },
        ],
        language: {
            url: './public/plugins/datatable/language/es_es.json',
        },
    });

    let id_embarcacion = document.getElementById('id_embarcacion');

    $('#table_historialE tbody').on('click', '.btnEDITAR', function () {
        const rowData = table_historialE.row($(this).parents()).data();

        id_embarcacion.value = rowData.id;
        document.getElementById('loader').classList.remove('d-none');

        let formData = new FormData();
        formData.set('id_embarcacion', rowData.id);
        formData.set('id_programacion', rowData.id_programacion);

        fetch(instance._URL_ + 'encomienda/get_encomiendas_embarque', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(result => {
                if (!result.success) throw new Error('Error del servidor');

                tabla_edicion_embarcacion.clear().rows.add(result.data).draw();
                $("#modal_edit_embarcacion").modal("show");
            })
            .catch(error => console.error('❌ Error:', error))
            .finally(() => {
                document.getElementById('loader').classList.add('d-none');
            });
    });

    $('#modal_edit_embarcacion').on('shown.bs.modal', function () {
        tabla_edicion_embarcacion.columns.adjust().draw();
    });

    $('#modal_edit_embarcacion').on('hidden.bs.modal', function () {
        tabla_edicion_embarcacion.clear().draw();
        tabla_edicion_embarcacion.rows().deselect();
        $('.inputSearchEnEmbarcados').val('');
        tabla_edicion_embarcacion.search('').draw();
        $('#button_EnEmbarcados').addClass('d-none');
        id_embarcacion.value = '';
    });

    $('.inputSearchEnEmbarcados').on('keyup', function () {
        tabla_edicion_embarcacion.search($(this).val()).draw();
    });

    $('#btn_selectedTodoEnEmbarcados').on('click', function () {
        const total = tabla_edicion_embarcacion.rows().count();
        const seleccion = tabla_edicion_embarcacion.rows({ selected: true }).count();

        if (seleccion === total) {
            tabla_edicion_embarcacion.rows().deselect();
        } else {
            tabla_edicion_embarcacion.rows().select();
        }
    });

    tabla_edicion_embarcacion.on('select deselect', function () {
        const haySeleccion = tabla_edicion_embarcacion.rows({ selected: true }).count() > 0;
        $('#button_EnEmbarcados').toggleClass('d-none', !haySeleccion);
    });

    $('#button_EnEmbarcados').on('click', function () {
        const filasSeleccionadas = tabla_edicion_embarcacion.rows({ selected: true }).data().toArray();

        if (filasSeleccionadas.length === 0) return;

        const ids_encomiendas = filasSeleccionadas.map(function (row) {
            return {
                id_encomienda: row.id_encomienda,
                id_dt_venta: row.id_dt_venta,   // ← estaba usando row.id_venta por error
                id_venta: row.id_venta,       // ← para el socket
                tabla: row.tabla           // ← "comprobante" o "notaVenta"
            };
        });

        $('#button_EnEmbarcados').addClass('d-none');
        $('#button_loadEnEmbarcados').removeClass('d-none');

        let formData = new FormData();
        formData.set('id_embarcacion', id_embarcacion.value);
        formData.set('ids_encomiendas', JSON.stringify(ids_encomiendas));

        fetch(instance._URL_ + 'encomienda/quitar_encomiendas_embarque', {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    $("#modal_edit_embarcacion").modal("toggle")
                    instance.Datatable.reloadTable(table_historialE);
                    instance.Datatable.reloadTable(table_guias);
                    instance.Toast.operacion_exitosa(data.message.message, data.message.links)
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message.message
                    });
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: error.message
                });
            })
            .finally(function () {
                $('#button_loadEnEmbarcados').addClass('d-none');
                const haySeleccion = tabla_edicion_embarcacion.rows({ selected: true }).count() > 0;
                $('#button_EnEmbarcados').toggleClass('d-none', !haySeleccion);
            });
    });

    $('#tipo_busqueda_error').on('change', function () {

        if ($(this).val() === 'tracking') {

            $('#contenedor_tracking').removeClass('d-none');
            $('#contenedor_comprobante').addClass('d-none');

        } else {

            $('#contenedor_tracking').addClass('d-none');
            $('#contenedor_comprobante').removeClass('d-none');

        }

    });

    $('#tracking_error, #serie_error, #correlativo_error').on('keypress', function (e) {

        if (e.which === 13) {
            $('#btn_buscar_encomienda_error').click();
        }

    });


    const form_encomienda_error = document.getElementById("form_buscar_encomienda_error");

    form_encomienda_error.addEventListener("submit", (event) => {

        event.preventDefault();

        let formData = new FormData(form_encomienda_error);

        fetch(instance._URL_ + 'encomienda/buscar_encomienda_d_erroneo', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {

                if (data.success) {

                    $('#resultado_encomienda_error').show();

                    $('#lbl_comprobante').text(data.data.comprobante);
                    $('#lbl_origen').text(data.data.origen);
                    $('#lbl_destino').text(data.data.destino);
                    $('#lbl_tracking').text(data.data.tracking);
                    $('#lbl_estado').text(data.data.estado);
                    $('#lbl_terminal_actual').text(data.data.terminal_actual);

                    $('#btn_registrar_error').prop('disabled', false);

                    $('#btn_registrar_error')
                        .data('id_encomienda', data.data.id_encomienda)
                        .data('id_terminal_origen', data.data.id_terminal_origen)
                        .data('id_terminal_destino', data.data.id_terminal_destino);
                } else {

                    $('#resultado_encomienda_error').hide();
                    $('#btn_registrar_error').prop('disabled', true);

                    instance.Toast.operacion_erronea(data.message);

                }

            })
            .catch(error => {
                instance.Toast.operacion_erronea(error.message);
            });

    });

    $('#modal_destino_erroneo').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');

        $('#resultado_encomienda_error').hide();
        $('#btn_registrar_error').prop('disabled', true);

        $('#lbl_comprobante').text('');
        $('#lbl_origen').text('');
        $('#lbl_destino').text('');
        $('#lbl_tracking').text('');
        $('#lbl_estado').text('');
        $('#lbl_terminal_actual').text('');
        $('#observacion_error').val('');
        $('#btn_registrar_error').removeData('id_encomienda');
        $('#btn_registrar_error').removeData('id_terminal_destino');
        $('#contenedor_tracking').removeClass('d-none');
        $('#contenedor_comprobante').addClass('d-none');
    });
    $('#btn_registrar_error').on('click', function () {

        const id_encomienda = $(this).data('id_encomienda');

        if (!id_encomienda) {
            instance.Toast.operacion_erronea('No se encontró la encomienda seleccionada');
            return;
        }

        Swal.fire({
            title: '¿Marcar encomienda como mal enviada?',
            text: 'La encomienda quedará registrada como destino erróneo y podrá ser reenviada posteriormente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, marcar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let formData = new FormData();

            formData.set(
                'id_encomienda',
                id_encomienda
            );

            formData.set(
                'id_terminal_origen',
                $('#btn_registrar_error').data('id_terminal_origen')
            );

            formData.set(
                'id_terminal_destino',
                $('#btn_registrar_error').data('id_terminal_destino')
            );

            formData.set(
                'observacion',
                $('#observacion_error').val().trim()
            );

            fetch(instance._URL_ + 'encomienda/marcar_destino_erroneo', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(data => {

                    Swal.close();

                    if (data.success) {

                        instance.Toast.operacion_exitosa(data.message);

                        $('#modal_destino_erroneo').modal('hide');

                        if (typeof table_comprobante !== 'undefined') {
                            instance.Datatable.reloadTable(table_comprobante);
                        }

                        if (typeof table_historialE !== 'undefined') {
                            instance.Datatable.reloadTable(table_historialE);
                        }

                    } else {

                        instance.Toast.operacion_erronea(data.message);

                    }

                })
                .catch(error => {

                    Swal.close();

                    instance.Toast.operacion_erronea(
                        error.message || 'Ocurrió un error inesperado'
                    );

                });

        });

    });
})