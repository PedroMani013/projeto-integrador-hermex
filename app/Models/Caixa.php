<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Entidade de domíniom sem acesso ao banco
 * Estados possíveis: criada | lacrada | em_transito | entregue | violada
 */
class Caixa
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $codigo,
        public readonly string  $tagNfc,
        public readonly string  $estado,
        public readonly array   $notasFiscais,
        public readonly int     $totalItens,
        public readonly float   $pesoBaseline,
        public readonly float   $pesoAtual,
        public readonly ?float  $toleranciaEfetiva,
        public readonly ?\DateTimeImmutable $anomaliaPesoIniciadaEm,
        public readonly ?\DateTimeImmutable $lacradaEm,
        public readonly string  $filialOrigemCodigo,
        public readonly string  $filialDestinoCodigo,
        public readonly string  $filialOrigemNome,
        public readonly string  $filialDestinoNome,
        public readonly string  $transportadora,
        public readonly \DateTimeImmutable $previsaoChegada,
        public readonly ?Evento $ultimoEvento,
        public readonly \DateTimeImmutable $criadoEm,
    ) {}

    public function deltaKg(): float
    {
        return round(($this->pesoAtual - $this->pesoBaseline) / 1000, 2);
    }

    public function temAlerta(): bool
    {
        return $this->estado === 'violada';
    }

    public function badgeClasse(): string
    {
        return match($this->estado) {
            'em_transito' => 'badge-transito',
            'entregue'    => 'badge-entregue',
            'violada'     => 'badge-alerta',
            'lacrada'     => 'badge-lacrada',
            default       => 'badge-secondary',
        };
    }

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'em_transito' => 'Em trânsito',
            'entregue'    => 'Entregue',
            'violada'     => 'Com alerta',
            'lacrada'     => 'Lacrada',
            'criada'      => 'Criada',
            default       => $this->estado,
        };
    }
}
