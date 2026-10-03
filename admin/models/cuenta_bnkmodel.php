<?php

class Cuenta_bnkModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {

            $selectFields = "cb.id_cuenta,
            b.nombre AS banco,
            cb.descripcion,
            cb.tipo_cuenta,
            cb.numero_cuenta,
            cb.cci,
            cb.moneda,
            cb.saldo_inicial,
            cb.mostrar_reportes,
            cb.id_banco,
            cb.estado";

            $baseQuery = "
            FROM empresa_cuenta_bancaria cb
            INNER JOIN banco b
            ON b.id_banco = cb.id_banco";

            $searchColumns = [
                "b.nombre",
                "cb.descripcion",
                "cb.numero_cuenta",
                "cb.cci",
                "cb.moneda"
            ];

            $orderBy = "cb.id_cuenta DESC";

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
                "error" => $e->getMessage()
            ];
        }
    }

    public function add_register($data)
    {
        try {
            $data['id_empresa'] = 1;
            $query = $this->db->connect()->prepare("INSERT INTO empresa_cuenta_bancaria
            (
                id_empresa,
                id_banco,
                descripcion,
                tipo_cuenta,
                numero_cuenta,
                cci,
                moneda,
                saldo_inicial,
                mostrar_reportes,
                estado,
                orden
            )
            VALUES
            (
                :id_empresa,
                :id_banco,
                :descripcion,
                :tipo_cuenta,
                :numero_cuenta,
                :cci,
                :moneda,
                :saldo_inicial,
                :mostrar_reportes,
                :estado,
                :orden
            )");

            $query->bindParam(":id_empresa", $data["id_empresa"]);
            $query->bindParam(":id_banco", $data["id_banco"]);
            $query->bindParam(":descripcion", $data["descripcion"]);
            $query->bindParam(":tipo_cuenta", $data["tipo_cuenta"]);
            $query->bindParam(":numero_cuenta", $data["numero_cuenta"]);
            $query->bindParam(":cci", $data["cci"]);
            $query->bindParam(":moneda", $data["moneda"]);
            $query->bindParam(":saldo_inicial", $data["saldo_inicial"]);
            $query->bindParam(":mostrar_reportes", $data["mostrar_reportes"]);
            $query->bindParam(":estado", $data["estado"]);
            $query->bindParam(":orden", $data["orden"]);

            $query->execute();

            return [
                "success" => true,
                "message" => "Registro creado con éxito"
            ];
        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        "success" => false,
                        "message" => "Ya existe una cuenta con esos datos.",
                        $e->getMessage()
                    ];

                default:
                    return [
                        "success" => false,
                        "message" => "Ha ocurrido un error."
                    ];
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $data['id_empresa'] = 1;

            $query = $this->db->connect()->prepare("UPDATE empresa_cuenta_bancaria SET

            id_banco=:id_banco,
            descripcion=:descripcion,
            tipo_cuenta=:tipo_cuenta,
            numero_cuenta=:numero_cuenta,
            cci=:cci,
            moneda=:moneda,
            saldo_inicial=:saldo_inicial,
            mostrar_reportes=:mostrar_reportes,
            estado=:estado,
            orden=:orden

            WHERE id_cuenta=:id_cuenta");

            $query->bindParam(":id_cuenta", $data["id_cuenta"]);
            $query->bindParam(":id_banco", $data["id_banco"]);
            $query->bindParam(":descripcion", $data["descripcion"]);
            $query->bindParam(":tipo_cuenta", $data["tipo_cuenta"]);
            $query->bindParam(":numero_cuenta", $data["numero_cuenta"]);
            $query->bindParam(":cci", $data["cci"]);
            $query->bindParam(":moneda", $data["moneda"]);
            $query->bindParam(":saldo_inicial", $data["saldo_inicial"]);
            $query->bindParam(":mostrar_reportes", $data["mostrar_reportes"]);
            $query->bindParam(":estado", $data["estado"]);
            $query->bindParam(":orden", $data["orden"]);

            $query->execute();

            return [
                "success" => true,
                "message" => "Registro modificado con éxito"
            ];
        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        "success" => false,
                        "message" => "Ya existe una cuenta con esos datos."
                    ];

                default:
                    return [
                        "success" => false,
                        "message" => "Ha ocurrido un error."
                    ];
            }
        }
    }

    public function delete_register($data)
    {
        try {

            $query = $this->db->connect()->prepare("
                UPDATE empresa_cuenta_bancaria
                SET estado='INACTIVO'
                WHERE id_cuenta=:id_cuenta;
            ");

            $query->bindParam(":id_cuenta", $data["id_cuenta"]);

            $query->execute();

            return [
                "success" => true,
                "message" => "Registro eliminado con éxito"
            ];

        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        "success" => false,
                        "message" => "La cuenta se encuentra en uso."
                    ];

                default:
                    return [
                        "success" => false,
                        "message" => "Ha ocurrido un error."
                    ];
            }
        }
    }
}