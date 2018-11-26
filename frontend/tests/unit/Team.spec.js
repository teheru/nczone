import { shallowMount, createLocalVue } from '@vue/test-utils'
import matchFixture from './__fixtures__/match'
import meFixture from './__fixtures__/me'
import Team from '@/view/components/Team'
import VueI18n from 'vue-i18n'
import lang from '@/lang'

describe('Team.vue', () => {
  const localVue = createLocalVue()
  localVue.use(VueI18n)

  const i18n = new VueI18n({
    locale: 'de',
    messages: lang
  })

  const cmp = shallowMount(Team, {
    computed: {
      me: () => meFixture,
      canReplace: () => true
    },
    localVue: localVue,
    i18n,
    propsData: {
      match: matchFixture,
      team: 1
    }
  })

  test('Should match snapshot', () => {
    expect(cmp.html()).toMatchSnapshot()
  })
})
