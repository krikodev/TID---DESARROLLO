<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Reportes de Encomiendas';
        $title_modal = 'NUEVA SERIE';
        $show_btnadd = false;

        include("views/templates/components/cmp_headerpage.php")
            ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-start my-2">
                    <!-- Cambié justify-content-end a justify-content-start -->
                    <div class="col-md-2" id="div_parentTerminal">
                        <label class="form-label">Terminal Origen</label>
                        <select class="form-select" id="terminal" name="terminal" required>
                            <?php if ($this->terminal["success"]): ?>
                                <option value="">Seleccione terminal</option>
                                <?php for ($i = 0; $i < count($this->terminal["message"]); $i++): ?>
                                    <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                                    <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>">
                                        <?php echo $text ?></option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo de comprobante</label>
                        <select class="form-select" id="tp_comprobante">
                            <option selected value="TODO">TODO</option>
                            <option value="0">COMPROBANTES</option>
                            <option value="1">FACTURAS</option>
                            <option value="3">BOLETAS</option>
                            <option value="7">NOTAS DE CREDITO Y DEBITO</option>
                            <option value="2">NOTAS DE VENTA</option>
                        </select>
                    </div>
                    <div class="col-md-2" id="div_parentFormaPago">
                        <label class="form-label">F. Pago</label>
                        <select class="form-select" id="forma_pago" name="forma_pago" required>
                            <option value="">Seleccione</option>
                            <?php if ($this->forma_pago["success"]): ?>
                                <?php for ($i = 0; $i < count($this->forma_pago["message"]); $i++): ?>
                                    <?php $text = $this->forma_pago["message"][$i]["descripcion"] ?>
                                    <option value="<?php echo $this->forma_pago["message"][$i]["id_forma_pago"] ?>">
                                        <?php echo $text ?></option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado de pago</label>
                        <select class="form-select" id="estado_pago">
                            <option selected value="">TODO</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                            <option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Fecha Final</label>
                        <input type="date" class="form-control" id="fecha_fin">
                    </div>
                    <div class="col-md-2">
                        <div class="btn-container d-flex gap-2">
                            <a class="btn btnColorVerde" title="Filtrar" id="btn_filtrar">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </a>
                            <a class="btn btn-secondary" title="Limpiar filtros" id="btn_limpiar"
                                style="background-color: #6c757d; border-color: #6c757d;">
                                <i class="bi bi-eraser-fill"></i> Limpiar
                            </a>
                            <a class="btn btnColorVerde d-none" type="button" id="btn_loadSave" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Filtrando...
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-2 d-flex justify-content-start my-2">
                    <div class="col-md-8 mb-3">
                        <a id="exportExcel" class="btn BtnEXCEL"><i class='fa-solid fa-file-excel'></i> Excel</a>
                        <a id="exportPrint" class="btn BtnPRINT"><i class='bi bi-printer-fill'></i> Imprimir</a>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="tabla_comprobantesreport" class="table display responsive" cellspacing="0"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Fecha de emisión</th>
                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th>Destino</th>
                            <th>Terminal</th>
                            <th>Estado SUNAT</th>
                            <th>Estado pago</th>
                            <th>T. Gravado</th>
                            <th>T. IGV</th>
                            <th>Total</th>
                            <th>Forma pago</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>