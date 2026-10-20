import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'

const styles = {
  primary: 'bg-accent text-onaccent hover:bg-white hover:text-onaccent',
  light: 'bg-accent text-onaccent hover:bg-white hover:text-onaccent',
  outline: 'border border-white text-white hover:border-accent hover:bg-accent hover:text-onaccent',
  outlineLight: 'border border-white/60 text-white hover:border-accent hover:bg-accent hover:text-onaccent',
}

export default function Button({ to, href, children, variant = 'primary', arrow = true, className = '', ...rest }) {
  const cls = `group inline-flex items-center gap-3 disabled:cursor-wait disabled:opacity-60 px-6 py-3.5 text-[15px] font-medium transition-colors duration-300 ${styles[variant]} ${className}`
  const inner = (
    <>
      <span>{children}</span>
      {arrow && <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />}
    </>
  )
  if (to) return <Link to={to} className={cls} {...rest}>{inner}</Link>
  if (href) return <a href={href} className={cls} {...rest}>{inner}</a>
  return <button className={cls} {...rest}>{inner}</button>
}
