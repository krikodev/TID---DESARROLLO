<?php

class View
{
	public $pathScript = null;
	public $url = null;
	public
		$js, $css, $php, $tp_comprobante, $ubigeo, $paises, $tp_usuario, $tp_docu,
		$terminal, $almacen, $terminal_origen, $empresa, $tp_user_interno, $cliente, $pasajero,
		$tp_user_externo, $vehiculo, $conductor, $personal, $tp_servicio_pasaje, $forma_pago, $medio_pago, $producto, $unidad_medida, $caja_userSesion, $comision_empresa,
		$terminal_destino, $terminal_activo, $ubigeo_terminal, $impresora_usuario, $tp_usuario_sesion, $productos, $afectaciones, $tp_operacion_venta,
		$tp_medio_pago, $config_vehiculo, $rucs, $config_encomienda, $config_pasaje, $config_general, $unidad_servicio, $bancos, $permisos_mods, $modulos,
		$series_manifiesto, $cotizaciones, $proveedor, $monedas;

	function __construct() {}

	public function render($name, $noInclude = false)
	{
		if ($noInclude == true) {
			require 'views/' . $name . '.php';
		} else {
			require 'views/templates/header.php';
			require 'views/' . $name . '.php';
			require 'views/templates/modal.php';
			require 'views/templates/footer.php';
		}
	}
}
