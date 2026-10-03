<div class="modal fade" id="modal_pasaje" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title text-white me-3" id="staticBackdropLabel">INFO VENTA</h5>
                <h5 class="modal-title text-white fw-normal" id="info_OD">Lima → Cusco &nbsp;|&nbsp; Lun 24 Feb 2025
                    &nbsp;|&nbsp; 22:00 hrs</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3">
                <div class="row g-3">

                    <!-- ══════════════════════════════════════
                         COL 1: Asiento + Comprobante
                    ══════════════════════════════════════ -->
                    <div class="col-md-4 d-flex flex-column gap-3">

                        <!-- Asiento visual -->
                        <div class="d-flex align-items-center gap-3 mb-1">
                            <div class="seat-badge">
                                <span class="seat-num" id="num_asiento_venta"></span>
                                <span class="seat-floor" id="n_piso_venta"></span>
                            </div>
                            <div>
                                <span class="badge badge-VENDIDO mb-1 d-block" id="badge_estado"></span>
                                <div class="text-muted" style="font-size:.75rem;">Origen embarque</div>
                                <div class="fw-semibold" id="origen_embarque"></div>
                                <div class="text-muted" style="font-size:.75rem;">Destino bajada</div>
                                <div class="fw-semibold" id="destino_bajada"></div>
                            </div>
                        </div>

                        <!-- Comprobante -->
                        <div>
                            <div class="section-label">Comprobante</div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-file-lines fa-xs"></i></span>
                                <span class="info-label">Tipo:</span>
                                <span class="info-value" id="venta_tp_comprobante"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-hashtag fa-xs"></i></span>
                                <span class="info-label">N° Comprobante:</span>
                                <span class="info-value" id="venta_serie_correlativo"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-calendar fa-xs"></i></span>
                                <span class="info-label">Fecha emisión:</span>
                                <span class="info-value" id="venta_fecha_emision"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-user fa-xs"></i></span>
                                <span class="info-label">Emitido por:</span>
                                <span class="info-value" id="venta_nombresU"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-building fa-xs"></i></span>
                                <span class="info-label">Terminal:</span>
                                <span class="info-value" id="venta_terminalN"></span>
                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════════════════════
                         COL 2: Pasajero + Cliente
                    ══════════════════════════════════════ -->
                    <div class="col-md-4 divider-v ps-md-3 d-flex flex-column gap-3">

                        <!-- Pasajero -->
                        <div>
                            <div class="section-label">Pasajero</div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-id-card fa-xs"></i></span>
                                <span class="info-label">DNI / Doc.:</span>
                                <span class="info-value" id="venta_pasajero_num"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-person fa-xs"></i></span>
                                <span class="info-label">Nombres:</span>
                                <span class="info-value" id="venta_nombres_pasajero"></span>
                            </div>
                            <div class="info-row d-none" id="div_obs_pasajero">
                                <span class="info-icon"><i class="fa-regular fa-person fa-xs"></i></span>
                                <span class="info-label">Observacion:</span>
                                <span class="info-value" id="venta_obs_pasajero"></span>
                            </div>

                            <!-- Niño: visible solo si c_nino == 1 -->
                            <div id="div_nino_info" class="d-none">
                                <hr class="my-2">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <i class="fa-regular fa-child text-info fa-sm"></i>
                                    <span class="fw-semibold" style="font-size:.8rem;">Acompañante niñ@ (menor de 5
                                        años)</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-icon"><i class="fa-regular fa-id-card fa-xs"></i></span>
                                    <span class="info-label">DNI / Doc.:</span>
                                    <span class="info-value" id="venta_nino_num"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-icon"><i class="fa-regular fa-user fa-xs"></i></span>
                                    <span class="info-label">Nombres:</span>
                                    <span class="info-value" id="venta_nombres_nino"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-icon"></span>
                                    <span class="info-label">Motivo:</span>
                                    <span class="info-value" id="venta_motivo_nino"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente (pagante) -->
                        <div>
                            <div class="section-label">Cliente (Pagante)</div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-id-card fa-xs"></i></span>
                                <span class="info-label">DNI / RUC:</span>
                                <span class="info-value" id="venta_num_docu"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-user fa-xs"></i></span>
                                <span class="info-label">Nombres:</span>
                                <span class="info-value" id="venta_nombres_cliente"></span>
                            </div>

                            <!-- Email / Celular: visible solo en VENTA_WEB -->
                            <div id="div_contacto_info" class="d-none">
                                <div class="info-row">
                                    <span class="info-icon"><i class="fa-regular fa-envelope fa-xs"></i></span>
                                    <span class="info-label">Correo:</span>
                                    <span class="info-value" id="venta_email"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-icon"><i class="fa-regular fa-mobile fa-xs"></i></span>
                                    <span class="info-label">Celular:</span>
                                    <span class="info-value" id="venta_celular"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════════════════════
                         COL 3: Pago + Descuento
                    ══════════════════════════════════════ -->
                    <div class="col-md-4 divider-v ps-md-3 d-flex flex-column gap-3">

                        <!-- Pago -->
                        <div>
                            <div class="section-label">Pago</div>
                            <div class="info-row align-items-center">
                                <span class="info-icon"><i class="fa-regular fa-money-bill fa-xs"></i></span>
                                <span class="info-label">Precio:</span>
                                <span class="info-value text-success fw-bold fs-5" id="venta_precio"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-credit-card fa-xs"></i></span>
                                <span class="info-label">Medio pago:</span>
                                <span class="info-value" id="venta_medio_pago"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-receipt fa-xs"></i></span>
                                <span class="info-label">Forma pago:</span>
                                <span class="info-value" id="venta_forma_pago"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-cash-register fa-xs"></i></span>
                                <span class="info-label">Caja chica:</span>
                                <span class="info-value" id="venta_caja_chica"></span>
                            </div>
                            <!-- Cod. operación: visible solo si existe -->
                            <div class="info-row" id="div_cod_operacion">
                                <span class="info-icon"><i class="fa-regular fa-barcode fa-xs"></i></span>
                                <span class="info-label">Cód. operación:</span>
                                <span class="info-value" id="venta_cod_operacion"></span>
                            </div>
                        </div>

                        <!-- Descuento / Cupón -->
                        <div class="d-none" id="div_cupon">
                            <div class="section-label">Descuento</div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-tag fa-xs"></i></span>
                                <span class="info-label">Cupón:</span>
                                <span class="info-value">
                                    <span class="badge bg-success" id="venta_cupon">DESC10</span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-icon"><i class="fa-regular fa-circle-minus fa-xs"></i></span>
                                <span class="info-label">Monto desc.:</span>
                                <span class="info-value text-danger fw-semibold" id="venta_descuento">- S/ 5.00</span>
                            </div>
                        </div>
                        <!-- Egresos venta -->
                        <div class="d-none" id="div_egresos">
                            <div class="section-label">Egresos</div>
                            <div class="">
                                <table class="mini-table-egresos" id="tabla_egresos">
                                    <thead>
                                        <tr>
                                            <th>Comprobante</th>
                                            <th>Monto</th>
                                            <th>Concepto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style="font-weight:600; text-align:right;" colspan="1">Total:</td>
                                            <td id="total_egresos" style="font-weight:600;">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /BODY -->

            <!-- FOOTER -->
            <div class="modal-footer" id="contenedor-botones">
                <button type="button" class="btn btnCancel  d-none" id="button_cancelarPostponer">Cancelar
                    Postponer</button>
                <button type="button" class="btn btnCambiarA d-none" id="button_cambiarA">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>Cambiar asiento
                </button>
                <button type="button" class="btn btnPostponer d-none" id="button_postponer">Postponer</button>
                <button type="button" class="btn btnPostponer d-none" id="button_aplicarPostponer">Posponer</button>
                <button type="button" class="btn btnAnular    d-none" id="button_anular">Anular</button>
                <a class="btn btnColorViolet d-none" id="button_comprobante">
                    <i class="fa-regular fa-file-pdf me-1"></i>Comprobante
                </a>
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Salir</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal_pasajero" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo">NUEVO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_pasajero" class="needs-validation" novalidate>
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
                                                autocomplete="off" value="S/D" required>
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
                                            <label>Edad<span class="requiredField">*</span><a href="javascript:void(0)"
                                                    class="cambiar_entrada"> [Anio]</a></label>
                                            <input type="text" class="form-control" id="fecha_nacimiento"
                                                name="fecha_nacimiento" required minlength="2" maxlength="2"
                                                onkeypress="return controlTag(event);">
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
                        <button type="button" class="btn btnCancel" id="button_cancel_p"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_p">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_p" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir condcutor y demas en una programacion -->
<div class="modal fade" id="modal_conductor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">CONDUCTOR & PERSONAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_conductor" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="manifi" name="manifi">
                        <input type="hidden" id="id_program" name="id_program">
                        <?php
                        $col = "col-md-4"; ?>
                        <div class="<?php echo $col ?> my-2" id="div_parentConductor">
                            <label>Conductor<span class="requiredField">*</span></label>
                            <select class="form-select" id="conductor" name="conductor">

                                <?php if ($this->conductor["success"]): ?>
                                    <?php foreach ($this->conductor["message"] as $conductor): ?>
                                        <?php $text = $conductor["nombres"] . " | " . $conductor["apellidos"] . " - " . $conductor["num_docu"] ?>
                                        <option value="<?= $conductor["id_usuario"] ?>">
                                            <?= $text ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <?php
                        $col = "col-md-6";
                        include("./views/templates/components/cmp_select_personal.php") ?>
                        <div class="col-md-auto my-2 d-flex justify-content-center align-items-end">
                            <button type="button" class="btn btn-secondary" id="add_personal">Agregar</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table todo_list w-100" cellspacing="0" id="table_personal">
                            <thead>
                                <tr>
                                    <th>Personal</th>
                                    <th>ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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

<div class="modal fade" id="modal_vehiculo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">CAMBIAR VEHICULO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_vehiculo" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <?php
                        $col = "col-md-12"; ?>
                        <div class="<?php echo $col ?> my-2" id="div_parentVehiculo">
                            <label>Ingrese <b>PLACA</b><span class="requiredField">*</span></label>
                            <select class="form-select" id="vehiculo" name="vehiculo">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_v"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_v">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_v" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_asien" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">CAMBIAR ASIENTO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_asien" class="needs-validation" novalidate>
                    <input type="hidden" id="id_pro_a" name="id_pro_a">
                    <input type="hidden" id="id_vehi_a" name="id_vehi_a">
                    <input type="hidden" id="id_venta_a" name="id_venta_a">
                    <div class="row g-2 my-2">
                        <div class="col-md-12">
                            <label>Ingrese <b>Numero de asiento</b><span class="requiredField">*</span></label>
                            <input type="text" class="form-control" onkeypress="return controlTag(event);" id="asien"
                                name="asien">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_a"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_a">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_a" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_liquidacion_terminal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">LIQUIDAR TERMINAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <form id="form_liquidar_terminal" class="needs-validation" novalidate>
                    <input type="hidden" id="id_programacion_l" name="id_programacion_l">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="tp_porcen">Tipo %</label>
                            <select name="tp_porcen" id="tp_porcen" class="form-select">
                                <option value="1">%</option>
                                <option value="2">Monto</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="div_porcen" class="d-none">
                            <label for="porcen">Indique el porcentaje<span class="requiredField">*</span></label>
                            <input type="text" id="porcen" name="porcen" class="form-control" placeholder="0 %"
                                maxlength="8" aria-label="Digite el monto a cerrar"
                                onkeypress="return controlTag(event);" required>
                        </div>
                        <div class="col-md-3" id="div_monto" class="d-none">
                            <label for="monto">Indique el monto<span class="requiredField">*</span></label>
                            <input type="text" id="monto" name="monto" class="form-control" placeholder="S/ 0.00"
                                maxlength="8" aria-label="Digite el monto a cerrar"
                                onkeypress="return controlTag(event);">
                        </div>
                        <div class="col-md-7">
                            <label for="observaciones">Observaciones</label>
                            <input type="text" id="observaciones" name="observaciones" class="form-control"
                                aria-label="Cod. Operacion">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <label for="">Detalle de egresos (Opcional)</label>
                        <table class="table todo_list w-100" cellspacing="0" id="table_egreso">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th><button type="button" class="btn p-0" id="btn_add_egreso"><i
                                                class="bi bi-plus-square text-info fs-5"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                    <td class="fs-6">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td id="monto_total" class="fs-6">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btnSave" id="button_save_l">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_l" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                        <button type="button" class="btn btnCancel" id="button_cancel_l"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal liquidacion usuario -->
<div class="modal fade" id="modal_liquidacion_usuario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">LIQUIDAR PUNTO DE VENTA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <form id="form_liquidar_usuario" class="needs-validation" novalidate>
                    <input type="hidden" id="id_programacion_l_u" name="id_programacion_l_u">
                    <div class="row">
                        <div class="col-md-3" id="div_porcentaje_empresa">
                            <label>Porcentaje empresa<span class="requiredField">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text py-0">%</span>
                                <?php $comision_empresa = $this->comision_empresa['message'] ?? 0; ?>
                                <input type="text" class="form-control" name="porcentaje_empresa"
                                    id="porcentaje_empresa" placeholder="0.00" value="<?= $comision_empresa ?>">
                            </div>
                        </div>
                        <div class="col-md-9" id="div_observaciones_LU">
                            <label for="observaciones_LU">Observaciones Generales</label>
                            <input type="text" id="observaciones_LU" name="observaciones_LU" class="form-control"
                                aria-label="Cod. Operacion">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 450px;">
                        <label for="">Detalle de egresos (Opcional)</label>
                        <table class="table todo_list w-100" cellspacing="0" id="table_egreso_usuario">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th><button type="button" class="btn p-0" id="btn_add_egreso_usuario"><i
                                                class="bi bi-plus-square text-info fs-5"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                    <td class="fs-6">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td id="monto_total_usuario" class="fs-6">S/ 0.00</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btnSave" id="button_save_lu">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_lu" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                        <button type="button" class="btn btnCancel" id="button_cancel_lu"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_edad" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Ingrese edad</h5>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
            </div>
            <div class="modal-body text-center">
                <form id="form_edad" class="needs-validation" novalidate>
                    <input type="hidden" name="id_usuario_edad" id="id_usuario_edad">
                    <input type="text" class="form-control text-center fw-bold fs-4" id="edad_usuario"
                        name="edad_usuario" placeholder="Escribe aquí">

                    <div class="modal-footer justify-content-center">
                        <button type="submit" id="btn_guardar_edad" class="btn btnSave">Guardar</button>
                        <button id="load_guardar_edad" type="button" class="btn btnSave d-none"> <span
                                class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span>Guardando</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    function controlTag(e) {
        tecla = (document.all) ? e.keyCode : e.which;
        if (tecla == 8) return true;
        else if (tecla == 0 || tecla == 9) return true;
        patron = /[0-9\s]/;
        n = String.fromCharCode(tecla);
        return patron.test(n);
    }
</script>