
// Bar Chart: Items/Month
const barCtx = document.getElementById('barChart').getContext('2d');
const barLabels = Array.from({ length: 12 }, (_, i) => new Date(0, i).toLocaleString('en', { month: 'short' }));
const itemsData = Array.from({ length: 12 }, (_, i) => itemsChartData[i + 1] || 0); // PHP passes months as 1-indexed

const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: barLabels,
        datasets: [{
            label: 'Items Rented',
            data: itemsData,
            backgroundColor: ['#817AF3', '#74B0FA', '#79D0F1'],
            borderWidth: 0,
        }]
    },
    options: {
        scales: {
            x: {
                ticks: {
                    color: '#333'
                }
            },
            y: {
                ticks: {
                    color: '#333',
                    beginAtZero: true
                }
            }
        },
        barThickness: 30,
        categoryPercentage: 0.6,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Multi-dataset Line Chart: Categories/Month
const lineCtx = document.getElementById('lineChart').getContext('2d');
const categoriesData = Object.keys(categoriesChartData).map(category => ({
    label: category,
    data: Array.from({ length: 12 }, (_, i) => categoriesChartData[category][i + 1] || 0),
    borderColor: `#${Math.floor(Math.random() * 16777215).toString(16)}`, // Generate random colors
    backgroundColor: 'rgba(75, 192, 192, 0.2)',
    fill: true
}));

const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: barLabels,
        datasets: categoriesData
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                enabled: true,
                mode: 'nearest'
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#333'
                }
            },
            y: {
                ticks: {
                    color: '#333',
                    beginAtZero: true
                }
            }
        }
    }
});
