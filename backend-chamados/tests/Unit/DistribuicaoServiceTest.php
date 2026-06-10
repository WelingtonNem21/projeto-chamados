<?php

namespace Tests\Unit;

use App\Models\Chamado;
use App\Models\User;
use App\Services\DistribuicaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistribuicaoServiceTest extends TestCase
{
    use RefreshDatabase;

    private DistribuicaoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DistribuicaoService();
    }

    public function test_atribui_ao_responsavel_com_menos_chamados(): void
    {
        $cheio  = User::factory()->create();
        $livre  = User::factory()->create();

        Chamado::factory()->count(3)->create(['responsavel_id' => $cheio->id, 'status' => 'aberto']);
        Chamado::factory()->count(1)->create(['responsavel_id' => $livre->id, 'status' => 'aberto']);

        $resultado = $this->service->atribuirResponsavel();

        $this->assertEquals($livre->id, $resultado->id);
    }

    public function test_desempate_pelo_menor_id(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // ambos sem chamados
        $resultado = $this->service->atribuirResponsavel();

        $this->assertEquals($a->id, $resultado->id);
    }

    public function test_chamados_resolvidos_nao_contam_como_abertos(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // a tem 3 chamados resolvidos (não contam)
        Chamado::factory()->count(3)->create(['responsavel_id' => $a->id, 'status' => 'resolvido']);
        // b tem 1 chamado aberto (conta)
        Chamado::factory()->count(1)->create(['responsavel_id' => $b->id, 'status' => 'aberto']);

        $resultado = $this->service->atribuirResponsavel();

        // a tem 0 em aberto, b tem 1 → a deve ser escolhido
        $this->assertEquals($a->id, $resultado->id);
    }
}
