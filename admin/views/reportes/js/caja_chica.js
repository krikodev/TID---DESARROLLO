
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
        return $('#table_cajachica').DataTable({
            "ajax": {
                'url': $('#url').val() + 'reportes/getCajaChicas',
                'method': 'POST',
                'data': {
                    fechaini: $("#f_ini_filtro").val(),
                    fechafin: $("#f_fin_filtro").val(),
                    sucursal: $("#sucursal_filtro").val(),
                    usuario: $("#usuario_filtro").val(),
                    estado: $("#estado_filtro").val(),
                    turno: $("#turno_filtro").val(),
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
                    "data": "descripcion",
                },
                {
                    "data": "id_usuario",
                    render: function (data, type, row) {
                        return `
                        <label class="m-0 p-0">${row["full_names"]}</label><br>
                        <label class="m-0 p-0 text-secondary">${row["tipo_usuario"]}</label>
                        `
                    }
                },
                {
                    "data": "fecha_inicio",
                },
                {
                    "data": "fecha_fin",
                },
                {
                    "data": "monto_inicial",
                    render: function (data, type, row) {
                        return `S/ ${row["monto_inicial"]}`
                    }
                },
                {
                    "data": "monto_final",
                    render: function (data, type, row) {
                        return `S/ ${row["monto_final"]}`
                    }
                },
                {
                    "data": "estado",
                    render: function (data, type, row) {
                        if (row["estado"]) {
                            return `<span class="badge bg-success">Abierto</span>`
                        } else {
                            return `<span class="badge bg-primary">Cerrado</span>`
                        }
                    }
                },
                {
                    "data": "turno",
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
    select.createSelect("#sucursal_filtro", "#parentSucursalFiltro")
    select.createSelect("#usuario_filtro", "#parentUsuarioFiltro")

    //boton exportar EXCEL
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "excel",
                title: 'Reporte de Caja Chicas',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE CAJA CHICA',
            },

        ]
    }).container().appendTo($('.export_excel'));

    //boton exportar CSV
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: "csv",
                title: 'Reporte de Caja Chica',
                text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                autoFilter: true,
                sheetName: 'REPORTE DE CAJA CHICA',
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
                    title: 'Reporte de Caja Chica',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-excel fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE DE CAJA CHICA',
                },

            ]
        }).container().appendTo($('.export_excel'));

        //boton exportar CSV
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: "csv",
                    title: 'Reporte de Caja Chica',
                    text: `
                    <button class='btn btn-success'>
                        <i class="fa-solid fa-file-csv fs-1"></i>
                    </button>`,
                    autoFilter: true,
                    sheetName: 'REPORTE DE CAJA CHICA',
                },
            ]
        }).container().appendTo($('.export_csv'));
    })
});