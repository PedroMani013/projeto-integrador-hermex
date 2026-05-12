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

### 1. Instalar a extensão PHP do MongoDB

O XAMPP **não inclui** a extensão `ext-mongodb` por padrão. É obrigatório instalá-la antes de rodar o projeto.

**Passos:**

1. Verifique qual versão do PHP o seu XAMPP usa: abra `http://localhost/dashboard/phpinfo.php` e anote a versão (ex: `8.2.12`) e a arquitetura (`x64`).

2. Acesse [https://pecl.php.net/package/mongodb](https://pecl.php.net/package/mongodb) e baixe a versão **2.x** compatível com o seu PHP (escolha a DLL `NTS` ou `TS` conforme indicado no phpinfo — `Thread Safety: enabled` → TS, caso contrário → NTS).

   > Exemplo para PHP 8.2 x64 NTS: baixe `php_mongodb-2.x.x-8.2-nts-vs16-x64.zip`

3. Extraia o arquivo `.zip`, copie o arquivo `php_mongodb.dll` para a pasta `C:\xampp\php\ext\`.

4. Abra `C:\xampp\php\php.ini` em um editor de texto, localize a seção de extensões e adicione a linha:
   ```
   extension=mongodb
   ```

5. Reinicie o Apache no painel do XAMPP.

6. Verifique: acesse `http://localhost/dashboard/phpinfo.php` e pesquise por `mongodb` — deve aparecer a seção da extensão.

### 2. Instalar dependências PHP

Na raiz do projeto:

```bash
composer install
```

### 3. Configurar variáveis de ambiente

Copie o arquivo `.env.example` para `.env` na raiz do projeto:

```bash
copy .env.example .env
```

Edite o `.env` com as credenciais do seu MongoDB local. Para instalações padrão sem autenticação, deixe `MONGO_USER` e `MONGO_PASS` em branco:

```
MONGO_HOST=localhost
MONGO_PORT=27017
MONGO_DB=hermex
MONGO_USER=
MONGO_PASS=
```

### 4. Configurar o banco de dados

Abra o MongoDB Compass e conecte em `mongodb://localhost:27017`.

Crie um banco chamado `hermex` e importe as coleções a partir dos arquivos da pasta `scripts/data/`:

| Arquivo | Coleção |
|---|---|
| `filiais.json` | `filiais` |
| `caixas.json` | `caixas` |
| `eventos.json` | `eventos` |

Para importar: selecione a coleção → botão **Add Data** → **Import JSON**.

### 5. Configurar o Apache

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
