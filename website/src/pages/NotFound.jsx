import { useEffect } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { redirects } from '../data/settings'
import { logNotFound, normalizePath } from '../lib/content'
import useSeo from '../hooks/useSeo'
import Button from '../components/Button'
import ArrowLink from '../components/ArrowLink'
import PageSections from '../components/PageSections'
import { customPages, pageSections } from '../data/customPages'

// A page made in the admin ("Add New Page") has no fixed route, so it lands here first.
function CustomPage({ page }) {
  useSeo(page.title, page.description || '')
  return <PageSections sections={pageSections[page.path]} />
}

export default function NotFound() {
  const { pathname } = useLocation()
  const page = customPages[normalizePath(pathname)]
  return page ? <CustomPage page={page} /> : <Missing pathname={pathname} />
}

function Missing({ pathname }) {
  useSeo('Page Not Found', 'The page you are looking for could not be found.')
  const navigate = useNavigate()

  // Follow a redirect from the admin Redirects manager, otherwise record the missing URL.
  useEffect(() => {
    const match = redirects.find((r) => r.from === normalizePath(pathname))
    if (match) {
      if (/^https?:/.test(match.to)) window.location.replace(match.to)
      else navigate(match.to, { replace: true })
    } else {
      logNotFound(pathname)
    }
  }, [pathname, navigate])
  return (
    <section className="bg-mist py-24 md:py-36">
      <div className="container-site text-center">
        <motion.p
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
          className="font-display text-[140px] leading-none text-white md:text-[240px]"
          aria-hidden="true"
        >
          404
        </motion.p>
        <h1 className="mt-4 text-3xl font-bold md:text-4xl">This page does not exist</h1>
        <p className="mx-auto mt-4 max-w-md text-lg text-body/70">
          The link may be old or mistyped. Try one of the pages below, or head back to the homepage.
        </p>
        <div className="mt-10"><Button to="/">Back to Home</Button></div>
        <ul className="mt-12 flex flex-wrap justify-center gap-x-10 gap-y-4 border-t border-white/15 pt-8 font-semibold">
          <li><ArrowLink to="/services/" className="text-white">Services</ArrowLink></li>
          <li><ArrowLink to="/insights/case-studies/" className="text-white">Insights</ArrowLink></li>
          <li><ArrowLink to="/contact-us/" className="text-white">Contact</ArrowLink></li>
        </ul>
      </div>
    </section>
  )
}
