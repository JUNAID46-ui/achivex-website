// Smooth scroll to a section without changing the URL, so in-page links also work with hash routing.
export default function jumpTo(e, id) {
  const el = document.getElementById(id)
  if (!el) return
  e.preventDefault()
  el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
