import { img } from './images'

// Service areas in menu order. Each area groups the individual services listed in services.js.
// `single: true` would mean the area is itself a service page with no sub items (none at the moment).
export const categories = [
  {
    slug: 'web-and-software',
    name: 'Web and Software',
    short: 'Websites, web apps and internal systems built around how your business works.',
    description:
      'Your website is often the first thing a customer sees, and your internal tools shape how your team spends its day. We design, build and maintain both, using WordPress, WooCommerce, Laravel and React.',
    image: img('1498050108023-c5249f4df085'),
    heroImage: img('1517694712202-14dd9538aa97', 2000),
    services: ['web-development', 'custom-software'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'ai-and-automation',
    name: 'AI and Automation',
    short: 'AI, automation and CRM systems that take repetitive work off your team.',
    description:
      'Many hours in a week go into copying data, chasing leads and answering the same questions. We build AI systems, automations and CRM setups that handle that work and keep every lead moving.',
    image: img('1677442136019-21780ecad995'),
    heroImage: img('1620712943543-bcc4688e7485', 2000),
    services: ['ai-automation', 'crm-and-lead-management'],
    caseStudy: 'healthcare-patient-support',
  },
  {
    slug: 'growth-marketing',
    name: 'Growth Marketing',
    short: 'SEO, AI search visibility, social media and ads that bring in qualified leads.',
    description:
      'Customers find businesses through Google, AI assistants, social media and ads. We work across all of them, with reporting that shows what is bringing in leads and what is not.',
    image: img('1557838923-2985c318be48'),
    heroImage: img('1551288049-bebda4e38f71', 2000),
    services: ['seo', 'aeo-and-geo', 'social-media-management', 'ads-management'],
    caseStudy: 'retail-storefront-rebuild',
  },
]

export const getCategory = (slug) => categories.find((c) => c.slug === slug)
export const multiCategories = categories.filter((c) => !c.single)
