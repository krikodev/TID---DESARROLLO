<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Cupones Descuento';
        $title_modal = 'NUEVO CUPON';
        $show_btnadd = true;

        include("views/templates/components/cmp_headerpage.php")
        ?>

        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <div class="row g-2 d-flex justify-content-end my-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control inputSearch" placeholder="Buscar">
                            <button class="btn btn-success btnSearch">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <table id="table_cupon" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Tope Maximo</th>
                            <th>Tope Minimo</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Uso máximo</th>
                            <th>Uso actual</th>
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