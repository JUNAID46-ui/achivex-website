import { Check } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import CTA from '../../components/CTA'
import { pageContent } from '../../data/pageContent'
import jumpTo from '../../utils/jumpTo'

export default function Esg() {
  useSeo('Our Commitment to ESG', 'How AchiveX LLC approaches environmental, social and governance responsibilities.')
  const c = pageContent['/about-us/esg/']
  const esg = c.sections || []
  return (
    <>
      <section className="bg-canvas pb-16 pt-12 md:pb-24">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'About', to: '/about-us/' }, { label: 'Our Commitment to ESG' }]} />
          <FadeUp className="mx-auto mt-16 max-w-4xl text-center">
            <h1 className="h-display text-5xl md:text-7xl">{c.heroTitle}</h1>
            <p className="mx-auto mt-8 max-w-2xl text-lg leading-relaxed text-body/70">{c.heroText}</p>
          </FadeUp>
          <nav aria-label="ESG sections" className="mx-auto mt-12 flex max-w-xl justify-center gap-3">
            {esg.map((s) => (
              <a key={s.key} href={`#${s.key}`} onClick={(e) => jumpTo(e, s.key)} className="border border-white px-5 py-2 text-sm font-semibold text-white transition-colors hover:border-accent hover:bg-accent hover:text-canvas">
                {s.title}
              </a>
            ))}
          </nav>
        </div>
      </section>

      {esg.map((s, i) => {
        const dark = i === 1
        return (
          <section key={s.key} id={s.key} className={`scroll-mt-20 ${dark ? 'bg-navy text-white' : i === 2 ? 'bg-mist' : 'bg-canvas'}`}>
            <div className="grid lg:grid-cols-2">
              <div className={`relative min-h-[360px] overflow-hidden ${i % 2 ? 'lg:order-2' : ''}`}>
                <Img src={s.image} alt={`${s.title} commitment`} className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 hover:scale-105" />
              </div>
              <FadeUp className="px-5 py-16 sm:px-10 md:py-24 lg:px-16 xl:px-24">
                <span className={`font-display text-8xl leading-none ${dark ? 'text-accent' : 'text-white/15'}`} aria-hidden="true">{s.title[0]}</span>
                <h2 className={`h-display mt-4 text-5xl ${dark ? 'text-white' : ''}`}>{s.title}</h2>
                <p className={`mt-6 text-lg leading-relaxed ${dark ? 'text-white/80' : 'text-body/70'}`}>{s.text}</p>
                <ul className="mt-8 space-y-4">
                  {(s.commitments || []).map((item) => (
                    <li key={item} className="flex gap-3">
                      <Check className="mt-0.5 h-5 w-5 shrink-0 text-accent" aria-hidden="true" />
                      <span className={dark ? 'text-white/90' : 'text-body/80'}>{item}</span>
                    </li>
                  ))}
                </ul>
              </FadeUp>
            </div>
          </section>
        )
      })}

      <CTA variant="split" title={c.ctaTitle} text={c.ctaText} button={c.ctaButton} />
    </>
  )
}
