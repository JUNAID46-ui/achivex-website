import { Fragment } from 'react'
import { ArrowRight, Phone } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import { homeLayouts } from './Home'
import { homeSections } from '../data/homeSections'
import FadeUp from '../components/FadeUp'
import ContactSection from '../components/ContactSection'
import { site } from '../data/site'

// Landing page for campaigns (/free-consultation/): the home page with calls to action between its
// sections, the Contact Us form at the end and a copyright bar in place of the footer.

function scrollToForm(e) {
  e.preventDefault()
  document.getElementById('lead-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  setTimeout(() => document.getElementById('c-name')?.focus({ preventScroll: true }), 600)
}

// A call to action band between sections. Every button scrolls to the form at the end of the page.
function LandingCta({ title, text, tone = 'navy' }) {
  const bg = { navy: '!bg-site-hero', black: '!bg-site-black', brand: '!bg-site-brand' }[tone]
  return (
    <section className={`relative overflow-hidden bg-ink ${bg} py-14 md:py-16`}>
      <div className="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full bg-[#FFFFFF]/10 blur-[90px]" aria-hidden="true" />
      <FadeUp className="container-site relative flex flex-col gap-7 md:flex-row md:items-center md:justify-between">
        <div className="max-w-2xl">
          <h2 className="text-[26px] font-medium leading-[1.2] tracking-[-0.02em] text-[#FFFFFF] md:text-[34px]">{title}</h2>
          {text && <p className="mt-3 text-[16px] leading-relaxed text-[#FFFFFF]/75">{text}</p>}
        </div>
        <div className="flex shrink-0 flex-wrap gap-3">
          <a href="#lead-form" onClick={scrollToForm} className="inline-flex items-center gap-2 rounded-full bg-[#FFFFFF] px-6 py-3.5 text-[15px] font-semibold text-site-heading transition-transform hover:-translate-y-0.5">
            Book a free consultation <ArrowRight className="h-4 w-4" aria-hidden="true" />
          </a>
          <a href={site.phoneHref} className="inline-flex items-center gap-2 rounded-full border border-white/35 px-6 py-3.5 text-[15px] font-semibold text-[#FFFFFF] hover:border-[#FFFFFF]">
            <Phone className="h-4 w-4" aria-hidden="true" />{site.phone}
          </a>
        </div>
      </FadeUp>
    </section>
  )
}

// Calls to action placed after these home sections.
const CTAS = {
  intro: { title: 'Not sure where to start? The first call is free.', text: 'Tell us what you want to achieve and we will suggest the simplest way to get there.', tone: 'brand' },
  why: { title: 'Get a written plan with a fixed price', text: 'After a free consultation we send the scope, timeline and price in writing.', tone: 'black' },
  process: { title: 'Ready to start with step one?', text: 'Book your free consultation and we will take it from there.', tone: 'navy' },
  featured_case: { title: 'Want results like these for your business?', text: 'Talk to the team that does the work, with no obligation.', tone: 'brand' },
}

export default function Landing() {
  useSeo('Book a Free Consultation', 'Book a free consultation with AchiveX LLC for web development, custom software, AI automation, CRM, SEO and digital marketing. Fixed price quotes in writing.')
  // An exact copy of the home page: the same sections from the admin, in the same order.
  const sections = [...homeSections].filter((x) => x.visible !== false).sort((a, b) => a.sort - b.sort)
  return (
    <>
      {sections.map((x) => {
        const Section = homeLayouts[x.type || x.key]
        if (!Section) return null
        return (
          <Fragment key={x.id || x.key}>
            <Section d={x.data || {}} />
            {CTAS[x.key] && <LandingCta {...CTAS[x.key]} />}
          </Fragment>
        )
      })}

      {/* The same form as Contact Us. Messages are marked so the inbox shows they came from here. */}
      <ContactSection id="lead-form" source="Landing page" />

      {/* A single copyright bar instead of the footer. */}
      <footer className="bg-ink !bg-site-brand">
        <p className="container-site py-4 text-center text-[14px] font-semibold text-[#FFFFFF]">
          Copyright &copy; {new Date().getFullYear()} AchiveX LLC. All rights reserved.
        </p>
      </footer>
    </>
  )
}
