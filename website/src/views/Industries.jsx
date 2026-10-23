import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import Img from '../components/Img'
import CTA from '../components/CTA'
import { InnerHero, IntroSplit, CardGrid, Steps, ImageSplit, FaqBlock } from '../components/inner/InnerSections'
import { industries } from '../data/industries'
import { pageContent } from '../data/pageContent'
import { faqSchema } from '../lib/schema'

// /industries/ lists every industry page, since the menu links here directly.
export default function Industries() {
  const c = pageContent['/industries/']
  useSeo('Industries', 'The industries AchiveX works with, from financial services and healthcare to retail, manufacturing, logistics and education.', faqSchema([...(c.faqs || []), ...(c.sharedFaqs || [])]))
  return (
    <>
      <InnerHero crumbs={[{ label: 'Industries' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} image={c.heroImage} alt={c.heroImageAlt} />
      <IntroSplit eyebrow={c.introEyebrow} title={c.introTitle} keyword={c.introKeyword} paragraphs={c.introParagraphs} />
      <section className="bg-[#EEF0F3] py-20 md:py-28">
        <div className="container-site">
          <FadeUp>
            <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-navy">{c.listEyebrow}</p>
            <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[44px]">{c.listTitle}</h2>
          </FadeUp>
          <ul className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {industries.map((ind, i) => (
              <FadeUp as="li" key={ind.slug} delay={(i % 3) * 0.05}>
                <Link to={`/industries/${ind.slug}/`} className="group flex h-full flex-col overflow-hidden rounded-[22px] border border-[#E6E9EE] bg-[#FFFFFF] transition-colors hover:border-navy">
                  <div className="overflow-hidden">
                    <Img src={ind.image} alt={`${ind.name} industry`} className="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                  </div>
                  <div className="flex flex-1 flex-col p-7">
                    <p className="flex items-center justify-between gap-4 text-[20px] font-semibold text-[#111111]">
                      {ind.name}
                      <ArrowRight className="h-5 w-5 shrink-0 text-navy transition-transform group-hover:translate-x-1" aria-hidden="true" />
                    </p>
                    <p className="mt-3 text-[15.5px] leading-[1.7] text-[#5B616B]">{ind.summary}</p>
                  </div>
                </Link>
              </FadeUp>
            ))}
          </ul>
        </div>
      </section>
      <Steps eyebrow={c.processEyebrow} title={c.processTitle} text={c.processText} items={c.process} />
      <ImageSplit eyebrow={c.whyEyebrow} title={c.whyTitle} paragraphs={c.whyParagraphs} list={c.whyList} image={c.whyImage} alt={c.whyImageAlt} />
      <CardGrid eyebrow={c.expectEyebrow} title={c.expectTitle} text={c.expectText} items={c.expect} columns={4} tone="grey" />
      <FaqBlock title={c.faqTitle} text={c.faqText} items={[...(c.faqs || []), ...(c.sharedFaqs || [])]} />
      <CTA title={c.ctaTitle} />
    </>
  )
}
