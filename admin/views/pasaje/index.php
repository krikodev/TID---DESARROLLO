<div class="app-wrapper">
    <div class="app-content pt-0 p-md-2 p-lg-3">
        <div class="container-fluid">
            <div class="row g-2 align-items-stretch">
                <div class="col-md-3 mb-1 ps-0 pe-2">
                    <input type="hidden" name="id_tp_usuario_sesion" id="id_tp_usuario_sesion"
                        value="<?= $this->tp_usuario_sesion ?>">
                    <input type="hidden" id="id_venta_postponer" name="id_venta_postponer">
                    <input type="hidden" id="liquidacion_act" name="liquidacion_act">
                    <?php $caja = !empty($this->caja_userSesion["message"][0]["id_caja_chica"]) ? $this->caja_userSesion["message"][0]["id_caja_chica"] : 0; ?>
                    <input type="hidden" id="id_caja" name="id_caja" value="<?= $caja ?>">

                    <div class="shadow-sm p-2 bg-white rounded h-100">
                        <form id="form_filtro" method="post">
                            <div class="row gx-2">
                                <div class="col-6 col-lg-6 my-1" id="div_parentTerminalOrigen">
                                    <label>Terminal Origen</label>
                                    <select class="form-select" id="terminal_origen" name="terminal_origen" required>
                                        <?php if ($this->terminal["success"]): ?>
                                            <option value="">Seleccione</option>
                                            <?php foreach ($this->terminal["message"] as $terminal): ?>
                                                <?php $text = $terminal["nombre"] ?>
                                                <option value="<?= $terminal["id_terminal"] ?>"><?= $text ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-6 my-1" id="div_parentTerminalDestino">
                                    <label>Terminal Destino</label>
                                    <select class="form-select" id="terminal_destino" name="terminal_destino">
                                        <option value="">Seleccione</option>
                                        <?php if ($this->terminal["success"]): ?>
                                            <?php foreach ($this->terminal["message"] as $terminal): ?>
                                                <?php $text = $terminal["nombre"] ?>
                                                <option value="<?= $terminal["id_terminal"] ?>"><?= $text ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-6 my-1">
                                    <label>Fecha Inicio</label>
                                    <input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio"
                                        min="<?= date('Y-m-d', strtotime('-129 days')); ?>"
                                        value="<?= date("Y-m-d") ?>">
                                </div>
                                <div class="col-6 col-lg-6 my-1">
                                    <label>Fecha Fin</label>
                                    <input class="form-control" type="date" name="fecha_fin" id="fecha_fin"
                                        value="<?= date("Y-m-d") ?>">
                                </div>
                                <div class="col-md-6 col-6 col-lg-6 my-1">
                                    <select class="form-select" name="estado_programacion" id="estado_programacion">
                                        <option value="1">Libres</option>
                                        <option value="0">Liquidados</option>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-6 my-1">
                                    <div class="d-flex gap-1 justify-content-between">
                                        <button class="btn btnSearch btn-sm" type="submit" id="button_searchFilter">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button class="btn btnSearch btn-sm d-none" type="button"
                                            id="button_loadSearchFilter" disabled>
                                            <span class="spinner-border spinner-border-sm"></span>
                                        </button>
                                        <button class="btn btn-warning btn-sm" type="reset" id="c_limpiar">
                                            <i class="fa-solid fa-broom"></i>
                                        </button>
                                        <a class="btn btnProgra btn-sm" href="<?= URL ?>programacion" target="_blank">
                                            <i class="bi bi-patch-plus-fill"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-4 mb-1 ps-0 pe-2">
                    <div class="shadow-sm p-2 bg-white rounded h-100">
                        <table class="table display responsive w-100 p-0 position-relative" style="width: 100%;"
                            cellspacing="0" id="table_programacion">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Accion</th>
                                    <th>Destino</th>
                                    <th>Vehículo</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div id="paginacion_programacion"></div>
                    </div>
                </div>
                <div class="col-md-5 mb-1 ps-0 pe-0">
                    <div class="shadow-sm p-1 bg-white rounded h-100">
                        <!-- Pestañas para cambiar entre vistas -->
                        <div class="resumen-tabs mt-0">
                            <ul class="nav nav-pills justify-content-center mb-0" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link resumen-tab-btn active" id="tab_vendedores_btn"
                                        data-bs-toggle="pill" data-bs-target="#pills-vendedores" role="tab"
                                        aria-controls="pills-vendedores" aria-selected="true" type="button">
                                        <i class="bi bi-people-fill"></i> Vendedores
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link resumen-tab-btn" id="tab_destinos_btn" data-bs-toggle="pill"
                                        data-bs-target="#pills-destinos" role="tab" aria-controls="pills-destinos"
                                        aria-selected="false" type="button">
                                        <i class="bi bi-geo-alt-fill"></i> Destinos
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content" id="pills-tabContent">
                            <div id="resumen_vendedores_container" class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="tabla_resumen_vendedores">
                                        <thead>
                                            <tr>
                                                <th>Vendedor</th>
                                                <th title="Vendidos">Vend.</th>
                                                <th title="Reservados">Res.</th>
                                                <th title="Pospuestos">Posp.</th>
                                                <th title="Nota de Venta">N.V.</th>
                                                <th title="Boleta">Bol.</th>
                                                <th title="Factura">Fac.</th>
                                                <th>Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuerpo_tabla_vendedores">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>TOTALES:</td>
                                                <td id="total_vendidos">0</td>
                                                <td id="total_reservados">0</td>
                                                <td id="total_pospuestos">0</td>
                                                <td id="total_nota_venta">0</td>
                                                <td id="total_boleta">0</td>
                                                <td id="total_factura">0</td>
                                                <td id="total_monto">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div id="resumen_destinos_container" class="table-container" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="tabla_resumen_destinos">
                                        <thead>
                                            <tr>
                                                <th>Destino</th>
                                                <th title="Pasajes Vendidos">Vendidos</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuerpo_tabla_destinos">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>TOTAL GENERAL:</td>
                                                <td id="destinos_total_general">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-1 pt-0 ps-0 pe-0">
                    <div class="shadow-sm p-2 bg-white rounded">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center flex-wrap">

                                <!-- IZQUIERDA (asientos) -->
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <i class="fa-light fa-loveseat fs-3 asiento_normal"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_premium"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_reservado fw-bolder"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_vendido fw-bolder"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_seleccionado fw-bolder"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_postponer fw-bolder"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_proceso_web fw-bolder"></i>
                                    <i class="fa-light fa-loveseat fs-3 asiento_venta_web fw-bolder"></i>
                                </div>

                                <!-- DERECHA (botones) -->
                                <div class="d-flex gap-2 ms-auto flex-wrap">
                                    <?php $permiso_manifiesto_sunat = $this->config_pasaje['success'] && $this->config_pasaje['message']['manifiesto_sunat'] ? $this->config_pasaje['message']['manifiesto_sunat'] : 0; ?>

                                    <input type="hidden" name="permiso_manifiesto_sunat" id="permiso_manifiesto_sunat"
                                        value="<?= $permiso_manifiesto_sunat ?>">
                                    <a class="btn text-white d-none"
                                        style="background: linear-gradient(0deg, rgba(34,195,134,1) 25%, rgba(45,253,240,1) 100%);"
                                        id="btn_cambiar_carro">Cambiar vehiculo?</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #e82e7c"
                                        id="btn_manifiesto">Manifiesto</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #662ee8"
                                        id="btn_manifiesto_sunat">Manifiesto SUNAT</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #17a2b8"
                                        id="btn_control_pasajero">Control Pasajero</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #3772ff"
                                        id="btn_liquidacionSeguimientoUser">Segui. Usuario</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #e56b6f"
                                        id="btn_liquidacionSeguimiento">Segui. General</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #ffbe0b"
                                        id="btn_liquidacion">Liquidación</a>

                                    <a target="_blank" class="btn text-white d-none" style="background-color: #55c2dd"
                                        id="btn_liquidacion_usuario">Liquidación</a>

                                    <a target="_blank" class="btn text-white js-show-cart d-none"
                                        style="background-color: #47D1FF" id="btn_asientos_reserva">Asientos / R</a>

                                </div>

                            </div>
                        </div>
                        <div class="col-lg-12 py-3 d-none position-relative" id="superParentVehiculo">
                            <div class="col-lg-12 py-3 d-flex justify-content-center">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9 mb-1 pt-0 ps-0 pe-2">
                    <div class="shadow-sm p-2 bg-white rounded">
                        <div class="d-flex justify-content-center align-items-center text-center py-4"
                            style="min-height: 120px; border: 1px dashed #dee2e6; border-radius: 8px; background-color: #f8f9fa;"
                            id="div_sin_asientos">

                            <div>
                                <i class="bi bi-cursor-fill fs-3 text-secondary mb-1"></i>
                                <p class="mb-0 text-secondary fw-semibold">
                                    Seleccione un asiento para continuar
                                </p>
                            </div>

                        </div>
                        <form id="form_ventaPasaje" method="post" class="needs-validation" novalidate>
                            <div class="row g-2 my-2 d-none" id="div_formVentaPasaje">
                                <input type="hidden" id="id_ventaPasaje" name="id_ventaPasaje">
                                <input type="hidden" name="id_asiento_selected" id="id_asiento_selected">
                                <div class="row">
                                    <div
                                        class="col-md-1 d-flex flex-column align-items-center justify-content-center text-center">

                                        <span class="badge bg-success text-capitalize mb-2" style="font-size: 12px;">
                                            Piso <span id="num_piso_venta"></span>
                                        </span>

                                        <label class="text-secondary fs-4 mb-0" id="num_asiento"></label>

                                    </div>
                                    <div class="col-md-3 my-1" id="div_parentTpComprobante">
                                        <label>Tipo de comprobante<span class="requiredField">*</span></label>
                                        <select class="form-select" id="tp_comprobante" name="tp_comprobante" required>
                                            <option value="2">NOTA DE VENTA</option>
                                            <option value="3">BOLETA DE VENTA ELECTRÓNICO</option>
                                            <option value="1">FACTURA ELECTRÓNICA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 my-1" id="div_parentSerie">
                                        <label>Serie<span class="requiredField">*</span></label>
                                        <select class="form-control" name="serie_venta" id="serie_venta"></select>
                                    </div>
                                    <div class="col-md-2 my-1">
                                        <label>Estado<span class="requiredField">*</span></label>
                                        <select class="form-select" name="estado_venta" id="estado_venta" required>
                                            <option value="VENDIDO">VENDIDO</option>
                                            <option value="RESERVADO">RESERVADO</option>
                                            <option value="VENTA_WEB">VENTA WEB</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 my-1" id="">
                                        <label>Cambiar Destino?</label>
                                        <select class="form-select" id="destino_pasajero" name="destino_pasajero">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                    <!-- cupon -->
                                    <div class="col-md-2 my-1">
                                        <label>Cupón?<span class="optionalField me-2">*</span></label>
                                        <input type="hidden" id="id_cupon" name="id_cupon">
                                        <input type="hidden" id="monto_descuento" name="monto_descuento" value="0.00">
                                        <input type="text" class="form-control" autocomplete="off" id="cupon"
                                            name="cupon" required>
                                    </div>
                                    <div class="col-md-12 my-1" id="div_parentCliente">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Cliente<span class="requiredField me-1">*</span><a
                                                        href="javascript:void(0)" class="open_modal_cliente">[+
                                                        Nuevo]</a></label>
                                            </div>
                                            <!-- Inputs hidden del pospuesto -->
                                            <input type="hidden" id="pospuesto_id" name="pospuesto_id" value="">
                                            <input type="hidden" id="pospuesto_id_venta" name="pospuesto_id_venta"
                                                value="">
                                            <input type="hidden" id="pospuesto_saldo" name="pospuesto_saldo" value="">
                                            <input type="hidden" id="precio_original" name="precio_original" value="">
                                            <!-- para restaurar si desmarca -->
                                            <div class="col-md-3">
                                                <label for="c_nino" style="cursor: pointer;">Viaja con niñ@ menor a 5
                                                    años?
                                                    <div class="checkbox-wrapper">
                                                        <input type="checkbox" name="c_nino" id="c_nino"
                                                            class="custom-checkbox">
                                                        <span class="checkbox-custom"></span>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="obs_pasajero">Observacion Pasajero<span
                                                        class="requiredField me-2">*</span></label>
                                            </div>
                                            <!-- Tu checkbox existente, solo agregar el label del saldo -->
                                            <div class="col-md-4">
                                                <label for="aplicar_pospuesto" style="cursor: pointer;" class="d-none"
                                                    id="div_aplicar_pospuesto">
                                                    Aplicar saldo de pasaje pospuesto
                                                    <span id="label_saldo_favor"
                                                        class="text-success fw-bold ms-1"></span>
                                                    <div class="checkbox-wrapper">
                                                        <input type="checkbox" name="aplicar_pospuesto"
                                                            id="aplicar_pospuesto" class="custom-checkbox">
                                                        <span class="checkbox-custom"></span>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- Input de monto parcial, oculto por defecto -->
                                            <div class="col-md-4 mt-1 d-none" id="section_monto_aplicar">
                                                <label>Monto a aplicar del saldo:</label>
                                                <input type="number" id="monto_aplicar_pospuesto"
                                                    class="form-control form-control-sm" step="0.01" min="0.01">
                                                <small class="text-muted">Máximo aplicable: <span
                                                        id="max_monto_aplicar"></span></small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" id="cliente" name="cliente"
                                                        autocomplete="new-password" minlength="8"
                                                        placeholder="Escriba al cliente....">
                                                    <button type="button" class="btn btnSearch"
                                                        id="buscar_cliente">Buscar</button>
                                                    <button class="btn btnSearch d-none" type="button"
                                                        id="loader_buscar" disabled>
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                    </button>
                                                    <input type="hidden" id="cliente_id" name="cliente_id">
                                                    <input type="hidden" name="tipo_documento_client"
                                                        id="tipo_documento_client">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control" type="text" id="obs_pasajero"
                                                    name="obs_pasajero">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-1 d-none" id="div_parentPasajero">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Pasajero<span class="requiredField me-2">*</span>
                                                    <a href="javascript:void(0)" class="open_modal_pasajero"
                                                        style="margin-right: 10px;">[+ Nuevo]</a>
                                                </label>
                                                <div class="input-group">
                                                    <input class="form-control" type="text" id="pasajero"
                                                        autocomplete="new-password" name="pasajero"
                                                        placeholder="Escriba al pasajero....">
                                                    <button type="button" class="btn btnSearch"
                                                        id="buscar_pasajero">Buscar</button>
                                                    <button class="btn btnSearch d-none" type="button"
                                                        id="loader_buscar_p" disabled="">
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                    </button>
                                                    <input type="hidden" id="pasajero_id" name="pasajero_id">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-1 d-none" id="div_parentNino">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Niñ@<span class="requiredField me-2">*</span><a
                                                        href="javascript:void(0)" class="open_modal_nino">[+
                                                        Nuevo]</a></label>
                                                <div class="input-group">
                                                    <input class="form-control" autocomplete="new-password" type="text"
                                                        id="nino" name="nino">
                                                    <button type="button" class="btn btnSearch"
                                                        id="buscar_nino">Buscar</button>
                                                    <button class="btn btnSearch d-none" type="button"
                                                        id="loader_buscar_n" disabled="">
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                    </button>
                                                    <input type="hidden" id="nino_id" name="nino_id">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="desc_motivo">Motivo<span
                                                        class="requiredField me-2">*</span></label>
                                                <input class="form-control" type="text" id="desc_motivo"
                                                    name="desc_motivo">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 my-1">
                                        <label>Precio<span class="requiredField">*</span></label>
                                        <label for="devolucion_restante" style="cursor: pointer;" class="d-none"
                                            id="div_devolucion_restante">
                                            Devolver
                                            <span id="label_monto_devolucion" class="text-success fw-bold ms-1"></span>
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" name="devolucion_restante"
                                                    id="devolucion_restante" class="custom-checkbox">
                                                <span class="checkbox-custom"></span>
                                            </div>
                                        </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text py-0">S/</span>
                                            <input type="text" class="form-control" name="precio_venta"
                                                id="precio_venta">
                                        </div>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Referencia</label>
                                        <input type="text" class="form-control" name="referencia" id="referencia">
                                    </div>
                                    <div class="col-md-12 my-1 d-none" id="parentCodOperacion">
                                        <label>Código de operación<span class="requiredField me-2">*</span></label>
                                        <input type="text" class="form-control" autocomplete="off" id="cod_operacion"
                                            name="cod_operacion" required>
                                    </div>
                                    <div class="col-md-6 my-1 d-none">
                                        <label>Correo electrónico<span class="requiredField me-2">*</span></label>
                                        <input type="text" class="form-control" autocomplete="off" id="email_cliente"
                                            name="email_cliente" required>
                                    </div>
                                    <div class="col-md-6 my-1 d-none">
                                        <label>Celular<span class="requiredField me-2">*</span></label>
                                        <input type="text" class="form-control" autocomplete="off" id="celular_cliente"
                                            name="celular_cliente" required>
                                    </div>
                                    <div class="col-md-3 my-1" id="div_parentMedioPago">
                                        <label>M. Pago<span class="requiredField">*</span></label>
                                        <select id="medio_pago" name="medio_pago" required>
                                            <option value="">Seleccione</option>
                                            <?php if ($this->medio_pago["success"]): ?>
                                                <?php for ($i = 0; $i < count($this->medio_pago["message"]); $i++): ?>
                                                    <?php $text = $this->medio_pago["message"][$i]["descripcion"] ?>
                                                    <option
                                                        value="<?php echo $this->medio_pago["message"][$i]["id_medio_pago"] ?>">
                                                        <?php echo $text ?>
                                                    </option>
                                                <?php endfor ?>
                                            <?php endif ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 my-1 d-none" id="div_parentDestinoCj">
                                        <label>Destino <span class="fw-bolder">CAJA CHICA</span><span
                                                class="requiredField">*</span></label>
                                        <?php if ($this->caja_userSesion["success"]): ?>
                                            <input type="text" class="form-control" name="id_caja_chica" id="id_caja_chica"
                                                autocomplete="off"
                                                value="<?php echo $this->caja_userSesion["message"][0]["referencia"]; ?>"
                                                disabled>
                                            <input type="hidden" name="destino" id="destino"
                                                value="<?php echo $this->caja_userSesion["message"][0]["id_caja_chica"]; ?>">
                                        <?php endif ?>
                                    </div>
                                    <div class="col-md-2 my-1">
                                        <label class="invisible">Botón</label>
                                        <button type="submit" class="btn btnSave w-100" id="button_save">Vender</button>
                                        <button class="btn btnSave d-none w-100" type="button" id="button_loadSave"
                                            disabled>
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                            Vendiendo...
                                        </button>
                                    </div>
                                    <div class="accordion" id="accordionEgresos">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingEgresos">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#egresos" aria-expanded="true"
                                                    aria-controls="egresos">
                                                    <i class="fa fa-file-invoice me-2"></i> Egresos
                                                </button>
                                            </h2>
                                            <div class="col-md-12">
                                                <div id="egresos" class="accordion-collapse collapse"
                                                    data-bs-parent="#accordionEgresos">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-2 my-1">
                                                                <label>T. comprobante<span
                                                                        class="requiredField me-2">*</span></label>
                                                                <select class="form-select" name="tp_comprobante_egreso"
                                                                    id="tp_comprobante_egreso">
                                                                    <option value="FACTURA">FACTURA</option>
                                                                    <option value="BOLETA">BOLETA</option>
                                                                    <option value="OTRO">OTRO</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-2 my-1">
                                                                <label>Serie<span
                                                                        class="requiredField me-2">*</span></label>
                                                                <input type='text' class='form-control'
                                                                    id="serie_egreso" name="serie_egreso"
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                            </div>
                                                            <div class="col-lg-2 my-1">
                                                                <label>Correlativo<span
                                                                        class="requiredField me-2">*</span></label>
                                                                <input type='text' class='form-control'
                                                                    id="correlativo_egreso" name="correlativo_egreso"
                                                                    onkeypress="return controlTag(event);">
                                                            </div>
                                                            <div class="col-lg-2 my-1">
                                                                <label for="monto_egreso">Monto<span
                                                                        class="requiredField me-2">*</span></label>
                                                                <input class="form-control" type="number"
                                                                    name="monto_egreso" id="monto_egreso" min="0"
                                                                    step="0.01">
                                                            </div>
                                                            <div class="col-lg-3 my-1">
                                                                <label>Concepto<span
                                                                        class="requiredField me-2">*</span></label>
                                                                <input class="form-control" type="text"
                                                                    name="concepto_egreso" id="concepto_egreso">
                                                            </div>
                                                            <div class="col-lg-1 my-1 d-flex align-items-end">
                                                                <button type="button" class="btn btnColorViolet"
                                                                    id="add_egreso_venta">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </div>
                                                            <div class="col-md-12 my-2">
                                                                <table class="table todo_list w-100" cellspacing="0"
                                                                    id="table_egresos_venta">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Tipo comprobante</th>
                                                                            <th>Serie</th>
                                                                            <th>Correlativo</th>
                                                                            <th>Monto</th>
                                                                            <th>Concepto</th>
                                                                            <th>Acción</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-3 mb-1 pt-0 ps-0 pe-0 d-none" id="reserva_grupal">
                    <div class="shadow-sm p-2 bg-white rounded">
                        <div class="col-md-12">
                            <div class="text-center">
                                <span class="fw-bold">ASIENTOS SELECCIONADOS</span>
                            </div>
                            <!-- Contenedor con scroll condicional -->
                            <div class="asientos-seleccionados-wrapper" id="asientosSeleccionadosWrapper">
                                <div class="asientos-seleccionados-container">
                                    <table class="table display responsive w-100 p-0 position-relative"
                                        style="width: 100%;" cellspacing="0" id="table_asientos">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12"
                            style="text-align: center; align-items: center; justify-content: center; display: flex;">
                            <div class="col-md-12">
                                <a class="btn text-white px-4" style="background-color: #39afcc" id="btn_reserva_todos">
                                    RESERVAR TODOS?
                                </a>
                            </div>
                            <div class="col-md-6 d-none">
                                <a class="btn text-white px-4" style="background-color: #547179" id="btn_liberar_todos">
                                    LIBERAR TODOS?
                                </a>
                            </div>
                        </div>
                        <div class="col-md-10 col-lg-8 mx-auto py-4">
                            <div class="mb-3">
                                <label for="asientos_reserva" class="form-label">
                                    Ingrese los números de asientos
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="asientos_reserva" class="form-control" name="asientos_reserva"
                                    onkeypress="return controlTag(event);" placeholder="Ej: 1, 2, 3, 4">
                            </div>

                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a class="btn text-white px-4 d-none" style="background-color: #3D5FF2"
                                    id="btn_reserva_grupal">
                                    RESERVAR
                                </a>
                                <a class="btn text-white px-4" style="background-color: #1f2937"
                                    id="btn_liberar_reserva">
                                    LIBERAR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const URL_PAGE_WEB = "<?php echo URL_PAGE_WEB; ?>";
    </script>
</div>