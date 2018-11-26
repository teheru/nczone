export const avg = (arr, field) => {
  const avg = arr.reduce((acc, cur) => acc + cur[field], 0) / arr.length
  return isNaN(avg) ? 0 : Math.round(avg)
}
