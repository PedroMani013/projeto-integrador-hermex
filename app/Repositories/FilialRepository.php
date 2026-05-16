<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;

class FilialRepository
{
    private $collection;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance()->getDb();

        $this->collection = $db->filiais;
    }

    public function listar(
        ?string $estado = null,
        ?string $status = null,
        ?string $busca = null
    ): array {

        $filtro = [];

        if (!empty($estado)) {
            $filtro['uf'] = $estado;
        }

        if (!empty($status)) {
            $filtro['status'] = $status;
        }

        if (!empty($busca)) {

            $filtro['$or'] = [

                [
                    'nome' => [
                        '$regex' => $busca,
                        '$options' => 'i'
                    ]
                ],

                [
                    'cidade' => [
                        '$regex' => $busca,
                        '$options' => 'i'
                    ]
                ],

                [
                    'codigo' => [
                        '$regex' => $busca,
                        '$options' => 'i'
                    ]
                ]
            ];
        }

        return $this->collection
            ->find($filtro)
            ->toArray();
    }
}