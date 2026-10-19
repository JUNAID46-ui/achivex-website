export const formatDate = (iso) =>
  new Date(iso + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

export const shortDate = (iso) => {
  const d = new Date(iso + 'T00:00:00')
  return {
    day: d.toLocaleDateString('en-US', { day: '2-digit' }),
    month: d.toLocaleDateString('en-US', { month: 'short' }),
    year: d.getFullYear(),
  }
}
