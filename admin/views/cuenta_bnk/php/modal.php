<div class="modal fade" id="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="form_register" class="needs-validation" autocomplete="off" novalidate>
                <div class="modal-header bg-primary ">
                    <h5 class="modal-title titleModal text-white">
                        NUEVA CUENTA BANCARIA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cuenta" id="id_cuenta">
                    <div class="row g-3">
                        <div class="col-md-4" id="div_parentBanco">
                            <label class="form-label">
                                Banco
                            </label>
                            <select class="form-select" id="id_banco" name="id_banco">
                                <?php if ($this->bancos['success']): ?>
                                    <?php foreach ($this->bancos['message'] as $banco): ?>
                                        <option value="<?= $banco['id_banco'] ?>"><?= $banco["nombre"] ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">
                                Descripción
                            </label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion">
                        </div>

                        <div class="col-md-4" id="div_parentTipoCuenta">
                            <label class="form-label">
                                Tipo de cuenta
                            </label>
                            <select class="form-select" id="tipo_cuenta" name="tipo_cuenta">
                                <option value="AHORROS">Ahorros</option>
                                <option value="CORRIENTE">Corriente</option>
                                <option value="CTS">CTS</option>
                                <option value="RECAUDADORA">Recaudadora</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="div_parentMoneda">
                            <label class="form-label">
                                Moneda
                            </label>
                            <select class="form-select" id="moneda" name="moneda">
                                <option value="SOLES">Soles</option>
                                <option value="DOLARES">Dólares</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="div_parentEstado">
                            <label class="form-label">
                                Estado
                            </label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">
                                Número de cuenta
                            </label>
                            <input type="text" class="form-control" id="numero_cuenta" name="numero_cuenta">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                CCI
                            </label>
                            <input type="text" class="form-control" id="cci" name="cci">
                        </div>
                        <div class="col-md-4 d-none">
                            <label class="form-label">
                                Saldo inicial
                            </label>
                            <input type="number" step="0.01" class="form-control" id="saldo_inicial"
                                name="saldo_inicial" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Orden
                            </label>
                            <input type="number" class="form-control" id="orden" name="orden" value="1">
                        </div>
                        <div class="col-md-8 mt-2">
                            <label class="form-label">
                            </label>
                            <div class="form-check form-switch">

                                <input class="form-check-input" type="checkbox" id="mostrar_reportes">

                                <label class="form-check-label ms-2" for="mostrar_reportes">
                                    Mostrar cuenta en comprobantes y cotizaciones
                                </label>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary text-white" id="btnGuardar">
                        <i class="fa fa-save"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>