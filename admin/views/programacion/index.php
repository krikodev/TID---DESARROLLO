<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Programación';
        $title_modal = 'NUEVA PROGRAMACIÓN';
        if ($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2) {
            $show_btnadd = true;
        } else {
            $show_btnadd = false;
        }

        include("views/templates/components/cmp_headerpage.php")
        ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-programacion" role="tabpanel"
                        aria-labelledby="pills-programacion-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="filtro_fecha_inicio_programacion">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="filtro_fecha_fin_programacion">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Tipo Programación</label>
                                <select class="form-select form-select-sm" id="filtro_tipo_programacion">
                                    <option value="">TODOS</option>
                                    <option value="1">PASAJES</option>
                                    <option value="2">ENCOMIENDAS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="filtro_origen_programacion">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select" id="filtro_destino_programacion">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado</label>
                                <select class="form-select form-select-sm"
                                    id="filtro_estado_programacion">
                                    <option value="">TODOS</option>
                                    <option value="1">HABILITADO</option>
                                    <option value="0">DESHABILITADO</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Liquidación</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="filtro_fecha_liquidacion_programacion">
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosProgramacion()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltrosProgramacion('programacion')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm" onclick="exportarExcelProgramacion()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearch" placeholder="Buscar">
                                    <button class="btn btn-success btnSearchTable">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_programacion" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Tipo</th>
                                    <th>Conductor</th>
                                    <th>Vehículo</th>
                                    <th>Fecha salida</th>
                                    <th>Hora salida</th>
                                    <th>Precio Premium</th>
                                    <th>Precio Normal</th>
                                    <th>Estado</th>
                                    <th>Liquidado</th>
                                    <th>Fecha Liquidación</th>
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
    </div>
</div>