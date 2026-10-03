import * as instance from "../../../public/js/instance.js";

document.getElementById("link_valorizacion").classList.add("active");

document.addEventListener("DOMContentLoaded", function (event) {
  // Variable para almacenar los filtros actuales
  let filtrosActuales = {
    fecha_inicio: "",
    fecha_fin: "",
    estado: "",
  };

  // Variable para almacenar la instancia de la tabla
  let table = $("#table_valorizacion").DataTable({
    ajax: {
      url: instance._URL_ + "valorizacion/dataTable",
      method: "POST",
      data: function (d) {
        // Agregar los filtros a la petición AJAX
        d.filtro_fecha_inicio = filtrosActuales.fecha_inicio;
        d.filtro_fecha_fin = filtrosActuales.fecha_fin;
        d.filtro_estado = filtrosActuales.estado;
      },
      beforeSend: function () {
        document.getElementById("loader").classList.remove("d-none");
      },
      complete: function () {
        document.getElementById("loader").classList.add("d-none");
      },
    },
    responsive: true,
    fixedHeader: true,
    dom: "rtip",
    lengthMenu: [
      [20, 25, 50, -1],
      ["20 filas", "25 filas", "50 filas", "Mostrar todo"],
    ],
    processing: true,
    serverSide: true,
    ordering: false,
    columns: [
      {
        data: "fecha",
        render: function (data) {
          if (!data) return "-";

          const [anio, mes, dia] = data.split("-");
          return `${dia}/${mes}/${anio}`;
        },
      },
      {
        data: "correlativo",
        render: function (data, type, row) {
          return row.serie + "-" + String(row.correlativo).padStart(8, "0");
        },
      },
      { data: "cliente" },

      {
        data: "cantidad_fletes",
        className: "text-center",
      },
      {
        data: "estado",
        render: function (data, type, row) {
          const estado =
            instance.CONSTS.COLORES.ESTADO_VALORIZACION[row.estado];
          return `
            <span
                class="badge rounded-pill"
                style="
                    background:${estado.bg};
                    color:${estado.color};
                    border:1px solid ${estado.border};
                    font-weight:600;
                    padding:6px 10px;
                ">
                ${row.estado}
            </span>
            `;
        },
      },
      {
        data: "total",
        className: "text-end",
        render: function (data) {
          return parseFloat(data || 0).toFixed(2);
        },
      },
      {
        data: "comprobante",
        render: (data, type, row) =>
          data
            ? `<a href="${instance._URL_}${instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE}${row.id_comprobante}" target="_blank" class="btn_dropdown">${row.comprobante}</a>`
            : "-",
      },
      {
        data: "observacion",
        render: function (data) {
          return data || "-";
        },
      },
      {
        data: null,
        orderable: false,
        className: "text-center",
        render: function (data, type, row) {
          let btnVer = `
            <a href="${instance._URL_}valorizacion/ver/${row.id}"
                class="dropdown-item btn_dropdown"
                target="_blank">
                <i class="fa-light fa-eye me-2 text-primary"></i>
                Ver valorización
            </a>`;

          let btnEditar = `
            <a class="dropdown-item btn_dropdown btnEditarValorizacion">
                <i class="fa-light fa-pen-to-square me-2 text-warning"></i>
                Editar
            </a>`;

          let btnEliminar = `
            <a class="dropdown-item btn_dropdown btnEliminarValorizacion">
                <i class="fa-light fa-circle-xmark me-2 text-danger"></i>
                Eliminar
            </a>`;

          let btnEmitir = `
<a class="dropdown-item btn_dropdown btnEmitir">
    <i class="fa-light fa-file-invoice me-2 text-success"></i>
    Emitir comprobante
</a>`;

          let btnRevisarComprobante = `
  <a
    class="dropdown-item btn_dropdown btnRevisarComprobante"
    data-id-venta="${row.id_comprobante}">
    
    <i class="fa-light fa-file-magnifying-glass me-2 text-warning"></i>
    
    Revisar comprobante
  </a>
`;

          let btn_comprobante = `<a class="dropdown-item btn_dropdown" href="${instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE + row.id_venta}" target="_blank" ><i class="fa-light text-primary me-2 fa-print"></i> Comprobante</a>`;
          let btn_comprobante_a4 = `<a class="dropdown-item btn_dropdown" href="${instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE_A4 + row.id_venta}" target="_blank" ><i class="fa-light text-primary me-2 fa-print"></i> Comprobante A4</a>`;

          let ticket = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_VALORIZACION.TICKET + row.id}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light text-primary me-2 fa-scroll"></i> Imprimir Ticket</a>`;
          let a4 = `<a href="${instance._URL_ + instance.CONSTS.URL.IMPRESION_VALORIZACION.A4 + row.id}" target="_blank" class="dropdown-item btn_dropdown"><i class="fa-light fa-file-pdf me-2 text-primary"></i> Imprimir A4</a>`;

          let btn_cerrar = `<a class="dropdown-item btn_dropdown btnCerrarValorizacion" data-id="${row.id}"><i class="fa-light fa-circle-check me-2 text-success"></i>Marcar como CERRADA</a>`;

          return `
            <div class="dropdown">
                <a class="dropdown-toggle"
                    href="javascript:void(0)"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </a>

                <ul class="dropdown-menu">
                ${row.estado === "CERRADA" ? btnEmitir : ""}
                ${row.estado === "FACTURADA" && row.id_comprobante ? btnRevisarComprobante : ""}
                 ${row.estado == "BORRADOR" || row.estado == "ABIERTA" ? btn_cerrar : ""}

                    ${row.estado === "BORRADOR" || row.estado === "ABIERTA" ? btnEditar : ""}

                    ${row.estado !== "FACTURADA" ? btnEliminar : ""}
        ${ticket}
        ${a4}
                </ul>
            </div>
        `;
        },
      },
    ],
    language: {
      url: "./public/plugins/datatable/language/es_es.json",
    },
    deferRender: true,
    stateSave: false,
    pageLength: 20,
  });

  window.selectNacionalidad = new TomSelect("#nacionalidad", {
    valueField: "nombre_pais",
    labelField: "nombre_pais",
    searchField: "nombre_pais",
    maxOptions: 5,
    plugins: ["clear_button"],
    preload: "focus",

    shouldLoad: function (query) {
      return query.length >= 2;
    },

    load: function (query, callback) {
      fetch(
        instance._URL_ +
          "valorizacion/buscar_nacionalidad?q=" +
          encodeURIComponent(query),
      )
        .then((res) => res.json())
        .then((json) => callback(json))
        .catch(() => callback());
    },

    onInitialize: function () {
      this.addOption({
        cod_pais: "174",
        nombre_pais: "PERÚ",
      });

      this.setValue("PERÚ");
    },
  });

  // Configurar buscador general
  instance.Datatable.inputSearch(
    table,
    ".inputSearch",
    ".btnSearchTable",
    "manual",
  );

  // Evento para aplicar filtros
  var btnFiltrar = document.getElementById("btn_filtrar");
  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", function () {
      aplicarFiltros();
    });
  }

  // Evento para limpiar filtros
  var btnLimpiar = document.getElementById("btn_limpiar_filtros");
  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltros();
    });
  }

  // Permitir filtrar con Enter en los inputs
  var filtroIds = ["filtro_fecha_inicio", "filtro_fecha_fin", "filtro_estado"];
  for (var i = 0; i < filtroIds.length; i++) {
    var element = document.getElementById(filtroIds[i]);
    if (element) {
      element.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
          aplicarFiltros();
        }
      });
    }
  }

  // Evento para el botón de Excel (estructura básica)
  var btnExportarExcel = document.getElementById("btn_exportar_excel");
  if (btnExportarExcel) {
    btnExportarExcel.addEventListener("click", function () {
      exportarExcel();
    });
  }

  // Función para aplicar filtros
  function aplicarFiltros() {
    // Obtener valores de los filtros
    var fechaInicio = document.getElementById("filtro_fecha_inicio");
    var fechaFin = document.getElementById("filtro_fecha_fin");
    var estado = document.getElementById("filtro_estado");

    filtrosActuales.fecha_inicio = fechaInicio ? fechaInicio.value : "";
    filtrosActuales.fecha_fin = fechaFin ? fechaFin.value : "";
    filtrosActuales.estado = estado ? estado.value : "";

    // Validar fechas si ambas están presentes
    if (filtrosActuales.fecha_inicio && filtrosActuales.fecha_fin) {
      if (filtrosActuales.fecha_inicio > filtrosActuales.fecha_fin) {
        if (instance.Toast && instance.Toast.operacion_informativa) {
          instance.Toast.operacion_informativa(
            "La fecha de inicio no puede ser mayor que la fecha de fin",
          );
        }
        return;
      }
    }

    // Recargar la tabla con los nuevos filtros
    table.ajax.reload();

    if (instance.Toast && instance.Toast.operacion_exitosa) {
      instance.Toast.operacion_exitosa("Filtros aplicados correctamente");
    }
  }

  // Función para limpiar filtros
  function limpiarFiltros() {
    // Restablecer valores vacíos
    var fechaInicio = document.getElementById("filtro_fecha_inicio");
    var fechaFin = document.getElementById("filtro_fecha_fin");
    var estado = document.getElementById("filtro_estado");

    if (fechaInicio) fechaInicio.value = "";
    if (fechaFin) fechaFin.value = "";
    if (estado) estado.value = "";

    // Actualizar filtrosActuales
    filtrosActuales.fecha_inicio = "";
    filtrosActuales.fecha_fin = "";
    filtrosActuales.estado = "";

    // Recargar la tabla
    table.ajax.reload();

    if (instance.Toast && instance.Toast.operacion_exitosa) {
      instance.Toast.operacion_exitosa("Filtros limpiados correctamente");
    }
  }

  $("#table_valorizacion tbody").on(
    "click",
    ".btnRevisarComprobante",
    async function () {
      const idVenta = parseInt($(this).data("id-venta") || 0);

      if (!idVenta) {
        Swal.fire({
          icon: "warning",
          title: "Comprobante no encontrado",
          text: "No se encontró el comprobante asociado a esta valorización.",
        });

        return;
      }

      await cargarVistaPreviaComprobante(idVenta);
    },
  );

  // Función para exportar a Excel (estructura básica)
  function exportarExcel() {
    // Mostrar loader
    var loader = document.getElementById("loader");
    if (loader) {
      loader.classList.remove("d-none");
    }

    // Obtener los valores actuales de los filtros
    var fechaInicio = document.getElementById("filtro_fecha_inicio");
    var fechaFin = document.getElementById("filtro_fecha_fin");
    var estado = document.getElementById("filtro_estado");

    // Crear formulario con los filtros actuales
    var form = document.createElement("form");
    form.method = "POST";
    form.action = instance._URL_ + "guia_transportista/exportar_excel"; // <- Llamada correcta

    // Agregar filtros como campos ocultos
    var campos = [
      {
        name: "filtro_fecha_inicio",
        value: fechaInicio ? fechaInicio.value : "",
      },
      { name: "filtro_fecha_fin", value: fechaFin ? fechaFin.value : "" },
      { name: "filtro_estado", value: estado ? estado.value : "" },
    ];

    for (var i = 0; i < campos.length; i++) {
      var input = document.createElement("input");
      input.type = "hidden";
      input.name = campos[i].name;
      input.value = campos[i].value;
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Ocultar loader después de un tiempo
    setTimeout(function () {
      if (loader) {
        loader.classList.add("d-none");
      }
    }, 2000);
  }

  if (document.querySelector("#tp_comprobante")) {
    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
  }

  var medioPagoElement = $("#medio_pago");
  if (medioPagoElement.length) {
    var medio_pagoSelect = medioPagoElement.selectize({
      create: false,
      maxItems: 1,
      onDropdownOpen: function ($dropdown) {
        $dropdown.addClass("selectize-dropdown--single");
      },
    });
    window.selectizeMedioPago = medio_pagoSelect[0]
      ? medio_pagoSelect[0].selectize
      : null;
  }

  $("#table_valorizacion tbody").on("click", ".btnEmitir", function () {
    const row = table.row($(this).closest("tr")).data();

    $("#id_valorizacion_facturar").val(row.id);
    $("#total_pagar").val(row.total);
    $("#monto_credito").val(row.total);
    $("#id_valorizacion_pagar").val(row.id);

    actualizar_tp_comprobantes(row.id_tp_docu);
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById("modal_facturar"),
    ).show();
  });

  function actualizar_tp_comprobantes(tp_documento) {
    var $select = $("#tp_comprobante");

    if (tp_documento == 6) {
      $select.select2("destroy");
      $select.empty();
      $select.append('<option value="1">FACTURA ELECTRÓNICA</option>');
      $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');
    } else if (tp_documento == 1) {
      $select.select2("destroy");
      $select.empty();
      $select.append('<option value="3">BOLETA DE VENTA ELECTRÓNICO</option>');
    }

    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante");
    $select.trigger("change");
  }

  $("#tp_comprobante").change(function () {
    if (!tp_comprobante) return;

    var formData = new FormData();
    formData.set("tp_comprobante", tp_comprobante.value);

    fetch(instance._URL_ + "encomienda/get_serieForTpComprobante", {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        if (!response.ok) throw new Error(response.status);
        return response.json();
      })
      .then(function (data) {
        if (serie) serie.innerHTML = "";
        if (data && data.success && serie) {
          serie.insertAdjacentHTML(
            "beforeend",
            '<option value="' +
              data.message.id_serie +
              '">' +
              data.message.serie +
              "</option>",
          );
        }
      });
  });

  $("#tp_comprobante").trigger("change");

  $("#forma_pago").on("change", function () {
    var fp = $(this).val();
    if (fp == 2) {
      if (window.selectizeMedioPago) {
        window.selectizeMedioPago.setValue("", true);
        window.selectizeMedioPago.disable();
      }
      if (div_cuota) div_cuota.classList.remove("d-none");
    } else {
      if (window.selectizeMedioPago) {
        window.selectizeMedioPago.enable();
      }
      if (div_cuota) div_cuota.classList.add("d-none");
    }
  });

  $("#tiempo_credito").on("change", function () {
    var tiempo_credito = $(this).val();
    var dias = 0;

    switch (tiempo_credito) {
      case "1":
        dias = 30;
        break;
      case "2":
        dias = 0;
        break;
      case "3":
        dias = 15;
        break;
      case "4":
        dias = 45;
        break;
      case "5":
        dias = 60;
        break;
      default:
        dias = 0;
    }

    var hoy = new Date();
    hoy.setDate(hoy.getDate() + dias);

    var yyyy = hoy.getFullYear();
    var mm = String(hoy.getMonth() + 1).padStart(2, "0");
    var dd = String(hoy.getDate()).padStart(2, "0");

    $("#fecha_credito").val(yyyy + "-" + mm + "-" + dd);
  });

  $(document).ready(function () {
    $("#tiempo_credito").trigger("change");
    $("#forma_pago").trigger("change");
  });

  let button_pagar = document.getElementById("button_pagar");
  let loading = document.getElementById("loader");

  button_pagar.addEventListener("click", async function (event) {
    event.preventDefault();

    if (!form_pago_nota) return;

    const formData = new FormData(form_pago_nota);

    const dataValidacion = [
      formData.get("total_pagar"),
      formData.get("fecha_emision"),
      formData.get("serie"),
      formData.get("caja_chica"),
    ];

    // Si es CONTADO, requiere medio de pago
    if (forma_pago && forma_pago.value == 1) {
      dataValidacion.push(formData.get("medio_pago"));
    }

    if (!instance.Validate.validateData(dataValidacion)) {
      instance.Toast.operacion_erronea("Rellene correctamente los campos");
      return;
    }

    const confirmacion = await Swal.fire({
      title: "¿Emitir comprobante?",
      text: "Se generará el comprobante y podrás revisarlo antes de enviarlo a SUNAT.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, generar",
      cancelButtonText: "Cancelar",
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    try {
      // =====================================================
      // LOADER
      // =====================================================

      if (loading) {
        loading.classList.remove("d-none");
      }

      if (button_pagar) {
        button_pagar.classList.add("d-none");
      }

      if (button_loadPagar) {
        button_loadPagar.classList.remove("d-none");
      }

      // =====================================================
      // CREAR VENTA DESDE VALORIZACIÓN
      // =====================================================

      const response = await fetch(
        instance._URL_ + "valorizacion/pagar_documentos",
        {
          method: "POST",
          body: formData,
        },
      );

      const text = await response.text();

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Respuesta backend:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      if (!response.ok) {
        throw new Error(
          data?.message || `Error del servidor (${response.status})`,
        );
      }

      if (!data.success) {
        throw new Error(data.message || "No se pudo generar el comprobante.");
      }

      // =====================================================
      // OBTENER ID DE LA VENTA GENERADA
      // =====================================================

      const idVenta = parseInt(data.id_venta || data.id_comprobante || 0);

      if (!idVenta) {
        throw new Error(
          "El comprobante fue generado, pero no se obtuvo el ID de la venta.",
        );
      }

      // =====================================================
      // CERRAR MODAL FACTURACIÓN
      // =====================================================

      const modalFacturarElement = document.getElementById("modal_facturar");

      if (modalFacturarElement) {
        const modalFacturar = bootstrap.Modal.getInstance(modalFacturarElement);

        if (modalFacturar) {
          modalFacturar.hide();
        }
      }

      // =====================================================
      // LIMPIAR FORMULARIO
      // =====================================================

      form_pago_nota.reset();

      $("#id_valorizacion_facturar").val("");
      $("#id_valorizacion_pagar").val("");

      // Restauramos los controles dependientes
      $("#forma_pago").trigger("change");
      $("#tiempo_credito").trigger("change");

      // =====================================================
      // RECARGAR TABLA
      // La valorización ahora debe aparecer FACTURADA
      // =====================================================

      if (table) {
        table.ajax.reload(null, false);
      }

      // =====================================================
      // ABRIR VISTA PREVIA DEL COMPROBANTE
      // =====================================================

      await cargarVistaPreviaComprobante(idVenta);
    } catch (error) {
      console.error("Error al generar comprobante:", error);

      Swal.fire({
        icon: "error",
        title: "No se pudo generar el comprobante",
        text: error.message || "Ocurrió un error inesperado.",
      });
    } finally {
      // =====================================================
      // RESTAURAR BOTONES / LOADER
      // =====================================================

      if (loading) {
        loading.classList.add("d-none");
      }

      if (button_pagar) {
        button_pagar.classList.remove("d-none");
      }

      if (button_loadPagar) {
        button_loadPagar.classList.add("d-none");
      }
    }
  });

  $(document).on("click", ".btnCerrarValorizacion", function () {
    const id_valorizacion = $(this).data("id");

    Swal.fire({
      title: "¿Cerrar valorización?",
      text: "La valorización pasará al estado CERRADA y podrá ser pagada con boleta o factura.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, aceptar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (!result.isConfirmed) return;

      document.getElementById("loader").classList.remove("d-none");

      fetch($("#url").val() + "valorizacion/cerrar_valorizacion", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({ id_valorizacion: id_valorizacion }),
      })
        .then((response) => {
          if (!response.ok) {
            // Errores HTTP (404, 500, etc.) antes de intentar leer el JSON
            throw new Error(`Error del servidor (${response.status})`);
          }
          return response.json();
        })
        .then((resp) => {
          if (resp.success) {
            Swal.fire(
              "Listo",
              resp.message || "Valorización aceptada correctamente.",
              "success",
            );
            table.ajax.reload(null, false); // false = mantiene la página actual
          } else {
            Swal.fire(
              "No se pudo aceptar",
              resp.message || "Intenta nuevamente.",
              "error",
            );
          }
        })
        .catch((error) => {
          console.error("Error al aceptar cotización:", error);
          Swal.fire(
            "Error",
            "Ocurrió un error inesperado. Intenta nuevamente.",
            "error",
          );
        })
        .finally(() => {
          document.getElementById("loader").classList.add("d-none");
        });
    });
  });

  $("#table_valorizacion tbody").on(
    "click",
    ".btnEliminarValorizacion",
    function () {
      const data = table.row($(this).closest("tr")).data();

      Swal.fire({
        title: "Necesitamos de tu \nConfirmación",
        html: `<div>
                    <label>Se eliminará del sistema el registro:</label><br>
                    <span class="mt-1 fw-bolder">
                        ${data.serie + "-" + String(data.correlativo).padStart(8, "0")}
                    </span>
                    <p class="fs-6 mt-3 mb-0 text-success">
                        ¿Está Usted de Acuerdo?
                    </p>
                </div>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#429ef5",
        cancelButtonColor: "#ff3b3b",
        cancelButtonText: "No!",
        confirmButtonText: "Si, Adelante!",
      }).then(async (result) => {
        if (!result.isConfirmed) {
          return;
        }

        Swal.fire({
          title: "Eliminando...",
          text: "Por favor, espera un momento.",
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        try {
          const formData = new FormData();
          formData.append("id_valorizacion", data.id);

          const response = await fetch(
            instance._URL_ + "valorizacion/delete_register",
            {
              method: "POST",
              body: formData,
            },
          );

          if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
          }

          const reply = await response.json();

          if (reply && reply.success === true) {
            await Swal.fire({
              icon: "success",
              title: "Operación exitosa",
              text:
                reply.message || "La valorización fue eliminada correctamente.",
              confirmButtonText: "Aceptar",
            });

            instance.Datatable.reloadTable(table);
          } else {
            Swal.fire({
              icon: "error",
              title: "Operación errónea",
              text: reply.message || "No se pudo eliminar la valorización.",
            });
          }
        } catch (error) {
          console.error("Error al eliminar valorización:", error);

          Swal.fire({
            icon: "error",
            title: "Operación errónea",
            text: "Ha ocurrido un error, inténtalo más tarde.",
          });
        }
      });
    },
  );

  $("#table_valorizacion tbody").on(
    "click",
    ".btnEditarValorizacion",
    function () {
      const data = table.row($(this).closest("tr")).data();

      // Abrir modal
      bootstrap.Modal.getOrCreateInstance(modalValorizacionEl).show();

      // Cargar información
      cargarValorizacion(data.id);
    },
  );

  function cargarValorizacion(id_valorizacion) {
    const formData = new FormData();
    formData.append("id_valorizacion", id_valorizacion);

    fetch(instance._URL_ + "valorizacion/get_valorizacion", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((reply) => {
        if (!reply.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: reply.message,
          });
          return;
        }

        // La información viene dentro de message
        const data = reply.message;
        const cab = data.cabecera;
        const detalle = data.detalle ?? [];

        // Cabecera
        $("#mv_id_valorizacion").val(cab.id);
        $("#mv_observacion").val(cab.observacion ?? "");
        $("#mv_serie").val(cab.id_serie);

        $("#cliente_id").val(cab.id_cliente);
        $("#cliente").val(cab.cliente);

        // Mostrar bloque
        $("#mv_bloque_documentos").removeClass("d-none");

        // Limpiar tablas
        table_documentos.clear().draw();
        table_detalle.clear().draw();

        // Documentos/fletes que ya pertenecen a la valorización
        mvDocumentosAgregados = detalle;

        // Renderizar detalle actual
        renderTablaDetalle();

        // Buscar documentos/fletes adicionales disponibles
        cargarDocumentosDisponibles();
      })
      .catch((error) => {
        console.error("Error al cargar valorización:", error);

        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No fue posible obtener la valorización.",
        });
      });
  }

  $("#mv_buscar_documentos").on("click", function (e) {
    e.preventDefault();
    cargarDocumentosDisponibles();
  });

  $("#modal_valorizacion").on("show.bs.modal", (e) => {
    if ($.fn.DataTable.isDataTable("#mv_tabla_disponibles")) {
      table_documentos.columns.adjust().responsive.recalc();
    }
    cargarSeries();
  });

  function cargarDocumentosDisponibles() {
    const id_cliente = document.getElementById("cliente_id").value;

    if (!id_cliente) return;

    let formData = new FormData();
    formData.set("id_cliente", id_cliente);

    fetch(instance._URL_ + "valorizacion/get_fletes_cliente", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        table_documentos.clear();
        if (data.success) {
          const disponibles = data.data.filter(
            (doc) =>
              !mvDocumentosAgregados.some((x) => x.id_flete == doc.id_flete),
          );

          table_documentos.rows.add(disponibles);
        }
        table_documentos.draw();
      });
  }

  // ============================================================
  // 🔑 Interceptor: cualquier `cliente_id.value = X` desde
  // CUALQUIER parte del código (buscarCliente, modal nuevo
  // cliente, etc.) dispara automáticamente un 'change'.
  // Así ya no importa cuántos lugares seteen el id: siempre
  // se habilita serie + documentos de forma consistente.
  // ============================================================
  const clienteIdInput = document.getElementById("cliente_id");
  const nativeDescriptor = Object.getOwnPropertyDescriptor(
    HTMLInputElement.prototype,
    "value",
  );

  Object.defineProperty(clienteIdInput, "value", {
    get() {
      return nativeDescriptor.get.call(this);
    },
    set(val) {
      nativeDescriptor.set.call(this, val);
      this.dispatchEvent(new Event("change"));
    },
  });

  clienteIdInput.addEventListener("change", function () {
    if (this.value) {
      document
        .getElementById("mv_bloque_documentos")
        .classList.remove("d-none");
      cargarDocumentosDisponibles();
    } else {
      // se limpió el cliente (reset del modal, por ejemplo)
      document.getElementById("mv_bloque_documentos").classList.add("d-none");
    }
  });

  // ===== Estado en memoria del modal =====
  let mvDocumentosAgregados = [];

  // ============================================================
  // 🔑 Control de apertura / cierre del modal
  // ============================================================
  const modalValorizacionEl = document.getElementById("modal_valorizacion");

  modalValorizacionEl.addEventListener("show.bs.modal", function () {
    resetModalValorizacion();
  });

  modalValorizacionEl.addEventListener("hidden.bs.modal", function () {
    // limpia todo al cerrar, para que la próxima apertura no arrastre datos viejos
    resetModalValorizacion();
  });

  document
    .getElementById("button_newgister")
    .addEventListener("click", function () {
      new bootstrap.Modal(modalValorizacionEl).show(); // el reset ya lo hace 'show.bs.modal'
    });

  function resetModalValorizacion() {
    document.getElementById("mv_id_valorizacion").value = "";
    document.getElementById("cliente").value = "";
    document.getElementById("cliente_id").value = ""; // dispara change → deshabilita todo correctamente
    document.getElementById("mv_observacion").value = "";
    document.querySelector("#mv_tabla_disponibles tbody").innerHTML = "";
    mvDocumentosAgregados = [];
    renderTablaDetalle();
  }

  function cargarSeries() {
    const select = $("#mv_serie");

    select.empty().append('<option value="">Cargando...</option>');

    const formData = new FormData();
    formData.append("tp_comprobante", 5);

    fetch(instance._URL_ + "valorizacion/get_serieForTpComprobante", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error del servidor (${response.status})`);
        }
        return response.json();
      })
      .then((resp) => {
        select.empty();

        if (resp.success && resp.message.length > 0) {
          resp.message.forEach((serie) => {
            select.append(
              `<option value="${serie.id_serie}">${serie.serie}</option>`,
            );
          });

          // Seleccionar automáticamente la primera serie
          select.prop("selectedIndex", 0);

          // Si tienes algún evento change asociado
          select.trigger("change");
        } else {
          select.append('<option value="">No hay series disponibles</option>');
        }
      })
      .catch((error) => {
        console.error("Error al cargar series:", error);
        select
          .empty()
          .append('<option value="">Error al cargar series</option>');
      });
  }

  document
    .getElementById("mv_agregar_seleccionados")
    .addEventListener("click", function () {
      const seleccionados = table_documentos.rows({ selected: true });

      if (!seleccionados.count()) {
        Swal.fire({
          title: "Información",
          text: "Seleccione al menos un documento",
          icon: "info",
          confirmButtonText: "Sí",
        });
        return;
      }

      seleccionados.data().toArray().forEach(agregarDocumento);

      seleccionados.remove().draw(false);

      renderTablaDetalle();
    });

  function agregarDocumento(doc) {
    if (mvDocumentosAgregados.some((x) => x.id_flete == doc.id_flete)) {
      return;
    }

    mvDocumentosAgregados.push({
      id_flete: doc.id_flete,

      numero_flete: doc.numero_flete || "",

      origen: doc.origen || "",

      destino: doc.destino || "",

      vehiculo: doc.vehiculo || "-",

      id_tp_moneda: Number(doc.id_tp_moneda),

      codigo_moneda: doc.codigo_moneda || "",

      simbolo_moneda: doc.simbolo_moneda || "",

      subtotal: Number(doc.subtotal || 0),

      igv: Number(doc.igv || 0),

      total: Number(doc.total || doc.precio || 0),

      // TEMPORAL mientras terminamos de migrar la tabla
      precio: Number(doc.total || doc.precio || 0),
    });
  }
  const table_detalle = $("#mv_tabla_detalle").DataTable({
    destroy: true,
    responsive: true,
    fixedHeader: true,
    dom: "rtip",
    ordering: false,
    searching: false,
    paging: false,
    info: false,
    language: {
      url: "./public/plugins/datatable/language/es_es.json",
      emptyTable: "Sin documentos agregados",
    },
    columns: [
      {
        data: "origen",
      },
      {
        data: "destino",
      },
      {
        data: "vehiculo",
      },
      {
        data: "precio",
        className: "text-end",
        render: function (data) {
          return "S/ " + Number(data).toFixed(2);
        },
      },
      {
        data: null,
        className: "text-center",
        orderable: false,
        render: function () {
          return `
                    <button class="btn btn-sm btn-danger btn-quitar">
                        <i class="fa-solid fa-trash" style="color: rgb(255, 255, 255);"></i>
                    </button>
                `;
        },
      },
    ],
  });

  function renderTablaDetalle() {
    table_detalle.clear();
    table_detalle.rows.add(mvDocumentosAgregados);
    table_detalle.draw();

    calcularTotales();
  }

  $("#mv_tabla_detalle tbody").on("click", ".btn-quitar", function () {
    const fila = table_detalle.row($(this).closest("tr"));
    const documento = fila.data();

    // Devuelve el documento a la tabla disponible
    table_documentos.row.add(documento).draw(false);

    // Elimina del arreglo
    mvDocumentosAgregados = mvDocumentosAgregados.filter(
      (d) => d.id_flete != documento.id_flete,
    );

    // Elimina del DataTable del detalle
    fila.remove().draw(false);

    calcularTotales();
  });

  function obtenerTotales(documentos) {
    return documentos.reduce(
      (totales, d) => {
        totales.subtotal += Number(d.subtotal || 0);

        totales.igv += Number(d.igv || 0);

        totales.total += Number(d.total || 0);

        return totales;
      },
      {
        subtotal: 0,
        igv: 0,
        total: 0,
      },
    );
  }

  function calcularTotales() {
    const { subtotal, igv, total } = obtenerTotales(mvDocumentosAgregados);

    $("#mv_subtotal").text("S/ " + subtotal.toFixed(2));
    $("#mv_igv").text("S/ " + igv.toFixed(2));
    $("#mv_total").text("S/ " + total.toFixed(2));
  }

  document.getElementById("mv_guardar").addEventListener("click", function () {
    const btn = this;

    const id_cliente = document.getElementById("cliente_id").value;
    const id_serie = document.getElementById("mv_serie").value;

    if (!id_cliente) {
      Swal.fire({
        icon: "info",
        title: "Atención",
        text: "Selecciona un cliente.",
      });
      return;
    }

    if (!id_serie) {
      Swal.fire({
        icon: "info",
        title: "Atención",
        text: "Selecciona una serie.",
      });
      return;
    }

    if (mvDocumentosAgregados.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Atención",
        text: "Agregue por lo menos un documento.",
      });
      return;
    }

    const { subtotal, igv, total } = obtenerTotales(mvDocumentosAgregados);

    const formData = new FormData();

    formData.append("id_valorizacion", $("#mv_id_valorizacion").val());
    formData.append("id_cliente", id_cliente);
    formData.append("id_serie", id_serie);
    formData.append("observacion", $("#mv_observacion").val());
    formData.append("subtotal", subtotal.toFixed(2));
    formData.append("igv", igv.toFixed(2));
    formData.append("total", total.toFixed(2));

    formData.append(
      "documentos",
      JSON.stringify(
        mvDocumentosAgregados.map((d) => ({
          id_flete: d.id_flete,
          precio: d.precio,
        })),
      ),
    );

    btn.disabled = true;
    loading.classList.remove("d-none");

    fetch(instance._URL_ + "valorizacion/crud_register", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          instance.Datatable.reloadTable(table);
          bootstrap.Modal.getInstance(modalValorizacionEl).hide();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message || "No se pudo guardar la valorización.",
          });
        }
      })
      .catch((error) => {
        console.error(error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Ocurrió un error al procesar la solicitud.",
        });
      })
      .finally(() => {
        loading.classList.add("d-none");
        btn.disabled = false;
      });
  });

  // MODULO DE TABLA DE DOCUMENTOS PENDIENTES
  const table_documentos = $("#mv_tabla_disponibles").DataTable({
    destroy: true,
    responsive: true,
    fixedHeader: true,
    dom: "rtip",
    ordering: false,
    select: {
      style: "multi",
    },
    lengthMenu: [
      [10, 25, 50, -1],
      ["10 filas", "25 filas", "50 filas", "Mostrar todo"],
    ],
    columns: [
      {
        data: "origen",
      },
      {
        data: "destino",
      },
      {
        data: "vehiculo",
      },
      {
        data: "precio",
        className: "text-end",
        render: function (data) {
          return "S/ " + Number(data).toFixed(2);
        },
      },
    ],
    language: {
      url: "./public/plugins/datatable/language/es_es.json",
      emptyTable: "No hay documentos disponibles",
    },
  });

  table_documentos
    .on("select", function () {
      const seleccionados = table_documentos
        .rows({ selected: true })
        .data()
        .toArray();

      console.log(seleccionados);

      if (seleccionados.length === 1) {
        // Un documento seleccionado
      }
    })
    .on("deselect", function () {
      const seleccionados = table_documentos
        .rows({ selected: true })
        .data()
        .toArray();

      console.log(seleccionados);

      if (seleccionados.length === 0) {
        // Ningún documento seleccionado
      }
    });

  async function cargarVistaPreviaComprobante(idVenta) {
    try {
      const form = new FormData();

      form.set("id_venta", idVenta);

      const response = await fetch(
        instance._URL_ + "flete/get_comprobante_pendiente",
        {
          method: "POST",
          body: form,
        },
      );

      const text = await response.text();

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Respuesta backend:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      if (!data.success) {
        throw new Error(data.message || "No se pudo obtener el comprobante.");
      }

      pintarVistaPreviaComprobante(data.venta, data.detalle);

      bootstrap.Modal.getOrCreateInstance(
        document.getElementById("modalVistaPreviaComprobante"),
      ).show();
    } catch (error) {
      console.error(error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message || "No se pudo cargar el comprobante.",
      });
    }
  }

  function pintarVistaPreviaComprobante(venta, detalle) {
    const simbolo = venta.simbolo_moneda || "";

    $("#preview_id_venta").val(venta.id_venta);

    $("#preview_comprobante").text(`${venta.serie}-${venta.correlativo}`);

    $("#preview_cliente").text(venta.cliente || "-");

    $("#preview_documento").text(venta.num_docu || "-");

    $("#preview_fecha").text(venta.fecha_emision || "-");

    $("#preview_total").text(formatoMoneda(venta.total, simbolo));

    $("#preview_gravada").text(formatoMoneda(venta.op_gravada, simbolo));

    $("#preview_igv").text(formatoMoneda(venta.op_igv, simbolo));

    $("#preview_total_footer").text(formatoMoneda(venta.total, simbolo));

    let html = "";

    detalle.forEach((item) => {
      html += `
      <tr data-id-detalle="${item.id}">

        <td class="text-center">
          ${item.item}
        </td>

        <td>

          <textarea
            class="form-control descripcion-comprobante"
            rows="3"
            maxlength="1000"
            placeholder="Descripción que aparecerá en el comprobante"
          >${escapeHtml(item.descripcion || "")}</textarea>

          <small class="text-muted">
            Producto:
            ${escapeHtml(item.producto || "")}
          </small>

        </td>

        <td class="text-center">
          ${parseFloat(item.cantidad || 0).toFixed(2)}
        </td>

        <td class="text-end">
          ${formatoMoneda(item.precio_unitario, simbolo)}
        </td>

        <td class="text-end">
          ${formatoMoneda(item.igv, simbolo)}
        </td>

        <td class="text-end fw-semibold">
          ${formatoMoneda(item.importe_total, simbolo)}
        </td>

      </tr>
    `;
    });

    if (!detalle.length) {
      html = `
      <tr>
        <td
          colspan="6"
          class="text-center text-muted py-4"
        >
          El comprobante no tiene detalles.
        </td>
      </tr>
    `;
    }

    $("#tbodyPreviewDetalle").html(html);
  }

  function formatoMoneda(valor, simbolo = "") {
    const numero = parseFloat(valor || 0);

    return `${simbolo} ${numero.toLocaleString("es-PE", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`;
  }

  function escapeHtml(texto) {
    return $("<div>")
      .text(texto ?? "")
      .html();
  }

  async function guardarCambiosPreview(mostrarMensaje = true) {
    try {
      const idVenta = $("#preview_id_venta").val();

      if (!idVenta) {
        throw new Error("No se encontró el comprobante.");
      }

      const detalles = [];

      let errorDescripcion = false;

      $("#tbodyPreviewDetalle tr[data-id-detalle]").each(function () {
        const idDetalle = $(this).data("id-detalle");

        const descripcion = $(this)
          .find(".descripcion-comprobante")
          .val()
          .trim();

        if (!descripcion) {
          errorDescripcion = true;

          $(this).find(".descripcion-comprobante").addClass("is-invalid");

          return;
        }

        $(this).find(".descripcion-comprobante").removeClass("is-invalid");

        detalles.push({
          id_detalle: idDetalle,
          descripcion: descripcion,
        });
      });

      if (errorDescripcion) {
        Swal.fire({
          icon: "warning",
          title: "Descripción requerida",
          text: "Todos los detalles deben tener una descripción.",
        });

        return false;
      }

      if (detalles.length === 0) {
        throw new Error("El comprobante no tiene detalles.");
      }

      const form = new FormData();

      form.set("id_venta", idVenta);

      form.set("detalles", JSON.stringify(detalles));

      const response = await fetch(
        instance._URL_ + "flete/guardar_vista_previa",
        {
          method: "POST",
          body: form,
        },
      );

      const text = await response.text();

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Respuesta backend:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      if (!data.success) {
        throw new Error(data.message || "No se pudieron guardar los cambios.");
      }

      if (mostrarMensaje) {
        Swal.fire({
          icon: "success",
          title: data.message || "Cambios guardados correctamente.",
        });
      }

      return true;
    } catch (error) {
      console.error(error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message || "Ocurrió un error al guardar los cambios.",
      });

      return false;
    }
  }

  $("#btnGuardarPreview").on("click", async function () {
    await guardarCambiosPreview();
  });

  $("#btnEnviarSunat").on("click", async function () {
    const idVenta = parseInt($("#preview_id_venta").val() || 0);

    if (!idVenta) {
      Swal.fire({
        icon: "warning",
        title: "Comprobante no encontrado",
        text: "No se encontró el comprobante que se enviará a SUNAT.",
      });

      return;
    }

    // =====================================================
    // PRIMERO GUARDAMOS LOS CAMBIOS DE LA VISTA PREVIA
    // =====================================================

    const guardado = await guardarCambiosPreview(false);

    if (!guardado) {
      return;
    }

    // =====================================================
    // CONFIRMAR ENVÍO
    // =====================================================

    const confirmacion = await Swal.fire({
      icon: "question",
      title: "¿Enviar comprobante a SUNAT?",
      html: `
      <div class="text-start">
        <p>
          El comprobante será enviado a SUNAT con la información
          mostrada en la vista previa.
        </p>

        <p class="mb-0 text-danger">
          <strong>Importante:</strong>
          después del envío ya no podrás modificar la descripción
          desde esta vista.
        </p>
      </div>
    `,
      showCancelButton: true,
      confirmButtonText: "Sí, enviar a SUNAT",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#198754",
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    await enviarComprobanteSunat(idVenta);
  });

  async function enviarComprobanteSunat(idVenta) {
    const btnEnviar = document.getElementById("btnEnviarSunat");

    try {
      if (!idVenta) {
        throw new Error("No se encontró el comprobante.");
      }

      // =====================================================
      // BLOQUEAR BOTÓN
      // =====================================================

      if (btnEnviar) {
        btnEnviar.disabled = true;

        btnEnviar.innerHTML = `
        <span
          class="spinner-border spinner-border-sm me-2"
          role="status"
          aria-hidden="true">
        </span>
        Enviando...
      `;
      }

      // =====================================================
      // MOSTRAR LOADER GENERAL
      // =====================================================

      if (loading) {
        loading.classList.remove("d-none");
      }

      // =====================================================
      // FORM DATA
      // =====================================================

      const form = new FormData();

      form.set("id_venta", idVenta);

      // =====================================================
      // ENVIAR A SUNAT
      // =====================================================

      const response = await fetch(instance._URL_ + "flete/enviar_sunat", {
        method: "POST",
        body: form,
      });

      const text = await response.text();

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Respuesta backend:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      if (!response.ok) {
        throw new Error(
          data?.message || `Error del servidor (${response.status})`,
        );
      }

      if (!data.success) {
        throw new Error(
          data.message || "SUNAT rechazó o no pudo procesar el comprobante.",
        );
      }

      // =====================================================
      // CERRAR VISTA PREVIA
      // =====================================================

      const modalElement = document.getElementById(
        "modalVistaPreviaComprobante",
      );

      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {
          modal.hide();
        }
      }

      // =====================================================
      // RECARGAR VALORIZACIONES
      // =====================================================

      if (table) {
        table.ajax.reload(null, false);
      }

      // =====================================================
      // MENSAJE SUNAT
      // =====================================================

      await Swal.fire({
        icon: "success",
        title: "Comprobante enviado",
        text:
          data.message_sunat ||
          data.message ||
          "El comprobante fue enviado correctamente a SUNAT.",
        confirmButtonText: "Aceptar",
      });
    } catch (error) {
      console.error("Error SUNAT:", error);

      Swal.fire({
        icon: "error",
        title: "No se pudo enviar a SUNAT",
        text: error.message || "Ocurrió un error durante el envío.",
      });
    } finally {
      // =====================================================
      // RESTAURAR BOTÓN
      // =====================================================

      if (btnEnviar) {
        btnEnviar.disabled = false;

        btnEnviar.innerHTML = `
        <i class="fa-light fa-paper-plane me-1"></i>
        Enviar a SUNAT
      `;
      }

      if (loading) {
        loading.classList.add("d-none");
      }
    }
  }
});
