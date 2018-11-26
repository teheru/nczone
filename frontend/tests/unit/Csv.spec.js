import { shallowMount } from '@vue/test-utils'
import Csv from '@/view/components/Csv'

describe('Csv.vue', () => {
  const list = ['some Data ', 'in a list']
  const cmp = shallowMount(Csv, {
    propsData: { list }
  })

  test('Should match snapshot', () => {
    expect(cmp.html()).toMatchSnapshot()
  })
})
