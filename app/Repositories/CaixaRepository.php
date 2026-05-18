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

    /**
     * @throws \InvalidArgumentException se a caixa não existir, não estiver em estado 'criada', ou dados forem inválidos
     * @throws \RuntimeException se a atualização falhar
     */
    public function adicionarNf(string $caixaId, array $dados): void
    {
        $caixa = $this->buscarPorId($caixaId);

        if ($caixa === null) {
            throw new \InvalidArgumentException('Caixa não encontrada.');
        }

        if ((string) $caixa['estado'] !== 'criada') {
            throw new \InvalidArgumentException('Só é possível vincular NF a caixas em estado "criada".');
        }

        $numeroNf = trim($dados['numero_nf'] ?? '');
        $clienteNome      = trim($dados['cliente_nome'] ?? '');
        $clienteDocumento = trim($dados['cliente_documento'] ?? '');
        $clienteCep       = preg_replace('/\D/', '', $dados['cliente_cep'] ?? '');
        $clienteLogradouro = trim($dados['cliente_logradouro'] ?? '');
        $clienteNumero    = trim($dados['cliente_numero'] ?? '');
        $clienteBairro    = trim($dados['cliente_bairro'] ?? '');
        $clienteCidade    = trim($dados['cliente_cidade'] ?? '');
        $clienteUf        = strtoupper(trim($dados['cliente_uf'] ?? ''));

        if ($numeroNf === '' || $clienteNome === '' || $clienteDocumento === '') {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: número da NF, nome e documento do cliente são exigidos.'
            );
        }

        $produtosRaw = $dados['produtos'] ?? [];
        if (empty($produtosRaw) || !is_array($produtosRaw)) {
            throw new \InvalidArgumentException('A NF deve conter ao menos um produto.');
        }

        $produtos   = [];
        $totalItens = 0;

        foreach ($produtosRaw as $p) {
            $nome         = trim($p['nome'] ?? '');
            $sku          = trim($p['sku'] ?? '');
            $categoria    = trim($p['categoria'] ?? '');
            $quantidade   = (int) ($p['quantidade'] ?? 0);
            $pesoUnitario = (int) ($p['peso_unitario'] ?? 0);
            $tolerancia   = (float) ($p['tolerancia'] ?? 0);

            if ($nome === '' || $categoria === '' || $quantidade <= 0 || $pesoUnitario <= 0) {
                throw new \InvalidArgumentException(
                    'Cada produto deve ter nome, categoria, quantidade e peso unitário válidos.'
                );
            }

            $produtos[]  = compact('nome', 'sku', 'categoria', 'quantidade', 'peso_unitario', 'tolerancia');
            $totalItens += $quantidade;
        }

        $nf = [
            'numero_nf'            => $numeroNf,
            'cliente_destinatario' => [
                'nome'      => $clienteNome,
                'documento' => $clienteDocumento,
                'endereco'  => [
                    'cep'        => $clienteCep,
                    'logradouro' => $clienteLogradouro,
                    'numero'     => $clienteNumero,
                    'bairro'     => $clienteBairro,
                    'cidade'     => $clienteCidade,
                    'uf'         => $clienteUf,
                ],
            ],
            'produtos' => $produtos,
        ];

        $totalAtual = (int) ($caixa['total_itens'] ?? 0);

        $resultado = $this->collection->updateOne(
            ['_id' => new ObjectId($caixaId)],
            [
                '$push' => ['notas_fiscais' => $nf],
                '$set'  => ['total_itens' => $totalAtual + $totalItens],
            ]
        );

        if ($resultado->getModifiedCount() !== 1) {
            throw new \RuntimeException('Falha ao vincular NF à caixa no MongoDB.');
        }
    }
}
