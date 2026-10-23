import { useLayoutEffect, useRef, useState } from 'react'
import { motion, useScroll, useTransform, useReducedMotion } from 'framer-motion'
import CountUp from '../motion/CountUp'

// The band holds still while the visitor scrolls, and the row of figures slides across the screen with
// the scroll (scroll up and it slides back). Once the last figure is in view the page moves on.
// Each figure counts up the first time it appears. Stats: { value, suffix?, label }.
function Stat({ s, dark }) {
  return (
    <div className="w-[260px] shrink-0 md:w-[320px]">
      <span className="block h-[3px] w-28 rounded-full bg-gradient-to-r from-[#0F2C9C] to-[#5B7CFF]" aria-hidden="true" />
      <p className={`mt-7 text-[64px] font-light leading-none tracking-[-0.04em] md:text-[84px] ${dark ? 'text-[#FFFFFF]' : 'text-[#0F2C9C]'}`}>
        <CountUp value={s.value} suffix={s.suffix} />
      </p>
      <p className={`mt-4 max-w-[240px] text-[18px] leading-snug tracking-[-0.01em] md:text-[20px] ${dark ? 'text-[#D4D4D4]' : 'text-[#1C1C1C]'}`}>{s.label}</p>
    </div>
  )
}

export default function ScrollStats({ stats = [], tone = 'light', eyebrow, title }) {
  const dark = tone === 'dark'
  const reduce = useReducedMotion()
  const wrap = useRef(null)
  const track = useRef(null)
  const [distance, setDistance] = useState(0)

  // How far the row has to travel so its last figure ends at the right edge of the content area.
  useLayoutEffect(() => {
    const measure = () => {
      if (!track.current || !track.current.parentElement) return
      setDistance(Math.max(0, track.current.scrollWidth - track.current.parentElement.clientWidth))
    }
    measure()
    window.addEventListener('resize', measure)
    return () => window.removeEventListener('resize', measure)
  }, [stats.length])

  const { scrollYProgress } = useScroll({ target: wrap, offset: ['start start', 'end end'] })
  const x = useTransform(scrollYProgress, [0.05, 0.95], [0, -distance])
  const bar = useTransform(scrollYProgress, [0.05, 0.95], ['0%', '100%'])

  const head = (eyebrow || title) && (
    <div className="mb-14 max-w-3xl md:mb-16">
      {eyebrow && <p className={`text-[13px] font-semibold uppercase tracking-[0.18em] ${dark ? 'text-[#A3A3A3]' : 'text-[#0F2C9C]'}`}>{eyebrow}</p>}
      {title && <h2 className={`mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] md:text-[44px] ${dark ? 'text-[#FFFFFF]' : 'text-[#1C1C1C]'}`}>{title}</h2>}
    </div>
  )

  if (reduce) {
    return (
      <div className="container-site py-20">
        {head}
        <div className="flex flex-wrap gap-12">{stats.map((s) => <Stat key={s.label} s={s} dark={dark} />)}</div>
      </div>
    )
  }

  // The section is several screens tall; the inner frame sticks to the top while the row slides.
  return (
    <div ref={wrap} className="relative" style={{ height: `calc(100vh + ${Math.max(distance, 600) * 1.6}px)` }}>
      <div className="sticky top-0 flex h-screen flex-col justify-center overflow-hidden">
        <div className="container-site">
          {head}
          <div className="overflow-visible">
            <motion.div ref={track} style={{ x }} className="flex w-max gap-12 md:gap-20">
              {stats.map((s) => <Stat key={s.label} s={s} dark={dark} />)}
            </motion.div>
          </div>
          <div className={`mt-16 h-px w-full ${dark ? 'bg-white/10' : 'bg-[#D5D9E0]'}`} aria-hidden="true">
            <motion.div className={`h-full ${dark ? 'bg-[#FFFFFF]' : 'bg-[#0F2C9C]'}`} style={{ width: bar }} />
          </div>
        </div>
      </div>
    </div>
  )
}
