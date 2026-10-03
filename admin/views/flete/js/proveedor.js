import * as instance from "../../../public/js/instance.js";

document.addEventListener("DOMContentLoaded", function () {
  // =========================================================
  // VARIABLES
  // =========================================================

  const form = document.querySelector("#form_proveedor_p");

  const id_proveedor = document.querySelector("#id_proveedor_p");

  const tp_docu = document.querySelector("#tp_docu_p");
  const num_docu = document.querySelector("#num_docu_p");

  const nombres = document.querySelector("#nombres_p");
  const apellidos = document.querySelector("#apellidos_p");

  const direccion = document.querySelector("#direccion_p");
  const celular = document.querySelector("#celular_p");
  const email = document.querySelector("#email_p");

  const estado_sunat = document.querySelector("#estado_sunat_p");
  const condicion_sunat = document.querySelector("#condicion_sunat_p");

  const estado = document.querySelector("#estado_p");

  const button_search = document.querySelector("#button_search_p");
  const button_loadSearch = document.querySelector("#button_loadSearch_p");

  const button_save = document.querySelector("#button_save_p");
  const button_cancel = document.querySelector("#button_cancel_p");
  const button_loadSave = document.querySelector("#button_loadSave_p");

  // =========================================================
  // SELECT2
  // =========================================================

  instance.select.createSelect("#ubigeo_p", "#div_parentUbigeo_p");

  instance.select.createSelect("#terminal_p", "#div_parentTerminal_p");

  // =========================================================
  // VALIDACIONES
  // =========================================================

  instance.Validate.allowInputNum(["#num_docu_p", "#celular_p"]);

  instance.Validate.allowInputStringSpace(["#nombres_p", "#apellidos_p"]);

  // =========================================================
  // RESET MODAL
  // =========================================================

  $("#modal_proveedor").on("hidden.bs.modal", function () {
    form.reset();

    form.classList.remove("was-validated");

    id_proveedor.value = "";

    $("#ubigeo_p").val("").trigger("change");
    $("#terminal_p").val("").trigger("change");

    nombres.closest("div").querySelector("label").innerHTML =
      `Nombres<span class="requiredField">*</span>`;

    nombres.setAttribute("readonly", true);

    apellidos.parentElement.classList.remove("d-none");
    apellidos.setAttribute("readonly", true);
    apellidos.setAttribute("required", true);

    estado_sunat.parentElement.classList.add("d-none");
    condicion_sunat.parentElement.classList.add("d-none");

    num_docu.setAttribute("minlength", 8);
    num_docu.setAttribute("maxlength", 8);

    button_search.disabled = false;
  });

  // =========================================================
  // GUARDAR PROVEEDOR
  // =========================================================

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    // FormData original con _p
    const formData = new FormData(form);

    // =====================================================
    // CONVERTIR NOMBRES PARA EL BACKEND
    // tp_docu_p -> tp_docu
    // num_docu_p -> num_docu
    // nombres_p  -> nombres
    // etc.
    // =====================================================

    const formDataBackend = new FormData();

    for (const [key, value] of formData.entries()) {
      const newKey = key.endsWith("_p") ? key.slice(0, -2) : key;

      formDataBackend.append(newKey, value);
    }

    // =====================================================
    // VALIDAR
    // =====================================================

    const data = [
      formDataBackend.get("tp_docu"),
      formDataBackend.get("num_docu"),
      formDataBackend.get("nombres"),
      formDataBackend.get("ubigeo"),
      formDataBackend.get("direccion"),
      formDataBackend.get("celular"),
      formDataBackend.get("email"),
      formDataBackend.get("terminal"),
      formDataBackend.get("estado"),
    ];

    if (tp_docu.value == 1) {
      data.push(formDataBackend.get("apellidos"));
    }

    if (!instance.Validate.validateData(data)) {
      Swal.fire({
        icon: "error",
        title: "Operación errónea",
        text: "Rellene correctamente los campos",
      });

      return;
    }

    // =====================================================
    // AJAX
    // =====================================================

    $.ajax({
      type: "post",

      url: instance._URL_ + "proveedor/crud_register",

      contentType: false,
      cache: false,
      processData: false,

      data: formDataBackend,

      beforeSend: () => {
        button_save.classList.add("d-none");
        button_cancel.classList.add("d-none");
        button_loadSave.classList.remove("d-none");
      },

      success: (response) => {
        try {
          const reply = JSON.parse(response);

          if (reply.success) {
            Swal.fire({
              icon: "success",
              title: "Operación exitosa",
              text: reply.message,
            });

            // =============================================
            // AGREGAR PROVEEDOR AL SELECT DEL FLETE
            // =============================================

            if (reply.proveedor) {
              const proveedor = reply.proveedor;

              const idProveedor = proveedor.id_usuario;

              let textoProveedor = "";

              // Empresa / RUC
              if (proveedor.num_docu?.length === 11) {
                textoProveedor = `${proveedor.nombres} - ${proveedor.num_docu}`;
              }

              // Persona
              else {
                textoProveedor =
                  `${proveedor.nombres ?? ""} ` +
                  `${proveedor.apellidos ?? ""} - ` +
                  `${proveedor.num_docu ?? ""}`;
              }

              const $selectProveedor = $("#proveedor");

              // Evitar duplicados
              $selectProveedor.find(`option[value="${idProveedor}"]`).remove();

              // Agregar nuevo proveedor
              $selectProveedor.append(
                new Option(textoProveedor.trim(), idProveedor, true, true),
              );

              // Seleccionarlo
              $selectProveedor.val(String(idProveedor)).trigger("change");
            }

            // =============================================
            // CERRAR MODAL
            // =============================================

            $("#modal_proveedor").modal("hide");
          } else {
            Swal.fire({
              icon: "error",
              title: "Operación errónea",
              text: reply.message,
            });
          }
        } catch (error) {
          console.error("Error proveedor:", error, response);

          Swal.fire({
            icon: "error",
            title: "Operación errónea",
            text: "Ha ocurrido un error, inténtalo más tarde.",
          });
        }
      },

      complete: () => {
        button_save.classList.remove("d-none");
        button_cancel.classList.remove("d-none");
        button_loadSave.classList.add("d-none");
      },
    });
  });

  // =========================================================
  // BUSCAR RENIEC / SUNAT
  // =========================================================

  button_search.addEventListener("click", function () {
    const valido = instance.Validate.validateData([num_docu.value]);

    if (
      !valido ||
      (num_docu.value.length != 8 && num_docu.value.length != 11)
    ) {
      Swal.fire({
        icon: "error",
        title: "Operación errónea",
        text: "Rellene el documento correctamente",
      });

      return;
    }

    const ruta_query = tp_docu.value == 1 ? "reniec" : "sunat";

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
          const data = JSON.parse(response);

          if (data.success) {
            // =============================================
            // RUC
            // =============================================

            if (tp_docu.value == 6) {
              nombres.value = data.data.nombre_o_razon_social;

              apellidos.value = "";

              estado_sunat.value = data.data.estado ?? "";

              condicion_sunat.value = data.data.condicion ?? "";
            }

            // =============================================
            // DNI
            // =============================================
            else {
              nombres.value = data.data.nombres;

              apellidos.value =
                `${data.data.apellido_paterno ?? ""} ` +
                `${data.data.apellido_materno ?? ""}`;
            }

            $("#ubigeo_p").val(data.data.ubigeo_sunat).trigger("change");
          } else {
            Swal.fire({
              icon: "error",
              title: "Operación errónea",
              text: "No se encontraron datos",
            });
          }
        } catch (error) {
          console.error("Error consulta documento:", error);
        }
      },

      complete: () => {
        button_search.classList.remove("d-none");
        button_loadSearch.classList.add("d-none");
      },
    });
  });

  // =========================================================
  // CAMBIO TIPO DOCUMENTO
  // =========================================================

  tp_docu.addEventListener("change", function () {
    num_docu.value = "";

    nombres.value = "";
    apellidos.value = "";

    estado_sunat.value = "";
    condicion_sunat.value = "";

    $("#ubigeo_p").val("").trigger("change");

    direccion.value = "S/D";

    // =====================================================
    // RUC
    // =====================================================

    if (tp_docu.value == 6) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Razón social<span class="requiredField">*</span>`;

      nombres.setAttribute("readonly", true);

      apellidos.parentElement.classList.add("d-none");

      apellidos.removeAttribute("required");

      num_docu.setAttribute("minlength", 11);

      num_docu.setAttribute("maxlength", 11);

      estado_sunat.parentElement.classList.remove("d-none");

      condicion_sunat.parentElement.classList.remove("d-none");

      button_search.disabled = false;
    }

    // =====================================================
    // DNI
    // =====================================================
    else if (tp_docu.value == 1) {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.setAttribute("readonly", true);

      apellidos.parentElement.classList.remove("d-none");

      apellidos.setAttribute("required", true);

      apellidos.setAttribute("readonly", true);

      num_docu.setAttribute("minlength", 8);

      num_docu.setAttribute("maxlength", 8);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = false;
    }

    // =====================================================
    // OTROS
    // =====================================================
    else {
      nombres.closest("div").querySelector("label").innerHTML =
        `Nombres<span class="requiredField">*</span>`;

      nombres.removeAttribute("readonly");

      apellidos.parentElement.classList.remove("d-none");

      apellidos.setAttribute("required", true);

      apellidos.removeAttribute("readonly");

      num_docu.setAttribute("minlength", 1);

      num_docu.setAttribute("maxlength", 12);

      estado_sunat.parentElement.classList.add("d-none");

      condicion_sunat.parentElement.classList.add("d-none");

      button_search.disabled = true;
    }
  });
});
