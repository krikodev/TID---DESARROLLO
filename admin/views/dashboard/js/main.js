import * as instance from "../../../public/js/instance.js";

document.getElementById("link_dashboard").classList.add("active");

document.addEventListener("DOMContentLoaded", () => {

    const Dashboard = {
        loader: document.getElementById("loader"),
        fechaProgramacion: document.querySelector("#fecha_programacion"),

        charts: {
            grafico1: null,
            grafico2: null,
            grafico3: null,
            graficoResumenMensual: null
        },

        init() {
            const tpUsuario = parseInt(instance._TP_USUARIO_SESION);
            const esAdminOSoporte = [1, 2].includes(tpUsuario);

            if (!esAdminOSoporte) {
                $('#terminalParent, #parentGraficos1, #parentVentasTab, #parentMesesTotales, #parentUsarioTotal').addClass('d-none');
            }

            this.initSelects();
            this.initTables();
            this.initEvents();

            $('#periodo').trigger('change');
        },
        
        initSelects() {
            instance.select.createSelect("#terminal", "#terminalParent");
            instance.select.createSelectSinSearch("#periodo", "#periodoParent");
        },

        initTables() {
            this.tableProgramacion = $('#table_programacion').DataTable({
                ajax: {
                    url: instance._URL_ + 'dashboard/dataTableProgramacion',
                    method: 'POST',
                    data: (d) => {

                        const filtros = this.getFiltros();

                        d.periodo = filtros.periodo;
                        d.fecha_inicio = filtros.fecha_inicio;
                        d.fecha_fin = filtros.fecha_fin;
                    }
                },
                responsive: true,
                fixedHeader: true,
                serverSide: true,
                processing: true,
                dom: 'rtip',

                pageLength: 5,

                lengthMenu: [
                    [5, 25, 50, -1],
                    ['5 filas', '25 filas', '50 filas', 'Mostrar todo']
                ],

                ordering: false,

                columns: [
                    { data: "terminal_origen" },
                    { data: "terminal_destino" },
                    {
                        data: "vehiculo_placa",
                        render: (data, type, row) => {
                            return `<span class="badge" style="background-color: skyblue; color:black; font-weight:700">${row.vehiculo_placa}</span>`;
                        }
                    },
                    { data: "fecha_salida" },
                    { data: "hora_salida" },
                ],

                language: {
                    url: './public/plugins/datatable/language/es_es.json',
                },
            });

            this.tableTotales = $('#table_totales').DataTable({
                ajax: {
                    url: instance._URL_ + 'dashboard/obtenerResumenMensual',
                    method: 'GET',
                },
                responsive: true,
                fixedHeader: true,
                dom: 'rtip',
                lengthMenu: [
                    [12, 25, 50, -1],
                    ['12 filas', '25 filas', '50 filas', 'Mostrar todo']
                ],
                ordering: false,
                columns: [
                    { data: "nombre_mes" },
                    { data: "ventas_sunat" },
                    { data: "ventas_internas" },
                    { data: "compras_gastos" },
                ],
                language: {
                    url: './public/plugins/datatable/language/es_es.json',
                },
            });

            instance.Datatable.inputSearch(this.tableProgramacion, ".inputSearchProgramacion");
        },

        initEvents() {

            $('#periodo').on('change', () => {
                this.controlarFiltrosFecha();
            });

            $('#terminal, #fecha_inicio, #fecha_fin').on('change', () => {
                this.recargarDashboard();
            });
        },

        recargarDashboard() {
            this.tableProgramacion.ajax.reload();
            this.cargarCardsDashboard();
            this.cargarGraficos();
            this.cargarResumenMensual();
        },

        getFechaHoy() {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        },

        getMesActual() {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');

            return `${yyyy}-${mm}`;
        },

        getMesInicioAnio() {
            const hoy = new Date();
            return `${hoy.getFullYear()}-01`;
        },

        getPrimerDiaMes() {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');

            return `${yyyy}-${mm}-01`;
        },

        controlarFiltrosFecha() {
            const periodo = $('#periodo').val();

            const fechaInicio = $('#fecha_inicio');
            const fechaFin = $('#fecha_fin');

            fechaInicio.val('');
            fechaFin.val('');

            const esMes = periodo === 'por_mes' || periodo === 'entre_meses';

            fechaInicio.attr('type', esMes ? 'month' : 'date');
            fechaFin.attr('type', esMes ? 'month' : 'date');

            switch (periodo) {
                case 'todos':
                case 'ultima_semana':
                    fechaInicio.prop('disabled', true);
                    fechaFin.prop('disabled', true);

                    $('#ParentInicio').addClass('d-none');
                    $('#ParentFin').addClass('d-none');
                    break;

                case 'por_mes':
                    fechaInicio.prop('disabled', false);
                    fechaFin.prop('disabled', true);

                    fechaInicio.val(this.getMesActual());

                    $('#ParentInicio').removeClass('d-none');
                    $('#ParentFin').addClass('d-none');
                    break;

                case 'entre_meses':
                    fechaInicio.prop('disabled', false);
                    fechaFin.prop('disabled', false);

                    fechaInicio.val(this.getMesInicioAnio());
                    fechaFin.val(this.getMesActual());

                    $('#ParentInicio').removeClass('d-none');
                    $('#ParentFin').removeClass('d-none');
                    break;

                case 'por_fecha':
                    fechaInicio.prop('disabled', false);
                    fechaFin.prop('disabled', true);

                    fechaInicio.val(this.getFechaHoy());

                    $('#ParentInicio').removeClass('d-none');
                    $('#ParentFin').addClass('d-none');
                    break;

                case 'entre_fechas':
                    fechaInicio.prop('disabled', false);
                    fechaFin.prop('disabled', false);

                    fechaInicio.val(this.getPrimerDiaMes());
                    fechaFin.val(this.getFechaHoy());

                    $('#ParentInicio').removeClass('d-none');
                    $('#ParentFin').removeClass('d-none');
                    break;
            }

            this.recargarDashboard();
        },

        getFiltros() {
            return {
                terminal: $('#terminal').val(),
                periodo: $('#periodo').val(),
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
            };
        },

        cargarCardsDashboard() {
            this.mostrarLoader();

            const formData = new FormData();
            const filtros = this.getFiltros();

            for (const key in filtros) {
                formData.append(key, filtros[key]);
            }

            fetch(instance._URL_ + "dashboard/get_info_dashboard", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    this.renderCardsDashboard(data.cantidad_registro || []);
                    this.renderEmpresaDashboard(data.empresa || null);
                })
                .catch(error => {
                    console.error("Error al cargar cards dashboard:", error);
                    $("#cardsDashboard").html(`
                <div class="col-12">
                    <div class="alert alert-danger mb-0">
                        Error al cargar los indicadores del dashboard.
                    </div>
                </div>
            `);
                })
                .finally(() => {
                    this.ocultarLoader();
                });
        },

        renderCardsDashboard(registros) {
            const container = $("#cardsDashboard");

            if (!registros.length) {
                container.html(`
            <div class="col-12">
                <div class="alert alert-warning mb-0">
                    No hay indicadores disponibles.
                </div>
            </div>
        `);
                return;
            }

            let html = "";

            registros.forEach(registro => {
                html += `
            <div class="col-6 col-lg-3">
                <div class="app-card app-card-stat shadow-sm h-100">
                    <div class="app-card-body p-3 p-lg-4 text-center">
                        <div class="icon-container mb-3">
                            <div class="icon-circle">
                                ${registro.icon}
                            </div>
                        </div>

                        <h4 class="stats-type mb-2">
                            ${registro.nombre}
                        </h4>

                        <div class="stats-figure" style="font-weight: 600; font-size: 1.5rem;">
                            ${registro.amount}
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            container.html(html);
        },

        renderEmpresaDashboard(empresa) {
            const container = $("#empresaDashboard");

            if (!empresa) {
                container.html("");
                return;
            }

            const logo = empresa.logo ? `
               <img src="${empresa.logo}" alt="Logo empresa" class="img-fluid mb-2">
               ` : "";

            container.html(`
            <div class="card-body shadow-sm px-3 mb-3 bg-white rounded position-relative">
            <div class="row d-flex p-0">
                <div class="col-12" style="background-color: #2a9d8f; width:100%; border-radius:10px">
                    <h6 class="text-white py-3 mb-0">Empresa</h6>
                </div>

                <div class="col-12 my-2 row">
                    ${logo}

                    <label class="mb-1 fw-bold" style="font-size: 15px;">
                        <i class="fa-light fa-building me-2"></i>
                        ${empresa.razon_social || ''}
                    </label>

                    <label class="mb-1">
                        <i class="fa-light fa-address-card me-2"></i>
                        ${empresa.num_docu || ''}
                    </label>

                    <label class="mb-1">
                        <i class="fa-light fa-location-dot me-2"></i>
                        ${empresa.direccion_fiscal || ''}
                    </label>
                </div>
            </div>
        </div>
        `);
        },

        cargarGraficos() {
            this.cargarGrafico(
                'dashboard/totales_meses',
                'Grafico1',
                'grafico1',
                'Ventas por Meses'
            );

            this.cargarGrafico(
                'dashboard/ventas_por_terminales',
                'Grafico2',
                'grafico2',
                'Ventas por Terminales'
            );

            this.cargarGrafico(
                'dashboard/ventas_por_vendedores',
                'Grafico3',
                'grafico3',
                'Ventas por Vendedores'
            );
        },

        cargarGrafico(endpoint, canvasId, chartKey, titulo) {
            this.mostrarLoader();

            const formData = new FormData();

            const filtros = this.getFiltros();

            for (const key in filtros) {
                formData.append(key, filtros[key]);
            }

            fetch(instance._URL_ + endpoint, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (this.charts[chartKey]) {
                        this.charts[chartKey].destroy();
                        this.charts[chartKey] = null;
                    }

                    if (data && data.length > 0) {
                        this.charts[chartKey] = this.crearGraficoVentas(
                            data,
                            canvasId,
                            null,
                            titulo
                        );
                    } else {
                        this.charts[chartKey] = this.mostrarGraficoSinDatos(
                            canvasId,
                            'No hay datos para los filtros seleccionados'
                        );
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.charts[chartKey] = this.mostrarGraficoSinDatos(
                        canvasId,
                        'Error al cargar datos'
                    );
                })
                .finally(() => {
                    this.ocultarLoader();
                });
        },

        crearGraficoVentas(datos, canvasId, instanciaGrafico, titulo) {
            const ctx = document.getElementById(canvasId).getContext('2d');

            if (instanciaGrafico) {
                instanciaGrafico.destroy();
            }

            let labels = [];
            let datasets = [];

            if (canvasId === 'Grafico1') {
                labels = datos.map(item => item.periodo);

                datasets = [
                    {
                        label: 'Pasajes',
                        data: datos.map(item => parseFloat(item.total_pasajes || 0)),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 4
                    },
                    {
                        label: 'Encomiendas',
                        data: datos.map(item => parseFloat(item.total_encomiendas || 0)),
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        borderRadius: 4
                    }
                ];
            }

            if (canvasId === 'Grafico2') {

                datos.sort((a, b) => {
                    if (parseInt(a.año) === parseInt(b.año)) {
                        return parseInt(a.mes) - parseInt(b.mes);
                    }

                    return parseInt(a.año) - parseInt(b.año);
                });

                const terminales = [...new Set(datos.map(item => item.nombre_terminal))];

                const periodos = [...new Map(
                    datos.map(item => [item.periodo, item.periodo])
                ).values()];

                labels = periodos;

                datasets = terminales.map((terminal) => ({
                    label: terminal,
                    data: periodos.map(periodo => {
                        const item = datos.find(d =>
                            d.nombre_terminal === terminal &&
                            d.periodo === periodo
                        );

                        return item ? parseFloat(item.total || 0) : 0;
                    }),
                    borderWidth: 2,
                    borderRadius: 4
                }));
            }

            if (canvasId === 'Grafico3') {

                datos.sort((a, b) => {
                    if (parseInt(a.año) === parseInt(b.año)) {
                        return parseInt(a.mes) - parseInt(b.mes);
                    }

                    return parseInt(a.año) - parseInt(b.año);
                });

                const vendedores = [...new Set(datos.map(item => item.nombre_completo))];

                const periodos = [...new Map(
                    datos.map(item => [item.periodo, item.periodo])
                ).values()];

                labels = periodos;

                datasets = vendedores.map((vendedor) => ({
                    label: vendedor,
                    data: periodos.map(periodo => {
                        const item = datos.find(d =>
                            d.nombre_completo === vendedor &&
                            d.periodo === periodo
                        );

                        return item ? parseFloat(item.total || 0) : 0;
                    }),
                    borderWidth: 2,
                    borderRadius: 4
                }));
            }

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: titulo
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => new Intl.NumberFormat('es-PE', {
                                    style: 'currency',
                                    currency: 'PEN'
                                }).format(value)
                            }
                        }
                    }
                }
            });
        },

        mostrarGraficoSinDatos(canvasId, mensaje) {
            const ctx = document.getElementById(canvasId).getContext('2d');

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sin datos'],
                    datasets: [{
                        label: mensaje,
                        data: [0],
                        backgroundColor: 'rgba(128, 128, 128, 0.3)',
                        borderColor: 'rgba(128, 128, 128, 0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: mensaje
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1
                        }
                    }
                }
            });
        },

        cargarResumenMensual() {
            this.mostrarLoader();

            const formData = new FormData();
            const filtros = this.getFiltros();

            for (const key in filtros) {
                formData.append(key, filtros[key]);
            }

            fetch(instance._URL_ + "dashboard/obtenerResumenMensual", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const processedData = this.procesarResumenMensual(data.data || []);
                    this.charts.graficoResumenMensual = this.crearGraficoResumenMensual(processedData);
                })
                .catch(error => {
                    console.error("Error al cargar resumen mensual:", error);
                })
                .finally(() => {
                    this.ocultarLoader();
                });
        },

        procesarResumenMensual(apiData) {
            if (!Array.isArray(apiData)) {
                return {
                    labels: [],
                    ventaSunat: [],
                    ventaInterna: [],
                    comprasGastos: []
                };
            }

            apiData.sort((a, b) => {
                if (parseInt(a.año) === parseInt(b.año)) {
                    return parseInt(a.mes) - parseInt(b.mes);
                }

                return parseInt(a.año) - parseInt(b.año);
            });

            const labels = [];
            const ventaSunat = [];
            const ventaInterna = [];
            const comprasGastos = [];

            apiData.forEach(item => {
                labels.push(item.nombre_mes + " " + item.año);
                ventaSunat.push(parseFloat(item.ventas_sunat) || 0);
                ventaInterna.push(parseFloat(item.ventas_internas) || 0);
                comprasGastos.push(parseFloat(item.compras_gastos) || 0);
            });

            return {
                labels,
                ventaSunat,
                ventaInterna,
                comprasGastos
            };
        },

        crearGraficoResumenMensual(data) {
            const canvas = document.getElementById("grafico_meses");

            if (!canvas) {
                return null;
            }

            const ctx = canvas.getContext("2d");

            if (this.charts.graficoResumenMensual) {
                this.charts.graficoResumenMensual.destroy();
            }

            return new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Venta SUNAT",
                            data: data.ventaSunat,
                            borderColor: "#3498db",
                            backgroundColor: "rgba(52, 152, 219, 0.1)",
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: "Venta Interna",
                            data: data.ventaInterna,
                            borderColor: "#2ecc71",
                            backgroundColor: "rgba(46, 204, 113, 0.1)",
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: "Compras + Gastos",
                            data: data.comprasGastos,
                            borderColor: "#e74c3c",
                            backgroundColor: "rgba(231, 76, 60, 0.1)",
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: "index"
                    },
                    plugins: {
                        legend: {
                            position: "top"
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return context.dataset.label + ": " + this.formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => this.formatCurrency(value)
                            }
                        }
                    }
                }
            });
        },

        formatCurrency(value) {
            return new Intl.NumberFormat("es-PE", {
                style: "currency",
                currency: "PEN",
                minimumFractionDigits: 2
            }).format(value);
        },

        mostrarLoader() {
            this.loader.classList.remove("d-none");
        },

        ocultarLoader() {
            this.loader.classList.add("d-none");
        }
    };

    Dashboard.init();
});