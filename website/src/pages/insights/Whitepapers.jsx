import { Link } from 'react-router-dom'
import { Download } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import CTA from '../../components/CTA'
import { whitepapers } from '../../data/whitepapers'
import { pageContent } from '../../data/pageContent'
import { permalink } from '../../lib/permalinks'

export function BookCover({ paper, className = '' }) {
  return (
    <div className={`relative overflow-hidden bg-navy ${className}`}>
      <Img src={paper.cover} alt="" className="absolute inset-0 h-full w-full object-cover opacity-30 mix-blend-luminosity transition-transform duration-700 group-hover:scale-105" />
      <span className="absolute inset-y-0 left-0 w-3 bg-canvas/40" aria-hidden="true" />
      <div className="relative flex h-full flex-col justify-between p-7 pl-9">
        <span className="text-xs font-bold uppercase tracking-[0.2em] text-white/70">AchiveX {paper.type}</span>
        <div>
          <span className="block h-1 w-10 bg-accent" aria-hidden="true" />
          <p className="mt-4 font-display text-3xl leading-tight text-white">{paper.title}</p>
        </div>
      </div>
    </div>
  )
}

export default function Whitepapers() {
  useSeo('Whitepapers and eBooks', 'Free practical guides from AchiveX on cloud migration, data quality, generative AI and security.')
  const pc = pageContent['/insights/whitepapers/']
  return (
    <>
      <section className="bg-mist pb-16 pt-12 md:pb-20">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Insights' }, { label: 'Whitepapers and eBooks' }]} />
          <FadeUp className="mt-12 max-w-3xl">
            <h1 className="h-display text-5xl md:text-7xl">{pc.heroTitle}</h1>
            <p className="mt-6 text-lg leading-relaxed text-body/70">{pc.heroText}</p>
          </FadeUp>
        </div>
      </section>

      <section className="bg-canvas py-20 md:py-24">
        <div className="container-site grid gap-x-10 gap-y-16 sm:grid-cols-2 lg:grid-cols-4">
          {whitepapers.map((w, i) => (
            <FadeUp key={w.slug} delay={(i % 4) * 0.06} className="group flex flex-col">
              <Link to={permalink(`/insights/whitepapers/${w.slug}/`)} aria-label={w.title}>
                <BookCover paper={w} className="aspect-[3/4] transition-transform duration-500 group-hover:-translate-y-1" />
              </Link>
              <h2 className="mt-7 text-xl font-bold leading-snug">
                <Link to={permalink(`/insights/whitepapers/${w.slug}/`)} className="hover:text-accent">{w.title}</Link>
              </h2>
              <p className="mt-2 flex-1 text-[15px] leading-relaxed text-body/70">{w.description}</p>
              <Link
                to={permalink(`/insights/whitepapers/${w.slug}/`)}
                className="mt-6 inline-flex w-fit items-center gap-2 bg-accent px-5 py-2.5 text-sm font-semibold text-onaccent transition-colors hover:bg-white hover:text-onaccent"
              >
                <Download className="h-4 w-4" aria-hidden="true" /> {pc.downloadText}
              </Link>
            </FadeUp>
          ))}
        </div>
      </section>

      <CTA />
    </>
  )
}
