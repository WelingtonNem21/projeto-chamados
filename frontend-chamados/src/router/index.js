import { createRouter, createWebHistory } from 'vue-router'
import ChamadosIndex  from '@/pages/ChamadosIndex.vue'
import ChamadosCreate from '@/pages/ChamadosCreate.vue'
import ChamadosShow   from '@/pages/ChamadosShow.vue'
import ChamadosEdit   from '@/pages/ChamadosEdit.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                     component: ChamadosIndex },
    { path: '/chamados/criar',       component: ChamadosCreate },
    { path: '/chamados/:id',         component: ChamadosShow,  props: true },
    { path: '/chamados/:id/editar',  component: ChamadosEdit,  props: true },
  ],
})
