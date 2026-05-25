<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ViaCepService
{
    private const BASE_URL = 'https://viacep.com.br/ws/';
    private const TIMEOUT  = 10;

    /**
     * consulta o ViaCEP e retorna array com logradouro, bairro, localidade, uf, cep.
     * @throws RuntimeException se CEP inválido ou não encontrado
     */
    public function buscarCep(string $cep): array
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            throw new RuntimeException("CEP inválido: {$cep}");
        }

        $url = self::BASE_URL . $cepLimpo . '/json/';

        $ctx = stream_context_create(['http' => [
            'timeout' => self::TIMEOUT,
            'method'  => 'GET',
            'header'  => "User-Agent: HermeX/1.0\r\n",
        ]]);

        $resposta = @file_get_contents($url, false, $ctx);

        if ($resposta === false) {
            throw new RuntimeException("Falha ao conectar com ViaCEP para o CEP {$cep}");
        }

        $dados = json_decode($resposta, true);

        if (isset($dados['erro']) && $dados['erro'] === true) {
            throw new RuntimeException("CEP {$cep} não encontrado no ViaCEP");
        }

        return [
            'cep'        => $dados['cep']        ?? $cep,
            'logradouro' => $dados['logradouro']  ?? '',
            'bairro'     => $dados['bairro']      ?? '',
            'cidade'     => $dados['localidade']  ?? '',
            'uf'         => $dados['uf']          ?? '',
        ];
    }
}
