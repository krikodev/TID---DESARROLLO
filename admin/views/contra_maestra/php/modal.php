<!-- Modal add Register-->
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
                        <input type="hidden" id="id_serie" name="id_serie">
                        <?php
                        $col = "col-md-6";
                        include('./views/templates/components/cmp_select_terminal.php') ?>
                        <?php
                        $col = "col-md-6";
                        include('./views/templates/components/cmp_select_tp_comprobante.php') ?>
                        <div class="primero" style="display: block;">
                            <div class="row g-2 my-2">
                                <div class="col-md-6 my-2">
                                    <label>Serie<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="serie" name="serie" autocomplete="off"
                                        required>
                                </div>
                                <div class="col-md-4 my-2">
                                    <label>Correlativo<span class="requiredField">*</span></label>
                                    <input type="number" class="form-control" id="correlativo" name="correlativo"
                                        min="1" autocomplete="off" required>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para la creacion de series correspondientes a notas de credito y debito-->
                        <div class="grupo-campos-oculto" style="display: none;">
                            <div class="row g-2 my-2">
                                <div class="col-md-6 my-2">
                                    <label>Serie para Factura<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="serie2" name="serie2" autocomplete="off"
                                        required>
                                </div>
                                <div class="col-md-4 my-2">
                                    <label>Correlativo<span class="requiredField">*</span></label>
                                    <input type="number" class="form-control" id="correlativo2" name="correlativo2"
                                        min="1" autocomplete="off" required>
                                </div>

                                <div class="col-md-6 my-2">
                                    <label>Serie para Boleta<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="serie3" name="serie3" autocomplete="off"
                                        required>
                                </div>
                                <div class="col-md-4 my-2">
                                    <label>Correlativo<span class="requiredField">*</span></label>
                                    <input type="number" class="form-control" id="correlativo3" name="correlativo3"
                                        min="1" autocomplete="off" required>
                                </div>
                            </div>
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