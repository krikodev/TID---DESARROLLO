<footer class="footer-container">
    <div class="footer-main">
        <div class="footer-logo-section">
            <img
                src="<?= URL_IMAGEN_ADMIN ?>logo_sinfondo.png"
                alt="IMCATRANS"
                class="footer-logo"
            >
            <p>
                Av. Valle Sagrado de los Incas s/n, Urb. Santiago, Cusco
                (dentro de las instalaciones del terminal principal).
            </p>
        </div>

        <div class="footer-info-container">

            <!-- CONTACTO -->
            <div class="footer-contact">
                <h5 class="footer-heading">
                    CONTACTO RÁPIDO
                </h5>
                <ul class="footer-contact-list">
                    <li>
                        <span class="contact-label">
                            Teléfonos:
                        </span>
                        <br>
                        993 312 605
                    </li>

                    <li>
                        <span class="contact-label">
                            Email:
                        </span>
                        <br>
                        turismotisochermanos@gmail.com
                    </li>
                </ul>

                <h5 class="footer-heading">
                    TERMINOS Y POLÍTICAS
                </h5>
                <ul class="footer-contact-list policy-links">
                    <li class="agency-item">
                        <a
                            href="<?= URL_VENTA_WEB ?>pdf/ClausulasGeneralesContratacionServicioEncomiendas.pdf"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Cláusulas de Encomiendas y Carga
                        </a>
                    </li>

                    <li class="agency-item">
                        <a
                            href="<?= URL_VENTA_WEB ?>pdf/ClausulasGeneralesContratacionServicioViaje.pdf"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Cláusulas de servicio de pasaje
                        </a>
                    </li>
                </ul>
            </div>

            <!-- SUCURSALES -->
            <div class="footer-agencies">
                <h4 class="footer-heading">
                    NUESTROS SUCURSALES
                </h4>
                <div class="agencies-grid">
                    <div class="agency-column">
                        <div class="agency-item">
                            Av. Circunvalación Nro. 2621 Int. B11
                            Puerto Maldonado - Tambopata - Tambopata -
                            Madre de Dios
                            <br>
                            <span class="contact-label">
                                Tel. 930945688
                            </span>
                        </div>

                        <div class="agency-item">
                            Jr. Huascar Mza. D Lote. 2
                            (Con pasaje Miraflores)
                            <br>
                            <span class="contact-label">
                                Tel. 930945688
                            </span>
                        </div>

                        <div class="agency-item">
                            Av. Pioneros Nro. 07 - Terminal Terrestre Stand
                            <br>
                            <span class="contact-label">
                                Tel. 927233955
                            </span>
                        </div>

                        <div class="agency-item">
                            Cal. Jose Olaya Mza. H Lote. 10 Urb. Bancopata
                            <br>
                            <span class="contact-label">
                                Tel. 927231048
                            </span>
                        </div>

                        <div class="agency-item">
                            Av. Evitamiento Stand D-7 Y/O 134 Nro. 429 -
                            Terminal Terrestre
                            <br>
                            <span class="contact-label">
                                Tel. 928603277
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECLAMACIONES + REDES -->
            <div class="footer-right-section">
                <!-- <div class="reclamos-section">
                    <h5 class="reclamos-heading">
                        LIBRO DE RECLAMACIONES
                    </h5>
                    <a
                        href="<?= URL_VENTA_WEB ?>home/reclamo"
                        class="reclamos-link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img
                            src="<?= URL_IMAGEN_WEB ?>libro_reclamaciones.png"
                            alt="Libro de Reclamaciones"
                            class="reclamos-img"
                        >
                    </a>
                </div> -->

                <div class="social-section">
                    <h5 class="social-heading">
                        SÍGUENOS
                    </h5>
                    <div class="social-icons-container">
                        <a
                            href="https://www.facebook.com/turismotisoc/?locale=es_LA"
                            target="_blank"
                            class="social-icon-link-square"
                        >
                            <img
                                src="<?= URL_IMAGEN_WEB ?>facebook.png"
                                alt="Facebook"
                                class="social-icon-square"
                            >
                        </a>

                        <a
                            href="https://wa.me/51993312605"
                            target="_blank"
                            class="social-icon-link-square"
                        >
                            <img
                                src="<?= URL_IMAGEN_WEB ?>whatsapp.png"
                                alt="WhatsApp"
                                class="social-icon-square"
                            >
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COPYRIGHT -->
    <div class="footer-bottom">
        <p>
            © 2026 - TISOC Transporte & Alquiler |
            Todos los derechos reservados.
        </p>
    </div>
</footer>

<!-- WHATSAPP FLOTANTE -->
<a
    href="https://wa.me/51993312605"
    class="whatsaap btn btn-success btn-lg-square back-to-top-whatsappp"
    target="_blank"
>
    <i class="fab fa-whatsapp fa-3x"></i>
</a>

<style>
    /* =========================================================
   FOOTER - VENTA WEB
========================================================= */

.footer-container {
    width: 100%;
    padding: 0;
    overflow: hidden;

    background: linear-gradient(
        135deg,
        #0B1B36 0%,
        #1A3E7C 100%
    );

    color: #FFFFFF;

    font-family: 'Poppins', sans-serif;

    border-radius: 12px 12px 0 0;

    border: 1px solid rgba(255, 255, 255, 0.05);

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.3);
}


/* =========================================================
   CONTENIDO PRINCIPAL
========================================================= */

.footer-main {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;

    padding: 3rem 5%;
}


/* =========================================================
   LOGO
========================================================= */

.footer-logo-section {
    flex: 0 0 22%;

    padding-right: 2rem;

    color: #E6E7E8;

    font-size: 0.85rem;
}

.footer-logo-section p {
    line-height: 1.6;
}

.footer-logo {
    display: block;

    max-width: 100%;
    height: auto;

    margin-bottom: 1.5rem;

    filter:
        drop-shadow(
            0 2px 4px rgba(0, 0, 0, 0.2)
        );
}


/* =========================================================
   CONTENEDOR DE INFORMACIÓN
========================================================= */

.footer-info-container {
    flex: 0 0 75%;

    display: flex;
    flex-wrap: wrap;
}


/* =========================================================
   CONTACTO
========================================================= */

.footer-contact {
    flex: 0 0 35%;

    padding-right: 1.5rem;
}


/* =========================================================
   TÍTULOS
========================================================= */

.footer-heading {
    position: relative;

    margin-bottom: 1.5rem;
    padding-bottom: 0.7rem;

    color: #FFFFFF;

    font-size: 1.1rem;
    font-weight: 700;

    letter-spacing: 0.5px;

    text-transform: uppercase;
}

.footer-heading::after {
    content: '';

    position: absolute;

    left: 0;
    bottom: 0;

    width: 40px;
    height: 3px;

    background-color: #FDB913;
}


/* =========================================================
   CONTACTO / LISTAS
========================================================= */

.footer-contact-list {
    list-style: none;

    margin: 0;
    padding: 0;
}

.footer-contact-list li {
    position: relative;

    margin-bottom: 1rem;

    color: #E6E7E8;

    font-size: 0.8rem;

    transition:
        transform 0.2s ease,
        color 0.2s ease;
}

.footer-contact-list li:hover {
    color: #FFFFFF;

    transform: translateX(5px);
}

.contact-label {
    margin-right: 5px;

    color: #FDB913;

    font-weight: 600;
}


/* =========================================================
   POLÍTICAS
========================================================= */

.policy-links li {
    margin-bottom: 0.7rem;
}

.policy-links li a {
    color: #E6E7E8;

    font-size: 0.85rem;

    text-decoration: none;

    transition:
        color 0.3s ease;
}

.policy-links li a:hover {
    color: #FDB913;

    text-decoration: underline;
}


/* =========================================================
   SUCURSALES
========================================================= */

.footer-agencies {
    flex: 0 0 45%;
}

.agencies-grid {
    display: flex;
    flex-wrap: wrap;
}

.agency-column {
    flex: 0 0 100%;
}

.agency-item {
    position: relative;

    margin-bottom: 0.5rem;
    padding: 0.5rem 0 0.2rem 0.5rem;

    color: #B0B0B0;

    font-size: 0.75rem;

    border-left: 2px solid transparent;

    transition:
        all 0.2s ease;
}

.agency-item::before {
    content: '•';

    position: absolute;

    left: 0;

    color: #F0E519;

    transition:
        transform 0.2s ease;
}

.agency-item:hover {
    padding-left: 1.2rem;

    color: #FFFFFF;

    border-left-color: #FDB913;

    background-color: rgba(255, 255, 255, 0.03);
}

.agency-item:hover::before {
    transform: scale(1.2);
}


/* =========================================================
   SECCIÓN DERECHA
========================================================= */

.footer-right-section {
    flex: 0 0 20%;

    display: flex;
    flex-direction: column;

    align-items: flex-end;
    justify-content: flex-start;

    padding-left: 1rem;
}


/* =========================================================
   LIBRO DE RECLAMACIONES
========================================================= */

.reclamos-section {
    width: 100%;

    margin-bottom: 2rem;

    text-align: center;
}

.reclamos-heading {
    margin-bottom: 0.8rem;

    color: #FFFFFF;

    font-size: 0.8rem;
    font-weight: 600;

    letter-spacing: 1px;

    text-align: center;
}

.reclamos-link {
    display: block;

    text-align: center;

    transition:
        transform 0.3s ease;
}

.reclamos-link:hover {
    transform: scale(1.05);
}

.reclamos-img {
    max-width: 140px;
    height: auto;

    border-radius: 4px;

    box-shadow:
        0 4px 8px rgba(0, 0, 0, 0.2);
}


/* =========================================================
   REDES SOCIALES
========================================================= */

.social-section {
    width: 100%;

    text-align: center;
}

.social-heading {
    margin-bottom: 0.8rem;

    color: #FFFFFF;

    font-size: 0.8rem;
    font-weight: 600;

    letter-spacing: 1px;

    text-transform: uppercase;
}

.social-icons-container {
    display: flex;

    justify-content: center;

    gap: 15px;
}

.social-icon-link-square {
    display: inline-block;

    width: 50px;
    height: 50px;

    overflow: hidden;

    border-radius: 4px;

    background-color: rgba(255, 255, 255, 0.1);

    border: 2px solid rgba(255, 255, 255, 0.2);

    transition:
        transform 0.3s ease,
        filter 0.3s ease,
        border-color 0.3s ease,
        background-color 0.3s ease;
}

.social-icon-link-square:hover {
    transform:
        translateY(-3px)
        scale(1.05);

    filter: brightness(1.1);

    border-color: #FDB913;

    background-color:
        rgba(253, 185, 19, 0.2);
}

.social-icon-square {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
}


/* =========================================================
   COPYRIGHT
========================================================= */

.footer-bottom {
    padding: 1rem 5%;

    text-align: center;

    background-color:
        rgba(0, 0, 0, 0.2);

    border-top:
        1px solid rgba(230, 231, 232, 0.15);
}

.footer-bottom p {
    margin: 0;

    color: #E6E7E8;

    font-size: 0.85rem;
}


/* =========================================================
   WHATSAPP FLOTANTE
========================================================= */

.back-to-top-whatsappp {
    position: fixed;

    right: 25px;
    bottom: 25px;

    z-index: 1000;

    display: flex;

    align-items: center;
    justify-content: center;

    width: 60px;
    height: 60px;

    padding: 0;

    color: #FFFFFF;

    font-size: 24px;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #25D366 0%,
            #128C7E 100%
        );

    border: 2px solid #FFFFFF;

    border-radius: 50%;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.2);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.back-to-top-whatsappp:hover {
    color: #FFFFFF;

    transform:
        scale(1.1)
        translateY(-5px);

    box-shadow:
        0 8px 20px rgba(0, 0, 0, 0.3);
}

.back-to-top-whatsappp i {
    color: #FFFFFF;

    font-size: 32px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .footer-logo-section {
        flex: 0 0 30%;
    }

    .footer-info-container {
        flex: 0 0 65%;
    }

    .footer-right-section {
        flex: 0 0 100%;

        margin-top: 2rem;
    }

    .reclamos-section,
    .social-section {
        width: auto;
    }
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 992px) {

    .footer-logo-section {
        flex: 0 0 100%;

        margin-bottom: 2rem;

        padding-right: 0;

        text-align: center;
    }

    .footer-logo {
        max-width: 250px;

        margin-right: auto;
        margin-left: auto;
    }

    .footer-info-container {
        flex: 0 0 100%;
    }

    .footer-contact {
        flex: 0 0 50%;
    }

    .footer-agencies {
        flex: 0 0 50%;
    }

    .footer-right-section {
        flex: 0 0 100%;

        flex-direction: row;

        align-items: flex-start;

        justify-content: center;

        gap: 50px;

        padding-left: 0;

        margin-top: 2rem;
    }

    .reclamos-section,
    .social-section {
        width: auto;

        margin-bottom: 0;
    }

    .footer-heading {
        text-align: center;
    }

    .footer-heading::after {
        left: 50%;

        transform:
            translateX(-50%);
    }

    .footer-contact-list li {
        text-align: center;
    }

}


/* =========================================================
   MÓVIL
========================================================= */

@media (max-width: 768px) {

    .footer-main {
        padding: 2rem 5%;
    }

    .footer-contact,
    .footer-agencies {
        flex: 0 0 100%;

        padding-right: 0;
    }

    .footer-contact {
        margin-bottom: 2rem;
    }

    .footer-agencies {
        margin-bottom: 2rem;
    }

    .footer-right-section {
        flex-direction: column;

        align-items: center;

        gap: 0;
    }

    .reclamos-section {
        margin-bottom: 2rem;
    }

    .social-icons-container {
        gap: 20px;
    }

    .social-icon-link-square {
        width: 55px;
        height: 55px;
    }

}


/* =========================================================
   MÓVIL PEQUEÑO
========================================================= */

@media (max-width: 480px) {

    .footer-main {
        padding: 2rem 1.2rem;
    }

    .footer-logo {
        max-width: 210px;
    }

    .footer-heading {
        font-size: 1rem;
    }

    .agency-item {
        font-size: 0.72rem;
    }

    .footer-bottom {
        padding: 1rem;
    }

    .footer-bottom p {
        font-size: 0.75rem;
        line-height: 1.5;
    }

    .back-to-top-whatsappp {
        right: 15px;
        bottom: 15px;

        width: 55px;
        height: 55px;
    }

    .back-to-top-whatsappp i {
        font-size: 28px;
    }

}
</style>

<!-- PHP -->
<?php if (isset($this->php)) : ?>
    <?php foreach ($this->php as $php) : ?>
        <?php require("views/" . $php) ?>
    <?php endforeach ?>
<?php endif ?>

<!-- SCRIPTS -->
<?php
// Cambia manualmente esta versión cuando actualices los scripts
$version = "1.0.8";
if (isset($this->js)) : ?>
    <?php foreach ($this->js as $js) : ?>
        <?php
        $arry = explode(".", $js);
        MINIFY_JS ? $arry[0] = $arry[0] . '.min.' : $arry[0] = $arry[0] . '.';
        $name_script = implode("", $arry);
        ?>
        <script src="<?= URL_VENTA_WEB; ?>views/<?= $name_script ?>?v=<?= $version; ?>" type="module"></script>
    <?php endforeach ?>
<?php endif ?>

<script>
    function controlTag(e) {
        tecla = (document.all) ? e.keyCode : e.which;
        // Permite las teclas de retroceso y tabulación
        if (tecla == 8 || tecla == 9) return true;
        // Define el patrón para permitir solo números y comas
        patron = /^[0-9,]$/;
        n = String.fromCharCode(tecla);
        return patron.test(n);
    }
</script>