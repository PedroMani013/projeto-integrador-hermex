<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\UTCDateTime;

class AlertaRepository
{
    private \MongoDB\Collection $caixas;

    public function __construct()
    {
        $this->caixas = DatabaseConnection::getInstance()->getDb()->caixas;
    }

    public function contarAbertos(): int
    {
        return (int) $this->caixas->countDocuments([
            'estado'             => 'violada',
            'alerta_reconhecido' => ['$ne' => true],
        ]);
    }

    public function contarReconhecidosHoje(): int
    {
        $inicioDia = new UTCDateTime(strtotime(date('Y-m-d 00:00:00')) * 1000);
        return (int) $this->caixas->countDocuments([
            'alerta_reconhecido'                        => true,
            'ultimo_reconhecimento.reconhecido_em'      => ['$gte' => $inicioDia],
        ]);
    }

    /**
     * Tempo médio de reconhecimento em minutos (últimos 30 dias).
     * Calcula como: reconhecido_em - timestamp do ultimo_evento que gerou a violação.
     */
    public function tempoMedioReconhecimentoMin(): ?float
    {
        $inicio30d = new UTCDateTime((time() - 30 * 86400) * 1000);

        $pipeline = [
            ['$match' => [
                'estado'             => 'violada',
                'alerta_reconhecido' => true,
                'ultimo_reconhecimento.reconhecido_em' => ['$gte' => $inicio30d],
            ]],
            ['$project' => [
                'diffMs' => ['$subtract' => [
                    '$ultimo_reconhecimento.reconhecido_em',
                    '$ultimo_evento.timestamp',
                ]],
            ]],
            ['$group' => [
                '_id'    => null,
                'media'  => ['$avg' => '$diffMs'],
            ]],
        ];

        $resultado = $this->caixas->aggregate($pipeline)->toArray();
        if (empty($resultado)) {
            return null;
        }

        $mediaMs = (float) ($resultado[0]['media'] ?? 0);
        return round($mediaMs / 60000, 1);
    }

    /**
     * Lista alertas com filtros opcionais.
     * Ordenação: não reconhecidos primeiro, depois por último evento mais antigo (mais urgente).
     */
    public function listar(array $filtros = []): array
    {
        $query = ['estado' => 'violada'];

        if (!empty($filtros['filial_origem'])) {
            $query['filial_origem_codigo'] = $filtros['filial_origem'];
        }
        if (!empty($filtros['filial_destino'])) {
            $query['filial_destino_codigo'] = $filtros['filial_destino'];
        }
        if (!empty($filtros['transportadora'])) {
            $query['transportadora'] = $filtros['transportadora'];
        }
        if (isset($filtros['reconhecido']) && $filtros['reconhecido'] !== '') {
            $query['alerta_reconhecido'] = (bool) $filtros['reconhecido'];
        }
        if (!empty($filtros['data_inicio'])) {
            $ts = strtotime($filtros['data_inicio']);
            if ($ts !== false) {
                $query['ultimo_evento.timestamp']['$gte'] = new UTCDateTime($ts * 1000);
            }
        }
        if (!empty($filtros['data_fim'])) {
            $ts = strtotime($filtros['data_fim'] . ' 23:59:59');
            if ($ts !== false) {
                $query['ultimo_evento.timestamp']['$lte'] = new UTCDateTime($ts * 1000);
            }
        }

        return $this->caixas->find($query, [
            'sort' => [
                'alerta_reconhecido'       => 1,   // não reconhecidos (false/null) primeiro
                'ultimo_evento.timestamp'  => 1,   // mais antigo = mais urgente
            ],
        ])->toArray();
    }

    /** Retorna lista de transportadoras únicas em estado violada (para o filtro). */
    public function transportadorasUnicas(): array
    {
        $docs = $this->caixas->distinct('transportadora', ['estado' => 'violada']);
        return array_filter(array_map('strval', $docs));
    }
}
