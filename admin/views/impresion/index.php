<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'IMPRESIONES';
        $title_modal = null;
        $show_btnadd = false;
        include("views/templates/components/cmp_headerpage.php")
        ?>

        <!-- Emmpresa -->
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <form class="row" method="post" id="form">
                    <div class="col-md-12 row">
                        <div class="col-md-2">
                            <label>Impresión Directa</label>
                            <i class="uiverse fa-solid fa-circle-info">
                                <span class="tooltip">El sistema imprimirá directamente los comprobantes sin una vista previa</span>
                            </i>
                            <input type="hidden" id="impr" name="impresion_d" value="0">
                            <label class="container">
                                <input type="checkbox" id="impresion_d" name="impresion">
                                <div class="checkmark"></div>
                            </label>
                        </div>

                        <!-- Sección de impresoras -->
                        <div id="div_impresora" class="col-md-10 row d-none">

                            <!-- Selección tipo de impresión -->
                            <div class="col-md-3">
                                <label>Tipo de Impresión</label>
                                <select id="tipo_impresion" name="tipo_impresion" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="escritorio">PC / Red</option>
                                    <option value="bluetooth">Bluetooth</option>
                                </select>
                            </div>

                            <!-- Cambio temporal para probar impresion con puente -->
                            <div id="impresora_bth" class="col-md-8 d-none row">
                                <div class="col-md-4">
                                    <label>IP de celular</label>
                                    <input type="text" class="form-control" name="ip_celular" id="ip_celular" autocomplete="off">
                                </div>
                            </div>

                            <!-- Impresora PC / Red -->
                            <div id="impresora_pc" class="col-md-8 d-none row">
                                <div class="col-md-4">
                                    <label>Nombre Impresora</label>
                                    <input type="text" class="form-control" name="nombre_impresora" id="nombre_impresora" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label>Nombre PC Principal</label>
                                    <input type="text" class="form-control" name="nombre_pc" id="nombre_pc" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label>IP PC Principal</label>
                                    <input type="text" class="form-control" name="ip_pc" id="ip_pc" autocomplete="off">
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