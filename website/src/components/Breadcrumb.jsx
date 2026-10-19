import { Link } from 'react-router-dom'
import { ChevronRight } from 'lucide-react'

export default function Breadcrumb({ items, light = false }) {
  const base = light ? 'text-white/70' : 'text-ink/60'
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
              <span aria-current="page" className={light ? 'text-white' : 'text-navy'}>{item.label}</span>
            )}
          </li>
        ))}
      </ol>
    </nav>
  )
}
