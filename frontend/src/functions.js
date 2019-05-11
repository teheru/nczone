export const avg = (arr, field) => {
  const avg = arr.reduce((acc, cur) => acc + cur[field], 0) / arr.length
  return isNaN(avg) ? 0 : Math.round(avg)
}

export const pad = (n) => n > 9 ? n : `0${n}`

export const assign = (source, source2) => {
  const obj = {}
  for (const key of Object.keys(source)) {
    obj[key] = source2.hasOwnProperty(key) ? source2[key] : source[key]
  }
  return obj
}

export const sort = (arr, sort) => {
  return arr.sort((a, b) => {
    if (a[sort.field] === b[sort.field]) {
      return 0
    }
    return (a[sort.field] > b[sort.field] ? 1 : -1) * sort.order
  })
}
