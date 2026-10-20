import { Link } from 'react-router-dom'
import { Phone, Mail, MapPin, ExternalLink } from 'lucide-react'
import Logo from './Logo'
import SocialIcons from './SocialIcons'
import { site, quickLinks } from '../data/site'
import { categories } from '../data/categories'
import { getService, servicePath, categoryPath } from '../data/services'
import { insightsLinks } from '../data/menus'

const footerServices = ['digital', 'data-and-analytics', 'cloud', 'security', 'business-process-services']

function Col({ title, children }) {
  return (
    <div>
      <h2 className="text-sm font-bold uppercase tracking-wider text-white">{title}</h2>
      <span className="mt-3 block h-0.5 w-8 bg-accent" aria-hidden="true" />
      <div className="mt-6">{children}</div>
    </div>
  )
}

const linkCls = 'text-[15px] text-white/75 transition-colors hover:text-accent'

export default function Footer() {
  return (
    <footer className="border-t border-white/10 bg-canvas text-white">
      <div className="container-site grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.1fr_1.1fr_1.3fr] lg:gap-10 lg:py-20">
        <div>
          <Logo variant="full" />
          <p className="mt-6 max-w-xs text-[15px] leading-relaxed text-white/75">{site.tagline}</p>
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
            {footerServices.map((slug) => {
              const c = categories.find((x) => x.slug === slug)
              const to = c.single ? servicePath(getService(c.slug)) : categoryPath(c)
              return <li key={slug}><Link to={to} className={linkCls}>{c.name}</Link></li>
            })}
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
            <li><a href={site.phoneHref} className={`flex items-center gap-3 ${linkCls}`}><Phone className="h-4 w-4 text-accent" aria-hidden="true" />{site.phone}</a></li>
            <li><a href={`mailto:${site.email}`} className={`flex items-center gap-3 ${linkCls}`}><Mail className="h-4 w-4 text-accent" aria-hidden="true" />{site.email}</a></li>
            <li className="flex items-center gap-3 text-[15px] text-white/75"><MapPin className="h-4 w-4 text-accent" aria-hidden="true" />{site.location}</li>
            <li>
              <a href={site.googleProfile} target="_blank" rel="noopener noreferrer" className={`flex items-center gap-3 ${linkCls}`}>
                <ExternalLink className="h-4 w-4 text-accent" aria-hidden="true" />Google Business Profile
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
