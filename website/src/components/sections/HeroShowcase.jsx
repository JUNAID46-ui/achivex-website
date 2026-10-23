import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { img } from '../../data/images'
import CurvedFrame from '../CurvedFrame'

// Home hero: headline, button, a featured card and a rotating badge on the left,
// a three image slider on the right with a curved cut out on its lower left corner.
// Every text, link and image can be changed in the admin Page Builder (Home hero);
// these are the defaults when a field is left empty.
const defaultSlides = [
  img('1498050108023-c5249f4df085', 1400),
  img('1677442136019-21780ecad995', 1400),
  img('1551288049-bebda4e38f71', 1400),
]
const defaults = {
  line1: 'Smart Technology', line2: 'Meets Real Growth',
  lead: 'Websites, custom software, AI automation and growth marketing, planned and delivered by one team that explains every step in plain language.',
  ctaText: 'Explore Our Services', ctaLink: '/services/',
  cardImage: img('1531297484001-80022131f5a1', 700), cardTitle: 'AI & Automation', cardText: 'Chatbots. Workflows. Smarter CRM.', cardLink: '/services/ai-and-automation/',
  badgeLabel: 'Book a call', badgeText: 'FREE CONSULTATION', badgeLink: '/contact-us/',
}

function RotatingBadge({ label, words, to }) {
  const text = `${words} \u2022 ${words} \u2022 `
  return (
    <div className="relative grid h-[190px] w-[190px] shrink-0 place-items-center xl:h-[210px] xl:w-[210px]">
      <svg viewBox="0 0 200 200" className="absolute inset-0 h-full w-full animate-[spin_18s_linear_infinite] motion-reduce:animate-none" aria-hidden="true">
        <defs>
          <path id="hero-badge-circle" d="M100,100 m-76,0 a76,76 0 1,1 152,0 a76,76 0 1,1 -152,0" />
        </defs>
        <text className="fill-[#1F1F1F] text-[11px] font-semibold tracking-[3.6px]">
          <textPath href="#hero-badge-circle" textLength="472" lengthAdjust="spacing">{text}</textPath>
        </text>
      </svg>
      <Link
        to={to}
        className="absolute flex h-[112px] w-[112px] flex-col items-center justify-center gap-1 rounded-full bg-[#080808] text-[14px] text-[#FFFFFF] shadow-[0_10px_26px_rgba(0,0,0,0.16)] transition-transform duration-200 hover:scale-105 xl:h-[122px] xl:w-[122px]"
      >
        <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
        {label}
      </Link>
    </div>
  )
}

export default function HeroShowcase({ d = {} }) {
  const v = (k) => (typeof d[k] === 'string' && d[k].trim() ? d[k] : defaults[k])
  const urls = (Array.isArray(d.slides) ? d.slides : []).filter(Boolean)
  const slides = (urls.length ? urls : defaultSlides).map((src, i) => ({ src, alt: `${v('line1')} ${v('line2')}, image ${i + 1}` }))
  const [active, setActive] = useState(0)

  // Moves to the next image every 5 seconds. Clicking a bar restarts the timer.
  useEffect(() => {
    const id = setInterval(() => setActive((i) => (i + 1) % slides.length), 5000)
    return () => clearInterval(id)
  }, [active, slides.length])

  return (
    <section className="overflow-hidden bg-canvas pb-12 pt-8 md:pt-10">
      <div className="container-site grid gap-12 lg:grid-cols-[1fr_1.08fr] lg:gap-8">
        <div className="relative z-10 flex flex-col pt-2 lg:justify-between">
          <div>
            <h1 className="motion-rise text-[40px] font-medium leading-[1.1] tracking-[-0.03em] text-[#111111] sm:text-[54px] lg:whitespace-nowrap lg:text-[52px] xl:text-[64px]">
              {v('line1')}
              <br />
              {v('line2')}
            </h1>
            <p className="motion-rise-late mt-5 max-w-[480px] text-[15px] leading-[1.65] text-[#666666]">{v('lead')}</p>
            <Link
              to={v('ctaLink')}
              className="mt-7 inline-flex w-fit items-center gap-4 rounded-full bg-[#2B2927] py-1.5 pl-6 pr-1.5 text-[15px] text-[#FFFFFF] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#141414]"
            >
              {v('ctaText')}
              <span className="grid h-10 w-10 place-items-center rounded-full bg-[#FFFFFF] text-[#141414]">
                <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
              </span>
            </Link>
          </div>

          <div className="mt-12 flex flex-wrap items-start gap-x-[52px] gap-y-6 lg:mt-10 lg:flex-nowrap">
            <Link to={v('cardLink')} className="group block w-[280px] shrink-0 xl:w-[300px]">
              <div className="mb-4 aspect-[262/170] overflow-hidden rounded-[22px] bg-[#1A2129] shadow-[0_8px_24px_rgba(0,0,0,0.08)]">
                <img
                  width="600"
                  height="390"
                  loading="lazy"
                  src={v('cardImage')}
                  alt={v('cardTitle')}
                  className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
              </div>
              <p className="mb-1 text-[18px] font-semibold tracking-[-0.2px] text-[#161616]">{v('cardTitle')}</p>
              <p className="text-[13.5px] text-[#6E6E6E]">{v('cardText')}</p>
            </Link>
            <div className="lg:-mt-9">
              <RotatingBadge label={v('badgeLabel')} words={v('badgeText').toUpperCase()} to={v('badgeLink')} />
            </div>
          </div>
        </div>

        <div className="flex w-full flex-col items-center">
          <CurvedFrame>
            {slides.map((s, i) => (
              <img
                key={s.src}
                src={s.src}
                srcSet={/images\.unsplash\.com/.test(s.src) ? [768, 1080, 1440].map((w) => `${s.src.replace(/([?&])w=\d+/, `$1w=${w}`)} ${w}w`).join(', ') : undefined}
                sizes="(min-width: 1024px) 620px, 100vw"
                width="1100"
                height="1030"
                alt={s.alt}
                loading={i === 0 ? 'eager' : 'lazy'}
                fetchPriority={i === 0 ? 'high' : undefined}
                className={`absolute inset-0 h-full w-full object-cover transition-all duration-700 ease-in-out ${i === active ? 'scale-100 opacity-100' : 'scale-105 opacity-0'}`}
              />
            ))}
          </CurvedFrame>

          <div className="mt-6 flex items-center justify-center gap-2.5 sm:ml-12">
            {slides.map((s, i) => (
              <button
                key={s.src}
                type="button"
                onClick={() => setActive(i)}
                aria-label={`Show image ${i + 1}`}
                aria-current={i === active}
                className={`h-[7px] rounded-[20px] transition-all duration-300 ${i === active ? 'w-[105px] bg-navy' : 'w-[34px] bg-[#DCDCDC] hover:bg-[#C8C8C8]'}`}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
