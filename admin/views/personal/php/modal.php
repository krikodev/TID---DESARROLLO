<!-- Modal add Register-->
<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#495057;">
                <h5 class="modal-title text-white" id="staticBackdropLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <form id="form" class="needs-validation" novalidate>
                    <ul class="nav nav-pills mb-3" id="pillsTab_contrato" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_general" data-bs-toggle="pill"
                                data-bs-target="#general" type="button" role="tab" aria-controls="tab_general"
                                aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_permisos" data-bs-toggle="pill" data-bs-target="#permisos"
                                type="button" role="tab" aria-controls="tab_permisos"
                                aria-selected="false">Permisos</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabs_contrato">
                        <!-- GENERAL -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel"
                            aria-labelledby="tab_general" tabindex="0">
                            <div class="row g-2 my-2">
                                <div class="col-md-12 row">
                                    <input type="hidden" id="id_personal" name="id_personal">
                                    <div class="col-md-4">
                                        <label>Tipo de documento<span class="requiredField">*</span></label>
                                        <select class="form-select" name="tp_docu" id="tp_docu">
                                            <?php if ($this->tp_docu["success"]): ?>
                                                <?php foreach ($this->tp_docu["message"] as $tp_docu): ?>
                                                    <?php $text = $tp_docu["descripcion"] ?>
                                                    <option value="<?= $tp_docu["id_tp_docu"] ?>"><?= $text ?></option>
                                                <?php endforeach ?>
                                            <?php endif ?>
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
                                <div class="col-md-12 row">
                                    <div class="col-md-3 my-2">
                                        <label>Nombres<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" readonly
                                            autocomplete="off" required>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Apellidos<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" readonly
                                            autocomplete="off" required>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Fecha de nacimiento</label>
                                        <input type="date" class="form-control" id="f_naci" name="f_naci"
                                            autocomplete="off">
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Estado civil</label>
                                        <select class="form-select" id="estado_civil" name="estado_civil">
                                            <option value="">Seleccione</option>
                                            <option value="Casado(a)">Casado(a)</option>
                                            <option value="Conviviente">Conviviente</option>
                                            <option value="Separado(a)">Separado(a)</option>
                                            <option value="Viudo(a)">Viudo(a)</option>
                                            <option value="Soltero(a)">Soltero(a)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Género</label>
                                        <select class="form-select" id="genero" name="genero">
                                            <option value="">Seleccione</option>
                                            <option value="MASCULINO">MASCULINO</option>
                                            <option value="FEMENINO">FEMENINO</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Dirección<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                            autocomplete="off" value="S/D" required>
                                    </div>
                                    <?php $col = "col-md-3";
                                    include("views/templates/components/cmp_select_ubigeo.php") ?>
                                    <div class="col-md-3 my-2">
                                        <label>Celular<span class="requiredField">*</span></label>
                                        <input type="text" class="form-control" id="celular" name="celular"
                                            minlength="9" maxlength="9" autocomplete="off" required>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Usuario | Correo E.<span class="requiredField">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            autocomplete="off" required>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Contraseña</label>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" id="pass" name="pass"
                                                autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary" id="generar_pass"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Generar contraseña">
                                                <i class="fas fa-key"></i> <!-- Icono de FontAwesome -->
                                            </button>
                                            <button class="btn btn-outline-secondary d-none" type="button" disabled
                                                id="generrar_passLoad">
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <?php $col = "col-md-3";
                                    include("views/templates/components/cmp_select_terminal.php") ?>
                                    <div class="col-md-3 my-2">
                                        <label>Nacionalidad <span class="requiredField">*</span></label>
                                        <select id="nacionalidad" name="nacionalidad" required autocomplete="new-password"></select>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Tipo de usuario<span class="requiredField">*</span></label>
                                        <select class="form-select" name="tp_usuario" id="tp_usuario" required>
                                            <?php if (in_array($data_usuario["tp_usuario"], ["SOPORTE", "DESARROLLO", "ADMINISTRADOR"])): ?>
                                                <option value="2">ADMINISTRADOR(A)</option>
                                            <?php endif ?>
                                            <option value="3">VENDEDOR(A)</option>
                                            <option value="8">TERRAMOZA(O)</option>
                                            <option value="11">AYUDANTE</option>
                                            <option value="12">ASISTENTE</option>
                                            <option value="13">OTROS</option>
                                            <option value="14">COMISIONISTA 1</option>
                                            <option value="15">COMISIONISTA 2</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 my-2">
                                        <label>Estado<span class="requiredField">*</span></label>
                                        <select class="form-select" name="estado" id="estado" required>
                                            <option value="1">HABILITADO</option>
                                            <option value="0">DESHABILITADO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PERMISOS -->
                        <div class="tab-pane fade" id="permisos" role="tabpanel" aria-labelledby="tab_permisos"
                            tabindex="0">
                            <!-- ==================== ACCESO A MÓDULOS ==================== -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        $menus = [];
                                        $modulosHijos = [];
                                        $modulosRaiz = [];

                                        foreach ($this->modulos as $modulo) {
                                            if ($modulo["tipo"] == "MENU") {
                                                $menus[$modulo["id_modulo"]] = $modulo;
                                            } elseif (empty($modulo["id_modulo_padre"])) {
                                                $modulosRaiz[] = $modulo;
                                            } else {
                                                $modulosHijos[$modulo["id_modulo_padre"]][] = $modulo;
                                            }
                                        }
                                        ?>

                                        <!-- MÓDULOS SIN MENÚ -->
                                        <?php if (!empty($modulosRaiz)): ?>
                                            <div class="col-lg-6 mb-4">
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <i class="fa-solid fa-layer-group text-primary me-2"></i>
                                                        General
                                                    </div>
                                                    <div class="permission-group-body">
                                                        <div class="row">
                                                            <?php foreach ($modulosRaiz as $modulo): ?>
                                                                <div class="col-6">
                                                                    <div class="form-check permission-check">
                                                                        <input class="form-check-input permiso-modulo" type="checkbox"
                                                                            id="<?= $modulo["campo_permiso"] ?>">
                                                                        <label class="form-check-label"
                                                                            for="<?= $modulo["campo_permiso"] ?>">
                                                                            <?= $modulo["nombre"] ?>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- MENÚS -->
                                        <?php foreach ($menus as $idMenu => $menu): ?>
                                            <?php
                                            $hijos = $modulosHijos[$idMenu] ?? [];

                                            if (count($hijos) == 0) {
                                                continue;
                                            }
                                            ?>
                                            <div class="col-lg-6 mb-4">
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <i class="<?= $menu["icono"] ?> text-primary me-2"></i>
                                                        <?= $menu["nombre"] ?>
                                                    </div>

                                                    <div class="permission-group-body">
                                                        <div class="row">
                                                            <?php foreach (($modulosHijos[$idMenu] ?? []) as $modulo): ?>
                                                                <div class="col-6">
                                                                    <div class="form-check permission-check">
                                                                        <input class="form-check-input permiso-modulo" type="checkbox"
                                                                            id="<?= $modulo["campo_permiso"] ?>">
                                                                        <label class="form-check-label"
                                                                            for="<?= $modulo["campo_permiso"] ?>">
                                                                            <?= $modulo["nombre"] ?>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- ==================== PERMISOS ESPECIALES ==================== -->

                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 pb-0">
                                    <div class="d-flex align-items-center">
                                        <div class="permission-icon bg-warning-subtle text-warning me-3">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Permisos especiales</h5>
                                            <small class="text-muted">
                                                Acciones avanzadas que requieren autorización.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="p_anular_comprobante">
                                                <label class="form-check-label" for="p_anular_comprobante">
                                                    Anular comprobantes
                                                </label>
                                            </div>

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="p_anular_notaventa">
                                                <label class="form-check-label" for="p_anular_notaventa">
                                                    Anular notas de venta
                                                </label>
                                            </div>

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="p_posponer_pasaje">
                                                <label class="form-check-label" for="p_posponer_pasaje">
                                                    Posponer pasajes
                                                </label>
                                            </div>

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="p_cambiar_asiento">
                                                <label class="form-check-label" for="p_cambiar_asiento">
                                                    Cambiar asiento (Pasajes)
                                                </label>
                                            </div>

                                        </div>

                                        <div class="col-lg-6">

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_m_precio">
                                                <label class="form-check-label" for="permiso_m_precio">
                                                    Modificar precio pasaje
                                                </label>
                                            </div>

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="p_enviar_resumen">
                                                <label class="form-check-label" for="p_enviar_resumen">
                                                    Enviar resumen
                                                </label>
                                            </div>

                                            <div class="form-check permission-check" id="ContraMaestraParent">
                                                <input class="form-check-input" type="checkbox" id="p_contra_maestra">
                                                <label class="form-check-label" for="p_contra_maestra">
                                                    Contraseñas maestras
                                                </label>
                                            </div>

                                            <div class="form-check permission-check">
                                                <input class="form-check-input" type="checkbox" id="chk_p_desbloquear_reservado">
                                                <label class="form-check-label" for="chk_p_desbloquear_reservado">
                                                    Desbloquear reserva (Bajo responsabilidad)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btnCancel" id="button_cancel"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btnSave" id="button_save">Guardar</button>
                        <button class="btn btnSave d-none" type="button" id="button_loadSave" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardando...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>