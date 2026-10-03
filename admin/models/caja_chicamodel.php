<?php

date_default_timezone_set('America/Lima');

class Caja_ChicaModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $wh = '';
            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $wh = "WHERE cj.id_usuario = " . $this->id_usuario_sesion;
            }

            $selectFields = "cj.id_caja_chica,
            cj.id_usuario,
            u.nombres AS usuario_nombres,
            u.apellidos AS usuario_apellidos,
            tp_u.descripcion AS tp_usuario,
            tp_u.tipo AS tp_usuario_tipo,
            cj.referencia,
            cj.saldo_inicial,
            cj.saldo_final,
            cj.saldo_real,
            cj.fecha_inicio,
            cj.fecha_fin,
            cj.estado";
            $baseQuery = "FROM caja_chica cj
            LEFT JOIN usuario u ON u.id_usuario=cj.id_usuario
            LEFT JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            $wh";
            $searchColumns = ["cj.referencia", "u.nombres", "u.apellidos", "CONCAT(u.nombres, ' ', u.apellidos)", "tp_u.descripcion", "cj.fecha_inicio", "cj.fecha_fin", "cj.saldo_inicial", "cj.saldo_final", "cj.saldo_real", "CASE WHEN cj.estado = 1 THEN 'Abierto' ELSE 'Cerrado' END"];
            $orderBy = "id_caja_chica DESC";

            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );
        } catch (PDOException $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    public function add_register($data)
    {
        try {
            $estado = 1;
            $fecha_inicio = date("Y-m-d H:i:s");
            $query = $this->db->connect()->prepare("SELECT COUNT(*) AS amount FROM caja_chica WHERE id_usuario=:id_usuario AND estado=:estado");
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->bindParam(":estado", $estado);
            $query->execute();
            $amount = $query->fetch(PDO::FETCH_ASSOC)["amount"];
            if ($amount > 0) {
                return array("success" => false, "message" => "No se puede crear la caja chica, por favor cierre caja chica para el usuario definido");
            }

            $query = $this->db->connect()->prepare("INSERT INTO caja_chica (
                id_usuario, referencia, saldo_inicial, fecha_inicio, estado) 
                VALUES(
                :id_usuario, :referencia, :saldo_inicial, :fecha_inicio, :estado)");
            $query->bindParam(':id_usuario', $this->id_usuario_sesion);
            $query->bindParam(':referencia', $data["referencia"]);
            $query->bindParam(':saldo_inicial', $data["saldo_inicial"]);
            $query->bindParam(':fecha_inicio', $fecha_inicio);
            $query->bindParam(':estado', $estado);
            $query->execute();
            return array('success' => true, "message" => "Registro creado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("UPDATE caja_chica SET 
            referencia=:referencia,
            saldo_inicial=:saldo_inicial
            WHERE id_caja_chica=:id_caja_chica");
            $query->bindParam(':id_caja_chica', $data["id_caja_chica"]);
            $query->bindParam(':referencia', $data["referencia"]);
            $query->bindParam(':saldo_inicial', $data["saldo_inicial"]);
            $query->execute();
            return array('success' => true, "message" => "Registro modificado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM caja_chica WHERE id_caja_chica=:id_caja_chica");
            $query->bindParam(":id_caja_chica", $data["id_caja_chica"]);
            $query->execute();
            return array('success' => true, "message" => "Registro eliminado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El registro se encuentra protegido");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function cerrar_caja($data)
    {
        try {
            $fecha_fin = date("Y-m-d H:i:s");
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $query = $conn->prepare("
              SELECT
                 COALESCE(SUM(v.total), 0) AS montoFinal,
                 cj.saldo_inicial
              FROM caja_chica cj
              LEFT JOIN venta v 
                  ON cj.id_caja_chica = v.id_caja_chica
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
              WHERE cj.id_caja_chica = :id_caja_chica
              GROUP BY cj.id_caja_chica, cj.saldo_inicial
            ");
            $query->bindParam(":id_caja_chica", $data["id_caja"]);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            $total_ventas = floatval($reply["montoFinal"] ?? 0);
            $saldo_inicial = floatval($reply["saldo_inicial"] ?? 0);

            $monto_final = $total_ventas + $saldo_inicial;

            $query = $conn->prepare("
             SELECT
               p_empresaxcaja,
               porcentaje_empresa_caja
             FROM configuracion
             LIMIT 1
            ");
            $query->execute();

            $config = $query->fetch(PDO::FETCH_ASSOC);
            if ($config["p_empresaxcaja"] == 1) {

                $porcentaje_empresa = floatval(
                    $config["porcentaje_empresa_caja"] ?? 0
                );

                $monto_empresa = $total_ventas * ($porcentaje_empresa / 100);

            } else {

                $porcentaje_empresa = 0;
                $monto_empresa = 0;
            }
            $dinero_entregar = max(
                0,
                floatval($data["monto_real"]) - $monto_empresa
            );

            $query = $conn->prepare("UPDATE caja_chica SET 
            estado=0,
            saldo_final=:saldo_final,
            saldo_real=:saldo_real,
            fecha_fin=:fecha_fin,
            porcentaje_empresa=:porcentaje_empresa,
            monto_empresa=:monto_empresa,
            dinero_entregar = :dinero_entregar
            WHERE id_caja_chica=:id_caja_chica");
            $query->bindParam(":id_caja_chica", $data["id_caja"]);
            $query->bindParam(":saldo_final", $monto_final);
            $query->bindParam(":saldo_real", $data["monto_real"]);
            $query->bindParam(":fecha_fin", $fecha_fin);
            $query->bindParam(":porcentaje_empresa", $porcentaje_empresa);
            $query->bindParam(":monto_empresa", $monto_empresa);
            $query->bindParam(":dinero_entregar", $dinero_entregar);

            // Si hay egresos, insertarlos
            if (!empty($data['egresos']) && $data['egresos'] !== '[]') {
                $egresos = json_decode($data['egresos'], true);

                foreach ($egresos as $egreso) {
                    $query2 = $conn->prepare("
                        INSERT INTO egreso_caja
                        (cod_caja_chica, tp_comprobante, serie, correlativo, monto, concepto) 
                        VALUES 
                        (:cod_caja_chica, :tp_comprobante, :serie, :correlativo, :monto, :concepto)
                    ");

                    $query2->bindParam(":cod_caja_chica", $data['id_caja']);
                    $query2->bindParam(":tp_comprobante", $egreso[0]);
                    $query2->bindParam(":serie", $egreso[1]);
                    $query2->bindParam(":correlativo", $egreso[2]);
                    $query2->bindParam(":monto", $egreso[3]);
                    $query2->bindParam(":concepto", $egreso[4]);

                    if (!$query2->execute()) {
                        throw new Exception("Error al insertar egreso de egreso");
                    }
                }
            }

            if ($query->execute()) {
                $conn->commit();
                return [
                    'success' => true,
                    "message" => [
                        "message" => "¡Caja cerrado con éxito!",
                        "links" => [
                            ['nombre' => 'Imprimir A4', 'link' => URL . "caja_chica/impresion/cierre/" . $data["id_caja"]],
                            ['nombre' => 'Imprimir Ticket', 'link' => URL . "caja_chica/impresion/cierre_ticket/" . $data["id_caja"]],
                        ]
                    ]
                ];
            } else {
                $conn->rollback();
                return ["success" => false, "message" => ["message" => "Ha ocurrido un error intentelo más tarde"]];
            }
        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollback();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ["success" => false, "message" => ["message" => "Ha ocurrido un error intentelo más tarde"]];
                    break;
                default:
                    return ["success" => false, "message" => ["message" => "Ha ocurrido un error intenteloX más tarde"]];
                    break;
            }
        }
    }

    public function getDataReporteCierre($id_caja_chica)
    {
        if (!is_numeric($id_caja_chica) || $id_caja_chica <= 0) {
            throw new Exception("ID de caja chica inválido");
        }

        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC) ?: ['razon_social' => 'N/A', 'num_docu' => 'N/A'];

        $query = $this->db->connect()->prepare("
        SELECT
            vd.nombres AS vendedor_nombres,
            vd.apellidos AS vendedor_apellidos,
            cj.estado AS caja_estado,
            cj.fecha_inicio AS caja_fecha_inicio,
            cj.fecha_fin AS caja_fecha_fin,
            cj.saldo_inicial AS caja_saldo_inicial,
            cj.saldo_final AS caja_saldo_final,
            cj.monto_empresa AS caja_monto_empresa,
            t.nombre AS terminal
        FROM caja_chica cj
        LEFT JOIN usuario vd ON vd.id_usuario=cj.id_usuario
        LEFT JOIN terminal t ON t.id_terminal=vd.id_terminal
        WHERE id_caja_chica=:id_caja_chica
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC) ?: [
            'vendedor_nombres' => '',
            'vendedor_apellidos' => '',
            'caja_estado' => null,
            'caja_fecha_inicio' => null,
            'caja_fecha_fin' => null,
            'caja_saldo_inicial' => 0,
            'caja_saldo_final' => 0,
            'terminal' => 'N/A'
        ];

        $query = $this->db->connect()->prepare("
        SELECT 
            m_p.descripcion AS medio_pago,
            SUM(CASE WHEN v.estado NOT IN ('ANULADO', 'CANCELADO') THEN v.total ELSE 0 END) AS total
        FROM venta v
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND v.id_caja_chica=:id_caja_chica 
        GROUP BY m_p.descripcion
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        // Comprobantes a credito
        $query = $this->db->connect()->prepare("
        SELECT 
            SUM(CASE WHEN v.estado NOT IN ('ANULADO', 'CANCELADO') THEN v.total ELSE 0 END) AS total
        FROM venta v
        WHERE v.id_caja_chica=:id_caja_chica AND v.id_forma_pago = 2
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $total_creditos = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
        SELECT 
             SUM(CASE WHEN v.id_tp_comprobante = 1 THEN v.total ELSE 0 END) AS total_facturas,
             SUM(CASE WHEN v.id_tp_comprobante = 2 THEN v.total ELSE 0 END) AS total_notas,
             SUM(CASE WHEN v.id_tp_comprobante = 3 THEN v.total ELSE 0 END) AS total_boletas,
             SUM(CASE WHEN dt.id_tp_servicio = 1 THEN v.porcentaje_venta ELSE 0 END) AS total_p_pasaje,
             SUM(CASE WHEN dt.id_tp_servicio = 2 THEN v.porcentaje_venta ELSE 0 END) AS total_p_encomienda
        FROM venta v
        LEFT JOIN dt_venta dt ON dt.id_venta = v.id_venta
        WHERE 
           v.estado NOT IN ('ANULADO', 'CANCELADO')
           AND v.id_caja_chica = :id_caja_chica
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();

        $totales_tpc = $query->fetch(PDO::FETCH_ASSOC) ?: [
            'total_facturas' => 0,
            'total_notas' => 0,
            'total_boletas' => 0,
            'total_p_pasaje' => 0,
            'total_p_encomienda' => 0,
        ];

        // Extraer el total de las cuotas pagada
        $query = $this->db->connect()->prepare("
        SELECT 
        SUM(importe) AS total_cuotas
        FROM cuota
        WHERE id_caja_chica = :id_caja_chica
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $total_cuotas = $query->fetchColumn();

        $query = $this->db->connect()->prepare("
        SELECT
            tp_s.descripcion AS tp_servicio,
            tp_c.descripcion AS tp_comprobante,
            v.id_tp_comprobante,
            v.serie AS venta_serie,
            v.correlativo AS venta_correlativo,
            v.fecha_emision,
            c.nombres AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.num_docu AS cliente_num_docu,
            v.op_gravada AS importe,
            v.total AS total
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND v.id_caja_chica=:id_caja_chica
    ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $detalle = $query->fetchAll(PDO::FETCH_ASSOC);

        // Egresos
        $query = $this->db->connect()->prepare("
        SELECT
            e.concepto,
            e.monto,
            e.tp_comprobante,
            e.serie
        FROM egreso_caja e
        WHERE e.cod_caja_chica = :id_caja_chica
    ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $egresos = array_map(function ($egreso) {
            return [
                'tp_comprobante' => $egreso['tp_comprobante'] ?? 'N/A',
                'serie' => $egreso['serie'] ?? 'N/A',
                'concepto' => $egreso['concepto'] ?? 'N/A',
                'monto' => $egreso['monto'] ?? 0.00
            ];
        }, $query->fetchAll(PDO::FETCH_ASSOC));

        // Ajustar saldo_final restando los egresos
        $total_egresos = array_sum(array_column($egresos, 'monto'));
        $header_detalle['caja_saldo_final'] = ($header_detalle['caja_saldo_final'] ?? 0) - $total_egresos;

        return array(
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "HEADER_DETALLE" => $header_detalle,
                "TOTALES_TPD" => $totales_tpc
            ],
            "MEDIO_PAGOS" => $medio_pagos,
            "CREDITOS" => $total_creditos,
            "DETALLE" => $detalle,
            "EGRESOS" => $egresos,
            "TOTAL_CUOTAS" => $total_cuotas
        );
    }

    public function getDataReporteLiquidacionTerminal($id_terminal, $fecha_inicio)
    {
        $fecha_inicio = implode("-", explode("_", $fecha_inicio));

        // Obteniendo data de caja chica
        $query = $this->db->connect()->prepare("
        SELECT
            cj.fecha_inicio
        FROM venta v
        LEFT JOIN caja_chica cj ON cj.id_caja_chica=v.id_caja_chica
        WHERE id_terminal=:id_terminal AND date_format(cj.fecha_inicio,'%Y-%m-%d')=:fecha_inicio");
        $query->bindParam(":id_terminal", $id_terminal);
        $query->bindParam(":fecha_inicio", $fecha_inicio);
        $query->execute();
        $caja_chica = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
        SELECT
            t.id_terminal,
            t.nombre AS terminal_origen
        FROM terminal t
        WHERE id_terminal=:id_terminal
        ");
        $query->bindParam(":id_terminal", $id_terminal);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        $caja_chica ? $header_detalle["fecha_inicio_cajaChica"] = $caja_chica["fecha_inicio"] : null;

        // Obteniendo la cantidad de pagos que se realizó
        $query = $this->db->connect()->prepare("SELECT 
            m_p.descripcion AS medio_pago,
            COUNT(m_p.descripcion) AS amount,
            vh.num_piso AS num_piso
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN caja_chica cj ON cj.id_caja_chica=v.id_caja_chica
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo 
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND v.id_terminal=:id_terminal AND date_format(cj.fecha_inicio,'%Y-%m-%d')=:fecha_inicio GROUP BY m_p.descripcion");
        $query->bindParam(":id_terminal", $id_terminal);
        $query->bindParam(":fecha_inicio", $fecha_inicio);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_generalPasaje = array();
        $detalle_generalEncomienda = array();
        for ($i = 0; $i < count($medio_pagos); $i++) {
            $pisos = array();
            $cantidad = $medio_pagos[$i]["num_piso"] ? $medio_pagos[$i]["num_piso"] : 1;
            for ($x = 0; $x < $cantidad; $x++) {
                // PASAJE
                $piso = $x + 1;
                $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.num_asiento AS num_asiento,
                    c.nombres AS cliente_nombres,
                    c.apellidos AS cliente_apellidos,
                    c.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.total AS importe,
                    dt_v.piso AS num_piso,
                    dt_v.estado_asiento
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN caja_chica cj ON cj.id_caja_chica=v.id_caja_chica
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                WHERE v.id_terminal=:id_terminal AND v.estado NOT IN ('ANULADO', 'CANCELADO') AND tp_s.descripcion='PASAJE' AND dt_v.piso=:piso AND m_p.descripcion=:medio_pago AND date_format(cj.fecha_inicio,'%Y-%m-%d')=:fecha_inicio
                ");
                $query->bindParam(":id_terminal", $id_terminal);
                $query->bindParam(":fecha_inicio", $fecha_inicio);
                $query->bindParam(":piso", $piso);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->execute();
                $ventas_porPiso = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($ventas_porPiso) {
                    array_push($pisos, $ventas_porPiso);
                }

                // ENCOMIENDA
                $query = $this->db->connect()->prepare("
                SELECT
                    c_r.nombres AS cliente_nombres,
                    c_r.apellidos AS cliente_apellidos,
                    c_r.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.total AS importe,
                    e.estado AS estado_encomienda,
                    e.pago AS pago_encomienda
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
                LEFT JOIN usuario c_r ON c_r.id_usuario=e.id_remitente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=e.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN caja_chica cj ON cj.id_caja_chica=v.id_caja_chica
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                WHERE v.id_terminal=:id_terminal AND v.estado NOT IN ('ANULADO', 'CANCELADO') AND tp_s.descripcion='ENCOMIENDA' AND m_p.descripcion=:medio_pago AND date_format(cj.fecha_inicio,'%Y-%m-%d')=:fecha_inicio
                ");
                $query->bindParam(":id_terminal", $id_terminal);
                $query->bindParam(":fecha_inicio", $fecha_inicio);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->execute();
                $ventas_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);
            }
            array_push(
                $detalle_generalPasaje,
                [
                    "medio_pago" => $medio_pagos[$i]["medio_pago"],
                    "pisos" => $pisos
                ]
            );
            array_push(
                $detalle_generalEncomienda,
                [
                    "medio_pago" => $medio_pagos[$i]["medio_pago"],
                    "encomiendas" => $ventas_encomiendas
                ]
            );
        }
        return array(
            "HEADER" => [
                "HEADER_DETALLE" => $header_detalle,
            ],
            "DETALLE" => [
                "PASAJE" => $detalle_generalPasaje,
                "ENCOMIENDA" => $detalle_generalEncomienda,
            ]
        );
    }

    public function getDataReporteLiquidacionUsuario($id_caja_chica)
    {
        $query = $this->db->connect()->prepare("
        SELECT
            u.nombres AS usuario_nombres,
            u.apellidos AS usuario_apellidos,
            t.nombre AS terminal
        FROM caja_chica cj
        LEFT JOIN usuario u ON u.id_usuario=cj.id_usuario
        LEFT JOIN terminal t ON t.id_terminal=u.id_terminal
        WHERE cj.id_caja_chica=:id_caja_chica
        ");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        // Obteniendo la cantidad de pagos que se realizó
        $query = $this->db->connect()->prepare("SELECT 
            m_p.descripcion AS medio_pago,
            COUNT(m_p.descripcion) AS amount,
            vh.num_piso AS num_piso
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo 
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND v.id_caja_chica=:id_caja_chica GROUP BY m_p.descripcion");
        $query->bindParam(":id_caja_chica", $id_caja_chica);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_generalPasaje = array();
        $detalle_generalEncomienda = array();
        for ($i = 0; $i < count($medio_pagos); $i++) {
            $pisos = array();
            $cantidad = $medio_pagos[$i]["num_piso"] ? $medio_pagos[$i]["num_piso"] : 1;
            for ($x = 0; $x < $cantidad; $x++) {
                // PASAJE
                $piso = $x + 1;
                $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.num_asiento AS num_asiento,
                    c.nombres AS cliente_nombres,
                    c.apellidos AS cliente_apellidos,
                    c.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.total AS importe,
                    dt_v.piso AS num_piso,
                    dt_v.estado_asiento
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                WHERE v.id_caja_chica=:id_caja_chica AND v.estado NOT IN ('ANULADO', 'CANCELADO') AND tp_s.descripcion='PASAJE' AND dt_v.piso=:piso AND m_p.descripcion=:medio_pago
                ");
                $query->bindParam(":id_caja_chica", $id_caja_chica);
                $query->bindParam(":piso", $piso);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->execute();
                $ventas_porPiso = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($ventas_porPiso) {
                    array_push($pisos, $ventas_porPiso);
                }

                // ENCOMIENDA
                $query = $this->db->connect()->prepare("
                SELECT
                    c_r.nombres AS cliente_nombres,
                    c_r.apellidos AS cliente_apellidos,
                    c_r.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.total AS importe,
                    e.estado AS estado_encomienda,
                    e.pago AS pago_encomienda
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
                LEFT JOIN usuario c_r ON c_r.id_usuario=e.id_remitente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=e.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                WHERE v.id_caja_chica=:id_caja_chica AND v.estado NOT IN ('ANULADO', 'CANCELADO') AND tp_s.descripcion='ENCOMIENDA' AND m_p.descripcion=:medio_pago
                ");
                $query->bindParam(":id_caja_chica", $id_caja_chica);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->execute();
                $ventas_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($pisos) {
                array_push(
                    $detalle_generalPasaje,
                    [
                        "medio_pago" => $medio_pagos[$i]["medio_pago"],
                        "pisos" => $pisos
                    ]
                );
            }
            if ($ventas_encomiendas) {
                array_push(
                    $detalle_generalEncomienda,
                    [
                        "medio_pago" => $medio_pagos[$i]["medio_pago"],
                        "encomiendas" => $ventas_encomiendas
                    ]
                );
            }
        }
        return array(
            "HEADER" => [
                "HEADER_DETALLE" => $header_detalle,
            ],
            "DETALLE" => [
                "PASAJE" => $detalle_generalPasaje,
                "ENCOMIENDA" => $detalle_generalEncomienda,
            ]
        );
    }

    public function consultarCajasLiquidacionTerminal($data)
    {
        $query = $this->db->connect()->prepare("
        SELECT
            cj.estado AS estado
        FROM caja_chica cj
        LEFT JOIN usuario u ON u.id_usuario=cj.id_usuario
        WHERE u.id_terminal=:id_terminal AND date_format(cj.fecha_inicio, '%Y-%m-%d')=:fecha_inicio
        ");
        $query->bindParam(":id_terminal", $data["terminal"]);
        $query->bindParam(":fecha_inicio", $data["fecha_inicio_reporte"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($reply); $i++) {
            if ($reply[$i]["estado"] == 1) {
                return array("success" => false, "message" => "No se puede, liquidar por que existe cajas abiertas");
            }
        }
        return array("success" => true, "message" => null);
    }

    public function buscar_cajas_cerradas_reporte($data)
    {
        try {
            $conn = $this->db->connect();

            $fecha_cierre = $data['fecha_cierre'];
            $id_terminal = intval($data['id_terminal'] ?? 0);
            $id_usuario = intval($data['id_usuario'] ?? 0);

            $sql = "
            SELECT 
                cc.id_caja_chica,
                cc.fecha_inicio,
                cc.fecha_fin,
                cc.saldo_inicial,
                cc.saldo_final,
                cc.saldo_real,
                cc.porcentaje_empresa,
                cc.monto_empresa,
                cc.dinero_entregar,

                COALESCE(SUM(v.total), 0) AS total_ventas,

                t.nombre AS terminal,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario
            FROM caja_chica cc
            LEFT JOIN venta v 
                ON v.id_caja_chica = cc.id_caja_chica
                AND v.estado NOT IN ('ANULADO', 'CANCELADO')
            LEFT JOIN usuario u 
                ON u.id_usuario = cc.id_usuario
            LEFT JOIN terminal t 
                ON t.id_terminal = u.id_terminal
            WHERE 
                cc.estado = 0
                AND DATE(cc.fecha_fin) = :fecha_cierre
        ";

            if ($id_terminal > 0) {
                $sql .= " AND u.id_terminal = :id_terminal ";
            }

            if ($id_usuario > 0) {
                $sql .= " AND cc.id_usuario = :id_usuario ";
            }

            $sql .= "
            GROUP BY 
                cc.id_caja_chica,
                cc.fecha_inicio,
                cc.fecha_fin,
                cc.saldo_inicial,
                cc.saldo_final,
                cc.saldo_real,
                cc.porcentaje_empresa,
                cc.monto_empresa,
                cc.dinero_entregar,
                t.nombre,
                u.nombres,
                u.apellidos
            ORDER BY cc.fecha_fin ASC
        ";

            $query = $conn->prepare($sql);
            $query->bindParam(":fecha_cierre", $fecha_cierre);

            if ($id_terminal > 0) {
                $query->bindParam(":id_terminal", $id_terminal, PDO::PARAM_INT);
            }

            if ($id_usuario > 0) {
                $query->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            }

            $query->execute();
            $cajas = $query->fetchAll(PDO::FETCH_ASSOC);

            if (!$cajas) {
                return [
                    "success" => false,
                    "message" => "No se encontraron cajas cerradas con los filtros seleccionados."
                ];
            }

            $total_ventas = 0;
            $monto_empresa = 0;
            $dinero_entregar = 0;
            $saldo_final = 0;

            foreach ($cajas as $caja) {
                $total_ventas += floatval($caja["total_ventas"]);
                $monto_empresa += floatval($caja["monto_empresa"]);
                $dinero_entregar += floatval($caja["dinero_entregar"]);
                $saldo_final += floatval($caja["saldo_final"]);
            }

            return [
                "success" => true,
                "total_cajas" => count($cajas),
                "cajas" => $cajas,
                "totales" => [
                    "total_ventas" => $total_ventas,
                    "monto_empresa" => $monto_empresa,
                    "dinero_entregar" => $dinero_entregar,
                    "saldo_final" => $saldo_final
                ]
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error al buscar cajas cerradas."
            ];
        }
    }

    public function getDataReporteCajaCerradas($fecha, $id_terminal = 0, $id_usuario = 0)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("SELECT * FROM empresa LIMIT 1");
            $query->execute();

            $empresa = $query->fetch(PDO::FETCH_ASSOC) ?: [
                "razon_social" => "N/A",
                "num_docu" => "N/A"
            ];

            $id_terminal = intval($id_terminal);
            $id_usuario = intval($id_usuario);

            $sql = "
            SELECT 
                cc.id_caja_chica,
                cc.fecha_inicio,
                cc.fecha_fin,
                cc.saldo_inicial,
                cc.saldo_final,
                cc.saldo_real,
                cc.porcentaje_empresa,
                cc.monto_empresa,
                cc.dinero_entregar,

                COALESCE(SUM(v.total), 0) AS total_ventas,

                t.id_terminal,
                t.nombre AS terminal,

                u.id_usuario,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario
            FROM caja_chica cc
            LEFT JOIN venta v 
                ON v.id_caja_chica = cc.id_caja_chica
                AND v.estado NOT IN ('ANULADO', 'CANCELADO')
            LEFT JOIN usuario u 
                ON u.id_usuario = cc.id_usuario
            LEFT JOIN terminal t 
                ON t.id_terminal = u.id_terminal
            WHERE 
                cc.estado = 0
                AND DATE(cc.fecha_fin) = :fecha
        ";

            if ($id_terminal > 0) {
                $sql .= " AND u.id_terminal = :id_terminal ";
            }

            if ($id_usuario > 0) {
                $sql .= " AND cc.id_usuario = :id_usuario ";
            }

            $sql .= "
            GROUP BY 
                cc.id_caja_chica,
                cc.fecha_inicio,
                cc.fecha_fin,
                cc.saldo_inicial,
                cc.saldo_final,
                cc.saldo_real,
                cc.dinero_entregar,
                cc.porcentaje_empresa,
                cc.monto_empresa,
                t.id_terminal,
                t.nombre,
                u.id_usuario,
                u.nombres,
                u.apellidos
            ORDER BY 
                t.nombre ASC,
                u.nombres ASC,
                cc.fecha_fin ASC
        ";

            $query = $conn->prepare($sql);
            $query->bindParam(":fecha", $fecha);

            if ($id_terminal > 0) {
                $query->bindParam(":id_terminal", $id_terminal, PDO::PARAM_INT);
            }

            if ($id_usuario > 0) {
                $query->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            }

            $query->execute();
            $cajas = $query->fetchAll(PDO::FETCH_ASSOC);

            if (!$cajas) {
                return [
                    "success" => false,
                    "message" => "No se encontraron cajas cerradas con los filtros seleccionados."
                ];
            }

            $totales = [
                "total_ventas" => 0,
                "monto_empresa" => 0,
                "dinero_entregar" => 0,
                "saldo_final" => 0,
                "saldo_real" => 0,
                "saldo_inicial" => 0
            ];

            $terminales = [];
            $usuarios = [];

            foreach ($cajas as $caja) {
                $idTerminal = $caja["id_terminal"] ?? 0;
                $idUsuario = $caja["id_usuario"] ?? 0;

                $totalVentas = floatval($caja["total_ventas"]);
                $montoEmpresa = floatval($caja["monto_empresa"]);
                $dineroEntregar = floatval($caja["dinero_entregar"]);
                $saldoFinal = floatval($caja["saldo_final"]);
                $saldoReal = floatval($caja["saldo_real"]);
                $saldoInicial = floatval($caja["saldo_inicial"]);

                $totales["total_ventas"] += $totalVentas;
                $totales["monto_empresa"] += $montoEmpresa;
                $totales["dinero_entregar"] += $dineroEntregar;
                $totales["saldo_final"] += $saldoFinal;
                $totales["saldo_real"] += $saldoReal;
                $totales["saldo_inicial"] += $saldoInicial;

                if (!isset($terminales[$idTerminal])) {
                    $terminales[$idTerminal] = [
                        "id_terminal" => $idTerminal,
                        "terminal" => $caja["terminal"] ?? "SIN TERMINAL",
                        "total_cajas" => 0,
                        "total_ventas" => 0,
                        "monto_empresa" => 0,
                        "dinero_entregar" => 0,
                        "saldo_final" => 0,
                        "saldo_real" => 0,
                        "saldo_inicial" => 0
                    ];
                }

                $terminales[$idTerminal]["total_cajas"]++;
                $terminales[$idTerminal]["total_ventas"] += $totalVentas;
                $terminales[$idTerminal]["monto_empresa"] += $montoEmpresa;
                $terminales[$idTerminal]["dinero_entregar"] += $dineroEntregar;
                $terminales[$idTerminal]["saldo_final"] += $saldoFinal;
                $terminales[$idTerminal]["saldo_real"] += $saldoReal;
                $terminales[$idTerminal]["saldo_inicial"] += $saldoInicial;

                if (!isset($usuarios[$idUsuario])) {
                    $usuarios[$idUsuario] = [
                        "id_usuario" => $idUsuario,
                        "usuario" => $caja["usuario"] ?? "SIN USUARIO",
                        "terminal" => $caja["terminal"] ?? "SIN TERMINAL",
                        "total_cajas" => 0,
                        "total_ventas" => 0,
                        "monto_empresa" => 0,
                        "dinero_entregar" => 0,
                        "saldo_final" => 0,
                        "saldo_real" => 0,
                        "saldo_inicial" => 0
                    ];
                }

                $usuarios[$idUsuario]["total_cajas"]++;
                $usuarios[$idUsuario]["total_ventas"] += $totalVentas;
                $usuarios[$idUsuario]["monto_empresa"] += $montoEmpresa;
                $usuarios[$idUsuario]["dinero_entregar"] += $dineroEntregar;
                $usuarios[$idUsuario]["saldo_final"] += $saldoFinal;
                $usuarios[$idUsuario]["saldo_real"] += $saldoReal;
                $usuarios[$idUsuario]["saldo_inicial"] += $saldoInicial;
            }

            return [
                "success" => true,
                "empresa" => $empresa,
                "fecha" => $fecha,
                "modo" => $id_usuario > 0 ? "detalle_usuario" : "resumen_terminal",
                "filtros" => [
                    "id_terminal" => $id_terminal,
                    "id_usuario" => $id_usuario
                ],
                "total_cajas" => count($cajas),
                "cajas" => $cajas,
                "terminales" => array_values($terminales),
                "usuarios" => array_values($usuarios),
                "totales" => $totales
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error al generar el reporte de cajas cerradas."
            ];
        }
    }
}
