<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Usuario';
        $title_modal = 'NUEVO USUARIO';
        $show_btnadd = true;

        include("views/templates/components/cmp_headerpage.php")
        ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <!-- Filtros específicos -->
                <div class="row g-2 my-2">
                    <!-- Terminal -->
                    <div class="col-md-3">
                        <label for="filtro_terminal_pasajeros" class="form-label">
                            Terminal
                        </label>
                        <select id="filtro_terminal_pasajeros" class="form-select">
                            <option value="">Todos</option>
                            <?php if ($this->terminal["success"]) : ?>
                                <?php for ($i = 0; $i < count($this->terminal["message"]); $i++) : ?>
                                    <option value="<?= $this->terminal["message"][$i]["id_terminal"] ?>">
                                        <?= $this->terminal["message"][$i]["nombre"] ?>
                                    </option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>

                    <!-- Tipo Documento -->
                    <div class="col-md-3">
                        <label for="filtro_tp_docu_pasajeros" class="form-label">
                            Tipo Documento
                        </label>
                        <select id="filtro_tp_docu_pasajeros" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">DNI</option>
                            <option value="4">Carnet extranjeria</option>
                            <option value="6">RUC</option>
                            <option value="7">Pasaporte</option>
                            <option value="0">Otros documentos</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-3">
                        <label for="filtro_estado_pasajeros" class="form-label">
                            Estado
                        </label>
                        <select id="filtro_estado_pasajeros" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Habilitado</option>
                            <option value="0">Deshabilitado</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                id="btnFiltrarPasajeros"
                                class="btn btn-primary">
                                <i class="fa fa-filter"></i>
                                Filtrar
                            </button>
                            <button
                                type="button"
                                id="btnLimpiarPasajeros"
                                class="btn btn-secondary">
                                <i class="fa fa-eraser"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Exportaciones + búsqueda general -->
                <div class="row g-2 d-flex justify-content-between align-items-center my-2">
                    <!-- Botones de exportación -->
                    <div class="col-md-6">
                        <button
                            type="button"
                            id="btnExportarExcelPasajeros"
                            class="btn btn-success">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </button>
                        <button
                            type="button"
                            id="btnExportarPDFPasajeros"
                            class="btn btn-danger">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </button>
                    </div>

                    <!-- Búsqueda general -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control inputSearch"
                                placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <table
                    id="table_pasajero"
                    class="table display responsive"
                    cellspacing="0"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Terminal</th>
                            <th>Personal</th>
                            <th>Género</th>
                            <th>Tipo Documento</th>
                            <th>Nro. Documento</th>
                            <th>Fecha Nacimiento</th>
                            <th>Nacionalidad</th>
                            <th>Celular</th>
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