<script setup>
defineProps({ status: String })

const classes = {
  aberto:        'bg-blue-100 text-blue-700',
  em_andamento:  'bg-yellow-100 text-yellow-700',
  resolvido:     'bg-green-100 text-green-700',
  fechado:       'bg-gray-100 text-gray-500',
}

const labels = {
  aberto:        'Aberto',
  em_andamento:  'Em Andamento',
  resolvido:     'Resolvido',
  fechado:       'Fechado',
}
</script>

<template>
  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', classes[status]]">
    {{ labels[status] ?? status }}
  </span>
</template>
