import * as instance from "../../../public/js/instance.js";

// document.getElementById("link_cuenta_bnk").classList.add("active");

document.addEventListener("DOMContentLoaded", () => {

    //==========================
    // DATATABLE
    //==========================
    const table = $("#table_cuenta_bancaria").DataTable({

        ajax: {
            url: instance._URL_ + "cuenta_bnk/dataTable",
            method: "POST",
            beforeSend: () => {
                document.getElementById("loader").classList.remove("d-none");
            },
            complete: () => {
                document.getElementById("loader").classList.add("d-none");
            }
        },

        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        ordering: false,
        dom: "rtip",

        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],

        pageLength: 20,

        columns: [

            {
                data: "banco"
            },

            {
                data: "descripcion"
            },

            {
                data: "numero_cuenta"
            },

            {
                data: "moneda"
            },

            {
                data: "mostrar_reportes",
                render: (data) => {

                    if (data == 1) {
                        return '<span class="badge bg-success">SI</span>';
                    }

                    return '<span class="badge bg-secondary">NO</span>';

                }
            },

            {
                data: "estado",
                render: (data) => {

                    if (data == "ACTIVO") {
                        return '<span class="badge bg-success">ACTIVO</span>';
                    }

                    return '<span class="badge bg-danger">INACTIVO</span>';

                }
            },

            {
                data: "id_cuenta",
                render: (data, type, row) => {

                    return `
    <a class="btn btnEdit"
        data-bs-toggle="modal"
        data-bs-target="#modal"
        data-bs-whatever="EDITAR CUENTA | ${row.descripcion}"
        title="Editar">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>

    <a class="btn btnDelete"
        title="Desactivar">
        <i class="fa-solid fa-ban"></i>
    </a>
`;

                }
            }

        ],

        language: {
            url: "./public/plugins/datatable/language/es_es.json"
        }

    });



    //==========================
    // VARIABLES
    //==========================

    let form = document.querySelector("#form_register");

    let id_cuenta = document.querySelector("#id_cuenta");
    let id_banco = document.querySelector("#id_banco");
    let descripcion = document.querySelector("#descripcion");
    let tipo_cuenta = document.querySelector("#tipo_cuenta");
    let numero_cuenta = document.querySelector("#numero_cuenta");
    let cci = document.querySelector("#cci");
    let moneda = document.querySelector("#moneda");
    let estado = document.querySelector("#estado");
    let mostrar_reportes = document.querySelector("#mostrar_reportes");

    let button_save = document.querySelector("#btnGuardar");


    //==========================
    // CONFIGURACIONES
    //==========================

    instance.Modal.change_name(["#modal"]);

    instance.Datatable.inputSearch(
        table,
        ".inputSearch",
        ".btnSearch",
        "manual"
    );

    instance.select.createSelect("#id_banco", "#div_parentBanco");
    instance.select.createSelect("#tipo_cuenta", "#div_parentTipoCuenta");
    instance.select.createSelectSinSearch("#moneda", "#div_parentMoneda");
    instance.select.createSelectSinSearch("#estado", "#div_parentEstado");


    //==========================
    // LIMPIAR MODAL
    //==========================

    $("#modal").on("hidden.bs.modal", () => {

        form.reset();

        id_cuenta.value = "";

        $("#id_banco").val("").trigger("change");

        $("#tipo_cuenta").val("AHORROS").trigger("change");

        $("#moneda").val("SOLES").trigger("change");

        $("#estado").val("ACTIVO").trigger("change");

        mostrar_reportes.checked = false;

        let forms = document.querySelectorAll(".needs-validation");

        Array.prototype.slice.call(forms).forEach((form) => {
            form.classList.remove("was-validated");
        });

    });



    //==========================
    // GUARDAR
    //==========================

    form.addEventListener("submit", (e) => {

        e.preventDefault();

        let formData = new FormData(form);

        formData.set(
            "mostrar_reportes",
            mostrar_reportes.checked ? 1 : 0
        );

        let data = [

            formData.get("id_banco"),
            formData.get("descripcion"),
            formData.get("tipo_cuenta"),
            formData.get("numero_cuenta")

        ];

        if (!instance.Validate.validateData(data)) {

            Swal.fire({

                icon: "error",

                title: "Operación errónea",

                text: "Complete correctamente los campos."

            });

            return;

        }

        button_save.disabled = true;

        fetch(instance._URL_ + "cuenta_bnk/crud_register", {

            method: "POST",

            body: formData

        })

            .then(response => {

                if (!response.ok)
                    throw new Error(response.status);

                return response.json();

            })

            .then(data => {

                if (data.success) {

                    instance.Toast.operacion_exitosa(data.message);

                    $("#modal").modal("hide");

                    instance.Datatable.reloadTable(table);

                }
                else {

                    instance.Toast.operacion_erronea(data.message);

                }

            })

            .catch(error => {

                instance.Toast.operacion_erronea(error.message);

            })

            .finally(() => {

                button_save.disabled = false;

            });

    });



    //==========================
    // EDITAR
    //==========================

    $("#table_cuenta_bancaria tbody").on("click", ".btnEdit", function () {

        const data = table.row($(this).parents("tr")).data();

        id_cuenta.value = data.id_cuenta;
        console.log(data)
        $("#id_banco")
            .val(data.id_banco)
            .trigger("change");

        descripcion.value = data.descripcion;

        $("#tipo_cuenta")
            .val(data.tipo_cuenta)
            .trigger("change");

        numero_cuenta.value = data.numero_cuenta;

        cci.value = data.cci;

        $("#moneda")
            .val(data.moneda)
            .trigger("change");

        $("#estado")
            .val(data.estado)
            .trigger("change");

        mostrar_reportes.checked = data.mostrar_reportes == 1;

    });



    //==========================
    // DESACTIVAR
    //==========================

    $("#table_cuenta_bancaria tbody").on("click", ".btnDelete", function () {

        const data = table.row($(this).parents("tr")).data();

        Swal.fire({

            title: "¿Desea desactivar la cuenta bancaria?",

            html: `
                <b>${data.descripcion}</b>
                <br><br>
                La cuenta dejará de estar disponible.
            `,

            icon: "warning",

            showCancelButton: true,

            confirmButtonText: "Sí, desactivar",

            cancelButtonText: "Cancelar"

        }).then((result) => {

            if (!result.isConfirmed)
                return;

            let formData = new FormData();

            formData.set("id_cuenta", data.id_cuenta);

            fetch(instance._URL_ + "cuenta_bnk/delete_register", {

                method: "POST",

                body: formData

            })

                .then(response => {

                    if (!response.ok)
                        throw new Error(response.status);

                    return response.json();

                })

                .then(data => {

                    if (data.success) {

                        instance.Toast.operacion_exitosa(data.message);

                        instance.Datatable.reloadTable(table);

                    }
                    else {

                        instance.Toast.operacion_erronea(data.message);

                    }

                })

                .catch(error => {

                    instance.Toast.operacion_erronea(error.message);

                });

        });

    });

});