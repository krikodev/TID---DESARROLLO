import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    // Variables Form
    let form_liquidar = document.querySelector("#form_liquidar_terminal")

    let button_save_l = document.querySelector("#button_save_l")
    let button_cancel_l = document.querySelector("#button_cancel_l")
    let button_loadSave_l = document.querySelector("#button_loadSave_l")

    let form_liquidar_usuario = document.querySelector("#form_liquidar_usuario")
    let button_save_lu = document.querySelector("#button_save_lu")
    let button_cancel_lu = document.querySelector("#button_cancel_lu")
    let button_loadSave_lu = document.querySelector("#button_loadSave_lu")
    instance.Validate.allowInputMoney(["#porcentaje_empresa"]);

    $("#modal_liquidacion_terminal").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_liquidar) {
            form_liquidar.classList.remove("was-validated")
        })
        $("#tp_porcen").val("1").trigger("change")
        $('#table_egreso tbody tr').remove()
    })

    $("#modal_liquidacion_usuario").on("hidden.bs.modal", (e) => {
        $("#modal").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_liquidar) {
            form_liquidar_usuario.classList.remove("was-validated")
        })
        $('#table_egreso_usuario tbody tr').remove()
    })

    //Tabla de egresos
    $('#table_egreso thead').on('click', '#btn_add_egreso', function (e) {
        let tbody = table_egreso.getElementsByTagName("tbody")[0];
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

    $('#table_egreso_usuario thead').on('click', '#btn_add_egreso_usuario', function (e) {
        let tbody = table_egreso_usuario.getElementsByTagName("tbody")[0];
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
                <td><input type='number' class='form-control monto_u' required value='0.00' style="width: 100px;"></td>
                <td><textarea class='form-control concepto' rows='3' style="width: 230px;"></textarea></td>
                <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
            </tr>
        `);
    });

    $('#table_egreso_usuario tbody').on('click', '.button_deleteItem', function (e) {
        e.preventDefault();
        document.querySelector("#table_egreso_usuario tbody").removeChild(this.closest("tr"))
        let tbody = table_egreso_usuario.getElementsByTagName("tbody")[0]
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total_usuario").textContent = total;
    });

    $('#table_egreso_usuario').on('change', '.monto_u', function (e) {
        let tbody = table_egreso_usuario.getElementsByTagName("tbody")[0];
        let precios = Array.from(tbody.querySelectorAll(".monto_u")).map(input => parseFloat(input.value) || 0);
        let total = precios.reduce((acum, e) => acum + e, 0).toFixed(2);
        tbody.querySelector("#monto_total_usuario").textContent = total;
    });

    $(document).ready(function () {
        $("#tp_porcen").val(1).trigger("change");
    });

    $("#tp_porcen").on('change', function () {
        let tp_porcen = $(this).val();
        let monto_p = document.querySelector("#div_porcen")
        let porcen = document.querySelector("#div_monto")
        let monto_por = document.querySelector("#div_porcen")
        let porcentaje = document.querySelector("#div_monto")
        if (tp_porcen == 1) {
            monto_p.classList.remove('d-none');
            monto_por.setAttribute("required", true);
            porcentaje.removeAttribute("required");
            porcen.classList.add('d-none');
            $("#porcen").val(0);
        } else if (tp_porcen == 2) {
            porcen.classList.remove('d-none');
            monto_p.classList.add('d-none');
            monto_por.removeAttribute("required");
            porcentaje.setAttribute("required", true);
            $("#monto").val(0);
        }
    });

    //Envio de registro de liquidacion
    form_liquidar.addEventListener("submit", (e) => {
        e.preventDefault()
        let id_programacion_l = document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value ? document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value : '';
        if (id_programacion_l == '') {
            Swal.fire({
                title: 'Error',
                text: 'No se ha seleccionado una programagramacion para liquidación',
                icon: 'error',
            })
        }
        let formData = new FormData(form_liquidar)

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

        formData.set("detalles", JSON.stringify(detalles))
        formData.set("id_programacion", id_programacion_l)
        button_save_l.classList.add("d-none")
        button_cancel_l.classList.add("d-none")
        button_loadSave_l.classList.remove("d-none")
        fetch(instance._URL_ + 'pasaje/liquidar_terminal_v', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status);
            return response.json();
        }).then(data => {
            if (data.success) {
                $("#modal_liquidacion_terminal").modal('toggle')
                fetch(instance._URL_ + instance.CONSTS.URL.IMPRESION_PASAJE.LIQUIDACION_TERMINAL + id_programacion_l + "/" + instance._ID_TERMINAL_SESION, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.blob(); // Obtener el PDF como Blob
                }).then(blob => {
                    var url = URL.createObjectURL(blob);

                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none'; // Ocultar el iframe
                    iframe.src = url;

                    document.body.appendChild(iframe);
                    iframe.onload = function () {
                        iframe.contentWindow.print(); // Imprimir el PDF
                    };
                    instance.Toast.operacion_exitosa(data.message)
                    // Recargar la página después de un tiempo para asegurar que la impresión se complete
                    setTimeout(() => {
                        window.location.reload();
                    }, 10000); // 1000 milisegundos (1 segundo)
                }).catch(error => {
                    instance.Toast.operacion_erronea(error.message);
                }).finally(() => {
                    button_save_l.classList.remove("d-none")
                    button_cancel_l.classList.remove("d-none")
                    button_loadSave_l.classList.add("d-none")
                });
            } else {
                instance.Toast.operacion_erronea(data.message);
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message));
    })

    //Envio de registro de liquidacion usuario
    form_liquidar_usuario.addEventListener("submit", (e) => {
        e.preventDefault()
        let id_programacion_l = document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value ? document.querySelector(".btn_selection_programacion.active").closest("tr").querySelector(".id_programacion").value : '';
        if (id_programacion_l == '') {
            Swal.fire({
                title: 'Error',
                text: 'No se ha seleccionado una programagramacion para liquidación',
                icon: 'error',
            })
        }
        let formData = new FormData(form_liquidar_usuario)

        let tp_com = document.querySelectorAll(".tp_comprobante")
        let serie = document.querySelectorAll(".serie")
        let correlativo = document.querySelectorAll(".correlativo")
        let monto_u = document.querySelectorAll(".monto_u")
        let concepto = document.querySelectorAll(".concepto")
        let detalles = []
        let valid_2 = true;

        Object.values(serie).forEach((e, i) => {
            if (tp_com[i].value.trim() === "" ||
                e.value.trim() === "" ||
                correlativo[i].value.trim() === "" ||
                monto_u[i].value.trim() === "" ||
                concepto[i].value.trim() === "") {
                valid_2 = false;
            }

            if (parseFloat(monto_u[i].value) === 0) {
                valid_2 = false;
                instance.Toast.operacion_informativa("El monto no puede ser 0. Verifica los registros.")
                return false;
            }

            detalles.push([tp_com[i].value, e.value, correlativo[i].value, monto_u[i].value, concepto[i].value]);
        });

        if (!valid_2) {
            instance.Toast.operacion_informativa("Ningún campo de la tabla egresos debe estar vacío. Verifica los registros.")
            return false;
        }

        formData.set("detalles", JSON.stringify(detalles));
        formData.set("id_programacion", id_programacion_l);
        button_save_lu.classList.add("d-none");
        button_cancel_lu.classList.add("d-none");
        button_loadSave_lu.classList.remove("d-none");
        fetch(instance._URL_ + 'pasaje/liquidar_usuario_p', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(response.status);
            return response.json();
        }).then(data => {
            if (data.success) {
                $("#modal_liquidacion_usuario").modal('toggle')

                let botones = '';

                for (const [nombre, link] of Object.entries(data.links)) {
                    if (!link) continue;
                    botones += `
                    <a data-link="${link}" 
                    class="d-flex flex-column text-decoration-none py-2 btn-print" 
                    style="cursor:pointer;" target="_blank">
                        <i class="fa-light fa-receipt fs-2 text-secondary animated pulse infinite"></i>
                        <span class="text-secondary mt-1">Imprimir ${nombre}</span>
                    </a>`;
                }
                Swal.fire({
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    html: `
                    <div class="d-flex flex-column justify-content-center align-items-center pt-2 pb-0">
                    <i class="fa-solid fa-circle-check mb-3" style="font-size:50px; color:#34d16e"></i>
                    <h5 class="mb-3">${data.message}</h5>
                    ${botones}
                    </div>
                `,
                    confirmButtonText: 'Continuar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else {
                instance.Toast.operacion_erronea(data.message);
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message));
    })

    // Lógica de agregar egreso
    const add_egreso_venta = document.getElementById('add_egreso_venta');
    const table_egresos_venta = document.getElementById('table_egresos_venta');

    // Campos del formulario de egresos
    const tp_comprobante_egreso = document.getElementById('tp_comprobante_egreso');
    const serie_egreso = document.getElementById('serie_egreso');
    const correlativo_egreso = document.getElementById('correlativo_egreso');
    const monto_egreso = document.getElementById('monto_egreso');
    const concepto_egreso = document.getElementById('concepto_egreso');

    // ─── Validación ────────────────────────────────────────────────────────────────
    const validarCamposEgreso = () => {
        const serie = serie_egreso.value.trim();
        const correlativo = correlativo_egreso.value.trim();
        const monto = parseFloat(monto_egreso.value);
        const concepto = concepto_egreso.value.trim();

        if (!serie) {
            instance.Toast.operacion_erronea("Debe ingresar la Serie del comprobante");
            serie_egreso.focus();
            return false;
        }
        if (!correlativo) {
            instance.Toast.operacion_erronea("Debe ingresar el Correlativo del comprobante");
            correlativo_egreso.focus();
            return false;
        }
        if (!monto || isNaN(monto) || monto <= 0) {
            instance.Toast.operacion_erronea("El monto debe ser mayor a 0");
            monto_egreso.focus();
            return false;
        }
        if (!concepto) {
            instance.Toast.operacion_erronea("Debe ingresar el Concepto del egreso");
            concepto_egreso.focus();
            return false;
        }
        return true;
    };

    // ─── Agregar fila ──────────────────────────────────────────────────────────────
    add_egreso_venta.addEventListener("click", () => {
        if (!validarCamposEgreso()) return;

        const tbody = table_egresos_venta.getElementsByTagName("tbody")[0];
        const tipo = tp_comprobante_egreso.value;
        const serie = serie_egreso.value.trim();
        const correlativo = correlativo_egreso.value.trim();
        const monto = parseFloat(monto_egreso.value).toFixed(2);
        const concepto = concepto_egreso.value.trim();

        const makeRow = () => `
        <tr>
            <td><label class="tp_comprobante_egreso">${tipo}</label></td>
            <td><label class="serie_egreso">${serie}</label></td>
            <td><label class="correlativo_egreso">${correlativo}</label></td>
            <td><label class="monto_egreso">${monto}</label></td>
            <td><label class="concepto_egreso">${concepto}</label></td>
            <td>
                <button type="button" class="btn button_deleteEgreso p-0 pt-2">
                    <i class="bi bi-trash text-danger fs-5"></i>
                </button>
            </td>
        </tr>
    `;

        tbody.insertAdjacentHTML("afterbegin", makeRow());
        limpiarCamposEgreso();
    });

    // ─── Eliminar fila (delegación de eventos) ─────────────────────────────────────
    table_egresos_venta.addEventListener("click", (e) => {
        const btnEliminar = e.target.closest(".button_deleteEgreso");
        if (!btnEliminar) return;

        const fila = btnEliminar.closest("tr");
        fila.remove();
    });

    // ─── Limpiar formulario ────────────────────────────────────────────────────────
    const limpiarCamposEgreso = () => {
        tp_comprobante_egreso.value = "FACTURA";
        serie_egreso.value = "";
        correlativo_egreso.value = "";
        monto_egreso.value = "";
        concepto_egreso.value = "";
        serie_egreso.focus();
    };
})