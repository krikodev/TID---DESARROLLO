<!-- Modal add Register -->
<div class="modal fade"
    id="modal_conductor"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby="staticBackdropLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel_c">
                    NUEVO CONDUCTOR
                </h5>

                <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <div class="modal-body h-100">

                <form id="form_conductor_c"
                    class="needs-validation"
                    novalidate>

                    <div class="row g-2 my-2">

                        <div class="col-md-12 row">

                            <input type="hidden"
                                id="id_conductor_c"
                                name="id_conductor_c">

                            <!-- Tipo documento -->
                            <div class="col-md-4">

                                <label>
                                    Tipo de documento
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    name="tp_docu_c"
                                    id="tp_docu_c">

                                    <option value="1">DNI</option>
                                    <option value="4">Carnet extranjeria</option>
                                    <option value="6">RUC</option>
                                    <option value="7">Pasaporte</option>
                                    <option value="0">Otros documentos</option>

                                </select>

                            </div>

                            <!-- Documento -->
                            <div class="col-md-4">

                                <label>
                                    N° Documento
                                    <span class="requiredField">*</span>
                                </label>

                                <div class="input-group mb-3">

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="num_docu_c"
                                        name="num_docu_c"
                                        autocomplete="off"
                                        required
                                        minlength="8"
                                        maxlength="8">

                                    <button
                                        type="button"
                                        class="btn btnSearch"
                                        id="button_search_c">
                                        BUSCAR
                                    </button>

                                    <button
                                        class="btn btnSearch d-none"
                                        type="button"
                                        id="button_loadSearch_c"
                                        disabled>

                                        <span
                                            class="spinner-border spinner-border-sm"
                                            role="status"
                                            aria-hidden="true">
                                        </span>

                                        BUSCAR

                                    </button>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-12 row">

                            <!-- Nombres -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Nombres
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombres_c"
                                    name="nombres_c"
                                    readonly
                                    autocomplete="off"
                                    required>

                            </div>

                            <!-- Apellidos -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Apellidos
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="apellidos_c"
                                    name="apellidos_c"
                                    readonly
                                    autocomplete="off"
                                    required>

                            </div>

                            <!-- Fecha nacimiento -->
                            <div class="col-md-3 my-2">

                                <label>Fecha de nacimiento</label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="fecha_nacimiento_c"
                                    name="fecha_nacimiento_c"
                                    autocomplete="off">

                            </div>

                            <!-- Estado civil -->
                            <div class="col-md-3 my-2">

                                <label>Estado civil</label>

                                <select
                                    class="form-select"
                                    id="estado_civil_c"
                                    name="estado_civil_c">

                                    <option value="">Seleccione</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Conviviente">Conviviente</option>
                                    <option value="Separado(a)">Separado(a)</option>
                                    <option value="Viudo(a)">Viudo(a)</option>
                                    <option value="Soltero(a)">Soltera(a)</option>

                                </select>

                            </div>

                            <!-- Género -->
                            <div class="col-md-3 my-2">

                                <label>Género</label>

                                <select
                                    class="form-select"
                                    id="genero_c"
                                    name="genero_c">

                                    <option value="">Seleccione</option>
                                    <option value="MASCULINO">MASCULINO</option>
                                    <option value="FEMENINO">FEMENINO</option>

                                </select>

                            </div>

                            <!-- Ubigeo -->
                            <div class="col-md-3 my-2" id="div_parentUbigeo_c">

                                <label>
                                    Ubigeo
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    id="ubigeo_c"
                                    name="ubigeo_c"
                                    required>
                                    <?php if ($this->ubigeo["success"]) : ?>
                                        <option value="">Seleccione</option>
                                        <?php for ($i = 0; $i < count($this->ubigeo["message"]); $i++) : ?>
                                            <?php $text = $this->ubigeo["message"][$i]["cod_ubigeo"] . " | " . $this->ubigeo["message"][$i]["depa"] . " | " . $this->ubigeo["message"][$i]["provi"] . " | " . $this->ubigeo["message"][$i]["distri"] ?>
                                            <option value="<?php echo $this->ubigeo["message"][$i]["cod_ubigeo"] ?>"><?php echo $text ?></option>
                                        <?php endfor ?>
                                    <?php endif ?>
                                </select>

                            </div>

                            <!-- Dirección -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Dirección
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="direccion_c"
                                    name="direccion_c"
                                    autocomplete="off"
                                    value="S/D"
                                    required>

                            </div>

                            <!-- Celular -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Celular
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="celular_c"
                                    name="celular_c"
                                    minlength="9"
                                    maxlength="9"
                                    autocomplete="off">

                            </div>

                            <!-- Email -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Correo electrónico
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email_c"
                                    name="email_c"
                                    autocomplete="off"
                                    required>

                            </div>

                            <!-- Estado SUNAT -->
                            <div class="col-md-3 my-2 d-none">

                                <label>Estado SUNAT</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="estado_sunat_c"
                                    name="estado_sunat_c"
                                    autocomplete="off"
                                    readonly>

                            </div>

                            <!-- Condición SUNAT -->
                            <div class="col-md-3 my-2 d-none">

                                <label>Condición SUNAT</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="condicion_sunat_c"
                                    name="condicion_sunat_c"
                                    autocomplete="off"
                                    readonly>

                            </div>

                            <!-- Terminal -->
                            <div class="col-md-3 my-2" id="div_parentTerminal_c">

                                <label>
                                    Terminal
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    id="terminal_c"
                                    name="terminal_c"
                                    required>
                                    <?php if ($this->terminal["success"]) : ?>
                                        <option value="">Seleccione</option>
                                        <?php for ($i = 0; $i < count($this->terminal["message"]); $i++) : ?>
                                            <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                                            <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
                                        <?php endfor ?>
                                    <?php endif ?>
                                </select>

                            </div>

                            <!-- Tipo usuario -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Tipo de usuario
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    name="tp_usuario_c"
                                    id="tp_usuario_c"
                                    required>

                                    <option value="4">CONDUCTOR</option>
                                    <option value="10">COPILOTO</option>

                                </select>

                            </div>

                            <!-- Estado -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Estado
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    name="estado_c"
                                    id="estado_c"
                                    required>

                                    <option value="1">Habilitado</option>
                                    <option value="0">Deshabilitado</option>

                                </select>

                            </div>

                            <!-- Licencia -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Licencia
                                    <span class="requiredField">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="licencia_c"
                                    name="licencia_c"
                                    autocomplete="off">

                            </div>

                            <!-- Categoría -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Categoría
                                    <span class="requiredField">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    name="ctg_licencia_c"
                                    id="ctg_licencia_c"
                                    required>

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

                        <button
                            type="button"
                            class="btn btnCancel"
                            id="button_cancel_c"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn btnSave"
                            id="button_save_c">
                            Guardar
                        </button>

                        <button
                            class="btn btnSave d-none"
                            type="button"
                            id="button_loadSave_c"
                            disabled>

                            <span
                                class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true">
                            </span>

                            Guardando...

                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>