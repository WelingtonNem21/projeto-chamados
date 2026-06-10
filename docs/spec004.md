# spec004 — Testes

Stack: **PHPUnit (Laravel) + Vitest (Vue)**
Camada: **Backend + Frontend**

---

## 1. Backend — Laravel (PHPUnit)

### 1.1 Configuração

Laravel já vem com PHPUnit configurado. Usar banco SQLite em memória para testes:

`phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Rodar testes:
```bash
php artisan test
php artisan test --filter ChamadoTest   # rodar só um arquivo
```

---

### 1.2 `tests/Feature/ChamadoTest.php`

```php
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

        // responsavel tem 2 chamados, outro tem 0
        Chamado::factory()->count(2)->create(['responsavel_id' => $this->responsavel->id]);

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
```

---

### 1.3 `tests/Feature/UserTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_responsaveis(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
                 ->assertJsonCount(3)
                 ->assertJsonStructure([['id', 'name']]);
    }
}
```

---

### 1.4 `tests/Unit/DistribuicaoServiceTest.php`

```php
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
```

---

### 1.5 Factory para `Chamado`

Criar com:
```bash
php artisan make:factory ChamadoFactory --model=Chamado
```

`database/factories/ChamadoFactory.php`:
```php
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
```

---

## 2. Frontend — Vue (Vitest)

### 2.1 Configuração

```bash
cd frontend-chamados
npm install -D vitest @vue/test-utils jsdom @vitejs/plugin-vue
```

`vite.config.js` — adicionar bloco `test`:
```js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    globals: true,
  },
})
```

Rodar testes:
```bash
npx vitest
npx vitest run   # modo CI (sem watch)
```

---

### 2.2 `src/components/__tests__/BadgePrioridade.test.js`

```js
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import BadgePrioridade from '../BadgePrioridade.vue'

describe('BadgePrioridade', () => {
  it('exibe o texto da prioridade', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'alta' } })
    expect(wrapper.text()).toBe('alta')
  })

  it('aplica classe vermelha para alta', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'alta' } })
    expect(wrapper.classes()).toContain('bg-red-100')
  })

  it('aplica classe laranja para media', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'media' } })
    expect(wrapper.classes()).toContain('bg-orange-100')
  })

  it('aplica classe verde para baixa', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'baixa' } })
    expect(wrapper.classes()).toContain('bg-green-100')
  })
})
```

---

### 2.3 `src/components/__tests__/BadgeStatus.test.js`

```js
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import BadgeStatus from '../BadgeStatus.vue'

describe('BadgeStatus', () => {
  it('exibe label "Em Andamento" para em_andamento', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'em_andamento' } })
    expect(wrapper.text()).toBe('Em Andamento')
  })

  it('aplica classe cinza para fechado', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'fechado' } })
    expect(wrapper.classes()).toContain('bg-gray-100')
  })

  it('aplica classe azul para aberto', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'aberto' } })
    expect(wrapper.classes()).toContain('bg-blue-100')
  })
})
```

---

### 2.4 `src/components/__tests__/SelectResponsavel.test.js`

```js
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import SelectResponsavel from '../SelectResponsavel.vue'

const responsaveis = [
  { id: 1, name: 'Ana Lima' },
  { id: 2, name: 'Bruno Costa' },
]

describe('SelectResponsavel', () => {
  it('exibe o select quando automatico=false', () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: false },
    })
    expect(wrapper.find('select').exists()).toBe(true)
  })

  it('oculta o select quando automatico=true', () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: true },
    })
    expect(wrapper.find('select').exists()).toBe(false)
  })

  it('emite update:automatico ao marcar checkbox', async () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: false },
    })
    await wrapper.find('input[type=checkbox]').trigger('change')
    expect(wrapper.emitted('update:automatico')).toBeTruthy()
  })
})
```

---

## 3. Cobertura dos Testes

| Área | O que é testado |
|---|---|
| `GET /api/chamados` | listagem, filtro por status, busca por título |
| `POST /api/chamados` | criação válida, validação obrigatória, atribuição automática |
| `GET /api/chamados/:id` | retorno correto, 404 para inexistente |
| `PUT /api/chamados/:id` | atualização, bloqueio de chamado fechado |
| `DELETE /api/chamados/:id` | exclusão e verificação no banco |
| `GET /api/users` | listagem de responsáveis |
| `DistribuicaoService` | menor carga, desempate por id, chamados resolvidos não contam |
| `BadgePrioridade` | texto e classes CSS por prioridade |
| `BadgeStatus` | labels e classes CSS por status |
| `SelectResponsavel` | visibilidade do select, emissão de eventos |

---

## 4. Estrutura de Arquivos desta Spec

```
backend-chamados/
├── tests/
│   ├── Feature/
│   │   ├── ChamadoTest.php
│   │   └── UserTest.php
│   └── Unit/
│       └── DistribuicaoServiceTest.php
└── database/factories/
    └── ChamadoFactory.php

frontend-chamados/
└── src/components/__tests__/
    ├── BadgePrioridade.test.js
    ├── BadgeStatus.test.js
    └── SelectResponsavel.test.js
```

---

*Anterior: [spec003.md](spec003.md) — Frontend Vue.js SPA*
