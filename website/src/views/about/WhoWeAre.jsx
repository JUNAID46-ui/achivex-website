import { HeartHandshake, Search, Target, Wallet } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import CTA from '../../components/CTA'
import StatsBand from '../../components/sections/StatsBand'
import ScrollStats from '../../components/sections/ScrollStats'
import { siteFacts } from '../../lib/facts'
import { InnerHero, IntroSplit, CardGrid, ImageSplit, Pair, Steps, FaqBlock, DarkBanner } from '../../components/inner/InnerSections'
import { pageContent } from '../../data/pageContent'
import { categories } from '../../data/categories'
import { services } from '../../data/services'
import { industries } from '../../data/industries'
import { faqSchema } from '../../lib/schema'

export default function WhoWeAre() {
  const c = pageContent['/about-us/']
  useSeo('About Us', 'AchiveX LLC is a Wyoming based company that builds websites, custom software, AI automation and growth marketing for small and mid sized businesses.', faqSchema(c.faqs))
  // Figures come from the site data, so they stay true when services or industries change.
  const facts = [
    { value: categories.length, label: 'Service areas' },
    { value: services.length, label: 'Individual services' },
    { value: industries.length, label: 'Industries served' },
    { value: 'Wyoming', label: 'Registered in the USA' },
  ]
  return (
    <>
      <InnerHero crumbs={[{ label: 'About Us' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} image={c.heroImage} alt={c.heroImageAlt} action={{ label: 'Talk to our team', to: '/contact-us/' }} />
      <IntroSplit eyebrow={c.introEyebrow} title={c.introTitle} keyword={c.introKeyword} paragraphs={c.introParagraphs} />
      <section className="bg-site-grey">
        <ScrollStats stats={siteFacts()} eyebrow="AchiveX in numbers" title="What one team covers" />
      </section>
      <Pair
        eyebrow={c.statementsEyebrow}
        title={c.statementsTitle}
        items={[{ title: c.missionTitle, text: c.missionText }, { title: c.visionTitle, text: c.visionText }, { title: c.philosophyTitle, text: c.philosophyText }]}
      />
      <CardGrid eyebrow={c.areasEyebrow} title={c.areasTitle} text={c.areasText} items={c.areas} tone="grey" />
      <StatsBand title={c.statsTitle} image={c.statsImage} alt={c.statsImageAlt} stats={facts.slice(0, 3)} />
      <ImageSplit eyebrow={c.storyEyebrow} title={c.storyTitle} paragraphs={c.storyParagraphs} list={c.storyList} image={c.storyImage} alt={c.storyImageAlt} />
      <CardGrid eyebrow={c.valuesEyebrow} title={c.valuesTitle} text={c.valuesText} items={c.values} tone="grey" columns={4} icons={[HeartHandshake, Search, Target, Wallet]} />
      <DarkBanner eyebrow={c.bannerEyebrow} title={c.bannerTitle} text={c.bannerText} action={{ label: 'Book a consultation', to: '/contact-us/' }} />
      <Steps eyebrow={c.processEyebrow} title={c.processTitle} text={c.processText} items={c.process} />
      <FaqBlock title={c.faqTitle} text={c.faqText} items={c.faqs} />
      <CTA title={c.ctaTitle} />
    </>
  )
}
