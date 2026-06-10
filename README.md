# Sistema de Controle de Chamados Internos

Desafio técnico desenvolvido para a **Codificar Sistemas Tecnológicos**.  
Sistema web para abertura, acompanhamento e resolução de chamados internos de suporte.

---

## Estrutura do Projeto

```
projetochamados/
├── backend-chamados/    # API REST em Laravel 11
└── frontend-chamados/   # SPA em Vue 3 + Vite + Tailwind CSS
```

---

## Stack Tecnológica

### Backend

| Tecnologia | Papel |
|---|---|
| **PHP 8.2+** | Linguagem base |
| **Laravel 11** | Framework — rotas, models, validações, migrations |
| **SQLite** | Banco de dados em arquivo, sem configuração extra |
| **PHPUnit** | Testes automatizados (Feature e Unit) |

### Frontend

| Tecnologia | Papel |
|---|---|
| **Vue 3** | Framework reativo (Composition API) |
| **Vite 8** | Build tool e dev server |
| **Vue Router 4** | Roteamento SPA |
| **Axios** | Requisições HTTP para a API |
| **Tailwind CSS 4** | Estilização utilitária |

---

## Como Rodar o Projeto

> Pré-requisitos: **PHP 8.2+**, **Composer**, **Node.js 18+** e **npm**.

### 1. Backend (Laravel)

```bash
cd backend-chamados

# Instalar dependências PHP
composer install

# Copiar e configurar o ambiente
cp .env.example .env
php artisan key:generate

# Criar o banco de dados SQLite (Windows PowerShell)
$null > database/database.sqlite

# Criar tabelas e popular com dados iniciais
php artisan migrate --seed

# Subir o servidor
php artisan serve
```

API disponível em `http://localhost:8000`

---

### 2. Frontend (Vue 3)

Em outro terminal:

```bash
cd frontend-chamados

# Instalar dependências Node
npm install

# Subir o servidor de desenvolvimento
npm run dev
```

Aplicação disponível em `http://localhost:5173`

> O frontend aponta para `http://localhost:8000` por padrão. Certifique-se de que o backend está rodando antes de abrir o frontend.

---

## Funcionalidades

- **CRUD completo de chamados** — criar, visualizar, editar e excluir
- **Campos obrigatórios:** título, descrição, prioridade (baixa / média / alta), status (aberto / em andamento / resolvido / fechado), responsável e data de abertura
- **Distribuição automática** — atribui o chamado ao responsável com menos chamados em aberto no momento
- **Atribuição manual** — o usuário pode selecionar o responsável diretamente
- **Listagem com filtros** — por status, prioridade, responsável e busca por título
- **Chamado `fechado` é imutável** — bloqueado no `update`

---

## Endpoints da API

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/chamados` | Lista chamados (aceita filtros via query string) |
| POST | `/api/chamados` | Cria chamado |
| GET | `/api/chamados/{id}` | Detalhes de um chamado |
| PUT | `/api/chamados/{id}` | Edita chamado |
| DELETE | `/api/chamados/{id}` | Exclui chamado |
| GET | `/api/users` | Lista responsáveis disponíveis |

---

## Decisões Técnicas

**API REST separada do frontend** — o backend expõe apenas JSON, sem Inertia.js. Permite que o frontend Vue seja um SPA totalmente independente; cada camada evolui sem acoplamento.

**SQLite** — banco em arquivo, sem necessidade de instalar MySQL ou PostgreSQL. Qualquer pessoa do time sobe o projeto com um único comando, ideal para avaliação local.

**DistribuicaoService** — lógica de auto-atribuição isolada em um Service dedicado (SRP do SOLID). O controller delega a decisão sem precisar conhecer os critérios de distribuição.

**"Em aberto" = `aberto` + `em_andamento`** — esses dois status representam carga de trabalho real. `resolvido` e `fechado` não entram no cálculo de distribuição automática, pois o chamado já foi concluído ou encerrado.

**Tailwind CSS 4** — framework utilitário que permite construir interfaces rapidamente sem escrever CSS customizado, seguindo a dica do desafio de usar um framework CSS moderno.
