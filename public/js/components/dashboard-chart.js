document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById('topProductsChart');
    if (!canvas) return;

    if (typeof Chart === 'undefined') {
        console.error("Chart.js no se encuentra cargado.");
        return;
    }

    const ctx = canvas.getContext('2d');

    let labels = [];
    let values = [];

    try {
        const rawLabels = canvas.getAttribute('data-labels');
        const rawValues = canvas.getAttribute('data-values');
        
        labels = rawLabels ? JSON.parse(rawLabels) : [];
        values = rawValues ? JSON.parse(rawValues) : [];
    } catch (error) {
        console.error("Error al procesar los datos de la gráfica:", error);
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['Sin movimientos registrados'],
            datasets: [{
                label: 'Unidades Vendidas',
                data: values.length > 0 ? values : [0],
                backgroundColor: 'rgba(255, 107, 74, 0.8)',
                borderColor: 'rgba(255, 69, 0, 1)',
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(100, 116, 139, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});