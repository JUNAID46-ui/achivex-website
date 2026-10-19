import { useEffect } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import Navbar from './Navbar'
import Footer from './Footer'
import jumpTo from '../utils/jumpTo'
import SiteHead from './SiteHead'
import { logPageView, normalizePath } from '../lib/content'
import { customPages, pageSections } from '../data/customPages'
import PageSections from './PageSections'

export default function Layout() {
  const { pathname } = useLocation()
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' })
    logPageView(pathname)
  }, [pathname])

  return (
    <>
      <a href="#main" onClick={(e) => jumpTo(e, 'main')} className="sr-only z-[90] bg-accent px-4 py-2 text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Skip to content
      </a>
      <SiteHead />
      <Navbar />
      <main id="main">
        <Outlet />
        {/* Extra sections added to a fixed page in the Page Builder appear above the footer. */}
        {pathname !== '/' && !customPages[normalizePath(pathname)] && <PageSections sections={pageSections[normalizePath(pathname)]} />}
      </main>
      <Footer />
    </>
  )
}
