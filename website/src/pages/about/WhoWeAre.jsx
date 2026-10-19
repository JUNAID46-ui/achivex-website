import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import Button from '../../components/Button'
import CTA from '../../components/CTA'
import { pageContent } from '../../data/pageContent'

export default function WhoWeAre() {
  useSeo('Who We Are', 'Learn about AchiveX LLC, a Wyoming based technology services company, its story, mission, vision and the values that guide its work.')
  const c = pageContent['/about-us/']
  return (
    <>
      <section className="bg-white">
        <div className="grid lg:min-h-[640px] lg:grid-cols-2">
          <div className="relative min-h-[360px] overflow-hidden lg:order-2">
            <Img src={c.heroImage} alt={c.heroImageAlt} className="absolute inset-0 h-full w-full object-cover" />
          </div>
          <div className="flex flex-col justify-between px-5 py-12 sm:px-8 lg:py-16 lg:pl-[max(2.5rem,calc((100vw-1280px)/2+2.5rem))] lg:pr-16">
            <Breadcrumb items={[{ label: 'About', to: '/about-us/' }, { label: 'Who We Are' }]} />
            <FadeUp className="mt-14">
              <h1 className="h-display text-5xl md:text-6xl">{c.heroTitle}</h1>
              <p className="mt-8 max-w-lg text-xl leading-relaxed text-ink/75">{c.heroText}</p>
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-28">
        <div className="container-site grid gap-14 lg:grid-cols-[1fr_1.4fr]">
          <FadeUp>
            <h2 className="h-display text-4xl md:text-5xl">{c.storyTitle}</h2>
            <span className="mt-6 block h-1 w-14 bg-accent" aria-hidden="true" />
          </FadeUp>
          <FadeUp delay={0.1} className="space-y-6 text-[17px] leading-relaxed text-ink/75">
            {(c.storyParagraphs || []).map((p, i) => <p key={i}>{p}</p>)}
          </FadeUp>
        </div>
      </section>

      <section className="bg-white py-20 md:py-28">
        <div className="container-site grid gap-12 md:grid-cols-2 md:gap-16">
          <FadeUp className="border-t-2 border-navy pt-8">
            <h2 className="h-display text-4xl md:text-5xl">{c.missionTitle}</h2>
            <p className="mt-6 max-w-md text-lg leading-relaxed text-ink/65">{c.missionText}</p>
          </FadeUp>
          <FadeUp delay={0.1} className="border-t-2 border-accent pt-8">
            <h2 className="h-display text-4xl md:text-5xl">{c.visionTitle}</h2>
            <p className="mt-6 max-w-md text-lg leading-relaxed text-ink/65">{c.visionText}</p>
          </FadeUp>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-28">
        <div className="container-site">
          <FadeUp className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <h2 className="h-display text-4xl md:text-5xl">{c.valuesTitle}</h2>
            <Button to="/about-us/leadership/" variant="outline">{c.valuesButton}</Button>
          </FadeUp>
          <ul className="mt-14 grid gap-px overflow-hidden bg-navy/10 sm:grid-cols-2 lg:grid-cols-3">
            {(c.values || []).map((v, i) => (
              <FadeUp as="li" key={v.title} delay={(i % 3) * 0.06} className="bg-white p-8 md:p-10">
                <span className="block h-1 w-8 bg-accent" aria-hidden="true" />
                <h3 className="mt-6 text-xl font-bold">{v.title}</h3>
                <p className="mt-3 leading-relaxed text-ink/70">{v.text}</p>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      <CTA variant="split" title={c.ctaTitle} />
    </>
  )
}
