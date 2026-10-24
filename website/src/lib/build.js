// Build time helpers for the Next.js pages: the content from the database, the list of every
// address to prerender, and the title and description of each one. Runs on the server only.
import { readFileSync } from 'node:fs'
import { join } from 'node:path'
import { fetchTables, applyAll, normalizePath } from './content'
import { blogs } from '../data/blogs'
import { caseStudies } from '../data/caseStudies'
import { news } from '../data/news'
import { whitepapers } from '../data/whitepapers'
import { customPages } from '../data/customPages'
import { settings, seoMeta } from '../data/settings'
import { services, servicePath } from '../data/services'
import { permalink, base } from './permalinks'

let contentPromise = null

// Loads the content once per build. CONTENT_SNAPSHOT can point at a saved JSON file (useful when
// the build machine cannot reach the database). Without either, the bundled content is used.
export function getContent() {
  contentPromise ??= (async () => {
    let rows = null
    try {
      if (process.env.CONTENT_SNAPSHOT) rows = JSON.parse(readFileSync(process.env.CONTENT_SNAPSHOT, 'utf8'))
      else rows = await Promise.race([fetchTables(), new Promise((_, no) => setTimeout(() => no(new Error('timeout')), 20000))])
    } catch (e) {
      console.warn(`Building with the bundled content: ${e.message}`)
      rows = null
    }
    if (rows) applyAll(rows)
    return rows
  })()
  return contentPromise
}

const routesFile = () => {
  try {
    return JSON.parse(readFileSync(join(process.cwd(), 'public', 'site-routes.json'), 'utf8')).pages || []
  } catch {
    return [{ path: '/', title: 'Home', description: '' }]
  }
}

// Every address that gets its own HTML file.
export async function allPaths() {
  await getContent()
  // Service pages use their current address only; the default one opens the site, which redirects.
  const current = new Set(services.map((s) => servicePath(s)))
  const paths = new Set(routesFile().filter((p) => p.type !== 'Service' || current.has(p.path)).map((p) => p.path))
  services.forEach((s) => paths.add(servicePath(s)))
  const item = (type, list) => list.forEach((x) => paths.add(`${base(type)}${x.slug}/`))
  item('blogs', blogs)
  item('caseStudies', caseStudies)
  item('newsroom', news)
  item('whitepapers', whitepapers)
  ;['/insights/blogs/', '/insights/case-studies/', '/insights/newsroom/', '/insights/whitepapers/'].forEach((p) => paths.add(permalink(p)))
  Object.keys(customPages).forEach((p) => paths.add(normalizePath(p)))
  return [...paths]
}

// Title and description for the page head, matching what the page sets in the browser.
export async function metaFor(path) {
  await getContent()
  const fixed = routesFile().find((p) => p.path === path)
  const find = (type, list) => list.find((x) => path === `${base(type)}${x.slug}/`)
  const blog = find('blogs', blogs)
  const cs = find('caseStudies', caseStudies)
  const item = find('newsroom', news)
  const wp = find('whitepapers', whitepapers)
  const custom = customPages[path]
  const svc = services.find((s) => servicePath(s) === path)
  let title = fixed?.title
  let description = fixed?.description
  if (blog) [title, description] = [blog.title, blog.excerpt]
  else if (cs) [title, description] = [cs.title, cs.summary]
  else if (item) [title, description] = [item.title, item.summary]
  else if (wp) [title, description] = [wp.title, wp.description]
  else if (custom) [title, description] = [custom.title, custom.description]
  else if (svc) [title, description] = [svc.name, `${svc.short} ${svc.heroLine}`]
  const saved = seoMeta[path] || {}
  const pageTitle = saved.title || title
  const full = path === '/' && !saved.title
    ? settings.seo.homeTitle
    : (settings.seo.titleTemplate || '%title% | AchiveX LLC').replace('%title%', pageTitle || 'AchiveX LLC')
  return {
    title: full,
    description: saved.description || description || settings.seo.defaultDescription,
    image: saved.og_image || settings.seo.ogImage,
    noindex: Boolean(saved.noindex),
    canonical: saved.canonical || null,
  }
}
