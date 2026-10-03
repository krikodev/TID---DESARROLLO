import * as instance from "../../../public/js/instance.js";

// Activar menú
document.getElementById("link_viaje_report").closest(".nav-item").querySelector("a.nav-link").classList.add("active");
document.getElementById("link_viaje_report").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed");
document.getElementById("link_viaje_report").closest(".submenu").classList.add("show");
document.getElementById("link_viaje_report").classList.add("active");

document.addEventListener("DOMContentLoaded", function (event) {
    const terminal_origen = document.querySelector("#terminal_origen");
    const terminal_destino = document.querySelector("#terminal_destino");
    const tipo_servicio = document.querySelector("#tipo_servicio");
    const fecha_inicio = document.querySelector("#fecha_inicio");
    const fecha_fin = document.querySelector("#fecha_fin");
    const btn_filtrar = document.querySelector("#btn_filtrar");
    const btn_loadSave = document.querySelector("#btn_loadSave");
    const btn_limpiar = document.querySelector("#btn_limpiar");

    // Variable para la tabla
    let table;

    // Inicializar la tabla al cargar la página
    inicializarDataTable();

    // Función para validar fechas
    function validarFechas() {
        if (fecha_inicio.value && fecha_fin.value) {
            const inicio = new Date(fecha_inicio.value);
            const fin = new Date(fecha_fin.value);
            if (fin < inicio) {
                Swal.fire({
                    icon: "error",
                    title: "Rango de fechas inválido",
                    text: "La fecha final no puede ser anterior a la fecha de inicio."
                });
                return false;
            }
        } else if (fecha_fin.value && !fecha_inicio.value) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Es necesaria una fecha de inicio para usar la fecha final."
            });
            return false;
        }
        return true;
    }

    // Inicializar DataTable
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabla_viajesreport')) {
            table.destroy();
        }

        table = $('#tabla_viajesreport').DataTable({
            ajax: {
                url: instance._URL_ + 'viaje_report/dataTable',
                type: 'POST',
                data: function (d) {
                    return {
                        draw: d.draw,
                        start: d.start,
                        length: d.length,
                        search: d.search,
                        terminal_origen: terminal_origen.value,
                        terminal_destino: terminal_destino.value,
                        tipo_servicio: tipo_servicio.value,
                        fecha_inicio: fecha_inicio.value,
                        fecha_fin: fecha_fin.value
                    };
                },
                dataSrc: function (json) {
                    console.log("Respuesta del servidor:", json);

                    if (json.error) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: json.error
                        });
                        return [];
                    }
                    return json.data;
                },
                error: function (xhr, error, thrown) {
                    console.error("Error AJAX:", xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudieron cargar los datos."
                    });
                }
            },
            processing: true,
            serverSide: true,
            searching: true,
            ordering: false,
            columns: [
                {
                    data: "asiento",
                    render: function (data, type, row) {
                        return data || 'N/A';
                    }
                },
                {
                    data: "numero_documento",
                    render: function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: "fecha_emision",
                    render: function (data) {
                        return data ? new Date(data).toLocaleDateString('es-PE') : 'N/A';
                    }
                },
                {
                    data: "cliente",
                    render: function (data, type, row) {
                        const doc = row.documento_cliente ? ` (${row.documento_cliente})` : '';
                        return (data || 'N/A') + doc;
                    }
                },
                {
                    data: "pasajero",
                    render: function (data, type, row) {
                        const doc = row.documento_pasajero ? ` (${row.documento_pasajero})` : '';
                        return (data || 'N/A') + doc;
                    }
                },
                {
                    data: "origen",
                    render: function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: "destino",
                    render: function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: "op_gravada",
                    render: function (data) {
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: "op_igv",
                    render: function (data) {
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: "op_total",
                    render: function (data) {
                        return 'S/ ' + parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: "placa",
                    render: function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: "estado_asiento",
                    render: function (data) {
                        let color = '#28a745';
                        let texto = 'PAGADO';

                        if (data === '0' || data === 0) {
                            color = '#dc3545';
                            texto = 'ANULADO';
                        } else if (data === '2' || data === 2) {
                            color = '#ffc107';
                            texto = 'PENDIENTE';
                        }

                        return `<span class="badge" style="background-color:${color}">${texto}</span>`;
                    }
                }
            ],
            language: {
                url: instance._URL_ + "public/plugins/datatable/language/es_es.json"
            },
            dom: 'rtip',
            lengthMenu: [
                [20, 25, 50, -1],
                ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
            ],
            buttons: [
                {
                    extend: "print",
                    text: "<i class='bi bi-printer-fill'></i> Imprimir",
                    titleAttr: "Imprimir",
                    className: "BtnPRINT",
                    title: "Reporte de Viajes Generales",
                    messageTop: function () {
                        return `Filtros aplicados: 
                            Terminal Origen: ${getFilterText('#terminal_origen', terminal_origen.value)}, 
                            Terminal Destino: ${getFilterText('#terminal_destino', terminal_destino.value)}, 
                            Servicio: ${getFilterText('#tipo_servicio', tipo_servicio.value)}, 
                            Fecha: ${fecha_inicio.value || 'Inicio'} - ${fecha_fin.value || 'Fin'}`;
                    },
                    customize: function (win) {
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', '10pt');
                        
                        // Agregar fecha de generación
                        $(win.document.body).prepend(
                            '<div style="text-align:center;margin-bottom:20px;">' +
                            '<h2>Reporte de Viajes Generales</h2>' +
                            '<p>Generado el: ' + new Date().toLocaleDateString('es-PE') + '</p>' +
                            '</div>'
                        );
                    }
                }
            ]
        });

        return table;
    }

    // Función auxiliar para obtener texto de filtros
    function getFilterText(selector, value) {
        if (!value) return 'Todos';
        const element = document.querySelector(selector);
        if (element && element.options) {
            const option = Array.from(element.options).find(opt => opt.value === value);
            return option ? option.text : value;
        }
        return value;
    }

    // Función para filtrar
    function filtrarDatos() {
        if (!validarFechas()) return;

        btn_filtrar.classList.add("d-none");
        btn_loadSave.classList.remove("d-none");

        if (table) {
            table.ajax.reload(function (json) {
                btn_filtrar.classList.remove("d-none");
                btn_loadSave.classList.add("d-none");

                // Mostrar mensaje si no hay resultados
                if (json.data.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No se encontraron registros con los filtros aplicados.',
                        timer: 2000
                    });
                }
            });
        }
    }

    // Función para limpiar filtros
    function limpiarFiltros() {
        fecha_inicio.value = '';
        fecha_fin.value = '';
        terminal_origen.value = '';
        terminal_destino.value = '';
        tipo_servicio.value = '';

        // Limpiar también el buscador
        $('.inputSearch').val('');

        if (table) {
            table.search('').draw(); // Limpiar búsqueda
            table.ajax.reload(function () {
                btn_filtrar.classList.remove("d-none");
                btn_loadSave.classList.add("d-none");
            });
        }
    }

    // EVENT LISTENERS PARA FILTROS AUTOMÁTICOS

    // Filtrar al cambiar cualquier filtro
    [terminal_origen, terminal_destino, tipo_servicio, fecha_inicio, fecha_fin].forEach(function (element) {
        element.addEventListener('change', function () {
            // Solo filtrar automáticamente si hay algún valor seleccionado
            if (terminal_origen.value || terminal_destino.value || tipo_servicio.value || fecha_inicio.value || fecha_fin.value) {
                filtrarDatos();
            }
        });
    });

    // Botón filtrar
    btn_filtrar.addEventListener('click', filtrarDatos);

    // Botón limpiar
    btn_limpiar.addEventListener('click', limpiarFiltros);

    // Búsqueda en tiempo real con debounce
    let searchTimeout;
    $('.inputSearch').on('keyup', function () {
        const searchValue = this.value.trim();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (table) {
                // Usar el método search de DataTables que envía el parámetro al servidor
                table.search(searchValue).draw();
            }
        }, 500); // 500ms de delay
    });

    // También agregar un botón de limpiar búsqueda si quieres
    $('.inputSearch').on('keydown', function (e) {
        if (e.key === 'Escape') {
            this.value = '';
            if (table) {
                table.search('').draw();
            }
        }
    });

    // Exportación Excel (actualizado con nuevo filtro)
    $('#exportExcel').on("click", function () {
        if (!validarFechas()) return;

        const spinner = document.querySelector("#exportExcelSpinner");
        spinner.classList.remove("d-none");

        const formData = new FormData();
        formData.append('terminal_origen', terminal_origen.value || '');
        formData.append('terminal_destino', terminal_destino.value || '');
        formData.append('tipo_servicio', tipo_servicio.value || '');
        formData.append('fecha_inicio', fecha_inicio.value || '');
        formData.append('fecha_fin', fecha_fin.value || '');

        console.log("Enviando parámetros:", {
            terminal_origen: terminal_origen.value,
            terminal_destino: terminal_destino.value,
            tipo_servicio: tipo_servicio.value,
            fecha_inicio: fecha_inicio.value,
            fecha_fin: fecha_fin.value
        });

        fetch(instance._URL_ + 'viaje_report/exportacion', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                spinner.classList.add("d-none");
                if (!response.ok) {
                    return response.text().then(text => {
                        let errorMessage = 'No se pudo generar el archivo Excel.';
                        try {
                            const data = JSON.parse(text);
                            errorMessage = data.message || errorMessage;
                        } catch (e) {
                            errorMessage = text || errorMessage;
                        }
                        throw new Error(errorMessage);
                    });
                }
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const fechaInicioStr = fecha_inicio.value ? fecha_inicio.value.replace(/-/g, '') : 'todo';
                const fechaFinStr = fecha_fin.value ? fecha_fin.value.replace(/-/g, '') : 'todo';
                a.download = `reporte_viajes_${fechaInicioStr}_${fechaFinStr}.xlsx`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                Swal.fire({ icon: 'success', title: 'Éxito', text: 'Archivo descargado correctamente.', timer: 1500 });
            })
            .catch(error => {
                spinner.classList.add("d-none");
                Swal.fire({ icon: "error", title: "Error", text: error.message || "Error al conectar con el servidor." });
            });
    });

    // Imprimir
    $('#exportPrint').on("click", function () {
        if (table) {
            table.button('.BtnPRINT').trigger();
        }
    });
});