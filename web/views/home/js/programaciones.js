import {
    StorageManager
} from './storageManager.js';

document.addEventListener("DOMContentLoaded", () => {
    let _URL_VENTA_WEB = document.getElementById("url_venta_web").value;
    const Storage = new StorageManager({});

    localStorage.clear();

    document.getElementById("origen").value = '';
    document.getElementById("destino").value = '';

    // Manejar el envío del formulario
    document.getElementById("form-busqueda").addEventListener("submit", (e) => {
        e.preventDefault()

        const origen = document.getElementById("origen").value
        const destino = document.getElementById("destino").value
        const fecha = document.getElementById("fecha_programacion").value

        if (!origen || !destino || !fecha) {
            Swal.fire({
                title: 'Campos incompletos',
                text: 'Por favor, complete todos los campos: origen, destino y fecha.',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Verificar si es para hoy y redirigir directamente
        const hoy = new Date().toISOString().split("T")[0];
        if (fecha === hoy) {
            verificarYRedirigir(origen, destino, fecha);
        } else {
            // Para fechas futuras, buscar normalmente
            buscarProgramaciones(origen, destino, fecha);
        }
    })

    // Nueva función para verificar disponibilidad para hoy y redirigir
    function verificarYRedirigir(origen, destino, fecha) {
        // Mostrar loading
        Swal.fire({
            title: 'Verificando disponibilidad...',
            text: 'Buscando programaciones para hoy',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('fechaIda', fecha);
        formData.append('origen', origen);
        formData.append('destino', destino);

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
                Swal.close();

                if (data.success) {
                    // Guardar datos para búsqueda automática en el carrito
                    const datosBusqueda = {
                        origen: origen,
                        destino: destino,
                        fecha_programacion: fecha,
                        timestamp: Date.now(),
                        busqueda_automatica: true
                    };

                    // Guardar en storage normal
                    Storage.actualizar({
                        origen: origen,
                        destino: destino,
                        fecha_programacion: fecha,
                    });

                    // Guardar datos para búsqueda automática
                    localStorage.setItem('busqueda_pendiente', JSON.stringify(datosBusqueda));

                    // Redirigir con parámetro especial
                    window.location.href = `${_URL_VENTA_WEB}carrito?auto_search=true`;
                } else {
                    Swal.fire({
                        title: 'Sin disponibilidad',
                        text: 'No se han encontrado programaciones disponibles para hoy.',
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
                Swal.close();
                console.error("Error al verificar programaciones:", error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al verificar la disponibilidad. Intente nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
    }

    // Función original para fechas futuras
    function buscarProgramaciones(origen, destino, fechaIda) {
        const formData = new FormData();
        formData.append('fechaIda', fechaIda);
        formData.append('origen', origen);
        formData.append('destino', destino);

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
                    const datosBusqueda = {
                        origen: origen,
                        destino: destino,
                        fecha_programacion: fechaIda,
                        timestamp: Date.now(),
                        busqueda_automatica: true
                    };

                    Storage.actualizar({
                        origen: origen,
                        destino: destino,
                        fecha_programacion: fechaIda,
                    });
                    localStorage.setItem('busqueda_pendiente', JSON.stringify(datosBusqueda));

                    window.location.href = `${_URL_VENTA_WEB}carrito?auto_search=true`;
                } else {
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
            });
    }

    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split("T")[0]
    document.getElementById("fecha").setAttribute("min", today)
})