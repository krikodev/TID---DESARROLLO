<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="col-md-12 body-page">
            <div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
                <form id="form_config_encomienda" class="needs-validation" novalidate>
                    <div class="row g-2 my-2">
                        <div class="col-md-8 row">
                            <div class="col-md-2" style="text-align:center;">
                                <label>N. de recibo fisico</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Cuando la opcion este activada se <br> habilitara el campo en el
                                        modal de creación</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="numero_recibo" id="numero_recibo" value="0">
                                        <input type="checkbox" id="numero_recibo_check" name="numero_recibo_check"
                                            value="1">
                                        <label for="numero_recibo_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label>Cambiar nombre nota venta</label>
                                <i class="uiverse fa-solid fa-circle-info">
                                    <span class="tooltip">Cuando la opción esté activada, el nombre de nota de venta en
                                        encomienda será reemplazado por el que ingrese</span>
                                </i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="cambiar_n_NV" id="cambiar_n_NV" value="0">
                                        <input type="checkbox" id="cambiar_n_NV_check" name="cambiar_n_NV_check"
                                            value="1">
                                        <label for="cambiar_n_NV_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label>Credito automatico</label>
                                <i class="uiverse fa-solid fa-circle-info">
                                    <span class="tooltip">Cuando la opción esté activada, el sistema seleccionará
                                        automáticamente la opción pago en bloque o crédito cuando se seleccione pago en
                                        destino</span>
                                </i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="detectar_pago_bloque" id="detectar_pago_bloque"
                                            value="0">
                                        <input type="checkbox" id="detectar_pago_bloque_check"
                                            name="detectar_pago_bloque_check" value="1">
                                        <label for="detectar_pago_bloque_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Embarques generales</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Se incluiran encomiendas con destino intermedio de la
                                        programacion general para los embarques</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="embarque_general" id="embarque_general" value="0">
                                        <input type="checkbox" id="embarque_general_check" name="embarque_general_check"
                                            value="1">
                                        <label for="embarque_general_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Impresion Completa</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Se incluiran archivo y transportista en la impresion de los
                                        comprobantes de encomienda</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="impresion_ecompleta" id="impresion_ecompleta"
                                            value="0">
                                        <input type="checkbox" id="impresion_ecompleta_check"
                                            name="impresion_ecompleta_check" value="1">
                                        <label for="impresion_ecompleta_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Dirección Completa</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Mostrar dirección completa con ubigeo en comprobantes de
                                        encomienda</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="mostrar_direccion_completa"
                                            id="mostrar_direccion_completa" value="0">
                                        <input type="checkbox" id="mostrar_direccion_completa_check"
                                            name="mostrar_direccion_completa_check" value="1">
                                        <label for="mostrar_direccion_completa_check"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2" style="text-align:center;">
                                <label>Mostrar Tracking</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Mostrar sección de tracking en comprobantes de
                                        encomienda</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="mostrar_tracking" id="mostrar_tracking" value="0">
                                        <input type="checkbox" id="mostrar_tracking_check" name="mostrar_tracking_check"
                                            value="1">
                                        <label for="mostrar_tracking_check"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2" style="text-align:center;">
                                <label>Mostrar Vendedor</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Mostrar nombre del vendedor en comprobantes de
                                        encomienda</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="mostrar_vendedor" id="mostrar_vendedor" value="0">
                                        <input type="checkbox" id="mostrar_vendedor_check" name="mostrar_vendedor_check"
                                            value="1">
                                        <label for="mostrar_vendedor_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Datos Destinatario</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Mostrar sección completa de datos del destinatario en
                                        comprobantes</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="datos_destinatario" id="datos_destinatario"
                                            value="0">
                                        <input type="checkbox" id="datos_destinatario_check"
                                            name="datos_destinatario_check" value="1">
                                        <label for="datos_destinatario_check"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2" style="text-align:center;">
                                <label>ORIGEN/DESTINO</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Mostrar sección ORIGEN/DESTINO completa (incluye
                                        dirección)</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="origen_destino" id="origen_destino" value="0">
                                        <input type="checkbox" id="origen_destino_check" name="origen_destino_check"
                                            value="1">
                                        <label for="origen_destino_check"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2" style="text-align:center;">
                                <label>Número Tel. Enc.</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Cuando se haga una encomienda y el usuario no tenga registrado
                                        su nro. de celular pedir obligatoriamente</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="nrocel_cliente" id="nrocel_cliente" value="0">
                                        <input type="checkbox" id="nrocel_cliente_check" name="nrocel_cliente_check"
                                            value="1">
                                        <label for="nrocel_cliente_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Envíos erroneos</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Permite marcar encomiendas como mal enviado y asi poder hacer
                                        que se reenvie al lugar correcto</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="envio_erroneo" id="envio_erroneo" value="0">
                                        <input type="checkbox" id="envio_erroneo_check" name="envio_erroneo_check"
                                            value="1">
                                        <label for="envio_erroneo_check"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <label>Rotulado E.</label> <i class="uiverse fa-solid fa-circle-info"><span
                                        class="tooltip">Permite mostrar el formato de impresión del rotulado para
                                        encomiendas</span></i>
                                <div class="form-group">
                                    <div class="checkbox-container">
                                        <input type="hidden" name="rotulado_e" id="rotulado_e" value="0">
                                        <input type="checkbox" id="rotulado_e_check" name="rotulado_e_check" value="1">
                                        <label for="rotulado_e_check"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Opcion predeterminado de tipo precio<span
                                    class="requiredField me-2">*</span></label><i
                                class="uiverse fa-solid fa-circle-info"><span class="tooltip">Seleccione la opcion que
                                    quieres que este al inicio del select</span></i>
                            <select class="form-select" name="default_tipo_precio" id="default_tipo_precio">
                                <option value="unidad">Por unidad</option>
                                <option value="peso">Por peso (Kg)</option>
                                <option value="peso_total">Por peso total (Kg)</option>
                                <option value="precio_total">Por precio total</option>
                            </select>
                            <label for="nombre_nota_venta">
                                Ingrese el nuevo nombre <span class="requiredField">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre_nota_venta" id="nombre_nota_venta"
                                disabled>

                            <label>Términos condiciones <strong>Encomienda</strong></label>
                            <div id="termscond_encomienda">
                            </div>
                        </div>
                    </div>
                    <div class="page-header" style="text-align:center;">
                        <h1> <span>Exportación a Excel</span> &nbsp;<i class="uiverse fa-solid fa-circle-info">
                                <span class="tooltip">Seleccioná los campos que quieres exportar, personalizá sus
                                    etiquetas y ordénalos arrastrando.</span>
                            </i></h1>
                    </div>

                    <div class="main-card">

                        <!-- Toolbar -->
                        <div class="toolbar">
                            <div class="d-flex align-items-center gap-2">
                                <label>Módulo</label>
                                <select id="sel-modulo">
                                    <option value="encomienda">Encomienda</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2 d-none">
                                <label>Perfil</label>
                                <select id="sel-perfil">
                                    <option value="1">Default</option>
                                    <option value="2">Contabilidad</option>
                                    <option value="3">Gerencia</option>
                                </select>
                            </div>
                            <input id="search-field" class="toolbar-search" type="text" placeholder="Buscar campo…" />
                            <button type="button" class="btn-action btn-secondary-accent ms-auto" id="btn-select-all">
                                <i class="bi bi-check2-all"></i> Todos
                            </button>
                            <button type="button" class="btn-action btn-secondary-accent" id="btn-clear-all">
                                <i class="bi bi-x-lg"></i> Limpiar
                            </button>
                        </div>

                        <!-- Panels -->
                        <div class="panels">

                            <!-- LEFT: Available fields -->
                            <div class="panel panel-left">
                                <div class="panel-title">
                                    <i class="bi bi-database"></i>
                                    Campos disponibles
                                    <span class="count-badge" id="count-available">0</span>
                                </div>
                                <div class="scroll-area" id="fields-container"></div>
                            </div>

                            <!-- RIGHT: Selected fields -->
                            <div class="panel panel-right">
                                <div class="panel-title">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                    Campos seleccionados
                                    <span class="count-badge green" id="count-selected">0</span>
                                </div>
                                <div id="drop-zone" class="drop-zone">
                                    <div class="drop-zone-empty" id="empty-hint">
                                        <i class="bi bi-arrow-left-circle"></i>
                                        <span>Haz clic en un campo<br />para agregarlo aquí</span>
                                    </div>
                                    <div id="selected-list" class="scroll-area" style="max-height:440px"></div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="footer-bar">
                            <div class="footer-info">
                                <strong id="footer-count">0</strong> campos seleccionados para exportar
                            </div>
                            <div class="d-flex gap-2 d-none">
                                <button type="button" class="btn-action btn-secondary-accent" id="btn-preview-json">
                                    <i class="bi bi-code-slash"></i> Ver JSON
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- JSON Preview Modal -->
                    <div class="modal fade" id="jsonModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content"
                                style="background:var(--surface);border:1px solid var(--border);border-radius:14px;">
                                <div class="modal-header" style="border-color:var(--border)">
                                    <h5 class="modal-title"
                                        style="font-family:'Syne',sans-serif;font-size:1rem;color:var(--text)">
                                        <i class="bi bi-code-slash me-2" style="color:var(--accent2)"></i>Preview
                                        config_exportacion_campos
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre id="json-output"
                                        style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:1rem;font-size:0.75rem;color:var(--accent2);max-height:400px;overflow:auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btnSave" id="button_save">
                            <i class="bi bi-floppy me-1"></i> Guardar todo
                        </button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave" disabled>
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>