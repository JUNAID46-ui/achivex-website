import { Link, Navigate, useLocation, useParams } from 'react-router-dom'
import { Check, ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import FAQ from '../../components/FAQ'
import CTA from '../../components/CTA'
import ArrowLink from '../../components/ArrowLink'
import NotFound from '../NotFound'
import { getService, servicePath, servicesByCategory } from '../../data/services'
import { getCategory } from '../../data/categories'
import { getCaseStudy } from '../../data/caseStudies'
import { permalink } from '../../lib/permalinks'

export default function ServiceDetail({ singleSlug }) {
  const params = useParams()
  const service = getService(singleSlug || params.slug)
  const { pathname } = useLocation()
  const slug = singleSlug || params.slug
  // An old address from the earlier catalog is valid too; it is sent on to the current page below.
  const legacy = service && service.slug !== slug
  const valid = service && (legacy || !params.category || service.category === params.category)
  useSeo(valid ? service.name : 'Page Not Found', valid ? `${service.short} ${service.heroLine}` : undefined)
  if (!valid) return <NotFound />
  // Old or other-format addresses (after a permalink change) go to the current one.
  const canonical = servicePath(service)
  if ((pathname.endsWith('/') ? pathname : `${pathname}/`) !== canonical) return <Navigate to={canonical} replace />

  const category = service.category ? getCategory(service.category) : null
  const cs = getCaseStudy(service.caseStudy)
  const siblings = category ? servicesByCategory(category.slug).filter((s) => s.slug !== service.slug) : []
  const crumbs = [{ label: 'Services', to: '/services/' }]
  if (category) crumbs.push({ label: category.name, to: `/services/${category.slug}/` })
  crumbs.push({ label: service.name })

  return (
    <>
      {/* Hero with breadcrumb */}
      <section className="relative overflow-hidden bg-canvas text-white">
        <Img src={service.heroImage} alt={`${service.name} at AchiveX`} className="absolute inset-0 h-full w-full object-cover opacity-50" />
        <div className="absolute inset-0 bg-canvas/40" aria-hidden="true" />
        <div className="container-site relative flex min-h-[520px] flex-col justify-between py-12 md:min-h-[600px]">
          <Breadcrumb light items={crumbs} />
          <FadeUp className="max-w-4xl pt-24">
            <h1 className="h-display text-5xl text-white md:text-7xl">{service.name}</h1>
            <p className="mt-6 max-w-2xl text-xl leading-relaxed text-white/85">{service.heroLine}</p>
          </FadeUp>
        </div>
      </section>

      {/* Overview */}
      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site grid gap-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
          <FadeUp>
            <h2 className="h-display text-4xl md:text-5xl">Overview</h2>
            <span className="mt-6 block h-1 w-14 bg-accent" aria-hidden="true" />
            {service.overview.map((p, i) => (
              <p key={i} className={`mt-6 leading-relaxed text-body/75 ${i === 0 ? 'text-xl' : 'text-[17px]'}`}>{p}</p>
            ))}
          </FadeUp>
          <FadeUp delay={0.1} className="overflow-hidden">
            <Img src={service.images[0]} alt={`${service.name} overview`} className="aspect-[4/5] w-full object-cover transition-transform duration-700 hover:scale-105" />
          </FadeUp>
        </div>
      </section>

      {/* What is included */}
      <section className="bg-mist py-20 md:py-28">
        <div className="container-site grid gap-14 lg:grid-cols-[0.8fr_1.2fr]">
          <FadeUp className="lg:sticky lg:top-28 lg:self-start">
            <h2 className="h-display text-4xl md:text-5xl">What is included</h2>
            <p className="mt-6 text-lg leading-relaxed text-body/70">The scope is agreed with you up front. These are the pieces most clients ask for.</p>
            <div className="mt-10 hidden overflow-hidden lg:block">
              <Img src={service.images[1]} alt={`${service.name} team at work`} className="aspect-[4/3] w-full object-cover transition-transform duration-700 hover:scale-105" />
            </div>
          </FadeUp>
          <ul className="border-t border-body/15">
            {service.included.map((item) => (
              <FadeUp as="li" key={item} className="flex items-start gap-4 border-b border-body/15 py-5">
                <Check className="mt-1 h-4 w-4 shrink-0 text-accent" aria-hidden="true" />
                <span className="text-[17px] leading-snug text-white">{item}</span>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      {/* Benefits */}
      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display max-w-2xl text-4xl md:text-5xl">What you can expect</h2></FadeUp>
          <div className="mt-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {service.benefits.map((b, i) => (
              <FadeUp key={b.title} delay={i * 0.06} className="border-t border-body/15 pt-6">
                <h3 className="text-lg font-semibold">{b.title}</h3>
                <p className="mt-3 leading-relaxed text-body/60">{b.text}</p>
              </FadeUp>
            ))}
          </div>
        </div>
      </section>

      {/* Process */}
      <section className="bg-canvas py-20 text-white md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl text-white md:text-5xl">Our process</h2></FadeUp>
          <ol className="mt-14 grid gap-10 md:grid-cols-4 md:gap-8">
            {service.process.map((step, i) => (
              <FadeUp as="li" key={step.title} delay={i * 0.08} className="border-t border-white/20 pt-6">
                <span className="text-sm text-accent">Step {i + 1}</span>
                <h3 className="mt-3 text-lg font-semibold text-white">{step.title}</h3>
                <p className="mt-2 leading-relaxed text-white/65">{step.text}</p>
              </FadeUp>
            ))}
          </ol>
        </div>
      </section>

      {/* Related case study */}
      {cs && (
        <section className="bg-mist py-20 md:py-28">
          <div className="container-site">
            <FadeUp className="grid items-center gap-10 md:grid-cols-[1fr_1fr]">
              <Link to={permalink(`/insights/case-studies/${cs.slug}/`)} className="group block overflow-hidden">
                <Img src={cs.image.replace('w=2000', 'w=1200')} alt={cs.title} className="aspect-[16/11] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
              </Link>
              <div>
                <h2 className="h-display text-4xl">{cs.title}</h2>
                <p className="mt-3 text-sm font-semibold text-accent">Related case study / {cs.clientType}</p>
                <p className="mt-5 text-lg leading-relaxed text-body/70">{cs.summary}</p>
                <Link to={permalink(`/insights/case-studies/${cs.slug}/`)} className="group mt-8 inline-flex items-center gap-2 font-semibold text-white hover:text-accent">
                  Read Case Study <ArrowRight className="h-4 w-4 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </Link>
              </div>
            </FadeUp>
          </div>
        </section>
      )}

      <CTA title={`Ready to talk about ${service.name.toLowerCase()}?`} />

      {/* FAQ */}
      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
          <FadeUp>
            <h2 className="h-display text-4xl md:text-5xl">Questions we often hear</h2>
            {siblings.length > 0 && (
              <div className="mt-10 border-t border-body/15 pt-6">
                <p className="font-semibold text-white">More in {category.name}</p>
                <ul className="mt-4 space-y-3">
                  {siblings.map((s) => (
                    <li key={s.slug}><ArrowLink to={servicePath(s)} className="text-[15px] text-white">{s.name}</ArrowLink></li>
                  ))}
                </ul>
              </div>
            )}
          </FadeUp>
          <FadeUp delay={0.1}><FAQ items={service.faqs} /></FadeUp>
        </div>
      </section>

    </>
  )
}
