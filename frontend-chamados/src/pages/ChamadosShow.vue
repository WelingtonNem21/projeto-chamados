<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api.js'
import BadgePrioridade from '@/components/BadgePrioridade.vue'
import BadgeStatus from '@/components/BadgeStatus.vue'

const props = defineProps({ id: String })
const router = useRouter()

const chamado    = ref(null)
const carregando = ref(true)
const erro       = ref('')

async function carregar() {
  try {
    const { data } = await api.get(`/chamados/${props.id}`)
    chamado.value = data.data ?? data
  } catch {
    erro.value = 'Chamado não encontrado.'
  } finally {
    carregando.value = false
  }
}

function formatarData(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('pt-BR', { dateStyle: 'long', timeStyle: 'short' })
}

function iniciais(nome) {
  if (!nome) return '?'
  return nome.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

onMounted(carregar)
</script>

<template>
  <div>
    <div v-if="carregando" class="text-center py-16 text-gray-400">Carregando...</div>

    <div v-else-if="erro" class="text-center py-16 text-red-500">{{ erro }}</div>

    <div v-else-if="chamado">
      <!-- Cabeçalho -->
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3 flex-wrap">
            <span class="text-gray-400 text-xl">#{{ chamado.id }}</span>
            {{ chamado.titulo }}
          </h1>
          <div class="flex items-center gap-2 mt-2">
            <BadgeStatus :status="chamado.status" />
            <BadgePrioridade :prioridade="chamado.prioridade" />
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <RouterLink to="/"
            class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
            ← Voltar
          </RouterLink>
          <RouterLink :to="`/chamados/${chamado.id}/editar`"
            class="inline-flex items-center gap-1 px-3 py-2 text-sm text-white bg-blue-600 border border-blue-600 rounded-md hover:bg-blue-700 transition-colors">
            ✏ Editar
          </RouterLink>
        </div>
      </div>

      <!-- Aviso: chamado fechado -->
      <div v-if="chamado.status === 'fechado'"
        class="mb-6 flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
        <span class="text-lg">🔒</span>
        <div>
          <p class="font-semibold">Chamado Encerrado</p>
          <p>Este chamado está fechado e não pode ser editado.</p>
        </div>
      </div>

      <!-- Conteúdo principal -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Descrição -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Descrição do Problema</h2>
            <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
              {{ chamado.descricao || 'Sem descrição.' }}
            </p>
          </div>
        </div>

        <!-- Detalhes -->
        <div class="space-y-4">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Detalhes do Chamado</h2>

            <div class="space-y-4">
              <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Responsável</p>
                <div v-if="chamado.responsavel" class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    {{ iniciais(chamado.responsavel.name) }}
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ chamado.responsavel.name }}</span>
                </div>
                <span v-else class="text-sm text-gray-400 italic">Sem responsável</span>
              </div>

              <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Aberta em</p>
                <p class="text-sm text-gray-700">{{ formatarData(chamado.created_at) }}</p>
              </div>

              <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Última atualização</p>
                <p class="text-sm text-gray-700">{{ formatarData(chamado.updated_at) }}</p>
              </div>

              <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nº do chamado</p>
                <p class="text-sm font-mono text-gray-700">#{{ chamado.id }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
