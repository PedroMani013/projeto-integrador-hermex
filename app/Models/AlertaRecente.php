<?php

declare(strict_types=1);

namespace App\Models;

class AlertaRecente
{
    public function __construct(
        public readonly string $caixaCodigo,
        public readonly string $titulo,
        public readonly string $filialOrigem,
        public readonly string $filialDestino,
        /** critico | anomalia */
        public readonly string $nivel,
        public readonly \DateTimeImmutable $timestamp,
    ) {}
}
