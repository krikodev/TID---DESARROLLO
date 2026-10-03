<main class="content">
    <div class="col-md-12 body-page">
        <div class="card mb-0 header-body">
            <div class="card-header">
                <h3 class="my-0">
                    <a class="btn text-white" href="<?php echo URL ?>reportes">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    Reporte de Caja Chica
                </h3>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportExcel export_excel"></span>
                <span class="position-absolute mb-0 bg-transparent top-0 end-0 translate-middle btn_exportCSV export_csv"></span>
            </div>
        </div>
        <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
            <div class="row g-2 d-flex my-3">
                <div class="col-md-2">
                    <label>Fecha Inicio</label>
                    <input type="date" class="form-control" name="f_ini_filtro" id="f_ini_filtro" value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-md-2">
                    <label>Fecha Fin</label>
                    <input type="date" class="form-control" name="f_fin_filtro" id="f_fin_filtro" value="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="col-md-2" id="parentUsuarioFiltro">
                    <label>Usuario</label>
                    <select class="form-select" name="usuario_filtro" id="usuario_filtro" required>
                        <option value="%">Mostrar todo</option>
                        <?php if (isset($this->usuarios) && $this->usuarios["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->usuarios["message"]); $i++) : ?>
                                <?php $text = $this->usuarios["message"][$i] ?>
                                <?php $descrip = $text["nombres"] . " " . $text["apellidos"] ?>
                                <option value="<?php echo $text["id_usuario"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2" id="parentSucursalFiltro">
                    <label>Sucursal</label>
                    <select class="form-select" name="sucursal_filtro" id="sucursal_filtro" required>
                        <?php if (!in_array($data_usuario["tipo_usuario"], ["Vendedor", "Administrador"])) : ?>
                            <option value="%">Mostrar todo</option>
                        <?php endif ?>
                        <?php if (isset($this->sucursales) && $this->sucursales["success"]) : ?>
                            <?php for ($i = 0; $i < count($this->sucursales["message"]); $i++) : ?>
                                <?php $text = $this->sucursales["message"][$i] ?>
                                <?php $descrip = $text["descripcion"] ?>
                                <option value="<?php echo $text["id_sucursal"] ?>"><?php echo $descrip ?></option>
                            <?php endfor ?>
                        <?php else : ?>
                        <?php endif ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-select" name="estado_filtro" id="estado_filtro">
                        <option value="%">Mostrar todo</option>
                        <option value="1">ABIERTO</option>
                        <option value="0">CERRADO</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Turno</label>
                    <select class="form-select" name="turno_filtro" id="turno_filtro">
                        <option value="%">Mostrar todo</option>
                        <option value="Mañana">MAÑANA</option>
                        <option value="Tarde">TARDE</option>
                        <option value="Noche">NOCHE</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success" id="btnFiltrar">BUSCAR</button>
                </div>
            </div>
            <table id="table_cajachica" class="table display" cellspacing="0" style="width:100%">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Vendedor</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Saldo inicial</th>
                        <th>Saldo final</th>
                        <th>Estado</th>
                        <th>Turno</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</main>