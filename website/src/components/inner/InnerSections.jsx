import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { ArrowUpRight, Check } from 'lucide-react'
import Breadcrumb from '../Breadcrumb'
import FadeUp from '../FadeUp'
import Img from '../Img'
import FAQ from '../FAQ'

// Building blocks for inner pages (About, Industries, Insights and similar). Each one takes plain
// data, so every word can come from the admin Edit Page form.

const paras = (v) => (Array.isArray(v) ? v : v ? [v] : []).filter(Boolean)

const Eyebrow = ({ children, light = false }) =>
  children ? <p className={`text-[13px] font-semibold uppercase tracking-[0.18em] ${light ? 'text-white/60' : 'text-navy'}`}>{children}</p> : null

// Light hero: soft grey band with the title on the left and the lead on the right, then a wide
// image that overlaps into the white section below. Different from the home and blog heroes.
export function InnerHero({ crumbs, eyebrow, title, text, image, alt, action }) {
  return (
    <>
      <header className="relative overflow-hidden bg-[#F4F5F7]">
        <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(15,44,156,0.05)_1px,transparent_1px)] bg-[size:120px_100%]" aria-hidden="true" />
        <div className={`container-site relative pt-8 md:pt-10 ${image ? 'pb-[120px] md:pb-[200px]' : 'pb-16 md:pb-20'}`}>
          {crumbs && <Breadcrumb items={crumbs} />}
          <div className="mt-12 grid gap-8 md:mt-16 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:gap-16">
            <div className="motion-rise">
              <Eyebrow>{eyebrow}</Eyebrow>
              <h1 className="mt-5 text-[40px] font-medium leading-[1.05] tracking-[-0.035em] text-[#111111] sm:text-[54px] xl:text-[68px]">{title}</h1>
            </div>
            <div className="motion-rise-late lg:pb-2">
              {text && <p className="text-[18px] leading-[1.65] text-[#4B515B]">{text}</p>}
              {action && (
                <Link to={action.to} className="mt-7 inline-flex items-center gap-2 border-b-2 border-[#111111] pb-1 text-[15px] font-semibold text-[#111111] transition-colors hover:border-navy hover:text-navy">
                  {action.label} <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
                </Link>
              )}
            </div>
          </div>
        </div>
      </header>
      {image && (
        <div className="container-site relative -mt-[90px] md:-mt-[160px]">
          <div className="overflow-hidden rounded-[28px] bg-[#E9ECF0] shadow-[0_30px_80px_-45px_rgba(0,0,0,0.45)]">
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
    <section className={`${tone === 'grey' ? 'bg-[#F5F6F8]' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
        <FadeUp>
          <Eyebrow>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[44px]">{title}</h2>
        </FadeUp>
        <FadeUp delay={0.08} className="space-y-5">
          {paras(paragraphs).map((p, i) => (
            <p key={i} className={i === 0 ? 'text-[20px] leading-[1.65] text-[#2A2F37]' : 'text-[17px] leading-[1.75] text-[#4B515B]'}>
              {i === 0 && keyword && <strong className="font-semibold text-[#111111]">{keyword}</strong>}{p}
            </p>
          ))}
        </FadeUp>
      </div>
    </section>
  )
}

// Numbered cards. Items: { title, text, to? }.
export function CardGrid({ eyebrow, title, text, items = [], columns = 3, tone = 'white' }) {
  const list = items.filter((i) => i && (i.title || i.text))
  if (!list.length) return null
  const cols = columns === 2 ? 'md:grid-cols-2' : columns === 4 ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-3'
  return (
    <section className={`${tone === 'grey' ? 'bg-[#F5F6F8]' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site">
        <FadeUp className="grid gap-6 lg:grid-cols-[1fr_1fr] lg:items-end">
          <div>
            <Eyebrow>{eyebrow}</Eyebrow>
            {title && <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[44px]">{title}</h2>}
          </div>
          {text && <p className="text-[17px] leading-[1.7] text-[#4B515B] lg:justify-self-end lg:max-w-[520px]">{text}</p>}
        </FadeUp>
        <ul className={`mt-12 grid gap-5 ${cols}`}>
          {list.map((item, i) => {
            const body = (
              <>
                <span className="text-[13px] font-semibold tabular-nums text-navy">{String(i + 1).padStart(2, '0')}</span>
                <h3 className="mt-6 text-[20px] font-semibold leading-snug tracking-[-0.01em] text-[#111111]">{item.title}</h3>
                {item.text && <p className="mt-3 text-[15.5px] leading-[1.7] text-[#5B616B]">{item.text}</p>}
                {item.to && <span className="mt-6 inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#111111] group-hover:text-navy">Learn more <ArrowUpRight className="h-4 w-4" aria-hidden="true" /></span>}
              </>
            )
            const cls = `motion-lift group flex h-full flex-col rounded-[22px] border border-[#E6E9EE] p-7 transition-colors md:p-8 ${tone === 'grey' ? 'bg-[#FFFFFF]' : 'bg-[#FFFFFF] hover:border-[#C9CED6]'}`
            return (
              <FadeUp as="li" key={item.title || i} delay={(i % 3) * 0.05}>
                {item.to ? <Link to={item.to} className={`${cls} hover:border-navy`}>{body}</Link> : <div className={cls}>{body}</div>}
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
    <section className={`${tone === 'grey' ? 'bg-[#F5F6F8]' : 'bg-canvas'} py-20 md:py-28`}>
      <div className="container-site grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        {image && (
          <FadeUp className={reverse ? 'lg:order-2' : ''}>
            <div className="overflow-hidden rounded-[28px] bg-[#E9ECF0]">
              <Img sizes="(min-width: 1024px) 600px, 100vw" src={image} alt={alt || title} className="aspect-[5/4] w-full object-cover transition-transform duration-[1.2s] ease-out hover:scale-[1.04]" />
            </div>
          </FadeUp>
        )}
        <FadeUp delay={0.08}>
          <Eyebrow>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[42px]">{title}</h2>
          <div className="mt-6 space-y-5">
            {paras(paragraphs).map((p, i) => <p key={i} className="text-[17px] leading-[1.75] text-[#4B515B]">{p}</p>)}
          </div>
          {paras(list).length > 0 && (
            <ul className="mt-7 space-y-3">
              {paras(list).map((li) => (
                <li key={li} className="flex gap-3 text-[16px] leading-[1.6] text-[#2A2F37]">
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
    <section className="bg-ink py-20 text-white md:py-28">
      <div className="container-site">
        <FadeUp className="max-w-3xl">
          <Eyebrow light>{eyebrow}</Eyebrow>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-white md:text-[44px]">{title}</h2>
          {text && <p className="mt-5 text-[17px] leading-[1.7] text-white/65">{text}</p>}
        </FadeUp>
        <ol className={`mt-14 grid gap-10 sm:grid-cols-2 ${list.length >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3'}`}>
          {list.map((s, i) => (
            <FadeUp as="li" key={s.title} delay={i * 0.08} className="relative pt-6">
              <motion.span
                className="absolute inset-x-0 top-0 h-px origin-left bg-white/25"
                initial={{ scaleX: 0 }}
                whileInView={{ scaleX: 1 }}
                viewport={{ once: true, margin: '-60px' }}
                transition={{ duration: 1.1, delay: 0.15 + i * 0.12, ease: [0.22, 1, 0.36, 1] }}
                aria-hidden="true"
              />
              <span className="grid h-9 w-9 place-items-center rounded-full border border-white/30 text-[13px] font-semibold tabular-nums text-white">{i + 1}</span>
              <h3 className="mt-5 text-[19px] font-semibold text-white">{s.title}</h3>
              {s.text && <p className="mt-3 text-[15px] leading-[1.7] text-white/65">{s.text}</p>}
            </FadeUp>
          ))}
        </ol>
      </div>
    </section>
  )
}

// Two statement cards side by side, for mission and vision style pairs. Items: { title, text }.
export function Pair({ items = [] }) {
  const list = items.filter((i) => i && i.title)
  if (!list.length) return null
  return (
    <section className="bg-canvas py-20 md:py-24">
      <div className="container-site grid gap-5 md:grid-cols-2">
        {list.map((m, i) => (
          <FadeUp key={m.title} delay={i * 0.06} className={`rounded-[26px] p-9 md:p-12 ${i % 2 ? 'bg-navy' : 'bg-[#F5F6F8]'}`}>
            <p className={`text-[13px] font-semibold uppercase tracking-[0.18em] ${i % 2 ? 'text-white/60' : 'text-navy'}`}>{m.title}</p>
            <p className={`mt-6 text-[24px] font-medium leading-[1.4] tracking-[-0.015em] md:text-[28px] ${i % 2 ? 'text-white' : 'text-[#111111]'}`}>{m.text}</p>
          </FadeUp>
        ))}
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
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[44px]">{title}</h2>
          {text && <p className="mt-5 max-w-sm text-[17px] leading-[1.7] text-[#5B616B]">{text}</p>}
        </FadeUp>
        <FadeUp delay={0.08}><FAQ items={list} /></FadeUp>
      </div>
    </section>
  )
}
