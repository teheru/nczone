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
      canViewLogin: false,
    },
    loggedInUsers: [],
    matches: [],
  },
  getters: {
    users: (s) => s.loggedInUsers,
    me: (s) => s.me,
    userIds: (s) => s.loggedInUsers.map(u => u.id),
    canDraw: (s) => s.me.canViewLogin && s.me.canDraw,
    canLogin: (s, g) => s.me.canLogin && !g.canLogout,
    canLogout: (s, g) => g.userIds.includes(s.me.id),
    matches: (s) => s.matches,
    matchById: (s) => (id) => s.matches.find(m => m.id === id),
  },
  mutations: {
    setMe(state, payload) {
      state.me.id = payload.id || 0
      state.me.canDraw = payload.canDraw || false
      state.me.canLogin = payload.canLogin || false
      state.me.canViewLogin = payload.canViewLogin || false
    },
    setLoggedInUsers(state, payload) {
      state.loggedInUsers = payload
    },
    setMatches(state, payload) {
      state.matches = payload
    }
  },
  actions: {
    async initMe ({ commit }) {
      commit('setMe', await api.me())
    },
    async getLoggedInUsers ({ commit }) {
      commit('setLoggedInUsers', await api.loggedInUsers())
    },
    async getRunningMatches ({ commit }) {
      commit('setMatches', await api.runningMatches())
    },
    async getPastMatches ({ commit }) {
      commit('setMatches', await api.pastMatches())
    },
    async login ({ commit, dispatch }) {
      await api.login()
      dispatch('getLoggedInUsers')
    },
    async logout ({ commit, dispatch }) {
      await api.logout()
      dispatch('getLoggedInUsers')
    },
  }
})
