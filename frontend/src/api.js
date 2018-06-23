/* eslint-disable no-unused-vars */
const apiBase = process.env.VUE_APP_API_BASE
const xhrTimeoutMs = 30000

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

const get = (...params) => request('GET', ...params)
const put = (...params) => request('PUT', ...params)
const post = (...params) => request('POST', ...params)

const url = (path) => apiBase + path

// me
export const me = () => get('/me')
export const login = () => get('/me/login')
export const logout = () => get('/me/logout')

// draw
export const drawPreview = () => get('/draw/preview')
export const drawConfirm = () => get('/draw/confirm')
export const drawCancel = () => get('/draw/cancel')

// matches
export const runningMatches = () => get('/matches/running')
export const pastMatches = () => get('/matches/past')
export const match = (matchId) => get(`/matches/${matchId}`)
export const postMatchResult = (matchId, winner) => post(`/matches/${matchId}/post_result`, {body: JSON.stringify({winner: winner})})

// players
export const loggedInPlayers = () => get('/players/logged_in')
export const allPlayers = () => get('/players')

// information
export const getInformation = () => get('/information')
