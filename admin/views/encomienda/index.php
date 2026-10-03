<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="row g-2">
            <div
                class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                <h2>
                    <a href="<?php echo URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                    Encomienda
                </h2>
                <div>
                    <button type="button" class="btn text-white d-none" style="background-color: #fe6d73;"
                        data-bs-toggle="modal" data-bs-target="#modal_reporte"><i class="fa-light fa-file"></i>
                        Reportes</button>
                    <?php if ($this->config_encomienda['success'] && $this->config_encomienda['message']['envio_erroneo'] == 1): ?>
                        <button type="button" class="btn text-white" style="background-color: #dc3545;"
                            data-bs-toggle="modal" data-bs-target="#modal_destino_erroneo">
                            <i class="fa-light fa-triangle-exclamation"></i>
                            Destino Erróneo
                        </button>
                    <?php endif ?>
                    <button type="button" class="btn text-white" style="background-color: #ffc43d;"
                        data-bs-toggle="modal" data-bs-target="#modal_embarcar"><i
                            class="fa-light fa-cart-flatbed-suitcase"></i> Embarcar</button>
                    <button type="button" class="btn text-white" style="background-color: #01befe;"
                        data-bs-toggle="modal" data-bs-target="#modal_desembarcar"><i class="fa-light fa-box-open"></i>
                        Desembarcar</button>
                    <button type="button" class="btn text-white" style="background-color: #06d6a0;"
                        data-bs-toggle="modal" data-bs-target="#modal_entregar"><i class="fa-light fa-box-check"></i>
                        Entregar</button>
                    <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal"
                        data-bs-whatever="NUEVA ENCOMIENDA" data-mode="new" id="button_newgister"><i
                            class="fa-solid fa-plus"></i> Nuevo</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 body-page">
            <?php $permiso_celular = $this->config_encomienda['success'] && $this->config_encomienda['message']['nrocel_cliente'] ? $this->config_encomienda['message']['nrocel_cliente'] : 0; ?>
            <?php $permiso_rotulado = $this->config_encomienda['success'] && $this->config_encomienda['message']['rotulado_e'] ? $this->config_encomienda['message']['rotulado_e'] : 0; ?>
            <input type="hidden" name="permiso_celular" id="permiso_celular" value="<?= $permiso_celular ?>">
            <input type="hidden" name="permiso_rotulado" id="permiso_rotulado" value="<?= $permiso_rotulado ?>">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-comprobantes-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-comprobantes" type="button" role="tab"
                            aria-controls="pills-comprobantes" aria-selected="true">Factura-Boleta</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-notaventa-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-notaventa" type="button" role="tab" aria-controls="pills-notaventa"
                            aria-selected="false">Notas de Venta</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-guiastransportista-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-guiastransportista" type="button" role="tab"
                            aria-controls="pills-guiastransportista" aria-selected="false">Guias T.</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-historial-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-historial" type="button" role="tab" aria-controls="pills-historial"
                            aria-selected="false">Historial de embarcaciones</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-grupales-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-grupales" type="button" role="tab" aria-controls="pills-grupales"
                            aria-selected="false">Embarcaciones Grupales</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-historialD-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-historialD" type="button" role="tab" aria-controls="pills-historialD"
                            aria-selected="false">Historial de desembarques</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-guias-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-guias" type="button" role="tab" aria-controls="pills-guias"
                            aria-selected="false">G. R. - Transportista</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-comprobantes" role="tabpanel"
                        aria-labelledby="pills-comprobantes-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="fecha_inicio_comprobante">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_comprobante">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Tipo Comprobante</label>
                                <select class="form-select form-select-sm" id="tipo_comprobante">
                                    <option value="">TODOS</option>
                                    <option value="1">FACTURA</option>
                                    <option value="3">BOLETA</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="origen_comprobante">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select" id="destino_comprobante">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Envío</label>
                                <select class="form-select form-select-sm"
                                    id="estado_envio_comprobante">
                                    <option value="">TODOS</option>
                                    <option value="1">ENTREGADO</option>
                                    <option value="2">EN DESTINO</option>
                                    <option value="3">EN RUTA</option>
                                    <option value="4">EN ORIGEN</option>
                                    <option value="5">EN TRANSITO</option>
                                    <option value="6">MAL ENVIADO</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Venta</label>
                                <select id="estado_venta_comprobante" class="form-select form-select-sm">
                                    <option value="">TODOS</option>
                                    <option value="PAGADO">PAGADO</option>
                                    <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                                    <option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>
                                    <option value="CREDITO">CREDITO</option>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosComprobante()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('comprobante')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm" onclick="exportarExcelComprobante()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchComprobante" placeholder="Buscar">
                                    <?php
                                    $caja = !empty($this->caja_userSesion["message"][0]["id_caja_chica"]) ? $this->caja_userSesion["message"][0]["id_caja_chica"] : 0;
                                    ?>
                                    <input type="hidden" name="cj_chica" id="cj_chica" value="<?php echo $caja ?>">
                                    <button class="btn btn-success btnSTable btnSearch1">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span data-badge-tabla="comprobante" style="display:none; cursor:pointer;"
                                class="badge bg-info">
                            </span>
                        </div>
                        <table id="table_comprobante" class="table display responsive" cellspacing="0"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha de emisión</th>
                                    <th>Remitente</th>
                                    <th>Destinatario</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Fecha Salida</th>
                                    <th>Estado Envío</th>
                                    <th>Estado SUNAT</th>
                                    <th>T. Gravado</th>
                                    <th>T. Exonerado</th>
                                    <th>T. Inafecto</th>
                                    <th>T. IGV</th>
                                    <th>Total</th>
                                    <th>Tracking</th>
                                    <th>Estado venta</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-notaventa" role="tabpanel"
                        aria-labelledby="pills-notaventa-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_inicio_notaventa">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_notaventa">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="origen_notaventa">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select" id="destino_notaventa">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Envío</label>
                                <select class="form-select form-select-sm"
                                    id="estado_envio_notaventa">
                                    <option value="">TODOS</option>
                                    <option value="1">ENTREGADO</option>
                                    <option value="2">EN DESTINO</option>
                                    <option value="3">EN RUTA</option>
                                    <option value="4">EN ORIGEN</option>
                                    <option value="5">EN TRANSITO</option>
                                    <option value="6">MAL ENVIADO</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Venta</label>
                                <select id="estado_venta_notaventa" class="form-select form-select-sm">
                                    <option value="">TODOS</option>
                                    <option value="PAGADO">PAGADO</option>
                                    <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                                    <option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>
                                    <option value="CREDITO">CREDITO</option>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosNotaVenta()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('notaventa')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm me-2" onclick="exportarExcelNotaVenta()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>

                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchNotaVenta" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch2">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span data-badge-tabla="notaVenta" style="display:none; cursor:pointer;"
                                class="badge bg-info">
                            </span>
                        </div>
                        <table id="table_notaVenta" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha de emisión</th>
                                    <th>Remitente</th>
                                    <th>Destinatario</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Fecha Salida</th>
                                    <th>Estado Envío</th>
                                    <th>Precio</th>
                                    <th>Estado venta</th>
                                    <th>Tracking</th>
                                    <th>Comprobante pago</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-guiastransportista" role="tabpanel"
                        aria-labelledby="pills-guiastransportista-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_inicio_guiast">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_guiast">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="origen_guiast">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select" id="destino_guiast">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Envío</label>
                                <select class="form-select form-select-sm"
                                    id="estado_envio_guiast">
                                    <option value="">TODOS</option>
                                    <option value="1">ENTREGADO</option>
                                    <option value="2">EN DESTINO</option>
                                    <option value="3">EN RUTA</option>
                                    <option value="4">EN ORIGEN</option>
                                    <option value="5">EN TRANSITO</option>
                                    <option value="6">MAL ENVIADO</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Estado Venta</label>
                                <select id="estado_venta_guiast" class="form-select form-select-sm">
                                    <option value="">TODOS</option>
                                    <option value="PAGADO">PAGADO</option>
                                    <option value="PAGO EN DESTINO">PAGO EN DESTINO</option>
                                    <option value="PAGO EN BLOQUE">PAGO EN BLOQUE</option>
                                    <option value="CREDITO">CREDITO</option>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosGuiasT()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('guiast')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm me-2" onclick="exportarExcelGuiasT()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchGuiasT" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch6">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span data-badge-tabla="guiasTransportista" style="display:none; cursor:pointer;"
                                class="badge bg-info">
                            </span>
                        </div>
                        <table id="table_guiasTransportista" class="table display responsive" cellspacing="0"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha de emisión</th>
                                    <th>Remitente</th>
                                    <th>Destinatario</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Fecha Salida</th>
                                    <th>Estado Envío</th>
                                    <th>Precio</th>
                                    <th>Estado venta</th>
                                    <th>Tracking</th>
                                    <th>Comprobante pago</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-guias" role="tabpanel" aria-labelledby="pills-guias-tab"
                        tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_inicio_guiasrt">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_guiasrt">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">D. Partida</label>
                                <select class="form-select form-select-sm" id="partida_guiasrt">
                                    <option value="">TODOS</option>
                                    <?php foreach ($this->terminal_destino['message'] as $terminal): ?>
                                        <option value="<?php echo $terminal['ubigeo']; ?>">
                                            <?php echo $terminal['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">D. Destino</label>
                                <select class="form-select form-select-sm" id="destino_guiasrt">
                                    <option value="">TODOS</option>
                                    <?php foreach ($this->terminal_destino['message'] as $terminal): ?>
                                        <option value="<?php echo $terminal['ubigeo']; ?>">
                                            <?php echo $terminal['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Vehículo</label>
                                <select class="form-select form-select-sm"
                                    id="vehiculo_guiasrt">
                                    <option value="">TODOS</option>
                                    <?php if (!empty($this->vehiculo['message'])): ?>
                                        <?php foreach ($this->vehiculo['message'] as $vehiculo): ?>
                                            <option value="<?= $vehiculo['placa'] ?>">
                                                <?= $vehiculo['placa'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosGuiasRT()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('guiasrt')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm me-2" onclick="exportarExcelGuiasRT()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchGuiasRemision"
                                        placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch3">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_guias" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha de Emision</th>
                                    <th>Conductor</th>
                                    <th>Licencia</th>
                                    <th>Vehículo Placa</th>
                                    <th>D. Partida</th>
                                    <th>D. Destino</th>
                                    <th>Estado SUNAT</th>
                                    <th>Mensaje SUNAT</th>
                                    <th>Observaciones</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-historial" role="tabpanel"
                        aria-labelledby="pills-historial-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="fecha_inicio_embarcaciones">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="fecha_fin_embarcaciones">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select"
                                    id="origen_embarcaciones">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select"
                                    id="destino_embarcaciones">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vehículo</label>
                                <select class="form-select form-select-sm"
                                    id="vehiculo_embarcaciones">
                                    <option value="">TODOS</option>
                                    <?php if (!empty($this->vehiculo['message'])): ?>
                                        <?php foreach ($this->vehiculo['message'] as $vehiculo): ?>
                                            <option value="<?= $vehiculo['id_vehiculo'] ?>">
                                                <?= $vehiculo['placa'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosEmbarcaciones()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('embarcaciones')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm me-2" onclick="exportarExcelEmbarcaciones()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchHistorial" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch4">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_historialE" class="table display responsive" cellspacing="0"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Conductor</th>
                                    <th>Licencia</th>
                                    <th>Vehiculo</th>
                                    <th>Fecha de embarcacion</th>
                                    <th>Hora de embarcacion</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-historialD" role="tabpanel"
                        aria-labelledby="pills-historialD-tab" tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm"
                                    id="fecha_inicio_desembarques">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_desembarques">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="origen_desembarques">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select"
                                    id="destino_desembarques">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vehículo</label>
                                <select class="form-select form-select-sm"
                                    id="vehiculo_desembarques">
                                    <option value="">TODOS</option>
                                    <?php if (!empty($this->vehiculo['message'])): ?>
                                        <?php foreach ($this->vehiculo['message'] as $vehiculo): ?>
                                            <option value="<?= $vehiculo['id_vehiculo'] ?>">
                                                <?= $vehiculo['placa'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosDesembarques()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('desembarques')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm" onclick="exportarExcelDesembarques()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchHistorialD" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch5">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_historialD" class="table display responsive" cellspacing="0"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Conductor</th>
                                    <th>Licencia</th>
                                    <th>Vehiculo</th>
                                    <th>Fecha de embarcacion</th>
                                    <th>Hora de embarcacion</th>
                                    <th>Usuario</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="pills-grupales" role="tabpanel" aria-labelledby="pills-grupales-tab"
                        tabindex="0">
                        <div class="row g-2 align-items-end">
                            <div class="col-md">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_inicio_embgrupal">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_embgrupal">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Origen</label>
                                <select class="form-select form-select-sm terminal-select" id="origen_embgrupal">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destino</label>
                                <select class="form-select form-select-sm terminal-select" id="destino_embgrupal">
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vehículo</label>
                                <select class="form-select form-select-sm"
                                    id="vehiculo_embgrupal">
                                    <option value="">TODOS</option>
                                    <?php if (!empty($this->vehiculo['message'])): ?>
                                        <?php foreach ($this->vehiculo['message'] as $vehiculo): ?>
                                            <option value="<?= $vehiculo['id_vehiculo'] ?>">
                                                <?= $vehiculo['placa'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-1">
                                <button class="btn btn-primary btn-sm me-2" onclick="aplicarFiltrosEmbGrupal()">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary btn-sm me-2" onclick="limpiarFiltros('embgrupal')">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button class="btn btn-success btn-sm me-2" onclick="exportarExcelEmbGrupal()">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>

                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchGrupales" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch6">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_grupales" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Conductor</th>
                                    <th>Licencia</th>
                                    <th>Vehiculo</th>
                                    <th>Fecha de embarcacion</th>
                                    <th>Hora de embarcacion</th>
                                    <th>Usuario</th>
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