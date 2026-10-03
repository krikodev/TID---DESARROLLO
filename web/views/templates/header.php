<?php
require_once(__DIR__ . "/../../helpers/helpers.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo NAME_BUSINESS ?></title>
    <link rel="icon" href="<?= URL_IMAGEN_ADMIN ?>icono.png">
    <input type="hidden" id="url_web" value="<?php echo URL_WEB; ?>">
    <input type="hidden" id="url_venta_web" value="<?php echo URL_VENTA_WEB; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="<?php echo URL; ?>public/plugins/select2/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.3.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= URL_WEB ?>css/style.css">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/butoms.css">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/main.css">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/query.css">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/error.css">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/encomiendas.css">
    <link href="<?= URL_WEB; ?>public/css/swalert.min.css" rel="stylesheet">
</head>
<style>
    .navbar {
        position: fixed !important;
        width: 100%;
        z-index: 9999;
        top: 0;
        background-color: white;
    }

    body {
        padding-top: 80px;
        font-family: 'Poppins', sans-serif;
    }
</style>

<body>
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>

    <nav class="encabezado-color navbar bg-white navbar-expand-lg navbar-light p-0"> 
        <a href="<?= URL_WEB ?>" class="navbar-brand d-flex align-items-center px-4 px-lg-4 m-0">
            <h2 class="m-0 logo">
                <img src="<?= URL_IMAGEN_ADMIN ?>logo_amanecer.png" alt="" width="180px" border-radius="150px">
            </h2>
        </a>

        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="<?= URL_WEB ?>" class="nav-item nav-link">INICIO</a>
                <a href="<?= URL_WEB ?>home/about" class="nav-item nav-link">ACERCA DE</a>
                <a href="<?= URL_WEB ?>home/service" class="nav-item nav-link">NUESTRO SERVICIO</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">RECURSOS</a>
                    <div class="dropdown-menu bg-light m-0">
                        <a href="<?= URL_WEB ?>pdf/ClausulasGeneralesContratacionServicioEncomiendas.pdf" target="_blank" class="dropdown-item">Clausulas de servicio de enconmiendas</a>
                        <a href="<?= URL_WEB ?>pdf/ClausulasGeneralesContratacionServicioViaje.pdf" target="_blank" class="dropdown-item">Clausulas de servicio de viaje</a>
                        <a href="<?= URL_WEB ?>home/reclamo" target="_blank" class="dropdown-item">Libro de reclamaciones</a>
                        <a href="<?= URL_WEB ?>home/login" target="_blank" class="dropdown-item">Administrar reclamos</a>
                    </div>
                </div>
                <a href="<?= URL_WEB ?>home/contact" class="nav-item nav-link">CONTACTO</a>
            </div>
            <div class="p-3">
                <button class="boton-consulta" id="btn-encomiendas" onclick="abrirSeguimientoModal()">
                    CONSULTAR ENCOMIENDA
                </button>
            </div>

            <div id="seguimiento-modal" class="seguimiento-modal seguimiento-oculto">
                <div class="seguimiento-contenedor">
                    <div class="seguimiento-encabezado-superior">
                        <h2 class="seguimiento-titulo-superior">REALIZA TU <span>SEGUIMIENTO</span></h2>
                    </div>
                    <div class="seguimiento-der" style="width: 100%; padding: 30px;">
                        <div class="seguimiento-header">
                            <span class="seguimiento-pestana active" onclick="mostrarFormulario('comprobante')">COMPROBANTE DE PAGO</span>
                            <span class="seguimiento-pestana active" onclick="mostrarFormulario('tracking')">TRACKING</span>
                            <span class="seguimiento-cerrar" onclick="cerrarSeguimientoModal()">×</span>
                        </div>
                        
                        <form id="formulario">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Serie</b></label>
                                        <input type="text" name="serie" id="serie" class="form-control" placeholder="Ingrese Serie" maxlength="4" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Correlativo</b></label>
                                        <input type="text" name="correlativo" id="correlativo" class="form-control" placeholder="Ingrese Correlativo" maxlength="20" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Fecha</b></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <button type="button" name="buscar" id="buscar" class="btn btn-primary" onclick="BuscarEnco()">Buscar</button>
                                        <button type="button" class="btn btn-success" onclick="document.getElementById('formulario').reset()">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form id="tracking" class="seguimiento-form seguimiento-oculto">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><b>Tracking</b></label>
                                        <input type="text" name="trackingCode" id="trackingCode" class="form-control" placeholder="Ingrese el código de tracking" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <button type="button" name="buscar_Tracking" id="buscar_Tracking" class="btn btn-primary" onclick="BuscarTracking()">Buscar</button>
                                        <button type="button" class="btn btn-success" onclick="document.getElementById('tracking').reset()">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="tabla-contenedor mt-4">
                            <table class="tabla-consulta">
                                <thead>
                                    <tr>
                                        <th>DocumentoRem</th>
                                        <th>Remitente</th>
                                        <th>DocumentoDes</th>
                                        <th>Destinatario</th>
                                        <th>Terminal</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Fecha de salida</th>
                                        <th>Fecha de Registro</th>
                                        <th>Fecha de Entrega</th>
                                        <th>Estado</th>
                                        <th>Producto</th>
                                        <th>Observación</th>
                                        <th>Traking</th>
                                        <th>Evidencia</th> </tr>
                                </thead>
                                <tbody id="resultado">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="boton-sis d-lg-block justify-content-center">
                    <ul>
                        <?php $url = base_url(); ?>
                        <a href="<?= $url ?>" target="_blank">
                            <li>BACKOFFICE <span></span><span></span><span></span><span></span></li>
                        </a>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="modal fade" id="modalFotosEvidencia" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Evidencias de Entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-dark text-center">
                    <div id="carruselEvidencias" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="contenedor-fotos-carrusel">
                            </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselEvidencias" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselEvidencias" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($this->css)) : ?>
        <?php foreach ($this->css as $css) : ?>
            <?php
            $arry = explode(".", $css);
            MINIFY_CSS ? $arry[0] = $arry[0] . '.min.' : $arry[0] = $arry[0] . '.';
            $name_script = implode("", $arry);
            ?>
            <link rel="stylesheet" href="<?php echo URL_WEB; ?>views/<?php echo $name_script ?>">
        <?php endforeach ?>
    <?php endif ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function abrirSeguimientoModal() {
            document.getElementById("seguimiento-modal").classList.remove("seguimiento-oculto");
            mostrarFormulario('comprobante');
        }

        function cerrarSeguimientoModal() {
            document.getElementById("seguimiento-modal").classList.add("seguimiento-oculto");

            const serieInput = document.getElementById('serie');
            const correlativoInput = document.getElementById('correlativo');
            const fechaInput = document.getElementById('fecha');
            const trackingCodeInput = document.getElementById('trackingCode');
            const tablaResultado = document.getElementById('resultado');

            if (serieInput) serieInput.value = '';
            if (correlativoInput) correlativoInput.value = '';
            if (fechaInput) fechaInput.value = '';
            if (trackingCodeInput) trackingCodeInput.value = '';
            if (tablaResultado) tablaResultado.innerHTML = '';
            
            const tabs = document.querySelectorAll('.seguimiento-pestana');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.querySelector('.seguimiento-pestana[onclick="mostrarFormulario(\'comprobante\')"]').classList.add('active');
            mostrarFormulario('comprobante');
        }

        function mostrarFormulario(tipo) {
            const formularioComprobante = document.getElementById('formulario');
            const formularioTracking = document.getElementById('tracking');
            const pestanas = document.querySelectorAll('.seguimiento-pestana');

            formularioComprobante.classList.add('seguimiento-oculto');
            formularioTracking.classList.add('seguimiento-oculto');

            pestanas.forEach(pestana => {
                pestana.classList.remove('active');
            });

            if (tipo === 'comprobante') {
                formularioComprobante.classList.remove('seguimiento-oculto');
                document.querySelector('.seguimiento-pestana[onclick="mostrarFormulario(\'comprobante\')"]').classList.add('active');
            } else if (tipo === 'tracking') {
                formularioTracking.classList.remove('seguimiento-oculto');
                document.querySelector('.seguimiento-pestana[onclick="mostrarFormulario(\'tracking\')"]').classList.add('active');
            }

            document.getElementById('resultado').innerHTML = '';
        }

        function BuscarEnco() {
            const btnBuscar = document.getElementById('buscar');
            btnBuscar.addEventListener("click", () => {
                fetch("<?= URL_WEB ?>controller/buscar_encomiendas.php", {
                        method: "POST",
                        body: new FormData(document.getElementById('formulario'))
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('resultado').innerHTML = data;
                    })
            });
        }

        function BuscarTracking() {
            const trackingCodeInput = document.getElementById('trackingCode');
            const buscarTrackingButton = document.getElementById('buscar_Tracking');
            const trackingForm = document.getElementById('tracking');

            buscarTrackingButton.addEventListener("click", () => {
                if (trackingCodeInput.value.trim() === '') {
                    trackingCodeInput.classList.add('is-invalid');
                    alert('El código de Tracking es obligatorio.');
                    return;
                }
                fetch("<?= URL_WEB ?>controller/buscartraking.php", {
                        method: "POST",
                        body: new FormData(trackingForm)
                    })
                    .then(response => response.text())
                    .then(data => {
                        const resultadoElement = document.getElementById('resultado');
                        if (resultadoElement) {
                            resultadoElement.innerHTML = data;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al buscar. Inténtelo de nuevo.');
                    });
            });
        }

        // NUEVA FUNCIÓN PARA ABRIR LAS FOTOS 
        function abrirCarruselFotos(fotosArray) {
            const contenedor = document.getElementById('contenedor-fotos-carrusel');
            contenedor.innerHTML = ''; 

            if (!fotosArray || fotosArray.length === 0) {
                alert('No hay fotos disponibles para esta encomienda.');
                return;
            }

            fotosArray.forEach((url, index) => {
                let claseActiva = (index === 0) ? 'active' : '';
                contenedor.innerHTML += `
                    <div class="carousel-item ${claseActiva}">
                        <img src="${url.trim()}" class="d-block w-100" style="max-height: 500px; object-fit: contain; padding: 20px;" alt="Evidencia">
                    </div>
                `;
            });

            var modalFotos = new bootstrap.Modal(document.getElementById('modalFotosEvidencia'));
            modalFotos.show();
        }
    </script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>