<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\UTCDateTime;

class CategoriaRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection = DatabaseConnection::getInstance()->getDb()->categorias;
    }

    public function listar(): array
    {
        return $this->collection
            ->find([], ['sort' => ['nome' => 1]])
            ->toArray();
    }

    public function salvar(array $dados): void
    {
        $codigo           = trim($dados['codigo'] ?? '');
        $nome             = trim($dados['nome'] ?? '');
        $toleranciaPadrao = (float) ($dados['tolerancia_padrao'] ?? 0);

        if ($codigo === '' || $nome === '' || $toleranciaPadrao <= 0) {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: código, nome e tolerância padrão são exigidos.'
            );
        }

        $existente = $this->collection->findOne(['codigo' => $codigo]);
        if ($existente !== null) {
            throw new \InvalidArgumentException("Já existe uma categoria com o código '{$codigo}'.");
        }

        $resultado = $this->collection->insertOne([
            'codigo'            => $codigo,
            'nome'              => $nome,
            'tolerancia_padrao' => $toleranciaPadrao,
            'criado_em'         => new UTCDateTime(),
            'atualizado_em'     => new UTCDateTime(),
        ]);

        if ($resultado->getInsertedCount() !== 1) {
            throw new \RuntimeException('Falha ao persistir categoria no MongoDB.');
        }
    }
}
