import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {

    let form = document.querySelector("#form_salida");
    let p_partida = document.querySelector("#p_partida");
    let ubigeoP = document.querySelector("#ubigeoP");
    let p_llegada = document.querySelector("#p_llegada");
    let ubigeoLle = document.querySelector("#ubigeoLle");
    let id_salida = document.querySelector("#id_salida");

    let button_save = document.querySelector("#button_save_salida");
    let button_cancel = document.querySelector("#button_cancel_salida");
    let button_loadSave = document.querySelector("#button_loadSave_salida");

    window.destinoSalida = new TomSelect('#destino_salida', {
        valueField: "cod_ubigeo",
        labelField: "nombre",
        searchField: "nombre",
        placeholder: "Buscar destino...",

        onInitialize: function () {
            const inputInterno = this.control_input;

            const randomSuffix = Date.now() + "_" + Math.random().toString(36).substring(2);
            inputInterno.setAttribute("autocomplete", "off");
            inputInterno.setAttribute("autocorrect", "off");
            inputInterno.setAttribute("autocapitalize", "off");
            inputInterno.setAttribute("spellcheck", "false");
            inputInterno.setAttribute("data-form-type", "other");
            inputInterno.setAttribute("data-lpignore", "true");
            inputInterno.setAttribute("data-1p-ignore", "true");
            inputInterno.setAttribute("name", "no_autofill_" + randomSuffix);
            inputInterno.setAttribute("id", "no_autofill_" + randomSuffix);
            inputInterno.setAttribute("role", "presentation");

            inputInterno.addEventListener('change', function (e) {
                if (e.isTrusted === false) {
                    e.stopImmediatePropagation();
                }
            }, true);
        },

        load: function (query, callback) {
            if (!query.length) return callback();
            let FormD = new FormData();
            FormD.set("q", query);
            fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
                method: "POST",
                body: FormD
            })
                .then(res => res.json())
                .then(json => {
                    if (json.success) callback(json.items);
                    else callback();
                })
                .catch(() => callback());
        }
    });

    instance.Modal.change_name(["#modal_salida"]);

    instance.select.createSelect("#vehiculo", "#div_parentVehiculo");
    instance.select.createSelect("#conductor", "#div_parentConductor");
    instance.select.createSelect("#salida", "#divParentSalida");

    let pendingSelectId = null;

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(form);
        let data = [
            formData.get("vehiculo"),
            formData.get("fecha_salida"),
            formData.get("hora_salida")
        ];

        if (instance.Validate.validateData(data)) {
            formData.set("direccion_origen", p_partida.value);
            formData.set("direccion_destino", p_llegada.value);
            formData.set("ubigeo_origen", ubigeoP.value);
            formData.set("ubigeo_destino", ubigeoLle.value);

            button_save.classList.add("d-none");
            button_cancel.classList.add("d-none");
            button_loadSave.classList.remove("d-none");

            fetch(instance._URL_ + "programacion_salida/crud_register", {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        instance.Toast.operacion_exitosa(data.message);

                        pendingSelectId = data.id_salida;
                        setUbigeoValue(destinoSalida, ubigeoLle.value);

                        $('#modal_salida').modal('toggle');
                    } else {
                        instance.Toast.operacion_erronea(data.message);
                    }
                })
                .catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save.classList.remove("d-none");
                    button_cancel.classList.remove("d-none");
                    button_loadSave.classList.add("d-none");
                });
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos');
        }
    });

    let salidas_data = [];

    $("#destino_salida").on("change", function () {
        let formData = new FormData();
        formData.append("ubigeo_destino", this.value);

        fetch(instance._URL_ + 'programacion_salida/get_salidas', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    salidas_data = data.data;

                    const $salida = $("#salida");
                    $salida.empty();
                    $salida.append(new Option("— Seleccione —", "", true, true));

                    salidas_data.forEach((item) => {
                        const texto = `${item.placa} | ${item.fecha_salida} | ${item.direccion_origen} | ${item.direccion_destino}`;
                        $salida.append(new Option(texto, item.id_salida));
                    });

                    if (pendingSelectId) {
                        $salida.val(pendingSelectId).trigger("change");
                        pendingSelectId = null;
                    }
                }
            })
            .catch(error => console.error("❌ Error:", error));
    });

    $("#salida").on("change", function () {
        const id_seleccionado = this.value;
        const item = salidas_data.find(s => s.id_salida == id_seleccionado);

        if (!item) {
            ubigeoP.value = "";
            ubigeoLle.value = "";
            p_partida.value = "";
            p_llegada.value = "";
            return;
        }

        setUbigeoValue(window.ubigeoPartida, item.ubigeo_origen);
        setUbigeoValue(window.ubigeoLlegada, item.ubigeo_destino);
        p_partida.value = item.direccion_origen;
        p_llegada.value = item.direccion_destino;
    });

    function setUbigeoValue(ts, codUbigeo) {
        if (!codUbigeo) {
            ts.clear();
            return;
        }

        let FormD = new FormData();
        FormD.set("pre", codUbigeo);

        fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
            method: "POST",
            body: FormD
        })
            .then(res => res.json())
            .then(json => {
                if (json.success && json.items.length) {
                    let item = json.items[0];
                    ts.addOption(item);
                    ts.setValue(item.cod_ubigeo);
                }
            });
    }

    $("#modal_salida").on("hidden.bs.modal", (e) => {
        $("#modal_salida").find("form").trigger("reset");
        let forms = document.querySelectorAll(".needs-validation");
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated");
        });
        if (id_salida) id_salida.value = "";
        $("#vehiculo").val("").trigger("change");
        $("#conductor").val("").trigger("change");
    });
});