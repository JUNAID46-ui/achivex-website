import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'

// Text link with the small orange arrow used across menus and lists.
export default function ArrowLink({ to, children, className = '', onClick }) {
  return (
    <Link to={to} onClick={onClick} className={`group inline-flex items-center gap-2 transition-colors hover:text-accent ${className}`}>
      <span>{children}</span>
      <ArrowRight className="h-4 w-4 shrink-0 text-accent transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
    </Link>
  )
}
