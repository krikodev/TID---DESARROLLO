<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <div class="col-md-12 row">
                            <input type="hidden" id="id_conductor" name="id_conductor">
                            <div class="col-md-4">
                                <label>Tipo de documento<span class="requiredField">*</span></label>
                                <select class="form-select" name="tp_docu" id="tp_docu">
                                    <option value="1">DNI</option>
                                    <option value="4">Carnet extranjeria</option>
                                    <option value="6">RUC</option>
                                    <option value="7">Pasaporte</option>
                                    <option value="0">Otros documentos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>N° Documento<span class="requiredField">*</span></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="num_docu" name="num_docu" autocomplete="off" required minlength="8" maxlength="8">
                                    <button type="button" class="btn btnSearch" id="button_search">BUSCAR</button>
                                    <button class="btn btnSearch d-none" type="button" id="button_loadSearch" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        BUSCAR
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 row">
                            <div class="col-md-3 my-2">
                                <label>Nombres<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="nombres" name="nombres" readonly autocomplete="off" required>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Apellidos<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" readonly autocomplete="off" required>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" autocomplete="off">
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Estado civil</label>
                                <select class="form-select" id="estado_civil" name="estado_civil">
                                    <option value="">Seleccione</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Conviviente">Conviviente</option>
                                    <option value="Separado(a)">Separado(a)</option>
                                    <option value="Viudo(a)">Viudo(a)</option>
                                    <option value="Soltero(a)">Soltera(a)</option>
                                </select>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Género</label>
                                <select class="form-select" id="genero" name="genero">
                                    <option value="">Seleccione</option>
                                    <option value="MASCULINO">MASCULINO</option>
                                    <option value="FEMENINO">FEMENINO</option>
                                </select>
                            </div>
                            <?php $col = "col-md-3";
                            include("views/templates/components/cmp_select_ubigeo.php") ?>
                            <div class="col-md-3 my-2">
                                <label>Dirección<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="direccion" name="direccion" autocomplete="off" value="S/D" required>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Celular<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="celular" name="celular" minlength="9" maxlength="9" autocomplete="off">
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Correo electrónico<span class="requiredField">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" autocomplete="off" required>
                            </div>
                            <div class="col-md-3 my-2 d-none">
                                <label>Estado SUNAT</label>
                                <input type="text" class="form-control" id="estado_sunat" name="estado_sunat" autocomplete="off" readonly>
                            </div>
                            <div class="col-md-3 my-2 d-none">
                                <label>Condición SUNAT</label>
                                <input type="text" class="form-control" id="condicion_sunat" name="condicion_sunat" autocomplete="off" readonly>
                            </div>
                            <?php $col = "col-md-3";
                            include("views/templates/components/cmp_select_terminal.php") ?>
                            <div class="col-md-3 my-2">
                                <label>Tipo de usuario<span class="requiredField">*</span></label>
                                <select class="form-select" name="tp_usuario" id="tp_usuario" required>
                                    <option value="4">CONDUCTOR</option>
                                    <option value="10">COPILOTO</option>
                                </select>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Estado<span class="requiredField">*</span></label>
                                <select class="form-select" name="estado" id="estado" required>
                                    <option value="1">Habilitado</option>
                                    <option value="0">Deshabilitado</option>
                                </select>
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Licencia<span class="requiredField">*</span></label>
                                <input type="text" class="form-control" id="licencia" name="licencia" autocomplete="off">
                            </div>
                            <div class="col-md-3 my-2">
                                <label>Categoría<span class="requiredField">*</span></label>
                                <select class="form-select" name="ctg_licencia" id="ctg_licencia" required>
                                    <option value="A-I">A-I</option>
                                    <option value="A-IIa">A-IIa</option>
                                    <option value="A-IIb">A-IIb</option>
                                    <option value="A-IIIa">A-IIIa</option>
                                    <option value="A-IIIb">A-IIIb</option>
                                    <option value="A-IIIc">A-IIIc</option>
                                    <option value="B-I">B-I</option>
                                    <option value="B-IIa">B-IIa</option>
                                    <option value="B-IIb">B-IIb</option>
                                    <option value="B-IIc">B-IIc</option>
                                    <option value="A-IV">A-IV</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel" data-bs-dismiss="modal">Cancelar</button>
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