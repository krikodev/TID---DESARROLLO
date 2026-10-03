<?php

class PersonalModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        $search = normalizar_estado($data['search']['value'] ?? '');

        $terminal_personal = $data['terminal_personal'] ?? '';
        $tp_usuario_personal = $data['tp_usuario_personal'] ?? '';
        $genero_personal = $data['genero_personal'] ?? '';
        $estado_personal = $data['estado_personal'] ?? '';

        $extraWhere = "";
        $extraParams = [];

        if ($search === '0' || $search === '1') {
            $searchColumns = ["u.estado"];
            $data['search']['value'] = $search;
        } else {
            $searchColumns = ["t.nombre", "u.nombres", "u.apellidos", "CONCAT(u.nombres, ' ', u.apellidos)", "u.num_docu", "u.fecha_nacimiento", "u.estado_civil", "u.genero", "u.celular", "u.email", "u.direccion", "u.ubigeo", "tp_u.descripcion"];
            $data['search']['value'] = $search;
        }

        if ($terminal_personal !== '') {
            $extraWhere .= " AND u.id_terminal = ?";
            $extraParams[] = $terminal_personal;
        }
        if ($tp_usuario_personal !== '') {
            $extraWhere .= " AND u.id_tp_usuario = ?";
            $extraParams[] = $tp_usuario_personal;
        }
        if ($genero_personal !== '') {
            $extraWhere .= " AND u.genero = ?";
            $extraParams[] = $genero_personal;
        }
        if ($estado_personal !== '') {
            $extraWhere .= " AND u.estado = ?";
            $extraParams[] = $estado_personal;
        }

        try {
            $selectFields = "u.id_usuario,
            u.id_terminal,
            t.nombre AS terminal,
            t.tipo AS terminal_tipo,
            u.id_tp_docu,
            tp_d.descripcion AS tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            u.fecha_nacimiento,
            u.estado_civil,
            u.genero,
            u.celular,
            u.email,
            u.contrasena,
            u.direccion,
            u.ubigeo,
            ub.depa AS ubi_depa,
            ub.provi AS ubi_provi,
            ub.distri AS ubi_distri,
            u.id_tp_usuario,
            u.nacionalidad,
            tp_u.descripcion AS tp_usuario,
            tp_u.tipo AS tp_usuario_tipo,
            u.estado";
            $baseQuery = "FROM usuario u
            INNER JOIN terminal t ON t.id_terminal=u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu=u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo=u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            WHERE tp_u.tipo IN ('INTERNO', 'EXTERNO') 
            AND tp_u.descripcion IN ('PERSONAL', 'VENDEDOR', 'TERRAMOZA', 'ADMINISTRADOR', 'AYUDANTE', 'COMISIONISTA 1', 'COMISIONISTA 2') 
             AND u.num_docu != '1111'";
            $orderBy = "id_usuario DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy,
                $extraWhere,
                $extraParams
            );

            foreach ($result['data'] as &$row) {
                $row['contrasena'] = $this->security->decryption($row['contrasena']);
            }

            return $result;
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

            $conn = $this->db->connect();
            //Consultando si ya creo al usuario con este tipo de usuario
            $query = $conn->prepare("
            SELECT id_usuario FROM usuario WHERE num_docu = :num_docu AND id_tp_usuario = :id_tp_usuario 
            ");
            $query->bindParam(':id_tp_usuario', $data['tp_usuario']);
            $query->bindParam(':num_docu', $data['num_docu']);
            $query->execute();
            $id_usu = $query->fetch(PDO::FETCH_ASSOC);
            if (isset($id_usu['id_usuario'])) {
                return array('success' => false, 'message' => 'El usuario ya existe y esta registrado');
            }

            $conn->beginTransaction();

            $pass = $data["pass"] ? $this->security->encryption($data["pass"]) : $data["pass"];
            $query = $conn->prepare("INSERT INTO usuario (
                id_terminal, id_tp_docu, num_docu, nombres, apellidos, fecha_nacimiento, estado_civil,
                genero, direccion, ubigeo, celular, email, contrasena, nacionalidad,
                id_tp_usuario, estado) 
                VALUES(
                :id_terminal, :id_tp_docu, :num_docu, :nombres, :apellidos, :fecha_nacimiento, :estado_civil,
                :genero, :direccion, :ubigeo, :celular, :email, :contrasena, :nacionalidad,
                :id_tp_usuario, :estado)");
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["f_naci"]);
            $query->bindParam(':estado_civil', $data["estado_civil"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':contrasena', $pass);
            $query->bindParam(':nacionalidad', $data["nacionalidad"]);
            $query->bindParam(':id_tp_usuario', $data["tp_usuario"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();

            $query = $conn->prepare("SELECT id_usuario FROM usuario WHERE num_docu=:num_docu");
            $query->bindParam(":num_docu", $data["num_docu"]);
            $query->execute();
            $data["id_usuario"] = $query->fetch(PDO::FETCH_ASSOC)["id_usuario"];

            $registrar_permisos = $this->register_permisos($data, $conn);
            if (!$registrar_permisos['success']) {
                throw new Exception(
                    'No se crearon permisos: ' . $registrar_permisos['message']
                );
            }
            $conn->commit();

            return array('success' => true, "message" => "Registro creado con éxito");
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e);
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $pass = $data["pass"] ? $this->security->encryption($data["pass"]) : null;
            $query = $conn->prepare("UPDATE usuario SET 
            id_terminal=:id_terminal,
            id_tp_docu=:id_tp_docu,
            num_docu=:num_docu,
            nombres=:nombres,
            apellidos=:apellidos,
            fecha_nacimiento=:fecha_nacimiento,
            estado_civil=:estado_civil,
            genero=:genero,
            direccion=:direccion,
            ubigeo=:ubigeo,
            celular=:celular,
            email=:email,
            contrasena=:contrasena,
            nacionalidad=:nacionalidad,
            id_tp_usuario=:id_tp_usuario,
            estado=:estado
            WHERE id_usuario=:id_usuario");
            $query->bindParam(':id_usuario', $data["id_personal"]);
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["f_naci"]);
            $query->bindParam(':estado_civil', $data["estado_civil"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':contrasena', $pass);
            $query->bindParam(':nacionalidad', $data["nacionalidad"]);
            $query->bindParam(':id_tp_usuario', $data["tp_usuario"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            $data["id_usuario"] = $data["id_personal"];
            $registrar_permisos = $this->register_permisos($data, $conn);
            if (!$registrar_permisos['success']) {
                throw new Exception(
                    'No se crearon permisos: ' . $registrar_permisos['message']
                );
            }
            $conn->commit();

            return array('success' => true, "message" => "Registro modificado con éxito");
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde." . $e);
                    break;
            }
        }
    }

    public function register_permisos($data, $conn = null)
    {
        try {
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            $query = $conn->prepare("SELECT COUNT(*) AS count_regis FROM permiso WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_usuario"]);
            $query->execute();

            if (empty($data['id_usuario'])) {
                throw new Exception('No tiene un usuario a quien asignar los permisos');
            }

            if ($query->fetch(PDO::FETCH_ASSOC)["count_regis"] == 0) {
                $query = $conn->prepare("INSERT INTO permiso (
            id_usuario, p_flete, p_pasaje, p_encomienda, p_listado_comprobante, p_listado_nota_venta, p_programacion, p_programacion_salida,
            p_vehiculo, p_caja_chica, p_comprobantesreport, p_encomiendasreport, p_viaje_report, p_notas, p_pasajero, p_personal, p_proveedor, p_conductor, p_terminal, p_almacen,
            p_empresa, p_producto_encomienda, p_tp_servicio_pasaje, p_serie, p_medio_pago, p_reportes, p_config,
            p_anular_comprobante, p_anular_notaventa, p_posponer_pasaje, p_cambiar_asiento, p_enviar_resumen, p_facturador, p_pago_bloque, 
            p_cupon, p_guia_transportista, p_contra_maestra, p_desbloquear_reservado, permiso_m_precio, p_cotizacion, p_valorizacion)
            VALUES(
            :id_usuario, :p_flete, :p_pasaje, :p_encomienda, :p_listado_comprobante, :p_listado_nota_venta, :p_programacion, :p_programacion_salida,
            :p_vehiculo, :p_caja_chica, :p_comprobantesreport, :p_encomiendasreport, :p_viaje_report, :p_notas, :p_pasajero, :p_personal, :p_proveedor, :p_conductor, :p_terminal, :p_almacen,
            :p_empresa, :p_producto_encomienda, :p_tp_servicio_pasaje, :p_serie, :p_medio_pago, 0, :p_config,
            :p_anular_comprobante, :p_anular_notaventa, :p_posponer_pasaje, :p_cambiar_asiento,  :p_enviar_resumen, :p_facturador, :p_pago_bloque, 
            :p_cupon, :p_guia_transportista, :p_contra_maestra, :p_desbloquear_reservado, :permiso_m_precio, :p_cotizacion, :p_valorizacion)");
            } else {
                $query = $conn->prepare("UPDATE permiso SET
                p_flete=:p_flete,
                p_pasaje=:p_pasaje,
                p_encomienda=:p_encomienda,
                p_listado_comprobante=:p_listado_comprobante,
                p_listado_nota_venta=:p_listado_nota_venta,
                p_programacion=:p_programacion,
                p_programacion_salida=:p_programacion_salida,
                p_vehiculo=:p_vehiculo,
                p_comprobantesreport=:p_comprobantesreport,
                p_encomiendasreport=:p_encomiendasreport,
                p_viaje_report=:p_viaje_report,
                p_notas=:p_notas,
                p_caja_chica=:p_caja_chica,
                p_pasajero=:p_pasajero,
                p_personal=:p_personal,
                p_proveedor=:p_proveedor,
                p_conductor=:p_conductor,
                p_terminal=:p_terminal,
                p_almacen=:p_almacen,
                p_empresa=:p_empresa,
                p_producto_encomienda=:p_producto_encomienda,
                p_tp_servicio_pasaje=:p_tp_servicio_pasaje,
                p_serie=:p_serie,
                p_medio_pago=:p_medio_pago,
                p_reportes=0,
                p_config=:p_config,
                p_anular_comprobante=:p_anular_comprobante,
                p_anular_notaventa=:p_anular_notaventa,
                p_posponer_pasaje=:p_posponer_pasaje,
                p_cambiar_asiento=:p_cambiar_asiento,
                p_enviar_resumen=:p_enviar_resumen,
                p_facturador=:p_facturador,
                p_pago_bloque=:p_pago_bloque,
                p_cupon=:p_cupon,
                p_guia_transportista=:p_guia_transportista,
                p_contra_maestra=:p_contra_maestra,
                p_desbloquear_reservado=:p_desbloquear_reservado,
                permiso_m_precio=:permiso_m_precio,
                p_cotizacion=:p_cotizacion,
                p_valorizacion=:p_valorizacion
               WHERE id_usuario=:id_usuario");
            }
            $query->bindValue(":id_usuario", $data["id_usuario"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_flete", $data["p_flete"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_pasaje", $data["p_pasaje"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_encomienda", $data["p_encomienda"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_listado_comprobante", $data["p_listado_comprobante"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_listado_nota_venta", $data["p_listado_nota_venta"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_programacion", $data["p_programacion"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_programacion_salida", $data["p_programacion_salida"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_vehiculo", $data["p_vehiculo"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_caja_chica", $data["p_caja_chica"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_comprobantesreport", $data["p_comprobantesreport"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_encomiendasreport", $data["p_encomiendasreport"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_viaje_report", $data["p_viaje_report"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_notas", $data["p_notas"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_pasajero", $data["p_pasajero"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_personal", $data["p_personal"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_proveedor", $data["p_proveedor"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_conductor", $data["p_conductor"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_terminal", $data["p_terminal"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_almacen", $data["p_almacen"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_empresa", $data["p_empresa"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_producto_encomienda", $data["p_producto_encomienda"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_tp_servicio_pasaje", $data["p_tp_servicio_pasaje"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_serie", $data["p_serie"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_medio_pago", $data["p_medio_pago"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_config", $data["p_config"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_anular_comprobante", $data["p_anular_comprobante"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_anular_notaventa", $data["p_anular_notaventa"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_posponer_pasaje", $data["p_posponer_pasaje"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_cambiar_asiento", $data["p_cambiar_asiento"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_enviar_resumen", $data["p_enviar_resumen"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_facturador", $data["p_facturador"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_pago_bloque", $data["p_pago_bloque"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_cupon", $data["p_cupon"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_guia_transportista", $data["p_guia_transportista"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_contra_maestra", $data["p_contra_maestra"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_desbloquear_reservado", $data["p_desbloquear_reservado"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":permiso_m_precio", $data["permiso_m_precio"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_cotizacion", $data["p_cotizacion"] ?? 0, PDO::PARAM_INT);
            $query->bindValue(":p_valorizacion", $data["p_valorizacion"] ?? 0, PDO::PARAM_INT);
            if ($query->execute()) {
                return ['success' => true, 'message' => 'Se registraron los permisos del usuario'];
            } else {
                return ['success' => false, 'message' => 'No se pudo registrar los permisos'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM permiso WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_personal"]);
            $query->execute();

            $query = $this->db->connect()->prepare("DELETE FROM usuario WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_personal"]);
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

    public function get_otrosDatos($data)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM permiso WHERE id_usuario=:id_usuario");
        $query->bindParam(":id_usuario", $data["id_usuario"]);
        $query->execute();
        $data_permisos = $query->fetch(PDO::FETCH_ASSOC);

        if ($data_permisos) {
            return array("success" => true, "message" => array("permisos" => $data_permisos));
        } else {
            return array("success" => false, "message" => "");
        }
    }

    public function send_email($data)
    {
        // Envio de correo electrónico
        // datos del registro
        $email = $data["email"];
        $pass = $data["pass"];
        // $pass = $data["pass"] ? $this->security->decryption($data["pass"]) : '';
        // enviar correo
        $to = $email;
        $subject = "CREDENCIALES DE ACCESO";
        $mensaje = `
                Estimado usuario Bienvenido al Sistema de transporte, a continuación se le envía sus credenciales para el acceso al sistema
                --------------------------------------------------
                CREDENCIALES:
                
                Correo electrónico: $email
                Contraseña: $pass
                
                `;
        $headers = 'From: ' . $this->data_terminal["email"] . "\r\n";
        $reply = mail($to, $subject, $mensaje, $headers);

        if ($reply) {
            return array("success" => true, "message" => "Correo electrónico enviado con éxito");
        } else {
            return array("success" => false, "message" => "El correo electrónico no se pudo enviar, intentalo más tarde.");
        }
    }

    public function getComisiones()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT comision_nivel1, comision_nivel2 FROM configuracion LIMIT 1");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'nivel1' => $result['comision_nivel1'] ?? 0,
                    'nivel2' => $result['comision_nivel2'] ?? 0
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener comisiones'
            ];
        }
    }

    public function getTiposUsuario()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT id_tp_usuario, descripcion, tipo 
            FROM tp_usuario 
            WHERE descripcion IN ('ADMINISTRADOR', 'VENDEDOR', 'TERRAMOZA', 'AYUDANTE', 'ASISTENTE', 'OTROS', 'COMISIONISTA 1', 'COMISIONISTA 2')
            ORDER BY 
                CASE 
                    WHEN descripcion LIKE 'COMISIONISTA%' THEN 1
                    ELSE 2
                END,
                descripcion
        ");
            $query->execute();
            $tipos = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $tipos
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener tipos de usuario'
            ];
        }
    }

    public function get_personalxterminal($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT 
                u.id_usuario,
                u.id_terminal,
                CONCAT(u.nombres, ' ',u.apellidos) AS nombre_personal,
                u.num_docu,
                u.id_tp_usuario,
                tp_u.descripcion AS tp_usuario
            FROM usuario u 
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            WHERE tp_u.descripcion NOT IN('PASAJERO','PROVEEDOR','CLIENTE', 'SOPORTE', 'ADMINISTRADOR') AND id_usuario != 2 
            AND u.num_docu != '1111'
            AND u.id_terminal = :id_terminal
            ");
            $query->bindParam(":id_terminal", $data['id_terminal']);
            $query->execute();
            $personal_terminal = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => $personal_terminal
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener el personal'
            ];
        }
    }

    public function buscar_nacionalidad($q)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT 
            cod_pais, 
            nombre_pais, 
            abrev,
            gentilicio
        FROM paises
        WHERE nombre_pais LIKE ?
        LIMIT 5
        ");

        $query->execute(["%$q%"]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function getDataExportPersonal($data)
    {
        try {

            $terminal = $data['terminal_personal'] ?? '';
            $tp_usuario = $data['tp_usuario_personal'] ?? '';
            $genero = $data['genero_personal'] ?? '';
            $estado = $data['estado_personal'] ?? '';
            $search = $data['search'] ?? '';

            $where = [
                "tp_u.tipo IN ('INTERNO', 'EXTERNO')",
                "tp_u.descripcion IN (
                'PERSONAL',
                'VENDEDOR',
                'TERRAMOZA',
                'ADMINISTRADOR',
                'AYUDANTE',
                'COMISIONISTA 1',
                'COMISIONISTA 2'
            )",
                "u.num_docu != '1111'"
            ];

            $params = [];
            if (!empty($terminal)) {
                $where[] = "u.id_terminal = :terminal";
                $params[':terminal'] = $terminal;
            }
            if (!empty($tp_usuario)) {
                $where[] = "u.id_tp_usuario = :tp_usuario";
                $params[':tp_usuario'] = $tp_usuario;
            }
            if (!empty($genero)) {
                $where[] = "u.genero = :genero";
                $params[':genero'] = $genero;
            }
            if ($estado !== '') {
                $where[] = "u.estado = :estado";
                $params[':estado'] = $estado;
            }
            if (!empty($search)) {
                $search = trim($search);
                $searchValue = '%' . $search . '%';

                $where[] = "(
                t.nombre LIKE :search_terminal
                OR u.nombres LIKE :search_nombres
                OR u.apellidos LIKE :search_apellidos
                OR CONCAT(u.nombres, ' ', u.apellidos) LIKE :search_nombre_completo
                OR u.num_docu LIKE :search_num_docu
                OR u.fecha_nacimiento LIKE :search_fecha_nacimiento
                OR u.genero LIKE :search_genero
                OR u.celular LIKE :search_celular
                OR u.email LIKE :search_email
                OR u.direccion LIKE :search_direccion
                OR u.ubigeo LIKE :search_ubigeo
                OR tp_u.descripcion LIKE :search_tp_usuario
            )";

                $params[':search_terminal'] = $searchValue;
                $params[':search_nombres'] = $searchValue;
                $params[':search_apellidos'] = $searchValue;
                $params[':search_nombre_completo'] = $searchValue;
                $params[':search_num_docu'] = $searchValue;
                $params[':search_fecha_nacimiento'] = $searchValue;
                $params[':search_genero'] = $searchValue;
                $params[':search_celular'] = $searchValue;
                $params[':search_email'] = $searchValue;
                $params[':search_direccion'] = $searchValue;
                $params[':search_ubigeo'] = $searchValue;
                $params[':search_tp_usuario'] = $searchValue;
            }
            $whereClause = implode(' AND ', $where);
            $sql = "SELECT
                t.nombre AS terminal,
                CONCAT(u.nombres, ' ', u.apellidos) AS personal,
                u.fecha_nacimiento,
                tp_d.descripcion AS tp_docu,
                u.num_docu,
                u.genero,
                u.celular,
                u.email,
                u.direccion,
                u.ubigeo,
                tp_u.descripcion AS tp_usuario,
                u.estado
            FROM usuario u
            INNER JOIN terminal t ON t.id_terminal = u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu = u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo = u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario = u.id_tp_usuario
            WHERE $whereClause
            ORDER BY u.id_usuario DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportPersonal: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresaInfo()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT 
                e.id_empresa,
                e.razon_social AS nombre,
                e.num_docu AS ruc,
                e.direccion_fiscal AS direccion,
                e.logo
            FROM empresa e
            WHERE e.id_empresa = :id_empresa
            LIMIT 1
        ");

            $id_empresa = $this->id_empresa_sesion ?? 1;
            $query->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $query->execute();

            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                // Valores por defecto si no hay empresa
                return [
                    'id_empresa' => $id_empresa,
                    'nombre' => 'EMPRESA DE TRANSPORTES',
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'logo' => '',
                    'logo_exists' => false,
                    'logo_absolute_path' => ''
                ];
            }

            // Construir la ruta absoluta del logo
            $logo_absolute_path = '';
            $logo_exists = false;

            if (!empty($empresa['logo'])) {

                $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin/' . $empresa['logo'];

                if (file_exists($logo_absolute_path)) {
                    $logo_exists = true;
                } else {
                    // Intenta con otra ruta posible
                    $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin' . $empresa['logo'];
                    $logo_exists = file_exists($logo_absolute_path);
                }
            }

            // Agregar los campos calculados
            $empresa['logo_exists'] = $logo_exists;
            $empresa['logo_absolute_path'] = $logo_exists ? $logo_absolute_path : '';

            return $empresa;
        } catch (PDOException $e) {
            error_log("Error en getEmpresaInfo: " . $e->getMessage());
            return [
                'id_empresa' => $this->id_empresa_sesion ?? 1,
                'nombre' => 'EMPRESA DE TRANSPORTES',
                'ruc' => '',
                'direccion' => '',
                'telefono' => '',
                'logo' => '',
                'logo_absolute_path' => '',
                'logo_exists' => false
            ];
        }
    }
}
