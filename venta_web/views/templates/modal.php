<div class="offcanvas offcanvas-start" tabindex="-1" id="modal_perfil" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header" style="background-color:#495057;">
        <h5 class="offcanvas-title text-white" id="modal_perfil">Perfil</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <?php $data_usuario = Session::get("data_usuario") ?>
    <div class="offcanvas-body">
        <form id="form_user" class="row g-2 needs-validation" novalidate>
            <div class="col-md-12 d-flex justify-content-center align-items-center">
                <i class="fa-regular fa-circle-user my-2 text-secondary" style="width: 50px; height: 50px;"></i>
            </div>
            <div class="col-md-6">
                <label for="">Nombres</label><br>
                <input type="text" class="form-control" name="nombres_pf" id="nombres_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Apellidos</label><br>
                <input type="text" class="form-control" name="apellidos_pf" id="apellidos_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Fecha nacimiento</label><br>
                <input type="text" class="form-control" name="f_naci_pf" id="f_naci_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Tipo de documento</label><br>
                <input type="text" class="form-control" name="tp_docu_pf" id="tp_docu_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Número de documento</label><br>
                <input type="text" class="form-control" name="num_docu_pf" id="num_docu_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Cod. ubigeo</label><br>
                <input type="text" class="form-control" name="ubigeo_pf" id="ubigeo_pf" readonly required>
            </div>
            <div class="col-md-6">
                <label for="">Dirección <span class="requiredField">*</span></label><br>
                <input type="text" class="form-control" name="direccion_pf" id="direccion_pf" autocomplete="off" required>
            </div>
            <div class="col-md-6">
                <label for="">Celular <span class="requiredField">*</span></label><br>
                <input type="text" class="form-control" name="celular_pf" id="celular_pf" minlength="9" maxlength="9" autocomplete="off" required>
            </div>
            <div class="col-md-6">
                <label for="">Correo electrónico <span class="requiredField">*</span></label><br>
                <input type="text" class="form-control" name="email_pf" id="email_pf" autocomplete="off" required>
            </div>
            <div class="col-md-6">
                <label for="">Contraseña <span class="requiredField">*</span></label><br>
                <input type="password" class="form-control" name="pass_pf" id="pass_pf" autocomplete="off" required>
            </div>
            <div class="modal-footer mt-3">
                <button type="submit" class="btn btnSave" id="button_save_profile">Guardar</button>
                <button class="btn btnSave d-none" type="button" id="button_loadSave_profile" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Guardando...
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Imprimir-->
<div class="modal fade" id="modal_imprimir" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">IMPRIMIR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" id="idv_imprimir">
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="tab_preview" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#ticket80" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Ticket 80mm</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#ticket58" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Ticket 58mm</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#ticket57" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Ticket 57mm</button>
                    </li>
                </ul>
                <div class="tab-content" id="tabContent_preview">
                    <div class="tab-pane fade" id="ticket80" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                    </div>
                    <div class="tab-pane fade" id="ticket58" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
                    </div>
                    <div class="tab-pane fade" id="ticket57" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="email_send" name="email_send">
                        <button type="button" class="btn btn-secondary"><i class="bi bi-envelope"></i> Enviar</button>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">+51</span>
                        <input type="text" class="form-control" id="num_wsp" name="num_wsp">
                        <button type="button" class="btn btn-secondary" id="btnEnviarWSP"><i class="bi bi-whatsapp"></i> WhatsApp</button>
                    </div>
                    <a href="<?php echo URL; ?>ventas" class="btn text-white" style="background-color:#743bbf" id="button_ventas">Ir a Registro de Ventas</a>
                    <a class="btn btnSave" id="button_cancel" data-bs-dismiss="modal">Continuar</a>
                </div>
            </div>
        </div>
    </div>
</div>