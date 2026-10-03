import { iniciar_temporizador, detener_temporizador, actualizarTemporizador } from './temporizador.js';

document.addEventListener('DOMContentLoaded', function () {

    const _URL_ = document.querySelector("#url").value;
    const id_programacion_select = document.querySelector("#id_programacion");
    let asientosSeleccionadosData = [];
    let PrecioIndividual = [];
    let asientosSeleccionados = [];
    let asientosSeleccionadosArray = [];
    let TpAsientoArray = [];
    let sumaTotal = 0;


    let datosTab = document.getElementById("pills-datos-tab");

    // --------- CARGA DE DATOS DEL LOCALSTORAGE ---------
    if (localStorage.getItem("venta")) {
        cargarDatosLocalStorage();

        // Añade esta línea para cargar los asientos reservados
        if (localStorage.getItem("asientos_reservados")) {
            actualizarAsientosSeleccionados();
            actualizarAsientosPrecio();

            // Habilita el botón de compra si hay asientos seleccionados
            const botonComprar = document.getElementById('continue');
            const tex_sub = document.getElementById('tex-sub');

            if (botonComprar && asientosSeleccionados.length > 0) {
                botonComprar.disabled = false;
                botonComprar.classList.remove('d-none');
                tex_sub.classList.add('d-none');
            }

            // Inicia el temporizador si hay asientos seleccionados
            if (asientosSeleccionados.length > 0) {
                iniciar_temporizador();
            }
        }

        $('#spinner').removeClass('show');
    }

    /**
    * Carga datos guardados en localStorage y configura los elementos en el DOM
    */
    function cargarDatosLocalStorage() {

        const dataLocalStorage = JSON.parse(localStorage.getItem('venta'));
        const div_padreProgramacion = document.getElementById("div_padreProgramacion");
        const inputFecha = document.getElementById("fecha_programacion");

        inputFecha.value = dataLocalStorage.fechaIda;
        obtenerProgramaciones(dataLocalStorage, div_padreProgramacion);
    }

    /* MODULO DE PROGRAMACIONES*/
    /**
    * Obtiene las programaciones disponibles mediante AJAX y las renderiza en el DOM
    * @param {Object} dataLocalStorage - Datos almacenados en localStorage
    * @param {HTMLElement} contenedor - Contenedor donde se agregarán las programaciones
    */
    function obtenerProgramaciones(dataLocalStorage, contenedor) {

        $.ajax({
            type: "POST",
            url: $("#url_web").val() + "carrito/get_programaciones",
            data: {
                accion: 'getProgramaciones',
                origen: dataLocalStorage.origen,
                destino: dataLocalStorage.destino,
                fechaIda: dataLocalStorage.fechaIda
            },
            success: function (response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    renderizarProgramaciones(respuesta.message, contenedor, dataLocalStorage);
                } else {
                    console.error('Error al cargar las programaciones:', respuesta.message);
                }
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX:', error);
            }
        });
    }

    /**
  * Renderiza las programaciones en el DOM
  * @param {Array} programaciones - Lista de programaciones obtenidas
  * @param {HTMLElement} contenedor - Contenedor donde se agregarán las programaciones
  */
    function renderizarProgramaciones(programaciones, contenedor) {
        // Limpiar el contenedor antes de agregar nuevas programaciones
        if (contenedor) {
            contenedor.innerHTML = '';
        } else {
            console.error("Contenedor no encontrado");
            return;
        }

        // Verificar si hay programaciones
        if (!programaciones || programaciones.length === 0) {
            contenedor.innerHTML = '<div class="alert alert-info">No se encontraron programaciones disponibles.</div>';
            return;
        }

        // Obtener datos del localStorage para marcar programaciones activas
        const dataLocalStorage = obtenerDatosLocalStorage();
        const programacionActiva = dataLocalStorage.id_programacion;

        // Renderizar cada programación
        programaciones.forEach(programacion => {
            // Verificar propiedades requeridas
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

            // Determinar si este es el botón activo
            const esActivo = (programacionActiva === id_programacion);
            const botonEstilo = esActivo ? 'background-color: red' : 'background-color: rgb(60, 71, 165)';
            const botonTexto = esActivo ? 'CERRAR' : 'ASIENTOS';
            const botonClase = esActivo ? 'btn text-white rounded-3 p-2 mostrar-vehiculo-btn active' : 'btn text-white rounded-3 p-2 mostrar-vehiculo-btn';
            const contenidoClase = esActivo ? 'content-vehiculo d-flex row' : 'content-vehiculo d-flex row d-none';

            const programacionHTML = `
            <div class="row shadow-sm py-2 px-3 mx-5 mb-3 bg-white rounded programacion" data-id="${id_programacion}">
                <!-- Información del vehículo -->
                <section class="col-md-6 d-flex align-items-center">
                    <i class="fas fa-bus fa-x1 mx-2"></i>
                    <h4 class="mb-0">${descripcion}</h4>
                    <p class="ms-3 mb-0">Salida: <i class="fa-light fa-clock mx-2"></i> ${hora_salida}</p>
                </section>
                <!-- Precios -->
                <section class="col-md-6 d-flex justify-content-end">
                    <p class="mx-3">Total: S/. ${precio_primer_piso} <br>Asiento Individual</p>
                    <p>Total: S/. ${precio_segundo_piso} <br>Asiento Compartido</p>
                </section>
                <hr>
                <!-- Información adicional -->
                <section class="row d-flex justify-content-center py-1">
                    <div class="col-6 col-md-3 col-lg-2 text-center my-4">
                        <p class="fw-bold">Origen</p>
                        <label>${nombre_origen}</label>
                    </div>
                    <div class="col-6 col-md-3 text-center my-4">
                        <p class="fw-bold">Destino</p>
                        <label>${nombre_destino}</label>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2 text-center my-4">
                        <p class="fw-bold">Fecha de viaje</p>
                        <label>${fecha_salida}</label>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2 text-center my-4">
                        <p class="fw-bold">Asientos</p>
                        <label>${num_asientos}</label>
                    </div>
                    <!-- Botón de acción -->
                    <div class="col-md-3 col-lg-2 my-4 d-flex justify-content-center align-items-center">
                        <button class="${botonClase}" style="${botonEstilo}" 
                            data-vehiculo="${id_vehiculo}" 
                            data-programacion="${id_programacion}"
                            programacion="${id_programacion}"
                            descripcion="${descripcion}" 
                            hora_salida="${hora_salida}" 
                            NombreO="${nombre_origen}" 
                            NombreD="${nombre_destino}"
                            fechaIda="${fecha_salida}">
                            ${botonTexto}
                        </button>
                    </div>
                </section>
                <section class="${contenidoClase}">
                    ${esActivo ? '<div class="text-center w-100"><p>Cargando vehículo...</p></div>' : ''}
                </section>
            </div>
        `;
            contenedor.insertAdjacentHTML('beforeend', programacionHTML);
        });

        // Una vez que todas las programaciones están renderizadas, inicializar los botones
        inicializarBotonesVehiculo();

        // Si hay una programación activa, cargar su vehículo
        if (programacionActiva) {
            const programacionActivaElem = contenedor.querySelector(`[data-id="${programacionActiva}"]`);
            if (programacionActivaElem) {
                const botonActivo = programacionActivaElem.querySelector('.mostrar-vehiculo-btn');
                const contenidoVehiculo = programacionActivaElem.querySelector('.content-vehiculo');
                if (botonActivo && contenidoVehiculo) {
                    const vehiculoId = botonActivo.getAttribute('data-vehiculo');
                    show_vehiculo(programacionActiva, vehiculoId, botonActivo);
                }
            }
        }
    }

    /**
     * Inicializa los botones para mostrar vehículos
     */
    function inicializarBotonesVehiculo() {
        const botones = document.querySelectorAll('.mostrar-vehiculo-btn');

        // Primero, remover eventos antiguos para evitar duplicación
        botones.forEach(boton => {
            const nuevoBoton = boton.cloneNode(true);
            boton.parentNode.replaceChild(nuevoBoton, boton);
        });

        // Luego asignar nuevos eventos
        document.querySelectorAll('.mostrar-vehiculo-btn').forEach(boton => {
            boton.addEventListener('click', function () {
                const programacionId = this.getAttribute('data-programacion');
                const vehiculoId = this.getAttribute('data-vehiculo');

                if (!programacionId || !vehiculoId) {
                    console.error('Faltan atributos necesarios en el botón:', this);
                    return;
                }

                manejarClickBoton(this, programacionId, vehiculoId);
            });
        });

    }

    /**
     * Función principal para cargar programaciones
     * @param {string} fechaSalida - Fecha de salida (formato YYYY-MM-DD)
     * @param {string} idOrigen - ID del origen
     * @param {string} idDestino - ID del destino
     */
    function cargarProgramaciones(fechaSalida, idOrigen, idDestino) {
        const contenedor = document.getElementById('programaciones-container');
        if (!contenedor) {
            console.error("Contenedor de programaciones no encontrado");
            return;
        }

        // Mostrar indicador de carga
        contenedor.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

        // Preparar datos para la solicitud
        const formData = new FormData();
        formData.append('fecha_salida', fechaSalida);
        formData.append('id_origen', idOrigen);
        formData.append('id_destino', idDestino);

        // Realizar la solicitud fetch
        fetch(`${_URL_}pasaje/obtener_programaciones`, {
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
                if (data.status === 'success') {
                    // Renderizar programaciones obtenidas
                    renderizarProgramaciones(data.programaciones, contenedor);
                } else {
                    // Mostrar mensaje de error o información
                    contenedor.innerHTML = `<div class="alert alert-info">${data.message || 'No se encontraron programaciones disponibles.'}</div>`;
                }
            })
            .catch(error => {
                console.error("Error al cargar programaciones:", error);
                contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar las programaciones. Por favor, intenta nuevamente.</div>';
            });
    }

    /**
     * Inicializar el formulario de búsqueda
     */
    function inicializarFormularioBusqueda() {
        const formBusqueda = document.getElementById('form-busqueda-programaciones');
        if (!formBusqueda) return;

        formBusqueda.addEventListener('submit', function (event) {
            event.preventDefault();

            const fechaSalida = document.getElementById('fecha_salida').value;
            const idOrigen = document.getElementById('id_origen').value;
            const idDestino = document.getElementById('id_destino').value;

            // Validar datos de búsqueda
            if (!fechaSalida || !idOrigen || !idDestino) {
                alert('Por favor, complete todos los campos de búsqueda.');
                return;
            }

            // Cargar programaciones con los criterios seleccionados
            cargarProgramaciones(fechaSalida, idOrigen, idDestino);
        });
    }

    // Inicializar cuando el documento esté listo
    document.addEventListener('DOMContentLoaded', function () {
        inicializarFormularioBusqueda();

        // Si hay datos en localStorage, intentar restaurar el estado
        const dataLocalStorage = obtenerDatosLocalStorage();
        if (dataLocalStorage.id_programacion) {
            // Restaurar variables globales
            asientosSeleccionados = dataLocalStorage.asientosSeleccionados || [];
            asientosSeleccionadosArray = dataLocalStorage.asientosSeleccionadosArray || [];
            TpAsientoArray = dataLocalStorage.TpAsientoArray || [];
            PrecioIndividual = dataLocalStorage.PrecioIndividual || [];
            sumaTotal = dataLocalStorage.sumaTotal || 0;

            // Actualizar UI
            actualizarAsientosSeleccionados();
            actualizarAsientosPrecio();
        }
    });

    /**
     * Guarda datos en localStorage de forma segura y combinada
     * @param {Object} nuevosDatos - Datos a guardar o actualizar
     */
    function guardarEnLocalStorage(nuevosDatos) {
        try {
            // Obtener el objeto actual (o un objeto vacío si no existe)
            const datosActuales = JSON.parse(localStorage.getItem('venta')) || {};
            // Combinar los datos existentes con los nuevos
            const datosCombinados = { ...datosActuales, ...nuevosDatos };
            // Guardar el objeto combinado
            localStorage.setItem('venta', JSON.stringify(datosCombinados));
        } catch (error) {
            console.error('Error al guardar en localStorage:', error);
            mostrarError('No se pudieron guardar los datos localmente');
        }
    }

    /**
     * Libera asientos de una programación
     * @param {Array} asientos - Arreglo de asientos a liberar. Cada elemento puede ser:
     *   - Un string (el ID del asiento), o
     *   - Un objeto con la propiedad `idAsiento`
     * @param {string} id_prog - ID de programación
     * @returns {Promise} - Promesa que resuelve cuando todos los asientos son liberados
     */
    function liberarAsientos(asientos, id_prog) {
        if (!asientos || asientos.length === 0 || !id_prog) {
            return Promise.resolve();
        }

        const liberaciones = asientos.map(item => {
            // Extraer el ID del asiento, según el esquema unificado o el valor directo
            const id_asiento = (typeof item === 'object' && item.idAsiento) ? item.idAsiento : item;

            return new Promise((resolve) => {
                try {
                    const formData = new FormData();
                    formData.set("id_programacion", id_prog);
                    formData.set("id_obj_vehiculo", id_asiento);
                    formData.set("estado_venta", '');

                    fetch(`${_URL_}pasaje/liberar_asiento`, {
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
                            resolve(true);
                        })
                        .catch(error => {
                            console.error(`Error al liberar asiento ${id_asiento}:`, error);
                            resolve(false);
                        });
                } catch (error) {
                    console.error(`Error en solicitud para asiento ${id_asiento}:`, error);
                    resolve(false);
                }
            });
        });

        return Promise.all(liberaciones);
    }

    /**
     * Maneja el evento de clic en un botón de acción
     * @param {HTMLElement} boton - Botón que fue clickeado
     * @param {string} programacionId - ID de la programación seleccionada
     * @param {string} vehiculoId - ID del vehículo seleccionado
     */
    function manejarClickBoton(boton, programacionId, vehiculoId) {
        const isActive = boton.classList.contains('active');

        // Recuperar datos actuales desde localStorage
        const dataLocalStorage = obtenerDatosLocalStorage();

        const id_programacion_actual = programacionId;
        // Utilizamos el arreglo unificado, que se llama asientosSeleccionadosData
        const asientosSeleccionadosActuales = dataLocalStorage.asientosSeleccionadosData || [];
        // CASO 1: Si se hace click en el mismo botón que ya está activo (cerrar programación)
        if (isActive) {
            handleCierreProgramacion(boton, asientosSeleccionadosActuales, id_programacion_actual);
            return;
        }

        // CASO 2: Resetear todas las programaciones activas anteriores
        // Si hay programaciones activas, obtenemos el id de la programación activa anterior
        const programacionesActivas = document.querySelectorAll('.mostrar-vehiculo-btn.active');
        let idPro_anterior = "";
        if (programacionesActivas.length > 0) {
            // Como solo habrá un botón activo, usamos el primero para obtener el id
            idPro_anterior = programacionesActivas[0].getAttribute("programacion") ||
                programacionesActivas[0].getAttribute("data-programacion") ||
                programacionId;
        }

        if (programacionesActivas.length > 0) {
            handleCambioProgramacion(
                programacionesActivas,
                asientosSeleccionadosActuales,
                idPro_anterior,
                boton,
                programacionId,
                vehiculoId
            );
        } else {
            // Si no hay programaciones activas, simplemente activar la nueva
            activarNuevaProgramacion(boton, programacionId, vehiculoId);
        }
    }

    /**
     * Maneja el cierre de una programación activa
     * @param {HTMLElement} boton - Botón de la programación actual
     * @param {Array} asientosSeleccionadosActuales - Asientos seleccionados actualmente
     * @param {string} id_programacion_actual - ID de la programación actual
     */
    async function handleCierreProgramacion(boton, asientosSeleccionadosActuales, id_programacion_actual) {

        // Validar si los datos son correctos antes de continuar
        if (!Array.isArray(asientosSeleccionadosActuales) || asientosSeleccionadosActuales.length === 0) {
            console.warn("No hay asientos seleccionados para liberar.");
            resetearInterfaz(boton);
            return;
        }

        if (!id_programacion_actual) {
            console.warn("ID de programación no válido.");
            resetearInterfaz(boton);
            return;
        }

        try {
            await liberarAsientos(asientosSeleccionadosActuales, id_programacion_actual);
        } catch (error) {
            console.error("Error al liberar asientos:", error);
            mostrarError("Hubo un problema al liberar los asientos, pero se cerrará la programación.");
        }

        // Restablecer la interfaz aunque falle la liberación de asientos
        resetearInterfaz(boton);
    }

    /**
     * Resetea la interfaz y los datos
     * @param {HTMLElement} boton - Botón de la programación a resetear
     */
    function resetearInterfaz(boton) {
        // Resetear el botón actual
        boton.classList.remove('active');
        boton.style.backgroundColor = "rgb(60, 71, 165)";
        boton.textContent = "ASIENTOS";

        // Ocultar el contenido del vehículo
        const contenido = boton.closest(".programacion").querySelector(".content-vehiculo");
        if (contenido) {
            contenido.classList.add("d-none");
            contenido.innerHTML = "";
        }

        // Limpiar localStorage (eliminando el objeto 'venta')
        localStorage.removeItem('venta');

        // Resetear variables globales (ahora usamos un solo arreglo unificado para los asientos)
        asientosSeleccionadosData = [];
        sumaTotal = 0;

        // Actualizar la interfaz
        actualizarAsientosSeleccionados();
        actualizarAsientosPrecio();
        if (asientosSeleccionadosData.length === 0) {
            detener_temporizador();
        }
    }
    /**
     * Maneja el cambio de una programación a otra
     * @param {NodeListOf<Element>} programacionesActivas - Lista de botones de programaciones activas
     * @param {Array} asientosSeleccionadosActuales - Arreglo unificado de asientos seleccionados (objetos con idAsiento, etc.)
     * @param {string} id_programacion_actual - ID de la programación actual
     * @param {HTMLElement} boton - Nuevo botón seleccionado
     * @param {string} programacionId - ID de la nueva programación
     * @param {string} vehiculoId - ID del vehículo seleccionado
     */
    async function handleCambioProgramacion(programacionesActivas, asientosSeleccionadosActuales, id_programacion_actual, boton, programacionId, vehiculoId) {
        // Liberar asientos de la programación anterior utilizando el arreglo unificado
        await liberarAsientos(asientosSeleccionadosActuales, id_programacion_actual)
            .then(() => {

                // Resetear la UI de todas las programaciones activas
                programacionesActivas.forEach(botonActivo => {
                    botonActivo.classList.remove('active');
                    botonActivo.style.backgroundColor = "rgb(60, 71, 165)";
                    botonActivo.textContent = "ASIENTOS";
                    const contenido = botonActivo.closest(".programacion").querySelector(".content-vehiculo");
                    if (contenido) {
                        contenido.classList.add("d-none");
                        contenido.innerHTML = "";
                    }
                });

                // Activar la nueva programación
                activarNuevaProgramacion(boton, programacionId, vehiculoId);
            })
            .catch(error => {
                console.error("Error al liberar asientos de programación anterior:", error);
                mostrarError("Hubo un problema al liberar los asientos anteriores, pero se continuará con la nueva selección");

                // Aunque ocurra un error, resetear la UI de las programaciones activas
                programacionesActivas.forEach(botonActivo => {
                    botonActivo.classList.remove('active');
                    botonActivo.style.backgroundColor = "rgb(60, 71, 165)";
                    botonActivo.textContent = "ASIENTOS";
                    const contenido = botonActivo.closest(".programacion").querySelector(".content-vehiculo");
                    if (contenido) {
                        contenido.classList.add("d-none");
                        contenido.innerHTML = "";
                    }
                });

            });
    }

    /**
     * Activa una nueva programación
     * @param {HTMLElement} boton - Botón de la nueva programación
     * @param {string} programacionId - ID de la nueva programación
     * @param {string} vehiculoId - ID del vehículo seleccionado
     */
    function activarNuevaProgramacion(boton, programacionId, vehiculoId) {
        // Limpiar localStorage antes de iniciar nueva programación
        localStorage.removeItem('venta');

        // Configurar nuevo estado para el botón clickeado
        boton.classList.add('active');
        boton.style.backgroundColor = "red";
        boton.textContent = "CERRAR";

        // Actualizar el select de programación si existe
        const id_programacion_select = document.getElementById('id_programacion_select');
        if (id_programacion_select) {
            id_programacion_select.value = programacionId;
        }

        // Inicializar un nuevo objeto para almacenar solo la información de esta programación
        const dataLocalStorage = {};

        // Obtener atributos del botón
        const programacionAttr = boton.getAttribute("programacion") || programacionId;
        const descripcion = boton.getAttribute("descripcion") || '';
        const hora_salida = boton.getAttribute("hora_salida") || '';
        const NombreO = boton.getAttribute('NombreO') || '';
        const NombreD = boton.getAttribute('NombreD') || '';
        const fechaIda = boton.getAttribute('fechaIda') || '';

        // Guardar SOLO los datos de la nueva programación en el objeto
        dataLocalStorage.descripcion = descripcion;
        dataLocalStorage.id_programacion = programacionAttr;
        dataLocalStorage.hora_salida = hora_salida;
        dataLocalStorage.NombreO = NombreO;
        dataLocalStorage.NombreD = NombreD;
        dataLocalStorage.fechaIda = fechaIda;

        // Inicializar el array unificado de asientos seleccionados y el total
        dataLocalStorage.asientosSeleccionadosData = [];
        dataLocalStorage.sumaTotal = 0;

        // Resetear variables globales (ahora usamos solo asientosSeleccionadosData)
        asientosSeleccionadosData = [];
        sumaTotal = 0;

        // Guardar el objeto limpio con solo la información de la nueva programación
        guardarEnLocalStorage(dataLocalStorage);

        // Actualizar la UI
        actualizarAsientosSeleccionados();
        actualizarAsientosPrecio();

        // Mostrar vehículo de la nueva programación
        toggleVehiculo(false, boton, programacionId, vehiculoId);
        if (asientosSeleccionadosData.length === 0) {
            detener_temporizador();
        }
    }

    /**
     * Actualiza la lista de asientos seleccionados en la UI
     */
    function actualizarAsientosSeleccionados() {
        const asientosContainer = document.getElementById('asientos-seleccionados-container');
        if (!asientosContainer) return;

        if (asientosSeleccionados.length === 0) {
            asientosContainer.innerHTML = '<p>No hay asientos seleccionados</p>';
            return;
        }

        let html = '<ul>';
        asientosSeleccionados.forEach((asiento, index) => {
            html += `<li>Asiento ${asiento} - Tipo: ${TpAsientoArray[index] || 'N/A'} - Precio: ${PrecioIndividual[index] || 0}</li>`;
        });
        html += '</ul>';

        asientosContainer.innerHTML = html;
    }

    /**
    * Sistema de Gestión de Asientos de Vehículos
  * Maneja la carga, visualización y reserva de asientos
  */

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
        'RESERVADO_WEB': "#f6bd60",
        'PROCESO_WEB': "#4FF08F",
        'SELECCIONADO': "#ff0000" // Color para asientos seleccionados actualmente
    };

    // --------- FUNCIONES DE CARGA INICIAL ---------

    /**
     * Obtiene y muestra el vehículo con su configuración completa
     * @param {string} id_programacion - ID de la programación
     * @param {string} id_vehiculo - ID del vehículo
     * @param {HTMLElement} boton - Elemento que activó la función (usado para localizar el contenedor)
     */
    const show_vehiculo = async (id_programacion, id_vehiculo, boton) => {
        let formData = new FormData();
        let condic = 'getVehiculo';
        formData.set('accion', condic);
        formData.set('id_vehiculo', id_vehiculo);
        formData.set('id_programacion', id_programacion);

        try {
            const response = await fetch($("#url_web").val() + "carrito/getVehiculo", {
                method: "POST",
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                let datos = data.message;
                let superParentVehiculo = boton;

                // Si boton es un elemento DOM con querySelector
                if (boton.closest && typeof boton.closest === 'function') {
                    superParentVehiculo = boton.closest(".programacion").querySelector(".content-vehiculo");
                }

                // Limpiar contenedor
                superParentVehiculo.innerHTML = "";

                // Crear pisos del vehículo
                for (let i = 1; i <= datos.vehiculo.num_piso; i++) {
                    let delantera_vehiculo = `<img class="p-0" src="${$("#url_web").val()}img/img_card/vehiculo_delantero.jpg" width="305px" height="150px" alt="...">`;
                    let trasera_vehiculo = `<img class="p-0" src="${$("#url_web").val()}img/img_card/vehiculo_trasera.jpg" width="300px" height="40px" alt="...">`;

                    superParentVehiculo.insertAdjacentHTML('beforeend', `
                    <section class="position-relative col-md-4 d-flex flex-column">
                        ${i == 1 ? delantera_vehiculo : ''}
                        <div class="position-relative parent_vehiculo ${i != 1 ? 'vehiculo_segundoPiso' : ''}" floor="${i}"></div>
                        ${i == 1 ? trasera_vehiculo : ''}
                    </section>
                `);

                    // Ajustar altura según el piso
                    if (i > 1) {
                        $(`.parent_vehiculo[floor="${i}"]`).css("height", `${parseInt(datos.vehiculo.height.replace("px", "")) + 150}px`);
                    } else {
                        $(`.parent_vehiculo[floor="${i}"]`).css("height", `${datos.vehiculo.height}`);
                    }
                }

                // Crear objetos del vehículo (asientos, puertas, etc.)
                Object.values(datos.objs_vehiculo).forEach((e, i, array) => {
                    e.id_programacion = id_programacion;
                    crear_objeto(e);
                });

                // Inicializar información adicional
                cargar_info_a(superParentVehiculo);

                // Después de cargar todo, inicializar eventos y recuperar selecciones
                cargarAsientosSeleccionadosDesdeLocalStorage();
            }
        } catch (error) {
            console.error("Error al cargar el vehículo:", error);
            Swal.fire({
                title: 'Error!',
                text: error.message || "Error al cargar el vehículo",
                icon: 'error',
                confirmButtonColor: '#2a9d8f',
            });
        }
    };

    /**
     * Muestra u oculta el contenido del vehículo
     * @param {boolean} isActive - Estado actual (si está activo o no)
     * @param {HTMLElement} boton - Botón que activó la función
     * @param {string} programacionId - ID de la programación
     * @param {string} vehiculoId - ID del vehículo
     */
    function toggleVehiculo(isActive, boton, programacionId = null, vehiculoId = null) {
        const contenidoVehiculo = boton.closest(".programacion").querySelector(".content-vehiculo");

        if (isActive) {
            // Ocultar el vehículo y limpiar su contenido
            contenidoVehiculo.classList.add("d-none");
            contenidoVehiculo.innerHTML = "";

            // Resetear variables globales de asientos
            asientosSeleccionados = [];
            asientosSeleccionadosArray = [];
            TpAsientoArray = [];
            PrecioIndividual = [];
            sumaTotal = 0;

            // Limpiar asientos reservados del localStorage
            localStorage.removeItem('asientos_reservados');

            // Actualizar la interfaz
            actualizarAsientosSeleccionados();
            actualizarAsientosPrecio();

        } else {
            // Mostrar el vehículo
            contenidoVehiculo.classList.remove("d-none");

            // Cargar el contenido del vehículo según la programación y el vehículo seleccionado
            show_vehiculo(programacionId, vehiculoId, contenidoVehiculo);
        }
    }

    /**
    * Crea un objeto visual en el mapa del vehículo (asiento, puerta, etc)
    * @param {Object} dataObj - Datos del objeto a crear
    */
    async function crear_objeto(data_obj) {
        // Crear el elemento DOM
        let div = document.createElement("div");
        let objeto_cursorPointer = data_obj.tp_obj == "asiento" ? "cursor-pointer" : null;
        div.className = `position-absolute objeto ${objeto_cursorPointer} `;
        div.setAttribute("floor", data_obj.piso);
        div.setAttribute("tp_obj", data_obj.tp_obj);
        div.setAttribute("id_venta", data_obj.id_venta);
        div.setAttribute("precio", data_obj.precio);
        div.setAttribute('data-id-programacion', data_obj.id_programacion);
        div.id = data_obj.id_obj_vehiculo;

        if (data_obj.tp_obj == "asiento") {
            div.setAttribute("tp_asiento", data_obj.tp_asiento);
        }

        // Establecer estilos y posición
        div.style.cssText = `top:${data_obj.top_obj}; left:${data_obj.left_obj} `;
        div.style.transform = `rotate(${data_obj.rotate_obj})`;

        // Determinar color del asiento/objeto
        let color_asiento;

        if (data_obj.estado_asiento) {
            color_asiento = COLOR_ESTADO_ASIENTO[data_obj.estado_asiento];
        } else if (data_obj.tp_obj == "asiento") {
            color_asiento = COLOR_TIPO_ASIENTO[data_obj.tp_asiento.toUpperCase()];
        } else {
            color_asiento = "#6a696b";
        }

        // Verificar si está en los seleccionados
        let asientosGuardados = [];
        if (localStorage.getItem("asientos_reservados")) {
            asientosGuardados = JSON.parse(localStorage.getItem("asientos_reservados"));

            if (data_obj.tp_obj == "asiento" && asientosGuardados.includes(data_obj.id_obj_vehiculo)) {
                color_asiento = COLOR_ESTADO_ASIENTO.SELECCIONADO;

                // Sincronizar con variables globales
                let num_asiento = data_obj.text_obj;
                let precioA = data_obj.precio;
                let Easiento = data_obj.tp_asiento;

                if (!asientosSeleccionadosArray.includes(num_asiento)) {
                    asientosSeleccionadosArray.push(num_asiento);
                    TpAsientoArray.push(Easiento);
                    if (!asientosSeleccionados.includes(data_obj.id_obj_vehiculo)) {
                        asientosSeleccionados.push(data_obj.id_obj_vehiculo);
                    }
                    PrecioIndividual.push(precioA);
                    sumaTotal += parseFloat(precioA);
                }
            }
        }

        // Determinar color del texto
        let color_text = data_obj.estado_asiento ? "#ffff" : COLOR_TIPO_ASIENTO[data_obj.tp_asiento];
        let weight = data_obj.estado_asiento ? 'fw-bold' : '';

        // Agregar contenido HTML
        div.insertAdjacentHTML("beforeend", `
            <i class="${data_obj.icon_obj} icon_obj ${weight}" class-icon="${data_obj.icon_obj}" style="font-style: normal; font-size:45px; color: ${color_asiento}">
                <span class="position-absolute start-50 translate-middle text_obj fw-bolder mt-3" style="font-size:18px; color:${color_text}">${data_obj.text_obj}</span>
            </i>
            `);

        // Agregar al DOM
        if (document.querySelector(`.parent_vehiculo[floor='${data_obj.piso}']`)) {
            document.querySelector(`.parent_vehiculo[floor='${data_obj.piso}']`).appendChild(div);
        }

        // Guardar información del piso actual
        let numPiso = div.getAttribute("floor");
        let dataLocalStorage = JSON.parse(localStorage.getItem('venta')) || {};
        dataLocalStorage.numPiso = numPiso;
        localStorage.setItem('venta', JSON.stringify(dataLocalStorage));

        // Agregar eventos si es un asiento y está disponible
        if (data_obj.tp_obj == "asiento") {
            if (!["RESERVADO", "VENDIDO", "RESERVADO_WEB"].includes(data_obj.estado_asiento)) {
                div.addEventListener("click", async (e) => {
                    manejarClickAsiento(e.currentTarget);
                });
            } else {
                div.style.pointerEvents = "none";
            }
        }
    }

    /**
     * Carga información adicional en el contenedor del vehículo
     * @param {HTMLElement} contenedor - Contenedor donde se mostrará la información
     */
    function cargar_info_a(vehiculo) {
        vehiculo.insertAdjacentHTML('beforeend', `
            <section class="position-relative info-asientos col-md-4">
                <div class="leyenda-asientos">
                    <div class="title-asiento">
                        <h6>Estado de asientos</h6>
                    </div>
                    
                    <div class="contendor-icon d-flex flex-column justify-content-center ">
                      <div class="d-asiento d-flex align-items-center">
                        <i class="fa-light fa-loveseat fs-3 asiento_normal m-2" style="color:#6a696b;"></i>
                        <p class="mb-0">Asiento libre</p>
                      </div>
                      <div class="d-asiento d-flex align-items-center">
                        <i class="fa-light fa-loveseat fs-3 asiento_premiun m-2" style="color:#fca311;"></i>
                        <p class="mb-0">Asiento premium</p>
                      </div>
                      <div class="d-asiento d-flex align-items-center">
                        <i class="fa-light fa-loveseat fs-3 asiento_reservado fw-bolder m-2" style="color:#912de3;"></i>
                        <p class="mb-0">Asiento reservado</p>
                      </div>
                      <div class="d-asiento d-flex align-items-center">
                        <i class="fa-light fa-loveseat fs-3 asiento_vendido fw-bolder m-2" style="color:#0394fc;"></i>
                        <p class="mb-0">Asiento vendido</p>
                      </div>
                    <div class="d-asiento d-flex align-items-center">
                    <i class="fa-light fa-loveseat fs-3 asiento_proceso_web fw-bolder m-2" style="color:#4FF08F;"></i>
                    <p class="mb-0">Asiento proceso web</p>
                    </div>
                      <div class="d-asiento d-flex align-items-center">
                        <i class="fa-light fa-loveseat fs-3 asiento_reservado_web fw-bolder m-2" style="color:#f6bd60;"></i>
                        <p class="mb-0">Asiento reservado web</p>
                      </div>
                    </div>
                </div>
                <div class="asiSelecion">
                    <div class="title-asiSelecion">
                        <h6>Tus asientos</h6>
                    </div>
                    <div id="tex-sub">
                        <p>Selecciona un asiento</p>
                    </div>
                    <div class="datos-asi d-none" id="continue">
                        <p>Asientos: <span id="asientosSeleccionados"></span></p>
                        <p>Total a pagar: S/.<span id="precioAsiento"></span></p>
                        <button class="btn_continuar" >Continuar</button>
                    </div>
                </div>
            </section>
        `);
    }

    // --------- FUNCIONES DE MANEJO DE EVENTOS ASIENTOS---------

    /**
     * Maneja el evento de click en un asiento
     * @param {HTMLElement} asiento - Elemento del asiento clickeado
     */
    async function manejarClickAsiento(asiento) {
        // 1. Extraer datos básicos
        const idAsiento = asiento.id || asiento.getAttribute('data-id');
        const numAsiento = asiento.querySelector('.text_obj')?.textContent || asiento.textContent.trim();
        const tipoAsiento = asiento.getAttribute('tp_asiento') || asiento.getAttribute('data-tipo');
        const precioAsiento = parseFloat(asiento.getAttribute('precio') || asiento.getAttribute('data-precio') || '0');
        const programacionId = asiento.getAttribute('data-id-programacion') ||
            asiento.closest('.programacion')?.getAttribute('data-id-programacion') || '';

        // 2. Verificar si el asiento ya está en asientosSeleccionadosData
        const indice = asientosSeleccionadosData.findIndex(item => item.numAsiento === numAsiento);

        // 3. Lógica de selección/deselección
        if (indice === -1) {
            // === SELECCIONAR ASIENTO ===

            // Verificar límite (máximo 4)
            if (asientosSeleccionadosData.length >= 4) {
                Swal.fire('Límite de asientos alcanzado', 'No puedes seleccionar más de 4 asientos.', 'warning');
                return;
            }

            // Verificar disponibilidad en tiempo real
            const disponible = await validarAsientoSeleccionado(idAsiento, programacionId);
            if (disponible) {
                Swal.fire('Asiento no disponible', 'El asiento ya ha sido reservado o vendido.', 'error');
                return;
            }

            // Iniciar temporizador si es el primer asiento
            if (asientosSeleccionadosData.length === 0) {
                iniciar_temporizador();
            }

            // Agregar el asiento al array
            asientosSeleccionadosData.push({
                idAsiento,
                numAsiento,
                tipoAsiento,
                precioAsiento
            });

            // Marcar visualmente
            if (asiento.querySelector('.icon_obj')) {
                asiento.querySelector('.icon_obj').style.color = COLOR_ESTADO_ASIENTO.SELECCIONADO;
            } else {
                asiento.classList.add('seleccionado');
            }

        } else {
            // === DESELECCIONAR ASIENTO ===

            // Llamar a la API para liberar asientos
            eliminar_reserva(idAsiento, programacionId);

            // Quitar el asiento del array
            asientosSeleccionadosData.splice(indice, 1);

            // Desmarcar visualmente
            if (asiento.querySelector('.icon_obj')) {
                const colorOriginal = tipoAsiento
                    ? COLOR_TIPO_ASIENTO[tipoAsiento.toUpperCase()]
                    : COLOR_TIPO_ASIENTO.NORMAL;
                asiento.querySelector('.icon_obj').style.color = colorOriginal;
            } else {
                asiento.classList.remove('seleccionado');
            }

            // Si no quedan asientos seleccionados, detener temporizador
            if (asientosSeleccionadosData.length === 0) {
                detener_temporizador();
            }
        }

        // 4. Actualizar la interfaz
        actualizarAsientosSeleccionados();  // Se encargará de mostrar asientos y precio
        actualizarAsientosPrecio();
        actualizarBotonComprar();

        // 5. Guardar datos adicionales en localStorage
        const programacionContainer = asiento.closest('.programacion');
        const nombreOrigen = programacionContainer.querySelector('button').getAttribute('NombreO');
        const nombreDestino = programacionContainer.querySelector('button').getAttribute('NombreD');
        const Descripcion = programacionContainer.querySelector('button').getAttribute('Descripcion');

        // Usar nuestra función de ayuda para guardar
        guardarEnLocalStorage({
            NombreO: nombreOrigen,
            NombreD: nombreDestino,
            Descripcion
        });
    }

    // --------- FUNCIONES DE ACTUALIZACIÓN DE UI ---------

    /**
     * Actualiza la visualización de asientos seleccionados
     */
    function actualizarAsientosSeleccionados() {
        // Convertir los asientos a un array de números (numAsiento)
        const nums = asientosSeleccionadosData.map(item => item.numAsiento);

        // Crear el string separado por comas
        const asientosSeleccionadosString = nums.join(', ');

        // Mostrar en la interfaz
        let textasientos = document.querySelector("#asientosSeleccionados");
        if (textasientos) {
            textasientos.textContent = asientosSeleccionadosString;
        }

        // Guardar el arreglo completo en localStorage (en la clave "venta")
        guardarEnLocalStorage({
            asientosSeleccionadosData
        });
    }

    /**
     * Calcula y actualiza la suma total de los precios de los asientos seleccionados
     */
    function calcularPrecioTotal() {
        // Calcular el total a partir del array unificado
        sumaTotal = asientosSeleccionadosData.reduce((acc, asiento) => acc + asiento.precioAsiento, 0);

        // Actualizar el valor de sumaTotal en el localStorage utilizando la función de guardado
        guardarEnLocalStorage({ sumaTotal });

        return sumaTotal;
    }

    // Al mostrarlo en la UI
    function actualizarAsientosPrecio() {
        const precioTotal = calcularPrecioTotal();
        // Actualizas el DOM, por ejemplo:
        const precioSpan = document.getElementById("precioAsiento");
        if (precioSpan) {
            precioSpan.textContent = precioTotal.toFixed(2);
        }
    }

    /**
     * Actualiza el estado del botón de compra y la visualización del contenedor de asientos seleccionados
     */
    function actualizarBotonComprar() {
        const contenedorComprar = document.getElementById('continue'); // Contenedor que incluye el botón "Continuar"
        const texSub = document.getElementById('tex-sub');

        if (!contenedorComprar || !texSub) return;

        // Si existe un botón dentro del contenedor, lo obtenemos para actualizar su estado
        const botonComprar = contenedorComprar.querySelector('button.btn_continuar');

        // Si hay asientos seleccionados (máximo 4), mostramos el contenedor y habilitamos el botón
        if (asientosSeleccionadosData.length > 0 && asientosSeleccionadosData.length <= 4) {
            if (botonComprar) {
                botonComprar.disabled = false;
            }
            contenedorComprar.classList.remove('d-none');
            texSub.classList.add('d-none');
        } else {
            // En caso contrario, ocultamos el contenedor y mostramos el mensaje alternativo
            if (botonComprar) {
                botonComprar.disabled = true;
            }
            contenedorComprar.classList.add('d-none');
            texSub.classList.remove('d-none');
        }
    }

    // --------- FUNCIONES DE ALMACENAMIENTO ---------

    /**
     * Obtiene datos almacenados en localStorage
     * @returns {Object} - Datos almacenados
     */
    function obtenerDatosLocalStorage() {
        return JSON.parse(localStorage.getItem('venta')) || {};
    }

    /**
     * Carga los asientos seleccionados previamente desde localStorage
     */
    function cargarAsientosSeleccionadosDesdeLocalStorage() {
        if (localStorage.getItem('asientos_reservados')) {
            const asientosGuardados = JSON.parse(localStorage.getItem('asientos_reservados'));
            // Marcar visualmente los asientos
            asientosGuardados.forEach(idAsiento => {
                const asiento = document.getElementById(idAsiento);
                if (asiento) {
                    const numAsiento = asiento.querySelector('.text_obj')?.textContent || '';
                    const tipoAsiento = asiento.getAttribute('tp_asiento') || '';
                    const precioAsiento = parseFloat(asiento.getAttribute('precio') || '0');

                    // Solo agregar si no está ya en los arrays
                    if (!asientosSeleccionadosArray.includes(numAsiento)) {
                        asientosSeleccionadosArray.push(numAsiento);
                        TpAsientoArray.push(tipoAsiento);
                        asientosSeleccionados.push(idAsiento);
                        PrecioIndividual.push(precioAsiento);
                        sumaTotal += precioAsiento;

                        // Marcar visualmente
                        if (asiento.querySelector('.icon_obj')) {
                            asiento.querySelector('.icon_obj').style.color = COLOR_ESTADO_ASIENTO.SELECCIONADO;
                        } else {
                            asiento.classList.add('seleccionado');
                        }
                    }
                }
            });

            // Actualizar interfaz
            actualizarAsientosSeleccionados();
            actualizarAsientosPrecio();
            actualizarBotonComprar();

            // Iniciar temporizador si hay asientos seleccionados
            if (asientosSeleccionadosArray.length > 0) {
                iniciar_temporizador();
            }
        }
    }

    // --------- FUNCIONES DE UTILIDAD ---------

    /**
     * Resetea la selección de asientos
     */
    function resetearSeleccionAsientos() {
        // Limpiar marcas visuales
        asientosSeleccionados.forEach(idAsiento => {
            const asiento = document.getElementById(idAsiento);
            if (asiento) {
                if (asiento.querySelector('.icon_obj')) {
                    const tipoAsiento = asiento.getAttribute('tp_asiento') || 'NORMAL';
                    asiento.querySelector('.icon_obj').style.color = COLOR_TIPO_ASIENTO[tipoAsiento.toUpperCase()];
                } else {
                    asiento.classList.remove('seleccionado');
                }
            }
        });

        // Resetear arrays y variables
        asientosSeleccionados = [];
        asientosSeleccionadosArray = [];
        TpAsientoArray = [];
        PrecioIndividual = [];
        sumaTotal = 0;

        // Actualizar interfaz
        actualizarAsientosSeleccionados();
        actualizarAsientosPrecio();
        actualizarBotonComprar();

        // Limpiar localStorage
        localStorage.removeItem('asientos_reservados');
        const dataVenta = JSON.parse(localStorage.getItem('venta')) || {};
        delete dataVenta.asientosSeleccionados;
        delete dataVenta.asientosSeleccionadosArray;
        delete dataVenta.TpAsientoArray;
        delete dataVenta.PrecioIndividual;
        delete dataVenta.sumaTotal;
        localStorage.setItem('venta', JSON.stringify(dataVenta));
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
                // Remover el asiento del arreglo unificado
                asientosSeleccionadosData = asientosSeleccionadosData.filter(item => item.idAsiento !== id_asiento);

                // Actualizar localStorage utilizando la función auxiliar
                guardarEnLocalStorage({ asientosSeleccionadosData });
            } else {
                // Si la respuesta no es exitosa, mostrar el error recibido
                console.log(data.message || "Error al liberar el asiento.");
            }
        } catch (error) {
            console.log(error.message);
            Swal.fire('Error', 'Error', 'error');
            return false;
        }
    }

    window.addEventListener('load', manejarRecargaPagina);

    async function manejarRecargaPagina() {
        // Recuperar datos de localStorage
        const dataLocalStorage = obtenerDatosLocalStorage();

        if (dataLocalStorage.asientosSeleccionadosData && dataLocalStorage.asientosSeleccionadosData.length > 0) {
            // Crear un array de promesas para liberar cada asiento en el servidor
            const promesasEliminacion = dataLocalStorage.asientosSeleccionadosData.map(asiento => {
                // Extraer el id del asiento (si el elemento es un objeto)
                const idAsiento = (typeof asiento === 'object' && asiento.idAsiento) ? asiento.idAsiento : asiento;
                return eliminar_reserva(idAsiento, dataLocalStorage.id_programacion);
            });

            try {
                // Esperar a que todas las reservas se eliminen correctamente en el servidor
                await Promise.all(promesasEliminacion);

                // Limpiar los asientos reservados después de eliminarlos
                dataLocalStorage.asientosSeleccionadosData = [];
                // Guardar los cambios combinados en localStorage
                guardarEnLocalStorage(dataLocalStorage);
            } catch (error) {
                console.error("Error al eliminar reservas:", error);
            }
        }
    }

    window.addEventListener("unload", liberarAsientosEnServidor);

    function liberarAsientosEnServidor() {
        const dataLocalStorage = obtenerDatosLocalStorage();
        // Usamos el array unificado, asegurando que existe
        const asientos = dataLocalStorage.asientosSeleccionadosData || [];

        // Verificar que exista la programación y que haya asientos seleccionados
        if (dataLocalStorage.id_programacion && asientos.length > 0) {
            asientos.forEach(asiento => {
                // Extraer el ID del asiento, en caso de que sea un objeto
                const idAsiento = (typeof asiento === 'object' && asiento.idAsiento) ? asiento.idAsiento : asiento;
                const formData = new FormData();
                formData.set("id_programacion", dataLocalStorage.id_programacion);
                formData.set("id_obj_vehiculo", idAsiento);
                formData.set("estado_venta", '');

                // Enviar la solicitud al servidor para liberar el asiento
                navigator.sendBeacon(`${_URL_}pasaje/delete_estadoProcesoAsiento`, formData);
            });
        }
        detener_temporizador();
    }

    $(document).on('click', '.btn_continuar', function (e) {
        e.preventDefault();

        // Obtener datos actuales desde localStorage usando la función auxiliar
        let dataLocalStorage = obtenerDatosLocalStorage();

        // Actualizar datos en el objeto: usamos el arreglo unificado y la suma total
        dataLocalStorage.asientosSeleccionadosData = asientosSeleccionadosData; // Arreglo unificado de asientos
        dataLocalStorage.sumaTotal = sumaTotal;
        dataLocalStorage.asientosSeleccionados_completado = true; // Indica que se han seleccionado asientos

        // Guardar datos actualizados en localStorage mediante la función que hace merge
        guardarEnLocalStorage(dataLocalStorage);

        // Cambiar a la pestaña de datos (simulando clic en la pestaña "Datos")
        setTimeout(() => {
            datosTab.click();
        }, 1000);
    });

    const btnCancelVenta = document.querySelectorAll('.btnCancel');

    btnCancelVenta.forEach(btn => {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Cancelar compra',
                text: '¿Estás seguro que deseas cancelar la compra?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, continuar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // Obtener datos actuales de localStorage
                    const dataLocalStorage = obtenerDatosLocalStorage();

                    // Verificar si hay asientos seleccionados y liberar cada uno
                    if (dataLocalStorage.asientosSeleccionadosData && dataLocalStorage.asientosSeleccionadosData.length > 0) {
                        const promesasEliminacion = dataLocalStorage.asientosSeleccionadosData.map(asiento => {
                            // Extraer el ID del asiento (si es un objeto)
                            const idAsiento = (typeof asiento === 'object' && asiento.idAsiento) ? asiento.idAsiento : asiento;
                            return eliminar_reserva(idAsiento, dataLocalStorage.id_programacion);
                        });

                        try {
                            // Esperar a que todas las reservas se eliminen en el servidor
                            await Promise.all(promesasEliminacion);

                            // Limpiar los asientos reservados después de eliminarlos
                            dataLocalStorage.asientosSeleccionadosData = [];
                            guardarEnLocalStorage(dataLocalStorage);
                        } catch (error) {
                            console.error("Error al eliminar reservas:", error);
                        }
                    }

                    // Limpiar completamente el localStorage
                    localStorage.clear();

                    // Redirigir al home
                    const ruta_web = $("#url_web").val();
                    window.location.href = ruta_web;
                }
            });
        });
    });

});
