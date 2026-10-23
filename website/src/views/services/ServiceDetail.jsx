import { useEffect, useState } from 'react'
import { Link, Navigate, useLocation, useParams } from 'react-router-dom'
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
  const valid = service && (legacy || !params.category || service.category === params.category || service.formerCategory === params.category)
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
  // Sections of the page body, in order. Each one only appears when the service has that content.
  const parts = [
    { id: 'overview', label: 'Overview' },
    { id: 'included', label: 'What is included' },
    { id: 'benefits', label: 'What you can expect' },
    service.whoFor?.length > 0 && { id: 'who', label: 'Who it is for' },
    { id: 'process', label: 'How we work' },
    service.tools?.length > 0 && { id: 'tools', label: 'Tools we use' },
    studies.length > 0 && { id: 'cases', label: 'Case studies' },
    { id: 'faqs', label: 'Questions' },
  ].filter(Boolean)

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

      <section className="bg-canvas pb-24 pt-16 md:pt-24">
        <div className="container-site grid gap-14 lg:grid-cols-[230px_minmax(0,1fr)] lg:gap-20">
          <OnThisPage parts={parts} />

          <article className="min-w-0 max-w-[780px]">
            <Part id="overview" title="Overview" first>
              <p className="text-[22px] leading-[1.5] tracking-[-0.01em] text-site-heading md:text-[24px]">{service.short}</p>
              {service.overview.map((p, i) => (
                <p key={i} className="mt-5 text-[17px] leading-[1.8] text-site-text">{p}</p>
              ))}
            </Part>

            <Part id="included" title="What is included" lead="The scope is agreed with you in writing before work starts. These are the parts most clients ask for.">
              <ul className="grid gap-x-10 sm:grid-cols-2">
                {service.included.map((item) => (
                  <li key={item} className="flex gap-3 border-b border-site-line py-4 text-[16px] leading-[1.55] text-site-heading">
                    <Check className="mt-1 h-4 w-4 shrink-0 text-site-brand" strokeWidth={2.5} aria-hidden="true" />{item}
                  </li>
                ))}
              </ul>
            </Part>

            <Part id="benefits" title="What you can expect">
              <div className="grid gap-x-10 gap-y-9 sm:grid-cols-2">
                {service.benefits.map((b) => (
                  <div key={b.title} className="border-l-2 border-site-brand pl-5">
                    <h3 className="text-[18px] font-semibold text-site-heading">{b.title}</h3>
                    <p className="mt-2 text-[15.5px] leading-[1.7] text-site-text">{b.text}</p>
                  </div>
                ))}
              </div>
            </Part>

            {service.whoFor?.length > 0 && (
              <Part id="who" title="Who it is for">
                <ul className="space-y-3">
                  {service.whoFor.map((w) => (
                    <li key={w} className="flex gap-4 text-[17px] leading-[1.6] text-site-heading">
                      <span className="mt-[11px] h-1.5 w-1.5 shrink-0 rounded-full bg-site-brand" aria-hidden="true" />{w}
                    </li>
                  ))}
                </ul>
              </Part>
            )}

            <Part id="process" title="How we work">
              <ol className="relative">
                <span className="absolute bottom-6 left-[17px] top-6 w-px bg-site-line" aria-hidden="true" />
                {service.process.map((step, i) => (
                  <li key={step.title} className="relative flex gap-6 pb-8 last:pb-0">
                    <span className="relative z-10 grid h-9 w-9 shrink-0 place-items-center rounded-full border border-site-line bg-[#FFFFFF] text-[13px] font-semibold text-site-brand">{i + 1}</span>
                    <div className="pt-1.5">
                      <h3 className="text-[18px] font-semibold text-site-heading">{step.title}</h3>
                      <p className="mt-1.5 text-[15.5px] leading-[1.7] text-site-text">{step.text}</p>
                    </div>
                  </li>
                ))}
              </ol>
            </Part>

            {service.tools?.length > 0 && (
              <Part id="tools" title="Tools we use" lead="We choose tools that fit your budget and your team. These are the ones we use most often for this service.">
                <ul className="flex flex-wrap gap-2">
                  {service.tools.map((t) => (
                    <li key={t} className="rounded-md border border-site-line px-3.5 py-2 text-[14px] text-site-heading">{t}</li>
                  ))}
                </ul>
              </Part>
            )}

            {studies.length > 0 && (
              <Part id="cases" title="Case studies">
                <div className="grid gap-5 sm:grid-cols-2">
                  {studies.map((c) => (
                    <Link key={c.slug} to={permalink(`/insights/case-studies/${c.slug}/`)} className="group flex flex-col border-t-2 border-site-heading pt-5">
                      <p className="text-[12px] font-semibold uppercase tracking-[0.14em] text-site-muted">{c.clientType}</p>
                      <h3 className="mt-3 text-[19px] font-semibold leading-snug text-site-heading group-hover:text-site-brand">{c.title}</h3>
                      <p className="mt-2 text-[15px] leading-[1.7] text-site-text">{c.summary}</p>
                      <span className="mt-4 inline-flex items-center gap-1.5 text-[14px] font-semibold text-site-brand">
                        Read the case study <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                      </span>
                    </Link>
                  ))}
                </div>
              </Part>
            )}

            <Part id="faqs" title={`Questions about ${service.name}`}>
              <FAQ items={service.faqs} />
            </Part>

            {related.length > 0 && (
              <div className="mt-16 border-t border-site-line pt-10">
                <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-site-muted">Related services</p>
                <ul className="mt-5 flex flex-wrap gap-x-8 gap-y-3">
                  {related.map((s) => (
                    <li key={s.slug}>
                      <Link to={servicePath(s)} className="inline-flex items-center gap-1.5 text-[15px] font-medium text-site-heading underline decoration-site-line underline-offset-[6px] transition-colors hover:text-site-brand hover:decoration-site-brand">
                        {s.name} <ArrowUpRight className="h-3.5 w-3.5" aria-hidden="true" />
                      </Link>
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </article>
        </div>
      </section>

      <CTA title={`Ready to talk about ${service.name.toLowerCase()}?`} />
    </>
  )
}

// One titled part of the page body, separated from the one before by a rule.
function Part({ id, title, lead, first = false, children }) {
  return (
    <section id={id} className={`scroll-mt-28 ${first ? '' : 'mt-16 border-t border-site-line pt-14'}`}>
      <h2 className="text-[28px] font-medium leading-[1.15] tracking-[-0.02em] text-site-heading md:text-[34px]">{title}</h2>
      {lead && <p className="mt-3 max-w-2xl text-[16px] leading-[1.7] text-site-text">{lead}</p>}
      <div className="mt-8">{children}</div>
    </section>
  )
}

// Contents list that stays beside the text and marks the part being read.
function OnThisPage({ parts }) {
  const [active, setActive] = useState(parts[0]?.id)
  const ids = parts.map((p) => p.id).join(' ')
  useEffect(() => {
    const els = ids.split(' ').map((id) => document.getElementById(id)).filter(Boolean)
    const obs = new IntersectionObserver(
      (entries) => entries.forEach((e) => { if (e.isIntersecting) setActive(e.target.id) }),
      { rootMargin: '-30% 0px -60% 0px' },
    )
    els.forEach((el) => obs.observe(el))
    return () => obs.disconnect()
  }, [ids])
  return (
    <aside className="hidden lg:block">
      <nav aria-label="On this page" className="sticky top-28">
        <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-site-muted">On this page</p>
        <ul className="mt-5 border-l border-site-line">
          {parts.map((p) => (
            <li key={p.id}>
              <a
                href={`#${p.id}`}
                className={`-ml-px block border-l-2 py-2 pl-4 text-[14.5px] transition-colors ${active === p.id ? 'border-site-brand font-semibold text-site-heading' : 'border-transparent text-site-text hover:text-site-heading'}`}
              >
                {p.label}
              </a>
            </li>
          ))}
        </ul>
        <div className="mt-10 border-t border-site-line pt-6">
          <p className="text-[14px] leading-[1.6] text-site-text">Not sure where to start? A first call is free.</p>
          <Link to="/contact-us/" className="mt-4 inline-flex items-center gap-1.5 text-[14px] font-semibold text-site-brand hover:underline">
            Book a consultation <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
          </Link>
        </div>
      </nav>
    </aside>
  )
}
