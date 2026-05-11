<?php

declare(strict_types=1);

namespace App\Models;

class Filial
{
    public function __construct(
        public readonly string $id,
        public readonly string $codigo,
        public readonly string $nome,
        public readonly string $cep,
        public readonly string $logradouro,
        public readonly string $numero,
        public readonly string $bairro,
        public readonly string $cidade,
        public readonly string $uf,
        public readonly float  $latitude,
        public readonly float  $longitude,
        public readonly \DateTimeImmutable $criadoEm,
    ) {}

    public function enderecoCompleto(): string
    {
        return "{$this->logradouro}, {$this->numero} — {$this->bairro}, {$this->cidade}/{$this->uf}";
    }
}
