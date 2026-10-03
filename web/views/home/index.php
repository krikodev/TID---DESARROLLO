<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= URL_IMAGEN_ADMIN ?>icono.png?v=2">
    <title><?= NAME_BUSINESS ?></title>
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Icon FontAwesome 5.10.0 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Jquery 3.6.0 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- Libreria de FlatPick -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/l10n/es.js"></script>

    <!-- Otras librerias -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" rel="stylesheet">

</head>
<style>
    /* cambio de espacio del navegador */
    .custom-sticky {
        position: sticky;
        top: 81px;
        /* Ajusta este valor según necesites */
        z-index: 1020;
    }
</style>

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

        <!-- BARRA DE BÚSQUEDA MOVIDA AQUÍ DENTRO -->
        <div class="floating-search-wrapper" id="floating-search" style="<?= $this->venta_web == 1 ? '' : 'display:none'  ?>">
            <div class="py-3 custom-sticky for-principal" id="busqueda-programaciones">
                <div class="container-fluid">
                    <div class="search-container">
                        <form id="form-busqueda" class="row g-3 align-items-end">
                            <!-- Todo tu formulario aquí igual que antes -->
                            <!-- Campo Origen -->
                            <div class="col-12 col-md-3">
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                    <select class="form-control" id="origen" name="origen">
                                        <?php if ($this->terminal["success"]) : ?>
                                            <option value="" selected>Selecciona origen</option>
                                            <?php for ($i = 0; $i < count($this->terminal["message"]); $i++) : ?>
                                                <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                                                <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
                                            <?php endfor ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Campo Destino -->
                            <div class="col-12 col-md-3">
                                <div class="input-with-icon">
                                    <i class="fas fa-flag-checkered input-icon"></i>
                                    <select class="form-control" id="destino" name="destino"
                                        style="background-color: #f2f2f2; color: #999;">
                                        <?php if ($this->terminal["success"]) : ?>
                                            <option value="" selected>Selecciona destino</option>
                                            <?php for ($i = 0; $i < count($this->terminal["message"]); $i++) : ?>
                                                <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                                                <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
                                            <?php endfor ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Campo Fecha -->
                            <div class="col-12 col-md-3">
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar-alt input-icon"></i>
                                    <input
                                        type="date"
                                        name="fecha_programacion"
                                        id="fecha_programacion"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <!-- Botón de Búsqueda -->
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-custom w-100">
                                    <i class="fas fa-search me-2"></i>
                                    BUSCAR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin del carrusel que ahora incluye la barra de búsqueda -->

    <div id="carga" class="carga">
        <p>Buscando buses...</p>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>



    <?php
    include 'pago.php';
    include 'viajes.php';
    ?>

    <!-- Inicio de seccion de conectamos -->

    <section class="conectamos">
        <div class="title-container">
            <h2>Conectamos el mundo</h2>
            <p class="intro">
                Hacemos que tus envíos, mudanzas y encomiendas lleguen con rapidez, seguridad y confianza,
                sin importar la distancia.
            </p>
        </div>

        <div class="description-container">
            <div class="cards-container">
                <article class="card">
                    <div class="card-image-container">
                        <img src="<?= URL_IMAGEN_WEB ?>almacen.jpg" alt="Encomiendas y Mudanzas" />
                        <div class="card-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3>Nuestra estrategia</h3>
                        <p>
                            Optimizamos cada proceso logístico para garantizar entregas eficientes y puntuales, cuidando siempre tus pertenencias.
                        </p>
                    </div>
                </article>

                <article class="card">
                    <div class="card-image-container">
                        <img src="<?= URL_IMAGEN_WEB ?>tid_liderazgo.png" alt="Camión de carga" />
                        <div class="card-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3>Liderazgo</h3>
                        <p>
                            Con experiencia y un equipo especializado, lideramos soluciones de transporte que generan confianza en cada cliente.
                        </p>
                    </div>
                </article>

                <article class="card">
                    <div class="card-image-container">
                        <img src="<?= URL_IMAGEN_WEB ?>compu_digital.png" alt="Innovación" />
                        <div class="card-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3>Impulsado por la innovación</h3>
                        <p>
                            Integramos tecnología en tiempo real para rastrear tu carga y ofrecer un servicio moderno, ágil y seguro.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Fin de seccion conectamos -->

    <script src="<?php echo URL_WEB ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo URL; ?>public/plugins/sweetalert2/sweetalert2@11.js"></script>
    <script type="module" src="<?php echo URL_WEB ?>js/index.js"></script>

    <script src="<?php echo URL_WEB ?>lib/easing/easing.min.js"></script>
    <script src="<?php echo URL_WEB ?>lib/waypoints/waypoints.min.js"></script>
    <script src="<?php echo URL_WEB ?>lib/owlcarousel/owl.carousel.min.js"></script>
</body>

</html>