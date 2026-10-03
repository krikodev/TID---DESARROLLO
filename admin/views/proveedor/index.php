<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Proveedor';
        $title_modal = 'NUEVO PROVEEDOR';
        $show_btnadd = true;

        include("views/templates/components/cmp_headerpage.php")
        ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 my-2">
                    <div class="col-md-3">
                        <label class="form-label">Terminal</label>
                        <select id="filtro_terminal_proveedor" class="form-select">
                            <option value="">Todos</option>

                            <?php if ($this->terminal["success"]): ?>
                                <?php for ($i = 0; $i < count($this->terminal["message"]); $i++): ?>
                                    <option value="<?= $this->terminal["message"][$i]["id_terminal"] ?>">
                                        <?= $this->terminal["message"][$i]["nombre"] ?>
                                    </option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_tp_docu_proveedores" class="form-label">
                            Tipo Documento
                        </label>
                        <select id="filtro_tp_docu_proveedores" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">DNI</option>
                            <option value="4">Carnet extranjeria</option>
                            <option value="6">RUC</option>
                            <option value="7">Pasaporte</option>
                            <option value="0">Otros documentos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="filtro_estado_proveedor" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Habilitado</option>
                            <option value="0">Deshabilitado</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" id="btnFiltrarProveedor" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Filtrar
                            </button>

                            <button type="button" id="btnLimpiarProveedor" class="btn btn-secondary">
                                <i class="fa fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row g-2 d-flex justify-content-between align-items-center my-2">
                    <div class="col-md-6">
                        <button
                            type="button"
                            id="btnExportarExcelProveedor"
                            class="btn btn-success">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </button>
                        <button
                            type="button"
                            id="btnExportarPDFProveedor"
                            class="btn btn-danger">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </button>
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
                <table id="table_proveedor" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Terminal</th>
                            <th>Proveedor</th>
                            <th>Tipo documento</th>
                            <th>Nro. Documento</th>
                            <th>Celular</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Ubigeo</th>
                            <th>Estado</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>