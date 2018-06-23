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
        u_zone_login: false,
        u_zone_draw: false,
        u_zone_view_login: false,
        m_zone_draw_match: false,
        m_zone_login_players: false
      }
    },
    drawPreview: {
      visible: false,
      players: []
    },
    loggedInPlayers: [],
    allPlayers: [],
    matches: [],
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
    allPlayers: (s) => s.allPlayers,
    me: (s) => s.me,
    loggedInPlayers: (s) => s.loggedInPlayers,
    loggedInUserIds: (s) => s.loggedInPlayers.map(u => u.id),
    canDraw: (s, g) => {
      if (!s.me.permissions.u_zone_view_login) {
        return false
      }
      if (s.loggedInPlayers.length <= 1) {
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
    matchById: (s) => (id) => s.matches.find(m => m.id === id)
  },
  mutations: {
    setMe (state, payload) {
      state.me.id = payload.id || 0
      state.me.permissions.u_zone_draw = payload.permissions.u_zone_draw || false
      state.me.permissions.u_zone_login = payload.permissions.u_zone_login || false
      state.me.permissions.u_zone_view_login = payload.permissions.u_zone_view_login || false
      state.me.permissions.m_zone_draw_match = payload.permissions.m_zone_draw_match || false
      state.me.permissions.m_zone_login_players = payload.permissions.m_zone_login_players || false
    },
    setLoggedInPlayers (state, payload) {
      state.loggedInPlayers = payload
    },
    setAllPlayers (state, payload) {
      state.allPlayers = payload
    },
    setRunningMatches (state, payload) {
      state.matches = mergeMatches(payload, state.matches)
    },
    setPastMatches (state, payload) {
      state.matches = mergeMatches(payload, state.matches)
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
    }
  },
  actions: {
    async init ({ commit, dispatch }) {
      commit('setMe', await api.me())
      dispatch('getLoggedInPlayers')
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
      await dispatch('getRunningMatches')
      await dispatch('getLoggedInPlayers')
    },
    async drawCancel ({ commit }) {
      commit('hideDrawPreview', await api.drawCancel())
    },
    async postMatchResult ({ commit, dispatch }, {matchId, winner}) {
      await api.postMatchResult(matchId, winner)
      commit('setMatch', await api.match(matchId))
    }
  }
})
