import * as instance from "../../../public/js/instance.js"

// Hacer instance disponible globalmente
window.instance = instance;

document.getElementById("link_listado_facturador").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_listado_facturador").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_listado_facturador").closest(".submenu").classList.add("show")
document.getElementById("link_listado_facturador").classList.add("active")

document.addEventListener("DOMContentLoaded", function () {
    let dataTableListado;

    // Inicializar DataTable
    inicializarDataTable();

    // Configurar eventos
    configurarEventos();

    // Funciones principales
    function inicializarDataTable() {
        dataTableListado = $('#table_listado_facturador').DataTable({
            "ajax": {
                'url': instance._URL_ + 'listado_facturador/dataTable',
                'method': 'POST',
                'data': function (d) {
                    d.filtro_fecha_desde = $('#filtro_fecha_desde').val();
                    d.filtro_fecha_hasta = $('#filtro_fecha_hasta').val();
                    d.filtro_tipo_comprobante = $('#filtro_tipo_comprobante').val();
                    d.filtro_estado = $('#filtro_estado').val();
                    return d;
                },
                beforeSend: function () {
                    if (document.getElementById('loader')) {
                        document.getElementById('loader').classList.remove('d-none');
                    }
                },
                complete: function () {
                    if (document.getElementById('loader')) {
                        document.getElementById('loader').classList.add('d-none');
                    }
                }
            },
            "processing": true,
            "serverSide": true,
            responsive: true,
            fixedHeader: true,
            dom: 'rtip',
            lengthMenu: [[20, 25, 50, -1], ['20 filas', '25 filas', '50 filas', 'Mostrar todo']],
            "ordering": false,
            "columns": [
                { "data": "id_venta", "orderable": false, visible: false },
                { "data": "fecha_emision", "orderable": false },
                {
                    "data": null,
                    "orderable": false,
                    render: function (data, type, row) {
                        return row.serie && row.correlativo ? `${row.serie}-${row.correlativo}` : '---';
                    }
                },
                { "data": "cliente", "orderable": false },
                { "data": "ruc_cliente", "orderable": false },
                { "data": "tipo_comprobante", "orderable": false },
                {
                    "data": "op_gravada",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return 'S/ 0.00';
                        }
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    "data": "op_exonerada",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return 'S/ 0.00';
                        }
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    "data": "op_inafecta",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return 'S/ 0.00';
                        }
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    "data": "op_igv",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return 'S/ 0.00';
                        }
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    "data": "icbper",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return 'S/ 0.00';
                        }
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    "data": "total",
                    "orderable": false,
                    render: function (data, type, row) {
                        // Si está anulado, mostrar 0
                        if (row.estado === 'ANULADO') {
                            return '<strong>S/ 0.00</strong>';
                        }
                        return '<strong>S/ ' + parseFloat(data || 0).toFixed(2) + '</strong>';
                    }
                },
                {
                    "data": "estado",
                    "orderable": false,
                    render: function (data) {
                        let badgeClass = 'badge ';
                        switch (data) {
                            case 'PAGADO': badgeClass += 'bg-success'; break;
                            case 'ANULADO': badgeClass += 'bg-danger'; break;
                            case 'PENDIENTE': badgeClass += 'bg-warning'; break;
                            default: badgeClass += 'bg-secondary';
                        }
                        return `<span class="${badgeClass}">${data}</span>`;
                    }
                },
                {
                    "data": "envio_sunat",  // Cambiado para usar el código numérico
                    "orderable": false,
                    render: function (data, type, row) {
                        let badgeClass = 'badge ';
                        let texto = '';

                        // Basado en el valor numérico de envio_sunat
                        switch (data.toString()) {
                            case '0': // PENDIENTE - NO ENVIADO
                                badgeClass += 'bg-danger text-white';
                                texto = 'SIN ENVIAR';
                                break;
                            case '1': // ACEPTADO
                                badgeClass += 'bg-success text-white';
                                texto = 'ENVIADO A SUNAT';
                                break;
                            case '2': // RECHAZADO
                                badgeClass += 'bg-danger text-white';
                                texto = 'RECHAZADO POR SUNAT';
                                break;
                            case '3': // EXCEPCIÓN
                                badgeClass += 'bg-warning text-dark';
                                texto = 'EXCEPCIÓN SUNAT';
                                break;
                            default:
                                badgeClass += 'bg-secondary text-white';
                                texto = 'DESCONOCIDO';
                        }

                        // Agregar tooltip con la descripción si existe
                        let tooltip = row.descrip_cdr_sunat || '';
                        if (tooltip) {
                            return `<span class="${badgeClass}" title="${tooltip}">${texto}</span>`;
                        }
                        return `<span class="${badgeClass}">${texto}</span>`;
                    }
                },
                {
                    "data": "afecta_detraccion",
                    "orderable": false,
                    render: function (data) {
                        return data == 1 ? '<span class="badge bg-info">Sí</span>' : '<span class="badge bg-secondary">No</span>';
                    }
                },
                {
                    "data": null,
                    render: function (data, type, row) {
                        const estaAnulado = row.estado === 'ANULADO';
                        const idVenta = row.id_venta;
                        const tipoComp = row.id_tp_comprobante;

                        // XML - MUY SIMPLE: si existe file_xml, usar directamente
                        let html_xml = row.file_xml ?
                            `<a class="dropdown-item-accion" href="${row.file_xml}" target="_blank" title="XML">
                            <i class="fas fa-file-code me-2"></i> XML
                            </a>` :
                            `<a class="dropdown-item-accion disabled" href="#" title="XML">
                            <i class="fas fa-file-code me-2"></i> XML
                            </a>`;

                        // CDR - solo si fue aceptado por SUNAT y existe archivo
                        let html_cdr = (row.file_cdr && row.envio_sunat == 1) ?
                            `<a class="dropdown-item-accion" href="${row.file_cdr}" target="_blank" title="CDR">
                            <i class="fas fa-file-alt me-2"></i> CDR
                            </a>` :
                            `<a class="dropdown-item-accion disabled" href="#" title="CDR">
                            <i class="fas fa-file-alt me-2"></i> CDR
                            </a>`;

                        let botones = `
                        <div class="dropdown-acciones" data-id="${idVenta}" data-tipo="${tipoComp}">
                        <button class="btn-tres-puntos" type="button">
                        <i class="fas fa-ellipsis-v"></i>
                        </button>

                        <div class="dropdown-menu-acciones">
                        <a class="dropdown-item-accion" href="#" data-action="imprimir" data-id="${idVenta}" data-tipo="${tipoComp}">
                        <i class="fas fa-print me-2"></i> Comprobante
                        </a>
                        
                        <a class="dropdown-item-accion" href="#" data-action="imprimir_a4" data-id="${idVenta}" data-tipo="${tipoComp}">
                        <i class="fas fa-file-pdf me-2"></i> Comprobante A4
                        </a>
                        `;
                        // Opción: Anular
                        if (estaAnulado) {
                            botones += `<a class="dropdown-item-accion disabled" href="#">
                            <i class="fas fa-ban me-2"></i> <span class="text-muted">Anular</span>
                            </a>`;
                        } else {
                            botones += `<a class="dropdown-item-accion text-danger" href="#" data-action="anular" data-id="${idVenta}">
                            <i class="fas fa-ban me-2"></i> <span class="text-danger">Anular</span>
                            </a>`;
                        }

                        // Agregar XML y CDR (¡SIMPLEMENTE!)
                        botones += html_xml;
                        botones += html_cdr;

                        // Opción: Crear Nota (solo para comprobantes electrónicos enviados a SUNAT)
                        //if (row.envio_sunat == 1 && row.estado !== 'ANULADO') {
                        //    botones += `<a class="dropdown-item-accion" href="#" data-action="crear_nota" data-id="${idVenta}">
                        //    <i class="fas fa-sticky-note me-2"></i> Crear Nota
                        //    </a>`;
                        //}

                        botones += `</div></div>`;
                        return botones;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
            "language": { url: instance._URL_ + 'public/plugins/datatable/language/es_es.json' },
            "deferRender": true,
            "stateSave": false,
            "pageLength": 20,
        });

        instance.Datatable.inputSearch(dataTableListado, "#inputBusquedaGlobal", "#btnBuscarGlobal", "manual");
    }

    function configurarEventos() {
        $('#inputBusquedaGlobal').on('keyup', function (e) {
            if (e.keyCode === 13) $('#btnBuscarGlobal').click();
        });
        $('#btnAplicarFiltros').on('click', function () { dataTableListado.ajax.reload(); });
        $('#btnLimpiarFiltros').on('click', function () {
            $('#filtro_fecha_desde, #filtro_fecha_hasta, #filtro_tipo_comprobante, #filtro_estado, #inputBusquedaGlobal').val('');
            dataTableListado.search('').draw();
            dataTableListado.ajax.reload();
        });
        $('#btnConfirmarReenvio').on('click', reenviarSunatConfirmado);
        $('#btnImprimirComprobante').on('click', function () {
            var id = $('#modalDetalleComprobante').data('id-comprobante');
            var tipo = $('#modalDetalleComprobante').data('tipo-comprobante');
            if (id && tipo) imprimirTicket(id, tipo);
        });

        // MANEJO DE DROPDOWNS 
        $(document).on('click', '.btn-tres-puntos', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $button = $(this);
            var $menu = $button.next('.dropdown-menu-acciones');

            // Cerrar otros
            $('.dropdown-menu-acciones').not($menu).removeClass('show');

            // Abrir/cerrar este
            $menu.toggleClass('show');

            // Si está en modo responsive (columna expandida)
            if ($button.closest('.dtr-details').length || $button.closest('.dtr-expanded').length) {
                $menu.css({
                    'position': 'absolute',
                    'right': '-75px',
                    'left': 'auto',
                    'top': '100%',
                    'z-index': '1000'
                });
            }
        });

        // Cerrar al hacer clic fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.dropdown-acciones').length) {
                $('.dropdown-menu-acciones').removeClass('show');
            }
        });

        $(document).on('click', '.dropdown-item-accion[data-action]', function (e) {
            // Solo capturar elementos con data-action (XML/CDR no lo tienen)
            e.preventDefault();
            e.stopPropagation();

            // Cerrar dropdown
            $(this).closest('.dropdown-menu-acciones').removeClass('show');

            const action = $(this).data('action');
            const id = $(this).data('id');
            const tipo = $(this).data('tipo');

            switch (action) {
                case 'imprimir':
                    window.imprimirTicket(id, tipo);
                    break;
                case 'imprimir_a4':
                    window.imprimirTicketA4(id, tipo);
                    break;
                case 'anular':
                    window.anularComprobante(id);
                    break;
                //case 'crear_nota':
                //    window.crearNota(id);
                //    break;
            }

            return false;
        });

        // Botón de exportación Excel
        $('#btnExportarExcel').on('click', function () {
            exportarExcel();
        });

        // Función para exportar a Excel
        function exportarExcel() {
            // Mostrar carga
            Swal.fire({
                title: 'Generando Excel...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Recopilar datos de filtros actuales
            const filtros = {
                filtro_fecha_desde: $('#filtro_fecha_desde').val(),
                filtro_fecha_hasta: $('#filtro_fecha_hasta').val(),
                filtro_tipo_comprobante: $('#filtro_tipo_comprobante').val(),
                filtro_estado: $('#filtro_estado').val(),
                search: {
                    value: $('#inputBusquedaGlobal').val()
                }
            };

            // Crear formulario para enviar
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.instance._URL_ + 'listado_facturador/exportar_excel';
            form.target = '_blank'; // Abrir en nueva pestaña

            // Agregar campos ocultos
            for (const key in filtros) {
                if (typeof filtros[key] === 'object') {
                    for (const subKey in filtros[key]) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `${key}[${subKey}]`;
                        input.value = filtros[key][subKey];
                        form.appendChild(input);
                    }
                } else {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = filtros[key];
                    form.appendChild(input);
                }
            }

            // Enviar formulario
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            // Cerrar carga después de un momento
            setTimeout(() => {
                Swal.close();
                window.instance.Toast.operacion_exitosa('Excel generado correctamente');
            }, 2000);
        }
    }
});

// FUNCIONES GLOBALES CORREGIDAS
function imprimirTicket(id, tipoComprobante, clickEvent) {
    let tipoArchivo = '';
    if (tipoComprobante == 1 || tipoComprobante == 3) {
        tipoArchivo = 'comprobante';
    } else if (tipoComprobante == 2) {
        tipoArchivo = 'nota_venta';
    } else {
        window.instance.Toast.operacion_erronea('Tipo de comprobante no válido');
        return;
    }

    // Siempre abrir la URL, incluso si está anulado
    var url = window.instance._URL_ + 'facturador/impresion/' + tipoArchivo + '/' + id;
    window.open(url, '_blank');
}

// FUNCIÓN PARA IMPRIMIR COMPROBANTE A4
function imprimirTicketA4(id, tipoComprobante) {
    let tipoArchivo = '';

    // Determinar qué formato usar según el tipo de comprobante
    if (tipoComprobante == 1 || tipoComprobante == 3) {
        // Factura (1) o Boleta (3) -> usar formato A4 de comprobante
        tipoArchivo = 'comprobanteA4';
    } else if (tipoComprobante == 2) {
        // Nota de venta (2) -> usar formato A4 de nota de venta
        tipoArchivo = 'nota_ventaA4';
    } else {
        window.instance.Toast.operacion_erronea('Tipo de comprobante no válido para formato A4');
        return;
    }

    // Construir URL para el formato A4
    // Asumiendo que los formatos A4 están en la misma estructura pero en subdirectorio A4
    var url = window.instance._URL_ + 'listado_facturador/impresion_a4/' + tipoArchivo + '/' + id;
    window.open(url, '_blank');
}

function abrirModalAnulacion(id, clickEvent) {
    $('#idComprobanteAnular').val(id);
    $('#motivoAnulacion').val('');
    $('#modalAnularComprobante').modal('show');
}

function descargarXML(id) {
    // Esto ya no se usa porque las URLs están directamente en los enlaces
    console.log('Función descargarXML() no necesaria - URLs están en los enlaces');
}

function descargarCDR(id) {
    // Esto ya no se usa porque las URLs están directamente en los enlaces
    console.log('Función descargarCDR() no necesaria - URLs están en los enlaces');
}

// Función auxiliar para obtener datos de la fila
function getRowData(id) {
    const table = $('#table_listado_facturador').DataTable();
    const data = table.rows().data().toArray();
    return data.find(row => row.id_venta == id);
}

function crearNota(idVenta) {
    // Mostrar modal de carga
    Swal.fire({
        title: 'Cargando datos...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Cargar datos del comprobante
    Promise.all([
        fetch(window.instance._URL_ + 'listado_facturador/get_comprobante_nota', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_venta=' + idVenta
        }).then(r => r.json()),

        fetch(window.instance._URL_ + 'listado_facturador/get_motivos_nota')
            .then(r => r.json())
    ]).then(([comprobante, motivos]) => {
        Swal.close();

        if (!comprobante || comprobante.error) {
            throw new Error('Error al cargar datos del comprobante');
        }

        // Llenar datos en el modal
        $('#idVentaNota').val(idVenta);
        $('#txtSerieOriginal').val(comprobante.serie + '-' + comprobante.correlativo);
        $('#txtClienteOriginal').val(comprobante.cliente);
        $('#txtTotalOriginal').val('S/ ' + parseFloat(comprobante.total || 0).toFixed(2));
        $('#txtMonedaOriginal').val(comprobante.moneda || 'SOLES');

        // Cargar motivos
        const selectMotivo = $('#motivoNota');
        selectMotivo.empty();
        if (motivos && !motivos.error) {
            const motivosFiltrados = motivos.filter(m => m.tipo === 'C'); // Por defecto Nota Crédito
            motivosFiltrados.forEach(motivo => {
                selectMotivo.append(new Option(motivo.descripcion, motivo.codigo));
            });
            if (motivosFiltrados.length > 0) {
                $('#descripcionNota').val(motivosFiltrados[0].descripcion);
            }
        }

        // Cargar series
        cargarSeriesNota(comprobante.codigo || '03');

        // Cargar productos
        cargarProductosNota(idVenta);

        // Mostrar modal
        $('#modalCrearNota').modal('show');

    }).catch(error => {
        Swal.close();
        window.instance.Toast.operacion_erronea(error.message || 'Error al cargar datos');
    });
}

function cargarSeriesNota(tpComprobanteOriginal) {
    const tipoNota = $('#tipoNota').val();
    const selectSerie = $('#serieNota');
    const inputCorrelativo = $('#correlativoNota');

    // Determinar qué tipo de serie buscar basado en el tipo de nota
    let tpComprobanteBuscar = tipoNota === '07' ? '07' : '08';

    fetch(window.instance._URL_ + 'listado_facturador/get_serie_for_nota', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'tp_comprobante=' + tpComprobanteBuscar
    })
        .then(r => r.json())
        .then(series => {
            selectSerie.empty();

            if (series && !series.error && series.length > 0) {
                // Buscar serie apropiada
                let serieEncontrada = null;

                if (tpComprobanteOriginal === '01') { // Factura
                    serieEncontrada = series.find(s => s.serie.includes('F'));
                } else if (tpComprobanteOriginal === '03') { // Boleta
                    serieEncontrada = series.find(s => s.serie.includes('B'));
                }

                if (serieEncontrada) {
                    selectSerie.append(new Option(serieEncontrada.serie, serieEncontrada.serie));
                    inputCorrelativo.val(serieEncontrada.correlativo || '1');
                } else if (series[0]) {
                    selectSerie.append(new Option(series[0].serie, series[0].serie));
                    inputCorrelativo.val(series[0].correlativo || '1');
                }
            } else {
                selectSerie.append(new Option('No hay series disponibles', ''));
            }
        })
        .catch(error => {
            selectSerie.empty();
            selectSerie.append(new Option('Error al cargar series', ''));
        });
}

let productosNota = [];
let productoEditando = null;

function cargarProductosNota(idVenta) {
    const tbody = $('#tableProductosNota tbody');
    tbody.empty();
    productosNota = [];

    // Intentar cargar productos de encomienda primero
    fetch(window.instance._URL_ + 'listado_facturador/get_items_encomienda', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_venta=' + idVenta
    })
        .then(r => r.json())
        .then(productos => {
            if (productos && !productos.error && productos.length > 0) {
                // Mostrar productos de encomienda
                productos.forEach((prod, index) => {
                    const productoData = {
                        descripcion: prod.descripcion || 'Producto',
                        op_exonerada: parseFloat(prod.op_exonerada || 0),
                        op_gravada: parseFloat(prod.op_gravada || 0),
                        op_inafecta: parseFloat(prod.op_inafecta || 0),
                        descuento: parseFloat(prod.descuento || 0),
                        igv: parseFloat(prod.igv || 0),
                        total: parseFloat(prod.total || 0),
                        afectacion: prod.afectacion || 18
                    };

                    productosNota.push(productoData);
                    agregarFilaProducto(productoData, index);
                });
            } else {
                // Si no hay productos de encomienda, mostrar datos generales del comprobante
                fetch(window.instance._URL_ + 'listado_facturador/get_comprobante_nota', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_venta=' + idVenta
                })
                    .then(r => r.json())
                    .then(comprobante => {
                        if (comprobante && !comprobante.error) {
                            const productoData = {
                                descripcion: `Comprobante ${comprobante.serie}-${comprobante.correlativo}`,
                                op_exonerada: parseFloat(comprobante.op_exonerada || 0),
                                op_gravada: parseFloat(comprobante.op_gravada || 0),
                                op_inafecta: parseFloat(comprobante.op_inafecta || 0),
                                descuento: parseFloat(comprobante.descuento || 0),
                                igv: parseFloat(comprobante.igv || 0),
                                total: parseFloat(comprobante.total || 0),
                                afectacion: comprobante.igv > 0 ? 18 : 0
                            };

                            productosNota.push(productoData);
                            agregarFilaProducto(productoData, 0);
                        }
                    });
            }
        });
}

function agregarFilaProducto(producto, index) {
    const tbody = $('#tableProductosNota tbody');

    const fila = `
        <tr data-index="${index}">
            <td>${producto.descripcion}</td>
            <td class="op-exonerada">${producto.op_exonerada.toFixed(2)}</td>
            <td class="op-gravada">${producto.op_gravada.toFixed(2)}</td>
            <td class="op-inafecta">${producto.op_inafecta.toFixed(2)}</td>
            <td class="descuento">${producto.descuento.toFixed(2)}</td>
            <td class="igv">${producto.igv.toFixed(2)}</td>
            <td class="total"><strong>${producto.total.toFixed(2)}</strong></td>
            <td>
                <button type="button" class="btn btn-sm btn-warning btn-editar-producto" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger btn-eliminar-producto" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    tbody.append(fila);
}

function actualizarFilaProducto(index) {
    const producto = productosNota[index];
    const fila = $(`#tableProductosNota tbody tr[data-index="${index}"]`);

    if (fila.length) {
        fila.find('.op-exonerada').text(producto.op_exonerada.toFixed(2));
        fila.find('.op-gravada').text(producto.op_gravada.toFixed(2));
        fila.find('.op-inafecta').text(producto.op_inafecta.toFixed(2));
        fila.find('.descuento').text(producto.descuento.toFixed(2));
        fila.find('.igv').text(producto.igv.toFixed(2));
        fila.find('.total').html(`<strong>${producto.total.toFixed(2)}</strong>`);
    }
}

function recalcularMontosProducto(producto, nuevoTotal) {
    const total = parseFloat(nuevoTotal);

    if (producto.afectacion === 0) {
        // Producto exonerado (sin IGV)
        producto.total = total;
        producto.op_exonerada = total;
        producto.op_gravada = 0;
        producto.igv = 0;
    } else {
        // Producto con IGV (18%)
        producto.total = total;
        producto.op_gravada = total / 1.18;
        producto.igv = producto.op_gravada * 0.18;
        producto.op_exonerada = 0;
    }

    return producto;
}

// Configurar eventos del modal
$(document).ready(function () {
    // Cambiar tipo de nota
    $('#tipoNota').on('change', function () {
        const tipo = $(this).val();
        const selectMotivo = $('#motivoNota');

        // Recargar motivos según tipo
        fetch(window.instance._URL_ + 'listado_facturador/get_motivos_nota')
            .then(r => r.json())
            .then(motivos => {
                if (motivos && !motivos.error) {
                    selectMotivo.empty();
                    const motivosFiltrados = motivos.filter(m => m.tipo === (tipo === '07' ? 'C' : 'D'));
                    motivosFiltrados.forEach(motivo => {
                        selectMotivo.append(new Option(motivo.descripcion, motivo.codigo));
                    });
                    if (motivosFiltrados.length > 0) {
                        $('#descripcionNota').val(motivosFiltrados[0].descripcion);
                    }
                }
            });

        // Recargar series
        const idVenta = $('#idVentaNota').val();
        if (idVenta) {
            fetch(window.instance._URL_ + 'listado_facturador/get_comprobante_nota', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_venta=' + idVenta
            })
                .then(r => r.json())
                .then(comprobante => {
                    if (comprobante && !comprobante.error) {
                        cargarSeriesNota(comprobante.codigo || '03');
                    }
                });
        }
    });

    // Evento para editar producto
    $(document).on('click', '.btn-editar-producto', function () {
        const fila = $(this).closest('tr');
        const index = fila.data('index');
        const producto = productosNota[index];

        productoEditando = index;
        $('#precioTotal').val(producto.total.toFixed(2));
        $('#modalEditarPrecio').modal('show');
    });

    // Evento para eliminar producto
    $(document).on('click', '.btn-eliminar-producto', function () {
        const fila = $(this).closest('tr');
        const index = fila.data('index');

        Swal.fire({
            title: '¿Eliminar producto?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Eliminar producto del array
                productosNota.splice(index, 1);

                // Eliminar fila de la tabla
                fila.remove();

                // Reindexar las filas restantes
                $('#tableProductosNota tbody tr').each(function (newIndex) {
                    $(this).data('index', newIndex);
                });

                // Si no quedan productos, mostrar mensaje
                if (productosNota.length === 0) {
                    $('#tableProductosNota tbody').html(`
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
                                No hay productos para mostrar
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });

    // Actualizar precio del producto
    $('#btnActualizarPrecio').on('click', function () {
        const nuevoTotal = parseFloat($('#precioTotal').val());

        if (isNaN(nuevoTotal) || nuevoTotal < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ingrese un precio válido'
            });
            return;
        }

        if (productoEditando !== null && productosNota[productoEditando]) {
            const producto = productosNota[productoEditando];
            const productoActualizado = recalcularMontosProducto(producto, nuevoTotal);
            productosNota[productoEditando] = productoActualizado;
            actualizarFilaProducto(productoEditando);

            $('#modalEditarPrecio').modal('hide');
            productoEditando = null;

            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: 'Precio actualizado correctamente',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    // Limpiar modal de edición al cerrar
    $('#modalEditarPrecio').on('hidden.bs.modal', function () {
        $('#formEditarPrecio')[0].reset();
        productoEditando = null;
    });

    // Guardar nota
    $('#btnGuardarNota').on('click', function () {
        const form = $('#formCrearNota')[0];

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        if (productosNota.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un producto'
            });
            return;
        }

        $('#btnGuardarNota').addClass('d-none');
        $('#btnCargandoNota').removeClass('d-none');

        // Preparar datos como FormData (compatible con tu código actual)
        const formData = new FormData();

        // Agregar datos del formulario
        formData.append('id_venta', $('#idVentaNota').val());
        formData.append('list_comp', $('#tipoNota').val());
        formData.append('txtserie_nota', $('#serieNota').val());
        formData.append('txtcorrelativo_nota', $('#correlativoNota').val());
        formData.append('list_motivo', $('#motivoNota').val());
        formData.append('txt_fecha', $('#fechaNota').val());
        formData.append('txt_descripcion', $('#descripcionNota').val());

        // Agregar productos
        formData.append('productos', JSON.stringify(productosNota));

        // Enviar datos
        fetch(window.instance._URL_ + 'listado_facturador/crear_nota', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(response => {
                // Restaurar botones
                $('#btnGuardarNota').removeClass('d-none');
                $('#btnCargandoNota').addClass('d-none');

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalCrearNota').modal('hide');

                        // Recargar DataTable
                        if ($.fn.DataTable.isDataTable('#table_listado_facturador')) {
                            $('#table_listado_facturador').DataTable().ajax.reload(null, false);
                        }

                        // También puedes redirigir a la nueva nota
                        if (response.id_nota) {
                            console.log('Nota creada con ID:', response.id_nota);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al crear la nota'
                    });
                }
            })
            .catch(error => {
                $('#btnGuardarNota').removeClass('d-none');
                $('#btnCargandoNota').addClass('d-none');

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
                console.error('Error:', error);
            });
    });

    // Limpiar modal al cerrar
    $('#modalCrearNota').on('hidden.bs.modal', function () {
        $('#formCrearNota')[0].reset();
        $('#formCrearNota')[0].classList.remove('was-validated');
        $('#tableProductosNota tbody').empty();
        productosNota = [];
        productoEditando = null;
        $('#btnGuardarNota').removeClass('d-none');
        $('#btnCargandoNota').addClass('d-none');
    });
});

function reenviar_sunat(id) {
    $('#idComprobanteReenviar').val(id);
    $('#modalReenviarSunat').modal('show');
}

function reenviarSunatConfirmado() {
    var id = $('#idComprobanteReenviar').val();
    Swal.fire({
        title: '¿Está seguro de reenviar este comprobante a SUNAT?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, reenviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarCarga();
            $.ajax({
                url: window.instance._URL_ + 'listado_facturador/reenviar_sunat',
                type: 'POST',
                data: { id_comprobante: id },
                beforeSend: function () {
                    if (document.getElementById('loader')) document.getElementById('loader').classList.remove('d-none');
                },
                complete: function () {
                    if (document.getElementById('loader')) document.getElementById('loader').classList.add('d-none');
                },
                success: function (response) {
                    $('#modalReenviarSunat').modal('hide');
                    if (response.success) {
                        window.instance.Toast.operacion_exitosa('Comprobante reenviado a SUNAT: ' + (response.mensaje_sunat || response.message));
                        setTimeout(function () {
                            if (typeof dataTableListado !== 'undefined') dataTableListado.ajax.reload();
                        }, 1000);
                    } else {
                        window.instance.Toast.operacion_erronea('Error al reenviar: ' + response.message);
                    }
                },
                error: function () {
                    $('#modalReenviarSunat').modal('hide');
                    window.instance.Toast.operacion_erronea('Error de conexión al reenviar a SUNAT');
                }
            });
        }
    });
}

// FUNCIÓN PARA ANULAR COMPROBANTE CORREGIDA
function anularComprobante(id) {
    Swal.fire({
        title: '¿Está seguro que desea anular este comprobante?',
        text: "No podrá revertir los cambios",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'No, cancelar',
        showCloseButton: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        backdrop: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar carga
            Swal.fire({
                title: 'Anulando comprobante...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar solicitud al servidor
            let formData = new FormData();
            formData.append("id_comprobante", id);
            formData.append("id_venta", id);

            fetch(window.instance._URL_ + 'listado_facturador/anular', {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (response.ok) return response.json();
                    throw new Error('Error en la respuesta del servidor');
                })
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        console.log('Respuesta exitosa:', data);

                        // Mostrar mensaje de éxito
                        Swal.fire({
                            title: "¡Éxito!",
                            text: data.message,
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // OPCIÓN 1: Usar jQuery para obtener la DataTable
                            if ($.fn.DataTable.isDataTable('#table_listado_facturador')) {
                                $('#table_listado_facturador').DataTable().ajax.reload(null, false);
                            }

                            // OPCIÓN 2: Usar window.dataTableListado si existe
                            if (typeof window.dataTableListado !== 'undefined') {
                                window.dataTableListado.ajax.reload(null, false);
                            }

                            // OPCIÓN 3: Recargar la página si nada funciona
                            // location.reload();
                        });

                    } else {
                        // Usar instance.Toast si está disponible
                        if (typeof instance !== 'undefined' && instance.Toast) {
                            instance.Toast.operacion_erronea(data.message);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al anular',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error en anulación:', error);

                    // Usar instance.Toast si está disponible
                    if (typeof instance !== 'undefined' && instance.Toast) {
                        instance.Toast.operacion_erronea(error.message || 'Error de conexión');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
        }
    });
}

function mostrarCarga() {
    if (document.getElementById('loader')) document.getElementById('loader').classList.remove('d-none');
}

function ocultarCarga() {
    if (document.getElementById('loader')) document.getElementById('loader').classList.add('d-none');
}

// Hacer funciones globales
window.imprimirTicket = imprimirTicket;
window.imprimirTicketA4 = imprimirTicketA4;
window.abrirModalAnulacion = abrirModalAnulacion;
window.crearNota = crearNota;
window.reenviar_sunat = reenviar_sunat;
window.reenviarSunatConfirmado = reenviarSunatConfirmado;
window.anularComprobante = anularComprobante;
window.descargarXML = descargarXML;
window.descargarCDR = descargarCDR;
