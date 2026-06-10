<?php

namespace App\Services;

use App\Models\User;

/**
 * Responsável pela lógica de atribuição automática de chamados.
 *
 * Isolada em um Service para seguir o SRP (Single Responsibility Principle):
 * o controller apenas delega a decisão, sem conhecer os critérios de escolha.
 */
class DistribuicaoService
{
    /**
     * Retorna o responsável com menos chamados em aberto no momento.
     *
     * "Em aberto" = status `aberto` ou `em_andamento`.
     * Chamados `resolvido` ou `fechado` não entram na contagem de carga.
     *
     * Desempate: em caso de igualdade, o responsável com menor `id` é escolhido.
     * Critério simples, previsível e documentado — sem aleatoriedade.
     *
     * Retorna null se não houver nenhum responsável cadastrado.
     */
    public function atribuirResponsavel(): ?User
    {
        return User::withCount([
                // cria a coluna virtual `chamados_abertos_count` para ordenação
                'chamados as chamados_abertos_count' => fn($q) =>
                    $q->whereIn('status', ['aberto', 'em_andamento']),
            ])
            ->orderBy('chamados_abertos_count') // menor carga primeiro
            ->orderBy('id')                     // desempate pelo mais antigo
            ->first();
    }
}
