(() => {
    'use strict';

    let indiceProduto = 0;

    // ViaCEP para endereço do cliente
    const cepInput = document.getElementById('cliente_cep');
    const cepStatus = document.getElementById('cep-status');

    if (cepInput) {
        cepInput.addEventListener('input', e => {
            e.target.value = e.target.value
                .replace(/\D/g, '')
                .replace(/^(\d{5})(\d)/, '$1-$2')
                .slice(0, 9);
        });

        cepInput.addEventListener('blur', async () => {
            const cepLimpo = cepInput.value.replace(/\D/g, '');
            if (cepLimpo.length !== 8) return;

            cepStatus.textContent = '…';
            cepStatus.style.color = '#6c757d';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000);

            try {
                const res  = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`, { signal: controller.signal });
                const data = await res.json();

                if (data.erro) {
                    cepStatus.textContent = '✗';
                    cepStatus.style.color = '#dc3545';
                    return;
                }

                document.getElementById('cliente_logradouro').value = data.logradouro ?? '';
                document.getElementById('cliente_bairro').value     = data.bairro     ?? '';
                document.getElementById('cliente_cidade').value     = data.localidade  ?? '';
                document.getElementById('cliente_uf').value         = data.uf          ?? '';

                cepStatus.textContent = '✓';
                cepStatus.style.color = '#198754';

            } catch {
                cepStatus.textContent = '✗';
                cepStatus.style.color = '#dc3545';
            } finally {
                clearTimeout(timeout);
            }
        });
    }

    // Linha de produto
    function criarLinhaProduto() {
        const i = indiceProduto++;

        const opcoesCategorias = CATEGORIAS.map(c =>
            `<option value="${c.codigo}" data-tolerancia="${c.tolerancia_padrao}">${c.nome}</option>`
        ).join('');

        const div = document.createElement('div');
        div.className = 'row g-3 align-items-end mb-3 produto-linha';
        div.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Nome do Produto</label>
                <input type="text" name="produtos[${i}][nome]"
                       class="form-control" placeholder="Conector RJ45" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">SKU</label>
                <input type="text" name="produtos[${i}][sku]"
                       class="form-control" placeholder="HM-ELE-0042">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Categoria</label>
                <select name="produtos[${i}][categoria]"
                        class="form-select categoria-select" required>
                    <option value="">Selecione...</option>
                    ${opcoesCategorias}
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold">Qtd</label>
                <input type="number" name="produtos[${i}][quantidade]"
                       class="form-control" min="1" placeholder="10" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Peso unit. (g)</label>
                <input type="number" name="produtos[${i}][peso_unitario]"
                       class="form-control" min="1" placeholder="15" required>
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold">Toler. (%)</label>
                <input type="number" name="produtos[${i}][tolerancia]"
                       class="form-control tolerancia-input" min="0.1" max="100" step="0.1" placeholder="5" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-remover">✕</button>
            </div>
        `;

        // pré-preenche tolerância ao selecionar categoria
        div.querySelector('.categoria-select').addEventListener('change', function () {
            const cat = CATEGORIAS.find(c => c.codigo === this.value);
            if (cat) {
                div.querySelector('.tolerancia-input').value = cat.tolerancia_padrao;
            }
        });

        div.querySelector('.btn-remover').addEventListener('click', () => div.remove());

        return div;
    }

    document.getElementById('btn-adicionar-produto').addEventListener('click', () => {
        document.getElementById('produtos-lista').appendChild(criarLinhaProduto());
    });

    // adiciona primeira linha automaticamente
    document.getElementById('produtos-lista').appendChild(criarLinhaProduto());

})();
