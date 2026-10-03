<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <div class="row g-2">
            <div
                class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                <h2>
                    <a href="<?= URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                    Guías de remision transportista
                </h2>
                <?php $data_permisos = Session::get("data_permisos") ?>
                <?php if (isset($data_permisos["p_pago_bloque"]) && $data_permisos["p_pago_bloque"] == 1): ?>
                    <div class="">
                        <button type="button" class="btn btn_pagar_guias" data-bs-toggle="modal" data-bs-target="#modal"
                            data-bs-whatever="PAGAR NOTAS" id="button_newgister"><i class="fa-solid fa-circle-check "></i>
                            Pagar Guias en bloque</button>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-3 mb-3 p-3">

                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fecha Inicio -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Inicio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="filtro_fecha_inicio"
                                        name="filtro_fecha_inicio" value="">
                                </div>
                            </div>

                            <!-- Fecha Fin -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Fin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="filtro_fecha_fin"
                                        name="filtro_fecha_fin" value="">
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Estado</label>
                                <select class="form-select" id="filtro_estado" name="filtro_estado">
                                    <option value="">Todos los estados</option>
                                    <option value="PAGADO">Pagado</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="ANULADO">Anulado</option>
                                </select>
                            </div>

                            <!-- Botones de acción -->
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-primary flex-fill" id="btn_filtrar">
                                        <i class="fas fa-search me-2"></i>Filtrar
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btn_limpiar_filtros"
                                        title="Limpiar filtros">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                    <button type="button" class="btn btn-success" id="btn_exportar_excel">
                                        <i class="fas fa-file-excel me-1"></i> Excel
                                    </button>
                                </div>
                            </div>
                        </div>
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
                <table id="table_guiatransportista" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>F. emisión</th>
                            <th>Cliente</th>
                            <th>Número</th>
                            <th>Moneda</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Comprobante de pago</th>
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