import { motion } from 'framer-motion'
import Img from '../Img'
import CountUp from '../motion/CountUp'

// Full width photo with a statement and figures that count up as the band scrolls into view.
// Stats: { value, suffix?, label }.
export default function StatsBand({ title, image, alt = '', stats = [] }) {
  return (
    <section className="relative isolate overflow-hidden bg-ink text-white">
      <Img src={image} alt={alt} sizes="100vw" className="absolute inset-0 -z-20 h-full w-full object-cover" />
      <div className="absolute inset-0 -z-10 bg-gradient-to-b from-site-hero/80 via-site-hero/55 to-site-hero/85" aria-hidden="true" />
      <div className="container-site flex min-h-[620px] flex-col justify-between py-20 md:min-h-[700px] md:py-24">
        <motion.h2
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: '-80px' }}
          transition={{ duration: 0.9, ease: [0.22, 1, 0.36, 1] }}
          className="mx-auto max-w-4xl text-center text-[34px] font-light leading-[1.15] tracking-[-0.025em] text-[#FFFFFF] md:text-[56px]"
        >
          {title}
        </motion.h2>
        <dl className={`mt-16 grid gap-10 text-center ${stats.length >= 3 ? "sm:grid-cols-3" : "sm:grid-cols-2"}`}>
          {stats.map((s, i) => (
            <motion.div
              key={s.label}
              initial={{ opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '-40px' }}
              transition={{ duration: 0.8, delay: i * 0.12, ease: [0.22, 1, 0.36, 1] }}
            >
              <dd className="text-[64px] font-extralight leading-none tracking-[-0.04em] text-[#FFFFFF] md:text-[88px]">
                <CountUp value={s.value} suffix={s.suffix} />
              </dd>
              <dt className="mx-auto mt-4 max-w-[240px] text-[16px] leading-snug text-site-line">{s.label}</dt>
            </motion.div>
          ))}
        </dl>
      </div>
    </section>
  )
}
