// Script para manejar el comportamiento sticky y responsive
function manejarBarraFlotante() {
    const resolucionActual = window.innerWidth;
    const elemento = document.getElementById("floating-search");
    const body = document.body;

    if (resolucionActual < 992) {
        // En dispositivos móviles, removemos el sticky
        elemento.classList.remove("custom-sticky");
        elemento.classList.remove("sticky");
        body.classList.remove("sticky-active");

        // Asegurar posicionamiento correcto en móvil
        elemento.style.position = 'absolute';
        elemento.style.bottom = '40px';
        elemento.style.top = 'auto';
    } else {
        // En desktop, añadimos funcionalidad sticky
        elemento.classList.add("custom-sticky");

        // Detectar cuando hacer sticky basado en scroll
        const carrusel = document.getElementById("carouselExample");
        if (carrusel) {
            const carruselHeight = carrusel.offsetHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > carruselHeight - 150) {
                elemento.classList.add("sticky");
                body.classList.add("sticky-active");
            } else {
                elemento.classList.remove("sticky");
                body.classList.remove("sticky-active");

                // Asegurar posicionamiento correcto cuando no es sticky
                elemento.style.position = 'absolute';
                elemento.style.bottom = '60px';
                elemento.style.top = 'auto';
            }
        }
    }
}

// Función mejorada para el manejo de origen y destino
function inicializarSelectores() {
    const origenSelect = document.getElementById("origen");
    const destinoSelect = document.getElementById("destino");

    if (!origenSelect || !destinoSelect) return;

    // Almacena las opciones originales de destino
    const opcionesDestino = destinoSelect.innerHTML;

    // Inicializa el estado del selector de destino
    destinoSelect.disabled = true;
    destinoSelect.style.backgroundColor = "#f2f2f2";
    destinoSelect.style.color = "#999";

    // Evento para cambios en origen
    origenSelect.addEventListener("change", function () {
        const origenSeleccionado = this.value;

        if (origenSeleccionado) {
            // Habilita destino y restaura estilos
            destinoSelect.disabled = false;
            destinoSelect.style.backgroundColor = "#f8f9fa";
            destinoSelect.style.color = "#495057";

            // Filtra opciones de destino (excluye el origen seleccionado)
            const parser = new DOMParser();
            const tempDoc = parser.parseFromString(opcionesDestino, 'text/html');
            const opciones = tempDoc.querySelectorAll('option');

            let nuevasOpciones = '<option value="" selected>Selecciona destino</option>';
            opciones.forEach(opcion => {
                if (opcion.value !== origenSeleccionado && opcion.value !== '') {
                    nuevasOpciones += opcion.outerHTML;
                }
            });

            destinoSelect.innerHTML = nuevasOpciones;

            // Añade efecto visual de habilitado
            destinoSelect.style.transition = "all 0.3s ease";

        } else {
            // Deshabilita destino y resetea
            destinoSelect.disabled = true;
            destinoSelect.innerHTML = opcionesDestino;
            destinoSelect.value = "";
            destinoSelect.style.backgroundColor = "#f2f2f2";
            destinoSelect.style.color = "#999";
        }
    });

    // Evento para cambios en destino (validación adicional)
    destinoSelect.addEventListener("change", function () {
        const destinoSeleccionado = this.value;
        const origenSeleccionado = origenSelect.value;

        // Previene seleccionar el mismo lugar como origen y destino
        if (destinoSeleccionado === origenSeleccionado && destinoSeleccionado !== '') {
            this.value = "";

            // Mostrar mensaje de error (opcional)
            const errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-warning mt-2';
            errorMsg.style.cssText = 'font-size: 14px; padding: 8px 12px; border-radius: 8px;';
            errorMsg.textContent = 'El destino no puede ser igual al origen';

            this.parentNode.appendChild(errorMsg);

            // Remover mensaje después de 3 segundos
            setTimeout(() => {
                if (errorMsg.parentNode) {
                    errorMsg.parentNode.removeChild(errorMsg);
                }
            }, 3000);
        }
    });
}

// Función para validar el formulario antes del envío
function validarFormulario() {
    const form = document.getElementById("form-busqueda");

    if (form) {
        form.addEventListener("submit", function (e) {
            const origen = document.getElementById("origen").value;
            const destino = document.getElementById("destino").value;
            const fecha = document.getElementById("fecha_programacion").value;

            if (!origen || !destino || !fecha) {
                e.preventDefault();

                // Resaltar campos vacíos
                if (!origen) document.getElementById("origen").style.borderColor = "#dc3545";
                if (!destino) document.getElementById("destino").style.borderColor = "#dc3545";
                if (!fecha) document.getElementById("fecha_programacion").style.borderColor = "#dc3545";

                // Mostrar mensaje de error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger mt-3';
                errorMsg.style.cssText = 'font-size: 14px; padding: 12px; border-radius: 8px; text-align: center;';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, completa todos los campos';

                const container = document.querySelector('.search-container');
                container.appendChild(errorMsg);

                // Remover mensaje y bordes rojos después de 5 segundos
                setTimeout(() => {
                    if (errorMsg.parentNode) errorMsg.parentNode.removeChild(errorMsg);
                    document.getElementById("origen").style.borderColor = "";
                    document.getElementById("destino").style.borderColor = "";
                    document.getElementById("fecha_programacion").style.borderColor = "";
                }, 5000);

                return false;
            }

            // Si todo está correcto, mostrar loading en el botón
            const btnBuscar = form.querySelector('button[type="submit"]');
            const textoOriginal = btnBuscar.innerHTML;
            btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>BUSCANDO...';
            btnBuscar.disabled = true;

            // Restaurar botón después de 3 segundos (por si falla el envío)
            setTimeout(() => {
                btnBuscar.innerHTML = textoOriginal;
                btnBuscar.disabled = false;
            }, 3000);
        });
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {
    inicializarSelectores();
    validarFormulario();
    manejarBarraFlotante();
});

// Event listeners para scroll y resize
window.addEventListener("scroll", manejarBarraFlotante);
window.addEventListener("resize", manejarBarraFlotante);

// Función adicional para mejorar la experiencia de usuario
function mejorarExperienciaUsuario() {
    // Añadir animación al hacer focus en los campos
    const formControls = document.querySelectorAll('.form-control');

    formControls.forEach(control => {
        control.addEventListener('focus', function () {
            this.parentNode.style.transform = 'scale(1.02)';
            this.parentNode.style.transition = 'transform 0.2s ease';
        });

        control.addEventListener('blur', function () {
            this.parentNode.style.transform = 'scale(1)';
        });
    });

    // Añadir efecto hover mejorado al botón
    const btnBuscar = document.querySelector('.btn-custom');
    if (btnBuscar) {
        btnBuscar.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });

        btnBuscar.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(-2px) scale(1)';
        });
    }
}

// Llamar mejoras después de que todo esté cargado
window.addEventListener('load', mejorarExperienciaUsuario);