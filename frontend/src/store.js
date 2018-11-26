import Vue from 'vue'
import Vuex from 'vuex'
import View from '@/view'

import * as api from './api'
import * as timer from './timer'
import * as routes from './routes'
import { assign } from '@/functions'

const overlayRouting = {
  ADD_PAIR_PREVIEW: {
    name: 'ADD_PAIR_PREVIEW',
    component: View.Components.AddPairPreview,
    props: {
      matchId: null,
      player1: null,
      player2: null
    }
  },
  DRAW_PREVIEW: {
    name: 'DRAW_PREVIEW',
    component: View.Components.DrawPreview,
    props: {
      players: []
    }
  },
  PLAYER_DETAILS: {
    name: 'PLAYER_DETAILS',
    component: View.Components.PlayerDetails,
    props: {
      player: null,
      ratingData: [],
      details: [],
      dreamteams: [],
      nightmareteams: []
    }
  },
  REPLACE_PREVIEW: {
    name: 'REPLACE_PREVIEW',
    component: View.Components.ReplacePreview,
    props: {
      replacePlayer: null,
      replaceByPlayer: null
    }
  }
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
      overlay: {
        name: false,
        payload: {
          [overlayRouting.ADD_PAIR_PREVIEW.name]: {},
          [overlayRouting.DRAW_PREVIEW.name]: {},
          [overlayRouting.PLAYER_DETAILS.name]: {},
          [overlayRouting.REPLACE_PREVIEW.name]: {}
        }
      },
      match: null, // single match
      players: [],
      statistics: [],
      bets: [],
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
      i18n: null,
      timer: timer
    },
    getters: {
      overlayComponent: (s) => (overlayRouting[s.overlay.name] || {}).component || null,
      overlayPayload: s => s.overlay.payload[s.overlay.name],
      players: (s) => s.players,
      bets: (s) => s.bets,
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
      canReplace: (s, g) => {
        if (g.loggedInUserIds.length === 0) {
          return false
        }
        return s.me.permissions.m_zone_change_match
      },
      canAddPair: (s, g) => {
        if (g.loggedInUserIds.length < 2) {
          return false
        }
        return s.me.permissions.m_zone_change_match
      },
      canModPost: (s) => s.me.permissions.m_zone_draw_match,
      canModLogin: (s) => s.me.permissions.m_zone_login_players,
      canLogin: (s, g) => s.me.permissions.u_zone_view_login && s.me.permissions.u_zone_login && !g.isLoggedIn && !g.isPlaying,
      isLoggedIn: (s, g) => g.loggedInUserIds.includes(s.me.id),
      isPlaying: (s, g) => !!g.runningMatches.find(m => m.players.team1.find(p => p.id === s.me.id) || m.players.team2.find(p => p.id === s.me.id)),
      runningMatches: (s) => s.runningMatches,
      pastMatches: (s) => s.pastMatches.items,
      info: (s) => s.information.items[s.information.index] || '',
      rules: (s) => s.rulesPost,
      informationIndex: (s) => s.information.index,
      playerById: (s) => (id) => s.players.find(p => p.id === id),
      match: (s) => s.match,
      timer: (s) => s.timer
    },
    mutations: {
      init (state, { me, i18n }) {
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
      setStatistics (state, payload) {
        state.statistics = payload
      },
      setBets (state, payload) {
        state.bets = payload
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
      setRatingData (state, payload) {
        state.playerDetails.ratingData = payload
      },
      setPlayerDetails (state, payload) {
        state.playerDetails.details = payload
      },
      setDreamteams (state, payload) {
        state.playerDetails.dreamteams = payload
      },
      setNightmareteams (state, payload) {
        state.playerDetails.nightmareteams = payload
      },
      setRules (state, payload) {
        state.rulesPost = payload.post
      },
      increaseInformationIndex (state) {
        state.information.index += 1
        if (state.information.index > state.information.items.length - 1) {
          state.information.index = 0
        }
      },
      setOverlay (state, { name, payload }) {
        if (state.overlay.name && !name) {
          // todo: reset instead of setting empty object
          state.overlay.payload[state.overlay.name] = {}
        }
        state.overlay.name = name
        if (name) {
          state.overlay.payload[name] = payload
        }
      }
    },
    actions: {
      async init ({ rootState, state, commit, dispatch }, payload) {
        if (payload.matchId) {
          commit('setMatch', await api.passively.getMatch(payload.matchId))
          commit('init', { me: payload.me, i18n: payload.i18n })
        } else {
          commit('init', { me: payload.me, i18n: payload.i18n })

          dispatch('getInformation', { passive: true })
          dispatch('getLoggedInPlayers', { passive: true })
          dispatch('loadCurrentRouteData', { passive: true })

          dispatch('poll')
        }
      },

      async poll ({ dispatch, state }) {
        state.timer.start()
        state.timer.every(60, () => {
          // always fetch information
          dispatch('getInformation', { passive: true })
        })
        state.timer.every(5, () => {
          // always refresh logged in players
          dispatch('getLoggedInPlayers', { passive: true })
        })
        state.timer.every(15, () => {
          dispatch('loadCurrentRouteData')
        })
      },

      async loadCurrentRouteData ({ rootState, dispatch }) {
        // only refresh the rest of the stuff when the route matches
        if (rootState.route.name === routes.ROUTE_RMATCHES) {
          // console.log('fetching rmatches')
          dispatch('getRunningMatches', { passive: true })
        } else if (rootState.route.name === routes.ROUTE_PMATCHES) {
          // console.log('fetching pmatches')
          dispatch('getPastMatches', { passive: true })
        } else if (rootState.route.name === routes.ROUTE_PLAYERS) {
          // console.log('fetching players')
          dispatch('getAllPlayers', { passive: true })
        } else if (rootState.route.name === routes.ROUTE_SETTINGS) {
          // console.log('fetching settings')
          // todo
        } else if (rootState.route.name === routes.ROUTE_RULES) {
          // console.log('fetching rules')
          // todo
        }
      },

      async getLoggedInPlayers ({ commit, dispatch }, { passive }) {
        if (passive) {
          commit('setLoggedInPlayers', await api.passively.getLoggedInPlayers())
        } else {
          commit('setLoggedInPlayers', await api.actively.getLoggedInPlayers())
        }
      },

      async getAllPlayers ({ commit, dispatch }, { passive }) {
        if (passive) {
          commit('setAllPlayers', await api.passively.getAllPlayers())
        } else {
          commit('setAllPlayers', await api.actively.getAllPlayers())
          commit('setMeActive')
        }
      },

      async getStatistics ({ commit }) {
        commit('setStatistics', await api.actively.getStatistcs())
      },

      async getBets ({ commit }) {
        commit('setBets', await api.actively.getBets())
      },

      async getRunningMatches ({ commit, dispatch }, { passive }) {
        if (passive) {
          commit('setRunningMatches', await api.passively.getRunningMatches())
        } else {
          commit('setRunningMatches', await api.actively.getRunningMatches())
          commit('setMeActive')
        }
      },

      async getPastMatches ({ commit, dispatch }, { passive }) {
        if (passive) {
          commit('setPastMatches', await api.passively.getPastMatches())
        } else {
          commit('setPastMatches', await api.actively.getPastMatches())
          commit('setMeActive')
        }
      },

      async getInformation ({ commit, dispatch }, { passive }) {
        if (passive) {
          commit('setInformation', await api.passively.getInformation())
        } else {
          commit('setInformation', await api.actively.getInformation())
          commit('setMeActive')
        }
      },

      closeOverlay ({ commit }) {
        commit('setOverlay', { name: false })
        commit('setMeActive')
      },

      async openAddPairPreviewOverlay ({ commit, dispatch }, matchId) {
        const payload = await api.actively.addPairPreview(matchId)
        const route = overlayRouting.ADD_PAIR_PREVIEW
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            matchId: matchId,
            player1: payload.add_player1,
            player2: payload.add_player2
          })
        })
        commit('setMeActive')
      },

      async openPlayerReplacePreviewOverlay ({ commit, dispatch }, userId) {
        const payload = await api.actively.replacePreview(userId)
        const route = overlayRouting.REPLACE_PREVIEW
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            replacePlayer: payload.replace_player,
            replaceByPlayer: payload.replace_by_player
          })
        })
        commit('setMeActive')
      },

      async openPlayerDetailsOverlay ({ state, commit, dispatch }, userId) {
        const route = overlayRouting.PLAYER_DETAILS
        const playerDetails = await api.actively.getPlayerDetails(userId)
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            player: playerDetails.player,
            ratingData: playerDetails.ratingData,
            details: playerDetails.details,
            dreamteams: playerDetails.dreamteams,
            nightmareteams: playerDetails.nightmareteams
          })
        })
        commit('setMeActive')
      },

      async openDrawPreviewOverlay ({ commit }) {
        const route = overlayRouting.DRAW_PREVIEW
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            players: await api.actively.drawPreview()
          })
        })
        commit('setMeActive')
      },

      async getRules ({ commit }) {
        commit('setRules', await api.actively.getRules())
        commit('setMeActive')
      },

      async login ({ commit, dispatch }) {
        await api.actively.doLogin()
        await dispatch('getLoggedInPlayers', { passive: true })
      },

      async logout ({ commit, dispatch }) {
        await api.actively.doLogout()
        await dispatch('getLoggedInPlayers', { passive: true })
      },

      async loginPlayer ({ commit, dispatch }, { userId }) {
        await api.actively.doLoginPlayer(userId)
        await dispatch('getLoggedInPlayers', { passive: true })
      },

      async logoutPlayer ({ commit, dispatch }, { userId }) {
        await api.actively.doLogoutPlayer(userId)
        await dispatch('getLoggedInPlayers', { passive: true })
      },

      async drawConfirm ({ commit, dispatch }) {
        await api.actively.drawConfirm()
        dispatch('closeOverlay')
        dispatch('getRunningMatches', { passive: true })
        dispatch('getLoggedInPlayers', { passive: true })
      },

      async drawCancel ({ dispatch }) {
        await api.actively.drawCancel()
        dispatch('closeOverlay')
      },

      async replaceConfirm ({ commit, dispatch }, { userId }) {
        await api.actively.replaceConfirm(userId)
        dispatch('closeOverlay')
        dispatch('getRunningMatches', { passive: true })
        dispatch('getLoggedInPlayers', { passive: true })
      },

      async replaceCancel ({ dispatch }) {
        await api.actively.replaceCancel()
        dispatch('closeOverlay')
      },

      async addPairConfirm ({ state, commit, dispatch }) {
        await api.actively.addPairConfirm(state.addPairPreview.matchId)
        dispatch('closeOverlay')
        dispatch('getRunningMatches', { passive: true })
        dispatch('getLoggedInPlayers', { passive: true })
      },

      async addPairCancel ({ dispatch }) {
        await api.actively.addPairCancel()
        dispatch('closeOverlay')
      },

      async postMatchResult ({ commit, dispatch }, { matchId, winner }) {
        await api.actively.postMatchResult(matchId, winner)
        await dispatch('getRunningMatches', { passive: true })
      },

      async bet ({ commit, dispatch }, { matchId, team }) {
        await api.actively.placeBet(matchId, team)
        await dispatch('getRunningMatches', { passive: true })
      },

      async nextInformation ({ state, commit }) {
        commit('increaseInformationIndex')
      },

      toggleLanguage ({ state, commit }) {
        const lang = state.i18n.locale === 'en' ? 'de' : 'en'
        api.actively.setLang(lang) // async language set
        commit('setLang', lang)
      }
    }
  })
}
