<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link href="../img/favicon.ico" rel="icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/butoms.css">
    <link rel="stylesheet" href="../css/acerca.css">

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <title>Sobre Nosotros</title>
</head>

<body>


    <div class="about sticky-top"></div>

    <!-- carousel -->
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
    <header class="about-header text-center">
        <div class="container">
            <h1 class="animate-fade">¿Quiénes somos?</h1>
            <p class="animate-fade">Somos TID ofreciendo servicios de transporte de pasajeros y encomiendas a nivel nacional, para todos nuestros clientes.</p>
        </div>
    </header>

    <div class="content-section jungle-theme">
        <div class="container content-container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="images-layout">
                        <div class="image-wrapper main-img-wrapper animate-slide">
                            <img src="<?= URL_IMAGEN_WEB ?>carrito_tid.png" alt="Imagen Quienes Somos" class="img-fluid rounded-5 shadow-lg">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="text-column">
                        <div class="section-label">SOBRE NOSOTROS</div>
                        <h2 class="animate-fade">Transporte <span class="highlight-text">de Pasajeros</span></h2>

                        <p class="animate-fade text-justify">
                            <strong class="brand-name">TID DEMOSTRACIÓN</strong> Nos dedicamos a ofrecer servicio de transporte de pasajeros y encomiendas a nivel nacional, con un enfoque en la seguridad
                            cómodo y confiable. Con años de experiencia en el sector, nos especializamos en brindar viajes de calidad,
                            conectando diferentes destinos con puntualidad y eficiencia. Somos más que un servicio de transporte;
                            somos el puente que une personas y destinos, con la seguridad y confianza que nos caracteriza.
                        </p>

                        <div class="row mt-4 mb-4">
                            <div class="col-6">
                                <ul class="benefit-list">
                                    <li><i class="fas fa-check-square"></i> Viajes Locales</li>
                                    <li><i class="fas fa-check-square"></i> Seguridad 100%</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="benefit-list">
                                    <li><i class="fas fa-check-square"></i> Tracking en Ruta</li>
                                    <li><i class="fas fa-check-square"></i> Confort Total</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Características -->
    <section class="about-grid">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="about-icon">📦</div>
                        <h3>Nuestra Prioridad</h3>
                        <p>Brindar un servicio de logística y cargo seguro, confiable y de calidad para la satisfacción de nuestros clientes en toda la región.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="about-icon">🚚</div>
                        <h3>Nuestra Cobertura</h3>
                        <p>Ser líderes en el transporte terrestre de pasajeros y encomiendas en la región costa y selva del Perú, destacando por nuestra eficiencia y compromiso.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="about-icon">🤝</div>
                        <h3>Nuestro Compromiso</h3>
                        <p>Brindar un transporte seguro y confiable de pasajeros y encomiendas, con un servicio de calidad y buena atención al cliente.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Valores -->
    <section class="about-values">
        <div class="container values-container">
            <h2 class="values-title text-center">Nuestros Valores</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Seguridad</h4>
                        <p>Garantizamos un traslado seguro de pasajeros y encomiendas en cada servicio.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h4>Responsabilidad</h4>
                        <p>Cumplimos con los tiempos establecidos y respetamos a nuestros clientes.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Compromiso Social</h4>
                        <p>Mejoramos continuamente para ofrecer un mejor servicio y experiencia.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-award"></i>
                        </div>
                        <h4>Excelencia</h4>
                        <p>Buscamos calidad y mejora constante en todo lo que hacemos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Visión y Misión -->
    <div class="vm-container">
        <div class="container">
            <div class="vm-header text-center mb-5">
                <h1>Nuestra Filosofía Corporativa</h1>
                <p>Los principios fundamentales que guían cada decisión y acción en TID DEMOSTRACIÓN</p>
            </div>

            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="vm-card vision-card">
                        <div class="vm-card-content">
                            <h2 class="vm-card-title">Visión</h2>
                            <p class="vm-card-text">
                                Ser una empresa líder en el transporte terrestre de pasajeros en el Perú,
                                reconocida por brindar un servicio seguro, cómodo y confiable, conectando personas
                                y destinos con puntualidad y eficiencia.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="vm-card mision-card">
                        <div class="vm-card-content">
                            <h2 class="vm-card-title">Misión</h2>
                            <p class="vm-card-text">
                                En TID DEMOSTRACIÓN. brindamos un servicio de transporte de pasajeros enfocado en la seguridad,
                                comodidad y confianza, ofreciendo viajes de calidad que conectan distintos destinos,
                                respaldados por nuestra experiencia y compromiso con cada cliente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="<?= URL_WEB ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?= URL_WEB ?>js/main.js"></script>
    <script src="<?= URL_WEB ?>lib/wow/wow.min.js"></script>
    <script src="<?= URL_WEB ?>lib/easing/easing.min.js"></script>
    <script src="<?= URL_WEB ?>lib/waypoints/waypoints.min.js"></script>
    <script src="<?= URL_WEB ?>lib/owlcarousel/owl.carousel.min.js"></script>
    <script type="module" src="<?= URL_WEB ?>js/index.js"></script>

</body>

</html>