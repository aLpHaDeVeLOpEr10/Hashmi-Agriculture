import {
    Chart,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Tooltip,
);

export const palette = ['#2f855a', '#3182ce', '#c27c0e', '#805ad5', '#b7472a', '#0d9488'];

const ink = { primary: '#292524', secondary: '#57534e', muted: '#a8a29e', grid: '#f0eeec', surface: '#ffffff' };

Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = ink.secondary;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.animation.duration = 500;

const compact = new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 });
const full = new Intl.NumberFormat('en', { maximumFractionDigits: 1 });

function formatter(format, unit) {
    return (value, precise = false) => {
        const n = precise ? full.format(value) : compact.format(value);
        if (format === 'currency') return `PKR ${n}`;
        if (format === 'percent') return `${n}%`;
        return unit ? `${n} ${unit}` : n;
    };
}

function hexToRgba(hex, alpha) {
    const value = parseInt(hex.slice(1), 16);
    return `rgba(${(value >> 16) & 255}, ${(value >> 8) & 255}, ${value & 255}, ${alpha})`;
}

function buildDatasets(config) {
    const grouped = !config.stacked && config.datasets.length > 1;

    return config.datasets.map((dataset, index) => {
        const color = palette[dataset.color ?? index];

        if (config.type === 'doughnut') {
            const colors = dataset.data.map((_, i) => palette[dataset.colors?.[i] ?? i % palette.length]);
            return { ...dataset, backgroundColor: colors, borderColor: ink.surface, borderWidth: 2, hoverOffset: 4 };
        }

        if (config.type === 'line') {
            return {
                ...dataset,
                borderColor: color,
                backgroundColor: dataset.fill ? hexToRgba(color, 0.08) : color,
                borderWidth: 2,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBorderWidth: 2,
                pointHoverBorderColor: ink.surface,
                pointBackgroundColor: color,
                borderDash: dataset.dashed ? [5, 4] : [],
            };
        }

        return {
            ...dataset,
            backgroundColor: color,
            hoverBackgroundColor: hexToRgba(color, 0.85),
            borderRadius: 4,
            borderSkipped: 'start',
            borderColor: ink.surface,
            borderWidth: config.stacked ? { top: 2 } : 0,
            maxBarThickness: grouped ? 36 : 28,
            categoryPercentage: grouped ? 0.6 : 0.7,
            barPercentage: grouped ? 0.94 : 0.85,
        };
    });
}

function buildOptions(config) {
    const fmt = formatter(config.format, config.unit);
    const horizontal = config.type === 'hbar';
    const showLegend = config.legend ?? (config.type === 'doughnut' || config.datasets.length > 1);

    const tooltip = {
        backgroundColor: ink.primary,
        titleColor: '#fff',
        bodyColor: '#e7e5e4',
        padding: 10,
        cornerRadius: 8,
        boxPadding: 4,
        usePointStyle: true,
        titleFont: { weight: '600' },
        callbacks: {
            label(context) {
                const raw = context.raw;
                if (config.type === 'doughnut') {
                    const total = context.dataset.data.reduce((sum, v) => sum + v, 0);
                    return ` ${context.label}: ${fmt(raw, true)} (${Math.round((raw / total) * 100)}%)`;
                }
                return ` ${context.dataset.label}: ${fmt(raw, true)}`;
            },
        },
    };

    const legend = {
        display: showLegend,
        position: config.type === 'doughnut' ? 'bottom' : 'top',
        align: config.type === 'doughnut' ? 'center' : 'end',
        labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 8, boxHeight: 8, padding: 14, color: ink.secondary },
    };

    if (config.type === 'doughnut') {
        return { cutout: '68%', plugins: { legend, tooltip } };
    }

    const valueAxis = {
        beginAtZero: config.type !== 'line' || config.beginAtZero !== false,
        stacked: !!config.stacked,
        grid: { color: ink.grid, drawTicks: false },
        border: { display: false },
        ticks: { padding: 8, color: ink.muted, maxTicksLimit: 6, callback: (v) => fmt(v) },
    };

    const categoryAxis = {
        stacked: !!config.stacked,
        grid: { display: false },
        border: { color: '#e7e5e4' },
        ticks: { padding: 6, color: horizontal ? ink.secondary : ink.muted },
    };

    return {
        indexAxis: horizontal ? 'y' : 'x',
        interaction: { mode: 'index', intersect: false },
        layout: { padding: { top: 4 } },
        scales: horizontal ? { x: valueAxis, y: categoryAxis } : { x: categoryAxis, y: valueAxis },
        plugins: { legend, tooltip },
    };
}

export function initCharts(root = document) {
    root.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        if (canvas.chart) return;
        const config = JSON.parse(canvas.dataset.chart);

        canvas.chart = new Chart(canvas, {
            type: config.type === 'hbar' ? 'bar' : config.type,
            data: { labels: config.labels, datasets: buildDatasets(config) },
            options: buildOptions(config),
        });
    });
}
