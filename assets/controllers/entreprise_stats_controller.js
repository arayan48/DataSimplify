import { Controller } from '@hotwired/stimulus';

/**
 * Simple charts using HTML/CSS - no external library needed
 */
export default class extends Controller {
    static targets = [
        'wpDistribution',
        'financial',
        'wp2Radar',
        'serviceVsInvoiced'
    ];

    connect() {
        console.log('📊 entreprise-stats controller connected');
        
        // ES modules may load after DOM, so we need a slight delay
        // to ensure window.chartData is available
        requestAnimationFrame(() => {
            setTimeout(() => this.initCharts(), 50);
        });
    }

    initCharts() {
        const data = window.chartData;
        if (!data) {
            console.warn('❌ chartData not found in window');
            // Show empty states on all targets
            if (this.hasWpDistributionTarget) this.wpDistributionTarget.innerHTML = this.noDataHTML('Données non disponibles');
            if (this.hasFinancialTarget) this.financialTarget.innerHTML = this.noDataHTML('Données non disponibles');
            if (this.hasWp2RadarTarget) this.wp2RadarTarget.innerHTML = this.noDataHTML('Données non disponibles');
            if (this.hasServiceVsInvoicedTarget) this.serviceVsInvoicedTarget.innerHTML = this.noDataHTML('Données non disponibles');
            return;
        }

        console.log('📊 chartData found:', data);

        // Render all charts
        if (this.hasWpDistributionTarget && data.wpDistribution) {
            console.log('📊 Rendering WP Distribution...');
            this.renderDonutChart(this.wpDistributionTarget, data.wpDistribution);
        }
        
        if (this.hasFinancialTarget && data.financial) {
            console.log('📊 Rendering Financial...');
            this.renderBarChart(this.financialTarget, data.financial, '€');
        }
        
        if (this.hasWp2RadarTarget && data.wp2Radar) {
            console.log('📊 Rendering WP2 Radar...');
            this.renderRadarBars(this.wp2RadarTarget, data.wp2Radar);
        }
        
        if (this.hasServiceVsInvoicedTarget && data.serviceVsInvoiced) {
            console.log('📊 Rendering Service vs Invoiced...');
            this.renderComparisonChart(this.serviceVsInvoicedTarget, data.serviceVsInvoiced);
        }
    }

    renderDonutChart(target, data) {
        const total = data.values.reduce((a, b) => a + b, 0);
        
        if (total === 0) {
            target.innerHTML = this.noDataHTML('Aucune activité enregistrée');
            return;
        }

        // Build donut with conic-gradient
        let gradientParts = [];
        let currentPercent = 0;
        
        data.values.forEach((value, i) => {
            if (value > 0) {
                const percent = (value / total) * 100;
                gradientParts.push(`${data.colors[i]} ${currentPercent}% ${currentPercent + percent}%`);
                currentPercent += percent;
            }
        });

        // Build legend items
        const legendItems = data.labels.map((label, i) => {
            if (data.values[i] > 0) {
                const percent = ((data.values[i] / total) * 100).toFixed(1);
                return `
                    <div style="display: flex; align-items: center; gap: 8px; padding: 4px 0;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: ${data.colors[i]};"></span>
                        <span style="font-size: 12px; color: #374151;">${label}</span>
                        <span style="font-size: 12px; color: #6b7280; margin-left: auto;">${data.values[i]} (${percent}%)</span>
                    </div>
                `;
            }
            return '';
        }).join('');

        target.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; gap: 24px; height: 100%; padding: 16px;">
                <div style="position: relative; width: 160px; height: 160px;">
                    <div style="width: 100%; height: 100%; border-radius: 50%; background: conic-gradient(${gradientParts.join(', ')});"></div>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80px; height: 80px; border-radius: 50%; background: white; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                        <span style="font-size: 24px; font-weight: 700; color: #1f2937;">${total}</span>
                        <span style="font-size: 11px; color: #6b7280;">Total</span>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    ${legendItems}
                </div>
            </div>
        `;
    }

    renderBarChart(target, data, suffix = '') {
        const max = Math.max(...data.values, 1);
        const hasData = data.values.some(v => v > 0);
        
        if (!hasData) {
            target.innerHTML = this.noDataHTML('Aucune donnée financière');
            return;
        }

        const bars = data.labels.map((label, i) => {
            const percentage = (data.values[i] / max) * 100;
            const displayValue = data.values[i] >= 1000 
                ? (data.values[i] / 1000).toFixed(1) + 'k' + suffix 
                : data.values[i] + suffix;
            
            return `
                <div style="display: flex; flex-direction: column; gap: 4px; flex: 1;">
                    <div style="font-size: 11px; color: #6b7280; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${label}</div>
                    <div style="flex: 1; display: flex; align-items: flex-end; justify-content: center; min-height: 150px;">
                        <div style="width: 40px; height: ${Math.max(percentage, 2)}%; background: ${data.colors[i]}; border-radius: 6px 6px 0 0; transition: height 0.3s ease;"></div>
                    </div>
                    <div style="font-size: 12px; font-weight: 600; color: #374151; text-align: center;">${displayValue}</div>
                </div>
            `;
        }).join('');

        target.innerHTML = `
            <div style="display: flex; gap: 12px; height: 100%; padding: 16px; align-items: stretch;">
                ${bars}
            </div>
        `;
    }

    renderRadarBars(target, data) {
        const max = 5; // DMAO scores are out of 5
        const hasData = data.values.some(v => v > 0);
        
        if (!hasData) {
            target.innerHTML = this.noDataHTML('Aucun diagnostic DMAO');
            return;
        }

        const progressBars = data.labels.map((label, i) => {
            const percentage = (data.values[i] / max) * 100;
            const colors = ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6', '#ec4899', '#14b8a6'];
            
            return `
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; color: #374151; font-weight: 500;">${label}</span>
                        <span style="font-size: 12px; color: #6b7280;">${data.values[i]}/5</span>
                    </div>
                    <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; background: ${colors[i % colors.length]}; border-radius: 4px; width: ${percentage}%; transition: width 0.5s ease;"></div>
                    </div>
                </div>
            `;
        }).join('');

        target.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 12px; height: 100%; padding: 16px; justify-content: center;">
                ${progressBars}
            </div>
        `;
    }

    renderComparisonChart(target, data) {
        const allValues = [...data.service, ...data.invoiced];
        const max = Math.max(...allValues, 1);
        const hasData = allValues.some(v => v > 0);
        
        if (!hasData) {
            target.innerHTML = this.noDataHTML('Aucune facturation');
            return;
        }

        const formatValue = (v) => v >= 1000 ? (v / 1000).toFixed(1) + 'k€' : v + '€';

        const bars = data.categories.map((category, i) => {
            const servicePercent = (data.service[i] / max) * 100;
            const invoicedPercent = (data.invoiced[i] / max) * 100;
            
            return `
                <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                    <div style="font-size: 11px; color: #6b7280; text-align: center;">${category}</div>
                    <div style="flex: 1; display: flex; gap: 8px; align-items: flex-end; justify-content: center; min-height: 100px;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <span style="font-size: 10px; color: #64748b;">${formatValue(data.service[i])}</span>
                            <div style="width: 30px; height: ${Math.max(servicePercent, 3)}%; background: #94a3b8; border-radius: 4px 4px 0 0;"></div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <span style="font-size: 10px; color: #16a34a;">${formatValue(data.invoiced[i])}</span>
                            <div style="width: 30px; height: ${Math.max(invoicedPercent, 3)}%; background: #22c55e; border-radius: 4px 4px 0 0;"></div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        target.innerHTML = `
            <div style="display: flex; flex-direction: column; height: 100%; padding: 12px;">
                <div style="flex: 1; display: flex; gap: 16px;">
                    ${bars}
                </div>
                <div style="display: flex; justify-content: center; gap: 16px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #94a3b8;"></span>
                        <span style="font-size: 11px; color: #6b7280;">Prix Service</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #22c55e;"></span>
                        <span style="font-size: 11px; color: #6b7280;">Facturé</span>
                    </div>
                </div>
            </div>
        `;
    }

    noDataHTML(message) {
        return `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #6b7280; background: #f9fafb; border-radius: 8px; border: 1px dashed #d1d5db;">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 8px; color: #9ca3af;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span style="font-size: 13px; font-weight: 500;">${message}</span>
            </div>
        `;
    }
}
