# Sistema de Chamados — Backend

API REST para o Sistema de Controle de Chamados Internos.
Desenvolvido como desafio técnico para a **Codificar Sistemas Tecnológicos**.

---

## Tecnologias Utilizadas

| Tecnologia | Versão | Papel |
|---|---|---|
| **PHP** | 8.2+ | Linguagem base do projeto |
| **Laravel** | 11 | Framework principal — rotas, models, validações, migrations |
| **SQLite** | — | Banco de dados em arquivo, sem necessidade de instalar MySQL |
| **Laravel Sanctum** | 4.x | Instalado via `install:api` para habilitar as rotas de API |
| **PHPUnit** | 11 | Testes automatizados (Feature e Unit) |
| **Composer** | 2.x | Gerenciador de dependências PHP |

---

## Por que essas tecnologias?

- **Laravel 11** — framework maduro, usado no dia a dia da Codificar. Oferece Eloquent ORM, validação, migrations e Route Model Binding prontos para uso.
- **SQLite** — banco em arquivo (`database/database.sqlite`). Qualquer pessoa do time sobe o projeto sem instalar ou configurar um servidor de banco de dados.
- **API REST pura** — sem Inertia.js. O backend expõe apenas JSON, permitindo que o frontend Vue seja um SPA completamente independente.
- **PHP Enums** — `Prioridade` e `StatusChamado` como enums tipados, eliminando strings mágicas no código e garantindo consistência.
- **DistribuicaoService** — lógica de atribuição automática isolada em um Service (princípio SRP do SOLID), facilitando testes e futuras evoluções.

---

## Instalação e Execução

### Pré-requisitos

- PHP >= 8.2
- Composer
- Git

### Passo a passo

```bash
# 1. Clonar o repositório
git clone <url-do-repo>
cd backend-chamados

# 2. Instalar dependências PHP
composer install

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate

# 4. Criar o banco de dados SQLite
touch database/database.sqlite

# 5. Rodar migrations e popular com responsáveis
php artisan migrate --seed

# 6. Subir o servidor
php artisan serve
```

API disponível em: `http://localhost:8000`

---

## Endpoints da API

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/chamados` | Lista chamados (com filtros) |
| POST | `/api/chamados` | Cria novo chamado |
| GET | `/api/chamados/{id}` | Detalhes de um chamado |
| PUT | `/api/chamados/{id}` | Edita chamado |
| DELETE | `/api/chamados/{id}` | Exclui chamado |
| GET | `/api/users` | Lista responsáveis |

### Filtros disponíveis em `GET /api/chamados`

| Parâmetro | Exemplo | Descrição |
|---|---|---|
| `status` | `?status=aberto` | Filtra por status |
| `prioridade` | `?prioridade=alta` | Filtra por prioridade |
| `responsavel_id` | `?responsavel_id=1` | Filtra por responsável |
| `busca` | `?busca=impressora` | Busca parcial no título |

---

## Responsáveis Iniciais (Seed)

| Nome | E-mail | Senha |
|---|---|---|
| Ana Lima | ana@suporte.com | password |
| Bruno Costa | bruno@suporte.com | password |
| Carla Dias | carla@suporte.com | password |

---

## Estrutura de Pastas

```
app/
├── Enums/
│   ├── Prioridade.php         # baixa | media | alta
│   └── StatusChamado.php      # aberto | em_andamento | resolvido | fechado
├── Http/Controllers/Api/
│   ├── ChamadoController.php  # CRUD completo
│   └── UserController.php     # lista responsáveis
├── Models/
│   ├── Chamado.php
│   └── User.php
└── Services/
    └── DistribuicaoService.php # lógica de atribuição automática

database/
├── migrations/
│   └── create_chamados_table.php
└── seeders/
    └── UserSeeder.php

routes/
└── api.php
```

---

## Decisões Técnicas

**"Em aberto"** — chamados com status `aberto` ou `em_andamento` são considerados em aberto para fins da distribuição automática. Apenas `resolvido` e `fechado` são excluídos da contagem de carga.

**Desempate na auto-distribuição** — em caso de empate no número de chamados em aberto, o responsável com menor `id` é escolhido. Critério simples, previsível e documentado.

**Chamado fechado é imutável** — ao tentar editar um chamado com status `fechado`, a API retorna `422` com mensagem explicativa.

**CORS** — configurado em `config/cors.php` para aceitar requisições apenas de `http://localhost:5173` (frontend Vue em desenvolvimento).

---

## Comandos Úteis

```bash
# Recriar banco do zero
php artisan migrate:fresh --seed

# Rodar testes
php artisan test

# Listar todas as rotas
php artisan route:list --path=api
```
