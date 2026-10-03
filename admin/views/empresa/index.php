<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->

        <!-- Empresa -->
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <form id="form" class="needs-validation" novalidate>
                    <div class="row py-1">
                        <div class="col-md-2 my-1">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="">Logo <span class="text-warning fw-bolder ms-3">(.png, .jpg,
                                            .jpeg)</span></label><br>
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <label for="logo" width="80" height="80"
                                            class="img-fluid img_logo my-1 position-relative" id="show_logo">
                                            <a href="javascript:void(0)" target="_blank" title="Abrir imagen"
                                                class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none"
                                                id="link_logo"><i class="bi bi-box-arrow-in-up-right"></i></a>
                                        </label>
                                        <input type="file" class="form-control d-none" name="logo" id="logo">
                                        <input type="hidden" class="form-control" name="logo_before" id="logo_before">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="">Certificado Digital Tributario <strong>(CDT)</strong><span
                                            class="text-warning fw-bolder ms-3">(.pfx)</span></label><br>
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <label for="cdt" width="80" height="80"
                                            class="img-fluid img_filepxf my-1 position-relative" id="show_cdt">
                                            <a href="javascript:void(0)" title="Descargar archivo"
                                                class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none"
                                                id="link_cdt"><i class="bi bi-download"></i></a>
                                        </label>
                                        <input type="file" class="form-control d-none" name="cdt" id="cdt">
                                        <input type="hidden" class="form-control" name="cdt_before" id="cdt_before">
                                    </div>
                                    <div class="my-1">
                                        <label for="">Clave <strong>(CDT)</strong></label><br>
                                        <input class="form-control" type="text" id="cdt_clave" name="cdt_clave"
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-3 my-1">
                                    <label>N° RUC<span class="requiredField">*</span></label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="num_docu" name="num_docu"
                                            autocomplete="off" required minlength="11" maxlength="11"
                                            placeholder="Nro. RUC">
                                        <button type="button" class="btn btnSearch" id="button_search">BUSCAR</button>
                                        <button class="btn btnSearch d-none" type="button" id="button_loadSearch"
                                            disabled>
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                            BUSCAR
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-9 my-1">
                                    <label>Razon social<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" name="razon_social" id="razon_social"
                                        readonly autocomplete="off" required>
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Condición</label>
                                    <input type="text" class="form-control" name="condicion" id="condicion" readonly
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Estado</label>
                                    <input type="text" class="form-control" name="estado" id="estado" readonly
                                        autocomplete="off">
                                </div>
                                <div class="col-md-5 my-1">
                                    <label>Dirección<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" name="direccion" id="direccion"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-3 my-1" id="div_parentUbigeo">
                                    <label>Ubigeo<span class="requiredField">*</span></label>
                                    <select class="" id="ubigeo" name="ubigeo" required>
                                    </select>
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Usuario SOL</label>
                                    <input type="text" class="form-control" name="usuario_sol" id="usuario_sol"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Contraseña SOL</label>
                                    <input type="text" class="form-control" name="pass_sol" id="pass_sol"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>CPE ID</label>
                                    <input type="text" class="form-control" name="cpe_id" id="cpe_id"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>CPE Clave</label>
                                    <input type="text" class="form-control" name="cpe_clave" id="cpe_clave"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>GUIA ID</label>
                                    <input type="text" class="form-control" name="guia_id" id="guia_id"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>GUIA Clave</label>
                                    <input type="text" class="form-control" name="guia_clave" id="guia_clave"
                                        autocomplete="off">
                                </div>


                                <div class="col-md-4 my-1">
                                    <label>Modo Sistema<span class="requiredField">*</span></label>
                                    <select class="form-select" name="modo" id="modo" required>
                                        <option value="1">Producción</option>
                                        <option value="0">Demostración</option>
                                    </select>
                                </div>

                                <div class="col-md-2 my-1">
                                    <label>Envio OSE</label> <i class="uiverse fa-solid fa-circle-info"><span
                                            class="tooltip">El sistema esta conectado directamente a la WS de la
                                            SUNAT</span></i>
                                    <input type="hidden" name="envio_ose" value="0">
                                    <label class="container">
                                        <input type="checkbox" value="" id="envio_ose" name="ose">
                                        <div class="checkmark"></div>
                                    </label>
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Guia T.</label> <i class="uiverse fa-solid fa-circle-info"><span
                                            class="tooltip">El sistema creara y enviara automaticamente las guias por
                                            embarque de encomiendas</span></i>
                                    <input type="hidden" name="envio_guia" value="0">
                                    <label class="container">
                                        <input type="checkbox" value="1" id="envio_guia" name="guia">
                                        <div class="checkmark"></div>
                                    </label>
                                </div>
                                <div class="col-md-4 my-1 d-none" id="div_link_ose">
                                    <label>Link OSE</label>
                                    <input type="text" class="form-control" name="link_ose" id="link_ose"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-4 my-1">
                                    <label>N. cuenta DETRACCIONES<span class="optionalField">*</span></label>
                                    <input type="text" class="form-control" name="nro_cuenta" id="nro_cuenta"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-3 my-1">
                                    <label for="correo">Correo electrónico</label><br>
                                    <input class="form-control" type="email" id="correo" name="correo"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1">
                                    <label>Teléfono Empresa</label>
                                    <input type="text" class="form-control" name="telefono_empresa"
                                        id="telefono_empresa" autocomplete="off">
                                </div>
                                <div class="col-md-2 my-1" style="text-align:center;">
                                    <label>RUC encomienda</label>
                                    <i class="uiverse fa-solid fa-circle-info">
                                        <span class="tooltip">
                                            Si está activado podrás registrar un RUC diferente para las encomiendas
                                        </span>
                                    </i>
                                    <div class="form-group">
                                        <div class="checkbox-container">
                                            <input type="hidden" name="ruc_encomienda_separado"
                                                id="ruc_encomienda_separado" value="0">
                                            <input type="checkbox" id="ruc_encomienda_separado_check"
                                                name="ruc_encomienda_separado_check" value="1">
                                            <label for="ruc_encomienda_separado_check"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 my-1">
                                    <label>Datos Bancarios / N. cuenta Bancaria</label>
                                    <div id="nro_cuenta_bancaria"></div>
                                    <input type="hidden" name="nro_cuenta_bancaria" id="nro_cuenta_bancaria_hidden">
                                </div>
                            </div>
                        </div>

                        <div class="row d-none" id="div_ruc_encomienda">
                            <div class="col-md-12">
                                <h4>Encomienda</h4>
                            </div>
                            <div class="col-md-4 my-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Logo <span class="text-warning fw-bolder ms-3">(.png, .jpg,
                                                .jpeg)</span></label><br>
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <label for="logo_encomienda" width="80" height="80"
                                                class="img-fluid img_logo my-1 position-relative"
                                                id="show_logo_encomienda">
                                                <a href="javascript:void(0)" target="_blank" title="Abrir imagen"
                                                    class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none"
                                                    id="link_logo_encomienda"><i
                                                        class="bi bi-box-arrow-in-up-right"></i></a>
                                            </label>
                                            <input type="file" class="form-control d-none" name="logo_encomienda"
                                                id="logo_encomienda">
                                            <input type="hidden" class="form-control" name="logo_before_encomienda"
                                                id="logo_before_encomienda">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="">Certificado Digital Tributario <strong>(CDT)</strong><span
                                                class="text-warning fw-bolder ms-3">(.pfx)</span></label><br>
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <label for="cdt_encomienda" width="80" height="80"
                                                class="img-fluid img_filepxf my-1 position-relative"
                                                id="show_cdt_encomienda">
                                                <a href="javascript:void(0)" title="Descargar archivo"
                                                    class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none"
                                                    id="link_cdt_encomienda"><i class="bi bi-download"></i></a>
                                            </label>
                                            <input type="file" class="form-control d-none" name="cdt_encomienda"
                                                id="cdt_encomienda">
                                            <input type="hidden" class="form-control" name="cdt_before_encomienda"
                                                id="cdt_before_encomienda">
                                        </div>
                                        <div class="my-3">
                                            <label for="">Clave <strong>(CDT)</strong></label><br>
                                            <input class="form-control" type="text" id="cdt_clave_encomienda"
                                                name="cdt_clave_encomienda" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-4 my-1">
                                        <label>N° RUC<span class="requiredField">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="num_docu_encomienda"
                                                name="num_docu_encomienda" autocomplete="off" required minlength="11"
                                                maxlength="11" placeholder="Nro. RUC">
                                            <button type="button" class="btn btnSearch"
                                                id="button_searchE">BUSCAR</button>
                                            <button class="btn btnSearch d-none" type="button" id="button_loadSearchE"
                                                disabled>
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                                BUSCAR
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Razon social<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" name="razon_social_encomienda"
                                            id="razon_social_encomienda" readonly autocomplete="off" required>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Condición</label>
                                        <input type="text" class="form-control" name="condicion_encomienda"
                                            id="condicion_encomienda" readonly autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Estado</label>
                                        <input type="text" class="form-control" name="estado_encomienda"
                                            id="estado_encomienda" readonly autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Dirección<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" name="direccion_encomienda"
                                            id="direccion_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1" id="">
                                        <label>Ubigeo<span class="requiredField">*</span></label>
                                        <select class="" id="ubigeo_encomienda" name="ubigeo_encomienda" required>
                                        </select>
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Usuario SOL</label>
                                        <input type="text" class="form-control" name="usuario_sol_encomienda"
                                            id="usuario_sol_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>Contraseña SOL</label>
                                        <input type="text" class="form-control" name="pass_sol_encomienda"
                                            id="pass_sol_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>CPE ID</label>
                                        <input type="text" class="form-control" name="cpe_id_encomienda"
                                            id="cpe_id_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>CPE Clave</label>
                                        <input type="text" class="form-control" name="cpe_clave_encomienda"
                                            id="cpe_clave_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>GUIA ID</label>
                                        <input type="text" class="form-control" name="guia_id_encomienda"
                                            id="guia_id_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>GUIA Clave</label>
                                        <input type="text" class="form-control" name="guia_clave_encomienda"
                                            id="guia_clave_encomienda" autocomplete="off">
                                    </div>
                                    <div class="col-md-4 my-1">
                                        <label>N. cuenta DETRACCIONES<span class="optionalField">*</span></label>
                                        <input type="text" class="form-control" name="nro_cuenta_encomienda"
                                            id="nro_cuenta_encomienda" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h5>Comprobantes</h5>
                        <div class="row col-md-12">
                            <div class="col-md-2">
                                <label>Mostrar Terminales?</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">El sistema mostrara las terminales en los comprobantes de
                                        pago</span></i>
                                <input type="hidden" name="m_terminales" value="0">
                                <label class="container">
                                    <input type="checkbox" value="1" id="m_terminales" name="guia">
                                    <div class="checkmark"></div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label>Texto a mostrar en lo comprobantes</label>
                                <div id="fr_comprobante">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>Mostrar Terminales?</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">El sistema mostrara las terminales en el manifiesto de
                                        pasajeros, formatos de embarque y desembarque</span></i>
                                <input type="hidden" name="m_terminalesManifi" value="0">
                                <label class="container">
                                    <input type="checkbox" value="1" id="m_terminalesManifi" name="m_terminalesMa">
                                    <div class="checkmark"></div>
                                </label>
                            </div>
                            <hr>
                            <h5>Ventas</h5>
                            <div class="row col-md-12">
                                <div class="col-md-2" style="text-align:center;">
                                    <label>Porcentaje por venta</label> <i class="uiverse fa-solid fa-circle-info"><span
                                            class="tooltip">Se guardará el porcentaje de cada una de las ventas de
                                            ENCOMIENDA y PASAJE</span></i>
                                    <div class="form-group">
                                        <div class="checkbox-container">
                                            <input type="hidden" name="porcent_venta" id="porcent_venta" value="0">
                                            <input type="checkbox" id="porcent_venta_check" name="porcent_venta_check"
                                                value="1">
                                            <label for="porcent_venta_check"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <a href="<?= URL ?>cuenta_bnk" class="btnCuentaBancaria">
                                        <i class="fa-solid fa-building-columns"></i>
                                        Administrar cuentas bancarias
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
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