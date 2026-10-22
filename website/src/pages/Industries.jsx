import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import Img from '../components/Img'
import Breadcrumb from '../components/Breadcrumb'
import CTA from '../components/CTA'
import { industries } from '../data/industries'

// /industries/ lists every industry page, since the menu links here directly.
export default function Industries() {
  useSeo('Industries', 'The industries AchiveX works with, from financial services and healthcare to retail, manufacturing, logistics and education.')
  return (
    <>
      <section className="bg-mist pb-16 pt-12 md:pb-20">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Industries' }]} />
          <FadeUp className="mt-12 max-w-3xl">
            <h1 className="h-display text-5xl md:text-7xl">Industries we work with</h1>
            <p className="mt-6 text-lg leading-relaxed text-body/70">Each sector has its own rules and pressures. Choose yours to see where we usually help.</p>
          </FadeUp>
        </div>
      </section>
      <section className="bg-canvas py-16 md:py-24">
        <ul className="container-site grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {industries.map((ind, i) => (
            <FadeUp as="li" key={ind.slug} delay={(i % 3) * 0.05}>
              <Link to={`/industries/${ind.slug}/`} className="group block">
                <div className="overflow-hidden rounded-2xl">
                  <Img src={ind.image} alt={`${ind.name} industry`} className="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                </div>
                <p className="mt-5 flex items-center justify-between text-xl font-bold text-white">
                  {ind.name}
                  <ArrowRight className="h-5 w-5 text-navy transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </p>
                <p className="mt-2 leading-relaxed text-body/70">{ind.summary}</p>
              </Link>
            </FadeUp>
          ))}
        </ul>
      </section>
      <CTA />
    </>
  )
}
