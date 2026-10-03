<main class="content">
    <div class="col-md-12 body-page">
        <div class="card mb-0 header-body">
            <div class="card-header">
                <h3 class="my-0">
                    <a class="btn text-white" href="<?php echo URL ?>reportes">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    Reporte de las Sucursales
                </h3>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportExcel export_excel"></span>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportCSV export_csv"></span>
            </div>
        </div>
        <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
            <div class="row g-2 d-flex my-3">
                <div class="col-md-4" id="parentUbigeoFiltro">
                    <label>Ubigeo</label>
                    <select class="form-select" name="ubigeo_filtro" id="ubigeo_filtro" required>
                        <?php if (!in_array($data_usuario["tipo_usuario"], ["Vendedor", "Administrador"])) : ?>
                            <option value="%">Mostrar todo</option>
                        <?php endif ?>
                        <?php if (isset($this->ubigeo) && $this->ubigeo["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->ubigeo["message"]); $i++) : ?>
                                <?php $text = $this->ubigeo["message"][$i] ?>
                                <?php $descrip = $text["cod_ubigeo"] . " | " . $text["depa"] . " | " . $text["provi"] . " | " . $text["distri"] ?>
                                <option value="<?php echo $text["cod_ubigeo"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success" id="btnFiltrar">BUSCAR</button>
                </div>
            </div>
            <table id="table_sucursales" class="table display" cellspacing="0" style="width:100%">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Cod. Sucursal</th>
                        <th>Cod. Ubigeo</th>
                        <th>Departamento</th>
                        <th>Provincia</th>
                        <th>Distrito</th>
                        <th>Dirección</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Número de Serie</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</main>