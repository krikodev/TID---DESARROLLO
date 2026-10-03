<?php

class ReportesModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function getVentaGeneral()
    {
        $fechaini = $_POST["fechaini"];
        $fechafin = $_POST["fechafin"];
        $tDocu = $_POST["tDocu"];
        $serie = $_POST["serie"];
        $tpagos = $_POST["tpagos"];
        $estado = $_POST["estado"];
        $cliente = $_POST["cliente"];
        $tpCliente = $_POST["tpCliente"];
        $usuario = $_POST["usuario"];
        $cjchica = $_POST["cjchica"];

        $query = $this->db->connect()->prepare("
        SELECT
        vt.id_venta_notas AS id_venta_notas,
        vt.tp_cliente AS tp_cliente,
        vt.fecha_emision AS fecha_emision,
        cli.tp_docu AS tp_docu_cli,
        cli.num_docu AS num_docu_cli,
        cli.nombres AS nombres_cli,
        cli.apellidos AS apellidos_cli,
        cli.razon_social AS razon_social_cli,
        tp_p.descripcion AS descripcion_tp_c,
        tp_c.descripcion AS tipo_comprobante,
        vt.cod_voucher AS cod_voucher,
        vt.serie AS serie,
        vt.num_docu AS num_docu_venta,
        vt.estado AS estado_venta,
        cj.descripcion AS caja_chica,
        tp_m.descripcion AS tipo_moneda,
        vt.total AS precio_total,
        vt.motivo_anular AS motivo_anular,
        user.nombres AS nombres_user,
        user.apellidos AS apellidos_user,
        sucu.descripcion AS sucursal
        FROM venta_notas vt
        INNER JOIN tp_comprobante tp_c ON tp_c.id_comprobante=vt.id_tp_comprobante
        INNER JOIN usuario user ON user.id_usuario=vt.id_usuario
        INNER JOIN sucursal sucu ON sucu.id_sucursal=user.id_sucursal
        INNER JOIN cliente cli ON cli.id_cliente=vt.id_cliente
        INNER JOIN tp_pago tp_p ON tp_p.id_tp_pago=vt.id_tp_pago
        INNER JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=vt.id_tp_moneda
        INNER JOIN caja_chica cj ON cj.id_caja_chica=vt.id_caja_chica
        WHERE vt.estado LIKE '$estado' AND tp_c.id_comprobante LIKE '$tDocu' AND vt.serie LIKE '$serie' AND (DATE(vt.fecha_emision)>='$fechaini' AND DATE(vt.fecha_emision)<='$fechafin') AND vt.id_cliente LIKE '$cliente' AND vt.tp_cliente LIKE '$tpCliente' AND vt.id_usuario LIKE '$usuario' AND vt.id_tp_pago LIKE '$tpagos' AND vt.id_caja_chica LIKE '$cjchica'
        ORDER BY (vt.num_docu) DESC
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }

    public function getUsuarios()
    {
        $tdocu = $_POST["tdocu"];
        $sucursal = $_POST["sucursal"];
        $query = $this->db->connect()->prepare("SELECT 
        user.id_usuario AS id_usuario,
        CONCAT(user.nombres,' ',user.apellidos) AS usuario,
        user.tp_docu AS tp_docu,
        user.num_docu AS num_docu,
        ubi.cod_ubigeo AS cod_ubigeo,
        ubi.depa AS depa_ubigeo,
        ubi.provi AS provi_ubigeo,
        ubi.distri AS distri_ubigeo,
        user.direccion AS direccion,
        user.celular AS celular,
        user.fecha_naci AS fecha_naci,
        user.email AS email,
        user.tipo_usuario AS tipo_usuario,
        IF(user.estado=1,'Habilitado','Deshabilitado') AS estado,
        suc.descripcion AS sucursal

        FROM usuario user
        INNER JOIN sucursal suc ON suc.id_sucursal=user.id_sucursal
        INNER JOIN ubigeo ubi ON ubi.cod_ubigeo=user.cod_ubigeo
        WHERE user.tp_docu LIKE '$tdocu' AND user.id_sucursal LIKE '$sucursal'
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }

    public function getClientes()
    {
        $tpCliente = $_POST["tpCliente"];
        $tdocu = $_POST["tdocu"];
        $sucursal = $_POST["sucursal"];
        $query = $this->db->connect()->prepare("SELECT 
        cli.id_cliente AS id_cliente,
        IF(LENGTH(cli.razon_social)!=0, cli.razon_social, CONCAT(cli.nombres, ' ', cli.apellidos)) AS cliente,
        cli.tp_cliente AS tp_cliente,
        cli.tp_docu AS tp_docu,
        cli.num_docu AS num_docu,
        ubi.cod_ubigeo AS cod_ubigeo,
        ubi.depa AS depa_ubigeo,
        ubi.provi AS provi_ubigeo,
        ubi.distri AS distri_ubigeo,
        cli.direccion AS direccion,
        cli.celular AS celular,
        suc.descripcion AS sucursal

        FROM cliente cli
        INNER JOIN sucursal suc ON suc.id_sucursal=cli.id_sucursal
        INNER JOIN ubigeo ubi ON ubi.cod_ubigeo=cli.cod_ubigeo
        WHERE cli.tp_docu LIKE '$tdocu' AND cli.tp_cliente LIKE '$tpCliente' AND cli.id_sucursal LIKE '$sucursal'
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }

    public function getSucursales()
    {
        $ubigeo = $_POST["ubigeo"];
        $query = $this->db->connect()->prepare("SELECT 
        suc.id_sucursal AS id_sucursal,
        suc.descripcion AS sucursal,
        suc.cod_sucursal AS cod_sucursal,
        ubi.cod_ubigeo AS cod_ubigeo,
        ubi.depa AS depa_ubigeo,
        ubi.provi AS provi_ubigeo,
        ubi.distri AS distri_ubigeo,
        suc.direccion AS direccion,
        suc.celular AS celular,
        suc.email AS email,
        suc.num_serie AS num_serie
        FROM sucursal suc
        INNER JOIN ubigeo ubi ON ubi.cod_ubigeo=suc.cod_ubigeo
        WHERE ubi.cod_ubigeo LIKE '$ubigeo'
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }

    public function getTarifas()
    {
        $fechaini = $_POST["fechaini"];
        $fechafin = $_POST["fechafin"];
        $sucursal = $_POST["sucursal"];
        $query = $this->db->connect()->prepare("SELECT 
        estaci.descripcion AS estacionamiento,
        suc.descripcion AS sucursal,
        ubi.cod_ubigeo AS cod_ubigeo,
        ubi.depa AS depa_ubigeo,
        ubi.provi AS provi_ubigeo,
        ubi.distri AS distri_ubigeo,
        suc.direccion AS direccion,
        tari.fecha AS fecha,
        tari.monto AS monto
        FROM tarifa tari
        INNER JOIN estacionamiento estaci ON estaci.id_estacionamiento=tari.id_estacionamiento
        INNER JOIN sucursal suc ON suc.id_sucursal= estaci.id_sucursal
        INNER JOIN ubigeo ubi ON ubi.cod_ubigeo=suc.cod_ubigeo
        WHERE suc.id_sucursal LIKE '$sucursal' AND (tari.fecha>='$fechaini' AND tari.fecha<='$fechafin')
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }

    public function getCajaChicas()
    {
        $fechaini = $_POST["fechaini"];
        $fechafin = $_POST["fechafin"];
        $sucursal = $_POST["sucursal"];
        $usuario = $_POST["usuario"];
        $estado = $_POST["estado"];
        $turno = $_POST["turno"];

        $query = $this->db->connect()->prepare("SELECT 
        cj.id_caja_chica AS id_caja_chica,
        cj.id_usuario AS id_usuario,
        cj.fecha_inicio AS fecha_inicio,
        cj.fecha_fin AS fecha_fin,
        cj.descripcion AS descripcion,
        cj.monto_inicial AS monto_inicial,
        cj.monto_final AS monto_final,
        cj.estado AS estado,
        cj.turno AS turno,
        CONCAT(user.nombres, ' ', user.apellidos) AS full_names,
        user.tipo_usuario AS tipo_usuario
        FROM caja_chica cj 
        INNER JOIN usuario user ON user.id_usuario=cj.id_usuario
        INNER JOIN sucursal suc ON suc.id_sucursal=user.id_sucursal
        WHERE suc.id_sucursal LIKE '$sucursal' AND (cj.fecha_inicio>='$fechaini' AND cj.fecha_inicio<='$fechafin') AND user.id_usuario LIKE '$usuario' AND cj.estado LIKE '$estado' AND cj.turno LIKE '$turno'
        ");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('data' => $reply);
    }
}
