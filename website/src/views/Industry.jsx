import { useParams } from 'react-router-dom'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import ServiceCard from '../components/ServiceCard'
import ArrowLink from '../components/ArrowLink'
import CTA from '../components/CTA'
import NotFound from './NotFound'
import { InnerHero, IntroSplit, CardGrid, ImageSplit, Steps, FaqBlock } from '../components/inner/InnerSections'
import { industries } from '../data/industries'
import { getService } from '../data/services'
import { pageContent } from '../data/pageContent'
import { faqSchema } from '../lib/schema'

export default function Industry() {
  const { slug } = useParams()
  const ind = industries.find((i) => i.slug === slug)
  const shared = pageContent['/industries/']
  useSeo(ind?.name, ind ? `${ind.summary} See how AchiveX supports ${ind.name.toLowerCase()} organisations.` : undefined, ind && faqSchema([...(ind.faqs || []), ...(shared.sharedFaqs || [])]))
  if (!ind) return <NotFound />
  const services = (ind.services || []).map(getService).filter(Boolean)

  return (
    <>
      <InnerHero
        crumbs={[{ label: 'Industries', to: '/industries/' }, { label: ind.name }]}
        eyebrow="Industry"
        title={ind.name}
        text={ind.summary}
        image={ind.image}
        alt={`${ind.name} industry`}
        action={{ label: 'Talk to us about your business', to: '/contact-us/' }}
      />
      <IntroSplit eyebrow="Overview" title={`Technology and marketing for ${ind.name.toLowerCase()}`} paragraphs={ind.intro} />
      <CardGrid eyebrow="Common challenges" title={`What ${ind.name.toLowerCase()} teams tell us`} items={ind.challenges} columns={4} tone="grey" />
      <ImageSplit eyebrow="How we help" title={ind.helpTitle} paragraphs={ind.helpParagraphs} list={ind.helpList} image={ind.image} alt={`${ind.name} work`} reverse />

      {services.length > 0 && (
        <section className="bg-[#EEF0F3] py-20 md:py-28">
          <div className="container-site">
            <FadeUp>
              <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-navy">Services</p>
              <h2 className="mt-4 text-[32px] font-medium leading-[1.12] tracking-[-0.025em] text-[#111111] md:text-[44px]">Services that fit {ind.name.toLowerCase()}</h2>
            </FadeUp>
            <div className="mt-12 grid gap-6 md:grid-cols-3">
              {services.map((s) => <FadeUp key={s.slug}><ServiceCard service={s} /></FadeUp>)}
            </div>
            <div className="mt-14 flex flex-wrap gap-x-8 gap-y-3 border-t border-[#E3E6EA] pt-8">
              <span className="font-semibold text-[#111111]">Other industries:</span>
              {industries.filter((i) => i.slug !== slug).map((i) => (
                <ArrowLink key={i.slug} to={`/industries/${i.slug}/`} className="text-[#111111]">{i.name}</ArrowLink>
              ))}
            </div>
          </div>
        </section>
      )}

      <Steps eyebrow={shared.processEyebrow} title={shared.processTitle} text={shared.processText} items={shared.process} />
      <CardGrid eyebrow={shared.expectEyebrow} title={shared.expectTitle} text={shared.expectText} items={shared.expect} columns={4} tone="grey" />
      <FaqBlock title={`${ind.name} questions`} text="Ask us anything about your sector. We will tell you honestly whether we are a good fit." items={[...(ind.faqs || []), ...(shared.sharedFaqs || [])]} />
      <CTA title={`Support for ${ind.name.toLowerCase()} teams.`} />
    </>
  )
}
