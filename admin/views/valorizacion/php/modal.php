<div class="modal fade" id="modal_valorizacion" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Valorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mv_id_valorizacion" name="id_valorizacion">

                <div class="row g-3 mb-3">
                    <div class="col-md-3 my-1">
                        <label class="form-label fw-bold">Serie<span class="requiredField">*</span></label>
                        <select class="form-select" id="mv_serie">
                        </select>
                    </div>
                    <!-- Cliente -->
                    <div class="col-md-9 my-1 row" id="div_parentCliente">
                        <label>Cliente<span class="requiredField me-2">*</span>
                            <a href="javascript:void(0)" class="open_modal_cliente d-none"></a>
                        </label>
                        <div class="col-md-12">
                            <input class="form-control" type="text" id="cliente" name="cliente"
                                autocomplete="new-password" placeholder="Busca por DNI | Nombre | Apellido">
                            <button type="button" class="btn btnS d-none" id="buscar_cliente">Buscar</button>
                            <button class="btn btnS d-none" type="button" id="loader_buscar" disabled="">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <input type="hidden" id="cliente_id" name="cliente_id">
                        </div>
                    </div>

                </div>

                <hr>

                <!-- Buscador de documentos -->
                <div id="mv_bloque_documentos" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Documentos disponibles</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="mv_buscar_documentos">
                            <i class="fas fa-sync"></i> Cargar documentos
                        </button>
                    </div>
                    <div class="table-responsive mb-3" style="max-height:220px; overflow-y:auto;">
                        <table class="table table-sm table-hover" id="mv_tabla_disponibles">
                            <thead>
                                <tr>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Vehiculo</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="text-end mb-4">
                        <button type="button" class="btn btn-sm btn-primary text-white" id="mv_agregar_seleccionados">
                            Agregar seleccionados <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>

                    <h6 class="mb-2">Documentos agregados</h6>
                    <div class="table-responsive mb-2" style="max-height:220px; overflow-y:auto;">
                        <table class="table table-sm" id="mv_tabla_detalle">
                            <thead>
                                <tr>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Vehiculo</th>
                                    <th>Monto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Observación</label>
                        <textarea class="form-control" id="mv_observacion" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between"><span>Subtotal:</span><strong id="mv_subtotal">S/
                                0.00</strong></div>
                        <div class="d-flex justify-content-between"><span>IGV (18%):</span><strong id="mv_igv">S/
                                0.00</strong></div>
                        <div class="d-flex justify-content-between fs-5"><span>Total:</span><strong id="mv_total">S/
                                0.00</strong></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary text-white" id="mv_guardar">Guardar Borrador</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo">NUEVO CLIENTE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_cliente" class="needs-validation" novalidate>
                    <input type="hidden" id="operacion" name="operacion">
                    <ul class="nav nav-pills mb-3" id="pillsTab_contrato" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_general" data-bs-toggle="pill"
                                data-bs-target="#general" type="button" role="tab" aria-controls="tab_general"
                                aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_contacto" data-bs-toggle="pill" data-bs-target="#contacto"
                                type="button" role="tab" aria-controls="tab_contacto"
                                aria-selected="false">Contacto</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabs_contrato">
                        <!-- GENERAL -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel"
                            aria-labelledby="tab_general" tabindex="0">
                            <div class="row g-2 my-2">
                                <div class="col-md-12 row">
                                    <input type="hidden" id="id_pasajero" name="id_pasajero">
                                    <div class="col-md-4">
                                        <label>Tipo de documento<span class="requiredField">*</span></label>
                                        <select class="form-select" name="tp_docu" id="tp_docu">
                                            <option value="1">DNI</option>
                                            <option value="4">Carnet extranjeria</option>
                                            <option value="6">RUC</option>
                                            <option value="7">Pasaporte</option>
                                            <option value="0">Otros documentos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>N° Documento<span class="requiredField">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="num_docu" name="num_docu"
                                                autocomplete="off" required minlength="8" maxlength="8">
                                            <button type="button" class="btn btnSearch"
                                                id="button_search">BUSCAR</button>
                                            <button class="btn btnSearch d-none" type="button" id="button_loadSearch"
                                                disabled>
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                                BUSCAR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-3 my-2">
                                            <label>Nombres<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="nombres" name="nombres"
                                                autocomplete="off" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Apellidos<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                                autocomplete="off" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Género Pasajero</label>
                                            <select class="form-select" id="genero" name="genero">
                                                <option value="">Seleccione</option>
                                                <option value="MASCULINO">MASCULINO</option>
                                                <option value="FEMENINO">FEMENINO</option>
                                            </select>
                                        </div>
                                        <?php $col = "col-md-3"; ?>
                                        <div class="<?php echo $col ?> my-2" id="div_parentUbigeo">
                                            <label>Ubigeo<span class="requiredField">*</span></label>
                                            <select class="form-select" id="ubigeo" name="ubigeo" required>
                                                <?php if ($this->ubigeo_terminal["success"]): ?>
                                                    <?php $terminal_ubigeo = $this->ubigeo_terminal["message"]["ubigeo"]; ?>
                                                    <option value="<?php echo $terminal_ubigeo ?>">
                                                        <?php echo $this->ubigeo_terminal["message"]["ubigeo"] . " | " . $this->ubigeo_terminal["message"]["depa"] . " | " . $this->ubigeo_terminal["message"]["provi"] . " | " . $this->ubigeo_terminal["message"]["distri"] ?>
                                                    </option>
                                                <?php endif ?>

                                                <?php if ($this->ubigeo["success"]): ?>
                                                    <?php foreach ($this->ubigeo["message"] as $item): ?>
                                                        <?php if ($item["cod_ubigeo"] !== $terminal_ubigeo): ?>
                                                            <option value="<?php echo $item["cod_ubigeo"] ?>">
                                                                <?php echo $item["cod_ubigeo"] . " | " . $item["depa"] . " | " . $item["provi"] . " | " . $item["distri"] ?>
                                                            </option>
                                                        <?php endif ?>
                                                    <?php endforeach ?>
                                                <?php endif ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Dirección<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="direccion" name="direccion"
                                                autocomplete="off" value="Sin Direccion" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Celular</label>
                                            <input type="text" class="form-control" id="celular" name="celular"
                                                minlength="9" maxlength="9" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Correo electrónico</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                autocomplete="off">
                                        </div>
                                        <?php $col = "col-md-3";
                                        ?>
                                        <div class="<?php echo $col ?> my-2" id="div_parentTerminal">
                                            <label>Terminal<span class="requiredField">*</span></label>
                                            <select class="form-select" id="terminal" name="terminal" required>
                                                <?php if ($this->terminal_activo["success"]): ?>
                                                    <?php for ($i = 0; $i < count($this->terminal_activo["message"]); $i++): ?>
                                                        <?php $text = $this->terminal_activo["message"][$i]["nombre"] ?>
                                                        <option
                                                            value="<?php echo $this->terminal_activo["message"][$i]["id_terminal"] ?>">
                                                            <?php echo $text ?>
                                                        </option>
                                                    <?php endfor ?>
                                                <?php endif ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Fecha Nacimiento<span class="requiredField">*</span></label>
                                            <input type="date" class="form-control" id="fecha_nacimiento"
                                                name="fecha_nacimiento" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Nacionalidad <span class="requiredField">*</span></label>
                                            <select id="nacionalidad" name="nacionalidad" required autocomplete="new-password"></select>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Estado SUNAT</label>
                                            <input type="text" class="form-control" id="estado_sunat"
                                                name="estado_sunat" autocomplete="off" readonly>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Condición SUNAT</label>
                                            <input type="text" class="form-control" id="condicion_sunat"
                                                name="condicion_sunat" autocomplete="off" readonly>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Estado<span class="requiredField">*</span></label>
                                            <select class="form-select" name="estado" id="estado" required>
                                                <option value="1">Habilitado</option>
                                                <option value="0">Deshabilitado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contacto" role="tabpanel" aria-labelledby="tab_contacto"
                            tabindex="0">
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table todo_list w-100" cellspacing="0" id="table_contacto">
                                    <thead>
                                        <tr>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Celular</th>
                                            <th>Observación</th>
                                            <th><button type="button" class="btn p-0" id="btn_add_contacto"><i
                                                        class="bi bi-plus-square text-info fs-5"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_c"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_c">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_c" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal add Register-->
<div class="modal fade" id="modal_facturar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">PAGAR VALORIZACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <!-- Sección Cliente -->
                <div id="div_facturacion">
                    <form id="form_pago_nota" class="need-validation" novalidate>
                        <input type="hidden" name="id_valorizacion_pagar" id="id_valorizacion_pagar">
                        <div class="col-md-12 row">
                            <hr>
                            <div style="display: flex; justify-content:center">
                                <h5>Datos de facturación</h5>
                            </div>
                            <hr>
                            <!-- <div class="col-md-12 my-2 radio-container">
                                <div class="radios">
                                    <label class="radiol">
                                        <input checked name="tipo_cliente" type="radio" value="cliente_actual"
                                            id="cliente_actual" />
                                        <span class="radio-item">Usar cliente actual</span>
                                    </label>
                                    <label class="radiol">
                                        <input name="tipo_cliente" type="radio" value="cliente_nuevo"
                                            id="cliente_nuevo" />
                                        <span class="radio-item">Nuevo cliente</span>
                                    </label>
                                </div>
                            </div> -->

                            <div class="col-md-12 my-2 row d-none" id="div_generarNuevoCliente">
                                <label>Cliente<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                        class="open_modal_cliente">[+ Nuevo]</a></label>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="nuevo_cliente" name="nuevo_cliente"
                                            minlength="8" onkeypress="return controlTag(event);">
                                        <button type="button" class="btn btnSearch"
                                            id="buscar_nuevo_cliente">Buscar</button>
                                        <button class="btn btnSearch d-none" type="button" id="loader_buscar_nc"
                                            disabled="">
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                        </button>
                                        <input type="hidden" id="nuevo_cliente_id" name="nuevo_cliente_id">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <input class="form-control" type="text" id="nuevo_cliente_nombres"
                                        name="nuevo_cliente_nombres" data-tp_docu="" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 my-2">
                                <label for="total_pagar">Total a pagar</label>
                                <input type="number" name="total_pagar" id="total_pagar" class="form-control boldtxt"
                                    readonly>
                            </div>
                            <div class="col-md-3 my-2" id="div_parentTpComprobante">
                                <label>Tipo de comprobante<span class="requiredField">*</span></label>
                                <select class="form-select" id="tp_comprobante" name="tp_comprobante" required>
                                    <option value="3">BOLETA DE VENTA ELECTRÓNICO</option>
                                    <option value="1">FACTURA ELECTRÓNICA</option>
                                </select>
                            </div>
                            <div class="col-md-3 my-2" id="div_parentSerie">
                                <label>Serie<span class="requiredField">*</span></label>
                                <select class="form-select" name="serie" id="serie"></select>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Fecha de emisión<span class="requiredField">*</span></label>
                                <input type="date" name="fecha_emision" id="fecha_emision" class="form-control"
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Caja chica<span class="requiredField">*</span></label>
                                <?php if ($this->caja_userSesion["success"]): ?>
                                    <input type="text" class="form-control" name="id_caja_chica" id="id_caja_chica"
                                        autocomplete="off"
                                        value="<?= $this->caja_userSesion["message"][0]["referencia"]; ?>" disabled>
                                    <input type="hidden" name="caja_chica" id="caja_chica"
                                        value="<?= $this->caja_userSesion["message"][0]["id_caja_chica"]; ?>">
                                <?php endif ?>
                            </div>

                            <div class="col-md-3 my-2">
                                <label>Observación</label>
                                <textarea class="form-control" name="observaciones" id="observaciones" cols="30"
                                    rows="10"></textarea>
                            </div>

                            <div class="col-md-3 my-2">
                                <label>F. de pago<span class="requiredField me-2">*</span></label>
                                <select class="form-select" name="forma_pago" id="forma_pago">
                                    <option value="1">CONTADO</option>
                                    <option value="2">CREDITO</option>
                                </select>
                            </div>

                            <div class="col-md-3 my-2" id="div_parentMedioPago">
                                <label>Medio Pago<span class="requiredField">*</span></label>
                                <select id="medio_pago" name="medio_pago" required>
                                    <option value="">Seleccione</option>
                                    <?php if ($this->medio_pago["success"]): ?>
                                        <?php foreach ($this->medio_pago["message"] as $medio_pago): ?>
                                            <?php $text = $medio_pago["descripcion"] ?>
                                            <option value="<?= $medio_pago["id_medio_pago"] ?>">
                                                <?= $text ?>
                                            </option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-md-12 d-none" id="div_cuota">
                                <div class="row">
                                    <div class="col-md-4 my-1">
                                        <label>Tiempo de credito<span class="requiredField me-2">*</span></label>
                                        <select class="form-select" aria-label="Seleccionar método de pago"
                                            id="tiempo_credito" name="tiempo_credito">
                                            <option selected value="1">Factura a 30 días</option>
                                            <!-- <option value="2">Crédito</option> -->
                                            <option value="3">Factura a 15 días</option>
                                            <option value="4">Factura a 45 días</option>
                                            <option value="5">Factura a 60 días</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>F. de vencimiento<span class="requiredField me-2">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text py-0">
                                                <i class="bi bi-calendar-date"></i>
                                            </span>
                                            <input type="date" class="form-control" aria-label="fecha_credito"
                                                id="fecha_credito" name="fecha_credito">
                                        </div>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Monto de cuota<span class="requiredField me-2">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text py-0">$</span>
                                            <input type="number" class="form-control" placeholder="0.00" step="0.01"
                                                aria-label="monto_credito" id="monto_credito" name="monto_credito">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btnCancel" id="button_cancel_p"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btnSave" id="button_pagar">Pagar</button>
                    <button class="btn btnSave d-none" type="button" id="button_loadPagar" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Pagando...
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade"
    id="modalVistaPreviaComprobante"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h5 class="modal-title mb-1">
                        <i class="fa-light fa-file-invoice me-2"></i>
                        Revisar comprobante
                    </h5>

                    <small class="text-muted">
                        Revisa y modifica la descripción antes de enviar a SUNAT.
                    </small>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="preview_id_venta">

                <!-- CABECERA -->
                <div class="card border mb-4">
                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-3">
                                <small class="text-muted d-block">
                                    Comprobante
                                </small>

                                <strong id="preview_comprobante">
                                    -
                                </strong>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block">
                                    Cliente
                                </small>

                                <strong id="preview_cliente">
                                    -
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Documento
                                </small>

                                <strong id="preview_documento">
                                    -
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Fecha emisión
                                </small>

                                <strong id="preview_fecha">
                                    -
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Total
                                </small>

                                <strong
                                    id="preview_total"
                                    class="text-primary fs-5">
                                    S/ 0.00
                                </strong>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- AVISO -->
                <div class="alert alert-warning py-2">
                    <i class="fa-light fa-triangle-exclamation me-2"></i>

                    La descripción mostrada aquí será la que se enviará
                    en el comprobante electrónico.
                </div>

                <!-- DETALLE -->
                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">
                            <tr>
                                <th width="60">
                                    #
                                </th>

                                <th>
                                    Descripción
                                </th>

                                <th width="100" class="text-center">
                                    Cant.
                                </th>

                                <th width="130" class="text-end">
                                    P. Unit.
                                </th>

                                <th width="120" class="text-end">
                                    IGV
                                </th>

                                <th width="130" class="text-end">
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody id="tbodyPreviewDetalle">
                        </tbody>

                    </table>

                </div>

                <!-- TOTALES -->
                <div class="row justify-content-end">

                    <div class="col-md-5 col-lg-4">

                        <table class="table table-sm">

                            <tbody>

                                <tr>
                                    <td>Op. Gravada</td>

                                    <td
                                        class="text-end"
                                        id="preview_gravada">
                                        S/ 0.00
                                    </td>
                                </tr>

                                <tr>
                                    <td>IGV</td>

                                    <td
                                        class="text-end"
                                        id="preview_igv">
                                        S/ 0.00
                                    </td>
                                </tr>

                                <tr class="fw-bold fs-6">
                                    <td>TOTAL</td>

                                    <td
                                        class="text-end"
                                        id="preview_total_footer">
                                        S/ 0.00
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cerrar

                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnGuardarPreview">

                    <i class="fa-light fa-floppy-disk me-1"></i>

                    Guardar cambios

                </button>

                <button
                    type="button"
                    class="btn btn-success"
                    id="btnEnviarSunat">

                    <i class="fa-light fa-paper-plane me-1"></i>

                    Enviar a SUNAT

                </button>

            </div>

        </div>

    </div>

</div>