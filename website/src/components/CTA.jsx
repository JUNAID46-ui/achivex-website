import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import FadeUp from './FadeUp'

// Closing call to action. Diagonal panels are lit at a resting spot; on hover the light follows the pointer
// and glides back when the pointer leaves.
// `variant` is kept so existing pages keep working; every page now uses the same design.
export default function CTA({
  title = "Let's build what your business needs next.",
  text = 'Tell us about your goals. We will set up a free consultation and follow up with a clear plan and price.',
  button = 'Book a free consultation',
  to = '/contact-us/',
}) {
  const ref = useRef(null)
  const glow = useRef(null)
  const move = (e) => {
    const el = ref.current
    const g = glow.current
    if (!el || !g || e.pointerType === 'touch') return
    const r = g.getBoundingClientRect()
    el.classList.add('is-tracking')
    el.style.setProperty('--mx', `${e.clientX - r.left}px`)
    el.style.setProperty('--my', `${e.clientY - r.top}px`)
  }
  const leave = () => {
    const el = ref.current
    if (!el) return
    el.classList.remove('is-tracking')
    el.style.removeProperty('--mx')
    el.style.removeProperty('--my')
  }
  return (
    <section ref={ref} onPointerMove={move} onPointerLeave={leave} className="cta-shine relative overflow-hidden bg-ink text-white">
      <div ref={glow} className="cta-shine-glow" aria-hidden="true" />
      <div className="cta-shine-grain" aria-hidden="true" />
      <FadeUp className="container-site relative grid items-center gap-10 py-20 md:py-24 lg:grid-cols-[1fr_auto] lg:gap-16">
        <div>
          <h2 className="h-display max-w-3xl text-[34px] leading-[1.1] text-white md:text-[52px]">{title}</h2>
          {text && <p className="mt-5 max-w-xl text-[17px] leading-relaxed text-white/70">{text}</p>}
        </div>
        <Link
          to={to}
          className="group inline-flex w-fit items-center gap-4 rounded-full bg-[#FFFFFF] py-2 pl-7 pr-2 text-[15px] font-semibold text-[#000000] shadow-[0_20px_50px_-20px_rgba(91,124,255,0.6)] transition-transform duration-300 hover:-translate-y-0.5"
        >
          {button}
          <span className="grid h-11 w-11 place-items-center rounded-full bg-[#000000] text-[#FFFFFF] transition-transform duration-300 group-hover:-rotate-45">
            <ArrowRight className="h-4 w-4" aria-hidden="true" />
          </span>
        </Link>
      </FadeUp>
    </section>
  )
}
