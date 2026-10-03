
import { Select } from "../../../public/js/lib/select.js";
import { Validate } from "../../../public/js/lib/validate.js";
import { Datatable } from "../../../public/js/lib/datatable.js";
import { PDF } from "../../../public/js/lib/pdf.js";

document.getElementById("link_reporte").parentElement.parentElement.classList.add("active")
document.getElementById("link_reporte").parentElement.parentElement.children[0].classList.remove("collapsed")
document.getElementById("link_reporte").parentElement.classList.add("show")
document.getElementById("link_reporte").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const getDatatable = () => {
        return $('#table_ventas').DataTable({
            "ajax": {
                'url': $('#url').val() + 'reportes/getVentaGeneral',
                'method': 'POST',
                'data': {
                    fechaini: $("#fechaini_filtro").val(),
                    fechafin: $("#fechafin_filtro").val(),
                    tDocu: $("#tdocu_filtro").val(),
                    serie: $("#serie_filtro").val(),
                    tpagos: $("#tpagos_filtro").val(),
                    estado: $("#estado_filtro").val(),
                    cliente: $("#cliente_filtro").val(),
                    tpCliente: $("#tpcliente_filtro").val(),
                    usuario: $("#usuario_filtro").val(),
                    cjchica: $("#cjchica_filtro").val(),
                }
            },
            "destroy": true,
            scrollX: true,
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
                    "data": "nombres_cli",
                    render: function (data, type, row) {
                        if (row["razon_social"]) {
                            return `${row["razon_social_cli"]}`;
                        } else {
                            return `${row["nombres_cli"]} ${row["apellidos_cli"]}`;
                        }
                    }
                },
                {
                    "data": "nombres_cli",
                    render: function (data, type, row) {
                        return `
                    ${row["tp_docu_cli"]}: ${row["num_docu_cli"]}
                    `
                    }
                },
                {
                    "data": "tp_cliente",
                    render: function (data, type, row) {
                        if (row["tp_cliente"] == "ABONADO") {
                            return `<span class='badge' style="background-color:#7634d1">ABONADO</span>`
                        } else {
                            return `<span class='badge bg-primary'>NORMAL</span>`
                        }
                    }
                },
                {
                    "data": "descripcion_tp_c",
                },
                {
                    "data": "cod_voucher",
                    render: function (data, type, row) {
                        if (row["cod_voucher"]) {
                            return row["cod_voucher"];
                        } else {
                            return `-----`
                        }
                    }
                },
                {
                    "data": "tipo_comprobante",
                },
                {
                    "data": "serie",
                },
                {
                    "data": "num_docu_venta",
                },
                {
                    "data": "estado_venta",
                    render: function (data, type, row) {
                        let text = "";
                        switch (row["estado_venta"]) {
                            case "a":
                                text = "<span class='badge bg-danger'>ANULADO</span>"
                                break;
                            case "p":
                                text = "<span class='badge bg-warning'>PENDIENTE</span>"
                                break;
                            case "c":
                                text = "<span class='badge bg-success'>CANCELADO</span>"
                                break;
                            default:
                                break;
                        }
                        return text;
                    }
                },
                {
                    "data": "caja_chica",
                },
                {
                    "data": "tipo_moneda",
                },
                {
                    "data": "precio_total",
                },
                {
                    "data": "motivo_anular",
                    render: function (data, type, row) {
                        if (row["motivo_anular"]) {
                            return `${row["motivo_anular"]}`;
                        } else {
                            return `-----`;
                        }
                    }
                },
                {
                    "data": "nombres_user",
                    render: function (data, type, row) {
                        return `${row["nombres_user"]} ${row["apellidos_user"]}`
                    }
                },
                {
                    "data": "sucursal",
                },
            ],
            "language": {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
            },

        });
    };

    let table = getDatatable();

    // Instancias
    const validate = new Validate();
    const datatable = new Datatable(table);
    const select = new Select();
    const pdf = new PDF();
    const _URL_ = document.querySelector("#url").value;

    // Select 
    select.createSelect("#cliente_filtro", "#parentClienteFiltro")
    select.createSelect("#usuario_filtro", "#parentUsuarioFiltro")
    select.createSelect("#cjchica_filtro", "#parentCjChicaFiltro")

    //boton exportar EXCEL
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "excel",
                title: 'Reporte General',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE',
            },

        ]
    }).container().appendTo($('.export_excel'));

    //boton exportar CSV
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "csv",
                title: 'Reporte General',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE GENERAL',
            },
        ]
    }).container().appendTo($('.export_csv'));

    // FILTRAR
    let btn_filtrar = document.querySelector("#btnFiltrar");
    btn_filtrar.addEventListener("click", (e) => {
        table = getDatatable();
        //boton exportar EXCEL
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: "excel",
                    title: 'Reporte General',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE',
                },

            ]
        }).container().appendTo($('.export_excel'));

        //boton exportar CSV
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: "csv",
                    title: 'Reporte General',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE GENERAL',
                },
            ]
        }).container().appendTo($('.export_csv'));
    })
});