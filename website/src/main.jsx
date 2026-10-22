import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import { loadContent } from './lib/content'
import { applyTheme } from './lib/theme'
import { settings } from './data/settings'
import './index.css'

// Load editable content from the database first, then render.
loadContent().finally(() => {
  applyTheme(settings.theme)
  ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </React.StrictMode>,
  )
})
