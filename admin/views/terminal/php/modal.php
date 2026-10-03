<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form" class="row g-2 needs-validation" novalidate>
                    <input type="hidden" id="id_terminal" name="id_terminal">
                    <div class="col-md-4">
                        <label for="">Logo <span class="text-warning fw-bolder ms-3">(.png, .jpg,
                                .jpeg)</span></label><br>
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <label for="logo" width="80" height="80" class="img-fluid img_logo my-2 position-relative"
                                id="show_logo">
                                <a href="javascript:void(0)" target="_blank" title="Abrir imagen"
                                    class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none"
                                    id="link_logo"><i class="bi bi-box-arrow-in-up-right"></i></a>
                            </label>
                            <input type="file" class="form-control d-none" name="logo" id="logo">
                            <input type="hidden" class="form-control" name="logo_before" id="logo_before">
                        </div>
                        <div id="Div_encargado" class="d-none">
                            <div class="my-2" id="div_parentTPersonal">
                                <label>Encargado de sucursal/oficina<span class="requiredField">*</span></label>
                                <select class="form-select" id="t_personal" name="t_personal" required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 my-2">
                                <label>Nombre<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" autocomplete="off"
                                    required>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Tipo<span class="requiredField">*</span></label>
                                <select class="form-select" name="tipo" id="tipo" required>
                                    <option value="AGENCIA">AGENCIA</option>
                                    <option value="SUCURSAL">SUCURSAL</option>
                                    <option value="OFICINA ADMINISTRATIVA">OFICINA ADMINISTRATIVA</option>
                                </select>
                            </div>
                            <div class="col-md-3 my-2">
                                <label for="serie_manifiesto">Serie manifiesto<span class="optionalField">*</span></label>
                                <select class="form-select" id="serie_manifiesto" name="serie_manifiesto" required>
                                    <option value="">Seleccione</option>
                                    <?php if ($this->series_manifiesto["success"]): ?>
                                        <?php foreach ($this->series_manifiesto["message"] as $serie_manifiesto): ?>
                                            <?php $text = $serie_manifiesto["serie"] ?>
                                            <option value="<?= $serie_manifiesto["id_serie_manifiesto"] ?>">
                                                <?= $text ?>
                                            </option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <?php $col = "col-md-6";
                            include("views/templates/components/cmp_select_ubigeo.php") ?>
                            <div class="col-md-6 my-2">
                                <label>Cod. Domicilio Fiscal<span class="requiredField">*</span><span
                                        class="text-warning fw-bolder mx-2">SUNAT</span></label>
                                <input type="text" class="form-control" id="cod_domicilioFiscal"
                                    name="cod_domicilioFiscal" autocomplete="off" required>
                            </div>
                            <div class="col-md-6 my-2">
                                <label>Dirección Domicilio Fiscal<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="dir_domicilioFiscal"
                                    name="dir_domicilioFiscal" autocomplete="off" required>
                            </div>
                            <div class="col-md-6 my-2">
                                <label>Dirección Domicilio Comercial<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="dir_domicilioComercial"
                                    name="dir_domicilioComercial" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 my-2">
                        <label>Celular<span class="requiredField">*</span></label>
                        <input type="text" class="form-control" id="celular" name="celular" minlength="9" maxlength="9"
                            autocomplete="off" required>
                    </div>
                    <div class="col-md-3 my-2">
                        <label>Correo electrónico<span class="requiredField">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="off" required>
                    </div>
                    <div class="col-md-3 my-2">
                        <label>Sitio web</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="siteweb" name="siteweb" autocomplete="off">
                            <a class="input-group-text d-none" target="_blank" id="link_siteweb"><i
                                    class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                    </div>
                    <div class="col-md-1 my-2">
                        <label>Color<span class="requiredField">*</span></label>
                        <input type="color" class="form-control" id="color" name="color" autocomplete="off" required>
                    </div>
                    <div class="col-md-2 my-2">
                        <label for="c_selva">Selva?</label>
                        <div class="d-flex justify-content-center align-items-center" style="height: 38px;">
                            <input type="checkbox" class="form-check-input" id="c_selva" name="c_selva"
                                style="width: 1.5rem; height: 1.5rem;">
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