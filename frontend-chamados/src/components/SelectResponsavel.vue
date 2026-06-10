<script setup>
defineProps({
  responsaveis: Array,
  modelValue:   [Number, String, null],
  automatico:   Boolean,
})
defineEmits(['update:modelValue', 'update:automatico'])
</script>

<template>
  <div class="space-y-2">
    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
      <input
        type="checkbox"
        :checked="automatico"
        @change="$emit('update:automatico', $event.target.checked)"
        class="rounded border-gray-300 text-blue-600"
      />
      Atribuir automaticamente
    </label>
    <p v-if="automatico" class="text-sm text-gray-500 italic">
      O sistema atribuirá ao responsável com menos chamados em aberto.
    </p>
    <select
      v-else
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value || null)"
      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
      <option value="">Sem responsável</option>
      <option v-for="r in responsaveis" :key="r.id" :value="r.id">
        {{ r.name }}
      </option>
    </select>
  </div>
</template>
