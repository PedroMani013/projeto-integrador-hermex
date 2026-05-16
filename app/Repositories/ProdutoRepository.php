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

        // upload da imagem
        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] === UPLOAD_ERR_OK
        ) {

            $uploadDir = BASE_PATH . '/public/uploads/produtos/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $nomeArquivo = uniqid() . '-' . basename($_FILES['imagem']['name']);

            $caminhoArquivo = $uploadDir . $nomeArquivo;

            move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                $caminhoArquivo
            );

            $imagem = '/uploads/produtos/' . $nomeArquivo;
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