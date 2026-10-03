document.addEventListener('DOMContentLoaded', function () {

    // Cragando toda la data del navegador / Obtener los datos del localStorage
    let dataLocalStorage = obtenerDatosLocalStorage();
    console.log(dataLocalStorage)

    // APARTADO DE TODA LA CONFIGURACION DE LA PASARELA DE PAGOS DE CULQUI

    /* Las opciones se ordenan según se configuren */
    const paymentMethods = {
        tarjeta: true,
        yape: true,
        billetera: true,
        bancaMovil: true,
        agente: true,
        cuotealo: true,
    }

    /* Configuracion de la vista del DIV de pago */
    const options = {
        lang: 'auto',
        installments: true,
        modal: true,
        container: "#culqi-container",
        paymentMethods: paymentMethods,
        paymentMethodsSort: Object.keys(paymentMethods),
    }

    const client = {
        email: 'test2@demo.com',
    }

    const handleCulqiAction = () => {
        if (Culqi.token) {
            const token = Culqi.token.id;
            console.log('Se ha creado un Token: ', token);
            console.log('Token recibido:', token);

            const url = $("#url_web").val() + 'carrito/crear_cargo_unico';
            const formData = new FormData();
            formData.append('token', token);
            formData.append('amount', settings.amount);
            formData.append('order', settings.order);


            Object.keys(dataLocalStorage).forEach(key => {
                if (key === 'usuarios') return;

                if (typeof dataLocalStorage[key] === 'object' && dataLocalStorage[key] !== null) {
                    formData.append(key, JSON.stringify(dataLocalStorage[key]));
                } else {
                    formData.append(key, dataLocalStorage[key]);
                }
            });

            fetch(url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta del servidor:', data);
                    if (data.success) {
                        Swal.fire({
                            title: 'Pago exitoso',
                            text: 'Su pago se ha procesado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            localStorage.clear();
                            window.location.href = $("#url_web").val();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Hubo un problema al procesar su pago.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en la petición fetch:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, inténtelo de nuevo.'
                    });
                });
        } else if (Culqi.order) {
            const order = Culqi.order;
            console.log('Se ha creado el objeto Order: ', order);
        } else {
            console.log('Errorrr : ', Culqi.error);
        }
    }

    // Configuración inicial (se actualizará dinámicamente)
    let settings = {
        title: 'Culqi store 2',
        currency: 'PEN',
        amount: 0, // Se actualizará dinámicamente
        order: 'ord_live_d1P0Tu1n7Od4nZdp',
        xculqirsaid: 'e24c1bd2-6eab-4987-b4ea-1d80670137f3',
        rsapublickey: `-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCzmb7EYt4ojhkSuKiC/O7WyPYO
JY62qfnavuCAfB/pp2tdzAzJ9uKn9AI5HxS24e1ST8AsEdYSXUNqeDWII8akoS5D
c35/R1TZE/VRK45zQcpNkvWcgQa4tBLvUnNqwGCNC2MPtbbFgCMOIjvhq5jD2u+l
BcOfLAeXWU+9hn66bQIDAQAB
-----END PUBLIC KEY-----`
    }

    const appearance = {
        theme: "default",
        hiddenCulqiLogo: false,
        hiddenBannerContent: false,
        hiddenBanner: false,
        hiddenToolBarAmount: false,
        hiddenEmail: false,
        menuType: "sidebar",
        buttonCardPayText: "Pagar tal monto",
        logo: null,
        defaultStyle: {
            bannerColor: "blue",
            buttonBackground: "yellow",
            menuColor: "pink",
            linksColor: "green",
            buttonTextColor: "blue",
            priceColor: "red",
        },
    };

    // Variable global para almacenar la instancia de Culqi
    let Culqi = null;

    // Función para inicializar Culqi con el precio actualizado
    function initializeCulqi(amount) {
        // Actualizar el monto en settings
        settings.amount = amount;

        const config = {
            settings,
            client,
            options,
            appearance,
        };

        const publicKey = 'pk_test_OoprsleXqxamzyOn';
        Culqi = new CulqiCheckout(publicKey, config);

        Culqi.culqi = handleCulqiAction;

    }

    // Función para obtener datos de localStorage
    function obtenerDatosLocalStorage() {
        return JSON.parse(localStorage.getItem('venta')) || {};
    }

    // Manejador para el botón de pagar
    const btn_pagar = document.getElementById('btn_pagar');
    btn_pagar.addEventListener('click', function (e) {
        e.preventDefault();

        // Obtener el precio del localStorage
        const dataLocalStorage = obtenerDatosLocalStorage();
        const precioTotal = dataLocalStorage.sumaTotal || dataLocalStorage.precioData || 0;

        if (precioTotal <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el precio del viaje. Por favor, seleccione un viaje primero.'
            });
            return;
        }

        // Convertir el precio a centavos (Culqi maneja centavos)
        const precioEnCentavos = Math.round(precioTotal * 100);

        // Inicializar Culqi con el precio actualizado
        initializeCulqi(precioEnCentavos);


        // Abrir el checkout
        Culqi.open();

    });

    // Manejador para la pestaña de buses (actualizar información y precio)
    const btn_pago = document.getElementById("pills-pago-tab");
    btn_pago.addEventListener("click", function () {
        if (!localStorage.getItem("venta")) {
            return;
        }

        $('#spinner').removeClass('show');

        // Extraer datos principales
        const {
            NombreO: origenData,
            NombreD: destinoData,
            sumaTotal: precioData,
            asientosSeleccionadosData: asientosData,
            Descripcion: descripcionData,
            fechaIda: fechaData,
            hora_salida: horaData,
            pasajeros: usuarios
        } = dataLocalStorage;

        // Actualizar la información del viaje en la interfaz
        document.getElementById('origen_d').textContent = origenData.toUpperCase();
        document.getElementById('destino_d').textContent = destinoData.toUpperCase();
        document.getElementById('precio').textContent = precioData;
        document.getElementById('numAsientos_d').textContent =
            Array.isArray(asientosData) ? asientosData.map(seat => seat.numAsiento).join(', ') : '';
        document.getElementById('descripcion_d').textContent = descripcionData.toUpperCase();
        document.getElementById('fecha').textContent = fechaData;
        document.getElementById('hora').textContent = horaData;

        // Crear formularios de pasajeros
        const div_padre_user = document.getElementById('DivDatosPasajero');
        div_padre_user.innerHTML = '';

        usuarios.forEach((usuario) => {
            const nombreFormat = (usuario.nombres ?? "N/A").toUpperCase();
            const apellidoFormat = (usuario.apellidos ?? "N/A").toUpperCase();
            const numDoc = usuario.num_doc ?? "N/A";
            const email = usuario.email ?? "N/A";
            const telefono = usuario.telefono ?? "N/A";

            div_padre_user.insertAdjacentHTML('beforeend', `
                <div class="bg-light p-3 rounded-3">
                    <div class="fw-medium">${nombreFormat} ${apellidoFormat} | DNI: ${numDoc}</div>
                    <div class="text-secondary small">${email} | ${telefono}</div>
                </div>
            `);
        });

        // Actualizar el texto del botón de pago con el precio
        if (btn_pagar && precioData) {
            btn_pagar.textContent = `Pagar S/ ${precioData}`;
        }
    });

    const btnConfigVenta = document.getElementById('btnConfig');
    const InputComprobante = document.getElementById('inputComprobante');

    btnConfigVenta.addEventListener('click', function () {
        const inputComprobanteValue = InputComprobante.value.trim();

        if (inputComprobanteValue !== '') {
            let dataLocalStorage = JSON.parse(localStorage.getItem('venta'));
            dataLocalStorage.inputComprobanteValue = inputComprobanteValue;
            localStorage.setItem('venta', JSON.stringify(dataLocalStorage));
            localStorage.setItem('comprobante', inputComprobanteValue);

            const url = $("#url_web").val() + 'carrito/registro_usuario';
            const formData = new FormData();

            if (dataLocalStorage.usuarios) {
                formData.append('usuarios', JSON.stringify(dataLocalStorage.usuarios));
            }

            Object.keys(dataLocalStorage).forEach(key => {
                if (key === 'usuarios') return;

                if (typeof dataLocalStorage[key] === 'object' && dataLocalStorage[key] !== null) {
                    formData.append(key, JSON.stringify(dataLocalStorage[key]));
                } else {
                    formData.append(key, dataLocalStorage[key]);
                }
            });

            fetch(url, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Su reserva se ha registrado correctamente',
                            text: 'Se le envía al correo el detalle de su reserva',
                            icon: 'success',
                            confirmButtonText: 'ACEPTAR Y SALIR',
                        }).then(() => {
                            localStorage.clear();
                            window.location.href = $("#url_web").val();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Hubo un problema al procesar su reserva'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en la petición fetch:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, inténtelo de nuevo.'
                    });
                });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Advertencia',
                text: 'El campo está vacío. Por favor, ingrese el número de operación.'
            });
        }
    });
});

// const devideFingerPrintId = await Culqi3DS.generateDevice();

// if (devideFingerPrintId) {
//     Culqi3DS.initAuthentication("tkn_live_xxxxxxxxxxxxxxxx");
//     /* '8019959c-fab1-49eb-bbbe-b846d308d8df' */
// } else {
//     /* Error al generar el device */
// }

// Culqi3DS.reset();