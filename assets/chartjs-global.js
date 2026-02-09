import { Chart, registerables } from 'chart.js';

// Register Chart.js components globally
Chart.register(...registerables);

// Make Chart available globally for inline scripts
window.Chart = Chart;
