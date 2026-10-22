import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import { loadContent, applyCachedContent } from './lib/content'
import { applyTheme } from './lib/theme'
import { settings } from './data/settings'
import './index.css'

const root = ReactDOM.createRoot(document.getElementById('root'))
const render = (version) =>
  root.render(
    <React.StrictMode>
      <BrowserRouter>
        <App key={version} />
      </BrowserRouter>
    </React.StrictMode>,
  )

// Show the page straight away using the content saved from the last visit (or the built in
// content on a first visit), then refresh it once the latest content arrives from the database.
applyCachedContent()
applyTheme(settings.theme)
render(0)
loadContent().then((changed) => {
  if (!changed) return
  document.getElementById('site-theme')?.remove()
  applyTheme(settings.theme)
  render(1)
})
