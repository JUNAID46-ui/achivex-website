import { Link, useParams } from 'react-router-dom'
import { ArrowRight, Check } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ArrowLink from '../../components/ArrowLink'
import CTA from '../../components/CTA'
import NotFound from '../NotFound'
import { caseStudies } from '../../data/caseStudies'
import { getService, servicePath } from '../../data/services'
import { industries } from '../../data/industries'
import { permalink } from '../../lib/permalinks'

export default function CaseStudyDetail() {
  const { slug } = useParams()
  const idx = caseStudies.findIndex((c) => c.slug === slug)
  const cs = caseStudies[idx]
  useSeo(cs?.title, cs?.summary)
  if (!cs) return <NotFound />
  const next = caseStudies[(idx + 1) % caseStudies.length]
  const industry = industries.find((i) => i.slug === cs.industry)

  return (
    <>
      <section className="bg-canvas pt-12">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Insights' }, { label: 'Case Studies', to: permalink('/insights/case-studies/') }, { label: 'Case study' }]} />
          <FadeUp className="mt-12 max-w-4xl">
            <h1 className="h-display text-5xl md:text-7xl">{cs.title}</h1>
          </FadeUp>
        </div>
        <div className="mt-12 h-[340px] overflow-hidden md:h-[560px]">
          <Img src={cs.image} alt={cs.title} className="h-full w-full object-cover" />
        </div>
      </section>

      <section className="bg-canvas py-16 md:py-24">
        <div className="container-site grid gap-14 lg:grid-cols-[300px_1fr]">
          <aside className="lg:sticky lg:top-28 lg:self-start">
            <dl className="divide-y divide-mist bg-mist p-7">
              <div className="pb-5">
                <dt className="text-sm font-semibold text-body/60">Client</dt>
                <dd className="mt-1 font-bold text-white">{cs.clientType}</dd>
              </div>
              <div className="py-5">
                <dt className="text-sm font-semibold text-body/60">Industry</dt>
                <dd className="mt-1 font-bold text-white">{industry?.name}</dd>
              </div>
              <div className="pt-5">
                <dt className="text-sm font-semibold text-body/60">Services used</dt>
                <dd className="mt-3 space-y-2">
                  {cs.services.map(getService).filter(Boolean).map((s) => (
                    <ArrowLink key={s.slug} to={servicePath(s)} className="text-[15px] font-semibold text-white">{s.name}</ArrowLink>
                  ))}
                </dd>
              </div>
            </dl>
            <p className="mt-4 text-xs leading-relaxed text-body/50">This case study describes our approach for this type of client. Client names and figures are shared only with permission.</p>
          </aside>

          <div className="max-w-read">
            <FadeUp>
              <h2 className="h-display text-4xl">The situation</h2>
              <p className="mt-5 text-xl leading-relaxed text-body/75">{cs.overview}</p>
            </FadeUp>
            <FadeUp className="mt-14">
              <h2 className="h-display text-4xl">The challenge</h2>
              {cs.challenge.map((p, i) => <p key={i} className="mt-5 text-[17px] leading-relaxed text-body/75">{p}</p>)}
            </FadeUp>
            <FadeUp className="mt-14">
              <h2 className="h-display text-4xl">Our approach</h2>
              <ul className="mt-6 space-y-4">
                {cs.solution.map((p, i) => (
                  <li key={i} className="flex gap-4 text-[17px] leading-relaxed text-body/75">
                    <Check className="mt-1 h-5 w-5 shrink-0 text-accent" aria-hidden="true" />{p}
                  </li>
                ))}
              </ul>
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="bg-navy py-16 text-white md:py-24">
        <div className="container-site">
          <FadeUp>
            <h2 className="h-display text-4xl text-white md:text-5xl">How we measure success</h2>
            <p className="mt-4 max-w-xl text-white/70">Before work starts, we agree these measures with the client and report on them throughout the engagement.</p>
          </FadeUp>
          <ul className="mt-10 grid gap-6 md:grid-cols-3">
            {cs.results.map((r, i) => (
              <FadeUp as="li" key={i} delay={i * 0.06} className="border-t border-white/25 pt-6">
                <span className="text-sm font-semibold text-accent">Measure {i + 1}</span>
                <p className="mt-3 text-xl font-medium leading-snug text-white">{r}</p>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      <Link to={permalink(`/insights/case-studies/${next.slug}/`)} className="group block bg-mist">
        <div className="container-site flex flex-col justify-between gap-6 py-14 md:flex-row md:items-center">
          <p className="font-display text-3xl text-white group-hover:text-accent md:text-4xl">{next.title}</p>
          <span className="flex shrink-0 items-center gap-4 font-semibold text-white">
            Next case study
            <span className="flex h-14 w-14 items-center justify-center rounded-full bg-navy text-white transition-colors group-hover:bg-accent group-hover:text-onaccent">
              <ArrowRight className="h-6 w-6 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </span>
          </span>
        </div>
      </Link>

      <CTA />
    </>
  )
}
