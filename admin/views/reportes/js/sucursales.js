
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
        return $('#table_sucursales').DataTable({
            "ajax": {
                'url': $('#url').val() + 'reportes/getSucursales',
                'method': 'POST',
                'data': {
                    'ubigeo': $("#ubigeo_filtro").val()
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
                    "data": "sucursal",
                },
                {
                    "data": "cod_sucursal"
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
                },
                {
                    "data": "email",
                },
                {
                    "data": "num_serie"
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
    select.createSelect("#ubigeo_filtro", "#parentUbigeoFiltro")

    //boton exportar EXCEL
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "excel",
                title: 'Reporte de Sucursales',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE SUCURSALES',
            },

        ]
    }).container().appendTo($('.export_excel'));

    //boton exportar CSV
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "csv",
                title: 'Reporte de Sucursales',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE SUCURSALES',
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
                    title: 'Reporte de Sucursales',
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
                    sheetName: 'REPORTE DE SUCURSALES',
                },
            ]
        }).container().appendTo($('.export_csv'));
    })
});