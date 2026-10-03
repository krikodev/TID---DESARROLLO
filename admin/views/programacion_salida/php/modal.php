<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_salida" name="id_salida">
                        <div class="col-md-3">
                            <label for="ubigeo_origen">Ubigeo origen <span class="requiredField">*</span></label>
                            <select id="ubigeo_origen" name="ubigeo_origen" required></select>
                        </div>
                        <div class="col-md-3">
                            <label for="direccion_origen">Dirección origen <span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="direccion_origen" name="direccion_origen"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label for="ubigeo_destino">Ubigeo destino <span class="requiredField">*</span></label>
                            <select id="ubigeo_destino" name="ubigeo_destino" required></select>
                        </div>
                        <div class="col-md-3">
                            <label for="direccion_destino">Dirección destino <span
                                    class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="direccion_destino" name="direccion_destino"
                                required>
                        </div>
                        <?php
                        $col = "col-md-3";
                        include("./views/templates/components/cmp_select_vehiculo.php") ?>
                        <?php
                        $col = "col-md-3"; ?>
                        <div class="<?= $col ?> my-2" id="div_parentConductor">
                            <label>Conductor<span class="requiredField">*</span></label>
                            <select class="form-select" id="conductor" name="conductor">
                                <option value="">Seleccione</option>
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
                                min="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" required>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Hora salida<span class="requiredField">*</span></label>
                            <input class="form-control" type="time" name="hora_salida" id="hora_salida"
                                value="<?= date('H:i', strtotime('+1 hour')) ?>" required>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Estado<span class="requiredField">*</span></label>
                            <select class="form-select" name="estado" id="estado" required>
                                <option value="1">Habilitado</option>
                                <option value="0">Deshabilitado</option>
                            </select>
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
                        <div class="<?= $col ?> my-2" id="div_parentConductorP">
                            <label>Conductor<span class="requiredField">*</span></label>
                            <select class="form-select" id="conductor_p" name="conductor_p">
                                <option value="">Seleccione</option>
                                <?php if ($this->conductor["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->conductor["message"]); $i++): ?>
                                        <?php $text = $this->conductor["message"][$i]["nombres"] . " | " . $this->conductor["message"][$i]["apellidos"] . " - " . $this->conductor["message"][$i]["num_docu"] ?>
                                        <option value="<?= $this->conductor["message"][$i]["id_usuario"] ?>">
                                            <?= $text ?>
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
                                        <option value="<?= $this->personal["message"][$i]["id_usuario"] ?>">
                                            <?= $text ?>
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