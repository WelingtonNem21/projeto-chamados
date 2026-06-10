<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api.js'
import SelectResponsavel from '@/components/SelectResponsavel.vue'

const router       = useRouter()
const responsaveis = ref([])
const salvando     = ref(false)
const erros        = reactive({})

const form = reactive({
  titulo:         '',
  descricao:      '',
  prioridade:     '',
  responsavel_id: null,
  automatico:     false,
})

async function carregarResponsaveis() {
  try {
    const { data } = await api.get('/users')
    responsaveis.value = data.data ?? data
  } catch { /* silencioso */ }
}

function validar() {
  Object.keys(erros).forEach(k => delete erros[k])
  if (!form.titulo.trim()) erros.titulo = 'O campo título é obrigatório.'
  if (!form.prioridade)    erros.prioridade = 'Selecione a prioridade.'
  return Object.keys(erros).length === 0
}

async function salvar() {
  if (!validar()) return
  salvando.value = true
  try {
    const payload = {
      titulo:         form.titulo,
      descricao:      form.descricao,
      prioridade:     form.prioridade,
      responsavel_id:           form.automatico ? null : form.responsavel_id,
      atribuir_automaticamente: form.automatico,
    }
    await api.post('/chamados', payload)
    router.push('/')
  } catch (e) {
    if (e.response?.data?.errors) {
      Object.assign(erros, e.response.data.errors)
    } else {
      erros.geral = 'Erro ao criar chamado. Tente novamente.'
    }
  } finally {
    salvando.value = false
  }
}

onMounted(carregarResponsaveis)
</script>

<template>
  <div class="max-w-xl mx-auto">
    <RouterLink to="/" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
      ← Voltar para lista
    </RouterLink>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h1 class="text-xl font-bold text-gray-900 mb-1">Novo Chamado</h1>
      <p class="text-sm text-gray-500 mb-6">
        Preencha as informações abaixo para relatar um novo problema ou solicitação.
      </p>

      <div v-if="erros.geral" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
        {{ erros.geral }}
      </div>

      <form @submit.prevent="salvar" class="space-y-5">
        <!-- Título -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Título <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.titulo"
            type="text"
            placeholder="Descreva o problema brevemente"
            :class="['w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
              erros.titulo ? 'border-red-400 bg-red-50' : 'border-gray-300']"
          />
          <p v-if="erros.titulo" class="mt-1 text-xs text-red-600">{{ erros.titulo }}</p>
        </div>

        <!-- Descrição -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
          <textarea
            v-model="form.descricao"
            rows="4"
            placeholder="Detalhes adicionais (opcional)"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>

        <!-- Prioridade -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Prioridade <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.prioridade"
            :class="['w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
              erros.prioridade ? 'border-red-400' : 'border-gray-300']"
          >
            <option value="">Seleciona...</option>
            <option value="baixa">Baixa</option>
            <option value="media">Média</option>
            <option value="alta">Alta</option>
          </select>
          <p v-if="erros.prioridade" class="mt-1 text-xs text-red-600">{{ erros.prioridade }}</p>
        </div>

        <!-- Responsável -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Responsável</label>
          <SelectResponsavel
            :responsaveis="responsaveis"
            v-model="form.responsavel_id"
            :automatico="form.automatico"
            @update:automatico="form.automatico = $event"
          />
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-3 pt-2">
          <RouterLink to="/"
            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </RouterLink>
          <button
            type="submit"
            :disabled="salvando"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-60 transition-colors"
          >
            {{ salvando ? 'Criando...' : 'Criar Chamado' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
