/**
 * HermeX — Gráfico de Integridade da Cadeia (Chart.js)
 * Consome window.HERMEX_DADOS_INTEGRIDADE injetado pela view PHP.
 */

'use strict';

(function () {

    const canvas = document.getElementById('graficoIntegridade');
    if (!canvas || typeof Chart === 'undefined') return;

    const dados = window.HERMEX_DADOS_INTEGRIDADE || [];
    if (dados.length === 0) return;

    // Formatar datas no eixo X: "10 mai." (pt-BR resumido)
    const meses = ['jan.','fev.','mar.','abr.','mai.','jun.','jul.','ago.','set.','out.','nov.','dez.'];

    function formatarData(iso) {
        const [, m, d] = iso.split('-');
        return `${parseInt(d, 10)} ${meses[parseInt(m, 10) - 1]}`;
    }

    const rotulos     = dados.map(p => formatarData(p.data));
    const percentuais = dados.map(p => p.percentual);

    // Gradiente de preenchimento
    const ctx  = canvas.getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 240);
    grad.addColorStop(0, 'rgba(29, 78, 216, 0.15)');
    grad.addColorStop(1, 'rgba(29, 78, 216, 0)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: rotulos,
            datasets: [{
                label: 'Integridade (%)',
                data: percentuais,
                fill: true,
                backgroundColor: grad,
                borderColor: '#1d4ed8',
                borderWidth: 2,
                pointBackgroundColor: '#1d4ed8',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#f8fafc',
                    bodyColor: '#cbd5e1',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: ctx => `Integridade: ${ctx.parsed.y.toFixed(1)}%`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        maxRotation: 0,
                    },
                    border: { display: false },
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: v => `${v}%`,
                        stepSize: 25,
                    },
                    border: { display: false },
                },
            },
        },
    });

})();
