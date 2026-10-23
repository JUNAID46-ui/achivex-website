import { categories } from '../data/categories'
import { services } from '../data/services'
import { industries } from '../data/industries'
import { caseStudies } from '../data/caseStudies'
import { whitepapers } from '../data/whitepapers'
import { blogs } from '../data/blogs'
import { homeSections } from '../data/homeSections'

// Figures shown in the stats bands. Every one is counted from the site's own content, so they stay
// true when a service, industry, guide or article is added or removed in the admin.
export function siteFacts() {
  const steps = (homeSections.find((x) => x.key === 'process')?.data?.steps || []).length || 4
  return [
    { value: categories.length, label: 'Service areas under one team' },
    { value: services.length, label: 'Individual services' },
    { value: industries.length, label: 'Industries served' },
    { value: steps, label: 'Steps from first call to launch' },
    { value: caseStudies.length, label: 'Case studies published' },
    { value: whitepapers.length, label: 'Free guides to download' },
    { value: blogs.length, label: 'Practical articles' },
    { value: 24, suffix: '/7', label: 'AI receptionist for calls and bookings' },
  ]
}
