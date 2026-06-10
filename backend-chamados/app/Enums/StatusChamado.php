<?php

namespace App\Enums;

enum StatusChamado: string
{
    case Aberto      = 'aberto';
    case EmAndamento = 'em_andamento';
    case Resolvido   = 'resolvido';
    case Fechado     = 'fechado';

    public function estaEmAberto(): bool
    {
        return match($this) {
            self::Aberto, self::EmAndamento => true,
            default => false,
        };
    }
}
