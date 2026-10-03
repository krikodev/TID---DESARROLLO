<?php

class ConfiguracionModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_data()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM configuracion");
        $query->execute();
        $reply = $query->fetch(PDO::FETCH_ASSOC);
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => "No se encontraron datos de configuración");
        }
    }

    public function register($data)
    {
        try {
            $conn = $this->db->connect();

            $igv = floatval($data["igv"] ?? 0);
            $porcent_venta = floatval($data["porcent_venta"] ?? 0);
            $porcent_venta_e = floatval($data["porcent_venta_e"] ?? 0);

            $comision_nivel1 = floatval($data["comision_nivel1"] ?? 0);
            $comision_nivel2 = floatval($data["comision_nivel2"] ?? 0);

            $tp_comision_nivel1 = $data["tp_comision_nivel1"] ?? "PORCENTAJE";
            $tp_comision_nivel2 = $data["tp_comision_nivel2"] ?? "PORCENTAJE";

            $numero_GN = $data["numero_GN"] ?? 0;
            $p_empresaxcaja = $data["p_empresaxcaja"] ?? 0;

            $porcentaje_empresa = floatval($data["porcentaje_empresa"] ?? 0);
            $porcentaje_empresa_caja = floatval($data["porcentaje_empresa_caja"] ?? 0);

            if (!in_array($tp_comision_nivel1, ["PORCENTAJE", "MONTO"])) {
                $tp_comision_nivel1 = "PORCENTAJE";
            }

            if (!in_array($tp_comision_nivel2, ["PORCENTAJE", "MONTO"])) {
                $tp_comision_nivel2 = "PORCENTAJE";
            }

            $query = $conn->prepare("SELECT * FROM configuracion LIMIT 1");
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            if ($reply) {
                $query = $conn->prepare("
                UPDATE configuracion SET 
                    igv = :igv, 
                    porcent_venta = :porcent_venta, 
                    porcent_venta_e = :porcent_venta_e,
                    comision_nivel1 = :comision_nivel1,
                    comision_nivel2 = :comision_nivel2,
                    tp_comision_nivel1 = :tp_comision_nivel1,
                    tp_comision_nivel2 = :tp_comision_nivel2,
                    porcentaje_empresa = :porcentaje_empresa,
                    porcentaje_empresa_caja = :porcentaje_empresa_caja,
                    numero_GN = :numero_GN,
                    p_empresaxcaja = :p_empresaxcaja
            ");
            } else {
                $query = $conn->prepare("
                INSERT INTO configuracion (
                    igv, 
                    porcent_venta, 
                    porcent_venta_e, 
                    comision_nivel1, 
                    comision_nivel2,
                    tp_comision_nivel1,
                    tp_comision_nivel2,
                    porcentaje_empresa, 
                    porcentaje_empresa_caja, 
                    numero_GN, 
                    p_empresaxcaja
                ) VALUES (
                    :igv, 
                    :porcent_venta, 
                    :porcent_venta_e, 
                    :comision_nivel1, 
                    :comision_nivel2,
                    :tp_comision_nivel1,
                    :tp_comision_nivel2,
                    :porcentaje_empresa, 
                    :porcentaje_empresa_caja, 
                    :numero_GN, 
                    :p_empresaxcaja
                )
            ");
            }

            $query->bindParam(":igv", $igv);
            $query->bindParam(":porcent_venta", $porcent_venta);
            $query->bindParam(":porcent_venta_e", $porcent_venta_e);

            $query->bindParam(":comision_nivel1", $comision_nivel1);
            $query->bindParam(":comision_nivel2", $comision_nivel2);
            $query->bindParam(":tp_comision_nivel1", $tp_comision_nivel1);
            $query->bindParam(":tp_comision_nivel2", $tp_comision_nivel2);

            $query->bindParam(":porcentaje_empresa", $porcentaje_empresa);
            $query->bindParam(":porcentaje_empresa_caja", $porcentaje_empresa_caja);

            $query->bindParam(":numero_GN", $numero_GN);
            $query->bindParam(":p_empresaxcaja", $p_empresaxcaja);

            if ($query->execute()) {
                return ["success" => true, "message" => "Configuración guardada con éxito"];
            }

            return ["success" => false, "message" => "Error al guardar la configuración"];

        } catch (PDOException $e) {
            return ["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()];
        }
    }

    public function register_config_encomienda($data)
    {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // =========================
            // CONFIG ENCOMIENDA
            // =========================
            $numero_recibo = $data['numero_recibo'] ?? 0;
            $nombre_nota_venta = $data['nombre_nota_venta'] ?? '';

            $query = $conn->prepare("SELECT * FROM configuracion_encomienda");
            $query->execute();
            $existe = $query->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                $query = $conn->prepare("
                UPDATE configuracion_encomienda SET 
                    numero_recibo = :numero_recibo, 
                    default_tipo_precio = :default_tipo_precio,
                    cambiar_n_NV = :cambiar_n_NV,
                    nombre_nota_venta = :nombre_nota_venta,
                    detectar_pago_bloque = :detectar_pago_bloque,
                    nrocel_cliente  = :nrocel_cliente,
                    envio_erroneo  = :envio_erroneo, 
                    rotulado_e  = :rotulado_e
                ");
            } else {
                $query = $conn->prepare("
                INSERT INTO configuracion_encomienda 
                (numero_recibo, default_tipo_precio, cambiar_n_NV, nombre_nota_venta, detectar_pago_bloque, nrocel_cliente, envio_erroneo, rotulado_e) 
                VALUES(:numero_recibo, :default_tipo_precio, :cambiar_n_NV, :nombre_nota_venta, :detectar_pago_bloque, :nrocel_cliente, :envio_erroneo, :rotulado_e)
                ");
            }

            $query->execute([
                ":numero_recibo" => $numero_recibo,
                ":default_tipo_precio" => $data['default_tipo_precio'],
                ":cambiar_n_NV" => $data['cambiar_n_NV'],
                ":nombre_nota_venta" => $nombre_nota_venta,
                ":detectar_pago_bloque" => $data['detectar_pago_bloque'],
                ":nrocel_cliente" => $data['nrocel_cliente'],
                ":envio_erroneo" => $data['envio_erroneo'],
                ":rotulado_e" => $data['rotulado_e']
            ]);

            // =========================
            // EMPRESA (SOLO UPDATE)
            // =========================
            $queryEmpresa = $conn->prepare("
            UPDATE empresa SET
                termscond_encomienda = :termscond_encomienda,
                embarque_general = :embarque_general,
                impresion_ecompleta = :impresion_ecompleta,
                mostrar_direccion_completa = :mostrar_direccion_completa,
                mostrar_tracking = :mostrar_tracking,
                mostrar_vendedor = :mostrar_vendedor,
                datos_destinatario = :datos_destinatario,
                origen_destino = :origen_destino
            ");

            $queryEmpresa->execute([
                ":termscond_encomienda" => $data['termscond_encomienda'] ?? '',
                ":embarque_general" => $data['embarque_general'] ?? 0,
                ":impresion_ecompleta" => $data['impresion_ecompleta'] ?? 0,
                ":mostrar_direccion_completa" => $data['mostrar_direccion_completa'] ?? 0,
                ":mostrar_tracking" => $data['mostrar_tracking'] ?? 0,
                ":mostrar_vendedor" => $data['mostrar_vendedor'] ?? 0,
                ":datos_destinatario" => $data['datos_destinatario'] ?? 0,
                ":origen_destino" => $data['origen_destino'] ?? 0
            ]);

            // =========================
            $conn->commit();

            return ["success" => true, "message" => "Configuración guardada correctamente"];
        } catch (PDOException $e) {
            $conn->rollBack();
            return ["success" => false, "message" => "Error en la base de datos"];
        }
    }

    public function register_config_pasaje($data)
    {
        $conn = null;

        try {
            $conn = $this->db->connect();

            $conn->beginTransaction();

            // ==========================================
            // 1. ACTUALIZAR EMPRESA
            // ==========================================
            $query = $conn->prepare("
            UPDATE empresa SET 
                termscond_pasaje = :termscond_pasaje,
                venta_web = :venta_web,
                l_terminales = :l_terminales,
                l_usuarios = :l_usuarios
            ");

            $query->bindValue(":termscond_pasaje", $data['termscond_pasaje']);
            $query->bindValue(":venta_web", $data['venta_web']);
            $query->bindValue(":l_terminales", $data['l_terminales']);
            $query->bindValue(":l_usuarios", $data['l_usuarios']);

            $query->execute();


            // ==========================================
            // 2. VERIFICAR CONFIGURACIÓN DE PASAJE
            // ==========================================
            $query = $conn->prepare("
            SELECT id_config_pasaje
            FROM configuracion_pasaje
            LIMIT 1
            ");

            $query->execute();

            $configuracion = $query->fetch(PDO::FETCH_ASSOC);


            // ==========================================
            // 3. INSERTAR / ACTUALIZAR CONFIGURACIÓN
            // ==========================================
            if ($configuracion) {

                $query = $conn->prepare("
                UPDATE configuracion_pasaje
                SET manifiesto_sunat = :manifiesto_sunat,
                    tiempo_seleccion = :tiempo_seleccion
                WHERE id_config_pasaje = :id_config_pasaje
                ");

                $query->bindValue(
                    ":manifiesto_sunat",
                    $data['manifiesto_sunat']
                );

                $query->bindValue(
                    ":tiempo_seleccion",
                    $data['tiempo_seleccion']
                );

                $query->bindValue(
                    ":id_config_pasaje",
                    $configuracion['id_config_pasaje'],
                    PDO::PARAM_INT
                );

                $query->execute();

            } else {

                $query = $conn->prepare("
                INSERT INTO configuracion_pasaje (
                    manifiesto_sunat, tiempo_seleccion
                ) VALUES (
                    :manifiesto_sunat, :tiempo_seleccion
                )
               ");

                $query->bindValue(
                    ":manifiesto_sunat",
                    $data['manifiesto_sunat']
                );

                $query->bindValue(
                    ":tiempo_seleccion",
                    $data['tiempo_seleccion']
                );

                $query->execute();
            }


            // ==========================================
            // 5. CONFIRMAR
            // ==========================================
            $conn->commit();

            return [
                "success" => true,
                "message" => "Configuración guardada con éxito"
            ];

        } catch (PDOException $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            return [
                "success" => false,
                "message" => "Error al guardar la configuración",
                "error" => $e->getMessage()
            ];
        }
    }


    public function get_series_manifiesto()
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT id_serie_manifiesto, serie, correlativo, numero_autorizacion, estado
            FROM serie_manifiesto
            ORDER BY serie ASC
            ");
            $query->execute();

            $series = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "message" => $series
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error al obtener las series de manifiesto",
                "error" => $e->getMessage()
            ];
        }
    }

    public function add_serie_manifiesto($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            INSERT INTO serie_manifiesto (
                serie, correlativo, numero_autorizacion, estado
            ) VALUES (
                :serie, :correlativo, :numero_autorizacion, :estado
            )
           ");

            $query->bindValue(":serie", $data['serie']);
            $query->bindValue(":correlativo", $data['correlativo'], PDO::PARAM_INT);
            $query->bindValue(":numero_autorizacion", $data['numero_autorizacion']);
            $query->bindValue(":estado", $data['estado']);

            $query->execute();

            return [
                "success" => true,
                "message" => "Serie registrada con éxito",
                "id_serie_manifiesto" => $conn->lastInsertId()
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error al registrar la serie",
                "error" => $e->getMessage()
            ];
        }
    }

    public function update_serie_manifiesto($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            UPDATE serie_manifiesto SET
                serie = :serie,
                correlativo = :correlativo,
                numero_autorizacion = :numero_autorizacion,
                estado = :estado
            WHERE id_serie_manifiesto = :id_serie_manifiesto
           ");

            $query->bindValue(":serie", $data['serie']);
            $query->bindValue(":correlativo", $data['correlativo'], PDO::PARAM_INT);
            $query->bindValue(":numero_autorizacion", $data['numero_autorizacion']);
            $query->bindValue(":estado", $data['estado']);
            $query->bindValue(":id_serie_manifiesto", $data['id_serie_manifiesto'], PDO::PARAM_INT);

            $query->execute();

            return [
                "success" => true,
                "message" => "Serie actualizada con éxito"
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error al actualizar la serie",
                "error" => $e->getMessage()
            ];
        }
    }

    public function delete_serie_manifiesto($id_serie_manifiesto)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            DELETE FROM serie_manifiesto
            WHERE id_serie_manifiesto = :id_serie_manifiesto
            ");
            $query->bindValue(":id_serie_manifiesto", $id_serie_manifiesto, PDO::PARAM_INT);
            $query->execute();

            return [
                "success" => true,
                "message" => "Serie eliminada con éxito"
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error al eliminar la serie",
                "error" => $e->getMessage()
            ];
        }
    }

    public function register_config_facturador($data)
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("UPDATE empresa SET 
                    termscond_facturador = :termscond_facturador
            ");

            $query->bindParam(":termscond_facturador", $data['termscond_facturador']);

            if ($query->execute()) {
                return ["success" => true, "message" => "Configuración guardada con éxito"];
            } else {
                return ["success" => false, "message" => "Error al guardar la configuración"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Error en la base de datos"];
        }
    }

    public function register_config_cotizacion($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT id_config_cotizacion
            FROM configuracion_cotizacion
            LIMIT 1
            ");
            $query->execute();
            $id = $query->fetchColumn();

            if (empty($id)) {

                $query = $conn->prepare("
                INSERT INTO configuracion_cotizacion
                (terminos_condiciones)
                VALUES
                (:termscond_cotizacion)
                ");

            } else {

                $query = $conn->prepare("
                UPDATE configuracion_cotizacion
                SET terminos_condiciones = :termscond_cotizacion
                WHERE id_config_cotizacion = :id
                ");

                $query->bindParam(":id", $id, PDO::PARAM_INT);
            }

            $query->bindParam(":termscond_cotizacion", $data['termscond_cotizacion'], PDO::PARAM_STR);

            if ($query->execute()) {
                return [
                    "success" => true,
                    "message" => "Configuración guardada con éxito"
                ];
            }

            return [
                "success" => false,
                "message" => "Error al guardar la configuración"
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error en la base de datos"
            ];
        }
    }

    public function get_data_config_encomienda()
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("SELECT * FROM configuracion_encomienda");
        $query->execute();
        $config_data = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
        termscond_encomienda,
        embarque_general,
        impresion_ecompleta,
        mostrar_direccion_completa,
        mostrar_tracking,
        mostrar_vendedor,
        datos_destinatario,
        origen_destino
        FROM empresa
        ");
        $query->execute();
        $data_empresa = $query->fetch(PDO::FETCH_ASSOC);

        $reply = array_merge(
            $config_data ?: [],
            $data_empresa ?: []
        );

        if (!empty($reply)) {
            return ["success" => true, "message" => $reply];
        } else {
            return ["success" => false, "message" => "No se encontraron datos de configuración"];
        }
    }

    public function get_data_config_pasaje()
    {
        $conn = $this->db->connect();

        // Configuración de empresa
        $query = $conn->prepare("
        SELECT 
            termscond_pasaje,
            venta_web,
            l_terminales,
            l_usuarios
        FROM empresa
        ");
        $query->execute();

        $empresa = $query->fetch(PDO::FETCH_ASSOC);
        // Configuración de pasajes
        $query = $conn->prepare("
        SELECT 
            manifiesto_sunat,
            tiempo_seleccion
        FROM configuracion_pasaje
        ");
        $query->execute();

        $config_pasaje = $query->fetch(PDO::FETCH_ASSOC);

        if ($empresa || $config_pasaje) {

            $data = array_merge(
                $empresa ?: [],
                $config_pasaje ?: []
            );

            return [
                "success" => true,
                "message" => $data
            ];
        }

        return [
            "success" => false,
            "message" => "No se encontraron datos de configuración"
        ];
    }
    public function get_data_config_facturador()
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("
            SELECT 
            termscond_facturador
            FROM empresa
        ");
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => "No se encontraron datos de configuración"];
        }
    }

    public function get_data_config_cotizacion()
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("
            SELECT 
            terminos_condiciones
            FROM configuracion_cotizacion
        ");
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => "No se encontraron datos de configuración"];
        }
    }


    // ─────────────────────────────────────────────────────────────
    // 1. CAMPOS DISPONIBLES
    // ─────────────────────────────────────────────────────────────
    public function get_campos_disponibles($modulo)
    {
        $definiciones = [
            'encomienda' => [
                ['campo' => 'origen', 'label' => 'Origen', 'tabla' => 'encomienda'],
                ['campo' => 'destino', 'label' => 'Destino', 'tabla' => 'encomienda'],
                ['campo' => 'estado_encomienda', 'label' => 'Estado', 'tabla' => 'encomienda'],
                ['campo' => 'cliente_nombres_completo', 'label' => 'Cliente', 'tabla' => 'usuario'],
                ['campo' => 'cliente_nombres', 'label' => 'Nombres Cliente', 'tabla' => 'usuario'],
                ['campo' => 'cliente_apellidos', 'label' => 'Apellidos Cliente', 'tabla' => 'usuario'],
                ['campo' => 'cliente_num_docu', 'label' => 'N° Documento', 'tabla' => 'usuario'],
                ['campo' => 'cliente_telefono', 'label' => 'Telefono', 'tabla' => 'usuario'],
                ['campo' => 'tp_moneda_codigo', 'label' => 'Código Moneda', 'tabla' => 'tp_moneda'],
                ['campo' => 'tp_moneda', 'label' => 'Moneda', 'tabla' => 'tp_moneda'],
                ['campo' => 'serie', 'label' => 'Serie', 'tabla' => 'venta'],
                ['campo' => 'correlativo', 'label' => 'Correlativo', 'tabla' => 'venta'],
                ['campo' => 'numero', 'label' => 'Numero', 'tabla' => 'venta'],
                ['campo' => 'descuento', 'label' => 'Descuento', 'tabla' => 'venta'],
                ['campo' => 'op_igv', 'label' => 'IGV', 'tabla' => 'venta'],
                ['campo' => 'op_gravada', 'label' => 'Op. Gravada', 'tabla' => 'venta'],
                ['campo' => 'op_exonerada', 'label' => 'Op. Exonerada', 'tabla' => 'venta'],
                ['campo' => 'op_inafecta', 'label' => 'Op. Inafecta', 'tabla' => 'venta'],
                ['campo' => 'total', 'label' => 'Total', 'tabla' => 'venta'],
                ['campo' => 'estado', 'label' => 'Estado venta', 'tabla' => 'venta'],
                ['campo' => 'envio_sunat', 'label' => 'Enviado SUNAT', 'tabla' => 'venta'],
                ['campo' => 'fecha_emision', 'label' => 'Fecha Emisión', 'tabla' => 'venta'],
                ['campo' => 'fecha_registro', 'label' => 'Fecha Registro', 'tabla' => 'venta'],
                ['campo' => 'programacion_fecha_salida', 'label' => 'Fecha Salida', 'tabla' => 'programacion'],
                ['campo' => 'programacion_hora_salida', 'label' => 'Hora Salida', 'tabla' => 'programacion'],
                ['campo' => 'tp_servicio', 'label' => 'Tipo Servicio', 'tabla' => 'tp_servicio'],
                ['campo' => 'tp_venta_nombre', 'label' => 'Tipo Venta', 'tabla' => 'tp_venta'],
            ],
        ];

        if (!isset($definiciones[$modulo])) {
            return ['success' => false, 'message' => "Módulo '$modulo' no definido"];
        }

        return ['success' => true, 'campos' => $definiciones[$modulo]];
    }

    // ─────────────────────────────────────────────────────────────
    // 2. GUARDAR CONFIGURACIÓN
    // ─────────────────────────────────────────────────────────────
    public function save_config_campos($data)
    {
        $config_tabla = isset($data['config_tabla']) ? $data['config_tabla'] : '';
        $config_id = isset($data['config_id']) ? (int) $data['config_id'] : 0;
        $modulo = isset($data['modulo']) ? $data['modulo'] : '';
        $campos = isset($data['campos']) ? json_decode($data['campos'], true) : [];

        if (!$config_tabla || !$config_id || !$modulo || empty($campos)) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            $del = $conn->prepare("
            DELETE FROM config_exportacion_campos
            WHERE config_tabla = ? AND config_id = ? AND modulo = ?
            ");
            $del->execute([$config_tabla, $config_id, $modulo]);

            $ins = $conn->prepare("
            INSERT INTO config_exportacion_campos
              (config_tabla, config_id, modulo, campo, tabla, label, orden, visible)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($campos as $index => $c) {
                $label = isset($c['label']) ? $c['label'] : $c['campo'];
                $orden = isset($c['orden']) ? $c['orden'] : ($index + 1);
                $visible = isset($c['visible']) ? (int) $c['visible'] : 1;
                $tabla = isset($c['tabla']) ? $c['tabla'] : '';

                $ins->execute([
                    $config_tabla,
                    $config_id,
                    $modulo,
                    $c['campo'],
                    $tabla,
                    $label,
                    $orden,
                    $visible,
                ]);
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Configuración guardada'];
        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 3. CARGAR CONFIG GUARDADA
    //    Llamado al abrir el selector para pre-marcar los campos
    // ─────────────────────────────────────────────────────────────
    public function get_config_campos($modulo, $config_id)
    {
        $conn = $this->db->connect();

        $stmt = $conn->prepare("
        SELECT campo, tabla, label, orden, visible
        FROM   config_exportacion_campos
        WHERE  modulo    = ?
          AND  config_id = ?
          AND  visible   = 1
        ORDER  BY orden ASC
    ");

        $stmt->execute([$modulo, (int) $config_id]);
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($campos) {
            return ['success' => true, 'campos' => $campos];
        }

        return ['success' => false, 'campos' => []];
    }

    // ─────────────────────────────────────────────────────────────
    // 4. EXPORTAR DATOS con los campos configurados
    //    Recibe $_POST con filtros de fecha
    // ─────────────────────────────────────────────────────────────
    public function get_export_data($post)
    {
        $config_tabla = isset($post['config_tabla']) ? $post['config_tabla'] : 'config_encomienda';
        $config_id = isset($post['config_id']) ? (int) $post['config_id'] : 0;
        $modulo = isset($post['modulo']) ? $post['modulo'] : '';
        $fecha_desde = isset($post['fecha_desde']) ? $post['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($post['fecha_hasta']) ? $post['fecha_hasta'] : date('Y-m-t');

        $conn = $this->db->connect();

        // Lee la config guardada del usuario
        $cfg = $conn->prepare("
        SELECT campo, label
        FROM   config_exportacion_campos
        WHERE  config_tabla = ? AND config_id = ? AND modulo = ? AND visible = 1
        ORDER  BY orden ASC
        ");
        $cfg->execute([$config_tabla, $config_id, $modulo]);
        $config_campos = $cfg->fetchAll(PDO::FETCH_ASSOC);

        if (empty($config_campos)) {
            return ['success' => false, 'message' => 'Sin configuración de campos'];
        }

        $campos_alias = array_column($config_campos, 'campo');
        $label_map = array_column($config_campos, 'label', 'campo');

        $query = $conn->prepare("
        SELECT
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c.nombres               AS cliente_nombres,
            c.apellidos             AS cliente_apellidos,
            c.num_docu              AS cliente_num_docu,
            c.celular               AS cliente_telefono,
            v.id_forma_pago,
            v.id_medio_pago,
            tp_m.codigo             AS tp_moneda_codigo,
            tp_m.descripcion        AS tp_moneda,
            v.serie,
            v.correlativo,
            CONCAT(v.serie, '-', v.correlativo) AS numero,
            v.descuento,
            v.op_igv,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            v.estado,
            v.envio_sunat,
            v.fecha_emision,
            v.fecha_registro,
            p.fecha_salida          AS programacion_fecha_salida,
            p.hora_salida           AS programacion_hora_salida,
            tp_s.descripcion        AS tp_servicio,
            tp_v.nombre             AS tp_venta_nombre,
            t_o.nombre AS origen,
            t_d.nombre AS destino,
            e.estado AS estado_encomienda
        FROM venta v
        LEFT JOIN usuario c        ON c.id_usuario        = v.id_cliente
        LEFT JOIN dt_venta dt_v    ON dt_v.id_venta       = v.id_venta
        LEFT JOIN encomienda e     ON e.id_encomienda     = dt_v.id_encomienda
        LEFT JOIN terminal t_o     ON t_o.id_terminal     = e.id_terminal_origen
        LEFT JOIN terminal t_d     ON t_d.id_terminal     = e.id_terminal_destino
        LEFT JOIN programacion p   ON p.id_programacion   = dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN tp_moneda tp_m   ON tp_m.id_tp_moneda   = v.id_tp_moneda
        LEFT JOIN tp_venta tp_v    ON tp_v.id             = v.id_tp_venta
        WHERE v.id_tp_comprobante IN (2)
          AND DATE(v.fecha_emision) BETWEEN ? AND ?
        ");

        $query->execute([$fecha_desde, $fecha_hasta]);
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        // Filtra y renombra columnas según la config del usuario
        $export_rows = array_map(function ($row) use ($campos_alias, $label_map) {
            $filtered = [];
            foreach ($campos_alias as $alias) {
                if (array_key_exists($alias, $row)) {
                    $label = isset($label_map[$alias]) ? $label_map[$alias] : $alias;
                    $filtered[$label] = $row[$alias];
                }
            }
            return $filtered;
        }, $rows);

        return [
            'success' => true,
            'headers' => array_values($label_map),
            'data' => $export_rows,
            'total' => count($export_rows),
        ];
    }
}
