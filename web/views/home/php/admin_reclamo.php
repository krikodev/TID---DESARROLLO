<?php 
require_once __DIR__ . '/../../../controller/admin_reclamo_controller.php';
?>
<?php Session::init(); ?>
<?php if (Session::get(NAME_SESSION) == true) : ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reclamos - Libro de Reclamaciones</title>
    <link rel="stylesheet" href="<?= URL_WEB ?>css/output.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-pendiente {
            background-color: #FFC107;
            color: #000;
        }
        .badge-proceso {
            background-color: #2196F3;
            color: #fff;
        }
        .badge-resuelto {
            background-color: #4CAF50;
            color: #fff;
        }
        .badge-cerrado {
            background-color: #9E9E9E;
            color: #fff;
        }
    </style>
    <?php 
    $data_usuario = Session::get("data_usuario");
    
    // Configuración de paginación
    $registros_por_pagina = 10;
    $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $total_registros = count($reclamos);
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;
    $reclamos_paginados = array_slice($reclamos, $inicio, $registros_por_pagina);
    ?>
	<input type="hidden" id="fullnanme_usuario_sesion" value="<?php echo $data_usuario["nombres"] . " " . $data_usuario["apellidos"]; ?>">
</head>
<body class="bg-gray-100">
    <!-- Contenedor principal -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-[#D43415]">
                    <i class="fas fa-book mr-2"></i>Libro de Reclamos
                </h1>
            </div>
            <nav class="mt-6">
                <a href="#" class="flex items-center px-6 py-3 text-[#D43415] bg-red-50 border-r-4 border-[#D43415]">
                    <i class="fas fa-list mr-3"></i>
                    Reclamos
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Estadísticas
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-cog mr-3"></i>
                    Configuración
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>
                    Cerrar Sesión
                </a>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Gestión de Reclamos</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" id="buscar" placeholder="Buscar reclamo..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D43415]">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-[#D43415] rounded-full flex items-center justify-center text-white font-bold">
                                <i class="fa-solid fa-user "></i>
                            </div>
                            <label class="ml-2 text-sm font-medium"><?php echo $data_usuario["nombres"] . " " . $data_usuario["apellidos"] ?></label>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido del dashboard -->
            <main class="p-6">
                <!-- Mensajes -->
                <?php if ($mensaje_exito): ?>
                    <div class="bg-green-50 text-green-700 p-4 mb-4 border border-green-200 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $mensaje_exito; ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje_error): ?>
                    <div class="bg-red-50 text-red-700 p-4 mb-4 border border-red-200 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $mensaje_error; ?>
                    </div>
                <?php endif; ?>

                <!-- Tarjetas de métricas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <?php
                    $total = count($reclamos);
                    $pendientes = count(array_filter($reclamos, fn($r) => $r['estado_reclamo'] === 'Pendiente'));
                    $en_proceso = count(array_filter($reclamos, fn($r) => $r['estado_reclamo'] === 'En proceso'));
                    $resueltos = count(array_filter($reclamos, fn($r) => $r['estado_reclamo'] === 'Resuelto'));
                    $cerrados = count(array_filter($reclamos, fn($r) => $r['estado_reclamo'] === 'Cerrado'));
                    ?>
                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-clipboard-list text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Reclamos</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $total ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pendientes</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $pendientes ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-sync text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">En Proceso</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $en_proceso ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Resueltos</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $resueltos ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de reclamos -->
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">Lista de Reclamos</h3>
                            <!--<button class="px-4 py-2 bg-[#D43415] text-white rounded-lg text-sm font-medium hover:bg-[#b52d12] transition">
                                <i class="fas fa-file-export mr-2"></i>Exportar
                            </button>-->
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Reclamo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tabla-reclamos">
                                <?php foreach ($reclamos_paginados as $reclamo): ?>
                                    <tr class="hover:bg-gray-50 reclamo-row" data-reclamo='<?= json_encode($reclamo) ?>'>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($reclamo['numero_hoja_reclamacion']) ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d/m/Y', strtotime($reclamo['fecha'])) ?>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            <div class="font-medium"><?= htmlspecialchars($reclamo['nombre_consumidor']) ?></div>
                                            <div class="text-xs text-gray-400"><?= htmlspecialchars($reclamo['correo_consumidor']) ?></div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $reclamo['tipo_reclamo'] === 'reclamo' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' ?>">
                                                <?= ucfirst($reclamo['tipo_reclamo']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            <?= htmlspecialchars($reclamo['nombre_terminal']) ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <?php
                                            $estado_class = '';
                                            switch ($reclamo['estado_reclamo']) {
                                                case 'Pendiente':
                                                    $estado_class = 'badge-pendiente';
                                                    break;
                                                case 'En proceso':
                                                    $estado_class = 'badge-proceso';
                                                    break;
                                                case 'Resuelto':
                                                    $estado_class = 'badge-resuelto';
                                                    break;
                                                case 'Cerrado':
                                                    $estado_class = 'badge-cerrado';
                                                    break;
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $estado_class ?>">
                                                <?= $reclamo['estado_reclamo'] ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="abrirModal(<?= $reclamo['id'] ?>)" class="text-[#D43415] hover:text-[#b52d12] mr-3">
                                                <i class="fas fa-reply mr-1"></i>Responder
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200 bg-white">
                        <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                            <div class="text-sm text-gray-700">
                                Mostrando <span class="font-medium"><?= $inicio + 1 ?></span> a 
                                <span class="font-medium"><?= min($inicio + $registros_por_pagina, $total_registros) ?></span> de 
                                <span class="font-medium"><?= $total_registros ?></span> resultados
                            </div>
                            <div class="flex space-x-1">
                                <!-- Botón Anterior -->
                                <a href="?pagina=<?= max(1, $pagina_actual - 1) ?>" 
                                   class="px-3 py-1 text-sm font-medium rounded-lg border border-gray-300 <?= $pagina_actual <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50' ?>">
                                    <i class="fas fa-chevron-left mr-1"></i> Anterior
                                </a>
                                
                                <!-- Números de página -->
                                <?php 
                                $pagina_inicio = max(1, $pagina_actual - 2);
                                $pagina_fin = min($total_paginas, $pagina_actual + 2);
                                
                                if ($pagina_inicio > 1) {
                                    echo '<a href="?pagina=1" class="px-3 py-1 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">1</a>';
                                    if ($pagina_inicio > 2) echo '<span class="px-2 py-1 text-gray-500">...</span>';
                                }
                                
                                for ($i = $pagina_inicio; $i <= $pagina_fin; $i++): 
                                ?>
                                    <a href="?pagina=<?= $i ?>" 
                                       class="px-3 py-1 text-sm font-medium rounded-lg border <?= $i == $pagina_actual ? 'bg-[#D43415] text-white border-[#D43415]' : 'bg-white border-gray-300 hover:bg-gray-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; 
                                
                                if ($pagina_fin < $total_paginas) {
                                    if ($pagina_fin < $total_paginas - 1) echo '<span class="px-2 py-1 text-gray-500">...</span>';
                                    echo '<a href="?pagina=' . $total_paginas . '" class="px-3 py-1 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">' . $total_paginas . '</a>';
                                }
                                ?>
                                
                                <!-- Botón Siguiente -->
                                <a href="?pagina=<?= min($total_paginas, $pagina_actual + 1) ?>" 
                                   class="px-3 py-1 text-sm font-medium rounded-lg border border-gray-300 <?= $pagina_actual >= $total_paginas ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50' ?>">
                                    Siguiente <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para responder reclamo (más pequeño) -->
    <div id="modal-respuesta" class="modal">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[85vh] overflow-y-auto">
            <form method="POST" action="" id="form-respuesta">
                <input type="hidden" name="accion" value="responder">
                <input type="hidden" name="id_reclamo" id="modal-id-reclamo">
                
                <!-- Header del Modal -->
                <div class="bg-[#D43415] text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold">
                        <i class="fas fa-reply mr-2"></i>Responder Reclamo
                    </h3>
                    <button type="button" onclick="cerrarModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6">
                    <!-- Información del reclamo -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-bold text-gray-800 mb-3">Información del Reclamo</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="font-medium">N° Reclamo:</span>
                                <span id="modal-numero" class="ml-2 text-gray-700"></span>
                            </div>
                            <div>
                                <span class="font-medium">Fecha:</span>
                                <span id="modal-fecha" class="ml-2 text-gray-700"></span>
                            </div>
                            <div>
                                <span class="font-medium">Cliente:</span>
                                <span id="modal-cliente" class="ml-2 text-gray-700"></span>
                            </div>
                            <div>
                                <span class="font-medium">Email:</span>
                                <span id="modal-email" class="ml-2 text-gray-700"></span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="font-medium">Detalle del Reclamo:</span>
                            <p id="modal-detalle" class="mt-2 text-gray-700 text-sm bg-white p-3 rounded max-h-24 overflow-y-auto"></p>
                        </div>
                        <div class="mt-3">
                            <span class="font-medium">Pedido del Consumidor:</span>
                            <p id="modal-pedido" class="mt-2 text-gray-700 text-sm bg-white p-3 rounded max-h-24 overflow-y-auto"></p>
                        </div>
                    </div>

                    <!-- Estado del reclamo -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tasks mr-1"></i>Estado del Reclamo:
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="estado_reclamo" value="En proceso" required class="mr-2">
                                <span class="px-3 py-1 text-sm font-medium rounded-full badge-proceso">En Proceso</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="estado_reclamo" value="Resuelto" required class="mr-2">
                                <span class="px-3 py-1 text-sm font-medium rounded-full badge-resuelto">Resuelto</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="estado_reclamo" value="Cerrado" required class="mr-2">
                                <span class="px-3 py-1 text-sm font-medium rounded-full badge-cerrado">Cerrado</span>
                            </label>
                        </div>
                    </div>

                    <!-- Respuesta del proveedor -->
                    <div class="mb-6">
                        <label for="observaciones_proveedor" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment-alt mr-1"></i>Respuesta al Cliente:
                        </label>
                        <textarea 
                            id="observaciones_proveedor" 
                            name="observaciones_proveedor" 
                            rows="4" 
                            required
                            placeholder="Escriba aquí la respuesta que se enviará al cliente por correo electrónico..."
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D43415] focus:border-transparent resize-none"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Esta respuesta será enviada automáticamente al correo del cliente.
                        </p>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button 
                        type="button" 
                        onclick="cerrarModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-[#D43415] text-white rounded-lg hover:bg-[#b52d12] transition flex items-center"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variable para controlar si el formulario ya fue enviado
        let formularioEnviado = false;

        // Función para abrir el modal
        function abrirModal(idReclamo) {
            const filas = document.querySelectorAll('.reclamo-row');
            let reclamoData = null;
            
            filas.forEach(fila => {
                const data = JSON.parse(fila.getAttribute('data-reclamo'));
                if (data.id == idReclamo) {
                    reclamoData = data;
                }
            });

            if (reclamoData) {
                document.getElementById('modal-id-reclamo').value = reclamoData.id;
                document.getElementById('modal-numero').textContent = reclamoData.numero_hoja_reclamacion;
                document.getElementById('modal-fecha').textContent = new Date(reclamoData.fecha).toLocaleDateString('es-PE');
                document.getElementById('modal-cliente').textContent = reclamoData.nombre_consumidor;
                document.getElementById('modal-email').textContent = reclamoData.correo_consumidor;
                document.getElementById('modal-detalle').textContent = reclamoData.detalles_reclamo;
                document.getElementById('modal-pedido').textContent = reclamoData.pedido_consumidor;
                
                // Pre-seleccionar el estado actual si no es Pendiente
                if (reclamoData.estado_reclamo !== 'Pendiente') {
                    const radioEstado = document.querySelector(`input[name="estado_reclamo"][value="${reclamoData.estado_reclamo}"]`);
                    if (radioEstado) radioEstado.checked = true;
                }

                // Pre-llenar observaciones si existen
                if (reclamoData.observaciones_proveedor) {
                    document.getElementById('observaciones_proveedor').value = reclamoData.observaciones_proveedor;
                }
                
                document.getElementById('modal-respuesta').classList.add('active');
                formularioEnviado = false; // Resetear estado de envío
            }
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modal-respuesta').classList.remove('active');
            document.getElementById('observaciones_proveedor').value = '';
            document.querySelectorAll('input[name="estado_reclamo"]').forEach(radio => radio.checked = false);
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modal-respuesta').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Búsqueda en tiempo real
        document.getElementById('buscar').addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('.reclamo-row');
            
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                if (texto.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });

        // Auto-cerrar mensajes después de 5 segundos
        setTimeout(function() {
            const alertas = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alertas.forEach(alerta => {
                alerta.style.transition = 'opacity 0.5s';
                alerta.style.opacity = '0';
                setTimeout(() => alerta.remove(), 500);
            });
        }, 5000);

        // Prevenir reenvío del formulario
        document.getElementById('form-respuesta').addEventListener('submit', function(e) {
            if (formularioEnviado) {
                e.preventDefault();
                return;
            }
            
            formularioEnviado = true;
            
            // Deshabilitar el botón de envío para evitar múltiples clics
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        });

        // Manejar el evento beforeunload para prevenir el mensaje de confirmación
        window.addEventListener('beforeunload', function(e) {
            if (formularioEnviado) {
                // Si el formulario ya fue enviado, no mostrar el mensaje
                return undefined;
            }
        });
    </script>
</body>
</html>
<?php else : ?>
    <?php header("Location: " . URL_WEB . "home/login"); ?>
<?php endif; ?>