import Vue from 'vue'
import Vuex from 'vuex'

import * as api from './api'

Vue.use(Vuex)

const mergeMatches = (m, m2) => {
  const matches = []
  m.forEach(match => {
    matches.push(match)
  })
  m2.forEach(match => {
    if (!matches.find(m => m.id === match.id)) {
      matches.push(match)
    }
  })
  return matches
}

export default new Vuex.Store({
  state: {
    me: {
      id: 0,
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
    players: [],
    matches: [],
    information: [],
    informationIndex: 0,
    // idea: to reduce number of ajax calls, we save timestamps
    //       when certain actions were executed last and only
    //       make the ajax call if there is no data or timestamp
    //       far enough in the past
    actionTimestamps: {
      getPastMatches: 0,
      getRunningMatches: 0,
      getAllPlayers: 0,
      getLoggedInPlayers: 0
    }
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
    canLogin: (s, g) => s.me.permissions.u_zone_view_login && s.me.permissions.u_zone_login && !g.isLoggedIn,
    isLoggedIn: (s, g) => g.loggedInUserIds.includes(s.me.id),
    runningMatches: (s) => s.matches.filter(m => m.timestampEnd === 0),
    pastMatches: (s) => s.matches.filter(m => m.timestampEnd > 0),
    drawPreview: (s) => s.drawPreview,
    matchById: (s) => (id) => s.matches.find(m => m.id === id),
    info: (s) => s.information[s.informationIndex] || '',
    informationIndex: (s) => s.informationIndex,
    playerById: (s) => (id) => s.players.find(p => p.id === id)
  },
  mutations: {
    setMe (state, payload) {
      state.me.id = payload.id || 0
      state.me.permissions.u_zone_view_login = payload.permissions.u_zone_view_login || false
      state.me.permissions.u_zone_view_info = payload.permissions.u_zone_view_info || false
      state.me.permissions.u_zone_draw = payload.permissions.u_zone_draw || false
      state.me.permissions.u_zone_login = payload.permissions.u_zone_login || false
      state.me.permissions.u_zone_change_match = payload.permissions.u_zone_change_match || false
      state.me.permissions.m_zone_draw_match = payload.permissions.m_zone_draw_match || false
      state.me.permissions.m_zone_login_players = payload.permissions.m_zone_login_players || false
      state.me.permissions.m_zone_change_match = payload.permissions.m_zone_change_match || false
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
    setRunningMatches (state, payload) {
      state.matches = mergeMatches(payload, state.matches)
    },
    setPastMatches (state, payload) {
      state.matches = mergeMatches(payload, state.matches)
    },
    setInformation (state, payload) {
      state.information = payload
      state.informationIndex = 0
    },
    setMatch (state, payload) {
      state.matches = mergeMatches(payload ? [payload] : [], state.matches)
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
      state.informationIndex += 1
      if (state.informationIndex > state.information.length - 1) {
        state.informationIndex = 0
      }
    }
  },
  actions: {
    async init ({ state, commit, dispatch }) {
      commit('setMe', await api.me())
      dispatch('getLoggedInPlayers')
      dispatch('getInformation')
    },
    async getLoggedInPlayers ({ commit }) {
      commit('setLoggedInPlayers', await api.loggedInPlayers())
    },
    async getAllPlayers ({ commit }) {
      commit('setAllPlayers', await api.allPlayers())
    },
    async getRunningMatches ({ commit }) {
      commit('setRunningMatches', await api.runningMatches())
    },
    async getPastMatches ({ commit }) {
      commit('setPastMatches', await api.pastMatches())
    },
    async login ({ commit, dispatch }) {
      await api.login()
      await dispatch('getLoggedInPlayers')
    },
    async logout ({ commit, dispatch }) {
      await api.logout()
      await dispatch('getLoggedInPlayers')
    },
    async drawPreview ({ commit }) {
      commit('showDrawPreview', await api.drawPreview())
    },
    async drawConfirm ({ commit, dispatch }) {
      commit('hideDrawPreview', await api.drawConfirm())
      dispatch('getRunningMatches')
      dispatch('getLoggedInPlayers')
    },
    async drawCancel ({ commit }) {
      commit('hideDrawPreview', await api.drawCancel())
    },
    async postMatchResult ({ commit, dispatch }, {matchId, winner}) {
      await api.postMatchResult(matchId, winner)
      commit('setMatch', await api.match(matchId))
    },
    async getInformation ({ commit }) {
      commit('setInformation', await api.getInformation())
    },
    async nextInformation ({ state, commit }) {
      commit('increaseInformationIndex')
    }
  }
})
