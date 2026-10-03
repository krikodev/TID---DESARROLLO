<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="icon" type="image/png" href="<?= URL_IMAGEN_ADMIN ?>icono.png?v=2">
    <link rel="stylesheet" href="<?= URL; ?>views/error/css/main.css">
    <link rel="stylesheet" href="<?= URL; ?>public/plugins/bootstrap v5.1.1/bootstrap.min.css" />
</head>

<body class="bg-purple d-flex justify-content-center align-items-center">
    <div class="stars">
        <div class="central-body">
            <span id="title">404</span><br>
            <span id="text">La página no se encuentra</span>
            <a href="<?= URL_WEB; ?>" class="btn-go-home w-50">Ir a Inicio</a>
        </div>
        <div class="objects">
            <img class="object_rocket" src="http://salehriaz.com/404Page/img/rocket.svg" width="40px">
            <div class="earth-moon">
                <img class="object_earth" src="http://salehriaz.com/404Page/img/earth.svg" width="100px">
                <img class="object_moon" src="http://salehriaz.com/404Page/img/moon.svg" width="80px">
            </div>
            <div class="box_astronaut">
                <img class="object_astronaut" src="http://salehriaz.com/404Page/img/astronaut.svg" width="140px">
            </div>
        </div>
        <div class="glowing_stars">
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>
        </div>
    </div>
    <script src="<?= URL; ?>public/plugins/bootstrap v5.1.1/bootstrap.bundle.min.js"></script>
</body>

</html>