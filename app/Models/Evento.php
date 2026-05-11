<?php

declare(strict_types=1);

namespace App\Models;

/** ultimo evento da coleção de eventos */
class Evento
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $caixaId,
        /** peso | tampa | nfc | transicao */
        public readonly string  $tipo,
        public readonly mixed   $valor,
        public readonly bool    $emMovimento,
        public readonly bool    $pesoAnomalo,
        public readonly bool    $aberturaIndevida,
        public readonly \DateTimeImmutable $timestamp,
    ) {}
}
