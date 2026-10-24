import { Link } from 'react-router-dom'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import ArrowLink from '../../components/ArrowLink'
import { InnerHero, IntroSplit, CardGrid, Steps, FaqBlock } from '../../components/inner/InnerSections'
import { faqSchema } from '../../lib/schema'
import CTA from '../../components/CTA'
import { categories } from '../../data/categories'
import { getService, servicePath, categoryPath } from '../../data/services'
import jumpTo from '../../utils/jumpTo'
import { pageContent } from '../../data/pageContent'

export default function ServicesOverview() {
  const pc = pageContent['/services/']
  useSeo('Services', 'Explore AchiveX services: web development, custom software, AI and automation, CRM, SEO, AEO and GEO, social media and ads management.', faqSchema(pc.faqs))
  const multi = categories.filter((c) => !c.single)
  const singles = categories.filter((c) => c.single)

  return (
    <>
      <InnerHero crumbs={[{ label: 'Services' }]} eyebrow={pc.heroEyebrow} title={pc.heroTitle} text={pc.heroText} image={pc.heroImage} alt={pc.heroImageAlt} />
      <IntroSplit eyebrow={pc.introEyebrow} title={pc.introTitle} keyword={pc.introKeyword} paragraphs={pc.introParagraphs} />
      <nav aria-label="Service areas" className="bg-canvas">
        <ul className="container-site flex flex-wrap gap-x-8 gap-y-3 border-t border-site-line py-6 text-[15px] font-semibold">
          {categories.map((c, i) => (
            <li key={c.slug}>
              <a href={`#${c.slug}`} onClick={(e) => jumpTo(e, c.slug)} className="text-site-heading transition-colors hover:text-navy">
                <span className="mr-2 text-navy">{String(i + 1).padStart(2, '0')}</span>{c.name}
              </a>
            </li>
          ))}
        </ul>
      </nav>

      {multi.map((c, i) => {
        const reverse = i % 2 === 1
        return (
          <section key={c.slug} id={c.slug} className={`scroll-mt-24 py-20 md:py-28 ${reverse ? 'bg-mist' : 'bg-canvas'}`}>
            <div className="container-site grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
              <FadeUp className={reverse ? 'lg:order-2' : ''}>
                <Link to={categoryPath(c)} className="group block overflow-hidden rounded-[28px]">
                  <Img src={c.image} alt={`${c.name} services`} className="aspect-[5/4] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                </Link>
              </FadeUp>
              <FadeUp delay={0.1}>
                <h2 className="h-display flex items-baseline gap-4 text-5xl md:text-6xl">
                  <span className="text-2xl text-accent">{String(categories.indexOf(c) + 1).padStart(2, '0')}</span>
                  <Link to={categoryPath(c)} className="transition-colors hover:text-accent">{c.name}</Link>
                </h2>
                <p className="mt-6 text-lg leading-relaxed text-body/80">{c.description}</p>
                <ul className="mt-8 divide-y divide-white/10 border-y border-white/10">
                  {c.services.map((slug) => {
                    const s = getService(slug)
                    return (
                      <li key={slug}>
                        <ArrowLink to={servicePath(s)} className="w-full justify-between py-4 text-[17px] font-semibold text-white">{s.name}</ArrowLink>
                      </li>
                    )
                  })}
                </ul>
              </FadeUp>
            </div>
          </section>
        )
      })}

      {singles.length > 0 && <section className="bg-canvas py-20 text-white md:py-28">
        <div className="container-site">
          <FadeUp>
            <h2 className="h-display max-w-2xl text-5xl text-white md:text-6xl">{pc.specialistTitle}</h2>
          </FadeUp>
          <div className="mt-14 grid gap-6 md:grid-cols-3">
            {singles.map((c, i) => {
              const s = getService(c.slug)
              return (
                <FadeUp key={c.slug} delay={i * 0.08}>
                  <Link to={servicePath(s)} id={c.slug} className="group block scroll-mt-24">
                    <div className="overflow-hidden">
                      <Img src={s.images[0].replace('w=1600', 'w=900')} alt={c.name} className="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    </div>
                    <h3 className="mt-6 flex items-baseline gap-3 text-2xl font-bold text-white transition-colors group-hover:text-accent">
                      <span className="font-display text-xl text-accent">{String(categories.indexOf(c) + 1).padStart(2, '0')}</span>{c.name}
                    </h3>
                    <p className="mt-3 leading-relaxed text-white/70">{s.short}</p>
                  </Link>
                </FadeUp>
              )
            })}
          </div>
        </div>
      </section>}

      <CardGrid eyebrow={pc.whyEyebrow} title={pc.whyTitle} text={pc.whyText} items={pc.whyItems} columns={4} />
      <Steps eyebrow={pc.processEyebrow} title={pc.processTitle} text={pc.processText} items={pc.process} />
      <FaqBlock title={pc.faqTitle} text={pc.faqText} items={pc.faqs} />
      <CTA />
    </>
  )
}
