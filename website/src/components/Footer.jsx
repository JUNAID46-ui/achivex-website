import { Link } from 'react-router-dom'
import { Phone, Mail, MapPin, ExternalLink } from 'lucide-react'
import Logo from './Logo'
import SocialIcons from './SocialIcons'
import { site, quickLinks } from '../data/site'
import { categories } from '../data/categories'
import { categoryPath } from '../data/services'
import { insightsLinks } from '../data/menus'
import { safeHref } from '../utils/safeHref'

function Col({ title, children }) {
  return (
    <div>
      <h2 className="text-[17px] font-medium tracking-[-0.01em] text-white">{title}</h2>
      <div className="mt-6">{children}</div>
    </div>
  )
}

const linkCls = 'text-[15px] text-site-muted transition-colors hover:text-white'

export default function Footer() {
  return (
    <footer className="bg-ink !bg-site-black text-white">
      <div className="container-site grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.1fr_1.1fr_1.3fr] lg:gap-10 lg:py-20">
        <div>
          <Logo variant="full" light />
          <p className="mt-6 max-w-xs text-[15px] leading-relaxed text-site-muted">{site.tagline}</p>
          <SocialIcons className="mt-7" />
        </div>

        <Col title="Quick Links">
          <ul className="space-y-3">
            {quickLinks.map((l) => (
              <li key={l.to}><Link to={l.to} className={linkCls}>{l.label}</Link></li>
            ))}
          </ul>
        </Col>

        <Col title="Services">
          <ul className="space-y-3">
            {categories.map((c) => (
              <li key={c.slug}><Link to={categoryPath(c)} className={linkCls}>{c.name}</Link></li>
            ))}
            <li><Link to="/services/" className={linkCls}>All services</Link></li>
          </ul>
        </Col>

        <Col title="Insights">
          <ul className="space-y-3">
            {insightsLinks.map((l) => (
              <li key={l.to}><Link to={l.to} className={linkCls}>{l.label}</Link></li>
            ))}
          </ul>
        </Col>

        <Col title="Contact">
          <ul className="space-y-4">
            <li><a href={site.phoneHref} className={`flex items-center gap-3 ${linkCls}`}><Phone className="h-4 w-4 text-site-muted" aria-hidden="true" />{site.phone}</a></li>
            <li><a href={`mailto:${site.email}`} className={`flex items-center gap-3 ${linkCls}`}><Mail className="h-4 w-4 text-site-muted" aria-hidden="true" />{site.email}</a></li>
            <li className="flex items-center gap-3 text-[15px] text-site-muted"><MapPin className="h-4 w-4 text-site-muted" aria-hidden="true" />{site.location}</li>
            <li>
              <a href={safeHref(site.googleProfile)} target="_blank" rel="noopener noreferrer" className={`flex items-center gap-3 ${linkCls}`}>
                <ExternalLink className="h-4 w-4 text-site-muted" aria-hidden="true" />Google Business Profile
              </a>
            </li>
          </ul>
        </Col>
      </div>

      <div className="border-t border-white/15">
        <div className="container-site flex flex-col gap-3 py-6 text-sm text-white/70 sm:flex-row sm:items-center sm:justify-between">
          <p>Copyright &copy; 2026 AchiveX. All rights reserved.</p>
          <div className="flex gap-6">
            <Link to="/privacy-policy/" className="transition-colors hover:text-accent">Privacy Policy</Link>
            <Link to="/terms-of-service/" className="transition-colors hover:text-accent">Terms of Service</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
