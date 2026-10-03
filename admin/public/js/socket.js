class PasajesWebSocket {
    constructor(url) {
        this.url = url;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 5000;
        this.isConnected = false;
        this.lastMessageTime = Date.now();

        this.connect();
        this.initializeEventListeners();
    }

    connect() {
        try {
            console.log('Conectando al servidor de pasajes...');
            this.ws = new WebSocket(this.url);

            this.ws.onopen = () => {
                console.log('Conexión establecida con el servidor de pasajes');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.mostrarEstadoConexion(true);
            };

            this.ws.onmessage = this.handleMessage.bind(this);
            this.ws.onerror = this.handleError.bind(this);
            this.ws.onclose = this.handleClose.bind(this);
        } catch (error) {
            console.error('Error al crear conexión:', error);
            this.handleError(error);
        }
    }

    handleMessage(event) {
        try {
            const data = JSON.parse(event.data);
            console.log('Mensaje recibido:', data);

            if (data.tipo === 'venta_nueva') {
                this.procesarNuevaVenta(data);
            }
        } catch (error) {
            console.error('Error al procesar mensaje:', error);
        }
    }

    procesarNuevaVenta(data) {
        // Verificar si hay un vehículo seleccionado
        const btnSeleccion = document.querySelector(".btn_selection_programacion.active");
        if (!btnSeleccion) {
            return;
        }

        const idVehiculo = btnSeleccion.closest("tr").querySelector(".id_vehiculo").value;

        // Verificar si la venta afecta al vehículo actual
        if (data.id_vehiculo === idVehiculo) {
            // Actualizar la interfaz
            this.actualizarInterfazVenta(data);

            // Mostrar notificación
            this.mostrarNotificacionVenta(data);
        }
    }

    actualizarInterfazVenta(data) {
        try {
            // Actualizar los asientos
            if (typeof window.show_vehiculo === 'function') {
                window.show_vehiculo(data.id_vehiculo);
            }

            // Reproducir sonido de notificación
            this.reproducirSonidoNotificacion();
        } catch (error) {
            console.error('Error al actualizar interfaz:', error);
        }
    }

    mostrarNotificacionVenta(data) {
        const mensaje = `
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                Nueva venta realizada - Asiento ${data.numero_asiento}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const notificacionContainer = document.getElementById('notificacion-container');
        if (notificacionContainer) {
            notificacionContainer.innerHTML = mensaje;

            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                const alert = notificacionContainer.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }

    notificarNuevaVenta(datosVenta) {
        if (!this.isConnected) {
            console.error('No hay conexión con el servidor');
            return;
        }

        try {
            const mensaje = {
                tipo: 'venta_nueva',
                id_vehiculo: datosVenta.id_vehiculo,
                numero_asiento: datosVenta.numero_asiento,
                timestamp: Date.now(),
                // Otros datos relevantes de la venta
            };

            this.ws.send(JSON.stringify(mensaje));
        } catch (error) {
            console.error('Error al notificar venta:', error);
        }
    }

    handleError(error) {
        console.error('Error en WebSocket:', error);
        this.mostrarEstadoConexion(false);
    }

    handleClose() {
        this.isConnected = false;
        this.mostrarEstadoConexion(false);

        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Reconectando... Intento ${this.reconnectAttempts}`);
            setTimeout(() => this.connect(), this.reconnectInterval);
        }
    }

    mostrarEstadoConexion(conectado) {
        const estadoElement = document.getElementById('estado-conexion');
        if (estadoElement) {
            if (conectado) {
                estadoElement.className = 'badge bg-success';
                estadoElement.textContent = 'Conectado';
            } else {
                estadoElement.className = 'badge bg-danger';
                estadoElement.textContent = 'Desconectado';
            }
        }
    }

    reproducirSonidoNotificacion() {
        try {
            const audio = new Audio('/ruta/a/tu/sonido-notificacion.mp3');
            audio.play();
        } catch (error) {
            console.error('Error al reproducir sonido:', error);
        }
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    // Asegúrate de que estos elementos existan en tu HTML
    const html = `
        <div id="estado-conexion" class="badge bg-secondary">Desconectado</div>
        <div id="notificacion-container"></div>
    `;

    // Insertar elementos en el DOM si no existen
    if (!document.getElementById('estado-conexion')) {
        const div = document.createElement('div');
        div.innerHTML = html;
        document.body.insertBefore(div, document.body.firstChild);
    }

    // Iniciar WebSocket
    window.pasajesWS = new PasajesWebSocket('wss://tid.net.pe:8085');
});

// Ejemplo de uso en tu código de venta
function realizarVenta(datosVenta) {
    // Tu código actual de venta

    // Después de realizar la venta exitosamente
    if (window.pasajesWS) {
        window.pasajesWS.notificarNuevaVenta(datosVenta);
    }
}