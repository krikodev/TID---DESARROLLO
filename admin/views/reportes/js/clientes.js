
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
        return $('#table_clientes').DataTable({
            "ajax": {
                'url': $('#url').val() + 'reportes/getClientes',
                'method': 'POST',
                'data': {
                    tpCliente: $("#tpcliente_filtro").val(),
                    tdocu: $("#tdocu_filtro").val(),
                    sucursal: $("#sucursal_filtro").val(),
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
                    "data": "cliente",
                },
                {
                    "data": "tp_docu"
                },
                {
                    "data": "num_docu",
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
                    "data": "cod_ubigeo",
                },
                {
                    "data": "depa_ubigeo",
                },
                {
                    "data": "provi_ubigeo",
                },
                {
                    "data": "distri_ubigeo",
                },
                {
                    "data": "direccion",
                },
                {
                    "data": "celular",
                    render: function (data, type, row) {
                        if (row["celular"].length!=0) {
                            return row["celular"];
                        } else {
                            return `---------`;
                        }
                    }
                },
                {
                    "data": "sucursal"
                }

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
    select.createSelect("#sucursal_filtro", "#parentSucursalFiltro")

    //boton exportar EXCEL
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "excel",
                title: 'Reporte de Clientes',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE CLIENTES',
            },

        ]
    }).container().appendTo($('.export_excel'));

    //boton exportar CSV
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "csv",
                title: 'Reporte de Clientes',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE CLIENTES',
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
                    title: 'Reporte de Clientes',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE DE CLIENTES',
                },

            ]
        }).container().appendTo($('.export_excel'));

        //boton exportar CSV
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: "csv",
                    title: 'Reporte de Clientes',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE DE CLIENTES',
                },
            ]
        }).container().appendTo($('.export_csv'));
    })
});