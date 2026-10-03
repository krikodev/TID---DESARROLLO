<?php

class EmpresaModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_data()
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("SELECT * FROM empresa");
        $query->execute();
        $reply = $query->fetch(PDO::FETCH_ASSOC);

        $query1 = $conn->prepare("
        SELECT
        ruc_encomienda_separado
        FROM configuracion
        ");
        $query1->execute();
        $ruc_encomienda_separado = $query1->fetchColumn();

        $reply['fr_comprobante'] = $reply['fr_comprobante'] ?? '';
        $reply['termscond_pasaje'] = $reply['termscond_pasaje'] ?? '';
        $reply['termscond_encomienda'] = $reply['termscond_encomienda'] ?? '';
        $reply['termscond_facturador'] = $reply['termscond_facturador'] ?? '';
        $reply['mostrar_direccion_completa'] = $reply['mostrar_direccion_completa'] ?? '';
        $reply['mostrar_tracking'] = $reply['mostrar_tracking'] ?? '';
        $reply['mostrar_vendedor'] = $reply['mostrar_vendedor'] ?? '';
        $reply['datos_destinatario'] = $reply['datos_destinatario'] ?? '';  // NUEVO
        $reply['origen_destino'] = $reply['origen_destino'] ?? '';  // NUEVO
        $reply['ruc_encomienda_separado'] = $ruc_encomienda_separado ?? 0;  // NUEVO
        $reply['nro_cuenta_bancaria'] = $reply['nro_cuenta_bancaria'] ?? '';
        $reply['telefono_empresa'] = $reply['telefono_empresa'] ?? '';
        return $reply ? array("success" => true, "message" => $reply) : array("success" => false, "message" => null);
    }

    public function register($data)
    {
        $ose = intval($data["envio_ose"]);
        $guia = intval($data["envio_guia"]);
        $m_terminales = intval($data["m_terminales"]);
        $m_terminalesManifi = intval($data["m_terminalesManifi"]);
        $porcent_venta = intval($data["porcent_venta"]);
        $ruc_encomienda_separado = intval($data["ruc_encomienda_separado"]);  // NUEVO
        $nro_cuenta_BN = trim($data["nro_cuenta"]);
        $nro_cuenta_bancaria = trim($data["nro_cuenta_bancaria"] ?? '');
        $telefono_empresa = trim($data["telefono_empresa"] ?? '');
        $termscond_facturador = trim($data["termscond_facturador"] ?? '');
        try {
            if ($_FILES["logo"]["error"] == 0) {
                $reply = $this->upload->upload_basic($_FILES["logo"], "../img/admin/", $_FILES["logo"]["name"]);
                if ($reply["success"]) {
                    $logo = $reply["message"];
                } else {
                    return ['success' => false, "message" => "Ha ocurrido un error al subir la imagen"];
                }
            } else {
                $logo = $data["logo_before"];
            }

            if ($_FILES["logo_encomienda"]["error"] == 0) {
                $reply = $this->upload->upload_basic($_FILES["logo_encomienda"], "../img/admin/", $_FILES["logo_encomienda"]["name"]);
                if ($reply["success"]) {
                    $logo_encomienda = $reply["message"];
                } else {
                    return ['success' => false, "message" => "Ha ocurrido un error al subir la imagen"];
                }
            } else {
                $logo_encomienda = $data["logo_before_encomienda"];
            }

            if ($_FILES["cdt"]["error"] == 0) {
                $reply = $this->upload->upload_basic($_FILES["cdt"], "private/empresa/", "cdt.pfx");
                if ($reply["success"]) {
                    $cdt = $reply["message"];
                } else {
                    return ['success' => false, "message" => "Ha ocurrido un error al subir el archivo"];
                }
            } else {
                $cdt = $data["cdt_before"];
            }


            if ($_FILES["cdt_encomienda"]["error"] == 0) {
                $reply = $this->upload->upload_basic($_FILES["cdt_encomienda"], "private/empresa/", "cdt.pfx");
                if ($reply["success"]) {
                    $cdt_encomienda = $reply["message"];
                } else {
                    return ['success' => false, "message" => "Ha ocurrido un error al subir el archivo"];
                }
            } else {
                $cdt_encomienda = $data["cdt_before_encomienda"];
            }

            $conn = $this->db->connect();
            $query = $conn->prepare("SELECT * FROM empresa");
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);
            if ($reply) {
                $query = $conn->prepare("UPDATE empresa SET
                logo=:logo,
                tp_docu='RUC',
                num_docu=:num_docu,
                razon_social=:razon_social,
                ubigeo=:ubigeo,
                direccion_fiscal=:direccion,
                cdt_file=:cdt,
                cdt_clave=:cdt_clave,
                modo_sistema=:modo,
                estado=:estado,
                condicion=:condicion,
                user_sol=:user_sol,
                pass_sol=:pass_sol,
                cpe_id=:cpe_id,
                cpe_clave=:cpe_clave,
                guia_id=:guia_id,
                guia_clave=:guia_clave,
                envio_ose=:envio_ose,
                permiso_guia=:envio_guia,
                link_ose=:link_ose,
                m_terminales=:m_terminales,
                m_terminalesManifi=:m_terminalesManifi,
                l_terminales=:l_terminales,
                l_usuarios=:l_usuarios,
                venta_web=:venta_web,
                fr_comprobante=:fr_comprobante,
                porcent_venta=:porcent_venta,
                embarque_general=:embarque_general,
                impresion_ecompleta=:impresion_ecompleta,
                mostrar_direccion_completa=:mostrar_direccion_completa,
                mostrar_tracking=:mostrar_tracking,
                mostrar_vendedor=:mostrar_vendedor,
                datos_destinatario=:datos_destinatario,
                origen_destino=:origen_destino,
                nro_cuenta_BN=:nro_cuenta_BN,
                nro_cuenta_bancaria=:nro_cuenta_bancaria, 
                telefono_empresa=:telefono_empresa,
                termscond_pasaje=:termscond_pasaje,
                termscond_encomienda=:termscond_encomienda,
                termscond_facturador=:termscond_facturador,
                correo=:correo,

                logo_encomienda =:logo_encomienda,
                tp_docu_encomienda ='RUC',
                num_docu_encomienda =:num_docu_encomienda,
                razon_social_encomienda =:razon_social_encomienda,
                ubigeo_encomienda =:ubigeo_encomienda,
                direccion_fiscal_encomienda=:direccion_fiscal_encomienda,
                cdt_file_encomienda=:cdt_file_encomienda,
                cdt_clave_encomienda=:cdt_clave_encomienda,
                estado_encomienda=:estado_encomienda,
                condicion_encomienda=:condicion_encomienda,
                user_sol_encomienda=:user_sol_encomienda,
                pass_sol_encomienda=:pass_sol_encomienda,
                cpe_id_encomienda=:cpe_id_encomienda,
                cpe_clave_encomienda=:cpe_clave_encomienda,
                guia_id_encomienda=:guia_id_encomienda,
                guia_clave_encomienda=:guia_clave_encomienda,
                nro_cuenta_BN_encomienda=:nro_cuenta_BN_encomienda
                ");
            } else {
                $query = $conn->prepare("INSERT INTO empresa (
                    logo, tp_docu, num_docu, razon_social, ubigeo, direccion_fiscal,
                    cdt_file, modo_sistema, estado, condicion, user_sol, pass_sol,
                    cpe_id, cpe_clave, guia_id, guia_clave, envio_ose, permiso_guia, link_ose, 
                    m_terminales, m_terminalesManifi, l_terminales, l_usuarios, venta_web, fr_comprobante,
                    porcent_venta, embarque_general, impresion_ecompleta, mostrar_direccion_completa, 
                    mostrar_tracking, mostrar_vendedor, datos_destinatario, origen_destino, nro_cuenta_BN, 
                    nro_cuenta_bancaria, telefono_empresa, termscond_pasaje, termscond_encomienda,
                    termscond_facturador, correo, logo_encomienda, tp_docu_encomienda, num_docu_encomienda, razon_social_encomienda,
                    ubigeo_encomienda, direccion_fiscal_encomienda, cdt_file_encomienda, cdt_clave_encomienda,
                    estado_encomienda, condicion_encomienda, user_sol_encomienda, pass_sol_encomienda,
                    cpe_id_encomienda, cpe_clave_encomienda, guia_id_encomienda, guia_clave_encomienda,
                    nro_cuenta_BN_encomienda
                ) VALUES(
                    :logo, 'RUC', :num_docu, :razon_social, :ubigeo, :direccion,
                    :cdt, :modo, :estado, :condicion, :user_sol, :pass_sol,
                    :cpe_id, :cpe_clave, :guia_id, :guia_clave, :envio_ose, :envio_guia, :link_ose, 
                    :m_terminales, :m_terminalesManifi, :l_terminales, :l_usuarios, :venta_web, :fr_comprobante,
                    :porcent_venta, :embarque_general, :impresion_ecompleta,:mostrar_direccion_completa,
                    :mostrar_tracking, :mostrar_vendedor, :datos_destinatario, :origen_destino, :nro_cuenta_BN,
                    :nro_cuenta_bancaria, :telefono_empresa, :termscond_pasaje, :termscond_encomienda,
                    :termscond_facturador, :correo, :logo_encomienda, 'RUC', :num_docu_encomienda, :razon_social_encomienda,
                    :ubigeo_encomienda, :direccion_fiscal_encomienda, :cdt_file_encomienda, :cdt_clave_encomienda,
                    :estado_encomienda, :condicion_encomienda, :user_sol_encomienda, :pass_sol_encomienda,
                    :cpe_id_encomienda, :cpe_clave_encomienda, :guia_id_encomienda, :guia_clave_encomienda,
                    :nro_cuenta_BN_encomienda
                )");
            }
            $query->bindParam(':logo', $logo);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':razon_social', $data["razon_social"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':cdt', $cdt);
            $query->bindParam(':cdt_clave', $data["cdt_clave"]);
            $query->bindParam(':modo', $data["modo"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->bindParam(':condicion', $data["condicion"]);
            $query->bindParam(':user_sol', $data["usuario_sol"]);
            $query->bindParam(':pass_sol', $data["pass_sol"]);
            $query->bindParam(':cpe_id', $data["cpe_id"]);
            $query->bindParam(':cpe_clave', $data["cpe_clave"]);
            $query->bindParam(':guia_id', $data["guia_id"]);
            $query->bindParam(':guia_clave', $data["guia_clave"]);
            $query->bindParam(':envio_ose', $ose);
            $query->bindParam(':envio_guia', $guia);
            $query->bindParam(':link_ose', $data['link_ose']);
            $query->bindParam(':m_terminales', $m_terminales);
            $query->bindParam(':m_terminalesManifi', $m_terminalesManifi);
            $query->bindParam(':l_terminales', $l_terminales);
            $query->bindParam(':l_usuarios', $l_usuarios);
            $query->bindParam(':venta_web', $venta_web);
            $query->bindParam(':fr_comprobante', $data['fr_comprobante']);
            $query->bindParam(':porcent_venta', $porcent_venta);
            $query->bindParam(':embarque_general', $embarque_general);
            $query->bindParam(':impresion_ecompleta', $impresion_ecompleta);
            $query->bindParam(':mostrar_direccion_completa', $mostrar_direccion_completa);
            $query->bindParam(':mostrar_tracking', $mostrar_tracking);
            $query->bindParam(':mostrar_vendedor', $mostrar_vendedor);
            $query->bindParam(':datos_destinatario', $datos_destinatario);
            $query->bindParam(':origen_destino', $origen_destino);
            $query->bindParam(':nro_cuenta_BN', $nro_cuenta_BN);
            $query->bindParam(':nro_cuenta_bancaria', $nro_cuenta_bancaria);  // NUEVO
            $query->bindParam(':telefono_empresa', $telefono_empresa);
            $query->bindParam(':termscond_pasaje', $data['termscond_pasaje']);
            $query->bindParam(':termscond_encomienda', $data['termscond_encomienda']);
            $query->bindParam(':termscond_facturador', $data['termscond_facturador']);
            $query->bindParam(':correo', $data['correo']);

            $query->bindParam(':logo_encomienda', $logo_encomienda);
            $query->bindParam(':num_docu_encomienda', $data['num_docu_encomienda']);
            $query->bindParam(':razon_social_encomienda', $data['razon_social_encomienda']);
            $query->bindParam(':ubigeo_encomienda', $data['ubigeo_encomienda']);
            $query->bindParam(':direccion_fiscal_encomienda', $data['direccion_encomienda']);
            $query->bindParam(':cdt_file_encomienda', $cdt_encomienda);
            $query->bindParam(':cdt_clave_encomienda', $data['cdt_clave_encomienda']);
            $query->bindParam(':estado_encomienda', $data['estado_encomienda']);
            $query->bindParam(':condicion_encomienda', $data['condicion_encomienda']);
            $query->bindParam(':user_sol_encomienda', $data['usuario_sol_encomienda']);
            $query->bindParam(':pass_sol_encomienda', $data['pass_sol_encomienda']);
            $query->bindParam(':cpe_id_encomienda', $data['cpe_id_encomienda']);
            $query->bindParam(':cpe_clave_encomienda', $data['cpe_clave_encomienda']);
            $query->bindParam(':guia_id_encomienda', $data['guia_id_encomienda']);
            $query->bindParam(':guia_clave_encomienda', $data['guia_clave_encomienda']);
            $query->bindParam(':nro_cuenta_BN_encomienda', $data['nro_cuenta_encomienda']);

            $query->execute();

            $query1 = $conn->prepare("SELECT * FROM configuracion");
            $query1->execute();
            $config = $query1->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                $query2 = $conn->prepare("UPDATE configuracion SET 
                    ruc_encomienda_separado = :ruc_encomienda_separado
                ");
            } else {
                $query2 = $conn->prepare("INSERT INTO configuracion 
                    (ruc_encomienda_separado) 
                    VALUES(:ruc_encomienda_separado)
                ");
            }
            $query2->bindParam(":ruc_encomienda_separado", $ruc_encomienda_separado);
            $query2->execute();

            return array('success' => true, "message" => "Registro actualizado con éxito");
        } catch (PDOException $e) {
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
}
