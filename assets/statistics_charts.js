// Uses Chart.js registered globally in app.js

// ES modules load after DOMContentLoaded, so we need to check if DOM is already ready
function onReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        // DOM already loaded, run immediately
        callback();
    }
}

onReady(function() {
    // Tab switching logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(target).classList.add('active');
            window.dispatchEvent(new Event('resize'));
        });
    });

    // Charts will be initialized by inline script after data is set
    // Fallback: if data already exists (e.g., page cached), init now
    if (window.wpPieChartData && typeof window.initAllCharts === 'function') {
        window.initAllCharts();
    }
});

// Chart initialization logic, expects data to be set on window by Twig
let chartsInitialized = false;
window.initAllCharts = function() {
    if (chartsInitialized) return; // Prevent double initialization
    
    const Chart = window.Chart;
    if (!Chart) {
        console.error('Chart.js not loaded');
        return;
    }
    
    // Check if we have data
    if (!window.wpPieChartData) {
        console.warn('Chart data not yet available');
        return;
    }
    
    chartsInitialized = true;
    
    // 1. Camembert répartition WP
    createChart('wpPieChart', 'pie', window.wpPieChartData, { plugins: { legend: { position: 'right' } } });
    // 2. Donut entreprises par statut
    createChart('statusDonutChart', 'doughnut', window.statusDonutChartData, { cutout: '60%', plugins: { legend: { position: 'bottom' } } });
    // 3. Évolution par année
    createChart('yearEvolutionChart', 'line', window.yearEvolutionChartData, { scales: { y: { beginAtZero: true } } });
    // 4. Moyennes par WP (Radar)
    createChart('avgRadarChart', 'radar', window.avgRadarChartData, { scales: { r: { beginAtZero: true } } });
    // 5. Secteurs d'activité (Polar Area)
    createChart('secteurPolarChart', 'polarArea', window.secteurPolarChartData, { plugins: { legend: { position: 'right' } } });
    // 6. WP par année (Stacked Bar)
    createChart('wpByYearChart', 'bar', window.wpByYearChartData, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } });
    // 7. Partenaires (Horizontal Bar)
    createChart('partenaireBarChart', 'bar', window.partenaireBarChartData, { indexAxis: 'y', scales: { x: { beginAtZero: true } } });
    // 8. Villes (Camembert)
    createChart('villesPieChart', 'pie', window.villesPieChartData, { plugins: { legend: { position: 'bottom' } } });
    // 9. Taille (Donut)
    createChart('tailleDonutChart', 'doughnut', window.tailleDonutChartData, { cutout: '65%', plugins: { legend: { position: 'bottom' } } });
    // 10. Tendance mensuelle (Line)
    createChart('monthlyTrendChart', 'line', window.monthlyTrendChartData, { scales: { y: { beginAtZero: true } } });
};

function createChart(id, type, data, options = {}) {
    const Chart = window.Chart;
    const el = document.getElementById(id);
    
    // Skip silently if element doesn't exist (chart not on this page)
    if (!el) return;
    
    if (!data) {
        console.warn('Chart skipped:', id, '- data not provided');
        return;
    }
    
    // Validate data structure
    if (!data.labels || !data.datasets || !Array.isArray(data.datasets)) {
        console.warn('Chart skipped:', id, '- invalid data structure');
        return;
    }
    
    // Ensure datasets have valid data arrays
    for (const ds of data.datasets) {
        if (!ds.data || !Array.isArray(ds.data)) {
            // Convert object to array if needed (Twig merge can create objects)
            if (ds.data && typeof ds.data === 'object') {
                ds.data = Object.values(ds.data);
            } else {
                console.warn('Chart skipped:', id, '- dataset has invalid data');
                return;
            }
        }
    }
    
    // Ensure labels is an array
    if (!Array.isArray(data.labels)) {
        if (typeof data.labels === 'object') {
            data.labels = Object.values(data.labels);
        }
    }
    
    try {
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type,
            data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...options
            }
        });
    } catch (error) {
        console.error('Chart error:', id, error);
    }
}
