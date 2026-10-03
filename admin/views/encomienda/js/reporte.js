import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    let tp_reporte = document.querySelector("#tp_reporte")
    let terminal_origenReporte = document.querySelector("#terminal_origenReporte")
    let terminal_destinoReporte = document.querySelector("#terminal_destinoReporte")
    let programacion_destinoReporte = document.querySelector("#programacion_destinoReporte")
    let cliente_reporte = document.querySelector("#cliente_reporte")
    let div_imprimir = document.querySelector("#div_imprimir")
    let btn_generarReporte = document.querySelector("#btn_generarReporte")
    let fecha_inicio = document.querySelector("#fecha_inicio")
    let fecha_fin = document.querySelector("#fecha_fin")
    instance.select.createSelect("#terminal_origenReporte", "#div_parentTerminalOrigenReporte", "SELECCIONE", true)
    instance.select.createSelect("#terminal_destinoReporte", "#div_parentTerminalDestinoReporte", "SELECCIONE", true)
    instance.select.createSelect("#programacion_destinoReporte", "#div_parentProgramacionDestinoReporte", "SELECCIONE", true);
    instance.select.createSelect("#cliente_reporte", "#div_parentClienteReporte", "SELECCIONE", true);

    $("#modal").on("hidden.bs.modal", (e) => {
        div_imprimir.classList.add("d-none")
    })

    $("#modal_reporte").on("show.bs.modal", (e) => {
        $("#tp_reporte").val($("#tp_reporte").val()).trigger("change")
    })

    $("#terminal_origenReporte").change(function (e) {
        e.preventDefault();
        programacion_destinoReporte.innerHTML = ""
        let formData = new FormData()
        formData.set("id_terminalOrigen", terminal_origenReporte.value)
        if (tp_reporte.value != "vehiculo") {
            // Destino 
            fetch(instance._URL_ + "encomienda/get_terminalDestinoReporte", {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        terminal_destinoReporte.innerHTML = ""
                        Object.values(data.message).forEach((e, i, array) => {
                            terminal_destinoReporte.insertAdjacentHTML('beforeend', `
                                <option value="${e.id_terminal}">${e.nombre}</option>
                            `)
                        })
                    }
                })
                .catch(error => instance.Toast.operacion_erronea(error.message))
        } else {
            // Programación
            fetch(instance._URL_ + "encomienda/get_programacionTerminalOrigenReporte", {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status())
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        programacion_destinoReporte.innerHTML = ""
                        Object.values(data.message).forEach((e, i, array) => {
                            programacion_destinoReporte.insertAdjacentHTML('beforeend', `
                                <option value="${e.id_programacion}">${e.fecha_salida.split("-").reverse().join("-")} ${e.hora_salida} - ${e.placa} - ${e.tp_servicio_pasaje}</option>
                            `)
                        })
                    }
                })
                .catch(error => instance.Toast.operacion_erronea(error.message))
        }
    });

    $("#terminal_destinoReporte").change(function (e) {
        e.preventDefault();
        let formData = new FormData()
        formData.set("id_terminalOrigen", terminal_origenReporte.value)
        formData.set("id_terminalDestino", terminal_destinoReporte.value)
        fetch(instance._URL_ + "encomienda/get_programacionReporte", {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    programacion_destinoReporte.innerHTML = ""
                    Object.values(data.message).forEach((e, i, array) => {
                        programacion_destinoReporte.insertAdjacentHTML('beforeend', `
                            <option value="${e.id_programacion}">${e.fecha_salida.split("-").reverse().join("-")} ${e.hora_salida} - ${e.placa} - ${e.tp_servicio_pasaje}</option>
                        `)
                    })
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message))
    });

    $(".filtro_reporte").change(function (e) {
        e.preventDefault();
        div_imprimir.classList.add("d-none")
    });

    btn_generarReporte.addEventListener("click", (event) => {
        div_imprimir.innerHTML = ""
        let id_value
        switch (tp_reporte.value) {
            case 'general':
                if (instance.Validate.validateData([terminal_origenReporte.value])) {
                    if (!instance.Validate.validateData([fecha_inicio.value, fecha_fin.value])) {
                        return instance.Toast.operacion_erronea('Rellene correctamente los campos')
                    }
                    div_imprimir.classList.remove("d-none")
                    id_value = `${terminal_origenReporte.value}/${fecha_inicio.value.split("-").reverse().join("_")}/${fecha_fin.value.split("-").reverse().join("_")}`
                }
                break;
            case 'origen_destino':
                if (instance.Validate.validateData([programacion_destinoReporte.value])) {
                    div_imprimir.classList.remove("d-none")
                    id_value = programacion_destinoReporte.value
                }
            case 'vehiculo':
                if (instance.Validate.validateData([programacion_destinoReporte.value])) {
                    div_imprimir.classList.remove("d-none")
                    id_value = programacion_destinoReporte.value
                }
                break;
            case 'cliente':
                if (instance.Validate.validateData([terminal_origenReporte.value, cliente_reporte.value])) {
                    div_imprimir.classList.remove("d-none")
                    id_value = `${terminal_origenReporte.value}/${cliente_reporte.value}`
                }
                break;

            default:
                break;
        }
        div_imprimir.insertAdjacentHTML('beforeend', `
            <a href = "${instance.CONSTS.URL.REPORTES_ENCOMIENDA[tp_reporte.value.toUpperCase()] + id_value}" class="d-flex flex-column justify-content-center align-items-center text-decoration-none py-4 mx-2 wow pulse" data-wow-iteration="infinite" data-wow-duration="500ms" target="_blank">
                <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
            </a>
        `)
    })

    $("#tp_reporte").change(function (e) {
        terminal_destinoReporte.innerHTML = ""
        programacion_destinoReporte.innerHTML = ""

        terminal_origenReporte.closest("div").classList.add("d-none")
        terminal_destinoReporte.closest("div").classList.add("d-none")
        programacion_destinoReporte.closest("div").classList.add("d-none")
        cliente_reporte.closest("div").classList.add("d-none")
        fecha_inicio.parentElement.classList.add("d-none")
        fecha_fin.parentElement.classList.add("d-none")
        switch (tp_reporte.value) {
            case 'general':
                terminal_origenReporte.closest("div").classList.remove("d-none")
                fecha_inicio.parentElement.classList.remove("d-none")
                fecha_fin.parentElement.classList.remove("d-none")
                break;
            case 'origen_destino':
                terminal_origenReporte.closest("div").classList.remove("d-none")
                terminal_destinoReporte.closest("div").classList.remove("d-none")
                programacion_destinoReporte.closest("div").classList.remove("d-none")
                break;
            case 'vehiculo':
                terminal_origenReporte.closest("div").classList.remove("d-none")
                programacion_destinoReporte.closest("div").classList.remove("d-none")
                break;
            case 'cliente':
                terminal_origenReporte.closest("div").classList.remove("d-none")
                cliente_reporte.closest("div").classList.remove("d-none")
                break;
            default:
                break;
        }
    })
})