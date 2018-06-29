import Vue from 'vue'
import Vuex from 'vuex'

import * as api from './api'
import * as routes from './routes'

const waitMs = async (time) => {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve()
    }, time)
  })
}

export default () => {
  Vue.use(Vuex)

  return new Vuex.Store({
    state: {
      me: {
        id: 0,
        sid: '',
        permissions: {
          u_zone_view_login: false,
          u_zone_view_info: false,
          u_zone_draw: false,
          u_zone_login: false,
          u_zone_change_match: false,
          m_zone_draw_match: false,
          m_zone_login_players: false,
          m_zone_change_match: false
        }
      },
      drawPreview: {
        visible: false,
        players: []
      },
      match: null, // single match
      players: [],
      runningMatches: [],
      pastMatches: {
        items: [],
        total: 0,
        page: 1
      },
      information: {
        items: [],
        index: 0
      },
      // idea: to reduce number of ajax calls, we save timestamps
      //       when certain actions were executed last and only
      //       make the ajax call if there is no data or timestamp
      //       far enough in the past
      actionTimestamps: {
        getPastMatches: 0,
        getRunningMatches: 0,
        getAllPlayers: 0,
        getLoggedInPlayers: 0
      },
      i18n: null
    },
    getters: {
      players: (s) => s.players,
      me: (s) => s.me,
      loggedInPlayers: (s) => s.players.filter(p => p.logged_in > 0).sort((a, b) => {
        if (a.logged_in === b.logged_in) {
          return a.rating > b.rating ? -1 : 1
        }
        return a.logged_in > b.logged_in ? 1 : -1
      }),
      loggedInUserIds: (s) => s.players.filter(p => p.logged_in > 0).map(u => u.id),
      canDraw: (s, g) => {
        if (!s.me.permissions.u_zone_view_login) {
          return false
        }
        if (g.loggedInUserIds.length <= 1) {
          return false
        }
        if (g.loggedInUserIds.includes(s.me.id) && s.me.permissions.u_zone_draw) {
          return true
        }
        return s.me.permissions.m_zone_draw_match
      },
      canModPost: (s) => s.me.permissions.m_zone_draw_match,
      canLogin: (s, g) => s.me.permissions.u_zone_view_login && s.me.permissions.u_zone_login && !g.isLoggedIn && !g.isPlaying,
      isLoggedIn: (s, g) => g.loggedInUserIds.includes(s.me.id),
      isPlaying: (s, g) => !!g.runningMatches.find(m => m.players.team1.find(p => p.id === s.me.id) || m.players.team2.find(p => p.id === s.me.id)),
      runningMatches: (s) => s.runningMatches,
      pastMatches: (s) => s.pastMatches.items,
      drawPreview: (s) => s.drawPreview,
      info: (s) => s.information.items[s.information.index] || '',
      informationIndex: (s) => s.information.index,
      playerById: (s) => (id) => s.players.find(p => p.id === id),
      match: (s) => s.match
    },
    mutations: {
      init (state, {me, i18n}) {
        state.me.id = me.id || 0
        state.me.sid = me.sid || ''
        state.me.permissions.u_zone_view_login = me.permissions.u_zone_view_login || false
        state.me.permissions.u_zone_view_info = me.permissions.u_zone_view_info || false
        state.me.permissions.u_zone_draw = me.permissions.u_zone_draw || false
        state.me.permissions.u_zone_login = me.permissions.u_zone_login || false
        state.me.permissions.u_zone_change_match = me.permissions.u_zone_change_match || false
        state.me.permissions.m_zone_draw_match = me.permissions.m_zone_draw_match || false
        state.me.permissions.m_zone_login_players = me.permissions.m_zone_login_players || false
        state.me.permissions.m_zone_change_match = me.permissions.m_zone_change_match || false

        state.i18n = i18n
        state.i18n.locale = me.lang

        api.setSid(state.me.sid)
      },
      setLang (state, payload) {
        state.i18n.locale = payload
      },
      setLoggedInPlayers (state, payload) {
        // all players are updated to be logged in if needed
        state.players.forEach(player => {
          const loggedInPlayer = payload.find(m => m.id === player.id)
          if (loggedInPlayer) {
            player.logged_in = loggedInPlayer.logged_in
          } else {
            player.logged_in = 0
          }
        })

        // missing players are added
        payload.forEach(player => {
          const p = state.players.find(m => m.id === player.id)
          if (!p) {
            state.players.push(player)
          }
        })
      },
      setAllPlayers (state, payload) {
        const players = []
        payload.forEach(player => {
          players.push(player)
        })
        state.players.forEach(player => {
          if (!players.find(m => m.id === player.id)) {
            players.push(player)
          }
        })
        state.players = players
      },
      setMatch (state, payload) {
        state.match = payload
      },
      setRunningMatches (state, payload) {
        state.runningMatches = payload
      },
      setPastMatches (state, payload) {
        state.pastMatches.items = payload
      },
      setInformation (state, payload) {
        state.information.items = payload
        state.information.index = 0
      },
      showDrawPreview (state, payload) {
        state.drawPreview.visible = true
        state.drawPreview.players = payload
      },
      hideDrawPreview (state, payload) {
        state.drawPreview.visible = false
        state.drawPreview.players = []
      },
      increaseInformationIndex (state) {
        state.information.index += 1
        if (state.information.index > state.information.items.length - 1) {
          state.information.index = 0
        }
      }
    },
    actions: {
      async init ({state, commit, dispatch}, payload) {
        if (payload.matchId) {
          commit('setMatch', await api.match(payload.matchId))
          commit('init', {me: payload.me, i18n: payload.i18n})
        } else {
          commit('init', {me: payload.me, i18n: payload.i18n})
          dispatch('poll')
        }
      },
      async poll ({rootState, state, commit, dispatch}) {
        // todo: all these should trigger passive api requests
        // todo: check if 30sec is a good value for all the api calls. getInformation for example could have a slower interval..

        // always refresh logged in players
        dispatch('getLoggedInPlayers')

        // always fetch information
        dispatch('getInformation')

        // only refresh the rest of the stuff when the route matches
        if (rootState.route.name === routes.ROUTE_RMATCHES) {
          // console.log('fetching rmatches')
          dispatch('getRunningMatches')
        } else if (rootState.route.name === routes.ROUTE_PMATCHES) {
          // console.log('fetching pmatches')
          dispatch('getPastMatches')
        } else if (rootState.route.name === routes.ROUTE_PLAYERS) {
          // console.log('fetching players')
          dispatch('getAllPlayers')
        } else if (rootState.route.name === routes.ROUTE_SETTINGS) {
          // console.log('fetching settings')
          // todo
        } else if (rootState.route.name === routes.ROUTE_RULES) {
          // console.log('fetching rules')
          // todo
        }

        // wait a bit, then dispatch poll again
        await waitMs(30000)

        dispatch('poll')
      },
      async getLoggedInPlayers ({commit}) {
        commit('setLoggedInPlayers', await api.loggedInPlayers())
      },
      async getAllPlayers ({commit}) {
        commit('setAllPlayers', await api.allPlayers())
      },
      async getRunningMatches ({commit}) {
        commit('setRunningMatches', await api.runningMatches())
      },
      async getPastMatches ({commit}) {
        commit('setPastMatches', await api.pastMatches())
      },
      async login ({commit, dispatch}) {
        await api.login()
        await dispatch('getLoggedInPlayers')
      },
      async logout ({commit, dispatch}) {
        await api.logout()
        await dispatch('getLoggedInPlayers')
      },
      async drawPreview ({commit}) {
        commit('showDrawPreview', await api.drawPreview())
      },
      async drawConfirm ({commit, dispatch}) {
        commit('hideDrawPreview', await api.drawConfirm())
        dispatch('getRunningMatches')
        dispatch('getLoggedInPlayers')
      },
      async drawCancel ({commit}) {
        commit('hideDrawPreview', await api.drawCancel())
      },
      async postMatchResult ({commit, dispatch}, {matchId, winner}) {
        await api.postMatchResult(matchId, winner)
        await dispatch('getRunningMatches')
      },
      async bet ({commit, dispatch}, {matchId, team}) {
        await api.bet(matchId, team)
        await dispatch('getRunningMatches')
      },
      async getInformation ({commit}) {
        commit('setInformation', await api.getInformation())
      },
      async nextInformation ({state, commit}) {
        commit('increaseInformationIndex')
      },
      toggleLanguage ({state, commit}) {
        const lang = state.i18n.locale === 'en' ? 'de' : 'en'
        api.setLang(lang) // async language set
        commit('setLang', lang)
      }
    }
  })
}
