<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['filtrar'])
defineProps({ responsaveis: Array })

const filtros = reactive({ status: '', prioridade: '', responsavel_id: '', busca: '' })

function aplicar() {
  emit('filtrar', { ...filtros })
}
</script>

<template>
  <div class="flex flex-wrap gap-3 mb-6">
    <div class="relative">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
      </svg>
      <input
        v-model="filtros.busca"
        @input="aplicar"
        placeholder="Buscar por título..."
        class="border border-gray-300 rounded-md pl-9 pr-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <select v-model="filtros.status" @change="aplicar"
      class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Status: Todos</option>
      <option value="aberto">Aberto</option>
      <option value="em_andamento">Em Andamento</option>
      <option value="resolvido">Resolvido</option>
      <option value="fechado">Fechado</option>
    </select>

    <select v-model="filtros.prioridade" @change="aplicar"
      class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Prioridade: Todas</option>
      <option value="alta">Alta</option>
      <option value="media">Média</option>
      <option value="baixa">Baixa</option>
    </select>

    <select v-model="filtros.responsavel_id" @change="aplicar"
      class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Responsável: Todos</option>
      <option v-for="r in responsaveis" :key="r.id" :value="r.id">{{ r.name }}</option>
    </select>
  </div>
</template>
