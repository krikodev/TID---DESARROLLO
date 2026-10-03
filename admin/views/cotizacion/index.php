<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-start my-2">
                    <div class="col-md-3">
                        <div>
                            <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal"
                                data-bs-whatever="NUEVA COTIZACIÓN" id="button_newgister"><i
                                    class="fa-solid fa-plus"></i> Nuevo</button>
                        </div>
                    </div>
                    <div class="col-md-5"></div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearch">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <table id="table_cotizacion" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>N° COTIZACIÓN</th>
                            <th>EMISIÓN</th>
                            <th>CLIENTE</th>
                            <th>CONDICIÓN</th>
                            <th>IMPORTE</th>
                            <th>VENCIMIENTO</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>