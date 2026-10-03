<!-- Modal add Register-->
<div class="modal fade" id="modal_vehiculo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">NUEVO VEHICULO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_vehiculo" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_vehiculo" name="id_vehiculo">
                        <div class="col-md-4">
                            <label for="">Tarjeta propiedad <strong>(PDF)</strong><span class="text-warning fw-bolder ms-3">(.pdf)</span></label><br>
                            <div class="d-flex flex-column justify-content-center align-items-center">
                                <label for="tarjeta_propiedad" width="80" height="80" class="img-fluid img_filepdf my-2 position-relative" id="show_tarjeta_propiedad">
                                    <a href="javascript:void(0)" title="Descargar archivo" class="btn position-absolute top-100 start-100 translate-middle linkopen_upload d-none" id="link_tarjeta_propiedad"><i class="bi bi-box-arrow-up-right"></i></a>
                                </label>
                                <input type="file" class="form-control d-none" name="tarjeta_propiedad" id="tarjeta_propiedad">
                                <input type="hidden" class="form-control" name="tarjeta_propiedad_before" id="tarjeta_propiedad_before">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 my-2">
                                    <label>Descripción<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" autocomplete="off" required>
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Placa<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="placa" name="placa" autocomplete="off" required>
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" autocomplete="off">
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" autocomplete="off">
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>SOAT<span class="requiredField">*</span></label>
                                    <input type="text" class="form-control" id="soat" name="soat" autocomplete="off" required>
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Serie Motor</label>
                                    <input type="text" class="form-control" id="serie_motor" name="serie_motor" autocomplete="off">
                                </div>
                                <div class="col-md-3 my-2">
                                    <label>Nro. Ejes</label>
                                    <input type="text" class="form-control" id="num_ejes" name="num_ejes" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Nro. Pisos<span class="requiredField">*</span></label>
                            <select class="form-select" name="num_piso" id="num_piso" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Nro. Asientos<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="num_asiento" name="num_asiento" autocomplete="off" required>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>TUC</label>
                            <input type="text" class="form-control" id="tuc" name="tuc" autocomplete="off">
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Nro. POLIZA</label>
                            <input type="text" class="form-control" id="num_poliza" name="num_poliza" autocomplete="off">
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Estado<span class="requiredField">*</span></label>
                            <select class="form-select" name="estado" id="estado" required>
                                <option value="1">Habilitado</option>
                                <option value="0">Deshabilitado</option>
                            </select>
                        </div>
                        <div class="col-md-2 my-2">
                            <label>Nº Registro MTC<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="n_mtc" name="n_mtc" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_v" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_v">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_v" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>