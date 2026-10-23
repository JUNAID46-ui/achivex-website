import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import { InnerHero, IntroSplit, CardGrid, FaqBlock } from '../../components/inner/InnerSections'
import CTA from '../../components/CTA'
import { insightsLinks } from '../../data/menus'
import { blogs } from '../../data/blogs'
import { caseStudies } from '../../data/caseStudies'
import { news } from '../../data/news'
import { whitepapers } from '../../data/whitepapers'
import { pageContent } from '../../data/pageContent'

// /insights/ brings the four kinds of content together, since the menu links here directly.
export default function Insights() {
  const c = pageContent['/insights/']
  useSeo('Insights', 'Case studies, company news, free guides and practical articles from AchiveX LLC.')
  const counts = { 'Case Studies': caseStudies.length, Newsroom: news.length, 'Whitepapers and eBooks': whitepapers.length, Blogs: blogs.length }
  return (
    <>
      <InnerHero crumbs={[{ label: 'Insights' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} image={c.heroImage} alt={c.heroImageAlt} />
      <IntroSplit eyebrow={c.introEyebrow} title={c.introTitle} keyword={c.introKeyword} paragraphs={c.introParagraphs} />
      <section className="bg-site-grey py-20 md:py-28">
        <div className="container-site">
          <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-navy">{c.typesEyebrow}</p>
          <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[44px]">{c.typesTitle}</h2>
        </div>
        <ul className="container-site mt-12 grid gap-5 md:grid-cols-2">
          {insightsLinks.map((l, i) => (
            <FadeUp as="li" key={l.label} delay={(i % 2) * 0.05}>
              <Link to={l.to} className="group flex h-full items-end justify-between gap-6 rounded-[22px] border border-site-line bg-[#FFFFFF] p-8 transition-colors hover:border-navy md:p-10">
                <div>
                  <p className="text-sm text-body/80">{counts[l.label] ?? ''} {counts[l.label] === 1 ? 'item' : 'items'}</p>
                  <p className="mt-3 font-display text-3xl font-medium text-white">{l.label}</p>
                  <p className="mt-2 text-body/80">{l.text}</p>
                </div>
                <ArrowRight className="h-6 w-6 shrink-0 text-navy transition-transform group-hover:translate-x-1" aria-hidden="true" />
              </Link>
            </FadeUp>
          ))}
        </ul>
      </section>
      <CardGrid eyebrow={c.topicsEyebrow} title={c.topicsTitle} text={c.topicsText} items={c.topics} columns={4} />
      <FaqBlock title={c.faqTitle} text={c.faqText} items={c.faqs} />
      <CTA title={c.ctaTitle} />
    </>
  )
}
