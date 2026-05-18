<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class CaixaRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection = DatabaseConnection::getInstance()->getDb()->caixas;
    }

    public function listar(): array
    {
        return $this->collection
            ->find(
                ['estado' => ['$in' => ['criada', 'lacrada']]],
                ['sort' => ['criado_em' => -1]]
            )
            ->toArray();
    }

    public function buscarPorId(string $id): ?object
    {
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }

    public function tagNfcAtiva(string $tagNfc): bool
    {
        return $this->collection->countDocuments([
            'tag_nfc' => $tagNfc,
            'estado'  => ['$in' => ['criada', 'lacrada', 'em_transito']],
        ]) > 0;
    }

    /**
     * @throws \InvalidArgumentException se campos obrigatórios estiverem ausentes ou tag NFC já estiver em uso
     * @throws \RuntimeException se a inserção falhar
     */
    public function salvar(array $dados): void
    {
        $codigo      = trim($dados['codigo'] ?? '');
        $tagNfc      = trim($dados['tag_nfc'] ?? '');
        $origemCod   = trim($dados['filial_origem_codigo'] ?? '');
        $destinoCod  = trim($dados['filial_destino_codigo'] ?? '');
        $transportadora = trim($dados['transportadora'] ?? '');

        if ($codigo === '' || $tagNfc === '' || $origemCod === '' || $destinoCod === '' || $transportadora === '') {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: código, tag NFC, filiais e transportadora são exigidos.'
            );
        }

        if ($origemCod === $destinoCod) {
            throw new \InvalidArgumentException('Filial de origem e destino não podem ser iguais.');
        }

        if ($this->tagNfcAtiva($tagNfc)) {
            throw new \InvalidArgumentException("A tag NFC '{$tagNfc}' já está associada a uma caixa ativa.");
        }

        $filialRepo = new FilialRepository();
        $filiais    = $filialRepo->listar();
        $filialMap  = [];
        foreach ($filiais as $f) {
            $filialMap[(string) $f['codigo']] = (string) $f['nome'];
        }

        if (!isset($filialMap[$origemCod])) {
            throw new \InvalidArgumentException("Filial de origem '{$origemCod}' não encontrada.");
        }
        if (!isset($filialMap[$destinoCod])) {
            throw new \InvalidArgumentException("Filial de destino '{$destinoCod}' não encontrada.");
        }

        $resultado = $this->collection->insertOne([
            'codigo'                    => $codigo,
            'tag_nfc'                   => $tagNfc,
            'estado'                    => 'criada',
            'notas_fiscais'             => [],
            'total_itens'               => 0,
            'peso_baseline'             => 0,
            'peso_atual'                => 0,
            'tolerancia_efetiva'        => null,
            'anomalia_peso_iniciada_em' => null,
            'lacrada_em'                => null,
            'filial_origem_codigo'      => $origemCod,
            'filial_destino_codigo'     => $destinoCod,
            'filial_origem_nome'        => $filialMap[$origemCod],
            'filial_destino_nome'       => $filialMap[$destinoCod],
            'transportadora'            => $transportadora,
            'previsao_chegada'          => null,
            'ultimo_evento'             => null,
            'criado_em'                 => new UTCDateTime(),
        ]);

        if ($resultado->getInsertedCount() !== 1) {
            throw new \RuntimeException('Falha ao persistir caixa no MongoDB.');
        }
    }
}
