import { ArrowRight } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import { InnerHero, IntroSplit, CardGrid, Steps, FaqBlock } from '../components/inner/InnerSections'
import { faqSchema } from '../lib/schema'
import { pageContent } from '../data/pageContent'
import { careers } from '../data/about'
import { site } from '../data/site'

export default function Careers() {
  const c = pageContent['/careers/']
  useSeo('Careers', 'Careers at AchiveX LLC. Learn about our culture, the roles we hire for and current open positions.', faqSchema(c.faqs))
  return (
    <>
      <InnerHero crumbs={[{ label: 'Careers' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} image={c.heroImage} alt={c.heroImageAlt} />
      <IntroSplit eyebrow={c.introEyebrow} title={c.introTitle} keyword={c.introKeyword} paragraphs={c.introParagraphs} />
      <CardGrid eyebrow="Culture" title={c.cultureTitle} items={c.culture} tone="grey" />
      <CardGrid eyebrow={c.rolesEyebrow} title={c.rolesTitle} text={c.rolesText} items={c.roles} columns={4} />
      <Steps eyebrow={c.applyEyebrow} title={c.applyTitle} items={c.applySteps} />

      <section id="positions" className="scroll-mt-24 bg-site-grey py-20 md:py-24">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <h2 className="h-display text-4xl md:text-5xl">{c.positionsTitle}</h2>
            <p className="max-w-md text-body/80">{c.positionsText}</p>
          </FadeUp>
          {careers.positions.length === 0 && (
            <FadeUp className="mt-10 border-t border-body/15 pt-8">
              <p className="text-xl font-medium text-white">{c.noPositionsTitle}</p>
              <p className="mt-3 max-w-xl leading-relaxed text-body/80">{c.noPositionsText}</p>
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
                  <span className="text-body/80">{p.team}</span>
                  <span className="text-body/80">{p.location}</span>
                  <span className="text-body/80">{p.type}</span>
                  <ArrowRight className="h-5 w-5 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </a>
              </FadeUp>
            ))}
          </ul>
          <p className="mt-8 text-body/80">
            {c.applyText} <a href={`mailto:${site.email}`} className="font-semibold text-white underline decoration-accent underline-offset-4 hover:text-accent">{site.email}</a>.
          </p>
        </div>
      </section>
      <FaqBlock title={c.faqTitle} items={c.faqs} />
    </>
  )
}
