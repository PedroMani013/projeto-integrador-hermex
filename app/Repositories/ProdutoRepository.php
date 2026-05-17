<?php

declare(strict_types=1);

namespace App\Repositories;

class ProdutoRepository
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['produtos'])) {

            $_SESSION['produtos'] = [

                [
                    'id' => 1,
                    'sku' => 'HM-ELE-0042',
                    'nome' => 'MacBook Pro M3 Max',
                    'categoria' => 'Eletrônico',
                    'toleranciaPeso' => 0.8,
                    'codigoNfc' => 'NFC-001',
                    'pesoUnitario' => 2.1,
                    'descricao' => 'Notebook de alto desempenho',
                    'ativo' => true,
                    'imagem' => null
                ],

                [
                    'id' => 2,
                    'sku' => 'HM-MED-0011',
                    'nome' => 'Monitor Cardíaco',
                    'categoria' => 'Médico',
                    'toleranciaPeso' => 2.5,
                    'codigoNfc' => 'NFC-002',
                    'pesoUnitario' => 1.4,
                    'descricao' => 'Equipamento hospitalar',
                    'ativo' => true,
                    'imagem' => null
                ]
            ];
        }
    }

    public function listar(): array
    {
        return $_SESSION['produtos'];
    }

    public function salvar(array $dados): void
    {
        $produtos = $_SESSION['produtos'];

        $novoId = count($produtos) + 1;

        $imagem = null;

        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] === UPLOAD_ERR_OK
        ) {
            $imagem = $this->validarEMoverImagem($_FILES['imagem']);
        }

        $_SESSION['produtos'][] = [

            'id' => $novoId,

            'nome' => $dados['nome'] ?? '',

            'sku' => $dados['sku'] ?? '',

            'categoria' => $dados['categoria'] ?? '',

            'codigoNfc' => $dados['codigoNfc'] ?? '',

            'pesoUnitario' => (float)($dados['pesoUnitario'] ?? 0),

            'toleranciaPeso' => (float)($dados['toleranciaPeso'] ?? 0),

            'descricao' => $dados['descricao'] ?? '',

            'ativo' => true,

            'imagem' => $imagem
        ];
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

    public function buscarPorId(int $id): ?array
    {
        foreach ($_SESSION['produtos'] as $produto) {

            if ((int)$produto['id'] === $id) {
                return $produto;
            }
        }

        return null;
    }

    public function excluir(int $id): void
    {
        foreach ($_SESSION['produtos'] as $key => $produto) {

            if ((int)$produto['id'] === $id) {

                unset($_SESSION['produtos'][$key]);

                $_SESSION['produtos'] = array_values($_SESSION['produtos']);

                break;
            }
        }
    }
}