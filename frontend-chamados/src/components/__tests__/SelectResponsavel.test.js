import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import SelectResponsavel from '../SelectResponsavel.vue'

const responsaveis = [
  { id: 1, name: 'Ana Lima' },
  { id: 2, name: 'Bruno Costa' },
]

describe('SelectResponsavel', () => {
  it('exibe o select quando automatico=false', () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: false },
    })
    expect(wrapper.find('select').exists()).toBe(true)
  })

  it('oculta o select quando automatico=true', () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: true },
    })
    expect(wrapper.find('select').exists()).toBe(false)
  })

  it('emite update:automatico ao marcar checkbox', async () => {
    const wrapper = mount(SelectResponsavel, {
      props: { responsaveis, modelValue: null, automatico: false },
    })
    await wrapper.find('input[type=checkbox]').trigger('change')
    expect(wrapper.emitted('update:automatico')).toBeTruthy()
  })
})
