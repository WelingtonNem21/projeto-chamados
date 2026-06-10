<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200 px-6 py-3">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-2">
          <span class="text-base font-semibold text-gray-800">Chamados</span>
        </RouterLink>

        <div class="flex items-center gap-3">
        
          <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-sm font-semibold">
            U
          </div>
          <RouterLink
            to="/chamados/criar"
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
          >
            + Novo Chamado
          </RouterLink>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-8">
      <slot />
    </main>

    <footer class="border-t border-gray-200 mt-auto py-4 text-center text-sm text-gray-400">
      © {{ new Date().getFullYear() }} Chamados. Todos os direitos reservados.
    </footer>
  </div>
</template>
