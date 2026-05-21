<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Recepção NFC — hermeX</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <style>
        body {
            background: #f4f4f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
        }
        .recepcao-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
            text-align: center;
        }
        .logo-texto {
            font-size: 22px;
            font-weight: 800;
            color: #d4b54c;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .nfc-icone {
            font-size: 72px;
            line-height: 1;
            margin: 24px 0 16px;
            user-select: none;
        }
        .instrucao {
            font-size: 17px;
            color: #444;
            margin-bottom: 28px;
        }
        .aviso-bipar {
            font-size: 13px;
            color: #888;
            background: #fff8e1;

            padding: 10px 14px;
            border-radius: 6px;
            text-align: left;
            margin-bottom: 28px;
        }
        .status-box {
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 15px;
            display: none;
        }
        .status-sucesso { background: #d1fae5; color: #065f46; }
        .status-erro    { background: #fee2e2; color: #991b1b; }
        .status-lendo   { background: #e0f2fe; color: #0c4a6e; }
        .btn-hermex {
            background: #d4b54c;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-hermex:hover { background: #b8963a; }
        .btn-hermex:disabled { background: #ccc; cursor: not-allowed; }
        #secaoFallback { display: none; margin-top: 24px; }
        .divisor { color: #aaa; font-size: 13px; margin: 20px 0; }
    </style>
</head>
<body>

<div class="recepcao-card">

    <div class="logo-texto">hermeX</div>
    <div style="color:#888;font-size:13px;margin-bottom:4px;">Recepção de carga</div>

    <div class="nfc-icone" id="iconeNfc">📡</div>

    <div class="instrucao" id="textoInstrucao">
        Aproxime o celular da tag NFC da caixa para registrar a chegada
    </div>

    <div class="aviso-bipar">
        ⚠ <strong>Bipar antes de abrir.</strong> Abrir a caixa antes do registro de chegada
        será detectado como anomalia.
    </div>

    <!-- STATUS -->
    <div class="status-box status-lendo"  id="statusLendo">Lendo tag NFC…</div>
    <div class="status-box status-sucesso" id="statusSucesso"></div>
    <div class="status-box status-erro"    id="statusErro"></div>

    <!-- BOTÃO NFC (só aparece quando NFC disponível) -->
    <button class="btn-hermex" id="btnNfc" style="display:none">
        Iniciar leitura NFC
    </button>

    <!-- FALLBACK MANUAL -->
    <div id="secaoFallback">
        <div class="divisor">— ou informe o código manualmente —</div>
        <div class="input-group">
            <input type="text"
                   id="inputCodigo"
                   class="form-control rounded-start-3"
                   placeholder="ID da tag ou código da caixa"
                   autocomplete="off"
                   inputmode="text">
            <button class="btn btn-dark rounded-end-3 px-3" id="btnManual">
                Confirmar
            </button>
        </div>
    </div>

    <div style="margin-top:24px;">
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?action=caixas"
           style="color:#888;font-size:13px;text-decoration:none;">
            ← Voltar para o sistema
        </a>
    </div>

</div>

<script>
(function () {
    const API = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?action=api-evento';

    const btnNfc      = document.getElementById('btnNfc');
    const btnManual   = document.getElementById('btnManual');
    const inputCodigo = document.getElementById('inputCodigo');
    const secaoFallback = document.getElementById('secaoFallback');

    const boxLendo  = document.getElementById('statusLendo');
    const boxSucesso= document.getElementById('statusSucesso');
    const boxErro   = document.getElementById('statusErro');
    const icone     = document.getElementById('iconeNfc');
    const instrucao = document.getElementById('textoInstrucao');

    function mostrar(box, msg) {
        [boxLendo, boxSucesso, boxErro].forEach(b => {
            b.style.display = 'none';
            b.textContent = '';
        });
        box.textContent = msg;
        box.style.display = 'block';
    }

    function exibirSucesso(codigo, jaEntregue) {
        icone.textContent = '✅';
        if (jaEntregue) {
            instrucao.textContent = 'Entrega já registrada anteriormente.';
            mostrar(boxSucesso, `Caixa ${codigo} já foi recebida.`);
        } else {
            instrucao.textContent = 'Entrega registrada com sucesso!';
            mostrar(boxSucesso, `Caixa ${codigo} recebida. Pode abrir!`);
        }
        btnNfc.disabled = true;
        inputCodigo.disabled = true;
        btnManual.disabled = true;
    }

    function exibirErro(msg) {
        icone.textContent = '❌';
        mostrar(boxErro, msg);
    }

    async function enviarTag(tagNfc) {
        mostrar(boxLendo, 'Registrando chegada…');
        try {
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo: 'nfc', tag_nfc: tagNfc }),
            });
            const json = await res.json();
            if (json.ok) {
                if (json.ja_entregue) {
                    exibirSucesso(json.codigo || tagNfc, true);
                } else {
                    exibirSucesso(json.codigo || tagNfc, false);
                }
            } else {
                exibirErro(json.erro || 'Erro desconhecido.');
            }
        } catch (e) {
            exibirErro('Falha de conexão com o servidor.');
        }
    }

    // Web NFC API
    if ('NDEFReader' in window) {
        btnNfc.style.display = 'block';
        secaoFallback.style.display = 'block'; // mostra fallback também

        btnNfc.addEventListener('click', async () => {
            btnNfc.disabled = true;
            mostrar(boxLendo, 'Aguardando leitura NFC…');
            try {
                const reader = new NDEFReader();
                await reader.scan();
                reader.onreadingerror = () => {
                    exibirErro('Erro ao ler a tag. Tente novamente.');
                    btnNfc.disabled = false;
                };
                reader.onreading = ({ serialNumber, message }) => {
                    // usa o número de série da tag como identificador
                    const tagId = serialNumber || (message?.records?.[0]?.data
                        ? new TextDecoder().decode(message.records[0].data)
                        : null);
                    if (!tagId) {
                        exibirErro('Tag lida mas sem identificador. Use o fallback manual.');
                        btnNfc.disabled = false;
                        return;
                    }
                    enviarTag(tagId);
                };
            } catch (e) {
                // permissão negada ou outro erro
                exibirErro('NFC não disponível ou permissão negada. Use o campo manual abaixo.');
                btnNfc.disabled = false;
            }
        });

    } else {
        // dispositivo sem Web NFC — mostra só o fallback
        instrucao.textContent = 'NFC não disponível neste dispositivo. Informe o código manualmente.';
        icone.textContent = '⌨️';
        secaoFallback.style.display = 'block';
    }

    // fallback manual
    btnManual.addEventListener('click', () => {
        const val = inputCodigo.value.trim();
        if (!val) { inputCodigo.focus(); return; }
        enviarTag(val);
    });

    inputCodigo.addEventListener('keydown', e => {
        if (e.key === 'Enter') btnManual.click();
    });

})();
</script>

</body>
</html>
