<?php

class ImpresionModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_data()
    {
        $query =  $this->db->connect()->prepare("SELECT * FROM impresoras WHERE cod_usuario = :usuario");
        $query->bindParam(":usuario", $this->id_usuario_sesion);
        $query->execute();
        $reply = $query->fetch(PDO::FETCH_ASSOC);
        $reply['estado'] = $reply['estado'] ? $reply['estado'] : 0;
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => "Error al registrar los datos");
        }
    }

    public function register($data)
    {
        try {
            // Conexión única a la base de datos
            $conn = $this->db->connect();

            // Lógica para la configuración de impresoras
            $querySelect = $conn->prepare("SELECT id FROM impresoras WHERE cod_usuario = :usuario");
            $querySelect->bindParam(":usuario", $this->id_usuario_sesion);
            $querySelect->execute();

            // Fetch result safely, checking if it exists
            $result = $querySelect->fetch(PDO::FETCH_ASSOC);
            $respId = $result['id'] ?? null;

            // Hcaer inserciones dependiendo del tipo
            $tipo_impresion = isset($data['tipo_impresion']) ? $data['tipo_impresion'] : 'escritorio';
            if($tipo_impresion == 'bluetooth'){
                $nombre_impresora = null;
                $mac_impresora = null;
                $nombre_pc = null;
                $ip_pc = $data['ip_celular'];
            }else{
                $nombre_impresora = $data['nombre_impresora'];
                $nombre_pc = $data['nombre_pc'];
                $ip_pc = $data['ip_pc'];
                $mac_impresora = null;
            }

            // Preparar la consulta de actualización o inserción
            if ($respId) {
                // Si existe un registro previo, se actualiza
                $queryUpdate = $conn->prepare("UPDATE impresoras SET 
                cod_usuario = :usuario, 
                nombre_impresora = :nombre_impresora, 
                nombre_pc = :nombre_pc, 
                ip_pc = :ip_pc, 
                estado = :estado,
                mac_address = :mac_address, 
                tipo = :tipo 
                WHERE id = :id");

                $queryUpdate->bindParam(":id", $respId);
            } else {
                // Si no existe, se inserta uno nuevo
                $queryUpdate = $conn->prepare("INSERT INTO impresoras 
                (cod_usuario, nombre_impresora, nombre_pc, ip_pc, estado, mac_address, tipo) 
                VALUES (:usuario, :nombre_impresora, :nombre_pc, :ip_pc, :estado, :mac_address, :tipo)");
            }

            // Asignación común de parámetros
            $queryUpdate->bindParam(":usuario", $this->id_usuario_sesion);
            $queryUpdate->bindParam(":nombre_impresora", $nombre_impresora);
            $queryUpdate->bindParam(":nombre_pc", $nombre_pc);
            $queryUpdate->bindParam(":ip_pc", $ip_pc);
            // Asignar estado según el valor de 'impresion_d'
            $estado = isset($data['impresion_d']) && $data['impresion_d'] == '1' ? '1' : '0';
            $queryUpdate->bindParam(":estado", $estado);
            $queryUpdate->bindParam(":mac_address", $mac_impresora);
            $queryUpdate->bindParam(":tipo", $data['tipo_impresion']);

            // Ejecutar las consultas
            $conn->beginTransaction(); // Iniciar transacción
            $queryUpdate->execute(); // Actualizar o insertar impresora

            $conn->commit(); // Confirmar transacción

            return array("success" => true, "message" => "Registrado con éxito");
        } catch (PDOException $e) {
            // Si ocurre un error, se hace rollback de la transacción
            $conn->rollBack();
            error_log("Error al registrar: " . $e->getMessage());
            return array("success" => false, "message" => "Error al registrar los datos", $e->getMessage());
        }
    }
}
