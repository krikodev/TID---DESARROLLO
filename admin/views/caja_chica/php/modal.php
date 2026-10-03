<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_caja_chica" name="id_caja_chica">

                        <div class="col-md-6 my-2">
                            <label>Vendedor<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" autocomplete="off"
                                value="<?php echo $data_usuario["nombres"] . " " . $data_usuario["apellidos"] ?>"
                                readonly required>
                        </div>
                        <div class="col-md-6 my-2">
                            <label>Saldo Inicial<span class="requiredField">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text py-0">S/</span>
                                <input type="text" class="form-control" id="saldo_inicial" name="saldo_inicial"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6 my-2">
                            <label>Referencia<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="referencia" name="referencia" autocomplete="off"
                                minlength="5" maxlength="50" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save">Guardar</button>
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

<div class="modal fade" id="modal_reporte" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">LIQUIDACIÓN POR TERMINAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <?php
                $col = "col-md-5";
                include("views/templates/components/cmp_select_terminal.php");
                ?>
                <div class="col-md-auto my-2">
                    <label>Fecha Inicio<span class="requiredField">*</span></label>
                    <input type="date" class="form-control" id="fecha_inicio_reporte" name="fecha_inicio_reporte"
                        value="<?php echo date("Y-m-d") ?>" required>
                </div>
                <div class="col-auto my-2 d-flex align-items-end">
                    <button type="button" class="btn btnSearch text-white" id="btn_generarReporte">Generar</button>
                </div>
                <div class="col-md-12 d-flex justify-content-center align-items-center d-none" id="div_imprimir"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_cierre_caja" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">CERRA CAJA CHICA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100 row">
                <form id="form_cierre" class="needs-validation" novalidate>
                    <input type="hidden" id="id_caja" name="id_caja">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="observaciones">Monto real <span class="requiredField">*</span></label>
                            <input class="form-control" type="number" name="monto_real" id="monto_real"
                                placeholder="Ingrese el monto con el que cierra caja">
                        </div>
                        <div class=" col-md-8">
                            <label for="observaciones">Observaciones</label>
                            <input type="text" id="observaciones" name="observaciones" class="form-control"
                                placeholder="Si en caso tuviera observaciones, ingreselas aqui...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <label for="">Detalle de egresos (Opcional)</label>
                        <table class="table todo_list w-100" cellspacing="0" id="table_egreso">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th><button type="button" class="btn p-0" id="btn_add_egreso"><i
                                                class="bi bi-plus-square text-info fs-5"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                    <td class="fs-6">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td id="monto_total" class="fs-6">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btnSave" id="button_save_c">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_c" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                        <button type="button" class="btn btnCancel" id="button_cancel_c"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Reporte Consolidado -->
<div class="modal fade" id="modal_reporte_cajas" tabindex="-1" aria-labelledby="modalReporteCajasLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:#3FCCBA;">
                <h5 class="modal-title text-white" id="modalReporteCajasLabel">
                    <i class="fa-solid fa-chart-column"></i>
                    Reporte Consolidado de Cajas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form_reporte_cajas">
                <div class="modal-body">

                    <div class="alert alert-light border mb-3">
                        Busca cajas cerradas por fecha, terminal o vendedor antes de generar el reporte.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de cierre</label>
                        <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3" id="div_parentTerminalR">
                        <label class="form-label">Terminal</label>
                        <select class="form-select" name="id_terminal_reporte" id="id_terminal_reporte">
                            <option value="0">Todas las terminales</option>

                            <?php if ($this->terminal["success"]): ?>
                                <?php foreach ($this->terminal["message"] as $terminal): ?>
                                    <option value="<?= $terminal["id_terminal"] ?>">
                                        <?= $terminal["nombre"] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="div_parentPersonalR">
                        <label class="form-label">Vendedor</label>
                        <select class="form-select" name="id_usuario_reporte" id="id_usuario_reporte">
                            <option value="0">Todos los vendedores</option>
                        </select>

                        <small class="text-muted">
                            Al seleccionar una terminal se cargarán automáticamente sus vendedores.
                        </small>
                    </div>

                    <div id="resultado_reporte_cajas" class="mt-3"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn text-white" style="background:#3FCCBA;">
                        <i class="fa-solid fa-search"></i>
                        Buscar cajas
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>