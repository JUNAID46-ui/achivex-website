import { MapPin, Phone, Mail } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Breadcrumb from '../../components/Breadcrumb'
import CTA from '../../components/CTA'
import { locations } from '../../data/about'
import { site } from '../../data/site'
import { pageContent } from '../../data/pageContent'

export default function GlobalPresence() {
  useSeo('Our Global Presence', 'AchiveX LLC is headquartered in Wyoming, USA.')
  const hq = locations.find((l) => l.hq)
  const c = pageContent['/about-us/global-presence/']
  return (
    <>
      <section className="bg-navy pb-16 pt-12 text-white">
        <div className="container-site">
          <Breadcrumb light items={[{ label: 'About', to: '/about-us/' }, { label: 'Our Global Presence' }]} />
          <FadeUp className="mt-14 max-w-3xl">
            <h1 className="h-display text-5xl text-white md:text-6xl">{c.heroTitle}</h1>
            <p className="mt-6 text-lg leading-relaxed text-white/80">{c.heroText}</p>
          </FadeUp>
        </div>
      </section>

      {/* Map with headquarters card overlaid */}
      <section className="relative bg-mist">
        <iframe
          title="Map showing Wyoming, USA"
          src={site.mapEmbed}
          className="block h-[420px] w-full grayscale md:h-[560px]"
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        />
        <div className="pt-8 md:pointer-events-none md:absolute md:inset-x-0 md:top-10 md:pt-0">
          <div className="container-site">
            <FadeUp className="max-w-md bg-canvas p-8 md:pointer-events-auto">
              <div className="flex items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-full bg-accent text-onaccent"><MapPin className="h-5 w-5" aria-hidden="true" /></span>
                <h2 className="text-2xl font-bold">{hq.city}, {hq.country}</h2>
              </div>
              <p className="mt-2 text-sm font-semibold text-accent">{hq.type}</p>
              {hq.address && <p className="mt-4 text-sm text-body/70">{hq.address}</p>}
              <div className="mt-5 space-y-2 text-[15px]">
                <a href={site.phoneHref} className="flex items-center gap-2 text-white hover:text-accent"><Phone className="h-4 w-4 text-accent" aria-hidden="true" />{hq.phone}</a>
                <a href={`mailto:${hq.email}`} className="flex items-center gap-2 text-white hover:text-accent"><Mail className="h-4 w-4 text-accent" aria-hidden="true" />{hq.email}</a>
              </div>
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-24">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl md:text-5xl">{c.locationsTitle}</h2></FadeUp>
          <ul className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {locations.map((l) => (
              <FadeUp as="li" key={l.city} className="bg-canvas p-8">
                <MapPin className="h-6 w-6 text-accent" aria-hidden="true" />
                <h3 className="mt-5 text-xl font-bold">{l.city}, {l.country}</h3>
                <p className="mt-1 text-body/60">{l.type}</p>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      <CTA />
    </>
  )
}
