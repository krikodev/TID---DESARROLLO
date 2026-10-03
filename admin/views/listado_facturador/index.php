<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="row g-2">
            <div
                class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                <h2>
                    <a href="<?php echo URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                    Listado Facturador
                </h2>
                <div>
                    <a class="btn btnAddRegis" href="<?= URL ?>facturador">Nuevo comprobante</a>
                </div>
            </div>
        </div>

        <!-- Header Page  -->
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">

                <!-- Filtros superiores -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="filtro_fecha_desde" class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="filtro_fecha_desde" name="filtro_fecha_desde">
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_fecha_hasta" class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="filtro_fecha_hasta" name="filtro_fecha_hasta">
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_tipo_comprobante" class="form-label">Tipo Comprobante</label>
                        <select class="form-select" id="filtro_tipo_comprobante" name="filtro_tipo_comprobante">
                            <option value="">Todos</option>
                            <option value="1">Factura</option>
                            <option value="2">Nota de Venta</option>
                            <option value="3">Boleta</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_estado" class="form-label">Estado</label>
                        <select class="form-select" id="filtro_estado" name="filtro_estado">
                            <option value="">Todos</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="ANULADO">ANULADO</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                        </select>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="row g-2 d-flex justify-content-between my-2">
                    <div class="col-md-4">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnAplicarFiltros">
                                <i class="fas fa-filter me-1"></i> Aplicar Filtros
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                                <i class="fas fa-times me-1"></i> Limpiar
                            </button>
                            <!-- NUEVO BOTÓN DE EXCEL -->
                            <button type="button" class="btn btn-success" id="btnExportarExcel">
                                <i class="fas fa-file-excel me-1"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputBusquedaGlobal"
                                placeholder="Buscar en todos los campos...">
                            <button class="btn btn-success" id="btnBuscarGlobal">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="table_listado_facturador" class="table table-striped display responsive"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Emisión</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>RUC/DNI</th>
                                <th>Tipo</th>
                                <th>Op. Gravada</th>
                                <th>Op. Exonerada</th>
                                <th>Op. Inafecta</th>
                                <th>IGV</th>
                                <th>ICBPER</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Estado SUNAT</th>
                                <th>Detracción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle del comprobante -->
<div class="modal fade" id="modalDetalleComprobante" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalDetalleComprobanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleComprobanteLabel">Detalle del Comprobante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detalleComprobanteContent">
                    <!-- Aquí se cargará el contenido dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnImprimirComprobante">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para anular comprobante -->
<div class="modal fade" id="modalAnularComprobante" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalAnularComprobanteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalAnularComprobanteLabel">Anular Comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAnularComprobante">
                    <input type="hidden" id="idComprobanteAnular" name="id_venta">
                    <div class="mb-3">
                        <label for="motivoAnulacion" class="form-label">Motivo de anulación <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoAnulacion" name="motivo" rows="3" required></textarea>
                        <div class="form-text">Ingrese el motivo por el cual desea anular este comprobante.</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Esta acción no se puede deshacer. Se generará la nota correspondiente si
                        es necesario.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarAnulacion">
                    <i class="fas fa-ban me-1"></i> Anular Comprobante
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reenviar a SUNAT -->
<div class="modal fade" id="modalReenviarSunat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalReenviarSunatLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalReenviarSunatLabel">Reenviar a SUNAT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idComprobanteReenviar" name="id_venta">
                <div class="text-center">
                    <i class="fas fa-paper-plane fa-3x text-info mb-3"></i>
                    <p>¿Está seguro que desea reenviar este comprobante a SUNAT?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Solo se pueden reenviar comprobantes que no hayan sido procesados correctamente por SUNAT.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnConfirmarReenvio">
                    <i class="fas fa-paper-plane me-1"></i> Reenviar a SUNAT
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Nota -->
<div class="modal fade" id="modalCrearNota" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearNotaLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCrearNotaLabel">Crear Nota</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearNota">
                    <input type="hidden" id="idVentaNota" name="id_venta">

                    <!-- Información del comprobante original -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Comprobante Original</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Serie-Correlativo</label>
                                    <input type="text" class="form-control" id="txtSerieOriginal" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="txtClienteOriginal" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total</label>
                                    <input type="text" class="form-control" id="txtTotalOriginal" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Moneda</label>
                                    <input type="text" class="form-control" id="txtMonedaOriginal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de la Nota -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="tipoNota" class="form-label">Tipo de Nota *</label>
                            <select class="form-select" id="tipoNota" name="list_comp" required>
                                <option value="07">Nota de Crédito</option>
                                <option value="08">Nota de Débito</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="serieNota" class="form-label">Serie *</label>
                            <select class="form-select" id="serieNota" name="txtserie_nota" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="correlativoNota" class="form-label">Correlativo</label>
                            <input type="text" class="form-control" id="correlativoNota" name="txtcorrelativo_nota"
                                readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="motivoNota" class="form-label">Motivo *</label>
                            <select class="form-select" id="motivoNota" name="list_motivo" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fechaNota" class="form-label">Fecha Emisión *</label>
                            <input type="date" class="form-control" id="fechaNota" name="txt_fecha" required
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="descripcionNota" class="form-label">Descripción *</label>
                            <input type="text" class="form-control" id="descripcionNota" name="txt_descripcion"
                                required>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="mt-4">
                        <h6>Productos</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="tableProductosNota">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Op. Exonerada</th>
                                        <th>Op. Gravada</th>
                                        <th>Op. Inafecta</th>
                                        <th>Descuento</th>
                                        <th>IGV</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llenará dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarNota">
                    <i class="fas fa-save me-1"></i> Guardar Nota
                </button>
                <button class="btn btn-primary d-none" type="button" id="btnCargandoNota" disabled>
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Procesando...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Precio -->
<div class="modal fade" id="modalEditarPrecio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalEditarPrecioLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditarPrecioLabel">Editar Precio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPrecio">
                    <div class="mb-3">
                        <label for="precioTotal" class="form-label">Precio Total *</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="precioTotal" name="precio_total" step="0.01"
                                min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarPrecio">
                    <i class="fas fa-save me-1"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de productos -->
<div class="modal fade" id="modalDetalleProductos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalDetalleProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalDetalleProductosLabel">Detalle de Productos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm" id="tableDetalleProductos">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Valor Total</th>
                                <th>IGV</th>
                                <th>ICBPER</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de carga -->
<div class="modal fade" id="modalCarga" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-white">Procesando solicitud...</p>
            </div>
        </div>
    </div>
</div>