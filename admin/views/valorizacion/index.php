<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-3 mb-3 p-3">

                    <div class="card-body d-none">
                        <div class="row g-3">
                            <!-- Fecha Inicio -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Fecha Inicio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="filtro_fecha_inicio"
                                        name="filtro_fecha_inicio" value="">
                                </div>
                            </div>

                            <!-- Fecha Fin -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Fecha Fin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="filtro_fecha_fin"
                                        name="filtro_fecha_fin" value="">
                                </div>
                            </div>
                            <!-- Estado -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Estado</label>
                                <select class="form-select" id="filtro_estado">
                                    <option value="">Todos</option>
                                    <option value="BORRADOR">Borrador</option>
                                    <option value="ABIERTA">Abierta</option>
                                    <option value="CERRADA">Cerrada</option>
                                    <option value="FACTURADA">Facturada</option>
                                    <option value="ANULADA">Anulada</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cliente</label>
                                <select class="form-select" id="filtro_cliente">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>

                            <!-- Botones de acción -->
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary flex-fill text-white" id="btn_filtrar">
                                        <i class="fas fa-search me-2"></i>Filtrar
                                    </button>
                                    <button type="button" class="btn btn-secondary text-white" id="btn_limpiar_filtros"
                                        title="Limpiar filtros">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 d-flex justify-content-start my-2">
                    <div class="col-md-1">
                        <button type="button" class="btn btn_pagar_guias" id="button_newgister">
                            <i class="fa-solid fa-file-circle-plus"></i>
                            Nuevo
                        </button>
                    </div>
                    <!-- <div class="col-md-1">
                        <button type="button" class="btn btn-success text-white" id="btn_exportar_excel">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                    </div> -->
                    <div class="col-md-7"></div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="table_valorizacion" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>N° Valorización</th>
                            <th>Cliente</th>
                            <th>Cant. Fletes</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Comprobante</th>
                            <th>Observación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>