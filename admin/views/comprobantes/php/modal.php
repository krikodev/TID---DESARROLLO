<!-- Modal de pago comprobante -->
<div class="modal fade" id="modal_pc" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="titulo_modal_pc" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title text-white" id="titulo_modal_pc">PAGAR COMPROBANTE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_pc" class="needs-validation" novalidate>
                    <div class="row g-3 my-2">
                        <input type="hidden" id="id_comprobante_pc" name="id_comprobante_pc">

                        <div class="col-md-4" id="div_parentMedioPagoPc">
                            <label for="medio_pago_pc" class="form-label">
                                Medio Pago<span class="requiredField text-danger">*</span>
                            </label>
                            <select id="medio_pago_pc" name="medio_pago_pc" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php if ($this->medio_pago["success"]): ?>
                                    <?php foreach ($this->medio_pago["message"] as $medio_pago): ?>
                                        <?php $text = $medio_pago["descripcion"] ?>
                                        <option value="<?= $medio_pago["id_medio_pago"] ?>">
                                            <?= $text ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un medio de pago.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="fecha_pc" class="form-label">
                                Fecha de pago<span class="requiredField text-danger">*</span>
                            </label>
                            <input type="date" name="fecha_pc" id="fecha_pc" class="form-control" required>
                            <div class="invalid-feedback">Ingrese la fecha de pago.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="id_caja_chica" class="form-label">
                                Caja chica<span class="requiredField text-danger">*</span>
                            </label>
                            <?php if ($this->caja_userSesion["success"]): ?>
                                <input type="text" class="form-control" name="id_caja_chica" id="id_caja_chica"
                                    autocomplete="off" value="<?= $this->caja_userSesion["message"][0]["referencia"]; ?>"
                                    disabled>
                                <input type="hidden" name="caja_chica_pc" id="caja_chica_pc"
                                    value="<?= $this->caja_userSesion["message"][0]["id_caja_chica"]; ?>">
                            <?php endif ?>
                        </div>

                        <div class="col-md-12">
                            <label for="pago_file" class="form-label">
                                Suba algún archivo relacionado al pago <span class="text-muted">(opcional)</span>
                            </label>
                            <input type="file" name="pago_file" id="pago_file" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Formatos permitidos: PDF, JPG, PNG.</div>
                            <div class="invalid-feedback">Adjunte un archivo de pago.</div>
                        </div>

                        <div class="col-md-12">
                            <label for="observacion_pc" class="form-label">
                                Observación <span class="text-muted">(opcional)</span>
                            </label>
                            <input type="text" name="observacion_pc" id="observacion_pc" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="button_cancel_pc"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="button_save_pc">Guardar</button>
                        <button class="btn btn-primary d-none" type="button" id="button_loadSave_pc" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal detalle de pago credito -->
<div class="modal fade" id="modal_detallePC" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo_modal_pc">DETALLE DE PAGO DE CREDITO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <table id="tabla_detalle_pago">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>