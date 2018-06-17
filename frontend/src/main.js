import Vue from 'vue'
import VueI18n from 'vue-i18n'
import App from './App.vue'
import lang from './lang'
import store from './store'
import {sync} from 'vuex-router-sync'
import Router from 'vue-router'
import RunningMatches from './components/RunningMatches.vue'
import PastMatches from './components/PastMatches.vue'
import Table from './components/Table.vue'

import './style/zone.scss'

Vue.use(Router)

const router = new Router({
  routes: [
    {
      name: 'rmatches',
      path: '/',
      component: RunningMatches,
    },
    {
      name: 'pmatches',
      path: '/pmatches',
      component: PastMatches,
    },
    {
      name: 'table',
      path: '/table',
      component: Table,
    }
  ]
})

sync(store, router)

Vue.config.productionTip = false
Vue.use(VueI18n)

const i18n = new VueI18n({
  locale: 'en',
  messages: lang
})

new Vue({
  i18n,
  router,
  store,
  render: h => h(App)
}).$mount('#nczone')
