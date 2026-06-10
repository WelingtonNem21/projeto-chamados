# spec003 — Frontend Vue.js SPA

Stack: **Vue 3 + Vite + Vue Router + Tailwind CSS + Axios**
Camada: **Frontend**
Pasta: `frontend-chamados/` (projeto separado do backend)

---

## 1. Criação do Projeto

```bash
# Na raiz de e:\projetochamados\
npm create vite@latest frontend-chamados -- --template vue
cd frontend-chamados
npm install
```

Instalar dependências:
```bash
npm install vue-router axios
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

---

## 2. Configuração do Tailwind CSS

`tailwind.config.js`:
```js
export default {
  content: ['./index.html', './src/**/*.{vue,js}'],
  theme: { extend: {} },
  plugins: [],
}
```

`src/style.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

`src/main.js`:
```js
import { createApp } from 'vue'
import { router } from './router'
import App from './App.vue'
import './style.css'

createApp(App).use(router).mount('#app')
```

---

## 3. Serviço de API

Arquivo: `src/services/api.js`

```js
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json' },
})

export default api
```

Todos os componentes importam `api` daqui para comunicar com o backend Laravel.

---

## 4. Vue Router

Arquivo: `src/router/index.js`

```js
import { createRouter, createWebHistory } from 'vue-router'
import ChamadosIndex  from '@/pages/ChamadosIndex.vue'
import ChamadosCreate from '@/pages/ChamadosCreate.vue'
import ChamadosShow   from '@/pages/ChamadosShow.vue'
import ChamadosEdit   from '@/pages/ChamadosEdit.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                      component: ChamadosIndex },
    { path: '/chamados/criar',        component: ChamadosCreate },
    { path: '/chamados/:id',          component: ChamadosShow,  props: true },
    { path: '/chamados/:id/editar',   component: ChamadosEdit,  props: true },
  ],
})
```

---

## 5. Estrutura de Arquivos

```
frontend-chamados/
├── index.html
├── vite.config.js
├── tailwind.config.js
└── src/
    ├── main.js
    ├── style.css
    ├── App.vue                        — RouterView dentro do AppLayout
    ├── router/
    │   └── index.js
    ├── services/
    │   └── api.js
    ├── layouts/
    │   └── AppLayout.vue              — nav + container base
    ├── pages/
    │   ├── ChamadosIndex.vue          — tabela + filtros + busca
    │   ├── ChamadosCreate.vue         — formulário de criação
    │   ├── ChamadosShow.vue           — visualização detalhada
    │   └── ChamadosEdit.vue           — formulário de edição
    └── components/
        ├── BadgePrioridade.vue        — badge colorido por prioridade
        ├── BadgeStatus.vue            — badge colorido por status
        ├── SelectResponsavel.vue      — select + checkbox "Atribuir automaticamente"
        └── FiltrosChamados.vue        — filtros de listagem
```

---

## 6. Layout Base

Arquivo: `src/layouts/AppLayout.vue`

```vue
<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <RouterLink to="/" class="text-lg font-semibold text-gray-800">
          Sistema de Chamados
        </RouterLink>
        <RouterLink
          to="/chamados/criar"
          class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700"
        >
          Novo Chamado
        </RouterLink>
      </div>
    </nav>
    <main class="max-w-6xl mx-auto px-6 py-8">
      <slot />
    </main>
  </div>
</template>
```

---

## 7. Componentes

### 7.1 `BadgePrioridade.vue`

Props: `prioridade` (string: baixa | media | alta)

```vue
<script setup>
defineProps({ prioridade: String })

const classes = {
  alta:  'bg-red-100 text-red-700',
  media: 'bg-orange-100 text-orange-700',
  baixa: 'bg-green-100 text-green-700',
}
</script>

<template>
  <span :class="['inline-flex px-2 py-0.5 rounded text-xs font-medium', classes[prioridade]]">
    {{ prioridade }}
  </span>
</template>
```

### 7.2 `BadgeStatus.vue`

Props: `status` (string)

```vue
<script setup>
defineProps({ status: String })

const classes = {
  aberto:        'bg-blue-100 text-blue-700',
  em_andamento:  'bg-yellow-100 text-yellow-700',
  resolvido:     'bg-green-100 text-green-700',
  fechado:       'bg-gray-100 text-gray-500',
}

const labels = {
  aberto:       'Aberto',
  em_andamento: 'Em Andamento',
  resolvido:    'Resolvido',
  fechado:      'Fechado',
}
</script>

<template>
  <span :class="['inline-flex px-2 py-0.5 rounded text-xs font-medium', classes[status]]">
    {{ labels[status] }}
  </span>
</template>
```

### 7.3 `SelectResponsavel.vue`

Props: `responsaveis` (array), `modelValue` (id | null), `automatico` (boolean)
Emits: `update:modelValue`, `update:automatico`

```vue
<script setup>
defineProps({
  responsaveis: Array,
  modelValue:   [Number, null],
  automatico:   Boolean,
})
defineEmits(['update:modelValue', 'update:automatico'])
</script>

<template>
  <div class="space-y-2">
    <label class="flex items-center gap-2 text-sm text-gray-600">
      <input
        type="checkbox"
        :checked="automatico"
        @change="$emit('update:automatico', $event.target.checked)"
        class="rounded"
      />
      Atribuir automaticamente
    </label>
    <select
      v-if="!automatico"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value || null)"
      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
    >
      <option value="">Sem responsável</option>
      <option v-for="r in responsaveis" :key="r.id" :value="r.id">
        {{ r.name }}
      </option>
    </select>
    <p v-else class="text-sm text-gray-500 italic">
      O sistema atribuirá ao responsável com menos chamados em aberto.
    </p>
  </div>
</template>
```

### 7.4 `FiltrosChamados.vue`

Emite evento `filtrar` com os valores quando qualquer filtro muda.

```vue
<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['filtrar'])
const props = defineProps({ responsaveis: Array })

const filtros = reactive({ status: '', prioridade: '', responsavel_id: '', busca: '' })

function aplicar() {
  emit('filtrar', { ...filtros })
}
</script>

<template>
  <div class="flex flex-wrap gap-3 mb-6">
    <input
      v-model="filtros.busca"
      @input="aplicar"
      placeholder="Buscar por título..."
      class="border border-gray-300 rounded-md px-3 py-2 text-sm w-52"
    />
    <select v-model="filtros.status" @change="aplicar" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
      <option value="">Todos os status</option>
      <option value="aberto">Aberto</option>
      <option value="em_andamento">Em Andamento</option>
      <option value="resolvido">Resolvido</option>
      <option value="fechado">Fechado</option>
    </select>
    <select v-model="filtros.prioridade" @change="aplicar" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
      <option value="">Todas as prioridades</option>
      <option value="alta">Alta</option>
      <option value="media">Média</option>
      <option value="baixa">Baixa</option>
    </select>
    <select v-model="filtros.responsavel_id" @change="aplicar" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
      <option value="">Todos os responsáveis</option>
      <option v-for="r in responsaveis" :key="r.id" :value="r.id">{{ r.name }}</option>
    </select>
  </div>
</template>
```

---

## 8. Pages

### 8.1 `ChamadosIndex.vue`

- Carrega chamados via `GET /api/chamados` com os filtros ativos
- Tabela com colunas: Título, Prioridade, Status, Responsável, Aberto em, Ações
- Ações por linha: Ver (`/chamados/:id`), Editar (`/chamados/:id/editar`), Excluir (DELETE com confirmação)
- Usa `FiltrosChamados`, `BadgePrioridade`, `BadgeStatus`
- Suporta paginação básica (botões anterior/próximo)

### 8.2 `ChamadosCreate.vue`

Campos do formulário:
- `titulo` — input text (obrigatório)
- `descricao` — textarea (opcional)
- `prioridade` — select: baixa | media | alta
- Responsável — componente `SelectResponsavel` (manual ou automático)

Envia `POST /api/chamados` e redireciona para `/` após sucesso.

### 8.3 `ChamadosShow.vue`

- Carrega via `GET /api/chamados/:id`
- Exibe todos os campos em layout de detalhe
- Botões: Editar, Voltar

### 8.4 `ChamadosEdit.vue`

Campos do formulário (mesmo que Create mais):
- `status` — select com todos os status (aparece apenas na edição)
- Responsável — `SelectResponsavel`

Envia `PUT /api/chamados/:id` e redireciona para `/chamados/:id` após sucesso.
Se o chamado estiver `fechado`, exibe aviso e desabilita o formulário.

---

## 9. Regras Visuais (Tailwind)

| Valor | Classes Tailwind |
|---|---|
| Prioridade `alta` | `bg-red-100 text-red-700` |
| Prioridade `media` | `bg-orange-100 text-orange-700` |
| Prioridade `baixa` | `bg-green-100 text-green-700` |
| Status `aberto` | `bg-blue-100 text-blue-700` |
| Status `em_andamento` | `bg-yellow-100 text-yellow-700` |
| Status `resolvido` | `bg-green-100 text-green-700` |
| Status `fechado` | `bg-gray-100 text-gray-500` |

---

## 10. Subir o Frontend

```bash
cd frontend-chamados
npm run dev
# Acesse: http://localhost:5173
```

O backend Laravel deve estar rodando em `http://localhost:8000`:
```bash
# Em outro terminal, dentro de backend-chamados/
php artisan serve
```

---

*Anterior: [spec002.md](spec002.md) — API Routes e Controllers*
