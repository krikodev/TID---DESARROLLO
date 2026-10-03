<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Conductor / Copiloto';
        $title_modal = 'NUEVO CONDUCTOR / COPILOTO';
        $show_btnadd = true;

        include("views/templates/components/cmp_headerpage.php")
        ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 my-2 align-items-end">

                    <!-- Terminal -->
                    <div class="col-md-2">
                        <label class="form-label">
                            Terminal
                        </label>
                        <select
                            id="filtro_terminal_conductor"
                            class="form-select">
                            <option value="">
                                Todos
                            </option>
                            <?php if ($this->terminal["success"]): ?>
                                <?php for ($i = 0; $i < count($this->terminal["message"]); $i++): ?>
                                    <option value="<?= $this->terminal["message"][$i]["id_terminal"] ?>">
                                        <?= $this->terminal["message"][$i]["nombre"] ?>
                                    </option>
                                <?php endfor ?>
                            <?php endif ?>
                        </select>
                    </div>

                    <!-- Categoría -->
                    <div class="col-md-2">
                        <label
                            for="filtro_categoria_conductor"
                            class="form-label">
                            Categoría
                        </label>
                        <select
                            id="filtro_categoria_conductor"
                            class="form-select">
                            <option value="">
                                Todos
                            </option>
                            <option value="A-I">A-I</option>
                            <option value="A-IIa">A-IIa</option>
                            <option value="A-IIb">A-IIb</option>
                            <option value="A-IIIa">A-IIIa</option>
                            <option value="A-IIIb">A-IIIb</option>
                            <option value="A-IIIc">A-IIIc</option>
                            <option value="B-I">B-I</option>
                            <option value="B-IIa">B-IIa</option>
                            <option value="B-IIb">B-IIb</option>
                            <option value="B-IIc">B-IIc</option>
                            <option value="A-IV">A-IV</option>
                        </select>
                    </div>

                    <!-- Estado Civil -->
                    <div class="col-md-2">
                        <label
                            for="filtro_estado_civil_conductor"
                            class="form-label">
                            Estado Civil
                        </label>
                        <select
                            id="filtro_estado_civil_conductor"
                            class="form-select">
                            <option value="">
                                Todos
                            </option>
                            <option value="Casado(a)">
                                Casado(a)
                            </option>
                            <option value="Conviviente">
                                Conviviente
                            </option>
                            <option value="Separado(a)">
                                Separado(a)
                            </option>
                            <option value="Viudo(a)">
                                Viudo(a)
                            </option>
                            <option value="Soltero(a)">
                                Soltero(a)
                            </option>
                        </select>
                    </div>

                    <!-- Tipo Usuario -->
                    <div class="col-md-2">
                        <label
                            for="filtro_tp_usuario_conductor"
                            class="form-label">
                            Tipo de usuario
                        </label>
                        <select
                            id="filtro_tp_usuario_conductor"
                            class="form-select">
                            <option value="">
                                Todos
                            </option>
                            <option value="4">
                                Conductor
                            </option>
                            <option value="10">
                                Copiloto
                            </option>
                        </select>
                    </div>
                    <!-- Estado -->
                    <div class="col-md-2">
                        <label class="form-label">
                            Estado
                        </label>
                        <select
                            id="filtro_estado_conductor"
                            class="form-select">
                            <option value="">
                                Todos
                            </option>
                            <option value="1">
                                Habilitado
                            </option>
                            <option value="0">
                                Deshabilitado
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" id="btnFiltrarConductor" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Filtrar
                            </button>
                            <button type="button" id="btnLimpiarConductor" class="btn btn-secondary">
                                <i class="fa fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-2 d-flex justify-content-between align-items-center my-2">
                    <div class="col-md-6">
                        <button
                            type="button"
                            id="btnExportarExcelConductor"
                            class="btn btn-success">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </button>
                        <button
                            type="button"
                            id="btnExportarPDFConductor"
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
                <table id="table_conductor" class="table display" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Terminal</th>
                            <th>DNI</th>
                            <th>Personal</th>
                            <th>Licencia</th>
                            <th>Categoría</th>
                            <th>Estado Civil</th>
                            <th>Celular</th>
                            <th>Dirección</th>
                            <th>Ubigeo</th>
                            <th>Tipo de usuario</th>
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