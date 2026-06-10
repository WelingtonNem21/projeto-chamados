<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api.js'
import FiltrosChamados from '@/components/FiltrosChamados.vue'
import BadgePrioridade from '@/components/BadgePrioridade.vue'
import BadgeStatus from '@/components/BadgeStatus.vue'

const chamados     = ref([])
const responsaveis = ref([])
const meta         = reactive({ current_page: 1, last_page: 1, total: 0 })
const filtros      = reactive({ status: '', prioridade: '', responsavel_id: '', busca: '' })
const carregando   = ref(false)
const erro         = ref('')

async function carregar(page = 1) {
  carregando.value = true
  erro.value = ''
  try {
    const params = { page, ...filtros }
    const { data } = await api.get('/chamados', { params })
    chamados.value      = data.data
    meta.current_page   = data.meta?.current_page ?? data.current_page ?? page
    meta.last_page      = data.meta?.last_page    ?? data.last_page    ?? 1
    meta.total          = data.meta?.total         ?? data.total         ?? 0
  } catch {
    erro.value = 'Erro ao carregar chamados.'
  } finally {
    carregando.value = false
  }
}

async function carregarResponsaveis() {
  try {
    const { data } = await api.get('/responsaveis')
    responsaveis.value = data.data ?? data
  } catch {}
}

async function excluir(id) {
  if (!confirm('Tem certeza que deseja excluir este chamado?')) return
  try {
    await api.delete(`/chamados/${id}`)
    await carregar(meta.current_page)
  } catch {
    alert('Erro ao excluir chamado.')
  }
}

function aplicarFiltros(f) {
  Object.assign(filtros, f)
  carregar(1)
}

function formatarData(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('pt-BR')
}

function iniciais(nome) {
  if (!nome) return '?'
  return nome.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

onMounted(() => {
  carregar()
  carregarResponsaveis()
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Lista de Chamados</h1>
      <p class="text-sm text-gray-500 mt-1">Gerencie e acompanhe os chamados de TI em andamento.</p>
    </div>

    <FiltrosChamados :responsaveis="responsaveis" @filtrar="aplicarFiltros" />

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="carregando" class="p-12 text-center text-gray-400">
        Carregando...
      </div>

      <div v-else-if="erro" class="p-6 text-red-600 text-sm">{{ erro }}</div>

      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <th class="px-4 py-3 text-left font-medium">Título do Chamado</th>
            <th class="px-4 py-3 text-left font-medium">Prioridade</th>
            <th class="px-4 py-3 text-left font-medium">Status</th>
            <th class="px-4 py-3 text-left font-medium">Responsável</th>
            <th class="px-4 py-3 text-left font-medium">Aberto em</th>
            <th class="px-4 py-3 text-left font-medium">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="chamados.length === 0">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400">Nenhum chamado encontrado.</td>
          </tr>
          <tr v-for="c in chamados" :key="c.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">
              <RouterLink :to="`/chamados/${c.id}`" class="font-medium text-gray-900 hover:text-blue-600">
                <span class="text-gray-400 text-xs mr-1">#{{ c.id }}</span>{{ c.titulo }}
              </RouterLink>
            </td>
            <td class="px-4 py-3">
              <BadgePrioridade :prioridade="c.prioridade" />
            </td>
            <td class="px-4 py-3">
              <BadgeStatus :status="c.status" />
            </td>
            <td class="px-4 py-3">
              <div v-if="c.responsavel" class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                  {{ iniciais(c.responsavel.name) }}
                </div>
                <span class="text-gray-700 text-sm">{{ c.responsavel.name }}</span>
              </div>
              <span v-else class="text-gray-400 italic text-xs">Sem responsável</span>
            </td>
            <td class="px-4 py-3 text-gray-500">{{ formatarData(c.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <RouterLink :to="`/chamados/${c.id}`" class="text-blue-600 hover:underline">Ver</RouterLink>
                <RouterLink :to="`/chamados/${c.id}/editar`" class="text-gray-600 hover:underline">Editar</RouterLink>
                <button @click="excluir(c.id)" class="text-red-500 hover:underline">Excluir</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!carregando && meta.last_page > 1" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
        <span>Página {{ meta.current_page }} de {{ meta.last_page }} — {{ meta.total }} chamados</span>
        <div class="flex gap-2">
          <button
            @click="carregar(meta.current_page - 1)"
            :disabled="meta.current_page <= 1"
            class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >← Anterior</button>
          <button
            @click="carregar(meta.current_page + 1)"
            :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >Próximo →</button>
        </div>
      </div>
    </div>
  </div>
</template>
