import { ArrowRight } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import Img from '../components/Img'
import Breadcrumb from '../components/Breadcrumb'
import { pageContent } from '../data/pageContent'
import { careers } from '../data/about'
import { site } from '../data/site'

export default function Careers() {
  useSeo('Careers', 'Careers at AchiveX LLC. Learn about our culture and see current open positions.')
  const c = pageContent['/careers/']
  return (
    <>
      <section className="bg-canvas pb-16 pt-12">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Careers' }]} />
          <div className="mt-12 grid gap-12 lg:grid-cols-2 lg:items-end">
            <FadeUp>
              <h1 className="h-display text-5xl md:text-7xl">{c.heroTitle}</h1>
              <p className="mt-8 max-w-lg text-xl leading-relaxed text-body/70">{c.heroText}</p>
            </FadeUp>
            <FadeUp delay={0.1} className="overflow-hidden">
              <Img src={c.heroImage} alt={c.heroImageAlt} className="aspect-[16/11] w-full object-cover transition-transform duration-700 hover:scale-105" />
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="bg-navy py-20 text-white md:py-24">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl text-white md:text-5xl">{c.cultureTitle}</h2></FadeUp>
          <div className="mt-12 grid gap-10 md:grid-cols-3">
            {(c.culture || []).map((item, i) => (
              <FadeUp key={item.title} delay={i * 0.06} className="border-l-2 border-accent pl-6">
                <h3 className="text-xl font-bold text-white">{item.title}</h3>
                <p className="mt-3 leading-relaxed text-white/75">{item.text}</p>
              </FadeUp>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-24">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <h2 className="h-display text-4xl md:text-5xl">{c.positionsTitle}</h2>
            <p className="max-w-md text-body/60">{c.positionsText}</p>
          </FadeUp>
          {careers.positions.length === 0 && (
            <FadeUp className="mt-10 border-t border-body/15 pt-8">
              <p className="text-xl font-medium text-white">{c.noPositionsTitle}</p>
              <p className="mt-3 max-w-xl leading-relaxed text-body/65">{c.noPositionsText}</p>
            </FadeUp>
          )}
          <ul className={`mt-10 divide-y divide-white/10 overflow-hidden bg-canvas ${careers.positions.length ? '' : 'hidden'}`}>
            {careers.positions.map((p, i) => (
              <FadeUp as="li" key={i}>
                <a
                  href={`mailto:${site.email}?subject=${encodeURIComponent('Application: ' + p.title)}`}
                  className="group grid items-center gap-3 px-6 py-6 md:grid-cols-[2fr_1fr_1fr_1fr_auto] md:px-8"
                >
                  <span className="text-lg font-bold text-white group-hover:text-accent">{p.title}</span>
                  <span className="text-body/60">{p.team}</span>
                  <span className="text-body/60">{p.location}</span>
                  <span className="text-body/60">{p.type}</span>
                  <ArrowRight className="h-5 w-5 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </a>
              </FadeUp>
            ))}
          </ul>
          <p className="mt-8 text-body/70">
            {c.applyText} <a href={`mailto:${site.email}`} className="font-semibold text-white underline decoration-accent underline-offset-4 hover:text-accent">{site.email}</a>.
          </p>
        </div>
      </section>
    </>
  )
}
