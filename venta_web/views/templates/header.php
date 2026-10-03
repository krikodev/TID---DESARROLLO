<?php
require_once(__DIR__ . "/../../helpers/helpers.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= NAME_BUSINESS ?></title>

    <input
        type="hidden"
        id="url_venta_web"
        value="<?= URL_VENTA_WEB; ?>"
    >

    <input
        type="hidden"
        id="url_imagen"
        value="<?= URL_IMAGEN; ?>"
    >

    <link
        rel="icon"
        type="image/png"
        href="<?= URL_IMAGEN_ADMIN ?>icono.png?v=2"
    >

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- CSS propios de la vista -->
    <?php if (isset($this->css)) : ?>

        <?php foreach ($this->css as $css) : ?>

            <?php

            $arry = explode(".", $css);

            MINIFY_CSS
                ? $arry[0] = $arry[0] . '.min.'
                : $arry[0] = $arry[0] . '.';

            $name_script = implode("", $arry);

            ?>

            <link
                rel="stylesheet"
                href="<?= URL_VENTA_WEB; ?>views/<?= $name_script ?>"
            >

        <?php endforeach ?>

    <?php endif ?>

    <style>
        /* =========================================================
   HEADER - VENTA WEB
========================================================= */
body {
    margin: 0;
    padding-top: 82px;
}
.venta-header {
    position: fixed;
    top: 0;
    left: 0;

    width: 100%;
    height: 82px;

    z-index: 9999;

    background: #ffffff;

    box-shadow:
        0 3px 15px rgba(0, 0, 0, 0.10);

    font-family: 'Poppins', sans-serif;
}


/* =========================================================
   CONTENEDOR
========================================================= */

.venta-header-container {
    width: 100%;
    max-width: 1500px;
    height: 100%;

    margin: 0 auto;
    padding: 0 35px;

    display: flex;
    align-items: center;

    gap: 30px;
}


/* =========================================================
   LOGO
========================================================= */

.venta-logo {
    flex-shrink: 0;

    display: flex;
    align-items: center;

    text-decoration: none;
}

.venta-logo img {
    display: block;

    width: 145px;
    max-height: 62px;

    object-fit: contain;
}


/* =========================================================
   CONTACTOS
========================================================= */

.venta-contactos {
    flex: 1;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 35px;
}


/* =========================================================
   CONTACTO INDIVIDUAL
========================================================= */

.venta-contacto {
    display: flex;
    align-items: center;

    gap: 10px;

    padding: 8px 12px;

    color: #333333;

    text-decoration: none;

    border-radius: 8px;

    transition:
        background-color 0.25s ease,
        transform 0.25s ease;
}

.venta-contacto:hover {
    color: #333333;

    background-color: #f5f7fa;

    transform: translateY(-1px);
}


/* =========================================================
   ICONO
========================================================= */

.venta-contacto-icon {
    width: 36px;
    height: 36px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #0B1B36,
            #1A3E7C
        );

    border-radius: 50%;

    font-size: 13px;
}


/* =========================================================
   INFORMACIÓN DEL CONTACTO
========================================================= */

.venta-contacto-info {
    display: flex;
    flex-direction: column;

    line-height: 1.2;
}

.venta-contacto-nombre {
    margin-bottom: 4px;

    color: #555555;

    font-size: 10px;
    font-weight: 600;

    letter-spacing: 0.7px;

    text-transform: uppercase;
}

.venta-contacto-numero {
    color: #0B1B36;

    font-size: 13px;
    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   BOTÓN VOLVER A WEB
========================================================= */

.venta-header-button {
    flex-shrink: 0;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 9px;

    min-height: 42px;

    padding: 0 19px;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #0B1B36,
            #1A3E7C
        );

    border-radius: 7px;

    text-decoration: none;

    font-size: 12px;
    font-weight: 600;

    letter-spacing: 0.3px;

    box-shadow:
        0 4px 10px rgba(11, 27, 54, 0.20);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

.venta-header-button:hover {
    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #1A3E7C,
            #254f94
        );

    transform: translateY(-2px);

    box-shadow:
        0 6px 15px rgba(11, 27, 54, 0.28);
}

.venta-header-button i {
    font-size: 12px;

    transition:
        transform 0.25s ease;
}

.venta-header-button:hover i {
    transform: translateX(-3px);
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1200px) {

    .venta-header-container {
        padding: 0 25px;

        gap: 15px;
    }

    .venta-contactos {
        gap: 10px;
    }

    .venta-contacto {
        padding: 7px 8px;
    }

    .venta-contacto-nombre {
        font-size: 9px;
    }

    .venta-contacto-numero {
        font-size: 12px;
    }

    .venta-header-button {
        padding: 0 14px;
    }
}


/* =========================================================
   TABLET PEQUEÑA
========================================================= */

@media (max-width: 992px) {

    .venta-header {
        height: auto;
        min-height: 82px;
    }

    .venta-header-container {
        min-height: 82px;

        padding: 10px 20px;

        flex-wrap: wrap;
    }

    .venta-logo {
        flex: 0 0 auto;
    }

    .venta-logo img {
        width: 125px;
    }

    .venta-contactos {
        flex: 1;

        justify-content: flex-end;

        gap: 5px;
    }

    .venta-contacto {
        padding: 5px;
    }

    .venta-contacto-icon {
        width: 32px;
        height: 32px;

        font-size: 11px;
    }

    .venta-contacto-info {
        display: none;
    }

    .venta-header-button {
        min-height: 38px;

        padding: 0 12px;

        font-size: 11px;
    }

    body {
        padding-top: 82px;
    }
}


/* =========================================================
   MÓVIL
========================================================= */

@media (max-width: 768px) {
    .venta-header {
        position: fixed;

        top: 0;
        left: 0;

        width: 100%;

        height: 140px;

        z-index: 9999;
    }

    .venta-header-container {
        min-height: auto;

        padding: 15px;

        flex-wrap: wrap;

        justify-content: space-between;
    }

    .venta-logo img {
        width: 130px;
    }

    .venta-contactos {
        order: 3;

        flex: 0 0 100%;

        display: flex;

        justify-content: space-between;

        gap: 5px;

        padding-top: 10px;

        border-top:
            1px solid #eeeeee;
    }

    .venta-contacto {
        flex: 1;

        justify-content: center;

        padding: 5px;

        background: #f7f8fa;
    }

    .venta-contacto-icon {
        width: 30px;
        height: 30px;
    }

    .venta-header-button {
        min-height: 38px;

        padding: 0 12px;

        font-size: 10px;
    }

    body {
        padding-top: 140px;
    }
}


/* =========================================================
   MÓVIL PEQUEÑO
========================================================= */

@media (max-width: 480px) {

    .venta-header-container {
        padding: 12px;
    }

    .venta-logo img {
        width: 115px;
    }

    .venta-header-button span {
        display: none;
    }

    .venta-header-button {
        width: 38px;
        height: 38px;

        padding: 0;

        border-radius: 50%;
    }

    .venta-header-button i {
        font-size: 13px;
    }

    .venta-contactos {
        gap: 3px;
    }

    .venta-contacto {
        min-width: 0;
    }

    .venta-contacto-icon {
        width: 28px;
        height: 28px;

        font-size: 10px;
    }
}
    </style>

</head>


<body>

<header class="venta-header">

    <div class="venta-header-container">


        <!-- =====================================================
             LOGO
        ====================================================== -->

        <a
            href="<?= URL_VENTA_WEB ?>"
            class="venta-logo"
        >

            <img
                src="<?= URL_IMAGEN_ADMIN ?>logo_tisoc.png"
                alt="<?= NAME_BUSINESS ?>"
            >

        </a>



        <!-- =====================================================
             SUCURSALES / CONTACTOS
        ====================================================== -->

        <div class="venta-contactos">


            <!-- CUSCO -->

            <a
                href="tel:+51993312605"
                class="venta-contacto"
            >

                <div class="venta-contacto-icon">
                    <i class="fa-solid fa-phone"></i>
                </div>

                <div class="venta-contacto-info">

                    <span class="venta-contacto-nombre">
                        CUSCO
                    </span>

                    <span class="venta-contacto-numero">
                        993 312 605
                    </span>

                </div>

            </a>



            <!-- PUERTO MALDONADO -->

            <a
                href="tel:+51930945688"
                class="venta-contacto"
            >

                <div class="venta-contacto-icon">
                    <i class="fa-solid fa-phone"></i>
                </div>

                <div class="venta-contacto-info">

                    <span class="venta-contacto-nombre">
                        PUERTO MALDONADO
                    </span>

                    <span class="venta-contacto-numero">
                        930 945 688
                    </span>

                </div>

            </a>



            <!-- TERMINAL TERRESTRE -->

            <a
                href="tel:+51928603277"
                class="venta-contacto"
            >

                <div class="venta-contacto-icon">
                    <i class="fa-solid fa-phone"></i>
                </div>

                <div class="venta-contacto-info">

                    <span class="venta-contacto-nombre">
                        TERMINAL TERRESTRE
                    </span>

                    <span class="venta-contacto-numero">
                        928 603 277
                    </span>

                </div>

            </a>

        </div>



        <!-- =====================================================
             VOLVER A WEB
        ====================================================== -->

        <a
            href="<?= URL_WEB ?>"
            class="venta-header-button"
        >

            <i class="fa-solid fa-arrow-left"></i>

            <span>
                VOLVER A WEB
            </span>

        </a>


    </div>

</header>