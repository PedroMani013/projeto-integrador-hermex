<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\UTCDateTime;

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
        ?string $busca = null
    ): array {

        $filtro = [];

        if (!empty($estado)) {
            $filtro['uf'] = $estado;
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

    /**
     * @throws \InvalidArgumentException se campos obrigatórios estiverem ausentes
     * @throws \RuntimeException se a inserção falhar no MongoDB
     */
    public function salvar(array $dados): void
    {
        $nome     = trim($dados['nome'] ?? '');
        $codigo   = trim($dados['codigo'] ?? '');
        $cidade   = trim($dados['cidade'] ?? '');
        $uf       = strtoupper(trim($dados['uf'] ?? $dados['estado'] ?? ''));
        $endereco = trim($dados['endereco'] ?? '');

        if ($nome === '' || $codigo === '' || $cidade === '' || $uf === '' || $endereco === '') {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: nome, código, cidade, UF e endereço são exigidos.'
            );
        }

        $documento = [
            'nome'                   => $nome,
            'codigo'                 => $codigo,
            'cidade'                 => $cidade,
            'uf'                     => $uf,
            'cep'                    => preg_replace('/\D/', '', $dados['cep'] ?? ''),
            'endereco'               => $endereco,
            'bairro'                 => trim($dados['bairro'] ?? ''),
            'numero'                 => trim($dados['numero'] ?? ''),
            'complemento'            => trim($dados['complemento'] ?? ''),
            'latitude'               => null,
            'longitude'              => null,
            'geocodificacao_pendente' => true,
            'criado_em'              => new UTCDateTime(),
        ];

        $resultado = $this->collection->insertOne($documento);

        if ($resultado->getInsertedCount() !== 1) {
            throw new \RuntimeException('Falha ao persistir filial no MongoDB.');
        }
    }
}