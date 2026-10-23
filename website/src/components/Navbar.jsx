import { useEffect, useRef, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ChevronDown, Search, Menu } from 'lucide-react'
import Logo from './Logo'
import ServicesMenu from './menus/ServicesMenu'
import MobileMenu from './MobileMenu'
import SearchOverlay from './SearchOverlay'

// Only Services opens a dropdown. Everything else links straight to its page.
const items = [
  { key: 'home', label: 'Home', to: '/', match: (p) => p === '/' },
  { key: 'services', label: 'Services', to: '/services/', match: (p) => p.startsWith('/services'), panel: ServicesMenu },
  { key: 'industries', label: 'Industries', to: '/industries/', match: (p) => p.startsWith('/industries') },
  { key: 'insights', label: 'Insights', to: '/insights/', match: (p) => p.startsWith('/insights') },
  { key: 'about', label: 'About', to: '/about-us/', match: (p) => p.startsWith('/about-us') },
  { key: 'careers', label: 'Careers', to: '/careers/', match: (p) => p.startsWith('/careers') },
]

export default function Navbar() {
  const [open, setOpen] = useState(null)
  const [scrolled, setScrolled] = useState(false)
  const [mobile, setMobile] = useState(false)
  const [search, setSearch] = useState(false)
  const { pathname } = useLocation()
  const closeTimer = useRef()

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    setOpen(null)
    setMobile(false)
  }, [pathname])

  useEffect(() => {
    const onKey = (e) => e.key === 'Escape' && setOpen(null)
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [])

  const enter = (key) => {
    clearTimeout(closeTimer.current)
    setOpen(key)
  }
  const leave = () => {
    closeTimer.current = setTimeout(() => setOpen(null), 120)
  }
  const close = () => setOpen(null)

  const solid = scrolled || open
  const linkCls = (on) => `relative flex items-center gap-1.5 px-4 py-2 text-[15px] font-semibold transition-colors ${on ? 'text-navy' : 'text-white hover:text-navy'}`

  return (
    <>
      <header
        className={`sticky top-0 z-50 bg-canvas transition-shadow duration-300 ${solid ? 'shadow-[0_1px_0_rgba(11,13,18,0.08)]' : ''}`}
        onMouseLeave={leave}
      >
        <div className="container-site flex h-[72px] items-center justify-between gap-6">
          <Logo onClick={close} />

          <nav aria-label="Main" className="hidden h-full lg:block">
            <ul className="flex h-full items-center gap-1">
              {items.map((item) => {
                const isActive = item.match(pathname)
                const isOpen = open === item.key
                return (
                  <li key={item.key} className="relative flex h-full items-center" onMouseEnter={() => (item.panel ? enter(item.key) : setOpen(null))}>
                    <Link to={item.to} onClick={close} className={linkCls(isActive || isOpen)} aria-haspopup={item.panel ? 'true' : undefined} aria-expanded={item.panel ? isOpen : undefined}>
                      {item.label}
                      {item.panel && <ChevronDown className={`h-3.5 w-3.5 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`} aria-hidden="true" />}
                      {isActive && <span className="absolute inset-x-4 -bottom-[17px] h-[2px] bg-navy" aria-hidden="true" />}
                    </Link>
                  </li>
                )
              })}
            </ul>
          </nav>

          <div className="flex h-full items-center gap-1">
            <button
              onClick={() => { setSearch(true); close() }}
              className="flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-navy"
              aria-label="Search the site"
            >
              <Search className="h-5 w-5" />
            </button>
            <Link
              to="/contact-us/"
              onClick={close}
              className="ml-2 hidden rounded-full bg-navy px-5 py-2.5 text-[14px] font-semibold text-[#FFFFFF] transition-colors hover:bg-ink lg:inline-flex"
            >
              Contact us
            </Link>
            <button
              onClick={() => setMobile(true)}
              className="flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-navy lg:hidden"
              aria-label="Open menu"
            >
              <Menu className="h-6 w-6" />
            </button>
          </div>
        </div>

        {/* Services dropdown: full width, flat, under the whole header */}
        <AnimatePresence>
          {open && items.find((i) => i.key === open)?.panel && (
            <motion.div
              initial={{ opacity: 0, y: -6 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -6 }}
              transition={{ duration: 0.2 }}
              className="absolute inset-x-0 top-full hidden border-y border-site-line bg-canvas shadow-[0_18px_30px_-24px_rgba(11,13,18,0.25)] lg:block"
              onMouseEnter={() => enter(open)}
            >
              {(() => { const Panel = items.find((i) => i.key === open).panel; return <Panel onNavigate={close} /> })()}
            </motion.div>
          )}
        </AnimatePresence>
      </header>

      <MobileMenu open={mobile} onClose={() => setMobile(false)} onSearch={() => { setMobile(false); setSearch(true) }} />
      <SearchOverlay open={search} onClose={() => setSearch(false)} />
    </>
  )
}
