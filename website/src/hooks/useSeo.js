import { useEffect } from 'react'
import { useLocation } from 'react-router-dom'
import { settings, seoMeta } from '../data/settings'

const setMeta = (attr, key, content) => {
  let tag = document.head.querySelector(`meta[${attr}="${key}"]`)
  if (!content) {
    if (tag) tag.remove()
    return
  }
  if (!tag) {
    tag = document.createElement('meta')
    tag.setAttribute(attr, key)
    document.head.appendChild(tag)
  }
  tag.setAttribute('content', content)
}

const setCanonical = (href) => {
  let link = document.head.querySelector('link[rel="canonical"]')
  if (!link) {
    link = document.createElement('link')
    link.rel = 'canonical'
    document.head.appendChild(link)
  }
  link.href = href
}

// Sets title, description, robots, canonical and social tags for each page.
// Values saved in the admin SEO module (seo_meta table) override the page defaults.
export default function useSeo(title, description) {
  const { pathname } = useLocation()
  useEffect(() => {
    const path = pathname.endsWith('/') ? pathname : `${pathname}/`
    const meta = seoMeta[path] || {}
    const pageTitle = meta.title || title
    const fullTitle = pageTitle
      ? (settings.seo.titleTemplate || '%title%').replace('%title%', pageTitle)
      : settings.seo.homeTitle
    const desc = meta.description || description || settings.seo.defaultDescription
    document.title = fullTitle
    setMeta('name', 'description', desc)
    setMeta('name', 'robots', meta.noindex ? 'noindex, nofollow' : 'index, follow')
    setCanonical(meta.canonical || `${window.location.origin}${path}`)
    setMeta('property', 'og:title', fullTitle)
    setMeta('property', 'og:description', desc)
    setMeta('property', 'og:type', 'website')
    setMeta('property', 'og:url', `${window.location.origin}${path}`)
    setMeta('property', 'og:image', meta.og_image || settings.seo.ogImage)
    setMeta('name', 'twitter:card', 'summary_large_image')
    setMeta('name', 'twitter:site', settings.seo.twitterHandle)
  }, [pathname, title, description])
}
