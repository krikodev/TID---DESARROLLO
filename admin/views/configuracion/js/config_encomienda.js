import * as instance from "../../../public/js/instance.js";
document.getElementById("link_config").classList.add("active")

document.addEventListener("DOMContentLoaded", function () {

    // ── Referencias del formulario principal ──
    var form = document.getElementById("form_config_encomienda");
    var buttonSave = document.getElementById("button_save");
    var buttonLoadSave = document.getElementById("button_loadSave");
    var numeroRecibo = document.getElementById("numero_recibo");
    var numeroReciboCheck = document.getElementById("numero_recibo_check");
    var defaultTipoPrecio = document.getElementById("default_tipo_precio");
    var cambiarNNV = document.getElementById("cambiar_n_NV");
    var cambiarNNVCheck = document.getElementById("cambiar_n_NV_check");
    var nombreNotaVenta = document.getElementById("nombre_nota_venta");
    var detectarPagoBloque = document.getElementById("detectar_pago_bloque");
    var detectarPagoBloqueCheck = document.getElementById("detectar_pago_bloque_check");
    var nrocelCliente = document.getElementById("nrocel_cliente");
    var nrocelClienteCheck = document.getElementById('nrocel_cliente_check');
    var envioErroneo = document.getElementById("envio_erroneo");
    var envioErroneoCheck = document.getElementById('envio_erroneo_check');
    var rotuladoE = document.getElementById("rotulado_e");
    var rotuladoECheck = document.getElementById('rotulado_e_check');
    var loader = document.getElementById("loader");

    let embarque_general = document.getElementById('embarque_general');
    let embarque_general_check = document.getElementById('embarque_general_check');
    let impresion_ecompleta = document.getElementById('impresion_ecompleta');
    let impresion_ecompleta_check = document.getElementById('impresion_ecompleta_check');
    let mostrar_direccion_completa = document.getElementById('mostrar_direccion_completa');
    let mostrar_direccion_completa_check = document.getElementById('mostrar_direccion_completa_check');
    let mostrar_tracking = document.getElementById('mostrar_tracking');
    let mostrar_tracking_check = document.getElementById('mostrar_tracking_check');
    let mostrar_vendedor = document.getElementById('mostrar_vendedor');
    let mostrar_vendedor_check = document.getElementById('mostrar_vendedor_check');
    let datos_destinatario = document.getElementById('datos_destinatario');
    let datos_destinatario_check = document.getElementById('datos_destinatario_check');
    let origen_destino = document.getElementById('origen_destino');
    let origen_destino_check = document.getElementById('origen_destino_check');

    instance.Toast.info_lateral(
        "Atención!",
        "Recuerde que los cambios realizados en las configuraciones afectarán en el comportamiento del sistema."
    );

    $('#termscond_encomienda').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strikethrough']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontsize', ['fontsize']],
            ['font', ['fontname']],
            ['height', ['height']]
        ],
        lineHeights: ['0', '0.3', '0.5', '0.7', '1.0', '1.2', '1.5', '2.0', '3.0'],
        placeholder: 'Hello Bootstrap 5',
        tabsize: 2,
        height: 100
    });

    // ── Checkboxes ──
    numeroReciboCheck.addEventListener("change", function () {
        numeroRecibo.value = this.checked ? "1" : "0";
    });

    cambiarNNVCheck.addEventListener("change", function () {
        cambiarNNV.value = this.checked ? "1" : "0";
        nombreNotaVenta.disabled = !this.checked;
        if (!this.checked) nombreNotaVenta.value = "";
    });

    detectarPagoBloqueCheck.addEventListener("change", function () {
        detectarPagoBloque.value = this.checked ? "1" : "0";
    });

    nrocelClienteCheck.addEventListener("change", function () {
        nrocelCliente.value = this.checked ? "1" : "0";
    });

    envioErroneoCheck.addEventListener("change", function () {
        envio_erroneo.value = this.checked ? "1" : "0";
    });

    rotuladoECheck.addEventListener("change", function () {
        rotulado_e.value = this.checked ? "1" : "0";
    });

    embarque_general_check.addEventListener('change', function () {
        embarque_general.value = this.checked ? 1 : 0;
    });

    impresion_ecompleta_check.addEventListener('change', function () {
        impresion_ecompleta.value = this.checked ? 1 : 0;
    });

    mostrar_direccion_completa_check.addEventListener('change', function () {
        mostrar_direccion_completa.value = this.checked ? 1 : 0;

        if (this.checked && origen_destino.value == "0") {
            origen_destino.value = "1";
            origen_destino_check.checked = true;
        }

        if (!this.checked) {
            if (origen_destino.value == "1") {
            }
        }
    });

    mostrar_tracking_check.addEventListener('change', function () {
        mostrar_tracking.value = this.checked ? 1 : 0;
    });

    mostrar_vendedor_check.addEventListener('change', function () {
        mostrar_vendedor.value = this.checked ? 1 : 0;
    });

    datos_destinatario_check.addEventListener('change', function () {
        datos_destinatario.value = this.checked ? 1 : 0;
    });

    origen_destino_check.addEventListener('change', function () {
        origen_destino.value = this.checked ? 1 : 0;

        if (this.checked && mostrar_direccion_completa.value == "0") {
            mostrar_direccion_completa.value = "1";
            mostrar_direccion_completa_check.checked = true;
        }

        if (!this.checked && mostrar_direccion_completa.value == "1") {
            mostrar_direccion_completa.value = "0";
            mostrar_direccion_completa_check.checked = false;
        }
    });

    // ── Carga config existente ──
    loader.classList.remove('d-none');

    fetch(instance._URL_ + 'configuracion/get_data_config_encomienda')
        .then(function (res) {
            if (!res.ok) throw new Error("Error de red");
            return res.json();
        })
        .then(function (data) {
            if (!data.success) return;
            var message = data.message;
            defaultTipoPrecio.value = message.default_tipo_precio;
            numeroRecibo.value = message.numero_recibo;
            numeroReciboCheck.checked = String(message.numero_recibo) === "1";
            cambiarNNV.value = message.cambiar_n_NV;
            cambiarNNVCheck.checked = String(message.cambiar_n_NV) === "1";
            nombreNotaVenta.value = message.nombre_nota_venta ? message.nombre_nota_venta : "";
            nombreNotaVenta.disabled = String(message.cambiar_n_NV) !== "1";
            detectarPagoBloque.value = message.detectar_pago_bloque;
            detectarPagoBloqueCheck.checked = String(message.detectar_pago_bloque) === "1";
            nrocelCliente.value = message.nrocel_cliente;
            nrocelClienteCheck.checked = String(message.nrocel_cliente) === "1";
            envioErroneo.value = message.envio_erroneo;
            envioErroneoCheck.checked = String(message.envio_erroneo) === "1";
            rotuladoE.value = message.rotulado_e;
            rotuladoECheck.checked = String(message.rotulado_e) === "1";
            if (message.termscond_encomienda) {
                $('#termscond_encomienda').summernote('code', message.termscond_encomienda);
            }

            embarque_general.value = message.embarque_general
            impresion_ecompleta.value = message.impresion_ecompleta
            mostrar_direccion_completa.value = message.mostrar_direccion_completa
            mostrar_tracking.value = message.mostrar_tracking
            mostrar_vendedor.value = message.mostrar_vendedor
            datos_destinatario.value = message.datos_destinatario || 0;
            origen_destino.value = message.origen_destino || 0;

            embarque_general_check.checked = embarque_general.value == "1" ? true : false;
            impresion_ecompleta_check.checked = impresion_ecompleta.value == "1" ? true : false;
            mostrar_direccion_completa_check.checked = mostrar_direccion_completa.value == "1" ? true : false;
            mostrar_tracking_check.checked = mostrar_tracking.value == "1" ? true : false;
            mostrar_vendedor_check.checked = mostrar_vendedor.value == "1" ? true : false;
            datos_destinatario_check.checked = datos_destinatario.value == "1" ? true : false;
            origen_destino_check.checked = origen_destino.value == "1" ? true : false;
            nrocel_cliente_check.checked = nrocel_cliente.value == "1" ? true : false;
            envio_erroneo_check.checked = envio_erroneo.value == "1" ? true : false;
            rotulado_e_check.checked = rotulado_e.value == "1" ? true : false;
        })
        .catch(function () {
            Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar la configuración." });
        })
        .finally(function () {
            loader.classList.add('d-none');
        });

    // ── Submit principal — guarda config general + export en paralelo ──
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        var formData = new FormData(form);

        let terminos_encomienda = $('#termscond_encomienda').summernote('code');
        formData.append("termscond_encomienda", terminos_encomienda);

        var dataToValidate = [
            formData.get("default_tipo_precio"),
            formData.get("numero_recibo")
        ];

        if (cambiarNNV.value == "1") {
            dataToValidate.push(formData.get("nombre_nota_venta"));
        }

        if (!instance.Validate.validateData(dataToValidate)) {
            Swal.fire({ icon: "error", title: "Campos inválidos", text: "Rellene correctamente los campos." });
            return;
        }

        buttonSave.classList.add("d-none");
        buttonLoadSave.classList.remove("d-none");

        // Petición 1: config general del formulario
        var promesaConfig = new Promise(function (resolve, reject) {
            $.ajax({
                type: "POST",
                url: instance._URL_ + 'configuracion/register_config_encomienda',
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
                dataType: "json",
                success: function (res) { resolve(res); },
                error: function () { reject(new Error('Error de red en config')); }
            });
        });

        // Petición 2: config de exportación (solo si hay campos seleccionados)
        var promesaExport;
        if (ExportConfig.selected.length > 0) {
            var exportData = new FormData();
            exportData.append('config_tabla', 'config_encomienda');
            exportData.append('config_id', parseInt(document.getElementById('sel-perfil').value));
            exportData.append('modulo', document.getElementById('sel-modulo').value);
            exportData.append('campos', JSON.stringify(
                ExportConfig.selected.map(function (f, i) {
                    return {
                        campo: f.campo,
                        tabla: f.tabla,
                        label: f.customLabel ? f.customLabel : f.label,
                        orden: i + 1,
                        visible: 1
                    };
                })
            ));

            promesaExport = fetch(instance._URL_ + 'configuracion/save_config_campos', {
                method: 'POST',
                body: exportData
            }).then(function (r) { return r.json(); });
        } else {
            // Sin campos seleccionados — se omite silenciosamente
            promesaExport = Promise.resolve({ success: true });
        }

        Promise.all([promesaConfig, promesaExport])
            .then(function (results) {
                var resConfig = results[0];
                var resExport = results[1];

                // Si alguno falló, muestra error
                if (!resConfig.success) {
                    Swal.fire({ icon: "error", title: "Error", text: resConfig.message });
                    return;
                }
                if (!resExport.success) {
                    Swal.fire({ icon: "warning", title: "Config guardada", text: "Pero hubo un error al guardar los campos de exportación: " + resExport.message });
                    return;
                }

                Swal.fire({ icon: "success", title: "Operación exitosa", text: resConfig.message })
                    .then(function (result) {
                        if (result.isConfirmed) location.reload();
                    });
            })
            .catch(function (err) {
                console.error(err);
                Swal.fire({ icon: "error", title: "Error de red", text: "No se pudo guardar la configuración." });
            })
            .finally(function () {
                buttonSave.classList.remove("d-none");
                buttonLoadSave.classList.add("d-none");
            });
    });

    // ═══════════════════════════════════════════════════════════════
    // CONFIG EXPORTACIÓN — estado central
    // ═══════════════════════════════════════════════════════════════
    var ExportConfig = {
        fields: [],
        selected: [],
        dragSrc: null,
        isLoading: false,

        init: function (modulo, configId) {
            var self = this;
            self.setLoading(true);

            Promise.all([
                fetch(instance._URL_ + 'configuracion/get_campos_disponibles/' + modulo).then(function (r) { return r.json(); }),
                fetch(instance._URL_ + 'configuracion/get_config_campos/' + modulo + '/' + configId).then(function (r) { return r.json(); })
            ])
                .then(function (results) {
                    var camposRes = results[0];
                    var configRes = results[1];

                    if (camposRes.success) {
                        self.fields = camposRes.campos;
                    } else {
                        showToast('No se pudieron cargar los campos', 'error');
                    }

                    if (configRes.success && configRes.campos && configRes.campos.length) {
                        self.selected = configRes.campos.map(function (c) {
                            return {
                                campo: c.campo,
                                tabla: c.tabla,
                                label: c.label,
                                customLabel: c.label,
                                orden: c.orden
                            };
                        });
                    }
                })
                .catch(function (err) {
                    console.error('Error al inicializar:', err);
                    showToast('Error de conexión al cargar campos', 'error');
                })
                .finally(function () {
                    self.setLoading(false);
                    renderAvailable();
                    renderSelected();
                    updateCounts();
                });
        },

        setLoading: function (state) {
            this.isLoading = state;
            var container = document.getElementById('fields-container');
            if (state) {
                container.innerHTML =
                    '<div class="d-flex flex-column align-items-center gap-2 py-5 text-center" style="color:var(--muted)">' +
                    '<div class="spinner-border spinner-border-sm" role="status"></div>' +
                    '<span style="font-size:.75rem">Cargando campos...</span>' +
                    '</div>';
            }
        }
    };

    // ── Botones del export via addEventListener (sin onclick en HTML) ──
    document.getElementById('btn-select-all').addEventListener('click', function () { selectAll(); });
    document.getElementById('btn-clear-all').addEventListener('click', function () { clearAll(); });
    document.getElementById('btn-preview-json').addEventListener('click', function () { previewJSON(); });

    document.getElementById('sel-modulo').addEventListener('change', function () {
        ExportConfig.selected = [];
        ExportConfig.init(
            document.getElementById('sel-modulo').value,
            document.getElementById('sel-perfil').value
        );
    });

    document.getElementById('search-field').addEventListener('input', function (e) {
        renderAvailable(e.target.value);
    });

    document.getElementById('drop-zone').addEventListener('dragover', function (e) {
        e.preventDefault();
        document.getElementById('drop-zone').classList.add('drag-over');
    });
    document.getElementById('drop-zone').addEventListener('dragleave', function () {
        document.getElementById('drop-zone').classList.remove('drag-over');
    });
    document.getElementById('drop-zone').addEventListener('drop', function () {
        document.getElementById('drop-zone').classList.remove('drag-over');
    });

    // ═══════════════════════════════════════════════════════════════
    // RENDER — campos disponibles (izquierda)
    // ═══════════════════════════════════════════════════════════════
    function renderAvailable(filter) {
        filter = filter || '';
        var container = document.getElementById('fields-container');
        if (ExportConfig.isLoading) return;

        container.innerHTML = '';
        var filterLow = filter.toLowerCase();

        var grupos = ExportConfig.fields
            .map(function (f) { return f.tabla; })
            .filter(function (v, i, arr) { return arr.indexOf(v) === i; });

        var totalVisible = 0;

        grupos.forEach(function (tabla) {
            var fields = ExportConfig.fields.filter(function (f) {
                return f.tabla === tabla && (
                    f.campo.toLowerCase().indexOf(filterLow) !== -1 ||
                    f.label.toLowerCase().indexOf(filterLow) !== -1
                );
            });
            if (!fields.length) return;

            totalVisible += fields.length;

            var cls = getTablaClass(tabla);
            var chip = getTablaChip(tabla);

            var grpEl = document.createElement('div');
            grpEl.className = 'table-group';
            grpEl.innerHTML =
                '<div class="table-group-label ' + cls + '">' +
                '<i class="bi bi-table"></i> ' + tabla +
                '</div>' +
                '<div class="field-list" id="list-' + tabla + '"></div>';
            container.appendChild(grpEl);

            var listEl = grpEl.querySelector('#list-' + tabla);

            fields.forEach(function (f) {
                var isSelected = ExportConfig.selected
                    .some(function (s) { return s.campo === f.campo && s.tabla === f.tabla; });

                var item = document.createElement('div');
                item.className = 'field-item' + (isSelected ? ' selected' : '');
                item.innerHTML =
                    '<div class="field-check">' +
                    (isSelected ? '<i class="bi bi-check-lg"></i>' : '') +
                    '</div>' +
                    '<span class="field-name">' + f.label + '</span>' +
                    '<span class="field-type" style="opacity:.5;font-size:.6rem">' + f.campo + '</span>' +
                    '<span class="tag-chip ' + cls + '">' + chip + '</span>';

                if (!isSelected) {
                    item.onclick = (function (field) {
                        return function () { addField(field); };
                    })(f);
                }

                listEl.appendChild(item);
            });
        });

        if (totalVisible === 0 && filter) {
            container.innerHTML =
                '<div class="text-center py-4" style="color:var(--muted);font-size:.78rem">' +
                '<i class="bi bi-search me-1"></i> Sin resultados para "<strong>' + filter + '</strong>"' +
                '</div>';
        }

        document.getElementById('count-available').textContent = totalVisible;
    }

    // ═══════════════════════════════════════════════════════════════
    // RENDER — campos seleccionados (derecha)
    // ═══════════════════════════════════════════════════════════════
    function renderSelected() {
        var list = document.getElementById('selected-list');
        var hint = document.getElementById('empty-hint');
        list.innerHTML = '';
        hint.style.display = ExportConfig.selected.length ? 'none' : 'flex';

        ExportConfig.selected.forEach(function (f, i) {
            var cls = getTablaClass(f.tabla);
            var chip = getTablaChip(f.tabla);
            var row = document.createElement('div');

            row.className = 'selected-field';
            row.draggable = true;
            row.dataset.index = i;
            row.innerHTML =
                '<div class="drag-handle"><span></span><span></span><span></span></div>' +
                '<span class="order-num">' + (i + 1) + '</span>' +
                '<span class="tag-chip ' + cls + '">' + chip + '</span>' +
                '<span class="sel-field-name" title="' + f.campo + '">' + f.campo + '</span>' +
                '<input class="sel-label-input" type="text"' +
                ' value="' + escapeAttr(f.customLabel) + '"' +
                ' placeholder="Etiqueta columna..."' +
                ' title="Etiqueta que aparecerá en el Excel" />' +
                '<button type="button" class="btn-remove" title="Quitar campo"><i class="bi bi-x-lg"></i></button>';

            row.querySelector('.sel-label-input')
                .addEventListener('input', (function (idx) {
                    return function (e) { ExportConfig.selected[idx].customLabel = e.target.value; };
                })(i));

            row.querySelector('.btn-remove')
                .addEventListener('click', (function (campo, tabla) {
                    return function () { removeField(campo, tabla); };
                })(f.campo, f.tabla));

            row.addEventListener('dragstart', (function (idx, el) {
                return function (e) {
                    ExportConfig.dragSrc = idx;
                    setTimeout(function () { el.classList.add('dragging'); }, 0);
                    e.dataTransfer.effectAllowed = 'move';
                };
            })(i, row));

            row.addEventListener('dragend', (function (el) {
                return function () { el.classList.remove('dragging'); };
            })(row));

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                document.querySelectorAll('.selected-field')
                    .forEach(function (r) { r.classList.remove('drag-target'); });
                row.classList.add('drag-target');
            });

            row.addEventListener('dragleave', function () { row.classList.remove('drag-target'); });

            row.addEventListener('drop', (function (idx) {
                return function (e) {
                    e.preventDefault();
                    row.classList.remove('drag-target');
                    var src = ExportConfig.dragSrc;
                    if (src === null || src === idx) return;
                    var moved = ExportConfig.selected.splice(src, 1)[0];
                    ExportConfig.selected.splice(idx, 0, moved);
                    ExportConfig.selected.forEach(function (s, x) { s.orden = x + 1; });
                    renderSelected();
                };
            })(i));

            list.appendChild(row);
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // ACCIONES
    // ═══════════════════════════════════════════════════════════════
    function addField(f) {
        var exists = ExportConfig.selected
            .some(function (s) { return s.campo === f.campo && s.tabla === f.tabla; });
        if (exists) return;
        ExportConfig.selected.push({
            campo: f.campo, tabla: f.tabla, label: f.label,
            customLabel: f.label, orden: ExportConfig.selected.length + 1
        });
        refresh();
    }

    function removeField(campo, tabla) {
        ExportConfig.selected = ExportConfig.selected
            .filter(function (s) { return !(s.campo === campo && s.tabla === tabla); });
        ExportConfig.selected.forEach(function (s, i) { s.orden = i + 1; });
        refresh();
    }

    function selectAll() {
        ExportConfig.fields.forEach(function (f) {
            var exists = ExportConfig.selected
                .some(function (s) { return s.campo === f.campo && s.tabla === f.tabla; });
            if (!exists) {
                ExportConfig.selected.push({
                    campo: f.campo, tabla: f.tabla, label: f.label,
                    customLabel: f.label, orden: ExportConfig.selected.length + 1
                });
            }
        });
        refresh();
    }

    function clearAll() {
        ExportConfig.selected = [];
        refresh();
    }

    function refresh() {
        renderSelected();
        renderAvailable(document.getElementById('search-field').value);
        updateCounts();
    }

    function updateCounts() {
        var n = ExportConfig.selected.length;
        document.getElementById('count-selected').textContent = n;
        document.getElementById('footer-count').textContent = n;
    }

    function previewJSON() {
        var modulo = document.getElementById('sel-modulo').value;
        var configId = document.getElementById('sel-perfil').value;
        var rows = ExportConfig.selected.map(function (f, i) {
            return {
                config_tabla: 'venta', config_id: parseInt(configId),
                modulo: modulo, campo: f.campo, label: f.customLabel,
                orden: i + 1, visible: 1
            };
        });
        document.getElementById('json-output').textContent = JSON.stringify(rows, null, 2);
        new bootstrap.Modal(document.getElementById('jsonModal')).show();
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════
    var TABLA_CLASS_MAP = {
        encomienda: 'encomienda', venta: 'ventas', usuario: 'usuario', programacion: 'programacion',
        tp_moneda: 'detalle', tp_servicio: 'detalle', tp_venta: 'detalle'
    };
    function getTablaClass(tabla) {
        if (!tabla) return 'detalle';
        return TABLA_CLASS_MAP[tabla] ? TABLA_CLASS_MAP[tabla] : 'detalle';
    }

    var TABLA_CHIP_MAP = {
        encomienda: 'ENC', venta: 'VTA', usuario: 'USR', programacion: 'PRG',
        tp_moneda: 'MON', tp_servicio: 'SRV', tp_venta: 'TPV'
    };
    function getTablaChip(tabla) {
        if (!tabla) return '???';
        return TABLA_CHIP_MAP[tabla] ? TABLA_CHIP_MAP[tabla] : tabla.substring(0, 3).toUpperCase();
    }
    function escapeAttr(str) {
        return String(str ? str : '')
            .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;').replace(/</g, '&lt;');
    }

    function showToast(msg, type) {
        type = type || 'success';
        var colors = { success: 'var(--accent2)', error: 'var(--danger)', warning: '#f0a500' };
        var color = colors[type] ? colors[type] : colors.success;
        var t = document.getElementById('toast');
        document.getElementById('toast-msg').textContent = msg;
        t.style.borderColor = color;
        t.style.color = color;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3500);
    }

    // ── Arranque ──
    ExportConfig.init(
        document.getElementById('sel-modulo').value,
        document.getElementById('sel-perfil').value
    );
});