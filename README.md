# Sistema de Controle de Chamados Internos

Desafio técnico desenvolvido para a **Codificar Sistemas Tecnológicos**.
Sistema web para abertura, acompanhamento e resolução de chamados internos de suporte.

---

## Estrutura do Projeto

```
projetochamados/
├── backend-chamados/   # API REST em Laravel 11
└── frontend-chamados/  # SPA em Vue 3 (a implementar)
```

---

## Backend

### Stack

| Tecnologia | Papel |
|---|---|
| **PHP 8.2+** | Linguagem base |
| **Laravel 11** | Framework — rotas, models, validações, migrations |
| **SQLite** | Banco de dados em arquivo, sem configuração extra |
| **PHPUnit** | Testes automatizados (Feature e Unit) |

### Principais funcionalidades

- CRUD completo de chamados (título, descrição, prioridade, status, responsável)
- Distribuição automática — atribui o chamado ao responsável com **menos chamados em aberto**
- Filtros na listagem por status, prioridade, responsável e busca por título
- Chamado `fechado` é imutável — bloqueado no `update`

### Como rodar

```bash
cd backend-chamados

composer install
cp .env.example .env
php artisan key:generate

# Windows — criar o banco SQLite
echo $null > database/database.sqlite

php artisan migrate --seed
php artisan serve
```

API disponível em `http://localhost:8000`

### Endpoints

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/chamados` | Lista chamados |
| POST | `/api/chamados` | Cria chamado |
| GET | `/api/chamados/{id}` | Detalhes |
| PUT | `/api/chamados/{id}` | Edita |
| DELETE | `/api/chamados/{id}` | Exclui |
| GET | `/api/users` | Lista responsáveis |

Documentação completa: [`backend-chamados/README.md`](backend-chamados/README.md)

---

## Decisões Técnicas

**API REST separada do frontend** — o backend expõe apenas JSON, sem Inertia.js. Permite que o frontend Vue seja um SPA independente, cada parte evoluindo sem acoplamento.

**SQLite** — banco em arquivo, sem necessidade de instalar MySQL. Qualquer membro do time sobe o projeto com um único comando.

**DistribuicaoService** — lógica de auto-atribuição isolada em um Service (SRP do SOLID). O controller delega a decisão sem conhecer os critérios.

**"Em aberto"** — status `aberto` e `em_andamento` contam como carga de trabalho. `resolvido` e `fechado` não entram na distribuição automática.
