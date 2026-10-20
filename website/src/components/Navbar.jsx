import { useEffect, useRef, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ChevronDown, Search, Menu } from 'lucide-react'
import Logo from './Logo'
import ServicesMenu from './menus/ServicesMenu'
import InsightsMenu from './menus/InsightsMenu'
import AboutMenu from './menus/AboutMenu'
import IndustriesMenu from './menus/IndustriesMenu'
import MobileMenu from './MobileMenu'
import SearchOverlay from './SearchOverlay'

const items = [
  { key: 'services', label: 'Services', match: '/services', panel: ServicesMenu, full: true },
  { key: 'industries', label: 'Industries', match: '/industries', panel: IndustriesMenu, full: false },
  { key: 'insights', label: 'Insights', match: '/insights', panel: InsightsMenu, full: true },
  { key: 'about', label: 'About', match: '/about-us', panel: AboutMenu, full: true },
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

  const active = items.find((i) => i.key === open)
  const solid = scrolled || open

  return (
    <>
      <header
        className={`sticky top-0 z-50 bg-canvas transition-shadow duration-300 ${solid ? 'shadow-[0_1px_0_rgba(255,255,255,0.08)]' : ''}`}
        onMouseLeave={leave}
      >
        <div className="container-site flex h-[72px] items-center justify-between gap-6">
          <Logo onClick={close} />

          <nav aria-label="Main" className="hidden h-full lg:block">
            <ul className="flex h-full items-center gap-1">
              {items.map((item) => {
                const isActive = pathname.startsWith(item.match)
                const isOpen = open === item.key
                return (
                  <li key={item.key} className="relative flex h-full items-center" onMouseEnter={() => enter(item.key)}>
                    <button
                      className={`relative flex items-center gap-1.5 px-4 py-2 text-[15px] font-semibold transition-colors ${
                        isOpen || isActive ? 'text-accent' : 'text-white hover:text-accent'
                      }`}
                      aria-expanded={isOpen}
                      aria-haspopup="true"
                      onClick={() => setOpen(isOpen ? null : item.key)}
                    >
                      {item.label}
                      <ChevronDown className={`h-3.5 w-3.5 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`} aria-hidden="true" />
                      {isActive && <span className="absolute inset-x-4 -bottom-[17px] h-[3px] bg-accent" aria-hidden="true" />}
                    </button>
                    {!item.full && (
                      <AnimatePresence>
                        {isOpen && (
                          <motion.div
                            initial={{ opacity: 0, y: -6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -6 }}
                            transition={{ duration: 0.22 }}
                            className="absolute left-0 top-full bg-canvas"
                          >
                            <item.panel onNavigate={close} />
                          </motion.div>
                        )}
                      </AnimatePresence>
                    )}
                  </li>
                )
              })}
            </ul>
          </nav>

          <div className="flex h-full items-center">
            <Link
              to="/careers/"
              onClick={close}
              className={`hidden px-4 text-[15px] font-semibold transition-colors hover:text-accent lg:block ${pathname.startsWith('/careers') ? 'text-accent' : 'text-white'}`}
            >
              Careers
            </Link>
            <Link
              to="/contact-us/"
              onClick={close}
              className={`hidden px-4 text-[15px] font-semibold transition-colors hover:text-accent xl:block ${pathname.startsWith('/contact') ? 'text-accent' : 'text-white'}`}
            >
              Contact
            </Link>
            <span className="mx-2 hidden h-full w-px bg-white/15 lg:block" aria-hidden="true" />
            <button
              onClick={() => { setSearch(true); close() }}
              className="flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-accent"
              aria-label="Search the site"
            >
              <Search className="h-5 w-5" />
            </button>
            <button
              onClick={() => setMobile(true)}
              className="flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-accent lg:hidden"
              aria-label="Open menu"
            >
              <Menu className="h-6 w-6" />
            </button>
          </div>
        </div>

        <AnimatePresence>
          {active?.full && (
            <motion.div
              key={active.key}
              initial={{ opacity: 0, y: -8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.25, ease: 'easeOut' }}
              className="absolute inset-x-0 top-full hidden border-t border-mist bg-canvas lg:block"
              onMouseEnter={() => enter(active.key)}
            >
              <div className="mx-auto max-w-[1440px]">
                <active.panel onNavigate={close} />
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </header>

      <MobileMenu open={mobile} onClose={() => setMobile(false)} onSearch={() => { setMobile(false); setSearch(true) }} />
      <SearchOverlay open={search} onClose={() => setSearch(false)} />
    </>
  )
}
