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
                        <input type="hidden" id="id_cupon" name="id_cupon">

                        <div class="col-md-4">
                            <label for="codigo">Codigo <span class="requiredField">*</span></label>
                            <input class="form-control" type="text" name="codigo" id="codigo">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Tipo <span class="requiredField">*</span></label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="MONTO">MONTO</option>
                                <option value="PORCENTAJE">PORCENTAJE</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Valor <span class="requiredField">*</span></label>
                            <input class="form-control" type="number" name="valor" id="valor">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Tope Maximo <span class="requiredField">*</span></label>
                            <input class="form-control" type="number" name="tope_maximo" id="tope_maximo">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Monto Minimo <span class="requiredField">*</span></label>
                            <input class="form-control" type="number" name="monto_minimo" id="monto_minimo">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Fecha Inicio <span class="requiredField">*</span></label>
                            <input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Fecha Fin <span class="requiredField">*</span></label>
                            <input class="form-control" type="date" name="fecha_fin" id="fecha_fin">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Uso Máximo <span class="requiredField">*</span></label>
                            <input class="form-control" type="number" name="uso_maximo" id="uso_maximo">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">Estado <span class="requiredField">*</span></label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                                <option value="VENCIDO">VENCIDO</option>
                            </select>
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