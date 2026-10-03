<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <input type="hidden" name="id_egreso" id="id_egreso">
                    <div class="table-responsive" style="max-height: 250px;">
                        <label for="">Ingrese sus egresos</label>
                        <table class="table todo_list w-100" cellspacing="0" id="table_egreso">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th><button type="button" class="btn p-0" id="btn_add_egreso"><i class="bi bi-plus-square text-info fs-5"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-secondary fw-bolder" style="background-color: #f8f8f8!important;">
                                    <td class="fs-6">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td id="monto_total" class="fs-6">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
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