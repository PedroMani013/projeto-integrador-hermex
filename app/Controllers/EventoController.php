<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EventoRepository;

class EventoController
{
    /**
     * POST /?action=api-evento
     * Aceita JSON no body ou campos de formulário.
     * Campos: caixa_id (string), tipo (peso|tampa|nfc), valor (float, só para peso)
     * Responde sempre em JSON.
     */
    public function receberEvento(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        // aceita JSON no body ou form POST
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $body = (array) json_decode(file_get_contents('php://input'), true);
        } else {
            $body = $_POST;
        }

        $caixaId = trim($body['caixa_id'] ?? '');
        $tipo    = trim($body['tipo'] ?? '');

        if ($caixaId === '' || $tipo === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'erro' => 'Campos caixa_id e tipo são obrigatórios.']);
            return;
        }

        $repository = new EventoRepository();

        try {

            match ($tipo) {
                'peso'  => $repository->registrarPeso($caixaId, (float) ($body['valor'] ?? 0)),
                'tampa' => $repository->registrarTampa($caixaId),
                'nfc'   => $repository->registrarNfc($caixaId),
                default => throw new \InvalidArgumentException("Tipo de evento desconhecido: {$tipo}. Use: peso, tampa ou nfc."),
            };

            echo json_encode(['ok' => true]);

        } catch (\InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);

        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'erro' => 'Erro interno ao registrar evento.']);
        }
    }
}
