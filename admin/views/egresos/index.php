<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <?php
                $id_caja = !empty($this->caja_userSesion["message"][0]["id_caja_chica"]) ? $this->caja_userSesion["message"][0]["id_caja_chica"] : 0;
                ?>
                <input type="hidden" name="id_caja_chica" id="id_caja_chica" value="<?= $id_caja ?>">
                <div class="row g-2 d-flex justify-content-end my-2">
                    <div class="col-md-1">
                        <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="NUEVO EGRESO" id="button_newgister"><i class="fa-solid fa-plus"></i> Nuevo</button>
                    </div>
                    <div class="col-md-7"></div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearch">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="table_egresos" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Caja chica</th>
                            <th>Tipo comprobante</th>
                            <th>Serie</th>
                            <th>Correlativo</th>
                            <th>Monto</th>
                            <th>Concepto</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>