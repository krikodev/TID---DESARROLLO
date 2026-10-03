<?php
require __DIR__ . '/../../vendor/autoload.php';

use Culqi\Culqi;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;

date_default_timezone_set("America/Lima");

class CarritoModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_programaciones($data)
    {
        $conn = $this->db->connect();
        $query = "
SELECT DISTINCT
    p.*,
    t_d.nombre      AS nombre_destino,
    t_o.nombre      AS nombre_origen,
    t_p.descripcion AS descripcion,
    v_n.num_asiento AS num_asientos,
    COALESCE(rd.precio_primer_piso,  p.precio_primer_piso)  AS precio_primer_piso,
    COALESCE(rd.precio_segundo_piso, p.precio_segundo_piso) AS precio_segundo_piso
FROM programacion p
INNER JOIN terminal           t_d ON t_d.id_terminal           = p.id_terminal_destino
INNER JOIN terminal           t_o ON t_o.id_terminal           = p.id_terminal_origen
INNER JOIN tp_servicio_pasaje t_p ON t_p.id_tp_servicio_pasaje = p.id_tp_servicio_pasaje
INNER JOIN vehiculo           v_n ON v_n.id_vehiculo            = p.id_vehiculo
LEFT  JOIN rutas_destino       rd  ON rd.id_programacion         = p.id_programacion
                                  AND rd.id_terminal             = :id_terminal_destino_rd
WHERE p.id_terminal_origen = :id_terminal_origen
AND (
    p.id_terminal_destino = :id_terminal_destino
    OR rd.id_terminal     = :id_terminal_destino_rd2
)
AND p.fecha_salida = :fecha_salida
AND p.estado = 1
";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_terminal_origen", $data['origen']);
        $stmt->bindParam(":id_terminal_destino", $data['destino']);
        $stmt->bindParam(":id_terminal_destino_rd", $data['destino']);
        $stmt->bindParam(":id_terminal_destino_rd2", $data['destino']);
        $stmt->bindParam(":fecha_salida", $data['fechaIda']);
        $stmt->execute();
        $programacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($programacion) {
            foreach ($programacion as &$prog) {
                $prog['detalle'] = '160°';
                $prog['tipo_viaje'] = 'directo';
                $prog['duracion'] = '';
                $prog['hora_llegada'] = '';
                $prog['destacado'] = true;

                $queryAsientos = "
            SELECT COUNT(*) AS total_asientos
            FROM obj_vehiculo
            LEFT JOIN programacion_obj po
                   ON po.id_obj_vehiculo  = obj_vehiculo.id_obj_vehiculo
                  AND po.id_programacion  = :id_programacion
            WHERE obj_vehiculo.id_vehiculo = :id_vehiculo
            AND (
                po.estado IS NULL
                OR po.estado NOT IN ('ANULADO', 'RESERVADO', 'VENTA_WEB', 'VENDIDO', 'PROCESO_WEB')
            )";
                $stmtAsientos = $conn->prepare($queryAsientos);
                $stmtAsientos->bindParam(":id_vehiculo", $prog['id_vehiculo']);
                $stmtAsientos->bindParam(":id_programacion", $prog['id_programacion']);
                $stmtAsientos->execute();
                $asientosDisponibles = $stmtAsientos->fetch(PDO::FETCH_ASSOC);
                $prog['asientos_disponibles'] = $asientosDisponibles['total_asientos'];
            }

            return ["success" => true, "message" => $programacion];
        } else {
            return ["success" => false, "message" => null];
        }
    }
    public function getVehiculo($data)
    {
        try {
            $conn = $this->db->connect();

            $query = "SELECT * FROM programacion WHERE id_programacion=:id_programacion";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id_programacion", $data['id_programacion']);
            $stmt->execute();
            $programacion = $stmt->fetch(PDO::FETCH_ASSOC);

            $query = "SELECT * FROM vehiculo WHERE id_vehiculo=:id_vehiculo";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id_vehiculo", $programacion['id_vehiculo']);
            $stmt->execute();
            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vehiculo) {
                return ["success" => false, "message" => null];
            }

            // Buscar precio en rutas_destino si el destino es una parada intermedia
            $precio_primer_piso = $programacion['precio_primer_piso'];
            $precio_segundo_piso = $programacion['precio_segundo_piso'];
            $es_ruta_destino = false;

            if (!empty($data['id_terminal_destino'])) {
                $query = "
                SELECT precio_primer_piso, precio_segundo_piso 
                FROM rutas_destino 
                WHERE id_programacion = :id_programacion 
                AND id_terminal = :id_terminal
            ";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":id_programacion", $data['id_programacion']);
                $stmt->bindParam(":id_terminal", $data['id_terminal_destino']);
                $stmt->execute();
                $rutaDestino = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($rutaDestino) {
                    $precio_primer_piso = $rutaDestino['precio_primer_piso'];
                    $precio_segundo_piso = $rutaDestino['precio_segundo_piso'];
                    $es_ruta_destino = true;
                }
            }

            $query = "SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id_vehiculo", $programacion['id_vehiculo']);
            $stmt->execute();
            $objs_vehiculo = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = "SELECT * FROM programacion_obj WHERE id_programacion=:id_programacion";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id_programacion", $data['id_programacion']);
            $stmt->execute();
            $objs_programacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $mapaEstados = [];
            foreach ($objs_programacion as $op) {
                if (in_array($op['estado'], ['RESERVADO', 'VENTA_WEB', 'VENDIDO', 'PROCESO_WEB'])) {
                    $mapaEstados[$op['id_obj_vehiculo']] = $op;
                }
            }

            foreach ($objs_vehiculo as &$obj) {
                if (isset($mapaEstados[$obj['id_obj_vehiculo']])) {
                    $obj['estado_asiento'] = $mapaEstados[$obj['id_obj_vehiculo']]['estado'];
                    $obj['id_venta'] = $mapaEstados[$obj['id_obj_vehiculo']]['id_venta'];
                }

                if ($es_ruta_destino) {
                    // Precio único para todos los asientos sin distinción de premium
                    if ($obj['piso'] == 1) {
                        $obj['precio'] = $precio_primer_piso;
                    } else if ($obj['piso'] == 2) {
                        $obj['precio'] = $precio_segundo_piso;
                    }
                } else {
                    // Lógica original: premium → primer piso, resto → segundo piso
                    $obj['precio'] = $obj['tp_asiento'] === 'premium'
                        ? $precio_primer_piso
                        : $precio_segundo_piso;
                }
            }
            unset($obj);

            return [
                "success" => true,
                "message" => [
                    'vehiculo' => $vehiculo,
                    'objs_vehiculo' => $objs_vehiculo,
                    'objs_programacion' => $objs_programacion
                ]
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Error al obtener el vehículo: " . $e->getMessage()];
        }
    }

    public function crear_cargo_unico($data)
    {
        try {
            // Validación inicial del token
            $token = $data['token'] ?? null;
            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => "Token no válido o no proporcionado"
                ];
            }

            // Validar datos requeridos
            $camposRequeridos = ['amount', 'correoComprador', 'nombreComprador', 'apellidosComprador', 'telefonoComprador'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return [
                        'success' => false,
                        'message' => "Campo requerido faltante: {$campo}"
                    ];
                }
            }

            // Validar email
            if (!filter_var($data['correoComprador'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => "Email no válido"
                ];
            }

            // Validar monto (debe ser mayor a 0)
            $monto = (int) $data['amount'];
            if ($monto <= 0) {
                return [
                    'success' => false,
                    'message' => "Monto debe ser mayor a 0"
                ];
            }

            // Configurar Culqi - IMPORTANTE: usar variable de entorno para la API key
            $apiKey = SECRET_KEY_CULQUI;
            $culqi = new Culqi(['api_key' => $apiKey]);

            // Procesar datos de asientos
            $asientos_string = '';
            $num_asientos = 0;

            if (!empty($data['datosOriginales'])) {
                $datos_g = json_decode($data['datosOriginales'], true);
                if (json_last_error() === JSON_ERROR_NONE && isset($datos_g['asientosSeleccionados'])) {
                    $asientos = $datos_g['asientosSeleccionados'];
                    $num_asientos = count($asientos);

                    // Validar y limpiar números de asiento
                    $numeros_asientos = array_map(function ($a) {
                        return isset($a['numAsiento']) ? trim($a['numAsiento']) : '';
                    }, $asientos);

                    // Filtrar asientos vacíos
                    $numeros_asientos = array_filter($numeros_asientos);
                    $asientos_string = implode(', ', $numeros_asientos);
                }
            }

            // Crear el cargo
            $cargo = $culqi->Charges->create([
                "metadata" => [
                    "numero_asientos" => $num_asientos,
                    "asientos" => $asientos_string,
                    "timestamp" => date('Y-m-d H:i:s')
                ],
                "amount" => $monto,
                "source_id" => $token,
                "capture" => true,
                "currency_code" => "PEN",
                "description" => "Venta de pasaje",
                "email" => $data['correoComprador'],
                "installments" => 0,
                "antifraud_details" => [
                    "address" => $data['direccionComprador'] ?? "Av. Lima 123",
                    "address_city" => $data['ciudadComprador'] ?? "LIMA",
                    "country_code" => "PE",
                    "first_name" => $data['nombreComprador'],
                    "last_name" => $data['apellidosComprador'],
                    "phone_number" => $data['telefonoComprador'],
                ]
            ]);

            // Verificar si el cargo fue exitoso
            if (isset($cargo->object) && $cargo->object === 'charge') {
                return [
                    'success' => true,
                    'message' => 'Cargo creado exitosamente',
                    'data' => $cargo
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el cargo',
                    'error' => $cargo
                ];
            }
        } catch (Exception $e) {
            // Log del error (recomendado usar un sistema de logging)
            error_log("Error en crear_cargo_unico: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage() // Solo en desarrollo
            ];
        }
    }

    public function crear_orden_yape($data)
    {
        try {
            // Validar datos requeridos
            $camposRequeridos = ['amount', 'correoComprador', 'nombreComprador', 'apellidosComprador', 'telefonoComprador'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return [
                        'success' => false,
                        'message' => "Campo requerido faltante: {$campo}"
                    ];
                }
            }

            // Validar email
            if (!filter_var($data['correoComprador'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => "Email no válido"
                ];
            }

            // Validar monto (debe ser mayor a 0)
            $monto = (int) $data['amount'];
            if ($monto <= 0) {
                return [
                    'success' => false,
                    'message' => "Monto debe ser mayor a 0"
                ];
            }

            // Configurar Culqi
            $apiKey = SECRET_KEY_CULQUI;
            $culqi = new Culqi(['api_key' => $apiKey]);

            // Procesar datos de asientos
            $asientos_string = '';
            $num_asientos = 0;

            if (!empty($data['datosOriginales'])) {
                $datos_g = json_decode($data['datosOriginales'], true);
                if (json_last_error() === JSON_ERROR_NONE && isset($datos_g['asientosSeleccionados'])) {
                    $asientos = $datos_g['asientosSeleccionados'];
                    $num_asientos = count($asientos);

                    // Validar y limpiar números de asiento
                    $numeros_asientos = array_map(function ($a) {
                        return isset($a['numAsiento']) ? trim($a['numAsiento']) : '';
                    }, $asientos);

                    // Filtrar asientos vacíos
                    $numeros_asientos = array_filter($numeros_asientos);
                    $asientos_string = implode(', ', $numeros_asientos);
                }
            }

            // Generar número de orden único
            $order_number = 'ORDER-' . time() . '-' . rand(1000, 9999);

            // Crear la orden para Yape
            $order = $culqi->Orders->create([
                "amount" => $monto,
                "currency_code" => "PEN",
                "description" => "Venta de pasaje - Yape",
                "order_number" => $order_number,
                "client_details" => [
                    "first_name" => $data['nombreComprador'],
                    "last_name" => $data['apellidosComprador'],
                    "email" => $data['correoComprador'],
                    "phone_number" => $data['telefonoComprador']
                ],
                "metadata" => [
                    "numero_asientos" => $num_asientos,
                    "asientos" => $asientos_string,
                    "timestamp" => date('Y-m-d H:i:s'),
                    "metodo_pago" => "yape",
                    "direccion" => $data['direccionComprador'] ?? "Av. Lima 123",
                    "ciudad" => $data['ciudadComprador'] ?? "LIMA"
                ],
                // Orden válida por 24 horas (86400 segundos)
                "expiration_date" => time() + 24 * 60 * 60
            ]);

            // Verificar si la orden fue creada exitosamente
            if (isset($order->object) && $order->object === 'order') {
                return [
                    'success' => true,
                    'message' => 'Orden creada exitosamente',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'amount' => $order->amount,
                        'currency' => $order->currency_code,
                        'state' => $order->state,
                        'expiration_date' => $order->expiration_date,
                        'payment_code' => $order->payment_code ?? null, // Para Yape
                        'qr_code' => $order->qr_code ?? null // Para mostrar QR de Yape
                    ],
                    'order_complete' => $order // Objeto completo por si necesitas más datos
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la orden',
                    'error' => $order
                ];
            }
        } catch (Exception $e) {
            // Log del error
            error_log("Error en crear_orden_yape: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage() // Solo en desarrollo
            ];
        }
    }

    public function verificar_estado_orden($order_id)
    {
        try {
            if (empty($order_id)) {
                return [
                    'success' => false,
                    'message' => 'ID de orden no proporcionado'
                ];
            }

            $apiKey = SECRET_KEY;
            $culqi = new Culqi(['api_key' => $apiKey]);

            // Obtener la orden
            $order = $culqi->Orders->get($order_id);

            if (isset($order->object) && $order->object === 'order') {
                return [
                    'success' => true,
                    'data' => [
                        'order_id' => $order->id,
                        'state' => $order->state, // pending, paid, expired, etc.
                        'amount' => $order->amount,
                        'paid_amount' => $order->paid_amount ?? 0,
                        'expiration_date' => $order->expiration_date
                    ],
                    'order_complete' => $order
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Orden no encontrada'
                ];
            }
        } catch (Exception $e) {
            error_log("Error en verificar_estado_orden: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error al verificar orden',
                'error_message' => $e->getMessage()
            ];
        }
    }

    public function concretar_venta($data)
    {
        try {
            $conn = $this->db->connect();

            $data = $this->preparar_datos_entrada($data);
            $data = $this->preparar_datos_factura($data);
            $this->validar_datos_necesarios($data);
            $data = $this->transformar_datos_pasajeros($data);
            $data = $this->validar_datos_adicionales($data);
            $resultado = $this->procesar_venta($conn, $data);

            if ($resultado['success'] && !empty($resultado['asientos_ws'])) {
                $this->notificarWebSocket(
                    "empresa_{$this->uuid_ws_sesion}_pasaje",
                    [
                        "tipo" => "concretar_venta_web",
                        "id_programacion" => $data["id_programacion"],
                        "asientos" => $resultado['asientos_ws']
                    ]
                );
            }

            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function preparar_datos_entrada($data)
    {
        if (isset($data['datosOriginales']) && is_string($data['datosOriginales'])) {
            $decoded = json_decode($data['datosOriginales'], true);
            if ($decoded === null || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar datosOriginales: ' . json_last_error_msg());
            }
            $data['datosOriginales'] = $decoded;
            $data['id_programacion'] = $data['datosOriginales']['programacion'];
        }
        return $data;
    }

    private function preparar_datos_factura($data)
    {
        if (isset($data['datosFactura']) && is_string($data['datosFactura'])) {
            $decoded = json_decode($data['datosFactura'], true);
            if ($decoded === null || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar datosFactura: ' . json_last_error_msg());
            }
            $data['datosFactura'] = $decoded;
        }
        return $data;
    }

    public function validar_datos_necesarios($data)
    {
        if (!isset($data['datosOriginales']) || !is_array($data['datosOriginales'])) {
            throw new Exception('No se encontraron datosOriginales válidos');
        }
        if (!isset($data['datosOriginales']['datosPasajeros']) || !is_array($data['datosOriginales']['datosPasajeros'])) {
            throw new Exception('No se encontraron datos de pasajeros');
        }
        if (!isset($data['datosOriginales']['programacion'])) {
            throw new Exception('No se encontró la programación');
        }
    }

    public function transformar_datos_pasajeros($data)
    {
        $data['pasajeros'] = [];

        foreach ($data['datosOriginales']['datosPasajeros'] as $index => $pasajero) {
            // Validar campos requeridos para cada pasajero
            $this->validar_campos_pasajero($pasajero, $index);

            $data['pasajeros'][] = [
                'PrecioU' => $pasajero['precioAsiento'],
                'id_asiento' => $pasajero['asientoId'],
                'numAsiento' => trim($pasajero['numeroAsiento']),
                'tp_doc' => $pasajero['tipoDocumento'],
                'num_doc' => $pasajero['numeroDocumento'],
                'nombres' => $pasajero['nombres'],
                'apellidos' => $pasajero['apellidos'],
                'fecha_nacimiento' => $pasajero['fechaNacimiento'],
                'genero' => $pasajero['genero'],
                'email' => $pasajero['email'] ?? null,
                'telefono' => $pasajero['telefono'] ?? null,
                'esunat' => '-',
                'csunat' => '-',
                'es_principal' => $index === 0 // Solo el primer pasajero es principal
            ];
        }

        return $data;
    }

    private function validar_campos_pasajero($pasajero, $index)
    {
        $camposRequeridos = ['asientoId', 'numeroAsiento', 'precioAsiento', 'tipoDocumento', 'numeroDocumento', 'nombres', 'apellidos', 'fechaNacimiento', 'genero'];

        // Solo validar email y teléfono para el primer pasajero (pasajero principal)
        if ($index === 0) {
            $camposRequeridos[] = 'email';
            $camposRequeridos[] = 'telefono';
        }

        foreach ($camposRequeridos as $campo) {
            if (!isset($pasajero[$campo]) || empty($pasajero[$campo])) {
                throw new Exception("Campo '{$campo}' faltante o vacío para el pasajero " . ($index + 1));
            }
        }
    }

    public function validar_datos_adicionales($data) // 🔧 ahora retorna $data
    {
        $data['id_programacion'] = $data['datosOriginales']['programacion'];

        if (!isset($data['charge_id']) || empty($data['charge_id'])) {
            throw new Exception('No se encontró charge_id');
        }

        $data['inputComprobanteValue'] = $data['charge_id'];

        return $data;
    }

    private function procesar_venta($conn, $data)
    {
        if (!$data || !isset($data["pasajeros"]) || !is_array($data["pasajeros"]) || count($data["pasajeros"]) === 0) {
            throw new Exception("No hay ningún pasajero a procesar");
        }

        $conn->beginTransaction();

        try {
            $ultimo_id_venta = null;
            $links = [];
            $pasajes_final = [];
            $asientos_ws = []; // NUEVO: acumular todos los asientos para el WS

            foreach ($data["pasajeros"] as $index => $pasajero) {
                $ultimo_id_venta = $this->procesar_pasajero_individual($conn, $pasajero, $data, $index);
                $links[] = URL . 'comprobantes/impresion/comprobante/' . $ultimo_id_venta;
                $pasajes_final[] = [
                    'asiento' => $pasajero['numAsiento'],
                    'url' => URL . 'comprobantes/impresion/comprobante/' . $ultimo_id_venta
                ];

                // NUEVO: guardar id_obj_vehiculo + id_venta de cada pasajero
                $asientos_ws[] = [
                    'id_obj_vehiculo' => $pasajero['id_asiento'],
                    'estado' => 'VENTA_WEB',
                    'id_venta' => $ultimo_id_venta,

                ];
            }

            $conn->commit();

            return [
                'success' => true,
                'id_venta' => $ultimo_id_venta,
                'message' => "Todos los pasajeros han sido registrados correctamente",
                'links' => $links,
                'pasajes' => $pasajes_final,
                'asientos_ws' => $asientos_ws // NUEVO: pasar al caller
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Error al procesar la venta: " . $e->getMessage());
        }
    }

    private function procesar_pasajero_individual($conn, $pasajero, $data, $index)
    {
        // 1. Gestionar usuario (crear o actualizar)
        $id_usuario = $this->gestionar_usuario($conn, $pasajero);
        $pasajero['id_usuario'] = $id_usuario;

        // En caso se haya dicho para generar facturas 
        if (isset($data['datosFactura'])) {
            $id_usuario = $this->gestionar_ruc($conn, $data['datosFactura']);
            $tp_comprobante = 1;
        } else {
            $tp_comprobante = 3;
        }
        // 2. Crear venta
        $medio_pago = $data['payment_method'] == 'tarjeta' ? 9 : 10;
        $ids_ventas = [];
        $id_venta = $this->crear_venta($conn, $id_usuario, $data['charge_id'], $medio_pago, $pasajero, $tp_comprobante);

        // 3. Crear detalle de venta
        $this->crear_detalle_venta($conn, $id_venta, $pasajero, $data['id_programacion']);

        // Enviar comprobante a la sunat
        $this->enviar_comprobante($conn, $id_venta);

        // 4. Actualizar programación objeto
        $this->actualizar_programacion_objeto($conn, $pasajero['id_asiento'], $data['id_programacion'], $id_venta);

        return $id_venta;
    }

    private function gestionar_usuario($conn, $pasajero)
    {
        $sql = "SELECT id_usuario FROM usuario WHERE num_docu = :num_doc AND id_tp_usuario = 5";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':num_doc', $pasajero['num_doc']);
        $stmt->execute();

        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            $id_usuario = $usuarioExistente['id_usuario'];

            // Actualizar solo si es pasajero principal (tiene email y teléfono)
            if (!empty($pasajero['email']) && !empty($pasajero['telefono'])) {
                $sql = "UPDATE usuario SET email = :email, celular = :telefono WHERE id_usuario = :id_usuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $pasajero['email']);
                $stmt->bindParam(':telefono', $pasajero['telefono']);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->execute();
            }

            return $id_usuario;
        } else {
            $nombres = strtoupper(trim($pasajero['nombres']));
            $apellidos = strtoupper(trim($pasajero['apellidos']));
            $email = $pasajero['email'] ?? '';
            $celular = $pasajero['telefono'] ?? '';

            $sql = "INSERT INTO usuario 
                    (id_terminal, id_tp_docu, num_docu, nombres, apellidos, fecha_nacimiento, 
                     genero, celular, email, direccion, ubigeo, id_tp_usuario, estado)
                VALUES 
                    ('1', :tp_doc, :num_doc, :nombres, :apellidos, :fecha_nacimiento, 
                     :genero, :celular, :email, 'S/D', '010101', '5', '1')";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':tp_doc', $pasajero['tp_doc']);
            $stmt->bindParam(':num_doc', $pasajero['num_doc']);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':fecha_nacimiento', $pasajero['fecha_nacimiento']);
            $stmt->bindParam(':genero', $pasajero['genero']);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':celular', $celular);
            $stmt->execute();

            return (int) $conn->lastInsertId();
        }
    }

    private function gestionar_ruc($conn, $datos_factura)
    {
        $sql = "SELECT id_usuario FROM usuario WHERE num_docu = :num_doc AND id_tp_usuario = 5";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':num_doc', $datos_factura['ruc']);
        $stmt->execute();

        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            $id_usuario = $usuarioExistente['id_usuario'];
            return $id_usuario;
        } else {
            $nombres = strtoupper(trim($datos_factura['razonSocial']));
            $apellidos = '';
            $celular = '';
            $tp_docum = 6;
            $genero = '';
            $email = '';
            $celular = '';
            $ruc_factura = $datos_factura['ruc'];
            $ubigeo = isset($datos_factura['ubigeoFactura'][2]) ? $datos_factura['ubigeoFactura'][2] : null;
            $estado_sunat = $datos_factura['estadoFactura'];
            $condicion_sunat = $datos_factura['condicionFactura'];
            $direccion = $datos_factura['direccionFactura'] ?? 'S/D';

            $sql = "INSERT INTO usuario 
                    (id_terminal, id_tp_docu, num_docu, nombres, apellidos, 
                     genero, celular, email, direccion, ubigeo, id_tp_usuario, 
                     estado, nacionalidad, estado_sunat, condicion_sunat)
                VALUES 
                    ('1', :tp_doc, :num_doc, :nombres, :apellidos,
                     :genero, :celular, :email, :direccion, :ubigeo, '5', 
                     '1', 'PERUANO (A)', :estado_sunat, :condicion_sunat)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':tp_doc', $tp_docum);
            $stmt->bindParam(':num_doc', $ruc_factura);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':genero', $genero);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':ubigeo', $ubigeo);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':celular', $celular);
            $stmt->bindParam(':estado_sunat', $estado_sunat);
            $stmt->bindParam(':condicion_sunat', $condicion_sunat);
            $stmt->execute();

            return (int) $conn->lastInsertId();
        }
    }

    private function crear_venta($conn, $id_usuario, $codigo_comprobante, $medio_pago, $pasajero, $tp_comprobante)
    {
        $codigo_venta = uniqid(rand());
        $fecha_emision = date("Y-m-d H:i:s");
        $fecha_vencimiento = date("Y-m-d");
        $query = $conn->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante");
        $query->bindParam(":id_tp_comprobante", $tp_comprobante);
        $query->execute();
        $serie_data = $query->fetch(PDO::FETCH_ASSOC);
        $serie = $serie_data['serie'] ?? null;
        $correlativo = $this->get_correlativo($serie_data['id_serie']);
        $op_igv = 0.00;
        $op_gravada = 0.00;
        $op_exonerada = $pasajero['PrecioU'];
        $op_inafecta = 0.00;
        $total = $pasajero['PrecioU'];
        $id_tp_venta = 1; // Se refiere a tipo de venta pasaje, eencomienda y otros
        $id_tp_operacion = 1; // Venta interna

        // consultar id de venta web como vendedor 
        $query = $conn->prepare("
         SELECT id_usuario FROM usuario WHERE num_docu = '1111'
        ");
        $query->execute();
        $id_vendedor = $query->fetchColumn();

        // Medio de pago 
        $sql = "INSERT INTO venta 
                (codigo, id_vendedor, id_cliente, id_forma_pago, id_medio_pago, id_tp_moneda, estado, cod_operacion,
                fecha_emision, id_tp_comprobante, id_serie, serie, correlativo, op_igv, op_gravada, op_exonerada, op_inafecta,
                total, fecha_vencimiento, id_tp_venta, id_tp_operacion)
            VALUES 
                (:codigo_venta, :id_vendedor, :id_usuario, '1', :medio_pago, '1', 'PAGADO', :codigo_comprobante,
                :fecha_emision, :id_tp_comprobante, :id_serie, :serie, :correlativo, :op_igv, :op_gravada, :op_exonerada, :op_inafecta,
                :total, :fecha_vencimiento, :id_tp_venta, :id_tp_operacion)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_venta', $codigo_venta);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':medio_pago', $medio_pago);
        $stmt->bindParam(':codigo_comprobante', $codigo_comprobante);
        $stmt->bindParam(':fecha_emision', $fecha_emision);
        $stmt->bindParam(':id_tp_comprobante', $tp_comprobante);
        $stmt->bindParam(':id_serie', $serie_data['id_serie']);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':correlativo', $correlativo);
        $stmt->bindParam(':op_igv', $op_igv);
        $stmt->bindParam(':op_gravada', $op_gravada);
        $stmt->bindParam(':op_exonerada', $op_exonerada);
        $stmt->bindParam(':op_inafecta', $op_inafecta);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
        $stmt->bindParam(':id_tp_venta', $id_tp_venta);
        $stmt->bindParam(':id_tp_operacion', $id_tp_operacion);

        if (!$stmt->execute()) {
            throw new Exception("Error al crear venta");
        }

        return (int) $conn->lastInsertId();
    }

    private function crear_detalle_venta($conn, $id_venta, $pasajero, $id_programacion)
    {
        // Obtener datos del asiento
        $sql = "SELECT * FROM obj_vehiculo WHERE id_obj_vehiculo = :id_asiento";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_asiento', $pasajero['id_asiento'], PDO::PARAM_INT);
        $stmt->execute();

        $asiento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asiento) {
            throw new Exception("No se encontró el asiento ID: {$pasajero['id_asiento']}");
        }

        $num_asiento = trim($asiento['text_obj']);

        $sql = "INSERT INTO dt_venta 
                (id_venta, id_tp_servicio, id_programacion, piso, num_asiento, 
                 estado_asiento, precio, id_pasajero, op_igv, op_gravada, op_exonerada, 
                 op_inafecta, op_total)
            VALUES 
                (:id_venta, '1', :id_programacion, :piso, :numAsiento, 'VENTA_WEB', :precio, 
                 :id_pasajero, 0.00, 0.00, :op_exonerada, 0.00, :op_total)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
        $stmt->bindParam(':piso', $asiento['piso']);
        $stmt->bindParam(':numAsiento', $num_asiento);
        $stmt->bindParam(':precio', $pasajero['PrecioU']);
        $stmt->bindParam(':id_pasajero', $pasajero['id_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':op_exonerada', $pasajero['PrecioU']);
        $stmt->bindParam(':op_total', $pasajero['PrecioU']);

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar detalle de venta");
        }
    }

    private function actualizar_programacion_objeto($conn, $id_asiento, $id_programacion, $id_venta)
    {
        $fecha_emision = date("Y-m-d H:i:s");
        $estado_venta = "VENTA_WEB";

        $sql = "UPDATE programacion_obj SET 
                estado          = :estado,
                id_venta        = :id_venta,
                estado_proceso  = 0,
                fecha_registro  = :fecha_registro
            WHERE 
                id_obj_vehiculo = :id_obj_vehiculo 
                AND id_programacion = :id_programacion";

        $query = $conn->prepare($sql); 
        $query->bindParam(':id_obj_vehiculo', $id_asiento, PDO::PARAM_INT);
        $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
        $query->bindParam(':estado', $estado_venta);
        $query->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $query->bindParam(':fecha_registro', $fecha_emision);

        if (!$query->execute()) { 
            throw new Exception("Error al actualizar programación objeto");
        }
    }

    public function enviar_comprobante($conn = null, $id_venta = 0)
    {
        try {
            $this->generar_codQR($id_venta, $conn);
            $json = json_encode($this->get_data_comprobante_sunat($conn, $id_venta));
            $rpta_sunat = $this->enviar_json_a_api($json);
            $resp = json_decode($rpta_sunat, true);
            $resp[0]["id_venta"] = $id_venta;
            $this->updateVentaSetForSunat($conn, $resp[0]);
        } catch (PDOException $e) {
        }
    }

    public function notificarWebSocket(string $room, array $data): void
    {
        $url = "http://127.0.0.1:3000/evento";
        $json = json_encode(array_merge(["room" => $room], $data));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-secret-key: " . getenv('WEBSOCKET_SECRET'),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log temporal para debugear
    }

    //=================APARTADO DE FACTURACION =======================================

    public function updateVentaSetForSunat($conn = null, $data)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $envio_sunat = $data["estado"] == 1 ? 1 : 0;
        $query = $conn->prepare("UPDATE venta SET
            envio_sunat=:envio_sunat,
            descrip_cdr_sunat=:descrip_cdr_sunat,
            hash_cdr=:hash_cdr,
            file_xml=:file_xml,
            file_cdr=:file_cdr
        WHERE id_venta=:id_venta
        ");
        $query->bindParam(':envio_sunat', $envio_sunat);
        $query->bindParam(':descrip_cdr_sunat', $data["mensaje_sunat"]);
        $query->bindParam(':hash_cdr', $data["hash_cpe"]);
        $query->bindParam(':file_xml', $data["xml"]);
        $query->bindParam(':file_cdr', $data["cdr"]);
        $query->bindParam(':id_venta', $data["id_venta"]);
        $query->execute();
    }

    public function get_data_comprobante_sunat($conn = null, $id_venta)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $query = $conn->prepare("SELECT
        e.envio_ose AS ose,
        e.num_docu AS ruc,
        e.razon_social AS razon_social,
        e.razon_social AS nombre_comercial,
        e.ubigeo AS ubigeo,
        ub.depa AS departamento,
        ub.provi AS provincia,
        ub.distri AS distrito,
        e.direccion_fiscal AS direccion,    
        e.user_sol AS usuario_sol,
        e.pass_sol AS clave_sol,
        td.id_tp_docu AS tipodoc,
        e.guia_id AS api_id,
        e.guia_clave AS api_clave
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo
        LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
        LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
        WHERE t.id_terminal=1");
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);
        $emisor['pais'] = "PE";

        $query = $conn->prepare("
        SELECT 
        v.serie,
        v.correlativo,
        v.fecha_emision,
        date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format,
        date_format(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
        v.id_forma_pago,
        f_p.descripcion AS forma_pago,
        v.id_medio_pago,
        m_p.descripcion AS medio_pago,
        v.fecha_vencimiento,
        v.id_tp_moneda,
        tp_m.codigo AS tp_moneda_codigo,
        tp_m.descripcion AS tp_moneda_descripcion,
        v.id_tp_comprobante,
        v.op_igv,
        v.op_gravada,
        v.op_exonerada,
        v.op_inafecta,
        v.total,
        v.descuento,
        tp_c.descripcion AS tp_comprobante,
        tp_c.codigo AS tp_comprobante_codigo,
        v.estado,
        v.icbper,
        v.fecha_registro,
        v.id_cliente,
        v.obs,
        v.afecta_detraccion
        FROM venta v
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
        LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
        LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
        LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
        WHERE v.id_venta = :id_venta
        ");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $data_venta = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("SELECT
            CONCAT(c.nombres,' ',c.apellidos ) AS razon_social,
            c.id_tp_docu AS tipo_documento,
            c.num_docu AS ruc,
            c.direccion AS direccion
            FROM usuario c
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
            WHERE c.id_usuario=:id_usuario");
        $query->bindParam(":id_usuario", $data_venta['id_cliente']);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);
        $cliente['pais'] = 'PE';

        //=============CALCULOS=========================
        $total_impuestos = 0.00;

        $cabecera = [
            'tipo_operacion' => $data_venta['afecta_detraccion'] == 1 ? "1004" : "0101",
            'tipo_comprobante' => $data_venta['tp_comprobante_codigo'],
            'moneda' => $data_venta['tp_moneda_codigo'],
            'serie' => $data_venta['serie'],
            'correlativo' => $data_venta['correlativo'],
            'total_op_gravadas' => $data_venta['op_gravada'],
            'igv' => $data_venta['op_igv'],
            'icbper' => $data_venta['icbper'],
            'total_op_exoneradas' => $data_venta['op_exonerada'],
            'total_op_inafectas' => $data_venta['op_inafecta'],
            'total_antes_impuestos' => $data_venta['total'],
            'total_impuestos' => $total_impuestos,
            'total_despues_impuestos' => $data_venta['total'],
            'descuento_global' => 0.00,
            'suma_descuento_item' => 0.00,
            'total_a_pagar' => $data_venta['total'],
            'fecha_emision' => $data_venta['fecha_emision_format'],
            'hora_emision' => $data_venta['hora_emision_format'],
            'fecha_vencimiento' => $data_venta['fecha_vencimiento'],
            'forma_pago' => ucwords(strtolower(ucfirst($data_venta['forma_pago']))),
            'monto_credito' => 0.00,
            'anexo_sucursal' => "0000",
            'cuotas' => [],
            'observacion' => $data_venta['obs'] ?? '',
            "detraccion" => false,
            "detraccion_data" => []
        ];

        $query = $conn->prepare("SELECT
            dt_v.piso,
            dt_v.num_asiento,
            dt_v.estado_asiento,
            dt_v.precio,
            dt_v.op_igv,
            dt_v.op_gravada,
            dt_v.op_exonerada,
            dt_v.op_inafecta,
            dt_v.op_total
        FROM dt_venta dt_v
        WHERE dt_v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $data_item = $query->fetch(PDO::FETCH_ASSOC);

        $item = [
            "item" => 1,
            "nombre" => "PASAJE",
            "cantidad" => 1,
            "codigo" => 0,
            "valor_unitario" => $data_item['precio'],
            "precio_lista" => $data_item['precio'],
            "valor_total" => $data_item['precio'],
            "igv" => $data_item['op_igv'],
            "icbper" => 0.00,
            "factor_icbper" => 0.00,
            "total_antes_impuestos" => $data_item['op_exonerada'],
            "total_impuestos" => $data_item['op_igv'],
            "porcentaje_igv" => 0,
            "unidad" => "ZZ",
            "codigos" => ["E", "20", "9997", "EXO", "VAT"]
        ];
        $items[] = $item;

        return [
            "ose" => $emisor['ose'],
            "emisor" => $emisor,
            "cabecera" => $cabecera,
            "cliente" => $cliente,
            "items" => $items,
        ];
    }

    public function enviar_json_a_api($json)
    {

        $api_url = API_URL;

        $opciones = array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            )
        );

        $curl = curl_init();
        curl_setopt_array($curl, $opciones);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return 'Error en la solicitud: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }
}
