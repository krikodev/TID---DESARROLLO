import * as instance from "../../../public/js/instance.js";

document.getElementById("link_cotizacion")?.classList.add("active");

document.addEventListener("DOMContentLoaded", function () {
  const table = $("#table_cotizacion").DataTable({
    ajax: {
      url: $("#url").val() + "cotizacion/dataTable",
      method: "POST",
      beforeSend: function () {
        $("#loader").removeClass("d-none");
      },
      complete: function () {
        $("#loader").addClass("d-none");
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

    columns: [
      // ======================================================
      // N° COTIZACIÓN
      // ======================================================

      {
        data: "serie_correlativo",
        orderable: false,

        render: function (data, type, row) {
          return `
        <div class="d-flex flex-column">

          <span class="fw-semibold text-dark">
            ${data || "---"}
          </span>

          ${
            row.codigo
              ? `
                <small class="text-muted">
                  ${row.codigo}
                </small>
              `
              : ""
          }

        </div>
      `;
        },
      },

      // ======================================================
      // EMISIÓN
      // ======================================================

      {
        data: "fecha_emision_format",
        orderable: false,

        render: function (data) {
          return `
        <div class="d-flex align-items-center">

          <i class="
            fa-light
            fa-calendar
            me-2
            text-muted
          "></i>

          <span>
            ${data || "---"}
          </span>

        </div>
      `;
        },
      },

      // ======================================================
      // CLIENTE
      // ======================================================

      {
        data: null,
        orderable: false,

        render: function (data, type, row) {
          return `
        <div class="d-flex flex-column">

          <span class="fw-semibold">
            ${row.cliente || "Sin cliente"}
          </span>

          ${
            row.ruc_cliente
              ? `
                <small class="text-muted">
                  ${row.ruc_cliente}
                </small>
              `
              : ""
          }

        </div>
      `;
        },
      },

      // ======================================================
      // CONDICIÓN
      // ======================================================

      {
        data: "condicion_pago",
        orderable: false,

        render: function (data, type, row) {
          if (data === "CREDITO") {
            const dias = Number(row.dias_credito || 0);

            return `
          <div class="d-flex flex-column align-items-start gap-1">

            <span class="badge bg-warning text-white">
              CRÉDITO
            </span>

            ${
              dias > 0
                ? `
                  <small class="text-muted">
                    ${dias} días
                  </small>
                `
                : ""
            }

          </div>
        `;
          }

          return `
        <span class="badge bg-success">
          CONTADO
        </span>
      `;
        },
      },

      // ======================================================
      // IMPORTE
      // ======================================================

      {
        data: "total",
        orderable: false,

        render: function (data, type, row) {
          const simbolo = row.simbolo_moneda || "S/";

          const importe = row.estado === "ANULADA" ? 0 : Number(data || 0);

          return `
        <div class="d-flex flex-column">

          <span class="fw-bold text-dark">
            ${simbolo}
            ${importe.toLocaleString("es-PE", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            })}
          </span>

          ${
            Number(row.descuento || 0) > 0
              ? `
                <small class="text-muted">
                  Desc. ${simbolo}
                  ${Number(row.descuento).toFixed(2)}
                </small>
              `
              : ""
          }

        </div>
      `;
        },
      },

      // ======================================================
      // VENCIMIENTO
      // ======================================================

      {
        data: null,
        orderable: false,

        render: function (data, type, row) {
          if (!row.fecha_vencimiento_format) {
            return `<span class="text-muted">---</span>`;
          }

          const dias = Number(row.dias_para_vencer);

          let texto = "";
          let clase = "text-muted";

          /*
           * Solo mostramos alertas de vencimiento para
           * cotizaciones que todavía están activas.
           */
          if (row.estado === "BORRADOR" || row.estado === "ENVIADA") {
            if (dias < 0) {
              const cantidad = Math.abs(dias);

              texto = `Vencida hace ${cantidad} ${
                cantidad === 1 ? "día" : "días"
              }`;

              clase = "text-danger";
            } else if (dias === 0) {
              texto = "Vence hoy";
              clase = "text-danger";
            } else {
              texto = `Vence en ${dias} ${dias === 1 ? "día" : "días"}`;

              if (dias <= 3) {
                clase = "text-warning";
              }
            }
          } else {
            texto = `${row.dias_validez || 0} días de validez`;
          }

          return `
        <div class="d-flex flex-column">

          <span>
            ${row.fecha_vencimiento_format}
          </span>

          <small class="${clase}">
            ${texto}
          </small>

        </div>
      `;
        },
      },

      // ======================================================
      // ESTADO
      // ======================================================

      {
        data: "estado",
        orderable: false,

        render: function (data) {
          const estados = {
            BORRADOR: {
              clase: "bg-secondary",
              icono: "fa-file",
            },

            ENVIADA: {
              clase: "bg-info text-dark",
              icono: "fa-paper-plane",
            },

            ACEPTADA: {
              clase: "bg-success",
              icono: "fa-circle-check",
            },

            RECHAZADA: {
              clase: "bg-danger",
              icono: "fa-circle-xmark",
            },

            VENCIDA: {
              clase: "bg-warning text-dark",
              icono: "fa-clock",
            },

            ANULADA: {
              clase: "bg-dark",
              icono: "fa-ban",
            },
          };

          const estado = estados[data] || {
            clase: "bg-secondary",
            icono: "fa-circle",
          };

          return `
        <span
          class="
            badge
            ${estado.clase}
            rounded-pill
          "
        >
          <i
            class="
              fa-light
              ${estado.icono}
              me-1
            "
          ></i>

          ${data || "---"}
        </span>
      `;
        },
      },

      // ======================================================
      // ACCIONES
      // ======================================================

      {
        data: null,
        orderable: false,

        render: function (data, type, row) {
          const id = row.id_cotizacion;

          const url = $("#url").val();

          let aceptar = "";

          if (row.estado === "BORRADOR" || row.estado === "ENVIADA") {
            aceptar = `
          <li>
            <a
              href="javascript:void(0)"
              class="
                dropdown-item
                btn_dropdown
                btn-aceptar-cotizacion
              "
              data-id="${id}"
            >
              <i
                class="
                  fa-light
                  fa-circle-check
                  me-2
                  text-success
                "
              ></i>

              Aceptar cotización
            </a>
          </li>
        `;
          }

          return `
        <div class="dropdown">

          <a
            class="dropdown-toggle"
            data-bs-toggle="dropdown"
            href="javascript:void(0)"
            role="button"
            aria-expanded="false"
          >
            <i class="bi bi-three-dots-vertical"></i>
          </a>

          <ul class="dropdown-menu dropdown-menu-end">

            <li>
              <a
                href="javascript:void(0)"
                class="
                  dropdown-item
                  btn_dropdown
                  btn-editar-cotizacion
                "
                data-id="${id}"
              >
                <i
                  class="
                    fa-light
                    fa-pen
                    me-2
                    text-primary
                  "
                ></i>

                Editar
              </a>
            </li>

            ${aceptar}

            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a
                href="${url}cotizacion/impresion/cotizacion/${id}"
                target="_blank"
                class="
                  dropdown-item
                  btn_dropdown
                "
              >
                <i
                  class="
                    fa-light
                    fa-print
                    me-2
                    text-success
                  "
                ></i>

                Imprimir
              </a>
            </li>

            <li>
              <a
                href="${url}cotizacion/impresion/cotizacion_a4/${id}"
                target="_blank"
                class="
                  dropdown-item
                  btn_dropdown
                "
              >
                <i
                  class="
                    fa-light
                    fa-file-pdf
                    me-2
                    text-danger
                  "
                ></i>

                Imprimir A4
              </a>
            </li>

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

  /* =========================================================
   * SUMMERNOTE
   * ========================================================= */

  inicializarTerminoCondicion();
  cargarTerminosCondicion("nuevo");

  function inicializarTerminoCondicion() {
    $("#condiciones_servicio").summernote({
      toolbar: [
        ["style", ["bold", "italic", "underline", "strikethrough"]],
        ["color", ["color"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["fontsize", ["fontsize"]],
        ["font", ["fontname"]],
        ["height", ["height"]],
      ],

      lineHeights: [
        "0",
        "0.3",
        "0.5",
        "0.7",
        "1.0",
        "1.2",
        "1.5",
        "2.0",
        "3.0",
      ],

      placeholder: "Escribe los mensajes personalizados para la cotización...",

      tabsize: 2,
      height: 150,
    });
  }

  function cargarTerminosCondicion(modo, contenido = "") {
    if (modo === "nuevo") {
      $("#condiciones_servicio").summernote(
        "code",
        `
          <ul>
            <li>Forma de pago: 15 días después de la presentación de la factura.</li>
            <li>El presente costo no incluye el igv.</li>
            <li>No incluye la carga ni la descarga.</li>
            <li>Incluye ubicación de unidades vía control satelital GPS.</li>
            <li>Seguro complementario de trabajo de riesgo (SCTR), Pensión y Salud.</li>
            <li>Seguro de carga hasta por 50 mil dólares por evento.</li>
            <li>Seguro vehicular y R.C. frente a terceros por 150 mil dólares.</li>
          </ul>

          <p style="line-height: 1;">
            <b>
              <i>
                NOTA: LA FACTURA SERA REPORTADA A LAS CENTRALES DE RIESGO VENCIDA A 5 DIAS DEL PLAZO OTORGADO
              </i>
            </b>
          </p>
        `,
      );
    } else if (modo === "editar") {
      $("#condiciones_servicio").summernote("code", contenido || "");
    }
  }

  /* =========================================================
   * ELEMENTOS DEL FORMULARIO
   * ========================================================= */

  const form = document.querySelector("#form");

  const button_save = document.querySelector("#button_save");
  const button_cancel = document.querySelector("#button_cancel");
  const button_loadSave = document.querySelector("#button_loadSave");

  const s_cotizacion = document.querySelector("#s_cotizacion");

  const fecha_emision = document.querySelector("#fecha_emision");
  const dias_validez = document.querySelector("#dias_validez");
  const fecha_vencimiento = document.querySelector("#fecha_vencimiento");

  const cliente = document.querySelector("#cliente");
  const cliente_id = document.querySelector("#cliente_id");

  const condicion_pago = document.querySelector("#condicion_pago");
  const moneda = document.getElementById("moneda");
  const dias_credito = document.querySelector("#dias_credito");

  /*
   * ORIGEN / DESTINO
   *
   * Ahora pertenecen directamente a la cotización.
   */

  const p_origen = document.querySelector("#p_origen");
  const p_destino = document.querySelector("#p_destino");

  const direccion_origen = document.querySelector("#direccion_origen");

  const direccion_destino = document.querySelector("#direccion_destino");

  /*
   * DETALLE DEL SERVICIO
   */

  const producto = document.querySelector("#producto");
  const producto_id = document.querySelector("#producto_id");

  const afectacion_prod = document.querySelector("#afectacion_prod");

  const tipo_unidad_servicio = document.querySelector("#tipo_unidad_servicio");

  const cantidad_viajes = document.querySelector("#cantidad_viajes");

  const precio_unit = document.querySelector("#precio_unit");

  const importe_total = document.querySelector("#importe_total");

  /*
   * TOTALES
   */

  const descuento = document.querySelector("#descuento");
  const subtotal_total = document.querySelector("#subtotal_total");
  const igv_total = document.querySelector("#igv_total");
  const porcentaje_igv = document.querySelector("#porcentaje_igv");
  const costo_total = document.querySelector("#costo_total");
  const add_producto = document.querySelector("#add_producto");
  const table_productos = document.querySelector("#table_productos");
  const btnMinusV = document.querySelector("#btn-minusV");
  const btnPlusV = document.querySelector("#btn-plusV");

  instance.Modal.change_name(["#modal"]);

  instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual");

  let productoSeleccionado = null;

  /* =========================================================
   * AUTOCOMPLETE PRODUCTO / SERVICIO
   * ========================================================= */

  const ProductoAuto = new instance.NoxAutoComplete({
    input: "#producto",

    url: instance._URL_ + "cotizacion/buscar_productos_cotizacion",

    valueField: "id_producto",

    restoreOnBlur: false,

    clearOnFocus: true,

    textField: "descripcion_prod",

    onSelect(item) {
      productoSeleccionado = item;

      producto_id.value = item.id_producto;

      const precio = parseFloat(item.valor_unitario) || 0;

      const afectacion = item.tipo_afectacion_id || "";

      precio_unit.value = precio.toFixed(2);

      if (afectacion_prod) {
        afectacion_prod.value = afectacion;
      }

      actualizarImporte();
    },

    onNotFound: async function (texto) {
      const productoCreado = await crearProducto(texto);

      if (!productoCreado) {
        return null;
      }

      productoSeleccionado = productoCreado;

      producto_id.value = productoCreado.id_producto;

      producto.value =
        productoCreado.descripcion_prod || productoCreado.nombre || texto;

      const precio = parseFloat(productoCreado.valor_unitario) || 0;

      const afectacion =
        productoCreado.tipo_afectacion_id ||
        productoCreado.afectacion_prod ||
        "";

      precio_unit.value = precio.toFixed(2);

      if (afectacion_prod) {
        afectacion_prod.value = afectacion;
      }

      actualizarImporte();

      return productoCreado;
    },
  });

  function crearFormDataProducto(datos) {
    const formData = new FormData();

    Object.entries(datos).forEach(([key, value]) => {
      formData.append(key, value);
    });

    return formData;
  }

  async function crearProducto(texto) {
    const nombre = (texto !== "" ? texto : $("#producto").val()).trim();

    if (nombre === "") {
      instance.Toast.operacion_erronea("Ingrese un producto.");

      return null;
    }

    const result = await Swal.fire({
      title: "Crear servicio",

      html: `
        El servicio <b>${nombre}</b> no existe.
        <br><br>
        ¿Desea crearlo?
      `,

      icon: "question",

      showCancelButton: true,

      confirmButtonText: "Sí, crear",

      cancelButtonText: "Cancelar",
    });

    if (!result.isConfirmed) {
      return null;
    }

    const formData = crearFormDataProducto({
      nombre_producto: nombre,

      descripcion_producto: "",

      precio_unitario: $("#precio_unit").val() || 0,

      afectacion_prod: 1,

      estado_prod: 1,

      codigo_sunat: "",

      afecto_icbper: 0,
    });

    try {
      const response = await fetch(
        instance._URL_ + "cotizacion/crear_servicio",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error(response.status);
      }

      const data = await response.json();

      if (!data.success) {
        instance.Toast.operacion_erronea(data.message);

        return null;
      }

      instance.Toast.operacion_exitosa(data.message);

      return data.producto;
    } catch (error) {
      instance.Toast.operacion_erronea(error.message);

      return null;
    }
  }

  /* =========================================================
   * UNIDAD
   * ========================================================= */

  if (tipo_unidad_servicio) {
    instance.select.createSelect(
      "#tipo_unidad_servicio",
      "#div_parentTpUnServicio",
    );
  }

  instance.select.createSelect("#moneda", "#div_parentMoneda");

  /* =========================================================
   * UBIGEO
   * ========================================================= */

  function initUbigeoSelect(selectId) {
    const element = document.querySelector(selectId);

    if (!element) {
      return null;
    }

    return new TomSelect(selectId, {
      valueField: "cod_ubigeo",

      labelField: "nombre",

      searchField: "nombre",

      placeholder: "Buscar ubicación...",

      maxItems: 1,

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
          .then((response) => {
            if (!response.ok) {
              throw new Error(response.status);
            }

            return response.json();
          })

          .then((data) => {
            if (data.success && Array.isArray(data.items)) {
              callback(data.items);
            } else {
              callback([]);
            }
          })

          .catch((error) => {
            console.error("Error buscando ubigeo:", error);

            callback([]);
          });
      },
    });
  }

  const ubigeoPOrigen = initUbigeoSelect("#p_origen");

  const ubigeoPDestino = initUbigeoSelect("#p_destino");

  function setUbigeoValue(selectInstance, codUbigeo) {
    if (!selectInstance || !codUbigeo) {
      if (selectInstance) {
        selectInstance.clear();
      }

      return;
    }

    const formData = new FormData();

    formData.set("pre", codUbigeo);

    fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())

      .then((data) => {
        if (data.success && Array.isArray(data.items) && data.items.length) {
          const item = data.items[0];

          selectInstance.addOption(item);

          selectInstance.setValue(item.cod_ubigeo);
        }
      })

      .catch((error) => {
        console.error("Error asignando ubigeo:", error);
      });
  }

  /* =========================================================
   * SERIES
   * ========================================================= */

  $("#modal").on("show.bs.modal", function () {
    cargarSeriesCotizacion();
  });

  async function cargarSeriesCotizacion() {
    try {
      const response = await fetch(
        instance._URL_ + "cotizacion/get_numero_cotizacion",
      );

      if (!response.ok) {
        throw new Error(response.status);
      }

      const data = await response.json();

      if (!data.success) {
        Swal.fire({
          icon: "info",
          title: "Información",
          text: data.message,
        });

        return;
      }

      s_cotizacion.innerHTML = "";

      if (Array.isArray(data.message)) {
        data.message.forEach((item) => {
          s_cotizacion.insertAdjacentHTML(
            "beforeend",
            `
                <option value="${item.id_serie}">
                  ${item.serie}
                </option>
              `,
          );
        });
      }
    } catch (error) {
      console.error("Error cargando series:", error);

      instance.Toast.operacion_erronea(error.message);
    }
  }

  /* =========================================================
   * FECHA DE VENCIMIENTO
   * ========================================================= */

  function formatDate(date) {
    const year = date.getFullYear();

    const month = String(date.getMonth() + 1).padStart(2, "0");

    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  }

  function calcularFechaVencimiento() {
    if (!fecha_emision.value || !dias_validez.value) {
      fecha_vencimiento.value = "";

      return;
    }

    const fecha = new Date(fecha_emision.value + "T00:00:00");

    const dias = parseInt(dias_validez.value) || 0;

    fecha.setDate(fecha.getDate() + dias);

    fecha_vencimiento.value = formatDate(fecha);
  }

  fecha_emision?.addEventListener("change", calcularFechaVencimiento);

  dias_validez?.addEventListener("input", calcularFechaVencimiento);

  dias_validez?.addEventListener("change", calcularFechaVencimiento);

  calcularFechaVencimiento();

  /* =========================================================
   * CONDICIÓN DE PAGO
   * ========================================================= */

  $("#condicion_pago").on("change", function () {
    const $diasCredito = $("#dias_credito");

    if ($(this).val() === "CREDITO") {
      $("#div_parentDiasCredito").removeClass("d-none");

      $diasCredito.prop("required", true);

      if (!$diasCredito.val() || Number($diasCredito.val()) <= 0) {
        $diasCredito.val(15);
      }
    } else {
      $("#div_parentDiasCredito").addClass("d-none");

      $diasCredito.prop("required", false);
      $diasCredito.val(0);
    }
  });

  /* =========================================================
   * IMPORTE DEL DETALLE
   * ========================================================= */

  function actualizarImporte() {
    const cantidad = parseFloat(cantidad_viajes.value) || 0;

    const precio = parseFloat(precio_unit.value) || 0;

    const importe = cantidad * precio;

    importe_total.value = importe.toFixed(2);
  }

  cantidad_viajes?.addEventListener("input", function () {
    actualizarImporte();

    updateButtonStatesViajes();
  });

  cantidad_viajes?.addEventListener("change", function () {
    actualizarImporte();

    updateButtonStatesViajes();
  });

  precio_unit?.addEventListener("input", actualizarImporte);

  precio_unit?.addEventListener("change", actualizarImporte);

  function updateButtonStatesViajes() {
    const value = parseInt(cantidad_viajes.value) || 0;

    const min = parseInt(cantidad_viajes.min) || 1;

    const max = parseInt(cantidad_viajes.max) || 100;

    if (btnMinusV) {
      btnMinusV.disabled = value <= min;
    }

    if (btnPlusV) {
      btnPlusV.disabled = value >= max;
    }
  }

  btnMinusV?.addEventListener("click", function () {
    let value = parseInt(cantidad_viajes.value) || 1;

    const min = parseInt(cantidad_viajes.min) || 1;

    if (value > min) {
      cantidad_viajes.value = value - 1;

      actualizarImporte();

      updateButtonStatesViajes();
    }
  });

  btnPlusV?.addEventListener("click", function () {
    let value = parseInt(cantidad_viajes.value) || 1;

    const max = parseInt(cantidad_viajes.max) || 100;

    if (value < max) {
      cantidad_viajes.value = value + 1;

      actualizarImporte();

      updateButtonStatesViajes();
    }
  });

  /* =========================================================
   * AGREGAR SERVICIO
   *
   * IMPORTANTE:
   * El origen y destino YA NO se validan ni se guardan
   * aquí porque pertenecen a la cotización.
   * ========================================================= */

  add_producto?.addEventListener("click", async function () {
    const tbody = table_productos.querySelector("tbody");

    if (!producto || !producto.value.trim()) {
      Swal.fire({
        icon: "warning",
        title: "Producto o servicio",
        text: "Ingrese o seleccione un producto o servicio.",
      });

      return;
    }

    if (!producto_id.value) {
      const texto = producto.value.trim();

      const productoCreado = await crearProducto(texto);

      if (!productoCreado) {
        return;
      }

      productoSeleccionado = productoCreado;

      producto_id.value = productoCreado.id_producto;

      producto.value =
        productoCreado.descripcion_prod || productoCreado.nombre || texto;

      const precio = parseFloat(productoCreado.valor_unitario) || 0;

      const afectacion =
        productoCreado.tipo_afectacion_id ||
        productoCreado.afectacion_prod ||
        "";

      precio_unit.value = precio.toFixed(2);

      if (afectacion_prod) {
        afectacion_prod.value = afectacion;
      }

      actualizarImporte();
    }

    if (!producto_id.value) {
      return;
    }

    if (!tipo_unidad_servicio.value) {
      Swal.fire({
        icon: "warning",
        title: "Unidad",
        text: "Seleccione la unidad del servicio.",
      });

      return;
    }

    const productoId = producto_id.value;

    const productoNombre = producto.value.trim();

    const cantidad = parseFloat(cantidad_viajes.value) || 0;

    const unidadId = tipo_unidad_servicio.value;

    const unidadNombre =
      tipo_unidad_servicio.options[
        tipo_unidad_servicio.selectedIndex
      ]?.textContent.trim() || "";

    const precio = parseFloat(precio_unit.value) || 0;

    const importe = parseFloat(importe_total.value) || 0;

    /*
     * Verificar que el producto no esté repetido.
     */

    const productosExistentes = tbody.querySelectorAll(".producto");

    const existe = Array.from(productosExistentes).some(
      (element) => element.dataset.idProducto === String(productoId),
    );

    if (existe) {
      instance.Toast.operacion_erronea(
        "El producto o servicio ya se encuentra agregado.",
      );

      return;
    }

    /*
     * Crear fila.
     *
     * YA NO contiene:
     * - origen
     * - destino
     * - dirección origen
     * - dirección destino
     */

    const tr = document.createElement("tr");

    tr.innerHTML = `
        <td>
          <label
            class="producto"
            data-id-producto="${productoId}"
            id-producto="${productoId}">
            ${productoNombre}
          </label>
        </td>

        <td>
          <label
            class="tipo_unidad_servicio"
            data-id="${unidadId}">
            ${unidadNombre}
          </label>
        </td>

        <td>
          <label class="cantidad_viajes">
            ${cantidad}
          </label>
        </td>

        <td>
          <label class="precio_producto">
            ${precio.toFixed(2)}
          </label>
        </td>

        <td>
          <label class="precio_total">
            ${importe.toFixed(2)}
          </label>
        </td>

        <td>
          <button
            type="button"
            class="btn button_deleteItem p-0">
            <i class="bi bi-trash text-danger fs-5"></i>
          </button>
        </td>
      `;

    tbody.prepend(tr);

    actualizarTotales();

    limpiarCamposProducto();
  });

  /* =========================================================
   * LIMPIAR CAMPOS DEL SERVICIO
   *
   * IMPORTANTE:
   * NO limpiamos origen/destino aquí.
   * ========================================================= */

  function limpiarCamposProducto() {
    if (producto) {
      producto.value = "";
    }

    if (producto_id) {
      producto_id.value = "";
    }

    productoSeleccionado = null;

    if (afectacion_prod) {
      afectacion_prod.value = "";
    }

    if (tipo_unidad_servicio) {
      tipo_unidad_servicio.value = "";

      $("#tipo_unidad_servicio").trigger("change");
    }

    cantidad_viajes.value = 1;

    precio_unit.value = "0.00";

    importe_total.value = "0.00";

    updateButtonStatesViajes();
  }

  /* =========================================================
   * ELIMINAR DETALLE
   * ========================================================= */

  $("#table_productos tbody").on("click", ".button_deleteItem", function () {
    const fila = this.closest("tr");

    if (fila) {
      fila.remove();
    }

    actualizarTotales();
  });

  /* =========================================================
   * TOTALES
   * ========================================================= */

  /* =========================================================
   * TOTALES / IGV
   * ========================================================= */

  function actualizarTotales() {
    const filas = table_productos.querySelectorAll("tbody tr");

    let importeIngresado = 0;

    filas.forEach((fila) => {
      const importe =
        parseFloat(fila.querySelector(".precio_total")?.textContent) || 0;

      importeIngresado += importe;
    });

    importeIngresado = redondear(importeIngresado);

    calcularTotalCotizacion(importeIngresado);
  }

  function calcularTotalCotizacion(importeIngresado = null) {
    /*
     * Si no recibimos el importe, lo volvemos a obtener
     * directamente desde la tabla.
     */
    if (importeIngresado === null) {
      const filas = table_productos.querySelectorAll("tbody tr");

      importeIngresado = 0;

      filas.forEach((fila) => {
        const importe =
          parseFloat(fila.querySelector(".precio_total")?.textContent) || 0;

        importeIngresado += importe;
      });
    }

    importeIngresado = redondear(importeIngresado);

    /*
     * ============================================
     * CONFIGURACIÓN IGV
     * ============================================
     */

    const porcentajeIGV = parseFloat(instance._IGV_SESION) || 18;

    const tasaIGV = porcentajeIGV / 100;

    const incluyeIGV =
      document.querySelector('input[name="incluye_igv"]:checked')?.value ===
      "1";

    /*
     * ============================================
     * DESCUENTO
     *
     * Actualmente está oculto y siempre será 0.
     * Lo conservamos para no romper el backend.
     * ============================================
     */

    let descuentoValor = parseFloat(descuento?.value) || 0;

    if (descuentoValor < 0) {
      descuentoValor = 0;
    }

    if (descuentoValor > importeIngresado) {
      descuentoValor = importeIngresado;
    }

    if (descuento) {
      descuento.value = descuentoValor.toFixed(2);
    }

    /*
     * Importe después del descuento.
     */
    const importeBase = redondear(importeIngresado - descuentoValor);

    /*
     * ============================================
     * CALCULAR VALOR VENTA / IGV / TOTAL
     * ============================================
     */

    let valorVenta = 0;
    let igv = 0;
    let total = 0;

    if (incluyeIGV) {
      /*
       * EJEMPLO:
       *
       * Precio ingresado = 1180
       *
       * El precio YA contiene IGV.
       *
       * Valor venta:
       * 1180 / 1.18 = 1000
       *
       * IGV:
       * 1180 - 1000 = 180
       *
       * Total:
       * 1180
       */

      total = importeBase;

      valorVenta = redondear(total / (1 + tasaIGV));

      igv = redondear(total - valorVenta);
    } else {
      /*
       * EJEMPLO:
       *
       * Precio ingresado = 1000
       *
       * El precio NO contiene IGV.
       *
       * Valor venta:
       * 1000
       *
       * IGV:
       * 1000 × 18% = 180
       *
       * Total:
       * 1180
       */

      valorVenta = importeBase;

      igv = redondear(valorVenta * tasaIGV);

      total = redondear(valorVenta + igv);
    }

    /*
     * ============================================
     * MOSTRAR RESULTADOS
     * ============================================
     */

    subtotal_total.textContent = valorVenta.toFixed(2);

    if (igv_total) {
      igv_total.textContent = igv.toFixed(2);
    }

    if (porcentaje_igv) {
      porcentaje_igv.textContent = porcentajeIGV.toFixed(0);
    }

    costo_total.textContent = total.toFixed(2);
  }

  $("#descuento, input[name='incluye_igv']").on("input change", function () {
    actualizarTotales();
  });

  function redondear(numero, decimales = 2) {
    const factor = Math.pow(10, decimales);

    return Math.round((numero + Number.EPSILON) * factor) / factor;
  }

  $("#descuento, input[name='incluye_igv']").on("input change", function () {
    calcularTotalCotizacion();
  });

  function redondear(numero, decimales = 2) {
    const factor = Math.pow(10, decimales);

    return Math.round(numero * factor) / factor;
  }

  /* =========================================================
   * OBTENER PRODUCTOS
   *
   * SOLO datos propios del detalle.
   * ========================================================= */

  function obtenerProductos() {
    const filas = table_productos.querySelectorAll("tbody tr");

    const productos = [];

    filas.forEach((fila) => {
      const productoEl = fila.querySelector(".producto");

      const unidadEl = fila.querySelector(".tipo_unidad_servicio");

      const cantidadEl = fila.querySelector(".cantidad_viajes");

      const precioEl = fila.querySelector(".precio_producto");

      const importeEl = fila.querySelector(".precio_total");

      if (!productoEl) {
        return;
      }

      productos.push({
        id_producto:
          productoEl.dataset.idProducto ||
          productoEl.getAttribute("id-producto") ||
          "",

        descripcion: productoEl.textContent.trim(),

        id_unidad: unidadEl?.dataset.id || "",

        unidad: unidadEl?.textContent.trim() || "",

        cantidad: parseFloat(cantidadEl?.textContent) || 0,

        precio_unitario: parseFloat(precioEl?.textContent) || 0,

        subtotal: parseFloat(importeEl?.textContent) || 0,
      });
    });

    return productos;
  }

  /* =========================================================
   * VALIDAR ORIGEN / DESTINO
   *
   * Se valida una sola vez al guardar la cotización.
   * ========================================================= */

  function validarRuta() {
    if (!ubigeoPOrigen || !ubigeoPOrigen.getValue()) {
      Swal.fire({
        icon: "warning",
        title: "Origen",
        text: "Seleccione el punto de origen.",
      });

      return false;
    }

    if (!ubigeoPDestino || !ubigeoPDestino.getValue()) {
      Swal.fire({
        icon: "warning",
        title: "Destino",
        text: "Seleccione el punto de destino.",
      });

      return false;
    }

    return true;
  }

  /* =========================================================
   * GUARDAR COTIZACIÓN
   * ========================================================= */

  form?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const mostrarError = function (titulo, texto, campo = null) {
      Swal.fire({
        icon: "warning",
        title: titulo,
        text: texto,
      });

      if (campo) {
        campo.focus();
      }

      return false;
    };

    /* -----------------------------------------
     * CLIENTE
     * ----------------------------------------- */

    if (!cliente_id || !cliente_id.value) {
      mostrarError("Cliente", "Seleccione un cliente.", cliente);

      return;
    }

    /* -----------------------------------------
     * ORIGEN / DESTINO
     * ----------------------------------------- */

    if (!validarRuta()) {
      return;
    }

    /* -----------------------------------------
     * DETALLE
     * ----------------------------------------- */

    const productos = obtenerProductos();

    if (!productos || productos.length === 0) {
      mostrarError(
        "Detalle vacío",
        "Debe agregar al menos un servicio a la cotización.",
      );

      return;
    }

    /* -----------------------------------------
     * FECHA
     * ----------------------------------------- */

    if (!fecha_emision || !fecha_emision.value) {
      mostrarError(
        "Fecha de emisión",
        "Ingrese la fecha de emisión.",
        fecha_emision,
      );

      return;
    }

    /* -----------------------------------------
     * VALIDEZ
     * ----------------------------------------- */

    if (!dias_validez || !dias_validez.value) {
      mostrarError(
        "Validez",
        "Ingrese los días de validez de la cotización.",
        dias_validez,
      );

      return;
    }

    if (Number(dias_validez.value) <= 0) {
      mostrarError(
        "Validez",
        "Los días de validez deben ser mayores a cero.",
        dias_validez,
      );

      return;
    }

    /* -----------------------------------------
     * CONDICIÓN DE PAGO
     * ----------------------------------------- */

    if (!condicion_pago || !condicion_pago.value) {
      mostrarError(
        "Condición de pago",
        "Seleccione la condición de pago.",
        condicion_pago,
      );

      return;
    }

    if (condicion_pago.value === "CREDITO") {
      if (
        !dias_credito ||
        !dias_credito.value ||
        Number(dias_credito.value) <= 0
      ) {
        mostrarError(
          "Plazo de crédito",
          "Ingrese los días de crédito.",
          dias_credito,
        );

        return;
      }
    }

    /* -----------------------------------------
     * TOTALES
     * ----------------------------------------- */

    calcularTotalCotizacion();

    const subtotal = parseFloat(subtotal_total.textContent) || 0;

    const igv = parseFloat(igv_total?.textContent) || 0;

    const descuentoValue = parseFloat(descuento?.value) || 0;

    const total = parseFloat(costo_total.textContent) || 0;

    const incluyeIGV =
      document.querySelector('input[name="incluye_igv"]:checked')?.value === "1"
        ? 1
        : 0;

    const porcentajeIGV = parseFloat(instance._IGV_SESION) || 18;

    if (subtotal < 0) {
      mostrarError("Subtotal inválido", "El subtotal no puede ser negativo.");

      return;
    }

    if (igv < 0) {
      mostrarError("IGV inválido", "El IGV no puede ser negativo.");

      return;
    }

    if (total < 0) {
      mostrarError(
        "Total inválido",
        "El total de la cotización no puede ser negativo.",
      );

      return;
    }

    /* -----------------------------------------
     * FORMDATA
     * ----------------------------------------- */

    const formData = new FormData(form);

    formData.set("productos", JSON.stringify(productos));
    formData.set("ubigeo_origen", ubigeoPOrigen.getValue());
    formData.set("direccion_origen", direccion_origen?.value || "");
    formData.set("ubigeo_destino", ubigeoPDestino.getValue());
    formData.set("direccion_destino", direccion_destino?.value || "");
    formData.set("moneda", moneda.value);
    formData.set("subtotal", subtotal.toFixed(2));
    formData.set("descuento", "0.00");
    formData.set("igv", igv.toFixed(2));
    formData.set("porcentaje_igv", porcentajeIGV.toFixed(2));
    formData.set("total", total.toFixed(2));
    formData.set("incluye_igv", incluyeIGV);
    formData.set("condicion_pago", condicion_pago.value);
    formData.set(
      "dias_credito",
      condicion_pago.value === "CREDITO" ? dias_credito.value : "0",
    );

    /*
     * Summernote
     */

    const condicionesServicio = $("#condiciones_servicio").summernote("code");

    formData.set("condiciones_servicio", condicionesServicio);

    const obs = document.querySelector("#obs");

    formData.set("obs", obs ? obs.value : "");

    const modo = document.querySelector("#modo");

    formData.set("modo", modo ? modo.value : "nuevo");

    /* -----------------------------------------
     * LOADING
     * ----------------------------------------- */

    button_save?.classList.add("d-none");

    button_cancel?.classList.add("d-none");

    button_loadSave?.classList.remove("d-none");

    try {
      const response = await fetch(
        instance._URL_ + "cotizacion/crud_register",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        const text = await response.text();

        throw new Error(`Error ${response.status}: ${text}`);
      }

      const data = await response.json();

      if (!data.success) {
        Swal.fire({
          icon: "error",
          title: "Operación errónea",
          text: data.message || "No se pudo guardar la cotización.",
        });

        return;
      }

      $("#modal").modal("hide");

      limpiarFormularioCotizacion();

      instance.Datatable.reloadTable(table);

      instance.Toast.operacion_exitosa(
        data.message || "Cotización registrada correctamente.",
      );
    } catch (error) {
      console.error("Error guardando cotización:", error);

      instance.Toast.operacion_erronea(
        error.message || "Ocurrió un error al guardar la cotización.",
      );
    } finally {
      button_save?.classList.remove("d-none");

      button_cancel?.classList.remove("d-none");

      button_loadSave?.classList.add("d-none");
    }
  });

  /* =========================================================
   * LIMPIAR TABLA
   * ========================================================= */

  function limpiarTablaProductos() {
    const tbody = table_productos.querySelector("tbody");

    if (!tbody) {
      return;
    }

    tbody.innerHTML = "";
  }

  function limpiarTotales() {
    if (subtotal_total) {
      subtotal_total.textContent = "0.00";
    }

    if (igv_total) {
      igv_total.textContent = "0.00";
    }

    if (descuento) {
      descuento.value = "0.00";
    }

    if (costo_total) {
      costo_total.textContent = "0.00";
    }

    if (porcentaje_igv) {
      porcentaje_igv.textContent = (
        parseFloat(instance._IGV_SESION) || 18
      ).toFixed(0);
    }
  }

  /* =========================================================
   * LIMPIAR FORMULARIO
   * ========================================================= */

  function limpiarFormularioCotizacion() {
    if (form) {
      form.reset();
    }

    const modo = document.querySelector("#modo");

    if (modo) {
      modo.value = "nuevo";
    }

    const idCotizacion = document.querySelector("#id_cotizacion");

    if (idCotizacion) {
      idCotizacion.value = "";
    }

    if (producto) {
      producto.value = "";
    }

    if (producto_id) {
      producto_id.value = "";
    }

    productoSeleccionado = null;

    if (afectacion_prod) {
      afectacion_prod.value = "";
    }

    /*
     * Limpiar origen y destino
     * solamente al limpiar TODA la cotización.
     */

    if (ubigeoPOrigen) {
      ubigeoPOrigen.clear();
    }

    if (ubigeoPDestino) {
      ubigeoPDestino.clear();
    }

    if (direccion_origen) {
      direccion_origen.value = "";
    }

    if (direccion_destino) {
      direccion_destino.value = "";
    }

    limpiarTablaProductos();

    limpiarTotales();

    if (condicion_pago) {
      condicion_pago.value = "CONTADO";
    }

    $("#div_parentDiasCredito").addClass("d-none");

    if (dias_credito) {
      dias_credito.required = false;

      dias_credito.value = 0;
    }

    if (cantidad_viajes) {
      cantidad_viajes.value = 1;
    }

    if (precio_unit) {
      precio_unit.value = "0.00";
    }

    if (importe_total) {
      importe_total.value = "0.00";
    }

    $("#incluye_igv_si").prop("checked", true);
    $("#incluye_igv_no").prop("checked", false);
    /*
     * Restablecer términos
     * por defecto.
     */

    cargarTerminosCondicion("nuevo");

    form?.classList.remove("was-validated");

    updateButtonStatesViajes();

    calcularFechaVencimiento();
  }

  $("#modal").on("hidden.bs.modal", function () {
    limpiarFormularioCotizacion();
  });

  $("#button_cancel").on("click", function () {
    limpiarFormularioCotizacion();
  });

  $(".btn-nueva-cotizacion").on("click", function () {
    limpiarFormularioCotizacion();

    $("#modal").modal("show");
  });

  /* =========================================================
   * EDITAR
   * ========================================================= */

  $("#table_cotizacion tbody").on(
    "click",
    ".btn-editar-cotizacion",
    async function () {
      const id = this.dataset.id;

      if (!id) {
        return;
      }

      await cargarCotizacion(id);
    },
  );

  /* =========================================================
   * ACEPTAR COTIZACIÓN
   * ========================================================= */

  $("#table_cotizacion tbody").on(
    "click",
    ".btn-aceptar-cotizacion",
    async function () {
      const id_cot = $(this).data("id");

      Swal.fire({
        title: "¿Marcar como aceptada?",

        text: "La cotización pasará al estado ACEPTADA y podrá ser convertida en boleta o factura.",

        icon: "question",

        showCancelButton: true,

        confirmButtonText: "Sí, aceptar",

        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (!result.isConfirmed) {
          return;
        }

        document.getElementById("loader").classList.remove("d-none");

        fetch($("#url").val() + "cotizacion/aceptar_cotizacion", {
          method: "POST",

          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },

          body: new URLSearchParams({
            id_cotizacion: id_cot,
          }),
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Error del servidor (${response.status})`);
            }

            return response.json();
          })

          .then((resp) => {
            if (resp.success) {
              Swal.fire(
                "Listo",

                resp.message || "Cotización aceptada correctamente.",

                "success",
              );

              table.ajax.reload(null, false);
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
    },
  );

  /* =========================================================
   * CARGAR COTIZACIÓN
   * ========================================================= */

  async function cargarCotizacion(id) {
    try {
      limpiarFormularioCotizacion();

      const formData = new FormData();

      formData.set("id_cotizacion", id);

      const response = await fetch(
        instance._URL_ + "cotizacion/getCotizacion",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error(response.status);
      }

      const data = await response.json();

      if (!data.success) {
        instance.Toast.operacion_erronea(data.message);

        return;
      }

      const cotizacion = data.data;

      /* -----------------------------------------
       * DATOS GENERALES
       * ----------------------------------------- */

      $("#id_cotizacion").val(cotizacion.id_cotizacion);

      $("#modo").val("editar");

      $("#s_cotizacion").val(cotizacion.id_serie || "");

      $("#fecha_emision").val(
        cotizacion.fecha_emision
          ? cotizacion.fecha_emision.substring(0, 10)
          : "",
      );

      $("#dias_validez").val(cotizacion.dias_validez || 15);

      $("#fecha_vencimiento").val(cotizacion.fecha_vencimiento || "");

      $("#cliente").val(cotizacion.nombre_cliente || "");

      $("#cliente_id").val(cotizacion.id_cliente || "");

      $("#moneda")
        .val(cotizacion.id_tp_moneda || "")
        .trigger("change");

      /* -----------------------------------------
       * ORIGEN / DESTINO
       *
       * Ahora vienen directamente
       * de cotizacion.
       * ----------------------------------------- */

      setUbigeoValue(ubigeoPOrigen, cotizacion.ubigeo_origen || "");

      setUbigeoValue(ubigeoPDestino, cotizacion.ubigeo_destino || "");

      $("#direccion_origen").val(cotizacion.direccion_origen || "");

      $("#direccion_destino").val(cotizacion.direccion_destino || "");

      /* -----------------------------------------
       * CONDICIÓN DE PAGO
       * ----------------------------------------- */

      $("#condicion_pago").val(cotizacion.condicion_pago || "CONTADO");

      $("#dias_credito").val(cotizacion.dias_credito || 0);

      $("#condicion_pago").trigger("change");

      $(
        'input[name="incluye_igv"][value="' +
          (cotizacion.incluye_igv ?? 1) +
          '"]',
      ).prop("checked", true);
      /* -----------------------------------------
       * TOTALES
       * ----------------------------------------- */

      $("#descuento").val(parseFloat(cotizacion.descuento || 0).toFixed(2));

      $("#obs").val(cotizacion.obs || "");

      /* -----------------------------------------
       * CONDICIONES DEL SERVICIO
       *
       * Corregido:
       * usamos Summernote, no Quill.
       * ----------------------------------------- */

      cargarTerminosCondicion("editar", cotizacion.condiciones_servicio || "");

      /* -----------------------------------------
       * DETALLES
       * ----------------------------------------- */

      cargarDetalleCotizacion(cotizacion.detalles || []);

      actualizarTotales();

      $("#modal").modal("show");
    } catch (error) {
      console.error("Error cargando cotización:", error);

      instance.Toast.operacion_erronea(error.message);
    }
  }

  /* =========================================================
   * CARGAR DETALLES
   * ========================================================= */

  function cargarDetalleCotizacion(detalles) {
    const tbody = table_productos.querySelector("tbody");

    tbody.innerHTML = "";

    if (!Array.isArray(detalles)) {
      return;
    }

    detalles.forEach((detalle) => {
      agregarFilaDetalle(detalle);
    });

    actualizarTotales();
  }

  /* =========================================================
   * AGREGAR FILA DE DETALLE
   *
   * YA NO EXISTEN:
   * - origen
   * - destino
   * - dirección origen
   * - dirección destino
   * ========================================================= */

  function agregarFilaDetalle(detalle) {
    const tbody = table_productos.querySelector("tbody");

    const tr = document.createElement("tr");

    const idProducto = detalle.producto_id || detalle.id_producto || "";

    const descripcion = detalle.descripcion || "";

    const unidadId = detalle.id_tipo_unidad || detalle.id_unidad || "";

    const unidadNombre = detalle.unidad || "";

    const cantidad = parseFloat(detalle.cantidad_viajes) || 0;

    const precio = parseFloat(detalle.precio_unitario) || 0;

    const subtotal = parseFloat(detalle.importe_total) || 0;

    tr.innerHTML = `
      <td>
        <label
          class="producto"
          data-id-producto="${idProducto}"
          id-producto="${idProducto}">
          ${descripcion}
        </label>
      </td>

      <td>
        <label
          class="tipo_unidad_servicio"
          data-id="${unidadId}">
          ${unidadNombre}
        </label>
      </td>

      <td>
        <label class="cantidad_viajes">
          ${cantidad}
        </label>
      </td>

      <td>
        <label class="precio_producto">
          ${precio.toFixed(2)}
        </label>
      </td>

      <td>
        <label class="precio_total">
          ${subtotal.toFixed(2)}
        </label>
      </td>

      <td>
        <button
          type="button"
          class="btn button_deleteItem p-0">
          <i class="bi bi-trash text-danger fs-5"></i>
        </button>
      </td>
    `;

    tbody.appendChild(tr);
  }

  /* =========================================================
   * MODAL PRODUCTO
   * ========================================================= */

  $(".open_modal_producto").on("click", function (e) {
    e.preventDefault();

    $("#modal_producto").modal("show");
  });

  /* =========================================================
   * INICIALIZACIÓN
   * ========================================================= */

  actualizarImporte();

  actualizarTotales();

  updateButtonStatesViajes();
});
