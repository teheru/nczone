/* eslint-disable no-unused-vars */
const apiBase = process.env.VUE_APP_API_BASE
const xhrTimeoutMs = 30000

let sid = ''

const _request = (url, opts = {}, onProgress) => {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest()
    xhr.open(opts.method || 'GET', url, true)
    xhr.timeout = xhrTimeoutMs
    xhr.withCredentials = true
    for (let k in opts.headers || {}) {
      xhr.setRequestHeader(k, opts.headers[k])
    }
    xhr.onload = e => {
      resolve({ status: e.target.status, text: e.target.responseText })
    }
    xhr.onerror = reject
    if (xhr.upload && onProgress) {
      xhr.upload.onprogress = onProgress // event.loaded / event.total * 100 ; //event.lengthComputable
    }
    xhr.send(opts.body)
  })
}

/**
 * Wrapper for api calls
 * @param method
 * @param path
 * @param options
 * @param onProgress function that is called when request progresses
 */
const request = (method, path, options, onProgress) => {
  const defaultOptions = {
    // credentials: 'include',
    method: method,
    headers: {}
  }
  return _request(url(path), Object.assign(defaultOptions, options), onProgress)
    .then(response => {
      if (response.status === 401) {
        throw new Error('Unauthorized')
      }
      if (response.status === 204) {
        return {}
      }
      if (response.status === 501) {
        throw new Error('Not implemented')
      }
      return JSON.parse(response.text) // .json()
    })
    .then(response => {
      if (response.error) {
        throw new Error(response.error)
      }
      return response
    })
    .catch(error => {
      throw new Error(error.message)
    })
}

const _actively = (method, path, options, onProgress) => {
  return request(method, path, Object.assign(options || {}, { headers: { 'X-Update-Session': '1' } }), onProgress)
}
const _passively = (...params) => request(...params)

const put = (...params) => _actively('PUT', ...params)
const post = (...params) => _actively('POST', ...params)
const get = (...params) => _passively('GET', ...params)
const doGet = (...params) => _actively('GET', ...params)

const url = (path) => apiBase + path + (sid !== '' ? `?sid=${sid}` : '')

export const setSid = (s) => {
  sid = s
}

export const passively = {
  getRunningMatches: () => get('/matches/running'),
  getPastMatches: (page) => get(`/matches/past/${page}`),
  getLoggedInPlayers: () => get('/players/logged_in'),
  getAllPlayers: () => get('/players'),
  getInformation: () => get('/information'),
  getMatch: (matchId) => get(`/matches/${matchId}`),
  getDrawBlockedTime: () => get('/draw/blocked')
}

export const actively = {
  // me
  getMe: () => doGet('/me'),
  doLogin: () => post('/me/login'),
  doLogout: () => post('/me/logout'),
  setLang: (lang) => post('/me/set_language', { body: JSON.stringify({ lang }) }),

  // draw
  drawBlock: () => post('/draw/block'),
  drawUnblock: () => post('/draw/unblock'),
  drawPreview: () => post('/draw/preview'),
  drawConfirm: () => post('/draw/confirm'),
  drawCancel: () => post('/draw/cancel'),

  // replace
  replacePreview: (playerId) => post(`/replace/preview/${playerId}`),
  replaceConfirm: (playerId) => post(`/replace/confirm/${playerId}`),
  replaceCancel: () => post('/replace/cancel'),

  // add two
  addPairPreview: (matchId) => post(`/add_pair/preview/${matchId}`),
  addPairConfirm: (matchId) => post(`/add_pair/confirm/${matchId}`),
  addPairCancel: () => post('/add_pair/cancel'),

  // matches
  getRunningMatches: () => doGet('/matches/running'),
  getPastMatches: (page) => doGet(`/matches/past/${page}`),
  placeBet: (matchId, team) => post(`/matches/${matchId}/bet`, { body: JSON.stringify({ team }) }),
  postMatchResult: (matchId, winner) => post(`/matches/${matchId}/post_result`, { body: JSON.stringify({ winner }) }),

  // players
  getLoggedInPlayers: () => doGet('/players/logged_in'),
  getAllPlayers: () => doGet('/players'),
  getRatingData: (userId) => doGet(`/players/ratings/${userId}`),
  getPlayerDetails: (userId) => doGet(`/players/details/${userId}`),
  getDreamteams: (userId, reverse, number) => doGet(`/players/dreamteams/${userId}/${reverse}/${number}/`),

  // statistics
  getStatistics: () => doGet('/players/statistics'),

  // bets
  getBets: () => doGet('/players/bets'),

  // maps
  getMaps: () => doGet('/maps'),
  getMapCivs: (map_id) => doGet(`/map/${map_id}/civs`),
  setMapDescription: (map_id, description) => post(`/map/${map_id}/description`, { body: JSON.stringify({ description }) }),
  setMapImage: (map_id, image) => post(`/map/${map_id}/image`, { body: JSON.stringify({ image }) }),

  // information
  getInformation: () => doGet('/information'),

  // rules
  getRules: () => doGet('/rules'),

  // mod
  doLoginPlayer: (playerId) => post(`/players/login/${playerId}`),
  doLogoutPlayer: (playerId) => post(`/players/logout/${playerId}`)
}
