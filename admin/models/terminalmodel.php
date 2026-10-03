<?php

class TerminalModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            if ($_SESSION["data_usuario"]["tp_usuario"] == "ADMINISTRADOR" || $_SESSION["data_usuario"]["tp_usuario"] == "SOPORTE") {
                $where = "";
            } else {
                $where = "WHERE t.id_terminal = " . intval($_SESSION["data_terminal"]["id_terminal"]);
            }

            $selectFields = "
            t.id_terminal,
            t.id_empresa,
            t.logo,
            t.nombre,
            t.tipo,
            t.color,
            t.ubigeo,
            ub.depa AS ubi_depa,
            ub.provi AS ubi_provi,
            ub.distri AS ubi_distri,
            t.direccion_fiscal,
            t.direccion_comercial,
            t.cod_domicilio_fiscal,
            t.celular,
            t.email,
            t.siteweb,
            t.encargado,
            t.c_selva,
            tsm.id_serie_manifiesto
            ";

            $baseQuery = "
            FROM terminal t
            INNER JOIN empresa e ON e.id_empresa = t.id_empresa
            INNER JOIN ubigeo ub ON ub.cod_ubigeo = t.ubigeo
            LEFT JOIN terminal_serie_manifiesto tsm ON t.id_terminal = tsm.id_terminal
            $where
            ";

            $searchColumns = [
                "t.nombre",
                "t.tipo",
                "t.ubigeo",
                "t.cod_domicilio_fiscal",
                "t.direccion_fiscal",
                "t.direccion_comercial",
                "t.celular",
                "t.email"
            ];

            $orderBy = "t.id_terminal DESC";

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
        $conn = null;

        try {

            $conn = $this->db->connect();
            $conn->beginTransaction();

            // ==========================================
            // 1. SUBIR LOGO
            // ==========================================
            if ($_FILES["logo"]["error"] == 0) {

                $reply = $this->upload->upload_basic(
                    $_FILES["logo"],
                    "private/terminales/" . $data["nombre"] . "/",
                    "logo.png"
                );

                if ($reply["success"]) {
                    $logo = $reply["message"];
                } else {
                    return [
                        'success' => false,
                        "message" => "Ha ocurrido un error al subir la imagen"
                    ];
                }

            } else {
                $logo = $data["logo_before"];
            }


            // ==========================================
            // 2. SELVA
            // ==========================================
            $c_selva = isset($data['c_selva']) ? 1 : 0;


            // ==========================================
            // 3. REGISTRAR TERMINAL
            // ==========================================
            $query = $conn->prepare("
            INSERT INTO terminal (
                id_empresa,
                logo,
                nombre,
                tipo,
                color,
                ubigeo,
                direccion_fiscal,
                direccion_comercial,
                cod_domicilio_fiscal,
                celular,
                email,
                siteweb,
                id_sesionpersonal,
                c_selva
            ) 
            VALUES (
                :id_empresa,
                :logo,
                :nombre,
                :tipo,
                :color,
                :ubigeo,
                :direccion_fiscal,
                :direccion_comercial,
                :cod_domicilio_fiscal,
                :celular,
                :email,
                :siteweb,
                :id_sesionpersonal,
                :c_selva
            )
            ");

            $query->bindValue(':id_empresa', $this->id_empresa_sesion);
            $query->bindValue(':logo', $logo);
            $query->bindValue(':nombre', $data["nombre"]);
            $query->bindValue(':tipo', $data["tipo"]);
            $query->bindValue(':color', $data["color"]);
            $query->bindValue(':ubigeo', $data["ubigeo"]);
            $query->bindValue(':direccion_fiscal', $data["dir_domicilioFiscal"]);
            $query->bindValue(':direccion_comercial', $data["dir_domicilioComercial"]);
            $query->bindValue(':cod_domicilio_fiscal', $data["cod_domicilioFiscal"]);
            $query->bindValue(':celular', $data["celular"]);
            $query->bindValue(':email', $data["email"]);
            $query->bindValue(':siteweb', $data["siteweb"]);
            $query->bindValue(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindValue(':c_selva', $c_selva);

            $query->execute();


            // ==========================================
            // 4. OBTENER ID DEL TERMINAL
            // ==========================================
            $id_terminal = $conn->lastInsertId();


            // ==========================================
            // 5. RELACIONAR TERMINAL CON SERIE
            // ==========================================
            if (!empty($data['serie_manifiesto'])) {

                $query = $conn->prepare("
                INSERT INTO terminal_serie_manifiesto (
                    id_terminal,
                    id_serie_manifiesto
                )
                VALUES (
                    :id_terminal,
                    :id_serie_manifiesto
                )
                ");

                $query->bindValue(
                    ':id_terminal',
                    $id_terminal,
                    PDO::PARAM_INT
                );

                $query->bindValue(
                    ':id_serie_manifiesto',
                    $data['serie_manifiesto'],
                    PDO::PARAM_INT
                );

                $query->execute();
            }


            // ==========================================
            // 6. CONFIRMAR
            // ==========================================
            $conn->commit();

            return [
                'success' => true,
                "message" => "Registro creado con éxito"
            ];

        } catch (PDOException $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        "message" => "Ya existe un registro con estos datos"
                    ];

                default:
                    return [
                        'success' => false,
                        "message" => "Ha ocurrido un error, intentalo mas tarde.",
                        "error" => $e->getMessage()
                    ];
            }
        }
    }

    public function edit_register($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();
            $conn->beginTransaction();

            $data['t_personal'] = $data['t_personal'] ?? '';

            // ==========================================
            // 1. SUBIR LOGO
            // ==========================================
            if ($_FILES["logo"]["error"] == 0) {

                $reply = $this->upload->upload_basic(
                    $_FILES["logo"],
                    "private/terminales/" . $data["nombre"] . "/",
                    "logo.png"
                );

                if ($reply["success"]) {
                    $logo = $reply["message"];
                } else {
                    return [
                        'success' => false,
                        "message" => "Ha ocurrido un error al subir la imagen"
                    ];
                }

            } else {
                $logo = $data["logo_before"];
            }


            // ==========================================
            // 2. SELVA
            // ==========================================
            $c_selva = isset($data['c_selva']) ? 1 : 0;


            // ==========================================
            // 3. ACTUALIZAR TERMINAL
            // ==========================================
            $query = $conn->prepare("
            UPDATE terminal 
            SET 
                logo = :logo,
                nombre = :nombre,
                tipo = :tipo,
                color = :color,
                ubigeo = :ubigeo,
                direccion_fiscal = :direccion_fiscal,
                direccion_comercial = :direccion_comercial,
                cod_domicilio_fiscal = :cod_domicilio_fiscal,
                celular = :celular,
                email = :email,
                siteweb = :siteweb,
                id_sesionpersonal = :id_sesionpersonal,
                encargado = :encargado,
                c_selva = :c_selva
            WHERE id_terminal = :id_terminal
        ");

            $query->bindValue(':id_terminal', $data["id_terminal"], PDO::PARAM_INT);
            $query->bindValue(':logo', $logo);
            $query->bindValue(':nombre', $data["nombre"]);
            $query->bindValue(':tipo', $data["tipo"]);
            $query->bindValue(':color', $data["color"]);
            $query->bindValue(':ubigeo', $data["ubigeo"]);
            $query->bindValue(':direccion_fiscal', $data["dir_domicilioFiscal"]);
            $query->bindValue(':direccion_comercial', $data["dir_domicilioComercial"]);
            $query->bindValue(':cod_domicilio_fiscal', $data["cod_domicilioFiscal"]);
            $query->bindValue(':celular', $data["celular"]);
            $query->bindValue(':email', $data["email"]);
            $query->bindValue(':siteweb', $data["siteweb"]);
            $query->bindValue(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindValue(':encargado', $data['t_personal']);
            $query->bindValue(':c_selva', $c_selva);

            $query->execute();


            // ==========================================
            // 4. ELIMINAR RELACIÓN ACTUAL
            // ==========================================
            $query = $conn->prepare("
            DELETE FROM terminal_serie_manifiesto
            WHERE id_terminal = :id_terminal
        ");

            $query->bindValue(
                ':id_terminal',
                $data["id_terminal"],
                PDO::PARAM_INT
            );

            $query->execute();


            // ==========================================
            // 5. CREAR NUEVA RELACIÓN
            // ==========================================
            if (!empty($data['serie_manifiesto'])) {

                $query = $conn->prepare("
                INSERT INTO terminal_serie_manifiesto (
                    id_terminal,
                    id_serie_manifiesto
                )
                VALUES (
                    :id_terminal,
                    :id_serie_manifiesto
                )
            ");

                $query->bindValue(
                    ':id_terminal',
                    $data["id_terminal"],
                    PDO::PARAM_INT
                );

                $query->bindValue(
                    ':id_serie_manifiesto',
                    $data['serie_manifiesto'],
                    PDO::PARAM_INT
                );

                $query->execute();
            }


            // ==========================================
            // 6. CONFIRMAR
            // ==========================================
            $conn->commit();

            return [
                'success' => true,
                "message" => "Registro modificado con éxito"
            ];

        } catch (PDOException $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        "message" => $e->getMessage()
                    ];

                default:
                    return [
                        'success' => false,
                        "message" => "Ha ocurrido un error, intentalo mas tarde.",
                        "error" => $e->getMessage()
                    ];
            }
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM terminal WHERE id_terminal=:id_terminal");
            $query->bindParam(':id_terminal', $data["id_terminal"]);
            $query->execute();
            if (file_exists($data["logo"])) {
                unlink($data["logo"]);
            }
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

    public function get_terminal_personal($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                *
            FROM usuario u
            INNER JOIN terminal t ON u.id_terminal=t.id_terminal
            INNER JOIN tp_usuario ut ON u.id_tp_usuario = ut.id_tp_usuario
            WHERE u.id_terminal=:id_terminal AND ut.tipo = 'INTERNO' AND u.id_usuario != 1");
            $query->bindParam(':id_terminal', $data["id_terminal"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
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
}
