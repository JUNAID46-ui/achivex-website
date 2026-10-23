import { useRef } from 'react'
import { motion, useScroll, useTransform, useReducedMotion } from 'framer-motion'
import CountUp from '../motion/CountUp'

// A row of large figures under gradient lines. The row is tied to the scroll: scrolling down slides it
// from left to right, scrolling up slides it back. Each figure counts up once. Stats: { value, suffix?, label }.
export default function ScrollStats({ stats = [], tone = 'light' }) {
  const ref = useRef(null)
  const reduce = useReducedMotion()
  const { scrollYProgress } = useScroll({ target: ref, offset: ['start end', 'end start'] })
  const x = useTransform(scrollYProgress, [0, 1], reduce ? ['0%', '0%'] : ['-16%', '12%'])
  const dark = tone === 'dark'
  return (
    <div ref={ref} className="overflow-hidden">
      <motion.dl style={{ x }} className="grid gap-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10">
        {stats.map((s, i) => (
          <motion.div
            key={s.label}
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: '-40px' }}
            transition={{ duration: 0.7, delay: i * 0.1, ease: [0.22, 1, 0.36, 1] }}
          >
            <motion.span
              className="block h-[3px] w-28 origin-left rounded-full bg-gradient-to-r from-[#0F2C9C] to-[#5B7CFF]"
              initial={{ scaleX: 0 }}
              whileInView={{ scaleX: 1 }}
              viewport={{ once: true }}
              transition={{ duration: 1, delay: 0.2 + i * 0.1, ease: [0.22, 1, 0.36, 1] }}
              aria-hidden="true"
            />
            <dd className={`mt-7 text-[64px] font-light leading-none tracking-[-0.04em] md:text-[76px] ${dark ? 'text-[#FFFFFF]' : 'text-[#0F2C9C]'}`}>
              <CountUp value={s.value} suffix={s.suffix} />
            </dd>
            <dt className={`mt-4 max-w-[260px] text-[19px] leading-snug tracking-[-0.01em] ${dark ? 'text-[#D4D4D4]' : 'text-[#1C1C1C]'}`}>{s.label}</dt>
          </motion.div>
        ))}
      </motion.dl>
    </div>
  )
}
