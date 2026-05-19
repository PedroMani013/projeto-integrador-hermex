(() => {
    'use strict';

    const cepInput      = document.getElementById('cep');
    const statusIcon    = document.getElementById('cep-status');
    const erroDiv       = document.getElementById('cep-erro');
    const camposViaCep  = {
        endereco:    document.getElementById('endereco'),
        bairro:      document.getElementById('bairro'),
        cidade:      document.getElementById('cidade'),
        uf:          document.getElementById('uf'),
    };

    if (!cepInput) return;

    function formatarCep(valor) {
        return valor.replace(/\D/g, '').replace(/^(\d{5})(\d)/, '$1-$2').slice(0, 9);
    }

    function limparEndereco() {
        Object.values(camposViaCep).forEach(el => { if (el) el.value = ''; });
    }

    function exibirErro(msg) {
        erroDiv.textContent = msg;
        erroDiv.classList.remove('d-none');
        statusIcon.textContent = '✗';
        statusIcon.style.color = '#dc3545';
    }

    function limparErro() {
        erroDiv.textContent = '';
        erroDiv.classList.add('d-none');
        statusIcon.textContent = '';
        statusIcon.style.color = '';
    }

    async function buscarCep(cep) {
        const cepLimpo = cep.replace(/\D/g, '');

        if (cepLimpo.length !== 8) return;

        statusIcon.textContent = '…';
        statusIcon.style.color = '#6c757d';
        limparErro();

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);

        try {
            const res = await fetch(
                `https://viacep.com.br/ws/${cepLimpo}/json/`,
                { signal: controller.signal }
            );

            if (!res.ok) throw new Error('Serviço indisponível');

            const dados = await res.json();

            if (dados.erro) {
                limparEndereco();
                exibirErro('CEP não encontrado.');
                return;
            }

            if (camposViaCep.endereco) camposViaCep.endereco.value = dados.logradouro ?? '';
            if (camposViaCep.bairro)   camposViaCep.bairro.value   = dados.bairro     ?? '';
            if (camposViaCep.cidade)   camposViaCep.cidade.value   = dados.localidade  ?? '';
            if (camposViaCep.uf)       camposViaCep.uf.value       = dados.uf          ?? '';

            statusIcon.textContent = '✓';
            statusIcon.style.color = '#198754';

        } catch (err) {
            limparEndereco();
            if (err.name === 'AbortError') {
                exibirErro('Tempo esgotado ao consultar o CEP. Preencha o endereço manualmente.');
            } else {
                exibirErro('Falha ao consultar o CEP. Preencha o endereço manualmente.');
            }
        } finally {
            clearTimeout(timeout);
        }
    }

    cepInput.addEventListener('input', e => {
        e.target.value = formatarCep(e.target.value);
    });

    cepInput.addEventListener('blur', e => {
        buscarCep(e.target.value);
    });
})();
