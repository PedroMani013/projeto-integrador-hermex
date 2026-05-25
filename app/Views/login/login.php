<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HermeX — Login</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <style>
        :root {
            --hermex-brand:         #F4D35E;
            --hermex-brand-dark:    #C9A227;
            --hermex-primary:       #1E293B;
            --hermex-primary-hover: #0F172A;
            --hermex-text:          #0F172A;
            --hermex-text-muted:    #64748B;
            --hermex-bg:            #F8FAFC;
            --hermex-border:        #E2E8F0;
            --radius-sm: 6px;
            --radius-md: 10px;
            --transition: 150ms ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: "Inter", system-ui, -apple-system, sans-serif;
            background: #fff;
            color: var(--hermex-text);
        }

        /* ── Shell ── */
        .login-shell {
            display: flex;
            height: 100vh;
            min-height: 600px;
        }

        /* ── Painel esquerdo ── */
        .painel-esq {
            width: 42%;
            flex-shrink: 0;
            background: var(--hermex-primary);
            display: flex;
            flex-direction: column;
            padding: 36px 36px 28px;
            position: relative;
            overflow: hidden;
        }

        .painel-esq::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 22px 22px;
            pointer-events: none;
        }

        /* Logo */
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            position: relative;
            z-index: 1;
        }

        .logo-nome {
            font-size: 1.125rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.04em;
        }

        /* Headline */
        .esq-headline {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .esq-titulo {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 14px;
        }

        .esq-sub {
            font-size: 0.8375rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* Mini cards */
        .stat-grid {
            display: flex;
            gap: 10px;
        }

        .stat-card {
            flex: 1;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
            padding: 14px 16px;
        }

        .stat-icon {
            width: 30px;
            height: 30px;
            background: rgba(244,211,94,0.15);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            color: var(--hermex-brand);
        }

        .stat-label {
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 4px;
        }

        .stat-valor {
            font-size: 1rem;
            font-weight: 700;
            color: var(--hermex-brand);
        }

        /* Rodapé esq */
        .esq-footer {
            font-size: 0.6875rem;
            color: rgba(255,255,255,0.25);
            position: relative;
            z-index: 1;
        }

        /* ── Painel direito ── */
        .painel-dir {
            flex: 1;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            position: relative;
            overflow: hidden;
        }

        /* Padrão de pontos decorativo canto inferior direito */
        .painel-dir::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            width: 55%;
            height: 42%;
            background-image: radial-gradient(circle, rgba(30,41,59,0.05) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
        }

        .form-box {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
        }

        .form-titulo {
            font-size: 1.625rem;
            font-weight: 700;
            color: var(--hermex-text);
            margin-bottom: 6px;
        }

        .form-sub {
            font-size: 0.85rem;
            color: var(--hermex-text-muted);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* Campos */
        .campo-bloco {
            margin-bottom: 20px;
        }

        .campo-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--hermex-text);
            margin-bottom: 6px;
        }

        .campo-rel {
            position: relative;
        }

        .campo-icone {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--hermex-text-muted);
            display: flex;
            pointer-events: none;
        }

        .campo-input {
            width: 100%;
            height: 46px;
            border: 1px solid var(--hermex-border);
            border-radius: var(--radius-md);
            padding: 0 14px 0 40px;
            font-size: 0.875rem;
            color: var(--hermex-text);
            background: var(--hermex-bg);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
            font-family: inherit;
        }

        .campo-input::placeholder { color: #b0bac8; }

        .campo-input:focus {
            border-color: var(--hermex-brand-dark);
            box-shadow: 0 0 0 3px rgba(244,211,94,0.22);
            background: #fff;
        }

        .campo-input-pass { padding-right: 44px; }

        .btn-olho {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--hermex-text-muted);
            display: flex;
            padding: 4px;
            border-radius: 4px;
            transition: color var(--transition);
        }
        .btn-olho:hover { color: var(--hermex-text); }

        /* Checkbox */
        .lembrar-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 28px;
        }

        .lembrar-wrap input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--hermex-primary);
            cursor: pointer;
            flex-shrink: 0;
        }

        .lembrar-wrap label {
            font-size: 0.8125rem;
            color: var(--hermex-text-muted);
            cursor: pointer;
            user-select: none;
        }

        /* Botão submit */
        .btn-entrar {
            width: 100%;
            height: 50px;
            background: var(--hermex-brand);
            color: var(--hermex-primary);
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: inherit;
            transition: background var(--transition), transform var(--transition);
        }

        .btn-entrar:hover {
            background: var(--hermex-brand-dark);
            transform: translateY(-1px);
        }

        .btn-entrar:active { transform: translateY(0); }

        /* Responsivo */
        @media (max-width: 768px) {
            .painel-esq { display: none; }
            .painel-dir { padding: 32px 20px; }
        }
    </style>
</head>
<body>

<div class="login-shell">

    <!-- Painel esquerdo -->
    <div class="painel-esq">

        <a href="#" class="logo-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="38" height="38">
                <rect width="40" height="40" rx="8" fill="#F4D35E"/>
                <path d="M20 8 L32 14 L32 26 L20 32 L8 26 L8 14 Z" fill="none" stroke="#1E293B" stroke-width="2" stroke-linejoin="round"/>
                <path d="M8 14 L20 20 L32 14" fill="none" stroke="#1E293B" stroke-width="2"/>
                <path d="M20 20 L20 32" fill="none" stroke="#1E293B" stroke-width="2"/>
            </svg>
            <span class="logo-nome">HermeX</span>
        </a>

        <div class="esq-headline">
            <h1 class="esq-titulo">Logística inteligente com integridade total.</h1>
            <p class="esq-sub">Monitore cada elo da sua cadeia de suprimentos em tempo real com nossa plataforma de custódia segura.</p>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <div class="stat-label">Eficiência de Frota</div>
                    <div class="stat-valor">98.4%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <div class="stat-label">Cadeia de Custódia</div>
                    <div class="stat-valor">Verificada</div>
                </div>
            </div>
        </div>

        <div class="esq-footer">
            &copy; 2024 HermeX Logistics Suite. Todos os direitos reservados.
        </div>
    </div>

    <!-- Painel direito -->
    <div class="painel-dir">
        <div class="form-box">

            <h2 class="form-titulo">Bem-vindo de volta</h2>
            <p class="form-sub">Acesse sua conta para gerenciar seus despachos e inventário.</p>

            <?php
                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                $erro    = $erro ?? null;
            ?>

            <?php if ($erro): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:12px 16px;font-size:.85rem;margin-bottom:20px;">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>login" method="POST" novalidate>

                <!-- Usuário -->
                <div class="campo-bloco">
                    <label class="campo-label" for="usuario">Nome de Usuário</label>
                    <div class="campo-rel">
                        <span class="campo-icone">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input type="text" id="usuario" name="usuario"
                               class="campo-input"
                               placeholder="Digite seu usuário"
                               value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                               autocomplete="username" required>
                    </div>
                </div>

                <!-- Senha -->
                <div class="campo-bloco">
                    <label class="campo-label" for="senha">Senha</label>
                    <div class="campo-rel">
                        <span class="campo-icone">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" id="senha" name="senha"
                               class="campo-input campo-input-pass"
                               placeholder="••••••••"
                               autocomplete="current-password" required>
                        <button type="button" class="btn-olho" id="btnOlho" aria-label="Mostrar senha">
                            <svg id="iconeOlho" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Lembrar -->
                <div class="lembrar-wrap">
                    <input type="checkbox" id="lembrar" name="lembrar">
                    <label for="lembrar">Lembrar de mim</label>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-entrar">
                    Entrar no Dashboard
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>

            </form>
        </div>
    </div>

</div>

<script>
    const btnOlho    = document.getElementById('btnOlho');
    const inputSenha = document.getElementById('senha');
    const iconeOlho  = document.getElementById('iconeOlho');

    const svgAberto  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    const svgFechado = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    btnOlho.addEventListener('click', () => {
        const visivel = inputSenha.type === 'text';
        inputSenha.type = visivel ? 'password' : 'text';
        iconeOlho.innerHTML = visivel ? svgAberto : svgFechado;
        btnOlho.setAttribute('aria-label', visivel ? 'Mostrar senha' : 'Ocultar senha');
    });
</script>

</body>
</html>