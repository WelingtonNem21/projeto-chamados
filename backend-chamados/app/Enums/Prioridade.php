<?php

namespace App\Enums;

/**
 * Define os níveis de prioridade de um chamado.
 *
 * Backed enum em string: cada case armazena o valor que vai
 * para o banco de dados (coluna `prioridade` da tabela `chamados`).
 * O Laravel usa automaticamente esses values no cast do Model.
 */
enum Prioridade: string
{
    case Baixa = 'baixa'; // tarefas de baixo impacto, sem urgência
    case Media = 'media'; // padrão ao criar um chamado
    case Alta  = 'alta';  // requer atenção imediata
}
