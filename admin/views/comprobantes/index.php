<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="col-md-12 d-flex flex-column align-items-center justify-content-center text-center">
                    <ul class="nav nav-pills mb-3" id="pillsTab_contrato" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_comprobante" data-bs-toggle="pill"
                                data-bs-target="#comprobante" type="button" role="tab" aria-controls="tab_comprobante"
                                aria-selected="true">Comprobantes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_resumen" data-bs-toggle="pill" data-bs-target="#resumen"
                                type="button" role="tab" aria-controls="tab_resumen"
                                aria-selected="false">Resumenes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_reenvio" data-bs-toggle="pill" data-bs-target="#reenvio"
                                type="button" role="tab" aria-controls="tab_reenvio" aria-selected="false">Reenvio por
                                resumen</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="comprobante" role="tabpanel"
                        aria-labelledby="tab_comprobante" tabindex="0">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="form-label mb-1">Fecha Inicio</label>
                                <input type="date" id="filtro_fecha_inicio_comp" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Fecha Fin</label>
                                <input type="date" id="filtro_fecha_fin_comp" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Comprobante</label>
                                <select id="filtro_tipo_comp" class="form-select form-select-sm">
                                    <option value="">TODOS</option>
                                    <option value="1">FACTURA</option>
                                    <option value="3">BOLETA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Tipo</label>
                                <select id="filtro_tipo_venta" class="form-select form-select-sm">
                                    <option value="">TODOS</option>
                                    <option value="1">PASAJE</option>
                                    <option value="2">ENCOMIENDA</option>
                                    <option value="3">FACTURADOR</option>
                                    <option value="4">NOTAS BLOQUE</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm flex-fill" id="btnFiltrarComp">
                                        <i class="bi bi-search me-1"></i>Filtrar
                                    </button>
                                    <button class="btn btn-secondary btn-sm flex-fill" id="btnLimpiarFiltrosComp">
                                        <i class="bi bi-eraser me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-2">
                                <button class="btn btn-success btn-sm text-white" id="btnExportarExcelComp"
                                    title="Exportar a Excel">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearch" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch1">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_comprobantes" class="table display responsive" cellspacing="0"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Emisión</th>
                                    <th>Cliente</th>
                                    <th>Número</th>
                                    <th>Estado</th>
                                    <th>Tipo</th>
                                    <th>T. Gravado</th>
                                    <th>T. Exonerado</th>
                                    <th>T. Inafecto</th>
                                    <th>T. IGV</th>
                                    <th>Total</th>
                                    <th>Estado SUNAT</th>
                                    <th>Forma Pago</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade show" id="resumen" role="tabpanel" aria-labelledby="tab_resumen"
                        tabindex="0">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Fecha Inicio</label>
                                <input type="date" id="filtro_fecha_inicio_res" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Fecha Fin</label>
                                <input type="date" id="filtro_fecha_fin_res" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm flex-fill" id="btnFiltrarRes">
                                        <i class="bi bi-search me-1"></i>Filtrar
                                    </button>
                                    <button class="btn btn-secondary btn-sm flex-fill" id="btnLimpiarFiltrosRes">
                                        <i class="bi bi-eraser me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2 d-flex justify-content-end my-2">
                            <div class="col-md-2">
                                <button class="btn btn-success btn-sm" id="btnExportarExcelRes"
                                    title="Exportar a Excel">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                            </div>
                            <div class="col-md-6">
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchResumen" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch2">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_resumen" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Fecha Emision</th>
                                    <th>Fecha Emision C.</th>
                                    <th>Identificador</th>
                                    <th>Ticket</th>
                                    <th>Mensaje SUNAT</th>
                                    <th>Código SUNAT</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade show" id="reenvio" role="tabpanel" aria-labelledby="tab_reenvio"
                        tabindex="0">
                        <div class="col-md-auto d-flex gap-2">
                            <button class="btn btn-outline-primary" id="btnSeleccionarTodos">
                                <i class="fa-light fa-check-double"></i> Seleccionar todos
                            </button>
                            <button class="btn btn-primary text-white" id="btnCrearResumen">
                                <i class="fa-light fa-file-plus"></i> Crear resumen
                            </button>
                        </div>
                        <div class="row d-flex  my-2">
                            <div class="col-md-3" id="divParent_ruc">
                                <select class="form-select" id="ruc" name="ruc">
                                    <?php if ($this->rucs['success']): ?>
                                        <?php foreach ($this->rucs['message'] as $ruc): ?>
                                            <option value="<?= $ruc['id'] ?>"><?= $ruc['num_docu'] ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control date-width"
                                    aria-label="Fecha de inicio" />
                            </div>

                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-arrows-left-right fs-4"></i>
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control date-width"
                                    aria-label="Fecha de fin" />
                            </div>
                            <div class="col-md-4 d-flex">
                                <div class="input-group">
                                    <input type="text" class="form-control inputSearchReenvio" placeholder="Buscar">
                                    <button class="btn btn-success btnSTable btnSearch3">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="table_reenvio" class="table display responsive" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>T. Gravado</th>
                                    <th>T. Exonerado</th>
                                    <th>T. Inafecto</th>
                                    <th>T. IGV</th>
                                    <th>Total</th>
                                    <th>Fecha Emision</th>
                                    <th>Estado SUNAT</th>
                                    <th></th>
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

<div class="modal fade" id="modal_nota" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">Notas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_notas" method="post" class="needs-validation" novalidate
                    onkeydown="return event.key != 'Enter';">
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_ventaPasaje" name="id_ventaPasaje">
                        <input type="hidden" name="tp_servicio" id="tp_servicio">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3 my-2" id="div_parentTpComprobante">
                                    <input type="hidden" id="id_venta" name="id_venta">
                                    <label>Tipo de comprobante<span class="requiredField">*</span></label>
                                    <select class="form-select" id="list_comp" name="list_comp" required>
                                        <option value="07">Nota credito</option>
                                        <option value="08">Nota debito</option>
                                    </select>
                                </div>
                                <div class="col-md-2 my-2">
                                    <input type="hidden" id="txtcorrelativo_nota" name="txtcorrelativo_nota">
                                    <label>Serie<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <select class="form-select" id="txtserie_nota" name="txtserie_nota"
                                            required></select>
                                    </div>
                                </div>
                                <div class="col-md-2 my-2" id="div_parentSerie">
                                    <label>Comprobante<span class="requiredField">*</span></label>
                                    <input class="form-control" name="txtserie" id="txtserie" style="font-size: 12px;"
                                        readonly required>
                                </div>
                                <div class="col-md-1 my-2" id="div_parentSerie">
                                    <label>C.<span class="requiredField">*</span></label>
                                    <input class="form-control" name="txtcorrelativo" id="txtcorrelativo"
                                        style="font-size: 12px;" readonly required>
                                </div>
                                <div class="col-md-4 my-2" id="div_parentCliente">
                                    <label>Cliente<span class="requiredField me-2">*</span></label>
                                    <input class="form-control" id="txtcliente" name="txtcliente"
                                        style="font-size: 12px;" readonly required>
                                </div>
                                <div class="col-md-4 my-2">
                                    <label>Motivo de nota<span class="requiredField">*</span></label>
                                    <select class="form-select" name="list_motivo" id="list_motivo" required></select>
                                </div>
                                <div class="col-md-6 my-2">
                                    <label>Descripción<span class="requiredField">*</span></label>
                                    <input class="form-control" id="txt_descripcion" name="txt_descripcion"
                                        style="font-size: 12px;" required>
                                </div>
                                <div class="col-md-2 my-2">
                                    <label>Moneda<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <input class="form-control" id="txt_moneda" name="txt_moneda"
                                            style="font-size: 12px;" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Fecha de emisión<span class="requiredField">*</span></label>
                                    <input type="date" class="form-control" name="txt_fecha" id="txt_fecha"
                                        value="<?= date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <table id="table_productos" class="table display responsive" cellspacing="0"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th>Unidad</th>
                                        <th>Cantidad</th>
                                        <th>P. Unit</th>
                                        <th>Descuento</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_productos"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4"></th>
                                        <th>OP GRAVADA:</th>
                                        <th id="op_gravada">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4"></th>
                                        <th>OP EXONERADA:</th>
                                        <th id="op_exonerada">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4"></th>
                                        <th>OP INAFECTA:</th>
                                        <th id="op_inafecta">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4"></th>
                                        <th>IGV:</th>
                                        <th id="igv_total">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4"></th>
                                        <th>TOTAL PAGAR:</th>
                                        <th id="total_pagar" class="fs-5">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save">Generar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_precio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">Editar Precio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_precio" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_serie" name="id_serie">
                        <div class="primero" style="display: block;">
                            <div class="row g-2 my-2">
                                <div class="col-md-6 my-2">
                                    <label>Precio total<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text py-0" id="basic-addon1">S/</span>
                                        <input type="text" class="form-control" id="precio_total" name="precio_total"
                                            autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="btn_cancel"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="btn_save">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="btn_loadSave" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>