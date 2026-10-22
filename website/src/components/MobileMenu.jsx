import { useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { X, ChevronDown, Search, Phone, Mail } from 'lucide-react'
import Logo from './Logo'
import ArrowLink from './ArrowLink'
import { services, servicePath } from '../data/services'
import { site } from '../data/site'

function Section({ title, id, openId, setOpenId, children }) {
  const isOpen = openId === id
  return (
    <div className="border-b border-mist">
      <button
        className="flex w-full items-center justify-between py-4 text-left text-lg font-bold text-body"
        aria-expanded={isOpen}
        onClick={() => setOpenId(isOpen ? null : id)}
      >
        {title}
        <ChevronDown className={`h-5 w-5 text-accent transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`} aria-hidden="true" />
      </button>
      <AnimatePresence initial={false}>
        {isOpen && (
          <motion.div
            initial={{ height: 0 }}
            animate={{ height: 'auto' }}
            exit={{ height: 0 }}
            transition={{ duration: 0.28 }}
            className="overflow-hidden"
          >
            <div className="pb-5">{children}</div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

export default function MobileMenu({ open, onClose, onSearch }) {
  const [openId, setOpenId] = useState(null)
  const link = 'text-[15px] text-body/75'

  return (
    <AnimatePresence>
      {open && (
        <>
          <motion.div
            className="fixed inset-0 z-[60] bg-canvas/60 lg:hidden"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
          />
          <motion.aside
            className="fixed inset-y-0 right-0 z-[70] flex w-full max-w-md flex-col bg-canvas lg:hidden"
            initial={{ x: '100%' }}
            animate={{ x: 0 }}
            exit={{ x: '100%' }}
            transition={{ type: 'tween', duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
            aria-label="Mobile menu"
          >
            <div className="flex h-[72px] items-center justify-between bg-canvas px-5">
              <Logo onClick={onClose} />
              <button onClick={onClose} className="flex h-11 w-11 items-center justify-center text-white" aria-label="Close menu">
                <X className="h-6 w-6" />
              </button>
            </div>
            <nav className="flex-1 overflow-y-auto px-5 pb-10" aria-label="Mobile">
              <Link to="/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-navy">Home</Link>
              <Section title="Services" id="services" openId={openId} setOpenId={setOpenId}>
                <ul className="space-y-3">
                  {services.map((s) => (
                    <li key={s.slug}><ArrowLink to={servicePath(s)} onClick={onClose} className={link}>{s.name}</ArrowLink></li>
                  ))}
                </ul>
              </Section>
              <Link to="/industries/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-navy">Industries</Link>
              <Link to="/insights/blogs/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-navy">Insights</Link>
              <Link to="/about-us/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-navy">About</Link>
              <Link to="/careers/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-accent">Careers</Link>
              <Link to="/contact-us/" onClick={onClose} className="block border-b border-mist py-4 text-lg font-bold text-body hover:text-accent">Contact us</Link>
              <button onClick={onSearch} className="mt-6 flex w-full items-center gap-3 bg-mist px-4 py-3 text-left text-body/60">
                <Search className="h-4 w-4" aria-hidden="true" /> Search the site
              </button>
              <div className="mt-8 space-y-3 text-[15px] text-body">
                <a href={site.phoneHref} className="flex items-center gap-3 hover:text-accent"><Phone className="h-4 w-4 text-accent" />{site.phone}</a>
                <a href={`mailto:${site.email}`} className="flex items-center gap-3 hover:text-accent"><Mail className="h-4 w-4 text-accent" />{site.email}</a>
              </div>
            </nav>
          </motion.aside>
        </>
      )}
    </AnimatePresence>
  )
}
