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


<!-- Modal estacionamiento-->
<div class="modal fade" id="modal_configuracion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">CONFIGURACIÓN DE VEHÍCULO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_vehiculo" class="needs-validation" novalidate>
                    <input type="hidden" name="id_vehiculo_selected" id="id_vehiculo_selected">
                    <div class="row">
                        <div class="col-md-5" id="herramientas">
                            <div class="row">
                                <div class="col-md-12 my-4 d-flex flex-wrap flex-lg-nowrap justify-content-center">
                                    <button type="button" class="btn btnColorGray mx-1 obj d-flex flex-column my-2 justify-content-center align-items-center">
                                        <i class="fa-light fa-restroom-simple text-white fs-4 mb-2"></i>
                                        Baño
                                    </button>
                                    <button type="button" class="btn btnColorGray mx-1 obj d-flex flex-column my-2 justify-content-center align-items-center">
                                        <i class="fa-light fa-stairs text-white fs-4 mb-2"></i>
                                        Escalera
                                    </button>
                                    <button type="button" class="btn btnColorGray mx-1 obj d-flex flex-column my-2 justify-content-center align-items-center">
                                        <i class="bi bi-usb text-white fs-4 mb-2"></i>
                                        Televisión
                                    </button>
                                    <button type="button" class="btn btnColorGray mx-1 obj d-flex flex-column my-2 justify-content-center align-items-center">
                                        <i class="fa-light fa-refrigerator text-white fs-4 mb-2"></i>
                                        Refrigeradora
                                    </button>
                                    <button type="button" class="btn btnColorGray mx-1 obj d-flex flex-column my-2 justify-content-center align-items-center">
                                        <i class="fa-light fa-person-dress text-white fs-4 mb-2"></i>
                                        Terramoza
                                    </button>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center mb-3">
                                    <label>Piso</label>
                                    <select class="form-select py-2" name="piso_config" id="piso_config"></select>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                                    <label>Asiento</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="asiento_config" id="asiento_config">
                                        <button type="button" class="btn btnColorGray" id="add_element"><i class="bi bi-plus-lg text-white"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center mb-3">
                                    <label>Tipo de asiento</label>
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" name="tp_asiento" id="normal" value="normal" autocomplete="off" checked>
                                        <label class="btn btn-outline-secondary" for="normal">Normal</label>

                                        <input type="radio" class="btn-check" name="tp_asiento" id="premium" value="premium" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="premium">Premium</label>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                                    <label>Tamaño vehículo</label>
                                    <div class="input-group w-100 mb-3 d-flex justify-content-center aling-items-center">
                                        <button type="button" class="btn btnColorGray me-2" id="disminuir"><i class="fa-light fa-minus"></i></button>
                                        <button type="button" class="btn btnColorGray" id="aumentar"><i class="fa-sharp fa-light fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-auto d-flex flex-column justify-content-center align-items-center">
                                    <label>Grillas</label>
                                    <div class="input-group w-100 mb-3 d-flex justify-content-center align-items-center">
                                        <button type="button" class="btn btnColorGray me-2" id="btn_grid_horizontal">Horizontal</button>
                                        <button type="button" class="btn btnColorGray" id="btn_grid_vertical">Vertical</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 position-relative d-flex flex-column justify-content-center align-items-center">
                            <img class="p-0 img_vehiculo" src="<?php echo URL_IMAGEN_ADMIN ?>vehiculo/vehiculo_delantero.jpg" width="300px" height="150px" alt="...">
                            <div class="position-relative" id="parent_vehiculo" style="height: 500px"></div>
                            <img class="p-0 img_vehiculo" src="<?php echo URL_IMAGEN_ADMIN ?>vehiculo/vehiculo_trasera.jpg" width="300px" height="40px" alt="...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_config" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_config">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSaveConfig" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>