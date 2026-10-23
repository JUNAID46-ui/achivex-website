import { Suspense, useEffect, useRef, useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import Navbar from './Navbar'
import Footer from './Footer'
import jumpTo from '../utils/jumpTo'
import SiteHead from './SiteHead'
import CustomCode from './CustomCode'
import { logPageView, normalizePath } from '../lib/content'
import { customPages, pageSections, pageDesigns } from '../data/customPages'
import { pageDesignCss } from '../lib/sectionDesign'
import PageSections from './PageSections'
import ClosingStrip from './ClosingStrip'

// Landing pages end with their own copyright bar instead of the footer.
const LANDING_PAGES = ['/free-consultation/']

export default function Layout() {
  const { pathname } = useLocation()
  const bare = LANDING_PAGES.includes(normalizePath(pathname))
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' })
    logPageView(pathname)
  }, [pathname])

  // When a page ends with the black call to action, add a light closing band before the black
  // footer. Watched with an observer because content can arrive from the database after render.
  const mainRef = useRef(null)
  const [endsWithCta, setEndsWithCta] = useState(false)
  useEffect(() => {
    const el = mainRef.current
    if (!el) return
    const check = () => {
      const blocks = [...el.querySelectorAll('section')].filter((s) => !s.closest('[data-closing]'))
      setEndsWithCta(Boolean(blocks.length && blocks[blocks.length - 1].classList.contains('cta-shine')))
    }
    check()
    const obs = new MutationObserver(check)
    obs.observe(el, { childList: true, subtree: true })
    return () => obs.disconnect()
  }, [pathname])

  return (
    <>
      <a href="#main" onClick={(e) => jumpTo(e, 'main')} className="sr-only z-[90] bg-accent px-4 py-2 text-onaccent focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Skip to content
      </a>
      <SiteHead />
      <CustomCode />
      <Navbar />
      <main id="main" ref={mainRef}>
        {pageDesigns[normalizePath(pathname)] && <style>{pageDesignCss(pageDesigns[normalizePath(pathname)])}</style>}
        {/* Pages load on demand; the short fallback keeps the footer from jumping up meanwhile. */}
        <Suspense fallback={<div className="min-h-[70vh]" />}>
          <Outlet />
        </Suspense>
        {/* Extra sections added to a fixed page in the Page Builder appear above the footer. */}
        {!bare && pathname !== '/' && !customPages[normalizePath(pathname)] && <PageSections sections={pageSections[normalizePath(pathname)]} />}
        {!bare && endsWithCta && <div data-closing><ClosingStrip /></div>}
      </main>
      {!bare && <Footer />}
    </>
  )
}
