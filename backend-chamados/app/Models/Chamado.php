<?php

namespace App\Models;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamado extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => 'aberto',
    ];

    protected $fillable = [
        'titulo',
        'descricao',
        'prioridade',
        'status',
        'responsavel_id',
        'aberto_em',
    ];

    protected $casts = [
        'prioridade' => Prioridade::class,
        'status'     => StatusChamado::class,
        'aberto_em'  => 'datetime',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
