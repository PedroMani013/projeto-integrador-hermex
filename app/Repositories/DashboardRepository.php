<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AlertaRecente;
use App\Models\Caixa;
use App\Models\Evento;
use Config\DatabaseConnection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class DashboardRepository
{
    private \MongoDB\Database $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getDb();
    }

    public function contarCaixasEmTransito(): int
    {
        return (int) $this->db->caixas->countDocuments(['estado' => 'em_transito']);
    }

    public function contarSinaisIsolados24h(): int
    {
        $limite = new UTCDateTime((time() - 86400) * 1000);
        return (int) $this->db->eventos->countDocuments([
            '$or' => [
                ['peso_anomalo'      => true],
                ['abertura_indevida' => true],
            ],
            'timestamp' => ['$gte' => $limite],
        ]);
    }

    public function contarCaixasEntreguesMes(): int
    {
        $inicioMes = new UTCDateTime(strtotime(date('Y-m-01')) * 1000);
        return (int) $this->db->caixas->countDocuments([
            'estado'    => 'entregue',
            'criado_em' => ['$gte' => $inicioMes],
        ]);
    }

    public function contarAlertasAbertos(): int
    {
        return (int) $this->db->caixas->countDocuments(['estado' => 'violada']);
    }

    /**
     * busca todas caixas no mongodb com estado entregue ou violada criadas nos últimos 14 dias
     * cada ponto = { data: string, percentual: float }
     * percentual = caixas entregues sem alerta / total entregues no dia * 100
     */
    public function integridade14dias(): array
    {
        $inicio = new UTCDateTime((time() - 13 * 86400) * 1000);

        $pipeline = [
            ['$match' => [
                'estado'    => ['$in' => ['entregue', 'violada']],
                'criado_em' => ['$gte' => $inicio],
            ]],
            ['$group' => [
                '_id'   => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$criado_em']],
                'total' => ['$sum' => 1],
                'semAlerta' => ['$sum' => ['$cond' => [['$eq' => ['$estado', 'entregue']], 1, 0]]],
            ]],
            ['$sort' => ['_id' => 1]],
            ['$project' => [
                'data'       => '$_id',
                'percentual' => ['$multiply' => [
                    ['$cond' => [
                        ['$gt' => ['$total', 0]],
                        ['$divide' => ['$semAlerta', '$total']],
                        1,
                    ]],
                    100,
                ]],
            ]],
        ];

        $resultado = [];
        // garantir todos os 14 dias mesmo sem dados
        for ($i = 13; $i >= 0; $i--) {
            $resultado[date('Y-m-d', time() - $i * 86400)] = 100.0;
        }

        foreach ($this->db->caixas->aggregate($pipeline) as $doc) {
            $resultado[(string) $doc['data']] = round((float) $doc['percentual'], 1);
        }

        $pontos = [];
        foreach ($resultado as $data => $pct) {
            $pontos[] = ['data' => $data, 'percentual' => $pct];
        }
        return $pontos;
    }

    /** @return AlertaRecente[] */
    public function alertasRecentes(int $limite = 5): array
    {
        $docs = $this->db->eventos->find(
            ['$or' => [['peso_anomalo' => true], ['abertura_indevida' => true]]],
            ['sort' => ['timestamp' => -1], 'limit' => $limite]
        );

        $alertas = [];
        foreach ($docs as $doc) {
            if (!isset($doc['caixa_id'])) {
                continue;
            }
            $caixa = $this->db->caixas->findOne(['_id' => $doc['caixa_id']]);
            if ($caixa === null) {
                continue;
            }

            $titulo = $this->tituloAlerta((string) $doc['tipo'], (bool) ($doc['peso_anomalo'] ?? false), (bool) ($doc['abertura_indevida'] ?? false));
            $nivel  = ((bool) ($doc['abertura_indevida'] ?? false)) ? 'critico' : 'anomalia';

            $alertas[] = new AlertaRecente(
                caixaCodigo:   (string) $caixa['codigo'],
                titulo:        $titulo,
                filialOrigem:  (string) $caixa['filial_origem_codigo'],
                filialDestino: (string) $caixa['filial_destino_codigo'],
                nivel:         $nivel,
                timestamp:     $this->toDateTime($doc['timestamp']),
            );
        }
        return $alertas;
    }

    /**
     * retorna caixas em_transito
     * ordem: alertas (violadas) primeiro, depois por último evento decrescente
     *
     * @return array{total: int, pagina: int, porPagina: int, caixas: Caixa[]}
     */
    public function caixasEmTransitoPaginado(int $pagina = 1, int $porPagina = 10): array
    {
        $filtro = ['estado' => ['$in' => ['em_transito', 'violada']]];
        $total  = (int) $this->db->caixas->countDocuments($filtro);

        $docs = $this->db->caixas->find($filtro, [
            'sort'  => ['estado' => -1, 'ultimo_evento.timestamp' => -1],
            'skip'  => ($pagina - 1) * $porPagina,
            'limit' => $porPagina,
        ]);

        $caixas = [];
        foreach ($docs as $doc) {
            $caixas[] = $this->hidratarCaixa($doc);
        }

        return [
            'total'    => $total,
            'pagina'   => $pagina,
            'porPagina' => $porPagina,
            'caixas'   => $caixas,
        ];
    }

    private function hidratarCaixa(object $doc): Caixa
    {
        $ultimoEvento = null;
        if (isset($doc['ultimo_evento'])) {
            $ue = $doc['ultimo_evento'];
            $ultimoEvento = new Evento(
                id:               '',
                caixaId:          (string) $doc['_id'],
                tipo:             (string) $ue['tipo'],
                valor:            $ue['valor'] ?? null,
                emMovimento:      (bool) ($ue['em_movimento'] ?? false),
                pesoAnomalo:      (bool) ($ue['peso_anomalo'] ?? false),
                aberturaIndevida: (bool) ($ue['abertura_indevida'] ?? false),
                timestamp:        $this->toDateTime($ue['timestamp']),
            );
        }

        return new Caixa(
            id:                  (string) $doc['_id'],
            codigo:              (string) $doc['codigo'],
            tagNfc:              (string) ($doc['tag_nfc'] ?? ''),
            estado:              (string) $doc['estado'],
            notaFiscal:          (string) ($doc['nota_fiscal'] ?? ''),
            totalItens:          (int)    ($doc['total_itens'] ?? 0),
            pesoBaseline:        (float)  ($doc['peso_baseline'] ?? 0),
            pesoAtual:           (float)  ($doc['peso_atual'] ?? 0),
            filialOrigemCodigo:  (string) ($doc['filial_origem_codigo'] ?? ''),
            filialDestinoCodigo: (string) ($doc['filial_destino_codigo'] ?? ''),
            filialOrigemNome:    (string) ($doc['filial_origem_nome'] ?? ''),
            filialDestinoNome:   (string) ($doc['filial_destino_nome'] ?? ''),
            transportadora:      (string) ($doc['transportadora'] ?? ''),
            previsaoChegada:     $this->toDateTime($doc['previsao_chegada']),
            ultimoEvento:        $ultimoEvento,
            criadoEm:            $this->toDateTime($doc['criado_em']),
        );
    }

    private function toDateTime(mixed $v): \DateTimeImmutable
    {
        if ($v instanceof UTCDateTime) {
            return \DateTimeImmutable::createFromMutable($v->toDateTime())->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        }
        return new \DateTimeImmutable();
    }

    private function tituloAlerta(string $tipo, bool $pesoAnomalo, bool $aberturaIndevida): string
    {
        if ($aberturaIndevida && $pesoAnomalo) {
            return 'Lacre aberto com variação de peso';
        }
        if ($aberturaIndevida) {
            return 'Tampa aberta durante o trânsito';
        }
        return 'Peso fora da tolerância configurada';
    }
}
