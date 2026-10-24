'use client'

import { useEffect, useState } from 'react'
import { BrowserRouter, StaticRouter } from 'react-router-dom'
import App from './App'
import { applyAll, loadContent, setBuiltContent } from './lib/content'
import { applyTheme } from './lib/theme'
import { settings } from './data/settings'

let booted = false

// The whole website as one React app, rendered to HTML at build time (StaticRouter) and taken over
// in the browser (BrowserRouter). The content the page was built with is applied first, so the
// browser renders exactly what the HTML shows; newer content from the database is swapped in after.
export default function SiteRoot({ path, rows }) {
  if (!booted) {
    if (rows) applyAll(rows)
    setBuiltContent(rows)
    booted = typeof window !== 'undefined'
  }
  const [version, setVersion] = useState(0)
  // Addresses that were not built (a post added after the last build) render in the browser only.
  const [ready, setReady] = useState(path !== null)

  useEffect(() => {
    setReady(true)
    loadContent().then((changed) => {
      if (!changed) return
      document.getElementById('site-theme')?.remove()
      applyTheme(settings.theme)
      setVersion((v) => v + 1)
    })
  }, [])

  if (typeof window === 'undefined') {
    return path === null ? null : <StaticRouter location={path}><App /></StaticRouter>
  }
  if (!ready) return null
  return <BrowserRouter><App key={version} /></BrowserRouter>
}
