import * as instance from "../../../public/js/instance.js"

document.getElementById("link_notas").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_notas').DataTable({
        "ajax": {
            'url': instance._URL_ + 'notas/dataTable',
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
            [10, 25, 50, -1],
            ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        "ordering": false,

        "columns": [
            {
                "data": "fecha_emision",
            },
            {
                "data": "serie",
                render: (data, type, row) => {
                    return row.serie + '-' + row.correlativo
                }
            },
            {
                "data": "descripcion",
            },
            {
                "data": "mensaje_sunat",
            },
            {
                "data": "estado_sunat",
                render: (data, type, row) => {
                    return `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.estado_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.estado_sunat]}</span>`
                }
            },
            {
                "data": "documento_relacionado",
                render: (data, type, row) => {
                    if (row.serie_ref && row.correlativo_ref) {
                        return row.serie_ref + '-' + row.correlativo_ref;
                    }
                    return 'N/A';
                }
            },
            {
                "data": "total",
                render: (data, type, row) => {
                    if (row.total) {
                        return 'S/ ' + parseFloat(row.total).toFixed(2);
                    }
                    return 'S/ 0.00';
                }
            },
            {
                "data": "id_nota",
                render: (data, type, row) => {
                    // Botón de impresión - NUEVO
                    let html_imprimir = `<button class="btn btn-primary mb-1" onclick="mostrarModalImpresion(${row.id_nota}, '${row.tipo_nota || ''}')" title="Imprimir" style="margin-right: 5px;">
                                            <i class="fa-light fa-print"></i>
                                         </button>`;
                    
                    // Botón XML (si existe)
                    let html_xml = row.xml ? 
                        `<a class="btn btnXML text-white mb-1" href="${row.xml}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.XML}; margin-right: 5px;" title="XML">
                            <i class="fa-light fa-file-xmark"></i>
                         </a>` : '';
                    
                    // Botón CDR (si existe)
                    let html_cdr = row.cdr ? 
                        `<a class="btn btnCDR text-white mb-1" href="${row.cdr}" target="_blank" style="background-color:${instance.CONSTS.COLORES.BUTTONS.CDR}" title="CDR">
                            <i class="fa-light fa-file-zipper"></i>
                         </a>` : '';
                    
                    // Combinar todos los botones
                    return html_imprimir + html_xml + html_cdr;
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
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual");
})

// Función mostrarModalImpresion (MEJORADA)
function mostrarModalImpresion(id_nota, tipo_nota_param = '') {
    // Primero obtener información detallada de la nota
    fetch(instance._URL_ + 'notas/getTipoNota/' + id_nota)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const tipo_nota = data.tipo_nota; // 'credito' o 'debito'
                const tipoTexto = tipo_nota === 'credito' ? 'Crédito' : 'Débito';
                const codigoComprobante = data.codigo_comprobante; // '07' o '08'
                const descComprobante = data.desc_comprobante || 'Comprobante';
                
                // Construir URLs - usar formato simple con solo el ID
                const tipoBase = data.tipo_nota === 'credito' ? 'nota_credito' : 'nota_debito';
                const urlTicket = `${instance._URL_}notas/impresion/${tipoBase}/${id_nota}`;
                const urlA4 = `${instance._URL_}notas/impresion_a4/${tipoBase}A4/${id_nota}`;
                // Textos descriptivos
                const tituloModal = `${descComprobante}`;
                const subtitulo = tipo_nota === 'credito' ? 
                    'Nota de Crédito Electrónica' : 'Nota de Débito Electrónica';
                
                Swal.fire({
                    title: tituloModal,
                    html: `
                    <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
                        <i class="fa-solid fa-print py-3" style="font-size:50px; color:#3498db"></i>
                        <h5>${subtitulo}</h5>
                        <p class="text-muted">Seleccione formato de impresión</p>
                        <section class="d-flex justify-content-center align-items-center">
                            <a href="${urlTicket}" 
                               class="d-flex flex-column text-decoration-none py-4 mx-2 wow pulse animated" 
                               target="_blank"
                               onclick="Swal.close()">
                                <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                                <label class="text-secondary cursor-pointer mt-2">Formato Ticket</label>
                            </a>
                            
                            <a href="${urlA4}" 
                               class="d-flex flex-column text-decoration-none py-4 mx-2 wow pulse animated" 
                               target="_blank"
                               onclick="Swal.close()">
                                <i class="fa-regular fa-file-lines fs-2 text-primary"></i>
                                <label class="text-secondary cursor-pointer mt-2">Formato A4</label>
                            </a>
                        </section>
                        <p class="text-muted mt-3">El documento se abrirá en una nueva pestaña</p>
                    </div>`,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Cerrar',
                    cancelButtonColor: '#6c757d',
                    width: 500,
                    backdrop: true,
                    allowOutsideClick: true
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo obtener la información de la nota',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'Aceptar'
            });
        });
}

// Exportar la función para que esté disponible globalmente
window.mostrarModalImpresion = mostrarModalImpresion;