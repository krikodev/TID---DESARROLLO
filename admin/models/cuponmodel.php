<?php

class CuponModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $selectFields   =
                "c.id_cupon, 
                c.codigo, 
                c.tipo, 
                c.valor, 
                c.tope_maximo, 
                c.monto_minimo, 
                DATE(c.fecha_inicio) AS fecha_inicio, 
                DATE(c.fecha_fin) AS fecha_fin, 
                IFNULL(u.uso_actual,0) AS uso_actual,
                c.uso_maximo, 
                c.solo_nuevos, 
                c.estado
            ";
            $baseQuery      =
                "FROM cupon c
                 LEFT JOIN (
                       SELECT id_cupon, COUNT(*) AS uso_actual
                       FROM cupon_uso
                       GROUP BY id_cupon
                ) u ON u.id_cupon = c.id_cupon";
            $searchColumns = [
                "c.codigo",
                "c.tipo",
                "c.valor",
                "c.tope_maximo",
                "c.monto_minimo",
                "c.fecha_inicio",
                "c.fecha_fin",
                "c.uso_maximo",
                "c.uso_actual",
                "c.creado_en",
            ];

            $orderBy        = "c.id_cupon DESC";


            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );
            return $resultado;
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
            $query =  $this->db->connect()->prepare("INSERT INTO cupon (
                codigo, tipo, valor, tope_maximo, monto_minimo, fecha_inicio, fecha_fin, uso_maximo, estado) 
                VALUES(
                :codigo, :tipo, :valor, :tope_maximo, :monto_minimo, :fecha_inicio, :fecha_fin, :uso_maximo, :estado)");
            $query->bindParam(':codigo', $data["codigo"]);
            $query->bindParam(':tipo', $data["tipo"]);
            $query->bindParam(':valor', $data["valor"]);
            $query->bindParam(':tope_maximo', $data["tope_maximo"]);
            $query->bindParam(':monto_minimo', $data["monto_minimo"]);
            $query->bindParam(':fecha_inicio', $data["fecha_inicio"]);
            $query->bindParam(':fecha_fin', $data["fecha_fin"]);
            $query->bindParam(':uso_maximo', $data["uso_maximo"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            return ['success' => true, "message" => "Registro creado con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e];
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            UPDATE cupon SET 
                codigo = :codigo,
                tipo = :tipo,
                valor = :valor,
                tope_maximo = :tope_maximo,
                monto_minimo = :monto_minimo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                uso_maximo = :uso_maximo,
                estado = :estado
            WHERE id_cupon = :id_cupon
        ");

            $query->bindParam(':id_cupon', $data['id_cupon']);
            $query->bindParam(':codigo', $data['codigo']);
            $query->bindParam(':tipo', $data['tipo']);
            $query->bindParam(':valor', $data['valor']);
            $query->bindParam(':tope_maximo', $data['tope_maximo']);
            $query->bindParam(':monto_minimo', $data['monto_minimo']);
            $query->bindParam(':fecha_inicio', $data['fecha_inicio']);
            $query->bindParam(':fecha_fin', $data['fecha_fin']);
            $query->bindParam(':uso_maximo', $data['uso_maximo']);
            $query->bindParam(':estado', $data['estado']);

            $query->execute();

            return ['success' => true, 'message' => 'Registro modificado con éxito'];
        } catch (PDOException $e) {

            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, 'message' => 'Ya existe un cupón con estos datos'];
                default:
                    return ['success' => false, 'message' => 'Ha ocurrido un error, inténtalo más tarde'];
            }
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM cupon WHERE id_cupon=:id_cupon");
            $query->bindParam(":id_cupon", $data["id_cupon"]);
            $query->execute();
            return ['success' => true, "message" => "Registro eliminado con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "El registro se encuentra protegido"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function get_precio_cupon($data)
    {
        try {
            $conn = $this->db->connect();

            $codigo = trim($data['codigo'] ?? '');
            $precio = floatval($data['precio'] ?? 0);

            if ($codigo === '' || $precio <= 0) {
                return ['success' => false, 'mensaje' => 'Datos incompletos'];
            }

            $query = $conn->prepare("
            SELECT 
                c.id_cupon,
                c.tipo,
                c.valor,
                c.tope_maximo,
                c.monto_minimo,
                c.fecha_inicio,
                c.fecha_fin,
                c.uso_maximo,
                COUNT(u.id_cupon_uso) AS uso_actual
            FROM cupon c
            LEFT JOIN cupon_uso u ON u.id_cupon = c.id_cupon
            WHERE c.codigo = :codigo
              AND c.estado = 'ACTIVO'
              AND NOW() BETWEEN c.fecha_inicio AND c.fecha_fin
            GROUP BY c.id_cupon
            LIMIT 1
           ");

            $query->bindParam(':codigo', $codigo);
            $query->execute();

            $cupon = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cupon) {
                return ['success' => false, 'mensaje' => 'Cupón no válido o vencido'];
            }

            if ($precio < $cupon['monto_minimo']) {
                return [
                    'success' => false,
                    'mensaje' => 'Monto mínimo requerido: S/ ' . $cupon['monto_minimo']
                ];
            }

            if (!is_null($cupon['uso_maximo']) && $cupon['uso_actual'] >= $cupon['uso_maximo']) {
                return ['success' => false, 'mensaje' => 'Cupón agotado'];
            }

            if ($cupon['tipo'] === 'PORCENTAJE') {
                $descuento = ($precio * $cupon['valor']) / 100;
                if (!is_null($cupon['tope_maximo'])) {
                    $descuento = min($descuento, $cupon['tope_maximo']);
                }
            } else {
                $descuento = $cupon['valor'];
            }

            $descuento = min($descuento, $precio);
            $total_final = round($precio - $descuento, 2);

            return [
                'success' => true,
                'descuento' => round($descuento, 2),
                'total_final' => $total_final,
                'id_cupon' => $cupon['id_cupon'],
                'mensaje' => 'Cupón aplicado correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'mensaje' => 'Error al validar cupón'
            ];
        }
    }
}
