import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Breadcrumb from '../../components/Breadcrumb'
import CTA from '../../components/CTA'
import { insightsLinks } from '../../data/menus'
import { blogs } from '../../data/blogs'
import { caseStudies } from '../../data/caseStudies'
import { news } from '../../data/news'
import { whitepapers } from '../../data/whitepapers'

// /insights/ brings the four kinds of content together, since the menu links here directly.
export default function Insights() {
  useSeo('Insights', 'Case studies, company news, free guides and practical articles from AchiveX LLC.')
  const counts = { 'Case Studies': caseStudies.length, Newsroom: news.length, 'Whitepapers and eBooks': whitepapers.length, Blogs: blogs.length }
  return (
    <>
      <section className="bg-mist pb-16 pt-12 md:pb-20">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Insights' }]} />
          <FadeUp className="mt-12 max-w-3xl">
            <h1 className="h-display text-5xl md:text-7xl">Insights</h1>
            <p className="mt-6 text-lg leading-relaxed text-body/70">How we approach client problems, what is new at AchiveX, and practical guides and articles you can use.</p>
          </FadeUp>
        </div>
      </section>
      <section className="bg-canvas py-16 md:py-24">
        <ul className="container-site grid gap-5 md:grid-cols-2">
          {insightsLinks.map((l, i) => (
            <FadeUp as="li" key={l.label} delay={(i % 2) * 0.05}>
              <Link to={l.to} className="group flex h-full items-end justify-between gap-6 rounded-2xl border border-body/10 p-8 transition-colors hover:border-navy md:p-10">
                <div>
                  <p className="text-sm text-body/60">{counts[l.label] ?? ''} {counts[l.label] === 1 ? 'item' : 'items'}</p>
                  <p className="mt-3 font-display text-3xl font-medium text-white">{l.label}</p>
                  <p className="mt-2 text-body/70">{l.text}</p>
                </div>
                <ArrowRight className="h-6 w-6 shrink-0 text-navy transition-transform group-hover:translate-x-1" aria-hidden="true" />
              </Link>
            </FadeUp>
          ))}
        </ul>
      </section>
      <CTA />
    </>
  )
}
