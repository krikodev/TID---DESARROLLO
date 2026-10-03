<div class="seccion-flota-premium">
    <div class="container-fluid px-md-5">
        <header class="sfp-header">
            <span class="sfp-tag">Nuestra Flota</span>
        </header>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-xl-6">
                <div class="sfp-card">
                    <div class="sfp-img-container">
                        <img src="<?= URL_IMAGEN_WEB ?>tid_empresa.png" alt="Servicio VIP">
                    </div>
                    <div class="sfp-content">
                        <h2 class="sfp-card-title">Servicio VIP Cama 160°</h2>
                        <div class="sfp-info-asientos">
                            <i class="bi bi-bus-front-fill"></i> 40 Asientos
                        </div>
                        <ul class="sfp-feature-list">
                            <li>Asientos de cuero</li>
                            <li>WiFi Satelital</li>
                            <li>Climatización Bi-Zona</li>
                            <li>Pantallas individuales</li>
                        </ul>
                        <a href="javascript:void(0)" class="sfp-btn-action">Víaja con Comodidad</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="sfp-card">
                    <div class="sfp-img-container">
                        <img src="<?= URL_IMAGEN_WEB ?>flota_imperial.png" alt="Servicio Imperial">
                    </div>
                    <div class="sfp-content">
                        <h2 class="sfp-card-title">Servicio Imperial</h2>
                        <div class="sfp-info-asientos">
                            <i class="bi bi-bus-front-fill"></i> 50 Asientos
                        </div>
                        <ul class="sfp-feature-list">
                            <li>Semi-cama ergonómico</li>
                            <li>Cargadores USB</li>
                            <li>GPS Monitoreado</li>
                            <li>Snack a bordo</li>
                        </ul>
                        <a href="javascript:void(0)" class="sfp-btn-action">Víaja con Comodidad</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="hero-container">
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <h1 class="hero-title">Evolucionamos<br>Pensando siempre en ti</h1>

        <div class="hero-features-grid">
            
            <div class="hero-feature-item">
                <div class="hero-icon-circle">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <h3 class="hero-feature-title">Viajamos</h3>
                <p class="hero-feature-desc">Por todo el Perú contigo</p>
            </div>

            <div class="hero-feature-item">
                <div class="hero-icon-circle">
                    <i class="bi bi-display"></i>
                </div>
                <h3 class="hero-feature-title">Cómodas y seguras</h3>
                <p class="hero-feature-desc">Unidades de última generación</p>
            </div>

            <div class="hero-feature-item">
                <div class="hero-icon-circle">
                    <i class="bi bi-briefcase"></i>
                </div>
                <h3 class="hero-feature-title">Revisión continua</h3>
                <p class="hero-feature-desc">Para tu tranquilidad</p>
            </div>

            <div class="hero-feature-item">
                <div class="hero-icon-circle">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="hero-feature-title">Vigilancia 24/7</h3>
                <p class="hero-feature-desc">Control total en cada viaje</p>
            </div>

        </div>
    </div>
</section>
<br>

<style>
    /* --- SECCIÓN HERO PERSONALIZADA --- */
    .hero-container {
        position: relative;
        width: 100%;
        min-height: 70vh; /* Ocupa todo el alto de pantalla */
        background: url('<?= URL_IMAGEN_WEB ?>carro_turismo.png') no-repeat center center;
        background-size: cover; /* La imagen se adapta para cubrir todo el ancho/largo */
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        color: #ffffff;
        padding: 40px 20px;
    }

    /* Capa oscura para legibilidad del texto */
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4); /* Ajustar opacidad según la imagen */
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 1200px;
        width: 100%;
    }

    /* --- TÍTULOS --- */
    .hero-title {
        font-size: 3.5rem; /* Tamaño grande como en la imagen */
        font-weight: 700;
        margin-bottom: 60px;
        line-height: 1.1;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
    }

    /* --- CONTENEDOR DE ICONOS --- */
    .hero-features-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* 4 columnas iguales */
        gap: 20px;
        margin-top: 50px;
    }

    .hero-feature-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    /* Estilo de los iconos circulares */
    .hero-icon-circle {
        width: 65px;
        height: 65px;
        border: 2.5px solid #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 1.8rem;
        transition: all 0.3s ease;
    }

    /* --- TEXTOS DE LOS ICONOS --- */
    .hero-feature-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 5px;
        text-transform: none;
    }

    .hero-feature-desc {
        font-size: 0.9rem;
        font-weight: 300;
        opacity: 0.9;
        line-height: 1.3;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
        .hero-features-grid {
            grid-template-columns: repeat(2, 1fr); /* 2 columnas en tablets */
            gap: 40px;
        }
        .hero-title {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 576px) {
        .hero-features-grid {
            grid-template-columns: 1fr; /* 1 columna en celulares */
        }
        .hero-title {
            font-size: 2rem;
        }
    }

/* Variables de color locales */
    .seccion-flota-premium {
        --sfp-green: #008055;
        --sfp-text-dark: #1a2b3c;
        --sfp-bg-card: #ffffff;
        --sfp-gray-text: #666666;
        --sfp-shadow: 0 10px 30px rgba(0,0,0,0.08);
        
        font-family: 'Poppins', sans-serif;
        padding: 60px 0;
        width: 100%;
        overflow: hidden; /* Evita desbordamientos */
    }

    /* --- ENCABEZADO --- */
    .sfp-header {
        text-align: center;
        margin-bottom: 50px;
        padding: 0 15px;
    }
    .sfp-tag {
        color: var(--sfp-green);
        text-transform: uppercase;
        font-weight: 700;
        font-size: clamp(0.7rem, 2vw, 0.85rem); /* Tamaño adaptable */
        letter-spacing: 2px;
        display: block;
        margin-bottom: 10px;
    }

    /* --- TARJETAS --- */
    .sfp-card {
        background: var(--sfp-bg-card);
        border-radius: 30px;
        overflow: hidden;
        display: flex;
        flex-direction: row; /* Horizontal en pantallas grandes */
        border: none;
        box-shadow: var(--sfp-shadow);
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .sfp-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    }

    /* Contenedor de Imagen */
    .sfp-img-container {
        width: 42%;
        min-height: 100%;
        position: relative;
    }
    .sfp-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Contenedor de Texto */
    .sfp-content {
        width: 58%;
        padding: clamp(20px, 4vw, 35px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .sfp-card-title {
        font-size: clamp(1.2rem, 3vw, 1.6rem);
        font-weight: 700;
        color: var(--sfp-text-dark);
        margin-bottom: 10px;
    }

    .sfp-info-asientos {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--sfp-green);
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }

    /* Lista de características */
    .sfp-feature-list {
        list-style: none;
        padding: 0;
        margin-bottom: 30px;
    }
    .sfp-feature-list li {
        position: relative;
        padding-left: 20px;
        margin-bottom: 10px;
        color: var(--sfp-gray-text);
        font-size: 0.9rem;
        line-height: 1.4;
        display: flex;
        align-items: center;
    }
    .sfp-feature-list li::before {
        content: "";
        position: absolute;
        left: 0;
        width: 6px;
        height: 6px;
        background-color: var(--sfp-green);
        border-radius: 50%;
    }

    /* --- BOTÓN --- */
    .sfp-btn-action {
        background: transparent;
        color: var(--sfp-green);
        border: 2px solid var(--sfp-green);
        border-radius: 15px;
        padding: 12px 20px;
        font-weight: 700;
        width: 100%;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: default;
        font-size: 0.9rem;
    }

    /* --- RESPONSIVO TOTAL --- */
    
    /* Para tablets y pantallas medianas (apilar contenido en la tarjeta) */
    @media (max-width: 1199px) {
        .sfp-card {
            flex-direction: column; /* Cambia a vertical */
        }
        .sfp-img-container, .sfp-content {
            width: 100%;
        }
        .sfp-img-container {
            height: 250px; /* Altura fija para la imagen en vertical */
        }
    }

    /* Ajustes para móviles pequeños */
    @media (max-width: 576px) {
        .seccion-flota-premium {
            padding: 30px 10px;
        }
        .sfp-img-container {
            height: 200px;
        }
        .sfp-content {
            padding: 20px;
        }
    }
</style>