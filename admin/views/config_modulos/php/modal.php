<div class="modal fade" id="modalModulo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modulo_title">
                    <i class="fa-solid fa-cubes me-2"></i>
                    Nuevo Módulo
                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formModulo">
                <input type="hidden" id="id_modulo" name="id_modulo">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Tipo -->
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="MODULO">Módulo</option>
                                <option value="MENU">Menú</option>
                            </select>
                        </div>

                        <!-- Padre -->
                        <div class="col-md-8">
                            <label class="form-label">Menú Padre</label>
                            <select class="form-select" id="id_modulo_padre" name="id_modulo_padre">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre">
                        </div>

                        <!-- Clave -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Clave
                            </label>
                            <input type="text" class="form-control text-uppercase" id="clave" name="clave"
                                placeholder="Ej. PASAJE">
                        </div>

                        <!-- Controlador -->
                        <div class="col-md-6 controlador">
                            <label class="form-label">
                                Controlador
                            </label>
                            <input type="text" class="form-control" id="controlador" name="controlador"
                                placeholder="Ej. pasaje">
                        </div>

                        <!-- Campo Permiso -->
                        <div class="col-md-6 controlador">
                            <label class="form-label">
                                Campo permiso
                            </label>
                            <input type="text" class="form-control" id="campo_permiso" name="campo_permiso"
                                placeholder="Ej. p_pasaje">
                        </div>

                        <!-- Icono -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Icono
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="previewIcon" class="fa-solid fa-cube"></i>
                                </span>
                                <input type="text" class="form-control" id="icono" name="icono"
                                    placeholder="fa-solid fa-cube">
                                <button class="btn btn-outline-secondary" type="button" id="btnBuscarIcono">
                                    <i class="fa-solid fa-icons"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Orden -->
                        <div class="col-md-2">
                            <label class="form-label">
                                Orden
                            </label>
                            <input type="number" class="form-control" id="orden" name="orden" min="1">
                        </div>

                        <!-- Visible -->
                        <div class="col-md-2">
                            <label class="form-label">
                                Visible
                            </label>

                            <select class="form-select" id="visible_menu" name="visible_menu">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Estado
                            </label>

                            <select class="form-select" id="estado" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-12">
                            <label class="form-label">
                                Descripción
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">
                        Cancelar
                    </button>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIconos" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-icons me-2"></i>
                    Seleccionar icono
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="buscarIcono" class="form-control" placeholder="Buscar icono...">
                    </div>
                </div>
                <div class="accordion" id="accordionIconos">
                </div>
            </div>
        </div>
    </div>
</div>