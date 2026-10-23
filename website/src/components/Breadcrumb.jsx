import { useEffect } from 'react'
import { Link } from 'react-router-dom'
import { ChevronRight } from 'lucide-react'
import { setJsonLd } from '../lib/schema'

export default function Breadcrumb({ items, light = false }) {
  const base = light ? 'text-white/70' : 'text-body/80'
  // BreadcrumbList schema for search engines, built from the same trail.
  const trail = JSON.stringify(items)
  useEffect(() => {
    const origin = window.location.origin
    const list = [{ label: 'Home', to: '/' }, ...items]
    setJsonLd('breadcrumb-schema', {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: list.map((it, i) => ({
        '@type': 'ListItem', position: i + 1, name: it.label,
        item: `${origin}${it.to || window.location.pathname}`,
      })),
    })
    return () => setJsonLd('breadcrumb-schema', null)
  }, [trail]) // eslint-disable-line react-hooks/exhaustive-deps
  return (
    <nav aria-label="Breadcrumb">
      <ol className={`flex flex-wrap items-center gap-1.5 text-sm ${base}`}>
        <li><Link to="/" className="transition-colors hover:text-accent">Home</Link></li>
        {items.map((item, i) => (
          <li key={i} className="flex items-center gap-1.5">
            <ChevronRight className="h-3.5 w-3.5" aria-hidden="true" />
            {item.to ? (
              <Link to={item.to} className="transition-colors hover:text-accent">{item.label}</Link>
            ) : (
              <span aria-current="page" className={light ? 'text-white' : 'text-white'}>{item.label}</span>
            )}
          </li>
        ))}
      </ol>
    </nav>
  )
}
