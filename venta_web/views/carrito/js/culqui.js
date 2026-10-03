/**
 * Clase para manejar pagos con Culqi
 * Soporta únicamente:
 * - Pagos con tarjeta (flujo inmediato con token)
 * - Pagos con código Yape (flujo inmediato con código)
 */
class CulqiPaymentManager {
    constructor(publicKey, config = {}) {
        this.publicKey = publicKey;
        this.culqiInstance = null;
        this.currentPaymentMethod = null;

        this.defaultConfig = {
            paymentMethods: {
                tarjeta: true,
                yape: true,
                billetera: false,
                bancaMovil: false,
                agente: false,
                cuotealo: false,
            },
            options: {
                lang: 'auto',
                installments: true,
                modal: true,
                container: "#culqi-container",
            },
            settings: {
                title: 'Tecnologia Informatica y Desarrollo',
                currency: 'PEN',
                amount: 0,
                xculqirsaid: 'e24c1bd2-6eab-4987-b4ea-1d80670137f3',
                rsapublickey: `-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCzmb7EYt4ojhkSuKiC/O7WyPYO
JY62qfnavuCAfB/pp2tdzAzJ9uKn9AI5HxS24e1ST8AsEdYSXUNqeDWII8akoS5D
c35/R1TZE/VRK45zQcpNkvWcgQa4tBLvUnNqwGCNC2MPtbbFgCMOIjvhq5jD2u+l
BcOfLAeXWU+9hn66bQIDAQAB
-----END PUBLIC KEY-----`
            },
            client: {
                email: '',
            },
            appearance: {
                theme: "default",
                hiddenCulqiLogo: false,
                hiddenBannerContent: false,
                hiddenBanner: false,
                hiddenToolBarAmount: false,
                hiddenEmail: false,
                menuType: "sidebar",
                buttonCardPayText: "Pagar con Tarjeta",
                logo: null,
                defaultStyle: {
                    bannerColor: "#00a19b",
                    buttonBackground: "#00a19b",
                    menuColor: "#ffffff",
                    linksColor: "#00a19b",
                    buttonTextColor: "#ffffff",
                    priceColor: "#333333",
                },
            }
        };

        this.config = this.mergeConfig(this.defaultConfig, config);
    }


    // ============ CONFIGURACIÓN ============

    mergeConfig(defaultConfig, customConfig) {
        const result = { ...defaultConfig };
        for (const key in customConfig) {
            if (customConfig[key] && typeof customConfig[key] === 'object' && !Array.isArray(customConfig[key])) {
                result[key] = this.mergeConfig(result[key] || {}, customConfig[key]);
            } else {
                result[key] = customConfig[key];
            }
        }
        return result;
    }

    updateConfig(newConfig) {
        this.config = this.mergeConfig(this.config, newConfig);
    }


    // ============ INICIALIZACIÓN ============

    initializeCardPayment(amount) {
        this.currentPaymentMethod = 'tarjeta';
        return this.initialize(amount, {
            paymentMethods: { tarjeta: true, yape: false, billetera: false, bancaMovil: false, agente: false, cuotealo: false }
        });
    }

    initializeYapeCodePayment(amount) {
        this.currentPaymentMethod = 'yape_code';
        return this.initialize(amount, {
            paymentMethods: { tarjeta: false, yape: true, billetera: false, bancaMovil: false, agente: false, cuotealo: false }
        });
    }

    initialize(amount, customConfig = {}) {
        const finalConfig = this.mergeConfig(this.config, customConfig);
        finalConfig.settings.amount = amount;
        finalConfig.options.paymentMethods = finalConfig.paymentMethods;
        finalConfig.options.paymentMethodsSort = Object.keys(finalConfig.paymentMethods);

        const culqiConfig = {
            settings: finalConfig.settings,
            client: finalConfig.client,
            options: finalConfig.options,
            appearance: finalConfig.appearance,
        };

        this.culqiInstance = new CulqiCheckout(this.publicKey, culqiConfig);
        this.culqiInstance.culqi = () => this.handleCulqiResponse();

        return this.culqiInstance;
    }


    // ============ MANEJO DE RESPUESTAS ============

    handleCulqiResponse() {
        if (this.culqiInstance.error) {
            this.handleError(this.culqiInstance.error);
            return;
        }

        if (this.culqiInstance.token) {
            const method = this.currentPaymentMethod;
            if (method === 'tarjeta' || method === 'yape_code') {
                this.handlePayment(this.culqiInstance.token, method);
            } else {
                // Método desconocido: loguear y tratar como tarjeta
                console.warn('Método de pago desconocido, procesando como tarjeta:', method);
                this.handlePayment(this.culqiInstance.token, 'tarjeta');
            }
        } else {
            this.handleError({ user_message: 'No se recibió token de Culqi' });
        }
    }

    handleError(error) {
        console.error('Error de Culqi:', error);
        this.showErrorMessage('Error en el pago', error.user_message || 'Ocurrió un error inesperado durante el procesamiento.');
    }


    // ============ PROCESAMIENTO DE PAGOS ============

    /**
     * Método unificado para tarjeta y Yape código.
     * FIX: elimina la duplicación entre handleCardPayment y handleYapeCodePayment.
     * FIX: loadingAlert eliminada (se declaraba pero nunca se usaba).
     * FIX: loader ahora se muestra y oculta en ambos métodos, no solo en tarjeta.
     */
    async handlePayment(token, method) {
        const isCard = method === 'tarjeta';
        const endpoint = isCard
            ? 'carrito/crear_cargo_unico'
            : 'carrito/crear_cargo_yape_codigo';

        try {
            $("#loader").removeClass("d-none");

            const url = $("#url_venta_web").val() + endpoint;
            const formData = this.buildFormData({ token: token.id, paymentMethod: method, flowType: 'immediate' });

            const response = await fetch(url, { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                this.close();

                Swal.fire({
                    title: isCard ? '¡Pago procesado exitosamente!' : '¡Pago con Yape procesado!',
                    html: `
                        <p><strong>Su pago ${isCard ? 'con tarjeta' : 'con código Yape'} ha sido procesado</strong></p>
                        <p><small>Finalizando su compra...</small></p>
                    `,
                    icon: 'success',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const resultado = await this.concretarVenta({
                        charge_id: data.data?.id || data.data?.order_id || data.id,
                        transaction_id: data.data?.transaction_id,
                        payment_method: method,
                        amount: this.config.settings.amount,
                        currency: 'PEN',
                    });

                    $("#loader").addClass("d-none");
                    Swal.close();

                    await this.showFinalSuccessMessage(
                        '¡Compra realizada con éxito!',
                        resultado.links,
                        resultado.pasajes
                    );

                    this.redirectToHome();

                } catch (ventaError) {
                    $("#loader").addClass("d-none");
                    Swal.close();
                    console.error('Error al concretar venta:', ventaError);
                    this.showErrorMessage(
                        'Error al finalizar compra',
                        'Su pago fue procesado pero hubo un error al finalizar la compra. Contacte con soporte.'
                    );
                }

            } else {
                $("#loader").addClass("d-none");
                this.close();
                this.showErrorMessage(
                    'Error en el pago',
                    data.message || `Hubo un problema al procesar su pago ${isCard ? 'con tarjeta' : 'con Yape'}.`
                );
            }

        } catch (error) {
            $("#loader").addClass("d-none");
            console.error('Error procesando pago:', error);
            this.close();
            this.showErrorMessage('Error de conexión', 'No se pudo conectar con el servidor para procesar el pago.');
        }
    }


    // ============ MÉTODOS AUXILIARES ============

    /**
     * Delega en window.concretarVentaFinal (definida en main.js).
     * FIX: ya no llama showFinalSuccessMessage aquí — evita el doble modal.
     * FIX: relanza el error para que handlePayment lo capture correctamente.
     */
    async concretarVenta(datosPago) {
        if (typeof window.concretarVentaFinal !== 'function') {
            throw new Error('concretarVentaFinal no está disponible. Verifica que main.js está cargado.');
        }
        return await window.concretarVentaFinal(datosPago);
    }

    buildFormData(additionalData = {}) {
        const formData = new FormData();

        formData.append('amount', this.config.settings.amount);

        Object.entries(additionalData).forEach(([key, value]) => {
            formData.append(key, value);
        });

        if (window.datosCompraProceso) {
            const dc = window.datosCompraProceso;
            formData.append('nombreComprador', dc.nombreComprador);
            formData.append('apellidosComprador', dc.apellidosComprador);
            formData.append('telefonoComprador', dc.telefonoComprador);
            formData.append('correoComprador', dc.email);
            formData.append('descripcion', dc.descripcion);
            if (dc.datosFactura) formData.append('datosFactura', JSON.stringify(dc.datosFactura));
            if (dc.datosOriginales) formData.append('datosOriginales', JSON.stringify(dc.datosOriginales));
        }

        if (typeof dataLocalStorage !== 'undefined') {
            Object.entries(dataLocalStorage).forEach(([key, value]) => {
                if (key === 'usuarios') return;
                formData.append(
                    key,
                    typeof value === 'object' && value !== null ? JSON.stringify(value) : value
                );
            });
        }

        return formData;
    }


    // ============ MENSAJES DE USUARIO ============

    async showFinalSuccessMessage(title, comprobantes, pasajes) {

        let html = `
        <p><b>Tu compra fue realizada con éxito</b></p>
        <p>También enviamos los comprobantes a tu correo.</p>
        <hr>
        <b>🎟 Tus Pasajes:</b><br><br>

        <div style="
            display:grid;
            grid-template-columns: repeat(2, 1fr);
            gap:15px;
            justify-items:center;
        ">
        `;

        pasajes.forEach(p => {

            html += `
        <div style="
            border:1px solid #eee;
            border-radius:10px;
            padding:15px;
            width:130px;
            text-align:center;
            box-shadow:0 2px 6px rgba(0,0,0,0.1);
        ">

            <a href="${p.url}" target="_blank" style="text-decoration:none;">
                <div style="
                    width:60px;
                    height:60px;
                    border-radius:50%;
                    background:#28a745;
                    color:white;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-weight:bold;
                    font-size:18px;
                    margin:auto;
                    cursor:pointer;
                ">
                    ${p.asiento}
                </div>
            </a>

            <div style="margin-top:10px;">
                <a href="${p.url}" download class="btn btn-success btn-sm">
                    Descargar
                </a>
            </div>

        </div>
        `;
        });

        html += `</div>`;

        return Swal.fire({
            title,
            html,
            icon: 'success',
            confirmButtonText: 'Continuar',
            confirmButtonColor: '#28a745',
            allowOutsideClick: false,
            width: 600
        });
    }

    /**
     * FIX: método único con animación.
     * Eliminada la versión duplicada (sin animación) que sobreescribía a esta.
     */
    showErrorMessage(title, text) {
        Swal.fire({
            icon: 'error',
            title, text,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545',
            showClass: { popup: 'animate__animated animate__shakeX' }
        });
    }

    async showSuccessMessage(title, text) {
        return Swal.fire({
            title, text,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            timer: 3000,
            timerProgressBar: true,
        });
    }


    // ============ CONTROL DEL MODAL ============

    open() {
        if (this.culqiInstance) {
            this.culqiInstance.open();
        } else {
            console.error('Culqi no ha sido inicializado. Llama a uno de los métodos initialize primero.');
        }
    }

    /**
     * FIX: método único con try/catch y fallback.
     * Eliminada la versión simple que sobreescribía a esta silenciosamente.
     */
    close() {
        try {
            if (this.culqiInstance) {
                this.culqiInstance.close();
            }
        } catch (error) {
            console.warn('Error al cerrar modal de Culqi:', error);
            try {
                if (typeof Culqi !== 'undefined' && Culqi.close) Culqi.close();
            } catch (forceError) {
                console.warn('No se pudo forzar el cierre del modal:', forceError);
            }
        }
    }

    cleanupCulqi() {
        try {
            this.close();
            document.querySelectorAll('[id*="culqi"], [class*="culqi"], .culqi-checkout')
                .forEach(el => el.parentNode?.removeChild(el));
            this.culqiInstance = null;
            this.currentPaymentMethod = null;
        } catch (error) {
            console.warn('Error en limpieza de Culqi:', error);
        }
    }

    destroy() {
        this.culqiInstance = null;
        this.currentPaymentMethod = null;
    }

    getCurrentPaymentMethod() {
        return this.currentPaymentMethod;
    }

    redirectToHome() {
        localStorage.clear();
        window.location.href = $("#url_venta_web").val();
    }
}


// ============ EXPORTACIONES ============

export default CulqiPaymentManager;

if (typeof window !== 'undefined') {
    window.CulqiPaymentManager = CulqiPaymentManager;
}