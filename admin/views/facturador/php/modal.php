<!-- Modal add Register-->
<div class="modal fade" id="modal_producto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">NUEVO PRODUCTO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_producto" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_ctg_encomienda" name="id_ctg_encomienda">
                        <div class="col-md-7 my-1">
                            <label>Nombre<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" autocomplete="off" required>
                        </div>
                        <div class="col-md-5 my-1">
                            <label>Precio Ud.<span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>
                                <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6 my-1">
                            <label>Descripción</label>
                            <input type="text" class="form-control" id="descripcion_producto" name="descripcion_producto" autocomplete="off" required>
                        </div>
                        <!-- <div class="col-md-3 my-1">
                            <label>Codigo interno<span class="requiredField">*</span></label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="codigo_interno" name="codigo_interno" autocomplete="off">
                                <button type="button" class="input-group-text" id="generar_codigo_interno">Generar</button>
                                <button class="input-group-text d-none" type="button" disabled id="generrar_codigo_internoLoad">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div> -->
                        <div class="col-md-3 my-1">
                            <label>Codigo SUNAT</label>
                            <input type="text" class="form-control" id="codigo_sunat" name="codigo_sunat" autocomplete="off" required>
                        </div>
                        <div class="col-md-1 my-1">
                            <label>ICBPER</label>
                            <div class="d-flex justify-content-center align-items-center" style="height: 38px;">
                                <input type="checkbox" class="form-check-input" id="afecto_icbper" name="afecto_icbper" style="width: 1.5rem; height: 1.5rem;">
                            </div>
                        </div>
                        <div class="col-md-2 my-1 d-none" id="div_factIcbper">
                            <label>Factor ICBPER<span class="requiredField">*</span></label>
                            <input type="number" class="form-control" id="factor_icbper" name="factor_icbper" autocomplete="off" required value="0.50">
                        </div>
                        <div class="col-md-4 my-1" id="div_parentUnidadM">
                            <label>Unidad de medida<span class="requiredField">*</span></label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida" required>
                                <?php if ($this->unidad_medida["success"]) : ?>
                                    <?php foreach ($this->unidad_medida["message"] as $unidad_medida) : ?>
                                        <?php $text = $unidad_medida["nombre"] ?>
                                        <option value="<?= $unidad_medida["id"] ?>"><?= $text ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-4 my-1" id="div_parentAfectacion">
                            <label>Afectación<span class="requiredField">*</span></label>
                            <select class="form-select" name="afectacion_prod" id="afectacion_prod" required>
                                <?php if ($this->afectaciones['success']): ?>
                                    <?php foreach ($this->afectaciones["message"] as $afectacion) : ?>
                                        <?php $text = $afectacion["descripcion"] ?>
                                        <option value="<?= $afectacion["id"] ?>"><?= $text ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-4 my-1">
                            <label>Estado<span class="requiredField">*</span></label>
                            <select class="form-select" name="estado_prod" id="estado_prod" required>
                                <option value="1">ACTIVO</option>
                                <!-- <option value="0">INACTIVO</option> -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancelP" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_saveP">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSaveP" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal cliente -->
<div class="modal fade" id="modal_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo">NUEVO CLIENTE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_cliente" class="needs-validation" novalidate>
                    <input type="hidden" id="operacion" name="operacion">
                    <ul class="nav nav-pills mb-3" id="pillsTab_contrato" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_general" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab" aria-controls="tab_general" aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_contacto" data-bs-toggle="pill" data-bs-target="#contacto" type="button" role="tab" aria-controls="tab_contacto" aria-selected="false">Contacto</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabs_contrato">
                        <!-- GENERAL -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="tab_general" tabindex="0">
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
                                            <input type="text" class="form-control" id="num_docu" name="num_docu" autocomplete="off" required minlength="8" maxlength="8">
                                            <button type="button" class="btn btnSearch" id="button_search">BUSCAR</button>
                                            <button class="btn btnSearch d-none" type="button" id="button_loadSearch" disabled>
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                BUSCAR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-3 my-2">
                                            <label>Nombres<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="nombres" name="nombres" autocomplete="off" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Apellidos<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos" autocomplete="off" required>
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
                                                <?php if ($this->ubigeo_terminal["success"]) : ?>
                                                    <?php $terminal_ubigeo = $this->ubigeo_terminal["message"]["ubigeo"]; ?>
                                                    <option value="<?php echo $terminal_ubigeo ?>">
                                                        <?php echo $this->ubigeo_terminal["message"]["ubigeo"] . " | " . $this->ubigeo_terminal["message"]["depa"] . " | " . $this->ubigeo_terminal["message"]["provi"] . " | " . $this->ubigeo_terminal["message"]["distri"] ?>
                                                    </option>
                                                <?php endif ?>

                                                <?php if ($this->ubigeo["success"]) : ?>
                                                    <?php foreach ($this->ubigeo["message"] as $item) : ?>
                                                        <?php if ($item["cod_ubigeo"] !== $terminal_ubigeo) : ?>
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
                                            <input type="text" class="form-control" id="direccion" name="direccion" autocomplete="off" value="Sin Direccion" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Celular</label>
                                            <input type="text" class="form-control" id="celular" name="celular" minlength="9" maxlength="9" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Correo electrónico</label>
                                            <input type="email" class="form-control" id="email" name="email" autocomplete="off">
                                        </div>
                                        <?php $col = "col-md-3";
                                        ?>
                                        <div class="<?php echo $col ?> my-2" id="div_parentTerminal">
                                            <label>Terminal<span class="requiredField">*</span></label>
                                            <select class="form-select" id="terminal" name="terminal" required>
                                                <?php if ($this->terminal_activo["success"]) : ?>
                                                    <?php for ($i = 0; $i < count($this->terminal_activo["message"]); $i++) : ?>
                                                        <?php $text = $this->terminal_activo["message"][$i]["nombre"] ?>
                                                        <option value="<?php echo $this->terminal_activo["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
                                                    <?php endfor ?>
                                                <?php endif ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Fecha Nacimiento<span class="requiredField">*</span></label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Nacionalidad <span class="requiredField">*</span></label>
                                            <select id="nacionalidad" name="nacionalidad" required autocomplete="new-password"></select>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Estado SUNAT</label>
                                            <input type="text" class="form-control" id="estado_sunat" name="estado_sunat" autocomplete="off" readonly>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Condición SUNAT</label>
                                            <input type="text" class="form-control" id="condicion_sunat" name="condicion_sunat" autocomplete="off" readonly>
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
                        <div class="tab-pane fade" id="contacto" role="tabpanel" aria-labelledby="tab_contacto" tabindex="0">
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table todo_list w-100" cellspacing="0" id="table_contacto">
                                    <thead>
                                        <tr>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Celular</th>
                                            <th>Observación</th>
                                            <th><button type="button" class="btn p-0" id="btn_add_contacto"><i class="bi bi-plus-square text-info fs-5"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_c" data-bs-dismiss="modal">Cancelar</button>
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