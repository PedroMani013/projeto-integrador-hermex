<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class RelatorioRepository
{
    private Collection $collection;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance()->getDb();

        $this->collection = $db->caixas;
    }

    public function buscarPorPeriodo(
        string $periodo,
        ?string $dataInicio = null,
        ?string $dataFim = null
    ): array {

        $filtro = [];

        $agora = new \DateTime();

        switch ($periodo) {

            case 'dia':

                $inicio = (clone $agora)->modify('-1 day');

                break;

            case 'mes':

                $inicio = (clone $agora)->modify('-1 month');

                break;

            case 'personalizado':

                if ($dataInicio && $dataFim) {

                    $inicio = new \DateTime($dataInicio . ' 00:00:00');

                    $fim = new \DateTime($dataFim . ' 23:59:59');

                    $filtro['criado_em'] = [
                        '$gte' => new UTCDateTime(
                            $inicio->getTimestamp() * 1000
                        ),
                        '$lte' => new UTCDateTime(
                            $fim->getTimestamp() * 1000
                        ),
                    ];
                }

                break;

            default:

                $inicio = (clone $agora)->modify('-7 days');

                break;
        }

        if ($periodo !== 'personalizado') {

            $filtro['criado_em'] = [
                '$gte' => new UTCDateTime(
                    $inicio->getTimestamp() * 1000
                )
            ];
        }

        $resultado = $this->collection
            ->find($filtro)
            ->toArray();

        return array_map(function ($item) {

            return (array) $item;

        }, $resultado);
    }

    /**
     * H24 — cadeia de custódia completa de uma caixa específica.
     * Retorna null se a caixa não for encontrada.
     *
     * @return array{ caixa: array, eventos: array }|null
     */
    public function custodiaCompleta(string $codigo): ?array
    {
        $caixa = $this->collection->findOne(['codigo' => $codigo]);

        if ($caixa === null) {
            return null;
        }

        $db     = DatabaseConnection::getInstance()->getDb();
        $eventos = $db->eventos
            ->find(
                ['caixa_id' => $caixa['_id']],
                ['sort' => ['timestamp' => 1]]
            )
            ->toArray();

        return [
            'caixa'   => (array) $caixa,
            'eventos' => array_map(fn($e) => (array) $e, $eventos),
        ];
    }
}