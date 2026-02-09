import { Controller } from '@hotwired/stimulus';
import * as echarts from 'echarts';

export default class extends Controller {
    static targets = [
        'wpDistribution',
        'financial',
        'wp2Radar',
        'serviceVsInvoiced'
    ];

    charts = [];

    connect() {
        console.log('📊 ECharts Controller: connect() called');
        console.log('📊 echarts module:', echarts);
        console.log('📊 window.chartData:', window.chartData);
        
        // Wait for DOM to be fully ready
        if (document.readyState === 'complete') {
            this.initWithDelay();
        } else {
            window.addEventListener('load', () => this.initWithDelay());
        }
    }

    initWithDelay() {
        setTimeout(() => this.initCharts(), 200);
    }

    initCharts() {
        console.log('📊 initCharts() called');
        
        const data = window.chartData;
        if (!data) {
            console.error('❌ window.chartData is undefined!');
            this.showErrorOnAllTargets('Données non disponibles');
            return;
        }

        console.log('📊 Data loaded:', JSON.stringify(data, null, 2));
        console.log('📊 Targets - wpDist:', this.hasWpDistributionTarget, 'financial:', this.hasFinancialTarget);

        try {
            if (this.hasWpDistributionTarget && data.wpDistribution) {
                console.log('📊 Rendering WP Distribution...');
                this.renderDonut(this.wpDistributionTarget, data.wpDistribution);
            }

            if (this.hasFinancialTarget && data.financial) {
                console.log('📊 Rendering Financial...');
                this.renderFinancialBar(this.financialTarget, data.financial);
            }

            if (this.hasWp2RadarTarget && data.wp2Radar) {
                console.log('📊 Rendering Radar...');
                this.renderRadar(this.wp2RadarTarget, data.wp2Radar);
            }

            if (this.hasServiceVsInvoicedTarget && data.serviceVsInvoiced) {
                console.log('📊 Rendering Comparison...');
                this.renderComparisonBar(this.serviceVsInvoicedTarget, data.serviceVsInvoiced);
            }

            setTimeout(() => this.resizeCharts(), 100);
        } catch (error) {
            console.error('❌ Error initializing charts:', error);
        }

        window.addEventListener('resize', () => this.resizeCharts());
    }

    showErrorOnAllTargets(message) {
        [this.wpDistributionTarget, this.financialTarget, this.wp2RadarTarget, this.serviceVsInvoicedTarget]
            .filter(Boolean)
            .forEach(target => this.showNoData(target, message));
    }

    disconnect() {
        this.charts.forEach(chart => { try { chart.dispose(); } catch (e) {} });
        this.charts = [];
    }

    resizeCharts() {
        this.charts.forEach(chart => { try { chart.resize(); } catch (e) {} });
    }

    showNoData(target, message = 'Aucune donnée disponible') {
        target.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:200px;color:#6b7280;background:#f9fafb;border-radius:8px;border:2px dashed #d1d5db;"><svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:0.5rem;color:#9ca3af;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span style="font-size:0.875rem;font-weight:500;">' + message + '</span></div>';
    }

    renderDonut(target, data) {
        console.log('📊 renderDonut() - target:', target.offsetWidth, 'x', target.offsetHeight);
        
        const filteredData = data.labels
            .map((label, i) => ({ name: label, value: data.values[i], itemStyle: { color: data.colors[i] } }))
            .filter(item => item.value > 0);

        console.log('📊 filteredData:', filteredData);

        if (filteredData.length === 0) {
            this.showNoData(target, 'Aucune activité enregistrée');
            return;
        }

        target.style.width = '100%';
        target.style.height = '280px';
        target.style.minHeight = '280px';

        try {
            const chart = echarts.init(target, null, { renderer: 'canvas' });
            console.log('📊 Chart created:', chart);
            this.charts.push(chart);

            chart.setOption({
                backgroundColor: 'transparent',
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: { bottom: 10, left: 'center', textStyle: { fontSize: 11, color: '#374151' } },
                series: [{
                    name: 'Work Packages',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['50%', '45%'],
                    avoidLabelOverlap: false,
                    itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 2 },
                    label: { show: false },
                    emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
                    labelLine: { show: false },
                    data: filteredData
                }]
            });
            console.log('📊 Donut rendered OK');
        } catch (error) {
            console.error('❌ Donut error:', error);
            target.innerHTML = '<div style="color:red;padding:20px;">Erreur: ' + error.message + '</div>';
        }
    }

    renderFinancialBar(target, data) {
        const hasData = data.values.some(v => v > 0);
        if (!hasData) { this.showNoData(target, 'Aucune donnée financière'); return; }

        target.style.width = '100%';
        target.style.height = '280px';

        try {
            const chart = echarts.init(target, null, { renderer: 'canvas' });
            this.charts.push(chart);
            chart.setOption({
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '3%', right: '4%', bottom: '15%', top: '10%', containLabel: true },
                xAxis: { type: 'category', data: data.labels, axisLabel: { fontSize: 11, interval: 0, rotate: 15, color: '#374151' } },
                yAxis: { type: 'value', axisLabel: { formatter: (v) => v >= 1000 ? (v/1000) + 'k€' : v + '€', color: '#374151' } },
                series: [{ data: data.values.map((v, i) => ({ value: v, itemStyle: { color: data.colors[i] } })), type: 'bar', barWidth: '60%', itemStyle: { borderRadius: [6, 6, 0, 0] } }]
            });
            console.log('📊 Financial bar rendered OK');
        } catch (error) {
            console.error('❌ Financial error:', error);
            target.innerHTML = '<div style="color:red;padding:20px;">Erreur: ' + error.message + '</div>';
        }
    }

    renderRadar(target, data) {
        const hasData = data.values.some(v => v > 0);
        if (!hasData) { this.showNoData(target, 'Aucun diagnostic DMAO'); return; }

        target.style.width = '100%';
        target.style.height = '280px';

        try {
            const chart = echarts.init(target, null, { renderer: 'canvas' });
            this.charts.push(chart);
            chart.setOption({
                backgroundColor: 'transparent',
                tooltip: { trigger: 'item' },
                radar: {
                    indicator: data.labels.map(name => ({ name, max: 5 })),
                    shape: 'polygon',
                    splitNumber: 5,
                    axisName: { color: '#374151', fontSize: 11 },
                    splitLine: { lineStyle: { color: '#e5e7eb' } },
                    splitArea: { show: true, areaStyle: { color: ['#fff', '#f9fafb'] } },
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                series: [{ type: 'radar', data: [{ value: data.values, name: 'Score DMAO', areaStyle: { color: 'rgba(59, 130, 246, 0.3)' }, lineStyle: { color: '#3b82f6', width: 2 }, itemStyle: { color: '#3b82f6' } }] }]
            });
            console.log('📊 Radar rendered OK');
        } catch (error) {
            console.error('❌ Radar error:', error);
        }
    }

    renderComparisonBar(target, data) {
        const hasData = data.service.some(v => v > 0) || data.invoiced.some(v => v > 0);
        if (!hasData) { this.showNoData(target, 'Aucune facturation'); return; }

        target.style.width = '100%';
        target.style.height = '200px';

        try {
            const chart = echarts.init(target, null, { renderer: 'canvas' });
            this.charts.push(chart);
            chart.setOption({
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: ['Prix Service', 'Facturé'], bottom: 5, textStyle: { color: '#374151' } },
                grid: { left: '3%', right: '4%', bottom: '20%', top: '10%', containLabel: true },
                xAxis: { type: 'category', data: data.categories, axisLabel: { color: '#374151' } },
                yAxis: { type: 'value', axisLabel: { formatter: (v) => v >= 1000 ? (v/1000) + 'k€' : v + '€', color: '#374151' } },
                series: [
                    { name: 'Prix Service', type: 'bar', data: data.service, itemStyle: { color: '#94a3b8', borderRadius: [4, 4, 0, 0] }, barGap: '10%' },
                    { name: 'Facturé', type: 'bar', data: data.invoiced, itemStyle: { color: '#22c55e', borderRadius: [4, 4, 0, 0] } }
                ]
            });
            console.log('📊 Comparison rendered OK');
        } catch (error) {
            console.error('❌ Comparison error:', error);
        }
    }
}
