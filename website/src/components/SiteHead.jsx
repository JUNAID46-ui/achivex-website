import { useEffect } from 'react'
import { settings } from '../data/settings'
import { site } from '../data/site'

// Adds verification tags and Organization structured data from the admin settings.
export default function SiteHead() {
  useEffect(() => {
    const add = (name, content) => {
      if (!content || document.head.querySelector(`meta[name="${name}"]`)) return
      const m = document.createElement('meta')
      m.name = name
      m.content = content
      document.head.appendChild(m)
    }
    add('google-site-verification', settings.seo.googleVerification)
    add('msvalidate.01', settings.seo.bingVerification)

    const s = settings.schema
    const data = {
      '@context': 'https://schema.org',
      '@type': s.type || 'Organization',
      name: s.name || site.name,
      url: s.url,
      logo: s.logo || undefined,
      telephone: s.phone || site.phone,
      email: s.email || site.email,
      address: {
        '@type': 'PostalAddress',
        streetAddress: s.street || undefined,
        addressLocality: s.city || undefined,
        addressRegion: s.region || undefined,
        postalCode: s.postalCode || undefined,
        addressCountry: s.country || undefined,
      },
      openingHours: s.openingHours || undefined,
      priceRange: s.priceRange || undefined,
      sameAs: site.social.map((x) => x.href),
    }
    let tag = document.getElementById('org-schema')
    if (!tag) {
      tag = document.createElement('script')
      tag.type = 'application/ld+json'
      tag.id = 'org-schema'
      document.head.appendChild(tag)
    }
    tag.textContent = JSON.stringify(data)
  }, [])
  return null
}
