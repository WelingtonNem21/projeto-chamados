<?php

namespace Database\Factories;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChamadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo'         => $this->faker->sentence(4),
            'descricao'      => $this->faker->paragraph(),
            'prioridade'     => $this->faker->randomElement(['baixa', 'media', 'alta']),
            'status'         => $this->faker->randomElement(['aberto', 'em_andamento', 'resolvido', 'fechado']),
            'responsavel_id' => null,
            'aberto_em'      => now(),
        ];
    }
}
