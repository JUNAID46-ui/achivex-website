import { useEffect } from 'react'
import { useLocation } from 'react-router-dom'
import { settings } from '../data/settings'

// Inserts an HTML snippet. Scripts are recreated so the browser runs them.
function insert(html, parent, before, tag) {
  if (!html || !html.trim()) return []
  const tpl = document.createElement('template')
  tpl.innerHTML = html
  return [...tpl.content.childNodes].map((node) => {
    let el = node
    if (node.nodeName === 'SCRIPT') {
      el = document.createElement('script')
      for (const a of node.attributes) el.setAttribute(a.name, a.value)
      el.text = node.textContent
    }
    if (el.nodeType === 1) el.setAttribute('data-custom-code', tag)
    parent.insertBefore(el, before)
    return el
  })
}

// Custom code saved in the admin (Tools, Custom Code): site wide code once,
// home page code only while the home page is open.
export default function CustomCode() {
  const { pathname } = useLocation()
  const code = settings.customCode

  useEffect(() => {
    const nodes = [
      ...insert(code.css && `<style>${code.css.replace(/<\/style/gi, '')}</style>`, document.head, null, 'all'),
      ...insert(code.head, document.head, null, 'all'),
      ...insert(code.bodyStart, document.body, document.body.firstChild, 'all'),
      ...insert(code.footer, document.body, null, 'all'),
    ]
    if (code.js && code.js.trim()) {
      const s = document.createElement('script')
      s.text = code.js
      s.setAttribute('data-custom-code', 'all')
      document.body.appendChild(s)
      nodes.push(s)
    }
    return () => nodes.forEach((n) => n.remove())
  }, [code])

  const isHome = pathname === '/'
  useEffect(() => {
    if (!isHome) return undefined
    const nodes = [
      ...insert(code.homeHead, document.head, null, 'home'),
      ...insert(code.homeFooter, document.body, null, 'home'),
    ]
    return () => nodes.forEach((n) => n.remove())
  }, [code, isHome])

  return null
}
