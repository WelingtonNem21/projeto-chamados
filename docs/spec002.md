# spec002 — API Routes, Controllers, Services e Contratos JSON

Stack: **Laravel 11 REST API**
Camada: **Backend**

---

## 1. Configuração CORS

Arquivo: `config/cors.php`

```php
'allowed_origins' => ['http://localhost:5173'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

O middleware `HandleCors` já está registrado por padrão no Laravel 11. Não é necessário adicionar manualmente.

---

## 2. Rotas da API

Arquivo: `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\ChamadoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('chamados', ChamadoController::class);
Route::get('users', [UserController::class, 'index']);
```

### Tabela de endpoints

| Método | URI | Ação | Descrição |
|---|---|---|---|
| GET | `/api/chamados` | `index` | Listagem com filtros e busca |
| POST | `/api/chamados` | `store` | Criar novo chamado |
| GET | `/api/chamados/{chamado}` | `show` | Detalhes de um chamado |
| PUT/PATCH | `/api/chamados/{chamado}` | `update` | Editar chamado |
| DELETE | `/api/chamados/{chamado}` | `destroy` | Excluir chamado |
| GET | `/api/users` | `index` | Lista de responsáveis (para selects) |

Verificar rotas registradas:
```bash
php artisan route:list --path=api
```

---

## 3. ChamadoController

Arquivo: `app/Http/Controllers/Api/ChamadoController.php`

```php
php artisan make:controller Api/ChamadoController --api
```

### 3.1 `index()` — Listagem com filtros

Query params aceitos:
- `status` — filtra por status exato (ex: `?status=aberto`)
- `prioridade` — filtra por prioridade (ex: `?prioridade=alta`)
- `responsavel_id` — filtra por responsável (ex: `?responsavel_id=2`)
- `busca` — busca parcial no título (ex: `?busca=impressora`)

```php
public function index(Request $request): JsonResponse
{
    $chamados = Chamado::with('responsavel')
        ->when($request->status,        fn($q) => $q->where('status', $request->status))
        ->when($request->prioridade,    fn($q) => $q->where('prioridade', $request->prioridade))
        ->when($request->responsavel_id, fn($q) => $q->where('responsavel_id', $request->responsavel_id))
        ->when($request->busca,         fn($q) => $q->where('titulo', 'like', "%{$request->busca}%"))
        ->orderByRaw("CASE prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END")
        ->orderBy('aberto_em', 'desc')
        ->paginate(15);

    return response()->json($chamados);
}
```

### 3.2 `store()` — Criar chamado

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'titulo'                 => 'required|string|max:255',
        'descricao'              => 'nullable|string',
        'prioridade'             => 'required|in:baixa,media,alta',
        'responsavel_id'         => 'nullable|exists:users,id',
        'atribuir_automaticamente' => 'boolean',
    ]);

    if ($request->boolean('atribuir_automaticamente')) {
        $responsavel = app(DistribuicaoService::class)->atribuirResponsavel();
        $data['responsavel_id'] = $responsavel?->id;
    }

    $chamado = Chamado::create($data);

    return response()->json($chamado->load('responsavel'), 201);
}
```

### 3.3 `show()` — Detalhes

```php
public function show(Chamado $chamado): JsonResponse
{
    return response()->json($chamado->load('responsavel'));
}
```

### 3.4 `update()` — Editar

```php
public function update(Request $request, Chamado $chamado): JsonResponse
{
    if ($chamado->status === StatusChamado::Fechado) {
        return response()->json(['message' => 'Chamado fechado não pode ser editado.'], 422);
    }

    $data = $request->validate([
        'titulo'         => 'sometimes|required|string|max:255',
        'descricao'      => 'nullable|string',
        'prioridade'     => 'sometimes|required|in:baixa,media,alta',
        'status'         => 'sometimes|required|in:aberto,em_andamento,resolvido,fechado',
        'responsavel_id' => 'nullable|exists:users,id',
    ]);

    $chamado->update($data);

    return response()->json($chamado->load('responsavel'));
}
```

### 3.5 `destroy()` — Excluir

```php
public function destroy(Chamado $chamado): JsonResponse
{
    $chamado->delete();
    return response()->json(null, 204);
}
```

---

## 4. UserController

Arquivo: `app/Http/Controllers/Api/UserController.php`

```php
public function index(): JsonResponse
{
    $users = User::select('id', 'name')->orderBy('name')->get();
    return response()->json($users);
}
```

Resposta:
```json
[
  { "id": 1, "name": "Ana Lima" },
  { "id": 2, "name": "Bruno Costa" },
  { "id": 3, "name": "Carla Dias" }
]
```

---

## 5. DistribuicaoService

Arquivo: `app/Services/DistribuicaoService.php`

```php
<?php

namespace App\Services;

use App\Models\User;

class DistribuicaoService
{
    public function atribuirResponsavel(): ?User
    {
        return User::withCount([
                'chamados as chamados_abertos_count' => fn($q) =>
                    $q->whereIn('status', ['aberto', 'em_andamento'])
            ])
            ->orderBy('chamados_abertos_count')
            ->orderBy('id')
            ->first();
    }
}
```

**Regra de desempate:** Em caso de empate no número de chamados em aberto, o responsável com menor `id` é escolhido. Critério simples, previsível e documentado.

---

## 6. Contratos JSON

### Objeto `chamado` completo (usado em show, store, update)

```json
{
  "id": 1,
  "titulo": "Impressora não funciona",
  "descricao": "A impressora do setor financeiro não está imprimindo.",
  "prioridade": "alta",
  "status": "aberto",
  "responsavel_id": 2,
  "aberto_em": "2026-06-10T14:30:00.000000Z",
  "created_at": "2026-06-10T14:30:00.000000Z",
  "updated_at": "2026-06-10T14:30:00.000000Z",
  "responsavel": {
    "id": 2,
    "name": "Bruno Costa"
  }
}
```

### Resposta `index` (paginada)

```json
{
  "data": [ ...array de chamados... ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 15,
  "total": 42
}
```

### Erros de validação (`422`)

```json
{
  "message": "The titulo field is required.",
  "errors": {
    "titulo": ["O campo título é obrigatório."]
  }
}
```

### Codes HTTP resumidos

| Situação | HTTP |
|---|---|
| Listagem / detalhes | 200 |
| Criado com sucesso | 201 |
| Atualizado com sucesso | 200 |
| Excluído com sucesso | 204 |
| Erro de validação | 422 |
| Não encontrado | 404 |

---

## 7. Estrutura de Arquivos desta Spec

```
app/
├── Http/Controllers/Api/
│   ├── ChamadoController.php
│   └── UserController.php
└── Services/
    └── DistribuicaoService.php

routes/
└── api.php

config/
└── cors.php
```

---

*Anterior: [spec001.md](spec001.md) — DB e Models*
*Próximo: [spec003.md](spec003.md) — Frontend Vue.js SPA*
