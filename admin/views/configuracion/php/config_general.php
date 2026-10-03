<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">

        <div class="col-md-12 body-page">
            <div class="card shadow-sm border-0 rounded">
                <div class="card-body p-4">

                    <form class="row g-3" method="post" id="form">

                        <div class="col-12">
                            <h6 class="mb-0">Configuración general</h6>
                            <hr>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label>IGV <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0 px-3">%</span>
                                <input type="text" class="form-control" name="igv" id="igv">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label>% ventas Pasaje <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0 px-3">%</span>
                                <input type="text" class="form-control" name="porcent_venta" id="porcent_venta">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label>% ventas Encomienda <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0 px-3">%</span>
                                <input type="text" class="form-control" name="porcent_venta_e" id="porcent_venta_e">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label>% empresa Programación <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0 px-3">%</span>
                                <input type="text" class="form-control" name="porcentaje_empresa"
                                    id="porcentaje_empresa" placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label>% empresa Caja chica <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0 px-3">%</span>
                                <input type="text" class="form-control" name="porcentaje_empresa_caja"
                                    id="porcentaje_empresa_caja" placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="mb-0">Comisiones</h6>
                            <hr>
                        </div>

                        <div class="col-md-4 col-lg-3">
                            <label>Comisión Nivel 1 <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <select class="form-select" name="tp_comision_nivel1" id="tp_comision_nivel1"
                                    style="max-width: 85px;">
                                    <option value="PORCENTAJE">%</option>
                                    <option value="MONTO">S/</option>
                                </select>
                                <input type="text" class="form-control" name="comision_nivel1" id="comision_nivel1"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-3">
                            <label>Comisión Nivel 2 <span class="requiredField">*</span></label>
                            <div class="input-group">
                                <select class="form-select" name="tp_comision_nivel2" id="tp_comision_nivel2"
                                    style="max-width: 85px;">
                                    <option value="PORCENTAJE">%</option>
                                    <option value="MONTO">S/</option>
                                </select>
                                <input type="text" class="form-control" name="comision_nivel2" id="comision_nivel2"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="mb-0">Opciones adicionales</h6>
                            <hr>
                        </div>

                        <div class="col-md-4 col-lg-3 text-center">
                            <label>
                                S-C Guía | Comprobante
                                <i class="uiverse fa-solid fa-circle-info">
                                    <span class="tooltip">
                                        Cuando la opción esté activada se mostrará la serie y correlativo de la
                                        nota/guía pagada en el comprobante electrónico.
                                    </span>
                                </i>
                            </label>

                            <div class="form-group mt-2">
                                <div class="checkbox-container">
                                    <input type="hidden" name="numero_GN" id="numero_GN" value="0">
                                    <input type="checkbox" id="numero_GN_check" name="numero_GN_check" value="1">
                                    <label for="numero_GN_check"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-3 text-center">
                            <label>
                                Aplicar % empresa caja chica
                                <i class="uiverse fa-solid fa-circle-info">
                                    <span class="tooltip">
                                        Cuando esté activado, se aplicará el porcentaje de empresa en caja chica.
                                    </span>
                                </i>
                            </label>

                            <div class="form-group mt-2">
                                <div class="checkbox-container">
                                    <input type="hidden" name="p_empresaxcaja" id="p_empresaxcaja" value="0">
                                    <input type="checkbox" id="p_empresaxcaja_check" name="p_empresaxcaja_check"
                                        value="1">
                                    <label for="p_empresaxcaja_check"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end mt-4">
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
</div>