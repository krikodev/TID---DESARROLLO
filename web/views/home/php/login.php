<?php Session::init(); ?>
<?php if (Session::get(NAME_SESSION) == true) : ?>
    <?php echo "<script> window.location.href='" . URL_WEB. "home/admin_reclamo' </script>"; ?>
<?php else : ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema administrativo <?= NAME_BUSINESS; ?>">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="sistema, administración, <?= NAME_BUSINESS; ?>">

    <link rel="icon" href="<?= URL_IMAGEN_ADMIN ?>icono.png">
    <input type="hidden" id="url" value="<?= URL; ?>">
    <input type="hidden" id="id_usuario_sesion">
    <input type="hidden" id="p_imprimir" value="">
    <input type="hidden" id="tipo_impresora" value="">
    <input type="hidden" id="l_terminales_p" value="">
    <input type="hidden" id="id_terminal_sesion" value="">

    <title><?= NAME_BUSINESS; ?> - Administrar Libro de Reclamaciones</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        .gradient-blue {
            background: linear-gradient(135deg, #08C8CF 0%, #08C8CF 50%, #2F5661 100%);
        }
        
        .floating-animation {
            animation: float 20s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translate(0, 0) scale(1); 
            }
            33% { 
                transform: translate(30px, -30px) scale(1.05); 
            }
            66% { 
                transform: translate(-20px, 20px) scale(0.95); 
            }
        }
        
        .rotate-animation {
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-white overflow-y-auto">
            <div class="w-full max-w-sm">
                
                <!-- Logo y Título -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-100 rounded-xl mb-3">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Iniciar Sesión</h2>
                    <p class="text-gray-500 text-sm">Accede al sistema de administración</p>
                </div>

                <!-- fromulario -->
                <form id="form_login" method="post" novalidate class="space-y-4">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            autocomplete="email"
                            required
                            placeholder="correo@ejemplo.com"
                            class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all"
                        >
                        <i class="fas fa-check-circle text-green-500 hidden" id="email-valid"></i>
                    </div>
                    
                    <div>
                        <label for="pass" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="pass" 
                                name="pass"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                                class="w-full px-3 py-2.5 pr-10 bg-gray-50 border-2 border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all"
                            >
                            <button 
                                type="button" 
                                id="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember_me" 
                            name="remember_me"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        >
                        <label for="remember_me" class="ml-2 text-sm text-gray-600 cursor-pointer">
                            Mantener sesión iniciada
                        </label>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        <button 
                            type="submit" 
                            id="button_send"
                            class="gradient-blue w-full py-2.5 px-6 text-white text-sm font-semibold rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center"
                        >
                            <span>INICIAR</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </button>

                        <!-- Boton cargando (hidden) -->
                        <button 
                            type="button"
                            id="button_load"
                            disabled
                            class="hidden w-full py-2.5 px-6 bg-blue-400 text-white text-sm font-semibold rounded-full shadow-lg items-center justify-center cursor-not-allowed"
                        >
                            <i class="fas fa-spinner fa-spin mr-2 text-xs"></i>
                            <span>Iniciando Sesión...</span>
                        </button>
                    </div>
                </form>

                <!-- Separador -->
                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 bg-white text-xs text-gray-500">Soporte y contacto</span>
                    </div>
                </div>
                
                <!-- Redes Sociales -->
                <div class="flex justify-center gap-3">
                    <a 
                        href="https://www.facebook.com/TurismoMallccoSAC" 
                        target="_blank"
                        class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-500 rounded-lg transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <i class="fab fa-facebook-f text-blue-600"></i>
                    </a>
                    <a 
                        href="https://wa.me/51993634428" 
                        target="_blank"
                        class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-green-50 border-2 border-gray-200 hover:border-green-500 rounded-lg transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <i class="fab fa-whatsapp text-green-600"></i>
                    </a>
                    <a 
                        href="mailto:expresointernacionalsanchez@hotmail.com"
                        class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-red-50 border-2 border-gray-200 hover:border-red-500 rounded-lg transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <i class="fas fa-envelope text-red-600"></i>
                    </a>
                </div>

                <div class="mt-5 text-center text-xs text-gray-500">
                    <div class="flex items-center justify-center gap-2 mb-1.5">
                        <img src="<?php echo URL_IMAGEN_ADMIN; ?>logo.png" alt="TID Logo" class="h-4">
                        <span class="font-semibold text-gray-700">TID Systems</span>
                    </div>
                    <p>© <?= date('Y'); ?> Todos los derechos reservados</p>
                    <p class="mt-0.5">
                        Soporte: +51 993 634 428 | 
                        <a 
                            href="https://tid.com.pe" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
                        >
                            tid.com.pe
                        </a>
                    </p>
                </div>

            </div>
        </div>

        <!-- información -->
        <div class="hidden lg:flex lg:w-1/2 gradient-blue items-center justify-center p-8 text-white relative overflow-hidden">
            
            <!-- Círculos animados de fondo -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="floating-animation absolute w-96 h-96 bg-white opacity-10 rounded-full -top-32 -right-32"></div>
                <div class="floating-animation absolute w-64 h-64 bg-white opacity-10 rounded-full -bottom-16 -left-16" style="animation-delay: -10s;"></div>
                <div class="rotate-animation absolute inset-0 w-full h-full" style="background: radial-gradient(circle, rgba(21, 214, 221, 0.8) 0%, transparent 70%);"></div>
            </div>
            
            <div class="relative z-10 text-center max-w-lg px-4">
                
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 backdrop-blur-sm rounded-2xl mb-5 border-2 border-white border-opacity-30">
                    <i class="fas fa-book-open text-3xl"></i>
                </div>
                
                <h1 class="text-3xl font-bold mb-3">Bienvenido a</h1>
                <h2 class="text-2xl font-bold mb-5">Libro de Reclamaciones</h2>
                
                <p class="text-white text-opacity-90 text-sm leading-relaxed mb-6">
                    Sistema integral para la gestión eficiente de reclamaciones. 
                    Cumple con todas las normativas legales mientras optimizas tu atención al cliente.
                </p>
                
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="text-center">
                        <div class="text-2xl font-bold mb-1">Disponible</div>
                        <div class="text-xs text-white text-opacity-80">Horario Oficina</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold mb-1">100%</div>
                        <div class="text-xs text-white text-opacity-80">Seguro</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold mb-1">Respuestas</div>
                        <div class="text-xs text-white text-opacity-80">De inmediato</div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-center gap-3 text-sm">
                        <i class="fas fa-check-circle text-green-300"></i>
                        <span>Gestión completa de reclamaciones</span>
                    </div>
                    <div class="flex items-center justify-center gap-3 text-sm">
                        <i class="fas fa-check-circle text-green-300"></i>
                        <span>Reportes automatizados</span>
                    </div>
                    <div class="flex items-center justify-center gap-3 text-sm">
                        <i class="fas fa-check-circle text-green-300"></i>
                        <span>Cumplimiento normativo garantizado</span>
                    </div>
                </div>
                
            </div>
        </div>

    </div>

    <script type="module" src="<?= URL_WEB ?>js/login.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passInput = document.getElementById('pass');
            const icon = this.querySelector('i');
            
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
<?php endif; ?>