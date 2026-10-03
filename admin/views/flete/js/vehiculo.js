import * as instance from "../../../public/js/instance.js";

document.addEventListener("DOMContentLoaded", function (event) {
  // Variables Form
  let form = document.querySelector("#form_vehiculo");
  let id_vehiculo = document.querySelector("#id_vehiculo");
  let show_tarjeta_propiedad = document.querySelector(
    "#show_tarjeta_propiedad",
  );
  let link_tarjeta_propiedad = document.querySelector(
    "#link_tarjeta_propiedad",
  );
  let tarjeta_propiedad = document.querySelector("#tarjeta_propiedad");
  let tarjeta_propiedad_before = document.querySelector(
    "#tarjeta_propiedad_before",
  );
  let num_piso = document.querySelector("#num_piso");
  let button_save = document.querySelector("#button_save_v");
  let button_cancel = document.querySelector("#button_cancel_v");
  let button_loadSave = document.querySelector("#button_loadSave_v");

  instance.Validate.allowInputNum(["#num_piso", "#num_asiento"]);
  instance.Upload.uploadFile(tarjeta_propiedad, show_tarjeta_propiedad, "pdf");

  instance.Validate.allowInputNum(["#asiento_config"]);

  // Reset formulario al Cerrar modal
  $("#modal_vehiculo").on("hidden.bs.modal", (e) => {
    $("#modal_vehiculo").find("form_vehiculo").trigger("reset");
    //Eliminando el was-validated
    let forms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.classList.remove("was-validated");
    });
    id_vehiculo.value = "";
    show_tarjeta_propiedad.style.backgroundImage = `url("./public/image/upload/notupload_filepdf.png")`;
    link_tarjeta_propiedad.classList.add("d-none");
    link_tarjeta_propiedad.href = "";
  });

  // Envio de regitro
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(form);
    let data = [
      formData.get("descripcion"),
      formData.get("placa"),
      formData.get("soat"),
      formData.get("num_piso"),
      formData.get("num_asiento"),
      formData.get("estado"),
    ];
    if (num_piso.value <= 0 || num_piso.value > 2) {
      instance.Toast.operacion_erronea("El piso máximo es de 2.");
    } else {
      if (instance.Validate.validateData(data)) {
        button_save.classList.add("d-none");
        button_cancel.classList.add("d-none");
        button_loadSave.classList.remove("d-none");
        fetch(instance._URL_ + "vehiculo/crud_register", {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (!response.ok) throw new Error(response.status);
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              instance.Toast.operacion_exitosa(data.message);

              const vehiculo = data.vehiculo;

              const idVehiculo = vehiculo.id_vehiculo;

              const textoVehiculo = `${vehiculo.descripcion ?? ""} | ${vehiculo.placa ?? ""}`;

              // =====================================================
              // VEHÍCULO PROPIO
              // =====================================================

              const $vehiculo = $("#vehiculo");

              // Evitar duplicados
              $vehiculo.find(`option[value="${idVehiculo}"]`).remove();

              const optionVehiculo = new Option(
                textoVehiculo,
                idVehiculo,
                true,
                true,
              );

              // Mantener información del número de pisos
              $(optionVehiculo).attr("num-piso", vehiculo.num_piso);

              $vehiculo.append(optionVehiculo);

              $vehiculo.val(String(idVehiculo)).trigger("change");

              // =====================================================
              // VEHÍCULO TERCERIZADO
              // =====================================================

              const $vehiculoTercerizado = $("#vehiculo_tercerizado");

              if ($vehiculoTercerizado.length) {
                // Evitar duplicados
                $vehiculoTercerizado
                  .find(`option[value="${idVehiculo}"]`)
                  .remove();

                const optionVehiculoTercerizado = new Option(
                  textoVehiculo,
                  idVehiculo,
                  true,
                  true,
                );

                $(optionVehiculoTercerizado).attr(
                  "num-piso",
                  vehiculo.num_piso,
                );

                $vehiculoTercerizado.append(optionVehiculoTercerizado);

                $vehiculoTercerizado.val(String(idVehiculo)).trigger("change");
              }

              // =====================================================
              // CERRAR MODAL
              // =====================================================

              $("#modal_vehiculo").modal("hide");
            } else {
              instance.Toast.operacion_erronea(data.message);
            }
            button_save.classList.remove("d-none");
            button_cancel.classList.remove("d-none");
            button_loadSave.classList.add("d-none");
          })
          .catch((error) => instance.Toast.operacion_erronea(error.message));
      } else {
        instance.Toast.operacion_erronea("Rellene correctamente los campos");
        button_save.classList.remove("d-none");
        button_cancel.classList.remove("d-none");
        button_loadSave.classList.add("d-none");
      }
    }
  });
});
