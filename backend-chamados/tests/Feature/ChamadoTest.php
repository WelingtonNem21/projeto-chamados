<?php

namespace Tests\Feature;

use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChamadoTest extends TestCase
{
    use RefreshDatabase;

    private User $responsavel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responsavel = User::factory()->create();
    }

    // --- index ---

    public function test_lista_chamados(): void
    {
        Chamado::factory()->count(3)->create(['responsavel_id' => $this->responsavel->id]);

        $response = $this->getJson('/api/chamados');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_filtra_chamados_por_status(): void
    {
        Chamado::factory()->create(['status' => 'aberto',    'responsavel_id' => $this->responsavel->id]);
        Chamado::factory()->create(['status' => 'resolvido', 'responsavel_id' => $this->responsavel->id]);

        $response = $this->getJson('/api/chamados?status=aberto');

        $response->assertOk()
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.status', 'aberto');
    }

    public function test_busca_chamados_por_titulo(): void
    {
        Chamado::factory()->create(['titulo' => 'Impressora quebrada', 'responsavel_id' => $this->responsavel->id]);
        Chamado::factory()->create(['titulo' => 'Computador lento',    'responsavel_id' => $this->responsavel->id]);

        $response = $this->getJson('/api/chamados?busca=impressora');

        $response->assertOk()
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.titulo', 'Impressora quebrada');
    }

    // --- store ---

    public function test_cria_chamado_com_dados_validos(): void
    {
        $payload = [
            'titulo'         => 'Teclado sem funcionar',
            'descricao'      => 'Teclas A e S não respondem.',
            'prioridade'     => 'alta',
            'responsavel_id' => $this->responsavel->id,
        ];

        $response = $this->postJson('/api/chamados', $payload);

        $response->assertCreated()
                 ->assertJsonPath('titulo', 'Teclado sem funcionar')
                 ->assertJsonPath('status', 'aberto');

        $this->assertDatabaseHas('chamados', ['titulo' => 'Teclado sem funcionar']);
    }

    public function test_nao_cria_chamado_sem_titulo(): void
    {
        $response = $this->postJson('/api/chamados', [
            'prioridade' => 'media',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['titulo']);
    }

    public function test_cria_chamado_com_atribuicao_automatica(): void
    {
        $outro = User::factory()->create();

        // responsavel tem 2 chamados abertos, outro tem 0
        Chamado::factory()->count(2)->create(['responsavel_id' => $this->responsavel->id, 'status' => 'aberto']);

        $response = $this->postJson('/api/chamados', [
            'titulo'                   => 'Cadeira quebrada',
            'prioridade'               => 'baixa',
            'atribuir_automaticamente' => true,
        ]);

        $response->assertCreated()
                 ->assertJsonPath('responsavel_id', $outro->id);
    }

    // --- show ---

    public function test_exibe_chamado_existente(): void
    {
        $chamado = Chamado::factory()->create(['responsavel_id' => $this->responsavel->id]);

        $response = $this->getJson("/api/chamados/{$chamado->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $chamado->id)
                 ->assertJsonStructure(['id', 'titulo', 'prioridade', 'status', 'responsavel']);
    }

    public function test_retorna_404_para_chamado_inexistente(): void
    {
        $this->getJson('/api/chamados/9999')->assertNotFound();
    }

    // --- update ---

    public function test_atualiza_chamado(): void
    {
        $chamado = Chamado::factory()->create(['responsavel_id' => $this->responsavel->id]);

        $response = $this->putJson("/api/chamados/{$chamado->id}", [
            'status' => 'em_andamento',
        ]);

        $response->assertOk()
                 ->assertJsonPath('status', 'em_andamento');
    }

    public function test_nao_permite_editar_chamado_fechado(): void
    {
        $chamado = Chamado::factory()->create([
            'status'         => 'fechado',
            'responsavel_id' => $this->responsavel->id,
        ]);

        $response = $this->putJson("/api/chamados/{$chamado->id}", [
            'titulo' => 'Novo título',
        ]);

        $response->assertUnprocessable();
    }

    // --- destroy ---

    public function test_exclui_chamado(): void
    {
        $chamado = Chamado::factory()->create(['responsavel_id' => $this->responsavel->id]);

        $this->deleteJson("/api/chamados/{$chamado->id}")->assertNoContent();

        $this->assertDatabaseMissing('chamados', ['id' => $chamado->id]);
    }
}
