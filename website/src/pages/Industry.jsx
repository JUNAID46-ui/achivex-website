import { useParams } from 'react-router-dom'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import Img from '../components/Img'
import Breadcrumb from '../components/Breadcrumb'
import ServiceCard from '../components/ServiceCard'
import ArrowLink from '../components/ArrowLink'
import CTA from '../components/CTA'
import NotFound from './NotFound'
import { industries } from '../data/industries'
import { getService } from '../data/services'

export default function Industry() {
  const { slug } = useParams()
  const ind = industries.find((i) => i.slug === slug)
  useSeo(ind?.name, ind ? `${ind.summary} See how AchiveX supports ${ind.name.toLowerCase()} organisations.` : undefined)
  if (!ind) return <NotFound />

  return (
    <>
      <section className="relative bg-canvas text-white">
        <Img src={ind.image} alt={`${ind.name} industry`} className="absolute inset-0 h-full w-full object-cover opacity-40" />
        <div className="container-site relative flex min-h-[460px] flex-col justify-between py-12">
          <Breadcrumb light items={[{ label: 'Industries' }, { label: ind.name }]} />
          <FadeUp className="max-w-3xl pt-20">
            <h1 className="h-display text-5xl text-white md:text-7xl">{ind.name}</h1>
            <p className="mt-6 text-xl leading-relaxed text-white/85">{ind.summary}</p>
          </FadeUp>
        </div>
      </section>

      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site grid gap-12 lg:grid-cols-2">
          <FadeUp>
            <h2 className="h-display text-4xl md:text-5xl">Where we focus</h2>
            <p className="mt-6 max-w-md text-lg leading-relaxed text-body/70">
              The areas below are where {ind.name.toLowerCase()} clients most often ask for help. Every engagement starts with your own priorities.
            </p>
          </FadeUp>
          <ul className="grid gap-4 sm:grid-cols-2">
            {ind.focus.map((f, i) => (
              <FadeUp as="li" key={f} delay={i * 0.05} className="bg-mist p-7">
                <span className="block h-1 w-8 bg-accent" aria-hidden="true" />
                <p className="mt-5 text-lg font-bold text-white">{f}</p>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl md:text-5xl">Relevant services</h2></FadeUp>
          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {ind.services.map(getService).filter(Boolean).map((s) => (
              <FadeUp key={s.slug}><ServiceCard service={s} /></FadeUp>
            ))}
          </div>
          <div className="mt-14 flex flex-wrap gap-x-8 gap-y-3 border-t border-white/15 pt-8">
            <span className="font-semibold text-white">Other industries:</span>
            {industries.filter((i) => i.slug !== slug).map((i) => (
              <ArrowLink key={i.slug} to={`/industries/${i.slug}/`} className="text-white">{i.name}</ArrowLink>
            ))}
          </div>
        </div>
      </section>

      <CTA variant="split" title={`Support for ${ind.name.toLowerCase()} teams.`} />
    </>
  )
}
