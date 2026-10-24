import useSeo from '../../hooks/useSeo'
import LegalPage from '../../components/LegalPage'
import { site } from '../../data/site'
import { pageContent } from '../../data/pageContent'

// Text is edited in the admin (Pages); contact placeholders are filled from Settings.
const fill = (text) => String(text ?? '').replace(/{email}/g, site.email).replace(/{phone}/g, site.phone).replace(/{location}/g, site.location)

export default function Terms() {
  useSeo('Terms of Service', 'The terms that govern use of the AchiveX LLC website and services.')
  const pc = pageContent['/terms-of-service/']
  const sections = (pc.sections || []).map((s) => ({
    ...s,
    paragraphs: (s.paragraphs || []).map(fill),
    bullets: (s.bullets || []).map(fill),
  }))
  return <LegalPage title={pc.title} updated={pc.updated} intro={fill(pc.intro)} sections={sections} />
}
