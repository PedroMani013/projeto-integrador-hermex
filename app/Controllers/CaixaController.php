<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CaixaRepository;
use App\Repositories\CategoriaRepository;
use App\Repositories\FilialRepository;

class CaixaController
{
    private CaixaRepository $repository;

    public function __construct()
    {
        $this->repository = new CaixaRepository();
    }

    public function index(): void
    {
        $caixas = $this->repository->listar();

        require BASE_PATH . '/app/Views/caixas/index.php';
    }

    public function cadastro(): void
    {
        $filiais    = (new FilialRepository())->listar();
        $categorias = (new CategoriaRepository())->listar();

        require BASE_PATH . '/app/Views/caixas/cadastro-caixa.php';
    }

    public function detalhe(): void
    {
        $id    = $_GET['id'] ?? '';
        $caixa = $this->repository->buscarPorId($id);

        if ($caixa === null) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/erros/404.php';
            exit;
        }

        $eventos = $this->repository->buscarEventos($id, 50);

        // série de peso para o gráfico (apenas eventos tipo 'peso', ordem cronológica)
        $seriePeso = [];
        foreach (array_reverse($eventos) as $ev) {
            if ((string) ($ev['tipo'] ?? '') === 'peso') {
                $ts = $ev['timestamp'] ?? null;
                $seriePeso[] = [
                    'ts'    => $ts instanceof \MongoDB\BSON\UTCDateTime
                        ? $ts->toDateTime()->format('H:i d/m')
                        : '',
                    'valor' => (float) ($ev['valor'] ?? 0),
                ];
            }
        }

        require BASE_PATH . '/app/Views/caixas/detalhe.php';
    }

    public function lacrar(): void
    {
        $id    = $_GET['id'] ?? '';
        $caixa = $this->repository->buscarPorId($id);

        if ($caixa === null || (string) $caixa['estado'] !== 'criada') {
            $_SESSION['erro'] = 'Caixa não encontrada ou não está em estado "criada".';
            header('Location: ' . BASE_URL . '?action=caixas');
            exit;
        }

        require BASE_PATH . '/app/Views/caixas/lacrar-caixa.php';
    }

    public function vincularNf(): void
    {
        $id    = $_GET['id'] ?? '';
        $caixa = $this->repository->buscarPorId($id);

        if ($caixa === null || (string) $caixa['estado'] !== 'criada') {
            $_SESSION['erro'] = 'Caixa não encontrada ou não está em estado "criada".';
            header('Location: ' . BASE_URL . '?action=caixas');
            exit;
        }

        $categorias = (new CategoriaRepository())->listar();

        require BASE_PATH . '/app/Views/caixas/vincular-nf.php';
    }

    public function despachar(): void
    {
        $caixaId = $_POST['caixa_id'] ?? '';

        try {
            $caixa = $this->repository->buscarPorId($caixaId);

            if ($caixa === null || (string) $caixa['estado'] !== 'lacrada') {
                throw new \InvalidArgumentException('Caixa não encontrada ou não está em estado "lacrada".');
            }

            $this->repository->atualizar($caixaId, ['estado' => 'em_transito']);

            $_SESSION['sucesso'] = 'Caixa despachada. Monitoramento iniciado.';
            header('Location: ' . BASE_URL . '?action=caixas');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '?action=caixas');

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao despachar caixa. Tente novamente.';
            header('Location: ' . BASE_URL . '?action=caixas');
        }

        exit;
    }

    public function salvar(): void
    {
        try {
            $caixaId = $this->repository->salvar($_POST);
            $this->repository->adicionarNf($caixaId, $_POST);

            $_SESSION['sucesso'] = 'Caixa cadastrada com sucesso!';
            header('Location: ' . BASE_URL . '?action=caixas');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '?action=cadastro-caixa');

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao salvar caixa. Tente novamente.';
            header('Location: ' . BASE_URL . '?action=cadastro-caixa');
        }

        exit;
    }

    public function confirmarLacre(): void
    {
        $caixaId = $_POST['caixa_id'] ?? '';

        try {
            $this->repository->lacrar($caixaId, $_POST);

            $_SESSION['sucesso'] = 'Caixa lacrada com sucesso!';
            header('Location: ' . BASE_URL . '?action=caixas');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '?action=lacrar-caixa&id=' . urlencode($caixaId));

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao lacrar caixa. Tente novamente.';
            header('Location: ' . BASE_URL . '?action=lacrar-caixa&id=' . urlencode($caixaId));
        }

        exit;
    }

    public function confirmarNf(): void
    {
        $caixaId = $_POST['caixa_id'] ?? '';

        try {
            $this->repository->adicionarNf($caixaId, $_POST);

            $_SESSION['sucesso'] = 'Nota fiscal vinculada com sucesso!';
            header('Location: ' . BASE_URL . '?action=caixas');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '?action=vincular-nf&id=' . urlencode($caixaId));

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao vincular NF. Tente novamente.';
            header('Location: ' . BASE_URL . '?action=vincular-nf&id=' . urlencode($caixaId));
        }

        exit;
    }

    public function reconhecerAlerta(): void
    {
        $caixaId        = $_POST['caixa_id'] ?? '';
        $classificacao  = $_POST['classificacao'] ?? '';
        $observacao     = trim($_POST['observacao'] ?? '');

        $classificacoesValidas = [
            'violacao_confirmada',
            'conferencia_legitima_fora_de_ordem',
            'investigacao_concluida_sem_violacao',
            'outro',
        ];

        try {
            $caixa = $this->repository->buscarPorId($caixaId);

            if ($caixa === null || (string) $caixa['estado'] !== 'violada') {
                throw new \InvalidArgumentException('Caixa não encontrada ou não está em estado "violada".');
            }

            if (!in_array($classificacao, $classificacoesValidas, true)) {
                throw new \InvalidArgumentException('Classificação inválida.');
            }

            if (mb_strlen($observacao) < 10) {
                throw new \InvalidArgumentException('A observação deve ter pelo menos 10 caracteres.');
            }

            $reconhecimento = [
                'classificacao' => $classificacao,
                'observacao'    => $observacao,
                'reconhecido_em' => new \MongoDB\BSON\UTCDateTime(),
                'operador'      => $_SESSION['usuario'] ?? 'desconhecido',
            ];

            $this->repository->atualizar($caixaId, [
                'alerta_reconhecido'   => true,
                'ultimo_reconhecimento' => $reconhecimento,
            ]);

            $_SESSION['sucesso'] = 'Alerta reconhecido e registrado no histórico.';
            header('Location: ' . BASE_URL . '?action=detalhe-caixa&id=' . urlencode($caixaId));

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: ' . BASE_URL . '?action=detalhe-caixa&id=' . urlencode($caixaId));

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao reconhecer alerta.';
            header('Location: ' . BASE_URL . '?action=detalhe-caixa&id=' . urlencode($caixaId));
        }

        exit;
    }
}
