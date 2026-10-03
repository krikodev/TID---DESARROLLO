<?php
require_once __DIR__ . '/../../../controller/reclamo.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Reclamaciones</title>
    <link rel="icon" type="image/png" href="<?= URL_IMAGEN_ADMIN ?>icono.png?v=2">
    <link rel="stylesheet" href="<?= URL_WEB ?>css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
        }

        .requerido::after {
            content: " *";
            color: #D43415;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .step.active {
            background-color: #D43415;
            color: white;
        }

        .campo-readonly {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <div class="bg-[#d4ca67] text-white py-4 text-center border-b-4 border-[#070706]">
        <div class="max-w-5xl mx-auto flex items-center gap-4 px-4">
            <img src="<?= URL_IMAGEN_ADMIN ?>logo_tid.png" class="w-28 sm:w-36 lg:w-40 h-auto object-contain" />
            <div class="text-center flex-1">
                <h1 class="text-xl md:text-2xl font-bold mb-1">LIBRO DE RECLAMACIONES ELECTRÓNICO</h1>
                <p class="text-xs md:text-sm">Conforme al Decreto Legislativo N° 1308 y Decreto Supremo N° 011-2011-PCM</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 py-6 max-w-[95%]">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if ($mensaje_exito): ?>
                <div class="bg-green-50 text-green-700 p-3 mb-4 border border-green-200 text-sm">
                    <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
                <div class="bg-red-50 text-red-700 p-3 mb-4 border border-red-200 text-sm">
                    <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-[#0e4b9b] text-white p-3 text-center font-bold text-base">
                Nº DE RECLAMACIÓN: <span id="numero-reclamacion"></span>
            </div>

            <div class="text-right italic text-gray-500 text-xs p-3">
                Fecha de generación: <span id="fecha-generacion"></span>
            </div>

            <div class="flex justify-center items-center mb-6 p-3">
                <div class="step active w-6 h-6 rounded-full bg-[#1EBBE6] text-white font-bold flex items-center justify-center text-xs" data-step="1">1</div>
                <div class="w-12 h-0.5 bg-gray-300 mx-1"></div>
                <div class="step w-6 h-6 rounded-full bg-gray-300 text-gray-600 font-bold flex items-center justify-center text-xs" data-step="2">2</div>
                <div class="w-12 h-0.5 bg-gray-300 mx-1"></div>
                <div class="step w-6 h-6 rounded-full bg-gray-300 text-gray-600 font-bold flex items-center justify-center text-xs" data-step="3">3</div>
            </div>

            <form class="form" id="reclamo-form" method="POST" action="">
                <!-- SECCIÓN I: DATOS DEL PROVEEDOR -->
                <div class="bg-gray-50 p-4 border-l-3 border-[#0e4b9b] mb-4">
                    <h2 class="text-base font-bold text-gray-800 mb-3">I. DATOS DEL PROVEEDOR</h2>
                    <div class="space-y-3">
                        <div>
                            <label for="id_terminal" class="requerido block text-xs font-medium text-gray-700 mb-1">Sucursal/Agencia</label>
                            <select id="id_terminal" name="id_terminal" required onchange="actualizarDatosTerminal()" class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <option value="">Seleccione una sucursal</option>
                                <?php foreach ($terminales as $terminal): ?>
                                    <option value="<?php echo $terminal['id_terminal']; ?>"
                                        data-ruc="<?php echo $terminal['ruc']; ?>"
                                        data-razon="<?php echo htmlspecialchars($terminal['razon_social']); ?>"
                                        data-direccion="<?php echo htmlspecialchars($terminal['direccion_fiscal']); ?>">
                                        <?php echo htmlspecialchars($terminal['nombre_terminal']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="ruc" class="requerido block text-xs font-medium text-gray-700 mb-1">RUC</label>
                                <input type="text" id="ruc" name="ruc" class="w-full p-2 text-sm border border-gray-300 rounded bg-gray-50 campo-readonly" readonly>
                            </div>
                            <div>
                                <label for="razon_social" class="requerido block text-xs font-medium text-gray-700 mb-1">Razón Social</label>
                                <input type="text" id="razon_social" name="razon_social" class="w-full p-2 text-sm border border-gray-300 rounded bg-gray-50 campo-readonly" readonly>
                            </div>
                        </div>
                        <div>
                            <label for="direccion_legal" class="requerido block text-xs font-medium text-gray-700 mb-1">Dirección Legal</label>
                            <input type="text" id="direccion_legal" name="direccion_legal" class="w-full p-2 text-sm border border-gray-300 rounded bg-gray-50 campo-readonly" readonly>
                        </div>
                    </div>
                </div>

                <!-- Paso 1: Datos personales -->
                <div class="form-step active p-4" id="step-1">
                    <h2 class="text-base font-bold text-gray-800 border-b border-[#0e4b9b] pb-2 mb-3">II. DATOS DEL CONSUMIDOR RECLAMANTE</h2>

                    <div class="space-y-4">
                        <!-- Tipo de Persona -->
                        <div>
                            <label class="requerido block text-xs font-medium text-gray-700 mb-2">Tipo de Persona:</label>
                            <div class="flex flex-col sm:flex-row gap-3 text-sm" id="tipo-persona-group">
                                <label class="flex items-center">
                                    <input type="radio" name="tipo_persona" value="Persona Natural" required class="mr-2"> Persona Natural
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="tipo_persona" value="Persona Jurídica" required class="mr-2"> Persona Jurídica
                                </label>
                            </div>
                            <div class="mt-2 text-sm" id="menor-edad-container" style="display: none;">
                                <label class="flex items-center">
                                    <input type="checkbox" id="es_menor_edad" name="es_menor_edad" class="mr-2"> Menor de Edad
                                </label>
                            </div>
                            <div class="error-message text-red-600 text-xs mt-1 hidden" id="tipo-persona-error"></div>
                        </div>

                        <!-- Campos para RUC -->
                        <div id="campos-ruc" style="display: none;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label for="ruc_consumidor" class="requerido block text-xs font-medium text-gray-700 mb-1">RUC</label>
                                    <input type="text" id="ruc_consumidor" name="ruc_consumidor" maxlength="11" placeholder="Ingrese RUC" class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="ruc-error">Por favor, ingrese un RUC válido.</div>
                                </div>
                                <div>
                                    <label for="razon_social_consumidor" class="requerido block text-xs font-medium text-gray-700 mb-1">Razón Social</label>
                                    <input type="text" id="razon_social_consumidor" name="razon_social_consumidor" placeholder="Razón Social" class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="razon-social-error">Por favor, ingrese la razón social.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para Persona Natural -->
                        <div id="campos-persona-natural">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label for="tipo_documento" class="requerido block text-xs font-medium text-gray-700 mb-1">Tipo de Documento</label>
                                    <select id="tipo_documento" name="tipo_documento" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $enum_doc = $conn->query("SHOW COLUMNS FROM reclamos WHERE Field = 'tipo_documento'")->fetch(PDO::FETCH_ASSOC);
                                        preg_match("/^enum\((.*)\)$/", $enum_doc['Type'], $matches);
                                        $enums_doc = array_map(function ($v) {
                                            return trim($v, "'");
                                        }, explode(',', $matches[1]));
                                        foreach ($enums_doc as $val): ?>
                                            <option value="<?= $val ?>"><?= $val ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="tipo-documento-error"></div>
                                </div>
                                <div>
                                    <label for="numero_documento" class="requerido block text-xs font-medium text-gray-700 mb-1">Número de Documento</label>
                                    <input type="text" id="numero_documento" name="numero_documento" maxlength="12" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="numero-documento-error">Por favor, ingrese su número de documento.</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label for="nombres" class="requerido block text-xs font-medium text-gray-700 mb-1">Nombres</label>
                                    <input type="text" id="nombres" name="nombres" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="nombres-error">Por favor, ingrese sus nombres.</div>
                                </div>
                                <div>
                                    <label for="apellidos" class="requerido block text-xs font-medium text-gray-700 mb-1">Apellidos</label>
                                    <input type="text" id="apellidos" name="apellidos" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="apellidos-error">Por favor, ingrese sus apellidos.</div>
                                </div>
                            </div>

                            <!-- Campo para Padre/Madre -->
                            <div id="campo-padre-madre" style="display: none;" class="mt-3">
                                <div>
                                    <label for="nombre_padre_madre" class="requerido block text-xs font-medium text-gray-700 mb-1">Nombre del Padre/Madre o Apoderado</label>
                                    <input type="text" id="nombre_padre_madre" name="nombre_padre_madre" placeholder="Nombre completo del padre, madre o apoderado" class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                    <div class="error-message text-red-600 text-xs mt-1 hidden" id="padre-madre-error">Por favor, ingrese el nombre del padre/madre.</div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="email" class="requerido block text-xs font-medium text-gray-700 mb-1">Correo Electrónico:</label>
                                <input type="email" id="email" name="email" placeholder="Correo" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="email-error">Por favor, ingrese un correo electrónico válido.</div>
                            </div>
                            <div>
                                <label for="celular" class="requerido block text-xs font-medium text-gray-700 mb-1">Número Celular:</label>
                                <input type="tel" id="celular" name="celular" placeholder="Número Celular +51" maxlength="9" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="celular-error">Por favor, ingrese un número de celular válido.</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium hover:bg-gray-300 transition duration-200" onclick="window.close()">Cerrar</button>
                        <button type="button" class="bg-[#0e4b9b] text-white px-4 py-2 rounded text-sm font-medium hover:bg-[#e63c1e] transition duration-200" onclick="nextStep(2)">Siguiente</button>
                    </div>
                </div>

                <!-- Paso 2: Dirección y tipo de reclamo -->
                <div class="form-step p-4" id="step-2">
                    <h2 class="text-base font-bold text-gray-800 border-b border-[#D43415] pb-2 mb-3">III. DETALLES DEL BIEN O SERVICIO RECLAMADO</h2>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label for="direccion" class="requerido block text-xs font-medium text-gray-700 mb-1">Dirección:</label>
                                <input type="text" id="direccion" name="direccion" placeholder="Dirección completa" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="direccion-error">Por favor, ingrese su dirección.</div>
                            </div>
                            <div>
                                <label for="distrito" class="requerido block text-xs font-medium text-gray-700 mb-1">Distrito:</label>
                                <input type="text" id="distrito" name="distrito" placeholder="Distrito" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="distrito-error">Por favor, ingrese su distrito.</div>
                            </div>
                            <div>
                                <label for="provincia" class="requerido block text-xs font-medium text-gray-700 mb-1">Provincia:</label>
                                <input type="text" id="provincia" name="provincia" placeholder="Provincia" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="provincia-error">Por favor, ingrese su provincia.</div>
                            </div>
                            <div>
                                <label for="departamento" class="requerido block text-xs font-medium text-gray-700 mb-1">Departamento:</label>
                                <input type="text" id="departamento" name="departamento" placeholder="Departamento" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="departamento-error">Por favor, ingrese su departamento.</div>
                            </div>
                        </div>

                        <div>
                            <label for="fecha_hecho" class="requerido block text-xs font-medium text-gray-700 mb-1">Fecha del Hecho</label>
                            <input type="date" id="fecha_hecho" name="fecha_hecho" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                            <div class="error-message text-red-600 text-xs mt-1 hidden" id="fecha-hecho-error">Por favor, seleccione la fecha del hecho.</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="requerido block text-xs font-medium text-gray-700 mb-2">Tipo:</label>
                                <div class="flex flex-col gap-1 text-sm">
                                    <label class="flex items-center">
                                        <input type="radio" name="tipo_reclamo" value="reclamo" required class="mr-2"> Reclamo
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="tipo_reclamo" value="queja" required class="mr-2"> Queja
                                    </label>
                                </div>
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="tipo-reclamo-error">Por favor, seleccione un tipo.</div>
                            </div>

                            <div>
                                <label for="producto_servicio" class="requerido block text-xs font-medium text-gray-700 mb-1">Producto o Servicio:</label>
                                <input type="text" id="producto_servicio" name="producto_servicio" placeholder="Producto o servicio relacionado" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="producto-error">Por favor, ingrese el producto o servicio.</div>
                            </div>

                            <div>
                                <label for="monto" class="requerido block text-xs font-medium text-gray-700 mb-1">Monto Reclamado (S/.):</label>
                                <input type="number" id="monto" name="monto" placeholder="0.00" step="0.01" required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none">
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="monto-error">Por favor, ingrese el monto reclamado.</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium hover:bg-gray-300 transition duration-200" onclick="prevStep(1)">Anterior</button>
                        <button type="button" class="bg-[#0e4b9b] text-white px-4 py-2 rounded text-sm font-medium hover:bg-[#e65d1e] transition duration-200" onclick="nextStep(3)">Siguiente</button>
                    </div>
                </div>

                <!-- Paso 3: Detalles y envío -->
                <div class="form-step p-4" id="step-3">
                    <h2 class="text-base font-bold text-gray-800 border-b border-[#D43415] pb-2 mb-3">IV. DESCRIPCIÓN DEL RECLAMO</h2>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label for="detalle_reclamo" class="requerido block text-xs font-medium text-gray-700 mb-1">Detalle de los Hechos:</label>
                                <textarea id="detalle_reclamo" name="detalle_reclamo" rows="3" placeholder="Describa detalladamente los hechos que motivan su reclamo..." required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none resize-y"></textarea>
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="detalle-error">Por favor, describa su queja o reclamo.</div>
                            </div>

                            <div>
                                <label for="pedido" class="requerido block text-xs font-medium text-gray-700 mb-1">Pedido del Consumidor:</label>
                                <textarea id="pedido" name="pedido" rows="3" placeholder="Indique claramente qué solución espera obtener..." required class="w-full p-2 text-sm border border-gray-300 rounded focus:border-[#D43415] focus:ring-1 focus:ring-[#D43415] outline-none resize-y"></textarea>
                                <div class="error-message text-red-600 text-xs mt-1 hidden" id="pedido-error">Por favor, indique qué solución espera.</div>
                            </div>
                        </div>

                        <div>
                            <label for="archivo_adjunto" class="block text-xs font-medium text-gray-700 mb-1">Archivo Adjunto (opcional):</label>
                            <div class="relative overflow-hidden inline-block w-full">
                                <span class="bg-gray-50 border border-gray-300 rounded px-3 py-2 text-sm cursor-pointer inline-block">Seleccionar archivo</span>
                                <input type="file" id="archivo_adjunto" name="archivo_adjunto" class="absolute left-0 top-0 opacity-0 w-full h-full cursor-pointer" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <span class="ml-2 italic text-xs" id="file-name">No se ha seleccionado ningún archivo</span>
                            </div>
                            <small class="text-gray-500 text-xs">Tipos aceptados: .jpg, .png, .pdf, .doc. Tamaño máximo: 5MB.</small>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 p-3 rounded my-4">
                            <h3 class="font-bold text-sm mb-1">DECLARACIÓN JURADA</h3>
                            <p class="text-gray-700 text-xs">Declaro bajo juramento que la información proporcionada en el presente formulario es veraz y exacta. Asimismo, autorizo expresamente el tratamiento de mis datos personales para los fines relacionados con la atención de mi reclamo, de conformidad con la Ley N° 29733, Ley de Protección de Datos Personales.</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex gap-3 text-xs">
                                <a href="#" class="text-[#D43415] no-underline hover:underline" onclick="abrirDocumentos(event, 1)">Decreto supremo</a>
                                <a href="#" class="text-[#D43415] no-underline hover:underline" onclick="abrirDocumentos(event, 2)">Decreto supremo N° 011-211-PCM</a>
                            </div>
                        </div>
                        <div class="flex gap-2 text-xs items-start">
                            <input type="checkbox" id="check-terminos" required class="w-4 h-4 mt-0.5">
                            <label for="check-terminos" class="requerido">Acepto los términos y condiciones y autorizo el tratamiento de mis datos personales</label>
                            <div class="error-message text-red-600 text-xs mt-1 hidden" id="terminos-error">Debe aceptar los términos y condiciones.</div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium hover:bg-gray-300 transition duration-200" onclick="prevStep(2)">Anterior</button>
                        <button type="submit" class="bg-[#0e4b9b] text-white px-4 py-2 rounded text-sm font-medium hover:bg-[#e6961e] transition duration-200" name="enviar">ENVIAR RECLAMO</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;

        // Inicialización del formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Generar número de reclamación automático
            document.getElementById('numero-reclamacion').textContent = 'Se generará automáticamente';

            // Establecer fecha actual
            const fechaActual = new Date();
            document.getElementById('fecha-generacion').textContent = formatearFecha(fechaActual);
            document.getElementById('fecha_hecho').valueAsDate = fechaActual;

            // Event listeners para los radio buttons de tipo de persona
            const radioButtons = document.querySelectorAll('input[name="tipo_persona"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', manejarCambioTipoPersona);
            });

            // Event listener para el checkbox de menor de edad
            document.getElementById('es_menor_edad').addEventListener('change', manejarMenorEdad);

            // Event listener para validar RUC en tiempo real
            document.getElementById('ruc_consumidor').addEventListener('blur', validarRUC);
        });

        // Función para actualizar datos del terminal seleccionado
        function actualizarDatosTerminal() {
            const select = document.getElementById('id_terminal');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption.value) {
                document.getElementById('ruc').value = selectedOption.getAttribute('data-ruc');
                document.getElementById('razon_social').value = selectedOption.getAttribute('data-razon');
                document.getElementById('direccion_legal').value = selectedOption.getAttribute('data-direccion');
            } else {
                document.getElementById('ruc').value = '';
                document.getElementById('razon_social').value = '';
                document.getElementById('direccion_legal').value = '';
            }
        }

        // Función para formatear fecha
        function formatearFecha(fecha) {
            const opciones = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return fecha.toLocaleDateString('es-ES', opciones);
        }

        // Función para cambiar entre pasos
        function nextStep(step) {
            if (validateStep(currentStep)) {
                document.getElementById(`step-${currentStep}`).classList.remove('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

                currentStep = step;

                document.getElementById(`step-${currentStep}`).classList.add('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

                // Scroll al inicio del formulario
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }

        function prevStep(step) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

            currentStep = step;

            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

            // Scroll al inicio del formulario
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Manejo de cambios en el tipo de persona
        function manejarCambioTipoPersona(event) {
            const tipoPersona = event.target.value;
            const camposRUC = document.getElementById('campos-ruc');
            const camposPersonaNatural = document.getElementById('campos-persona-natural');
            const menorEdadContainer = document.getElementById('menor-edad-container');
            const tipoDocumentoSelect = document.getElementById('tipo_documento');

            document.getElementById('es_menor_edad').checked = false;
            document.getElementById('campo-padre-madre').style.display = 'none';
            document.getElementById('nombre_padre_madre').required = false;

            if (tipoPersona === 'Persona Jurídica') {
                // Mostrar campos RUC, ocultar campos persona natural
                camposRUC.style.display = 'block';
                camposPersonaNatural.style.display = 'none';
                menorEdadContainer.style.display = 'none';

                // Hacer requeridos los campos RUC
                document.getElementById('ruc_consumidor').required = true;
                document.getElementById('razon_social_consumidor').required = true;

                // Quitar requeridos de campos persona natural
                document.getElementById('tipo_documento').required = false;
                document.getElementById('numero_documento').required = false;
                document.getElementById('nombres').required = false;
                document.getElementById('apellidos').required = false;

                // Seleccionar automáticamente RUC
                tipoDocumentoSelect.value = 'RUC';

            } else if (tipoPersona === 'Persona Natural') {
                // Mostrar campos persona natural, ocultar campos RUC
                camposRUC.style.display = 'none';
                camposPersonaNatural.style.display = 'block';
                menorEdadContainer.style.display = 'block';

                // Quitar requeridos de campos RUC
                document.getElementById('ruc_consumidor').required = false;
                document.getElementById('razon_social_consumidor').required = false;

                // Hacer requeridos los campos persona natural
                document.getElementById('tipo_documento').required = true;
                document.getElementById('numero_documento').required = true;
                document.getElementById('nombres').required = true;
                document.getElementById('apellidos').required = true;

                // Seleccionar automáticamente DNI
                tipoDocumentoSelect.value = 'DNI';
            }
        }

        function manejarMenorEdad(event) {
            const esMenor = event.target.checked;
            const campoPadreMadre = document.getElementById('campo-padre-madre');
            const inputPadreMadre = document.getElementById('nombre_padre_madre');

            if (esMenor) {
                campoPadreMadre.style.display = 'block';
                inputPadreMadre.required = true;
            } else {
                campoPadreMadre.style.display = 'none';
                inputPadreMadre.required = false;
            }
        }

        function validarRUC() {
            const rucInput = document.getElementById('ruc_consumidor');
            const ruc = rucInput.value.trim();
            const rucError = document.getElementById('ruc-error');

            if (ruc && !esRUCValido(ruc)) {
                rucError.textContent = 'Por favor, ingrese un RUC válido (11 dígitos).';
                rucError.style.display = 'block';
                return false;
            } else {
                rucError.style.display = 'none';
                return true;
            }
        }

        function esRUCValido(ruc) {
            const rucRegex = /^[0-9]{11}$/;
            return rucRegex.test(ruc);
        }

        // Validación de campos por paso
        function validateStep(step) {
            let isValid = true;
            clearErrors();

            switch (step) {
                case 1:
                    if (!document.getElementById('id_terminal').value) {
                        alert('Por favor, seleccione una sucursal en la sección de Datos del Proveedor.');
                        isValid = false;
                    }

                    const tipoPersonaSeleccionado = document.querySelector('input[name="tipo_persona"]:checked');
                    if (!tipoPersonaSeleccionado) {
                        showError('tipo-persona-error', 'Por favor, seleccione el tipo de persona.');
                        isValid = false;
                    } else {
                        const tipoPersona = tipoPersonaSeleccionado.value;

                        if (tipoPersona === 'Persona Jurídica') {
                            if (!document.getElementById('ruc_consumidor').value.trim()) {
                                showError('ruc-error', 'Por favor, ingrese el RUC.');
                                isValid = false;
                            } else if (!esRUCValido(document.getElementById('ruc_consumidor').value.trim())) {
                                showError('ruc-error', 'Por favor, ingrese un RUC válido (11 dígitos).');
                                isValid = false;
                            }

                            if (!document.getElementById('razon_social_consumidor').value.trim()) {
                                showError('razon-social-error', 'Por favor, ingrese la razón social.');
                                isValid = false;
                            }
                        } else if (tipoPersona === 'Persona Natural') {
                            if (!document.getElementById('nombres').value.trim()) {
                                showError('nombres-error', 'Por favor, ingrese sus nombres.');
                                isValid = false;
                            }

                            if (!document.getElementById('apellidos').value.trim()) {
                                showError('apellidos-error', 'Por favor, ingrese sus apellidos.');
                                isValid = false;
                            }

                            if (!document.getElementById('tipo_documento').value) {
                                showError('tipo-documento-error', 'Por favor, seleccione un tipo de documento.');
                                isValid = false;
                            }

                            if (!document.getElementById('numero_documento').value.trim()) {
                                showError('numero-documento-error', 'Por favor, ingrese su número de documento.');
                                isValid = false;
                            }

                            if (document.getElementById('es_menor_edad').checked &&
                                !document.getElementById('nombre_padre_madre').value.trim()) {
                                showError('padre-madre-error', 'Por favor, ingrese el nombre del padre/madre o apoderado.');
                                isValid = false;
                            }
                        }
                    }

                    const email = document.getElementById('email').value;
                    if (!email || !isValidEmail(email)) {
                        showError('email-error', 'Por favor, ingrese un correo electrónico válido.');
                        isValid = false;
                    }

                    const celular = document.getElementById('celular').value;
                    if (!celular || !isValidPhone(celular)) {
                        showError('celular-error', 'Por favor, ingrese un número de celular válido.');
                        isValid = false;
                    }
                    break;

                case 2:
                    if (!document.getElementById('direccion').value.trim()) {
                        showError('direccion-error', 'Por favor, ingrese su dirección.');
                        isValid = false;
                    }

                    if (!document.getElementById('distrito').value.trim()) {
                        showError('distrito-error', 'Por favor, ingrese su distrito.');
                        isValid = false;
                    }

                    if (!document.getElementById('provincia').value.trim()) {
                        showError('provincia-error', 'Por favor, ingrese su provincia.');
                        isValid = false;
                    }

                    if (!document.getElementById('departamento').value.trim()) {
                        showError('departamento-error', 'Por favor, ingrese su departamento.');
                        isValid = false;
                    }

                    if (!document.getElementById('fecha_hecho').value) {
                        showError('fecha-hecho-error', 'Por favor, seleccione la fecha del hecho.');
                        isValid = false;
                    }

                    const tipoReclamo = document.querySelector('input[name="tipo_reclamo"]:checked');
                    if (!tipoReclamo) {
                        showError('tipo-reclamo-error', 'Por favor, seleccione un tipo.');
                        isValid = false;
                    }

                    if (!document.getElementById('producto_servicio').value.trim()) {
                        showError('producto-error', 'Por favor, ingrese el producto o servicio.');
                        isValid = false;
                    }

                    const monto = document.getElementById('monto').value;
                    if (!monto || monto <= 0) {
                        showError('monto-error', 'Por favor, ingrese un monto válido mayor a 0.');
                        isValid = false;
                    }
                    break;

                case 3:
                    if (!document.getElementById('detalle_reclamo').value.trim()) {
                        showError('detalle-error', 'Por favor, describa su queja o reclamo.');
                        isValid = false;
                    }

                    if (!document.getElementById('pedido').value.trim()) {
                        showError('pedido-error', 'Por favor, indique qué solución espera.');
                        isValid = false;
                    }

                    if (!document.getElementById('check-terminos').checked) {
                        showError('terminos-error', 'Debe aceptar los términos y condiciones.');
                        isValid = false;
                    }
                    break;
            }

            return isValid;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const phoneRegex = /^[0-9]{9}$/;
            return phoneRegex.test(phone);
        }

        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }

        function clearErrors() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(error => {
                error.classList.add('hidden');
            });
        }

        document.getElementById('archivo_adjunto').addEventListener('change', function() {
            const fileName = this.files.length > 0 ? this.files[0].name : 'No se ha seleccionado ningún archivo';
            document.getElementById('file-name').textContent = fileName;
        });

        function abrirDocumentos(event, tipo) {
            event.preventDefault();

            setTimeout(function() {
                if (tipo === 1) {
                    window.open('../pdf/decreto.pdf', '_blank');
                } else if (tipo === 2) {
                    window.open('../pdf/decreto1.pdf', '_blank');
                }
            }, 100);
        }

        document.getElementById('reclamo-form').addEventListener('submit', function(e) {
            if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
                e.preventDefault();
                alert('Por favor, complete todos los campos requeridos correctamente.');
                return false;
            }

            if (!document.getElementById('check-terminos').checked) {
                e.preventDefault();
                showError('terminos-error', 'Debe aceptar los términos y condiciones.');
                return false;
            }

            e.preventDefault();

            Swal.fire({
                title: '¿Enviar reclamo?',
                text: 'Confirma para continuar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 💡 Enviar formulario sin romper tu lógica
                    this.submit();
                }
            });


            return true;
        });

        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            if (email && !isValidEmail(email)) {
                showError('email-error', 'Por favor, ingrese un correo electrónico válido.');
            } else {
                document.getElementById('email-error').classList.add('hidden');
            }
        });

        document.getElementById('celular').addEventListener('blur', function() {
            const celular = this.value;
            if (celular && !isValidPhone(celular)) {
                showError('celular-error', 'Por favor, ingrese un número de celular válido (9 dígitos).');
            } else {
                document.getElementById('celular-error').classList.add('hidden');
            }
        });
    </script>

</body>

</html>