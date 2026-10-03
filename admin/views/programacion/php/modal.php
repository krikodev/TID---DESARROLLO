<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-general-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-general" type="button" role="tab" aria-controls="pills-general"
                                aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-personal-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-personal" type="button" role="tab" aria-controls="pills-personal"
                                aria-selected="false">Personal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-ruta-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-ruta" type="button" role="tab" aria-controls="pills-ruta"
                                aria-selected="false">Rutas Origenes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-ruta_destino-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-ruta_destino" type="button" role="tab"
                                aria-controls="pills-ruta_destino" aria-selected="false">Rutas Destinos</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-general" role="tabpanel"
                            aria-labelledby="pills-general-tab" tabindex="0">
                            <div class="row g-2 my-2">
                                <input type="hidden" id="id_programacion" name="id_programacion">
                                <div class="col-md-2 my-2" id="div_parentTipo">
                                    <label>Tipo<span class="requiredField">*</span></label>
                                    <select class="form-select" id="tipo_programacion" name="tipo_programacion">
                                        <option value="1">Pasaje y encomienda</option>
                                        <option value="2">Encomienda</option>
                                    </select>
                                </div>
                                <div class="col-md-2 my-2">
                                    <label>Estado<span class="requiredField">*</span></label>
                                    <select class="form-select" name="estado" id="estado" required>
                                        <option value="1">Habilitado</option>
                                        <option value="0">Deshabilitado</option>
                                    </select>
                                </div>
                                <?php
                                $col = "col-md-4";
                                include("./views/templates/components/cmp_select_terminal_origen.php") ?>
                                <div class="col-md-4" id="div_parentTerminalDestino">
                                    <label>Terminal Destino<span class="requiredField">*</span></label>
                                    <select class="form-select" name="terminal_destino" id="terminal_destino" required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                                <?php
                                $col = "col-md-4";
                                include("./views/templates/components/cmp_select_vehiculo.php") ?>
                                <?php
                                $col = "col-md-4"; ?>
                                <div class="col-md-4 my-2" id="div_parentConductor">
                                    <label>Conductor<span class="requiredField">*</span></label>
                                    <select class="form-select" id="conductor" name="conductor">
                                        <option value="2">CONDUCTOR GENERAL</option>
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
                                <div class="col-md-2 my-2">
                                    <label>Fecha salida<span class="requiredField">*</span></label>
                                    <input class="form-control" type="date" name="fecha_salida" id="fecha_salida"
                                        min="<?php echo date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" required>
                                </div>
                                <div class="col-md-2 my-2">
                                    <label>Hora salida<span class="requiredField">*</span></label>
                                    <input class="form-control" type="time" name="hora_salida" id="hora_salida"
                                        required>
                                </div>
                                <?php
                                $col = "col-md-4";
                                include("./views/templates/components/cmp_select_tp_servicio_pasaje.php")
                                    ?>
                                <div class="col-md-2 my-2" id="Parent_PrPrimerPiso">
                                    <label>Precio Premium<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text py-0" id="basic-addon1">S/</span>
                                        <input class="form-control" type="text" name="precio_primer_piso"
                                            id="precio_primer_piso" required>
                                    </div>
                                </div>
                                <div class="col-md-2 my-2" id="Parent_PrSegundoPiso">
                                    <label>Precio Normal<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text py-0" id="basic-addon1">S/</span>
                                        <input class="form-control" type="text" name="precio_segundo_piso"
                                            id="precio_segundo_piso" required>
                                    </div>
                                </div>
                                <div class="col-md-2 my-2" id="Parent_PrMinimo">
                                    <label>Precio Mínimo<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text py-0" id="basic-addon1">S/</span>
                                        <input class="form-control" type="text" name="precio_minimo" id="precio_minimo"
                                            required>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-personal" role="tabpanel"
                            aria-labelledby="pills-personal-tab" tabindex="0">
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
                        </div>
                        <div class="tab-pane fade" id="pills-ruta" role="tabpanel" aria-labelledby="pills-ruta-tab"
                            tabindex="0">
                            <div class="row mb-2">
                                <div class="col-md-6 my-2" id="div_parentRuta">
                                    <label>Ruta<span class="requiredField">*</span></label>
                                    <select class="form-select" name="terminal_ruta" id="terminal_ruta" required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                                <div class="col-md-auto my-2 d-flex justify-content-center align-items-end">
                                    <button type="button" class="btn btn-secondary"
                                        id="add_terminalRuta">Agregar</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table todo_list w-100" cellspacing="0" id="table_terminalRuta">
                                    <thead>
                                        <tr>
                                            <th>Terminal</th>
                                            <th>Hora</th>
                                            <th>ACCIÓN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-ruta_destino" role="tabpanel"
                            aria-labelledby="pills-ruta_destino-tab" tabindex="0">
                            <div class="row mb-2 align-items-end">
                                <div class="col-md-4" id="div_parentDestinoRuta">
                                    <label>Ruta<span class="requiredField">*</span></label>
                                    <select class="form-select" name="ruta_destino" id="ruta_destino" required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="hora_salida_ruta">Hora llegada <span
                                            class="requiredField">*</span></label>
                                    <input class="form-control" type="time" name="hora_salida_ruta"
                                        id="hora_salida_ruta">
                                </div>

                                <div class="col-md-2">
                                    <label for="precio_rd_piso_1">Precio 1° piso <span
                                            class="requiredField">*</span></label>
                                    <input class="form-control" type="number" name="precio_rd_piso_1"
                                        id="precio_rd_piso_1" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="precio_rd_piso_2">Precio 2° piso <span
                                            class="requiredField">*</span></label>
                                    <input class="form-control" type="number" name="precio_rd_piso_2"
                                        id="precio_rd_piso_2" min="1">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" id="add_destinoRuta">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table todo_list w-100" cellspacing="0" id="table_destinoRuta">
                                    <thead>
                                        <tr>
                                            <th>Terminal</th>
                                            <th>Hora</th>
                                            <th>Precio Piso 1</th>
                                            <th>Precio Piso 2</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave" disabled>
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
                        <div class="<?php echo $col ?> my-2" id="div_parentConductorP">
                            <label>Conductor<span class="requiredField">*</span></label>
                            <select class="form-select" id="conductor_p" name="conductor_p">
                                <option value="">Seleccione</option>
                                <?php if ($this->conductor["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->conductor["message"]); $i++): ?>
                                        <?php $text = $this->conductor["message"][$i]["nombres"] . " | " . $this->conductor["message"][$i]["apellidos"] . " - " . $this->conductor["message"][$i]["num_docu"] ?>
                                        <option value="<?php echo $this->conductor["message"][$i]["id_usuario"] ?>">
                                            <?php echo $text ?>
                                        </option>
                                    <?php endfor ?>
                                <?php endif ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 my-2" id="div_parentPersonalP">
                            <label>Personal<span class="requiredField">*</span></label>
                            <select class="form-select" id="personal_p" name="personal_p" required>
                                <option value="">Seleccione</option>
                                <?php if ($this->personal["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->personal["message"]); $i++): ?>
                                        <?php $text = $this->personal["message"][$i]["nombres"] . " " . $this->personal["message"][$i]["apellidos"] . " - " . $this->personal["message"][$i]["num_docu"] . " - " . $this->personal["message"][$i]["tp_usuario"] ?>
                                        <option value="<?php echo $this->personal["message"][$i]["id_usuario"] ?>">
                                            <?php echo $text ?>
                                        </option>
                                    <?php endfor ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-auto my-2 d-flex justify-content-center align-items-end">
                            <button type="button" class="btn btn-secondary" id="add_personal_p">Agregar</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table todo_list w-100" cellspacing="0" id="table_personal_p">
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


<!-- <div class="modal fade" id="modal_liquidacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo_liquidacion"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <form id="form_liquidar" class="needs-validation" novalidate>
                    <input type="hidden" id="id_programacion_l" name="id_programacion_l">
                    <div id="apart_pasaje" class="section-card">
                        <div class="section-header">
                            <h3 class="section-title pasajes">Pasajes</h3>
                        </div>
                        <div class="section-content">
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
                                    <input type="text" id="porcen" name="porcen" class="form-control" placeholder="0 %" maxlength="8" aria-label="Digite el monto a cerrar" onkeypress="return controlTag(event);" required>
                                </div>
                                <div class="col-md-3" id="div_monto" class="d-none">
                                    <label for="monto">Indique el monto<span class="requiredField">*</span></label>
                                    <input type="text" id="monto" name="monto" class="form-control" placeholder="S/ 0.00" maxlength="8" aria-label="Digite el monto a cerrar" onkeypress="return controlTag(event);">
                                </div>
                                <div class="col-md-7">
                                    <label for="observaciones">Observaciones</label>
                                    <input type="text" id="observaciones" name="observaciones" class="form-control" aria-label="Cod. Operacion">
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
                                            <th><button type="button" class="btn p-0" id="btn_add_egreso"><i class="bi bi-plus-square text-info fs-5"></i></button></th>
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
                        </div>
                    </div>

                    <div id="apart_encomienda" class="section-card">
                        <div class="section-header">
                            <h3 class="section-title encomiendas">Encomiendas</h3>
                        </div>
                        <div class="section-content">

                            <div class="row">
                                <div class="col-md-2">
                                    <label for="tp_porcen_e">Tipo %</label>
                                    <select name="tp_porcen_e" id="tp_porcen_e" class="form-select">
                                        <option value="1">%</option>
                                        <option value="2">Monto</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="div_porcen_e" class="d-none">
                                    <label for="porcen_e">Indique el porcentaje<span class="requiredField">*</span></label>
                                    <input type="text" id="porcen_e" name="porcen_e" class="form-control" placeholder="0 %" maxlength="8" aria-label="Digite el monto a cerrar" onkeypress="return controlTag(event);" required>
                                </div>
                                <div class="col-md-3" id="div_monto_e" class="d-none">
                                    <label for="monto_e">Indique el monto<span class="requiredField">*</span></label>
                                    <input type="text" id="monto_e" name="monto_e" class="form-control" placeholder="S/ 0.00" maxlength="8" aria-label="Digite el monto a cerrar" onkeypress="return controlTag(event);">
                                </div>
                                <div class="col-md-7">
                                    <label for="observaciones_e">Observaciones</label>
                                    <input type="text" id="observaciones_e" name="observaciones_e" class="form-control" aria-label="Cod. Operacion">
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 250px;">
                                <label for="">Detalle de egresos (Opcional)</label>
                                <table class="table todo_list w-100" cellspacing="0" id="table_egreso_e">
                                    <thead>
                                        <tr>
                                            <th>Comprobante</th>
                                            <th>Serie</th>
                                            <th>Correlativo</th>
                                            <th>Monto</th>
                                            <th>Concepto</th>
                                            <th><button type="button" class="btn p-0" id="btn_add_egreso_e"><i class="bi bi-plus-square text-info fs-5"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                            <td class="fs-6">Total</td>
                                            <td></td>
                                            <td></td>
                                            <td id="monto_total_e" class="fs-6">S/ 0.00</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btnSave" id="button_save_l">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_l" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                        <button type="button" class="btn btnCancel" id="button_cancel_l" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> -->

<div class="modal fade" id="modal_liquidacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">LIQUIDAR VEHICULO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <form id="form_liquidar" class="needs-validation" novalidate>
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
                            <input type="number" id="porcen" name="porcen" class="form-control" placeholder="0 %"
                                maxlength="8" aria-label="Digite el monto a cerrar" required>
                        </div>
                        <div class="col-md-3" id="div_monto" class="d-none">
                            <label for="monto">Indique el monto<span class="requiredField">*</span></label>
                            <input type="number" id="monto" name="monto" class="form-control" placeholder="S/ 0.00"
                                maxlength="8" aria-label="Digite el monto a cerrar"">
                        </div>
                        <div class=" col-md-7">
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


<!-- Modal egreso -->
<div class="modal fade" id="modal_egreso" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_egreso" class="needs-validation" novalidate>
                    <input type="hidden" name="id_egreso" id="id_egreso">
                    <div class="table-responsive" style="max-height: 550px;">
                        <table class="table todo_list w-100" cellspacing="0" id="table_egreso_p">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th><button type="button" class="btn p-0" id="btn_add_egreso_p"><i
                                                class="bi bi-plus-square text-info fs-5"></i></button></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                    <td class="fs-6">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td id="monto_total_p" class="fs-6">S/ 0.00</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
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
<!-- Fin de modal egreso -->


<!-- Modal de seleccion de vendedor para ver su ventas -->
<div class="modal fade" id="modal_ventas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_egreso" class="needs-validation" novalidate>
                    <div class="table-responsive" style="max-height: 550px;">
                        <table class="table table-sm" id="tabla_resumen_vendedores">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th title="Vendidos">Vend.</th>
                                    <th title="Reservados">Res.</th>
                                    <th title="Anulados">Anul.</th>
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
                                    <td><span class="fw-semibold">TOTALES:</span></td>
                                    <td id="total_vendidos" class="fw-semibold">0</td>
                                    <td id="total_reservados" class="fw-semibold">0</td>
                                    <td id="total_anulados" class="fw-semibold">0</td>
                                    <td id="total_nota_venta" class="fw-semibold">0</td>
                                    <td id="total_boleta" class="fw-semibold">0</td>
                                    <td id="total_factura" class="fw-semibold">0</td>
                                    <td id="total_monto" class="fw-semibold">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_salir_m"
                            data-bs-dismiss="modal">Salir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Fin modal -->


<!-- Modal de asientos vendidos y seleccionado en tabla -->

<div class="modal fade" id="modalProgramacionRegistros" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Programación con registros asociados
                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-warning mb-3">
                    Esta programación no puede desactivarse porque tiene
                    <strong>ventas</strong> o <strong>asientos seleccionados</strong>.
                    Primero debe eliminarlos.
                </div>

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Asiento</th>
                            <th width="180">Estado</th>
                            <th>Cliente</th>
                            <th width="80" class="text-center">Acción</th>
                        </tr>
                    </thead>

                    <tbody id="tbProgramacionRegistros">

                    </tbody>

                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>