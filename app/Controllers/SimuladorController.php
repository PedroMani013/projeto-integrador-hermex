<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CaixaRepository;
use App\Repositories\EventoRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Config\DatabaseConnection;

class SimuladorController
{
    private CaixaRepository $caixas;
    private EventoRepository $eventos;

    public function __construct()
    {
        $this->caixas  = new CaixaRepository();
        $this->eventos = new EventoRepository();
    }

    public function index(): void
    {
        $db = DatabaseConnection::getInstance()->getDb();

        $caixasSimulaveis = $db->caixas
            ->find(
                ['estado' => ['$in' => ['lacrada', 'em_transito', 'violada']]],
                ['sort' => ['codigo' => 1]]
            )
            ->toArray();

        require BASE_PATH . '/app/Views/simulador/index.php';
    }

    public function executar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $acao   = $_POST['acao']    ?? '';
        $id     = $_POST['caixa_id'] ?? '';
        $peso   = $_POST['peso']    ?? null;

        try {
            $caixa = $this->caixas->buscarPorId($id);

            if ($caixa === null) {
                echo json_encode(['ok' => false, 'erro' => 'Caixa não encontrada.']);
                return;
            }

            $estado = (string) $caixa['estado'];

            switch ($acao) {
                case 'despachar':
                    if ($estado !== 'lacrada') {
                        echo json_encode(['ok' => false, 'erro' => "Caixa deve estar 'lacrada' (estado atual: {$estado})."]);
                        return;
                    }
                    $this->caixas->atualizar($id, ['estado' => 'em_transito']);
                    $this->registrarEvento($id, 'transicao', 'em_transito');
                    echo json_encode(['ok' => true, 'mensagem' => "Caixa {$caixa['codigo']} despachada → em_transito."]);
                    break;

                case 'peso_normal':
                    if (!in_array($estado, ['lacrada', 'em_transito'], true)) {
                        echo json_encode(['ok' => false, 'erro' => "Caixa deve estar em trânsito (estado atual: {$estado})."]);
                        return;
                    }
                    $baseline = (int) ($caixa['peso_baseline'] ?? 0);
                    $ruido    = rand(-30, 30);
                    $valorPeso = $peso !== null ? (int) $peso : ($baseline + $ruido);
                    $this->caixas->atualizar($id, [
                        'peso_atual'                => $valorPeso,
                        'anomalia_peso_iniciada_em' => null,
                        'ultimo_evento'             => $this->eventoInline('peso', $valorPeso, false, false),
                    ]);
                    $this->registrarEvento($id, 'peso', $valorPeso, false, false);
                    echo json_encode(['ok' => true, 'mensagem' => "Leitura de peso registrada: {$valorPeso}g (baseline {$baseline}g)."]);
                    break;

                case 'peso_anomalo':
                    if (!in_array($estado, ['lacrada', 'em_transito'], true)) {
                        echo json_encode(['ok' => false, 'erro' => "Caixa deve estar em trânsito (estado atual: {$estado})."]);
                        return;
                    }
                    $baseline   = (int) ($caixa['peso_baseline'] ?? 0);
                    $tolerancia = (float) ($caixa['tolerancia_efetiva'] ?? 5);
                    $desvio     = $tolerancia + rand(5, 20);
                    $valorPeso  = $peso !== null ? (int) $peso : (int) ($baseline * (1 - $desvio / 100));

                    // ignorar timer de 5min — transição imediata para demo
                    $db = DatabaseConnection::getInstance()->getDb();
                    $db->caixas->updateOne(
                        ['_id' => new ObjectId($id)],
                        ['$set' => [
                            'estado'                    => 'violada',
                            'peso_atual'                => $valorPeso,
                            'anomalia_peso_iniciada_em' => new UTCDateTime(),
                            'alerta_reconhecido'        => false,
                            'ultimo_evento'             => $this->eventoInline('peso', $valorPeso, false, true),
                        ]]
                    );
                    $this->registrarEvento($id, 'peso', $valorPeso, false, true);
                    echo json_encode(['ok' => true, 'mensagem' => "Anomalia de peso simulada ({$valorPeso}g vs baseline {$baseline}g). Caixa → violada."]);
                    break;

                case 'abertura_tampa':
                    if ($estado !== 'em_transito') {
                        echo json_encode(['ok' => false, 'erro' => "Abertura indevida só é detectada durante o trânsito (estado atual: {$estado})."]);
                        return;
                    }
                    $db = DatabaseConnection::getInstance()->getDb();
                    $db->caixas->updateOne(
                        ['_id' => new ObjectId($id)],
                        ['$set' => [
                            'estado'             => 'violada',
                            'alerta_reconhecido' => false,
                            'ultimo_evento'      => $this->eventoInline('tampa', 'aberta', true, false),
                        ]]
                    );
                    $this->registrarEvento($id, 'tampa', 'aberta', true, false);
                    echo json_encode(['ok' => true, 'mensagem' => "Abertura indevida da tampa simulada. Caixa {$caixa['codigo']} → violada."]);
                    break;

                case 'entrega_nfc':
                    if ($estado !== 'em_transito') {
                        echo json_encode(['ok' => false, 'erro' => "Caixa deve estar em_transito para receber NFC (estado atual: {$estado})."]);
                        return;
                    }
                    $this->caixas->atualizar($id, ['estado' => 'entregue']);
                    $this->registrarEvento($id, 'nfc', 'confirmado');
                    echo json_encode(['ok' => true, 'mensagem' => "Recepção NFC confirmada. Caixa {$caixa['codigo']} → entregue."]);
                    break;

                default:
                    echo json_encode(['ok' => false, 'erro' => "Ação desconhecida: '{$acao}'."]);
            }

        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    private function registrarEvento(
        string $caixaId,
        string $tipo,
        mixed $valor,
        bool $aberturaIndevida = false,
        bool $pesoAnomalo = false
    ): void {
        $db = DatabaseConnection::getInstance()->getDb();
        $db->eventos->insertOne([
            'caixa_id'          => new ObjectId($caixaId),
            'tipo'              => $tipo,
            'valor'             => $valor,
            'em_movimento'      => $tipo === 'transicao' && $valor === 'em_transito',
            'peso_anomalo'      => $pesoAnomalo,
            'abertura_indevida' => $aberturaIndevida,
            'timestamp'         => new UTCDateTime(),
        ]);
    }

    private function eventoInline(
        string $tipo,
        mixed $valor,
        bool $aberturaIndevida = false,
        bool $pesoAnomalo = false
    ): array {
        return [
            'tipo'              => $tipo,
            'valor'             => $valor,
            'em_movimento'      => false,
            'peso_anomalo'      => $pesoAnomalo,
            'abertura_indevida' => $aberturaIndevida,
            'timestamp'         => new UTCDateTime(),
        ];
    }
}
