import * as instance from "../../../public/js/instance.js"

document.getElementById("link_encomiendas_report").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_encomiendas_report").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_encomiendas_report").closest(".submenu").classList.add("show")
document.getElementById("link_encomiendas_report").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    let table = $('#tabla_comprobantesreport').DataTable({
        "ajax": {
            'url': $('#url').val() + 'encomiendas_report/dataTable',
            'method': 'POST',
            data: function (d) {
                d.terminal = terminal.value;
                d.forma_pago = forma_pago.value;
                d.estado_pago = estado_pago.value;
                d.tp_comprobante = tp_comprobante.value;
                d.fecha_inicio = fecha_inicio.value;
                d.fecha_fin = fecha_fin.value;
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
                "data": "numero",
            },
            {
                "data": "fecha_emision",
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
                "data": "destino",
                render: (data, type, row) => {
                    return `${row.destino}`
                }
            },
            {
                "data": "terminal",
            },
            {
                "data": "envio_sunat",
                render: (data, type, row) => {
                    let envio = row.id_tp_comprobante == 1 || row.id_tp_comprobante == 3 ? `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.envio_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.envio_sunat]}</span>` : `<span class="badge" style="background-color:#b5b5b5">NO APLICA</span>`;
                    return envio;
                }
            },
            {
                "data": "estado_pago",
            },
            {
                "data": "op_gravada",
            },
            {
                "data": "op_igv",
            },
            {
                "data": "total",
            },
            {
                "data": "forma_pago",
            },
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
        "buttons": [
            {
                "extend": "excelHtml5",
                "text": "<i class='bi bi-file-earmark-excel-fill'></i> Excel",
                "titleAttr": "Exportar a Excel",
                "className": "btnExcel"
            },
            {
                "extend": "print",
                "text": " <i class='bi bi-printer-fill'></i> Imprimir",
                "titleAttr": "Imprimir",
                "className": "BtnPrint"
            }
        ]
    })

    $('#exportExcel').on('click', function () {
        table.button('.btnExcel').trigger();
    });

    $('#exportPrint').on('click', function () {
        table.button('.BtnPrint').trigger();
    });

    $('#exportPdf').on('click', function () {
        let url = instance._URL_ + 'reportes/impresion/pagos';
        window.open(url, '_blank');
    });

    let button_save = document.querySelector("#btn_filtrar")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#btn_loadSave")
    let CamposOcultos = document.querySelector('.grupo-campos-oculto');
    let CampoPrimero = document.querySelector('.primero');

    // Obtener referencias a los inputs
    const terminal = document.querySelector("#terminal");
    const forma_pago = document.querySelector("#forma_pago");
    const estado_pago = document.querySelector("#estado_pago");
    const tp_comprobante = document.querySelector("#tp_comprobante");
    const fecha_inicio = document.querySelector("#fecha_inicio");
    const fecha_fin = document.querySelector("#fecha_fin");

    const btnFiltrar = document.getElementById("btn_filtrar");

    // Inicializar buscador de DataTable si usas inputSearch
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearchTable", "manual");


    btnFiltrar.addEventListener("click", () => {
        if (fecha_inicio.value && fecha_fin.value) {
            const inicio = new Date(fecha_inicio.value);
            const fin = new Date(fecha_fin.value);

            if (fin < inicio) {
                Swal.fire({
                    icon: 'error',
                    title: 'Rango de fechas inválido',
                    text: 'La fecha final no puede ser anterior a la fecha de inicio.'
                });
                return;
            }
        }
        table.ajax.reload();
    });

    // Función para limpiar todos los filtros
    function limpiarFiltros() {

        terminal.value = "";
        forma_pago.value = "";
        estado_pago.value = "TODO";
        tp_comprobante.value = "TODO";
        fecha_inicio.value = "";
        fecha_fin.value = "";
    }

    const btnLimpiar = document.getElementById("btn_limpiar");
    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", () => {
            limpiarFiltros();
            table.ajax.reload();

        });
    }

})