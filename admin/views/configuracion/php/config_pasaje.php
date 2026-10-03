<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <form id="form_config_pasaje" class="needs-validation" novalidate>
                    <div class="row">

                        <div class="col-md-2" style="text-align:center;">
                            <label>Venta WEB</label> <i class="uiverse fa-solid fa-circle-info"><span
                                    class="tooltip">Venta WEB de pasajes</span></i>
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="hidden" name="venta_web" id="venta_web" value="0">
                                    <input type="checkbox" id="venta_web_check" name="venta_web_check" value="1">
                                    <label for="venta_web_check"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="text-align:center;">
                            <label>L. por Terminales?</label> <i class="uiverse fa-solid fa-circle-info"><span
                                    class="tooltip">El sistema liquidará por terminales</span></i>
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="hidden" name="l_terminales" id="l_terminales" value="0">
                                    <input type="checkbox" id="l_terminales_check" name="l_terminales_check" value="1">
                                    <label for="l_terminales_check"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="text-align:center;">
                            <label>L. por usuario</label> <i class="uiverse fa-solid fa-circle-info"><span
                                    class="tooltip">La liquidación de las programaciones <br> se realizará por
                                    usuario</span></i>
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="hidden" name="l_usuarios" id="l_usuarios" value="0">
                                    <input type="checkbox" id="l_usuarios_check" name="l_usuarios_check" value="1">
                                    <label for="l_usuarios_check"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="text-align:center;">
                            <label>Manifiesto SUNAT</label> <i class="uiverse fa-solid fa-circle-info"><span
                                    class="tooltip">Si la opcion esta activa se mostrara el boton de manifiesto
                                    SUNAT(Solo comprobantes)</span></i>
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="hidden" name="manifiesto_sunat" id="manifiesto_sunat" value="0">
                                    <input type="checkbox" id="manifiesto_sunat_check" name="manifiesto_sunat_check"
                                        value="1">
                                    <label for="manifiesto_sunat_check"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="">Tiempo de espera seleccion asiento</label>
                            <input type="number" id="tiempo_seleccion" name="tiempo_seleccion" min="0" class="form-control">
                        </div>
                        <div class="col-md-6 my-1">
                            <label>Términos condiciones <b>Pasaje</b></label>
                            <div id="termscond_pasaje">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btnSave" id="button_save">
                                <i class="bi bi-floppy me-1"></i> Guardar todo
                            </button>
                            <button class="btn btnSave d-none" type="button" id="button_loadSave" disabled>
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Guardando...
                            </button>
                        </div>
                        <div class="col-md-12 manifiesto-wrap">
                            <h6><i class="bi bi-file-earmark-text"></i> Autorizaciones de Manifiesto SUNAT</h6>

                            <div class="manifiesto-toolbar">
                                <button type="button" class="btn-add-fila" id="btn_add_manifiesto">
                                    <i class="bi bi-plus-lg"></i> Agregar autorización
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="tabla-manifiesto" id="tabla_manifiesto">
                                    <thead>
                                        <tr>
                                            <th style="width:20%">Serie</th>
                                            <th style="width:20%">Correlativo</th>
                                            <th style="width:25%">N° Autorización SUNAT</th>
                                            <th style="width:15%">Estado</th>
                                            <th style="width:20%; text-align:center;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_manifiesto_body">
                                    </tbody>
                                </table>
                                <div class="manifiesto-empty d-none" id="manifiesto_empty">
                                    No hay autorizaciones registradas. Haz clic en "Agregar autorización" para crear la
                                    primera.
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>