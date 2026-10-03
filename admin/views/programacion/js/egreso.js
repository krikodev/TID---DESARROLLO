document.addEventListener("DOMContentLoaded", function (event) {
    let form = document.getElementById("form_egreso");
    let button_save = document.querySelector("#button_save_p");
    let button_cancel = document.querySelector("#button_cancel_p");
    let button_loadSave = document.querySelector("#button_loadSave_p");

    // Funcion para cerrado de modal de liquidacion de caja chica
    $("#modal_egreso").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        id_egreso.value = ''
        $('#table_egreso_p tbody tr').remove()
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
                    $('#modal_egreso').modal('toggle')
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


    //Inicio de nueva tabla egresos
    function recalcularTotal() {
        let tbody = document.getElementById("table_egreso_p").getElementsByTagName("tbody")[0];
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        document.querySelector("#monto_total_p").textContent = "S/ " + total;
    }

    // Agregar fila
    $('#table_egreso_p thead').on('click', '#btn_add_egreso_p', function (e) {
        let tbody = document.getElementById("table_egreso_p").getElementsByTagName("tbody")[0];
        tbody.insertAdjacentHTML('afterbegin', `
        <tr>
            <td>
                <select class='form-control tp_comprobante' required style="width: 100%;">
                    <option value="FACTURA">FACTURA</option>
                    <option value="BOLETA">BOLETA</option>
                    <option value="RECIBO">RECIBO</option>
                </select>
            </td>
            <td><input type='text' class='form-control serie' required style="width: 120px;" oninput="this.value = this.value.toUpperCase()"></td>
            <td><input type='text' class='form-control correlativo' required style="width: 90px;"></td>
            <td><input type='number' class='form-control monto_u' required value='0.00' min='0' style="width: 100px;"></td>
            <td><textarea class='form-control concepto' rows='3' style="width: 230px;"></textarea></td>
            <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
        </tr>
    `);
    });

    // Borrar fila
    $('#table_egreso_p tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        this.closest("tr").remove();
        recalcularTotal();
    });

    $('#table_egreso_p').on('change', '.monto_u', function (e) {
        recalcularTotal();
    });

});