import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import BadgePrioridade from '../BadgePrioridade.vue'

describe('BadgePrioridade', () => {
  it('exibe o texto da prioridade', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'alta' } })
    expect(wrapper.text()).toBe('alta')
  })

  it('aplica classe vermelha para alta', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'alta' } })
    expect(wrapper.classes()).toContain('bg-red-100')
  })

  it('aplica classe laranja para media', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'media' } })
    expect(wrapper.classes()).toContain('bg-orange-100')
  })

  it('aplica classe verde para baixa', () => {
    const wrapper = mount(BadgePrioridade, { props: { prioridade: 'baixa' } })
    expect(wrapper.classes()).toContain('bg-green-100')
  })
})
