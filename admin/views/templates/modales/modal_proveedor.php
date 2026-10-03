<!-- Modal add Register-->
<div class="modal fade"
    id="modal_proveedor"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby="staticBackdropLabel_p"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel_p">NUEVO PROVEEDOR</h5>

                <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <div class="modal-body h-100">

                <form id="form_proveedor_p" class="needs-validation" novalidate>

                    <div class="row g-2 my-2">

                        <div class="col-md-12 row">

                            <input type="hidden"
                                id="id_proveedor_p"
                                name="id_proveedor_p">

                            <!-- TIPO DOCUMENTO -->
                            <div class="col-md-4">

                                <label>
                                    Tipo de documento
                                    <span class="requiredField">*</span>
                                </label>

                                <select class="form-select"
                                    name="tp_docu_p"
                                    id="tp_docu_p">

                                    <option value="1">DNI</option>
                                    <option value="4">Carnet extranjeria</option>
                                    <option value="6">RUC</option>
                                    <option value="7">Pasaporte</option>
                                    <option value="0">Otros documentos</option>

                                </select>

                            </div>

                            <!-- NÚMERO DOCUMENTO -->
                            <div class="col-md-4">

                                <label>
                                    N° Documento
                                    <span class="requiredField">*</span>
                                </label>

                                <div class="input-group mb-3">

                                    <input type="text"
                                        class="form-control"
                                        id="num_docu_p"
                                        name="num_docu_p"
                                        autocomplete="off"
                                        required
                                        minlength="8"
                                        maxlength="8">

                                    <button type="button"
                                        class="btn btnSearch"
                                        id="button_search_p">
                                        BUSCAR
                                    </button>

                                    <button class="btn btnSearch d-none"
                                        type="button"
                                        id="button_loadSearch_p"
                                        disabled>

                                        <span class="spinner-border spinner-border-sm"
                                            role="status"
                                            aria-hidden="true">
                                        </span>

                                        BUSCAR

                                    </button>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-12 row">

                            <!-- NOMBRES -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Nombres
                                    <span class="requiredField">*</span>
                                </label>

                                <input type="text"
                                    class="form-control"
                                    id="nombres_p"
                                    name="nombres_p"
                                    readonly
                                    autocomplete="off"
                                    required>

                            </div>


                            <!-- APELLIDOS -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Apellidos
                                    <span class="requiredField">*</span>
                                </label>

                                <input type="text"
                                    class="form-control"
                                    id="apellidos_p"
                                    name="apellidos_p"
                                    readonly
                                    autocomplete="off"
                                    required>

                            </div>


                            <!-- UBIGEO -->
                            <?php
                            $col = "col-md-3";

                            /*
                             * IMPORTANTE:
                             * El componente debe generar:
                             *
                             * id="ubigeo_p"
                             * name="ubigeo_p"
                             *
                             * y su contenedor:
                             *
                             * id="div_parentUbigeo_p"
                             */
                            $id_ubigeo = "ubigeo_p";

                            include("views/templates/components/cmp_select_ubigeo.php");
                            ?>


                            <!-- DIRECCIÓN -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Dirección
                                    <span class="requiredField">*</span>
                                </label>

                                <input type="text"
                                    class="form-control"
                                    id="direccion_p"
                                    name="direccion_p"
                                    autocomplete="off"
                                    value="S/D"
                                    required>

                            </div>


                            <!-- CELULAR -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Celular
                                    <span class="requiredField">*</span>
                                </label>

                                <input type="text"
                                    class="form-control"
                                    id="celular_p"
                                    name="celular_p"
                                    minlength="9"
                                    maxlength="9"
                                    autocomplete="off">

                            </div>


                            <!-- EMAIL -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Correo electrónico
                                    <span class="requiredField">*</span>
                                </label>

                                <input type="email"
                                    class="form-control"
                                    id="email_p"
                                    name="email_p"
                                    autocomplete="off"
                                    required>

                            </div>


                            <!-- ESTADO SUNAT -->
                            <div class="col-md-3 my-2 d-none">

                                <label>Estado SUNAT</label>

                                <input type="text"
                                    class="form-control"
                                    id="estado_sunat_p"
                                    name="estado_sunat_p"
                                    autocomplete="off"
                                    readonly>

                            </div>


                            <!-- CONDICIÓN SUNAT -->
                            <div class="col-md-3 my-2 d-none">

                                <label>Condición SUNAT</label>

                                <input type="text"
                                    class="form-control"
                                    id="condicion_sunat_p"
                                    name="condicion_sunat_p"
                                    autocomplete="off"
                                    readonly>

                            </div>


                            <!-- TERMINAL -->
                            <?php
                            $col = "col-md-3";

                            /*
                             * IMPORTANTE:
                             * El componente debe generar:
                             *
                             * id="terminal_p"
                             * name="terminal_p"
                             *
                             * y su contenedor:
                             *
                             * id="div_parentTerminal_p"
                             */
                            $id_terminal = "terminal_p";

                            include("views/templates/components/cmp_select_terminal.php");
                            ?>


                            <!-- ESTADO -->
                            <div class="col-md-3 my-2">

                                <label>
                                    Estado
                                    <span class="requiredField">*</span>
                                </label>

                                <select class="form-select"
                                    name="estado_p"
                                    id="estado_p"
                                    required>

                                    <option value="1">Habilitado</option>
                                    <option value="0">Deshabilitado</option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- FOOTER -->
                    <div class="modal-footer">

                        <button type="button"
                            class="btn btnCancel"
                            id="button_cancel_p"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="btn btnSave"
                            id="button_save_p">
                            Guardar
                        </button>

                        <button class="btn btnSave d-none"
                            type="button"
                            id="button_loadSave_p"
                            disabled>

                            <span class="spinner-border spinner-border-sm"
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