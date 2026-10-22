import { useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { ArrowRight, ArrowRightCircle, ArrowUpRight, Check, Phone, Mail, MapPin, ExternalLink } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import Button from '../components/Button'
import FadeUp from '../components/FadeUp'
import Img from '../components/Img'
import InsightCard from '../components/InsightCard'
import ArrowLink from '../components/ArrowLink'
import FAQ from '../components/FAQ'
import CTA from '../components/CTA'
import { img } from '../data/images'
import { site } from '../data/site'
import { categories } from '../data/categories'
import { services, getService, servicePath, categoryPath } from '../data/services'
import { industries } from '../data/industries'
import { caseStudies } from '../data/caseStudies'
import { blogs } from '../data/blogs'
import { whitepapers } from '../data/whitepapers'
import { heroStats } from '../data/about'
import { homeSections } from '../data/homeSections'
import { isLight } from '../theme'
import PageSections from '../components/PageSections'
import { safeHref } from '../utils/safeHref'
import { permalink } from '../lib/permalinks'

const mainAreas = categories.map((c) => c.slug)
const areaName = (slug) => categories.find((c) => c.slug === slug).name
const pathFor = (slug) => {
  const c = categories.find((x) => x.slug === slug)
  return c.single ? servicePath(getService(slug)) : categoryPath(c)
}

function SectionHead({ title, text, link, light = false }) {
  return (
    <FadeUp className={`grid gap-6 border-b pb-10 md:grid-cols-2 md:items-end ${light ? 'border-white/15' : 'border-body/10'}`}>
      <h2 className={`h-display text-4xl md:text-5xl ${light ? 'text-white' : ''}`}>{title}</h2>
      <div className="flex flex-col gap-4 md:items-end md:text-right">
        {text && <p className={`max-w-md text-[17px] leading-relaxed ${light ? 'text-white/65' : 'text-body/60'}`}>{text}</p>}
        {link}
      </div>
    </FadeUp>
  )
}

/* 1. Hero, laid out like the Triloe reference: pill, centered heading, notched image with floating cards and chips */
function HeroStat({ stat, dark = false, icon = false }) {
  if (!stat) return null
  return (
    <>
      <p className={`flex items-center gap-2 text-[28px] font-semibold leading-none tracking-tight lg:text-[34px] ${dark ? 'text-white' : 'text-ink'}`}>
        {stat.value}
        {icon && (
          <span className="flex h-6 w-6 items-center justify-center rounded-full bg-ink text-white">
            <Check className="h-3.5 w-3.5" aria-hidden="true" />
          </span>
        )}
      </p>
      <p className={`mt-3 text-[13px] leading-snug lg:mt-4 ${dark ? 'text-white/80' : 'text-ink/60'}`}>{stat.label}</p>
    </>
  )
}

function Hero({ d }) {
  const [first, second, third] = heroStats
  return (
    <section className="overflow-hidden bg-canvas pb-20 pt-14 md:pb-24 md:pt-20">
      <div className="container-site">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
          className="text-center"
        >
          <span className="inline-block rounded-full border border-ink/10 bg-white px-4 py-1.5 text-xs text-ink/70 shadow-[0_2px_10px_rgba(0,0,0,0.06)]">
            {d.pill}
          </span>
          <h1 className="mt-6 font-display text-[40px] font-semibold leading-[1.05] tracking-[-0.035em] text-white sm:text-6xl lg:text-[64px]">
            {d.heading}
          </h1>
          <p className="mx-auto mt-5 max-w-xl text-[15px] leading-relaxed text-body/70 md:text-base">
            {d.text}
          </p>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.15, ease: [0.22, 1, 0.36, 1] }}
          className="relative mx-auto mt-12 max-w-[760px] md:mt-16"
        >
          <div className="relative aspect-[4/3] md:aspect-[2/1]">
            <Img
              src={d.image}
              alt={d.imageAlt || ''}
              className="absolute inset-0 h-full w-full rounded-[28px] bg-cloud object-cover md:rounded-[32px]"
            />

            {/* Dark card in the top left notch */}
            {/* The ring matches the page background; bg-ink switches the colour variables inside the card, so it is set here. */}
            <div className="absolute left-0 top-0 hidden h-[44%] w-[27%] flex-col justify-between rounded-[22px] bg-ink p-5 ring-[14px] md:flex lg:p-6" style={{ '--tw-ring-color': isLight ? '#FFFFFF' : '#0B0D12' }}>
              <HeroStat stat={first} dark />
            </div>

            {/* Card overlapping the left edge */}
            <div className="absolute -left-[11%] top-[54%] hidden w-[21%] rounded-[20px] bg-white p-5 shadow-[0_18px_50px_-12px_rgba(0,0,0,0.18)] md:block lg:p-6">
              <HeroStat stat={second} />
            </div>

            {/* Card overlapping the right edge */}
            <div className="absolute -right-[8%] top-[13%] hidden w-[19%] rounded-[20px] bg-white p-5 shadow-[0_18px_50px_-12px_rgba(0,0,0,0.18)] md:block lg:p-6">
              <HeroStat stat={third} icon />
            </div>

            {/* Chips panel cut into the bottom right corner */}
            <div className="absolute bottom-0 right-0 hidden w-[49%] rounded-tl-[28px] bg-canvas pl-5 pt-5 md:block">
              <ul className="flex flex-wrap gap-2.5">
                {mainAreas.map((slug) => (
                  <li key={slug}>
                    <Link
                      to={pathFor(slug)}
                      className="inline-block rounded-full bg-white px-4 py-2.5 text-[13px] font-medium text-ink shadow-[0_4px_16px_rgba(0,0,0,0.08)] ring-1 ring-ink/5 transition-colors hover:bg-ink hover:text-white"
                    >
                      {areaName(slug)}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          </div>

          {/* Small screens: cards and chips stack under the image */}
          <div className="mt-5 grid grid-cols-3 gap-3 md:hidden">
            <div className="rounded-2xl bg-ink p-4"><HeroStat stat={first} dark /></div>
            <div className="rounded-2xl bg-white p-4 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.2)]"><HeroStat stat={second} /></div>
            <div className="rounded-2xl bg-white p-4 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.2)]"><HeroStat stat={third} /></div>
          </div>
          <ul className="mt-5 flex flex-wrap justify-center gap-2 md:hidden">
            {mainAreas.map((slug) => (
              <li key={slug}>
                <Link to={pathFor(slug)} className="inline-block rounded-full bg-white px-4 py-2 text-[13px] font-medium text-ink shadow-[0_4px_16px_rgba(0,0,0,0.08)] ring-1 ring-ink/5">
                  {areaName(slug)}
                </Link>
              </li>
            ))}
          </ul>
        </motion.div>

        <div className="mt-12 text-center md:mt-14">
          <Link
            to={d.buttonLink || '/contact-us/'}
            className="group inline-flex items-center gap-2.5 rounded-full bg-accent px-7 py-3.5 text-sm font-medium text-onaccent transition-colors hover:bg-white hover:text-onaccent"
          >
            {d.buttonText}
            <ArrowRightCircle className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true" />
          </Link>
        </div>
      </div>
    </section>
  )
}

/* 2. Intro statement with facts taken from the site data */
function Intro({ d }) {
  const facts = [
    { value: 'Wyoming, USA', label: 'Headquarters' },
    { value: String(categories.length), label: 'Service areas' },
    { value: String(services.length), label: 'Individual services' },
    { value: String(industries.length), label: 'Industries served' },
  ]
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site grid gap-16 lg:grid-cols-[1.5fr_1fr]">
        <FadeUp>
          <h2 className="sr-only">About AchiveX</h2>
          <p className="text-3xl font-light leading-snug tracking-tight text-white md:text-[40px] md:leading-[1.25]">
            {d.before}{' '}
            {d.highlight && <span className="font-medium text-slate underline decoration-accent decoration-2 underline-offset-[10px]">{d.highlight}</span>}{' '}
            {d.after}
          </p>
          <div className="mt-10">
            <ArrowLink to={d.linkTo || '/about-us/'} className="font-medium text-white">{d.linkText}</ArrowLink>
          </div>
        </FadeUp>
        <FadeUp delay={0.1}>
          <dl className="grid grid-cols-2 border-l border-t border-body/10">
            {facts.map((f) => (
              <div key={f.label} className="border-b border-r border-body/10 p-6">
                <dt className="text-sm text-body/55">{f.label}</dt>
                <dd className="mt-3 text-2xl font-medium tracking-tight text-white md:text-3xl">{f.value}</dd>
              </div>
            ))}
          </dl>
        </FadeUp>
      </div>
    </section>
  )
}

/* 4. Interactive service explorer covering every category */
function ServiceExplorer({ d }) {
  const [active, setActive] = useState(categories[0].slug)
  const cat = categories.find((c) => c.slug === active)
  const list = cat.single ? [getService(cat.slug)] : cat.services.map(getService)
  const intro = cat.single ? getService(cat.slug).overview[0] : cat.description

  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site">
        <SectionHead
          title={d.title}
          text={d.text}
          link={<ArrowLink to="/services/" className="text-[15px] font-medium text-white">View all services</ArrowLink>}
        />
        <div className="grid gap-12 pt-12 lg:grid-cols-[320px_1fr]">
          <ul role="tablist" aria-label="Service areas" className="flex gap-2 overflow-x-auto pb-2 lg:flex-col lg:gap-0 lg:overflow-visible lg:pb-0">
            {categories.map((c) => {
              const on = c.slug === active
              return (
                <li key={c.slug} className="shrink-0 lg:border-b lg:border-body/10">
                  <button
                    role="tab"
                    aria-selected={on}
                    onClick={() => setActive(c.slug)}
                    className={`flex w-full items-center justify-between gap-4 whitespace-nowrap border-b-2 px-1 py-3 text-left text-[15px] font-medium transition-colors lg:whitespace-normal lg:border-b-0 lg:border-l-2 lg:px-5 lg:py-5 lg:text-lg ${
                      on ? 'border-accent text-white' : 'border-transparent text-body/50 hover:text-white'
                    }`}
                  >
                    {c.name}
                    <ArrowRight className={`hidden h-4 w-4 transition-opacity lg:block ${on ? 'text-accent opacity-100' : 'opacity-0'}`} aria-hidden="true" />
                  </button>
                </li>
              )
            })}
          </ul>

          <AnimatePresence mode="wait">
            <motion.div
              key={active}
              role="tabpanel"
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.3 }}
              className="grid gap-10 md:grid-cols-[1.1fr_1fr]"
            >
              <div className="min-w-0">
                <h3 className="h-display text-3xl md:text-4xl">{cat.name}</h3>
                <p className="mt-5 text-[17px] leading-relaxed text-body/65">{intro}</p>
                <ul className="mt-8 border-t border-body/10">
                  {list.map((s) => (
                    <li key={s.slug} className="border-b border-body/10">
                      <ArrowLink to={servicePath(s)} className="w-full justify-between py-4 text-[16px] text-white">{s.name}</ArrowLink>
                    </li>
                  ))}
                </ul>
                <div className="mt-8">
                  <Button to={pathFor(cat.slug)} variant="outline">{cat.single ? `About ${cat.name}` : `Explore ${cat.name}`}</Button>
                </div>
              </div>
              <div className="overflow-hidden">
                <Img src={cat.image.replace('w=1600', 'w=1000')} alt={`${cat.name} services`} className="aspect-[4/5] h-full w-full bg-mist object-cover" />
              </div>
            </motion.div>
          </AnimatePresence>
        </div>
      </div>
    </section>
  )
}

/* 5. Why AchiveX */
function WhyAchivex({ d }) {
  return (
    <section className="bg-ink py-24 text-white md:py-32">
      <div className="container-site">
        <SectionHead light title={d.title} text={d.text} />
        <div className="grid gap-x-16 sm:grid-cols-2">
          {(d.items || []).map((r, i) => (
            <FadeUp key={r.title} delay={(i % 2) * 0.06} className="border-b border-white/15 py-10">
              <Check className="h-5 w-5 text-accent" aria-hidden="true" />
              <h3 className="mt-5 text-xl font-semibold text-white">{r.title}</h3>
              <p className="mt-3 max-w-md leading-relaxed text-white/65">{r.text}</p>
            </FadeUp>
          ))}
        </div>
      </div>
    </section>
  )
}

/* 6. Two service spotlights */
function Spotlights({ d }) {
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site space-y-24 md:space-y-32">
        {(d.items || []).map((s, i) => (
          <div key={s.title} className="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <FadeUp className={`overflow-hidden ${i % 2 ? 'lg:order-2' : ''}`}>
              <Img src={s.image} alt={s.alt} className="aspect-[5/4] w-full bg-mist object-cover transition-transform duration-700 hover:scale-[1.03]" />
            </FadeUp>
            <FadeUp delay={0.1}>
              <h2 className="h-display text-4xl md:text-5xl">{s.title}</h2>
              <p className="mt-6 max-w-lg text-[17px] leading-relaxed text-body/65">{s.text}</p>
              <ul className="mt-8 space-y-4">
                {(s.points || []).map((p) => (
                  <li key={p} className="flex gap-3 text-white">
                    <Check className="mt-0.5 h-5 w-5 shrink-0 text-accent" aria-hidden="true" />{p}
                  </li>
                ))}
              </ul>
              {s.link && <div className="mt-10"><Button to={s.to}>{s.link}</Button></div>}
            </FadeUp>
          </div>
        ))}
      </div>
    </section>
  )
}

/* 7. Process */
function Process({ d }) {
  return (
    <section className="bg-mist py-24 md:py-32">
      <div className="container-site">
        <SectionHead title={d.title} text={d.text} />
        <ol className="grid gap-px bg-body/10 pt-px sm:grid-cols-2 lg:grid-cols-4">
          {(d.steps || []).map((s, i) => (
            <FadeUp as="li" key={s.title} delay={i * 0.06} className="bg-mist py-10 pr-8 lg:px-8 lg:first:pl-0">
              <span className="text-sm font-semibold text-slate">Step {i + 1}</span>
              <h3 className="mt-3 text-2xl font-medium tracking-tight">{s.title}</h3>
              <p className="mt-3 leading-relaxed text-body/60">{s.text}</p>
            </FadeUp>
          ))}
        </ol>
      </div>
    </section>
  )
}

/* 8. Industries */
function Industries({ d }) {
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site">
        <SectionHead title={d.title} text={d.text} />
        <ul>
          {industries.map((ind) => (
            <li key={ind.slug} className="border-b border-body/10">
              <Link to={`/industries/${ind.slug}/`} className="group grid items-center gap-2 py-7 md:grid-cols-[1fr_1.4fr_auto] md:gap-10">
                <span className="text-2xl font-medium tracking-tight text-white transition-colors group-hover:text-slate">{ind.name}</span>
                <span className="text-[15px] text-body/55">{ind.summary}</span>
                <span className="hidden h-10 w-10 items-center justify-center rounded-full border border-body/15 text-white transition-colors group-hover:border-accent group-hover:bg-accent md:flex">
                  <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
                </span>
              </Link>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}

/* 10. Featured case study */
function FeaturedCase({ d }) {
  const cs = caseStudies.find((c) => c.slug === d.caseSlug) || caseStudies[0]
  if (!cs) return null
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site">
        <FadeUp className="grid overflow-hidden bg-navy text-white lg:grid-cols-2">
          <Img src={cs.image} alt={cs.title} className="h-full min-h-[320px] w-full bg-mist object-cover" />
          <div className="flex flex-col justify-center p-8 md:p-14">
            <h2 className="h-display text-3xl text-white md:text-4xl">{cs.title}</h2>
            <p className="mt-3 text-sm font-semibold text-accent">Featured case study / {cs.clientType}</p>
            <dl className="mt-8 space-y-6">
              <div>
                <dt className="text-sm font-semibold text-white">The challenge</dt>
                <dd className="mt-2 leading-relaxed text-white/65">{cs.challenge[0]}</dd>
              </div>
              <div>
                <dt className="text-sm font-semibold text-white">What we did</dt>
                <dd className="mt-2 leading-relaxed text-white/65">{cs.solution[1]}</dd>
              </div>
            </dl>
            <div className="mt-10"><Button to={permalink(`/insights/case-studies/${cs.slug}/`)} variant="light">Read Case Study</Button></div>
          </div>
        </FadeUp>
      </div>
    </section>
  )
}

/* 11. Latest insights */
function LatestInsights({ d }) {
  const paper = whitepapers[0]
  const items = [
    ...caseStudies.slice(1, 2).map((c) => ({ key: c.slug, to: permalink(`/insights/case-studies/${c.slug}/`), image: c.image.replace('w=2000', 'w=900'), kicker: `Case study / ${c.clientType}`, title: c.title, cta: 'Read Case Study' })),
    ...blogs.slice(0, 2).map((b) => ({ key: b.slug, to: permalink(`/insights/blogs/${b.slug}/`), image: b.image.replace('w=2000', 'w=900'), kicker: `Blog / ${b.category}`, title: b.title, cta: 'Read Article' })),
  ]
  return (
    <section className="bg-mist py-24 md:py-32">
      <div className="container-site">
        <SectionHead
          title={d.title}
          text={d.text}
          link={<ArrowLink to={permalink('/insights/blogs/')} className="text-[15px] font-medium text-white">View all insights</ArrowLink>}
        />
        <div className="grid gap-12 pt-12 md:grid-cols-3 md:gap-8">
          {items.map((it) => (
            <FadeUp key={it.key}>
              <InsightCard to={it.to} image={it.image} kicker={it.kicker} title={it.title} cta={it.cta} />
            </FadeUp>
          ))}
        </div>
        {paper && <FadeUp className="mt-16 flex flex-col justify-between gap-6 border-t border-body/10 pt-10 md:flex-row md:items-center">
          <div>
            <p className="text-xl font-semibold text-white">Free guide: {paper.title}</p>
            <p className="mt-2 max-w-xl text-body/60">{paper.description}</p>
          </div>
          <Button to={permalink(`/insights/whitepapers/${paper.slug}/`)} variant="outline">Download the guide</Button>
        </FadeUp>}
      </div>
    </section>
  )
}

/* 12. FAQ */
function HomeFaq({ d }) {
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
        <FadeUp>
          <h2 className="h-display text-4xl md:text-5xl">{d.title}</h2>
          {d.text && <p className="mt-6 max-w-sm text-[17px] leading-relaxed text-body/60">{d.text}</p>}
          <div className="mt-8"><ArrowLink to="/contact-us/" className="font-medium text-white">Ask us a question</ArrowLink></div>
        </FadeUp>
        <FadeUp delay={0.1}><FAQ items={d.items || []} /></FadeUp>
      </div>
    </section>
  )
}

/* 13. Contact strip */
function ContactStrip() {
  const items = [
    { icon: Phone, label: 'Call us', value: site.phone, href: site.phoneHref },
    { icon: Mail, label: 'Email us', value: site.email, href: `mailto:${site.email}` },
    { icon: MapPin, label: 'Location', value: site.location },
    { icon: ExternalLink, label: 'Find us on Google', value: 'Business Profile', href: site.googleProfile, external: true },
  ]
  return (
    <section className="bg-navy text-white">
      <ul className="container-site grid sm:grid-cols-2 lg:grid-cols-4">
        {items.map(({ icon: Icon, label, value, href, external }) => {
          const inner = (
            <>
              <Icon className="h-5 w-5 text-accent" aria-hidden="true" />
              <span className="mt-4 block text-sm text-white/60">{label}</span>
              <span className="mt-1 block text-lg font-medium">{value}</span>
            </>
          )
          return (
            <li key={label} className="border-b border-white/15 py-10 sm:px-6 lg:border-b-0 lg:border-r lg:first:pl-0 lg:last:border-r-0">
              {href ? (
                <a href={safeHref(href)} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})} className="block transition-colors hover:text-accent">{inner}</a>
              ) : inner}
            </li>
          )
        })}
      </ul>
    </section>
  )
}

// Maps a section key from the Page Builder to its layout.
const layouts = {
  hero: Hero,
  intro: Intro,
  explorer: ServiceExplorer,
  why: WhyAchivex,
  spotlights: Spotlights,
  process: Process,
  industries: Industries,
  featured_case: FeaturedCase,
  insights: LatestInsights,
  faq: HomeFaq,
  contact_strip: ContactStrip,
  cta: ({ d }) => <CTA title={d.title} text={d.text} button={d.button} />,
}

export const homeLayouts = layouts

export default function Home() {
  useSeo(
    null,
    'AchiveX LLC is a Wyoming based marketing and software solutions company offering web development, custom software, AI and automation, CRM, SEO, social media and ads management.',
  )
  return <PageSections sections={homeSections} />
}
