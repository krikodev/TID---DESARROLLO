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

                    <input type="hidden" id="id_flete" name="id_flete">

                    <div class="row g-2 my-2">

                        <div class="col-md-6" id="div_parentCotizacion">
                            <label for="cotizacion">
                                Cotización <span class="requiredField">*</span>
                            </label>

                            <select class="form-select" id="cotizacion" name="cotizacion" required>
                                <option value="Seleccione">Seleccione</option>

                                <?php if ($this->cotizaciones['success']): ?>
                                    <?php foreach ($this->cotizaciones["message"] as $cotizacion): ?>
                                        <?php $text = $cotizacion["numero"] . ' | ' . $cotizacion["cliente"] ?>
                                        <option value="<?= $cotizacion["id_cotizacion"] ?>">
                                            <?= $text ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <div class="col-md-6" id="div_parentCliente">
                            <label>
                                Cliente
                                <span class="requiredField">*</span>
                                <a href="javascript:void(0)" class="open_modal_cliente">[+ Nuevo]</a>
                            </label>

                            <div class="input-group">
                                <input class="form-control"
                                    type="text"
                                    id="cliente"
                                    autocomplete="new-password"
                                    name="cliente"
                                    minlength="8"
                                    required>

                                <button type="button" class="btn btnS" id="buscar_cliente">
                                    Buscar
                                </button>

                                <button class="btn btnS d-none"
                                    type="button"
                                    id="loader_buscar"
                                    disabled>
                                    <span class="spinner-border spinner-border-sm"
                                        role="status"
                                        aria-hidden="true"></span>
                                </button>

                                <input type="hidden" id="cliente_id" name="cliente_id">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="ubigeo_origen">
                                Ubigeo origen <span class="requiredField">*</span>
                            </label>

                            <select id="ubigeo_origen"
                                name="ubigeo_origen"
                                required>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="direccion_origen">
                                Dirección origen <span class="optionalField">*</span>
                            </label>

                            <input type="text"
                                class="form-control"
                                id="direccion_origen"
                                name="direccion_origen"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="ubigeo_destino">
                                Ubigeo destino <span class="requiredField">*</span>
                            </label>

                            <select id="ubigeo_destino"
                                name="ubigeo_destino"
                                required>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="direccion_destino">
                                Dirección destino <span class="optionalField">*</span>
                            </label>

                            <input type="text"
                                class="form-control"
                                id="direccion_destino"
                                name="direccion_destino"
                                required>
                        </div>

                        <div class="col-md-12 my-1">
                            <label>
                                Tipo de operación <span class="requiredField">*</span>
                            </label>

                            <div class="igv-selector">

                                <input type="radio"
                                    class="btn-check"
                                    name="tipo_operacion"
                                    id="tipo_operacion_propio"
                                    value="PROPIO"
                                    checked>

                                <label class="igv-option" for="tipo_operacion_propio">
                                    <i class="fa-light fa-truck"></i>
                                    <span>Propio</span>
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="tipo_operacion"
                                    id="tipo_operacion_tercerizado"
                                    value="TERCERIZADO">

                                <label class="igv-option" for="tipo_operacion_tercerizado">
                                    <i class="fa-light fa-handshake"></i>
                                    <span>Tercerizado</span>
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="tipo_operacion"
                                    id="tipo_operacion_mixto"
                                    value="MIXTO">

                                <label class="igv-option d-none" for="tipo_operacion_mixto">
                                    <i class="fa-light fa-truck-fast"></i>
                                    <span>Mixto</span>
                                </label>

                            </div>
                        </div>

                        <div class="col-md-6 my-2" id="div_parentVehiculo">
                            <label>
                                Vehículo <span class="requiredField">*</span>
                                <a href="javascript:void(0)" class="open_modal_vehiculo">[+ Nuevo]</a>
                            </label>

                            <select class="form-select"
                                id="vehiculo"
                                name="vehiculo">

                                <option value="">Seleccione</option>

                                <?php if ($this->vehiculo["success"]): ?>
                                    <?php foreach ($this->vehiculo["message"] as $vehiculo): ?>

                                        <?php
                                        $text = $vehiculo["descripcion"] . " | " . $vehiculo["placa"];
                                        ?>

                                        <option value="<?= $vehiculo["id_vehiculo"] ?>"
                                            num-piso="<?= $vehiculo["num_piso"] ?>">
                                            <?= $text ?>
                                        </option>

                                    <?php endforeach ?>
                                <?php endif ?>

                            </select>
                        </div>

                        <div class="col-md-6 my-2" id="div_parentConductor">
                            <label>
                                Conductor <span class="requiredField">*</span>
                                <a href="javascript:void(0)" class="open_modal_conductor">[+ Nuevo]</a>
                            </label>

                            <select class="form-select"
                                id="conductor"
                                name="conductor">

                                <option value="">Seleccione</option>

                                <?php if ($this->conductor["success"]): ?>
                                    <?php foreach ($this->conductor["message"] as $conductor): ?>

                                        <?php
                                        $text = $conductor["nombres"] . " | " .
                                            $conductor["apellidos"] . " - " .
                                            $conductor["num_docu"];
                                        ?>

                                        <option value="<?= $conductor["id_usuario"] ?>">
                                            <?= $text ?>
                                        </option>

                                    <?php endforeach ?>
                                <?php endif ?>

                            </select>
                        </div>

                        <!-- DATOS DE TERCERIZACIÓN -->
                        <div class="col-md-12 d-none" id="div_parentTercerizado">

                            <div class="card border mt-2">
                                <div class="card-header bg-light">
                                    <strong>
                                        <i class="fa-light fa-handshake me-1"></i>
                                        Datos de tercerización
                                    </strong>
                                </div>

                                <div class="card-body">

                                    <div class="row g-2">

                                        <!-- Proveedor -->
                                        <div class="col-lg-4 col-md-12">
                                            <label for="proveedor">
                                                Proveedor / Transportista
                                                <span class="requiredField">*</span>
                                                <a href="javascript:void(0)" class="open_modal_proveedor">[+ Nuevo]</a>
                                            </label>

                                            <select
                                                class="form-select"
                                                id="proveedor"
                                                name="proveedor">

                                                <option value="">Seleccione</option>

                                                <?php if (!empty($this->proveedor["success"])): ?>
                                                    <?php foreach ($this->proveedor["message"] as $proveedor): ?>

                                                        <?php
                                                        $text =
                                                            ($proveedor["num_docu"] ?? '') . " | " .
                                                            ($proveedor["nombres"] ?? '') . " " .
                                                            ($proveedor["apellidos"] ?? '');
                                                        ?>

                                                        <option value="<?= $proveedor["id_usuario"] ?>">
                                                            <?= $text ?>
                                                        </option>

                                                    <?php endforeach ?>
                                                <?php endif ?>

                                            </select>
                                        </div>


                                        <!-- Vehículo tercero -->
                                        <div class="col-lg-4 col-md-6">
                                            <label for="vehiculo_tercerizado">
                                                Vehículo
                                                <span class="requiredField">*</span>
                                                <a href="javascript:void(0)" class="open_modal_vehiculo">[+ Nuevo]</a>
                                            </label>

                                            <select
                                                class="form-select"
                                                id="vehiculo_tercerizado"
                                                name="vehiculo_tercerizado">

                                                <option value="">Seleccione</option>

                                                <?php if ($this->vehiculo["success"]): ?>
                                                    <?php foreach ($this->vehiculo["message"] as $vehiculo): ?>

                                                        <?php
                                                        $text =
                                                            $vehiculo["descripcion"] .
                                                            " | " .
                                                            $vehiculo["placa"];
                                                        ?>

                                                        <option value="<?= $vehiculo["id_vehiculo"] ?>">
                                                            <?= $text ?>
                                                        </option>

                                                    <?php endforeach ?>
                                                <?php endif ?>

                                            </select>
                                        </div>


                                        <!-- Conductor tercero -->
                                        <div class="col-lg-4 col-md-6">
                                            <label for="conductor_tercerizado">
                                                Conductor
                                                <span class="requiredField">*</span>
                                                <a href="javascript:void(0)" class="open_modal_conductor">[+ Nuevo]</a>
                                            </label>

                                            <select
                                                class="form-select"
                                                id="conductor_tercerizado"
                                                name="conductor_tercerizado">

                                                <option value="">Seleccione</option>

                                                <?php if ($this->conductor["success"]): ?>
                                                    <?php foreach ($this->conductor["message"] as $conductor): ?>

                                                        <?php
                                                        $text =
                                                            $conductor["nombres"] . " | " .
                                                            $conductor["apellidos"] . " - " .
                                                            $conductor["num_docu"];
                                                        ?>

                                                        <option value="<?= $conductor["id_usuario"] ?>">
                                                            <?= $text ?>
                                                        </option>

                                                    <?php endforeach ?>
                                                <?php endif ?>

                                            </select>
                                        </div>


                                        <!-- Costo -->
                                        <div class="col-lg-3 col-md-6">
                                            <label for="costo_tercerizado">
                                                Costo de tercerización
                                                <span class="requiredField">*</span>
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    S/
                                                </span>

                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    id="costo_tercerizado"
                                                    name="costo_tercerizado"
                                                    min="0"
                                                    step="0.01"
                                                    placeholder="0.00">

                                            </div>
                                        </div>


                                        <!-- Estado -->
                                        <div class="col-lg-3 col-md-6">
                                            <label for="estado_tercerizado">
                                                Estado
                                            </label>

                                            <select
                                                class="form-select"
                                                id="estado_tercerizado"
                                                name="estado_tercerizado">

                                                <option value="PENDIENTE">
                                                    Pendiente
                                                </option>

                                                <option value="ASIGNADO">
                                                    Asignado
                                                </option>

                                                <option value="FINALIZADO">
                                                    Finalizado
                                                </option>

                                                <option value="CANCELADO">
                                                    Cancelado
                                                </option>

                                            </select>
                                        </div>


                                        <!-- Observación -->
                                        <div class="col-lg-6 col-md-12">
                                            <label for="observacion_tercerizado">
                                                Observación de tercerización
                                            </label>

                                            <textarea
                                                class="form-control"
                                                id="observacion_tercerizado"
                                                name="observacion_tercerizado"
                                                rows="3"
                                                placeholder="Observación sobre el proveedor, unidad o servicio"></textarea>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                        <div class="col-lg-3 col-md-6 my-2">
                            <label>
                                Fecha salida <span class="requiredField">*</span>
                            </label>

                            <input class="form-control"
                                type="date"
                                name="fecha_salida"
                                id="fecha_salida"
                                min="<?= date("Y-m-d") ?>"
                                value="<?= date("Y-m-d") ?>"
                                required>
                        </div>

                        <div class="col-lg-3 col-md-6 my-2">
                            <label>
                                Hora salida <span class="requiredField">*</span>
                            </label>

                            <input class="form-control"
                                type="time"
                                name="hora_salida"
                                id="hora_salida"
                                value="<?= date('H:i', strtotime('+1 hour')) ?>"
                                required>
                        </div>

                        <div class="col-lg-3 col-md-6 my-2">
                            <label>
                                Condición de pago <span class="requiredField">*</span>
                            </label>

                            <select class="form-select"
                                id="condicion_pago"
                                name="condicion_pago"
                                required>

                                <option value="CONTADO">Contado</option>
                                <option value="CREDITO">Crédito</option>

                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 my-2 d-none" id="div_parentDiasCredito">
                            <label>
                                Días de crédito <span class="requiredField">*</span>
                            </label>

                            <input class="form-control"
                                type="number"
                                id="dias_credito"
                                name="dias_credito"
                                min="1"
                                value="30">
                        </div>

                        <div class="col-md-3 my-2 d-none" id="div_parentFechaVencimiento">
                            <label>
                                Vencimiento
                            </label>

                            <input class="form-control"
                                type="date"
                                id="fecha_vencimiento"
                                name="fecha_vencimiento">
                        </div>

                        <div class="col-md-3 my-2">
                            <label>
                                Importe <span class="requiredField">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>

                                <input class="form-control"
                                    type="text"
                                    name="precio"
                                    id="precio"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4 my-2">
                            <label>
                                ¿Incluye IGV? <span class="requiredField">*</span>
                            </label>

                            <div class="igv-selector">

                                <input type="radio"
                                    class="btn-check"
                                    name="incluye_igv"
                                    id="incluye_igv_si"
                                    value="1"
                                    checked>

                                <label class="igv-option" for="incluye_igv_si">
                                    <i class="fa-light fa-circle-check"></i>
                                    <span>Sí</span>
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="incluye_igv"
                                    id="incluye_igv_no"
                                    value="0">

                                <label class="igv-option" for="incluye_igv_no">
                                    <i class="fa-light fa-circle-xmark"></i>
                                    <span>No</span>
                                </label>

                            </div>
                        </div>

                        <div class="col-md-3 my-2">
                            <label>
                                Total <span class="requiredField">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>

                                <input class="form-control"
                                    type="text"
                                    id="total"
                                    name="total"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-2 my-2" id="div_parentMoneda">
                            <label for="moneda">Moneda <span class="requiredField">*</span></label>
                            <select name="moneda" id="moneda" class="form-select">
                                <?php if ($this->monedas['success']): ?>
                                    <?php foreach ($this->monedas['message'] as $moneda): ?>
                                        <option value="<?= $moneda['id_tp_moneda'] ?>"><?= $moneda['descripcion'] ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <div class="col-md-12 my-2">
                            <label for="observacion" class="form-label">
                                Observación
                            </label>

                            <textarea class="form-control"
                                name="observacion"
                                id="observacion"
                                rows="4"
                                placeholder="Escribe aquí alguna observación"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                            class="btn btnCancel"
                            id="button_cancel"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="btn btnSave"
                            id="button_save">
                            Guardar
                        </button>

                        <button class="btn btnSave d-none"
                            type="button"
                            id="button_loadSave"
                            disabled>

                            <span class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true"></span>

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

<?php
include("./views/templates/modales/modal_cliente.php") ?>



<div class="modal fade" id="modal_facturar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">Nuevo Comprobante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_venta" class="needs-validation" novalidate>

                    <!-- Contexto de facturación -->
                    <input type="hidden" id="id_cotizacion_venta" name="id_cotizacion_venta">
                    <input type="hidden" id="id_flete_venta" name="id_flete_venta">
                    <input type="hidden" id="origen_venta" name="origen_venta">
                    <input type="hidden" id="tipo_comprobante_venta" name="tipo_comprobante_venta">
                    <?php
                    $caja = !empty($this->caja_userSesion["message"][0]["id_caja_chica"])
                        ? $this->caja_userSesion["message"][0]["id_caja_chica"]
                        : 0;
                    ?>
                    <input type="hidden" id="id_caja" name="id_caja" value="<?= $caja ?>">
                    <div
                        class="alert alert-light border d-none"
                        id="resumen_flete_facturacion">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="fw-semibold" id="factura_numero_flete"></div>

                                <small class="text-muted" id="factura_cliente_flete"></small>

                                <div class="mt-1" id="factura_ruta_flete"></div>
                            </div>

                            <div class="text-end">
                                <small class="text-muted d-block">
                                    Total
                                </small>

                                <span
                                    class="fw-bold fs-5"
                                    id="factura_total_flete">
                                </span>
                            </div>

                        </div>

                    </div>
                    <div class="row g-2 my-2">

                        <!-- Tipo comprobante -->
                        <div class="col-lg-3 col-md-6 col-12 mb-3" id="div_parentTpComprobante">
                            <label class="form-label">
                                Tipo de comprobante
                                <span class="requiredField">*</span>
                            </label>

                            <select
                                class="form-select"
                                id="tp_comprobante"
                                name="tp_comprobante"
                                required>

                                <option value="3">
                                    BOLETA DE VENTA ELECTRÓNICO
                                </option>

                                <option value="1">
                                    FACTURA ELECTRÓNICA
                                </option>

                            </select>
                        </div>


                        <!-- Serie -->
                        <div class="col-lg-3 col-md-6 col-12 mb-3" id="div_parentSerie">
                            <label class="form-label">
                                Serie
                                <span class="requiredField">*</span>
                            </label>

                            <select
                                class="form-select"
                                name="serie_venta"
                                id="serie_venta"
                                required>
                            </select>
                        </div>


                        <!-- Fecha emisión -->
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <label class="form-label">
                                Fecha de emisión
                                <span class="requiredField">*</span>
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="fecha_emision_venta"
                                id="fecha_emision_venta"
                                value="<?= date('Y-m-d') ?>"
                                required>
                        </div>


                        <!-- Forma de pago -->
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <label class="form-label">
                                Forma de Pago
                                <span class="requiredField">*</span>
                            </label>

                            <select
                                class="form-select"
                                name="forma_pago"
                                id="forma_pago"
                                required>

                                <option value="1">CONTADO</option>
                                <option value="2">CREDITO</option>

                            </select>
                        </div>


                        <!-- Medio de pago -->
                        <div class="col-lg-4 col-md-6 col-12 mb-3" id="div_parentMedioPago">
                            <label class="form-label">
                                Medio de Pago
                                <span class="requiredField">*</span>
                            </label>

                            <select
                                id="medio_pago"
                                name="medio_pago"
                                class="form-select"
                                required>

                                <option value="">Seleccione</option>

                                <?php if ($this->medio_pago["success"]): ?>
                                    <?php foreach ($this->medio_pago["message"] as $medio_pago): ?>

                                        <option value="<?= $medio_pago["id_medio_pago"] ?>">
                                            <?= $medio_pago["descripcion"] ?>
                                        </option>

                                    <?php endforeach ?>
                                <?php endif ?>

                            </select>
                        </div>

                    </div>
                    <div class="row g-2 my-2 d-none" id="div_cuota">
                        <div class="col-md-4 col-sm-6 col-12 mb-3">
                            <label class="form-label">
                                Tiempo de crédito
                                <span class="requiredField">*</span>
                            </label>

                            <select
                                class="form-select"
                                id="tiempo_credito"
                                name="tiempo_credito">

                                <option value="15">Factura a 15 días</option>
                                <option value="30" selected>Factura a 30 días</option>
                                <option value="45">Factura a 45 días</option>
                                <option value="60">Factura a 60 días</option>

                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6 col-12 mb-3">
                            <label class="form-label">F. de vencimiento<span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control" id="fecha_credito" name="fecha_credito">
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 col-12 mb-3">
                            <label class="form-label">Monto de cuota<span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>
                                <input type="number" class="form-control" placeholder="0.00" step="0.01"
                                    id="monto_credito" name="monto_credito">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancelP"
                            data-bs-dismiss="modal">Cancelar</button>
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



<div class="modal fade" id="modal_control_tercerizado" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <!-- HEADER -->
            <div
                class="modal-header"
                style="background-color:#495057;">

                <h5 class="modal-title text-white">
                    Control de Tercerización
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <!-- BODY -->
            <div class="modal-body">

                <input
                    type="hidden"
                    id="id_flete_tercerizado_pago">


                <!-- ================================================= -->
                <!-- INFORMACIÓN GENERAL -->
                <!-- ================================================= -->

                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-3">
                                <small class="text-muted">
                                    Flete
                                </small>

                                <div
                                    class="fw-semibold"
                                    id="tercerizado_numero_flete">
                                    -
                                </div>
                            </div>


                            <div class="col-md-5">
                                <small class="text-muted">
                                    Proveedor
                                </small>

                                <div
                                    class="fw-semibold"
                                    id="tercerizado_proveedor">
                                    -
                                </div>

                                <small
                                    class="text-muted"
                                    id="tercerizado_documento">
                                </small>
                            </div>


                            <div class="col-md-2">
                                <small class="text-muted">
                                    Estado operativo
                                </small>

                                <div>
                                    <span
                                        class="badge rounded-pill bg-secondary"
                                        id="tercerizado_estado">
                                        -
                                    </span>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <small class="text-muted">
                                    Estado de pago
                                </small>

                                <div>
                                    <span
                                        class="badge rounded-pill bg-secondary"
                                        id="tercerizado_estado_pago">
                                        -
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- RESUMEN FINANCIERO -->
                <!-- ================================================= -->

                <div class="row g-3 mb-4">

                    <div class="col-md-4">

                        <div class="card h-100 border-0 shadow-sm">

                            <div class="card-body">

                                <small class="text-muted">
                                    Costo acordado
                                </small>

                                <div
                                    class="fs-4 fw-bold"
                                    id="tercerizado_costo">
                                    S/ 0.00
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="card h-100 border-0 shadow-sm">

                            <div class="card-body">

                                <small class="text-muted">
                                    Total pagado
                                </small>

                                <div
                                    class="fs-4 fw-bold"
                                    id="tercerizado_pagado">
                                    S/ 0.00
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="card h-100 border-0 shadow-sm">

                            <div class="card-body">

                                <small class="text-muted">
                                    Saldo pendiente
                                </small>

                                <div
                                    class="fs-4 fw-bold"
                                    id="tercerizado_saldo">
                                    S/ 0.00
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- HISTORIAL -->
                <!-- ================================================= -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white">

                        <div
                            class="d-flex justify-content-between align-items-center">

                            <h6 class="mb-0">
                                Historial de pagos
                            </h6>

                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                id="btn_nuevo_pago_tercerizado">

                                <i class="bi bi-plus-circle me-1"></i>
                                Registrar pago

                            </button>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table
                                class="table table-hover align-middle mb-0"
                                id="table_pagos_tercerizado">

                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Medio de pago</th>
                                        <th>N° Operación</th>
                                        <th>Observación</th>
                                        <th class="text-end">Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody id="tbody_pagos_tercerizado">

                                    <tr>
                                        <td
                                            colspan="6"
                                            class="text-center text-muted py-4">

                                            Sin pagos registrados.

                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FORMULARIO NUEVO PAGO -->
                <!-- ================================================= -->

                <div
                    class="card border-0 shadow-sm d-none"
                    id="card_nuevo_pago_tercerizado">

                    <div class="card-header bg-white">

                        <h6 class="mb-0">
                            Registrar nuevo pago
                        </h6>

                    </div>


                    <div class="card-body">

                        <form id="form_pago_tercerizado" class="needs-validation" novalidate>
                            <input type="hidden" id="id_pago_tercerizado" name="id_pago" value="">
                            <div class="row g-3">

                                <!-- FECHA -->
                                <div class="col-md-3">

                                    <label class="form-label">
                                        Fecha de pago
                                        <span class="requiredField">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        id="fecha_pago_tercerizado"
                                        name="fecha_pago"
                                        value="<?= date('Y-m-d') ?>"
                                        required>

                                </div>


                                <!-- MONTO -->
                                <div class="col-md-3">

                                    <label class="form-label">
                                        Monto
                                        <span class="requiredField">*</span>
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            S/
                                        </span>

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="monto_pago_tercerizado"
                                            name="monto"
                                            min="0.01"
                                            step="0.01"
                                            placeholder="0.00"
                                            required>

                                    </div>

                                </div>


                                <!-- MEDIO PAGO -->
                                <div class="col-md-3">

                                    <label class="form-label">
                                        Medio de pago
                                    </label>

                                    <select
                                        class="form-select"
                                        id="medio_pago_tercerizado"
                                        name="id_medio_pago">

                                        <option value="">
                                            Seleccione
                                        </option>

                                        <?php if ($this->medio_pago["success"]): ?>

                                            <?php foreach ($this->medio_pago["message"] as $medio_pago): ?>

                                                <option
                                                    value="<?= $medio_pago["id_medio_pago"] ?>">

                                                    <?= $medio_pago["descripcion"] ?>

                                                </option>

                                            <?php endforeach ?>

                                        <?php endif ?>

                                    </select>

                                </div>


                                <!-- OPERACIÓN -->
                                <div class="col-md-3">

                                    <label class="form-label">
                                        N° Operación
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="numero_operacion_tercerizado"
                                        name="numero_operacion"
                                        maxlength="100">

                                </div>


                                <!-- OBSERVACIÓN -->
                                <div class="col-12">

                                    <label class="form-label">
                                        Observación
                                    </label>

                                    <textarea
                                        class="form-control"
                                        id="observacion_pago_tercerizado"
                                        name="observacion"
                                        rows="2"></textarea>

                                </div>

                            </div>


                            <div
                                class="d-flex justify-content-end gap-2 mt-3">

                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    id="btn_cancelar_pago_tercerizado">

                                    Cancelar

                                </button>


                                <button
                                    type="submit"
                                    class="btn btn-success"
                                    id="btn_guardar_pago_tercerizado">

                                    <i class="bi bi-check-circle me-1"></i>
                                    Registrar pago

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-success d-none"
                                    id="btn_guardando_pago_tercerizado"
                                    disabled>
                                    <span
                                        class="spinner-border spinner-border-sm me-1">
                                    </span>
                                    Guardando...
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- FOOTER -->
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cerrar
                </button>
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
                    id="btnEnviarSunatPreview">

                    <i class="fa-light fa-paper-plane me-1"></i>

                    Enviar a SUNAT

                </button>

            </div>

        </div>

    </div>

</div>

<?php
include("./views/templates/modales/modal_vehiculo.php") ?>

<?php
include("./views/templates/modales/modal_conductor.php") ?>

<?php
include("./views/templates/modales/modal_proveedor.php") ?>