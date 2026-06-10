# spec001 — Banco de Dados, Migrations, Models, Enums e Seeders

Stack: **Laravel 11 + SQLite**
Camada: **Backend**

---

## 1. Configuração do Banco de Dados

**Driver:** SQLite (arquivo em `database/database.sqlite`)

`.env`:
```env
DB_CONNECTION=sqlite
```

Criar o arquivo do banco antes de rodar migrations:
```bash
touch database/database.sqlite
```

---

## 2. Migrations

### 2.1 Tabela `users` (já gerada pelo Laravel)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```

### 2.2 Tabela `chamados`

Arquivo: `database/migrations/xxxx_xx_xx_create_chamados_table.php`

```php
Schema::create('chamados', function (Blueprint $table) {
    $table->id();
    $table->string('titulo');
    $table->text('descricao')->nullable();
    $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
    $table->enum('status', ['aberto', 'em_andamento', 'resolvido', 'fechado'])->default('aberto');
    $table->foreignId('responsavel_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamp('aberto_em')->useCurrent();
    $table->timestamps();
});
```

Criar e executar:
```bash
php artisan make:migration create_chamados_table
php artisan migrate
```

---

## 3. Enums PHP

Pasta: `app/Enums/`

### 3.1 `Prioridade.php`

```php
<?php

namespace App\Enums;

enum Prioridade: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta  = 'alta';
}
```

### 3.2 `StatusChamado.php`

```php
<?php

namespace App\Enums;

enum StatusChamado: string
{
    case Aberto       = 'aberto';
    case EmAndamento  = 'em_andamento';
    case Resolvido    = 'resolvido';
    case Fechado      = 'fechado';

    public function estaEmAberto(): bool
    {
        return match($this) {
            self::Aberto, self::EmAndamento => true,
            default => false,
        };
    }
}
```

> **Regra "em aberto":** Chamados com status `aberto` ou `em_andamento` são considerados em aberto para fins da distribuição automática. Apenas `resolvido` e `fechado` são considerados concluídos.

---

## 4. Models

### 4.1 `app/Models/Chamado.php`

```php
<?php

namespace App\Models;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamado extends Model
{
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
```

### 4.2 `app/Models/User.php`

Adicionar ao model padrão do Laravel:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function chamados(): HasMany
{
    return $this->hasMany(Chamado::class, 'responsavel_id');
}

public function chamadosEmAberto(): HasMany
{
    return $this->hasMany(Chamado::class, 'responsavel_id')
                ->whereIn('status', ['aberto', 'em_andamento']);
}
```

---

## 5. Seeders

### 5.1 `database/seeders/UserSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->createMany([
            ['name' => 'Ana Lima',    'email' => 'ana@suporte.com',   'password' => bcrypt('password')],
            ['name' => 'Bruno Costa', 'email' => 'bruno@suporte.com', 'password' => bcrypt('password')],
            ['name' => 'Carla Dias',  'email' => 'carla@suporte.com', 'password' => bcrypt('password')],
        ]);
    }
}
```

### 5.2 `database/seeders/DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call(UserSeeder::class);
}
```

Executar:
```bash
php artisan migrate --seed

# Recriar do zero:
php artisan migrate:fresh --seed
```

---

## 6. Estrutura de Arquivos desta Spec

```
app/
├── Enums/
│   ├── Prioridade.php
│   └── StatusChamado.php
└── Models/
    ├── Chamado.php
    └── User.php

database/
├── migrations/
│   ├── xxxx_create_users_table.php    (padrão Laravel)
│   └── xxxx_create_chamados_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php
```

---

*Próximo: [spec002.md](spec002.md) — API Routes, Controllers e Services*
