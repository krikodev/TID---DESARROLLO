<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?php $automatico_p_bloque = $this->config_encomienda['success'] && $this->config_encomienda['message']['detectar_pago_bloque'] ? $this->config_encomienda['message']['detectar_pago_bloque'] : 0; ?>
            <input type="hidden" id="automatico_p_bloque" name="automatico_p_bloque"
                value="<?= $automatico_p_bloque ?>">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">ENCOMIENDA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_venta" class="needs-validation" autocomplete="off" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_venta" name="id_venta">
                        <div class="col-md-4 my-1" id="div_parentTpComprobante">
                            <label>Tipo de comprobante<span class="requiredField">*</span></label>
                            <select class="form-select" id="tp_comprobante" name="tp_comprobante" required>
                                <?php $nombre_nota_venta = $this->config_encomienda['success'] && $this->config_encomienda['message']['cambiar_n_NV'] == 1 ? $this->config_encomienda['message']['nombre_nota_venta'] : 'NOTA DE VENTA'; ?>
                                <option value="2"><?= $nombre_nota_venta ?></option>
                                <option value="31">GUIA R. TRANSPORTISTA</option>
                                <option value="3">BOLETA DE VENTA ELECTRÓNICO</option>
                                <option value="1">FACTURA ELECTRÓNICA</option>
                            </select>
                        </div>
                        <div class="col-md-2 my-1" id="div_parentSerieVenta">
                            <label>Serie<span class="requiredField">*</span></label>
                            <select class="form-select" name="serie_venta" id="serie_venta"></select>
                        </div>

                        <!-- Check de que si es salida  -->
                        <div class="col-md-2 my-1">
                            <label>Rutas? <span class="requiredField">*</span></label>
                            <div class="d-flex justify-content-center align-items-center" style="height: 38px;">
                                <input type="checkbox" class="form-check-input" id="e_salida" name="e_salida"
                                    style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                        <div class="col-md-2 my-1">
                            <label>Pago<span class="requiredField me-2">*</span></label>
                            <select class="form-select" name="pago" id="pago">
                                <option value="PAGADO">PAGADO</option>
                                <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                                <option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>
                            </select>
                        </div>
                        <div class="col-md-2 my-1">
                            <label>E. a domicilio<span class="requiredField">*</span></label>
                            <div class="d-flex justify-content-center align-items-center" style="height: 38px;">
                                <input type="checkbox" class="form-check-input" id="e_domicilio" name="e_domicilio"
                                    style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                        <?php $class_recibo = $this->config_encomienda['success'] && $this->config_encomienda['message']['numero_recibo'] == 1 ? '' : 'd-none' ?>
                        <div class="col-md-12 <?= $class_recibo ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="num_recibo_fisico">Número de recibo :<span
                                            class="optionalField">*</span></label>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control" type="text" name="num_recibo_fisico"
                                        id="num_recibo_fisico">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 my-1" id="div_parentRemitente">
                            <label>Remitente<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                    class="open_modal_remitente"><i class="bi bi-plus-circle"></i> Nuevo</a></label>
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input class="form-control" type="text" id="remitente" name="remitente"
                                        minlength="8">
                                    <button type="button" class="btn btnSearch" id="buscar_remitente">Buscar</button>
                                    <button class="btn btnSearch d-none" type="button" id="loader_buscar_r" disabled="">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                    <input type="hidden" id="remitente_id" name="remitente_id">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 my-1" id="div_parentDestinatario">
                            <label>Destinatario<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                    class="open_modal_destinatario"><i class="bi bi-plus-circle"></i> Nuevo</a> &nbsp;
                                <a href="javascript:void(0)" class="btn_varios">Varios?</a> </label>
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input class="form-control" type="text" id="destinatario" name="destinatario"
                                        minlength="8">
                                    <button type="button" class="btn btnSearch" id="buscar_destinatario">Buscar</button>
                                    <button class="btn btnSearch d-none" type="button" id="loader_buscar" disabled="">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                    <input type="hidden" id="destinatario_id" name="destinatario_id">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3 d-none" id="dato_destinatario">
                            <input type="hidden" id="d_extra" value="0">
                            <label for="obs_destinatario">Dato extra (Destinatario)</label>
                            <input class="form-control" type="text" id="obs_destinatario" name="obs_destinatario"
                                placeholder="Observación para el destinatario" autocomplete="off">
                        </div>

                        <div class="col-md-12 d-none" id="divParentSalida">
                            <div class="row">
                                <div class="col-lg-4 my-1">
                                    <label>Destino Ruta <span class="requiredField">*</span></label>
                                    <select id="destino_salida" name="destino_salida" required
                                        autocomplete="new-password"></select>
                                </div>
                                <div class="col-lg-8 my-1">
                                    <label>Ruta<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                            class="open_modal_salida"><i class="bi bi-plus-circle"></i>
                                            Nuevo</a></label>
                                    <select class="form-select" id="salida" name="salida" required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-12 d-none" id="datos_entrega">
                            <div class="row g-3">
                                <div class="col-md-4 my-1 " id="div_parentOr">
                                    <label>Punto de partida<span class="requiredField me-2">*</span></label>
                                    <input class="form-control" type="text" id="p_partida" name="p_partida"
                                        minlength="8"
                                        value="<?= $this->terminal_activo['message'][0]['direccion_comercial'] ?>">
                                </div>
                                <div class="col-md-8 my-1">
                                    <label>Ubigeo partida <span class="requiredField">*</span></label>
                                    <select id="ubigeoP" name="ubigeoP" required autocomplete="new-password"></select>
                                </div>
                                <div class="col-md-4 my-1 ">
                                    <label>Ubicación de partida <span class="text-muted">(opcional)</span></label>
                                </div>
                                <div class="col-md-8 my-1">
                                    <input class="form-control" type="url" id="linkP" name="linkP" minlength="8"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-4 my-1 " id="div_parentLle">
                                    <label>Punto de llegada<span class="requiredField me-2">*</span></label>
                                    <input class="form-control" type="text" id="p_llegada" name="p_llegada"
                                        minlength="8">
                                </div>
                                <div class="col-md-8 my-1">
                                    <label>Ubigeo llegada <span class="requiredField">*</span></label>
                                    <select id="ubigeoLle" name="ubigeoLle" autocomplete="new-password"
                                        required></select>
                                </div>
                                <div class="col-md-4 my-1 ">
                                    <label>Ubicación de llegada <span class="text-muted">(opcional)</span></label>
                                </div>
                                <div class="col-md-8 my-1">
                                    <input class="form-control" type="url" id="linkLle" name="linkLle" minlength="8">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 my-1 d-none" id="div_parentpagante">
                            <label>Pagador de flete<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                    class="open_modal_pagante"> <i class="bi bi-plus-circle"></i> Nuevo</a></label>
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input class="form-control" type="text" id="pagante" name="pagante" minlength="8">
                                    <button type="button" class="btn btnSearch" id="buscar_pagante">Buscar</button>
                                    <button class="btn btnSearch d-none" type="button" id="loader_buscar_p" disabled="">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                    <input type="hidden" id="pagante_id" name="pagante_id">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 my-1">
                            <label>Origen<span class="requiredField me-2">*</span></label>
                            <input type="text" class="form-control" id="origen_terminal" name="origen_terminal"
                                value="<?= $data_terminal["nombre"] ?>" readonly>
                        </div>
                        <div class="col-md-3 my-1" id="div_parentDestinoTerminal">
                            <label>Destino<span class="requiredField me-2">*</span></label>
                            <select name="destino_terminal" id="destino_terminal" required>
                                <option value="">Seleccione</option>
                                <?php if ($this->terminal_destino["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->terminal_destino["message"]); $i++): ?>
                                        <?php $text = $this->terminal_destino["message"][$i]["nombre"] ?>
                                        <option value="<?= $this->terminal_destino["message"][$i]["id_terminal"] ?>"
                                            data-ubigeo="<?= $this->terminal_destino["message"][$i]["ubigeo"] ?>"
                                            data-direccion="<?= $this->terminal_destino["message"][$i]["direccion_comercial"] ?>">
                                            <?= $text ?>
                                        </option>
                                    <?php endfor ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-lg-3 my-1">
                            <label>Contraseña<span class="requiredField me-2">*</span></label>
                            <input type="password" class="form-control" id="pass" name="pass" minlength="6"
                                maxlength="6" required autocomplete="new-password">
                        </div>
                        <div class="col-md-3 my-1" id="div_parentAlmacen">
                            <label>Almacén<span class="requiredField">*</span></label>
                            <select class="form-select" id="almacen" name="almacen" required>
                                <?php if ($this->almacen["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->almacen["message"]); $i++): ?>
                                        <?php $text = $this->almacen["message"][$i]["descripcion"] ?>
                                        <option value="<?= $this->almacen["message"][$i]["id_almacen"] ?>"><?= $text ?></option>
                                    <?php endfor ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <div class="col-md-12" id="divParentProgra">
                            <label>Programación<span class="requiredField">*</span></label>
                            <select class="form-select" id="programacion_guia_t" name="programacion_guia_t" required>
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#productos" aria-expanded="true" aria-controls="productos">
                                            Lista de productos
                                        </button>
                                    </h2>
                                    <div id="productos" class="accordion-collapse collapse show"
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-lg-2 my-1">
                                                    <label>T. precio<span class="requiredField me-2">*</span></label>
                                                    <?php
                                                    $default = $this->config_encomienda['success']
                                                        ? $this->config_encomienda['message']['default_tipo_precio']
                                                        : 'unidad';
                                                    ?>
                                                    <select class="form-select" name="tipo_precio" id="tipo_precio">
                                                        <option value="unidad" <?= $default === 'unidad' ? 'selected' : '' ?>>Por unidad</option>
                                                        <option value="peso" <?= $default === 'peso' ? 'selected' : '' ?>>
                                                            Por peso (Kg)</option>
                                                        <option value="peso_total" <?= $default === 'peso_total' ? 'selected' : '' ?>>Por peso total (Kg)</option>
                                                        <option value="precio_total" <?= $default === 'precio_total' ? 'selected' : '' ?>>Por precio total</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 my-1" id="div_cantidad">
                                                    <label>Cantidad<span class="requiredField me-2">*</span></label>
                                                    <input type="text" class="form-control" name="cantidad"
                                                        id="cantidad" value="1" min="1">
                                                </div>
                                                <div class="col-lg-2 my-1" id="div_unidad">
                                                    <label>Unidad M.<span class="requiredField me-2">*</span></label>
                                                    <select class="form-select" id="unidad_m" name="unidad_m" required>
                                                        <?php if ($this->unidad_medida["success"]): ?>
                                                            <?php foreach ($this->unidad_medida["message"] as $unidad_medida) { ?>
                                                                <?php $text = $unidad_medida["nombre"] ?>
                                                                <option value="<?= $unidad_medida["id"] ?>"><?= $text ?>
                                                                </option>
                                                            <?php } ?>
                                                        <?php endif ?>
                                                    </select>
                                                </div>

                                                <div class="col-lg-6 my-1" id="div_parentCtgProducto">
                                                    <label>Producto<span class="requiredField me-2">*</span><a
                                                            href="javascript:void(0)"
                                                            class="open_modal_ctgProducto">[+Nuevo]</a></label>
                                                    <select class="" id="ctg_producto" name="producto"
                                                        autocomplete="new-password" required>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 my-1" id="div_peso">
                                                    <label id="titulo_peso">Peso Unid.<span
                                                            class="requiredField me-2">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="peso" name="peso"
                                                            autocomplete="off" required value="0.50" step="1.00" min="0"
                                                            max="9999.99">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 my-1 d-none" id="div_precio_peso">
                                                    <label for="precio_kg">Precio x Kg<span
                                                            class="requiredField me-2">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="precio_kg"
                                                            name="precio_kg" autocomplete="off" required value="0.00"
                                                            step="1.00" min="0" max="9999.99">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 my-1" id="div_precio_unitario">
                                                    <label>P. Unitario<span class="requiredField me-2">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="precio_unitario"
                                                            id="precio_unitario" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 my-1 d-none" id="div_precio_total">
                                                    <label for="p_total">Precio total<span
                                                            class="requiredField me-2">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="p_total"
                                                            name="p_total" autocomplete="off" required value="0.00"
                                                            step="1.00" min="0" max="9999.99">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 my-1 d-none" id="div_parentRotulado">
                                                    <label>Rot.<span class="requiredField me-2">*</span></label>
                                                    <select class="form-select" name="rotulado_producto"
                                                        id="rotulado_producto">
                                                        <option value="1">SI</option>
                                                        <option value="0">NO</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 my-1">
                                                    <label>Observación</label>
                                                    <textarea class="form-control" name="obs_producto" id="obs_producto"
                                                        cols="30" rows="10"></textarea>
                                                </div>
                                                <div class="col-lg-auto my-2 d-flex align-items-end">
                                                    <button type="button" class="btn btnColorViolet"
                                                        id="add_producto">Agregar</button>
                                                </div>
                                                <div class="col-md-12 my-2">
                                                    <table class="table todo_list w-100" cellspacing="0"
                                                        id="table_productos">
                                                        <thead>
                                                            <tr>
                                                                <th>Cantidad</th>
                                                                <th>Unidad M.</th>
                                                                <th>Producto</th>
                                                                <th>Precio Ud.</th>
                                                                <th>Precio Kg</th>
                                                                <th>Peso Total</th>
                                                                <th>Sub Total.</th>
                                                                <th>Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="text-secondary fw-bolder"
                                                                style="background-color: #f8f8f8!important;">
                                                                <td class="fs-6">Total</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td id="peso_total" class="fs-6">0.00</td>
                                                                <td id="costo_total" class="fs-6">S/ 0.00</td>
                                                                <td></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 my-1">
                                        <label for="serieGR">Guia R. Serie
                                            <i class="uiverse fa-solid fa-circle-info"><span class="tooltip">Los datos
                                                    de la guia relacionada a la encomienda no es obligatorio (En caso no
                                                    exista, dejar en blanco)</span></i></label>
                                        <input type="text" class="form-control" name="serieGR" id="serieGR"
                                            style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase();">
                                    </div>
                                    <div class="col-lg-4 my-1">
                                        <label for="correlativoGR">Guia R. Correlativo</label>
                                        <input type="text" class="form-control" name="correlativoGR" id="correlativoGR"
                                            onkeypress="return controlTag(event);">
                                    </div>
                                    <div class="col-lg-4 my-1">
                                        <label for="rucGR">RUC Emisor</label>
                                        <input type="text" class="form-control" name="rucGR" id="rucGR"
                                            onkeypress="return controlTag(event);">
                                    </div>
                                </div>
                                <div id="div_CxE" class="row my-2">
                                    <div class="col-lg-3 my-1">
                                        <label>Comprobante</label>
                                        <select class="form-select" name="tp_comprobante_e" id="tp_comprobante_e">
                                            <option value="">SIN COMPROBANTE</option>
                                            <option value="01">FACTURA</option>
                                            <option value="03">BOLETA</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-9 row my-1 d-none" id="div_parentSerieEP"
                                        name="div_parentSerieEP">
                                        <div class="col-md-4">
                                            <label for="serieEP">Serie<span style="font-weight: 800;"
                                                    class="requiredField me-2">*</span></label>
                                            <input type="text" class="form-control" name="serieEP" id="serieEP"
                                                style="text-transform: uppercase;"
                                                oninput="this.value = this.value.toUpperCase();">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="correlativoEP">Correlativo<span
                                                    class="requiredField me-2">*</span></label>
                                            <input type="text" class="form-control" name="correlativoEP"
                                                id="correlativoEP" onkeypress="return controlTag(event);">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="rucEP">RUC Emisor<span
                                                    class="requiredField me-2">*</span></label>
                                            <input type="text" class="form-control" name="rucEP" id="rucEP"
                                                onkeypress="return controlTag(event);">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 my-1" id="div_parentMedioPago">
                            <label>M. Pago<span class="requiredField">*</span></label>
                            <select id="medio_pago" name="medio_pago" required>
                                <option value="">Seleccione</option>
                                <?php if ($this->medio_pago["success"]): ?>
                                    <?php for ($i = 0; $i < count($this->medio_pago["message"]); $i++): ?>
                                        <?php $text = $this->medio_pago["message"][$i]["descripcion"] ?>
                                        <option value="<?php echo $this->medio_pago["message"][$i]["id_medio_pago"] ?>
                                        ">
                                            <?php echo $text ?>
                                        </option>
                                    <?php endfor ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <?php if ($this->caja_userSesion["success"]): ?>
                            <input type="hidden" name="destino" id="destino"
                                value="<?php echo $this->caja_userSesion["message"][0]["id_caja_chica"]; ?>">
                        <?php endif ?>

                        <div class="col-md-3 my-1"> <!-- Aumentado de col-md-2 a col-md-3 -->
                            <label>Tipo operacion<span class="requiredField me-2">*</span></label>
                            <select class="form-select" id="tp_operacion_venta" name="tp_operacion_venta">
                                <option value="1">Venta lnterna</option>
                                <option value="2">Operación Sujeta a Detracción - Servicios de Transporte
                                    Carga</option>
                            </select>
                        </div>

                        <div class="col-md-2 my-1"> <!-- Aumentado de col-md-2 a col-md-3 -->
                            <label>F. de pago<span class="requiredField me-2">*</span></label>
                            <select class="form-select" name="forma_pago" id="forma_pago">
                                <option value="1">CONTADO</option>
                            </select>
                        </div>

                        <div class="col-md-2 my-1">
                            <label>T. operacion<span class="requiredField me-2">*</span></label>
                            <select class="form-select" name="t_operacion" id="t_operacion">
                                <option value="EXO">EXONERADA</option>
                                <option value="GRA">GRAVADA</option>
                                <option value="INA">INAFECTA</option>
                            </select>
                        </div>
                        <div class="col-md-2 my-1">
                            <label>Fecha salida<span class="requiredField">*</span></label>
                            <input type="date" class="form-control" name="fecha_salida" id="fecha_salida"
                                value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-12 my-1">
                            <label>Referencia y/o Observación</label>
                            <input type="text" class="form-control" name="referencia" id="referencia">
                        </div>
                        <div class="row d-none" id="div_cuota">
                            <div class="col-md-4 my-1">
                                <label>Tiempo de credito<span class="requiredField me-2">*</span></label>
                                <select class="form-select" aria-label="Seleccionar método de pago" id="tiempo_credito"
                                    name="tiempo_credito">
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
                        <div id="div_detraccion" class="row d-none">
                            <br>
                            <div class="col-md-12"
                                style="justify-content: center; align-items:center; text-align:center"><b>Datos
                                    generales detracción</b></div>
                            <br>

                            <div class="col-md-6 my-1">
                                <label for="ubigeo_origen_d">Ubigeo origen <span class="requiredField">*</span></label>
                                <select id="ubigeo_origen_d" name="ubigeo_origen_d" required></select>
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="origen_detraccion">Direccion origen <span
                                        class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="origen_detraccion" id="origen_detraccion">
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="ubigeo_destino_d">Ubigeo destino <span
                                        class="requiredField">*</span></label>
                                <select id="ubigeo_destino_d" name="ubigeo_destino_d" required></select>
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="destino_detraccion">Direccion destino <span
                                        class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="destino_detraccion"
                                    id="destino_detraccion">
                            </div>
                            <div class="col-md-6 my-1" id="parentMedioPagoDetraccion">
                                <label for="medio_pago_detraccion">Medio de pago detracción <span
                                        class="requiredField">*</span></label>
                                <select class="form-select" id="medio_pago_detraccion" name="medio_pago_detraccion">
                                    <?php if ($this->tp_medio_pago["success"]): ?>
                                        <?php foreach ($this->tp_medio_pago['message'] as $tp_medio_p): ?>
                                            <option value="<?= $tp_medio_p['id'] ?>">
                                                <?= $tp_medio_p['descripcion'] ?>
                                            </option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-md-12 my-1">
                                <label for="detalle_detraccion">Detalle de viaje (*Se tiene que especificar el
                                    motivo de viaje*) <span class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="detalle_detraccion"
                                    id="detalle_detraccion">
                            </div>
                            <br>
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

                                <div class="col-md-12"
                                    style="justify-content: center; align-items:center; text-align:center">
                                    <b>Datos de calculos detracción</b>
                                </div>
                                <br>
                                <div class="col-md-4 my-1">
                                    <label for="total_operacion">Total Operacion <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="total_operacion"
                                        id="total_operacion">
                                </div>
                                <div class="col-md-4 my-1">
                                    <label for="tm_detraccion">TM detraccion <span
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
                            <div class="col-md-3 my-1">
                                <label for="v_ref_carga_efectiva">V. ref carga efectiva <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="v_ref_carga_efectiva"
                                    id="v_ref_carga_efectiva" readonly>
                            </div>
                            <div class="col-md-3 my-1">
                                <label for="v_ref_carga_util">Valor referencial carga útil <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="v_ref_carga_util" id="v_ref_carga_util"
                                    readonly>
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
                                <input type="number" class="form-control" name="monto_detraccion" id="monto_detraccion"
                                    style="font-weight:900; color:black;" readonly>
                                <input type="hidden" class="form-control" name="base_detraccion" id="base_detraccion"
                                    style="font-weight:900; color:black;" readonly>
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

<div class="modal fade" id="modal_entregar" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">ENTREGAR ENCOMIENDA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-12 col-lg-6">
                    <form id="form_buscarEncomienda" method="post" class="needs-validation" novalidate>
                        <div class="row g-2 my-2">
                            <div class="col-lg-8 row">
                                <input type="hidden" id="id_venta_e" name="id_venta_e">
                                <div class="col-lg-7 my-2" id="div_parentTpComprobante">
                                    <label>Nro. Documento (DESTINATARIO)<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="num_docuEntregar"
                                        name="num_docuEntregar" minlength="8" maxlength="11" required>
                                </div>
                                <div class="col-lg-auto my-2 d-flex align-items-end">
                                    <button type="submit" class="btn btnSearch"
                                        id="button_buscarEncomienda">Buscar</button>
                                    <button class="btn btnSearch d-none" type="button"
                                        id="button_loadSaveBuscarEncomienda" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Buscando...
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-4" id="datos_destinatarioEntregar"></div>
                        </div>
                    </form>
                    <table class="table todo_list w-100 my-3" cellspacing="0" id="table_encomiendas">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Origen</th>
                                <th>Remitente</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Fecha salida</th>
                                <th>Precio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <label class="w-100 text-center text-secondary fw-bolder" id="message_sinEncomiendas">Sin
                        encomiendas</label>
                </div>
                <div class="col-12 col-lg-6">
                    <table class="table todo_list w-100 my-3" cellspacing="0" id="table_productosEntregar">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Rotulado</th>
                                <th>Observación</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <section>
                        <div id="div_datosReceptor" class="col-md-12 row d-none">
                            <label>Receptor<span class="requiredField me-2">*</span><a href="javascript:void(0)"
                                    class="open_modal_receptor">[+ Nuevo]</a></label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input class="form-control" type="text" id="receptor" name="receptor" minlength="8">
                                    <button type="button" class="btn btnSearch" id="buscar_receptor">Buscar</button>
                                    <button class="btn btnSearch d-none" type="button" id="loader_buscarRe" disabled="">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                    <input type="hidden" id="receptor_id" name="receptor_id">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn text-white bg-light-aqua" id="button_registrarR"
                                    name="button_registrarR">Registrar</button>
                                <button class="btn text-white bg-light-aqua d-none" type="button"
                                    id="button_loadRegistrarR" disabled>
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Registrando...
                                </button>
                            </div>
                        </div>
                    </section>
                    <hr>
                    <section class="d-none" id="section_opcionesEntregar">
                        <input type="checkbox" class="btn-check" id="habilitarDivGenerarComprobante" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="habilitarDivGenerarComprobante">Generar
                            comprobante</label><br>

                        <div id="div_generarComprobante" class="row d-none">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="options_generarComprobante"
                                    id="opcion_dataOrigen" value="dataOrigen" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary" for="opcion_dataOrigen">Con datos de
                                    Remitente</label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="options_generarComprobante"
                                    id="opcion_dataDestino" value="dataDestino" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary" for="opcion_dataDestino">Con datos de
                                    Destinatario</label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="options_generarComprobante"
                                    id="opcion_dataNueva" value="dataNueva" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="opcion_dataNueva">Con nuevo
                                    Remitente</label>
                            </div>
                            <div class="col-md-12 row">
                                <div class="col-md-4 my-2" id="div_parentTpComprobanteEntregar">
                                    <label>Tipo de comprobante<span class="requiredField">*</span></label>
                                    <select class="form-select" id="tp_comprobanteEntregar"
                                        name="tp_comprobanteEntregar" required>
                                        <option value="3">BOLETA DE VENTA ELECTRÓNICO</option>
                                        <option value="1">FACTURA ELECTRÓNICA</option>
                                    </select>
                                </div>
                                <div class="col-md-3 my-2" id="div_parentSerieVentaEntregar">
                                    <label>Serie<span class="requiredField">*</span></label>
                                    <select class="form-select" name="serie_ventaEntregar"
                                        id="serie_ventaEntregar"></select>
                                </div>
                            </div>
                            <div class="col-md-12 row" id="div_generarNuevoComprobante">
                                <div class="col-md-12 my-2 row" id="div_parentRemitenteEntregar">
                                    <label>Remitente<span class="requiredField me-2">*</span><a
                                            href="javascript:void(0)" class="open_modal_cliente">[+ Nuevo]</a></label>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input class="form-control" type="text" id="remitenteEntregar"
                                                name="remitenteEntregar" minlength="8">
                                            <button type="button" class="btn btnSearch"
                                                id="buscar_remitenteEntregar">Buscar</button>
                                            <button class="btn btnSearch d-none" type="button" id="loader_buscar_rE"
                                                disabled="">
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                            </button>
                                            <input type="hidden" id="remitenteEntregar_id" name="remitenteEntregar_id">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <hr>
                    <section class="d-flex justify-content-center" id="div_imprimirComprobanteOrigen"></section>
                    <hr>
                    <section id="div_porPagar" class="d-none">
                        <div class="alert alert-warning" role="alert">
                            Para <strong>entregar la encomienda</strong> o <strong>generar un comprobante</strong>, debe
                            realizar el pago correspondiente.
                        </div>
                        <div class="row">
                            <div class="col-md-4 my-2" id="div_parentMedioPagoEntregar">
                                <label>M. Pago<span class="requiredField">*</span></label>
                                <select id="medio_pagoEntregar" name="medio_pagoEntregar" required>
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
                            <div class="col-md-4 my-2 d-none" id="div_parentDestinoCjEntregar">
                                <label><span class="fw-bolder">CAJA CHICA</span><span
                                        class="requiredField">*</span></label>
                                <?php if ($this->caja_userSesion["success"]): ?>
                                    <input type="text" class="form-control" name="id_caja_chica_entregar"
                                        id="id_caja_chica_entregar" autocomplete="off"
                                        value="<?php echo $this->caja_userSesion["message"][0]["referencia"]; ?>" disabled>
                                    <input type="hidden" name="destino_entregar" id="destino_entregar"
                                        value="<?php echo $this->caja_userSesion["message"][0]["id_caja_chica"]; ?>">
                                <?php endif ?>
                            </div>
                            <div class="col-md-4 my-2">
                                <label>Tipo operacion<span class="requiredField me-2">*</span></label>
                                <select class="form-select" id="tp_operacion_ventaEntregar"
                                    name="tp_operacion_ventaEntregar">
                                    <option value="1">Venta lnterna</option>
                                </select>
                            </div>
                            <div class="col-md-4 my-2">
                                <label>F. de pago<span class="requiredField me-2">*</span></label>
                                <select class="form-select" name="forma_pagoEntregar" id="forma_pagoEntregar">
                                    <option value="1">CONTADO</option>
                                </select>
                            </div>
                        </div>
                        <div class="row d-none" id="div_cuotaEntregar">
                            <div class="col-md-4 my-1">
                                <label>Tiempo de credito<span class="requiredField me-2">*</span></label>
                                <select class="form-select" aria-label="Seleccionar método de pago"
                                    id="tiempo_creditoEntregar" name="tiempo_creditoEntregar">
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
                                    <input type="date" class="form-control" aria-label="fecha_creditoEntregar"
                                        id="fecha_creditoEntregar" name="fecha_creditoEntregar">
                                </div>
                            </div>
                            <div class="col-md-4 my-1">
                                <label>Monto de cuota<span class="requiredField me-2">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text py-0">$</span>
                                    <input type="number" class="form-control" placeholder="0.00" step="0.01"
                                        aria-label="monto_creditoEntregar" id="monto_creditoEntregar"
                                        name="monto_creditoEntregar" readonly>
                                </div>
                            </div>
                        </div>
                        <div id="div_detraccionEntregar" class="row d-none">
                            <br>
                            <div class="col-md-12"
                                style="justify-content: center; align-items:center; text-align:center"><b>Datos
                                    generales detracción</b></div>
                            <br>

                            <div class="col-md-6 my-1">
                                <label for="ubigeo_origen_dEntregar">Ubigeo origen <span
                                        class="requiredField">*</span></label>
                                <select id="ubigeo_origen_dEntregar" name="ubigeo_origen_dEntregar" required></select>
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="origen_detraccionEntregar">Direccion origen <span
                                        class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="origen_detraccionEntregar"
                                    id="origen_detraccionEntregar">
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="ubigeo_destino_dEntregar">Ubigeo destino <span
                                        class="requiredField">*</span></label>
                                <select id="ubigeo_destino_dEntregar" name="ubigeo_destino_dEntregar" required></select>
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="destino_detraccionEntregar">Direccion destino <span
                                        class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="destino_detraccionEntregar"
                                    id="destino_detraccionEntregar">
                            </div>
                            <div class="col-md-6 my-1" id="parentMedioPagoDetraccionEntregar">
                                <label for="medio_pago_detraccionEntregar">Medio de pago detracción <span
                                        class="requiredField">*</span></label>
                                <select class="" id="medio_pago_detraccionEntregar"
                                    name="medio_pago_detraccionEntregar">
                                    <?php if ($this->tp_medio_pago["success"]): ?>
                                        <?php foreach ($this->tp_medio_pago['message'] as $tp_medio_p): ?>
                                            <option value="<?= $tp_medio_p['id'] ?>"><?= $tp_medio_p['descripcion'] ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-md-6 my-1">
                                <label for="detalle_detraccionEntregar">Detalle de viaje (*Se tiene que especificar el
                                    motivo de viaje*) <span class="requiredField">*</span></label>
                                <input type="text" class="form-control" name="detalle_detraccionEntregar"
                                    id="detalle_detraccionEntregar">
                            </div>
                            <br>
                            <div class="custom-radio-group-horizontal">
                                <label class="custom-radio-horizontal">
                                    <input type="radio" name="metodo_detraccionEntregar" value="sunat" id="radio-sunat"
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
                                    <input type="radio" name="metodo_detraccionEntregar" value="directo"
                                        id="radio-directo" />
                                    <div class="radio-horizontal-content">
                                        <span class="radio-horizontal-icon">⚠️</span>
                                        <div class="radio-horizontal-text">
                                            <strong>Aplicar 4% directo</strong>
                                            <small>Bajo responsabilidad del usuario</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div id="div_calculos_detraccionEntregar" class="row">

                                <div class="col-md-12"
                                    style="justify-content: center; align-items:center; text-align:center"><b>Datos de
                                        calculos detracción</b></div>
                                <br>
                                <div class="col-md-4 my-1">
                                    <label for="total_operacionEntregar">Total Operacion <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="total_operacionEntregar"
                                        id="total_operacionEntregar">
                                </div>
                                <div class="col-md-4 my-1">
                                    <label for="tm_detraccionEntregar">TM detraccion <span
                                            class="requiredField">*</span></label>
                                    <input type="number" class="form-control" name="tm_detraccionEntregar"
                                        id="tm_detraccionEntregar">
                                </div>
                                <div class="col-md-4 my-1" id="ParentConfigVehiculo">
                                    <label for="config_vehiculoEntregar">C. vehiculo <span
                                            class="requiredField">*</span></label>
                                    <select class="form-select" id="config_vehiculoEntregar"
                                        name="config_vehiculoEntregar">
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
                                    <label for="ruta_origenEntregar">Punto origen <span
                                            class="requiredField">*</span></label>
                                    <select id="ruta_origenEntregar" name="ruta_origenEntregar" required>
                                        <option value="0" selected>Lima</option>
                                    </select>
                                </div>
                                <div class="col-md-6 my-1">
                                    <label for="ruta_destinoEntregar">Punto destino <span
                                            class="requiredField">*</span></label>
                                    <select id="ruta_destinoEntregar" name="ruta_destinoEntregar" required></select>
                                </div>
                            </div>
                            <div class="col-md-3 my-1">
                                <label for="v_ref_carga_efectivaEntregar">V. ref carga efectiva <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="v_ref_carga_efectivaEntregar"
                                    id="v_ref_carga_efectivaEntregar" readonly>
                            </div>
                            <div class="col-md-3 my-1">
                                <label for="v_ref_carga_utilEntregar">Valor ref carga útil <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="v_ref_carga_utilEntregar"
                                    id="v_ref_carga_utilEntregar" readonly>
                            </div>
                            <div class="col-md-3 my-1">
                                <label for="v_ref_servicioEntregar">Valor ref servicio <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="v_ref_servicioEntregar"
                                    id="v_ref_servicioEntregar" style="font-weight:800; color:black;" readonly>
                            </div>
                            <div class="col-md-3 my-1">
                                <label for="monto_detraccionEntregar">Monto detraccion <span
                                        class="requiredField">*</span></label>
                                <input type="number" class="form-control" name="monto_detraccionEntregar"
                                    id="monto_detraccionEntregar" style="font-weight:900; color:black;" readonly>
                                <input type="hidden" class="form-control" name="base_detraccionEntregar"
                                    id="base_detraccionEntregar" style="font-weight:900; color:black;" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 my-2">
                                <label>Observación</label>
                                <input type="text" class="form-control" name="obs_ventaEntregar" id="obs_ventaEntregar">
                            </div>
                            <div class="col-auto my-2 d-flex align-items-end">
                                <button type="button" class="btn text-white bg-light-aqua"
                                    id="button_pagar">Pagar</button>
                                <button class="btn text-white bg-light-aqua d-none" type="button" id="button_loadPagar"
                                    disabled>
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Pagando...
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btnEntregar btnSave d-none" id="button_entregar">Entregar</button>
                <button class="btn btnSave d-none" type="button" id="button_loadEntregar" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Entregar...
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_pasajero" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo">NUEVO CLIENTE / PASAJERO</h5>
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

<div class="modal fade" id="modal_ctgEncomienda" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">NUEVO PRODUCTO ENCOMIENDA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_ctg_encomienda" name="id_ctg_encomienda">
                        <div class="col-md-7 my-2">
                            <label>Descripción<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                autocomplete="off" required>
                        </div>
                        <div class="col-md-5 my-2">
                            <label>Precio Ud.<span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>
                                <input type="text" class="form-control" id="precio" name="precio" autocomplete="off"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-5 my-2">
                            <label>Afectación<span class="requiredField">*</span></label>
                            <select class="form-select" name="afectacion" id="afectacion" required>
                                <option value="IGV">IGV Impuesto general a las ventas</option>
                                <option value="IVAP">Impuesto a la venta arroz pilado</option>
                                <option value="ISC">ISC Impuesto selectivo al consumo</option>
                                <option value="EXP">Exportación</option>
                                <option value="GRA">Gratuito</option>
                                <option value="EXO">Exonerado</option>
                                <option value="INA">Inafecto</option>
                                <option value="OTR">Otros conceptos de pago</option>
                            </select>
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

<div class="modal fade" id="modal_embarcar" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="title_embarcar">EMBARCAR ENCOMIENDA</h5>
                <h5 class="modal-title text-white" style="margin-left: auto; text-align: right;">Origen:
                    <?php echo $data_terminal["nombre"] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-12 row">
                    <div class="col-md-2 my-2"> <!-- Reducido de col-md-3 a col-md-2 -->
                        <label>Desde<span class="requiredField me-2">*</span></label>
                        <input type="date" class="form-control" name="fecha_inicio_embarcar" id="fecha_inicio_embarcar">
                    </div>
                    <div class="col-md-2 my-2">
                        <label>F. Fin<span class="requiredField">*</span></label>
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                            value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2 my-2">
                        <label for="estado_encomiendas">Estado<span class="requiredField">*</span></label>
                        <select class="form-select" name="estado_encomiendas" id="estado_encomiendas">
                            <option value="TODOS">TODOS</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="POR_PAGAR">POR PAGAR</option>
                        </select>
                    </div>
                    <div class="col-md-2 my-2" id="div_parentTerminalDestinoEmbarcar">
                        <label>Terminal Destino<span class="requiredField">*</span></label>
                        <select class="form-selec" style="font-weight: bold; font-weight:bold"
                            name="terminal_destinoEmbarcar" id="terminal_destinoEmbarcar">
                            <?php if ($this->terminal_destino["success"]): ?>
                                <?php for ($i = 0; $i < count($this->terminal_destino["message"]); $i++): ?>
                                    <?php $text = $this->terminal_destino["message"][$i]["nombre"] ?>
                                    <option value="<?= $this->terminal_destino["message"][$i]["id_terminal"] ?>"><?= $text ?>
                                    </option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="col-md-4 my-2" id="div_parentProgramacionDestinoEmbarcar">
                        <label>Programación<span class="requiredField">*</span><i
                                class="fa-solid fa-circle-info ms-2 info_programacion"></i></label>
                        <select class="form-select" name="programacion_destinoEmbarcar"
                            id="programacion_destinoEmbarcar"></select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-2 d-flex justify-content-between my-4">
                        <div class="col-md-4">
                            <button type="button" class="btn text-white" style="background-color: #8338ec;"
                                id="btn_seleccionarTodoEncomienda">Seleccionar todo</button>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control inputSearchEmbarcar" placeholder="Buscar">
                        </div>
                    </div>
                    <table class="table w-100 my-3" cellspacing="0" id="table_encomiendaEmbarcar">
                        <thead>
                            <tr>
                                <th>Almacen</th>
                                <th>Numero</th>
                                <th>Fecha_emision</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Destino</th>
                                <th>Tipo</th>
                                <th>Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btnEmbarcar btnSave d-none" id="button_embarcar">Embarcar</button>
                <button class="btn btnSave d-none" type="button" id="button_loadEmbarcar" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Embarcando...
                </button>
            </div>
        </div>
    </div>
</div>
<div id="productosTooltip" class="productos-tooltip"></div>

<div class="modal fade" id="modal_desembarcar" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">DESEMBARCAR ENCOMIENDA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-12 row">
                    <div class="col-md-4 my-2">
                        <label>Placa<span class="requiredField">*</span></label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="placa_vehiculoDesembarcar"
                                id="placa_vehiculoDesembarcar">
                            <button type="button" class="btn btnSearch"
                                id="button_searchEncomiendaDesembarcar">BUSCAR</button>
                            <button class="btn btnSearch d-none" type="button" id="button_loadSearch" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                BUSCAR
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 my-2 d-none" id="div_parentAlmacenDesembarcar">
                        <label>Almacén<span class="requiredField">*</span></label>
                        <select class="form-select" id="almacen_desembarcar" name="almacen_desembarcar" required>
                            <?php if ($this->almacen["success"]): ?>
                                <?php for ($i = 0; $i < count($this->almacen["message"]); $i++): ?>
                                    <?php $text = $this->almacen["message"][$i]["descripcion"] ?>
                                    <option value="<?php echo $this->almacen["message"][$i]["id_almacen"] ?>">
                                        <?php echo $text ?>
                                    </option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-12 d-none" id="div_tableEncomiendaDesembarcar">
                    <div class="row g-2 d-flex justify-content-between my-4">
                        <div class="col-md-4">
                            <button type="button" class="btn text-white" style="background-color: #8338ec;"
                                id="btn_selectedTodoEncomiendaDesembarcar">Seleccionar todo</button>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control inputSearchDesembarcar" placeholder="Buscar">
                        </div>
                    </div>
                    <table class="table w-100 my-3" cellspacing="0" id="table_encomiendaDesembarcar">
                        <thead>
                            <tr>
                                <th>Almacen</th>
                                <th>Numero</th>
                                <th>Fecha E.</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btnDesembarcar btnSave d-none"
                    id="button_desembarcar">Desembarcar</button>
                <button class="btn btnSave d-none" type="button" id="button_loadDesembarcar" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Desembarcando...
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_reporte" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">REPORTES</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-12">
                    <div class="col-3 my-2" id="div_parentTpReporte">
                        <label>Tipo de Reporte<span class="requiredField">*</span></label>
                        <select class="form-select filtro_reporte" name="tp_reporte" id="tp_reporte">
                            <option value="general">General</option>
                            <option value="origen_destino">Origen - Destino</option>
                            <option value="vehiculo">Vehículo</option>
                            <option value="cliente">Cliente</option>
                        </select>
                    </div>
                </div>
                <div class="col-3 my-2 d-none" id="div_parentTerminalOrigenReporte">
                    <label>Terminal Origen<span class="requiredField">*</span></label>
                    <select class="form-select filtro_reporte" id="terminal_origenReporte" name="terminal_origenReporte"
                        required>
                        <?php if ($this->terminal_origen["success"]): ?>
                            <option value="">Seleccione</option>
                            <?php for ($i = 0; $i < count($this->terminal_origen["message"]); $i++): ?>
                                <?php $text = $this->terminal_origen["message"][$i]["nombre"] ?>
                                <option value="<?php echo $this->terminal_origen["message"][$i]["id_terminal"] ?>">
                                    <?php echo $text ?>
                                </option>
                            <?php endfor ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-3 my-2 d-none" id="div_parentTerminalDestinoReporte">
                    <label>Terminal Destino<span class="requiredField">*</span></label>
                    <select class="form-select filtro_reporte" name="terminal_destinoReporte"
                        id="terminal_destinoReporte"></select>
                </div>
                <div class="col-3 my-2 d-none" id="div_parentProgramacionDestinoReporte">
                    <label>Programación<span class="requiredField">*</span><i
                            class="fa-solid fa-circle-info ms-2 info_programacion"></i></label>
                    <select class="form-select filtro_reporte" name="programacion_destinoReporte"
                        id="programacion_destinoReporte"></select>
                </div>
                <div class="col-md-3 my-2 d-none" id="div_parentClienteReporte">
                    <label>Cliente<span class="requiredField me-2">*</span></label>
                    <select class="form-select filtro_reporte" name="cliente_reporte" id="cliente_reporte" required>
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div class="col-3 my-2 d-none">
                    <label>Fecha Inicio<span class="requiredField">*</span></label>
                    <input type="date" class="form-control filtro_reporte" id="fecha_inicio" name="fecha_inicio"
                        value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-3 my-2 d-none">
                    <label>Fecha Fin<span class="requiredField">*</span></label>
                    <input type="date" class="form-control filtro_reporte" id="fecha_fin" name="fecha_fin"
                        value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-auto my-2 d-flex align-items-end">
                    <button type="button" class="btn btnSearch text-white" id="btn_generarReporte">Generar</button>
                </div>
            </div>
            <div class="d-flex justify-content-center align-items-center d-none" id="div_imprimir"></div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de creacion de embarque y creacion de guias de remision -->
<div class="modal fade" id="modal_crear_guia" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white">SELECCIONAR PROGRAMACION</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <input type="hidden" id="id_encomiendaCrearGuia" name="id_encomiendaCrearGuia">
                <input type="hidden" id="id_destinoCrearGuia" name="id_destinoCrearGuia">
                <div class="col-md-12 my-2" id="div_parentProDestinoCrearGuia">
                    <label>Programación<span class="requiredField">*</span><i
                            class="fa-solid fa-circle-info ms-2 info_programacion"></i></label>
                    <select class="form-select" name="pro_destinoCrearGuia" id="pro_destinoCrearGuia"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel_crear_guia"
                    data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btnSave" id="button_crearguia">Crear Guia</button>
                <button class="btn btnSave d-none" type="button" id="button_loadCrearGuia" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Creando...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de links Maps partida y llegada -->
<div class="modal fade" id="modal_links" data-bs-backdrop="static" data-bs-focus="false" data-bs-keyboard="false"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white">LINKS PARTIDA - LLEGADA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row">
                <div class="col-md-12 my-2">
                    <label for="link_p">Link Partida</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="link_p" name="link_p" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('link_p')"
                            title="Copiar">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-12 my-2">
                    <label for="link_lle">Link Llegada</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="link_lle" name="link_lle" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('link_lle')"
                            title="Copiar">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btnClose" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_embarcacion" data-bs-backdrop="static" data-bs-focus="false"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">EDITAR EMBARQUE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <input type="hidden" name="id_embarcacion" id="id_embarcacion">
                <div class="col-12">
                    <div class="row g-2 d-flex justify-content-between my-4">
                        <div class="col-md-4">
                            <button type="button" class="btn text-white" style="background-color: #8338ec;"
                                id="btn_selectedTodoEnEmbarcados">Seleccionar todo</button>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control inputSearchEnEmbarcados" placeholder="Buscar">
                        </div>
                    </div>
                    <table class="table w-100 my-3" cellspacing="0" id="table_EnEmbarcados">
                        <thead>
                            <tr>
                                <th>Comprobante</th>
                                <th>Remitente</th>
                                <th>Destinatario</th>
                                <th>Origen → Destino</th>
                                <th>Producto</th>
                                <th>Forma de Pago</th>
                                <th>Estado Pago</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btnEnEmbarcados btnSave d-none" id="button_EnEmbarcados">Quitar del
                    embarque</button>
                <button class="btn btnSave d-none" type="button" id="button_loadEnEmbarcados" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Quitando...
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal add Register-->
<div class="modal fade" id="modal_salida" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">NUEVA SALIDA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_salida" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_salida" name="id_salida">

                        <?php
                        $col = "col-md-3";
                        include("./views/templates/components/cmp_select_vehiculo.php") ?>
                        <div class="col-md-3 my-2" id="div_parentConductor">
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
                        <div class="col-md-3 ">
                            <label>Fecha salida<span class="requiredField">*</span></label>
                            <input class="form-control" type="date" name="fecha_salida" id="fecha_salida"
                                min="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" required>
                        </div>
                        <div class="col-md-3 ">
                            <label>Hora salida<span class="requiredField">*</span></label>
                            <input class="form-control" type="time" name="hora_salida" id="hora_salida"
                                value="<?= date('H:i', strtotime('+1 hour')) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_salida"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_salida">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_salida" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_celular" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Ingrese Numero de celular</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <form id="form_celular" class="needs-validation" novalidate>
                    <input type="hidden" name="id_usuario_celular" id="id_usuario_celular">
                    <input type="text" class="form-control text-center fw-bold fs-4" id="celular_usuario"
                        name="celular_usuario" placeholder="Escribe aquí" maxlength="9" inputmode="numeric"
                        autocomplete="off">

                    <div class="modal-footer justify-content-center">
                        <button type="submit" id="btn_guardar_celular" class="btn btnSave">Guardar</button>
                        <button id="load_guardar_celular" type="button" class="btn btnSave d-none"> <span
                                class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span>Guardando</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_destino_erroneo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="fa-light fa-triangle-exclamation me-2"></i>
                    Registrar Encomienda Mal Enviada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-warning">
                    <i class="fa-light fa-circle-info me-2"></i>
                    Utilice esta opción cuando una encomienda haya llegado físicamente
                    a este terminal por error y no corresponda a su destino programado.
                </div>
                <form id="form_buscar_encomienda_error">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tipo de búsqueda</label>

                            <select class="form-select" id="tipo_busqueda_error" name="tipo_busqueda">
                                <option value="tracking">Tracking</option>
                                <option value="comprobante">Comprobante</option>
                            </select>
                        </div>

                        <div class="col-md-7 mb-3" id="contenedor_tracking">
                            <label class="form-label">Tracking</label>

                            <input type="text" class="form-control" id="tracking_error" name="tracking"
                                placeholder="Ingrese el tracking">
                        </div>

                        <div class="col-md-7 mb-3 d-none" id="contenedor_comprobante">

                            <label class="form-label">Comprobante</label>

                            <div class="row g-2">

                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="serie_error" name="serie"
                                        placeholder="Serie">
                                </div>

                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="correlativo_error" name="correlativo"
                                        placeholder="Correlativo">
                                </div>

                            </div>

                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 text-white">

                                <i class="fa-light fa-magnifying-glass me-1"></i>
                                Buscar
                            </button>
                        </div>

                    </div>

                </form>

                <hr>

                <!-- Se llena luego de la búsqueda -->
                <div id="resultado_encomienda_error" style="display:none;">

                    <div class="card border-warning">
                        <div class="card-body">

                            <h6 class="fw-bold mb-3">
                                Información encontrada
                            </h6>

                            <div class="row">

                                <div class="col-md-4">
                                    <strong>Comprobante:</strong>
                                    <div id="lbl_comprobante"></div>
                                </div>

                                <div class="col-md-4">
                                    <strong>Origen:</strong>
                                    <div id="lbl_origen"></div>
                                </div>

                                <div class="col-md-4">
                                    <strong>Destino:</strong>
                                    <div id="lbl_destino"></div>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <strong>Tracking:</strong>
                                    <div id="lbl_tracking"></div>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <strong>Estado:</strong>
                                    <div id="lbl_estado"></div>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <strong>Terminal Actual:</strong>
                                    <div id="lbl_terminal_actual"></div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <label class="form-label">
                                    Observación
                                </label>

                                <textarea class="form-control" id="observacion_error" rows="3"
                                    placeholder="Ej.: La encomienda fue descargada por error en este terminal."></textarea>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-danger text-white" id="btn_registrar_error" disabled>

                    <i class="fa-light fa-triangle-exclamation me-1"></i>
                    Marcar como Mal Enviado
                </button>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal_timeline_encomienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fa-light fa-route me-2"></i>
                    Historial de Movimientos
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <!-- Información General -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <small class="text-muted">Comprobante</small>
                                <div class="fw-semibold" id="tl_comprobante">-</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Tracking</small>
                                <div class="fw-semibold" id="tl_tracking">-</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Origen</small>
                                <div class="fw-semibold" id="tl_origen">-</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Destino Final</small>
                                <div class="fw-semibold" id="tl_destino">-</div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Timeline -->
                <div id="timeline_encomienda">

                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>

            </div>

        </div>
    </div>
</div>