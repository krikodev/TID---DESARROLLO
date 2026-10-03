<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">

                <div class="row g-2 align-items-center my-2">

                    <div class="col-md-4">
                        <button class="btn btnAddRegis" id="btnNuevoModulo">
                            <i class="fa-solid fa-plus"></i>
                            Nuevo Módulo
                        </button>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-secondary" id="btnOrdenar">
                            <i class="fa-solid fa-up-down-left-right"></i>
                            Reordenar módulos
                        </button>
                    </div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar módulo...">

                            <button class="btn btn-success btnSearch">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <table id="table_modulo" class="table table-hover display responsive nowrap align-middle"
                    style="width:100%">

                    <thead>
                        <tr>
                            <th class="text-center" style="width:70px;">Orden</th>
                            <th class="text-center" style="width:70px;">
                                <i class="fa-solid fa-icons"></i>
                            </th>
                            <th>Nombre</th>
                            <th style="width:130px;">Clave</th>
                            <th class="text-center" style="width:100px;">Tipo</th>
                            <th>Padre</th>
                            <th>Controlador</th>
                            <th>C. permiso</th>
                            <th class="text-center" style="width:90px;">Visible</th>
                            <th class="text-center" style="width:90px;">Estado</th>
                            <th class="text-center" style="width:150px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>
        </div>

    </div>
</div>