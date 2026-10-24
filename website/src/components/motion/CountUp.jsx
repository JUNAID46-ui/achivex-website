import { useEffect, useRef, useState } from 'react'
import { animate, useInView, useReducedMotion } from 'framer-motion'

// A number that counts up from zero the first time it scrolls into view. The final value is in the
// server HTML, so search engines and visitors without JavaScript still see it.
export default function CountUp({ value, suffix = '', duration = 1.8, className = '' }) {
  const ref = useRef(null)
  const inView = useInView(ref, { once: true, margin: '-40px' })
  const reduce = useReducedMotion()
  const target = Number(value) || 0
  const [shown, setShown] = useState(target)
  const started = useRef(false)

  useEffect(() => {
    if (reduce || started.current) return
    if (!inView) { setShown(0); return }
    started.current = true
    const c = animate(0, target, { duration, ease: [0.16, 1, 0.3, 1], onUpdate: (v) => setShown(Math.round(v)) })
    return () => c.stop()
  }, [inView, reduce, target, duration])

  return <span ref={ref} className={`tabular-nums ${className}`}>{shown}{suffix}</span>
}
