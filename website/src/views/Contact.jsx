import useSeo from '../hooks/useSeo'
import { InnerHero, Steps, FaqBlock } from '../components/inner/InnerSections'
import ContactSection from '../components/ContactSection'
import { faqSchema } from '../lib/schema'
import { pageContent } from '../data/pageContent'

export default function Contact() {
  const c = pageContent['/contact-us/']
  useSeo('Contact Us', 'Contact AchiveX LLC by phone at +1 307 622 1140, by email at info@achivex.com, or send a message using our contact form.', faqSchema(c.faqs))
  return (
    <>
      <InnerHero crumbs={[{ label: 'Contact us' }]} eyebrow={c.heroEyebrow} title={c.heroTitle} text={c.heroText} />

      <ContactSection />
      <Steps eyebrow={c.nextEyebrow} title={c.nextTitle} text={c.nextText} items={c.next} />
      <FaqBlock title={c.faqTitle} text={c.faqText} items={c.faqs} />
    </>
  )
}
