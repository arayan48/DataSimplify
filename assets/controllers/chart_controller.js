import { Controller } from '@hotwired/stimulus';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

export default class extends Controller {
    static values = {
        type: String,
        data: Object,
        options: Object
    }

    connect() {
        this.chart = new Chart(this.element, {
            type: this.typeValue || 'bar',
            data: this.dataValue,
            options: this.optionsValue || {}
        });
    }

    disconnect() {
        if (this.chart) {
            this.chart.destroy();
        }
    }
}
