// JSON-LD helpers. Each page sets its own block (by id) so navigating replaces the old one.
export function setJsonLd(id, data) {
  let tag = document.getElementById(id)
  const list = (Array.isArray(data) ? data : [data]).filter(Boolean)
  if (!list.length) {
    if (tag) tag.remove()
    return
  }
  if (!tag) {
    tag = document.createElement('script')
    tag.type = 'application/ld+json'
    tag.id = id
    document.head.appendChild(tag)
  }
  tag.textContent = JSON.stringify(list.length === 1 ? list[0] : list)
}

export const faqSchema = (faqs) => (faqs?.length
  ? {
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      mainEntity: faqs.map((f) => ({ '@type': 'Question', name: f.q, acceptedAnswer: { '@type': 'Answer', text: f.a } })),
    }
  : null)
