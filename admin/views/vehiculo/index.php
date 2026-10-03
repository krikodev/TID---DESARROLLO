<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Vehículos';
        $title_modal = 'NUEVO VEHÍCULO';
        $show_btnadd = true;

        include("views/templates/components/cmp_headerpage.php")
        ?>

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
                <table id="table_vehiculo" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>SOAT</th>
                            <th>Nro. Piso</th>
                            <th>Nro. Asiento</th>
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