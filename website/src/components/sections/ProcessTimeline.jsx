import { useEffect, useRef, useState } from 'react'
import { motion, useInView, useReducedMotion } from 'framer-motion'

const STEP_MS = 4200

// Steps on a line for dark bands. The active step moves on by itself while the band is on screen,
// a bar fills under it, and hovering or focusing a step picks it. Items: { title, text }.
export default function ProcessTimeline({ items = [] }) {
  const list = items.filter((i) => i && i.title)
  const ref = useRef(null)
  const inView = useInView(ref, { margin: '-120px' })
  const reduce = useReducedMotion()
  const [active, setActive] = useState(0)
  const [paused, setPaused] = useState(false)
  const playing = inView && !paused && !reduce && list.length > 1

  useEffect(() => {
    if (!playing) return
    const t = setTimeout(() => setActive((a) => (a + 1) % list.length), STEP_MS)
    return () => clearTimeout(t)
  }, [playing, active, list.length])

  if (!list.length) return null
  const fill = list.length > 1 ? active / (list.length - 1) : 1

  return (
    <div ref={ref} onMouseLeave={() => setPaused(false)} className="relative">
      {/* Track: vertical on small screens, horizontal through the step markers on large ones. */}
      <div className="absolute bottom-4 left-[15px] top-4 w-px bg-white/15 lg:hidden" aria-hidden="true">
        <motion.div className="w-full bg-[#FFFFFF]" initial={false} animate={{ height: `${fill * 100}%` }} transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }} />
      </div>
      <div className="absolute inset-x-0 top-[15px] hidden h-px bg-white/15 lg:block" aria-hidden="true">
        <motion.div
          className="h-full bg-[#FFFFFF]"
          initial={false}
          animate={{ width: `${fill * ((list.length - 1) / list.length) * 100}%` }}
          transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
        />
      </div>

      <ol className={`relative grid gap-10 lg:gap-8 ${list.length >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3'}`}>
        {list.map((s, i) => {
          const on = i === active
          const done = i < active
          return (
            <motion.li
              key={s.title}
              initial={reduce ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '-60px' }}
              transition={{ duration: 0.7, delay: i * 0.12, ease: [0.22, 1, 0.36, 1] }}
              className="relative pl-12 lg:pl-0 lg:pr-6"
            >
              <button
                type="button"
                onMouseEnter={() => { setActive(i); setPaused(true) }}
                onFocus={() => { setActive(i); setPaused(true) }}
                onClick={() => setActive(i)}
                aria-current={on ? 'step' : undefined}
                className="group block w-full text-left focus:outline-none"
              >
                <span
                  className={`absolute left-0 top-0 grid h-[31px] w-[31px] place-items-center rounded-full border text-[12px] font-semibold tabular-nums transition-all duration-500 lg:static ${
                    on ? 'scale-110 border-[#FFFFFF] bg-[#FFFFFF] text-site-heading shadow-[0_0_0_6px_rgba(255,255,255,0.08)]' : done ? 'border-[#FFFFFF] bg-site-dark text-[#FFFFFF]' : 'border-white/25 bg-site-dark text-site-muted'
                  }`}
                >
                  {String(i + 1).padStart(2, '0')}
                </span>
                <h3 className={`mt-0 text-[22px] font-medium tracking-tight transition-colors duration-500 lg:mt-8 ${on ? 'text-[#FFFFFF]' : 'text-site-muted group-hover:text-[#FFFFFF]'}`}>{s.title}</h3>
                {s.text && <p className={`mt-3 text-[15px] leading-[1.7] transition-colors duration-500 ${on ? 'text-[#D4D4D4]' : 'text-site-muted/80'}`}>{s.text}</p>}
                <span className="mt-6 block h-[2px] w-full overflow-hidden rounded-full bg-white/10" aria-hidden="true">
                  {on && (
                    <motion.span
                      key={`${active}-${playing}`}
                      className="block h-full origin-left bg-[#FFFFFF]"
                      initial={{ scaleX: playing ? 0 : 1 }}
                      animate={{ scaleX: 1 }}
                      transition={{ duration: playing ? STEP_MS / 1000 : 0, ease: 'linear' }}
                    />
                  )}
                </span>
              </button>
            </motion.li>
          )
        })}
      </ol>
    </div>
  )
}
