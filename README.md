# hermeX — Sistema de Monitoramento de Cadeia de Custódia

hermeX é um sistema web para monitoramento de caixas em trânsito entre filiais, com detecção de anomalias e histórico auditável de cada carga. Desenvolvido como Projeto Interdisciplinar 3 e 4 do curso de Desenvolvimento de Software Multiplataforma — FATEC Itapira.

O sistema permite acompanhar em tempo real o estado das cargas, identificar violações de custódia (peso, lacre, temperatura) e gerar indicadores operacionais para gestores.

---

## Pré-requisitos

- XAMPP (PHP 8.1+ e Apache)
- Composer
- MongoDB Community Edition com MongoDB Compass

---

## Configuração do ambiente

### 1. Instalar dependências PHP

Na raiz do projeto:

```bash
composer install
```

### 2. Configurar o banco de dados

Abra o MongoDB Compass e conecte em `mongodb://localhost:27017`.

Crie um banco chamado `hermex` e importe as coleções a partir dos arquivos da pasta `scripts/data/`:

| Arquivo | Coleção |
|---|---|
| `filiais.json` | `filiais` |
| `caixas.json` | `caixas` |
| `eventos.json` | `eventos` |

Para importar: selecione a coleção → botão **Add Data** → **Import JSON**.

### 3. Configurar o Apache

Coloque a pasta do projeto em `C:\xampp\htdocs\` e certifique-se de que o `mod_rewrite` está habilitado no Apache (painel do XAMPP → Apache → Config → `httpd.conf`, descomente `LoadModule rewrite_module`).

Acesse: [http://localhost/projeto-integrador-hermex/public](http://localhost/projeto-integrador-hermex/public)

---

## Estrutura do projeto

```
├── app/
│   ├── Controllers/       # Controladores (DashboardController, ...)
│   ├── Models/            # Entidades de domínio (Caixa, Evento, Filial, ...)
│   ├── Repositories/      # Queries ao MongoDB (DashboardRepository, ...)
│   ├── Services/          # Serviços auxiliares
│   └── Views/
│       ├── dashboard/     # Tela principal
│       ├── layouts/       # Layout HTML base (base.php)
│       ├── partials/      # Componentes reutilizáveis (sidebar, header)
│       └── erros/         # Páginas de erro (404, 500)
├── config/
│   └── DatabaseConnection.php   # Singleton de conexão com MongoDB
├── docs/                        # Documentação do projeto
├── public/
│   ├── index.php                # Front Controller — ponto de entrada único
│   ├── .htaccess                # Redireciona todas as requisições ao index.php
│   └── assets/
│       ├── css/
│       │   ├── tokens.css       # Design tokens (variáveis CSS globais)
│       │   └── dashboard.css    # Estilos do dashboard
│       ├── js/
│       │   ├── dashboard.js          # Comportamentos gerais da página
│       │   └── chart-integridade.js  # Gráfico de integridade (Chart.js)
│       └── img/
│           └── logo-hermex.svg       # Logo do sistema
└── scripts/
    └── data/              # Arquivos JSON para importação no MongoDB Compass
        ├── filiais.json
        ├── caixas.json
        └── eventos.json
```

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8+ sem framework, arquitetura MVC manual |
| Banco de dados | MongoDB com driver `mongodb/mongodb` (Composer) |
| Frontend | Bootstrap 5 via CDN, JavaScript vanilla |
| Gráficos | Chart.js 4 via CDN |
| Servidor | Apache via XAMPP com `mod_rewrite` |

A arquitetura segue os padrões Repository, Singleton e Front Controller, sem uso de ORMs ou frameworks MVC externos.
