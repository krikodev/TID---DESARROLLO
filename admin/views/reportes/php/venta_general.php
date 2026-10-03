<main class="content">
    <div class="col-md-12 body-page">
        <div class="card mb-0 header-body">
            <div class="card-header">
                <h3 class="my-0">
                    <a class="btn text-white" href="<?php echo URL ?>reportes">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    Reporte de ventas en general
                </h3>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportExcel export_excel"></span>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportCSV export_csv"></span>
            </div>
        </div>
        <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
            <div class="row g-2 d-flex my-3">
                <div class="col-md-2">
                    <label>Fecha inicio</label>
                    <input type="date" class="form-control" name="fechaini_filtro" id="fechaini_filtro" value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-md-2">
                    <label>Fecha fin</label>
                    <input type="date" class="form-control" name="fechafin_filtro" id="fechafin_filtro" value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-md-2">
                    <label>T. Documento</label>
                    <select class="form-select" name="tdocu_filtro" id="tdocu_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->tpDocus) && $this->tpDocus["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->tpDocus["message"]); $i++) : ?>
                                <?php $text = $this->tpDocus["message"][$i] ?>
                                <option value="<?php echo $text["id_comprobante"] ?>"><?php echo $text["descripcion"] ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Serie</label>
                    <select class="form-select" name="serie_filtro" id="serie_filtro" required>
                        <?php if (!in_array($data_usuario["tipo_usuario"], ["Vendedor", "Administrador"])) : ?>
                            <option value="%">Mostrar todo</option>
                        <?php endif ?>
                        <?php if (isset($this->series) && $this->series["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->series["message"]); $i++) : ?>
                                <?php $text = $this->series["message"][$i] ?>
                                <option value="<?php echo $text["num_serie"] ?>"><?php echo $text["num_serie"] ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2" id="parentTpagosFiltro">
                    <label>T. Pago</label>
                    <select class="form-select" name="tpagos_filtro" id="tpagos_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->tpagos) && $this->tpagos["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->tpagos["message"]); $i++) : ?>
                                <?php $text = $this->tpagos["message"][$i] ?>
                                <?php $descrip = $text["descripcion"] ?>
                                <option value="<?php echo $text["id_tp_pago"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-select" name="estado_filtro" id="estado_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <option value="c">Cancelado</option>
                        <option value="p">Pendiente</option>
                        <option value="a">Anulado</option>
                    </select>
                </div>
                <div class="col-md-2" id="parentClienteFiltro">
                    <label>Cliente</label>
                    <select class="form-select" name="cliente_filtro" id="cliente_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->clientes) && $this->clientes["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->clientes["message"]); $i++) : ?>
                                <?php $text = $this->clientes["message"][$i] ?>
                                <?php $descrip = $text["razon_social"] ? $text["razon_social"] : ($text["nombres"] . " " . $text["apellidos"]) ?>
                                <option value="<?php echo $text["id_cliente"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>T. Cliente</label>
                    <select class="form-select" name="tpcliente_filtro" id="tpcliente_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <option value="NORMAL">NORMAL</option>
                        <option value="ABONADO">ABONADO</option>
                    </select>
                </div>
                <div class="col-md-2" id="parentUsuarioFiltro">
                    <label>Usuario</label>
                    <select class="form-select" name="usuario_filtro" id="usuario_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->usuarios) && $this->usuarios["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->usuarios["message"]); $i++) : ?>
                                <?php $text = $this->usuarios["message"][$i] ?>
                                <?php $descrip = $text["nombres"] . " " . $text["apellidos"] ?>
                                <option value="<?php echo $text["id_usuario"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2" id="parentCjChicaFiltro">
                    <label>Caja chica</label>
                    <select class="form-select" name="cjchica_filtro" id="cjchica_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->cjchicas) && $this->cjchicas["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->cjchicas["message"]); $i++) : ?>
                                <?php $text = $this->cjchicas["message"][$i] ?>
                                <?php $descrip = $text["descripcion"] . " | " . $text["turno"] . " | " . $text["sucursal"] ?>
                                <option value="<?php echo $text["id_caja_chica"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success" id="btnFiltrar">BUSCAR</button>
                </div>
            </div>
            <table id="table_ventas" class="table display" cellspacing="0" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha emisión</th>
                        <th>Cliente</th>
                        <th>Tipo de documento</th>
                        <th>Tipo de cliente</th>
                        <th>Tipo de pago</th>
                        <th>Cod. Voucher</th>
                        <th>Tipo de documento</th>
                        <th>Serie</th>
                        <th>Nro. ticket</th>
                        <th>Estado de pago</th>
                        <th>Caja chica</th>
                        <th>Moneda</th>
                        <th>Total</th>
                        <th>Motivo anular</th>
                        <th>Vendedor</th>
                        <th>Sucursal</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</main>