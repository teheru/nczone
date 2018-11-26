import { shallowMount, createLocalVue } from '@vue/test-utils'
import CivList from '@/view/components/CivList'
import VueI18n from 'vue-i18n'
import lang from '@/lang'

describe('CivList.vue', () => {
  const localVue = createLocalVue()
  localVue.use(VueI18n)

  const i18n = new VueI18n({
    locale: 'de',
    messages: lang
  })

  const list = [
    { title: 'NCZONE_AZTECS', multiplier: 2 },
    { title: 'NCZONE_BRITONS', multiplier: 1 }
  ]
  const cmp = shallowMount(CivList, {
    localVue: localVue,
    i18n,
    propsData: { list }
  })

  test('Should match snapshot', () => {
    expect(cmp.html()).toMatchSnapshot()
  })
})
