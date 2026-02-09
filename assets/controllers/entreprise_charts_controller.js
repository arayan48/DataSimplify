import { Controller } from '@hotwired/stimulus';
import { Chart } from 'chart.js';

export default class extends Controller {
    static targets = [
        'wpDistribution',
        'wp2Radar',
        'wp5Timeline',
        'financial'
    ];

    connect() {
        // 1. WP Distribution
        if (this.hasWpDistributionTarget && window.wpDistributionChartData) {
            new Chart(this.wpDistributionTarget, window.wpDistributionChartData.config);
        }
        // 2. WP2 Radar
        if (this.hasWp2RadarTarget && window.wp2RadarChartData) {
            new Chart(this.wp2RadarTarget, window.wp2RadarChartData.config);
        }
        // 3. WP5 Timeline
        if (this.hasWp5TimelineTarget && window.wp5TimelineChartData) {
            new Chart(this.wp5TimelineTarget, window.wp5TimelineChartData.config);
        }
        // 4. Financial
        if (this.hasFinancialTarget && window.financialChartData) {
            new Chart(this.financialTarget, window.financialChartData.config);
        }
    }
}
