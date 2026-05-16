<?php

declare(strict_types=1);

/**
 * Popula: filiais (via ViaCEP), caixas e eventos coerentes.
 * Uso: php scripts/seed.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

// config

$mongoHost = getenv('MONGO_HOST') ?: 'localhost';
$mongoPort = getenv('MONGO_PORT') ?: '27017';
$mongoDb   = getenv('MONGO_DB')   ?: 'hermex';
$mongoUser = getenv('MONGO_USER') ?: '';
$mongoPass = getenv('MONGO_PASS') ?: '';

if ($mongoUser !== '' && $mongoPass !== '') {
    $uri = "mongodb://{$mongoUser}:{$mongoPass}@{$mongoHost}:{$mongoPort}/{$mongoDb}?authSource=admin";
} else {
    $uri = "mongodb://{$mongoHost}:{$mongoPort}";
}

$client = new Client($uri);
$db     = $client->selectDatabase($mongoDb);

// helpers

function now(int $offsetSeconds = 0): UTCDateTime
{
    return new UTCDateTime((time() + $offsetSeconds) * 1000);
}

function mongoDate(string $datetime): UTCDateTime
{
    return new UTCDateTime((new \DateTime($datetime, new \DateTimeZone('America/Sao_Paulo')))->getTimestamp() * 1000);
}

function buscarViaCep(string $cep): array
{
    $cepLimpo = preg_replace('/\D/', '', $cep);
    $url      = "https://viacep.com.br/ws/{$cepLimpo}/json/";
    $ctx      = stream_context_create(['http' => ['timeout' => 10, 'method' => 'GET']]);
    $resp     = @file_get_contents($url, false, $ctx);

    if ($resp === false) {
        echo "  [AVISO] Falha ao consultar ViaCEP para {$cep}. Usando dados estáticos.\n";
        return [];
    }

    $dados = json_decode($resp, true);
    if (!empty($dados['erro'])) {
        echo "  [AVISO] CEP {$cep} não encontrado.\n";
        return [];
    }

    return $dados;
}

// limpeza

$db->caixas->drop();
$db->eventos->drop();
$db->filiais->drop();
$db->produtos->drop();
$db->operadores->drop();

// filiais


$filiaisConfig = [
    ['codigo' => 'F01', 'nome' => 'Itapira',     'cep' => '13970-000', 'numero' => '100', 'lat' => -22.4367, 'lng' => -46.7843],
    ['codigo' => 'F02', 'nome' => 'Sorocaba',     'cep' => '18035-300', 'numero' => '250', 'lat' => -23.5015, 'lng' => -47.4526],
    ['codigo' => 'F03', 'nome' => 'Santo André',  'cep' => '09010-160', 'numero' => '80',  'lat' => -23.6666, 'lng' => -46.5381],
    ['codigo' => 'F04', 'nome' => 'Jundiaí',      'cep' => '13201-010', 'numero' => '320', 'lat' => -23.1858, 'lng' => -46.8977],
    ['codigo' => 'F05', 'nome' => 'Campinas',     'cep' => '13013-001', 'numero' => '45',  'lat' => -22.9056, 'lng' => -47.0608],
    ['codigo' => 'F06', 'nome' => 'SP Lapa',      'cep' => '05065-000', 'numero' => '600', 'lat' => -23.5225, 'lng' => -46.7050],
    ['codigo' => 'F07', 'nome' => 'Ribeirão Preto','cep' => '14010-040', 'numero' => '12', 'lat' => -21.1767, 'lng' => -47.8208],
    ['codigo' => 'F08', 'nome' => 'Piracicaba',   'cep' => '13400-020', 'numero' => '77',  'lat' => -22.7253, 'lng' => -47.6492],
];

$filiaisInseridas = [];

foreach ($filiaisConfig as $fc) {
    echo "  Consultando CEP {$fc['cep']} ({$fc['nome']})...\n";
    $viacep = buscarViaCep($fc['cep']);
    sleep(1); // respeitar rate limit do ViaCEP

    $doc = [
        '_id'        => new ObjectId(),
        'codigo'     => $fc['codigo'],
        'nome'       => $fc['nome'],
        'cep'        => $viacep['cep']        ?? $fc['cep'],
        'logradouro' => $viacep['logradouro'] ?? 'Rua Principal',
        'numero'     => $fc['numero'],
        'bairro'     => $viacep['bairro']     ?? 'Centro',
        'cidade'     => $viacep['localidade'] ?? $fc['nome'],
        'uf'         => $viacep['uf']         ?? 'SP',
        'localizacao' => [
            'type'        => 'Point',
            'coordinates' => [$fc['lng'], $fc['lat']],
        ],
        'criado_em'  => now(),
    ];

    $db->filiais->insertOne($doc);
    $filiaisInseridas[$fc['codigo']] = $doc;
    echo "  [{$fc['codigo']}] {$fc['nome']} inserida.\n";
}

// produtos

$produtos = [
    ['nome' => 'Conector RJ45',     'peso_unitario' => 15,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Antena Wi-Fi 5dBi', 'peso_unitario' => 45,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Uniforme operador', 'peso_unitario' => 400, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'EPI Kit padrão',    'peso_unitario' => 650, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'Chave combinada',   'peso_unitario' => 210, 'categoria' => 'ferramentaria', 'tolerancia' => 4],
    ['nome' => 'Luva de raspa',     'peso_unitario' => 120, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'Cabo USB-C 2m',     'peso_unitario' => 55,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Pen drive 64GB',    'peso_unitario' => 12,  'categoria' => 'eletronica',    'tolerancia' => 5],
];

foreach ($produtos as &$p) {
    $doc = array_merge(['_id' => new ObjectId(), 'criado_em' => now()], $p);
    $db->produtos->insertOne($doc);
    $p['_id'] = $doc['_id'];
}
unset($p);

echo "  " . count($produtos) . " produtos inseridos.\n";

// operadores


$operadores = [
    ['nome' => 'Ana Costa',   'email' => 'ana.costa@hermex.local',   'perfil' => 'coordenador', 'filial' => 'F01'],
    ['nome' => 'Bruno Silva', 'email' => 'bruno.silva@hermex.local',  'perfil' => 'operador_filial', 'filial' => 'F02'],
    ['nome' => 'Carla Matos', 'email' => 'carla.matos@hermex.local',  'perfil' => 'administrador', 'filial' => 'F01'],
];

foreach ($operadores as $op) {
    $db->operadores->insertOne(array_merge(['_id' => new ObjectId(), 'criado_em' => now()], $op));
}

echo "  " . count($operadores) . " operadores inseridos.\n";

// transportadoras

$transportadoras = ['Rápido SP', 'Translog Brasil', 'CargoNorte', 'ViaExpress', 'LogFácil'];

// caixas e eventos

$codigosCodigos = [];
$contador       = 2000;

/**
 * Gera um documento de caixa + eventos coerentes e insere no banco.
 */
function criarCaixa(
    \MongoDB\Database $db,
    array $filiaisInseridas,
    array $transportadoras,
    string $estado,
    int &$contador,
    bool $comAnomalia = false,
    string $tipoAnomalia = 'peso'
): void {
    $codigosFiliais = array_keys($filiaisInseridas);
    shuffle($codigosFiliais);
    $origemCod  = $codigosFiliais[0];
    $destinoCod = $codigosFiliais[1];

    $origem  = $filiaisInseridas[$origemCod];
    $destino = $filiaisInseridas[$destinoCod];

    $contador++;
    $codigo     = 'CG-' . $contador;
    $notaFiscal = 'NF-' . rand(10000, 99999);
    $totalItens = rand(5, 50);
    $pesoBase   = $totalItens * rand(100, 600); // gramas
    $caixaId    = new ObjectId();

    $criadoEm        = now(-rand(3600 * 24 * 5, 3600 * 24 * 30));
    $previsaoChegada = now(rand(3600 * 2, 3600 * 48));

    // estado atual define o peso atual e a previsão
    $pesoAtual = $pesoBase;

    if ($comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos'])) {
        $pesoAtual = (int) ($pesoBase * (1 - rand(10, 25) / 100)); // redução de 10-25%
    }

    $transportadora = $transportadoras[array_rand($transportadoras)];

    // ultimo evento snapshot
    $ultimoEventoTipo = match($estado) {
        'em_transito' => 'peso',
        'violada'     => 'tampa',
        'entregue'    => 'nfc',
        default       => 'transicao',
    };

    $ultimoEvento = [
        'tipo'             => $ultimoEventoTipo,
        'valor'            => $ultimoEventoTipo === 'tampa' ? 'aberta' : $pesoAtual,
        'em_movimento'     => false,
        'peso_anomalo'     => $comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos']),
        'abertura_indevida'=> $comAnomalia && in_array($tipoAnomalia, ['tampa', 'ambos']),
        'timestamp'        => now(-rand(60, 3600)),
    ];

    $caixaDoc = [
        '_id'                  => $caixaId,
        'codigo'               => $codigo,
        'tag_nfc'              => 'NFC-' . strtoupper(bin2hex(random_bytes(4))),
        'estado'               => $estado,
        'nota_fiscal'          => $notaFiscal,
        'total_itens'          => $totalItens,
        'peso_baseline'        => $pesoBase,
        'peso_atual'           => $pesoAtual,
        'filial_origem_codigo' => $origemCod,
        'filial_destino_codigo'=> $destinoCod,
        'filial_origem_nome'   => (string) $origem['nome'],
        'filial_destino_nome'  => (string) $destino['nome'],
        'transportadora'       => $transportadora,
        'previsao_chegada'     => $previsaoChegada,
        'ultimo_evento'        => $ultimoEvento,
        'criado_em'            => $criadoEm,
    ];

    $db->caixas->insertOne($caixaDoc);

    // eventos históricos

    $eventos = [];
    $baseTime = (int) ($criadoEm->toDateTime()->getTimestamp());

    // evento de lacre
    $eventos[] = [
        '_id'              => new ObjectId(),
        'caixa_id'         => $caixaId,
        'tipo'             => 'transicao',
        'valor'            => 'lacrada',
        'em_movimento'     => false,
        'peso_anomalo'     => false,
        'abertura_indevida'=> false,
        'timestamp'        => new UTCDateTime(($baseTime + 300) * 1000),
    ];

    // evento de início de trânsito
    $eventos[] = [
        '_id'              => new ObjectId(),
        'caixa_id'         => $caixaId,
        'tipo'             => 'transicao',
        'valor'            => 'em_transito',
        'em_movimento'     => true,
        'peso_anomalo'     => false,
        'abertura_indevida'=> false,
        'timestamp'        => new UTCDateTime(($baseTime + 1800) * 1000),
    ];

    // leituras de peso durante o trajeto (nominais)
    for ($i = 0; $i < rand(4, 8); $i++) {
        $ruido  = rand(-50, 50); // ruído em gramas
        $eventos[] = [
            '_id'              => new ObjectId(),
            'caixa_id'         => $caixaId,
            'tipo'             => 'peso',
            'valor'            => $pesoBase + $ruido,
            'em_movimento'     => (bool) rand(0, 1),
            'peso_anomalo'     => false,
            'abertura_indevida'=> false,
            'timestamp'        => new UTCDateTime(($baseTime + 3600 * ($i + 1)) * 1000),
        ];
    }

    // eventos de anomalia
    if ($comAnomalia) {
        $tempoAnomalia = $baseTime + 3600 * 5;

        if (in_array($tipoAnomalia, ['tampa', 'ambos'])) {
            $eventos[] = [
                '_id'              => new ObjectId(),
                'caixa_id'         => $caixaId,
                'tipo'             => 'tampa',
                'valor'            => 'aberta',
                'em_movimento'     => false,
                'peso_anomalo'     => false,
                'abertura_indevida'=> true,
                'timestamp'        => new UTCDateTime($tempoAnomalia * 1000),
            ];
        }

        if (in_array($tipoAnomalia, ['peso', 'ambos'])) {
            $eventos[] = [
                '_id'              => new ObjectId(),
                'caixa_id'         => $caixaId,
                'tipo'             => 'peso',
                'valor'            => $pesoAtual,
                'em_movimento'     => false,
                'peso_anomalo'     => true,
                'abertura_indevida'=> false,
                'timestamp'        => new UTCDateTime(($tempoAnomalia + 30) * 1000),
            ];
        }

        // transição para violada
        $eventos[] = [
            '_id'              => new ObjectId(),
            'caixa_id'         => $caixaId,
            'tipo'             => 'transicao',
            'valor'            => 'violada',
            'em_movimento'     => false,
            'peso_anomalo'     => false,
            'abertura_indevida'=> false,
            'timestamp'        => new UTCDateTime(($tempoAnomalia + 60) * 1000),
        ];
    }

    // evento de entrega (se aplicável)
    if ($estado === 'entregue') {
        $eventos[] = [
            '_id'              => new ObjectId(),
            'caixa_id'         => $caixaId,
            'tipo'             => 'nfc',
            'valor'            => 'confirmado',
            'em_movimento'     => false,
            'peso_anomalo'     => false,
            'abertura_indevida'=> false,
            'timestamp'        => new UTCDateTime(($baseTime + 3600 * 10) * 1000),
        ];
    }

    if (!empty($eventos)) {
        $db->eventos->insertMany($eventos);
    }

    echo "  [{$codigo}] estado={$estado}" . ($comAnomalia ? " [ANOMALIA:{$tipoAnomalia}]" : '') . "\n";
}

// Gerar caixas distribuídas por estado conforme critérios de aceitação:
// - maioria em_transito
// - algumas entregues no mês
// - 2-3 violadas (com anomalia)
// - algumas lacradas

// Caixas em trânsito nominais (30)
for ($i = 0; $i < 30; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, 'em_transito', $contador);
}

// Caixas violadas (3) — com anomalia combinada
for ($i = 0; $i < 3; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, 'violada', $contador, true, 'ambos');
}

// Caixas em trânsito com anomalia leve de peso (não chegou a violar)
for ($i = 0; $i < 4; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, 'em_transito', $contador, true, 'peso');
}

// Caixas entregues no mês (12)
for ($i = 0; $i < 12; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, 'entregue', $contador);
}

// Caixas lacradas (5)
for ($i = 0; $i < 5; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, 'lacrada', $contador);
}

// índices

$db->caixas->createIndex(['estado' => 1]);
$db->caixas->createIndex(['estado' => 1, 'ultimo_evento.timestamp' => -1]);
$db->caixas->createIndex(['criado_em' => 1]);
$db->eventos->createIndex(['caixa_id' => 1, 'timestamp' => -1]);
$db->eventos->createIndex(['peso_anomalo' => 1, 'timestamp' => -1]);
$db->eventos->createIndex(['abertura_indevida' => 1, 'timestamp' => -1]);
$db->filiais->createIndex(['codigo' => 1], ['unique' => true]);
$db->filiais->createIndex(['localizacao' => '2dsphere']);

echo "  Índices criados.\n";

// resumo do seed

$totalCaixas   = $db->caixas->countDocuments();
$totalEventos  = $db->eventos->countDocuments();
$totalFiliais  = $db->filiais->countDocuments();
$totalProdutos = $db->produtos->countDocuments();

echo "\n=== Seed concluído ===\n";
echo "  Filiais:  {$totalFiliais}\n";
echo "  Produtos: {$totalProdutos}\n";
echo "  Caixas:   {$totalCaixas}\n";
echo "  Eventos:  {$totalEventos}\n";
echo "  Banco:    {$mongoDb} em {$mongoHost}:{$mongoPort}\n\n";
