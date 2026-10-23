import useSeo from '../../hooks/useSeo'
import CTA from '../../components/CTA'
import { InnerHero, IntroSplit, CardGrid, ImageSplit, Pair, Steps, FaqBlock } from '../../components/inner/InnerSections'
import { pageContent } from '../../data/pageContent'
import { faqSchema } from '../../lib/schema'

export default function WhoWeAre() {
  const c = pageContent['/about-us/']
  useSeo('About Us', 'AchiveX LLC is a Wyoming based company that builds websites, custom software, AI automation and growth marketing for small and mid sized businesses.', faqSchema(c.faqs))
  return (
    <>
      <InnerHero crumbs={[{ label: 'About Us' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} image={c.heroImage} alt={c.heroImageAlt} action={{ label: 'Talk to our team', to: '/contact-us/' }} />
      <IntroSplit eyebrow={c.introEyebrow} title={c.introTitle} keyword={c.introKeyword} paragraphs={c.introParagraphs} />
      <CardGrid eyebrow={c.areasEyebrow} title={c.areasTitle} text={c.areasText} items={c.areas} tone="grey" />
      <ImageSplit eyebrow={c.storyEyebrow} title={c.storyTitle} paragraphs={c.storyParagraphs} list={c.storyList} image={c.storyImage} alt={c.storyImageAlt} />
      <Pair items={[{ title: c.missionTitle, text: c.missionText }, { title: c.visionTitle, text: c.visionText }]} />
      <CardGrid eyebrow={c.valuesEyebrow} title={c.valuesTitle} text={c.valuesText} items={c.values} tone="grey" />
      <Steps eyebrow={c.processEyebrow} title={c.processTitle} text={c.processText} items={c.process} />
      <ImageSplit eyebrow={c.whyEyebrow} title={c.whyTitle} paragraphs={c.whyParagraphs} list={c.whyList} image={c.whyImage} alt={c.whyImageAlt} reverse />
      <FaqBlock title={c.faqTitle} text={c.faqText} items={c.faqs} />
      <CTA title={c.ctaTitle} />
    </>
  )
}
