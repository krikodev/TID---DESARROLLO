
import * as instance from "../../../public/js/instance.js";
document.getElementById("link_config").classList.add("active")

document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("form_config_pasaje");
    var buttonSave = document.getElementById("button_save");
    var buttonLoadSave = document.getElementById("button_loadSave");
    var termscond_pasaje = document.getElementById("termscond_pasaje");
    let l_terminales = document.getElementById('l_terminales');
    let l_terminales_check = document.getElementById('l_terminales_check');
    let l_usuarios = document.getElementById('l_usuarios');
    let l_usuarios_check = document.getElementById('l_usuarios_check');
    let manifiesto_sunat = document.getElementById('manifiesto_sunat');
    let tiempo_seleccion = document.getElementById('tiempo_seleccion');
    let manifiesto_sunat_check = document.getElementById('manifiesto_sunat_check');
    let venta_web = document.getElementById('venta_web');
    let venta_web_check = document.getElementById('venta_web_check');
    let serie_manifiesto = document.getElementById('serie_manifiesto');
    let correlativo_manifiesto = document.getElementById('correlativo_manifiesto');
    let num_aut_sunat = document.getElementById('num_aut_sunat');
    var loader = document.getElementById("loader");

    $('#termscond_pasaje').summernote({
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

    function radio_liquidacion_tipo(tipo) {

        if (tipo === 'l_terminales') {
            let el = document.getElementById('l_usuarios_check');
            el.checked = false;
            el.dispatchEvent(new Event('change'));
        }

        if (tipo === 'l_usuarios') {
            let el = document.getElementById('l_terminales_check');
            el.checked = false;
            el.dispatchEvent(new Event('change'));
        }
    }

    venta_web_check.addEventListener('change', function () {
        venta_web.value = this.checked ? 1 : 0;
    });

    l_terminales_check.addEventListener('change', function () {
        l_terminales.value = this.checked ? '1' : '0';
        if (this.checked) radio_liquidacion_tipo('l_terminales');
    });

    l_usuarios_check.addEventListener('change', function () {
        l_usuarios.value = this.checked ? '1' : '0';
        if (this.checked) radio_liquidacion_tipo('l_usuarios');
    });

    manifiesto_sunat_check.addEventListener('change', function () {
        manifiesto_sunat.value = this.checked ? 1 : 0;
    });

    loader.classList.remove('d-none');

    fetch(instance._URL_ + 'configuracion/get_data_config_pasaje')
        .then(function (res) {
            if (!res.ok) throw new Error("Error de red");
            return res.json();
        })
        .then(function (data) {
            if (!data.success) return;
            var message = data.message;
            if (message.termscond_pasaje) {
                $('#termscond_pasaje').summernote('code', message.termscond_pasaje);
            }
            l_terminales.value = message.l_terminales
            l_usuarios.value = message.l_usuarios
            manifiesto_sunat.value = message.manifiesto_sunat
            tiempo_seleccion.value = message.tiempo_seleccion
            venta_web.value = message.venta_web

            venta_web_check.checked = venta_web.value == "1" ? true : false;
            l_terminales_check.checked = l_terminales.value == "1" ? true : false;
            l_usuarios_check.checked = l_usuarios.value == "1" ? true : false;
            manifiesto_sunat_check.checked = manifiesto_sunat.value == "1" ? true : false;
        })
        .catch(function () {
            Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar la configuración." });
        })
        .finally(function () {
            loader.classList.add('d-none');
        });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        let terminos_pasaje = $('#termscond_pasaje').summernote('code');

        var formData = new FormData(form);
        formData.append("termscond_pasaje", terminos_pasaje);


        buttonSave.classList.add("d-none");
        buttonLoadSave.classList.remove("d-none");

        fetch(instance._URL_ + 'configuracion/register_config_pasaje', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Error: ' + response.status);
                return response.json();
            })
            .then(data => {
                Swal.fire({ icon: "success", title: "Operación exitosa", text: data.message })
                    .then(function (result) {
                        if (result.isConfirmed) location.reload();
                    });
            })
            .catch(error => {
                console.error(error);
                Swal.fire({ icon: "error", title: "Error de red", text: "No se pudo guardar la configuración." });
            })
            .finally(function () {
                buttonSave.classList.remove("d-none");
                buttonLoadSave.classList.add("d-none");
            });
    });

    //Apartado de manifiesto serie y demas
    let manifiestos = [];
    let filaEnEdicion = null; // id_serie_manifiesto (o 'new_...') de la fila en edición

    const tbodyManifiesto = document.getElementById('tabla_manifiesto_body');
    const emptyStateManifiesto = document.getElementById('manifiesto_empty');
    const btnAddManifiesto = document.getElementById('btn_add_manifiesto');
    const loaderManifiesto = document.getElementById('loader_manifiesto'); // opcional, si tienes un loader propio para la tabla

    function cargarSeriesManifiesto() {
        if (loaderManifiesto) loaderManifiesto.classList.remove('d-none');

        fetch(instance._URL_ + 'configuracion/get_series_manifiesto')
            .then(res => {
                if (!res.ok) throw new Error('Error de red');
                return res.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error');
                manifiestos = data.message || [];
                renderManifiesto();
            })
            .catch(() => {
                Swal.fire({ icon: "error", title: "Error", text: "No se pudieron cargar las series de manifiesto." });
            })
            .finally(() => {
                if (loaderManifiesto) loaderManifiesto.classList.add('d-none');
            });
    }

    function renderManifiesto() {
        tbodyManifiesto.innerHTML = '';

        if (manifiestos.length === 0) {
            emptyStateManifiesto.classList.remove('d-none');
        } else {
            emptyStateManifiesto.classList.add('d-none');
        }

        manifiestos.forEach(row => {
            tbodyManifiesto.appendChild(crearFilaManifiesto(row));
        });
    }

    function crearFilaManifiesto(row) {
        const tr = document.createElement('tr');
        tr.dataset.id = row.id_serie_manifiesto;
        const enEdicion = filaEnEdicion === row.id_serie_manifiesto;
        const activo = row.estado === 'ACTIVO';

        tr.innerHTML = `
        <td>
            <input type="text" class="cell-input" data-field="serie"
                   value="${escapeHtml(row.serie)}" ${enEdicion ? '' : 'disabled'}>
        </td>
        <td>
            <input type="number" min="0" class="cell-input" data-field="correlativo"
                   value="${row.correlativo}" ${enEdicion ? '' : 'disabled'}>
        </td>
        <td>
            <input type="text" class="cell-input" data-field="numero_autorizacion"
                   value="${escapeHtml(row.numero_autorizacion)}" ${enEdicion ? '' : 'disabled'}>
        </td>
        <td>
            ${enEdicion ? `
                <select class="cell-input" data-field="estado">
                    <option value="ACTIVO" ${activo ? 'selected' : ''}>Activo</option>
                    <option value="INACTIVO" ${!activo ? 'selected' : ''}>Inactivo</option>
                </select>
            ` : `
                <span class="badge-estado ${activo ? 'badge-activo' : 'badge-inactivo'}">
                    ${activo ? 'Activo' : 'Inactivo'}
                </span>
            `}
        </td>
        <td>
            <div class="fila-actions">
                ${enEdicion ? `
                    <button type="button" class="btn-guardar-fila" title="Guardar" data-action="save">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button type="button" class="btn-cancelar-fila" title="Cancelar" data-action="cancel">
                        <i class="bi bi-x-lg"></i>
                    </button>
                ` : `
                    <button type="button" class="btn-editar" title="Editar" data-action="edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn-eliminar" title="Eliminar" data-action="delete">
                        <i class="bi bi-trash"></i>
                    </button>
                `}
            </div>
        </td>
    `;

        tr.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => onActionManifiesto(btn.dataset.action, row.id_serie_manifiesto, tr));
        });

        return tr;
    }

    function onActionManifiesto(action, id, tr) {
        const row = manifiestos.find(r => r.id_serie_manifiesto === id);

        if (action === 'edit') {
            filaEnEdicion = id;
            renderManifiesto();
        }

        if (action === 'cancel') {
            if (row && row.esNueva) {
                manifiestos = manifiestos.filter(r => r.id_serie_manifiesto !== id);
            }
            filaEnEdicion = null;
            renderManifiesto();
        }

        if (action === 'save') {
            const serie = tr.querySelector('[data-field="serie"]').value.trim();
            const correlativo = parseInt(tr.querySelector('[data-field="correlativo"]').value, 10) || 0;
            const numeroAutorizacion = tr.querySelector('[data-field="numero_autorizacion"]').value.trim();
            const estado = tr.querySelector('[data-field="estado"]').value;

            if (!serie) {
                Swal.fire({ icon: "warning", title: "Falta la serie", text: "La serie es obligatoria." });
                return;
            }

            const payload = {
                serie: serie,
                correlativo: correlativo,
                numero_autorizacion: numeroAutorizacion,
                estado: estado
            };

            if (row.esNueva) {
                guardarNuevaSerie(payload, row, id);
            } else {
                payload.id_serie_manifiesto = row.id_serie_manifiesto;
                actualizarSerie(payload, row);
            }
        }

        if (action === 'delete') {
            if (row.esNueva) {
                manifiestos = manifiestos.filter(r => r.id_serie_manifiesto !== id);
                renderManifiesto();
                return;
            }

            Swal.fire({
                icon: "warning",
                title: "¿Eliminar serie?",
                text: `Se eliminará la serie "${row.serie}" de forma permanente.`,
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    eliminarSerie(row);
                }
            });
        }
    }

    function guardarNuevaSerie(payload, row, tempId) {
        const formData = new FormData();
        Object.keys(payload).forEach(key => formData.append(key, payload[key]));

        fetch(instance._URL_ + 'configuracion/add_serie_manifiesto', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error');

                row.id_serie_manifiesto = data.id_serie_manifiesto;
                row.serie = payload.serie;
                row.correlativo = payload.correlativo;
                row.numero_autorizacion = payload.numero_autorizacion;
                row.estado = payload.estado;
                delete row.esNueva;

                filaEnEdicion = null;
                renderManifiesto();
                Swal.fire({ icon: "success", title: "Serie registrada", timer: 1200, showConfirmButton: false });
            })
            .catch(() => {
                Swal.fire({ icon: "error", title: "Error", text: "No se pudo registrar la serie." });
            });
    }

    function actualizarSerie(payload, row) {
        const formData = new FormData();
        Object.keys(payload).forEach(key => formData.append(key, payload[key]));

        fetch(instance._URL_ + 'configuracion/update_serie_manifiesto', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error');

                row.serie = payload.serie;
                row.correlativo = payload.correlativo;
                row.numero_autorizacion = payload.numero_autorizacion;
                row.estado = payload.estado;

                filaEnEdicion = null;
                renderManifiesto();
                Swal.fire({ icon: "success", title: "Serie actualizada", timer: 1200, showConfirmButton: false });
            })
            .catch(() => {
                Swal.fire({ icon: "error", title: "Error", text: "No se pudo actualizar la serie." });
            });
    }

    function eliminarSerie(row) {
        const formData = new FormData();
        formData.append('id_serie_manifiesto', row.id_serie_manifiesto);

        fetch(instance._URL_ + 'configuracion/delete_serie_manifiesto', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error');

                manifiestos = manifiestos.filter(r => r.id_serie_manifiesto !== row.id_serie_manifiesto);
                renderManifiesto();
                Swal.fire({ icon: "success", title: "Serie eliminada", timer: 1200, showConfirmButton: false });
            })
            .catch(() => {
                Swal.fire({ icon: "error", title: "Error", text: "No se pudo eliminar la serie." });
            });
    }

    btnAddManifiesto.addEventListener('click', () => {
        if (filaEnEdicion !== null) {
            Swal.fire({ icon: "info", title: "Fila pendiente", text: "Termina de guardar o cancelar la fila actual antes de agregar otra." });
            return;
        }
        const tempId = 'new_' + Date.now();
        manifiestos.push({
            id_serie_manifiesto: tempId,
            serie: '',
            correlativo: 0,
            numero_autorizacion: '',
            estado: 'ACTIVO',
            esNueva: true
        });
        filaEnEdicion = tempId;
        renderManifiesto();
    });

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    cargarSeriesManifiesto();
});