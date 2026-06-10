<?php

namespace App\Services;

use App\Models\User;

class DistribuicaoService
{
    public function atribuirResponsavel(): ?User
    {
        return User::withCount([
                'chamados as chamados_abertos_count' => fn($q) =>
                    $q->whereIn('status', ['aberto', 'em_andamento']),
            ])
            ->orderBy('chamados_abertos_count')
            ->orderBy('id')
            ->first();
    }
}
