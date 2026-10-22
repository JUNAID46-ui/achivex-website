import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import Img from '../Img'
import ArrowLink from '../ArrowLink'
import { insightsLinks } from '../../data/menus'
import { caseStudies } from '../../data/caseStudies'
import { blogs } from '../../data/blogs'
import { permalink } from '../../lib/permalinks'

export default function InsightsMenu({ onNavigate }) {
  const featured = blogs.find((b) => b.featured) || blogs[0]
  return (
    <div className="grid grid-cols-[minmax(260px,30%)_1fr]">
      <aside className="bg-cloud px-10 py-10">
        <p className="font-display text-3xl text-body">Insights</p>
        <Link to={permalink(`/insights/blogs/${featured.slug}/`)} onClick={onNavigate} className="group mt-6 block">
          <div className="overflow-hidden">
            <Img src={featured.image.replace('w=2000', 'w=800')} alt={featured.title} className="aspect-[16/9] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
          </div>
          <p className="mt-5 text-lg font-bold leading-snug text-body">{featured.title}</p>
          <span className="mt-4 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-body group-hover:text-accent">
            Learn More <span className="text-accent transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
          </span>
        </Link>
      </aside>
      <div className="grid grid-cols-2 gap-12 px-12 py-10">
        <ul className="space-y-1">
          {insightsLinks.map((l) => (
            <li key={l.to}>
              <Link to={l.to} onClick={onNavigate} className="group flex items-start justify-between gap-4 px-4 py-4 transition-colors hover:bg-canvas">
                <span>
                  <span className="block text-lg font-bold text-body group-hover:text-accent">{l.label}</span>
                  <span className="mt-1 block text-sm text-body/60">{l.text}</span>
                </span>
                <ArrowRight className="mt-1.5 h-4 w-4 text-accent transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
              </Link>
            </li>
          ))}
        </ul>
        <div>
          <p className="text-lg font-bold text-body">Recent case studies</p>
          <ul className="mt-4 space-y-3">
            {caseStudies.slice(0, 4).map((c) => (
              <li key={c.slug}>
                <ArrowLink to={permalink(`/insights/case-studies/${c.slug}/`)} onClick={onNavigate} className="text-[15px] text-body/75">{c.title}</ArrowLink>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  )
}
