import { Link } from 'react-router-dom'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ArrowLink from '../../components/ArrowLink'
import CTA from '../../components/CTA'
import { pageContent } from '../../data/pageContent'
import { milestones } from '../../data/about'
import { categories } from '../../data/categories'
import { services } from '../../data/services'
import { blogs } from '../../data/blogs'
import { whitepapers } from '../../data/whitepapers'
import { industries } from '../../data/industries'
import { permalink } from '../../lib/permalinks'

export default function Accomplishments() {
  useSeo('Our Accomplishments', 'Milestones and what AchiveX LLC has built so far: service areas, services, guides and articles.')
  const c = pageContent['/about-us/accomplishments/']
  const labels = c.builtLabels || []

  // Figures counted from the site content itself, so they always stay accurate.
  const built = [
    { value: categories.length, label: labels[0] ?? 'Service areas', to: '/services/' },
    { value: services.length, label: labels[1] ?? 'Individual services', to: '/services/' },
    { value: industries.length, label: labels[2] ?? 'Industries served', to: `/industries/${industries[0].slug}/` },
    { value: whitepapers.length, label: labels[3] ?? 'Free guides published', to: permalink('/insights/whitepapers/') },
    { value: blogs.length, label: labels[4] ?? 'Articles published', to: permalink('/insights/blogs/') },
  ]

  return (
    <>
      <section className="bg-canvas pb-20 pt-12 text-white md:pb-28">
        <div className="container-site">
          <Breadcrumb light items={[{ label: 'About', to: '/about-us/' }, { label: 'Our Accomplishments' }]} />
          <div className="mt-16 grid gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-end">
            <FadeUp>
              <h1 className="h-display text-5xl text-white md:text-7xl">{c.heroTitle}</h1>
            </FadeUp>
            <FadeUp delay={0.1}>
              <p className="text-lg leading-relaxed text-white/75">{c.heroText}</p>
            </FadeUp>
          </div>
        </div>
      </section>

      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display text-4xl md:text-5xl">{c.builtTitle}</h2></FadeUp>
          <ul className="mt-12 grid grid-cols-2 border-l border-t border-body/10 md:grid-cols-5">
            {built.map((b, i) => (
              <FadeUp as="li" key={b.label} delay={i * 0.05} className="border-b border-r border-body/10">
                <Link to={b.to} className="group block p-6 transition-colors hover:bg-navy md:p-8">
                  <span className="block font-display text-5xl font-medium tracking-tight text-white md:text-6xl">{b.value}</span>
                  <span className="mt-4 block text-[15px] text-body/60 group-hover:text-onaccent">{b.label}</span>
                </Link>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>

      <section className="bg-mist py-20 md:py-28">
        <div className="container-site grid gap-16 lg:grid-cols-[1fr_1.5fr]">
          <FadeUp className="lg:sticky lg:top-28 lg:self-start">
            <h2 className="h-display text-4xl md:text-5xl">{c.milestonesTitle}</h2>
            <div className="mt-10 hidden overflow-hidden lg:block">
              <Img src={c.milestonesImage} alt={c.milestonesImageAlt} className="aspect-[4/3] w-full bg-canvas object-cover" />
            </div>
          </FadeUp>
          <ol className="relative border-l-2 border-body/10 pl-10 md:pl-14">
            {milestones.map((m, i) => (
              <FadeUp as="li" key={i} className="relative pb-14 last:pb-0">
                <span className="absolute -left-[49px] top-2 h-4 w-4 rounded-full border-4 border-mist bg-accent md:-left-[65px]" aria-hidden="true" />
                <p className="font-display text-4xl font-medium tracking-tight text-white md:text-5xl">{m.date}</p>
                <h3 className="mt-4 text-2xl font-semibold">{m.title}</h3>
                {m.text && <p className="mt-3 max-w-xl leading-relaxed text-body/65">{m.text}</p>}
              </FadeUp>
            ))}
            <FadeUp as="li" className="relative">
              <span className="absolute -left-[49px] top-2 h-4 w-4 rounded-full border-2 border-body/20 bg-mist md:-left-[65px]" aria-hidden="true" />
              <p className="text-lg font-semibold text-white">{c.nextTitle}</p>
              <p className="mt-2 max-w-xl leading-relaxed text-body/65">{c.nextText}</p>
              <div className="mt-4"><ArrowLink to={permalink('/insights/newsroom/')} className="font-medium text-white">{c.nextLinkText}</ArrowLink></div>
            </FadeUp>
          </ol>
        </div>
      </section>

      <CTA />
    </>
  )
}
