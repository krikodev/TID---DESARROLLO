<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <!-- Header Page  -->
        <?php
        $title = 'Notas de Credito y Debito';
        $title_modal = '';
        $show_btnadd = false;

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
                <table id="table_notas" class="table display responsive" cellspacing="0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Emisión</th>
                            <th>Serie - Correlativo</th>
                            <th>Motivo D.</th>
                            <th>Mensaje SUNAT</th>
                            <th>Estado</th>
                            <th>Documento Relacionado</th>
                            <th>Total</th>
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