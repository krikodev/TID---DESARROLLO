import * as instance from "../../../public/js/instance.js";

document.getElementById("link_comprobantes_report").closest(".nav-item").querySelector("a.nav-link").classList.add("active");
document.getElementById("link_comprobantes_report").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed");
document.getElementById("link_comprobantes_report").closest(".submenu").classList.add("show");
document.getElementById("link_comprobantes_report").classList.add("active");

document.addEventListener("DOMContentLoaded", function (event) {
    const terminal = document.querySelector("#terminal");
    const tp_comprobante = document.querySelector("#tp_comprobante");
    const fecha_inicio = document.querySelector("#fecha_inicio");
    const fecha_fin = document.querySelector("#fecha_fin");
    const btn_filtrar = document.querySelector("#btn_filtrar");
    const btn_loadSave = document.querySelector("#btn_loadSave");

    let table = $('#tabla_comprobantesreport').DataTable({
        ajax: {
            url: $('#url').val() + 'comprobantes_report/dataTable',
            method: 'POST',
            data: function (d) {
                d.terminal = terminal.value;
                d.tp_comprobante = tp_comprobante.value;
                d.fecha_inicio = fecha_inicio.value;
                d.fecha_fin = fecha_fin.value;
            },
            beforeSend: function () {
                document.getElementById('loader').classList.remove('d-none');
            },
            complete: function () {
                document.getElementById('loader').classList.add('d-none');
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        fixedHeader: true,
        dom: 'rtip',
        lengthMenu: [
            [20, 25, 50, -1],
            ['20 filas', '25 filas', '50 filas', 'Mostrar todo']
        ],
        ordering: false,
        columns: [
            { data: "fecha_emision" },
            { data: "cliente" },
            { data: "numero" },
            {
                data: "estado",
                render: (data, type, row) => {
                    // Si no hay datos, mostrar vacío
                    if (!data && data !== 0) {
                        return '';
                    }

                    try {
                        const estado = String(data).toUpperCase();
                        let color = '#6c757d'; // Color gris por defecto
                        let texto = data; // Mantener el texto original

                        // Mapeo de colores por defecto si no existen en la configuración
                        const defaultColors = {
                            'PAGADO': '#28a745',      // Verde
                            'VENDIDO': '#17a2b8',      // Azul claro
                            'ANULADO': '#dc3545',      // Rojo
                            'APROBADO': '#65eb5b',     // Verde claro
                            'PENDIENTE': '#ffc107',    // Amarillo
                            'PROCESADO': '#007bff'     // Azul
                        };

                        // Verificar si instance.CONSTS existe
                        const consts = typeof instance !== 'undefined' && instance.CONSTS ? instance.CONSTS : null;
                        const colores = consts && consts.COLORES ? consts.COLORES : null;

                        // Si es nota de crédito/débito (APROBADO)
                        if (estado === 'APROBADO') {
                            // Intentar obtener el color de ESTADO_VENTA_ENCOMIENDA.APROBADO
                            if (colores && colores.ESTADO_VENTA_ENCOMIENDA && colores.ESTADO_VENTA_ENCOMIENDA.APROBADO) {
                                color = colores.ESTADO_VENTA_ENCOMIENDA.APROBADO;
                            } else {
                                color = defaultColors['APROBADO'] || '#65eb5b';
                            }
                        }
                        // Si es comprobante normal
                        else {
                            // Determinar qué objeto de colores usar
                            let colorObj = null;

                            if (row.tp_servicio === "PASAJE") {
                                colorObj = colores && colores.ESTADO_ASIENTO ? colores.ESTADO_ASIENTO : null;
                            } else {
                                colorObj = colores && colores.ESTADO_VENTA_ENCOMIENDA ? colores.ESTADO_VENTA_ENCOMIENDA : null;
                            }

                            // Intentar obtener el color del objeto, si no, usar el color por defecto
                            if (colorObj && colorObj[estado]) {
                                color = colorObj[estado];
                            } else {
                                // Buscar en colores por defecto
                                color = defaultColors[estado] || '#6c757d';
                            }
                        }

                        return '<span class="badge" style="background-color: ' + color + '; color: #ffffff;">' + texto + '</span>';

                    } catch (error) {
                        console.error('Error al renderizar estado:', error);
                        // Fallback seguro
                        return '<span class="badge" style="background-color: #6c757d; color: #ffffff;">' + (data || 'N/A') + '</span>';
                    }
                }
            },
            { data: "op_gravada" },
            { data: "op_exonerada" },
            { data: "op_inafecta" },
            { data: "igv" },
            { data: "total" },
            {
                data: "estado_sunat",
                render: (data, type, row) => {
                    let badge;
                    if (row.id_tp_comprobante == 2) {
                        badge = `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[0]}">COMPROBANTE LOCAL</span>`;
                    } else {
                        badge = `<span class="badge" style="background-color:${instance.CONSTS.COLORES.ENVIO_SUNAT[row.estado_sunat]}">${instance.CONSTS.TEXTO.TEXT_ENVIO_SUNAT[row.estado_sunat]}</span>`;
                    }
                    return badge;
                }
            },
            { data: "tp_servicio", title: "tipo de servicio", visible: false }
        ],
        language: { url: "./public/plugins/datatable/language/es_es.json" },
        buttons: [
            {
                extend: "print",
                text: "<i class='bi bi-printer-fill'></i> Imprimir",
                titleAttr: "Imprimir",
                className: "BtnPrint"
            }
        ]
    });

    function filtrarDatos() {
        // Permitir que terminal.value sea "" o "0" para mostrar todos los comprobantes
        if (!terminal.value && terminal.value !== "" && terminal.value !== "0") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor, seleccione una terminal."
            });
            return;
        }

        if (fecha_inicio.value && fecha_fin.value) {
            const inicio = new Date(fecha_inicio.value);
            const fin = new Date(fecha_fin.value);
            if (fin < inicio) {
                Swal.fire({
                    icon: "error",
                    title: "Rango de fechas inválido",
                    text: "La fecha final no puede ser anterior a la fecha de inicio."
                });
                return;
            }
        } else if (fecha_fin.value && !fecha_inicio.value) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Es necesaria una fecha de inicio para usar la fecha final."
            });
            return;
        }

        btn_filtrar.classList.add("d-none");
        btn_loadSave.classList.remove("d-none");
        table.ajax.reload(function () {
            btn_filtrar.classList.remove("d-none");
            btn_loadSave.classList.add("d-none");
        });
    }

    function limpiarFiltros() {
        // Limpiar el select de terminal
        const terminal = document.querySelector("#terminal");
        if (terminal) {
            terminal.value = "";  // Selecciona "Seleccione terminal"
        }

        // Limpiar el select de tipo de comprobante
        const tp_comprobante = document.querySelector("#tp_comprobante");
        if (tp_comprobante) {
            tp_comprobante.value = "TODO";  // Valor por defecto
        }

        // Limpiar fechas
        const fecha_inicio = document.querySelector("#fecha_inicio");
        const fecha_fin = document.querySelector("#fecha_fin");
        if (fecha_inicio) fecha_inicio.value = "";
        if (fecha_fin) fecha_fin.value = "";

        // Limpiar el input de búsqueda personalizado (si existe)
        const inputSearch = document.querySelector(".inputSearch");
        if (inputSearch) inputSearch.value = "";

        // Recargar la tabla con los filtros limpios
        const btn_filtrar = document.querySelector("#btn_filtrar");
        const btn_loadSave = document.querySelector("#btn_loadSave");

        btn_filtrar.classList.add("d-none");
        btn_loadSave.classList.remove("d-none");

        // Recargar DataTable
        const table = $('#tabla_comprobantesreport').DataTable();
        table.ajax.reload(function () {
            btn_filtrar.classList.remove("d-none");
            btn_loadSave.classList.add("d-none");
        });
    }
    const btn_limpiar = document.querySelector("#btn_limpiar");
    if (btn_limpiar) {
        btn_limpiar.addEventListener("click", limpiarFiltros);
    }


    function loadData() {
        // Permitir que terminal.value sea "" o "0" para mostrar todos los comprobantes
        if (!terminal.value && terminal.value !== "" && terminal.value !== "0") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor, seleccione una terminal."
            });
            return;
        }

        if (fecha_inicio.value && fecha_fin.value) {
            const inicio = new Date(fecha_inicio.value);
            const fin = new Date(fecha_fin.value);
            if (fin < inicio) {
                Swal.fire({
                    icon: "error",
                    title: "Rango de fechas inválido",
                    text: "La fecha final no puede ser anterior a la fecha de inicio."
                });
                return;
            }
        } else if (fecha_fin.value && !fecha_inicio.value) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Es necesaria una fecha de inicio para usar la fecha final."
            });
            return;
        }

        btn_filtrar.classList.add("d-none");
        btn_loadSave.classList.remove("d-none");

        const xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
        const formData = new FormData();
        formData.append("terminal", terminal.value);
        formData.append("tp_comprobante", tp_comprobante.value);
        formData.append("fecha_inicio", fecha_inicio.value);
        formData.append("fecha_fin", fecha_fin.value);

        const url = `${instance._URL_}comprobantes_report/dataTable`;
        xhr.open("POST", url, true);
        xhr.send(formData);

        xhr.onreadystatechange = () => {
            if (xhr.readyState !== 4) return;

            btn_filtrar.classList.remove("d-none");
            btn_loadSave.classList.add("d-none");

            if (xhr.status !== 200) {
                Swal.fire({
                    icon: "error",
                    title: "Error de servidor",
                    text: `No se pudo procesar la solicitud (Código: ${xhr.status}).`
                });
                return;
            }

            const response = JSON.parse(xhr.responseText);
            if (response.estado !== false) {
                table.clear().rows.add(response.data).draw();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response.message || "Es necesaria una fecha de inicio para usar la fecha final."
                });
            }
        };
    }

    $('#exportExcel').on("click", function () {
        if (fecha_inicio.value && fecha_fin.value) {
            const inicio = new Date(fecha_inicio.value);
            const fin = new Date(fecha_fin.value);
            if (fin < inicio) {
                Swal.fire({
                    icon: "error",
                    title: "Rango de fechas inválido",
                    text: "La fecha final no puede ser anterior a la fecha de inicio."
                });
                return;
            }
        } else if (fecha_fin.value && !fecha_inicio.value) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Es necesaria una fecha de inicio para usar la fecha final."
            });
            return;
        }

        document.querySelector("#exportExcelSpinner").classList.remove("d-none");
        const formData = new FormData();
        // Enviar terminal incluso si está vacío para indicar "todos"
        formData.append('terminal', terminal.value || '');
        formData.append('tp_comprobante', tp_comprobante.value || 'TODO');
        formData.append('fecha_inicio', fecha_inicio.value || '');
        formData.append('fecha_fin', fecha_fin.value || '');

        fetch(`${instance._URL_}comprobantes_report/exportacion`, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                document.querySelector("#exportExcelSpinner").classList.add("d-none");
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
                a.download = `reporte_ventas_ingresos_${new Date().toISOString().replace(/[:.]/g, '')}.xlsx`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'El archivo Excel se descargó correctamente.',
                    timer: 1500
                });
            })
            .catch(error => {
                document.querySelector("#exportExcelSpinner").classList.add("d-none");
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message || "Error al conectar con el servidor."
                });
            });
    });

    $('#exportPrint').on("click", function () {
        table.button('.BtnPrint').trigger();
    });

    $('#exportPdf').on("click", function () {
        const url = `${instance._URL_}reportes/impresion/pagos`;
        window.open(url, "_blank");
    });

    instance.Datatable.inputSearch(table, ".inputSearch");
    btn_filtrar.addEventListener("click", filtrarDatos);
});