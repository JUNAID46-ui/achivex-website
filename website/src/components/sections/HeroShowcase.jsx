import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { img } from '../../data/images'

// Home hero: headline, button, a featured card and a rotating badge on the left,
// a three image slider on the right with a curved cut out on its lower left corner.
const slides = [
  { src: img('1498050108023-c5249f4df085', 1400), alt: 'Laptop showing website code' },
  { src: img('1677442136019-21780ecad995', 1400), alt: 'Abstract artwork about artificial intelligence' },
  { src: img('1551288049-bebda4e38f71', 1400), alt: 'Marketing analytics dashboard on a laptop' },
]

// Outline of the slider drawn on a 550 x 515 grid, then scaled to the box with objectBoundingBox,
// so the shape holds at every width. The left edge runs down to 255, then one smooth S curve
// steps in to x 84 by 395 and the edge carries on to a rounded bottom corner.
const FRAME_PATH =
  'M36 0H514A36 36 0 0 1 550 36V479A36 36 0 0 1 514 515H120A36 36 0 0 1 84 479V395C84 320 0 330 0 255V36A36 36 0 0 1 36 0Z'

function RotatingBadge() {
  const text = 'FREE CONSULTATION • FREE CONSULTATION • '
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
        to="/contact-us/"
        className="absolute flex h-[112px] w-[112px] flex-col items-center justify-center gap-1 rounded-full bg-[#080808] text-[14px] text-[#FFFFFF] shadow-[0_10px_26px_rgba(0,0,0,0.16)] transition-transform duration-200 hover:scale-105 xl:h-[122px] xl:w-[122px]"
      >
        <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
        Book a call
      </Link>
    </div>
  )
}

export default function HeroShowcase() {
  const [active, setActive] = useState(0)

  // Moves to the next image every 5 seconds. Clicking a bar restarts the timer.
  useEffect(() => {
    const id = setInterval(() => setActive((i) => (i + 1) % slides.length), 5000)
    return () => clearInterval(id)
  }, [active])

  return (
    <section className="overflow-hidden bg-canvas pb-12 pt-8 md:pt-10">
      <div className="container-site grid gap-12 lg:grid-cols-[1fr_1.08fr] lg:gap-8">
        <div className="relative z-10 flex flex-col pt-2 lg:justify-between">
          <div>
            <h1 className="text-[40px] font-medium leading-[1.1] tracking-[-0.03em] text-[#111111] sm:text-[54px] lg:whitespace-nowrap lg:text-[52px] xl:text-[64px]">
              Smart Technology
              <br />
              Meets Real Growth
            </h1>
            <p className="mt-5 max-w-[480px] text-[15px] leading-[1.65] text-[#666666]">
              Websites, custom software, AI automation and growth marketing, planned and delivered by one
              team that explains every step in plain language.
            </p>
            <Link
              to="/services/"
              className="mt-7 inline-flex w-fit items-center gap-4 rounded-full bg-[#2B2927] py-1.5 pl-6 pr-1.5 text-[15px] text-[#FFFFFF] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#141414]"
            >
              Explore Our Services
              <span className="grid h-10 w-10 place-items-center rounded-full bg-[#FFFFFF] text-[#141414]">
                <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
              </span>
            </Link>
          </div>

          <div className="mt-12 flex flex-wrap items-center justify-between gap-y-6 lg:mt-10 lg:flex-nowrap">
            <Link to="/services/ai-and-automation/" className="group block w-[280px] shrink-0 xl:w-[300px]">
              <div className="mb-4 aspect-[262/170] overflow-hidden rounded-[22px] bg-[#1A2129] shadow-[0_8px_24px_rgba(0,0,0,0.08)]">
                <img
                  src={img('1531297484001-80022131f5a1', 700)}
                  alt="Laptop on a desk used for automation work"
                  className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
              </div>
              <p className="mb-1 text-[18px] font-semibold tracking-[-0.2px] text-[#161616]">AI &amp; Automation</p>
              <p className="text-[13.5px] text-[#6E6E6E]">Chatbots. Workflows. Smarter CRM.</p>
            </Link>
            <div className="lg:-mr-14 lg:-mt-16 xl:-mr-20">
              <RotatingBadge />
            </div>
          </div>
        </div>

        <div className="flex w-full flex-col items-center">
          <svg width="0" height="0" className="absolute" aria-hidden="true">
            <clipPath id="hero-frame" clipPathUnits="objectBoundingBox" transform="scale(0.0018182 0.0019417)">
              <path d={FRAME_PATH} />
            </clipPath>
          </svg>
          <div className="relative aspect-[550/515] w-full bg-[#E9ECF0]" style={{ clipPath: 'url(#hero-frame)' }}>
            {slides.map((s, i) => (
              <img
                key={s.src}
                src={s.src}
                alt={s.alt}
                loading={i === 0 ? 'eager' : 'lazy'}
                className={`absolute inset-0 h-full w-full object-cover transition-all duration-700 ease-in-out ${i === active ? 'scale-100 opacity-100' : 'scale-105 opacity-0'}`}
              />
            ))}
          </div>

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
