<?php Session::init(); ?>
<?php if (Session::get(NAME_SESSION) == true): ?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
		<meta name="author" content="TID">
		<meta name="keywords"
			content="seguimiento, SENATI, seguimiento senati, area seguimiento, seguimiento huancayo, template, responsive, css, sass, html, theme, front-end, ui kit, web">

		<link rel="icon" href="<?= URL_IMAGEN_ADMIN ?>icono.png">
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link rel="shortcut icon" href="img/icons/icon-48x48.png" />

		<title><?= NAME_BUSINESS ?></title>
		<link rel="manifest" href="<?= URL; ?>public/image/favicons/manifest.json?v=<?= date('ymd'); ?>">

		<link href="<?= URL; ?>public/plugins/bootstrap/bootstrap.min.css" rel="stylesheet">
		<link href="<?= URL; ?>public/css/app.min.css" rel="stylesheet">
		<link href="<?= URL; ?>public/css/aditional.css" rel="stylesheet">
		<!--LIBRERIAS-->
		<link href="<?= URL; ?>public/plugins/datatable/datatables.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/datatable/plugins/responsive/responsive.dataTables.min.css"
			rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/datatable/plugins/buttons/buttons.dataTables.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/datatable/plugins/select/dataTables.select.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/animate/animate.min.css" rel="stylesheet">
		<link href="<?= URL; ?>public/plugins/toast/jquery.toast.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/select2/select2.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/tom-select/tom-select.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/font-awesome-6.2.1/css/all.min.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/bootstrap-icons-1.10.2/font/bootstrap-icons.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/tippy/tippy-animation-scale.css" rel="stylesheet" />
		<link href="<?= URL; ?>public/plugins/font-awesome-6.2.1/pro/all.css" rel="stylesheet" />
		<link rel="stylesheet" href="<?= URL; ?>public/plugins/selectize/selectize.bootstrap5.min.css">
		<link rel="stylesheet" href="<?= URL; ?>public/plugins/selectize/selectize.min.css">
		<!-- include summernote css/js-->
		<link rel="stylesheet" href="<?= URL; ?>public/plugins/summernote/summernote-bs5.min.css">


		<?php $data_usuario = Session::get("data_usuario") ?>
		<?php $data_empresa = Session::get("data_empresa") ?>
		<?php $p_imprimir = Session::get("p_imprimir") ?>
		<?php $tipo_impresora = Session::get("tipo_impresora") ?>
		<?php $data_permisos = Session::get("data_permisos") ?>
		<?php $data_terminal = Session::get("data_terminal") ?>
		<?php $data_config = Session::get("data_config") ?>
		<?php date_default_timezone_set("America/Lima"); ?>
		<input type="hidden" id="url" value="<?= URL; ?>">
		<input type="hidden" id="igv_sesion" value="<?= $data_config['igv'] ?>">
		<input type="hidden" id="url_imagen" value="<?= URL_IMAGEN; ?>">
		<input type="hidden" id="id_usuario_sesion" value="<?= $data_usuario["id_usuario"]; ?>">
		<input type="hidden" id="tp_usuario_sesion" value="<?= $data_usuario["id_tp_usuario"]; ?>">
		<input type="hidden" id="p_imprimir" value="<?= $p_imprimir; ?>">
		<input type="hidden" id="p_selva" value="<?= $data_terminal['c_selva']; ?>">
		<input type="hidden" id="tipo_impresora" value="<?= $tipo_impresora; ?>">
		<input type="hidden" id="l_terminales_p" value="<?= $data_empresa['l_terminales']; ?>">
		<input type="hidden" id="l_usuarios_p" value="<?= $data_empresa['l_usuarios']; ?>">
		<input type="hidden" id="id_empresa_sesion" value="<?= $data_empresa['id_empresa']; ?>">
		<input type="hidden" id="uuid_ws_sesion" value="<?= $data_empresa['uuid_ws']; ?>">
		<input type="hidden" id="fullnanme_usuario_sesion"
			value="<?= $data_usuario["nombres"] . " " . $data_usuario["apellidos"]; ?>">
		<input type="hidden" id="ubigeo_terminal_sesion" value="<?= $data_terminal["ubigeo"] ?>">
		<input type="hidden" id="direccion_terminal_sesion" value="<?= $data_terminal["direccion_comercial"] ?>">
		<input type="hidden" id="id_terminal_sesion" value="<?= $data_terminal["id_terminal"] ?>">
		<input type="hidden" id="p_anularComprobante" value="<?= $data_permisos["p_anular_comprobante"] ?>">
		<input type="hidden" id="p_anularNotaventa" value="<?= $data_permisos["p_anular_notaventa"] ?>">
		<input type="hidden" id="p_enviarResumen" value="<?= $data_permisos["p_enviar_resumen"] ?>">
		<input type="hidden" id="p_posponerPasaje" value="<?= $data_permisos["p_posponer_pasaje"] ?>">
		<input type="hidden" id="p_cambiarAsiento" value="<?= $data_permisos["p_cambiar_asiento"] ?>">
		<input type="hidden" id="p_desbloquear_reservado" value="<?= $data_permisos["p_desbloquear_reservado"] ?>">
		<input type="hidden" id="p_cambiar_precio_asiento" value="<?= $data_permisos["permiso_m_precio"] ?>">
		<img id="barcode" class="d-none">
	</head>

	<body class="app">
		<script>
			(function() {
				const w = window.innerWidth;
				if (w >= 1200) {
					document.body.classList.add('sidebar-open');
				} else {
					document.body.classList.add('sidebar-closed');
				}
			})();
		</script>
		<header class="app-header fixed-top">
			<!-- Menu superior -->
			<div class="app-header-inner">
				<div class="container-fluid py-2">
					<div class="app-header-content">
						<div class="row justify-content-between align-items-center">
							<!-- btn hamburguesa -->
							<div class="col-auto  d-flex align-items-center gap-3">
								<a id="sidepanel-toggler" class="sidepanel-toggler d-inline-block"
									href="javascript:void(0)">
									<i class="bi bi-list fs-3 me-3"></i>
								</a>
								<div class="d-flex align-items-center gap-2">
									<div class="app-utility-item">
										<a href="<?= URL_PAGE_WEB ?>" target="_blank" title="Página web">
											<i class="bi bi-house-door fs-4"></i>
										</a>
									</div>
									<?php $modulos = $this->permisos_mods;
									?>
									<!-- Reportes -->
									<?php if (puedeVerModulo("p_reportes", $modulos, $data_permisos)): ?>
										<div class="app-utility-item d-none">
											<a href="<?= URL ?>reportes" title="Reportes">
												<i class="bi bi-journals fs-4"></i>
											</a>
										</div>
									<?php endif ?>
									<!-- Configuración -->
									<?php if (puedeVerModulo("p_config", $modulos, $data_permisos)): ?>
										<div class="app-utility-item">
											<a href="<?= URL ?>configuracion" title="Configuración">
												<i class="bi bi-gear fs-4"></i>
											</a>
										</div>
									<?php endif ?>
									<div class="app-utility-item">
										<a href="<?= URL ?>impresion" title="Impresion">
											<i class="fa-solid fa-print"></i>
										</a>
									</div>
									<?php if ($data_usuario["id_usuario"] == 1): ?>
										<div class="app-utility-item">
											<a href="<?= URL ?>config_modulos" title="Permisos Modulos"
												id="link_config_modulos">
												<i class="fa-solid fa-gears"></i>
											</a>
										</div>
									<?php endif ?>
								</div>
							</div>
							<div class="col-auto d-flex align-items-center gap-3">
								<!-- Información del usuario -->
								<label class="text-white mb-0 d-none d-md-block">
									<i class="fa-light fa-circle-user me-2 fs-6"></i>
									<?= $data_usuario["nombres"] . " " . $data_usuario["apellidos"] ?>
								</label>

								<!-- Terminal -->
								<label class="text-white mb-0 d-none d-md-block">
									<i class="bi bi-houses me-2 fs-6"></i>
									<?= $data_terminal["nombre"] ?>
								</label>

								<!-- Menú desplegable de usuario -->
								<div class="app-utility-item app-user-dropdown dropdown">
									<a class="dropdown-toggle" id="user-dropdown-toggle" data-bs-toggle="dropdown" href="#"
										role="button" aria-expanded="false">
										<i class="bi bi-person-circle fs-3"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-dropdown-toggle">
										<?php if (puedeVerModulo("p_config", $modulos, $data_permisos)): ?>
											<li><a class="dropdown-item" href="<?= URL ?>configuracion">Configuración</a>
											</li>
										<?php endif ?>
										<li>
											<hr class="dropdown-divider">
										</li>
										<li><a class="dropdown-item" href="<?= URL ?>dashboard/logout">Cerrar
												sesión</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- sidebar -->
			<div id="app-sidepanel" class="app-sidepanel">
				<div id="sidepanel-drop" class="sidepanel-drop"></div>
				<div class="sidepanel-inner d-flex flex-column">
					<!-- close sidebar -->
					<a href="javascript:void(0)" id="sidepanel-close"
						class="sidepanel-close text-secondary rounded justify-content-center"><i
							class="bi bi-x-lg fs-4"></i></a>
					<!-- logo sidebar -->
					<div class="app-branding">
						<a class="app-logo" href="<?= URL ?>"><span class="logo-text"><?= NAME_BUSINESS ?></span></a>
					</div>

					<nav id="app-nav-main" class="app-nav app-nav-main flex-grow-1">
						<ul class="app-menu list-unstyled accordion" id="menu-accordion">
							<li class="nav-item">
								<a class="nav-link d-flex align-content-center" id="link_dashboard" href="<?= URL ?>">
									<span class="nav-icon fs-5">
										<i class="bi bi-speedometer2"></i>
									</span>
									<span class="nav-link-text mt-1">Dashboard</span>
								</a>
							</li>

							<?php if (puedeVerModulo("p_pasaje", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_pasaje" href="<?= URL ?>pasaje">
										<span class="nav-icon fs-5">
											<i class="fa-light fa-loveseat"></i>
										</span>
										<span class="nav-link-text mt-1">Pasaje</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_encomienda", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_encomienda" href="<?= URL ?>encomienda">
										<span class="nav-icon fs-5">
											<i class="fa-light fa-boxes-packing"></i>
										</span>
										<span class="nav-link-text mt-1">Encomienda</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_cotizacion", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_cotizacion" href="<?= URL ?>cotizacion">
										<span class="nav-icon fs-5">
											<i class="fa-light fa-file-signature"></i>
										</span>
										<span class="nav-link-text mt-1">Cotizaciones</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_flete", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_flete" href="<?= URL ?>flete">
										<span class="nav-icon fs-5">
											<i class="bi bi-truck"></i>
										</span>
										<span class="nav-link-text mt-1">Flete</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_valorizacion", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_valorizacion" href="<?= URL ?>valorizacion">
										<span class="nav-icon fs-5">
											<i class="fas fa-calculator"></i>
										</span>
										<span class="nav-link-text mt-1">Valorización</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerMenu(["p_listado_comprobante", "p_listado_nota_venta", "p_guia_transportista"], $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#listado_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="fa-light fa-list"></i>
										</span>
										<span class="nav-link-text mt-1">Listado</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="listado_menu" class="collapse submenu submenu-1" data-bs-parent="#menu-accordion">
										<?php if (puedeVerModulo("p_listado_comprobante", $modulos, $data_permisos)): ?>
											<ul class="submenu-list list-unstyled">
												<li class="submenu-item"><a class="submenu-link" id="link_comprobantes"
														href="<?= URL ?>comprobantes">Comprobantes</a></li>
											</ul>
										<?php endif ?>
										<?php if (puedeVerModulo("p_listado_nota_venta", $modulos, $data_permisos)): ?>
											<ul class="submenu-list list-unstyled">
												<li class="submenu-item"><a class="submenu-link" id="link_nota_venta"
														href="<?= URL ?>nota_venta">Nota venta</a></li>
											</ul>
										<?php endif ?>
										<?php if (puedeVerModulo("p_guia_transportista", $modulos, $data_permisos)): ?>
											<ul class="submenu-list list-unstyled">
												<li class="submenu-item"><a class="submenu-link" id="link_guia_transportista"
														href="<?= URL ?>guia_transportista">Guias Transportista</a></li>
											</ul>
										<?php endif ?>
									</div>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_programacion", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_programacion" href="<?= URL ?>programacion">
										<span class="nav-icon fs-5">
											<i class="bi bi-clock"></i>
										</span>
										<span class="nav-link-text mt-1">Programación</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_programacion_salida", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_programacion_salida" href="<?= URL ?>programacion_salida">
										<span class="nav-icon fs-5">
											<i class="bi bi-clock"></i>
										</span>
										<span class="nav-link-text mt-1">Rutas</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_vehiculo", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_vehiculo" href="<?= URL ?>vehiculo">
										<span class="nav-icon fs-5">
											<i class="bi bi-car-front"></i>
										</span>
										<span class="nav-link-text mt-1">Vehículos</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_caja_chica", $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#empresa_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="bi bi-cart"></i>
										</span>
										<span class="nav-link-text mt-1">Caja chica</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="empresa_menu" class="collapse submenu submenu-1" data-bs-parent="#menu-accordion">
										<ul class="submenu-list list-unstyled">
											<li class="submenu-item"><a class="submenu-link" id="link_caja_chica"
													href="<?= URL ?>caja_chica">Caja chica</a></li>
										</ul>
										<ul class="submenu-list list-unstyled">
											<li class="submenu-item"><a class="submenu-link" id="link_egresos"
													href="<?= URL ?>egresos">Egresos</a></li>
										</ul>
									</div>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_facturador", $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#facturador_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="fa-solid fa-cash-register"></i>
										</span>
										<span class="nav-link-text mt-1">Facturador</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="facturador_menu" class="collapse submenu submenu-1"
										data-bs-parent="#menu-accordion">
										<ul class="submenu-list list-unstyled">
											<li class="submenu-item">
												<a class="submenu-link" id="link_facturador" href="<?= URL ?>facturador">
													Facturador
												</a>
											</li>
											<li class="submenu-item">
												<a class="submenu-link" id="link_listado_facturador"
													href="<?= URL ?>listado_facturador">
													Listado Facturador
												</a>
											</li>
										</ul>
									</div>
								</li>
							<?php endif ?>
							<?php if (puedeVerMenu(["p_comprobantesreport", "p_encomiendasreport", "p_viaje_report"], $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#reportes_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="bi bi-bar-chart"></i>
										</span>
										<span class="nav-link-text mt-1">Reportes</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="reportes_menu" class="collapse submenu submenu-1" data-bs-parent="#menu-accordion">
										<ul class="submenu-list list-unstyled">
											<?php if (puedeVerModulo("p_comprobantesreport", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_comprobantes_report"
														href="<?= URL ?>comprobantes_report">Ventas Generales</a></li>
											<?php endif ?>
											<?php if (puedeVerModulo("p_encomiendasreport", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_encomiendas_report"
														href="<?= URL ?>encomiendas_report">Encomiendas Generales</a></li>
											<?php endif ?>
											<?php if (puedeVerModulo("p_viaje_report", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_viaje_report"
														href="<?= URL ?>viaje_report">Reporte de Pasajeros</a></li>
											<?php endif ?>
										</ul>
									</div>
								</li>
							<?php endif ?>
							<!-- <li class="nav-item px-3">
								<hr>
							</li> -->
							<?php if (puedeVerMenu(["p_pasajero", "p_personal", "p_proveedor", "p_conductor"], $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#usuarios_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="bi bi-people"></i>
										</span>
										<span class="nav-link-text mt-1">Usuarios</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="usuarios_menu" class="collapse submenu submenu-1" data-bs-parent="#menu-accordion">
										<ul class="submenu-list list-unstyled">
											<?php if (puedeVerModulo("p_pasajero", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_pasajero"
														href="<?= URL ?>pasajero">Clientes</a></li>
											<?php endif ?>
											<?php if (puedeVerModulo("p_personal", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_personal"
														href="<?= URL ?>personal">Personal</a></li>
											<?php endif ?>
											<?php if (puedeVerModulo("p_proveedor", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_proveedor"
														href="<?= URL ?>proveedor">Proveedores</a></li>
											<?php endif ?>
											<?php if (puedeVerModulo("p_conductor", $modulos, $data_permisos)): ?>
												<li class="submenu-item"><a class="submenu-link" id="link_conductor"
														href="<?= URL ?>conductor">Conductores / Copiloto</a></li>
											<?php endif ?>
										</ul>
									</div>
								</li>
							<?php endif ?>

							<?php if (puedeVerModulo("p_terminal", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_terminal" href="<?= URL ?>terminal">
										<span class="nav-icon fs-5">
											<i class="bi bi-houses"></i>
										</span>
										<span class="nav-link-text mt-1">Terminal</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_almacen", $modulos, $data_permisos)): ?>
								<li class="nav-item has-submenu">
									<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse"
										data-bs-target="#inventario_menu" aria-expanded="false" aria-controls="submenu-1">
										<span class="nav-icon fs-5">
											<i class="bi bi-archive"></i>
										</span>
										<span class="nav-link-text mt-1">Inventario</span>
										<span class="submenu-arrow">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
												fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
											</svg>
										</span>
									</a>
									<div id="inventario_menu" class="collapse submenu submenu-1"
										data-bs-parent="#menu-accordion">
										<ul class="submenu-list list-unstyled">
											<li class="submenu-item"><a class="submenu-link" id="link_almacen"
													href="<?= URL ?>almacen">Almacen</a></li>
										</ul>
									</div>
								</li>
							<?php endif ?>

							<?php if (puedeVerModulo("p_empresa", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_empresa" href="<?= URL ?>empresa">
										<span class="nav-icon fs-5">
											<i class="bi bi-buildings"></i>
										</span>
										<span class="nav-link-text mt-1">Empresa</span>
									</a>
								</li>
							<?php endif ?>

							<?php if (puedeVerModulo("p_config", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_config" href="<?= URL ?>configuracion">
										<span class="nav-icon fs-5">
											<i class="bi bi-gear fs-4"></i>
										</span>
										<span class="nav-link-text mt-1">Configuración</span>
									</a>
								</li>
							<?php endif ?>

							<?php if (puedeVerModulo("p_producto_encomienda", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_ctg_encomienda" href="<?= URL ?>categoria_encomienda">
										<span class="nav-icon fs-5">
											<i class="bi bi-bookmarks"></i>
										</span>
										<span class="nav-link-text mt-1">Producto encomienda</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_tp_servicio_pasaje", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_tp_servicio_pasaje" href="<?= URL ?>tp_servicio_pasaje">
										<span class="nav-icon fs-5">
											<i class="bi bi-tags"></i>
										</span>
										<span class="nav-link-text mt-1">Tipo servicio pasaje</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_serie", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_serie" href="<?= URL ?>serie">
										<span class="nav-icon fs-5">
											<i class="fa-light fa-ballot-check"></i>
										</span>
										<span class="nav-link-text mt-1">Serie</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_contra_maestra", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_contra_maestra" href="<?= URL ?>contra_maestra">
										<span class="nav-icon fs-5">
											<i class="bi bi-asterisk"></i>
										</span>
										<span class="nav-link-text mt-1">Contraseña Maestra</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_medio_pago", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_medio_pago" href="<?= URL ?>medio_pago">
										<span class="nav-icon fs-5">
											<i class="bi bi-credit-card"></i>
										</span>
										<span class="nav-link-text mt-1">Medio de pago</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_notas", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_notas" href="<?= URL ?>notas">
										<span class="nav-icon fs-5">
											<i class="bi bi-card-text"></i>
										</span>
										<span class="nav-link-text mt-1">Notas Crédito & Débito</span>
									</a>
								</li>
							<?php endif ?>
							<?php if (puedeVerModulo("p_cupon", $modulos, $data_permisos)): ?>
								<li class="nav-item">
									<a class="nav-link" id="link_cupon" href="<?= URL ?>cupon">
										<span class="nav-icon fs-5">
											<i class="fa-solid fa-hand-holding-dollar"></i>
										</span>
										<span class="nav-link-text mt-1">Cupones</span>
									</a>
								</li>
							<?php endif ?>
						</ul>
					</nav>
					<!-- sidebar footer -->
					<div class="app-sidepanel-footer">
						<nav class="app-nav app-nav-footer">
							<ul class="app-menu footer-menu list-unstyled">
								<li class="nav-item">
									<a class="nav-link" href="<?= URL ?>dashboard/logout">
										<span class="nav-icon fs-5">
											<i class="bi bi-box-arrow-left"></i>
										</span>
										<span class="nav-link-text mt-1">Cerrar sesión</span>
									</a>
								</li>
							</ul>
						</nav>
					</div>
				</div>
			</div>
		</header>

		<!-- Inicio de loader -->
		<div id="loader" class="d-none">
			<svg class="pl" viewBox="0 0 128 128" width="128px" height="128px" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="pl-grad" x1="0" y1="0" x2="0" y2="1">
						<stop offset="0%" stop-color="hsl(193,90%,55%)"></stop>
						<stop offset="100%" stop-color="hsl(223,90%,55%)"></stop>
					</linearGradient>
				</defs>
				<circle class="pl__ring" r="56" cx="64" cy="64" fill="none" stroke="hsla(0,10%,10%,0.1)" stroke-width="16"
					stroke-linecap="round"></circle>
				<path class="pl__worm"
					d="M92,15.492S78.194,4.967,66.743,16.887c-17.231,17.938-28.26,96.974-28.26,96.974L119.85,59.892l-99-31.588,57.528,89.832L97.8,19.349,13.636,88.51l89.012,16.015S81.908,38.332,66.1,22.337C50.114,6.156,36,15.492,36,15.492a56,56,0,1,0,56,0Z"
					fill="none" stroke="url(#pl-grad)" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"
					stroke-dasharray="44 1111" stroke-dashoffset="10"></path>
			</svg>
		</div>
		<!-- Fin de loader -->
		<?php
		$version = "1.1.1";
		?>
		<?php if (isset($this->css)): ?>
			<?php foreach ($this->css as $css): ?>
				<?php
				$arry = explode(".", $css);
				MINIFY_CSS ? $arry[0] = $arry[0] . '.min.' : $arry[0] = $arry[0] . '.';
				$name_script = implode("", $arry);
				?>
				<link rel="stylesheet" href="<?= URL; ?>views/<?= $name_script ?>?v=<?= $version; ?>">
			<?php endforeach ?>
		<?php endif ?>
	<?php else: ?>
		<?php $url_login = URL . "login" ?>
		<?= "<script> window.location.href='$url_login' </script>" ?>
	<?php endif ?>