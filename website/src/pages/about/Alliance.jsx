import { useParams } from 'react-router-dom'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ServiceCard from '../../components/ServiceCard'
import ArrowLink from '../../components/ArrowLink'
import CTA from '../../components/CTA'
import NotFound from '../NotFound'
import { alliances } from '../../data/alliances'
import { getService } from '../../data/services'

// Shared template for every alliance page.
export default function Alliance() {
  const { slug } = useParams()
  const a = alliances.find((x) => x.slug === slug)
  useSeo(a ? `${a.name} Services` : 'Alliance', a ? `How AchiveX works with ${a.name} technology. ${a.intro}` : undefined)
  if (!a) return <NotFound />
  const others = alliances.filter((x) => x.slug !== slug)

  return (
    <>
      <section className="bg-white pb-16 pt-12 md:pb-24">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'About', to: '/about-us/' }, { label: 'Alliances' }, { label: a.name }]} />
          <div className="mt-14 grid gap-12 lg:grid-cols-[auto_1fr] lg:items-center">
            <FadeUp>
              <div className="flex h-40 w-40 items-center justify-center border border-ink/10 bg-mist md:h-52 md:w-52">
                {a.logo ? (
                  <img src={a.logo} alt={`${a.name} logo`} className="max-h-20 max-w-[70%]" />
                ) : (
                  <span className="text-center">
                    <span className="block text-2xl font-extrabold text-navy">{a.name}</span>
                  </span>
                )}
              </div>
            </FadeUp>
            <FadeUp delay={0.1}>
              <h1 className="h-display text-5xl md:text-7xl">AchiveX and {a.name}</h1>
              <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink/70">{a.intro}</p>
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="relative h-[320px] overflow-hidden md:h-[440px]">
        <Img src={a.image} alt={`Work involving ${a.name} technology`} className="h-full w-full object-cover" />
      </section>

      <section className="bg-white py-20 md:py-28">
        <div className="container-site grid gap-14 lg:grid-cols-[0.8fr_1.2fr]">
          <FadeUp>
            <h2 className="h-display text-4xl md:text-5xl">What we do with {a.name}</h2>
            <span className="mt-6 block h-1 w-14 bg-accent" aria-hidden="true" />
            <p className="mt-6 text-sm leading-relaxed text-ink/60">
              Product names are trademarks of their respective owners. Listing a platform here describes our experience with it and does not by itself imply a formal partnership.
            </p>
          </FadeUp>
          <ol className="divide-y divide-navy/10 border-y border-navy/10">
            {a.work.map((w, i) => (
              <FadeUp as="li" key={w.title} className="grid gap-4 py-8 sm:grid-cols-[60px_1fr]">
                <span className="font-display text-3xl text-accent">{String(i + 1).padStart(2, '0')}</span>
                <div>
                  <h3 className="text-xl font-bold">{w.title}</h3>
                  <p className="mt-2 leading-relaxed text-ink/70">{w.text}</p>
                </div>
              </FadeUp>
            ))}
          </ol>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl md:text-5xl">Related services</h2></FadeUp>
          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {a.services.map(getService).filter(Boolean).map((s) => (
              <FadeUp key={s.slug}><ServiceCard service={s} /></FadeUp>
            ))}
          </div>
          <div className="mt-14 flex flex-wrap gap-x-8 gap-y-3 border-t border-navy/15 pt-8">
            <span className="font-semibold text-navy">Other platforms:</span>
            {others.map((o) => (
              <ArrowLink key={o.slug} to={`/about-us/alliances/${o.slug}/`} className="text-navy">{o.name}</ArrowLink>
            ))}
          </div>
        </div>
      </section>

      <CTA title={`Planning work on ${a.name}?`} />
    </>
  )
}
