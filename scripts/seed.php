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
$db->categorias->drop();

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

// categorias

$categorias = [
    ['codigo' => 'eletronica',      'nome' => 'Eletrônica',                      'tolerancia_padrao' => 5],
    ['codigo' => 'textil',          'nome' => 'Têxtil corporativo',              'tolerancia_padrao' => 3],
    ['codigo' => 'ferramentaria',   'nome' => 'Ferramentaria leve',              'tolerancia_padrao' => 4],
    ['codigo' => 'insumos_medicos', 'nome' => 'Insumos médicos não-perecíveis',  'tolerancia_padrao' => 2],
    ['codigo' => 'escritorio',      'nome' => 'Material de escritório',          'tolerancia_padrao' => 4],
];

foreach ($categorias as $cat) {
    $db->categorias->insertOne(array_merge(['_id' => new ObjectId(), 'criado_em' => now(), 'atualizado_em' => now()], $cat));
}

$db->categorias->createIndex(['codigo' => 1], ['unique' => true]);
echo "  " . count($categorias) . " categorias inseridas.\n";

// catálogo de produtos para uso no seed (não persistido — vive embedded nas NFs)
$catalogoProdutos = [
    ['nome' => 'Conector RJ45',     'sku' => 'HM-ELE-0042', 'peso_unitario' => 15,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Antena Wi-Fi 5dBi', 'sku' => 'HM-ELE-0051', 'peso_unitario' => 45,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Uniforme operador', 'sku' => 'HM-TEX-0101', 'peso_unitario' => 400, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'EPI Kit padrão',    'sku' => 'HM-TEX-0102', 'peso_unitario' => 650, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'Chave combinada',   'sku' => 'HM-FER-0201', 'peso_unitario' => 210, 'categoria' => 'ferramentaria', 'tolerancia' => 4],
    ['nome' => 'Luva de raspa',     'sku' => 'HM-TEX-0103', 'peso_unitario' => 120, 'categoria' => 'textil',        'tolerancia' => 3],
    ['nome' => 'Cabo USB-C 2m',     'sku' => 'HM-ELE-0058', 'peso_unitario' => 55,  'categoria' => 'eletronica',    'tolerancia' => 5],
    ['nome' => 'Pen drive 64GB',    'sku' => 'HM-ELE-0063', 'peso_unitario' => 12,  'categoria' => 'eletronica',    'tolerancia' => 5],
];

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
    array $catalogoProdutos,
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
    $codigo  = 'CG-' . $contador;
    $caixaId = new ObjectId();

    $criadoEm        = now(-rand(3600 * 24 * 5, 3600 * 24 * 30));
    $previsaoChegada = now(rand(3600 * 2, 3600 * 48));
    $lacradaEm       = $estado !== 'criada' ? now(-rand(3600 * 24 * 4, 3600 * 24 * 29)) : null;

    // gerar uma NF com 1-3 produtos do catálogo
    $produtosSelecionados = $catalogoProdutos;
    shuffle($produtosSelecionados);
    $qtdProdutosTipos = rand(1, 3);
    $produtosNf       = [];
    $pesoBase         = 0;
    $toleranciaMin    = 100.0;

    for ($p = 0; $p < $qtdProdutosTipos; $p++) {
        $prod       = $produtosSelecionados[$p];
        $quantidade = rand(5, 30);
        $produtosNf[] = [
            'nome'          => $prod['nome'],
            'sku'           => $prod['sku'],
            'categoria'     => $prod['categoria'],
            'quantidade'    => $quantidade,
            'peso_unitario' => $prod['peso_unitario'],
            'tolerancia'    => $prod['tolerancia'],
        ];
        $pesoBase      += $quantidade * $prod['peso_unitario'];
        $toleranciaMin  = min($toleranciaMin, (float) $prod['tolerancia']);
    }

    $totalItens = array_sum(array_column($produtosNf, 'quantidade'));

    $nf = [
        'numero_nf'           => 'NF-' . rand(10000, 99999),
        'cliente_destinatario' => [
            'nome'      => 'Distribuidora Exemplo Ltda',
            'documento' => '12.345.678/0001-99',
            'endereco'  => [
                'cep'        => '14010-040',
                'logradouro' => 'Rua das Flores',
                'numero'     => '100',
                'bairro'     => 'Centro',
                'cidade'     => (string) $destino['cidade'],
                'uf'         => (string) $destino['uf'],
            ],
        ],
        'produtos' => $produtosNf,
    ];

    $pesoAtual = $pesoBase;

    if ($comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos'])) {
        $pesoAtual = (int) ($pesoBase * (1 - rand(10, 25) / 100));
    }

    $transportadora = $transportadoras[array_rand($transportadoras)];

    $ultimoEventoTipo = match($estado) {
        'em_transito' => 'peso',
        'violada'     => 'tampa',
        'entregue'    => 'nfc',
        default       => 'transicao',
    };

    $ultimoEvento = [
        'tipo'              => $ultimoEventoTipo,
        'valor'             => $ultimoEventoTipo === 'tampa' ? 'aberta' : $pesoAtual,
        'em_movimento'      => false,
        'peso_anomalo'      => $comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos']),
        'abertura_indevida' => $comAnomalia && in_array($tipoAnomalia, ['tampa', 'ambos']),
        'timestamp'         => now(-rand(60, 3600)),
    ];

    $caixaDoc = [
        '_id'                       => $caixaId,
        'codigo'                    => $codigo,
        'tag_nfc'                   => 'NFC-' . strtoupper(bin2hex(random_bytes(4))),
        'estado'                    => $estado,
        'notas_fiscais'             => [$nf],
        'total_itens'               => $totalItens,
        'peso_baseline'             => $pesoBase,
        'peso_atual'                => $pesoAtual,
        'tolerancia_efetiva'        => $toleranciaMin,
        'anomalia_peso_iniciada_em' => null,
        'lacrada_em'                => $lacradaEm,
        'filial_origem_codigo'      => $origemCod,
        'filial_destino_codigo'     => $destinoCod,
        'filial_origem_nome'        => (string) $origem['nome'],
        'filial_destino_nome'       => (string) $destino['nome'],
        'transportadora'            => $transportadora,
        'previsao_chegada'          => $previsaoChegada,
        'ultimo_evento'             => $ultimoEvento,
        'criado_em'                 => $criadoEm,
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

// Caixas demo — identificáveis pelo código DEMO-xx, usadas na apresentação

function montarNfDemo(
    array $filiaisInseridas,
    string $destinoCod,
    string $numeroNf,
    string $clienteNome,
    string $clienteDoc,
    array $produtos
): array {
    return [
        'numero_nf'            => $numeroNf,
        'cliente_destinatario' => [
            'nome'      => $clienteNome,
            'documento' => $clienteDoc,
            'endereco'  => [
                'cep'        => '13970-000',
                'logradouro' => 'Av. Demonstração',
                'numero'     => '1',
                'bairro'     => 'Centro',
                'cidade'     => (string) $filiaisInseridas[$destinoCod]['cidade'],
                'uf'         => (string) $filiaisInseridas[$destinoCod]['uf'],
            ],
        ],
        'produtos' => $produtos,
    ];
}

function criarCaixaDemo(
    \MongoDB\Database $db,
    array $filiaisInseridas,
    array $catalogoProdutos,
    string $codigo,
    string $origemCod,
    string $destinoCod,
    string $transportadora,
    string $estado,
    array $notasFiscais,
    bool $comAnomalia = false,
    string $tipoAnomalia = 'tampa',
    bool $alertaReconhecido = false
): string {
    $caixaId = new ObjectId();
    $tagNfc  = 'NFC-DEMO-' . strtoupper(substr($codigo, -2));

    $criadoEm        = now(-3600 * 6);
    $lacradaEm       = now(-3600 * 5);
    $previsaoChegada = now(3600 * 12);

    // calcular peso baseline e tolerancia_efetiva a partir dos produtos das NFs
    $pesoBaseline  = 0;
    $tolerancias   = [];
    $totalItens    = 0;
    foreach ($notasFiscais as $nf) {
        foreach ($nf['produtos'] as $p) {
            $pesoBaseline += $p['quantidade'] * $p['peso_unitario'];
            $tolerancias[] = (float) $p['tolerancia'];
            $totalItens   += $p['quantidade'];
        }
    }
    $tolerancia = empty($tolerancias) ? 4.0 : min($tolerancias);

    $pesoAtual = $comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos'])
        ? (int) ($pesoBaseline * (1 - ($tolerancia + 5) / 100))
        : (int) $pesoBaseline;

    $ultimoEventoTipo = match($estado) {
        'em_transito' => 'peso',
        'violada'     => ($tipoAnomalia === 'peso' ? 'peso' : 'tampa'),
        'entregue'    => 'nfc',
        'lacrada'     => 'transicao',
        default       => 'transicao',
    };

    $caixaDoc = [
        '_id'                       => $caixaId,
        'codigo'                    => $codigo,
        'tag_nfc'                   => $tagNfc,
        'estado'                    => $estado,
        'notas_fiscais'             => $notasFiscais,
        'total_itens'               => $totalItens,
        'peso_baseline'             => (int) $pesoBaseline,
        'peso_atual'                => $pesoAtual,
        'tolerancia_efetiva'        => $tolerancia,
        'anomalia_peso_iniciada_em' => null,
        'lacrada_em'                => $estado !== 'criada' ? $lacradaEm : null,
        'filial_origem_codigo'      => $origemCod,
        'filial_destino_codigo'     => $destinoCod,
        'filial_origem_nome'        => (string) $filiaisInseridas[$origemCod]['nome'],
        'filial_destino_nome'       => (string) $filiaisInseridas[$destinoCod]['nome'],
        'transportadora'            => $transportadora,
        'previsao_chegada'          => $previsaoChegada,
        'ultimo_evento'             => [
            'tipo'              => $ultimoEventoTipo,
            'valor'             => $ultimoEventoTipo === 'tampa' ? 'aberta' : $pesoAtual,
            'em_movimento'      => false,
            'peso_anomalo'      => $comAnomalia && in_array($tipoAnomalia, ['peso', 'ambos']),
            'abertura_indevida' => $comAnomalia && in_array($tipoAnomalia, ['tampa', 'ambos']),
            'timestamp'         => now(-rand(600, 3600)),
        ],
        'criado_em' => $criadoEm,
    ];

    if ($alertaReconhecido) {
        $caixaDoc['alerta_reconhecido']   = true;
        $caixaDoc['ultimo_reconhecimento'] = [
            'classificacao' => 'investigacao_concluida_sem_violacao',
            'observacao'    => 'Conferência realizada e carga íntegra confirmada pelo operador de demo.',
            'reconhecido_em' => now(-1800),
            'operador'       => 'Ana Costa',
        ];
    }

    $db->caixas->insertOne($caixaDoc);

    $baseTime = (int) ($criadoEm->toDateTime()->getTimestamp());
    $eventos  = [];

    $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'transicao',
        'valor' => 'lacrada', 'em_movimento' => false, 'peso_anomalo' => false,
        'abertura_indevida' => false, 'timestamp' => new UTCDateTime(($baseTime + 300) * 1000)];

    if ($estado !== 'lacrada') {
        $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'transicao',
            'valor' => 'em_transito', 'em_movimento' => true, 'peso_anomalo' => false,
            'abertura_indevida' => false, 'timestamp' => new UTCDateTime(($baseTime + 1800) * 1000)];

        for ($i = 0; $i < 4; $i++) {
            $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'peso',
                'valor' => (int) $pesoBaseline + rand(-30, 30), 'em_movimento' => (bool) rand(0, 1),
                'peso_anomalo' => false, 'abertura_indevida' => false,
                'timestamp' => new UTCDateTime(($baseTime + 3600 * ($i + 1)) * 1000)];
        }
    }

    if ($comAnomalia) {
        $ta = $baseTime + 3600 * 5;
        if (in_array($tipoAnomalia, ['tampa', 'ambos'])) {
            $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'tampa',
                'valor' => 'aberta', 'em_movimento' => false, 'peso_anomalo' => false,
                'abertura_indevida' => true, 'timestamp' => new UTCDateTime($ta * 1000)];
        }
        if (in_array($tipoAnomalia, ['peso', 'ambos'])) {
            $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'peso',
                'valor' => $pesoAtual, 'em_movimento' => false, 'peso_anomalo' => true,
                'abertura_indevida' => false, 'timestamp' => new UTCDateTime(($ta + 30) * 1000)];
        }
        $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'transicao',
            'valor' => 'violada', 'em_movimento' => false, 'peso_anomalo' => false,
            'abertura_indevida' => false, 'timestamp' => new UTCDateTime(($ta + 60) * 1000)];
    }

    if ($estado === 'entregue') {
        $eventos[] = ['_id' => new ObjectId(), 'caixa_id' => $caixaId, 'tipo' => 'nfc',
            'valor' => 'confirmado', 'em_movimento' => false, 'peso_anomalo' => false,
            'abertura_indevida' => false, 'timestamp' => new UTCDateTime(($baseTime + 3600 * 10) * 1000)];
    }

    if (!empty($eventos)) {
        $db->eventos->insertMany($eventos);
    }

    echo "  [DEMO] [{$codigo}] estado={$estado} tag={$tagNfc}\n";
    return (string) $caixaId;
}

echo "\n-- caixas demo --\n";

// DEMO-01: lacrada — ponto de partida do fluxo (despachar ao vivo)
// Conteúdo: kit de rede + uniformes de operador
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-01', 'F01', 'F05', 'Rápido SP', 'lacrada', [
    montarNfDemo($filiaisInseridas, 'F05', 'NF-2025-00101', 'TechRede Distribuidora Ltda', '12.345.678/0001-99', [
        ['nome' => 'Conector RJ45',     'sku' => 'HM-ELE-0042', 'categoria' => 'eletronica',    'quantidade' => 50,  'peso_unitario' => 15,  'tolerancia' => 5.0],
        ['nome' => 'Cabo USB-C 2m',     'sku' => 'HM-ELE-0058', 'categoria' => 'eletronica',    'quantidade' => 20,  'peso_unitario' => 55,  'tolerancia' => 5.0],
        ['nome' => 'Antena Wi-Fi 5dBi', 'sku' => 'HM-ELE-0051', 'categoria' => 'eletronica',    'quantidade' => 10,  'peso_unitario' => 45,  'tolerancia' => 5.0],
    ]),
    montarNfDemo($filiaisInseridas, 'F05', 'NF-2025-00102', 'Unifarme EPI Ltda', '98.765.432/0001-11', [
        ['nome' => 'Uniforme operador', 'sku' => 'HM-TEX-0101', 'categoria' => 'textil',        'quantidade' => 5,   'peso_unitario' => 400, 'tolerancia' => 3.0],
        ['nome' => 'Luva de raspa',     'sku' => 'HM-TEX-0103', 'categoria' => 'textil',        'quantidade' => 10,  'peso_unitario' => 120, 'tolerancia' => 3.0],
    ]),
]);

// DEMO-02: em_transito nominal — monitoramento saudável
// Conteúdo: insumos médicos não-perecíveis
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-02', 'F02', 'F04', 'Translog Brasil', 'em_transito', [
    montarNfDemo($filiaisInseridas, 'F04', 'NF-2025-00210', 'MedSupply SP Ltda', '55.111.222/0001-33', [
        ['nome' => 'Luva cirúrgica M (cx100)',  'sku' => 'HM-MED-0301', 'categoria' => 'insumos_medicos', 'quantidade' => 8,   'peso_unitario' => 180, 'tolerancia' => 2.0],
        ['nome' => 'Máscara cirúrgica (cx50)',  'sku' => 'HM-MED-0302', 'categoria' => 'insumos_medicos', 'quantidade' => 12,  'peso_unitario' => 95,  'tolerancia' => 2.0],
        ['nome' => 'Seringa 10ml (cx100)',      'sku' => 'HM-MED-0310', 'categoria' => 'insumos_medicos', 'quantidade' => 4,   'peso_unitario' => 320, 'tolerancia' => 2.0],
    ]),
]);

// DEMO-03: violada por abertura indevida — demo de detecção por tampa
// Conteúdo: ferramentas
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-03', 'F03', 'F06', 'CargoNorte', 'violada', [
    montarNfDemo($filiaisInseridas, 'F06', 'NF-2025-00387', 'Ferramentas Brasil Ltda', '44.222.333/0001-55', [
        ['nome' => 'Chave combinada 13mm',  'sku' => 'HM-FER-0201', 'categoria' => 'ferramentaria', 'quantidade' => 20,  'peso_unitario' => 210, 'tolerancia' => 4.0],
        ['nome' => 'Alicate universal 8"',  'sku' => 'HM-FER-0205', 'categoria' => 'ferramentaria', 'quantidade' => 15,  'peso_unitario' => 290, 'tolerancia' => 4.0],
        ['nome' => 'EPI Kit padrão',        'sku' => 'HM-TEX-0102', 'categoria' => 'textil',        'quantidade' => 6,   'peso_unitario' => 650, 'tolerancia' => 3.0],
    ]),
], true, 'tampa');

// DEMO-04: violada por peso anômalo — demo de detecção por sensor de peso
// Conteúdo: eletrônicos de alto valor (tolerância 2% — sensível)
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-04', 'F05', 'F07', 'ViaExpress', 'violada', [
    montarNfDemo($filiaisInseridas, 'F07', 'NF-2025-00441', 'InfoTech Atacado Ltda', '77.888.999/0001-00', [
        ['nome' => 'Pen drive 64GB',        'sku' => 'HM-ELE-0063', 'categoria' => 'eletronica',    'quantidade' => 100, 'peso_unitario' => 12,  'tolerancia' => 5.0],
        ['nome' => 'Módulo ESP32 DevKit',   'sku' => 'HM-ELE-0071', 'categoria' => 'eletronica',    'quantidade' => 30,  'peso_unitario' => 28,  'tolerancia' => 5.0],
        ['nome' => 'Sensor HX711 (balança)','sku' => 'HM-ELE-0075', 'categoria' => 'insumos_medicos','quantidade' => 20,  'peso_unitario' => 18,  'tolerancia' => 2.0],
    ]),
], true, 'peso');

// DEMO-05: alerta reconhecido — ciclo completo de triagem
// Conteúdo: material de escritório
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-05', 'F04', 'F08', 'LogFácil', 'violada', [
    montarNfDemo($filiaisInseridas, 'F08', 'NF-2025-00512', 'Papelaria Central Ltda', '33.444.555/0001-77', [
        ['nome' => 'Resma papel A4 (500fls)', 'sku' => 'HM-ESC-0401', 'categoria' => 'escritorio', 'quantidade' => 20,  'peso_unitario' => 2400, 'tolerancia' => 4.0],
        ['nome' => 'Caneta esferográfica (cx50)','sku' => 'HM-ESC-0402','categoria' => 'escritorio','quantidade' => 4,   'peso_unitario' => 250,  'tolerancia' => 4.0],
        ['nome' => 'Grampeador metálico',     'sku' => 'HM-ESC-0410', 'categoria' => 'escritorio', 'quantidade' => 8,   'peso_unitario' => 380,  'tolerancia' => 4.0],
    ]),
], true, 'ambos', true);

// DEMO-06: entregue — relatório de custódia com cadeia completa
// Conteúdo: mix eletrônico + têxtil (duas NFs, dois fornecedores)
criarCaixaDemo($db, $filiaisInseridas, $catalogoProdutos, 'DEMO-06', 'F01', 'F03', 'Rápido SP', 'entregue', [
    montarNfDemo($filiaisInseridas, 'F03', 'NF-2025-00601', 'Eletro Sudeste Ltda', '11.222.333/0001-44', [
        ['nome' => 'Conector RJ45',     'sku' => 'HM-ELE-0042', 'categoria' => 'eletronica',    'quantidade' => 80,  'peso_unitario' => 15,  'tolerancia' => 5.0],
        ['nome' => 'Cabo USB-C 2m',     'sku' => 'HM-ELE-0058', 'categoria' => 'eletronica',    'quantidade' => 30,  'peso_unitario' => 55,  'tolerancia' => 5.0],
    ]),
    montarNfDemo($filiaisInseridas, 'F03', 'NF-2025-00602', 'Vestuário Corporativo SP', '22.333.444/0001-66', [
        ['nome' => 'Uniforme operador', 'sku' => 'HM-TEX-0101', 'categoria' => 'textil',        'quantidade' => 8,   'peso_unitario' => 400, 'tolerancia' => 3.0],
        ['nome' => 'EPI Kit padrão',    'sku' => 'HM-TEX-0102', 'categoria' => 'textil',        'quantidade' => 6,   'peso_unitario' => 650, 'tolerancia' => 3.0],
    ]),
]);

echo "\n-- caixas geradas aleatoriamente --\n";

// Gerar caixas distribuídas por estado conforme critérios de aceitação:
// - maioria em_transito
// - algumas entregues no mês
// - 2-3 violadas (com anomalia)
// - algumas lacradas

// Caixas em trânsito nominais (30)
for ($i = 0; $i < 30; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, $catalogoProdutos, 'em_transito', $contador);
}

// Caixas violadas (3) — com anomalia combinada
for ($i = 0; $i < 3; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, $catalogoProdutos, 'violada', $contador, true, 'ambos');
}

// Caixas em trânsito com anomalia leve de peso (não chegou a violar)
for ($i = 0; $i < 4; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, $catalogoProdutos, 'em_transito', $contador, true, 'peso');
}

// Caixas entregues no mês (12)
for ($i = 0; $i < 12; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, $catalogoProdutos, 'entregue', $contador);
}

// Caixas lacradas (5)
for ($i = 0; $i < 5; $i++) {
    criarCaixa($db, $filiaisInseridas, $transportadoras, $catalogoProdutos, 'lacrada', $contador);
}

// índices

$db->caixas->createIndex(['estado' => 1]);
$db->caixas->createIndex(['estado' => 1, 'ultimo_evento.timestamp' => -1]);
$db->caixas->createIndex(['criado_em' => 1]);
$db->caixas->createIndex(['notas_fiscais.numero_nf' => 1]);
$db->eventos->createIndex(['caixa_id' => 1, 'timestamp' => -1]);
$db->eventos->createIndex(['peso_anomalo' => 1, 'timestamp' => -1]);
$db->eventos->createIndex(['abertura_indevida' => 1, 'timestamp' => -1]);
$db->filiais->createIndex(['codigo' => 1], ['unique' => true]);
$db->filiais->createIndex(['localizacao' => '2dsphere']);

echo "  Índices criados.\n";

// resumo do seed

$totalCaixas     = $db->caixas->countDocuments();
$totalEventos    = $db->eventos->countDocuments();
$totalFiliais    = $db->filiais->countDocuments();
$totalCategorias = $db->categorias->countDocuments();

echo "\n=== Seed concluído ===\n";
echo "  Filiais:    {$totalFiliais}\n";
echo "  Categorias: {$totalCategorias}\n";
echo "  Caixas:     {$totalCaixas}\n";
echo "  Eventos:    {$totalEventos}\n";
echo "  Banco:      {$mongoDb} em {$mongoHost}:{$mongoPort}\n\n";
