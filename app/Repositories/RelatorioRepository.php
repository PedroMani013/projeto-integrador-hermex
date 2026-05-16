<?php

declare(strict_types=1);

namespace App\Repositories;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class RelatorioRepository
{
    private Collection $collection;

    public function __construct()
    {
        $client = new Client(
            'mongodb://localhost:27017'
        );

        $database = $client->hermex;

        $this->collection = $database->caixas;
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
}