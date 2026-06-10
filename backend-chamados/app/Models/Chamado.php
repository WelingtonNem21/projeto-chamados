<?php

namespace App\Models;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model que representa um chamado de suporte interno.
 *
 * Cada chamado pertence a um responsável (User) e passa por um
 * ciclo de status definido em StatusChamado.
 */
class Chamado extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos via mass assignment (create/update).
     * Campos omitidos aqui (ex: id, created_at) são protegidos automaticamente.
     */
    protected $fillable = [
        'titulo',
        'descricao',
        'prioridade',
        'status',
        'responsavel_id',
        'aberto_em',
    ];

    /**
     * Conversões automáticas de tipo ao ler/escrever os atributos.
     *
     * - prioridade e status são convertidos para seus respectivos Enums,
     *   permitindo usar $chamado->status === StatusChamado::Fechado em vez de string.
     * - aberto_em é convertido para Carbon (objeto de data/hora do Laravel).
     */
    protected $casts = [
        'prioridade' => Prioridade::class,
        'status'     => StatusChamado::class,
        'aberto_em'  => 'datetime',
    ];

    /**
     * Relacionamento: um chamado pertence a um responsável (User).
     * A FK é `responsavel_id` (não o padrão `user_id`), então informamos explicitamente.
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
