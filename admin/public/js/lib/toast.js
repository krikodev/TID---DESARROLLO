
export class Toast {
    constructor() {
        this.animation = Object.freeze(['fade', 'slide', 'plain']);
        this.type = Object.freeze(['info', 'warning', 'error', 'success']);
        this.position = Object.freeze([
            'bottom-left', 'bottom-right', 'bottom-center',
            'top-right', 'top-left', 'top-center',
            'mid-center'
        ])
    }

    // Métodos existentes - sin cambios
    static basic(type, title, text) {
        $.toast({
            heading: title,
            text: text,
            showHideTransition: 'slide',
            icon: type,
            position: 'top-center',
            loader: true,
            loaderBg: '#9EC600',
            stack: false
        })
    }

    static operacion_exitosa(text) {
        $.toast({
            heading: 'Operación exitosa',
            text: text,
            showHideTransition: 'slide',
            icon: 'success',
            position: 'top-center',
            loader: true,
            loaderBg: '#9EC600',
            stack: false
        })
    }

    static operacion_informativa(text) {
        $.toast({
            heading: 'Importante',
            text: text,
            showHideTransition: 'slide',
            icon: 'info',
            position: 'top-center',
            loader: true,
            loaderBg: '#9EC600',
            stack: false
        })
    }

    static operacion_erronea(text) {
        $.toast({
            heading: 'Operación errónea',
            text: text,
            showHideTransition: 'slide',
            icon: 'error',
            position: 'top-center',
            loader: true,
            loaderBg: '#9EC600',
            stack: false
        })
    }

    // Nuevos métodos añadidos

    // Notificaciones laterales derechas
    static notificacion_lateral(text) {
        $.toast({
            heading: 'Notificación',
            text: text,
            showHideTransition: 'fade',
            icon: 'info',
            position: 'top-right',
            loader: false,
            hideAfter: 4000,
            stack: 5
        })
    }

    static info_lateral(title, text) {
        $.toast({
            heading: title,
            text: text,
            showHideTransition: 'slide',
            icon: 'info',
            position: 'top-right',
            loader: true,
            loaderBg: '#3498db',
            hideAfter: false,
            stack: 3
        })
    }

    static advertencia_lateral(text) {
        $.toast({
            heading: 'Advertencia',
            text: text,
            showHideTransition: 'slide',
            icon: 'warning',
            position: 'top-right',
            loader: true,
            loaderBg: '#f39c12',
            stack: false
        })
    }

    static exito_lateral(text) {
        $.toast({
            heading: 'Completado',
            text: text,
            showHideTransition: 'fade',
            icon: 'success',
            position: 'bottom-right',
            loader: true,
            loaderBg: '#2ecc71',
            stack: 3,
            hideAfter: 3000
        })
    }

    // Notificaciones inferiores
    static info_inferior(text) {
        $.toast({
            heading: 'Información',
            text: text,
            showHideTransition: 'plain',
            icon: 'info',
            position: 'bottom-center',
            loader: false,
            hideAfter: 5000,
            stack: false
        })
    }

    static progreso_inferior(text) {
        $.toast({
            heading: 'Procesando...',
            text: text,
            showHideTransition: 'slide',
            icon: 'info',
            position: 'bottom-left',
            loader: true,
            loaderBg: '#9EC600',
            hideAfter: false, // No se oculta automáticamente
            stack: false
        })
    }

    // Notificaciones superiores izquierdas
    static alerta_superior_izquierda(text) {
        $.toast({
            heading: 'Alerta',
            text: text,
            showHideTransition: 'slide',
            icon: 'warning',
            position: 'top-left',
            loader: true,
            loaderBg: '#e67e22',
            stack: 4
        })
    }

    // Notificación central (modal-style)
    static mensaje_central(title, text) {
        $.toast({
            heading: title,
            text: text,
            showHideTransition: 'fade',
            icon: 'info',
            position: 'mid-center',
            loader: false,
            hideAfter: 6000,
            stack: false
        })
    }

    // Notificación rápida (sin loader, desaparece rápido)
    static notificacion_rapida(text) {
        $.toast({
            text: text,
            showHideTransition: 'fade',
            icon: 'success',
            position: 'top-right',
            loader: false,
            hideAfter: 2000,
            stack: 5
        })
    }

    // Error crítico
    static error_critico(text) {
        $.toast({
            heading: 'Error Crítico',
            text: text,
            showHideTransition: 'slide',
            icon: 'error',
            position: 'mid-center',
            loader: false,
            hideAfter: false, // Debe cerrarse manualmente
            stack: false,
            bgColor: '#c0392b',
            textColor: '#fff'
        })
    }

    // Actualización disponible
    static actualizacion_disponible(text) {
        $.toast({
            heading: 'Nueva actualización',
            text: text,
            showHideTransition: 'slide',
            icon: 'info',
            position: 'bottom-right',
            loader: false,
            hideAfter: 10000,
            stack: false,
            bgColor: '#34495e',
            textColor: '#ecf0f1'
        })
    }

    // Guardado automático
    static guardado_automatico() {
        $.toast({
            text: 'Cambios guardados automáticamente',
            showHideTransition: 'fade',
            icon: 'success',
            position: 'bottom-right',
            loader: false,
            hideAfter: 2000,
            stack: false
        })
    }

    // Personalizado con todas las opciones
    static personalizado(options) {
        const defaults = {
            heading: '',
            text: '',
            showHideTransition: 'slide',
            icon: 'info',
            position: 'top-right',
            loader: true,
            loaderBg: '#9EC600',
            stack: false,
            hideAfter: 3000
        };

        $.toast({ ...defaults, ...options });
    }
}