<?php
Session::init();
if (Session::get(NAME_SESSION) == true) {
    header('Location: ./');
    exit();
}

// Cargar data_empresa si no existe en sesión
if (!Session::get('data_empresa')) {
    require_once __DIR__ . '/../../models/loginmodel.php';  // Ajusta ruta si es necesario
    $model = new LoginModel();
    $data_empresa = $model->get_empresa();
    Session::set("data_empresa", $data_empresa);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords"
        content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">
    <link rel="icon" href="<?= URL_IMAGEN_ADMIN ?>icono.png">
    <input type="hidden" id="url" value="<?= URL; ?>">
    <input type="hidden" id="p_imprimir">
    <input type="hidden" id="p_selva">
    <input type="hidden" id="igv_sesion">
    <input type="hidden" id="tipo_impresora">
    <input type="hidden" id="l_terminales_p">
    <input type="hidden" id="id_empresa_sesion">
    <input type="hidden" id="uuid_ws_sesion">
    <input type="hidden" id="l_usuarios_p">
    <input type="hidden" id="ubigeo_terminal_sesion" value="<?= Session::get('ubigeo_terminal') ?? '' ?>">
    <input type="hidden" id="direccion_terminal_sesion" value="<?= Session::get('direccion_terminal') ?? '' ?>">
    <input type="hidden" id="id_terminal_sesion" value="<?= Session::get('id_terminal') ?? '' ?>">
    <input type="hidden" id="id_usuario_sesion" value="<?= Session::get('id_usuario') ?? '' ?>">
    <input type="hidden" id="tp_usuario_sesion" value="<?= Session::get('id_tp_usuario') ?? '' ?>">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />
    <link rel="canonical" href="https://demo-basic.adminkit.io/" />
    <title><?= NAME_BUSINESS; ?></title>

    <!-- CSS -->
    <link href="<?= URL; ?>public/plugins/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- <link href="<?= URL; ?>public/css/app.css" rel="stylesheet">
    <link href="<?= URL; ?>public/css/aditional.css" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="<?= URL; ?>public/plugins/animate/animate.min.css" rel="stylesheet">
    <link href="<?= URL; ?>public/plugins/toast/jquery.toast.min.css" rel="stylesheet" />
    <link href="<?= URL; ?>public/plugins/select2/select2.min.css" rel="stylesheet" />
    <link href="<?= URL; ?>public/plugins/tippy/tippy-animation-scale.css" rel="stylesheet" />
    <link href="<?= URL; ?>public/plugins/font-awesome-6.2.1/css/all.min.css" rel="stylesheet" />
    <link href="<?= URL; ?>public/plugins/bootstrap-icons-1.10.2/font/bootstrap-icons.css" rel="stylesheet" />

    <?php if (isset($this->css)): ?>
        <?php foreach ($this->css as $css): ?>
            <link rel="stylesheet" href="<?php echo URL; ?>views/<?php echo $css ?>">
        <?php endforeach ?>
    <?php endif ?>
</head>

<body class="animate__animated animate__fadeIn">
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-left">
                <div class="login-shape"></div>
                <div class="login-content">
                    <h2 class="login-company">
                        <?= strtoupper(NAME_BUSINESS); ?>
                    </h2>
                </div>
            </div>

            <div class="login-right">
                <div class="login-circle login-circle-3"></div>
                <div class="right-content">
                    <form id="form_login" method="post"
                        class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token"
                            value="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)) ?>">
                        <div class="logo-empresa">
                            <?php
                            // Usar logo normalizado (ya lo tienes)
                            $logo_file = isset(Session::get('data_empresa')['logo']) && !empty(Session::get('data_empresa')['logo'])
                                ? Session::get('data_empresa')['logo']
                                : 'default_logo.png';
                            $logo_src = URL_IMAGEN_ADMIN . $logo_file;
                            ?>
                            <img src="<?= htmlspecialchars($logo_src) ?>"
                                alt="Logo de <?= htmlspecialchars(NAME_BUSINESS) ?>">

                        </div>
                        <div class="login-form-content">
                            <!-- <h2 class="login-title">Inicia sesión en <?php echo NAME_BUSINESS; ?></h2> -->
                            <p class="subtitle">Ingrese a tu cuenta</p>
                            <div class="form-group">
                                <div class="input-box">

                                    <i class="fa-solid fa-user"></i>

                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        autocomplete="off"
                                        placeholder="Correo electrónico"
                                        required>

                                </div>

                                <div class="invalid-feedback">
                                    Por favor, ingresa un correo válido.
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-box">

                                    <i class="fa-solid fa-lock"></i>

                                    <input
                                        id="pass"
                                        name="pass"
                                        type="password"
                                        autocomplete="off"
                                        placeholder="Contraseña"
                                        required
                                        minlength="6">

                                </div>

                                <div class="invalid-feedback">
                                    La contraseña debe tener al menos 6 caracteres.
                                </div>
                            </div>
                            <div class="login-buttons">
                                <input type="submit" class="btn btn-lg w-100 btn-login" id="button_send"
                                    value="INICIAR SESIÓN">
                                <button class="btn btnSave btn-lg w-100 d-none" type="button" id="button_load" disabled>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Iniciando sesión...
                                </button>
                            </div>
                            <div class="social-links">
                                <a href="https://www.facebook.com/TurismoMallccoSAC" class="social-link"
                                    aria-label="Facebook de Turismo Mallcco SAC" target="_blank" rel="noopener noreferrer">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://wa.me/51993634428" class="social-link" aria-label="WhatsApp de soporte"
                                    target="_blank" rel="noopener noreferrer">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="mailto:expresointernacionalsanchez@hotmail.com" class="social-link"
                                    aria-label="Email de contacto" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                        <div class="logo-footer">
                            <img src="<?php echo URL_IMAGEN_ADMIN; ?>logo.png" alt="Logo footer de TID Transporte"
                                id="logo">
                            <div class="footer-content">
                                <span class="footer-text">
                                    © Todos los derechos reservados - TID <br>
                                    Soportes al numero: <br>
                                </span>
                                <a href="https://tid.com.pe" target="_blank" rel="noopener noreferrer" class="footer-btn">
                                    <i class="fas fa-external-link-alt px-3"></i>Visita nuestra Página Web
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="login-circle login-circle-1"></div>
        <div class="login-circle login-circle-2"></div>
    </div>
    <!-- Scripts -->
    <?php if (isset($this->js)): ?>
        <?php foreach ($this->js as $js): ?>
            <script src="<?php echo URL; ?>views/<?php echo $js; ?>" type="module" defer></script>
        <?php endforeach ?>
    <?php endif ?>
    <script src="<?= URL; ?>public/plugins/jquery v3.6.0/jquery.min.js" defer></script>
    <script src="<?= URL; ?>public/plugins/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="<?= URL; ?>public/plugins/toast/jquery.toast.min.js" defer></script>
    <script src="<?= URL; ?>public/plugins/notify/notify.min.js" defer></script>
    <script src="<?= URL; ?>public/plugins/popper/popper.min.js" defer></script>
    <script src="<?= URL; ?>public/plugins/tippy/tippy-bundle.umd.js" defer></script>
    <script src="<?= URL; ?>public/plugins/sweetalert2/sweetalert2@11.js" defer></script>
    <script defer>
        $(document).ready(function() {
            // Validación Bootstrap
            (function() {
                'use strict';
                window.addEventListener('load', function() {
                    var forms = document.getElementsByClassName('needs-validation');
                    Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();

            // Submit AJAX
            $('#form_login').on('submit', function(e) {
                e.preventDefault();
                var email = $('#email').val().trim();
                var pass = $('#pass').val().trim();
                var csrf = $('input[name="csrf_token"]').val();

                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire('Error', 'Correo inválido.', 'error');
                    return;
                }
                if (pass.length < 6) {
                    Swal.fire('Error', 'Contraseña muy corta.', 'error');
                    return;
                }

                $('#button_send').addClass('d-none');
                $('#button_load').removeClass('d-none');

                $.post($('#url').val() + 'admin/login/auth', {
                    email: email,
                    pass: pass,
                    csrf_token: csrf
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', 'Login correcto. Redirigiendo...', 'success').then(() => {
                            window.location.href = './';
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Credenciales inválidas.', 'error');
                        $('#button_send').removeClass('d-none');
                        $('#button_load').addClass('d-none');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Error de conexión. Intenta de nuevo.', 'error');
                    $('#button_send').removeClass('d-none');
                    $('#button_load').addClass('d-none');
                });
            });
        });
    </script>
</body>

</html>