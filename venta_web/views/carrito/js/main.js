import {
  StorageManager
} from './storageManager.js';
import {
  iniciar_temporizador,
  detener_temporizador,
  restaurar_temporizador,
  onTemporizadorExpirado,
  establecerCallbackExpiracion
} from './temporizador.js';

import CulqiPaymentManager from './culqui.js';

document.addEventListener('DOMContentLoaded', function () {
  setupTerminalSelects();

  // CHECKBOX DE PAGO
  const checkbox_factura = document.getElementById("bus-factura");
  const checkbox_comprador = document.getElementById("bus-comprador");

  // Inicializar el StorageManager --- Esta clase maneja el almacenamiento local a nivel navegador
  const compraStorage = new StorageManager("datosCompra", {
    asientosSeleccionados: [],
    programacion: null
  });

  console.log("🚀 Datos de compra inicializados:", compraStorage.valor);

  // Restaurar al recargar
  establecerCallbackExpiracion(() =>
    onTemporizadorExpirado({
      asientosReservados: compraStorage.valor.asientosSeleccionados || [],
      programacionId: compraStorage.valor.programacion || '',
      urlBase: $("#url_web").val()
    })
  );


  //Variables utilizados en el js
  const _URL_ = document.querySelector("#url").value;
  const _URL_VENTA_WEB = document.querySelector("#url_venta_web").value;
  let form = document.getElementById("form-busqueda");
  let origen = document.getElementById("origen");
  let destino = document.getElementById("destino");
  let fecha_programacion = document.getElementById("fecha_programacion");
  // No permitir seleccionar fechas anteriores a hoy
  if (fecha_programacion) {
    const hoy = new Date();
    const fechaHoy = hoy.toISOString().split('T')[0];

    fecha_programacion.min = fechaHoy;
  }
  let loader = document.getElementById("loader");

  let contenedor_programaciones = document.getElementById('contenedor-programaciones');
  let lista_programaciones = document.getElementById('lista-programaciones');

  // Restaurar al recargar - AHORA USA EL CALLBACK GLOBAL
  restaurar_temporizador();

  // Restaurar los pasos
  recuperarPaso();

  // ============ AGREGAR ESTAS FUNCIONES A TU ARCHIVO VENTA_WEB.JS ============

  // Agregar esta verificación después de recuperarPaso();
  // Verificar si viene de una búsqueda automática desde la página web
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('auto_search') === 'true') {
    procesarBusquedaAutomatica();
  }

  /**
   * Procesa la búsqueda automática cuando el usuario viene de la página web
   */
  function procesarBusquedaAutomatica() {
    const busquedaPendiente = localStorage.getItem('busqueda_pendiente');

    if (!busquedaPendiente) {
      console.warn("⚠️ No se encontraron datos de búsqueda pendiente");
      return;
    }

    try {
      const datos = JSON.parse(busquedaPendiente);

      // Verificar que los datos no sean muy antiguos (máximo 5 minutos)
      const tiempoTranscurrido = Date.now() - datos.timestamp;
      const tiempoMaximo = 5 * 60 * 1000; // 5 minutos en milisegundos

      if (tiempoTranscurrido > tiempoMaximo) {
        console.warn("⚠️ Los datos de búsqueda han expirado");
        localStorage.removeItem('busqueda_pendiente');
        return;
      }

      // Llenar los campos del formulario automáticamente
      llenarCamposBusqueda(datos);

      // Ejecutar la búsqueda automáticamente después de un pequeño delay
      setTimeout(() => {
        ejecutarBusquedaAutomatica(datos);
      }, 500);

      // Limpiar el storage después de usar los datos
      localStorage.removeItem('busqueda_pendiente');

      // Limpiar la URL para evitar recargas accidentales
      if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, window.location.pathname);
      }

    } catch (error) {
      console.error("Error al procesar búsqueda automática:", error);
      localStorage.removeItem('busqueda_pendiente');
    }
  }

  /**
   * Llena los campos del formulario de búsqueda con los datos recibidos
   */
  function llenarCamposBusqueda(datos) {
    // Usar las variables que ya tienes definidas
    if (origen) origen.value = datos.origen;
    if (destino) destino.value = datos.destino;
    if (fecha_programacion) fecha_programacion.value = datos.fecha_programacion;
  }

  /**
   * Ejecuta la búsqueda automática usando tu función existente
   */
  function ejecutarBusquedaAutomatica(datos) {
    Swal.fire({
      title: 'Procesando búsqueda',
      text: 'Cargando programaciones disponibles para hoy...',
      icon: 'info',
      timer: 2000,
      showConfirmButton: false,
      timerProgressBar: true
    });

    // Actualizar el storage con los datos de búsqueda
    compraStorage.actualizar({
      origen: datos.origen,
      destino: datos.destino,
      fecha: datos.fecha_programacion
    });

    // Usar tu función existente cargarProgramaciones
    cargarProgramaciones(datos.fecha_programacion, datos.origen, datos.destino);
  }

  /* Funcion de la barra de progreso de los pasos generales del sistema de venta*/
  function mostrarIndicadorProgreso(pasoActivo) {
    const indicador = document.getElementById("indicador-progreso")
    const pasos = indicador.querySelectorAll(".step")

    indicador.style.display = "block"

    // Actualizar el storage con el paso activo
    compraStorage.actualizar({ pasoActual: pasoActivo });
    pasos.forEach((paso, index) => {
      const numeroPaso = index + 1
      paso.classList.remove("active", "completed")

      if (numeroPaso < pasoActivo) {
        paso.classList.add("completed")
      } else if (numeroPaso === pasoActivo) {
        paso.classList.add("active")
      }
    })
  }

  /* MODULO DE PROGRAMACIONES*/
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const fecha = fecha_programacion.value.trim();
    const origen_b = origen.value.trim();
    const destino_b = destino.value.trim();

    // Validar
    if (!origen_b || !destino_b || !fecha) {
      Swal.fire({
        title: 'Campos incompletos',
        text: 'Por favor, complete todos los campos: origen, destino y fecha.',
        icon: 'warning',
        confirmButtonText: 'Aceptar'
      });
      return;
    }

    compraStorage.actualizar({
      origen: origen_b,
      destino: destino_b,
      fecha: fecha
    });

    // Si todo está bien, enviar datos
    cargarProgramaciones(fecha, origen_b, destino_b);
  });

  /**
  * Función principal para cargar programaciones
  * @param {string} fechaSalida - Fecha de salida (formato YYYY-MM-DD)
  * @param {string} idOrigen - ID del origen
  * @param {string} idDestino - ID del destino
  */
  function cargarProgramaciones(fechaSalida, idOrigen, idDestino) {
    if (!contenedor_programaciones) {
      console.error("Contenedor de programaciones no encontrado");
      return;
    }

    // Mostrar indicador de carga
    loader.classList.remove("d-none");

    // Preparar datos para la solicitud
    const formData = new FormData();
    formData.append('fechaIda', fechaSalida);
    formData.append('origen', idOrigen);
    formData.append('destino', idDestino);

    // Realizar la solicitud fetch
    fetch(`${_URL_VENTA_WEB}carrito/get_programaciones`, {
      method: 'POST',
      body: formData
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          renderizarProgramaciones(data.message, idDestino);
        } else {
          mostrarIndicadorProgreso(1);
          lista_programaciones.innerHTML = '';
          Swal.fire({
            title: 'Atención',
            text: 'No se han encontrado programaciones para la fecha seleccionada.',
            icon: 'info',
            confirmButtonText: 'Aceptar',
            background: '#fff',
            color: '#212529',
            confirmButtonColor: '#ffc107',
            customClass: {
              popup: 'rounded shadow',
              title: 'fs-4',
              confirmButton: 'btn btn-primary'
            }
          });

        }
      })
      .catch(error => {
        console.error("Error al cargar programaciones:", error);
      })
      .finally(() => {
        loader.classList.add("d-none");
      });
  }

  /**
    * Renderiza las programaciones en el DOM
    * @param {Array} programaciones - Lista de programaciones obtenidas
    * @param {HTMLElement} contenedor - Contenedor donde se agregarán las programaciones
  */
  function renderizarProgramaciones(programaciones, idDestino) {
    if (!programaciones || programaciones.length === 0) {
      Swal.fire({
        title: 'Atención',
        text: 'No se han encontrado programaciones para la fecha seleccionada.',
        icon: 'info',
        confirmButtonText: 'Aceptar',
        background: '#fff',
        color: '#212529',
        confirmButtonColor: '#ffc107',
        customClass: {
          popup: 'rounded shadow',
          title: 'fs-4',
          confirmButton: 'btn btn-primary'
        }
      });
      return;
    }

    lista_programaciones.innerHTML = "";

    programaciones.forEach((programacion, index) => {
      const descripcion = programacion.descripcion || 'Sin descripción';
      const hora_salida = programacion.hora_salida || 'N/A';
      const precio_primer_piso = programacion.precio_primer_piso || '0.00';
      const precio_segundo_piso = programacion.precio_segundo_piso || '0.00';
      const nombre_origen = programacion.nombre_origen || 'N/A';
      const nombre_destino = programacion.nombre_destino || 'N/A';
      const fecha_salida = programacion.fecha_salida ? programacion.fecha_salida.split("-").reverse().join("-") : 'N/A';
      const num_asientos = programacion.num_asientos || '0';
      const id_vehiculo = programacion.id_vehiculo || '';
      const id_programacion = programacion.id_programacion || '';
      const destacado = programacion.destacado || '';
      const detalle = programacion.detalle || '';
      const tipo_viaje = programacion.tipo_viaje || '';
      const duracion = programacion.duracion || '';
      const hora_llegada = programacion.hora_llegada || '';
      const asientos_disponibles = programacion.asientos_disponibles || '';

      const programacionDiv = document.createElement("div")
      programacionDiv.className = `programacion-item ${destacado ? "destacado" : ""}`

      programacionDiv.innerHTML = `
      <!-- Información del servicio -->
      <div class="servicio-info">
        ${destacado ? '<span class="badge-destacado">Más barato</span>' : ""}
        <div class="servicio-tipo">${descripcion}</div>
        <div class="servicio-detalle">${detalle}</div>
      </div>

      <!-- Información de horarios -->
      <div class="horario-info">
        <div class="hora-salida">
          <div class="hora">${hora_salida}</div>
          <div class="estacion">${nombre_origen}</div>
        </div>
        
        <div class="ruta-info">
          <div class="ruta-linea">
            <span class="tipo-viaje">${tipo_viaje}</span>
          </div>
          <div class="duracion">${duracion}</div>
        </div>
        
        <div class="hora-llegada">
          <div class="hora">${hora_llegada}</div>
          <div class="estacion">${nombre_destino}</div>
        </div>
      </div>

      <!-- Precio y acciones -->
      <div class="precio-acciones">
        <div class="precio-info">
          <div class="precio-desde">Primer piso:</div>
          <div class="precio">${precio_primer_piso}</div>
          <div class="precio-desde">Segundo piso:</div>
          <div class="precio">${precio_segundo_piso}</div>
          ${asientos_disponibles ? `<div class="asientos-disponibles">${asientos_disponibles}</div>` : ""}
        </div>
        
        <div class="acciones">
          <a href="#" class="btn-ver-detalles d-none">Ver detalles</a>
          <button class="btn-ver-asientos seleccionar-programacion"
           data-vehiculo="${id_vehiculo}" 
                            data-programacion="${id_programacion}"
                            programacion="${id_programacion}"
                            data-destino="${idDestino}" 
                            descripcion="${descripcion}" 
                            hora_salida="${hora_salida}" 
                            NombreO="${nombre_origen}" 
                            NombreD="${nombre_destino}"
                            fechaIda="${fecha_salida}"
          ">
            Ver asientos
          </button>
        </div>
      </div>

      <!-- Sección expandible de asientos -->
      <div class="asientos-expandible" id="asientos-${id_programacion}">
        
        <div class="contenido-asientos">
          <div class="plano-asientos" id="contenedor-asientos-${id_programacion}" style="overflow-x: auto; width: 100%;">
          </div>
          
          <div class="panel-seleccion">
            <div class="precio-aproximado">
              <h6>Precio aproximado*</h6>
            </div>
            <div class="mensaje-seleccion" id="mensaje-${id_programacion}">
              Elige al menos 1 asiento
            </div>
            <div class="total-seleccion-expandible" id="total-${id_programacion}" style="display:none;">
              <div class="d-flex justify-content-between mb-2">
                <span>Total:</span>
                <strong class="total-precio">S/. 0.00</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>N° asientos:</span>
                <strong class="asientos-seleccionados">0</strong>
              </div>
            </div>
            <button class="btn-continuar-expandible" id="continuar-${id_programacion}" disabled>
              Elige al menos 1 asiento
            </button>
          </div>
        </div>
      </div>
    `;
      programacionDiv.style.animationDelay = `${index * 0.1}s`
      lista_programaciones.appendChild(programacionDiv)
    });

    // Mostrar el div principal de programaciones
    contenedor_programaciones.style.display = "block";

    setTimeout(() => {
      contenedor_programaciones.scrollIntoView({
        behavior: "smooth",
        block: "start",
      })
    }, 300)
    mostrarIndicadorProgreso(2);
  }

  // CONTROL DE CLICKS | Todo lo reclacionado con el proceso de venta
  let programacionAbiertaId = null;
  document.addEventListener("click", (e) => {
    // Event handler simplificado - solo maneja el click
    if (e.target.classList.contains("seleccionar-programacion")) {
      const btnActual = e.target;
      const programacionIdActual = Number.parseInt(btnActual.dataset.programacion);

      toggleAsientos(programacionIdActual, btnActual);
    }

    if (e.target.classList.contains("btn-continuar-expandible")) {
      const datosStorage = compraStorage.valor;
      if (datosStorage.asientosSeleccionados.length > 0) {
        generarFormulariosPasajero()
      }
    }

    if (e.target.closest('.btn-buscar-pasajero')) {
      e.preventDefault();
      const button = e.target.closest('.btn-buscar-pasajero');
      const formGroup = button.closest('.bus-form-grid');
      buscar_pasajero(button, formGroup);
    }

    if (e.target.id === "btn-continuar-pago") {
      if (validarDatosPersonales()) {
        guardarDatosPersonales();

        // Verificar datos completos antes de continuar
        if (validarDatosCompletos()) {
          mostrarSeccionPagos();
        }
      }
    }

    // Actualización del evento del botón
    if (e.target.id === "btn-pagar") {
      // let DatossCompra = {};
      // DatossCompra.charge_id = 'chr_test_18Q6FDT6J167jnPB';
      // DatossCompra.payment_method = 'tarjeta';
      // DatossCompra.amount = 3300;
      // DatossCompra.currency = 'PEN';
      // concretarVentaFinal(DatossCompra);
      // return;
      if (validarYGuardarDatosPagante()) {
        // Mostrar confirmación de pago
        Swal.fire({
          title: 'Confirmar Pago',
          text: '¿Estás seguro de que deseas proceder con el pago?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#2a9d8f',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, pagar',
          cancelButtonText: 'No, volver'
        }).then((result) => {
          if (result.isConfirmed) {
            iniciar_proceso_pago_con_metodo();
          }
        });
      }
    }

    if (e.target.id === "btn-volver-asientos") {
      mostrarIndicadorProgreso(2);
      const StorageData = compraStorage.valor;
      StorageData.datosPasajeros = [];
      compraStorage.actualizar(StorageData);
      document.getElementById('contenedor-formularios-pasajeros').style.display = 'none';
      document.getElementById('floating-search').style.display = 'block';
      document.getElementById('contenedor-programaciones').style.display = 'block';
      recuperarPaso();
    }

    if (e.target.id === "btn-cancelar-compra") {
      cancelarCompra();
    }

    if (e.target.id === "btn-volver-pasajeros") {
      document.getElementById('contenedor-pagos').classList.add('d-none');
      generarFormulariosPasajero();
      completarInformacionPasajeros();
    }

    if (e.target.classList.contains("metodo-pago")) {
      seleccionarMetodoPago(e.target)
    }

    if (e.target.id === "finalizar-compra") {
      finalizarCompra()
    }
  });

  // Método unificado que maneja toda la lógica
  function toggleAsientos(programacionId, boton = null, preservarDatos = false) {
    const datos = compraStorage.valor;
    const seccionActual = document.getElementById(`asientos-${programacionId}`);
    const btnActual = boton || document.querySelector(`.seleccionar-programacion[data-programacion="${programacionId}"]`);

    const estaAbierta = seccionActual.classList.contains("activo");

    if (estaAbierta) {
      cerrarProgramacion(programacionId, seccionActual, btnActual, datos);
    } else {
      if (programacionAbiertaId !== null && programacionAbiertaId !== programacionId) {
        cerrarProgramacionAnterior(programacionAbiertaId, datos);
      }
      abrirProgramacion(programacionId, seccionActual, btnActual, datos, preservarDatos);
    }
  }

  // Función auxiliar para cerrar programación actual
  function cerrarProgramacion(programacionId, seccion, boton, datos) {
    seccion.classList.remove("activo");
    boton.textContent = "Ver asientos";
    cerrar_programacion(programacionId);

    // Eliminar reservas visuales
    datos.asientosSeleccionados.forEach(a => eliminar_reserva(a.idAsiento, programacionId));

    // Limpiar datos
    datos.asientosSeleccionados = [];
    datos.sumaTotal = 0;
    datos.programacion = null;
    detener_temporizador();

    compraStorage.actualizar(datos);
    programacionAbiertaId = null;
  }

  // Función auxiliar para cerrar programación anterior
  function cerrarProgramacionAnterior(programacionIdAnterior, datos) {
    const seccionAnterior = document.getElementById(`asientos-${programacionIdAnterior}`);
    const btnAnterior = document.querySelector(`.seleccionar-programacion[data-programacion="${programacionIdAnterior}"]`);

    if (seccionAnterior && btnAnterior) {
      cerrarProgramacion(programacionIdAnterior, seccionAnterior, btnAnterior, datos);
    }
  }

  // Función auxiliar para abrir programación
  function abrirProgramacion(programacionId, seccion, boton, datos, preservarDatos = false) {
    // Mostrar la sección
    seccion.classList.add("activo");
    boton.textContent = "Cerrar asientos";
    const id_destino = boton.dataset.destino;

    // Generar los asientos dinámicamente
    generarAsientosExpandible(programacionId, id_destino);

    // Actualizar el estado
    datos.programacion = programacionId;

    // Solo limpiar asientos si NO estamos preservando datos
    if (!preservarDatos) {
      datos.asientosSeleccionados = []; // Limpiar selección anterior solo en uso normal
    }

    compraStorage.actualizar(datos);
    programacionAbiertaId = programacionId;

  }

  // Actualizar informacion de seleccion
  function actualizarInformacionSeleccion(datos) {
    const totalSeleccion = document.getElementById(`total-${datos.programacion}`);
    const mensajeSeleccion = document.getElementById(`mensaje-${datos.programacion}`);
    const btnContinuar = document.getElementById(`continuar-${datos.programacion}`);
    const totalPrecio = totalSeleccion.querySelector('.total-precio');
    const asientosSeleccionados = totalSeleccion.querySelector('.asientos-seleccionados');

    const precioTotal = datos.sumaTotal.toFixed(2);
    totalPrecio.textContent = `S/. ${precioTotal}`;

    // Mostrar los números de los asientos seleccionados
    const numerosAsientos = datos.asientosSeleccionados.map(a => a.numAsiento.trim()).join(', ');
    asientosSeleccionados.textContent = numerosAsientos || "0";

    if (datos.asientosSeleccionados.length > 0) {
      totalSeleccion.style.display = "block";
      mensajeSeleccion.style.display = "none";
      btnContinuar.disabled = false;
      btnContinuar.textContent = "Continuar con la compra";
    } else {
      totalSeleccion.style.display = "none";
      mensajeSeleccion.style.display = "block";
      btnContinuar.disabled = true;
      btnContinuar.textContent = "Elige al menos 1 asiento";
    }
  }

  function restaurarInfoSeleccionDesdeStorage() {
    const datos = compraStorage.valor;

    if (datos.asientosSeleccionados && datos.asientosSeleccionados.length > 0) {
      datos.sumaTotal = datos.asientosSeleccionados.reduce((acc, a) => acc + parseFloat(a.precioAsiento || 0), 0);
      actualizarInformacionSeleccion(datos);
    }
  }

  function cerrar_programacion(programacionId) {
    const seccionAsientos = document.getElementById(`asientos-${programacionId}`);
    if (seccionAsientos) seccionAsientos.classList.remove("activo");

    const contenedor = document.getElementById(`contenedor-asientos-${programacionId}`);
    if (contenedor) contenedor.innerHTML = ""; // Limpiar DOM

    compraStorage.actualizar({
      programacion: null,
      asientosSeleccionados: []
    });
  }

  // Función para generar asientos reales en la vista expandible
  async function generarAsientosExpandible(programacionId, id_destino) {
    let superParentVehiculo = document.querySelector(`#contenedor-asientos-${programacionId}`);
    superParentVehiculo.innerHTML = "";

    let formData = new FormData();
    formData.set('id_programacion', programacionId);
    formData.set('id_terminal_destino', id_destino);

    try {
      const response = await fetch(_URL_VENTA_WEB + "carrito/getVehiculo", {
        method: "POST",
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        const datos = data.message;

        superParentVehiculo.style.position = "relative";
        superParentVehiculo.style.overflowX = "auto";
        superParentVehiculo.style.width = "100%";
        superParentVehiculo.classList.add(
          "d-flex",
          "flex-column",
          "flex-md-row",
          "gap-3",
          "align-items-center",
          "align-items-md-start"
        );
        // Generar pisos
        // Generar pisos
        for (let i = 1; i <= datos.vehiculo.num_piso; i++) {

          superParentVehiculo.insertAdjacentHTML('beforeend', `
          <div class="piso-container d-flex flex-column align-items-center" 
              style="flex: 0 0 auto; min-width: 300px;">

              <div class="piso-label mb-2">
                  PISO ${i}
              </div>

              ${i === 1 ? `
                  <div class="parte-delantera-bus">
                      <img 
                          src="${_URL_}../img/venta_web/bus_frontal.png"
                          alt="Parte delantera del bus"
                      >
                  </div>
              ` : ''}

              <div class="position-relative parent_vehiculo ${i != 1 ? 'vehiculo_segundoPiso' : ''}" 
                  floor="${i}" 
                  style="width:300px;">
              </div>

          </div>
          `);

          let altura;

          if (i === 1) {
            altura = datos.vehiculo.height || "300px";
          } else if (i === 2) {
            altura = datos.vehiculo.height_piso2 || datos.vehiculo.height || "300px";
          } else {
            altura = datos.vehiculo.height || "300px";
          }

          $(superParentVehiculo)
            .find(`.parent_vehiculo[floor="${i}"]`)
            .css("height", altura);
        }

        // Crear objetos del vehículo (asientos, puertas, etc.)
        Object.values(datos.objs_vehiculo).forEach((e, i, array) => {
          e.id_programacion = programacionId;
          crear_objeto(e);
        });

        // Agregar inmediatamente después:
        setTimeout(() => {
          restaurarInfoSeleccionDesdeStorage();
        }, 200);

      } else {
        throw new Error("La respuesta del servidor fue negativa.");
      }
    } catch (error) {
      console.error("Error al cargar los asientos:", error);
      Swal.fire({
        title: 'Error!',
        text: error.message || "Error al cargar los asientos",
        icon: 'error',
        confirmButtonColor: '#2a9d8f',
      });
    }
  }

  // Configuración de colores
  const COLOR_TIPO_ASIENTO = {
    'PREMIUM': '#fca311',
    'NORMAL': '#6a696b'
  };

  const COLOR_ESTADO_ASIENTO = {
    'VENDIDO': "#0394fc",
    'RESERVADO': "#912de3",
    'ANULADO': "#fc0353",
    'LIBRE': "#6a696b",
    'VENTA_WEB': "#f6bd60",
    'PROCESO_WEB': "#4FF08F",
    'SELECCIONADO': "#ff0000" // Color para asientos seleccionados actualmente
  };

  /**
   * Crea un objeto visual en el mapa del vehículo (asiento, puerta, etc)
   * @param {Object} dataObj - Datos del objeto a crear
   */
  async function crear_objeto(data_obj) {
    const dataStorage = compraStorage.valor;

    // Verificar si el asiento ya fue seleccionado previamente por el usuario
    const asientoEnStorage = (dataStorage.asientosSeleccionados && Array.isArray(dataStorage.asientosSeleccionados))
      ? dataStorage.asientosSeleccionados.find(a => Number(a.idAsiento) === Number(data_obj.id_obj_vehiculo))
      : null;

    // Corregir estado si viene como PROCESO_WEB pero es del usuario
    let estadoReal = data_obj.estado_asiento;
    if (data_obj.estado_asiento === "PROCESO_WEB" && asientoEnStorage) {
      estadoReal = null;
    }

    // Crear el elemento DOM
    const div = document.createElement("div");
    const objeto_cursorPointer = data_obj.tp_obj === "asiento" ? "cursor-pointer" : "";
    div.className = `position-absolute objeto ${objeto_cursorPointer}`;
    div.id = data_obj.id_obj_vehiculo;
    div.setAttribute("floor", data_obj.piso);
    div.setAttribute("tp_obj", data_obj.tp_obj);
    div.setAttribute("id_venta", data_obj.id_venta);
    div.setAttribute("precio", data_obj.precio);
    div.setAttribute("data-id-programacion", data_obj.id_programacion);
    if (data_obj.tp_obj === "asiento") {
      div.setAttribute("tp_asiento", data_obj.tp_asiento);
    }

    // Posicionar
    div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj}`;
    div.style.transform = `rotate(${data_obj.rotate_obj})`;

    // Determinar color base
    let color_asiento;
    if (asientoEnStorage) {
      color_asiento = COLOR_ESTADO_ASIENTO.SELECCIONADO;
    } else if (estadoReal) {
      color_asiento = COLOR_ESTADO_ASIENTO[estadoReal];
    } else if (data_obj.tp_obj === "asiento") {
      color_asiento = COLOR_TIPO_ASIENTO[data_obj.tp_asiento.toUpperCase()];
    } else {
      color_asiento = "#6a696b";
    }

    // Color del texto
    const color_text = estadoReal ? "#fff" : COLOR_TIPO_ASIENTO[data_obj.tp_asiento];
    const weight = estadoReal ? "fw-bold" : "";

    // Renderizar contenido visual
    div.insertAdjacentHTML("beforeend", `
    <i class="${data_obj.icon_obj} icon_obj ${weight}" class-icon="${data_obj.icon_obj}" style="font-style: normal; font-size:45px; color:${color_asiento}">
      <span class="position-absolute start-50 translate-middle text_obj fw-bolder mt-3" style="font-size:18px; color:${color_text}">${data_obj.text_obj}</span>
    </i>
  `);

    // Insertar en DOM
    const parent = document.querySelector(`.parent_vehiculo[floor='${data_obj.piso}']`);
    if (parent) {
      parent.appendChild(div);
    }

    // Agregar evento si corresponde
    if (data_obj.tp_obj === "asiento") {
      const estadosBloqueados = ["RESERVADO", "VENDIDO", "VENTA_WEB"];
      const debeBloquear = estadosBloqueados.includes(data_obj.estado_asiento) && !asientoEnStorage;

      if (!debeBloquear) {
        div.addEventListener("click", async (e) => {
          manejarClickAsiento(e.currentTarget);
        });
      } else {
        div.style.pointerEvents = "none";
      }
    }

    // Log de depuración
    if (asientoEnStorage) {
    }
  }

  /**
  * Maneja el evento de click en un asiento
  * @param {HTMLElement} asiento - Elemento del asiento clickeado
  */
  const asientosPendientes = new Set();

  async function manejarClickAsiento(asiento) {
    const idAsiento = asiento.id || asiento.getAttribute('data-id');
    const textObj = asiento.querySelector('.text_obj');
    const numAsiento = textObj ? textObj.textContent : asiento.textContent.trim();
    const tipoAsiento = asiento.getAttribute('tp_asiento') || asiento.getAttribute('data-tipo');
    const precioAsiento = parseFloat(asiento.getAttribute('precio') || asiento.getAttribute('data-precio') || '0');
    const programacionId = asiento.getAttribute('data-id-programacion');

    if (asientosPendientes.has(idAsiento)) return;

    const datos = compraStorage.valor;
    const index = datos.asientosSeleccionados.findIndex(a => a.idAsiento === idAsiento);

    if (index === -1) {
      // — SELECCIONAR —
      if (datos.asientosSeleccionados.length + asientosPendientes.size >= 4) {
        Swal.fire('Límite de asientos alcanzado', 'No puedes seleccionar más de 4 asientos.', 'warning');
        return;
      }

      asientosPendientes.add(idAsiento);
      mostrarLoaderAsiento(asiento);

      const disponible = await validarAsientoSeleccionado(idAsiento, programacionId);

      quitarLoaderAsiento(asiento);
      asientosPendientes.delete(idAsiento);

      if (!disponible) {
        Swal.fire('Asiento no disponible', 'El asiento ya ha sido reservado o vendido.', 'error');
        return;
      }

      if (datos.asientosSeleccionados.length === 0) {
        iniciar_temporizador();
      }

      datos.asientosSeleccionados.push({ idAsiento, numAsiento, tipoAsiento, precioAsiento });

      const icon = asiento.querySelector('.icon_obj');
      icon ? icon.style.color = COLOR_ESTADO_ASIENTO.SELECCIONADO : asiento.classList.add('seleccionado');

    } else {
      // — DESELECCIONAR —
      asientosPendientes.add(idAsiento);
      mostrarLoaderAsiento(asiento); // ✅ Feedback visual mientras espera al backend

      try {
        await eliminar_reserva(idAsiento, programacionId); // ✅ Esperar confirmación del backend
      } catch (error) {
        // ✅ Si falla la liberación, abortar y notificar
        quitarLoaderAsiento(asiento);
        asientosPendientes.delete(idAsiento);
        Swal.fire('Error', 'No se pudo liberar el asiento. Intenta de nuevo.', 'error');
        return;
      }

      quitarLoaderAsiento(asiento);
      asientosPendientes.delete(idAsiento); // ✅ Solo se libera tras confirmar el backend

      datos.asientosSeleccionados.splice(index, 1);

      const icon = asiento.querySelector('.icon_obj');
      if (icon) {
        icon.style.color = COLOR_TIPO_ASIENTO[tipoAsiento ? tipoAsiento.toUpperCase() : 'NORMAL'] || COLOR_TIPO_ASIENTO.NORMAL;
      } else {
        asiento.classList.remove('seleccionado');
      }

      if (datos.asientosSeleccionados.length === 0) {
        detener_temporizador();
      }
    }

    datos.sumaTotal = datos.asientosSeleccionados.reduce((acc, a) => acc + parseFloat(a.precioAsiento), 0);

    const container = asiento.closest('.programacion-item');
    if (container) {
      const btn = container.querySelector('button');
      if (btn) {
        datos.NombreO = btn.getAttribute('NombreO') || '';
        datos.NombreD = btn.getAttribute('NombreD') || '';
        datos.fecha_salida = btn.getAttribute('fechaIda') || '';
        datos.hora_salida = btn.getAttribute('hora_salida') || '';
        datos.Descripcion = btn.getAttribute('Descripcion') || '';
      }
    }

    actualizarInformacionSeleccion(datos);
    compraStorage.actualizar(datos);
  }

  /**
 * Valida si un asiento está disponible en el servidor
 * @param {string} idAsiento - ID del asiento a validar
 * @param {string} programacionId - ID de la programación
 * @returns {boolean} - True si el asiento está disponible, false si no
 */
  async function validarAsientoSeleccionado(idAsiento, programacionId) {
    try {
      let formData = new FormData();
      formData.set("id_programacion", programacionId);
      formData.set("id_obj_vehiculo", idAsiento);
      formData.set("estado_venta", '');

      const response = await fetch(`${_URL_}pasaje/verificar_asiento`, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      return data.success;
    } catch (error) {
      console.error('Error al verificar la disponibilidad del asiento:', error);
      Swal.fire('Error', 'No se pudo verificar el estado del asiento. Intente nuevamente.', 'error');
      return false;
    }
  }


  async function eliminar_reserva(id_asiento, id_progra) {
    try {
      const formData = new FormData();
      formData.set("id_programacion", id_progra);
      formData.set("id_obj_vehiculo", id_asiento);
      formData.set("estado_venta", '');

      const response = await fetch(`${_URL_}pasaje/liberar_asiento`, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        // Obtener el estado actual del storage
        const datos = compraStorage.valor;

        // Remover el asiento del arreglo
        datos.asientosSeleccionados = datos.asientosSeleccionados.filter(
          item => item.idAsiento !== id_asiento
        );

        // Recalcular el total
        datos.sumaTotal = datos.asientosSeleccionados.reduce(
          (acc, item) => acc + parseFloat(item.precioAsiento),
          0
        );

        // Guardar de nuevo
        compraStorage.actualizar(datos);

      } else {
        console.error(data.message || "Error al liberar el asiento.");
      }

    } catch (error) {
      console.error(error.message);
      Swal.fire('Error', 'Ocurrió un error al liberar el asiento.', 'error');
      return false;
    }
  }


  // -----------------------------------SECCION 2 DATOS DE PASAJEROS---------------------------

  /**
   * Genera formularios dinámicos para cada asiento seleccionado
   * y transiciona a la página de datos de pasajeros
   */
  async function generarFormulariosPasajero() {
    const datosStorage = compraStorage.valor;

    if (!datosStorage.asientosSeleccionados || datosStorage.asientosSeleccionados.length === 0) {
      Swal.fire('Error', 'No hay asientos seleccionados', 'error');
      return;
    }

    // 1. Ocultar elementos anteriores
    let contenedorbusqueda = document.querySelector('.search-placeholder');
    if (contenedorbusqueda) {
      contenedorbusqueda.style.display = 'none';
    }
    document.getElementById('floating-search').style.display = 'none';
    document.getElementById('contenedor-programaciones').style.display = 'none';

    // 2. Actualizar indicador de progreso al paso 3
    mostrarIndicadorProgreso(3);

    // 3. Crear contenedor principal para los formularios
    let contenedorFormularios = document.getElementById('contenedor-formularios-pasajeros');
    if (!contenedorFormularios) {
      contenedorFormularios = document.createElement('div');
      contenedorFormularios.id = 'contenedor-formularios-pasajeros';
      contenedorFormularios.className = 'py-5';

      // Insertar después del indicador de progreso
      const indicadorProgreso = document.getElementById('indicador-progreso');
      indicadorProgreso.insertAdjacentElement('afterend', contenedorFormularios);
    }

    // 4. Generar HTML dinámico
    const htmlFormularios = generarHTMLFormularios(datosStorage);

    // 5. Insertar el HTML
    contenedorFormularios.innerHTML = htmlFormularios;

    // 6. Mostrar el contenedor
    contenedorFormularios.style.display = 'block';
  }

  /**
   * Genera el HTML completo para todos los formularios de pasajeros
   */
  function generarHTMLFormularios(datosStorage) {
    const asientosSeleccionados = datosStorage.asientosSeleccionados;
    const cantidadAsientos = asientosSeleccionados.length;

    // Generar formularios individuales
    let htmlFormularios = '';
    asientosSeleccionados.forEach((asiento, index) => {
      const esPrincipal = index === 0;

      // Buscar si ya existen datos para este asiento
      const datosExistentes = buscarDatosPasajero(datosStorage, asiento.idAsiento);

      htmlFormularios += generarFormularioIndividual(asiento, index + 1, esPrincipal, datosExistentes);
    });

    // HTML completo con información del viaje
    return `
      <div class="container">
          <div class="bus-booking-container">
              <div class="bus-form-section">
                  <h2 class="mb-4">
                      <i class="fas fa-users me-2"></i>
                      Datos de Pasajeros
                  </h2>
                  
                  ${htmlFormularios}

                  <div class="bus-checkbox-group">
                     <input type="checkbox" class="bus-checkbox" id="bus-terms" required>
                      <label for="bus-terms" class="bus-checkbox-label">
                        Acepto los <a href="#" class="bus-terms-link" data-bs-toggle="modal" data-bs-target="#modalTerminosCondiciones">términos y condiciones</a> *
                      </label>
                  </div>
                  
                  <div class="bus-actions-container mt-4">
                      <button class="bus-continue-btn" id="btn-continuar-pago">
                          <i class="fas fa-credit-card"></i> Continuar al Pago
                      </button>
                  </div>
              </div>

              <div class="bus-info-section">
                  ${generarInformacionViaje(datosStorage)}
              </div>
          </div>
      </div>
  `;
  }

  /**
   * Busca los datos existentes de un pasajero por ID de asiento
   */
  function buscarDatosPasajero(datosStorage, idAsiento) {
    if (!datosStorage.datosPasajeros || !Array.isArray(datosStorage.datosPasajeros)) {
      return null;
    }

    return datosStorage.datosPasajeros.find(pasajero =>
      pasajero.asientoId === idAsiento || pasajero.asientoId === idAsiento.toString()
    );
  }

  /**
   * Genera un formulario individual para un asiento específico
   */
  function generarFormularioIndividual(asiento, numeroPasajero, esPrincipal, datosExistentes = null) {
    const badgeClass = esPrincipal ? 'bus-passenger-badge' : 'bus-passenger-badge-secondary';
    const badgeText = esPrincipal ? 'Principal' : 'Adicional';

    // Función helper para obtener valor o vacío
    const getValue = (campo) => datosExistentes ? (datosExistentes[campo] || '') : '';
    const getSelected = (campo, valor) => datosExistentes && datosExistentes[campo] === valor ? 'selected' : '';

    return `
      <div class="bus-passenger-card" data-asiento-id="${asiento.idAsiento}">
          <div class="bus-passenger-title">
              <h3>Pasajero ${numeroPasajero}</h3>
              <span class="${badgeClass}">${badgeText}</span>
          </div>

          <div class="bus-seat-selection">
              <div class="bus-seat-btn bus-seat-selected">
                  <i class="fas fa-chair"></i> 
                  Asiento ${asiento.numAsiento}
                  <span class="bus-seat-price">S/. ${asiento.precioAsiento.toFixed(2)}</span>
              </div>
              ${asiento.tipoAsiento ? `<div class="bus-seat-type">${asiento.tipoAsiento}</div>` : ''}
          </div>

          <div class="bus-form-grid">
              <div class="bus-form-group">
                  <label class="bus-form-label">Tipo de Documento *</label>
                  <select class="bus-form-select" name="tipoDocumento" required>
                      <option value="1" ${getSelected('tipoDocumento', '1')}>DNI</option>
                      <option value="7" ${getSelected('tipoDocumento', '7')}>Pasaporte</option>
                      <option value="4" ${getSelected('tipoDocumento', '4')}>Carné de Extranjería</option>
                  </select>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Número de Documento *</label>
                  <div class="bus-search-input">
                      <input type="text" class="bus-form-input" name="numeroDocumento" 
                      placeholder="Ingresa tu documento" value="${getValue('numeroDocumento')}" required>
                      <button class="btn-buscar-pasajero"><i class="fas fa-search"></i></button>
                  </div>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Nombres *</label>
                  <input type="text" class="bus-form-input" name="nombres" 
                         placeholder="Nombres completos" value="${getValue('nombres')}" required>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Apellidos *</label>
                  <input type="text" class="bus-form-input" name="apellidos" 
                         placeholder="Apellidos completos" value="${getValue('apellidos')}" required>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Fecha de Nacimiento *</label>
                  <div class="bus-date-input">
                      <input type="date" class="bus-form-input" name="fechaNacimiento" 
                             value="${getValue('fechaNacimiento')}" required>
                      <i class="fas fa-calendar-alt"></i>
                  </div>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Género *</label>
                  <select class="bus-form-select" name="genero" required>
                      <option value="">Seleccionar...</option>
                      <option value="MASCULINO" ${getSelected('genero', 'MASCULINO')}>Masculino</option>
                      <option value="FEMENINO" ${getSelected('genero', 'FEMENINO')}>Femenino</option>
                      <option value="OTRO" ${getSelected('genero', 'OTRO')}>Otro</option>
                  </select>
              </div>
              
              ${esPrincipal ? `
              <div class="bus-form-group">
                  <label class="bus-form-label">Correo Electrónico *</label>
                  <input type="email" class="bus-form-input" name="email" 
                         placeholder="tu@email.com" value="${getValue('email')}" required>
              </div>
              
              <div class="bus-form-group">
                  <label class="bus-form-label">Teléfono *</label>
                  <input type="tel" class="bus-form-input" name="telefono" 
                         placeholder="999 999 999" value="${getValue('telefono')}" required maxlength="9">
              </div>
              ` : ''}
          </div>
      </div>
  `;
  }

  /**
  * Genera la información del viaje en el panel lateral
  */
  function generarInformacionViaje(datosStorage) {
    const fechaActual = new Date().toLocaleDateString('es-PE');
    const cantidadAsientos = datosStorage.asientosSeleccionados.length;

    return `
        <div class="bus-trip-info">
            <h3 class="bus-trip-header">
                <i class="fas fa-info-circle"></i>
                Información de Viaje
            </h3>

            <div class="bus-route">
                <div class="bus-route-point">
                    <div class="bus-route-city">${datosStorage.NombreO || 'ORIGEN'}</div>
                    <small class="bus-detail-label">Origen</small>
                </div>
                <i class="fas fa-arrow-right bus-route-arrow"></i>
                <div class="bus-route-point">
                    <div class="bus-route-city">${datosStorage.NombreD || 'DESTINO'}</div>
                    <small class="bus-detail-label">Destino</small>
                </div>
            </div>

            <div class="bus-trip-details">
                <div class="bus-detail-item">
                    <span class="bus-detail-label">Precio Total</span>
                    <span class="bus-detail-value bus-price">S/. ${datosStorage.sumaTotal.toFixed(2)}</span>
                </div>
                
                <div class="bus-detail-item">
                    <span class="bus-detail-label">Pasajeros</span>
                    <span class="bus-detail-value">
                        <i class="fas fa-user"></i> ${cantidadAsientos} asiento${cantidadAsientos > 1 ? 's' : ''}
                    </span>
                </div>
                
                <div class="bus-detail-item">
                    <span class="bus-detail-label">Servicio</span>
                    <span class="bus-service-badge">
                        <i class="fas fa-star"></i> ${datosStorage.Descripcion || 'ESTÁNDAR'}
                    </span>
                </div>
                
                <div class="bus-detail-item">
                    <span class="bus-detail-label">Fecha</span>
                    <div class="bus-detail-value">${fechaActual}</div>
                </div>
            </div>

            <div class="bus-selected-seats">
                <h4>Asientos Seleccionados:</h4>
                <div class="bus-seats-list">
                    ${datosStorage.asientosSeleccionados.map(asiento => `
                        <div class="bus-seat-item">
                            <span class="bus-seat-number">${asiento.numAsiento}</span>
                            <span class="bus-seat-price">S/. ${asiento.precioAsiento.toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>

        <div class="bus-action-buttons">
            <button class="bus-btn-secondary" id="btn-volver-asientos">
               <i class="fas fa-arrow-left"></i> Volver a Selección de Asientos
            </button>
            <button class="bus-btn-secondary" id="btn-cancelar-compra">
                <i class="fas fa-times"></i> Cancelar Compra
            </button>
        </div>
    `;
  }

  /**
   * Valida todos los formularios de pasajeros generados
   * @returns {boolean} - True si todos los formularios son válidos
   */
  function validarDatosPersonales() {
    const formularios = document.querySelectorAll('.bus-passenger-card');
    let todosValidos = true;
    let errores = [];

    if (formularios.length === 0) {
      Swal.fire({
        title: 'Error',
        text: 'No se encontraron formularios de pasajeros.',
        icon: 'error',
        confirmButtonColor: '#2a9d8f',
      });
      return false;
    }

    // Validar checkbox de términos y condiciones (solo en el primer formulario)
    const checkbox = document.getElementById('bus-terms');
    if (checkbox && !checkbox.checked) {
      Swal.fire({
        title: 'Términos y Condiciones',
        text: 'Debes aceptar los términos y condiciones para continuar.',
        icon: 'warning',
        confirmButtonColor: '#2a9d8f',
      });
      return false;
    }

    // Validar cada formulario individualmente
    formularios.forEach((formulario, index) => {
      const numeroPasajero = index + 1;
      const asientoId = formulario.getAttribute('data-asiento-id');
      const asientoNum = formulario.querySelector('.bus-seat-btn .fas').nextSibling.textContent.trim();

      // Obtener todos los campos requeridos de este formulario
      const camposRequeridos = formulario.querySelectorAll('.bus-form-input[required], .bus-form-select[required]');
      let formularioValido = true;
      let camposVacios = [];

      camposRequeridos.forEach(campo => {
        // Resetear estilos
        campo.style.borderColor = '#e2e8f0';
        campo.classList.remove('error');

        if (!campo.value.trim()) {
          campo.style.borderColor = '#e53e3e';
          campo.classList.add('error');
          formularioValido = false;

          // Obtener el nombre del campo para el error
          const label = formulario.querySelector(`label[for="${campo.name}"]`) ||
            formulario.querySelector(`label`).textContent;
          camposVacios.push(label || campo.name);
        }
      });

      // Validaciones específicas por tipo de campo
      const validacionesEspecificas = validarCamposEspecificos(formulario);
      if (!validacionesEspecificas.valido) {
        formularioValido = false;
        errores.push(...validacionesEspecificas.errores);
      }

      if (!formularioValido) {
        todosValidos = false;
        errores.push(`Pasajero ${numeroPasajero} (${asientoNum}): Campos incompletos o inválidos`);
      }
    });

    // Mostrar errores si existen
    if (!todosValidos) {
      const mensajeError = errores.length > 0 ?
        `Se encontraron los siguientes errores:\n\n• ${errores.join('\n• ')}` :
        'Por favor, completa todos los campos requeridos correctamente.';

      Swal.fire({
        title: 'Formularios incompletos',
        html: `<div style="text-align: left; font-size: 14px;">${mensajeError.replace(/\n/g, '<br>')}</div>`,
        icon: 'warning',
        confirmButtonColor: '#2a9d8f',
      });

      // Scroll al primer campo con error
      const primerCampoError = document.querySelector('.bus-form-input.error, .bus-form-select.error');
      if (primerCampoError) {
        primerCampoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        primerCampoError.focus();
      }
    }

    return todosValidos;
  }

  /**
   * Valida campos específicos con reglas personalizadas
   * @param {HTMLElement} formulario - El formulario a validar
   * @returns {Object} - Objeto con validez y errores
   */
  function validarCamposEspecificos(formulario) {
    const errores = [];
    let valido = true;

    // Validar DNI
    const tipoDoc = formulario.querySelector('[name="tipoDocumento"]').value;
    const numDoc = formulario.querySelector('[name="numeroDocumento"]').value;

    if (tipoDoc === 'dni' && numDoc) {
      if (!/^\d{8}$/.test(numDoc)) {
        errores.push('El DNI debe tener exactamente 8 dígitos');
        valido = false;
      }
    }

    // Validar email (solo en pasajero principal)
    const emailField = formulario.querySelector('[name="email"]');
    if (emailField && emailField.value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(emailField.value)) {
        errores.push('El email no tiene un formato válido');
        emailField.style.borderColor = '#e53e3e';
        valido = false;
      }
    }

    // Validar teléfono (solo en pasajero principal)
    const telefonoField = formulario.querySelector('[name="telefono"]');
    if (telefonoField && telefonoField.value) {
      const telefono = telefonoField.value.replace(/\D/g, ''); // Solo números
      if (telefono.length < 9) {
        errores.push('El teléfono debe tener al menos 9 dígitos');
        telefonoField.style.borderColor = '#e53e3e';
        valido = false;
      }
    }

    // Validar fecha de nacimiento
    const fechaNacimiento = formulario.querySelector('[name="fechaNacimiento"]').value;
    if (fechaNacimiento) {
      const fechaNac = new Date(fechaNacimiento);
      const fechaActual = new Date();
      const edad = Math.floor((fechaActual - fechaNac) / (365.25 * 24 * 60 * 60 * 1000));

      if (edad < 0 || edad > 120) {
        errores.push('La fecha de nacimiento no es válida');
        valido = false;
      }
    }

    return { valido, errores };
  }

  /**
   * Guarda todos los datos de pasajeros en localStorage
   * Asocia cada pasajero con su asiento correspondiente
   */
  function guardarDatosPersonales() {
    const datosStorage = compraStorage.valor;
    const formularios = document.querySelectorAll('.bus-passenger-card');
    let datosPasajeros = [];

    formularios.forEach((formulario, index) => {
      const asientoId = formulario.getAttribute('data-asiento-id');
      const esPrincipal = index === 0;

      // Buscar el asiento correspondiente en los datos almacenados
      const asientoInfo = datosStorage.asientosSeleccionados.find(a => a.idAsiento === asientoId);

      if (!asientoInfo) {
        console.error(`No se encontró información del asiento ${asientoId}`);
        return;
      }

      // Recopilar datos del formulario
      const datosPasajero = {
        asientoId: asientoId,
        numeroAsiento: asientoInfo.numAsiento.trim(),
        tipoAsiento: asientoInfo.tipoAsiento,
        precioAsiento: asientoInfo.precioAsiento,

        // Información del pasajero
        numeroPasajero: index + 1,
        esPrincipal: esPrincipal,
        tipoDocumento: formulario.querySelector('[name="tipoDocumento"]').value,
        numeroDocumento: formulario.querySelector('[name="numeroDocumento"]').value,
        nombres: formulario.querySelector('[name="nombres"]').value,
        apellidos: formulario.querySelector('[name="apellidos"]').value,
        fechaNacimiento: formulario.querySelector('[name="fechaNacimiento"]').value,
        genero: formulario.querySelector('[name="genero"]').value,

        // Información de contacto (solo para pasajero principal)
        email: esPrincipal ? formulario.querySelector('[name="email"]').value : null,
        telefono: esPrincipal ? formulario.querySelector('[name="telefono"]').value : null,

        fechaGuardado: new Date().toISOString()
      };

      datosPasajeros.push(datosPasajero);
    });

    // Guardar en el storage
    datosStorage.datosPasajeros = datosPasajeros;
    datosStorage.datosPersonalesCompletos = true;
    datosStorage.fechaUltimaActualizacion = new Date().toISOString();

    // Actualizar el storage
    compraStorage.actualizar(datosStorage);

    // Mostrar confirmación
    Swal.fire({
      title: '¡Datos guardados!',
      text: `Se guardaron los datos de ${datosPasajeros.length} pasajero${datosPasajeros.length > 1 ? 's' : ''} correctamente.`,
      icon: 'success',
      confirmButtonColor: '#2a9d8f',
      timer: 2000,
      showConfirmButton: false
    });

    return datosPasajeros;
  }

  /**
   * Función auxiliar para obtener datos de un pasajero específico
   * @param {string} asientoId - ID del asiento
   * @returns {Object|null} - Datos del pasajero o null si no existe
   */
  function obtenerDatosPasajero(asientoId) {
    const datosStorage = compraStorage.valor;
    if (!datosStorage.datosPasajeros) return null;

    return datosStorage.datosPasajeros.find(p => p.asientoId === asientoId) || null;
  }

  /**
   * Función auxiliar para obtener todos los datos de pasajeros
   * @returns {Array} - Array con todos los datos de pasajeros
   */
  function obtenerTodosDatosPasajeros() {
    const datosStorage = compraStorage.valor;
    return datosStorage.datosPasajeros || [];
  }

  /**
   * Función auxiliar para limpiar datos de pasajeros
   */
  function limpiarDatosPasajeros() {
    const datosStorage = compraStorage.valor;
    datosStorage.datosPasajeros = [];
    datosStorage.datosPersonalesCompletos = false;
    compraStorage.actualizar(datosStorage);
  }

  /**
   * Función para validar si todos los datos están completos antes de proceder al pago
   * @returns {boolean} - True si todos los datos están completos
   */
  function validarDatosCompletos() {
    const datosStorage = compraStorage.valor;

    // Verificar que hay asientos seleccionados
    if (!datosStorage.asientosSeleccionados || datosStorage.asientosSeleccionados.length === 0) {
      Swal.fire('Error', 'No hay asientos seleccionados', 'error');
      return false;
    }

    // Verificar que hay datos de pasajeros
    if (!datosStorage.datosPasajeros || datosStorage.datosPasajeros.length === 0) {
      Swal.fire('Error', 'No hay datos de pasajeros guardados', 'error');
      return false;
    }

    // Verificar que la cantidad de pasajeros coincide con los asientos
    if (datosStorage.datosPasajeros.length !== datosStorage.asientosSeleccionados.length) {
      Swal.fire('Error', 'La cantidad de pasajeros no coincide con los asientos seleccionados', 'error');
      return false;
    }

    return true;
  }


  // FUNCIONES PARA MANEJAR EVENTOS DE PAGO
  async function mostrarSeccionPagos() {
    const datosStorage = compraStorage.valor;

    // 1. Ocultar elementos anteriores
    let contenedorbus = document.querySelector('.search-placeholder');
    if (contenedorbus) {
      contenedorbus.style.display = 'none';
    }
    document.getElementById('floating-search').style.display = 'none';

    // 1. Ocultar elementos anteriores
    const contenedorForm = document.getElementById('contenedor-formularios-pasajeros');
    if (contenedorForm) {
      contenedorForm.style.display = 'none';
    }

    const contenedorPagos = document.getElementById('contenedor-pagos');
    if (contenedorPagos) {
      contenedorPagos.classList.remove('d-none');
    }
    // 2. Actualizar indicador de progreso al paso 3
    mostrarIndicadorProgreso(4);

    // Generar el html de informacion de la seccion de pagos 
    generarInformacionPagos(datosStorage);

    // Generar la informacion de los pasajeros
    generarInformacionPasajeros(datosStorage);

    // Generar la informacion del precio
    generarInformacionPrecio(datosStorage);

  }

  /**
* Genera la información del viaje en el panel lateral
*/
  function generarInformacionPagos(datosStorage) {
    let contenedor_info_general = document.getElementById('informacion_general_pago');

    let htmlInfo = `
        <h3 class="bus-summary-header">
          <i class="fas fa-route"></i>
          Resumen del Viaje
        </h3>

        <div class="bus-trip-route">
          <div class="bus-route-point">
            <div class="bus-route-city">${datosStorage.NombreO || 'ORIGEN'}</div>
            <div class="bus-route-label">Origen</div>
          </div>
          <i class="fas fa-arrow-right bus-route-arrow"></i>
          <div class="bus-route-point">
            <div class="bus-route-city">${datosStorage.NombreD || 'DESTINO'}</div>
            <div class="bus-route-label">Destino</div>
          </div>
        </div>

        <div style="margin-bottom: 15px;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span style="color: #718096; font-size: 14px;">Fecha:</span>
            <span style="color: #2d3748; font-weight: 600;">${datosStorage.fecha_salida || 'Fecha'}</span>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span style="color: #718096; font-size: 14px;">Hora:</span>
            <span style="color: #2d3748; font-weight: 600;">${datosStorage.hora_salida || 'Fecha'} hrs</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: #718096; font-size: 14px;">Servicio:</span>
            <span
              style="background: #48bb78; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${datosStorage.Descripcion || 'ESTÁNDAR'}</span>
          </div>
        </div>
        `;

    // Insertar el html en el conetenedor
    contenedor_info_general.innerHTML = htmlInfo;
  }

  /**
   * Genera la información del viaje en el panel lateral
  */
  function generarInformacionPasajeros() {
    const pasajeros = obtenerTodosDatosPasajeros();

    let contenedor_pasajeros = document.getElementById('informacion_pasajeros');

    // Recorrer cada uno de los pasajeros y generar su información
    let htmlInfoPasajero = pasajeros.map((pasajero, index) => {
      return `
        <div class="bus-passenger-info">
          <div class="bus-passenger-name">${pasajero.nombres} ${pasajero.apellidos}</div>
          <div class="bus-passenger-details">
            <div>DNI: ${pasajero.numeroDocumento} • Asiento ${pasajero.numeroAsiento}</div>
            <div>${pasajero.email || ' '} • ${pasajero.telefono || ' '}</div>
          </div>
        </div>
      `;
    });

    // Insertar el html en el conetenedor
    contenedor_pasajeros.innerHTML = `        
        <h3 class="bus-summary-header">
          <i class="fas fa-users"></i>
          Pasajeros
        </h3>` + htmlInfoPasajero;
  }


  /**
    * Genera la información del viaje en el panel lateral
  */
  function generarInformacionPrecio(datosStorage) {
    // Obtener el precio total
    const precio_total = datosStorage.sumaTotal;

    let contenedor_precio = document.getElementById('informacion_precio');

    // Recorrer cada uno de los pasajeros y generar su información
    let htmlInfoPrecio = `
        <h3 class="bus-summary-header">
          <i class="fas fa-calculator"></i>
          Desglose de Precios
        </h3>

        <div class="bus-price-breakdown d-none">
          <div class="bus-price-item">
            <span class="bus-price-label">Pasaje (1 persona)</span>
            <span class="bus-price-value">S/. 10.00</span>
          </div>
        </div>

        <div class="bus-total-price">
          <div class="bus-price-item">
            <span class="bus-price-label" style="font-size: 18px; font-weight: 600; color: #2d3748;">Total a
              Pagar</span>
            <span class="bus-price-value">S/. ${precio_total}</span>
          </div>
        </div>
      `;

    // Insertar el html en el conetenedor
    contenedor_precio.innerHTML = htmlInfoPrecio;
    // Marcar el checkbox de comprador
    setTimeout(() => {
      checkbox_comprador.checked = true;
      checkbox_comprador.dispatchEvent(new Event('change', { bubbles: true }));
    }, 500);
  }

  // Controlar los checkbox de términos y condiciones
  checkbox_factura.addEventListener("change", function () {
    if (this.checked) {
      document.getElementById("datos_facturacion").classList.remove("d-none");
      // Aquí puedes ejecutar el código que quieras cuando se marque
      document.getElementById("ruc_factura").focus();
    } else {
      document.getElementById("datos_facturacion").classList.add("d-none");
      // Limpiar los campos de datos de facturación
      document.getElementById("ruc_factura").value = '';
      document.getElementById("razon_social_factura").value = '';
    }
  });

  let inputRuc = document.getElementById("ruc_factura");
  let razon_social_factura = document.getElementById("razon_social_factura");
  let direccion_factura = document.getElementById("direccion_factura");
  let estado_factura = document.getElementById("estado_factura");
  let condicion_factura = document.getElementById("condicion_factura");
  let ubigeo_factura = document.getElementById("ubigeo_factura");

  inputRuc.addEventListener("input", function () {
    const ruc = this.value.trim();

    // Validar que tenga exactamente 11 números
    const soloNumeros = /^\d{11}$/;

    if (soloNumeros.test(ruc)) {
      let formData = new FormData();
      formData.set('docu', ruc);
      fetch(_URL_ + 'api/sunat', {
        method: 'POST',
        body: formData
      })
        .then(response => {
          if (!response.ok) throw new Error('Error: ' + response.status);
          return response.json();
        })
        .then(data => {
          if (data.success) {
            direccion_factura.value = data.data.direccion
            ubigeo_factura.value = data.data.ubigeo[2]
            estado_factura.value = data.data.estado
            condicion_factura.value = data.data.condicion
            razon_social_factura.value = data.data.nombre_o_razon_social
          } else {
            Swal.fire({
              title: 'Atencion!',
              text: 'No se pudo obtener la información del ruc ingresado, ingrese un ruc valido',
              icon: 'info',
            });
          }

        })
        .catch(error => {
          console.error('❌ Error:', error);
        });
    }

  });

  // Controlar checkbox de datos del comprador
  checkbox_comprador.addEventListener("change", function () {
    const datosCompradorDiv = document.getElementById("datos_comprador");
    const numeroDocumentoInput = document.getElementById("numero_documento_comprador");
    const nombresInput = document.getElementById("nombres_comprador");
    const apellidosInput = document.getElementById("apellidos_comprador");
    const telefonoInput = document.getElementById("telefono_comprador");
    const correoInput = document.getElementById("correo_comprador");

    if (this.checked) {
      datosCompradorDiv.classList.remove("d-none");

      const datosStorage = compraStorage.valor;
      const pasajeros = (datosStorage && datosStorage.datosPasajeros) ? datosStorage.datosPasajeros : [];

      if (pasajeros.length === 0) {
        Swal.fire({
          title: 'Atención',
          text: 'No se han encontrado pasajeros.',
          icon: 'info',
          confirmButtonText: 'Aceptar',
          background: '#fff',
          color: '#212529',
          confirmButtonColor: '#ffc107',
          customClass: {
            popup: 'rounded shadow',
            title: 'fs-4',
            confirmButton: 'btn btn-primary'
          }
        });
        return;
      }

      const primerPasajero = pasajeros[0];

      numeroDocumentoInput.value = primerPasajero.numeroDocumento || '';
      nombresInput.value = primerPasajero.nombres || '';
      apellidosInput.value = primerPasajero.apellidos || '';
      telefonoInput.value = primerPasajero.telefono || '';
      correoInput.value = primerPasajero.email || '';

      numeroDocumentoInput.disabled = true;
      nombresInput.disabled = true;
      apellidosInput.disabled = true;
      telefonoInput.disabled = true;
      correoInput.disabled = true;

    } else {

      numeroDocumentoInput.value = '';
      nombresInput.value = '';
      apellidosInput.value = '';
      telefonoInput.value = '';
      correoInput.value = '';

      numeroDocumentoInput.disabled = false;
      nombresInput.disabled = false;
      apellidosInput.disabled = false;
      telefonoInput.disabled = false;
      correoInput.disabled = false;
    }
  });


  // Función para obtener los datos del formulario
  function obtenerDatosPagante() {
    return {
      numeroDocumento: document.getElementById("numero_documento_comprador").value.trim(),
      nombres: document.getElementById("nombres_comprador").value.trim(),
      apellidos: document.getElementById("apellidos_comprador").value.trim(),
      telefono: document.getElementById("telefono_comprador").value.trim(),
      correo: document.getElementById("correo_comprador").value.trim()
    };
  }

  // Función para validar los datos
  function validarCamposRequeridos(datos) {
    for (const key in datos) {
      if (!datos[key]) {
        Swal.fire({
          title: 'Error',
          text: `El campo ${key.replace('_', ' ')} es obligatorio.`,
          icon: 'error',
          confirmButtonColor: '#2a9d8f',
        });
        return false;
      }
    }
    return true;
  }

  // Función para guardar en storage
  function guardarEnStorage(datosPagante) {
    const datosStorage = compraStorage.valor;
    datosStorage.datosPagante = datosPagante;
    compraStorage.actualizar(datosStorage);
  }

  // Función principal que valida y guarda (reemplaza a ambas funciones anteriores)
  function validarYGuardarDatosPagante(mostrarConfirmacion = false) {
    const datosPagante = obtenerDatosPagante();

    // Validar campos requeridos
    if (!validarCamposRequeridos(datosPagante)) {
      return false;
    }

    // Guardar datos del pagante en el storage
    guardarEnStorage(datosPagante);


    // Mostrar confirmación solo si se solicita
    if (mostrarConfirmacion) {
      Swal.fire({
        title: '¡Datos guardados!',
        text: 'Se guardaron los datos del pagante correctamente.',
        icon: 'success',
        confirmButtonColor: '#2a9d8f',
        timer: 2000,
        showConfirmButton: false
      });
    }

    return true;
  }

  // FUNCIONES PARA INICIAR EL PROCESO DE PAGO

  // ===== INTEGRACIÓN CON TU CÓDIGO EXISTENTE =====

  // Configuración personalizada para tu aplicación (mantén tu configuración actual)
  const culqiConfig = {
    settings: {
      title: 'Venta de Pasajes',
      currency: 'PEN',
    },
    appearance: {
      theme: "default",
      buttonCardPayText: "Pagar Pasaje",
      defaultStyle: {
        bannerColor: "#007bff",
        buttonBackground: "#28a745",
        menuColor: "#f8f9fa",
        linksColor: "#007bff",
        buttonTextColor: "#ffffff",
        priceColor: "#28a745",
      },
    },
    paymentMethods: {
      tarjeta: true,
      yape: true,
      billetera: false, // Deshabilitamos los que no usarás
      bancaMovil: false,
      agente: false,
      cuotealo: false,
    }
  };

  // Crear instancia global del manager de pagos (mantén tu clave)
  const publicKey = 'pk_test_OoprsleXqxamzyOn';
  let paymentManager = new CulqiPaymentManager(publicKey, culqiConfig);


  // ===== INICIALIZACIÓN DE EVENTOS DE LA INTERFAZ =====
  // document.addEventListener('DOMContentLoaded', function () {
  //   initializePaymentInterface();
  // });

  // function initializePaymentInterface() {
  //   console.log('Hola mundo!!')
  //   // Event listeners para los métodos de pago
  //   const paymentOptions = document.querySelectorAll('.bus-payment-option:not(.bus-disabled)');
  //   const processButton = document.getElementById('btn-procesar-pago');

  //   // Manejar selección de métodos de pago
  //   paymentOptions.forEach(option => {
  //     option.addEventListener('click', function () {
  //       // Remover selección anterior
  //       paymentOptions.forEach(opt => opt.classList.remove('bus-selected'));

  //       // Seleccionar método actual
  //       this.classList.add('bus-selected');
  //       selectedPaymentMethod = this.dataset.paymentMethod;

  //       // Habilitar botón de pago
  //       processButton.disabled = false;

  //       // Actualizar texto del botón
  //       updatePaymentButtonText();

  //       console.log('Método de pago seleccionado:', selectedPaymentMethod);
  //     });
  //   });

  //   // Event listener para el botón de procesar pago
  //   if (processButton) {
  //     processButton.addEventListener('click', function () {
  //       if (!selectedPaymentMethod) {
  //         if (typeof Swal !== 'undefined') {
  //           Swal.fire({
  //             icon: 'warning',
  //             title: 'Método no seleccionado',
  //             text: 'Por favor, seleccione un método de pago.'
  //           });
  //         }
  //         return;
  //       }

  //       // Usar tu función existente con mejoras
  //       iniciar_proceso_pago_con_metodo(selectedPaymentMethod);
  //     });
  //   }
  // }

  const paymentOptions = document.querySelectorAll('.bus-payment-option:not(.bus-disabled)');
  const processButton = document.getElementById('btn-pagar');
  let selectedPaymentMethod = null;

  // Si quieres iniciar con el botón deshabilitado
  if (processButton) {
    processButton.disabled = true
    processButton.innerHTML = '<i class="fas fa-lock"></i> Selecciona un método de pago';
    processButton.style.cursor = 'not-allowed';
    processButton.style.backgroundColor = '#6c757d'; // Gris deshabilitado
  };

  // Manejar selección de métodos de pago
  paymentOptions.forEach(option => {
    option.addEventListener('click', () => {

      // Quitar selección de todas
      paymentOptions.forEach(opt => opt.classList.remove('bus-selected'));

      // Marcar la opción actual
      option.classList.add('bus-selected');

      // Guardar método de pago
      selectedPaymentMethod = option.dataset.paymentMethod || option.id;

      // Habilitar botón de pago
      if (processButton) {
        processButton.disabled = false;
        if (typeof updatePaymentButtonText === 'function') {
          updatePaymentButtonText();
        }
      }
      processButton.style.cursor = 'pointer';

      console.log('Método de pago seleccionado:', selectedPaymentMethod);
    });
  });

  // Actualizar texto del botón según el método seleccionado
  function updatePaymentButtonText() {
    const button = document.getElementById('btn-pagar');
    if (!button) return;

    switch (selectedPaymentMethod) {
      case 'tarjeta':
        button.innerHTML = '<i class="fas fa-credit-card"></i> Pagar con Tarjeta';
        break;
      case 'yape-codigo':
        button.innerHTML = '<i class="fas fa-mobile-alt"></i> Pagar con Código Yape';
        break;
      case 'yape-qr':
        button.innerHTML = '<i class="fas fa-qrcode"></i> Pagar con QR Yape';
        break;
      default:
        button.innerHTML = '<i class="fas fa-lock"></i> Procesar Pago Seguro';
    }
  }

  // ============ FUNCIÓN PRINCIPAL PARA PROCESAR PAGOS ============

  /**
   * Función principal para iniciar el proceso de pago con método específico.
   * Unifica iniciar_proceso_pago, iniciar_proceso_pago_mejorado e iniciar_proceso_pago_con_metodo
   * en un solo punto de entrada claro.
   */
  async function iniciar_proceso_pago_con_metodo(metodo = null) {
    try {
      const metodoPago = metodo || selectedPaymentMethod || 'tarjeta';
      console.log('=== INICIANDO PROCESO DE PAGO | Método:', metodoPago, '===');

      const datosStoragess = compraStorage.valor;

      // Validar datos antes de procesar
      const errores = validarDatosPago(datosStoragess);
      if (errores.length > 0) {
        throw new Error(`Datos inválidos:\n${errores.join('\n')}`);
      }

      // ===== EXTRAER Y PROCESAR DATOS =====
      const montoTotal = Math.round(datosStoragess.sumaTotal * 100);

      if (!montoTotal || montoTotal <= 0) {
        throw new Error('El monto total debe ser mayor a 0');
      }

      const moneda = 'PEN';
      const descripcion = 'Compra de pasajes';
      const pagante = datosStoragess.datosPagante || {};
      const email = pagante.correo || '';
      const nombreComprador = pagante.nombres || 'Comprador Anónimo';
      const apellidosComprador = pagante.apellidos || '';
      const telefonoComprador = pagante.telefono || '';

      // Validar email
      if (!email) throw new Error('El email del pagante es requerido');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) throw new Error('El email no tiene un formato válido');

      // Datos de facturación
      const usaFactura = typeof checkbox_factura !== 'undefined' && checkbox_factura.checked;
      const getField = function (id) {
        var el = document.getElementById(id);
        return el && el.value ? el.value.trim() : '';
      };

      const datosFactura = usaFactura ? {
        ruc: getField('ruc_factura'),
        razonSocial: getField('razon_social_factura'),
        direccionFactura: getField('direccion_factura'),
        estadoFactura: getField('estado_factura'),
        condicionFactura: getField('condicion_factura'),
        ubigeoFactura: getField('ubigeo_factura'),
      } : null;

      // Validar facturación si aplica
      if (usaFactura) {
        if (!datosFactura.ruc || datosFactura.ruc.length !== 11) {
          throw new Error('El RUC debe tener 11 dígitos');
        }
        if (!datosFactura.razonSocial) {
          throw new Error('La razón social es requerida para facturación');
        }
      }

      // ===== REINICIALIZAR PAYMENT MANAGER =====
      reinicializarPaymentManager();

      // ===== CONFIGURAR PAYMENT MANAGER CON DATOS ACTUALIZADOS =====
      paymentManager.updateConfig({
        settings: { title: descripcion, currency: moneda, amount: montoTotal },
        client: { email },
      });

      // ===== ALMACENAR DATOS GLOBALMENTE =====
      window.datosCompraProceso = {
        nombreComprador,
        apellidosComprador,
        telefonoComprador,
        email,
        datosFactura,
        descripcion,
        datosOriginales: datosStoragess,
        montoOriginal: datosStoragess.sumaTotal,
        montoEnCentavos: montoTotal,
      };

      // ===== INICIALIZAR SEGÚN EL MÉTODO SELECCIONADO =====
      let culqiInstance = null;

      switch (metodoPago) {
        case 'tarjeta':
          culqiInstance = paymentManager.initializeCardPayment(montoTotal);
          break;
        case 'yape-codigo':
          culqiInstance = paymentManager.initializeYapeCodePayment(montoTotal);
          break;
        default:
          console.warn(`Método desconocido "${metodoPago}", usando tarjeta por defecto`);
          culqiInstance = paymentManager.initializeCardPayment(montoTotal);
      }

      if (!culqiInstance) {
        throw new Error('No se pudo inicializar Culqi correctamente');
      }

      paymentManager.open();
      return culqiInstance;

    } catch (error) {
      console.error('Error en iniciar_proceso_pago_con_metodo:', error);
      mostrarErrorPago(error.message || 'Ocurrió un error al procesar el pago');
      throw error;
    }
  }

  // ============ FUNCIONES DE COMPATIBILIDAD ============

  function iniciar_proceso_pago() {
    return iniciar_proceso_pago_con_metodo(selectedPaymentMethod || 'tarjeta');
  }

  // ============ FUNCIÓN PARA CONCRETAR LA VENTA ============
  async function concretarVentaFinal(datosPago) {
    try {
      console.log('=== CONCRETANDO VENTA FINAL ===');

      if (!datosPago.charge_id) {
        throw new Error('charge_id no recibido. No se puede concretar la venta.');
      }

      const url = $("#url_venta_web").val() + 'carrito/concretar_venta';
      const formData = new FormData();

      formData.append('charge_id', datosPago.charge_id);
      formData.append('payment_method', datosPago.payment_method || '');
      formData.append('amount', datosPago.amount || '');
      formData.append('currency', datosPago.currency || 'PEN');

      if (window.datosCompraProceso) {
        const dc = window.datosCompraProceso;
        formData.append('nombreComprador', dc.nombreComprador);
        formData.append('apellidosComprador', dc.apellidosComprador);
        formData.append('telefonoComprador', dc.telefonoComprador);
        formData.append('correoComprador', dc.email);
        formData.append('descripcion', dc.descripcion);

        if (dc.datosFactura) {
          formData.append('datosFactura', JSON.stringify(dc.datosFactura));
        }
        if (dc.datosOriginales) {
          formData.append('datosOriginales', JSON.stringify(dc.datosOriginales));
        }
      }

      if (typeof dataLocalStorage !== 'undefined') {
        Object.entries(dataLocalStorage).forEach(function (entry) {
          var key = entry[0];
          var value = entry[1];
          if (key === 'usuarios') return;
          formData.append(
            key,
            typeof value === 'object' && value !== null ? JSON.stringify(value) : value
          );
        });
      }

      const response = await fetch(url, { method: 'POST', body: formData });
      const data = await response.json();

      if (data.success) {
        return data;
      } else {
        throw new Error(data.message || 'Error al concretar la venta');
      }

    } catch (error) {
      console.error('Error concretando venta final:', error);
      throw error;
    }
  }

  // ============ VALIDACIÓN ============

  function validarDatosPago(datos) {
    const errores = [];

    if (!datos) {
      errores.push('No hay datos de compra disponibles');
      return errores;
    }

    if (!datos.sumaTotal || datos.sumaTotal <= 0) {
      errores.push('El monto total es inválido');
    }

    if (!datos.datosPagante) {
      errores.push('Los datos del pagante son requeridos');
    } else {
      if (!datos.datosPagante.correo) errores.push('El email es requerido');
      if (!datos.datosPagante.nombres) errores.push('El nombre es requerido');
      if (!datos.datosPagante.apellidos) errores.push('Los apellidos son requeridos');
    }

    if (!datos.datosPasajeros || datos.datosPasajeros.length === 0) {
      errores.push('Debe haber al menos un pasajero');
    }

    return errores;
  }

  // ============ PAYMENT MANAGER ============

  function reinicializarPaymentManager() {
    try {
      if (paymentManager) paymentManager.destroy();
      paymentManager = new CulqiPaymentManager(publicKey, culqiConfig);
    } catch (error) {
      console.error('Error reinicializando Payment Manager:', error);
      throw new Error('No se pudo reinicializar el gestor de pagos');
    }
  }

  function configurarMetodosPago(metodos) {
    if (paymentManager) {
      paymentManager.updateConfig({ paymentMethods: metodos });
    } else {
      console.warn('Payment Manager no está inicializado');
    }
  }

  function cerrarModalCulqi() {
    if (paymentManager) {
      paymentManager.close();
    } else {
      console.warn('No hay instancia de Payment Manager para cerrar');
    }
  }

  // ============ UTILIDAD ============

  function obtenerEstadoPaymentManager() {
    return {
      instanciaActiva: !!paymentManager,
      metodoSeleccionado: selectedPaymentMethod,
      configuracionActual: paymentManager ? paymentManager.config : null,
      culqiInicializado: paymentManager ? !!paymentManager.culqiInstance : false,
      metodoActual: paymentManager ? paymentManager.getCurrentPaymentMethod() : null,
    };
  }

  // ============ MENSAJES ============

  function mostrarErrorPago(mensaje) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error en el Pago', text: mensaje, confirmButtonText: 'Entendido' });
    } else {
      alert('Error en el pago: ' + mensaje);
    }
  }

  function mostrarErrorValidacion(mensaje) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'warning', title: 'Datos Incompletos', text: mensaje, confirmButtonText: 'Revisar Datos' });
    } else {
      alert('Datos incompletos: ' + mensaje);
    }
  }

  // ============ LIMPIEZA ============

  window.addEventListener('beforeunload', function () {
    if (paymentManager) paymentManager.destroy();
  });

  // ============ EXPORTAR ============

  window.iniciar_proceso_pago = iniciar_proceso_pago;
  window.iniciar_proceso_pago_con_metodo = iniciar_proceso_pago_con_metodo;
  window.cerrarModalCulqi = cerrarModalCulqi;
  window.validarDatosPago = validarDatosPago;
  window.configurarMetodosPago = configurarMetodosPago;
  window.reinicializarPaymentManager = reinicializarPaymentManager;
  window.obtenerEstadoPaymentManager = obtenerEstadoPaymentManager;
  window.concretarVentaFinal = concretarVentaFinal;
  window.mostrarErrorPago = mostrarErrorPago;
  window.mostrarErrorValidacion = mostrarErrorValidacion;

  // ============ DEBUG ============
  window.debugPayment = function () {
    console.group('=== DEBUG PAYMENT MANAGER ===');
    console.log('Método seleccionado:', selectedPaymentMethod);
    console.log('Estado:', obtenerEstadoPaymentManager());
    console.log('Datos de compra:', window.datosCompraProceso ? window.datosCompraProceso : 'no disponible');
    console.log('Storage:', compraStorage && compraStorage.valor ? compraStorage.valor : 'no disponible');
    console.log('Config actual:', paymentManager && paymentManager.config ? paymentManager.config : 'no disponible');
    console.groupEnd();
  };

  // Metodo para la recuperacion de la data y ui de la venta web
  function recuperarPaso() {
    const estado = compraStorage.obtener();

    if (estado && estado.pasoActual) {
      const paso = estado.pasoActual;
      mostrarIndicadorProgreso(paso);
      if (paso == 2) {
        loader.classList.add('d-none');
        origen.value = estado.origen || '';
        destino.value = estado.destino || '';
        actualizarOpcionesDisponibles();
        fecha_programacion.value = estado.fecha || '';
        if (estado.origen && estado.destino && estado.fecha) {
          cargarProgramaciones(estado.fecha, estado.origen, estado.destino);
          // En tu recuperación de storage, cambia esta línea:
          if (estado.programacion) {
            // Esperar 5 segundos (5000 milisegundos) antes de restaurar asientos
            setTimeout(() => {
              toggleAsientos(estado.programacion, null, true); // Tercer parámetro: preservar datos
            }, 5000);
          }
        }

        loader.classList.remove('d-none');
      }

      else if (paso == 3) {
        // Sacar toda la data necesaria
        const datosStorage = compraStorage.valor;
        if (datosStorage.asientosSeleccionados.length > 0) {
          generarFormulariosPasajero()
        }
      }

      else if (paso == 4) {
        // Mostrar la sección de pagos
        mostrarSeccionPagos();
      }

      // // Mostrar la sección/panel correspondiente al paso actual
      // mostrarPasoUI(paso);

      // if (estado.programacion) {
      //   // Renderizar visualmente la programación
      //   restaurarProgramacion(estado.programacion);
      // }

      // if (estado.asientosSeleccionados && estado.asientosSeleccionados.length > 0) {
      //   restaurarAsientos(estado.asientosSeleccionados);
      // }

      // // Si estás en el paso 2 o más, muestra el botón continuar como habilitado
      // if (paso >= 2) {
      //   habilitarBotonContinuar();
      // }
    } else {
      // Si no hay estado guardado, iniciar en paso 1
      mostrarIndicadorProgreso(1);
      // mostrarPasoUI(1);
    }
  }

  // Metodo para cancelar todo y volver a la pagina principal   
  async function cancelarCompra() {
    Swal.fire({
      title: '¿Estás seguro?',
      text: "Esta acción cancelará tu compra y perderás toda la información ingresada.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2a9d8f',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, cancelar',
      cancelButtonText: 'No, volver'
    }).then(async (result) => {
      if (result.isConfirmed) {
        const dataStor = compraStorage.valor;
        var programacionId = dataStor.programacion || null;

        // Verificar si hay asientos seleccionados y liberar cada uno
        if (dataStor.asientosSeleccionados && dataStor.asientosSeleccionados.length > 0) {
          Swal.fire({
            title: 'Cancelando compra...',
            text: 'Espere un momento por favor...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
              Swal.showLoading();
            }
          });

          try {
            // Crear array de promesas para eliminar reservas
            const promesasEliminacion = dataStor.asientosSeleccionados.map(asiento => {
              const idAsiento = (typeof asiento === 'object' && asiento.idAsiento) ? asiento.idAsiento : asiento;
              return eliminar_reserva(idAsiento, programacionId);
            });

            // Esperar a que todas las reservas se eliminen en el servidor
            await Promise.all(promesasEliminacion);

            detener_temporizador();
            compraStorage.limpiar();

            Swal.fire({
              title: 'Compra cancelada',
              text: ' Vuelva pronto...',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              // Redirigir a la página principal
              window.location.href = _URL_VENTA_WEB;
            });

          } catch (error) {
            console.error("Error al eliminar reservas:", error);

            Swal.fire({
              title: 'Advertencia',
              text: 'Hubo un problema...',
              icon: 'warning',
              confirmButtonText: 'Entendido'
            }).then(() => {
              compraStorage.limpiar();
              window.location.href = _URL_VENTA_WEB;
            });
          }
        } else {
          compraStorage.limpiar();
          window.location.href = _URL_VENTA_WEB;
        }
      }
    });
  }

  // Buscar pasajero en api
  async function buscar_pasajero(button, formGroup) {
    const tipoDocumento = formGroup.querySelector('[name="tipoDocumento"]').value.trim();
    const numeroDocumento = formGroup.querySelector('[name="numeroDocumento"]').value.trim();

    let errores = [];

    if (!tipoDocumento) errores.push("el tipo de documento");
    if (!numeroDocumento) errores.push("el número de documento");

    if (errores.length > 0) {
      Swal.fire({
        title: 'Atención!',
        text: `Falta completar ${errores.join(" y ")}.`,
        icon: 'error',
      });
      return;
    }

    if (tipoDocumento == 1) {
      if (numeroDocumento.length == 8) {
        loader.classList.remove('d-none');
        try {
          let formDatos = new FormData();

          formDatos.set("tp_docu", tipoDocumento);
          formDatos.set("docu", numeroDocumento);

          const response = await fetch(`${_URL_}api/reniec`, {
            method: 'POST',
            body: formDatos
          });

          if (!response.ok) throw new Error('Error al buscar el pasajero');

          const data = await response.json();
          formGroup.querySelector('[name="nombres"]').value = data.data.nombres || '';
          formGroup.querySelector('[name="apellidos"]').value = data.data.apellido_paterno + ' ' + data.data.apellido_materno || '';

        } catch (error) {
          Swal.fire({
            title: 'Atencion!',
            text: 'No se pudo obtener la información del pasajero',
            icon: 'info',
          });
        }
        loader.classList.add('d-none');
      } else {
        Swal.fire({
          title: 'Atencion!',
          text: 'La busqueda solo funciona para DNI de 8 digitos!! :]',
          icon: 'info',
        });
        return false;
      }
    } else {
      Swal.fire({
        title: 'Atencion!',
        text: 'La busqueda solo funciona para DNI :]',
        icon: 'info',
      });
      return false;
    }
  }

  function setupTerminalSelects() {
    const origenSelect = document.getElementById('origen');
    const destinoSelect = document.getElementById('destino');

    function updateOptions(changedSelect, otherSelect) {
      // Restaurar todas las opciones del otro select
      Array.from(otherSelect.options).forEach(option => {
        option.style.display = '';
        option.disabled = false;
      });

      // Ocultar la opción seleccionada en el otro select
      if (changedSelect.value) {
        const optionToHide = otherSelect.querySelector(`option[value="${changedSelect.value}"]`);
        if (optionToHide) {
          optionToHide.style.display = 'none';
          optionToHide.disabled = true;
        }
      }
    }

    origenSelect.addEventListener('change', () => updateOptions(origenSelect, destinoSelect));
    destinoSelect.addEventListener('change', () => updateOptions(destinoSelect, origenSelect));
  }

  // Función que puedes llamar en cualquier momento
  function actualizarOpcionesDisponibles() {
    if (!origen || !destino) return;

    // Restaurar todas las opciones
    Array.from(origen.options).forEach(option => {
      if (option.value !== '') {
        option.style.display = '';
        option.disabled = false;
      }
    });

    Array.from(destino.options).forEach(option => {
      if (option.value !== '') {
        option.style.display = '';
        option.disabled = false;
      }
    });

    // Ocultar opciones según valores actuales
    if (origen.value) {
      const optionToHide = destino.querySelector(`option[value="${origen.value}"]`);
      if (optionToHide) {
        optionToHide.style.display = 'none';
        optionToHide.disabled = true;
      }
    }

    if (destino.value) {
      const optionToHide = origen.querySelector(`option[value="${destino.value}"]`);
      if (optionToHide) {
        optionToHide.style.display = 'none';
        optionToHide.disabled = true;
      }
    }
  }

  function mostrarLoaderAsiento(asiento) {
    asiento.classList.add('cargando');

    const loader = document.createElement('div');
    loader.classList.add('loader-asiento');
    loader.innerHTML = `
    <div class="spinner-border spinner-border-sm text-danger"></div>
     `;

    asiento.appendChild(loader);
  }

  function quitarLoaderAsiento(asiento) {
    asiento.classList.remove('cargando');
    const loader = asiento.querySelector('.loader-asiento');
    if (loader) loader.remove();
  }
});

