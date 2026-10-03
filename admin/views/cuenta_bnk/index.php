<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 justify-content-end my-2">
                    <div class="col-md-4">
                        <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal"
                            data-bs-whatever="NUEVA CUENTA BANCARIA" id="button_newgister">
                            <i class="fa-solid fa-plus"></i>
                            Nueva Cuenta
                        </button>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearch">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <table id="table_cuenta_bancaria" class="table display responsive" cellspacing="0" style="width:100%">

                    <thead>
                        <tr>
                            <th>Banco</th>
                            <th>Descripción</th>
                            <th>N° Cuenta</th>
                            <th>Moneda</th>
                            <th>Mostrar</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>