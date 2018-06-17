const apiBase = 'http://new-chapter.local/app.php/nczone/api'
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
    headers: {},
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

export const loggedInUsers = () => get('/users/logged_in')
export const allUsers = () => get('/users')
export const runningMatches = () => get('/rmatches')
export const pastMatches = () => get('/pmatches')
export const me = () => get('/me')
export const login = () => get('/me/login')
export const logout = () => get('/me/logout')
