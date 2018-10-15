import Vue from 'vue'
import Vuex from 'vuex'

import * as api from './api'
import * as timer from './timer'
import * as routes from './routes'

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
      rulesPost: null,
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
      i18n: null,
      timer: timer
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
      canModLogin: (s) => s.me.permissions.m_zone_login_players,
      canLogin: (s, g) => s.me.permissions.u_zone_view_login && s.me.permissions.u_zone_login && !g.isLoggedIn && !g.isPlaying,
      isLoggedIn: (s, g) => g.loggedInUserIds.includes(s.me.id),
      isPlaying: (s, g) => !!g.runningMatches.find(m => m.players.team1.find(p => p.id === s.me.id) || m.players.team2.find(p => p.id === s.me.id)),
      runningMatches: (s) => s.runningMatches,
      pastMatches: (s) => s.pastMatches.items,
      drawPreview: (s) => s.drawPreview,
      info: (s) => s.information.items[s.information.index] || '',
      rules: (s) => s.rulesPost,
      informationIndex: (s) => s.information.index,
      playerById: (s) => (id) => s.players.find(p => p.id === id),
      match: (s) => s.match,
      timer: (s) => s.timer
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
      setMeActive (state) {
        state.players.forEach(player => {
          if (player.id === state.me.id) {
            player.last_activity = parseInt(Date.now() / 1000)
          }
        })
      },
      setLoggedInPlayers (state, payload) {
        // all players are updated to be logged in if needed
        state.players.forEach(player => {
          const loggedInPlayer = payload.find(m => m.id === player.id)
          if (loggedInPlayer) {
            player.logged_in = loggedInPlayer.logged_in
            player.last_activity = loggedInPlayer.last_activity
            player.rating = loggedInPlayer.rating
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
      setRules (state, payload) {
        state.rulesPost = payload.post
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
      async init ({rootState, state, commit, dispatch}, payload) {
        if (payload.matchId) {
          commit('setMatch', await api.passively.getMatch(payload.matchId))
          commit('init', {me: payload.me, i18n: payload.i18n})
        } else {
          commit('init', {me: payload.me, i18n: payload.i18n})

          dispatch('getInformation', {passive: true})
          dispatch('getLoggedInPlayers', {passive: true})
          dispatch('loadCurrentRouteData', {passive: true})

          dispatch('poll')
        }
      },

      async poll ({dispatch, state}) {
        state.timer.start()
        state.timer.every(60, () => {
          // always fetch information
          dispatch('getInformation', {passive: true})
        })
        state.timer.every(10, () => {
          // always refresh logged in players
          dispatch('getLoggedInPlayers', {passive: true})
        })
        state.timer.every(30, () => {
          dispatch('loadCurrentRouteData')
        })
      },

      async loadCurrentRouteData ({rootState, dispatch}) {
        // only refresh the rest of the stuff when the route matches
        if (rootState.route.name === routes.ROUTE_RMATCHES) {
          // console.log('fetching rmatches')
          dispatch('getRunningMatches', {passive: true})
        } else if (rootState.route.name === routes.ROUTE_PMATCHES) {
          // console.log('fetching pmatches')
          dispatch('getPastMatches', {passive: true})
        } else if (rootState.route.name === routes.ROUTE_PLAYERS) {
          // console.log('fetching players')
          dispatch('getAllPlayers', {passive: true})
        } else if (rootState.route.name === routes.ROUTE_SETTINGS) {
          // console.log('fetching settings')
          // todo
        } else if (rootState.route.name === routes.ROUTE_RULES) {
          // console.log('fetching rules')
          // todo
        }
      },

      async getLoggedInPlayers ({commit, dispatch}, {passive}) {
        if (passive) {
          commit('setLoggedInPlayers', await api.passively.getLoggedInPlayers())
        } else {
          commit('setLoggedInPlayers', await api.actively.getLoggedInPlayers())
        }
      },

      async getAllPlayers ({commit, dispatch}, {passive}) {
        if (passive) {
          commit('setAllPlayers', await api.passively.getAllPlayers())
        } else {
          commit('setAllPlayers', await api.actively.getAllPlayers())
          commit('setMeActive')
        }
      },

      async getRunningMatches ({commit, dispatch}, {passive}) {
        if (passive) {
          commit('setRunningMatches', await api.passively.getRunningMatches())
        } else {
          commit('setRunningMatches', await api.actively.getRunningMatches())
          commit('setMeActive')
        }
      },

      async getPastMatches ({commit, dispatch}, {passive}) {
        if (passive) {
          commit('setPastMatches', await api.passively.getPastMatches())
        } else {
          commit('setPastMatches', await api.actively.getPastMatches())
          commit('setMeActive')
        }
      },

      async getInformation ({commit, dispatch}, {passive}) {
        if (passive) {
          commit('setInformation', await api.passively.getInformation())
        } else {
          commit('setInformation', await api.actively.getInformation())
          commit('setMeActive')
        }
      },

      async getRules ({commit}) {
        commit('setRules', await api.actively.getRules())
        commit('setMeActive')
      },

      async login ({commit, dispatch}) {
        await api.actively.doLogin()
        await dispatch('getLoggedInPlayers', {passive: true})
      },

      async logout ({commit, dispatch}) {
        await api.actively.doLogout()
        await dispatch('getLoggedInPlayers', {passive: true})
      },

      async logoutPlayer ({commit, dispatch}, {userId}) {
        await api.actively.doLogoutPlayer(userId)
        await dispatch('getLoggedInPlayers', {passive: true})
      },

      async drawPreview ({commit}) {
        commit('showDrawPreview', await api.actively.drawPreview())
      },

      async drawConfirm ({commit, dispatch}) {
        commit('hideDrawPreview', await api.actively.drawConfirm())
        dispatch('getRunningMatches', {passive: true})
        dispatch('getLoggedInPlayers', {passive: true})
      },

      async drawCancel ({commit}) {
        commit('hideDrawPreview', await api.actively.drawCancel())
      },

      async postMatchResult ({commit, dispatch}, {matchId, winner}) {
        await api.actively.postMatchResult(matchId, winner)
        await dispatch('getRunningMatches', {passive: true})
      },

      async bet ({commit, dispatch}, {matchId, team}) {
        await api.actively.placeBet(matchId, team)
        await dispatch('getRunningMatches', {passive: true})
      },

      async nextInformation ({state, commit}) {
        commit('increaseInformationIndex')
      },

      toggleLanguage ({state, commit}) {
        const lang = state.i18n.locale === 'en' ? 'de' : 'en'
        api.actively.setLang(lang) // async language set
        commit('setLang', lang)
      }
    }
  })
}
