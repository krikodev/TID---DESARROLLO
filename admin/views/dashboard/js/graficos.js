import * as instance from "../../../public/js/instance.js";

document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('grafico_meses').getContext('2d');
    let chart;

    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(value);
    }

    function createChart(data) {
        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || meses,
                datasets: [
                    {
                        label: 'Venta SUNAT',
                        data: data.ventaSunat || [],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Venta Interna',
                        data: data.ventaInterna || [],
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#2ecc71',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Compras + Gastos',
                        data: data.comprasGastos || [],
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#e74c3c',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Meses 2025',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Monto (S/)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBorderWidth: 3
                    }
                }
            }
        });
    }

    async function fetchData() {
        try {
            const url = instance._URL_ + 'dashboard/obtenerResumenMensual';
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            document.getElementById('loading').style.display = 'none';
            console.log('Datos recibidos:', data);

            const processedData = processApiData(data.data);
            createChart(processedData);

        } catch (error) {
            console.error('Error fetching data:', error);
            document.getElementById('loading').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            createChart(getExampleData());
        }
    }

    function processApiData(apiData) {
        // Verificar que apiData sea un array
        if (!Array.isArray(apiData)) {
            console.error('Los datos no son un array:', apiData);
            return getExampleData();
        }

        // Ordenar por mes para asegurar el orden correcto
        apiData.sort((a, b) => parseInt(a.mes) - parseInt(b.mes));

        // Extraer los datos de cada mes
        const labels = [];
        const ventaSunat = [];
        const ventaInterna = [];
        const comprasGastos = [];

        apiData.forEach(item => {
            labels.push(item.nombre_mes);
            ventaSunat.push(parseFloat(item.ventas_sunat) || 0);
            ventaInterna.push(parseFloat(item.ventas_internas) || 0);
            comprasGastos.push(parseFloat(item.compras_gastos) || 0);
        });

        return {
            labels: labels,
            ventaSunat: ventaSunat,
            ventaInterna: ventaInterna,
            comprasGastos: comprasGastos
        };
    }

    function getExampleData() {
        return {
            labels: meses,
            ventaSunat: [3500, 4100, 200, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ventaInterna: [500, 800, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            comprasGastos: [3000, 1500, 300, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        };
    }

    fetchData();

    // Exponer la función para actualizaciones manuales si es necesario
    window.updateChart = fetchData;
});