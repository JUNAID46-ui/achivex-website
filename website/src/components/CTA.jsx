import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import FadeUp from './FadeUp'

// Closing call to action. Plain black at rest; diagonal panels light up around the pointer on hover.
// `variant` is kept so existing pages keep working; every page now uses the same design.
export default function CTA({
  title = "Let's build what your business needs next.",
  text = 'Tell us about your goals. We will set up a free consultation and follow up with a clear plan and price.',
  button = 'Book a free consultation',
  to = '/contact-us/',
}) {
  const ref = useRef(null)
  const move = (e) => {
    const el = ref.current
    if (!el) return
    const r = el.getBoundingClientRect()
    el.style.setProperty('--mx', `${e.clientX - r.left}px`)
    el.style.setProperty('--my', `${e.clientY - r.top}px`)
  }
  return (
    <section ref={ref} onPointerMove={move} className="cta-shine relative overflow-hidden bg-ink text-white">
      <div className="cta-shine-glow" aria-hidden="true" />
      <div className="cta-shine-grain" aria-hidden="true" />
      <FadeUp className="container-site relative py-24 md:py-32">
        <h2 className="h-display max-w-2xl text-4xl text-white md:text-6xl">{title}</h2>
        {text && <p className="mt-6 max-w-xl text-lg leading-relaxed text-white/75">{text}</p>}
        <Link
          to={to}
          className="group mt-10 inline-flex items-center gap-3 rounded-full border-[1.5px] border-white px-7 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-white hover:text-ink"
        >
          {button}
          <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
        </Link>
      </FadeUp>
    </section>
  )
}
