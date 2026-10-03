// --------- FUNCIONES DE TEMPORIZADOR ---------

let intervaloTemporizador;
let temporizadorIniciado = false;
let callbackExpiracionGlobal = null; // Nueva variable global
let URL_ADMIN = document.querySelector("#url").value;


// Inicialización del contenedor del temporizador
const timerContainer = document.getElementById('timer-container');

/**
 * Establece el callback global para cuando expire el temporizador
 * @param {Function} callback - Función a ejecutar cuando expire
 */
export function establecerCallbackExpiracion(callback) {
    callbackExpiracionGlobal = callback;
}

/**
 * Inicia el temporizador de reserva
 * @param {number} duracionMilisegundos - Duración del temporizador en ms (por defecto: 11 min)
 */
export function iniciar_temporizador(duracionMilisegundos = 10 * 60 * 1000) {
    if (temporizadorIniciado) return;

    temporizadorIniciado = true;
    const tiempoInicio = Date.now();
    const tiempoExpiracion = tiempoInicio + duracionMilisegundos;

    // Guardar en localStorage si se requiere persistencia al recargar (opcional)
    localStorage.setItem('tiempo_expiracion', tiempoExpiracion);

    // Iniciar contador - AHORA USA EL CALLBACK GLOBAL
    intervaloTemporizador = setInterval(() => {
        actualizarTemporizador(tiempoExpiracion, callbackExpiracionGlobal);
    }, 1000);

    actualizarTemporizador(tiempoExpiracion, callbackExpiracionGlobal);
}

/**
 * Detiene el temporizador de reserva
 */
export function detener_temporizador() {
    clearInterval(intervaloTemporizador);
    intervaloTemporizador = null;
    temporizadorIniciado = false;

    if (timerContainer) {
        timerContainer.innerHTML = '';
    }

    localStorage.removeItem('tiempo_expiracion');
}

/**
 * Función para actualizar el temporizador visual
 * @param {number} tiempoExpiracion - Timestamp (en ms) del tiempo de expiración
 * @param {Function} onExpiracionCallback - Función que se ejecuta si el tiempo se acaba
 */
export function actualizarTemporizador(tiempoExpiracion, onExpiracionCallback = null) {
    if (!timerContainer) return;

    const tiempoActual = Date.now();
    const tiempoRestante = tiempoExpiracion - tiempoActual;

    if (tiempoRestante <= 0) {
        detener_temporizador();

        // Callback para liberar asientos y redirigir (controlado desde main.js)
        if (typeof onExpiracionCallback === 'function') {
            onExpiracionCallback();
        }

        return;
    }

    const minutos = Math.floor(tiempoRestante / 60000);
    const segundos = Math.floor((tiempoRestante % 60000) / 1000);

    timerContainer.innerHTML = `
    <div class="purchase-timer" id="purchaseTimer">
      <div class="timer-header">Termina tu compra en:</div>
      <div class="timer-numbers">
        <span id="minutes">${String(minutos).padStart(2, '0')}</span> :
        <span id="seconds">${String(segundos).padStart(2, '0')}</span>
      </div>
      <div class="timer-labels">Minutos : Segundos</div>
    </div>
  `;
}

/**
 * Función auxiliar para ejecutar lógica al expirar el temporizador
 * @param {Object} opciones
 * @param {Array} opciones.asientosReservados - Lista de asientos seleccionados
 * @param {string} opciones.programacionId - ID de la programación activa
 * @param {string} opciones.urlBase - URL base para redirección o API
 */
export async function onTemporizadorExpirado({ asientosReservados = [], programacionId = '', urlBase = '/' }) {
    if (asientosReservados.length > 0 && programacionId) {
        try {
            const formData = new FormData();
            formData.append('id_programacion', programacionId);
            formData.append('asientos', JSON.stringify(asientosReservados));

            await fetch(`${URL_ADMIN}pasaje/liberar_asientos`, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Error al liberar asientos:', error);
        }
    }

    localStorage.clear();

    setTimeout(() => {
        window.location.href = urlBase;
    }, 500);
}

/**
 * Restaura el temporizador desde localStorage si está activo (llámalo desde main.js)
 * @param {Function} onExpiracionCallback - Callback a ejecutar si expira
 */
export function restaurar_temporizador(onExpiracionCallback = null) {
    const tiempoExpiracion = parseInt(localStorage.getItem('tiempo_expiracion'), 10);
    if (!tiempoExpiracion || isNaN(tiempoExpiracion)) return;

    const tiempoRestante = tiempoExpiracion - Date.now();
    if (tiempoRestante <= 0) {
        detener_temporizador();
        if (typeof onExpiracionCallback === 'function') {
            onExpiracionCallback();
        }
    } else {
        temporizadorIniciado = true;
        intervaloTemporizador = setInterval(() => {
            actualizarTemporizador(tiempoExpiracion, onExpiracionCallback);
        }, 1000);
        actualizarTemporizador(tiempoExpiracion, onExpiracionCallback);
    }
}
