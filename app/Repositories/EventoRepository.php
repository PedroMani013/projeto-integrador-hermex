<?php

declare(strict_types=1);

namespace App\Repositories;

use Config\DatabaseConnection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use App\Repositories\CaixaRepository;

class EventoRepository
{
    private $eventos;
    private $caixas;

    public function __construct()
    {
        $db           = DatabaseConnection::getInstance()->getDb();
        $this->eventos = $db->eventos;
        $this->caixas  = $db->caixas;
    }

    /**
     * H04 — evento de peso.
     * Detecta anomalia comparando com baseline × tolerancia_efetiva.
     * Transiciona para 'violada' se anomalia confirmada em caixa em_transito.
     *
     * @throws \InvalidArgumentException se a caixa não existir ou não estiver em estado válido
     */
    public function registrarPeso(string $caixaId, float $valorGramas): void
    {
        $caixa = $this->caixas->findOne(['_id' => new ObjectId($caixaId)]);

        if ($caixa === null) {
            throw new \InvalidArgumentException('Caixa não encontrada.');
        }

        $estado    = (string) ($caixa['estado'] ?? '');
        $estadosValidos = ['lacrada', 'em_transito'];
        if (!in_array($estado, $estadosValidos, true)) {
            throw new \InvalidArgumentException("Evento de peso só é aceito em caixas lacradas ou em trânsito (estado atual: {$estado}).");
        }

        $baseline    = (float) ($caixa['peso_baseline'] ?? 0);
        $tolerancia  = (float) ($caixa['tolerancia_efetiva'] ?? 0);
        $pesoAnomalo = false;

        if ($baseline > 0 && $tolerancia > 0) {
            $pesoAnomalo = abs($valorGramas - $baseline) > $baseline * $tolerancia / 100;
        }

        $agora = new UTCDateTime();

        $evento = [
            'caixa_id'         => new ObjectId($caixaId),
            'tipo'             => 'peso',
            'valor'            => $valorGramas,
            'em_movimento'     => false,
            'peso_anomalo'     => $pesoAnomalo,
            'abertura_indevida'=> false,
            'timestamp'        => $agora,
        ];

        $this->eventos->insertOne($evento);

        $setCaixa = [
            'peso_atual'    => $valorGramas,
            'ultimo_evento' => $evento,
        ];

        // anomalia_peso_iniciada_em: setar ao primeiro evento anômalo, limpar quando normal
        $anomaliaAtual = $caixa['anomalia_peso_iniciada_em'] ?? null;
        if ($pesoAnomalo && $anomaliaAtual === null) {
            $setCaixa['anomalia_peso_iniciada_em'] = $agora;
        } elseif (!$pesoAnomalo && $anomaliaAtual !== null) {
            $setCaixa['anomalia_peso_iniciada_em'] = null;
        }

        // peso anômalo em caixa em_transito → violada
        if ($pesoAnomalo && $estado === 'em_transito') {
            $setCaixa['estado'] = 'violada';
        }

        $this->caixas->updateOne(
            ['_id' => new ObjectId($caixaId)],
            ['$set' => $setCaixa]
        );
    }

    /**
     * H05 — evento de abertura de tampa.
     * Abertura indevida em caixa em_transito → dispara estado 'violada'.
     *
     * @throws \InvalidArgumentException se a caixa não existir
     */
    public function registrarTampa(string $caixaId): void
    {
        $caixa = $this->caixas->findOne(['_id' => new ObjectId($caixaId)]);

        if ($caixa === null) {
            throw new \InvalidArgumentException('Caixa não encontrada.');
        }

        $estado           = (string) ($caixa['estado'] ?? '');
        $aberturaIndevida = ($estado === 'em_transito');

        $evento = [
            'caixa_id'          => new ObjectId($caixaId),
            'tipo'              => 'tampa',
            'valor'             => null,
            'em_movimento'      => false,
            'peso_anomalo'      => false,
            'abertura_indevida' => $aberturaIndevida,
            'timestamp'         => new UTCDateTime(),
        ];

        $this->eventos->insertOne($evento);

        $setCaixa = ['ultimo_evento' => $evento];

        if ($aberturaIndevida) {
            $setCaixa['estado'] = 'violada';
        }

        $this->caixas->updateOne(
            ['_id' => new ObjectId($caixaId)],
            ['$set' => $setCaixa]
        );
    }

    /**
     * H06 — evento de leitura NFC.
     * Leitura NFC em caixa em_transito → transiciona para 'entregue'.
     *
     * @throws \InvalidArgumentException se a caixa não existir
     */
    public function registrarNfc(string $caixaId): void
    {
        $caixa = $this->caixas->findOne(['_id' => new ObjectId($caixaId)]);

        if ($caixa === null) {
            throw new \InvalidArgumentException('Caixa não encontrada.');
        }

        $estado = (string) ($caixa['estado'] ?? '');

        $evento = [
            'caixa_id'          => new ObjectId($caixaId),
            'tipo'              => 'nfc',
            'valor'             => null,
            'em_movimento'      => false,
            'peso_anomalo'      => false,
            'abertura_indevida' => false,
            'timestamp'         => new UTCDateTime(),
        ];

        $this->eventos->insertOne($evento);

        $setCaixa = ['ultimo_evento' => $evento];

        if ($estado === 'em_transito') {
            $setCaixa['estado'] = 'entregue';
        }

        $this->caixas->updateOne(
            ['_id' => new ObjectId($caixaId)],
            ['$set' => $setCaixa]
        );
    }

    /**
     * Lookup por tag NFC — usado pela página mobile de recepção (H11).
     * Valida que a caixa existe e está em_transito antes de registrar.
     *
     * @return array{ caixa_id: string, codigo: string }
     * @throws \InvalidArgumentException se a tag não for encontrada ou caixa não estiver em trânsito
     */
    public function registrarNfcPorTag(string $tagNfc): array
    {
        $caixaRepo = new CaixaRepository();
        $caixa     = $caixaRepo->buscarPorTagNfc($tagNfc);

        if ($caixa === null) {
            throw new \InvalidArgumentException("Nenhuma caixa encontrada com a tag NFC '{$tagNfc}'.");
        }

        $estado = (string) ($caixa['estado'] ?? '');
        if ($estado === 'entregue') {
            // entrega já registrada anteriormente — retorna sucesso idempotente
            return [
                'caixa_id' => (string) $caixa['_id'],
                'codigo'   => (string) ($caixa['codigo'] ?? ''),
                'ja_entregue' => true,
            ];
        }
        if ($estado !== 'em_transito') {
            throw new \InvalidArgumentException(
                "Esta caixa não pode ser recebida (estado atual: {$estado})."
            );
        }

        $caixaId = (string) $caixa['_id'];
        $this->registrarNfc($caixaId);

        return [
            'caixa_id' => $caixaId,
            'codigo'   => (string) ($caixa['codigo'] ?? ''),
        ];
    }

    /** Retorna os últimos $limite eventos de uma caixa, do mais recente ao mais antigo. */
    public function buscarPorCaixa(string $caixaId, int $limite = 50): array
    {
        return $this->eventos
            ->find(
                ['caixa_id' => new ObjectId($caixaId)],
                ['sort' => ['timestamp' => -1], 'limit' => $limite]
            )
            ->toArray();
    }
}
