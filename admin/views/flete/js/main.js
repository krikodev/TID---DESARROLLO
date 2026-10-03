import * as instance from "../../../public/js/instance.js";
document.getElementById("link_flete").classList.add("active");

document.addEventListener("DOMContentLoaded", function () {
  const table = $("#table_flete").DataTable({
    ajax: {
      url: $("#url").val() + "flete/dataTable",
      method: "POST",

      beforeSend: function () {
        const loader = document.getElementById("loader");

        if (loader) {
          loader.classList.remove("d-none");
        }
      },

      complete: function () {
        const loader = document.getElementById("loader");

        if (loader) {
          loader.classList.add("d-none");
        }
      },
    },

    processing: true,
    serverSide: true,
    responsive: true,
    fixedHeader: true,
    dom: "rtip",

    lengthMenu: [
      [20, 25, 50, -1],
      ["20 filas", "25 filas", "50 filas", "Mostrar todo"],
    ],

    ordering: false,

    createdRow: function (row, data) {
      const estado = data.estado || "PENDIENTE";

      switch (estado) {
        case "PENDIENTE":
          $(row).addClass("flete-pendiente");
          break;

        case "PROGRAMADO":
          $(row).addClass("flete-programado");
          break;

        case "EN_CURSO":
          $(row).addClass("flete-en-curso");
          break;

        case "FINALIZADO":
          $(row).addClass("flete-finalizado");
          break;

        case "CANCELADO":
          $(row).addClass("flete-cancelado");
          break;
      }
    },
    columns: [
      {
        data: "numero_flete",
        defaultContent: "",
      },
      {
        data: "cliente_nombres",

        render: function (data, type, row) {
          return `
          ${row.cliente_nombres || ""}
          <br>
          <span class="text-secondary">
            ${row.cliente_num_docu || ""}
          </span>
        `;
        },
      },

      {
        data: "origen",
        defaultContent: "",
      },

      {
        data: "destino",
        defaultContent: "",
      },

      {
        data: null,

        render: function (data, type, row) {
          // -----------------------------------------------------
          // TERCERIZADO
          // -----------------------------------------------------
          if (row.tipo_operacion === "TERCERIZADO") {
            if (row.conductor_tercerizado_nombres) {
              return `
              ${row.conductor_tercerizado_nombres}
              <br>
              <span class="text-secondary">
                ${row.conductor_tercerizado_num_docu || ""}
              </span>
            `;
            }

            return `
            <span class="text-secondary">
              Sin conductor
            </span>
          `;
          }

          // -----------------------------------------------------
          // PROPIO / MIXTO
          // -----------------------------------------------------
          if (!row.id_conductor) {
            return `
            <span class="text-secondary">
              Sin conductor
            </span>
          `;
          }

          return `
          ${row.conductor_nombres || ""}
          <br>
          <span class="text-secondary">
            ${row.conductor_num_docu || ""}
          </span>
        `;
        },
      },

      // =========================================================
      // VEHÍCULO
      // =========================================================
      {
        data: null,

        render: function (data, type, row) {
          // -----------------------------------------------------
          // TERCERIZADO
          // -----------------------------------------------------
          if (row.tipo_operacion === "TERCERIZADO") {
            if (row.vehiculo_tercerizado_placa) {
              return `
              ${row.vehiculo_tercerizado_placa}
              ${
                row.vehiculo_tercerizado_descripcion
                  ? `<br>
                     <span class="text-secondary">
                       ${row.vehiculo_tercerizado_descripcion}
                     </span>`
                  : ""
              }
            `;
            }

            return `
            <span class="text-secondary">
              Sin vehículo
            </span>
          `;
          }

          // -----------------------------------------------------
          // PROPIO / MIXTO
          // -----------------------------------------------------
          if (!row.id_vehiculo) {
            return `
            <span class="text-secondary">
              Sin vehículo
            </span>
          `;
          }

          return `
          ${row.vehiculo_placa || ""}
          ${
            row.vehiculo_descripcion
              ? `<br>
                 <span class="text-secondary">
                   ${row.vehiculo_descripcion}
                 </span>`
              : ""
          }
        `;
        },
      },

      // =========================================================
      // FECHA SALIDA
      // =========================================================
      {
        data: "fecha_salida",
        defaultContent: "",
      },

      // =========================================================
      // HORA SALIDA
      // =========================================================
      {
        data: "hora_salida_format",

        render: function (data, type, row) {
          return data || row.hora_salida || "";
        },
      },

      // =========================================================
      // TOTAL
      // =========================================================
      {
        data: "total",
        render: function (data, type, row) {
          const simbolo = row.simbolo_moneda || "";

          return `
      <span class="fw-semibold">
        ${simbolo} ${parseFloat(data || 0).toFixed(2)}
      </span>
    `;
        },
      },

      // =========================================================
      // INCLUYE IGV
      // =========================================================
      {
        data: "incluye_igv",

        render: function (data, type, row) {
          if (row.incluye_igv == 1) {
            return `
            <span class="badge badge-soft badge-success-soft">
              Sí
            </span>
          `;
          }

          return `
          <span class="badge badge-soft badge-secondary-soft">
            No
          </span>
        `;
        },
      },

      // =========================================================
      // CONDICIÓN DE PAGO
      // =========================================================
      {
        data: "condicion_pago",

        render: function (data, type, row) {
          if (row.condicion_pago === "CREDITO") {
            return `
            <span class="badge badge-soft badge-warning-soft">
              CRÉDITO
            </span>
          `;
          }

          return `
          <span class="badge badge-soft badge-primary-soft">
            CONTADO
          </span>
        `;
        },
      },

      // =========================================================
      // ESTADO DEL FLETE
      // =========================================================
      {
        data: "estado",

        render: function (data, type, row) {
          const estado = row.estado || "PENDIENTE";

          if (estado === "FINALIZADO") {
            return `
            <span class="badge badge-soft badge-success-soft">
              ${estado}
            </span>
          `;
          }

          if (estado === "CANCELADO") {
            return `
            <span class="badge badge-soft badge-danger-soft">
              ${estado}
            </span>
          `;
          }

          if (estado === "EN_CURSO") {
            return `
            <span class="badge badge-soft badge-warning-soft">
              ${estado.replace("_", " ")}
            </span>
          `;
          }

          if (estado === "PROGRAMADO") {
            return `
            <span class="badge badge-soft badge-primary-soft">
              ${estado}
            </span>
          `;
          }

          return `
          <span class="badge badge-soft badge-secondary-soft">
            ${estado}
          </span>
        `;
        },
      },

      // =========================================================
      // ESTADO DE PAGO
      // =========================================================
      {
        data: "estado_pago",

        render: function (data, type, row) {
          const estado = row.estado_pago || "PENDIENTE";

          if (estado === "PAGADO") {
            return `
            <span class="badge badge-soft badge-success-soft">
              ${estado}
            </span>
          `;
          }

          if (estado === "PARCIAL") {
            return `
            <span class="badge badge-soft badge-warning-soft">
              ${estado}
            </span>
          `;
          }

          if (estado === "VENCIDO") {
            return `
            <span class="badge badge-soft badge-danger-soft">
              ${estado}
            </span>
          `;
          }

          return `
          <span class="badge badge-soft badge-secondary-soft">
            ${estado}
          </span>
        `;
        },
      },
      {
        data: "tipo_operacion",

        render: function (data, type, row) {
          const tipo_operacion = row.tipo_operacion;

          if (tipo_operacion === "TERCERIZADO") {
            return `
            <span class="badge badge-soft badge-info-soft">
              ${tipo_operacion}
            </span>
          `;
          }

          if (tipo_operacion === "PROPIO") {
            return `
            <span class="badge badge-soft badge-purple-soft">
              ${tipo_operacion}
            </span>
          `;
          }
        },
      },
      {
        data: "id_venta",
        render: function (data, type, row) {
          if (row.id_venta) {
            let ruta_impresion =
              instance.CONSTS.URL.IMPRESION_FACTURADOR.COMPROBANTE;
            return `<span class="badge" style="background-color:white; color:black; font-weight: 700">Pagado con: <a href="${instance._URL_ + ruta_impresion + row.id_venta}" target="_blank" style="color:blue;">${row.numero_comprobante}</a></span>`;
          } else {
            return `<span class="badge" style="background-color:white; color:black; font-weight: 700">-</span>`;
          }
        },
      },
      // =========================================================
      // ACCIONES
      // =========================================================
      {
        data: "id_flete",

        render: function (data, type, row) {
          // =====================================================
          // IMPRESIONES
          // =====================================================

          let btn_facturacion = "";

          if (row.estado === "FINALIZADO" && !row.id_venta) {
            btn_facturacion = `
    <li>
      <a
        href="javascript:void(0)"
        class="dropdown-item btn_dropdown btnFacturarFlete">

        <i class="fa-light fa-file-invoice-dollar me-2 text-success"></i>
        Facturar

      </a>
    </li>
  `;
          }

          const btn_impresion_a4 = `
      <li>
        <a
          href="${instance._URL_}flete/impresion/a4/${row.id_flete}"
          target="_blank"
          class="dropdown-item btn_dropdown">

          <i class="fa-light fa-print me-2 text-success"></i>
          Imprimir A4

        </a>
      </li>
    `;

          const btn_impresion_pos = `
      <li>
        <a
          href="${instance._URL_}flete/impresion/ticket/${row.id_flete}"
          target="_blank"
          class="dropdown-item btn_dropdown">

          <i class="fa-light fa-print me-2 text-success"></i>
          Imprimir POS

        </a>
      </li>
    `;

          // =====================================================
          // EDITAR
          // Solo pendiente / programado
          // =====================================================

          let btn_editar = "";

          if (row.estado === "PENDIENTE" || row.estado === "PROGRAMADO") {
            btn_editar = `
        <li>
          <a
            class="dropdown-item btn_dropdown btnSmallEditRegis"
            data-bs-toggle="modal"
            data-bs-target="#modal"
            data-mode="edit"
            data-bs-whatever="EDITAR FLETE">

            <i class="fa-light fa-pen-to-square me-2 text-primary"></i>
            Editar

          </a>
        </li>
      `;
          }

          // =====================================================
          // ACCIONES DE ESTADO
          // =====================================================

          let accionesEstado = "";

          // -----------------------------------------------------
          // PENDIENTE
          // -----------------------------------------------------

          if (row.estado === "PENDIENTE") {
            accionesEstado = `
        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnProgramarFlete">

            <i class="fa-light fa-calendar-check me-2 text-primary"></i>
            Programar

          </a>
        </li>

        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnCancelarFlete">

            <i class="fa-light fa-circle-xmark me-2 text-danger"></i>
            Cancelar

          </a>
        </li>
      `;
          }

          // -----------------------------------------------------
          // PROGRAMADO
          // -----------------------------------------------------

          if (row.estado === "PROGRAMADO") {
            accionesEstado = `
        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnIniciarFlete">

            <i class="fa-light fa-circle-play me-2 text-success"></i>
            Iniciar flete

          </a>
        </li>

        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnCancelarFlete">

            <i class="fa-light fa-circle-xmark me-2 text-danger"></i>
            Cancelar

          </a>
        </li>
      `;
          }

          // -----------------------------------------------------
          // EN CURSO
          // -----------------------------------------------------

          if (row.estado === "EN_CURSO") {
            accionesEstado = `
        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnFinalizarFlete">

            <i class="fa-light fa-circle-check me-2 text-success"></i>
            Finalizar flete

          </a>
        </li>
      `;
          }

          // =====================================================
          // DUPLICAR
          // =====================================================

          const btn_duplicar = `
      <li>
        <a
          class="dropdown-item btn_dropdown btnSmallCopiarRegis"
          data-bs-toggle="modal"
          data-bs-target="#modal"
          data-mode="copy"
          data-bs-whatever="NUEVO FLETE">

          <i class="fa-light fa-copy me-2 text-info"></i>
          Duplicar

        </a>
      </li>
    `;

          // =====================================================
          // ELIMINAR
          // Solo pendiente
          // =====================================================

          let btn_eliminar = "";

          if (row.estado === "PENDIENTE") {
            btn_eliminar = `
        <li>
          <a
            href="javascript:void(0)"
            class="dropdown-item btn_dropdown btnSmallDeleteRegis">

            <i class="fa-light fa-trash me-2 text-danger"></i>
            Eliminar

          </a>
        </li>
      `;
          }

          let btn_orden_servicio_a4 = "";
          let orden_servicio_ticket = "";
          if (row.tipo_operacion === "TERCERIZADO") {
            btn_orden_servicio_a4 = `
  <li>
    <a
      class="dropdown-item btn_dropdown"
      href="${instance._URL_}flete/impresion/orden_servicio_a4/${row.id_flete}"
      target="_blank"
    >
      <i class="fa-light fa-file-contract me-2 text-warning"></i>
      Orden de Servicio A4
    </a>
  </li>
`;

            orden_servicio_ticket = `
  <li>
    <a
      class="dropdown-item btn_dropdown"
      href="${instance._URL_}flete/impresion/orden_servicio_ticket/${row.id_flete}"
      target="_blank"
    >
      <i class="fa-light fa-file-contract me-2 text-warning"></i>
      Orden de Servicio POS
    </a>
  </li>
`;
          }

          let btn_tercerizado = "";

          if (row.tipo_operacion === "TERCERIZADO") {
            btn_tercerizado = `
    <li>
      <a
        href="javascript:void(0)"
        class="
          dropdown-item
          btn_dropdown
          btnVerTercerizado
        "
      >
        <i
          class="
            fa-light
            fa-hand-holding-dollar
            me-2
            text-success
          "
        ></i>

        Control de tercerización
      </a>
    </li>
  `;
          }
          // =====================================================
          // REVISAR / VER COMPROBANTE
          // =====================================================

          let btn_revisar_comprobante = "";

          if (row.estado === "FINALIZADO" && row.id_venta) {
            // -----------------------------------------------------
            // COMPROBANTE PENDIENTE DE ENVÍO A SUNAT
            // -----------------------------------------------------

            if (parseInt(row.envio_sunat || 0) === 0) {
              btn_revisar_comprobante = `
      <li>
        <a
          href="javascript:void(0)"
          class="
            dropdown-item
            btn_dropdown
            btnRevisarComprobante
          "
          data-id-venta="${row.id_venta}"
        >
          <i
            class="
              fa-light
              fa-eye
              me-2
              text-warning
            "
          ></i>

          Revisar comprobante
        </a>
      </li>
    `;
            }

            // -----------------------------------------------------
            // COMPROBANTE YA ENVIADO
            // -----------------------------------------------------
            else if (parseInt(row.envio_sunat) === 1) {
              btn_revisar_comprobante = `
      <li>
        <a
          href="javascript:void(0)"
          class="
            dropdown-item
            btn_dropdown
            btnVerComprobante
          "
          data-id-venta="${row.id_venta}"
        >
          <i
            class="
              fa-light
              fa-file-invoice
              me-2
              text-primary
            "
          ></i>

          Ver comprobante
        </a>
      </li>
    `;
            }
          }

          // =====================================================
          // SEPARADOR DE ESTADOS
          // =====================================================

          let separadorEstado = "";

          if (accionesEstado !== "") {
            separadorEstado = `
        <li>
          <hr class="dropdown-divider">
        </li>
      `;
          }

          // =====================================================
          // RETURN
          // =====================================================

          return `
  <div class="dropdown">

    <a
      class="dropdown-toggle"
      data-bs-toggle="dropdown"
      href="javascript:void(0)"
      role="button"
      aria-expanded="false">

      <i class="bi bi-three-dots-vertical"></i>

    </a>

    <ul class="dropdown-menu">
      ${accionesEstado}
      ${separadorEstado}
      ${btn_editar}

      ${btn_revisar_comprobante}

      ${btn_facturacion}
      ${btn_duplicar}

      ${btn_eliminar}
                  <li>
        <hr class="dropdown-divider">
      </li>

      ${btn_tercerizado}
      ${btn_impresion_a4}

      ${btn_impresion_pos}
      ${btn_orden_servicio_a4}
      ${orden_servicio_ticket}

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

  // ============================================================
  // ELEMENTOS DEL FORMULARIO
  // ============================================================
  let cargandoFlete = false;
  let cotizacionController = null;
  const form = document.querySelector("#form");

  const id_flete = document.querySelector("#id_flete");

  const cotizacion = document.querySelector("#cotizacion");

  const cliente = document.querySelector("#cliente");
  const cliente_id = document.querySelector("#cliente_id");

  const ubigeo_origen = document.querySelector("#ubigeo_origen");
  const direccion_origen = document.querySelector("#direccion_origen");

  const ubigeo_destino = document.querySelector("#ubigeo_destino");
  const direccion_destino = document.querySelector("#direccion_destino");

  const moneda = document.getElementById("moneda");

  // ============================================================
  // PROPIO
  // ============================================================

  const vehiculo = document.querySelector("#vehiculo");
  const conductor = document.querySelector("#conductor");

  // ============================================================
  // TERCERIZADO
  // ============================================================

  const proveedor = document.querySelector("#proveedor");

  const vehiculo_tercerizado = document.querySelector("#vehiculo_tercerizado");

  const conductor_tercerizado = document.querySelector(
    "#conductor_tercerizado",
  );

  const costo_tercerizado = document.querySelector("#costo_tercerizado");

  const estado_tercerizado = document.querySelector("#estado_tercerizado");

  const observacion_tercerizado = document.querySelector(
    "#observacion_tercerizado",
  );

  // ============================================================
  // FECHA / PAGO
  // ============================================================

  const fecha_salida = document.querySelector("#fecha_salida");
  const hora_salida = document.querySelector("#hora_salida");

  const condicion_pago = document.querySelector("#condicion_pago");

  const dias_credito = document.querySelector("#dias_credito");

  const fecha_vencimiento = document.querySelector("#fecha_vencimiento");

  const div_parentDiasCredito = document.querySelector(
    "#div_parentDiasCredito",
  );

  const divFechaVencimiento = document.querySelector(
    "#div_parentFechaVencimiento",
  );

  // ============================================================
  // IMPORTES
  // ============================================================

  const precio = document.querySelector("#precio");

  const total = document.querySelector("#total");

  const observacion = document.querySelector("#observacion");

  // ============================================================
  // BOTONES
  // ============================================================

  const button_save = document.querySelector("#button_save");

  const button_cancel = document.querySelector("#button_cancel");

  const button_loadSave = document.querySelector("#button_loadSave");

  // ============================================================
  // RADIOS
  // ============================================================

  const incluyeIgv = document.querySelectorAll('input[name="incluye_igv"]');

  // ============================================================
  // INSTANCIAS GENERALES
  // ============================================================

  instance.Modal.change_name(["#modal"]);

  instance.Datatable.inputSearch(
    table,
    ".inputSearch",
    ".btnSearchTable",
    "manual",
  );

  // ============================================================
  // VALIDACIÓN MONEDA
  // ============================================================

  if (precio) {
    instance.Validate.allowInputMoney(["#precio"]);
  }

  if (costo_tercerizado) {
    instance.Validate.allowInputMoney(["#costo_tercerizado"]);
  }

  // ============================================================
  // SELECTS
  // ============================================================

  if (vehiculo && document.querySelector("#div_parentVehiculo")) {
    instance.select.createSelect("#vehiculo", "#div_parentVehiculo");
  }

  if (conductor && document.querySelector("#div_parentConductor")) {
    instance.select.createSelect("#conductor", "#div_parentConductor");
  }

  if (cotizacion && document.querySelector("#div_parentCotizacion")) {
    instance.select.createSelect("#cotizacion", "#div_parentCotizacion");
  }

  if (proveedor && document.querySelector("#div_parentTercerizado")) {
    instance.select.createSelect("#proveedor", "#div_parentTercerizado");
  }

  if (proveedor && document.querySelector("#div_parentMoneda")) {
    instance.select.createSelect("#moneda", "#div_parentMoneda");
  }

  if (
    vehiculo_tercerizado &&
    document.querySelector("#div_parentTercerizado")
  ) {
    instance.select.createSelect(
      "#vehiculo_tercerizado",
      "#div_parentTercerizado",
    );
  }

  if (
    conductor_tercerizado &&
    document.querySelector("#div_parentTercerizado")
  ) {
    instance.select.createSelect(
      "#conductor_tercerizado",
      "#div_parentTercerizado",
    );
  }

  // ============================================================
  // UBIGEOS
  // ============================================================

  function initUbigeoSelect(selectId) {
    const element = document.querySelector(selectId);

    if (!element) {
      return null;
    }

    const ts = new TomSelect(selectId, {
      valueField: "cod_ubigeo",

      labelField: "nombre",

      searchField: "nombre",

      placeholder: "Buscar ubigeo...",

      preload: false,

      load: function (query, callback) {
        if (!query.length) {
          callback();
          return;
        }

        const formData = new FormData();

        formData.set("q", query);

        fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
          method: "POST",
          body: formData,
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error("Error HTTP: " + response.status);
            }

            return response.json();
          })
          .then(function (json) {
            if (json.success) {
              callback(json.items);
            } else {
              callback();
            }
          })
          .catch(function (error) {
            console.error("Error al buscar ubigeo:", error);

            callback();
          });
      },
    });

    return ts;
  }

  function setUbigeoValue(ts, codUbigeo) {
    if (!ts) {
      return;
    }

    if (!codUbigeo) {
      ts.clear();
      return;
    }

    const formData = new FormData();

    formData.set("pre", codUbigeo);

    fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP: " + response.status);
        }

        return response.json();
      })
      .then(function (json) {
        if (json.success && json.items && json.items.length) {
          const item = json.items[0];

          ts.addOption(item);

          ts.setValue(item.cod_ubigeo);
        }
      })
      .catch(function (error) {
        console.error("Error al establecer ubigeo:", error);
      });
  }

  const ubigeoOrigen = initUbigeoSelect("#ubigeo_origen");

  const ubigeoDestino = initUbigeoSelect("#ubigeo_destino");

  // ============================================================
  // LIMPIAR SELECT
  // ============================================================

  function limpiarSelect(select) {
    if (!select) {
      return;
    }

    if (select.tomselect) {
      select.tomselect.clear(true);
    } else {
      $(select).val("").trigger("change");
    }
  }

  // ============================================================
  // ASIGNAR VALOR SELECT
  // ============================================================

  function setSelectValue(select, value) {
    if (!select) {
      return;
    }

    const valor = value !== undefined && value !== null ? String(value) : "";

    if (select.tomselect) {
      select.tomselect.setValue(valor);
    } else {
      $(select).val(valor).trigger("change");
    }
  }

  // ============================================================
  // TIPO DE OPERACIÓN
  // ============================================================

  function getTipoOperacion() {
    const selected = document.querySelector(
      'input[name="tipo_operacion"]:checked',
    );

    if (!selected) {
      return "PROPIO";
    }

    return selected.value;
  }

  function actualizarTipoOperacion() {
    const tipoOperacion = getTipoOperacion();

    // ========================================================
    // PROPIO
    // ========================================================

    if (tipoOperacion === "PROPIO") {
      $("#div_parentVehiculo").removeClass("d-none");

      $("#div_parentConductor").removeClass("d-none");

      $("#div_parentTercerizado").addClass("d-none");

      $(vehiculo).prop("required", true);

      $(conductor).prop("required", true);

      $(proveedor).prop("required", false);

      $(vehiculo_tercerizado).prop("required", false);

      $(conductor_tercerizado).prop("required", false);

      $(costo_tercerizado).prop("required", false);

      return;
    }

    // ========================================================
    // TERCERIZADO
    // ========================================================

    if (tipoOperacion === "TERCERIZADO") {
      $("#div_parentVehiculo").addClass("d-none");

      $("#div_parentConductor").addClass("d-none");

      $("#div_parentTercerizado").removeClass("d-none");

      $(vehiculo).prop("required", false);

      $(conductor).prop("required", false);

      $(proveedor).prop("required", true);

      $(vehiculo_tercerizado).prop("required", true);

      $(conductor_tercerizado).prop("required", true);

      $(costo_tercerizado).prop("required", true);

      return;
    }

    // ========================================================
    // MIXTO
    // ========================================================
    //
    // AÚN NO DEFINIMOS LA REGLA DE NEGOCIO.
    // Por ahora funcionará como PROPIO.
    //
    // ========================================================

    if (tipoOperacion === "MIXTO") {
      $("#div_parentVehiculo").removeClass("d-none");

      $("#div_parentConductor").removeClass("d-none");

      $("#div_parentTercerizado").addClass("d-none");

      $(vehiculo).prop("required", true);

      $(conductor).prop("required", true);

      $(proveedor).prop("required", false);

      $(vehiculo_tercerizado).prop("required", false);

      $(conductor_tercerizado).prop("required", false);

      $(costo_tercerizado).prop("required", false);
    }
  }

  // ============================================================
  // CONDICIÓN DE PAGO
  // ============================================================

  function actualizarCondicionPago() {
    if (!condicion_pago) {
      return;
    }

    if (condicion_pago.value === "CREDITO") {
      if (div_parentDiasCredito) {
        div_parentDiasCredito.classList.remove("d-none");
      }

      if (divFechaVencimiento) {
        divFechaVencimiento.classList.remove("d-none");
      }

      if (dias_credito) {
        dias_credito.required = true;

        if (!dias_credito.value || Number(dias_credito.value) <= 0) {
          dias_credito.value = 30;
        }
      }

      if (fecha_vencimiento) {
        fecha_vencimiento.required = true;
      }

      calcularFechaVencimiento();
    } else {
      if (div_parentDiasCredito) {
        div_parentDiasCredito.classList.add("d-none");
      }

      if (divFechaVencimiento) {
        divFechaVencimiento.classList.add("d-none");
      }

      if (dias_credito) {
        dias_credito.required = false;
        dias_credito.value = 0;
      }

      if (fecha_vencimiento) {
        fecha_vencimiento.required = false;
        fecha_vencimiento.value = "";
      }
    }
  }

  // ============================================================
  // FECHA VENCIMIENTO
  // ============================================================

  function calcularFechaVencimiento() {
    if (!fecha_salida || !dias_credito || !fecha_vencimiento) {
      return;
    }

    const dias = parseInt(dias_credito.value || 0);

    if (dias <= 0) {
      fecha_vencimiento.value = "";

      return;
    }

    if (!fecha_salida.value) {
      return;
    }

    const partes = fecha_salida.value.split("-");

    if (partes.length !== 3) {
      return;
    }

    const fecha = new Date(
      parseInt(partes[0]),
      parseInt(partes[1]) - 1,
      parseInt(partes[2]),
    );

    fecha.setDate(fecha.getDate() + dias);

    const year = fecha.getFullYear();

    const month = String(fecha.getMonth() + 1).padStart(2, "0");

    const day = String(fecha.getDate()).padStart(2, "0");

    fecha_vencimiento.value = `${year}-${month}-${day}`;
  }

  // ============================================================
  // IGV
  // ============================================================

  function getIncluyeIgv() {
    const selected = document.querySelector(
      'input[name="incluye_igv"]:checked',
    );

    if (!selected) {
      return "0";
    }

    return selected.value;
  }

  // ============================================================
  // IMPORTES
  // ============================================================

  function calcularImportes() {
    let importe = parseFloat(String(precio?.value || "0").replace(/,/g, ""));

    if (isNaN(importe)) {
      importe = 0;
    }

    let subtotal = 0;
    let igv = 0;
    let totalCalculado = 0;

    if (getIncluyeIgv() === "1") {
      totalCalculado = importe;

      subtotal = importe / 1.18;

      igv = totalCalculado - subtotal;
    } else {
      subtotal = importe;

      igv = subtotal * 0.18;

      totalCalculado = subtotal + igv;
    }

    if (total) {
      total.value = totalCalculado.toFixed(2);
    }

    return {
      subtotal: subtotal.toFixed(2),

      igv: igv.toFixed(2),

      total: totalCalculado.toFixed(2),
    };
  }

  // ============================================================
  // LIMPIAR FORMULARIO
  // ============================================================

  function limpiarFormulario() {
    if (!form) {
      return;
    }

    form.reset();

    form.classList.remove("was-validated");

    if (id_flete) {
      id_flete.value = "";
    }

    if (cliente) {
      cliente.value = "";
    }

    if (cliente_id) {
      cliente_id.value = "";
    }

    if (direccion_origen) {
      direccion_origen.value = "";
    }

    if (direccion_destino) {
      direccion_destino.value = "";
    }

    if (observacion) {
      observacion.value = "";
    }

    // ========================================================
    // SELECTS PRINCIPALES
    // ========================================================

    limpiarSelect(vehiculo);

    limpiarSelect(conductor);
    limpiarSelect(moneda);

    limpiarSelect(cotizacion);

    // ========================================================
    // TERCERIZADO
    // ========================================================

    limpiarSelect(proveedor);

    limpiarSelect(vehiculo_tercerizado);

    limpiarSelect(conductor_tercerizado);

    if (costo_tercerizado) {
      costo_tercerizado.value = "";
    }

    if (estado_tercerizado) {
      estado_tercerizado.value = "PENDIENTE";
    }

    if (observacion_tercerizado) {
      observacion_tercerizado.value = "";
    }

    // ========================================================
    // UBIGEOS
    // ========================================================

    if (ubigeoOrigen) {
      ubigeoOrigen.clear(true);
    }

    if (ubigeoDestino) {
      ubigeoDestino.clear(true);
    }

    // ========================================================
    // CONDICIÓN PAGO
    // ========================================================

    if (condicion_pago) {
      condicion_pago.value = "CONTADO";
    }

    if (dias_credito) {
      dias_credito.value = 0;
    }

    if (fecha_vencimiento) {
      fecha_vencimiento.value = "";
    }

    // ========================================================
    // TIPO OPERACIÓN
    // ========================================================

    const tipoPropio = document.querySelector(
      'input[name="tipo_operacion"][value="PROPIO"]',
    );

    if (tipoPropio) {
      tipoPropio.checked = true;
    }

    // ========================================================
    // IGV
    // ========================================================

    const igvSi = document.querySelector(
      'input[name="incluye_igv"][value="1"]',
    );

    if (igvSi) {
      igvSi.checked = true;
    }

    // ========================================================
    // TOTAL
    // ========================================================

    if (total) {
      total.value = "";
    }

    actualizarTipoOperacion();

    actualizarCondicionPago();

    calcularImportes();
  }

  // ============================================================
  // UBIGEO DE TERMINAL
  // ============================================================

  function cargarUbigeoSesion() {
    if (ubigeoOrigen && instance._UB_TERMINAL_SESION) {
      setUbigeoValue(ubigeoOrigen, instance._UB_TERMINAL_SESION);
    }

    if (direccion_origen && instance._DIR_TERMINAL_SESION) {
      direccion_origen.value = instance._DIR_TERMINAL_SESION;
    }
  }

  // ============================================================
  // EVENTOS
  // ============================================================

  $('input[name="tipo_operacion"]').on("change", function () {
    actualizarTipoOperacion();
  });

  if (condicion_pago) {
    condicion_pago.addEventListener("change", function () {
      actualizarCondicionPago();
    });
  }

  if (dias_credito) {
    dias_credito.addEventListener("input", function () {
      calcularFechaVencimiento();
    });
  }

  if (fecha_salida) {
    fecha_salida.addEventListener("change", function () {
      calcularFechaVencimiento();
    });
  }

  incluyeIgv.forEach(function (radio) {
    radio.addEventListener("change", function () {
      calcularImportes();
    });
  });

  if (precio) {
    precio.addEventListener("input", function () {
      calcularImportes();
    });
  }

  // ============================================================
  // MODAL
  // ============================================================

  $("#modal").on("show.bs.modal", function (e) {
    let mode = $("#modal").data("mode");

    if (!mode && e.relatedTarget) {
      mode = $(e.relatedTarget).data("mode");
    }

    $("#modal").data("mode", mode);

    if (mode === "new") {
      limpiarFormulario();

      cargarUbigeoSesion();
    }
  });

  $("#modal").on("hidden.bs.modal", function () {
    limpiarFormulario();

    $("#modal").removeData("mode");
  });

  // ============================================================
  // SUBMIT
  // ============================================================

  form?.addEventListener("submit", function (e) {
    e.preventDefault();

    actualizarTipoOperacion();

    actualizarCondicionPago();

    const tipoOperacionActual = getTipoOperacion();

    // ======================================================
    // VALIDACIONES GENERALES
    // ======================================================

    const data = [
      ubigeo_origen ? ubigeo_origen.value : "",

      ubigeo_destino ? ubigeo_destino.value : "",

      fecha_salida ? fecha_salida.value : "",

      hora_salida ? hora_salida.value : "",

      cliente_id ? cliente_id.value : "",

      precio ? precio.value : "",

      getIncluyeIgv(),
    ];

    // ======================================================
    // PROPIO
    // ======================================================

    if (tipoOperacionActual === "PROPIO") {
      data.push(
        vehiculo ? vehiculo.value : "",

        conductor ? conductor.value : "",
      );
    }

    // ======================================================
    // TERCERIZADO
    // ======================================================

    if (tipoOperacionActual === "TERCERIZADO") {
      data.push(
        proveedor ? proveedor.value : "",

        vehiculo_tercerizado ? vehiculo_tercerizado.value : "",

        conductor_tercerizado ? conductor_tercerizado.value : "",

        costo_tercerizado ? costo_tercerizado.value : "",
      );
    }

    // ======================================================
    // MIXTO
    // ======================================================

    if (tipoOperacionActual === "MIXTO") {
      data.push(
        vehiculo ? vehiculo.value : "",

        conductor ? conductor.value : "",
      );
    }

    // ======================================================
    // CRÉDITO
    // ======================================================

    if (condicion_pago && condicion_pago.value === "CREDITO") {
      data.push(
        dias_credito ? dias_credito.value : "",

        fecha_vencimiento ? fecha_vencimiento.value : "",
      );
    }

    if (!instance.Validate.validateData(data)) {
      instance.Toast.operacion_erronea("Rellene correctamente los campos");

      return;
    }

    // ======================================================
    // IMPORTES
    // ======================================================

    const importes = calcularImportes();

    // ======================================================
    // FORMDATA
    // ======================================================

    const formData = new FormData(form);

    formData.set("tipo_operacion", tipoOperacionActual);

    formData.set("incluye_igv", getIncluyeIgv());

    formData.set("subtotal", importes.subtotal);

    formData.set("igv", importes.igv);

    formData.set("total", importes.total);

    formData.set("moneda", moneda.value);

    // ======================================================
    // PAGO
    // ======================================================

    if (condicion_pago) {
      formData.set("condicion_pago", condicion_pago.value);
    }

    if (condicion_pago && condicion_pago.value === "CREDITO") {
      formData.set("dias_credito", dias_credito ? dias_credito.value : "0");

      formData.set(
        "fecha_vencimiento",
        fecha_vencimiento ? fecha_vencimiento.value : "",
      );
    } else {
      formData.set("dias_credito", "0");

      formData.set("fecha_vencimiento", "");
    }

    // ======================================================
    // PROPIO
    // ======================================================

    if (tipoOperacionActual === "PROPIO") {
      formData.set("vehiculo", vehiculo ? vehiculo.value : "");

      formData.set("conductor", conductor ? conductor.value : "");

      // Vaciar tercerización
      formData.set("id_proveedor", "");

      formData.set("id_vehiculo_tercerizado", "");

      formData.set("id_conductor_tercerizado", "");

      formData.set("costo_tercerizado", "0");

      formData.set("estado_tercerizado", "");

      formData.set("observacion_tercerizado", "");
    }

    // ======================================================
    // TERCERIZADO
    // ======================================================

    if (tipoOperacionActual === "TERCERIZADO") {
      // Flete no usa vehículo/conductor propio
      formData.set("vehiculo", "");

      formData.set("conductor", "");

      formData.set("id_proveedor", proveedor ? proveedor.value : "");

      formData.set(
        "id_vehiculo_tercerizado",
        vehiculo_tercerizado ? vehiculo_tercerizado.value : "",
      );

      formData.set(
        "id_conductor_tercerizado",
        conductor_tercerizado ? conductor_tercerizado.value : "",
      );

      formData.set(
        "costo_tercerizado",
        costo_tercerizado ? costo_tercerizado.value : "0",
      );

      formData.set(
        "estado_tercerizado",
        estado_tercerizado ? estado_tercerizado.value : "PENDIENTE",
      );

      formData.set(
        "observacion_tercerizado",
        observacion_tercerizado ? observacion_tercerizado.value : "",
      );
    }

    // ======================================================
    // MIXTO
    // ======================================================
    //
    // Por ahora se comporta como PROPIO.
    //
    // ======================================================

    if (tipoOperacionActual === "MIXTO") {
      formData.set("vehiculo", vehiculo ? vehiculo.value : "");

      formData.set("conductor", conductor ? conductor.value : "");
    }

    // ======================================================
    // LOADER BOTONES
    // ======================================================

    button_save?.classList.add("d-none");

    button_cancel?.classList.add("d-none");

    button_loadSave?.classList.remove("d-none");

    // ======================================================
    // FETCH
    // ======================================================

    fetch(instance._URL_ + "flete/crud_register", {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP: " + response.status);
        }

        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          instance.Toast.operacion_exitosa(data.message);

          $("#modal").modal("hide");

          instance.Datatable.reloadTable(table);
        } else {
          instance.Toast.operacion_erronea(
            data.message || "No se pudo realizar la operación.",
          );
        }
      })
      .catch(function (error) {
        console.error(error);

        instance.Toast.operacion_erronea(
          error.message || "Ha ocurrido un error, inténtalo más tarde.",
        );
      })
      .finally(function () {
        button_save?.classList.remove("d-none");

        button_cancel?.classList.remove("d-none");

        button_loadSave?.classList.add("d-none");
      });
  });

  // ============================================================
  // EDITAR / DUPLICAR
  // ============================================================

  $("#table_flete tbody").on(
    "click",
    ".btnSmallEditRegis, .btnSmallCopiarRegis",
    function () {
      const data = obtenerFilaDataTable(this);

      if (!data) {
        instance.Toast.operacion_erronea(
          "No se pudo obtener la información del flete.",
        );
        return;
      }

      const isEdit = $(this).hasClass("btnSmallEditRegis");

      // ======================================================
      // CANCELAR CUALQUIER CARGA ANTERIOR DE COTIZACIÓN
      // ======================================================

      if (cotizacionController) {
        cotizacionController.abort();
        cotizacionController = null;
      }

      // ======================================================
      // INDICAR QUE ESTAMOS CARGANDO UN FLETE
      // ======================================================
      //
      // Esto evita que setSelectValue(cotizacion, ...)
      // dispare el evento change y vuelva a cargar los datos
      // originales de la cotización.
      // ======================================================

      cargandoFlete = true;

      try {
        limpiarFormulario();

        // ======================================================
        // ID
        // ======================================================

        if (id_flete) {
          id_flete.value = isEdit ? data.id_flete : "";
        }

        // ======================================================
        // COTIZACIÓN
        // ======================================================

        if (cotizacion) {
          setSelectValue(cotizacion, data.id_cotizacion ?? "");
        }

        // ======================================================
        // UBIGEOS
        // ======================================================

        if (ubigeoOrigen) {
          setUbigeoValue(ubigeoOrigen, data.ubigeo_origen ?? "");
        }

        if (direccion_origen) {
          direccion_origen.value = data.direccion_origen ?? "";
        }

        if (ubigeoDestino) {
          setUbigeoValue(ubigeoDestino, data.ubigeo_destino ?? "");
        }

        if (direccion_destino) {
          direccion_destino.value = data.direccion_destino ?? "";
        }

        // ======================================================
        // CLIENTE
        // ======================================================

        if (cliente) {
          const documento = data.cliente_num_docu ?? "";

          const nombres = data.cliente_nombres ?? "";

          cliente.value = [documento, nombres].filter(Boolean).join(" - ");
        }

        if (cliente_id) {
          cliente_id.value = data.id_cliente ?? "";
        }

        // ======================================================
        // TIPO OPERACIÓN
        // ======================================================

        const tipo = data.tipo_operacion ?? "PROPIO";

        const radioTipo = document.querySelector(
          `input[name="tipo_operacion"][value="${tipo}"]`,
        );

        if (radioTipo) {
          radioTipo.checked = true;
        }

        // Mostrar / ocultar campos correspondientes
        actualizarTipoOperacion();

        // ======================================================
        // PROPIO / MIXTO
        // ======================================================

        if (tipo === "PROPIO" || tipo === "MIXTO") {
          if (vehiculo) {
            setSelectValue(vehiculo, data.id_vehiculo ?? "");
          }

          if (conductor) {
            setSelectValue(conductor, data.id_conductor ?? "");
          }
        }

        // ======================================================
        // MONEDA
        // ======================================================

        if (moneda) {
          setSelectValue(moneda, data.id_tp_moneda ?? "");
        }

        // ======================================================
        // TERCERIZADO
        // ======================================================

        if (tipo === "TERCERIZADO") {
          if (proveedor) {
            setSelectValue(proveedor, data.id_proveedor ?? "");
          }

          if (vehiculo_tercerizado) {
            setSelectValue(
              vehiculo_tercerizado,
              data.id_vehiculo_tercerizado ??
                data.tercerizado_id_vehiculo ??
                "",
            );
          }

          if (conductor_tercerizado) {
            setSelectValue(
              conductor_tercerizado,
              data.id_conductor_tercerizado ??
                data.tercerizado_id_conductor ??
                "",
            );
          }

          if (costo_tercerizado) {
            costo_tercerizado.value =
              data.costo_tercerizado ?? data.costo ?? "";
          }

          if (estado_tercerizado) {
            estado_tercerizado.value = data.estado_tercerizado ?? "PENDIENTE";
          }

          if (observacion_tercerizado) {
            observacion_tercerizado.value = data.observacion_tercerizado ?? "";
          }
        }

        // ======================================================
        // FECHA / HORA
        // ======================================================

        if (fecha_salida) {
          fecha_salida.value = data.fecha_salida ?? "";
        }

        if (hora_salida) {
          hora_salida.value = data.hora_salida ?? "";
        }

        // ======================================================
        // IGV
        // ======================================================

        const incluyeIgv = Number(data.incluye_igv ?? 0);

        const radioIgv = document.querySelector(
          `input[name="incluye_igv"][value="${incluyeIgv}"]`,
        );

        if (radioIgv) {
          radioIgv.checked = true;
        }

        // ======================================================
        // PRECIO
        // ======================================================

        if (precio) {
          /*
           * Si incluye IGV:
           * precio = total
           *
           * Si NO incluye IGV:
           * precio = subtotal
           */

          if (incluyeIgv === 1) {
            precio.value = data.total ?? data.precio ?? "";
          } else {
            precio.value = data.subtotal ?? data.precio ?? "";
          }
        }

        // ======================================================
        // OBSERVACIÓN
        // ======================================================

        if (observacion) {
          observacion.value = data.observacion ?? "";
        }

        // ======================================================
        // CONDICIÓN DE PAGO
        // ======================================================

        const condicion = data.condicion_pago ?? "CONTADO";

        if (condicion_pago) {
          condicion_pago.value = condicion;
        }

        if (dias_credito) {
          dias_credito.value = data.dias_credito ?? 0;
        }

        if (fecha_vencimiento) {
          fecha_vencimiento.value = data.fecha_vencimiento ?? "";
        }

        // ======================================================
        // ACTUALIZAR UI
        // ======================================================

        actualizarCondicionPago();

        calcularImportes();

        // ======================================================
        // FECHA VENCIMIENTO
        // ======================================================

        if (
          condicion === "CREDITO" &&
          fecha_vencimiento &&
          !fecha_vencimiento.value
        ) {
          calcularFechaVencimiento();
        }
      } finally {
        // ======================================================
        // FINALIZÓ LA CARGA DEL FLETE
        // ======================================================

        cargandoFlete = false;
      }
    },
  );
  // ============================================================
  // ELIMINAR
  // ============================================================

  $("#table_flete tbody").on("click", ".btnSmallDeleteRegis", function () {
    const data = table.row($(this).parents("tr")).data();

    Swal.fire({
      title: "Necesitamos de tu Confirmación",

      html: `
          <div>

            <label>
              Se eliminará del sistema
              el registro seleccionado
            </label>

            <br>

            <p class="fs-6 mt-3 mb-0 text-success">
              ¿Está Usted de Acuerdo?
            </p>

          </div>
        `,

      icon: "warning",

      showCancelButton: true,

      confirmButtonColor: "#429ef5",

      cancelButtonColor: "#ff3b3b",

      cancelButtonText: "No!",

      confirmButtonText: "Si, Adelante!",
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }

      const formData = new FormData();

      formData.set("id_flete", data.id_flete);

      fetch(instance._URL_ + "flete/delete_register", {
        method: "POST",
        body: formData,
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Error HTTP: " + response.status);
          }

          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            instance.Toast.operacion_exitosa(data.message);

            instance.Datatable.reloadTable(table);
          } else {
            instance.Toast.operacion_erronea(
              data.message || "No se pudo eliminar el registro.",
            );
          }
        })
        .catch(function (error) {
          console.error(error);

          instance.Toast.operacion_erronea(
            error.message || "Ha ocurrido un error.",
          );
        });
    });
  });

  // ============================================================
  // CARGAR DATOS DE COTIZACIÓN
  // ============================================================

  $("#cotizacion").on("change", function (e) {
    e.preventDefault();

    // ======================================================
    // IGNORAR CHANGE GENERADO POR EDITAR / DUPLICAR
    // ======================================================
    //
    // setSelectValue() puede disparar change.
    // En ese caso NO queremos volver a consultar la
    // cotización porque reemplazaría los datos del flete.
    // ======================================================

    if (cargandoFlete) {
      return;
    }

    const idCotizacion = $(this).val();

    const loader = document.getElementById("loader");

    // ======================================================
    // CANCELAR PETICIÓN ANTERIOR
    // ======================================================

    if (cotizacionController) {
      cotizacionController.abort();
      cotizacionController = null;
    }

    // ======================================================
    // NO HAY COTIZACIÓN
    // ======================================================

    if (!idCotizacion) {
      if (loader) {
        loader.classList.add("d-none");
      }

      return;
    }

    // ======================================================
    // NUEVO CONTROLADOR
    // ======================================================

    cotizacionController = new AbortController();

    const controllerActual = cotizacionController;

    // ======================================================
    // LOADER
    // ======================================================

    if (loader) {
      loader.classList.remove("d-none");
    }

    const formData = new FormData();

    formData.set("id_cotizacion", idCotizacion);

    // ======================================================
    // CONSULTAR COTIZACIÓN
    // ======================================================

    fetch(instance._URL_ + "flete/get_cotizacion_datos", {
      method: "POST",
      body: formData,
      signal: controllerActual.signal,
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP: " + response.status);
        }

        return response.json();
      })

      .then(function (data) {
        /*
         * Por seguridad:
         *
         * si mientras esperábamos la respuesta se inició
         * la carga de un flete, NO debemos modificar
         * el formulario.
         */

        if (cargandoFlete) {
          return;
        }

        /*
         * Si este fetch ya no es el actual tampoco
         * debemos utilizar su respuesta.
         */

        if (cotizacionController !== controllerActual) {
          return;
        }

        if (!data.success) {
          instance.Toast.operacion_erronea(
            data.message ||
              "No se pudieron obtener los datos de la cotización.",
          );

          return;
        }

        const info = data.message;

        // ==================================================
        // UBIGEO ORIGEN
        // ==================================================

        if (ubigeoOrigen) {
          setUbigeoValue(ubigeoOrigen, info.ubigeo_origen ?? "");
        }

        // ==================================================
        // UBIGEO DESTINO
        // ==================================================

        if (ubigeoDestino) {
          setUbigeoValue(ubigeoDestino, info.ubigeo_destino ?? "");
        }

        // ==================================================
        // DIRECCIÓN ORIGEN
        // ==================================================

        if (direccion_origen) {
          direccion_origen.value = info.direccion_origen ?? "";
        }

        // ==================================================
        // DIRECCIÓN DESTINO
        // ==================================================

        if (direccion_destino) {
          direccion_destino.value = info.direccion_destino ?? "";
        }

        // ==================================================
        // CLIENTE
        // ==================================================

        if (cliente) {
          const documento = info.cliente_num_docu ?? "";

          const nombres = info.cliente_nombres ?? "";

          cliente.value = [documento, nombres].filter(Boolean).join(" - ");
        }

        if (cliente_id) {
          cliente_id.value = info.id_cliente ?? "";
        }

        // ==================================================
        // CONDICIÓN DE PAGO
        // ==================================================

        const condicion = info.condicion_pago ?? "CONTADO";

        if (condicion_pago) {
          condicion_pago.value = condicion;
        }

        // ==================================================
        // DÍAS CRÉDITO
        // ==================================================

        if (dias_credito) {
          dias_credito.value = info.dias_credito ?? 0;
        }

        // ==================================================
        // FECHA VENCIMIENTO
        // ==================================================

        if (fecha_vencimiento) {
          fecha_vencimiento.value = info.fecha_vencimiento ?? "";
        }

        actualizarCondicionPago();

        // ==================================================
        // IGV
        // ==================================================

        const incluyeIgv = Number(info.incluye_igv ?? 0);

        const radioIgv = document.querySelector(
          `input[name="incluye_igv"][value="${incluyeIgv}"]`,
        );

        if (radioIgv) {
          radioIgv.checked = true;
        }

        // ==================================================
        // MONEDA
        // ==================================================

        if (moneda && info.id_tp_moneda) {
          setSelectValue(moneda, info.id_tp_moneda);
        }

        // ==================================================
        // IMPORTE
        // ==================================================

        if (precio) {
          if (incluyeIgv === 1) {
            precio.value = Number(info.total ?? 0).toFixed(2);
          } else {
            precio.value = Number(info.subtotal ?? 0).toFixed(2);
          }
        }

        // ==================================================
        // CALCULAR IMPORTES
        // ==================================================

        calcularImportes();
      })

      .catch(function (error) {
        /*
         * Si nosotros cancelamos el fetch porque el usuario
         * seleccionó otra cotización o abrió Editar/Duplicar,
         * NO mostramos error.
         */

        if (error.name === "AbortError") {
          return;
        }

        console.error("Error al cargar cotización:", error);

        instance.Toast.operacion_erronea(
          error.message || "No se pudieron cargar los datos de la cotización.",
        );
      })

      .finally(function () {
        /*
         * Solo el controlador actual puede ocultar
         * el loader y limpiarse.
         *
         * Así una petición antigua cancelada no afecta
         * una petición nueva.
         */

        if (cotizacionController === controllerActual) {
          cotizacionController = null;

          if (loader) {
            loader.classList.add("d-none");
          }
        }
      });
  });

  async function cambiarEstadoFlete(idFlete, estado, mensajeConfirmacion) {
    const confirmacion = await Swal.fire({
      title: "¿Confirmar acción?",
      text: mensajeConfirmacion,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar",
      cancelButtonText: "Cancelar",
      focusConfirm: false,
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    try {
      const form = new FormData();

      form.append("id_flete", idFlete);
      form.append("estado", estado);

      const response = await fetch($("#url").val() + "flete/cambiar_estado", {
        method: "POST",
        body: form,
      });

      const result = await response.json();

      if (!result.success) {
        await Swal.fire({
          icon: "warning",
          title: "No se pudo realizar la acción",
          text: result.message || "Ocurrió un error.",
        });

        return;
      }

      await Swal.fire({
        icon: "success",
        title: "Flete actualizado",
        text: result.message,
        timer: 1600,
        showConfirmButton: false,
      });

      table.ajax.reload(null, false);
    } catch (error) {
      console.error("Error al cambiar estado del flete:", error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ocurrió un error al actualizar el flete.",
      });
    }
  }

  $("#table_flete tbody").on("click", ".btnProgramarFlete", function () {
    const row = obtenerFilaDataTable(this);

    cambiarEstadoFlete(
      row.id_flete,
      "PROGRAMADO",
      "El flete quedará programado y listo para iniciar.",
    );
  });

  $("#table_flete tbody").on("click", ".btnIniciarFlete", function () {
    const row = obtenerFilaDataTable(this);

    cambiarEstadoFlete(
      row.id_flete,
      "EN_CURSO",
      "Se marcará que el servicio ya inició.",
    );
  });

  $("#table_flete tbody").on("click", ".btnFinalizarFlete", function () {
    const row = obtenerFilaDataTable(this);

    cambiarEstadoFlete(
      row.id_flete,
      "FINALIZADO",
      "El servicio quedará finalizado.",
    );
  });

  $("#table_flete tbody").on("click", ".btnCancelarFlete", function () {
    const row = obtenerFilaDataTable(this);

    cambiarEstadoFlete(
      row.id_flete,
      "CANCELADO",
      "El flete será cancelado. Esta acción no debería utilizarse si el servicio ya fue ejecutado.",
    );
  });

  $("#table_flete tbody").on("click", ".btnFacturarFlete", async function () {
    const data = obtenerFilaDataTable(this);

    if (!data) {
      return;
    }

    await validarFleteParaFacturar(data.id_flete);
  });

  $("#table_flete tbody").on("click", ".btnVerTercerizado", async function () {
    const row = obtenerFilaDataTable(this);

    if (!row) {
      return;
    }

    await abrirControlTercerizado(row);
  });

  async function abrirControlTercerizado(row) {
    try {
      const form = new FormData();

      form.append("id_flete", row.id_flete);

      const response = await fetch(
        instance._URL_ + "flete/get_pago_tercerizado",
        {
          method: "POST",
          body: form,
        },
      );

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      const result = await response.json();

      if (!result.success) {
        Swal.fire({
          icon: "warning",
          title: "No se pudo cargar",
          text: result.message || "No se pudo obtener la tercerización.",
        });

        return;
      }

      const data = result.message;

      // =====================================================
      // ID
      // =====================================================

      $("#id_flete_tercerizado_pago").val(data.id_flete_tercerizado);

      // =====================================================
      // GENERAL
      // =====================================================

      $("#tercerizado_numero_flete").text(row.numero_flete || row.id_flete);

      $("#tercerizado_proveedor").text(data.proveedor_nombres || "-");

      $("#tercerizado_documento").text(
        data.proveedor_num_docu ? `Documento: ${data.proveedor_num_docu}` : "",
      );

      // =====================================================
      // IMPORTES
      // =====================================================

      $("#tercerizado_costo").text(formatoMoneda(data.costo));

      $("#tercerizado_pagado").text(formatoMoneda(data.total_pagado));

      $("#tercerizado_saldo").text(formatoMoneda(data.saldo));

      // =====================================================
      // ESTADOS
      // =====================================================

      pintarEstadoTercerizado(data.estado);

      pintarEstadoPagoTercerizado(data.estado_pago);

      // =====================================================
      // FORMULARIO
      // =====================================================

      $("#card_nuevo_pago_tercerizado").addClass("d-none");

      $("#form_pago_tercerizado")[0].reset();

      // Evitar pagar más que el saldo
      $("#monto_pago_tercerizado").attr(
        "max",
        parseFloat(data.saldo || 0).toFixed(2),
      );

      // Si ya está pagado no mostramos registrar pago
      if (data.estado_pago === "PAGADO" || parseFloat(data.saldo || 0) <= 0) {
        $("#btn_nuevo_pago_tercerizado").addClass("d-none");
      } else {
        $("#btn_nuevo_pago_tercerizado").removeClass("d-none");
      }

      // =====================================================
      // HISTORIAL
      // =====================================================

      await cargarHistorialPagosTercerizado(data.id_flete_tercerizado);

      // =====================================================
      // MOSTRAR MODAL
      // =====================================================

      const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("modal_control_tercerizado"),
      );

      $("#modal_control_tercerizado").data("flete-row", row);

      modal.show();
    } catch (error) {
      console.error("Error al cargar tercerización:", error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ocurrió un error al cargar el control de tercerización.",
      });
    }
  }

  function pintarEstadoTercerizado(estado) {
    const badge = $("#tercerizado_estado");

    badge.removeClass().addClass("badge rounded-pill");

    switch (estado) {
      case "PENDIENTE":
        badge.addClass("bg-warning text-dark").text("PENDIENTE");

        break;

      case "ASIGNADO":
        badge.addClass("bg-primary").text("ASIGNADO");

        break;

      case "FINALIZADO":
        badge.addClass("bg-success").text("FINALIZADO");

        break;

      case "CANCELADO":
        badge.addClass("bg-danger").text("CANCELADO");

        break;

      default:
        badge.addClass("bg-secondary").text(estado || "-");
    }
  }

  function pintarEstadoPagoTercerizado(estado) {
    const badge = $("#tercerizado_estado_pago");

    badge.removeClass().addClass("badge rounded-pill");

    switch (estado) {
      case "PENDIENTE":
        badge.addClass("bg-warning text-dark").text("PENDIENTE");

        break;

      case "PARCIAL":
        badge.addClass("bg-info text-dark").text("PARCIAL");

        break;

      case "PAGADO":
        badge.addClass("bg-success").text("PAGADO");

        break;

      default:
        badge.addClass("bg-secondary").text(estado || "-");
    }
  }

  async function cargarHistorialPagosTercerizado(idFleteTercerizado) {
    const tbody = document.getElementById("tbody_pagos_tercerizado");

    tbody.innerHTML = `
    <tr>
      <td
        colspan="6"
        class="text-center py-4"
      >
        <div
          class="spinner-border spinner-border-sm me-2"
        ></div>

        Cargando pagos...
      </td>
    </tr>
  `;

    try {
      const form = new FormData();

      form.append("id_flete_tercerizado", idFleteTercerizado);

      const response = await fetch(
        instance._URL_ + "flete/get_historial_pago_tercerizado",
        {
          method: "POST",
          body: form,
        },
      );

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      const result = await response.json();

      tbody.innerHTML = "";

      if (!result.success || !result.message || result.message.length === 0) {
        tbody.innerHTML = `
        <tr>
          <td
            colspan="6"
            class="text-center text-muted py-4"
          >
            Sin pagos registrados.
          </td>
        </tr>
      `;

        return;
      }

      result.message.forEach((pago) => {
        const pagoJson = encodeURIComponent(JSON.stringify(pago));

        tbody.insertAdjacentHTML(
          "beforeend",
          `
      <tr>

        <td>
          ${pago.fecha_pago || "-"}
        </td>

        <td>
          ${escapeHtml(pago.medio_pago || "-")}
        </td>

        <td>
          ${escapeHtml(pago.numero_operacion || "-")}
        </td>

        <td>
          ${escapeHtml(pago.observacion || "-")}
        </td>

        <td class="text-end fw-semibold">
          ${formatoMoneda(pago.monto)}
        </td>

        <td class="text-center text-nowrap">

          <button
            type="button"
            class="btn btn-sm btn-outline-primary btnEditarPagoTercerizado"
            data-pago="${pagoJson}"
            title="Editar pago"
          >
            <i class="bi bi-pencil"></i>
          </button>

          <button
            type="button"
            class="btn btn-sm btn-outline-danger btnEliminarPagoTercerizado"
            data-id-pago="${pago.id_pago}"
            data-monto="${pago.monto}"
            title="Eliminar pago"
          >
            <i class="bi bi-trash"></i>
          </button>

        </td>

      </tr>
    `,
        );
      });
    } catch (error) {
      console.error("Error historial:", error);

      tbody.innerHTML = `
      <tr>
        <td
          colspan="6"
          class="text-center text-danger py-4"
        >
          Error al cargar los pagos.
        </td>
      </tr>
    `;
    }
  }

  $("#btn_nuevo_pago_tercerizado").on("click", function () {
    $("#id_pago_tercerizado").val("");

    $("#form_pago_tercerizado")[0].reset();

    $("#form_pago_tercerizado").removeClass("was-validated");

    $("#card_nuevo_pago_tercerizado").removeClass("d-none");

    $("#btn_guardar_pago_tercerizado").html(`
      <i class="bi bi-check-circle me-1"></i>
      Registrar pago
    `);

    $("#monto_pago_tercerizado").trigger("focus");
  });

  $("#btn_cancelar_pago_tercerizado").on("click", function () {
    $("#id_pago_tercerizado").val("");

    $("#card_nuevo_pago_tercerizado").addClass("d-none");

    $("#form_pago_tercerizado")[0].reset();

    $("#form_pago_tercerizado").removeClass("was-validated");

    $("#btn_guardar_pago_tercerizado").html(`
      <i class="bi bi-check-circle me-1"></i>
      Registrar pago
    `);
  });

  $(document).on("click", ".btnEditarPagoTercerizado", function () {
    try {
      const pago = JSON.parse(decodeURIComponent($(this).attr("data-pago")));

      // ==========================================
      // ID DEL PAGO
      // ==========================================

      $("#id_pago_tercerizado").val(pago.id_pago);

      // ==========================================
      // CARGAR DATOS
      // ==========================================

      $("#monto_pago_tercerizado").val(parseFloat(pago.monto || 0).toFixed(2));

      $("[name='fecha_pago']").val(pago.fecha_pago || "");

      $("[name='id_medio_pago']")
        .val(pago.id_medio_pago || "")
        .trigger("change");

      $("[name='numero_operacion']").val(pago.numero_operacion || "");

      $("[name='observacion']").val(pago.observacion || "");

      // ==========================================
      // IMPORTANTE:
      // EN EDICIÓN PUEDE USAR SU MONTO ACTUAL
      // + EL SALDO DISPONIBLE
      // ==========================================

      const saldoActual =
        parseFloat(
          $("#tercerizado_saldo")
            .text()
            .replace(/[^\d.-]/g, ""),
        ) || 0;

      const montoActual = parseFloat(pago.monto || 0);

      $("#monto_pago_tercerizado").attr(
        "max",
        (saldoActual + montoActual).toFixed(2),
      );

      // ==========================================
      // MOSTRAR FORMULARIO
      // ==========================================

      $("#card_nuevo_pago_tercerizado").removeClass("d-none");

      $("#btn_guardar_pago_tercerizado").html(`
          <i class="bi bi-pencil-square me-1"></i>
          Actualizar pago
        `);

      $("#monto_pago_tercerizado").trigger("focus");
    } catch (error) {
      console.error("Error cargando pago:", error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo cargar el pago.",
      });
    }
  });
  $(document).on("click", ".btnEliminarPagoTercerizado", async function () {
    const idPago = parseInt($(this).data("id-pago") || 0);

    const monto = parseFloat($(this).data("monto") || 0);

    if (!idPago) {
      return;
    }

    const confirmacion = await Swal.fire({
      title: "¿Eliminar pago?",

      html: `
          Se eliminará el pago de
          <strong>${formatoMoneda(monto)}</strong>.
          <br><br>
          El saldo del proveedor será recalculado.
        `,

      icon: "warning",

      showCancelButton: true,

      confirmButtonText: "Sí, eliminar",

      cancelButtonText: "Cancelar",

      confirmButtonColor: "#dc3545",
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    try {
      const form = new FormData();

      form.append("id_pago", idPago);

      const response = await fetch(
        instance._URL_ + "flete/eliminar_pago_tercerizado",
        {
          method: "POST",
          body: form,
        },
      );

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message || "No se pudo eliminar el pago.");
      }

      await Swal.fire({
        icon: "success",
        title: "Pago eliminado",
        text: result.message,
        timer: 1300,
        showConfirmButton: false,
      });

      // ==========================================
      // RECARGAR MODAL
      // ==========================================

      const row = $("#modal_control_tercerizado").data("flete-row");

      if (row) {
        await abrirControlTercerizado(row);
      }

      // ==========================================
      // DATATABLE
      // ==========================================

      table.ajax.reload(null, false);
    } catch (error) {
      console.error("Error eliminando pago:", error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message || "No se pudo eliminar el pago.",
      });
    }
  });

  $("#form_pago_tercerizado").on("submit", async function (e) {
    e.preventDefault();

    const formElement = this;

    // =====================================================
    // VALIDACIÓN HTML5
    // =====================================================

    if (!formElement.checkValidity()) {
      formElement.classList.add("was-validated");
      return;
    }

    // =====================================================
    // DATOS PRINCIPALES
    // =====================================================

    const idFleteTercerizado = parseInt(
      $("#id_flete_tercerizado_pago").val() || 0,
    );

    const idPago = parseInt($("#id_pago_tercerizado").val() || 0);

    const esEdicion = idPago > 0;

    const monto = parseFloat($("#monto_pago_tercerizado").val() || 0);

    const maximo = parseFloat($("#monto_pago_tercerizado").attr("max") || 0);

    // =====================================================
    // VALIDAR TERCERIZACIÓN
    // =====================================================

    if (idFleteTercerizado <= 0) {
      await Swal.fire({
        icon: "warning",
        title: "Registro inválido",
        text: "No se encontró la tercerización.",
      });

      return;
    }

    // =====================================================
    // VALIDAR MONTO
    // =====================================================

    if (!Number.isFinite(monto) || monto <= 0) {
      await Swal.fire({
        icon: "warning",
        title: "Monto inválido",
        text: "Ingrese un monto mayor a cero.",
      });

      return;
    }

    // =====================================================
    // VALIDAR MÁXIMO
    //
    // REGISTRO:
    //   max = saldo pendiente
    //
    // EDICIÓN:
    //   max = saldo pendiente + monto actual del pago
    //
    // Este max debe haber sido actualizado cuando se
    // presionó el botón Editar.
    // =====================================================

    if (Number.isFinite(maximo) && maximo > 0 && monto > maximo) {
      await Swal.fire({
        icon: "warning",
        title: "Monto inválido",
        text: esEdicion
          ? `El nuevo monto no puede superar el importe disponible de ${formatoMoneda(maximo)}.`
          : `El pago no puede superar el saldo pendiente de ${formatoMoneda(maximo)}.`,
      });

      return;
    }

    // =====================================================
    // CONFIRMACIÓN
    // =====================================================

    const confirmacion = await Swal.fire({
      title: esEdicion ? "¿Actualizar pago?" : "¿Registrar pago?",

      text: esEdicion
        ? `El pago será actualizado a ${formatoMoneda(monto)}.`
        : `Se registrará un pago de ${formatoMoneda(monto)}.`,

      icon: "question",

      showCancelButton: true,

      confirmButtonText: esEdicion ? "Sí, actualizar" : "Sí, registrar",

      cancelButtonText: "Cancelar",

      focusCancel: true,
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    // =====================================================
    // ENDPOINT
    // =====================================================

    const endpoint = esEdicion
      ? "flete/editar_pago_tercerizado"
      : "flete/registrar_pago_tercerizado";

    try {
      // =====================================================
      // LOADER BOTÓN
      // =====================================================

      $("#btn_guardar_pago_tercerizado").addClass("d-none");

      $("#btn_guardando_pago_tercerizado").removeClass("d-none");

      // =====================================================
      // FORM DATA
      // =====================================================

      const form = new FormData(formElement);

      // Evitamos duplicados si estos campos ya existen
      // dentro del formulario.

      form.set("id_flete_tercerizado", idFleteTercerizado);

      if (esEdicion) {
        form.set("id_pago", idPago);
      } else {
        // Si por algún motivo quedó un ID anterior,
        // lo eliminamos al registrar un pago nuevo.
        form.delete("id_pago");
      }

      // =====================================================
      // PETICIÓN
      // =====================================================

      const response = await fetch(instance._URL_ + endpoint, {
        method: "POST",
        body: form,
      });

      // =====================================================
      // LEER RESPUESTA
      // =====================================================

      const responseText = await response.text();

      let result;

      try {
        result = JSON.parse(responseText);
      } catch (error) {
        console.error("Respuesta inválida del servidor:", responseText);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      // =====================================================
      // ERROR HTTP
      // =====================================================

      if (!response.ok) {
        throw new Error(result?.message || `Error HTTP ${response.status}`);
      }

      // =====================================================
      // ERROR DEL BACKEND
      // =====================================================

      if (!result.success) {
        await Swal.fire({
          icon: "warning",

          title: esEdicion ? "No se pudo actualizar" : "No se pudo registrar",

          text: result.message || "Ocurrió un error al procesar el pago.",
        });

        return;
      }

      // =====================================================
      // ÉXITO
      // =====================================================

      await Swal.fire({
        icon: "success",

        title: esEdicion ? "Pago actualizado" : "Pago registrado",

        text:
          result.message ||
          (esEdicion
            ? "El pago fue actualizado correctamente."
            : "El pago fue registrado correctamente."),

        timer: 1400,

        showConfirmButton: false,
      });

      // =====================================================
      // LIMPIAR MODO EDICIÓN
      // =====================================================

      $("#id_pago_tercerizado").val("");

      // =====================================================
      // OCULTAR FORMULARIO
      // =====================================================

      $("#card_nuevo_pago_tercerizado").addClass("d-none");

      // =====================================================
      // RESET
      // =====================================================

      formElement.reset();

      formElement.classList.remove("was-validated");

      // Restauramos texto del botón por si estaba
      // en modo edición.

      $("#btn_guardar_pago_tercerizado").html(`
        <i class="bi bi-check-circle me-1"></i>
        Registrar pago
      `);

      // =====================================================
      // RECARGAR INFORMACIÓN DEL TERCERIZADO
      //
      // Esto actualizará:
      // - costo
      // - total pagado
      // - saldo
      // - estado de pago
      // - historial
      // - máximo permitido
      // =====================================================

      const row = $("#modal_control_tercerizado").data("flete-row");

      if (row) {
        await abrirControlTercerizado(row);
      }

      // =====================================================
      // RECARGAR DATATABLE PRINCIPAL
      // =====================================================

      table.ajax.reload(null, false);
    } catch (error) {
      console.error(
        esEdicion
          ? "Error editando pago tercerizado:"
          : "Error registrando pago tercerizado:",
        error,
      );

      await Swal.fire({
        icon: "error",

        title: "Error",

        text:
          error.message ||
          (esEdicion
            ? "Ocurrió un error al actualizar el pago."
            : "Ocurrió un error al registrar el pago."),
      });
    } finally {
      // =====================================================
      // RESTAURAR BOTONES
      // =====================================================

      $("#btn_guardar_pago_tercerizado").removeClass("d-none");

      $("#btn_guardando_pago_tercerizado").addClass("d-none");
    }
  });

  function obtenerFilaDataTable(elemento) {
    let $tr = $(elemento).closest("tr");

    if ($tr.hasClass("child")) {
      $tr = $tr.prev();
    }

    return table.row($tr).data();
  }

  async function validarFleteParaFacturar(idFlete) {
    try {
      const form = new FormData();

      form.append("id_flete", idFlete);

      const response = await fetch(
        `${instance._URL_}flete/validar_facturacion`,
        {
          method: "POST",
          body: form,
        },
      );

      const result = await response.json();

      if (!result.success) {
        Swal.fire({
          icon: "warning",
          title: "No se puede facturar",
          text: result.message,
        });

        return;
      }

      // =====================================================
      // AQUÍ ABRIREMOS TU MODAL DE FACTURACIÓN
      // =====================================================

      abrirFacturacionFlete(result.data);
    } catch (error) {
      console.error("Error al validar flete:", error);

      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo validar el flete.",
      });
    }
  }
  async function abrirFacturacionFlete(data) {
    // =========================================================
    // CONTEXTO
    // =========================================================

    $("#id_cotizacion_venta").val("");

    $("#id_flete_venta").val(data.id_flete);

    $("#origen_venta").val("FLETE");

    // =========================================================
    // COMPROBANTE
    // =========================================================

    $("#tp_comprobante").val("3");

    await cargarSerieComprobante();

    // =========================================================
    // TÍTULO
    // =========================================================

    $("#modal_facturar .modal-title").text(
      `Facturar Flete ${data.numero_flete}`,
    );

    // =========================================================
    // FECHA
    // =========================================================

    const hoy = new Date();

    const fechaHoy =
      hoy.getFullYear() +
      "-" +
      String(hoy.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(hoy.getDate()).padStart(2, "0");

    $("#fecha_emision_venta").val(fechaHoy);

    // =========================================================
    // CONDICIÓN PAGO
    // =========================================================

    if (data.condicion_pago === "CREDITO") {
      $("#forma_pago").val("2").trigger("change");

      $("#div_cuota").removeClass("d-none");

      if (data.fecha_vencimiento) {
        $("#fecha_credito").val(data.fecha_vencimiento);
      }

      $("#monto_credito").val(parseFloat(data.total || 0).toFixed(2));

      const dias = parseInt(data.dias_credito || 0);

      switch (dias) {
        case 15:
          $("#tiempo_credito").val("3");

          break;

        case 30:
          $("#tiempo_credito").val("1");

          break;

        case 45:
          $("#tiempo_credito").val("4");

          break;

        case 60:
          $("#tiempo_credito").val("5");

          break;
      }

      $("#div_parentMedioPago").addClass("d-none");

      $("#medio_pago").prop("required", false).val("");
    } else {
      $("#forma_pago").val("1").trigger("change");

      $("#div_cuota").addClass("d-none");

      $("#fecha_credito").val("");

      $("#monto_credito").val("");

      $("#div_parentMedioPago").removeClass("d-none");

      $("#medio_pago").prop("required", true);
    }

    // =========================================================
    // RESUMEN
    // =========================================================

    $("#resumen_flete_facturacion").removeClass("d-none");

    $("#factura_numero_flete").text(`Flete ${data.numero_flete}`);

    $("#factura_cliente_flete").text(
      `${data.cliente_nombres || ""}${
        data.cliente_num_docu ? " - " + data.cliente_num_docu : ""
      }`,
    );

    $("#factura_ruta_flete").text(
      `${data.origen || ""} → ${data.destino || ""}`,
    );

    $("#factura_total_flete").text(
      `${data.simbolo_moneda} ${parseFloat(data.total || 0).toFixed(2)}`,
    );

    // =========================================================
    // GUARDAR DATA
    // =========================================================

    $("#modal_facturar").data("flete", data);

    // =========================================================
    // MOSTRAR MODAL
    // =========================================================

    const modal = bootstrap.Modal.getOrCreateInstance(
      document.getElementById("modal_facturar"),
    );

    modal.show();
  }

  $("#forma_pago").on("change", function () {
    const formaPago = $(this).val();

    // =====================================================
    // CRÉDITO
    // =====================================================

    if (formaPago === "2") {
      $("#div_cuota").removeClass("d-none");

      $("#div_parentMedioPago").addClass("d-none");

      $("#medio_pago").prop("required", false).val("");

      $("#fecha_credito").prop("required", true);

      $("#monto_credito").prop("required", true);

      return;
    }

    // =====================================================
    // CONTADO
    // =====================================================

    $("#div_cuota").addClass("d-none");
    $("#div_parentMedioPago").removeClass("d-none");
    $("#medio_pago").prop("required", true);
    $("#fecha_credito").prop("required", false).val("");
    $("#monto_credito").prop("required", false).val("");
  });

  async function cargarSerieComprobante() {
    const tpComprobante = document.getElementById("tp_comprobante");

    const serieVenta = document.getElementById("serie_venta");

    if (!tpComprobante || !serieVenta) {
      return;
    }

    try {
      // Limpiar serie actual
      serieVenta.innerHTML = `
      <option value="">
        Cargando...
      </option>
    `;

      serieVenta.disabled = true;

      const formData = new FormData();

      formData.set("tp_comprobante", tpComprobante.value);

      const response = await fetch(
        instance._URL_ + "encomienda/get_serieForTpComprobante",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      const data = await response.json();

      serieVenta.innerHTML = "";

      if (data && data.success && data.message) {
        serieVenta.insertAdjacentHTML(
          "beforeend",
          `
          <option
            value="${data.message.id_serie}">
            ${data.message.serie}
          </option>
        `,
        );
      } else {
        serieVenta.insertAdjacentHTML(
          "beforeend",
          `
          <option value="">
            Sin series disponibles
          </option>
        `,
        );
      }
    } catch (error) {
      console.error("Error al cargar serie:", error);

      serieVenta.innerHTML = `
      <option value="">
        Error al cargar serie
      </option>
    `;
    } finally {
      serieVenta.disabled = false;
    }
  }

  $("#tp_comprobante").on("change", async function () {
    await cargarSerieComprobante();
  });

  function calcularFechaVencimientoVenta() {
    const fechaEmision = document.getElementById("fecha_emision_venta");

    const tiempoCredito = document.getElementById("tiempo_credito");

    const fechaCredito = document.getElementById("fecha_credito");

    if (!fechaEmision || !tiempoCredito || !fechaCredito) {
      return;
    }

    if (!fechaEmision.value || !tiempoCredito.value) {
      fechaCredito.value = "";

      return;
    }

    // =========================================================
    // OBTENER FECHA
    // =========================================================

    const [anio, mes, dia] = fechaEmision.value.split("-").map(Number);

    const fecha = new Date(anio, mes - 1, dia);

    // =========================================================
    // SUMAR DÍAS DE CRÉDITO
    // =========================================================

    const diasCredito = parseInt(tiempoCredito.value, 10);

    fecha.setDate(fecha.getDate() + diasCredito);

    // =========================================================
    // FORMATEAR YYYY-MM-DD
    // =========================================================

    const fechaVencimiento =
      fecha.getFullYear() +
      "-" +
      String(fecha.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(fecha.getDate()).padStart(2, "0");

    fechaCredito.value = fechaVencimiento;
  }

  $("#tiempo_credito").on("change", function () {
    calcularFechaVencimientoVenta();
  });

  $("#fecha_emision_venta").on("change", function () {
    if ($("#forma_pago").val() === "2") {
      calcularFechaVencimientoVenta();
    }
  });
  $("#forma_pago").on("change", function () {
    const formaPago = $(this).val();

    // =====================================================
    // CRÉDITO
    // =====================================================

    if (formaPago === "2") {
      $("#div_cuota").removeClass("d-none");

      $("#div_parentMedioPago").addClass("d-none");

      $("#medio_pago").prop("required", false).val("");

      $("#fecha_credito").prop("required", true);

      $("#monto_credito").prop("required", true);

      // Calcular automáticamente
      calcularFechaVencimientoVenta();

      return;
    }

    // =====================================================
    // CONTADO
    // =====================================================
    $("#div_cuota").addClass("d-none");
    $("#div_parentMedioPago").removeClass("d-none");
    $("#medio_pago").prop("required", true);
    $("#fecha_credito").prop("required", false).val("");
    $("#monto_credito").prop("required", false).val("");
  });

  $("#form_venta").on("submit", async function (e) {
    e.preventDefault();

    const form = this;

    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }

    const idFlete = $("#id_flete_venta").val();
    const origenVenta = $("#origen_venta").val();

    const tpComprobante = $("#tp_comprobante").val();
    const serieVenta = $("#serie_venta").val();
    const fechaEmision = $("#fecha_emision_venta").val();

    const formaPago = $("#forma_pago").val();
    const medioPago = $("#medio_pago").val();

    const fechaCredito = $("#fecha_credito").val();
    const montoCredito = parseFloat($("#monto_credito").val() || 0);

    // =========================================================
    // VALIDAR FLETE
    // =========================================================

    if (origenVenta === "FLETE" && !idFlete) {
      Swal.fire({
        icon: "warning",
        title: "Flete no válido",
        text: "No se encontró el flete que se desea facturar.",
      });

      return;
    }

    // =========================================================
    // COMPROBANTE
    // =========================================================

    if (!tpComprobante) {
      Swal.fire({
        icon: "warning",
        title: "Tipo de comprobante",
        text: "Seleccione el tipo de comprobante.",
      });

      return;
    }

    if (!serieVenta) {
      Swal.fire({
        icon: "warning",
        title: "Serie",
        text: "Seleccione una serie válida.",
      });

      return;
    }

    if (!fechaEmision) {
      Swal.fire({
        icon: "warning",
        title: "Fecha de emisión",
        text: "Ingrese la fecha de emisión.",
      });

      return;
    }

    // =========================================================
    // FORMA DE PAGO
    // =========================================================

    if (formaPago === "1" && !medioPago) {
      Swal.fire({
        icon: "warning",
        title: "Medio de pago",
        text: "Seleccione el medio de pago.",
      });

      return;
    }

    if (formaPago === "2") {
      if (!fechaCredito) {
        Swal.fire({
          icon: "warning",
          title: "Fecha de vencimiento",
          text: "Ingrese la fecha de vencimiento.",
        });

        return;
      }

      if (montoCredito <= 0) {
        Swal.fire({
          icon: "warning",
          title: "Monto de crédito",
          text: "Ingrese un monto de crédito válido.",
        });

        return;
      }
    }

    // =========================================================
    // CONFIRMACIÓN
    // =========================================================

    const tipoTexto = tpComprobante === "1" ? "FACTURA" : "BOLETA";

    const confirmar = await Swal.fire({
      title: "¿Generar comprobante?",

      html: `
            Se generará una <b>${tipoTexto}</b>
            para el flete
            <b>${$("#factura_numero_flete").text()}</b>.
            <br><br>
            Esta operación enviará el comprobante
            al servicio de facturación electrónica.
        `,

      icon: "question",

      showCancelButton: true,

      confirmButtonText: "Sí, facturar",

      cancelButtonText: "Cancelar",

      focusCancel: true,
    });

    if (!confirmar.isConfirmed) {
      return;
    }

    // =========================================================
    // FORM DATA
    // =========================================================

    const data = new FormData(form);

    data.set("id_flete", idFlete);
    data.set("origen_venta", origenVenta);
    data.set("tp_comprobante", tpComprobante);
    data.set("serie_venta", serieVenta);
    data.set("fecha_emision", fechaEmision);
    data.set("forma_pago", formaPago);

    // =========================================================
    // CONTADO / CRÉDITO
    // =========================================================

    if (formaPago === "1") {
      data.set("medio_pago", medioPago);
      data.set("fecha_credito", "");
      data.set("monto_credito", "0");
      data.set("tiempo_credito", "0");
    } else {
      data.set("medio_pago", "");
      data.set("fecha_credito", fechaCredito);
      data.set("monto_credito", montoCredito);
    }

    // =========================================================
    // LOADER
    // =========================================================

    $("#button_saveP").addClass("d-none");
    $("#button_loadSaveP").removeClass("d-none");

    try {
      const response = await fetch(instance._URL_ + "flete/facturar_flete", {
        method: "POST",
        body: data,
      });

      const text = await response.text();

      let result;

      try {
        result = JSON.parse(text);
      } catch (e) {
        console.error("Respuesta backend no JSON:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      console.log("RESPUESTA FACTURACIÓN:", result);

      if (!response.ok) {
        throw new Error(result.message || `Error HTTP ${response.status}`);
      }

      // =====================================================
      // ERROR BACKEND
      // =====================================================

      if (!result.success) {
        await Swal.fire({
          icon: "warning",
          title: "No se pudo facturar",
          text: result.message || "No se pudo generar el comprobante.",
        });

        return;
      }

      // =====================================================
      // CERRAR MODAL DE FACTURACIÓN
      // =====================================================

      const modalElement = document.getElementById("modal_facturar");

      const modal = bootstrap.Modal.getInstance(modalElement);

      if (modal) {
        modal.hide();
      }

      // =====================================================
      // VALIDAR ID VENTA
      // =====================================================

      const idVenta = parseInt(result.id_venta || result.id_comprobante || 0);

      if (!idVenta) {
        throw new Error(
          "El comprobante fue generado, pero no se obtuvo el ID de la venta.",
        );
      }

      // =====================================================
      // LIMPIAR FORMULARIO
      // =====================================================

      form.reset();

      form.classList.remove("was-validated");

      $("#id_flete_venta").val("");

      $("#origen_venta").val("");

      $("#resumen_flete_facturacion").addClass("d-none");

      // =====================================================
      // RECARGAR DATATABLE
      // =====================================================

      if (typeof table !== "undefined" && table) {
        table.ajax.reload(null, false);
      }

      // =====================================================
      // ABRIR VISTA PREVIA
      // =====================================================

      await cargarVistaPreviaComprobante(idVenta);
    } catch (error) {
      console.error("Error al facturar flete:", error);

      Swal.fire({
        icon: "error",
        title: "Error de facturación",
        text: error.message || "Ocurrió un error al generar el comprobante.",
      });
    } finally {
      $("#button_saveP").removeClass("d-none");

      $("#button_loadSaveP").addClass("d-none");
    }
  });
  // ============================================================
  // ESTADO INICIAL
  // ============================================================

  actualizarTipoOperacion();

  actualizarCondicionPago();

  calcularImportes();

  $(document).on("click", ".btnRevisarComprobante", async function () {
    const idVenta = $(this).data("id-venta");

    if (!idVenta) {
      Swal.fire({
        icon: "warning",
        title: "Comprobante",
        text: "No se encontró el comprobante asociado.",
      });

      return;
    }

    await cargarVistaPreviaComprobante(idVenta);
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
                        Producto: ${escapeHtml(item.producto || "")}
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
                <td colspan="6" class="text-center text-muted py-4">
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

  $("#btnGuardarPreview").on("click", async function () {
    await guardarCambiosPreview();
  });

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

  $(document).on("click", "#btnEnviarSunatPreview", async function () {
    // ============================================
    // 1. GUARDAR CAMBIOS
    // ============================================

    const guardado = await guardarCambiosPreview(false);

    if (!guardado) {
      return;
    }

    // ============================================
    // 2. CONFIRMAR
    // ============================================

    const confirmacion = await Swal.fire({
      icon: "question",

      title: "¿Enviar comprobante a SUNAT?",

      html: `
                    <div class="text-start">
                        El comprobante será enviado con
                        la información mostrada actualmente.
                        <br><br>

                        <strong>
                            Después del envío ya no podrás
                            modificar la descripción.
                        </strong>
                    </div>
                `,

      showCancelButton: true,

      confirmButtonText:
        '<i class="fa-light fa-paper-plane me-1"></i> Sí, enviar',

      cancelButtonText: "Cancelar",

      confirmButtonColor: "#198754",

      reverseButtons: true,
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    // ============================================
    // 3. ENVIAR
    // ============================================

    await enviarComprobanteSunat();
  });

  async function enviarComprobanteSunat() {
    const idVenta = $("#preview_id_venta").val();

    if (!idVenta) {
      Swal.fire({
        icon: "warning",
        title: "Comprobante",
        text: "No se encontró el comprobante.",
      });

      return;
    }

    const $btn = $("#btnEnviarSunatPreview");
    const htmlOriginal = $btn.html();

    try {
      // ============================================
      // BLOQUEAR BOTÓN
      // ============================================

      $btn.prop("disabled", true).html(`
        <span
          class="spinner-border spinner-border-sm me-1"
          role="status"
          aria-hidden="true">
        </span>
        Enviando...
      `);

      // ============================================
      // FORM DATA
      // ============================================

      const form = new FormData();

      form.set("id_venta", idVenta);

      // ============================================
      // REQUEST
      // ============================================

      const response = await fetch(instance._URL_ + "flete/enviar_sunat", {
        method: "POST",
        body: form,
      });

      const text = await response.text();

      let data;

      // ============================================
      // PARSEAR RESPUESTA
      // ============================================

      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Respuesta SUNAT:", text);

        throw new Error("El servidor devolvió una respuesta inválida.");
      }

      // ============================================
      // ERROR DEL BACKEND
      // ============================================

      if (!data.success) {
        throw new Error(data.message || "No se pudo procesar el comprobante.");
      }

      // ============================================
      // VALIDAR ESTADO SUNAT
      // ============================================

      const estadoSunat = parseInt(data.estado_sunat ?? 0);

      if (estadoSunat !== 1) {
        await Swal.fire({
          icon: "warning",
          title: "Respuesta de SUNAT",
          text:
            data.message ||
            "El comprobante fue procesado, pero no fue aceptado por SUNAT.",
        });

        // Actualizamos la tabla porque el backend
        // pudo haber actualizado envio_sunat.
        table.ajax.reload(null, false);

        return;
      }

      // ============================================
      // COMPROBANTE ACEPTADO
      // ============================================

      await Swal.fire({
        icon: "success",
        title: "Comprobante aceptado",
        text:
          data.message ||
          "El comprobante fue aceptado correctamente por SUNAT.",
      });

      // ============================================
      // CERRAR MODAL
      // ============================================

      const modalElement = document.getElementById(
        "modalVistaPreviaComprobante",
      );

      const modal = bootstrap.Modal.getInstance(modalElement);

      if (modal) {
        modal.hide();
      }

      // ============================================
      // RECARGAR DATATABLE
      // ============================================

      table.ajax.reload(null, false);
    } catch (error) {
      console.error(error);

      await Swal.fire({
        icon: "error",
        title: "No se pudo enviar",
        text: error.message || "Ocurrió un error al enviar el comprobante.",
      });
    } finally {
      // ============================================
      // RESTAURAR BOTÓN
      // ============================================

      $btn.prop("disabled", false).html(htmlOriginal);
    }
  }

  $(".open_modal_cliente").click(function (e) {
    e.preventDefault();
    $("#modal_cliente").modal("show");
  });

  $(".open_modal_vehiculo").click(function (e) {
    e.preventDefault();
    $("#modal_vehiculo").modal("show");
  });

  $(".open_modal_conductor").click(function (e) {
    e.preventDefault();
    $("#modal_conductor").modal("show");
  });

  $(".open_modal_proveedor").click(function (e) {
    e.preventDefault();
    $("#modal_proveedor").modal("show");
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
          "facturador/buscar_nacionalidad?q=" +
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
});
