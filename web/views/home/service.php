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

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/butoms.css">
    <link rel="stylesheet" href="../css/service.css">

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <title>Servicios</title>
</head>

<body>

    <!-- Carrusel de imágenes -->
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

    <!-- Header -->
    <header class="services-header text-center">
        <div class="container">
            <h1 class="animate-fade-in">Servicios de Pasajeros y Cargos</h1>
            <p class="animate-fade-in">Soluciones confiables para todas tus necesidades</p>
        </div>
    </header>

    <section class="passenger-service-section py-5">
        <div class="container">
            <div class="text-center mb-5 animate-fade-in">
                <h6 class="text-uppercase fw-bold text-success">¿Por qué elegirnos?</h6>
                <h2 class="display-5 fw-bold main-title">Servicio de Pasajeros <span class="text-highlight">Premium</span></h2>
            </div>

            <div class="row align-items-center justify-content-center">
                <div class="col-md-4">
                    <div class="feature-item text-md-end mb-5 animate-fade-in">
                        <div class="d-flex flex-row-reverse align-items-center">
                            <div class="feature-icon ms-3">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Equipo Experto</h4>
                                <p>Conductores certificados con amplia experiencia en las rutas de la selva y manejo defensivo.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-item text-md-end mb-5 animate-fade-in">
                        <div class="d-flex flex-row-reverse align-items-center">
                            <div class="feature-icon ms-3">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Cliente al Centro</h4>
                                <p>Priorizamos tu comodidad y seguridad desde el embarque hasta tu destino final.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-item text-md-end mb-5 animate-fade-in">
                        <div class="d-flex flex-row-reverse align-items-center">
                            <div class="feature-icon ms-3">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Tarifas Justas</h4>
                                <p>Precios competitivos con la mejor relación calidad-servicio del mercado nacional.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center my-4 my-md-0 animate-fade-in">
                    <div class="main-service-image-container">
                        <img src="<?= URL_IMAGEN_WEB ?>asiento_vip.png" alt="Interior Bus Premium" class="img-fluid rounded-4 shadow-lg border-gold">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-item mb-5 animate-fade-in">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Soporte Confiable</h4>
                                <p>Atención al pasajero 24/7 y monitoreo GPS en tiempo real durante todo el trayecto.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-item mb-5 animate-fade-in">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3">
                                <i class="fas fa-bus"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Flota Moderna</h4>
                                <p>Buses de última generación con asientos reclinables de 160° y 180° grados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-item mb-5 animate-fade-in">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Cuidado Integral</h4>
                                <p>Protocolos estrictos de limpieza y mantenimiento técnico preventivo diario.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="logistics-features py-5">
        <div class="container">
            <div class="text-center mb-5 animate-fade-in">
                <span class="subtitle text-uppercase fw-bold">Brindamos lo mejor</span>
                <h2 class="main-title fw-bold">Servicio de Encomiendas y Cargos</h2>
                <div class="title-divider mx-auto"></div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-4">
                    <div class="feature-box text-lg-end mb-5 animate-fade-in">
                        <div class="d-flex flex-row-reverse align-items-start">
                            <div class="feature-icon-wrapper ms-3">
                                <div class="icon-circle"><i class="fas fa-plane-departure"></i></div>
                            </div>
                            <div class="feature-content">
                                <h4>Entrega de Paqueteria</h4>
                                <p>Entregas urgentes a las principales ciudades de la selva con prioridad de despacho y máxima rapidez.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-box text-lg-end mb-5 animate-fade-in">
                        <div class="d-flex flex-row-reverse align-items-start">
                            <div class="feature-icon-wrapper ms-3">
                                <div class="icon-circle"><i class="fas fa-truck-loading"></i></div>
                            </div>
                            <div class="feature-content">
                                <h4>Transporte Terrestre</h4>
                                <p>Servicio logístico nacional con unidades modernas preparadas para la geografía del norte y sur chico.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 text-center mb-5 mb-lg-0 animate-fade-in">
                    <div class="central-image-container">
                        <img src="<?= URL_IMAGEN_WEB ?>currier.png" alt="Servicio de Carga" class="img-fluid">
                        <div class="map-bg-overlay"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-box mb-5 animate-fade-in">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon-wrapper me-3">
                                <div class="icon-circle"><i class="fas fa-ship"></i></div>
                            </div>
                            <div class="feature-content">
                                <h4>Logística Cargos</h4>
                                <p>Especialistas en la selva baja, conectando destinos con seguridad y conocimiento de ruta.</p>
                            </div>
                        </div>
                    </div>
                    <div class="feature-box mb-5 animate-fade-in">
                        <div class="d-flex align-items-start">
                            <div class="feature-icon-wrapper me-3">
                                <div class="icon-circle"><i class="fas fa-shipping-fast"></i></div>
                            </div>
                            <div class="feature-content">
                                <h4>Entrega a Tiempo</h4>
                                <p>Compromiso de entrega con seguimiento en tiempo real y evidencia digital de recepción en destino.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script type="module" src="../js/index.js"></script>
</body>

</html>