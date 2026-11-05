// CGSO dashboard charts (Chart.js).
var pageData = readPageData();

new Chart(document.getElementById('chartCategory'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(pageData.byCategory),
        datasets: [{
            data: Object.values(pageData.byCategory),
            backgroundColor: ['#2f7d4f', '#1e9e8a', '#e0a030', '#1971c2', '#9c36b5']
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } } }
    }
});

new Chart(document.getElementById('chartBarangay'), {
    type: 'bar',
    data: {
        labels: Object.keys(pageData.byBarangay),
        datasets: [{ label: 'Reports', data: Object.values(pageData.byBarangay), backgroundColor: '#2f7d4f' }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
