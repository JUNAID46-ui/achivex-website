import { useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { Search, X, ArrowRight } from 'lucide-react'
import { services, servicePath } from '../data/services'
import { multiCategories } from '../data/categories'
import { caseStudies } from '../data/caseStudies'
import { blogs } from '../data/blogs'
import { whitepapers } from '../data/whitepapers'
import { industries } from '../data/industries'

// Builds a small search index from the data files.
const index = [
  ...multiCategories.map((c) => ({ type: 'Service area', title: c.name, text: c.short, to: `/services/${c.slug}/` })),
  ...services.map((s) => ({ type: 'Service', title: s.name, text: s.short, to: servicePath(s) })),
  ...industries.map((i) => ({ type: 'Industry', title: i.name, text: i.summary, to: `/industries/${i.slug}/` })),
  ...caseStudies.map((c) => ({ type: 'Case study', title: c.title, text: c.summary, to: `/insights/case-studies/${c.slug}/` })),
  ...blogs.map((b) => ({ type: 'Blog', title: b.title, text: b.excerpt, to: `/insights/blogs/${b.slug}/` })),
  ...whitepapers.map((w) => ({ type: w.type, title: w.title, text: w.description, to: `/insights/whitepapers/${w.slug}/` })),
  { type: 'Page', title: 'Contact us', text: 'Phone, email and contact form', to: '/contact-us/' },
  { type: 'Page', title: 'Careers', text: 'Working at AchiveX', to: '/careers/' },
  { type: 'Page', title: 'Who We Are', text: 'Our story, mission and values', to: '/about-us/' },
]

export default function SearchOverlay({ open, onClose }) {
  const [q, setQ] = useState('')
  const input = useRef(null)

  useEffect(() => {
    if (open) {
      setQ('')
      setTimeout(() => input.current?.focus(), 50)
      const onKey = (e) => e.key === 'Escape' && onClose()
      window.addEventListener('keydown', onKey)
      return () => window.removeEventListener('keydown', onKey)
    }
  }, [open, onClose])

  const results = useMemo(() => {
    const term = q.trim().toLowerCase()
    if (term.length < 2) return []
    return index.filter((r) => `${r.title} ${r.text}`.toLowerCase().includes(term)).slice(0, 8)
  }, [q])

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          className="fixed inset-0 z-[80] bg-canvas/70"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={onClose}
          role="dialog"
          aria-modal="true"
          aria-label="Search"
        >
          <motion.div
            className="bg-canvas"
            initial={{ y: -30 }}
            animate={{ y: 0 }}
            exit={{ y: -30 }}
            transition={{ duration: 0.3 }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="container-site py-8">
              <div className="flex items-center gap-4 border-b-2 border-white pb-3">
                <Search className="h-6 w-6 text-accent" aria-hidden="true" />
                <label htmlFor="site-search" className="sr-only">Search the site</label>
                <input
                  id="site-search"
                  ref={input}
                  value={q}
                  onChange={(e) => setQ(e.target.value)}
                  placeholder="Search services, insights and pages"
                  className="w-full bg-transparent py-2 text-xl text-body outline-none placeholder:text-body/40 md:text-2xl"
                />
                <button onClick={onClose} aria-label="Close search" className="text-white hover:text-accent">
                  <X className="h-6 w-6" />
                </button>
              </div>
              {q.trim().length >= 2 && (
                <ul className="mt-4 max-h-[60vh] divide-y divide-mist overflow-y-auto">
                  {results.length === 0 && <li className="py-6 text-body/60">No results for "{q}". Try a service name such as cloud or security.</li>}
                  {results.map((r) => (
                    <li key={r.to + r.title}>
                      <Link to={r.to} onClick={onClose} className="group flex items-center justify-between gap-6 py-4">
                        <span>
                          <span className="block text-lg font-bold text-white group-hover:text-accent">{r.title}</span>
                          <span className="block text-sm text-body/60"><span className="font-semibold text-accent">{r.type}</span> / {r.text}</span>
                        </span>
                        <ArrowRight className="h-5 w-5 shrink-0 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
