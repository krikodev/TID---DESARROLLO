import * as instance from "../../../public/js/instance.js"


document.addEventListener("DOMContentLoaded", function (event) {

    // Variables Form
    let form = document.querySelector("#form_conductor")
    let btn_liqui = document.querySelector("#btn_liquidacion")

    let add_personal = document.querySelector("#add_personal_p")
    let table_personal = document.querySelector("#table_personal_p")
    let personal = document.querySelector("#personal_p")
    let cond_c = document.querySelector("#conductor_p")

    let button_save = document.querySelector("#button_save_c")
    let button_cancel = document.querySelector("#button_cancel_c")
    let button_loadSave = document.querySelector("#button_loadSave_c")

    //Control de tipo de boton 
    let manifi = document.querySelector("#manifi")
    let ruta_manifiesto = "";

    instance.select.createSelect("#conductor_p", "#div_parentConductorP")
    instance.select.createSelect("#personal_p", "#div_parentPersonalP")
    instance.select.createSelect("#vehiculo", "#div_parentVehiculo")

    //Control sobre los selects
    $("#conductor_p, #personal_p").on('change', function () {
        actualizar_selects();
    });

    function actualizar_selects() {
        const conductor = $("#conductor_p").val();
        const personal = $("#personal_p").val();

        // Mostrar todas las opciones
        $("#conductor_p option, #personal_p option").each(function () {
            $(this).prop('disabled', false).show();
        });

        // Ocultar las opciones seleccionadas en el otro select
        if (conductor) {
            $("#personal_p option[value='" + conductor + "']").prop('disabled', true).hide();
        }

        if (personal) {
            $("#conductor_p option[value='" + personal + "']").prop('disabled', true).hide();
        }

        // Refrescar Select2 para reflejar los cambios sin recargar
        $('#conductor_p').trigger('change.select2');
        $('#personal_p').trigger('change.select2');
    }

    // Inicializar el comportamiento en caso de que ya haya selecciones
    actualizar_selects();


    // Reset formulario al Cerrar modal
    $("#modal_conductor").on("hidden.bs.modal", (e) => {
        $("#modal_conductor").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        $("#conductor_p").val("").trigger("change")

        $('#table_personal_p tbody tr').remove()
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("conductor_p"),
            formData.get("id_program")
        ]

        // personal
        let id_personal_selected = document.querySelectorAll(".id_personal_selected")
        let personal = []
        Object.values(id_personal_selected).map((e, i, array) => {
            return personal.push([e.value])
        })

        if (instance.Validate.validateData(data)) {
            formData.append('conductor', cond_c.value);
            formData.set("personal", JSON.stringify(personal))
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + "programacion/actualizar_program", {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message)
                    ruta_manifiesto = instance._URL_ + instance.CONSTS.URL.MANIFIESTO.PASAJE + data.id_programacion
                    if (manifi.value != 1) {
                    } else {
                        Swal.fire({
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            html: `
                                <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
                                   <div class="radio-input-wrapper">
                                   <label class="label">
                                   <input value="1" name="seleccion" id="seleccion" class="radio-input" type="radio">
                                   <div class="radio-design"></div>
                                   <div class="label-text">Incluir Notas</div>
                                   </label>
                                   <label class="label">
                                   <input value="2" name="seleccion" id="seleccion" class="radio-input" type="radio">
                                   <div class="radio-design"></div>
                                   <div class="label-text">Solo Notas</div>
                                   </label>
                                   </div>
                                </div>
                                `,
                            confirmButtonText: 'Continuar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let label = document.querySelector('input[name="seleccion"]:checked')
                                let condicion = label ? label.value : 0;
                                $.ajax({
                                    url: ruta_manifiesto,
                                    success: function (response) {
                                        if (response == 1) {
                                            let formData = new FormData()
                                            formData.set("id_programacion", id_progra)
                                            formData.set("condicion", 0)
                                            fetch(instance._URL_ + "programacion/get_personalProgramacion", {
                                                method: 'POST',
                                                body: formData
                                            }).then(response => {
                                                if (!response.ok) throw new Error(response.status())
                                                return response.json()
                                            }).then(data => {
                                                try {
                                                    if (data.success) {
                                                        set_personal(data.message)
                                                    }
                                                } catch {
                                                    instance.Toast.operacion_erronea('Ha ocurrido un error al cargar los datos del personal.')
                                                }
                                            }).catch(error => instance.Toast.operacion_erronea(error.message))
                                            $('#modal_conductor').modal('show');
                                        } else {
                                            $.ajax({
                                                url: ruta_manifiesto,
                                                method: 'POST',
                                                data: {
                                                    condicion: condicion
                                                },
                                                xhrFields: {
                                                    responseType: 'blob' // Asegura que la respuesta se maneje como un Blob
                                                },
                                                success: function (response) {
                                                    var blob = new Blob([response], { type: 'application/pdf' });
                                                    var url = URL.createObjectURL(blob);

                                                    var iframe = document.createElement('iframe');
                                                    iframe.style.display = 'none'; // Oculta el iframe
                                                    iframe.src = url;

                                                    document.body.appendChild(iframe);
                                                    iframe.onload = function () {
                                                        iframe.contentWindow.print();
                                                    };
                                                },
                                                error: function (xhr, status, error) {
                                                    console.error("Error al recuperar el PDF:", error);
                                                }
                                            });
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                    }
                                });

                            }
                        })
                    }
                    $('#modal_conductor').modal('toggle')
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea('Rellene correctamente los campos')
        }
    })

    function cargarManifiesto() {
        window.open(ruta_manifiesto, '_blank');
    }

    // personal
    add_personal.addEventListener("click", (e) => {
        const selectedValue = personal.value;
        const selectedText = personal.options[personal.selectedIndex].text;

        // Función para verificar si el texto contiene "PILOTO" o "COPILOTO"
        function esPilotoCopiloto(texto) {
            return texto.includes("CONDUCTOR") || texto.includes("COPILOTO");
        }

        // Verificar si se está intentando agregar un "piloto" o "copiloto"
        if (esPilotoCopiloto(selectedText)) {
            // Verificar si ya existe un "piloto" o "copiloto" en la tabla
            const existePilotoCopiloto = Array.from(document.querySelectorAll(".personal_selected"))
                .some(element => esPilotoCopiloto(element.innerText));

            // Mostrar mensaje de error si ya hay un "piloto" o "copiloto" registrado
            if (existePilotoCopiloto) {
                instance.Toast.operacion_erronea("Ya hay un piloto o copiloto registrado");
                return; // Salir de la función sin agregar el registro
            }
        }

        // Verificar si el personal ya está agregado
        const personalYaAgregado = Array.from(document.querySelectorAll(".personal_selected"))
            .some(element => element.innerText === selectedText);

        if (selectedValue.length === 0 || personalYaAgregado) {
            instance.Toast.operacion_erronea("El personal ya se encuentra agregado");
        } else {
            let tbody = table_personal.getElementsByTagName("tbody")[0];
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <input type="hidden" class="id_personal_selected" value="${selectedValue}">
                    <td><label class="personal_selected mb-0 mt-2">${selectedText}</label></td>
                    <td><button type='button' class='btn btnDeleteRegis button_deleteItem' style="font-size:10px !important">Eliminar</button></td>
                </tr>
            `);
            $("#personal_p").val("").trigger("change");
        }
    });

    $('#table_personal_p tbody').on('click', '.button_deleteItem', function (e) {
        document.querySelector("#table_personal_p tbody").removeChild(this.closest("tr"))
    });

})