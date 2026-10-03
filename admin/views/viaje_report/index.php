<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Reporte de Pasajeros';
        $title_modal = 'NUEVO VIAJE';
        $show_btnadd = false;

        include("views/templates/components/cmp_headerpage.php")
            ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <!-- Filtros -->
                <div class="row g-2 d-flex justify-content-start my-2">
                    <!-- Terminal Origen -->
                    <div class="col-md-3 my-2" id="div_parentTerminal">
                        <label for="terminal_origen" class="form-label small fw-bold text-muted">
                            <i class="bi bi-geo-alt-fill text-primary"></i> Terminal Origen
                        </label>
                        <select class="form-select" id="terminal_origen" name="terminal_origen">
                            <?php if ($this->terminal["success"]): ?>
                                <option value="">Todas las terminales de origen</option>
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

                    <!-- Terminal Destino -->
                    <div class="col-md-3 my-2" id="div_parentTerminalDestino">
                        <label for="terminal_destino" class="form-label small fw-bold text-muted">
                            <i class="bi bi-geo-alt text-success"></i> Terminal Destino
                        </label>
                        <select class="form-select" id="terminal_destino" name="terminal_destino">
                            <?php if ($this->terminal["success"]): ?>
                                <option value="">Todas las terminales de destino</option>
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
                    </div>

                    <div class="col-md-2">
                        <label for="tipo_servicio" class="form-label small fw-bold text-muted">Tipo Servicio</label>
                        <select class="form-select" id="tipo_servicio">
                            <option value="">Todos los servicios</option>
                            <?php if (isset($this->tipos_servicio) && is_array($this->tipos_servicio)): ?>
                                <?php foreach ($this->tipos_servicio as $servicio): ?>
                                    <option value="<?php echo $servicio['id_tp_servicio_pasaje'] ?>">
                                        <?php echo $servicio['descripcion'] ?>
                                    </option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="fecha_inicio" class="form-label small fw-bold text-muted">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio">
                    </div>

                    <div class="col-md-2">
                        <label for="fecha_fin" class="form-label small fw-bold text-muted">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin">
                    </div>
                </div>

                <!-- Botones de acción agrupados a la izquierda -->
                <div class="row g-2 d-flex justify-content-between align-items-center my-3">
                    <!-- Grupo 1: Filtros principales -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <a class="btn btnColorVerde" title="Filtrar" id="btn_filtrar">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </a>
                            <a class="btn btnColorVerde d-none" type="button" id="btn_loadSave" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Filtrando...
                            </a>
                            <a class="btn btn-secondary" title="Limpiar" id="btn_limpiar">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>

                    <!-- Grupo 2: Exportación -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <a id="exportExcel" class="btn BtnEXCEL" aria-label="Exportar a Excel">
                                <i class="fa-solid fa-file-excel"></i> Excel
                                <span class="spinner-border spinner-border-sm d-none" id="exportExcelSpinner"
                                    role="status" aria-hidden="true"></span>
                            </a>
                            <a id="exportPrint" class="btn BtnPRINT" aria-label="Imprimir reporte">
                                <i class="bi bi-printer-fill"></i> Imprimir
                            </a>
                        </div>
                    </div>

                    <!-- Grupo 3: Búsqueda -->
                    <div class="col-md-4">
                        <input type="text"
                            class="form-control border-1 border-top-0 border-start-0 border-end-0 rounded-0 px-0 inputSearch"
                            id="inputSearch" placeholder="Buscar asiento, cliente o documento...">
                    </div>
                </div>

                <!-- Tabla de resultados -->
                <table id="tabla_viajesreport" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Asiento</th>
                            <th>Número</th>
                            <th>Fecha Emisión</th>
                            <th>Cliente</th>
                            <th>Pasajero</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>T. Gravado</th>
                            <th>T. IGV</th>
                            <th>Total</th>
                            <th>Placa</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>