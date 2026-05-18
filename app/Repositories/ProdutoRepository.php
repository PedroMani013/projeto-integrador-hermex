<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;

class ProdutoRepository
{
    private Collection $collection;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance()->getDb();

        $this->collection = $db->produtos;
    }

    public function listar(): array
    {
        return $this->collection
            ->find([], ['sort' => ['criado_em' => -1]])
            ->toArray();
    }

    public function salvar(array $dados): void
    {
        $nome     = trim($dados['nome'] ?? '');
        $sku      = trim($dados['sku'] ?? '');
        $categoria = trim($dados['categoria'] ?? '');

        if ($nome === '' || $sku === '' || $categoria === '') {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: nome, SKU e categoria são exigidos.'
            );
        }

        $imagem = null;

        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] === UPLOAD_ERR_OK
        ) {
            $imagem = $this->validarEMoverImagem($_FILES['imagem']);
        }

        $this->collection->insertOne([
            'sku'            => $sku,
            'nome'           => $nome,
            'categoria'      => $categoria,
            'codigo_nfc'     => trim($dados['codigoNfc'] ?? ''),
            'peso_unitario'  => (float) ($dados['pesoUnitario'] ?? 0),
            'tolerancia'     => (float) ($dados['toleranciaPeso'] ?? 0),
            'descricao'      => trim($dados['descricao'] ?? ''),
            'ativo'          => true,
            'imagem'         => $imagem,
            'criado_em'      => new UTCDateTime(),
        ]);
    }

    public function buscarPorId(string $id): ?array
    {
        try {
            $resultado = $this->collection->findOne([
                '_id' => new ObjectId($id)
            ]);
        } catch (\Exception) {
            return null;
        }

        return $resultado ? (array) $resultado : null;
    }

    public function excluir(string $id): void
    {
        try {
            $this->collection->deleteOne([
                '_id' => new ObjectId($id)
            ]);
        } catch (\Exception) {
            // ID inválido — nada a excluir
        }
    }

    /**
     * @throws \RuntimeException se tipo, MIME ou tamanho forem inválidos
     */
    private function validarEMoverImagem(array $arquivo): string
    {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $mimePermitidos      = ['image/jpeg', 'image/png', 'image/webp'];
        $tamanhoMaximo       = 2 * 1024 * 1024; // 2 MB

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new \RuntimeException(
                'Formato de imagem não permitido. Use JPG, PNG ou WebP.'
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($arquivo['tmp_name']);

        if (!in_array($mime, $mimePermitidos, true)) {
            throw new \RuntimeException(
                'O arquivo enviado não é uma imagem válida.'
            );
        }

        if ($arquivo['size'] > $tamanhoMaximo) {
            throw new \RuntimeException(
                'A imagem não pode ultrapassar 2 MB.'
            );
        }

        $uploadDir = BASE_PATH . '/public/uploads/produtos/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $nomeArquivo    = uniqid('img_', true) . '.' . $extensao;
        $caminhoDestino = $uploadDir . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
            throw new \RuntimeException(
                'Falha ao mover o arquivo enviado.'
            );
        }

        return '/uploads/produtos/' . $nomeArquivo;
    }
}
