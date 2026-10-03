// --------- FUNCIONES DE TEMPORIZADOR ---------
//Funciones para el temporizador
let intervaloTemporizador;
let temporizadorIniciado = false;
/**
 * Inicia el temporizador de reserva
 */

// Verifica si existe el contenedor
const timerContainer = document.getElementById('timer-container');

// Verifica si hay un temporizador guardado
const tiempoExpiracion = localStorage.getItem('tiempo_expiracion');
if (tiempoExpiracion) {
    const tiempoActual = new Date().getTime();
    const tiempoRestante = tiempoExpiracion - tiempoActual;

    if (tiempoRestante > 0) {
        // Si queda tiempo, reinicia el temporizador desde donde quedó
        temporizadorIniciado = true;

        // Inicia el intervalo de actualización
        intervaloTemporizador = setInterval(() => {
            actualizarTemporizador(tiempoExpiracion);
        }, 1000);

        // Actualiza inmediatamente
        actualizarTemporizador(tiempoExpiracion);
    }
}

export function iniciar_temporizador() {
    if (temporizadorIniciado) return; // Evita iniciar el temporizador más de una vez

    temporizadorIniciado = true; // Marca que el temporizador ya está iniciado
    const tiempoLimite = 11 * 60 * 1000; // 15 minutos en milisegundos
    const tiempoInicio = new Date().getTime(); // Obtiene el tiempo actual
    const tiempoExpiracion = tiempoInicio + tiempoLimite;

    // Almacena el tiempo de expiración en el almacenamiento local
    localStorage.setItem('tiempo_expiracion', tiempoExpiracion);

    // Inicia un intervalo para verificar el tiempo restante
    intervaloTemporizador = setInterval(() => {
        actualizarTemporizador(tiempoExpiracion);
    }, 1000); // Verifica cada segundo

    // Actualiza inmediatamente
    actualizarTemporizador(tiempoExpiracion);
}

/**
 * Detiene el temporizador de reserva
 */
export function detener_temporizador() {
    if (intervaloTemporizador) {
        clearInterval(intervaloTemporizador);
        intervaloTemporizador = null;
    }
    temporizadorIniciado = false;

    // Limpiar el contenedor del temporizador
    const timerContainer = document.getElementById('timer-container');
    if (timerContainer) {
        timerContainer.innerHTML = '';
    }

    // Eliminar el tiempo de expiración del almacenamiento local
    localStorage.removeItem('tiempo_expiracion');
}

/**
* Función para actualizar el temporizador visual
*/
export async function actualizarTemporizador(tiempoExpiracion) {
    const timerContainer = document.getElementById('timer-container');
    if (!timerContainer) return;

    const tiempoActual = new Date().getTime();
    const tiempoRestante = tiempoExpiracion - tiempoActual;

    if (tiempoRestante <= 0) {
        detener_temporizador();

        // Obtener los asientos seleccionados desde localStorage
        const asientosReservados = JSON.parse(localStorage.getItem('asientos_reservados')) || [];
        const programacionId = localStorage.getItem('id_programacion'); // ID de la programación actual

        if (asientosReservados.length > 0 && programacionId) {
            try {
                let formData = new FormData();
                formData.append('id_programacion', programacionId);
                formData.append('asientos', JSON.stringify(asientosReservados)); // Convertimos a string para enviarlo

                // Llamar a la API para liberar los asientos
                await fetch(`${_URL_}pasaje/delete_estadoProcesoAsiento`, {
                    method: "POST",
                    body: formData
                });
            } catch (error) {
                console.error("Error al liberar asientos:", error);
            }
        }

        // Eliminar solo los datos relacionados con la reserva
        localStorage.removeItem('asientos_reservados');
        localStorage.removeItem('id_programacion');

        // Redirigir al home después de un breve tiempo
        setTimeout(() => {
            window.location.href = $("#url_web").val();
        }, 500);
    } else {
        // Calcula minutos y segundos restantes
        const minutos = Math.floor(tiempoRestante / (60 * 1000));
        const segundos = Math.floor((tiempoRestante % (60 * 1000)) / 1000);

        timerContainer.innerHTML = `
            <div class="timer-container">
               <div class="purchase-timer" id="purchaseTimer">
                 <div class="timer-header">Termina tu compra en:</div>
                 <div class="timer-numbers">
                 <span id="hours">${minutos}</span> : <span id="minutes">${segundos.toString().padStart(2, '0')}</span>
                 </div>
                <div class="timer-labels">Minutos : Segundos</div>
                </div>
            </div>
        `;
    }
}
