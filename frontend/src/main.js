import Vue from 'vue'
import VueI18n from 'vue-i18n'
import App from './App.vue'
import AppSingle from './AppSingle.vue'
import lang from './lang'
import createStore from './store'
import {sync} from 'vuex-router-sync'
import Router from 'vue-router'
import RunningMatches from './components/RunningMatches.vue'
import PastMatches from './components/PastMatches.vue'
import Players from './components/Players.vue'
import Settings from './components/Settings.vue'
import Rules from './components/Rules.vue'
import * as api from './api'
import * as routes from './routes'

import './style/zone.scss'

export async function init (settings) {
  const me = await api.actively.getMe()

  Vue.config.productionTip = false
  Vue.use(VueI18n)

  const i18n = new VueI18n({
    locale: me.lang || 'de',
    messages: lang
  })

  const el = typeof settings.target === 'string'
    ? (document.getElementById(settings.target) || document.querySelector(settings.target))
    : settings.target

  const store = createStore()

  const initSingle = (matchId) => {
    // eslint-disable-next-line no-new
    new Vue({
      el,
      i18n,
      store,
      render: h => h(AppSingle)
    })

    store.dispatch('init', {me, i18n, matchId})
  }

  const initZone = () => {
    Vue.use(Router)
    const router = new Router({
      routes: [
        {
          name: routes.ROUTE_RMATCHES,
          path: '/',
          component: RunningMatches
        },
        {
          name: routes.ROUTE_PMATCHES,
          path: '/pmatches',
          component: PastMatches
        },
        {
          name: routes.ROUTE_PLAYERS,
          path: '/players',
          component: Players
        },
        {
          name: routes.ROUTE_SETTINGS,
          path: '/settings',
          component: Settings
        },
        {
          name: routes.ROUTE_RULES,
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

    store.dispatch('init', {me, i18n})
  }

  if (typeof settings.matchId === 'undefined') {
    initZone()
  } else {
    initSingle(settings.matchId)
  }
}
