import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion, useInView } from 'framer-motion'
import { ArrowRight, Check } from 'lucide-react'
import Img from '../Img'

// Text blocks scroll on the left while the picture on the right stays put and changes with the block
// being read. When the last block has passed, both columns scroll away together.
// Items: { title, text, points?, image, alt?, link?, to? }.
function Block({ item, index, onActive }) {
  const ref = useRef(null)
  const inView = useInView(ref, { margin: '-45% 0px -45% 0px' })
  useEffect(() => { if (inView) onActive(index) }, [inView, index, onActive])
  return (
    <div ref={ref} className="flex min-h-0 flex-col justify-center py-10 lg:min-h-[85vh] lg:py-0">
      <div className="mb-8 overflow-hidden rounded-[22px] lg:hidden">
        <Img src={item.image} alt={item.alt || ''} className="aspect-[5/4] w-full object-cover" />
      </div>
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true, margin: '-80px' }}
        transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
      >
        <h3 className="text-[30px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[40px]">{item.title}</h3>
        {item.text && <p className="mt-5 max-w-md text-[17px] leading-[1.7] text-site-text">{item.text}</p>}
        {item.points?.length > 0 && (
          <ul className="mt-7 space-y-3">
            {item.points.map((p) => (
              <li key={p} className="flex gap-3 text-[16px] text-site-heading">
                <span className="mt-[3px] grid h-5 w-5 shrink-0 place-items-center rounded-full bg-site-brand/10 text-site-brand"><Check className="h-3 w-3" strokeWidth={3} aria-hidden="true" /></span>
                {p}
              </li>
            ))}
          </ul>
        )}
        {item.link && item.to && (
          <Link to={item.to} className="group mt-9 inline-flex items-center gap-2 border-b-2 border-site-heading pb-1 text-[15px] font-semibold text-site-heading transition-colors hover:border-site-brand hover:text-site-brand">
            {item.link}
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
          </Link>
        )}
      </motion.div>
    </div>
  )
}

export default function StickyShowcase({ eyebrow, title, items = [] }) {
  const list = items.filter((i) => i && i.title)
  const [active, setActive] = useState(0)
  if (!list.length) return null
  const cur = list[active] || list[0]
  return (
    <section className="bg-canvas py-24 md:py-32">
      <div className="container-site">
        {(eyebrow || title) && (
          <div className="max-w-3xl">
            {eyebrow && <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-site-brand">{eyebrow}</p>}
            {title && <h2 className="mt-4 text-[34px] font-medium leading-[1.1] tracking-[-0.03em] text-site-heading md:text-[52px]">{title}</h2>}
          </div>
        )}
        <div className="mt-6 grid gap-x-20 lg:mt-0 lg:grid-cols-[1fr_1.05fr]">
          <div className="relative">
            {/* Progress rail beside the blocks on large screens. */}
            <div className="absolute -left-8 bottom-[20vh] top-[20vh] hidden w-px bg-site-line xl:block" aria-hidden="true">
              <motion.div className="w-full bg-site-brand" animate={{ height: `${((active + 1) / list.length) * 100}%` }} transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }} />
            </div>
            {list.map((item, i) => <Block key={item.title} item={item} index={i} onActive={setActive} />)}
          </div>
          <div className="hidden lg:block">
            <div className="sticky top-[12vh] flex h-[76vh] items-center">
              <div className="relative h-full max-h-[640px] w-full overflow-hidden rounded-[28px] bg-site-hero">
                <AnimatePresence initial={false}>
                  <motion.div
                    key={active}
                    className="absolute inset-0"
                    initial={{ opacity: 0, scale: 1.06 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.9, ease: [0.22, 1, 0.36, 1] }}
                  >
                    <Img src={cur.image} alt={cur.alt || ''} className="h-full w-full object-cover" />
                  </motion.div>
                </AnimatePresence>
                <div className="absolute inset-0 bg-gradient-to-t from-site-hero/85 via-transparent to-transparent" aria-hidden="true" />
                <div className="absolute inset-x-6 bottom-6 flex items-end justify-between gap-6">
                  <AnimatePresence mode="wait">
                    <motion.p
                      key={active}
                      initial={{ opacity: 0, y: 14 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -10 }}
                      transition={{ duration: 0.45 }}
                      className="rounded-2xl bg-[#FFFFFF]/95 px-5 py-4 text-[15px] font-semibold text-site-heading shadow-[0_20px_40px_-20px_rgba(0,0,0,0.5)] backdrop-blur"
                    >
                      {cur.title}
                    </motion.p>
                  </AnimatePresence>
                  <div className="flex gap-1.5" aria-hidden="true">
                    {list.map((_, i) => (
                      <span key={i} className={`h-1.5 rounded-full transition-all duration-500 ${i === active ? 'w-8 bg-[#FFFFFF]' : 'w-1.5 bg-[#FFFFFF]/45'}`} />
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
