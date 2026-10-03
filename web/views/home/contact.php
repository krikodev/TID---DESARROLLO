<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/butoms.css">
    <link rel="stylesheet" href="../css/contacto.css">

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <title>Contacto</title>
</head>

<body>

    <div id="carouselExample" class="carousel slide position-relative w-100" style="height: 600px;" data-bs-ride="carousel">
        <!-- Indicadores -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner h-100">
            <!-- Slide 1 -->
            <div class="carousel-item active h-100 position-relative">
                <img src="<?= URL_IMAGEN_WEB ?>banners_1.png" alt="Camion Logistics" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-bottom from-transparent to-black opacity-80"></div>
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-center px-4 z-3">
                    <div class="max-width-800 animate-fade-in">
                        <h2 class="display-4 fw-bold text-white mb-4 text-shadow font-heading">Empresa de logística y transporte</h2>
                        <p class="fs-5 text-white text-shadow">Realizamos servicio de transporte de carga de mercancías, norte chico, sur chico y Huancayo.</p>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item h-100 position-relative">
                <img src="<?= URL_IMAGEN_WEB ?>banners_2.png" alt="Almacen de Encomiendas" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-bottom from-transparent to-black opacity-80"></div>
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-center px-4 z-3">
                    <div class="max-width-800">
                        <h2 class="display-4 fw-bold text-white mb-4 text-shadow font-heading">Encomiendas Seguras</h2>
                        <p class="fs-5 text-white text-shadow">Llegamos a donde otros no llegan, cuidando tu carga.</p>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item h-100 position-relative">
                <img src="<?= URL_IMAGEN_WEB ?>banners_3.png" alt="Viajes confortables" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-bottom from-transparent to-black opacity-80"></div>
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-center px-4 z-3">
                    <div class="max-width-800">
                        <h2 class="display-4 fw-bold text-white mb-4 text-shadow font-heading">Salidas diarias rápidos y seguros</h2>
                        <p class="fs-5 text-white text-shadow">Llegadas a tiempo con servicios personalizados de entrega y recojo.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles personalizados -->
        <button class="carousel-control-prev position-absolute start-0 top-50 translate-middle-y z-3 bg-white bg-opacity-10 bg-blur text-white p-3 rounded-circle border-0" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next position-absolute end-0 top-50 translate-middle-y z-3 bg-white bg-opacity-10 bg-blur text-white p-3 rounded-circle border-0" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- Page Header -->
    <header class="contact-header text-center">
        <div class="container">
            <h1 class="fade-in-up">Contáctanos</h1>
            <p class="fade-in-up delay-1">Estamos aquí para ayudarte en lo que necesites</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="contact-container">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="form-card fade-in-up delay-1">
                        <div class="form-header">
                            <h2>Envíanos un mensaje</h2>
                        </div>
                        <div class="form-body">
                            <form id="contactForm" action="https://formsubmit.co/contacto@turismoamanecer.com.pe" method="POST">
                                <input type="hidden" name="_captcha" value="false">
                                <input type="hidden" name="_template" value="table">
                                <input type="hidden" name="_subject" value="Nuevo mensaje de contacto - TURISMO AMANECER SELVA S.A.C.">

                                <div class="form-group">
                                    <label for="nombre" class="form-label">
                                        <i class="bi bi-person"></i>
                                        Contribuyente / Nombres y Apellidos
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="form-feedback error" id="nombre-error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="tipo-doc" class="form-label">
                                        <i class="bi bi-card-checklist"></i>
                                        Tipo de Documento
                                    </label>
                                    <select class="form-select" id="tipo-doc" name="tipo-doc" required>
                                        <option value="">Seleccione tipo de documento</option>
                                        <option value="dni">DNI</option>
                                        <option value="ruc">RUC</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="telefono" class="form-label">
                                        <i class="bi bi-telephone"></i>
                                        Teléfono
                                    </label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+51 XXX XXX XXX">
                                    <div class="form-feedback error" id="telefono-error"></div>
                                </div>

                                <div class="form-group" id="campo-doc-container" style="display: none;">
                                    <label id="label-doc" class="form-label" for="documento"></label>
                                    <input type="tel" class="form-control" id="documento" name="documento">
                                    <div class="form-feedback error" id="documento-error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i>
                                        Correo Electrónico
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="form-feedback error" id="email-error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="mensaje" class="form-label">
                                        <i class="bi bi-chat-text"></i>
                                        Mensaje
                                    </label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" rows="15" placeholder="Describe tu consulta o requerimiento..." required></textarea>
                                    <div class="form-feedback error" id="mensaje-error"></div>
                                </div>

                                <button type="submit" class="submit-btn">
                                    <i class="bi bi-send"></i> Enviar Mensaje
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="col-lg-6">
                    <div class="info-card fade-in-up delay-2">
                        <div class="info-header">
                            <h2>Información de Contacto</h2>
                        </div>
                        <div class="info-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Teléfonos</h4>
                                    <p><strong>Servicio al Cliente:</strong></p>
                                    <p>+51 984551160</p>
                                    <p><strong>WhatsApp:</strong> +51 957516751</p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Correo Electrónico</h4>
                                    <p>contactos@tid.com.pe</p>
                                    <p><strong>Consultas:</strong> tidhuancayo@gmail.com</p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Horario de Atención</h4>
                                    <p><strong>Lunes a Viernes:</strong> 07:00 AM - 06:30 PM</p>
                                    <p><strong>Sábados:</strong> 09:00 AM - 02:00 PM</p>
                                    <p><strong>Domingos:</strong> Cerrado</p>
                                </div>
                            </div>

                            <!-- Mapa y Oficinas -->
                            <div class="map-section">
                                <div class="map-selector">
                                    <label for="officeSelector">
                                        <i class="bi bi-geo-alt"></i>
                                        Buscanos en nuestras Oficinas
                                    </label>
                                    <select class="form-select" id="officeSelector" name="office">
                                        <option value="Lavictoria">La Victoria</option>
                                        <option value="Olivos">Los Olivos</option>
                                    </select>
                                </div>
                                <div class="contact-map">
                                    <iframe id="officeMap" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.288499890389!2d-75.2053734246089!3d-12.07079978809799!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x910e94f1a87a82e9%3A0xddb958497b3fc58a!2sHuancayo!5e0!3m2!1ses-419!2spe!4v1712588371889!5m2!1ses-419!2spe" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const officeMaps = {
            'Lavictoria': "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902.0125998023827!2d-77.03198062418532!3d-12.042653341825833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c9f4e0e28625%3A0xeed33be56ed230e1!2sAMANECER%20SELVA!5e0!3m2!1ses-419!2spe!4v1771174953921!5m2!1ses-419!2spe",
            'Olivos': "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902.803139714234!2d-77.06500919999999!3d-11.9881195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105cfa237d9fc65%3A0x766cff5c54993d00!2sTerminal%20ViaBuss!5e0!3m2!1ses-419!2spe!4v1771176823036!5m2!1ses-419!2spe",
            
        };

        document.getElementById('officeSelector').addEventListener('change', function() {
            const officeMap = document.getElementById('officeMap');
            const selectedOffice = this.value;
            
            if (officeMaps[selectedOffice]) {
                officeMap.src = officeMaps[selectedOffice];
            }
        });

        function mostrarCampoDocumento() {
            const tipo = document.getElementById('tipo-doc').value;
            const campo = document.getElementById('campo-doc-container');
            const label = document.getElementById('label-doc');
            const input = document.getElementById('documento');

            if (tipo === "dni") {
                campo.style.display = "block";
                label.innerHTML = '<i class="bi bi-person-badge"></i> DNI';
                input.name = "dni";
                input.placeholder = "Ingrese su DNI (8 dígitos)";
                input.maxLength = 8;
            } else if (tipo === "ruc") {
                campo.style.display = "block";
                label.innerHTML = '<i class="bi bi-building"></i> RUC';
                input.name = "ruc";
                input.placeholder = "Ingrese su RUC (11 dígitos)";
                input.maxLength = 11;
            } else {
                campo.style.display = "none";
                input.name = "";
                input.value = "";
            }
        }

        // Validación del formulario
        document.getElementById('tipo-doc').addEventListener('change', mostrarCampoDocumento);

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validarFormulario()) {
                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';
                submitBtn.disabled = true;

                setTimeout(() => {
                    alert('¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
                    this.reset();
                    document.getElementById('campo-doc-container').style.display = 'none';
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });

        function validarFormulario() {
            let isValid = true;
            const nombre = document.getElementById('nombre');
            const email = document.getElementById('email');
            const telefono = document.getElementById('telefono');
            const mensaje = document.getElementById('mensaje');
            const tipoDoc = document.getElementById('tipo-doc').value;
            const documento = document.getElementById('documento');

            document.querySelectorAll('.form-feedback').forEach(fb => {
                fb.style.display = 'none';
                fb.classList.remove('error', 'success');
            });
            document.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('error', 'success');
            });

            if (nombre.value.trim().length < 2) {
                mostrarError('nombre', 'El nombre debe tener al menos 2 caracteres');
                isValid = false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                mostrarError('email', 'Ingrese un correo electrónico válido');
                isValid = false;
            }

            if (telefono.value && !/^\+?[\d\s-]{9,}$/.test(telefono.value)) {
                mostrarError('telefono', 'Ingrese un número de teléfono válido');
                isValid = false;
            }

            if (tipoDoc && documento.style.display !== 'none') {
                if (tipoDoc === 'dni' && !/^\d{8}$/.test(documento.value)) {
                    mostrarError('documento', 'El DNI debe tener 8 dígitos');
                    isValid = false;
                } else if (tipoDoc === 'ruc' && !/^\d{11}$/.test(documento.value)) {
                    mostrarError('documento', 'El RUC debe tener 11 dígitos');
                    isValid = false;
                }
            }

            if (mensaje.value.trim().length < 10) {
                mostrarError('mensaje', 'El mensaje debe tener al menos 10 caracteres');
                isValid = false;
            }

            return isValid;
        }

        function mostrarError(campo, mensaje) {
            const input = document.getElementById(campo);
            const feedback = document.getElementById(campo + '-error');
            
            input.classList.add('error');
            feedback.textContent = mensaje;
            feedback.classList.add('error');
            feedback.style.display = 'block';
        }

        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.id === 'nombre' && this.value.trim().length < 2) {
                    mostrarError(this.id, 'El nombre debe tener al menos 2 caracteres');
                } else if (this.id === 'email' && this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    mostrarError(this.id, 'Ingrese un correo electrónico válido');
                } else if (this.id === 'telefono' && this.value && !/^\+?[\d\s-]{9,}$/.test(this.value)) {
                    mostrarError(this.id, 'Ingrese un número de teléfono válido');
                } else if (this.id === 'mensaje' && this.value.trim().length < 10) {
                    mostrarError(this.id, 'El mensaje debe tener al menos 10 caracteres');
                } else {
                    const feedback = document.getElementById(this.id + '-error');
                    if (feedback) {
                        feedback.style.display = 'none';
                        this.classList.remove('error');
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in-up');
            elements.forEach((el, index) => {
                el.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>


    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script type="module" src="../js/index.js"></script>

</body>

</html>