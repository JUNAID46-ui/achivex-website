import { Link } from 'react-router-dom'
import { ArrowUpRight, Check } from 'lucide-react'
import Breadcrumb from '../Breadcrumb'
import ProcessTimeline from '../sections/ProcessTimeline'
import FadeUp from '../FadeUp'
import Img from '../Img'
import FAQ from '../FAQ'
import CountUp from '../motion/CountUp'

// Building blocks for inner pages (About, Industries, Insights and similar). Each one takes plain
// data, so every word can come from the admin Edit Page form.

const paras = (v) => (Array.isArray(v) ? v : v ? [v] : []).filter(Boolean)

const Eyebrow = ({ children, light = false }) =>
  children ? <p className={`text-[13px] font-semibold uppercase tracking-[0.18em] ${light ? 'text-site-muted' : 'text-navy'}`}>{children}</p> : null

// Deep navy hero with the title on the left and the lead on the right, then a wide image that
// overlaps into the section below. A dot grid and soft light follow the pointer.
export function InnerHero({ crumbs, eyebrow, title, text, image, alt, action, stats }) {
  return (
    <>
      <header className="overflow-hidden bg-ink !bg-site-hero text-white">
        <div className={`container-site relative pt-8 md:pt-10 ${image ? 'pb-[120px] md:pb-[200px]' : 'pb-16 md:pb-20'}`}>
          {crumbs && <Breadcrumb light items={crumbs} />}
          <div className="mt-12 grid gap-8 md:mt-16 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:gap-16">
            <div className="motion-rise">
              <Eyebrow light>{eyebrow}</Eyebrow>
              <h1 className="mt-5 text-[40px] font-medium leading-[1.05] tracking-[-0.035em] text-[#FFFFFF] sm:text-[54px] xl:text-[68px]">{title}</h1>
            </div>
            <div className="motion-rise-late lg:pb-2">
              {text && <p className="text-[18px] leading-[1.65] text-site-muted">{text}</p>}
              {action && (
                <Link to={action.to} className="mt-7 inline-flex items-center gap-2 border-b-2 border-[#FFFFFF] pb-1 text-[15px] font-semibold text-[#FFFFFF] transition-colors hover:border-site-muted hover:text-site-muted">
                  {action.label} <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
                </Link>
              )}
            </div>
          </div>
          {stats?.length > 0 && (
            <dl className="motion-rise-late mt-12 grid grid-cols-2 gap-8 border-t border-white/10 pt-10 md:mt-14 md:grid-cols-4">
              {stats.map((st) => (
                <div key={st.label}>
                  <dd className="text-[40px] font-light leading-none tracking-[-0.03em] text-[#FFFFFF] md:text-[48px]">
                    {typeof st.value === 'number' ? <CountUp value={st.value} suffix={st.suffix} /> : st.value}
                  </dd>
                  <dt className="mt-3 text-[14px] leading-snug text-site-muted">{st.label}</dt>
                </div>
              ))}
            </dl>
          )}
        </div>
      </header>
      {image && (
        <div className="container-site relative -mt-[90px] md:-mt-[160px]">
          <div className="overflow-hidden rounded-[28px] bg-site-grey shadow-[0_30px_80px_-45px_rgba(0,0,0,0.45)]">
            <Img priority sizes="(min-width: 1280px) 1200px, 100vw" src={image} alt={alt || title} className="motion-settle aspect-[16/9] w-full object-cover md:aspect-[21/8]" />
          </div>
        </div>
      )}
    </>
  )
}

// Heading on the left, paragraphs on the right.
export function IntroSplit({ eyebrow, title, keyword, paragraphs, tone = 'white' }) {
  if (!title && !paras(paragraphs).length) return null
  return (
    <section className={`${tone === 'grey' ? 'bg-site-grey' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
        <FadeUp>
          <Eyebrow>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[44px]">{title}</h2>
        </FadeUp>
        <FadeUp delay={0.08} className="space-y-5">
          {paras(paragraphs).map((p, i) => (
            <p key={i} className={i === 0 ? 'text-[20px] leading-[1.65] text-site-heading' : 'text-[17px] leading-[1.75] text-site-text'}>
              {i === 0 && keyword && <strong className="font-semibold text-site-heading">{keyword}</strong>}{p}
            </p>
          ))}
        </FadeUp>
      </div>
    </section>
  )
}

// Cards with a gradient line along the top that runs full width on hover. Items: { title, text, to?, icon? }.
export function CardGrid({ eyebrow, title, text, items = [], columns = 3, tone = 'white', icons = [] }) {
  const list = items.filter((i) => i && (i.title || i.text))
  if (!list.length) return null
  const cols = columns === 2 ? 'md:grid-cols-2' : columns === 4 ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-3'
  const card = tone === 'grey' ? 'bg-[#FFFFFF] border-site-line' : 'bg-site-card border-site-line'
  return (
    <section className={`${tone === 'grey' ? 'bg-site-grey' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site">
        <FadeUp className="grid gap-6 lg:grid-cols-[1fr_1fr] lg:items-end">
          <div>
            <Eyebrow>{eyebrow}</Eyebrow>
            {title && <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[44px]">{title}</h2>}
          </div>
          {text && <p className="text-[17px] leading-[1.7] text-site-text lg:justify-self-end lg:max-w-[520px]">{text}</p>}
        </FadeUp>
        <ul className={`mt-14 grid gap-5 ${cols}`}>
          {list.map((item, i) => {
            const Icon = item.icon || icons[i % (icons.length || 1)]
            const body = (
              <>
                <span className="absolute inset-x-0 top-0 h-[3px] bg-site-line" aria-hidden="true" />
                <span className="absolute left-0 top-0 h-[3px] w-16 bg-gradient-to-r from-site-hero to-[#3B5BFF] transition-all duration-700 ease-out group-hover:w-full" aria-hidden="true" />
                {Icon && (
                  <span className="mb-6 grid h-11 w-11 place-items-center rounded-xl bg-site-hero/[0.06] text-site-brand transition-colors duration-500 group-hover:bg-site-hero group-hover:text-[#FFFFFF]">
                    <Icon className="h-5 w-5" aria-hidden="true" />
                  </span>
                )}
                <h3 className="flex items-start justify-between gap-4 text-[20px] font-semibold leading-snug tracking-[-0.01em] text-site-heading">
                  {item.title}
                  {item.to && <ArrowUpRight className="mt-1 h-5 w-5 shrink-0 text-site-muted transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-navy" aria-hidden="true" />}
                </h3>
                {item.text && <p className="mt-3 text-[15.5px] leading-[1.7] text-site-text">{item.text}</p>}
              </>
            )
            const cls = `group relative block h-full overflow-hidden rounded-[18px] border p-7 pt-9 transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_24px_50px_-30px_rgba(7,21,45,0.35)] md:p-8 md:pt-10 ${card}`
            return (
              <FadeUp as="li" key={item.title || i} delay={(i % 4) * 0.06}>
                {item.to ? <Link to={item.to} className={cls}>{body}</Link> : <div className={cls}>{body}</div>}
              </FadeUp>
            )
          })}
        </ul>
      </div>
    </section>
  )
}

// Image on one side, text and an optional checklist on the other.
export function ImageSplit({ eyebrow, title, paragraphs, list, image, alt, reverse = false, tone = 'white' }) {
  if (!title && !paras(paragraphs).length) return null
  return (
    <section className={`${tone === 'grey' ? 'bg-site-grey' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        {image && (
          <FadeUp className={reverse ? 'lg:order-2' : ''}>
            <div className="overflow-hidden rounded-[28px] bg-site-grey">
              <Img sizes="(min-width: 1024px) 600px, 100vw" src={image} alt={alt || title} className="aspect-[5/4] w-full object-cover transition-transform duration-[1.2s] ease-out hover:scale-[1.04]" />
            </div>
          </FadeUp>
        )}
        <FadeUp delay={0.08}>
          <Eyebrow>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[42px]">{title}</h2>
          <div className="mt-6 space-y-5">
            {paras(paragraphs).map((p, i) => <p key={i} className="text-[17px] leading-[1.75] text-site-text">{p}</p>)}
          </div>
          {paras(list).length > 0 && (
            <ul className="mt-7 space-y-3">
              {paras(list).map((li) => (
                <li key={li} className="flex gap-3 text-[16px] leading-[1.6] text-site-heading">
                  <span className="mt-[3px] grid h-5 w-5 shrink-0 place-items-center rounded-full bg-navy/10 text-navy"><Check className="h-3 w-3" strokeWidth={3} aria-hidden="true" /></span>
                  {li}
                </li>
              ))}
            </ul>
          )}
        </FadeUp>
      </div>
    </section>
  )
}

// Dark band with steps along a line. Items: { title, text }.
export function Steps({ eyebrow, title, text, items = [] }) {
  const list = items.filter((i) => i && i.title)
  if (!list.length) return null
  return (
    <section className="overflow-hidden bg-ink py-20 text-white md:py-28">
      <div className="container-site relative">
        <FadeUp className="max-w-3xl">
          <Eyebrow light>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-white md:text-[44px]">{title}</h2>
          {text && <p className="mt-5 text-[17px] leading-[1.7] text-site-muted">{text}</p>}
        </FadeUp>
        <div className="mt-16">
          <ProcessTimeline items={list} />
        </div>
      </div>
    </section>
  )
}

// Statement cards for mission, vision and similar, with a heading above. Items: { title, text }.
export function Pair({ eyebrow, title, items = [] }) {
  const list = items.filter((i) => i && i.title && i.text)
  if (!list.length) return null
  return (
    <section className="bg-canvas py-20 md:py-28">
      <div className="container-site">
        {title && (
          <FadeUp>
            <Eyebrow>{eyebrow}</Eyebrow>
            <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[44px]">{title}</h2>
          </FadeUp>
        )}
        <div className={`grid gap-5 ${title ? 'mt-12' : ''} ${list.length >= 3 ? 'md:grid-cols-3' : 'md:grid-cols-2'}`}>
          {list.map((m, i) => (
            <FadeUp key={m.title} delay={i * 0.08} className="group relative overflow-hidden rounded-[20px] border border-site-line bg-[#FFFFFF] p-8 shadow-[0_20px_50px_-40px_rgba(7,21,45,0.4)] md:p-10">
              <span className="absolute inset-x-0 top-0 h-[3px] origin-left bg-gradient-to-r from-site-hero via-site-brand to-[#5B7CFF] transition-transform duration-700 group-hover:scale-x-100 md:scale-x-[0.35]" aria-hidden="true" />
              <p className="text-[12px] font-semibold uppercase tracking-[0.2em] text-navy">{m.title}</p>
              <p className="mt-6 text-[19px] leading-[1.6] text-site-heading">{m.text}</p>
            </FadeUp>
          ))}
        </div>
      </div>
    </section>
  )
}

// Dark rounded banner with a short message and a button, placed inside a light section.
export function DarkBanner({ eyebrow, title, text, action, tone = 'grey' }) {
  if (!title) return null
  return (
    <section className={`${tone === 'grey' ? 'bg-site-grey' : 'bg-canvas'} pb-20 md:pb-28`}>
      <div className="container-site">
        <FadeUp className="relative overflow-hidden rounded-[24px] bg-ink !bg-site-hero px-8 py-10 md:flex md:items-center md:justify-between md:gap-10 md:px-12 md:py-12">
          <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#3B5BFF]/25 blur-3xl" aria-hidden="true" />
          <div className="relative max-w-2xl">
            <Eyebrow light>{eyebrow}</Eyebrow>
            <h2 className="mt-3 text-[24px] font-medium leading-snug tracking-[-0.015em] text-[#FFFFFF] md:text-[28px]">{title}</h2>
            {text && <p className="mt-3 text-[16px] leading-[1.7] text-site-muted">{text}</p>}
          </div>
          {action && (
            <Link to={action.to} className="relative mt-7 inline-flex shrink-0 items-center gap-2 rounded-full bg-[#FFFFFF] px-6 py-3 text-[14px] font-semibold text-site-hero transition-transform duration-300 hover:-translate-y-0.5 md:mt-0">
              {action.label} <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
            </Link>
          )}
        </FadeUp>
      </div>
    </section>
  )
}

// Questions and answers with a heading column.
export function FaqBlock({ eyebrow = 'FAQs', title, text, items = [] }) {
  const list = items.filter((f) => f && f.q && f.a)
  if (!list.length) return null
  return (
    <section className="bg-canvas py-20 md:py-28">
      <div className="container-site grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
        <FadeUp>
          <Eyebrow>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[44px]">{title}</h2>
          {text && <p className="mt-5 max-w-sm text-[17px] leading-[1.7] text-site-text">{text}</p>}
        </FadeUp>
        <FadeUp delay={0.08}><FAQ items={list} /></FadeUp>
      </div>
    </section>
  )
}
