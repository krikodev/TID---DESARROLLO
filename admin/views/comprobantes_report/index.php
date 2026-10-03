<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Reportes de comprobantes';
        $title_modal = 'NUEVA SERIE';
        $show_btnadd = false;

        include("views/templates/components/cmp_headerpage.php")
            ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-start my-2">
                    <!-- Cambié justify-content-end a justify-content-start -->
                    <div class="col-md-2 my-2" id="div_parentTerminal">
                        <select class="form-select" id="terminal" name="terminal" required>
                            <?php if ($this->terminal["success"]): ?>
                                <option value="">Seleccione terminal</option>
                                <?php for ($i = 0; $i < count($this->terminal["message"]); $i++): ?>
                                    <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                                    <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>">
                                        <?php echo $text ?>
                                    </option>
                                <?php endfor ?>
                            <?php else: ?>
                                <option value="">No hay terminales disponibles</option>
                            <?php endif ?>
                        </select>
                        <?php if (!$this->terminal["success"]): ?>
                            <small class="text-danger"><?php echo $this->terminal["message"] ?></small>
                        <?php endif ?>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="tp_comprobante">
                            <option selected>TODO</option>
                            <option value="0">COMPROBANTES</option>
                            <option value="1">FACTURAS</option>
                            <option value="3">BOLETAS</option>
                            <option value="7">NOTAS DE CREDITO Y DEBITO</option>
                            <option value="2">NOTAS DE VENTA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="fecha_inicio">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="fecha_fin">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <a class="btn btnColorVerde flex-fill" title="Filtrar" id="btn_filtrar">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </a>
                            <a class="btn btnColorVerde flex-fill d-none" type="button" id="btn_loadSave" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Filtrando...
                            </a>
                            <a class="btn btn-secondary flex-fill" title="Limpiar filtros" id="btn_limpiar">
                                <i class="bi bi-eraser-fill"></i> Limpiar
                            </a>
                        </div>
                    </div>

                </div>
                <div class="row g-2 d-flex justify-content-start my-2">
                    <div class="col-md-8 mb-3">
                        <a id="exportExcel" class="btn BtnEXCEL" aria-label="Exportar a Excel">
                            <i class="fa-solid fa-file-excel"></i> Excel
                            <span class="spinner-border spinner-border-sm d-none" id="exportExcelSpinner" role="status"
                                aria-hidden="true"></span>
                        </a>
                        <a id="exportPrint" class="btn BtnPRINT" aria-label="Imprimir reporte">
                            <i class="bi bi-printer-fill"></i> Imprimir
                        </a>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control inputSearch" placeholder="Buscar comprobante"
                            aria-label="Buscar comprobante">
                    </div>
                </div>
                <table id="tabla_comprobantesreport" class="table display responsive" cellspacing="0"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>F. Emision</th>
                            <th>Cliente</th>
                            <th>Numero</th>
                            <th>Estado</th>
                            <th>T. Gravado</th>
                            <th>T. Exonerado</th>
                            <th>T. Inafecto</th>
                            <th>T. IGV</th>
                            <th>Total</th>
                            <th>Estado SUNAT</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>