let callbacks = []
let callbacksOnce = []

let stopped = false
let ticks = 0
let now = 0
let then
let elapsed = 0

const execCallbacks = () => {
  ticks++
  callbacks.forEach((cb) => {
    if (cb.interval === 1 || (ticks - cb.start) % cb.interval === 0) {
      cb.func(now, ticks)
    }
  })

  const tmp = []
  while (callbacksOnce.length > 0) {
    const cb = callbacksOnce.shift()
    if (cb.interval === 1 || (ticks - cb.start) % cb.interval === 0) {
      cb.func(ticks)
    } else {
      tmp.push(cb)
    }
  }
  while (tmp.length > 0) {
    callbacksOnce.push(tmp.shift())
  }
}

const next = () => {
  if (stopped) {
    return
  }
  requestAnimationFrame(next)
  now = Date.now()
  elapsed = now - then
  if (elapsed > 1000) {
    then = now - (elapsed % 1000)
    execCallbacks()
  }
}

export const start = () => {
  then = Date.now()
  ticks = 0
  elapsed = 0
  stopped = false
  next()
}

export const stop = () => {
  stopped = true
}

export const once = (tick, cb) => {
  callbacksOnce.push({
    start: ticks,
    interval: tick,
    func: cb
  })
}

export const every = (tick, cb) => {
  callbacks.push({
    start: ticks,
    interval: tick,
    func: cb
  })
}

export const off = (_cb) => {
  callbacks = callbacks.filter(cb => cb.func !== _cb)
  callbacksOnce = callbacksOnce.filter(cb => cb.func !== _cb)
}
