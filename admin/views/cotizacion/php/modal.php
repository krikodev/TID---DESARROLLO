<!-- Modal Cotización -->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">
                    Nueva Cotización
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <form id="form" class="needs-validation" novalidate>

                    <input type="hidden" name="id_cotizacion" id="id_cotizacion">
                    <input type="hidden" name="modo" id="modo" value="nuevo">


                    <!-- ========================================= -->
                    <!-- DATOS GENERALES -->
                    <!-- ========================================= -->

                    <div class="row">

                        <!-- Serie -->
                        <div class="col-md-3 my-1" id="div_parentSerie">
                            <label for="s_cotizacion">
                                Serie
                                <span class="requiredField">*</span>
                            </label>

                            <select class="form-select" name="s_cotizacion" id="s_cotizacion" required>
                            </select>
                        </div>


                        <!-- Fecha emisión -->
                        <div class="col-md-3 my-1" id="div_parentFechaEmision">
                            <label for="fecha_emision">
                                Fecha de emisión
                                <span class="requiredField">*</span>
                            </label>

                            <input type="date"
                                class="form-control"
                                name="fecha_emision"
                                id="fecha_emision"
                                value="<?= date('Y-m-d') ?>"
                                required>
                        </div>


                        <!-- Días validez -->
                        <div class="col-md-2 my-1">

                            <label for="dias_validez">
                                Validez
                                <span class="requiredField">*</span>
                            </label>

                            <div class="input-group">

                                <input type="number"
                                    class="form-control"
                                    name="dias_validez"
                                    id="dias_validez"
                                    value="15"
                                    min="1"
                                    required>

                                <span class="input-group-text">
                                    días
                                </span>

                            </div>

                        </div>

                        <div class="col-md-2 my-1" id="div_parentMoneda">
                            <label for="moneda">Moneda <span class="requiredField">*</span></label>
                            <select name="moneda" id="moneda" class="form-select">
                                <?php if ($this->monedas['success']): ?>
                                    <?php foreach ($this->monedas['message'] as $moneda): ?>
                                        <option value="<?= $moneda['id_tp_moneda'] ?>"><?= $moneda['descripcion'] ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <!-- Fecha vencimiento -->
                        <div class="col-md-2 my-1" id="div_parentFechaVencimiento">

                            <label for="fecha_vencimiento">
                                Fecha de vencimiento
                            </label>

                            <input type="date"
                                class="form-control"
                                name="fecha_vencimiento"
                                id="fecha_vencimiento"
                                readonly>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- CLIENTE -->
                    <!-- ========================================= -->

                    <div class="row">

                        <div class="col-md-12 my-2">

                            <label for="cliente">

                                Cliente
                                <span class="requiredField">*</span>

                                <a href="javascript:void(0)" class="open_modal_cliente">
                                    [+ Nuevo]
                                </a>

                            </label>


                            <div class="input-group">

                                <input type="text"
                                    class="form-control"
                                    id="cliente"
                                    name="cliente"
                                    placeholder="Buscar por DNI | RUC | Nombre | Razón social"
                                    required>

                                <button type="button"
                                    class="btn btnS"
                                    id="buscar_cliente">
                                    Buscar
                                </button>


                                <button class="btn btnS d-none"
                                    type="button"
                                    id="loader_buscar"
                                    disabled>

                                    <span class="spinner-border spinner-border-sm"
                                        role="status"
                                        aria-hidden="true">
                                    </span>

                                </button>


                                <input type="hidden"
                                    id="cliente_id"
                                    name="cliente_id">

                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- ORIGEN / DESTINO -->
                    <!-- ========================================= -->

                    <div class="card border-0 shadow-sm mt-2">

                        <div class="card-header">
                            <strong>Ruta del servicio</strong>
                        </div>

                        <div class="card-body">

                            <div class="row">

                                <!-- Origen -->
                                <div class="col-md-6 my-1">

                                    <label for="p_origen">

                                        Punto de origen
                                        <span class="requiredField">*</span>

                                    </label>

                                    <select id="p_origen"
                                        name="p_origen"
                                        required>
                                    </select>

                                </div>


                                <!-- Destino -->
                                <div class="col-md-6 my-1">

                                    <label for="p_destino">

                                        Punto de destino
                                        <span class="requiredField">*</span>

                                    </label>

                                    <select id="p_destino"
                                        name="p_destino"
                                        required>
                                    </select>

                                </div>

                            </div>


                            <!-- Direcciones -->

                            <div class="row">

                                <div class="col-md-6 my-1">

                                    <label for="direccion_origen">
                                        Dirección de origen
                                    </label>

                                    <input type="text"
                                        class="form-control"
                                        name="direccion_origen"
                                        id="direccion_origen"
                                        placeholder="Opcional">

                                </div>


                                <div class="col-md-6 my-1">

                                    <label for="direccion_destino">
                                        Dirección de destino
                                    </label>

                                    <input type="text"
                                        class="form-control"
                                        name="direccion_destino"
                                        id="direccion_destino"
                                        placeholder="Opcional">

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- CONDICIÓN DE PAGO / IGV -->
                    <!-- ========================================= -->

                    <div class="row mt-2">

                        <!-- Condición de pago -->
                        <div class="col-md-4 my-1">

                            <label for="condicion_pago">

                                Condición de pago
                                <span class="requiredField">*</span>

                            </label>

                            <select class="form-select"
                                name="condicion_pago"
                                id="condicion_pago"
                                required>

                                <option value="CONTADO">
                                    CONTADO
                                </option>

                                <option value="CREDITO">
                                    CRÉDITO
                                </option>

                            </select>

                        </div>


                        <!-- Días de crédito -->
                        <div class="col-md-4 my-1 d-none"
                            id="div_parentDiasCredito">

                            <label for="dias_credito">

                                Plazo de crédito
                                <span class="requiredField">*</span>

                            </label>

                            <div class="input-group">

                                <input type="number"
                                    class="form-control"
                                    name="dias_credito"
                                    id="dias_credito"
                                    value="0"
                                    min="1">

                                <span class="input-group-text">
                                    días
                                </span>

                            </div>

                        </div>


                        <!-- Incluye IGV -->
                        <div class="col-md-4 my-1">
                            <label>
                                ¿Incluye IGV? <span class="requiredField">*</span>
                            </label>

                            <div class="igv-selector">

                                <input type="radio"
                                    class="btn-check"
                                    name="incluye_igv"
                                    id="incluye_igv_si"
                                    value="1"
                                    checked>

                                <label class="igv-option" for="incluye_igv_si">
                                    <i class="fa-light fa-circle-check"></i>
                                    <span>Sí</span>
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="incluye_igv"
                                    id="incluye_igv_no"
                                    value="0">

                                <label class="igv-option" for="incluye_igv_no">
                                    <i class="fa-light fa-circle-xmark"></i>
                                    <span>No</span>
                                </label>

                            </div>
                        </div>

                    </div>

                    <!-- ========================================= -->
                    <!-- SERVICIO -->
                    <!-- ========================================= -->

                    <div class="card border-0 shadow-sm mt-3">

                        <div class="card-header">
                            <strong>Detalle del servicio</strong>
                        </div>

                        <div class="card-body">

                            <div class="row align-items-end">

                                <!-- Producto / Servicio -->
                                <div class="col-md-12 my-1"
                                    id="div_parentProducto">

                                    <label for="producto">

                                        Producto / Servicio

                                        <a href="javascript:void(0)"
                                            class="open_modal_producto">
                                            [+ Nuevo]
                                        </a>

                                        <span class="requiredField">*</span>

                                    </label>


                                    <div class="input-group">

                                        <input type="text"
                                        autocomplete="new-password"
                                            class="form-control"
                                            id="producto"
                                            name="producto"
                                            placeholder="Buscar por código o nombre"
                                            required>


                                        <button type="button"
                                            class="btn btnS"
                                            id="buscar_producto">
                                            Buscar
                                        </button>


                                        <button class="btn btnS d-none"
                                            type="button"
                                            id="loader_buscar_producto"
                                            disabled>

                                            <span class="spinner-border spinner-border-sm">
                                            </span>

                                        </button>


                                        <input type="hidden"
                                            id="producto_id"
                                            name="producto_id">

                                    </div>

                                </div>


                                <!-- Tipo unidad -->
                                <div class="col-md-4 my-1"
                                    id="div_parentTpUnServicio">

                                    <label for="tipo_unidad_servicio">

                                        Unidad
                                        <span class="requiredField">*</span>

                                    </label>


                                    <select class="form-select"
                                        id="tipo_unidad_servicio"
                                        name="tipo_unidad_servicio"
                                        required>

                                        <option value="">
                                            Seleccione
                                        </option>


                                        <?php if ($this->unidad_servicio['success']): ?>

                                            <?php foreach (
                                                $this->unidad_servicio["message"]
                                                as $unidad_servicio
                                            ): ?>

                                                <option value="<?= $unidad_servicio["id"] ?>">
                                                    <?= $unidad_servicio["nombre"] ?>
                                                </option>

                                            <?php endforeach ?>

                                        <?php endif ?>

                                    </select>

                                </div>


                                <!-- Cantidad -->
                                <div class="col-md-2 my-1">

                                    <label for="cantidad_viajes">

                                        Cantidad
                                        <span class="requiredField">*</span>

                                    </label>


                                    <div class="number-input-wrapper">

                                        <button type="button"
                                            id="btn-minusV"
                                            class="btn-number btn-minus"
                                            data-type="minus"
                                            data-field="cantidad_viajes">
                                            −
                                        </button>


                                        <input type="number"
                                            name="cantidad_viajes"
                                            id="cantidad_viajes"
                                            class="form-control"
                                            value="1"
                                            min="1"
                                            max="100"
                                            required>


                                        <button type="button"
                                            id="btn-plusV"
                                            class="btn-number btn-plus"
                                            data-type="plus"
                                            data-field="cantidad_viajes">
                                            +
                                        </button>

                                    </div>

                                </div>


                                <!-- Precio unitario -->
                                <div class="col-md-3 my-1">

                                    <label for="precio_unit">

                                        Precio unitario
                                        <span class="requiredField">*</span>

                                    </label>


                                    <input type="number"
                                        name="precio_unit"
                                        id="precio_unit"
                                        class="form-control"
                                        min="0"
                                        step="0.01"
                                        required>

                                </div>


                                <!-- Total -->
                                <div class="col-md-2 my-1">

                                    <label for="importe_total">
                                        Importe
                                    </label>


                                    <input type="number"
                                        name="importe_total"
                                        id="importe_total"
                                        class="form-control"
                                        readonly>

                                </div>


                                <!-- Agregar -->
                                <div class="col-md-1 my-1">

                                    <button type="button"
                                        class="btn btnColorViolet w-100"
                                        id="add_producto">
                                        +
                                    </button>

                                </div>

                            </div>


                            <!-- ========================================= -->
                            <!-- TABLA DE SERVICIOS -->
                            <!-- ========================================= -->

                            <div class="table-responsive mt-3">

                                <table class="table todo_list w-100"
                                    cellspacing="0"
                                    id="table_productos">

                                    <thead>

                                        <tr>

                                            <th>Servicio</th>
                                            <th>Unidad</th>
                                            <th>Cantidad</th>
                                            <th>Precio U.</th>
                                            <th>Importe</th>
                                            <th>Acción</th>

                                        </tr>

                                    </thead>


                                    <tbody>
                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- TOTALES -->
                    <!-- ========================================= -->

                    <div class="row justify-content-end mt-3">

                        <div class="col-md-5">

                            <div class="card">

                                <div class="card-body">

                                    <!-- Valor de venta -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Valor de venta</span>

                                        <strong id="subtotal_total">
                                            0.00
                                        </strong>
                                    </div>

                                    <!-- Descuento: oculto, se mantiene por compatibilidad -->
                                    <div class="d-none">
                                        <input type="number"
                                            name="descuento"
                                            id="descuento"
                                            value="0.00"
                                            min="0"
                                            step="0.01">
                                    </div>

                                    <!-- IGV -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>
                                            IGV
                                        </span>

                                        <strong id="igv_total">
                                            0.00
                                        </strong>
                                    </div>

                                    <hr>

                                    <!-- Total -->
                                    <div class="d-flex justify-content-between">

                                        <span class="fw-bold">
                                            Total
                                        </span>

                                        <strong id="costo_total" class="fs-5">
                                            0.00
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- CONDICIONES DEL SERVICIO -->
                    <!-- ========================================= -->

                    <div class="row mt-4">

                        <div class="col-md-12">

                            <label>
                                Condiciones del servicio
                            </label>

                            <div id="condiciones_servicio"></div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- OBSERVACIÓN -->
                    <!-- ========================================= -->

                    <div class="row mt-3">

                        <div class="col-md-12">

                            <label for="obs">
                                Observación
                            </label>

                            <textarea class="form-control"
                                name="obs"
                                id="obs"
                                rows="3"
                                placeholder="Observaciones adicionales de la cotización"></textarea>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- FOOTER -->
                    <!-- ========================================= -->

                    <div class="modal-footer mt-4">

                        <button type="button"
                            class="btn btnCancel"
                            id="button_cancel"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>


                        <button type="submit"
                            class="btn btnSave"
                            id="button_save">
                            Guardar
                        </button>


                        <button class="btn btnSave d-none"
                            type="button"
                            id="button_loadSave"
                            disabled>

                            <span class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true">
                            </span>

                            Guardando...

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


<div class="modal fade" id="modal_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="titulo">NUEVO CLIENTE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_cliente" class="needs-validation" novalidate>
                    <input type="hidden" id="operacion" name="operacion">
                    <ul class="nav nav-pills mb-3" id="pillsTab_contrato" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_general" data-bs-toggle="pill"
                                data-bs-target="#general" type="button" role="tab" aria-controls="tab_general"
                                aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_contacto" data-bs-toggle="pill" data-bs-target="#contacto"
                                type="button" role="tab" aria-controls="tab_contacto"
                                aria-selected="false">Contacto</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabs_contrato">
                        <!-- GENERAL -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel"
                            aria-labelledby="tab_general" tabindex="0">
                            <div class="row g-2 my-2">
                                <div class="col-md-12 row">
                                    <input type="hidden" id="id_pasajero" name="id_pasajero">
                                    <div class="col-md-4">
                                        <label>Tipo de documento<span class="requiredField">*</span></label>
                                        <select class="form-select" name="tp_docu" id="tp_docu">
                                            <option value="1">DNI</option>
                                            <option value="4">Carnet extranjeria</option>
                                            <option value="6">RUC</option>
                                            <option value="7">Pasaporte</option>
                                            <option value="0">Otros documentos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>N° Documento<span class="requiredField">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="num_docu" name="num_docu"
                                                autocomplete="off" required minlength="8" maxlength="8">
                                            <button type="button" class="btn btnSearch"
                                                id="button_search">BUSCAR</button>
                                            <button class="btn btnSearch d-none" type="button" id="button_loadSearch"
                                                disabled>
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                                BUSCAR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-3 my-2">
                                            <label>Nombres<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="nombres" name="nombres"
                                                autocomplete="off" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Apellidos<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                                autocomplete="off" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Género Pasajero</label>
                                            <select class="form-select" id="genero" name="genero">
                                                <option value="">Seleccione</option>
                                                <option value="MASCULINO">MASCULINO</option>
                                                <option value="FEMENINO">FEMENINO</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2" id="div_parentUbigeo">
                                            <label>Ubigeo<span class="requiredField">*</span></label>
                                            <select class="form-select" id="ubigeo" name="ubigeo" required>
                                                <?php if ($this->ubigeo_terminal["success"]): ?>
                                                    <?php $terminal_ubigeo = $this->ubigeo_terminal["message"]["ubigeo"]; ?>
                                                    <option value="<?php echo $terminal_ubigeo ?>">
                                                        <?php echo $this->ubigeo_terminal["message"]["ubigeo"] . " | " . $this->ubigeo_terminal["message"]["depa"] . " | " . $this->ubigeo_terminal["message"]["provi"] . " | " . $this->ubigeo_terminal["message"]["distri"] ?>
                                                    </option>
                                                <?php endif ?>

                                                <?php if ($this->ubigeo["success"]): ?>
                                                    <?php foreach ($this->ubigeo["message"] as $item): ?>
                                                        <?php if ($item["cod_ubigeo"] !== $terminal_ubigeo): ?>
                                                            <option value="<?php echo $item["cod_ubigeo"] ?>">
                                                                <?php echo $item["cod_ubigeo"] . " | " . $item["depa"] . " | " . $item["provi"] . " | " . $item["distri"] ?>
                                                            </option>
                                                        <?php endif ?>
                                                    <?php endforeach ?>
                                                <?php endif ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Dirección<span class="requiredField">*</span></label>
                                            <input type="text" class="form-control" id="direccion" name="direccion"
                                                autocomplete="off" value="Sin Direccion" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Celular</label>
                                            <input type="text" class="form-control" id="celular" name="celular"
                                                minlength="9" maxlength="9" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Correo electrónico</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                autocomplete="off">
                                        </div>
                                        <?php $col = "col-md-3";
                                        ?>
                                        <div class="<?php echo $col ?> my-2" id="div_parentTerminal">
                                            <label>Terminal<span class="requiredField">*</span></label>
                                            <select class="form-select" id="terminal" name="terminal" required>
                                                <?php if ($this->terminal_activo["success"]): ?>
                                                    <?php for ($i = 0; $i < count($this->terminal_activo["message"]); $i++): ?>
                                                        <?php $text = $this->terminal_activo["message"][$i]["nombre"] ?>
                                                        <option
                                                            value="<?php echo $this->terminal_activo["message"][$i]["id_terminal"] ?>">
                                                            <?php echo $text ?>
                                                        </option>
                                                    <?php endfor ?>
                                                <?php endif ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Fecha Nacimiento<span class="requiredField">*</span></label>
                                            <input type="date" class="form-control" id="fecha_nacimiento"
                                                name="fecha_nacimiento" required>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Nacionalidad <span class="requiredField">*</span></label>
                                            <select id="nacionalidad" name="nacionalidad" required
                                                autocomplete="new-password"></select>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Estado SUNAT</label>
                                            <input type="text" class="form-control" id="estado_sunat"
                                                name="estado_sunat" autocomplete="off" readonly>
                                        </div>
                                        <div class="col-md-3 my-2 d-none">
                                            <label>Condición SUNAT</label>
                                            <input type="text" class="form-control" id="condicion_sunat"
                                                name="condicion_sunat" autocomplete="off" readonly>
                                        </div>
                                        <div class="col-md-3 my-2">
                                            <label>Estado<span class="requiredField">*</span></label>
                                            <select class="form-select" name="estado" id="estado" required>
                                                <option value="1">Habilitado</option>
                                                <option value="0">Deshabilitado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contacto" role="tabpanel" aria-labelledby="tab_contacto"
                            tabindex="0">
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table todo_list w-100" cellspacing="0" id="table_contacto">
                                    <thead>
                                        <tr>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Celular</th>
                                            <th>Observación</th>
                                            <th><button type="button" class="btn p-0" id="btn_add_contacto"><i
                                                        class="bi bi-plus-square text-info fs-5"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel_c"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save_c">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave_c" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_producto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel">NUEVO PRODUCTO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form_producto" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <input type="hidden" id="id_ctg_encomienda" name="id_ctg_encomienda">
                        <div class="col-md-7 my-1">
                            <label>Nombre<span class="requiredField">*</span></label>
                            <input type="text" class="form-control" id="nombre_producto" name="nombre_producto"
                                autocomplete="off" required>
                        </div>
                        <div class="col-md-5 my-1">
                            <label>Descripción<span class="optionalField">*</span></label>
                            <input type="text" class="form-control" id="descripcion_producto"
                                name="descripcion_producto" autocomplete="off" required>
                        </div>
                        <div class="col-md-3 my-1">
                            <label>Precio Unit.<span class="requiredField">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text py-0">S/</span>
                                <input type="number" class="form-control" id="precio_unitario" name="precio_unitario"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-3 my-1">
                            <label>Codigo SUNAT<span class="optionalField">*</span></label>
                            <input type="text" class="form-control" id="codigo_sunat" name="codigo_sunat"
                                autocomplete="off" required>
                        </div>
                        <div class="col-md-3 my-1" id="div_parentAfectacion">
                            <label>Afectación<span class="requiredField">*</span></label>
                            <select class="form-select" name="afectacion_prod" id="afectacion_prod" required>
                                <?php if ($this->afectaciones['success']): ?>
                                    <?php foreach ($this->afectaciones["message"] as $afectacion): ?>
                                        <?php $text = $afectacion["descripcion"] ?>
                                        <option value="<?= $afectacion["id"] ?>">
                                            <?= $text ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-3 my-1">
                            <label>Estado<span class="requiredField">*</span></label>
                            <select class="form-select" name="estado_prod" id="estado_prod" required>
                                <option value="1">ACTIVO</option>
                                <!-- <option value="0">INACTIVO</option> -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancelP"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_saveP">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSaveP" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>