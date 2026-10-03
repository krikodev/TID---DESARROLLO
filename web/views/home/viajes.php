<!DOCTYPE html>
<html lang="es">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.css">
    <style>
        :root {
            --primary-color: #0F5B99;
            --primary-dark: #0C4A7D;
            --white: #ffffff;
            --dark-color: #1a1a1a;
            --text-color: #333;
            --text-light: #666;
            --accent: #ff9800;
            --box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fc;
        }

        /* Header con estilo refinado */
        .destinations-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2.5rem;
            padding: 0 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-left h2 {
            font-size: 1rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2.5px;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .header-left h1 {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        .header-right p {
            font-size: 0.95rem;
            color: #4a4a4a;
            max-width: 420px;
            line-height: 1.5;
            margin: 0;
        }

        /* Swiper container */
        .swiper-container {
            padding: 1rem 0 2.5rem;
            position: relative;
            width: 100%;
        }

        /* ===== TARJETA REDISEÑADA: imagen de fondo + texto superpuesto ===== */
        .destination-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            height: 450px; /* altura fija para desktop */
            width: 100%;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.3, 1);
            border: none;
            background: #ffffff;
        }

        .destination-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 45px rgba(0, 100, 0, 0.25);
        }

        .destination-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .destination-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .destination-card:hover .destination-image img {
            transform: scale(1.1);
        }

        /* Overlay oscuro suave para legibilidad del texto */
        .destination-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 70%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 40%, transparent 100%);
            z-index: 1;
            pointer-events: none;
        }

        .destino-label {
            position: absolute;
            top: 18px;
            left: 18px;
            background: var(--primary-color);
            color: var(--white);
            padding: 6px 16px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-radius: 50px;
            z-index: 3;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .destination-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 1.8rem 1.5rem 1.5rem;
            z-index: 2;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .viaja-a {
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.2rem;
        }

        .city-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            line-height: 1.2;
            color: green;
            text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff,
                0 2px 5px rgba(0, 128, 0, 0.4);
        }

        .agency-address-preview {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
            line-height: 1.4;
            max-width: 95%;
        }

        .agency-address-preview i {
            color: var(--accent);
            font-size: 1rem;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }

        .rating {
            color: #FFD700;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.3));
        }

        /* Botón CONOCE MÁS con estilo glassmorphism */
        .btn-conoce-mas {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.7rem 1rem;
            border-radius: 60px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.2rem;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-conoce-mas:hover {
            background: white;
            color: var(--primary-color);
            border-color: white;
            transform: scale(1.02);
        }

        .btn-conoce-mas i {
            font-size: 1rem;
        }

        /* Ocultar botones de navegación */
        #swiper-destinos .swiper-button-next,
        #swiper-destinos .swiper-button-prev {
            display: none !important;
        }

        .swiper-pagination {
            position: relative;
            margin-top: 2rem;
        }

        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: rgba(0, 128, 0, 0.3);
            opacity: 1;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .swiper-pagination-bullet-active {
            background: var(--primary-color);
            transform: scale(1.3);
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.5);
        }

        /* ===== MODALES (se mantienen igual, funcionales, solo refinamos bordes) ===== */
        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        .modal-dialog {
            margin: 0.5rem;
            max-width: 95vw;
        }

        @media (min-width: 576px) {
            .modal-dialog {
                margin: 1.75rem auto;
                max-width: 90vw;
            }
        }

        .modal-content-custom {
            border: none;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 60, 0, 0.5);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header-custom {
            background: linear-gradient(145deg, #008000, #00a34e);
            color: white;
            border-bottom: none;
            padding: 1rem 1.8rem;
            flex-shrink: 0;
        }

        .modal-header-custom .modal-title {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .modal-header-custom .btn-close:hover {
            opacity: 1;
        }

        .modal-body-custom {
            padding: 0;
            background: #f0f2f5;
            overflow-y: auto;
            flex: 1 1 auto;
            -webkit-overflow-scrolling: touch;
        }

        .agency-modal-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 0;
            min-height: 100%;
        }

        .agency-modal-image {
            width: 100%;
            height: 100%;
            min-height: 450px;
            max-height: 600px;
            object-fit: cover;
            background-color: #e0e4e8;
        }

        .agency-modal-details {
            padding: 2.2rem;
            background: white;
            height: 100%;
        }

        .agency-modal-details h3 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.8rem;
            border-left: 8px solid var(--accent);
            padding-left: 1.2rem;
        }

        .agency-detail-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            color: #2d2d2d;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 1.2rem;
        }

        .agency-detail-item:last-child {
            border-bottom: none;
        }

        .agency-detail-icon {
            font-size: 1.6rem;
            color: var(--primary-color);
            width: 2.2rem;
            text-align: center;
        }

        .agency-detail-item strong {
            font-weight: 600;
            color: #1a1a1a;
            min-width: 100px;
        }

        .agency-detail-item span {
            color: #3d3d3d;
            line-height: 1.5;
        }

        .btn-maps-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1.8rem;
            box-shadow: 0 10px 20px -5px rgba(0, 128, 0, 0.4);
        }

        .btn-maps-custom:hover {
            background: #006600;
            transform: translateY(-4px);
            box-shadow: 0 18px 28px -6px rgba(0, 100, 0, 0.5);
        }

        /* Responsive total */
        @media (max-width: 992px) {
            .agency-modal-grid {
                grid-template-columns: 1fr;
            }

            .agency-modal-image {
                min-height: 300px;
                max-height: 350px;
            }
        }

        @media (max-width: 768px) {
            .destinations-header h2 {
                font-size: 1.8rem;
            }

            .destinations-header p {
                font-size: 1rem;
            }

            .destination-card {
                height: 380px;
            }

            .city-name {
                font-size: 1.6rem;
            }

            .btn-conoce-mas {
                font-size: 0.85rem;
                padding: 0.6rem 1rem;
            }

            .modal-header-custom .modal-title {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .destination-card {
                height: 340px;
            }

            .city-name {
                font-size: 1.5rem;
            }

            .agency-address-preview {
                font-size: 0.8rem;
            }

            .btn-conoce-mas {
                padding: 0.5rem 0.8rem;
            }

            .modal-dialog {
                margin: 0.25rem;
            }
        }

        /* Ajuste para que la imagen ocupe toda la card en todos los tamaños */
        .destination-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .container {
            max-width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="destinations-header">
            <div class="header-left">
                <h2>Nuestras Rutas</h2>
                <h1>Destinos Mágicos</h1>
            </div>
            <div class="header-right">
                <p>Conectamos los puntos más importantes del oriente peruano con la capital, garantizando un viaje placentero a través de los Andes hacia la Amazonía.</p>
            </div>
        </div>

        <!-- Swiper Container -->
        <div class="swiper-container" id="swiper-destinos">
            <div class="swiper-wrapper">
                <!-- Agencia Lima -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Lima.png" alt="Agencia Lima">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">La Victoria</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. 28 de Julio 1598 Cruce Jr. Andahuaylas 400</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal1">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Tocache -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Lima.png" alt="Agencia La Victoria">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">La Victoria</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. 28 de Julio 1581 (Ter. la Merced Stand: 01)</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal2">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Huánuco -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Juanjui.png" alt="Agencia Juanjui">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Juanjui</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Jr. Arica N°. Int: 26 (Terminal Tobias Ruiz)</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal3">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Tingo María -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Picota.png" alt="Agencia Picota">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Picota</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Carr. Fernando Belaunde Terry S/N</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal4">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Tarma -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Tarapoto.png" alt="Agencia Tarapoto">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Tarapoto</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Costado del Mercado Santa Anita - Carretera a Mayopanga C-01. N° 153</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal5">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Pucallpa -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Tocache.png" alt="Agencia Tocache">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Tocache</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Jr. Jorge Chávez Cuadra 1</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal6">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia La Oroya -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>San_Hilarion.png" alt="Agencia San Hilarion">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">San Hilarion</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Carr. Fer. Belaunde T. 458 - Frente Coleg. José Mariategui</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal7">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Cerro de Pasco -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Bellavista.png" alt="Agencia Bellavista">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Bellavista</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Lima 3er. Piso Cuadra 5</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal8">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Junín -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Yurimaguas.png" alt="Agencia Yurimaguas">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Yurimaguas</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Calle Mariscal Caseres 200</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal9">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Moyobamba.png" alt="Agencia Moyobamba">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Moyobamba</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Almirante Grau Cuadra 6 S/N</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal10">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Sacanche.png" alt="Agencia Sacanche">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Sacanche</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Carre. Fer. Belaunde Terry Cruce Juanjui - Saposoa</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal11">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Saposoa.png" alt="Agencia Saposoa">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Saposoa</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Lima 720 Saposoa</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal12">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Olivos.png" alt="Agencia Los Olivos">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Los Olivos</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Alfredo Mendiola 3889 - (Ter. Via Buss)</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal13">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Porongo.png" alt="Agencia San Juan de Porongo">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">San Juan de Porongo</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. F. Belaunde Terry. Costado del Estadio Municipal</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal14">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Campanilla.png" alt="Agencia de Campanilla">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Campanilla</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Fernando Belaunde Terry S/N</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal15">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Nuevo_Jaen.png" alt="Agencia Nuevo Jaén">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Nuevo Jaen</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Fernando Belaunde T. Frente la Almendra</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal16">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agencia Carhuamayo -->
                <div class="swiper-slide">
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= URL_IMAGEN_WEB ?>Pizana.png" alt="Agencia Pizana">
                            <div class="destino-label">Agencia</div>
                        </div>
                        <div class="destination-info">
                            <div class="viaja-a">Visítanos en</div>
                            <h3 class="city-name">Puerto Pizana</h3>
                            <div class="agency-address-preview">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Av. Marginal 107 Cerca del Puente Puerto</span>
                            </div>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <button class="btn-conoce-mas" data-bs-toggle="modal" data-bs-target="#agencyModal17">
                                <i class="bi bi-info-circle"></i> CONOCE MÁS
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- Modal Agencia 1: Lima -->
    <div class="modal fade" id="agencyModal1" tabindex="-1" aria-labelledby="agencyModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel1">Agencia La Victoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Lima.png" alt="Agencia La Victoria" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>La Victoria</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>La Victoria</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. 28 de Julio 1598 Cruce Jr. Andahuaylas 400</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 9:00am a 7:00pm, Sáb 9:00am a 1:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/Y1EGUmTQVbYYngk68" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 2: Tocache -->
    <div class="modal fade" id="agencyModal2" tabindex="-1" aria-labelledby="agencyModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel2">Agencia La Victoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Lima.png" alt="Agencia La Victoria" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>La Victoria</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>La Victoria</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. 28 de Julio 1581 (Ter. la Merced Stand: 01)</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/S93eA2frSzDbhzQLA" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 3: Huánuco -->
    <div class="modal fade" id="agencyModal3" tabindex="-1" aria-labelledby="agencyModalLabel3" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel3">Agencia Juanjui</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Juanjui.png" alt="Agencia Juanjui" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Juanjui</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Juanjui</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Jr. Arica N°. Int: 26 (Terminal Tobias Ruiz)</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 9:00am a 8:00pm, Sáb 9:00am a 2:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/stK9Q3iUc2rYFzxY7" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 4: Tingo María -->
    <div class="modal fade" id="agencyModal4" tabindex="-1" aria-labelledby="agencyModalLabel4" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel4">Agencia de Picota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Picota.png" alt="Agencia de Picota" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Picota</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Picota</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Carr. Fernando Belaunde Terry S/N</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:30am a 7:30pm, Sáb 8:30am a 1:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/LPSTCbWEXG1pyYAq9" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 5: Tarma -->
    <div class="modal fade" id="agencyModal5" tabindex="-1" aria-labelledby="agencyModalLabel5" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel5">Agencia Tarapoto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Tarapoto.png" alt="Agencia Tarapoto" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Tarapoto</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Tarapoto</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Costado del Mercado Santa Anita - Carretera a Mayopanga C-01. N° 153</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 9:00am a 7:00pm, Sáb 9:00am a 1:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/LPSTCbWEXG1pyYAq9" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 6: Pucallpa -->
    <div class="modal fade" id="agencyModal6" tabindex="-1" aria-labelledby="agencyModalLabel6" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel6">Agencia Tocache</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Tocache.png" alt="Agencia Tocache" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Tocache</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Tocache</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Jr. Jorge Chávez Cuadra 1</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 8:00pm, Sáb 8:00am a 2:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/fyCnFELk7breyXaw9" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 7: La Oroya -->
    <div class="modal fade" id="agencyModal7" tabindex="-1" aria-labelledby="agencyModalLabel7" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel7">Agencia San Hilarion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>San_Hilarion.png" alt="Agencia San Hilarion" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>San Hilarion</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>San Hilarion</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Carr. Fer. Belaunde T. 458 - Frente Coleg. José Mariategui</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 9:00am a 6:00pm, Sáb 9:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/PcSh5rhvsaBNwrQG9" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 8: Cerro de Pasco -->
    <div class="modal fade" id="agencyModal8" tabindex="-1" aria-labelledby="agencyModalLabel8" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel8">Agencia Bellavista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Bellavista.png" alt="Agencia Bellavista" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Bellavista</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Bellavista</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Lima 3er. Piso Cuadra 5</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:30am a 6:30pm, Sáb 8:30am a 1:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/S93eA2frSzDbhzQLA" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 9: Junín -->
    <div class="modal fade" id="agencyModal9" tabindex="-1" aria-labelledby="agencyModalLabel9" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel9">Agencia Yurimaguas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Yurimaguas.png" alt="Agencia Yurimaguas" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Yurimaguas</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Yurimaguas</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Calle Mariscal Caseres 200</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 9:00am a 7:00pm, Sáb 9:00am a 1:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/M15vNqr2EvfWXiye8" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal10" tabindex="-1" aria-labelledby="agencyModalLabel10" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel10">Agencia Moyobamba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Moyobamba.png" alt="Agencia Moyobamba" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Moyobamba</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Moyobamba</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Almirante Grau Cuadra 6 S/N</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/6XDoPy7uo5JNBegU8" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal11" tabindex="-1" aria-labelledby="agencyModalLabel11" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel11">Agencia Sacanche</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Sacanche.png" alt="Agencia Sacanche" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Sacanche</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Sacanche</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Carre. Fer. Belaunde Terry Cruce Juanjui - Saposoa</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/S93eA2frSzDbhzQLA" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal12" tabindex="-1" aria-labelledby="agencyModalLabel12" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel12">Agencia Saposoa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Saposoa.png" alt="Agencia Saposoa" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Saposoa</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Saposoa</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Lima 720 Saposoa</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 960351927</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/iTwyvvzkRZffVPv58" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal13" tabindex="-1" aria-labelledby="agencyModalLabel13" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel13">Agencia Los Olivos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Olivos.png" alt="Agencia Los Olivos" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Los Olivos</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Los Olivos</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Alfredo Mendiola 3889 - (Ter. Via Buss)</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 955744186</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/Y1b79JuF8dH5wi5E7" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal14" tabindex="-1" aria-labelledby="agencyModalLabel14" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel14">Agencia San Juan de Porongo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Porongo.png" alt="Agencia San Juan de Porongo" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>San Juan de Porongo</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>San Juan de Porongo</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. F. Belaunde Terry. Costado del Estadio Municipal</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 955744186</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/FMmaASvUPmN4GYSG8" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal15" tabindex="-1" aria-labelledby="agencyModalLabel15" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel15">Agencia Campanilla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Campanilla.png" alt="Agencia Campanilla" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Campanilla</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Campanilla</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Fernando Belaunde Terry S/N</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 955744186</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/ME5EmfBZ6Je7JdhNA" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal16" tabindex="-1" aria-labelledby="agencyModalLabel16" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel16">Agencia Nuevo Jaen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Nuevo_Jaen.png" alt="Agencia Nuevo Jaen" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Nuevo Jaen</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Nuevo Jaen</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Fernando Belaunde T. Frente la Almendra</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 955744186</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/f61xpXNR69aWMdqe8" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agencia 10: Carhuamayo -->
    <div class="modal fade" id="agencyModal17" tabindex="-1" aria-labelledby="agencyModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="agencyModalLabel17">Agencia Puerto Pizana</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="agency-modal-grid">
                        <img src="<?= URL_IMAGEN_WEB ?>Pizana.png" alt="Agencia Puerto Pizana" class="agency-modal-image">
                        <div class="agency-modal-details">
                            <h3>Puerto Pizana</h3>
                            <div class="agency-detail-item">
                                <i class="bi bi-truck agency-detail-icon"></i>
                                <strong>Agencia:</strong> <span>Puerto Pizana</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-geo-alt agency-detail-icon"></i>
                                <strong>Dirección:</strong> <span>Av. Marginal 107 Cerca del Puente Puerto</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 955744186</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-telephone agency-detail-icon"></i>
                                <strong>Teléfono:</strong> <span>+51 967183455</span>
                            </div>
                            <div class="agency-detail-item">
                                <i class="bi bi-clock agency-detail-icon"></i>
                                <strong>Horario:</strong> <span>Lun - Vie 8:00am a 6:00pm, Sáb 8:00am a 12:00pm</span>
                            </div>
                            <a href="https://maps.app.goo.gl/mraqUFe8QwN9iEdt5" target="_blank" class="btn-maps-custom">
                                <i class="bi bi-google"></i> Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 15,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: false,
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                576: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 4,
                }
            }
        });
    </script>
</body>

</html>