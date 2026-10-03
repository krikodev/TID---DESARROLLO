<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-end my-2">
                    <div class="col-md-3">
                        <?php if ($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2): ?>
                            <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal"
                                data-bs-whatever="NUEVO FLETE" data-mode="new" id="button_newgister"><i
                                    class="fa-solid fa-plus"></i>
                                Nuevo</button>
                        <?php endif ?>
                    </div>
                    <div class="col-md-5"></div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearchTable">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table
                    id="table_flete"
                    class="table display responsive"
                    cellspacing="0"
                    style="width:100%">

                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Conductor</th>
                            <th>Vehículo</th>
                            <th>Fecha salida</th>
                            <th>Hora salida</th>
                            <th>Total</th>
                            <th>Incluye IGV</th>
                            <th>Condición pago</th>
                            <th>Estado</th>
                            <th>Estado pago</th>
                            <th>Tipo Flete</th>
                            <th>Comprobante</th>
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