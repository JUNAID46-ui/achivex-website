import { supabase } from './supabase'
import { blogs, blogCategories } from '../data/blogs'
import { caseStudies } from '../data/caseStudies'
import { news } from '../data/news'
import { whitepapers } from '../data/whitepapers'
import { milestones, leaders, locations, careers, heroStats } from '../data/about'
import { homeSections } from '../data/homeSections'
import { site } from '../data/site'
import { settings, seoMeta, redirects } from '../data/settings'
import { customPages, pageSections, pageDesigns } from '../data/customPages'
import { contentTargets } from '../data/contentTargets'

const normalizePath = (p) => (p.endsWith('/') ? p : `${p}/`)

// Applies site_settings rows onto the shared site and settings objects.
function applySettings(rows) {
  const byKey = Object.fromEntries(rows.map((r) => [r.key, r.value || {}]))
  const g = byKey.general || {}
  if (g.siteName) site.name = g.siteName
  if (g.tagline) site.tagline = g.tagline
  if (g.phone) {
    site.phone = g.phone
    site.phoneHref = `tel:${g.phone.replace(/[^+\d]/g, '')}`
  }
  if (g.email) site.email = g.email
  if (g.location) site.location = g.location
  if (g.googleProfile) site.googleProfile = g.googleProfile
  const social = byKey.social || {}
  site.social = site.social.map((s) => {
    const key = s.name.toLowerCase()
    return social[key] ? { ...s, href: social[key] } : s
  })
  Object.assign(settings.seo, byKey.seo || {})
  Object.assign(settings.schema, byKey.schema || {})
  Object.assign(settings.customCode, byKey.custom_code || {})
  Object.assign(settings.permalinks, byKey.permalinks || {})
  Object.assign(settings.theme, byKey.theme || {})
  // Blog categories saved in the admin (Blog Posts, Categories) replace the default list.
  const cats = (byKey.blog || {}).categories
  if (Array.isArray(cats) && cats.length) blogCategories.splice(0, blogCategories.length, ...cats)
}

// Loads the editable content from the database and swaps it into the data arrays
// that the components already import. If the database cannot be reached, the
// bundled data files are used instead, so the site never shows an empty page.
const replace = (target, rows) => target.splice(0, target.length, ...rows)

const tables = [
  ['hero_stats', 'sort', (r) => replace(heroStats, r.map((x) => ({ value: x.value, label: x.label })))],
  ['blog_posts', 'published_on', (r) => replace(blogs, r.map((x) => ({
    slug: x.slug, title: x.title, category: x.category, date: x.published_on, author: x.author,
    featured: x.featured, image: x.image, excerpt: x.excerpt,
    intro: { keyword: x.intro_keyword, text: x.intro_text }, sections: x.sections || [], faqs: x.faqs || [],
    heroColor: x.hero_color, heroImage: x.hero_image,
  }))), false],
  ['case_studies', 'sort', (r) => replace(caseStudies, r.map((x) => ({
    slug: x.slug, title: x.title, clientType: x.client_type, industry: x.industry, services: x.services || [],
    image: x.image, summary: x.summary, overview: x.overview, challenge: x.challenge || [],
    solution: x.solution || [], results: x.results || [],
  })))],
  ['news_posts', 'published_on', (r) => replace(news, r.map((x) => ({
    slug: x.slug, date: x.published_on, title: x.title, summary: x.summary, image: x.image, body: x.body || [],
  }))), false],
  ['whitepapers', 'sort', (r) => replace(whitepapers, r.map((x) => ({
    slug: x.slug, title: x.title, type: x.type, description: x.description, cover: x.cover,
    inside: x.inside || [], file: x.file_url,
  })))],
  ['milestones', 'sort', (r) => replace(milestones, r.map((x) => ({ date: x.year, title: x.title, text: x.description })))],
  ['team_members', 'sort', (r) => replace(leaders, r.map((x) => ({ name: x.name, role: x.role, photo: x.photo_url, linkedin: x.linkedin_url })))],
  ['locations', 'sort', (r) => replace(locations, r.map((x) => ({
    city: x.city, country: x.country, type: x.type, address: x.address, phone: x.phone, email: x.email, hq: x.is_hq,
  })))],
  ['site_settings', 'key', applySettings],
  ['page_sections', 'sort', (r) => {
    const toSection = (x) => ({ id: x.id, key: x.key, type: x.type || x.key, label: x.label, sort: x.sort, visible: x.visible, data: x.data || {} })
    const home = r.filter((x) => x.page === 'home')
    if (home.length) replace(homeSections, home.map(toSection))
    Object.keys(pageSections).forEach((k) => delete pageSections[k])
    r.filter((x) => x.page !== 'home').forEach((x) => {
      const path = normalizePath(x.page)
      ;(pageSections[path] ||= []).push(toSection(x))
    })
  }],
  ['pages', 'path', (r) => {
    const targets = contentTargets()
    Object.keys(customPages).forEach((k) => delete customPages[k])
    Object.keys(pageDesigns).forEach((k) => delete pageDesigns[k])
    r.forEach((x) => {
      const path = normalizePath(x.path)
      if (x.kind !== 'custom' && x.content?.design) pageDesigns[path] = x.content.design
      if (x.kind === 'custom') customPages[path] = { path, title: x.title, ...(x.content || {}) }
      else if (targets[path] && x.content) Object.assign(targets[path], x.content)
    })
  }],
  ['seo_meta', 'path', (r) => {
    Object.keys(seoMeta).forEach((k) => delete seoMeta[k])
    r.forEach((x) => { seoMeta[normalizePath(x.path)] = x })
  }],
  ['redirects', 'id', (r) => replace(redirects, r.map((x) => ({ from: normalizePath(x.from_path), to: x.to_path, status: x.status_code })))],
  ['job_openings', 'sort', (r) => replace(careers.positions, r.map((x) => ({
    title: x.title, team: x.team, location: x.location, type: x.employment_type, description: x.description,
  })))],
]

const CACHE_KEY = 'achivex_content_v1'

export const applyAll = (byTable) => {
  tables.forEach(([name, , apply]) => { if (Array.isArray(byTable[name])) apply(byTable[name]) })
}

// Content saved from the previous visit, applied before the first render so returning
// visitors never see old built in text flash on screen.
export function applyCachedContent() {
  try {
    const cached = JSON.parse(localStorage.getItem(CACHE_KEY) || 'null')
    if (cached && typeof cached === 'object') applyAll(cached)
  } catch {
    // Private mode or bad data: keep the built in content.
  }
}

// Fetches the latest content. Resolves true only when it differs from what is on screen,
// so the page re-renders once at most, and only when something actually changed.
// Every editable table, read with the public key. Used at build time for the prerendered pages
// and in the browser for the live refresh.
export async function fetchTables() {
  if (!supabase) throw new Error('Database is not configured')
  const rows = await Promise.all(
    tables.map(async ([name, order, , ascending = true]) => {
      const { data, error } = await supabase.from(name).select('*').order(order, { ascending })
      if (error) throw error
      return [name, data]
    }),
  )
  return Object.fromEntries(rows)
}

// Content the page was prerendered with (set by SiteRoot), so the live refresh only re-renders
// when the database has changed since the build.
let builtJson = null
export const setBuiltContent = (byTable) => { builtJson = byTable ? JSON.stringify(byTable) : null }

export async function loadContent(timeoutMs = 8000) {
  if (!supabase) return false
  const work = fetchTables().then((byTable) => {
    const json = JSON.stringify(byTable)
    let previous = builtJson
    if (previous === null) {
      try { previous = localStorage.getItem(CACHE_KEY) } catch { /* storage blocked */ }
    }
    // Apply only when every table loaded, so content is never half from each source.
    applyAll(byTable)
    try { localStorage.setItem(CACHE_KEY, json) } catch { /* storage full or blocked */ }
    return previous !== json
  })
  const timeout = new Promise((resolve) => setTimeout(() => resolve(false), timeoutMs))
  try {
    return await Promise.race([work, timeout])
  } catch {
    return false
  }
}

// Analytics: one row per page view, used by the admin dashboard.
function visitorId() {
  try {
    let id = localStorage.getItem('achivex_vid')
    if (!id) {
      id = Math.random().toString(36).slice(2) + Date.now().toString(36)
      localStorage.setItem('achivex_vid', id)
    }
    return id
  } catch {
    return null
  }
}

let firstView = true
export function logPageView(path) {
  if (!supabase) return
  const w = window.innerWidth
  const device = w < 768 ? 'mobile' : w < 1024 ? 'tablet' : 'desktop'
  const referrer = firstView && document.referrer && !document.referrer.includes(window.location.host) ? document.referrer.slice(0, 500) : null
  firstView = false
  supabase.from('page_views').insert({ path: path.slice(0, 500), referrer, device, visitor: visitorId() }).then(() => {}, () => {})
}

export function logNotFound(path) {
  if (!supabase) return
  supabase.from('not_found_log').insert({ path: path.slice(0, 500), referrer: document.referrer ? document.referrer.slice(0, 500) : null }).then(() => {}, () => {})
}

export { normalizePath }

// Form submissions
export async function sendContactMessage(form) {
  if (!supabase) throw new Error('Database is not configured')
  const { error } = await supabase.from('contact_submissions').insert({
    name: form.name, email: form.email, phone: form.phone || null, service: form.service || null, message: form.message,
  })
  if (error) throw error
}

export async function requestWhitepaper(slug, form) {
  if (!supabase) throw new Error('Database is not configured')
  const { error } = await supabase.from('whitepaper_requests').insert({
    whitepaper_slug: slug, name: form.name, email: form.email, company: form.company || null, job_title: form.title || null,
  })
  if (error) throw error
}
