import Vue from 'vue'
import Vuex from 'vuex'

import * as api from './api'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    me: {
      id: 0,
      canLogin: false,
      canDraw: false,
      canViewLogin: false
    },
    loggedInPlayers: [],
    allPlayers: [],
    runningMatches: [],
    pastMatches: [],
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
    loggedInPlayers: (s) => s.loggedInPlayers,
    me: (s) => s.me,
    userIds: (s) => s.loggedInPlayers.map(u => u.id),
    canDraw: (s) => s.me.canViewLogin && s.me.canDraw,
    canLogin: (s, g) => s.me.canLogin && !g.canLogout,
    canLogout: (s, g) => g.userIds.includes(s.me.id),
    runningMatches: (s) => s.runningMatches,
    pastMatches: (s) => s.pastMatches,
    matchById: (s) => (id) => s.matches.find(m => m.id === id)
  },
  mutations: {
    setMe (state, payload) {
      state.me.id = payload.id || 0
      state.me.canDraw = payload.canDraw || false
      state.me.canLogin = payload.canLogin || false
      state.me.canViewLogin = payload.canViewLogin || false
    },
    setLoggedInPlayers (state, payload) {
      state.loggedInPlayers = payload
    },
    setAllPlayers (state, payload) {
      state.allPlayers = payload
    },
    setMatches (state, payload) {
      state.matches = payload
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
      commit('setMatches', await api.runningMatches())
    },
    async getPastMatches ({ commit }) {
      commit('setMatches', await api.pastMatches())
    },
    async login ({ commit, dispatch }) {
      await api.login()
      dispatch('getLoggedInPlayers')
    },
    async logout ({ commit, dispatch }) {
      await api.logout()
      dispatch('getLoggedInPlayers')
    }
  }
})
