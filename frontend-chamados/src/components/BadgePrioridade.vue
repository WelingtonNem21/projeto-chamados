<script setup>
defineProps({ prioridade: String })

const classes = {
  alta:  'bg-red-100 text-red-700',
  media: 'bg-orange-100 text-orange-700',
  baixa: 'bg-green-100 text-green-700',
}

const labels = {
  alta:  'Alta',
  media: 'Média',
  baixa: 'Baixa',
}
</script>

<template>
  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', classes[prioridade]]">
    ● {{ labels[prioridade] ?? prioridade }}
  </span>
</template>
