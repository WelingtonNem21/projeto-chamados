<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api.js'
import SelectResponsavel from '@/components/SelectResponsavel.vue'
import BadgePrioridade from '@/components/BadgePrioridade.vue'
import BadgeStatus from '@/components/BadgeStatus.vue'

const props = defineProps({ id: String })
const router = useRouter()

const chamado      = ref(null)
const responsaveis = ref([])
const salvando     = ref(false)
const carregando   = ref(true)
const erros        = reactive({})

const form = reactive({
  titulo:         '',
  descricao:      '',
  prioridade:     '',
  status:         '',
  responsavel_id: null,
  automatico:     false,
})

function formatarData(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}

async function carregar() {
  try {
    const [{ data: c }, { data: r }] = await Promise.all([
      api.get(`/chamados/${props.id}`),
      api.get('/users'),
    ])
    const d = c.data ?? c
    chamado.value = d
    responsaveis.value = r.data ?? r

    form.titulo         = d.titulo        ?? ''
    form.descricao      = d.descricao     ?? ''
    form.prioridade     = d.prioridade    ?? ''
    form.status         = d.status        ?? ''
    form.responsavel_id = d.responsavel_id ?? d.responsavel?.id ?? null
    form.automatico     = false
  } catch {
    erros.geral = 'Erro ao carregar chamado.'
  } finally {
    carregando.value = false
  }
}

function validar() {
  Object.keys(erros).forEach(k => delete erros[k])
  if (!form.titulo.trim()) erros.titulo = 'O campo título é obrigatório.'
  if (!form.prioridade)    erros.prioridade = 'Selecione a prioridade.'
  if (!form.status)        erros.status = 'Selecione o status.'
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
      status:         form.status,
      responsavel_id: form.automatico ? null : form.responsavel_id,
      automatico:     form.automatico,
    }
    await api.put(`/chamados/${props.id}`, payload)
    router.push(`/chamados/${props.id}`)
  } catch (e) {
    if (e.response?.data?.errors) {
      Object.assign(erros, e.response.data.errors)
    } else {
      erros.geral = 'Erro ao salvar chamado.'
    }
  } finally {
    salvando.value = false
  }
}

onMounted(carregar)
</script>

<template>
  <div>
    <div v-if="carregando" class="text-center py-16 text-gray-400">Carregando...</div>

    <div v-else>
      <!-- Cabeçalho -->
      <div class="flex items-center gap-3 mb-6">
        <RouterLink :to="`/chamados/${id}`" class="text-sm text-gray-500 hover:text-gray-700">←</RouterLink>
        <h1 class="text-xl font-bold text-gray-900">Editar Chamado #{{ id }}</h1>
        <BadgeStatus v-if="chamado" :status="chamado.status" />
        <BadgePrioridade v-if="chamado" :prioridade="chamado.prioridade" />
      </div>

      <!-- Aviso: chamado fechado -->
      <div v-if="chamado?.status === 'fechado'"
        class="mb-6 flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
        <span class="text-lg">🔒</span>
        <p>Este chamado está fechado. Não é possível editá-lo.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulário -->
        <div class="lg:col-span-2">
          <div :class="['bg-white rounded-lg shadow-sm border border-gray-200 p-6', chamado?.status === 'fechado' ? 'opacity-60 pointer-events-none select-none' : '']">
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
                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                />
              </div>

              <!-- Prioridade + Status -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Prioridade <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.prioridade"
                    :class="['w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                      erros.prioridade ? 'border-red-400' : 'border-gray-300']"
                  >
                    <option value="">Selecionar...</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.status"
                    :class="['w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                      erros.status ? 'border-red-400' : 'border-gray-300']"
                  >
                    <option value="">Selecionar...</option>
                    <option value="aberto">Aberto</option>
                    <option value="em_andamento">Em Andamento</option>
                    <option value="resolvido">Resolvido</option>
                    <option value="fechado">Fechado</option>
                  </select>
                </div>
              </div>

              <!-- Atribuição -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Atribuição</label>
                <SelectResponsavel
                  :responsaveis="responsaveis"
                  v-model="form.responsavel_id"
                  :automatico="form.automatico"
                  @update:automatico="form.automatico = $event"
                />
              </div>

              <!-- Botões -->
              <div class="flex justify-end gap-3 pt-2">
                <RouterLink :to="`/chamados/${id}`"
                  class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                  Cancelar
                </RouterLink>
                <button
                  type="submit"
                  :disabled="salvando"
                  class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-60 transition-colors">
                  {{ salvando ? 'Salvando...' : 'Salvar Alterações' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4" v-if="chamado">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Detalhes do Chamado</h2>
            <div class="space-y-3 text-sm">
              <div>
                <p class="text-xs text-gray-400 mb-1">Criado em</p>
                <p class="text-gray-700">{{ formatarData(chamado.created_at) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400 mb-1">Última atualização</p>
                <p class="text-gray-700">{{ formatarData(chamado.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
