import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import { InnerHero } from '../../components/inner/InnerSections'
import CTA from '../../components/CTA'
import { news } from '../../data/news'
import { shortDate } from '../../utils/format'
import { pageContent } from '../../data/pageContent'
import { permalink } from '../../lib/permalinks'

export default function Newsroom() {
  useSeo('Newsroom', 'Company announcements and news from AchiveX LLC.')
  const pc = pageContent['/insights/newsroom/']
  const years = [...new Set(news.map((n) => n.date.slice(0, 4)))].sort((a, b) => b - a)
  const [year, setYear] = useState('all')
  const list = [...news].sort((a, b) => b.date.localeCompare(a.date)).filter((n) => year === 'all' || n.date.startsWith(year))

  return (
    <>
      <InnerHero crumbs={[{ label: 'Insights', to: '/insights/' }, { label: 'Newsroom' }]} eyebrow="Newsroom" title={pc.heroTitle} text={pc.heroText || 'Announcements, new services and company updates from AchiveX LLC.'} />
      <section className="bg-canvas pb-10 pt-14">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-8 border-b border-site-line pb-8 md:flex-row md:items-end">
            <h2 className="text-[28px] font-medium tracking-[-0.02em] text-site-heading">Latest news</h2>
            <div role="tablist" aria-label="Filter by year" className="flex flex-wrap gap-6">
              {['all', ...years].map((y) => (
                <button
                  key={y}
                  role="tab"
                  aria-selected={year === y}
                  onClick={() => setYear(y)}
                  className={`border-b-2 pb-1 text-sm font-semibold transition-colors ${year === y ? 'border-accent text-white' : 'border-transparent text-body/80 hover:text-accent'}`}
                >
                  {y === 'all' ? pc.allYearsLabel : y}
                </button>
              ))}
            </div>
          </FadeUp>
        </div>
      </section>

      <section className="bg-canvas pb-24">
        <div className="container-site">
          <ul className="divide-y divide-mist">
            {list.map((n) => {
              const d = shortDate(n.date)
              return (
                <FadeUp as="li" key={n.slug}>
                  <Link to={permalink(`/insights/newsroom/${n.slug}/`)} className="group grid items-center gap-6 py-10 md:grid-cols-[140px_1fr_260px] md:gap-10">
                    <time dateTime={n.date} className="flex items-baseline gap-3 md:block">
                      <span className="font-display text-5xl text-white">{d.day}</span>
                      <span className="block text-sm font-semibold uppercase tracking-wide text-body/80">{d.month} {d.year}</span>
                    </time>
                    <div>
                      <h2 className="text-2xl font-bold leading-snug transition-colors group-hover:text-accent md:text-3xl">{n.title}</h2>
                      <p className="mt-3 max-w-2xl leading-relaxed text-body/80">{n.summary}</p>
                      <span className="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white">
                        {pc.readMoreText} <ArrowRight className="h-4 w-4 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                      </span>
                    </div>
                    <div className="overflow-hidden">
                      <Img src={n.image.replace('w=1600', 'w=600')} alt={n.title} className="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    </div>
                  </Link>
                </FadeUp>
              )
            })}
          </ul>
        </div>
      </section>

      <CTA variant="split" title={pc.ctaTitle} text={pc.ctaText} button={pc.ctaButton} />
    </>
  )
}
