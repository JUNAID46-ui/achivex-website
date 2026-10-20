import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ServiceCard from '../../components/ServiceCard'
import CTA from '../../components/CTA'
import { getService } from '../../data/services'
import { getCaseStudy } from '../../data/caseStudies'

export default function ServiceCategory({ category }) {
  useSeo(category.name.endsWith('Services') ? category.name : `${category.name} Services`, `${category.short} AchiveX ${category.name.toLowerCase()} services for businesses in the USA and beyond.`)
  const items = category.services.map(getService)
  const cs = getCaseStudy(category.caseStudy)

  return (
    <>
      <section className="grid bg-navy text-white lg:min-h-[560px] lg:grid-cols-2">
        <div className="flex flex-col justify-between px-5 py-12 sm:px-8 lg:py-16 lg:pl-[max(2.5rem,calc((100vw-1280px)/2+2.5rem))] lg:pr-16">
          <Breadcrumb light items={[{ label: 'Services', to: '/services/' }, { label: category.name }]} />
          <div className="mt-16">
            <h1 className="h-display text-5xl text-white md:text-6xl">{category.name}</h1>
            <span className="mt-8 block h-1 w-16 bg-accent" aria-hidden="true" />
            <p className="mt-8 max-w-lg text-lg leading-relaxed text-white/80">{category.short}</p>
          </div>
        </div>
        <div className="relative min-h-[320px] overflow-hidden">
          <Img src={category.heroImage} alt={`${category.name} services at AchiveX`} className="absolute inset-0 h-full w-full object-cover" />
        </div>
      </section>

      <section className="bg-canvas py-20 md:py-28">
        <FadeUp className="container-site grid gap-10 lg:grid-cols-[1fr_2fr]">
          <h2 className="h-display text-4xl md:text-5xl">Where we help</h2>
          <p className="text-2xl font-light leading-relaxed text-body/80 md:text-[28px]">{category.description}</p>
        </FadeUp>
      </section>

      <section className="border-t border-body/10 bg-canvas py-20 md:py-28">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <h2 className="h-display text-5xl">{category.name} services</h2>
            <p className="max-w-md text-body/70">Choose a service to see what is included, how we work and common questions.</p>
          </FadeUp>
          <div className={`mt-12 grid gap-x-8 gap-y-14 sm:grid-cols-2 ${items.length > 4 ? 'lg:grid-cols-3' : items.length === 3 ? 'lg:grid-cols-3' : 'lg:grid-cols-2'}`}>
            {items.map((s, i) => (
              <FadeUp key={s.slug} delay={(i % 3) * 0.06}><ServiceCard service={s} index={i} /></FadeUp>
            ))}
          </div>
        </div>
      </section>

      {cs && (
        <section className="bg-canvas py-20 md:py-28">
          <div className="container-site">
            <FadeUp>
              <Link to={`/insights/case-studies/${cs.slug}/`} className="group grid border-y border-body/15 md:grid-cols-2">
                <div className="overflow-hidden">
                  <Img src={cs.image} alt={cs.title} className="h-full min-h-[280px] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                </div>
                <div className="flex flex-col justify-center py-10 md:p-14">
                  <h2 className="h-display text-4xl">{cs.title}</h2>
                  <p className="mt-3 text-sm font-semibold text-accent">Related case study / {cs.clientType}</p>
                  <p className="mt-5 leading-relaxed text-body/60">{cs.summary}</p>
                  <span className="mt-8 inline-flex items-center gap-2 font-medium text-white group-hover:text-accent">
                    Read Case Study <ArrowRight className="h-4 w-4 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                  </span>
                </div>
              </Link>
            </FadeUp>
          </div>
        </section>
      )}

      <CTA variant="split" title={`Talk to us about ${category.name.toLowerCase()}.`} />
    </>
  )
}
