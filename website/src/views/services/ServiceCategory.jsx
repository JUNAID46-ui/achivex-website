import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import { InnerHero, IntroSplit, CardGrid, Steps, FaqBlock } from '../../components/inner/InnerSections'
import { pageContent } from '../../data/pageContent'
import { faqSchema } from '../../lib/schema'
import ServiceCard from '../../components/ServiceCard'
import CTA from '../../components/CTA'
import { getService } from '../../data/services'
import { getCaseStudy } from '../../data/caseStudies'
import { permalink } from '../../lib/permalinks'

export default function ServiceCategory({ category }) {
  useSeo(category.name.endsWith('Services') ? category.name : `${category.name} Services`, `${category.short} AchiveX ${category.name.toLowerCase()} services for businesses in the USA and beyond.`, faqSchema(category.faqs))
  const pc = pageContent['/services/']
  const items = category.services.map(getService)
  const cs = getCaseStudy(category.caseStudy)

  return (
    <>
      <InnerHero
        crumbs={[{ label: 'Services', to: '/services/' }, { label: category.name }]}
        eyebrow="Services"
        title={category.name}
        text={category.short}
        image={category.heroImage}
        alt={`${category.name} services at AchiveX`}
        action={{ label: 'Book a free consultation', to: '/contact-us/' }}
      />

      <IntroSplit eyebrow="Where we help" title={`${category.name} for growing businesses`} paragraphs={[category.description, ...(category.overview || [])]} />

      <section className="border-t border-body/10 bg-canvas py-20 md:py-28">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <h2 className="h-display text-5xl">{category.name} services</h2>
            <p className="max-w-md text-body/80">Choose a service to see what is included, how we work and common questions.</p>
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
              <Link to={permalink(`/insights/case-studies/${cs.slug}/`)} className="group grid border-y border-body/15 md:grid-cols-2">
                <div className="overflow-hidden">
                  <Img src={cs.image} alt={cs.title} className="h-full min-h-[280px] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                </div>
                <div className="flex flex-col justify-center py-10 md:p-14">
                  <h2 className="h-display text-4xl">{cs.title}</h2>
                  <p className="mt-3 text-sm font-semibold text-accent">Related case study / {cs.clientType}</p>
                  <p className="mt-5 leading-relaxed text-body/80">{cs.summary}</p>
                  <span className="mt-8 inline-flex items-center gap-2 font-medium text-white group-hover:text-accent">
                    Read Case Study <ArrowRight className="h-4 w-4 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                  </span>
                </div>
              </Link>
            </FadeUp>
          </div>
        </section>
      )}

      <CardGrid eyebrow="Outcomes" title="What clients get from this work" items={category.outcomes} columns={4} tone="grey" />
      <Steps eyebrow={pc.processEyebrow} title={pc.processTitle} text={pc.processText} items={pc.process} />
      <FaqBlock title={`${category.name} questions`} items={category.faqs} />
      <CTA variant="split" title={`Talk to us about ${category.name.toLowerCase()}.`} />
    </>
  )
}
