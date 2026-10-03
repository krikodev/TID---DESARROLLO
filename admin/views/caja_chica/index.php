<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <div class="row g-2">
            <div
                class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
                <h2>
                    <a href="<?php echo URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
                    Caja chica
                </h2>
                <div>
                    <a data-bs-toggle="modal" data-bs-target="#modal_reporte_cajas" class="btn text-white d-none" id="button_rep_cajas"
                        style="background-color:#3FCCBA;">
                        <i class="fa-light fa-file"></i> Rep. cajas por terminal
                    </a>

                    <!-- href="<?php echo URL ?>caja_chica/impresion/liquidacion_terminal/<?php echo $data_terminal["id_terminal"] ?>" target="_blank" -->
                    <a data-bs-toggle="modal" data-bs-target="#modal_reporte" class="btn text-white"
                        style="background-color:#ffb703;"><i class="fa-light fa-file"></i> Liquidación Terminal</a>
                    <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal"
                        data-bs-whatever="NUEVA CAJA CHICA" id="button_newgister"><i class="fa-solid fa-plus"></i>
                        Nuevo</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-end my-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="table_caja_chica" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Referencia</th>
                            <th>Vendedor</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Saldo Inicial</th>
                            <th>Saldo Final</th>
                            <th>Saldo Real</th>
                            <th>Estado</th>
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