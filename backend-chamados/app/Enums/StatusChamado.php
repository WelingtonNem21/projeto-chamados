<?php

namespace App\Enums;

/**
 * Representa o ciclo de vida de um chamado.
 *
 * Fluxo esperado:
 *   aberto → em_andamento → resolvido → fechado
 *
 * Chamados `fechados` são imutáveis — não podem ser editados.
 * Chamados `aberto` ou `em_andamento` ainda não foram concluídos
 * e entram na contagem de carga do DistribuicaoService.
 */
enum StatusChamado: string
{
    case Aberto      = 'aberto';        // recém-criado, aguardando atendimento
    case EmAndamento = 'em_andamento';  // responsável já assumiu
    case Resolvido   = 'resolvido';     // solução aplicada, pode ser reaberto
    case Fechado     = 'fechado';       // encerrado definitivamente, imutável

    /**
     * Retorna true para os status que ainda não foram concluídos.
     * Usado pelo DistribuicaoService para contar a carga de cada responsável.
     */
    public function estaEmAberto(): bool
    {
        return match($this) {
            self::Aberto, self::EmAndamento => true,
            default => false,
        };
    }
}
