import * as instance from "../../../public/js/instance.js"

document.getElementById("link_egresos").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_egresos").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_egresos").closest(".submenu").classList.add("show")
document.getElementById("link_egresos").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    let id_caja = document.querySelector("#id_caja_chica")
    let button_save = document.querySelector("#button_save")
    let button_cancel = document.querySelector("#button_cancel")
    let button_loadSave = document.querySelector("#button_loadSave")

    //Verificacion caja 
    if (id_caja.value == 0) {
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

    const table = $('#table_egresos').DataTable({
        "ajax": {
            'url': $('#url').val() + 'egresos/dataTable',
            'method': 'POST',
            'data': {
                id_caja_chica: id_caja.value
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
                "data": "referencia",
            },
            {
                "data": "tp_comprobante",
            },
            {
                "data": "serie",
            },
            {
                "data": "correlativo",
            },
            {
                "data": "monto",
            },
            {
                "data": "concepto",
            },
            {
                "data": "id",
                render: (data, type, row) => {
                    let newRow = `
                        <a class="btn btnEditRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="EDITAR SERIE | ${row["serie"]}">Editar</a>
                        <a class="btn btnDeleteRegis">Eliminar</a>
                    `
                    return newRow
                },
            }
        ],
        "language": {
            url: './public/plugins/datatable/language/es_es.json',
        },
        "deferRender": true,
        "stateSave": false,
        "pageLength": 20,
    });
    instance.Modal.change_name(["#modal"])
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual")

    //Tabla de egresos
    $('#table_egreso thead').on('click', '#btn_add_egreso', function (e) {
        let tbody = table_egreso.getElementsByTagName("tbody")[0];
        tbody.insertAdjacentHTML('afterbegin', `
            <tr>
                <td>
                <select class='form-select tp_comprobante' required style="width: 100%;">
                 <option value="FACTURA">FACTURA</option>
                 <option value="BOLETA">BOLETA</option>
                 <option value="RECIBO">RECIBO</option>
                </select>
                </td>
                <td><input type='text' class='form-control serie' required style="width: 120px;" oninput="this.value = this.value.toUpperCase()"></td>
                <td><input type='text' class='form-control correlativo' required style="width: 90px;"></td>
                <td><input type='number' class='form-control monto_u' required value='0.00' style="width: 100px;"></td>
                <td><textarea class='form-control concepto' rows='3' style="width: 230px;"></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `);
    });

    //Borrar registros de tabla
    $('#table_egreso tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_egreso tbody").removeChild(this.closest("tr"))
        let tbody = table_egreso.getElementsByTagName("tbody")[0]
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total").textContent = total;
    });

    //Actualizar
    $('#table_egreso').on('change', '.monto_u', function (e) {
        let tbody = table_egreso.getElementsByTagName("tbody")[0];
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total").textContent = total;
    });


    // Funcion para cerrado de modal de liquidacion de caja chica
    $("#modal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        id_egreso.value = ''
        $('#table_egreso tbody tr').remove()
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let tp_com = document.querySelectorAll(".tp_comprobante")
        let serie = document.querySelectorAll(".serie")
        let correlativo = document.querySelectorAll(".correlativo")
        let monto_u = document.querySelectorAll(".monto_u")
        let concepto = document.querySelectorAll(".concepto")
        let detalles = []
        let valid_2 = true;

        Object.values(serie).forEach((e, i) => {
            // Validar campos vacíos
            if (tp_com[i].value.trim() === "" ||
                e.value.trim() === "" ||
                correlativo[i].value.trim() === "" ||
                monto_u[i].value.trim() === "" ||
                concepto[i].value.trim() === "") {
                valid_2 = false;
            }

            // Validar que el monto no sea 0
            if (parseFloat(monto_u[i].value) === 0) {
                valid_2 = false;
                instance.Toast.operacion_informativa("El monto no puede ser 0. Verifica los registros.")
                return false;
            }

            // Agregar tp_com al array de detalles
            detalles.push([tp_com[i].value, e.value, correlativo[i].value, monto_u[i].value, concepto[i].value]);
        });

        if (!valid_2) {
            instance.Toast.operacion_informativa("Ningún campo de la tabla egresos debe estar vacío. Verifica los registros.")
            return false;
        }

        if (id_caja.value != 0) {
            formData.set("id_caja", id_caja.value)
            formData.set("egresos", JSON.stringify(detalles))
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + 'egresos/crud_register', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    $('#modal').modal('toggle')
                    instance.Datatable.reloadTable(table)
                    instance.Toast.operacion_exitosa(data.message.message)
                } else {
                    instance.Toast.operacion_erronea(data.message.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Tiene que tener una caja abierta')
        }
    })

    $('#table_egresos tbody').on('click', '.btnDeleteRegis', function (e) {
        const data = table.row($(this).parents()).data()
        Swal.fire({
            title: 'Necesitamos de tu \nConfirmación',
            html: `<div>
                    <label>Se eliminará del sistema el registro:</label><br>
                    <span class="mt-1 fw-bolder">${data.concepto}</span>
                    <p class="fs-6 mt-3 mb-0 text-success">¿Está Usted de Acuerdo?</p>
                    </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#429ef5',
            cancelButtonColor: '#ff3b3b',
            cancelButtonText: 'No!',
            confirmButtonText: 'Si, Adelante!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData()
                formData.set("id_egreso", data.id)
                fetch(instance._URL_ + 'egresos/delete_register', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                }).then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message)
                        instance.Datatable.reloadTable(table)
                    } else {
                        instance.Toast.operacion_erronea(data.message)
                    }
                }).catch(error => instance.Toast.operacion_erronea(error.message()))
            }
        })
    })
    $('#table_egresos tbody').on('click', '.btnEditRegis', function (e) {
        const data = table.row($(this).parents('tr')).data();

        id_egreso.value = data.id;

        const tbody = document.querySelector("#table_egreso tbody");

        tbody.querySelectorAll("tr.fila_egreso").forEach(tr => tr.remove());

        tbody.insertAdjacentHTML("afterbegin", makeRowEgreso({
            tp_comprobante: data.tp_comprobante,
            serie: data.serie,
            correlativo: data.correlativo,
            monto: data.monto,
            concepto: data.concepto
        }));
    });

    const makeRowEgreso = ({ tp_comprobante, serie, correlativo, monto, concepto }) => `
    <tr class="fila_egreso">
        <td>
            <select class="form-select" name="tp_comprobante[]">
                <option value="FACTURA"  ${tp_comprobante === 'FACTURA' ? 'selected' : ''}>Factura</option>
                <option value="BOLETA"   ${tp_comprobante === 'BOLETA' ? 'selected' : ''}>Boleta</option>
                <option value="RECIBO"   ${tp_comprobante === 'RECIBO' ? 'selected' : ''}>Recibo</option>
            </select>
        </td>
        <td><input type="text"   class="form-control" name="serie[]"       value="${serie}"></td>
        <td><input type="text"   class="form-control" name="correlativo[]" value="${correlativo}"></td>
        <td><input type="number" class="form-control inp_monto" name="monto[]" value="${monto}"></td>
        <td><input type="text"   class="form-control" name="concepto[]"    value="${concepto}"></td>
        <td>
            <button type="button" class="btn p-0 btn_delete_egreso">
                <i class="bi bi-trash text-danger fs-5"></i>
            </button>
        </td>
    </tr>
   `;
});