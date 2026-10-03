<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <div class="row g-2">
            <div
                class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                <h2>
                    <a href="<?= URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                    Notas de venta
                </h2>
                <?php $data_permisos = Session::get("data_permisos") ?>
                <?php if (isset($data_permisos["p_pago_bloque"]) && $data_permisos["p_pago_bloque"] == 1): ?>
                    <div class="">
                        <button type="button" class="btn btn_pagar_notas" data-bs-toggle="modal" data-bs-target="#modal"
                            data-bs-whatever="PAGAR NOTAS" id="button_newgister"><i class="fa-solid fa-circle-check "></i>
                            Pagar Notas en bloque</button>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 mb-3 p-3 bg-light rounded">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Fecha Inicio</label>
                        <input type="date" class="form-control form-control-sm" id="filtro_fecha_inicio">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Fecha Fin</label>
                        <input type="date" class="form-control form-control-sm" id="filtro_fecha_fin">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tipo</label>
                        <select class="form-select form-select-sm" id="filtro_tipo">
                            <option value="">TODOS</option>
                            <option value="1">PASAJE</option>
                            <option value="2">ENCOMIENDA</option>
                            <option value="3">FACTURADOR</option>
                            <option value="4">PAGO BLOQUE</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Estado</label>
                        <select class="form-select form-select-sm" id="filtro_estado">
                            <option value="">TODOS</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="VENDIDO">VENDIDO</option>
                            <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="ANULADO">ANULADO</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button class="btn btn-primary btn-sm" id="btn_filtrar">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                            <button class="btn btn-secondary btn-sm" id="btn_limpiar_filtros">
                                <i class="fa fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row g-2 my-2">
                    <div class="col-md-2">
                        <button class="btn btn-success btn-sm" id="btn_exportar_excel">
                            <i class="fa fa-file-excel"></i> Excel
                        </button>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="table_notaventa" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Emisión</th>
                            <th>Cliente</th>
                            <th>Número</th>
                            <th>Moneda</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Tipo</th>
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