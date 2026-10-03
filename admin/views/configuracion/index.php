<div class="app-wrapper">
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="settings-wrapper">

            <div class="row g-3">

                <!-- Sistema -->
                <div class="col-12 col-md-6 section-col">
                    <div class="section-card amber h-100">
                        <p class="section-title">⚙ Sistema</p>

                        <a class="setting-item" href="<?= URL ?>configuracion/config_general ">
                            <div class="item-icon"><i class="bi bi-sliders"></i></div>
                            <div class="item-text">
                                <div class="item-title">Configuración inicial</div>
                                <div class="item-desc">Características, opciones, otros.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>
                        <a class="setting-item" href="<?= URL ?>configuracion/config_pasaje">
                            <div class="item-icon"> <i class="fa-light fa-route"></i>
                            </div>
                            <div class="item-text">
                                <div class="item-title">Pasaje</div>
                                <div class="item-desc">Preferencias para el modulo de pasajes</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>

                        <a class="setting-item" href="<?= URL ?>configuracion/config_encomienda">
                            <div class="item-icon"><i class="fa-light fa-boxes-packing"></i></div>
                            <div class="item-text">
                                <div class="item-title">Encomienda</div>
                                <div class="item-desc">Preferencias para modulo de encomiendas.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>
                        <a class="setting-item" href="<?= URL ?>configuracion/config_facturador">
                            <div class="item-icon"><i class="fa-light fa-sliders"></i></i></div>
                            <div class="item-text">
                                <div class="item-title">Facturador</div>
                                <div class="item-desc">Preferencias para el modulo de facturador</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>
                        <a class="setting-item" href="<?= URL ?>configuracion/config_cotizacion">
                            <div class="item-icon"><i class="fa-light fa-file-signature"></i></div>
                            <div class="item-text">
                                <div class="item-title">Cotizaciones</div>
                                <div class="item-desc">Preferencias para el modulo de cotizaciones.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="col-12 col-md-6 section-col">
                    <div class="section-card violet h-100">
                        <p class="section-title">◈ Empresa</p>

                        <a class="setting-item" href="<?= URL ?>empresa">
                            <div class="item-icon"><i class="bi bi-building"></i></div>
                            <div class="item-text">
                                <div class="item-title">Datos de la empresa</div>
                                <div class="item-desc">Modificar los datos de la empresa.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>

                        <a class="setting-item" href="<?= URL ?>personal">
                            <div class="item-icon"><i class="bi bi-people"></i></div>
                            <div class="item-text">
                                <div class="item-title">Usuarios / Roles</div>
                                <div class="item-desc">Creación, modificación.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>

                        <a class="setting-item" onclick="showToast('Tipo de documentos')">
                            <div class="item-icon"><i class="bi bi-file-earmark-text"></i></div>
                            <div class="item-text">
                                <div class="item-title">Tipo de documentos</div>
                                <div class="item-desc">Modificar los tipos de documentos.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>
                        <div class="item-divider"></div>

                        <a class="setting-item" href="<?= URL ?>medio_pago">
                            <div class="item-icon"><i class="bi bi-credit-card"></i></div>
                            <div class="item-text">
                                <div class="item-title">Tipos de pago</div>
                                <div class="item-desc">Modificar los tipos de pagos.</div>
                            </div>
                            <i class="bi bi-chevron-right item-arrow"></i>
                        </a>

                    </div>
                </div>

            </div>
        </div>

        <div class="toast-box" id="toast"></div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            let toastTimer;

            function showToast(name) {
                const el = document.getElementById('toast');
                el.textContent = `Abriendo: ${name}`;
                el.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => el.classList.remove('show'), 2000);
            }
        </script>
    </div>
</div>