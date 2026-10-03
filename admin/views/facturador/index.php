<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="row">
            <div class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0">
                <div class="row g-2">
                    <div
                        class=" mb-1 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                        <h2>
                            <a href="<?php echo URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                            Facturador
                        </h2>
                        <div>
                            <a class="btn btnAddRegis" href="<?= URL ?>listado_facturador">Listado Comprobantes</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 body-page">
                        <!-- Boton al final  -->
                        <form id="form" class="needs-validation" novalidate>
                            <div class="col-md-12 my-1 align-items-end">
                                <div class="col-md-12 row">
                                    <div class="col-md-3" id="div_parentTpComprobante">
                                        <label>Comprobante<span class="requiredField">*</span></label>
                                        <select class="form-select" id="tp_comprobante" name="tp_comprobante" required>
                                            <option value="2">NOTA DE VENTA</option>
                                            <option value="3">BOLETA DE VENTA ELECTRÓNICO</option>
                                            <option value="1">FACTURA ELECTRÓNICA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3" id="div_parentSerie">
                                        <label>Serie<span class="requiredField">*</span></label>
                                        <select class="form-select" name="serie_venta" id="serie_venta"></select>
                                    </div>
                                    <div class="col-md-3" id="div_parentFechaEmision">
                                        <label>Fecha de emisión<span class="requiredField">*</span></label>
                                        <input type="date" class="form-control" name="fecha_emision" id="fecha_emision"
                                            value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-3" id="div_parentFechaVencimiento">
                                        <label>F. de vencimiento<span class="requiredField">*</span></label>
                                        <input type="date" class="form-control" name="fecha_vencimiento"
                                            id="fecha_vencimiento" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12 my-1 row" id="div_parentCliente">
                                    <label>Cliente<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                            class="open_modal_cliente">[+ Nuevo]</a></label>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input class="form-control" type="text" id="cliente" name="cliente"
                                                minlength="8">
                                            <button type="button" class="btn btnS" id="buscar_cliente">Buscar</button>
                                            <button class="btn btnS d-none" type="button" id="loader_buscar"
                                                disabled="">
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                            </button>
                                            <input type="hidden" id="cliente_id" name="cliente_id">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="col-md-12 row my-1 align-items-end">
                            <div class="col-md-4" id="div_parentProducto">
                                <label>Producto <a href="javascript:void(0)" class="open_modal_producto">[+
                                        Nuevo]</a></label>
                                <select class="form-select" id="producto" name="producto" required>
                                    <option value="" precio="0.00">Seleccione</option>
                                    <?php if ($this->productos['success']): ?>
                                        <?php foreach ($this->productos["message"] as $producto): ?>
                                            <?php $text = $producto["nombre"] ?>
                                            <option value="<?= $producto["id"] ?>" precio="<?= $producto["valor_unitario"] ?>"
                                                afectacion="<?= $producto['tipo_afectacion_id'] ?>"
                                                factor_icbper="<?= $producto['factor_icbper'] ?>"><?= $text ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="cantidad">Cantidad<span class="requiredField">*</span></label>
                                <div class="number-input-wrapper">
                                    <button type="button" id="btn-minus" class="btn-number btn-minus" data-type="minus"
                                        data-field="cantidad">
                                        −
                                    </button>
                                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="1"
                                        min="1" max="100">
                                    <button type="button" id="btn-plus" class="btn-number btn-plus" data-type="plus"
                                        data-field="cantidad">
                                        +
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="cantidad">Precio unitario<span class="requiredField">*</span></label>
                                <input type="hidden" name="prod_afectacion" id="prod_afectacion">
                                <input type="hidden" name="icbper_prod" id="icbper_prod">
                                <input type="hidden" name="factor_icbper" id="factor_icbper">
                                <input type="number" name="precio_unit" id="precio_unit" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label for="cantidad">Total importe<span class="requiredField">*</span></label>
                                <input type="number" name="importe_total" id="importe_total" class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btnColorViolet" id="add_producto">Agregar</button>
                            </div>
                        </div>
                        <br>
                        <table class="table todo_list w-100" cellspacing="0" id="table_productos">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Cantidad</th>
                                    <th>Valor U.</th>
                                    <th>Precio U.</th>
                                    <th>Valor total</th>
                                    <th>Precio Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                            <td class="fs-6">Total</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td id="costo_total" class="fs-6">S/ 0.00</td>
                                            <td></td>
                                        </tr> -->
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">IGV:</td>
                                    <td id="igv_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">ICBPER:</td>
                                    <td id="icbper_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">Gravada:</td>
                                    <td id="gravada_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">Exonerada:</td>
                                    <td id="exonerada_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">Inafecta:</td>
                                    <td id="inafecta_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="fs-6">Total a pagar:</td>
                                    <td id="costo_total" class="fs-6">0.00</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Div detraccion -->
                    <div id="div_detraccion" class="row d-none">
                        <div class="col-md-12 my-1"
                            style="justify-content: center; align-items:center; text-align:center"><b>Datos generales
                                detracción</b></div>

                        <div class="col-md-6 my-1">
                            <label for="ubigeo_origen_d">Ubigeo origen <span class="requiredField">*</span></label>
                            <select id="ubigeo_origen_d" name="ubigeo_origen_d" required></select>
                        </div>
                        <div class="col-md-6 my-1">
                            <label for="origen_detraccion">Direccion origen <span class="requiredField">*</span></label>
                            <input type="text" class="form-control" name="origen_detraccion" id="origen_detraccion">
                        </div>
                        <div class="col-md-6 my-1">
                            <label for="ubigeo_destino_d">Ubigeo destino <span class="requiredField">*</span></label>
                            <select id="ubigeo_destino_d" name="ubigeo_destino_d" required></select>
                        </div>
                        <div class="col-md-6 my-1">
                            <label for="destino_detraccion">Direccion destino <span
                                    class="requiredField">*</span></label>
                            <input type="text" class="form-control" name="destino_detraccion" id="destino_detraccion">
                        </div>
                        <div class="col-md-6 my-1" id="parentMedioPagoDetraccion">
                            <label for="medio_pago_detraccion">Medio de pago detracción <span
                                    class="requiredField">*</span></label>
                            <select class="form-select" id="medio_pago_detraccion" name="medio_pago_detraccion">
                                <?php if ($this->tp_medio_pago["success"]): ?>
                                    <?php foreach ($this->tp_medio_pago['message'] as $tp_medio_p): ?>
                                        <option value="<?= $tp_medio_p['id'] ?>"><?= $tp_medio_p['descripcion'] ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-6 my-1">
                            <label for="detalle_detraccion">Detalle de viaje (*Se tiene que especificar el motivo de
                                viaje*) <span class="requiredField">*</span></label>
                            <input type="text" class="form-control" name="detalle_detraccion" id="detalle_detraccion">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label mb-2" style="font-size: 13px; font-weight: 600; color: #495057;">
                                Método de cálculo de detracción
                            </label>
                            <div class="custom-radio-group-horizontal">
                                <label class="custom-radio-horizontal">
                                    <input type="radio" name="metodo_detraccion" value="sunat" id="radio-sunat"
                                        checked />
                                    <div class="radio-horizontal-content">
                                        <span class="radio-horizontal-icon">📊</span>
                                        <div class="radio-horizontal-text">
                                            <strong>Según SUNAT</strong>
                                            <small>Valores referenciales oficiales</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="custom-radio-horizontal">
                                    <input type="radio" name="metodo_detraccion" value="directo" id="radio-directo" />
                                    <div class="radio-horizontal-content">
                                        <span class="radio-horizontal-icon">⚠️</span>
                                        <div class="radio-horizontal-text">
                                            <strong>Aplicar 4% directo</strong>
                                            <small>Bajo responsabilidad del usuario</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div id="div_calculos_detraccion" class="row">
                                <div class="col-md-12 centrado">
                                    <h6 class="mb-3"><b>Datos de cálculos detracción</b></h6>
                                </div>
                                <div class="col-md-4 my-1">
                                    <label for="total_operacion">Total Operacion <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="total_operacion"
                                        id="total_operacion">
                                </div>
                                <div class="col-md-4 my-1">
                                    <label for="tm_detraccion">TM detraccion <i
                                            class="uiverse fa-solid fa-circle-info"><span class="tooltip">Se refiere al
                                                peso total del producto en TONELADAS</span></i><span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="tm_detraccion" id="tm_detraccion">
                                </div>
                                <div class="col-md-4 my-1" id="ParentConfigVehiculo">
                                    <label for="config_vehiculo">C. vehiculo <span
                                            class="requiredField">*</span></label>
                                    <select class="form-select" id="config_vehiculo" name="config_vehiculo">
                                        <?php if ($this->config_vehiculo["success"]): ?>
                                            <?php foreach ($this->config_vehiculo['message'] as $config): ?>
                                                <option value="<?= $config['id'] ?>"
                                                    data-carga-util-tm="<?= $config['carga_util_tm'] ?>">
                                                    <?= $config['descripcion'] ?>
                                                </option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                                <div class="col-md-6 my-1">
                                    <label for="ruta_origen">Punto origen <span class="requiredField">*</span></label>
                                    <select id="ruta_origen" name="ruta_origen" required>
                                        <option value="0" selected>Lima</option>
                                    </select>
                                </div>
                                <div class="col-md-6 my-1">
                                    <label for="ruta_destino">Punto destino <span class="requiredField">*</span></label>
                                    <select id="ruta_destino" name="ruta_destino" required></select>
                                </div>
                            </div>
                            <div class="row" id="div_totales_detraccion">
                                <div class="col-md-3 my-1">
                                    <label for="v_ref_carga_efectiva">Valor referencia carga efectiva <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="v_ref_carga_efectiva"
                                        id="v_ref_carga_efectiva" readonly>
                                </div>
                                <div class="col-md-3 my-1">
                                    <label for="v_ref_carga_util">Valor referencial carga útil <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="v_ref_carga_util"
                                        id="v_ref_carga_util" readonly>
                                </div>
                                <div class="col-md-3 my-1">
                                    <label for="v_ref_servicio">Valor referencial servicio <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="v_ref_servicio" id="v_ref_servicio"
                                        style="font-weight:800; color:black;" readonly>
                                </div>
                                <div class="col-md-3 my-1">
                                    <label for="monto_detraccion">Monto detraccion <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="monto_detraccion"
                                        id="monto_detraccion" style="font-weight:900; color:black;" readonly>
                                    <input type="hidden" class="form-control" name="base_detraccion"
                                        id="base_detraccion" style="font-weight:900; color:black;" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin detraccion -->
                </div>
                <div class="col-md-12 body-page mt-4">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label>Observación<span class="requiredField me-2">*</span></label>
                            <textarea class="form-control" name="observacion" id="observacion" rows="3"
                                placeholder="Ingrese observaciones adicionales..."
                                style="resize: vertical; min-height: 80px;"></textarea>
                        </div>

                        <!-- FILA DE DATOS DE PAGO -->


                        <div class="col-md-12">
                            <div class="row align-items-end">
                                <!-- FORMA DE PAGO - MISMO TAMAÑO -->
                                <div class="col-md-2 col-sm-6 col-12 mb-3">
                                    <label>Forma de Pago<span class="requiredField me-2">*</span></label>
                                    <select class="form-control py-2" name="forma_pago" id="forma_pago">
                                        <option value="1">CONTADO</option>
                                        <option value="2">CREDITO</option>
                                    </select>
                                </div>
                                <!-- MÉTODO DE PAGO - TODOS CON MISMO TAMAÑO -->
                                <div class="col-md-2 col-sm-6 col-12 mb-3" id="div_parentMedioPago">
                                    <label>Medio de Pago<span class="requiredField">*</span></label>
                                    <select id="medio_pago" name="medio_pago" class="form-control" required>
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

                                <div class="col-md-2 col-sm-6 col-12 mb-3">
                                    <label>T. Operación<span class="requiredField me-2">*</span></label>
                                    <select class="form-control py-2" name="tp_operacion_venta" id="tp_operacion_venta">
                                        <option value="1">Venta lnterna</option>
                                        <option value="2">Operación Sujeta a Detracción - Servicios de Transporte Carga
                                        </option>
                                    </select>
                                </div>

                                <?php
                                $caja = !empty($this->caja_userSesion["message"][0]["id_caja_chica"])
                                    ? $this->caja_userSesion["message"][0]["id_caja_chica"]
                                    : 0;
                                ?>
                                <input type="hidden" id="id_caja" name="id_caja" value="<?= $caja ?>">

                                <!-- TOTAL A PAGAR - SIN S/ Y MISMO TAMAÑO -->
                                <div class="col-md-3 col-sm-6 col-12 mb-3">
                                    <label for="total">Total a pagar <span class="requiredField">*</span></label>
                                    <input type="number" id="total" name="total" class="form-control" readonly
                                        style="font-weight: bold; font-size: 1.1rem;">
                                </div>

                                <!-- BOTÓN GENERAR -->
                                <div class="col-md-3 col-sm-6 col-12 mb-3">
                                    <label>&nbsp;</label>
                                    <a class="btn btnPagar w-100" id="btnPagar"
                                        style="padding: 10px 0; font-size: 1rem;">
                                        <i class="bi bi-receipt me-2"></i>Generar
                                    </a>
                                </div>

                                <div class="row d-none" id="div_cuota">
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

                                    <div class="col-md-8 my-1 d-flex align-items-end justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            id="btn_agregar_cuota">
                                            <i class="bi bi-plus-circle"></i> Agregar cuota
                                        </button>
                                    </div>

                                    <div class="col-12 my-2">
                                        <table class="table table-sm table-bordered align-middle" id="tabla_cuotas">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:5%">#</th>
                                                    <th>F. de vencimiento<span class="requiredField ms-1">*</span></th>
                                                    <th>Monto de cuota<span class="requiredField ms-1">*</span></th>
                                                    <th style="width:5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_cuotas">
                                                <!-- Fila inicial (cuota 1) -->
                                                <tr class="fila_cuota">
                                                    <td class="text-center num_cuota">1</td>
                                                    <td>
                                                        <input type="date"
                                                            class="form-control form-control-sm fecha_credito_item"
                                                            name="fecha_credito[]" aria-label="fecha_credito">
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text py-0">$</span>
                                                            <input type="number" class="form-control monto_credito_item"
                                                                placeholder="0.00" step="0.01" name="monto_credito[]"
                                                                aria-label="monto_credito">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn_quitar_cuota"
                                                            disabled>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-end fw-bold">Total cuotas:</td>
                                                    <td class="fw-bold" id="total_cuotas_monto">$ 0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
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
</div>