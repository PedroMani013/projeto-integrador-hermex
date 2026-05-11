'use strict';

(function () {

    //sidebar drawer mobile

    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const btnAbrir = document.getElementById('btnSidebar');

    function abrirSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        btnAbrir.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function fecharSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        btnAbrir.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (btnAbrir) {
        btnAbrir.addEventListener('click', function () {
            sidebar.classList.contains('open') ? fecharSidebar() : abrirSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', fecharSidebar);
    }

    // fechar sidebar ao pressionar Esc
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            fecharSidebar();
            btnAbrir && btnAbrir.focus();
        }
    });

    // botão atualizar

    const btnAtualizar = document.getElementById('btnAtualizar');
    if (btnAtualizar) {
        btnAtualizar.addEventListener('click', function () {
            this.disabled = true;
            this.setAttribute('aria-label', 'Atualizando...');

            // ícone de spin inline via transform
            const svg = this.querySelector('svg');
            if (svg) {
                svg.style.transition = 'transform 0.6s linear';
                svg.style.transform  = 'rotate(360deg)';
            }

            setTimeout(() => { window.location.reload(); }, 500);
        });
    }

    // Busca: submete com Enter

    const campoBusca = document.querySelector('[role="search"] input');
    if (campoBusca) {
        campoBusca.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }

    // hora no subtítulo (atualiza a cada minuto)

    function formatarHora(d) {
        const meses = ['jan.','fev.','mar.','abr.','mai.','jun.','jul.','ago.','set.','out.','nov.','dez.'];
        const dia = d.getDate();
        const mes = meses[d.getMonth()];
        const ano = d.getFullYear();
        const h   = String(d.getHours()).padStart(2, '0');
        const min = String(d.getMinutes()).padStart(2, '0');
        return `${dia} de ${mes} de ${ano} às ${h}:${min}`;
    }

    const elTime = document.querySelector('.page-subtitle time');
    if (elTime) {
        setInterval(function () {
            const agora = new Date();
            elTime.textContent = formatarHora(agora);
            elTime.setAttribute('datetime', agora.toISOString().slice(0, 16));
        }, 60_000);
    }

    // Sparklines nos cards de indicador 
    // Desenha mini-gráficos de linha simples com Canvas 2D

    function desenharSparkline(canvasId, valores, cor) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !canvas.getContext) return;

        const ctx = canvas.getContext('2d');
        const w   = canvas.width;
        const h   = canvas.height;
        const max = Math.max(...valores);
        const min = Math.min(...valores);
        const range = max - min || 1;
        const pad   = 3;

        ctx.clearRect(0, 0, w, h);

        const pts = valores.map((v, i) => ({
            x: pad + (i / (valores.length - 1)) * (w - pad * 2),
            y: h - pad - ((v - min) / range) * (h - pad * 2),
        }));

        // gradiente de preenchimento
        const grad = ctx.createLinearGradient(0, 0, 0, h);
        grad.addColorStop(0, cor.replace(')', ', 0.25)').replace('rgb', 'rgba'));
        grad.addColorStop(1, cor.replace(')', ', 0)').replace('rgb', 'rgba'));

        ctx.beginPath();
        pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
        ctx.lineTo(pts[pts.length - 1].x, h);
        ctx.lineTo(pts[0].x, h);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        // Linha
        ctx.beginPath();
        pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
        ctx.strokeStyle = cor;
        ctx.lineWidth   = 1.5;
        ctx.lineJoin    = 'round';
        ctx.stroke();
    }

    //valores ficticios
    const dadosSparkline = {
        'sparkline-transito':  [28, 31, 29, 33, 30, 34, window.HERMEX_INDICADORES ? window.HERMEX_INDICADORES.transito : 33],
        'sparkline-anomalias': [2, 1, 3, 2, 4, 2, window.HERMEX_INDICADORES ? window.HERMEX_INDICADORES.anomalias : 3],
        'sparkline-entregues': [8, 10, 9, 11, 10, 12, window.HERMEX_INDICADORES ? window.HERMEX_INDICADORES.entregues : 11],
        'sparkline-alertas':   [1, 2, 1, 3, 2, 3, window.HERMEX_INDICADORES ? window.HERMEX_INDICADORES.alertas : 3],
    };

    const coresSparkline = {
        'sparkline-transito':  'rgb(29, 78, 216)',
        'sparkline-anomalias': 'rgb(133, 77, 14)',
        'sparkline-entregues': 'rgb(22, 101, 52)',
        'sparkline-alertas':   'rgb(153, 27, 27)',
    };

    Object.entries(dadosSparkline).forEach(([id, vals]) => {
        desenharSparkline(id, vals, coresSparkline[id]);
    });

})();
