import { Link, Navigate, useLocation, useParams } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Check, ArrowRight, ArrowUpRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import FAQ from '../../components/FAQ'
import CTA from '../../components/CTA'
import NotFound from '../NotFound'
import { getService, servicePath, services, servicesByCategory } from '../../data/services'
import { getCategory } from '../../data/categories'
import { caseStudies, getCaseStudy } from '../../data/caseStudies'
import { permalink } from '../../lib/permalinks'
import { faqSchema } from '../../lib/schema'
import { InnerHero } from '../../components/inner/InnerSections'

export default function ServiceDetail({ singleSlug }) {
  const params = useParams()
  const service = getService(singleSlug || params.slug)
  const { pathname } = useLocation()
  const slug = singleSlug || params.slug
  // An old address from the earlier catalog is valid too; it is sent on to the current page below.
  const legacy = service && service.slug !== slug
  const valid = service && (legacy || !params.category || service.category === params.category)
  useSeo(valid ? service.name : 'Page Not Found', valid ? `${service.short} ${service.heroLine}` : undefined, valid && [
    {
      '@context': 'https://schema.org',
      '@type': 'Service',
      name: service.name,
      serviceType: service.name,
      description: service.short,
      image: service.heroImage,
      areaServed: 'United States',
      provider: { '@type': 'Organization', name: 'AchiveX LLC', url: typeof window !== 'undefined' ? window.location.origin : undefined },
    },
    faqSchema(service.faqs),
  ])
  if (!valid) return <NotFound />
  // Old or other-format addresses (after a permalink change) go to the current one.
  const canonical = servicePath(service)
  if ((pathname.endsWith('/') ? pathname : `${pathname}/`) !== canonical) return <Navigate to={canonical} replace />

  const category = service.category ? getCategory(service.category) : null
  // Case studies that used this service, with the one linked to the service first.
  const linked = getCaseStudy(service.caseStudy)
  const studies = [linked, ...caseStudies.filter((c) => c !== linked && (c.services || []).includes(service.slug))].filter(Boolean).slice(0, 2)
  const siblings = category ? servicesByCategory(category.slug).filter((s) => s.slug !== service.slug) : []
  const related = [...siblings, ...services.filter((s) => s.category !== service.category)].slice(0, 6)
  const crumbs = [{ label: 'Services', to: '/services/' }]
  if (category) crumbs.push({ label: category.name, to: `/services/${category.slug}/` })
  crumbs.push({ label: service.name })
  const eyebrow = 'text-[13px] font-semibold uppercase tracking-[0.18em] text-navy'
  const h2 = 'text-[30px] font-medium leading-[1.12] tracking-[-0.025em] text-[#1C1C1C] md:text-[40px]'

  return (
    <>
      <InnerHero
        action={{ label: 'Book a free consultation', to: '/contact-us/' }}
        crumbs={crumbs}
        eyebrow={category ? category.name : 'Services'}
        title={service.name}
        text={service.heroLine}
        image={service.heroImage}
        alt={`${service.name} at AchiveX`}
      />

      {/* Overview and what it covers on the left; the sidebar stays in view until the column ends. */}
      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site grid gap-14 lg:grid-cols-[1fr_380px] lg:gap-20">
          <div className="min-w-0">
            <FadeUp className="rounded-[18px] border border-[#E3E6EB] border-l-[4px] border-l-[#0F2C9C] bg-[#F6F7F9] p-6 md:p-8">
              <p className="text-[19px] leading-[1.6] text-[#1C1C1C] md:text-[21px]">{service.short}</p>
            </FadeUp>
            <FadeUp className="mt-12 space-y-5">
              {service.overview.map((p, i) => (
                <p key={i} className={i === 0 ? 'text-[18px] leading-[1.75] text-[#2A2F37]' : 'text-[17px] leading-[1.75] text-[#4B515B]'}>{p}</p>
              ))}
            </FadeUp>

            <FadeUp className="mt-16"><h2 className={h2}>What this covers</h2></FadeUp>
            <ul className="mt-8 space-y-3">
              {service.benefits.map((b, i) => (
                <FadeUp as="li" key={b.title} delay={i * 0.05} className="group rounded-[16px] border border-[#E3E6EB] bg-[#FFFFFF] p-6 transition-all duration-500 hover:-translate-y-0.5 hover:border-[#0F2C9C]/40 hover:shadow-[0_20px_40px_-30px_rgba(7,21,45,0.4)] md:p-7">
                  <h3 className="text-[18px] font-semibold text-[#1C1C1C]">{b.title}</h3>
                  <p className="mt-2 text-[15.5px] leading-[1.7] text-[#5B616B]">{b.text}</p>
                </FadeUp>
              ))}
            </ul>

            {service.whoFor?.length > 0 && (
              <>
                <FadeUp className="mt-16"><h2 className={h2}>Who it is for</h2></FadeUp>
                <ul className="mt-8 grid gap-3 md:grid-cols-2">
                  {service.whoFor.map((w) => (
                    <FadeUp as="li" key={w} className="flex gap-3 rounded-[16px] bg-[#F6F7F9] p-5 text-[15.5px] leading-[1.6] text-[#2A2F37]">
                      <Check className="mt-1 h-4 w-4 shrink-0 text-navy" aria-hidden="true" />{w}
                    </FadeUp>
                  ))}
                </ul>
              </>
            )}
          </div>

          <aside className="lg:sticky lg:top-28 lg:self-start">
            <FadeUp className="rounded-[20px] border border-[#E3E6EB] bg-[#FFFFFF] p-7 shadow-[0_30px_60px_-45px_rgba(7,21,45,0.45)]">
              <p className="text-[12px] font-semibold uppercase tracking-[0.18em] text-[#999999]">What you get</p>
              <ul className="mt-5 space-y-3">
                {service.included.map((item) => (
                  <li key={item} className="flex gap-3 text-[14.5px] leading-[1.55] text-[#2A2F37]">
                    <Check className="mt-0.5 h-4 w-4 shrink-0 text-navy" aria-hidden="true" />{item}
                  </li>
                ))}
              </ul>
              {service.tools?.length > 0 && (
                <>
                  <p className="mt-8 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#999999]">Typical stack</p>
                  <ul className="mt-4 flex flex-wrap gap-2">
                    {service.tools.map((t) => (
                      <li key={t} className="rounded-full border border-[#E3E6EB] bg-[#F6F7F9] px-3 py-1.5 text-[13px] font-medium text-[#1C1C1C]">{t}</li>
                    ))}
                  </ul>
                </>
              )}
              <Link to="/contact-us/" className="mt-8 flex items-center justify-center gap-2 rounded-full bg-[#07152D] px-6 py-3.5 text-[14px] font-semibold text-[#FFFFFF] transition-colors hover:bg-[#0F2C9C]">
                Book a free consultation <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
              </Link>
            </FadeUp>
          </aside>
        </div>
      </section>

      {/* How we run it */}
      <section className="bg-[#EEF0F3] py-20 md:py-28">
        <div className="container-site">
          <FadeUp>
            <p className={eyebrow}>How we run it</p>
            <h2 className={`mt-4 max-w-2xl ${h2}`}>How a {service.name.toLowerCase()} project works</h2>
          </FadeUp>
          <ol className="mt-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            {service.process.map((step, i) => (
              <FadeUp as="li" key={step.title} delay={i * 0.1} className="relative pt-8">
                <span className="absolute inset-x-0 top-0 h-px bg-[#C9CED6]" aria-hidden="true" />
                <motion.span
                  className="absolute left-0 top-0 h-[2px] w-full origin-left bg-[#0F2C9C]"
                  initial={{ scaleX: 0 }}
                  whileInView={{ scaleX: 1 }}
                  viewport={{ once: true, margin: '-60px' }}
                  transition={{ duration: 1, delay: 0.2 + i * 0.25, ease: [0.22, 1, 0.36, 1] }}
                  aria-hidden="true"
                />
                <span className="font-mono text-[13px] text-[#999999]">{String(i + 1).padStart(2, '0')}</span>
                <h3 className="mt-3 text-[19px] font-semibold text-[#1C1C1C]">{step.title}</h3>
                <p className="mt-2 text-[15px] leading-[1.7] text-[#5B616B]">{step.text}</p>
              </FadeUp>
            ))}
          </ol>
        </div>
      </section>

      {/* Where we have done this */}
      {studies.length > 0 && (
        <section className="bg-canvas py-20 md:py-28">
          <div className="container-site">
            <FadeUp>
              <p className={eyebrow}>Case studies</p>
              <h2 className={`mt-4 ${h2}`}>Where this work fits</h2>
            </FadeUp>
            <div className={`mt-12 grid gap-5 ${studies.length > 1 ? 'md:grid-cols-2' : 'md:max-w-2xl'}`}>
              {studies.map((c, i) => (
                <FadeUp key={c.slug} delay={i * 0.08}>
                  <Link to={permalink(`/insights/case-studies/${c.slug}/`)} className="group flex h-full flex-col rounded-[20px] border border-[#E3E6EB] bg-[#FFFFFF] p-7 transition-all duration-500 hover:-translate-y-1 hover:border-[#0F2C9C]/40 hover:shadow-[0_30px_60px_-40px_rgba(7,21,45,0.45)] md:p-8">
                    <p className="font-mono text-[12px] uppercase tracking-[0.14em] text-[#999999]">{c.clientType}</p>
                    <h3 className="mt-4 text-[22px] font-medium leading-snug tracking-[-0.015em] text-[#1C1C1C]">{c.title}</h3>
                    <p className="mt-3 text-[15.5px] leading-[1.7] text-[#5B616B]">{c.summary}</p>
                    {c.results?.length > 0 && (
                      <>
                        <p className="mt-6 font-mono text-[11px] uppercase tracking-[0.14em] text-[#999999]">What we measure</p>
                        <ul className="mt-3 flex flex-wrap gap-2">
                          {c.results.map((r) => <li key={r} className="rounded-full bg-[#F6F7F9] px-3 py-1.5 text-[12.5px] text-[#2A2F37]">{r}</li>)}
                        </ul>
                      </>
                    )}
                    <span className="min-h-7 flex-1" aria-hidden="true" />
                    <span className="flex items-center justify-between border-t border-[#E3E6EB] pt-5 text-[14px] font-semibold text-[#1C1C1C]">
                      Read the case study
                      <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
                    </span>
                  </Link>
                </FadeUp>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Questions */}
      <section className="bg-[#EEF0F3] py-20 md:py-28">
        <div className="container-site grid gap-12 lg:grid-cols-[0.8fr_1.2fr]">
          <FadeUp>
            <p className={eyebrow}>FAQs</p>
            <h2 className={`mt-4 ${h2}`}>Questions about {service.name}</h2>
          </FadeUp>
          <FadeUp delay={0.1}><FAQ items={service.faqs} /></FadeUp>
        </div>
      </section>

      {/* Related services */}
      {related.length > 0 && (
        <section className="bg-canvas py-14 md:py-16">
          <div className="container-site">
            <p className="font-mono text-[12px] uppercase tracking-[0.16em] text-[#999999]">Related services</p>
            <ul className="mt-5 flex flex-wrap gap-2.5">
              {related.map((s) => (
                <li key={s.slug}>
                  <Link to={servicePath(s)} className="inline-flex items-center gap-1.5 rounded-full border border-[#D5D9E0] px-4 py-2 text-[14px] text-[#1C1C1C] transition-colors hover:border-[#07152D] hover:bg-[#07152D] hover:text-[#FFFFFF]">
                    {s.name} <ArrowUpRight className="h-3.5 w-3.5" aria-hidden="true" />
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </section>
      )}

      <CTA title={`Ready to talk about ${service.name.toLowerCase()}?`} />
    </>
  )
}
