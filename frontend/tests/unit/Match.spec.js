import { shallowMount, createLocalVue } from '@vue/test-utils'
import matchFixture from './__fixtures__/match'
import meFixture from './__fixtures__/me'
import VueI18n from 'vue-i18n'
import lang from '@/lang'
import View from '@/view'

describe('Match.vue', () => {
  const localVue = createLocalVue()
  localVue.use(VueI18n)
  View.install(localVue)

  const i18n = new VueI18n({
    locale: 'de',
    messages: lang
  })

  const cmp = shallowMount(View.Components.Match, {
    computed: {
      me: () => meFixture,
      canAddPair: () => true,
      canModPost: () => true,
      timer: () => ({ every: () => {}, off: () => {} })
    },
    localVue: localVue,
    i18n,
    propsData: {
      match: matchFixture
    }
  })

  test('Should match snapshot', () => {
    expect(cmp.html()).toMatchSnapshot()
  })
})
