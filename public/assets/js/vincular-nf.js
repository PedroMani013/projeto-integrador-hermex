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

    // Importação de NF-e XML
    const btnImportar = document.getElementById('btn-importar-xml');
    const inputXml    = document.getElementById('input-xml-nfe');
    const xmlStatus   = document.getElementById('xml-status');

    if (btnImportar && inputXml) {
        btnImportar.addEventListener('click', () => inputXml.click());

        inputXml.addEventListener('change', () => {
            const file = inputXml.files[0];
            if (!file) return;

            xmlStatus.textContent = 'Lendo arquivo…';
            xmlStatus.className = 'text-secondary small mt-2';

            const reader = new FileReader();
            reader.onload = e => {
                try {
                    preencherComXml(e.target.result);
                    xmlStatus.textContent = '✓ NF-e importada com sucesso.';
                    xmlStatus.className = 'text-success small mt-2';
                } catch (err) {
                    xmlStatus.textContent = '✗ ' + err.message;
                    xmlStatus.className = 'text-danger small mt-2';
                }
                inputXml.value = '';
            };
            reader.onerror = () => {
                xmlStatus.textContent = '✗ Erro ao ler o arquivo.';
                xmlStatus.className = 'text-danger small mt-2';
            };
            reader.readAsText(file, 'UTF-8');
        });
    }

    function preencherComXml(xmlString) {
        const parser = new DOMParser();
        const doc    = parser.parseFromString(xmlString, 'application/xml');

        if (doc.querySelector('parsererror')) {
            throw new Error('Arquivo XML inválido.');
        }

        // helper: busca tag ignorando namespace
        function tag(el, nome) {
            return el.querySelector(nome) || el.getElementsByTagName(nome)[0] || null;
        }
        function texto(el, nome) {
            const node = tag(el, nome);
            return node ? node.textContent.trim() : '';
        }

        const nfeProc = tag(doc, 'nfeProc') || tag(doc, 'NFe');
        if (!nfeProc) throw new Error('Estrutura NF-e não reconhecida.');

        // Número da NF
        const nNF = texto(doc, 'nNF');
        const serie = texto(doc, 'serie');
        if (nNF) {
            const numNf = serie ? `${serie}/${nNF}` : nNF;
            const campoNumero = document.getElementById('numero_nf');
            if (campoNumero) campoNumero.value = numNf;
        }

        // Destinatário
        const dest = tag(doc, 'dest');
        if (dest) {
            const nome = texto(dest, 'xNome');
            const cnpj = texto(dest, 'CNPJ') || texto(dest, 'CPF');
            const end  = tag(dest, 'enderDest');

            const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };

            set('cliente_nome',      nome);
            set('cliente_documento', cnpj);

            if (end) {
                set('cliente_logradouro', texto(end, 'xLgr'));
                set('cliente_numero',     texto(end, 'nro'));
                set('cliente_bairro',     texto(end, 'xBairro'));
                set('cliente_cidade',     texto(end, 'xMun'));
                set('cliente_uf',         texto(end, 'UF'));

                const cep = texto(end, 'CEP').replace(/\D/g, '');
                if (cep.length === 8) {
                    const cepEl = document.getElementById('cliente_cep');
                    if (cepEl) cepEl.value = cep.replace(/^(\d{5})(\d{3})$/, '$1-$2');
                }
            }
        }

        // Produtos (itens da NF-e: tag <det>)
        const itens = doc.querySelectorAll ? doc.querySelectorAll('det') : doc.getElementsByTagName('det');
        if (!itens || itens.length === 0) throw new Error('Nenhum item (det) encontrado na NF-e.');

        // limpa linhas existentes
        document.getElementById('produtos-lista').innerHTML = '';
        indiceProduto = 0;

        itens.forEach(det => {
            const prod    = tag(det, 'prod');
            if (!prod) return;

            const nome    = texto(prod, 'xProd');
            const sku     = texto(prod, 'cProd');
            const qtd     = parseFloat(texto(prod, 'qCom') || texto(prod, 'qTrib') || '1') || 1;

            // Peso: NF-e usa pesoL/pesoB em kg por unidade — converte para gramas
            const pesoLiq = parseFloat(texto(prod, 'pesoL') || '0');
            const pesoBru = parseFloat(texto(prod, 'pesoB') || '0');
            const pesoKg  = pesoLiq > 0 ? pesoLiq : pesoBru;
            const pesoGramas = pesoKg > 0 ? Math.round((pesoKg / qtd) * 1000) : 0;

            const linha = criarLinhaProduto();
            linha.querySelector(`[name$="[nome]"]`).value        = nome;
            linha.querySelector(`[name$="[sku]"]`).value         = sku;
            linha.querySelector(`[name$="[quantidade]"]`).value  = Math.round(qtd);
            linha.querySelector(`[name$="[peso_unitario]"]`).value = pesoGramas || '';

            document.getElementById('produtos-lista').appendChild(linha);
        });
    }

})();
