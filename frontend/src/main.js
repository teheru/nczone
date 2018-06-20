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
import Settings from './components/Settings.vue'
import Rules from './components/Rules.vue'
import * as api from './api'

import './style/zone.scss'
export async function init (settings) {
  const doInit = function (state) {
    Vue.use(Router)

    const router = new Router({
      routes: [
        {
          name: 'rmatches',
          path: '/',
          component: RunningMatches
        },
        {
          name: 'pmatches',
          path: '/pmatches',
          component: PastMatches
        },
        {
          name: 'table',
          path: '/table',
          component: Table
        },
        {
          name: 'settings',
          path: '/settings',
          component: Settings
        },
        {
          name: 'rules',
          path: '/rules',
          component: Rules
        }
      ]
    })

    sync(store, router)

    Vue.config.productionTip = false
    Vue.use(VueI18n)

    Vue.config.lang = localStorage.getItem('nczone-lang') || 'en'

    const i18n = new VueI18n({
      locale: Vue.config.lang,
      messages: lang
    })

    // eslint-disable-next-line no-new
    new Vue({
      el: typeof settings.target === 'string' ? document.getElementById(settings.target) : settings.target,
      i18n,
      router,
      store,
      render: h => h(App)
    })

    store.dispatch('init', state)
  }

  api.state()
    .then(s => {
      doInit(s)
    })
    .catch(_ => {
      doInit()
    })
}
