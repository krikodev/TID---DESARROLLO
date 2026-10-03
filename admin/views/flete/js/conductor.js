import * as instance from "../../../public/js/instance.js";

document.addEventListener("DOMContentLoaded", function (event) {
  // =========================================================
  // VARIABLES FORMULARIO
  // =========================================================

  let form = document.querySelector("#form_conductor_c");
  let id_conductor = document.querySelector("#id_conductor_c");

  let tp_docu = document.querySelector("#tp_docu_c");
  let num_docu = document.querySelector("#num_docu_c");

  let button_search = document.querySelector("#button_search_c");
  let button_loadSearch = document.querySelector("#button_loadSearch_c");

  let nombres = document.querySelector("#nombres_c");
  let apellidos = document.querySelector("#apellidos_c");

  let fecha_nacimiento = document.querySelector("#fecha_nacimiento_c");
  let estado_civil = document.querySelector("#estado_civil_c");
  let genero = document.querySelector("#genero_c");

  let ubigeo = document.querySelector("#ubigeo_c");
  let direccion = document.querySelector("#direccion_c");
  let celular = document.querySelector("#celular_c");
  let email = document.querySelector("#email_c");

  let estado_sunat = document.querySelector("#estado_sunat_c");
  let condicion_sunat = document.querySelector("#condicion_sunat_c");

  let terminal = document.querySelector("#terminal_c");

  let tp_usuario = document.querySelector("#tp_usuario_c");
  let estado = document.querySelector("#estado_c");

  let licencia = document.querySelector("#licencia_c");
  let ctg_licencia = document.querySelector("#ctg_licencia_c");

  let button_save = document.querySelector("#button_save_c");
  let button_cancel = document.querySelector("#button_cancel_c");
  let button_loadSave = document.querySelector("#button_loadSave_c");

  // =========================================================
  // SELECT2
  // =========================================================

  instance.select.createSelect("#ubigeo_c", "#div_parentUbigeo_c");

  instance.select.createSelect("#terminal_c", "#div_parentTerminal_c");

  // =========================================================
  // VALIDACIONES
  // =========================================================

  instance.Validate.allowInputNum(["#num_docu_c", "#celular_c"]);

  instance.Validate.allowInputStringSpace(["#nombres_c", "#apellidos_c"]);

  // =========================================================
  // RESET AL CERRAR MODAL
  // =========================================================

  $("#modal_conductor").on("hidden.bs.modal", (e) => {
    form.reset();

    form.classList.remove("was-validated");

    id_conductor.value = "";

    // Reset Select2
    $("#ubigeo_c").val("").trigger("change");

    $("#terminal_c").val("").trigger("change");

    // Restaurar nombres
    nombres.closest("div").querySelector("label").innerHTML =
      `Nombres<span class="requiredField">*</span>`;

    // Mostrar apellidos
    apellidos.parentElement.classList.remove("d-none");
    apellidos.setAttribute("readonly", true);

    // Ocultar datos SUNAT
    estado_sunat.parentElement.classList.add("d-none");
    condicion_sunat.parentElement.classList.add("d-none");

    // Restaurar botón búsqueda
    button_search.classList.remove("d-none");
    button_loadSearch.classList.add("d-none");

    button_search.disabled = false;
  });

  // =========================================================
  // ENVÍO DEL FORMULARIO
  // =========================================================

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    let formData = new FormData(form);

    // =====================================================
    // DATOS PARA VALIDACIÓN
    // =====================================================

    let data = [
      formData.get("tp_docu_c"),
      formData.get("num_docu_c"),
      formData.get("nombres_c"),
      formData.get("ubigeo_c"),
      formData.get("direccion_c"),
      formData.get("celular_c"),
      formData.get("email_c"),
      formData.get("terminal_c"),
      formData.get("estado_c"),
      formData.get("licencia_c"),
      formData.get("ctg_licencia_c"),
      formData.get("tp_usuario_c"),
    ];

    // Apellidos solamente para DNI
    if (tp_docu.value == 1) {
      data.push(formData.get("apellidos_c"));
    }

    // =====================================================
    // VALIDAR
    // =====================================================

    if (!instance.Validate.validateData(data)) {
      Swal.fire({
        icon: "error",
        title: "Operación erronea",
        text: "Rellene correctamente los campos",
      });

      return;
    }

    // =====================================================
    // FORM DATA PARA BACKEND
    //
    // En pantalla:
    // nombre_c
    //
    // Backend:
    // nombre
    // =====================================================

    const formDataBackend = new FormData();

    for (const [key, value] of formData.entries()) {
      const newKey = key.endsWith("_c") ? key.slice(0, -2) : key;

      formDataBackend.append(newKey, value);
    }

    // =====================================================
    // AJAX
    // =====================================================

    $.ajax({
      type: "post",

      url: instance._URL_ + "conductor/crud_register",

      contentType: false,

      cache: false,

      processData: false,

      data: formDataBackend,

      // =================================================
      // BEFORE SEND
      // =================================================

      beforeSend: () => {
        button_save.classList.add("d-none");

        button_cancel.classList.add("d-none");

        button_loadSave.classList.remove("d-none");
      },

      // =================================================
      // SUCCESS
      // =================================================

      success: (response) => {
        try {
          let reply = JSON.parse(response);

          if (reply.success) {
            Swal.fire({
              icon: "success",
              title: "Operación exitosa",
              text: reply.message,
            });

            // =================================================
            // AGREGAR CONDUCTOR AL SELECT2
            // =================================================

            if (reply.conductor) {
              const conductor = reply.conductor;

              const idConductor = conductor.id_usuario;

              const textoConductor =
                `${conductor.nombres ?? ""} ${conductor.apellidos ?? ""} - ${conductor.num_docu ?? ""}`
                  .replace(/\s+/g, " ")
                  .trim();

              // =====================================================
              // SELECT CONDUCTOR PROPIO
              // =====================================================

              const $conductor = $("#conductor");

              // Evitar duplicados
              $conductor.find(`option[value="${idConductor}"]`).remove();

              // Agregar y seleccionar
              $conductor.append(
                new Option(textoConductor, idConductor, true, true),
              );

              $conductor.val(String(idConductor)).trigger("change");

              // =====================================================
              // SELECT CONDUCTOR TERCERIZADO
              // =====================================================

              const $conductorTercerizado = $("#conductor_tercerizado");

              // Evitar duplicados
              $conductorTercerizado
                .find(`option[value="${idConductor}"]`)
                .remove();

              // Agregar y seleccionar
              $conductorTercerizado.append(
                new Option(textoConductor, idConductor, true, true),
              );

              $conductorTercerizado.val(String(idConductor)).trigger("change");
            }

            // Cerrar modal
            $("#modal_conductor").modal("hide");
          } else {
            Swal.fire({
              icon: "error",
              title: "Operación erronea",
              text: reply.message,
            });
          }
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Operación erronea",
            text: "Ha ocurrido un error, intentalo más tarde.",
          });
        }
      },

      // =================================================
      // COMPLETE
      // =================================================

      complete: () => {
        button_save.classList.remove("d-none");

        button_cancel.classList.remove("d-none");

        button_loadSave.classList.add("d-none");
      },
    });
  });

  // =========================================================
  // CONSULTA RENIEC / SUNAT
  // =========================================================

  button_search.addEventListener("click", (e) => {
    let reply_val = instance.Validate.validateData([num_docu.value]);

    if (
      reply_val &&
      (num_docu.value.length == 8 || num_docu.value.length == 11)
    ) {
      let ruta_query = tp_docu.value == 1 ? "reniec" : "sunat";

      $.ajax({
        type: "post",

        url: instance._URL_ + "api/" + ruta_query,

        data: {
          docu: num_docu.value,
        },

        beforeSend: () => {
          button_search.classList.add("d-none");

          button_loadSearch.classList.remove("d-none");
        },

        success: (response) => {
          try {
            let data = JSON.parse(response);

            if (data.success) {
              // =============================================
              // RUC
              // =============================================

              if (tp_docu.value == 6) {
                nombres.value = data.data.nombre_o_razon_social;

                estado_sunat.value = data.data.estado;

                condicion_sunat.value = data.data.condicion;
              }

              // =============================================
              // DNI
              // =============================================
              else {
                nombres.value = data.data.nombres;

                apellidos.value = `${data.data.apellido_paterno} ${data.data.apellido_materno}`;
              }

              // =============================================
              // UBIGEO
              // =============================================

              $("#ubigeo_c").val(data.data.ubigeo_sunat).trigger("change");
            } else {
              Swal.fire({
                icon: "error",
                title: "Operación erronea",
                text: "Rellene el campo correctamente",
              });
            }
          } catch {
            Swal.fire({
              icon: "error",
              title: "Operación erronea",
              text: "Ha ocurrido un error, intentalo más tarde.",
            });
          }
        },

        complete: () => {
          button_search.classList.remove("d-none");

          button_loadSearch.classList.add("d-none");
        },
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Operación erronea",
        text: "Rellene el campo correctamente",
      });
    }
  });

  // =========================================================
  // CAMBIO TIPO DOCUMENTO
  // =========================================================

  tp_docu.addEventListener("change", (e) => {
    num_docu.value = "";

    nombres.value = "";

    apellidos.value = "";

    estado_sunat.value = "";

    condicion_sunat.value = "";

    $("#ubigeo_c").val("").trigger("change");

    direccion.value = "S/D";

    // =====================================================
    // RUC
    // =====================================================

    if (tp_docu.value == 6) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Razon social<span class="requiredField">*</span>`;

      nombres.setAttribute("readonly", true);

      apellidos.parentElement.classList.add("d-none");

      apellidos.removeAttribute("readonly");

      num_docu.setAttribute("minlength", 11);

      num_docu.setAttribute("maxlength", 11);

      estado_sunat.parentElement.classList.remove("d-none");

      condicion_sunat.parentElement.classList.remove("d-none");

      button_search.disabled = false;
    }

    // =====================================================
    // CARNET EXTRANJERIA
    // =====================================================
    else if (tp_docu.value == 4) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.removeAttribute("readonly");

      apellidos.parentElement.classList.remove("d-none");

      apellidos.removeAttribute("readonly");

      num_docu.setAttribute("minlength", 12);

      num_docu.setAttribute("maxlength", 12);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = true;
    }

    // =====================================================
    // PASAPORTE
    // =====================================================
    else if (tp_docu.value == 7) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.removeAttribute("readonly");

      apellidos.parentElement.classList.remove("d-none");

      apellidos.removeAttribute("readonly");

      num_docu.setAttribute("minlength", 12);

      num_docu.setAttribute("maxlength", 12);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = true;
    }

    // =====================================================
    // OTROS DOCUMENTOS
    // =====================================================
    else if (tp_docu.value == 0) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.removeAttribute("readonly");

      apellidos.parentElement.classList.remove("d-none");

      apellidos.removeAttribute("readonly");

      num_docu.setAttribute("minlength", 12);

      num_docu.setAttribute("maxlength", 12);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = true;
    }

    // =====================================================
    // DNI
    // =====================================================
    else {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.setAttribute("readonly", true);

      apellidos.parentElement.classList.remove("d-none");

      apellidos.setAttribute("readonly", true);

      num_docu.setAttribute("minlength", 8);

      num_docu.setAttribute("maxlength", 8);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = false;
    }
  });
});
