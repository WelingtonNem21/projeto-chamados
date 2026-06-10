import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import BadgeStatus from '../BadgeStatus.vue'

describe('BadgeStatus', () => {
  it('exibe label "Em Andamento" para em_andamento', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'em_andamento' } })
    expect(wrapper.text()).toBe('Em Andamento')
  })

  it('aplica classe cinza para fechado', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'fechado' } })
    expect(wrapper.classes()).toContain('bg-gray-100')
  })

  it('aplica classe azul para aberto', () => {
    const wrapper = mount(BadgeStatus, { props: { status: 'aberto' } })
    expect(wrapper.classes()).toContain('bg-blue-100')
  })
})
