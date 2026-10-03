import * as instance from "../../../public/js/instance.js";
import { ICONOS } from "../js/iconos.js";

document.getElementById("link_config_modulos").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {
    const table = $('#table_modulo').DataTable({
        ajax: {
            url: $('#url').val() + 'config_modulos/dataTable',
            method: 'POST',
            beforeSend: function () {
                $('#loader').removeClass('d-none');
            },
            complete: function () {
                $('#loader').addClass('d-none');
            }
        },

        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        ordering: false,
        searching: true,
        dom: 'rtip',

        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],

        columns: [

            {
                data: "orden",
                className: "text-center",
                render: function (data) {
                    return `<span class="fw-semibold">${data}</span>`;
                }
            },

            {
                data: "icono",
                className: "text-center",
                orderable: false,
                render: function (data) {

                    if (!data)
                        return '<span class="text-muted">—</span>';

                    return `<i class="${data} fs-5"></i>`;
                }
            },

            {
                data: "nombre"
            },

            {
                data: "clave",
                render: data => data || '<span class="text-muted">—</span>'
            },

            {
                data: "tipo",
                className: "text-center",
                render: function (data) {

                    if (data == 'MENU') {

                        return `<span class="badge bg-primary">MENÚ</span>`;

                    }

                    return `<span class="badge bg-success">MÓDULO</span>`;

                }
            },

            {
                data: "modulo_padre",
                render: data => data || '<span class="text-muted">—</span>'
            },

            {
                data: "controlador",
                render: data => data || '<span class="text-muted">—</span>'
            },
            {
                data: "campo_permiso",
                render: data => data || '<span class="text-muted">—</span>'
            },
            {
                data: "visible_menu",
                className: "text-center",
                render: function (data) {

                    if (parseInt(data) === 1) {

                        return `<span class="badge bg-success">Sí</span>`;

                    }

                    return `<span class="badge bg-secondary">No</span>`;

                }
            },

            {
                data: "estado",
                className: "text-center",
                render: function (data) {

                    if (parseInt(data) === 1) {

                        return `<span class="badge bg-success">Activo</span>`;

                    }

                    return `<span class="badge bg-danger">Inactivo</span>`;

                }
            },

            {
                data: "id_modulo",
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {

                    return `
                    <div class="d-flex justify-content-center gap-2">
                     <button class="btn-action-circle btn-edit-circle btnEditarModulo" data-id="${data}" title="Editar módulo">
                       <i class="fa-solid fa-pen"></i>
                     </button>

                      <button class="btn-action-circle btn-delete-circle btnEliminarModulo" data-id="${data}" title="Eliminar módulo">
                       <i class="fa-solid fa-trash-can"></i>
                      </button>
                     </div>
                    `;
                }
            }

        ],

        language: {
            url: './public/plugins/datatable/language/es_es.json'
        },

        deferRender: true,
        pageLength: 20,
        stateSave: false
    });

    $("#btnBuscarIcono").click(function () {
        $("#buscarIcono").val("");
        cargarIconos();
        $("#modalIconos").modal("show");
    });
    function cargarIconos(busqueda = "") {

        let html = "";
        ICONOS.forEach(function (grupo, index) {
            const iconos = grupo.iconos.filter(function (icono) {
                return icono.toLowerCase()
                    .includes(busqueda.toLowerCase());
            });

            if (!iconos.length)
                return;

            html += `
            <div class="accordion-item">

            <h2 class="accordion-header">

                <button
                    class="accordion-button ${index == 0 ? "" : "collapsed"}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#cat_${index}">

                    ${grupo.categoria}

                </button>
            </h2>

            <div
                id="cat_${index}"
                class="accordion-collapse collapse ${index == 0 ? "show" : ""}">
                <div class="accordion-body">
                    <div class="row g-3">
            `;
            iconos.forEach(function (icono) {
                html += `
            <div class="col-md-2 col-3">
                <div
                    class="icono-item mx-auto"
                    data-icono="${icono}"
                    title="${icono}">
                    <i class="${icono}"></i>
                </div>
            </div>
            `;
            });
            html += `
                    </div>
                </div>
            </div>
        </div>`;
        });
        $("#accordionIconos").html(html);

    }

    $("#buscarIcono").on("keyup", function () {
        cargarIconos($(this).val());
    });

    $(document).on("click", ".icono-item", function () {
        const icono = $(this).data("icono");
        $("#icono").val(icono);
        $("#previewIcon")
            .attr("class", icono);
        $("#modalIconos").modal("hide");
    });

    // ================== MODAL MÓDULO ==================

    const modalModuloEl = document.getElementById('modalModulo');
    const modalModulo = new bootstrap.Modal(modalModuloEl);
    const formModulo = document.getElementById('formModulo');
    instance.Datatable.inputSearch(table, ".inputSearch", ".btnSearch", "manual")

    // Muestra/oculta el campo Controlador según el tipo
    function toggleControlador() {
        if ($('#tipo').val() === 'MENU') {
            $('.controlador').hide();
            $('#controlador').val('');
        } else {
            $('.controlador').show();
        }
    }
    $('#tipo').on('change', toggleControlador);

    // Puebla el select de Menú Padre
    function cargarPadres(idModuloActual = null, idPadreSeleccionado = null) {
        let formData = new FormData();
        if (idModuloActual) formData.set('id_modulo', idModuloActual);

        fetch($('#url').val() + 'config_modulos/get_padres', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                const select = $('#id_modulo_padre');
                select.empty().append('<option value="">Ninguno</option>');

                if (res.success) {
                    res.data.forEach(function (item) {
                        select.append(`<option value="${item.id_modulo}">${item.nombre}</option>`);
                    });
                }

                select.val(idPadreSeleccionado ? idPadreSeleccionado : "");
            });
    }

    // Resetea el modal a estado "Nuevo"
    function resetModalModulo() {
        formModulo.reset();
        formModulo.classList.remove('was-validated');
        $('#id_modulo').val('');
        $('#previewIcon').attr('class', 'fa-solid fa-cube');
        $("#modulo_title").html('<i class="fa-solid fa-cubes me-2"></i>Nuevo Módulo');
    }

    // Botón "Nuevo Módulo"
    document.getElementById('btnNuevoModulo').addEventListener('click', function () {
        resetModalModulo();
        toggleControlador();
        cargarPadres();
        modalModulo.show();
    });

    // Reset al cerrar el modal
    modalModuloEl.addEventListener('hidden.bs.modal', resetModalModulo);

    // ================== EDITAR ==================

    $('#table_modulo tbody').on('click', '.btnEditarModulo', function () {
        const id = $(this).data('id');
        let formData = new FormData();
        formData.set('id_modulo', id);

        fetch($('#url').val() + 'config_modulos/get_register', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    instance.Toast.operacion_erronea(res.message);
                    return;
                }

                const data = res.data;

                formModulo.reset();
                $('#id_modulo').val(data.id_modulo);
                $('#tipo').val(data.tipo);
                $('#nombre').val(data.nombre);
                $('#clave').val(data.clave);
                $('#controlador').val(data.controlador ? data.controlador : '');
                $('#campo_permiso').val(data.campo_permiso);
                $('#icono').val(data.icono ? data.icono : '');
                $('#previewIcon').attr('class', data.icono || 'fa-solid fa-cube');
                $('#orden').val(data.orden);
                $('#visible_menu').val(String(data.visible_menu));
                $('#estado').val(String(data.estado));
                $('#descripcion').val(data.descripcion ? data.descripcion : '');

                $("#modulo_title").html('<i class="fa-solid fa-cubes me-2"></i>Editar Módulo');
                toggleControlador();
                cargarPadres(data.id_modulo, data.id_modulo_padre);
                modalModulo.show();
            })
            .catch(() => instance.Toast.operacion_erronea('No se pudo cargar el módulo.'));
    });

    // ================== GUARDAR (crear/editar) ==================

    formModulo.addEventListener('submit', function (e) {
        e.preventDefault();

        const nombre = formModulo.nombre.value.trim();
        const clave = formModulo.clave.value.trim();
        const tipo = formModulo.tipo.value;
        const controlador = formModulo.controlador.value.trim();

        if (!nombre || !clave || (tipo === 'MODULO' && !controlador)) {
            Swal.fire({
                icon: 'error',
                title: 'Operación errónea',
                text: 'Rellene correctamente los campos obligatorios.'
            });
            return;
        }

        const formData = new FormData(formModulo);
        const btnSubmit = formModulo.querySelector('button[type="submit"]');
        const originalHtml = btnSubmit.innerHTML;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

        fetch($('#url').val() + 'config_modulos/crud_register', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    instance.Toast.operacion_exitosa(data.message);
                    modalModulo.hide();
                    instance.Datatable.reloadTable(table);
                } else {
                    instance.Toast.operacion_erronea(data.message);
                }
            })
            .catch(() => instance.Toast.operacion_erronea('Ha ocurrido un error de conexión.'))
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalHtml;
            });
    });

    // ================== ELIMINAR ==================

    $('#table_modulo tbody').on('click', '.btnEliminarModulo', function () {
        const id = $(this).data('id');
        const rowData = table.row($(this).closest('tr')).data();

        Swal.fire({
            title: 'Necesitamos tu confirmación',
            html: `<div>
            <label>Se eliminará del sistema el módulo:</label><br>
            <span class="mt-1 fw-bolder">${rowData.nombre}</span>
            <p class="fs-6 mt-3 mb-0 text-success">¿Está usted de acuerdo?</p>
        </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#429ef5',
            cancelButtonColor: '#ff3b3b',
            cancelButtonText: 'No!',
            confirmButtonText: 'Sí, adelante!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.set('id_modulo', id);

                fetch($('#url').val() + 'config_modulos/delete_register', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            instance.Toast.operacion_exitosa(data.message);
                            instance.Datatable.reloadTable(table);
                        } else {
                            instance.Toast.operacion_erronea(data.message);
                        }
                    })
                    .catch(() => instance.Toast.operacion_erronea('Ha ocurrido un error de conexión.'));
            }
        });
    });
})