<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Todos os chamados atribuídos a este responsável.
     * FK explícita porque o campo chama `responsavel_id`, não `user_id`.
     */
    public function chamados(): HasMany
    {
        return $this->hasMany(Chamado::class, 'responsavel_id');
    }

    /**
     * Apenas chamados ainda não concluídos deste responsável.
     * Usado pelo DistribuicaoService para calcular a carga de trabalho atual.
     */
    public function chamadosEmAberto(): HasMany
    {
        return $this->hasMany(Chamado::class, 'responsavel_id')
                    ->whereIn('status', ['aberto', 'em_andamento']);
    }
}
