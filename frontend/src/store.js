import Vue from 'vue'
import Vuex from 'vuex'
import View from '@/view'

import acl from '@/acl'
import * as api from './api'
import * as timer from './timer'
import * as routes from './routes'
import { assign, sort } from '@/functions'

const overlayRouting = {
  ERROR: {
    name: 'ERROR',
    component: View.Components.Error,
    props: {
      message: ''
    }
  },
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
  },
  MAP_DESCRIPTION: {
    name: 'MAP_DESCRIPTION',
    component: View.Components.MapDescriptionOverlay,
    props: {
      map: null
    }
  }
}

export default () => {
  Vue.use(Vuex)

  return new Vuex.Store({
    state: {
      me: {
        id: 0,
        title: '',
        sid: '',
        permissions: [],
        settings: []
      },
      overlay: {
        name: false,
        payload: {
          [overlayRouting.ADD_PAIR_PREVIEW.name]: {},
          [overlayRouting.DRAW_PREVIEW.name]: {},
          [overlayRouting.PLAYER_DETAILS.name]: {},
          [overlayRouting.REPLACE_PREVIEW.name]: {},
          [overlayRouting.MAP_DESCRIPTION.name]: {}
        }
      },
      sort: {
        field: '',
        order: -1
      },
      match: null, // single match
      drawBlockedTime: 0,
      players: [],
      statistics: [],
      bets: [],
      maps: [],
      mapVetos: [],
      mapCivInfo: {},
      runningMatches: [],
      pastMatches: {
        items: [],
        total_pages: 0,
        page: 0
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
      can: (s) => (permission) => acl.can(s.me, permission),
      overlayComponent: (s) => (overlayRouting[s.overlay.name] || {}).component || null,
      overlayPayload: s => s.overlay.payload[s.overlay.name],
      players: (s) => sort(s.players, s.sort),
      maps: (s, g) => sort(g.mapsWithCivInfo, s.sort),
      mapVetos: (s) => s.mapVetos,
      mapsWithCivInfo: (s) => s.maps.map(map => Object.assign({}, map, { civInfo: s.mapCivInfo[map.id] })),
      bets: (s, g) => sort(g.betsEnriched, s.sort),
      betsEnriched: (s) => s.bets.map(bet => Object.assign({}, bet, {
        bets_total: bet.bets_won + bet.bets_loss,
        bet_quota: bet.bets_won / (bet.bets_won + bet.bets_loss) * 100
      })),
      me: (s) => s.me,
      isGuest: (s) => s.me.id === 1,
      loggedInPlayers: (s) => s.players.filter(p => p.logged_in > 0).sort((a, b) => {
        if (a.logged_in === b.logged_in) {
          return a.rating > b.rating ? -1 : 1
        }
        return a.logged_in > b.logged_in ? 1 : -1
      }),
      loggedInUserIds: (s) => s.players.filter(p => p.logged_in > 0).map(u => u.id),
      drawBlockedTime: (s) => s.drawBlockedTime,
      canBlockDraw: (s, g) => g.can(acl.permissions.m_zone_block_draw),
      canDraw: (s, g) => {
        if (!g.can(acl.permissions.u_zone_view_login)) {
          return false
        }
        if (g.loggedInUserIds.length <= 1) {
          return false
        }
        if (g.loggedInUserIds.includes(s.me.id) && g.can(acl.permissions.u_zone_draw)) {
          return true
        }
        return g.can(acl.permissions.m_zone_draw_match)
      },
      canReplaceMod: (s, g) => {
        if (g.loggedInUserIds.length === 0) {
          return false
        }
        return g.can(acl.permissions.m_zone_change_match)
      },
      canReplaceUser: (s, g) => {
        if (g.loggedInUserIds.length === 0) {
          return false
        }
        return g.can(acl.permissions.u_zone_change_match)
      },
      canAddPairMod: (s, g) => {
        if (g.loggedInUserIds.length < 2) {
          return false
        }
        return g.can(acl.permissions.m_zone_change_match)
      },
      canAddPairUser: (s, g) => {
        if (g.loggedInUserIds.length < 2) {
          return false
        }
        return g.can(acl.permissions.u_zone_change_match)
      },
      canModPost: (s, g) => g.can(acl.permissions.m_zone_draw_match),
      canModLogin: (s, g) => g.can(acl.permissions.m_zone_login_players),
      canViewLogin: (s, g) => g.can(acl.permissions.u_zone_view_login),
      canLogin: (s, g) => g.can(acl.permissions.u_zone_view_login) && g.can(acl.permissions.u_zone_login) && !g.isLoggedIn && !g.isPlaying,
      canViewMatches: (s, g) => g.can(acl.permissions.u_zone_view_matches),
      canViewMaps: (s, g) => g.can(acl.permissions.u_zone_view_maps),
      canViewBets: (s, g) => g.can(acl.permissions.u_zone_view_bets),
      canEditMapDescription: (s, g) => g.can(acl.permissions.u_zone_view_maps) && g.can(acl.permissions.m_zone_manage_maps),
      isLoggedIn: (s, g) => g.loggedInUserIds.includes(s.me.id),
      isPlaying: (s, g) => !!g.runningMatches.find(m => m.players.team1.find(p => p.id === s.me.id) || m.players.team2.find(p => p.id === s.me.id)),
      runningMatches: (s) => s.runningMatches,
      pastMatches: (s) => s.pastMatches,
      info: (s) => s.information.items[s.information.index] || '',
      rules: (s) => s.rulesPost,
      informationIndex: (s) => s.information.index,
      playerById: (s) => (id) => s.players.find(p => p.id === id),
      match: (s) => s.match,
      mode: (s) => s.match ? 'single' : 'default',
      isSingle: (s, g) => g.mode === 'single',
      timer: (s) => s.timer,
      sort: (s) => s.sort
    },
    mutations: {
      init (state, { me, i18n }) {
        state.me.id = me.id || 0
        state.me.title = me.title || 'Zone'
        state.me.sid = me.sid || ''
        state.me.permissions = me.permissions

        state.i18n = i18n
        state.i18n.locale = me.lang

        api.setSid(state.me.sid)
      },
      setSettings (state, { settings }) {
        state.me.settings = {
          view_mchat: settings.view_mchat === '1',
          auto_logout: parseInt(settings.auto_logout)
        }
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
      setDrawBlockedTime (state, payload) {
        state.drawBlockedTime = payload['draw_blocked_until']
      },
      setStatistics (state, payload) {
        state.statistics = payload
      },
      setBets (state, payload) {
        state.bets = payload
      },
      setMaps (state, payload) {
        state.maps = payload
      },
      setMapVetos (state, payload) {
        state.mapVetos = payload
      },
      setMapCivInfo (state, { mapId, civInfo }) {
        state.mapCivInfo = Object.assign({}, state.mapCivInfo, { [mapId]: civInfo })
      },
      setMatch (state, payload) {
        state.match = payload
      },
      setRunningMatches (state, payload) {
        state.runningMatches = payload
      },
      setPastMatches (state, payload) {
        state.pastMatches.items = payload.items
        state.pastMatches.total_pages = payload.total_pages
      },
      setPastMatchesPage (state, page) {
        state.pastMatches.page = page
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
      },
      setSort (state, { field, order }) {
        if (state.sort.field !== field) {
          state.sort.field = field
        } else {
          state.sort.order *= -1
        }
        if (order === -1 || order === 1) {
          state.sort.order = order
        }
      }
    },
    actions: {
      async init ({ rootState, state, commit, dispatch }, payload) {
        if (payload.matchId) {
          commit('setMatch', await api.passively.getMatch(payload.matchId))
          commit('init', { me: payload.me, i18n: payload.i18n })
        } else {
          commit('init', {
            me: payload.me,
            i18n: payload.i18n
          })
          commit('setSettings', { settings: payload.me.settings })

          dispatch('getInformation', { passive: true })
          dispatch('getLoggedInPlayers', { passive: true })
          dispatch('loadCurrentRouteData', { passive: true })
          dispatch('getDrawBlockedTime')

          dispatch('poll')
        }
      },

      async loadSettings ({ commit }) {
        const settings = await api.passively.getMeSettings()
        commit('setSettings', { settings })
      },

      async saveSettings ({ state, commit }, newSettings) {
        const settings = await api.actively.setMeSettings({
          view_mchat: newSettings.view_mchat ? '1' : '0',
          auto_logout: parseInt(newSettings.auto_logout)
        })
        commit('setSettings', { settings })
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
        state.timer.every(10, () => {
          dispatch('getDrawBlockedTime')
        })
      },

      async loadCurrentRouteData ({ rootState, dispatch, state }) {
        // only refresh the rest of the stuff when the route matches
        if (rootState.route.name === routes.ROUTE_RMATCHES) {
          // console.log('fetching rmatches')
          dispatch('getRunningMatches', { passive: true })
        } else if (rootState.route.name === routes.ROUTE_PMATCHES) {
          // console.log('fetching pmatches')
          dispatch('getPastMatches', { passive: true, page: state.pastMatches.page })
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

      async getDrawBlockedTime ({ commit }) {
        commit('setDrawBlockedTime', await api.passively.getDrawBlockedTime())
      },

      async drawBlock ({ commit }) {
        await api.actively.drawBlock()
        commit('setDrawBlockedTime', await api.passively.getDrawBlockedTime())
      },

      async drawUnblock ({ commit }) {
        await api.actively.drawUnblock()
        commit('setDrawBlockedTime', await api.passively.getDrawBlockedTime())
      },

      async getStatistics ({ commit }) {
        commit('setStatistics', await api.actively.getStatistcs())
      },

      async getBets ({ commit }) {
        commit('setBets', await api.actively.getBets())
      },

      async getMaps ({ commit }) {
        commit('setMaps', await api.actively.getMaps())
      },

      async getMapVetos ({ commit }) {
        commit('setMapVetos', await api.actively.getMapVetos())
      },

      async setMapVeto ({ commit }, { mapId }) {
        await api.actively.setMapVeto(mapId)
        commit('setMapVetos', await api.actively.getMapVetos())
      },

      async removeMapVeto ({ commit }, { mapId }) {
        await api.actively.removeMapVeto(mapId)
        commit('setMapVetos', await api.actively.getMapVetos())
      },

      async clearMapVeto ({ commit }) {
        await api.actively.clearMapVetos()
        commit('setMapVetos', await api.actively.getMapVetos())
      },

      async getMapInfo ({ state, commit }, { mapId }) {
        if (mapId > 0 && typeof state.mapCivInfo[mapId] === 'undefined') {
          commit('setMapCivInfo', { mapId, civInfo: await api.actively.getMapCivs(mapId) })
        }
      },

      async saveMapDescription ({ commit }, { mapId, description }) {
        await api.actively.setMapDescription(mapId, description)
        commit('setMaps', await api.actively.getMaps())
      },

      async saveMapImage ({ commit }, { mapId, image }) {
        await api.actively.setMapImage(mapId, image)
        commit('setMaps', await api.actively.getMaps())
      },

      async getRunningMatches ({ commit }, { passive }) {
        if (passive) {
          commit('setRunningMatches', await api.passively.getRunningMatches())
        } else {
          commit('setRunningMatches', await api.actively.getRunningMatches())
          commit('setMeActive')
        }
      },

      async getPastMatches ({ commit }, { passive, page }) {
        if (passive) {
          commit('setPastMatches', await api.passively.getPastMatches(page))
        } else {
          commit('setPastMatches', await api.actively.getPastMatches(page))
          commit('setMeActive')
        }
      },

      async setPastMatchesPage ({ commit }, { page }) {
        commit('setPastMatchesPage', page)
        commit('setPastMatches', await api.actively.getPastMatches(page))
      },

      async getInformation ({ commit }, { passive }) {
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

      async openAddPairPreviewOverlay ({ commit }, matchId) {
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

      async openPlayerReplacePreviewOverlay ({ commit }, userId) {
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

      async openPlayerDetailsOverlay ({ state, commit }, userId) {
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

      async openMapDescriptionOverlay ({ state, commit }, map) {
        const route = overlayRouting.MAP_DESCRIPTION
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            map: map
          })
        })
        commit('setMeActive')
      },

      async openErrorOverlay ({ state, commit }, message) {
        const route = overlayRouting.ERROR
        commit('setOverlay', {
          name: route.name,
          payload: assign(route.props, {
            message: message
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

      async addPairConfirm ({ state, commit, dispatch }, matchId) {
        await api.actively.addPairConfirm(matchId)
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
      },

      setSort ({ commit }, { field, order }) {
        commit('setSort', { field, order })
      }
    }
  })
}
