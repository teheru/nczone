import Components from './components'
import Views from './views'

export default {
  install: (Vue) => {
    for (const key in Components) {
      Vue.component(Components[key].name, Components[key])
    }
  },
  Components,
  Views
}
