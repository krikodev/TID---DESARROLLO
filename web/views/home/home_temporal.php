<?php
require_once(__DIR__ . "/../../helpers/helpers.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= NAME_BUSINESS ?></title>
    <link rel="icon" href="<?= URL_IMAGEN_ADMIN ?>icono.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { 
                                opacity: '0',
                                transform: 'translateY(10px)'
                            },
                            '100%': { 
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: 
                linear-gradient(135deg, rgba(255, 255, 255, 0.72) 0%, rgba(255, 255, 255, 0.75) 100%),
                url('<?= URL_IMAGEN_WEB ?>jl_carro.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }

        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .contact-item {
            transition: all 0.2s ease;
        }

        .contact-item:hover {
            background: rgba(37, 99, 235, 0.03);
            border-color: rgba(37, 99, 235, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .logo-hover {
            transition: transform 0.2s ease;
        }

        .logo-hover:hover {
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-inter antialiased text-gray-700">
    <!-- Header compacto -->
    <header class="pt-4 pb-2 px-4 md:px-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-start">
                <!-- Logo más grande y compacto -->
                <div class="flex items-center space-x-3">
                    <div class="logo-hover w-20 h-20 md:w-28 md:h-28 bg-white/95 rounded-xl shadow-sm flex items-center justify-center p-2">
                        <img 
                            src="<?= URL_IMAGEN_ADMIN ?>logo_jlsac.png" 
                            alt="<?= NAME_BUSINESS ?>"
                            class="w-full h-full object-contain scale-125"
                        >
                    </div>
                    <div class="pt-1">
                        <h1 class="text-xl md:text-2xl font-semibold text-gray-900"><?= NAME_BUSINESS ?></h1>
                        <div class="flex items-center space-x-2 mt-1">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-xs text-gray-500 font-medium">Plataforma en desarrollo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido principal compacto -->
    <main class="flex-1 flex items-center px-4 py-6 md:py-8">
        <div class="max-w-6xl mx-auto w-full">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-10 items-start">
                
                <!-- Columna izquierda compacta -->
                <div class="space-y-6 animate-fade-in">
                    <!-- Título compacto -->
                    <div class="space-y-3">
                        <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                            Transformando la<br>
                            <span class="gradient-text">logística empresarial</span>
                        </h2>
                        <p class="text-gray-600 leading-relaxed max-w-xl">
                            Estamos construyendo una plataforma innovadora para optimizar tus envíos, 
                            cargos y operaciones logísticas con tecnología de última generación.
                        </p>
                    </div>

                    <!-- Características compactas -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <div class="feature-card glass-card rounded-lg p-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50/80 flex items-center justify-center mb-2">
                                <i class="fas fa-shipping-fast text-primary text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900 text-xs mb-0.5">Envíos Rápidos</h3>
                            <p class="text-xs text-gray-500">Entrega eficiente</p>
                        </div>

                        <div class="feature-card glass-card rounded-lg p-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50/80 flex items-center justify-center mb-2">
                                <i class="fas fa-boxes text-orange-500 text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900 text-xs mb-0.5">Gestión de Carga</h3>
                            <p class="text-xs text-gray-500">Control de inventario</p>
                        </div>

                        <div class="feature-card glass-card rounded-lg p-3">
                            <div class="w-8 h-8 rounded-lg bg-green-50/80 flex items-center justify-center mb-2">
                                <i class="fas fa-chart-line text-green-500 text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900 text-xs mb-0.5">Logística Inteligente</h3>
                            <p class="text-xs text-gray-500">Rutas optimizadas</p>
                        </div>
                    </div>

                    <!-- CTA compacto -->
                    <div class="pt-2">
                        <a href="<?= base_url() ?>" target="_blank" 
                           class="btn-primary text-white font-medium py-2.5 px-6 rounded-lg flex items-center justify-center space-x-2 w-full sm:w-auto">
                            <i class="fas fa-external-link-alt text-xs"></i>
                            <span class="text-sm">Acceder al Sistema Actual</span>
                        </a>
                    </div>
                </div>

                <!-- Panel de contacto compacto -->
                <div class="animate-slide-up">
                    <div class="glass-card rounded-xl p-5">
                        <!-- Encabezado compacto -->
                        <div class="text-center mb-6">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-primary/5 flex items-center justify-center">
                                <i class="fas fa-headset text-primary"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Atención Directa</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Equipo disponible para asistencia</p>
                        </div>

                        <!-- Lista de contactos compacta -->
                        <div class="space-y-1.5 mb-6">
                            <a href="tel:+51968107864" class="block contact-item">
                                <div class="p-3 rounded-lg border border-gray-100 hover:border-primary/20 transition-all">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/5 flex items-center justify-center">
                                                <i class="fas fa-phone text-primary text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 text-sm">(+51) 967 426 251</div>
                                                <div class="text-xs text-gray-500">Línea Principal</div>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- WhatsApp compacto -->
                        <div class="bg-green-50/60 rounded-lg p-3 border border-green-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <i class="fab fa-whatsapp text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-xs">WhatsApp Business</div>
                                        <div class="text-xs text-gray-600">Respuesta rápida</div>
                                    </div>
                                </div>
                                <a href="https://wa.me/51967426251" target="_blank" 
                                   class="bg-green-500 hover:bg-green-600 text-white font-medium py-1.5 px-4 rounded-lg flex items-center space-x-1.5 text-xs transition-colors">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>Chat</span>
                                </a>
                            </div>
                        </div>

                        <!-- Horario compacto -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="text-center text-gray-500 text-xs">
                                <i class="far fa-clock mr-1.5 text-gray-400"></i>
                                Lunes a Sábado • 7:00 AM - 10:00 PM
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer compacto -->
    <footer class="py-4 px-4 md:px-6 border-t border-gray-100 bg-white/50 mt-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center gap-2">
                <!-- Información compacta -->
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start space-x-1.5 mb-1">
                        <img 
                            src="<?= URL_IMAGEN_ADMIN ?>logo.png" 
                            alt="Logo"
                            class="h-4"
                        >
                        <span class="text-sm text-gray-800">TID: Tecnología, Informática y Desarrollo</span>
                    </div>
                    <p class="text-xs text-gray-500">
                        © <?= date('Y') ?> • <span class="text-primary font-medium"><?= NAME_BUSINESS ?> en desarrollo</span>
                    </p>
                </div>

                <!-- Redes sociales compactas -->
                <div class="flex items-center space-x-2">
                    <a href="https://wa.me/51967426251" target="_blank"
                       class="w-6 h-6 rounded bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-green-50 hover:text-green-600 transition-colors">
                        <i class="fab fa-whatsapp text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botones flotantes compactos -->
    <div class="fixed bottom-4 right-4 flex flex-col gap-2 z-50 md:hidden">
        <a href="https://wa.me/51967426251" target="_blank"
           class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center shadow hover:bg-green-600 transition-all">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="tel:+51967426251"
           class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center shadow hover:bg-primary-dark transition-all">
            <i class="fas fa-phone"></i>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación simple
            const elements = document.querySelectorAll('.animate-fade-in, .animate-slide-up');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 50);
            });

            // Efectos hover
            const contactItems = document.querySelectorAll('.contact-item');
            contactItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-1px)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>

</html>