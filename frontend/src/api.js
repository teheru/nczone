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
      resolve({status: e.target.status, text: e.target.responseText})
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
  return request(method, path, Object.assign(options || {}, {headers: {'X-Update-Session': '1'}}), onProgress)
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
  getPastMatches: () => get('/matches/past'),
  getLoggedInPlayers: () => get('/players/logged_in'),
  getAllPlayers: () => get('/players'),
  getInformation: () => get('/information'),
  getMatch: (matchId) => get(`/matches/${matchId}`)
}

export const actively = {
  // me
  getMe: () => doGet('/me'),
  doLogin: () => post('/me/login'),
  doLogout: () => post('/me/logout'),
  setLang: (lang) => post('/me/set_language', {body: JSON.stringify({lang})}),

  // draw
  drawPreview: () => post('/draw/preview'),
  drawConfirm: () => post('/draw/confirm'),
  drawCancel: () => post('/draw/cancel'),

  // matches
  getRunningMatches: () => doGet('/matches/running'),
  getPastMatches: () => doGet('/matches/past'),
  placeBet: (matchId, team) => post(`/matches/${matchId}/bet`, {body: JSON.stringify({team})}),
  postMatchResult: (matchId, winner) => post(`/matches/${matchId}/post_result`, {body: JSON.stringify({winner})}),

  // players
  getLoggedInPlayers: () => doGet('/players/logged_in'),
  getAllPlayers: () => doGet('/players'),

  // information
  getInformation: () => doGet('/information'),

  // rules
  getRules: () => doGet('/rules')
}
