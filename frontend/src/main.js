import Vue from 'vue'
import VueI18n from 'vue-i18n'
import App from './App.vue'
import AppSingle from './AppSingle.vue'
import lang from './lang'
import store from './store'
import {sync} from 'vuex-router-sync'
import Router from 'vue-router'
import RunningMatches from './components/RunningMatches.vue'
import PastMatches from './components/PastMatches.vue'
import Players from './components/Players.vue'
import Settings from './components/Settings.vue'
import Rules from './components/Rules.vue'

import './style/zone.scss'

export async function init (settings) {
  Vue.config.productionTip = false
  Vue.use(VueI18n)

  Vue.config.lang = localStorage.getItem('nczone-lang') || 'en'

  const i18n = new VueI18n({
    locale: Vue.config.lang,
    messages: lang
  })

  const el = typeof settings.target === 'string'
    ? (document.getElementById(settings.target) || document.querySelector(settings.target))
    : settings.target

  const initSingle = (matchId) => {
    // eslint-disable-next-line no-new
    new Vue({
      el,
      i18n,
      store,
      render: h => h(AppSingle)
    })

    store.dispatch('init', {
      matchId
    })
  }

  const initZone = () => {
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
          name: 'players',
          path: '/players',
          component: Players
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

    // eslint-disable-next-line no-new
    new Vue({
      el,
      i18n,
      router,
      store,
      render: h => h(App)
    })
    store.dispatch('init')
  }

  if (typeof settings.matchId === 'undefined') {
    initZone()
  } else {
    initSingle(settings.matchId)
  }
}
