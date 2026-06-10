# 📋 Planejamento — Sistema de Controle de Chamados Internos

**Desafio Técnico · Codificar Sistemas Tecnológicos**
Stack: Laravel + Inertia.js + Vue.js + Tailwind CSS + SQLite

---

## 1. Visão Geral do Sistema

Sistema web para abertura, acompanhamento e resolução de chamados internos de suporte. Funcionários abrem chamados; a equipe de suporte acompanha, assume e resolve. O sistema distribui automaticamente os chamados para equilibrar a carga de trabalho.

---

## 2. Stack Tecnológica e Justificativas

| Tecnologia | Papel | Justificativa |
|---|---|---|
| **Laravel 11** | Backend, rotas, regras de negócio | Framework PHP maduro, usado pela Codificar no dia a dia |
| **Inertia.js** | Ponte backend ↔ frontend | Elimina a necessidade de API REST; controller passa dados direto como props para o Vue |
| **Vue.js 3** | Interface do usuário (SPA) | Reativo, componentizável; preferência da Codificar |
| **Tailwind CSS** | Estilização | Utilitário moderno, interface organizada sem reinventar a roda |
| **SQLite** | Banco de dados | Zero configuração; qualquer membro do time sobe o projeto sem instalar MySQL |
| **Vite** | Build de assets | Padrão Laravel + Inertia; HMR em desenvolvimento |

---

## 3. Modelagem do Banco de Dados

### Tabela `users` (responsáveis)
```
id          | bigint PK auto_increment
name        | varchar(255)
email       | varchar(255) unique
password    | varchar(255)
created_at  | timestamp
updated_at  | timestamp
```

### Tabela `chamados`
```
id              | bigint PK auto_increment
titulo          | varchar(255)
descricao       | text
prioridade      | enum('baixa', 'media', 'alta')
status          | enum('aberto', 'em_andamento', 'resolvido', 'fechado')
responsavel_id  | bigint FK → users.id (nullable)
aberto_em       | timestamp  (data e hora de abertura)
created_at      | timestamp
updated_at      | timestamp
```

> **Decisão arquitetural — "em aberto":** Chamados com status `aberto` ou `em_andamento` são considerados **em aberto** para fins da distribuição automática. Apenas `resolvido` e `fechado` são considerados concluídos.

---

## 4. Regras de Negócio

### 4.1 Distribuição Automática
- Ao criar um chamado, o usuário pode marcar **"Atribuir automaticamente"**
- O sistema busca o responsável com **menor número de chamados em aberto** (`aberto` + `em_andamento`)
- Em caso de empate, o responsável com `id` menor é escolhido (critério desempate simples e documentado)
- O usuário pode também escolher manualmente um responsável pelo select

### 4.2 Status dos Chamados
```
aberto → em_andamento → resolvido → fechado
```
- Qualquer chamado pode ser reaberto (voltar para `aberto`) enquanto não estiver `fechado`
- Chamado `fechado` é imutável

### 4.3 Prioridades
- `alta` → aparece destacada em vermelho na listagem
- `media` → laranja
- `baixa` → verde

---

## 5. Estrutura de Telas (Pages Vue)

```
resources/js/Pages/
├── Chamados/
│   ├── Index.vue       # Listagem com filtros e busca
│   ├── Create.vue      # Formulário de novo chamado
│   ├── Edit.vue        # Edição de chamado existente
│   └── Show.vue        # Visualização detalhada
└── Dashboard.vue       # Resumo de chamados por status
```

### Tela de Listagem (`Index.vue`)
- Tabela com: título, prioridade (badge colorido), status, responsável, data de abertura
- Filtros: por status, prioridade, responsável
- Busca por título
- Ordenação por data e prioridade
- Botão "Novo Chamado"

### Tela de Criação/Edição (`Create.vue` / `Edit.vue`)
- Campo: Título (obrigatório)
- Campo: Descrição (textarea)
- Select: Prioridade
- Select: Responsável + checkbox "Atribuir automaticamente"
- Status só aparece na edição
- Data de abertura preenchida automaticamente

---

## 6. Estrutura de Rotas Laravel

```php
// routes/web.php
Route::get('/',            [ChamadoController::class, 'index'])->name('chamados.index');
Route::get('/chamados/create', [ChamadoController::class, 'create'])->name('chamados.create');
Route::post('/chamados',       [ChamadoController::class, 'store'])->name('chamados.store');
Route::get('/chamados/{chamado}',      [ChamadoController::class, 'show'])->name('chamados.show');
Route::get('/chamados/{chamado}/edit', [ChamadoController::class, 'edit'])->name('chamados.edit');
Route::put('/chamados/{chamado}',      [ChamadoController::class, 'update'])->name('chamados.update');
Route::delete('/chamados/{chamado}',   [ChamadoController::class, 'destroy'])->name('chamados.destroy');
```

---

## 7. Estrutura de Arquivos do Projeto

```
app/
├── Http/Controllers/
│   └── ChamadoController.php
├── Models/
│   ├── Chamado.php
│   └── User.php
├── Services/
│   └── DistribuicaoService.php   # lógica de auto-distribuição (SOLID: SRP)
└── Enums/
    ├── Prioridade.php
    └── StatusChamado.php

database/
├── migrations/
│   ├── create_users_table.php
│   └── create_chamados_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php             # cria os 3+ responsáveis iniciais

resources/js/
├── Pages/
│   └── Chamados/ ...
├── Components/
│   ├── BadgePrioridade.vue
│   ├── BadgeStatus.vue
│   ├── SelectResponsavel.vue
│   └── FiltrosChamados.vue
└── Layouts/
    └── AppLayout.vue
```

---

## 8. Componentes Vue Planejados

| Componente | Responsabilidade |
|---|---|
| `BadgePrioridade.vue` | Exibe badge colorido com a prioridade |
| `BadgeStatus.vue` | Exibe badge com o status atual |
| `SelectResponsavel.vue` | Select + checkbox de atribuição automática |
| `FiltrosChamados.vue` | Filtros de status, prioridade e busca |
| `AppLayout.vue` | Layout base com nav e container |

---

## 9. Cronograma (8 horas estimadas)

| Etapa | Descrição | Tempo |
|---|---|---|
| **1** | Criar projeto, configurar Laravel + Inertia + Vue + Tailwind | 1h |
| **2** | Migrations, Models, Seeders (3 responsáveis) | 30min |
| **3** | ChamadoController (CRUD completo) | 1h |
| **4** | DistribuicaoService (auto-atribuição) | 30min |
| **5** | Pages Vue: Index + Create + Edit + Show | 2h |
| **6** | Componentes reutilizáveis (badges, filtros) | 1h |
| **7** | Testes básicos (Feature test do CRUD) | 1h |
| **8** | README, revisão final, push GitHub | 30min |

---

## 10. Instruções — Criando o Projeto do Zero

### Pré-requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18 + npm
- Git

---

### Passo 1 — Criar o projeto Laravel

```bash
composer create-project laravel/laravel sistema-chamados
cd sistema-chamados
```

---

### Passo 2 — Instalar Inertia.js (lado servidor)

```bash
composer require inertiajs/inertia-laravel
```

Publicar o middleware do Inertia:

```bash
php artisan inertia:middleware
```

Registrar o middleware em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

Criar o template raiz em `resources/views/app.blade.php`:

```html
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistema de Chamados</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead
  </head>
  <body>
    @inertia
  </body>
</html>
```

---

### Passo 3 — Instalar Inertia.js + Vue.js + Vite (lado cliente)

```bash
npm install @inertiajs/vue3 vue @vitejs/plugin-vue
```

Configurar `vite.config.js`:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: { '@': '/resources/js' },
  },
})
```

Configurar `resources/js/app.js`:

```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

createInertiaApp({
  resolve: name =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
```

---

### Passo 4 — Instalar Tailwind CSS

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Configurar `tailwind.config.js`:

```js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: { extend: {} },
  plugins: [],
}
```

Em `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

---

### Passo 5 — Configurar banco de dados (SQLite)

No arquivo `.env`, altere:

```env
DB_CONNECTION=sqlite
# remova ou comente as outras linhas DB_*
```

Crie o arquivo do banco:

```bash
touch database/database.sqlite
```

---

### Passo 6 — Criar Migrations

```bash
php artisan make:migration create_chamados_table
```

Conteúdo da migration:

```php
Schema::create('chamados', function (Blueprint $table) {
    $table->id();
    $table->string('titulo');
    $table->text('descricao')->nullable();
    $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
    $table->enum('status', ['aberto', 'em_andamento', 'resolvido', 'fechado'])->default('aberto');
    $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('aberto_em')->useCurrent();
    $table->timestamps();
});
```

---

### Passo 7 — Criar Seeders com responsáveis

```bash
php artisan make:seeder UserSeeder
```

```php
// database/seeders/UserSeeder.php
User::factory()->createMany([
    ['name' => 'Ana Lima',    'email' => 'ana@suporte.com',    'password' => bcrypt('password')],
    ['name' => 'Bruno Costa', 'email' => 'bruno@suporte.com',  'password' => bcrypt('password')],
    ['name' => 'Carla Dias',  'email' => 'carla@suporte.com',  'password' => bcrypt('password')],
]);
```

Chamar no `DatabaseSeeder.php`:

```php
$this->call(UserSeeder::class);
```

Rodar migrations e seeders:

```bash
php artisan migrate --seed
```

---

### Passo 8 — Criar Model e Controller

```bash
php artisan make:model Chamado
php artisan make:controller ChamadoController --resource
php artisan make:service DistribuicaoService   # ou criar manualmente em app/Services/
```

---

### Passo 9 — Subir o servidor de desenvolvimento

Em dois terminais simultâneos:

```bash
# Terminal 1 — servidor PHP
php artisan serve

# Terminal 2 — assets (Vite com HMR)
npm run dev
```

Acesse: `http://localhost:8000`

---

### Passo 10 — Build para produção (opcional)

```bash
npm run build
php artisan optimize
```

---

## 11. Comandos de Referência Rápida

```bash
# Recriar banco do zero
php artisan migrate:fresh --seed

# Criar novo componente de teste
php artisan make:test ChamadoTest --feature

# Rodar testes
php artisan test

# Listar todas as rotas
php artisan route:list
```

---

## 12. README sugerido para o repositório

> Copie o bloco abaixo para o `README.md` do seu projeto GitHub.

```markdown
# Sistema de Controle de Chamados Internos

Sistema web para abertura e acompanhamento de chamados de suporte interno.

## Stack

- Laravel 11 · Inertia.js · Vue 3 · Tailwind CSS · SQLite

## Instalação

# 1. Clonar e instalar dependências
git clone <url-do-repo>
cd sistema-chamados
composer install
npm install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Banco de dados
touch database/database.sqlite
php artisan migrate --seed

# 4. Subir o projeto
php artisan serve
npm run dev

Acesse: http://localhost:8000

## Decisões técnicas

- **Inertia.js**: elimina a necessidade de API REST separada. O controller
  Laravel entrega dados diretamente como props para os componentes Vue.
- **SQLite**: banco em arquivo, sem necessidade de instalar MySQL localmente.
  Ideal para rodar o projeto de forma simples.
- **"Em aberto"**: chamados com status `aberto` ou `em_andamento`.
  Apenas `resolvido` e `fechado` são excluídos da contagem de carga.
- **Desempate na auto-distribuição**: em caso de empate no número de chamados,
  o responsável com menor `id` é escolhido. Critério simples, previsível e documentado.
- **DistribuicaoService**: lógica de atribuição automática isolada em um Service
  (princípio SRP do SOLID), facilitando testes e futuras evoluções.
```

---

*Planejamento elaborado com base no desafio técnico da Codificar Sistemas Tecnológicos.*
