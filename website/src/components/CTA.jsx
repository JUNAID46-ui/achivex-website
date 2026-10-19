import Button from './Button'
import FadeUp from './FadeUp'
import { site } from '../data/site'

// Closing call to action. "band" is the navy version; "split" is a quieter light version.
export default function CTA({
  title = 'Tell us what you are working on.',
  text = 'Book a free consultation and we will talk through your goals, your current setup and where we can help.',
  button = 'Book a Free Consultation',
  variant = 'band',
  to = '/contact-us/',
}) {
  const dark = variant === 'band'
  return (
    <section className={dark ? 'bg-navy text-white' : 'border-t border-ink/10 bg-mist'}>
      <FadeUp className="container-site grid gap-10 py-20 md:grid-cols-[1.4fr_1fr] md:items-end md:py-28">
        <div>
          <h2 className={`h-display text-4xl md:text-5xl ${dark ? 'text-white' : ''}`}>{title}</h2>
          <p className={`mt-6 max-w-xl text-lg leading-relaxed ${dark ? 'text-white/70' : 'text-ink/60'}`}>{text}</p>
        </div>
        <div className="flex flex-col items-start gap-4 md:items-end">
          <Button to={to} variant={dark ? 'light' : 'primary'}>{button}</Button>
          <a href={site.phoneHref} className={`text-[15px] transition-colors hover:text-accent ${dark ? 'text-white/70' : 'text-ink/60'}`}>
            or call {site.phone}
          </a>
        </div>
      </FadeUp>
    </section>
  )
}
