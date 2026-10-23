// Writes public/site-routes.json: every fixed page of the website with its default SEO text and default address
// (the admin maps addresses to the saved permalinks).
// The admin panel reads this file for the SEO page list and the sitemap.
// Run with: npm run export-routes
import { rmSync, writeFileSync } from 'node:fs'
import { categories } from '../src/data/categories.js'
import { services, defaultServicePath as servicePath, categoryPath } from '../src/data/services.js'
import { industries } from '../src/data/industries.js'
import { contentTargets } from '../src/data/contentTargets.js'
import { blogs } from '../src/data/blogs.js'
import { caseStudies } from '../src/data/caseStudies.js'
import { news } from '../src/data/news.js'
import { whitepapers } from '../src/data/whitepapers.js'

const pages = [
  { path: '/', title: 'Home', description: 'AchiveX LLC is a Wyoming based marketing and software solutions company offering web development, custom software, AI and automation, CRM, SEO, social media and ads management.', type: 'Page' },
  { path: '/about-us/', title: 'About Us', description: 'AchiveX LLC is a Wyoming based company that builds websites, custom software, AI automation and growth marketing for small and mid sized businesses.', type: 'Page' },
  { path: '/services/', title: 'Services', description: 'Explore AchiveX services: web development, custom software, AI and intelligent systems, automation, CRM, SEO, AEO and GEO, social media and ads management.', type: 'Page' },
  { path: '/industries/', title: 'Industries', description: 'The industries AchiveX works with, from financial services and healthcare to retail, manufacturing, logistics and education.', type: 'Page' },
  { path: '/insights/', title: 'Insights', description: 'Case studies, company news, free guides and practical articles from AchiveX LLC.', type: 'Page' },
  { path: '/insights/case-studies/', title: 'Case Studies', description: 'Examples of how AchiveX approaches client challenges across digital, data, cloud, security and business process services.', type: 'Page' },
  { path: '/insights/newsroom/', title: 'Newsroom', description: 'Company announcements and news from AchiveX LLC.', type: 'Page' },
  { path: '/insights/whitepapers/', title: 'Whitepapers and eBooks', description: 'Free practical guides from AchiveX on cloud migration, data quality, generative AI and security.', type: 'Page' },
  { path: '/insights/blogs/', title: 'Blogs', description: 'Practical articles from AchiveX on data, cloud, security, strategy and business operations.', type: 'Page' },
  { path: '/contact-us/', title: 'Contact Us', description: 'Contact AchiveX LLC by phone at +1 307 622 1140, by email at info@achivex.com, or send a message using our contact form.', type: 'Page' },
  { path: '/careers/', title: 'Careers', description: 'Careers at AchiveX LLC. Learn about our culture and see current open positions.', type: 'Page' },
  { path: '/privacy-policy/', title: 'Privacy Policy', description: 'How AchiveX LLC collects, uses and protects personal information.', type: 'Page' },
  { path: '/terms-of-service/', title: 'Terms of Service', description: 'The terms that govern use of the AchiveX LLC website and services.', type: 'Page' },
]
for (const c of categories.filter((c) => !c.single)) {
  pages.push({ path: categoryPath(c), title: c.name.endsWith('Services') ? c.name : `${c.name} Services`, description: `${c.short} AchiveX ${c.name.toLowerCase()} services for businesses in the USA and beyond.`, type: 'Service area' })
}
for (const s of services) {
  pages.push({ path: servicePath(s), title: s.name, description: `${s.short} ${s.heroLine}`, type: 'Service', focus: s.name.toLowerCase(), content: [...s.overview, ...s.included, ...s.benefits.map((b) => `${b.title}. ${b.text}`), ...s.faqs.map((f) => `${f.q} ${f.a}`)].join(' '), headings: ['Overview', 'What is included', 'What you can expect', 'Our process', 'Questions we often hear'] })
}
for (const i of industries) {
  pages.push({ path: `/industries/${i.slug}/`, title: i.name, description: `${i.summary} See how AchiveX supports ${i.name.toLowerCase()} organisations.`, type: 'Industry' })
}

// Default text and images of every editable page, so the admin can build the "Edit Page" form.
const targets = contentTargets()
for (const p of pages) {
  if (targets[p.path]) p.editable = JSON.parse(JSON.stringify(targets[p.path]))
}

const out = {
  generated: new Date().toISOString(),
  pages,
  services: services.map((s) => ({ slug: s.slug, name: s.name, path: servicePath(s) })),
  industries: industries.map((i) => ({ slug: i.slug, name: i.name })),
  blogCategories: ['Data', 'Cloud', 'Security', 'Strategy', 'Operations'],
}
writeFileSync('public/site-routes.json', JSON.stringify(out, null, 2))
console.log(`site-routes.json written with ${pages.length} pages`)

// sitemap.xml and robots.txt for hosts that serve the built site directly (Vercel, Netlify).
// Set SITE_URL in the host's environment if the domain is not www.achivex.com.
const base = (process.env.SITE_URL || 'https://www.achivex.com').replace(/\/$/, '')
const today = new Date().toISOString().slice(0, 10)
const urls = [
  ...pages.map((p) => ({ path: p.path, lastmod: today })),
  ...blogs.map((b) => ({ path: `/insights/blogs/${b.slug}/`, lastmod: b.date || today })),
  ...caseStudies.map((c) => ({ path: `/insights/case-studies/${c.slug}/`, lastmod: today })),
  ...news.map((n) => ({ path: `/insights/newsroom/${n.slug}/`, lastmod: n.date || today })),
  ...whitepapers.map((w) => ({ path: `/insights/whitepapers/${w.slug}/`, lastmod: today })),
]
const xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
  ...urls.map((u) => `  <url><loc>${base}${u.path}</loc><lastmod>${u.lastmod}</lastmod></url>`), '</urlset>', ''].join('\n')
// The admin owns robots.txt, sitemap.xml and llms.txt (SEO menu). Use its versions when it can be
// reached and the file looks right; otherwise keep the files generated above from bundled data.
const admin = (process.env.ADMIN_URL || 'https://achivex-admin.vercel.app').replace(/\/$/, '')
async function fromAdmin(file, looksRight) {
  try {
    const res = await fetch(`${admin}/${file}`, { signal: AbortSignal.timeout(8000) })
    const text = await res.text()
    if (res.ok && !/^\s*<!doctype html/i.test(text) && looksRight(text)) return text
  } catch {}
  return null
}
// On Vercel, vercel.json rewrites these three files to the admin, so they are always current and
// a new blog post shows up in the sitemap without a redeploy. No static copy is written there,
// because a file in the build would take priority over the rewrite.
if (process.env.VERCEL) {
  for (const f of ['sitemap.xml', 'robots.txt', 'llms.txt']) rmSync(`public/${f}`, { force: true })
  console.log('sitemap.xml, robots.txt, llms.txt: served live from the admin (vercel.json rewrites)')
  process.exit(0)
}
const adminSitemap = await fromAdmin('sitemap.xml', (t) => t.includes('<urlset'))
const adminRobots = await fromAdmin('robots.txt', (t) => /user-agent\s*:/i.test(t))
const adminLlms = await fromAdmin('llms.txt', (t) => t.trim().length > 0)
writeFileSync('public/sitemap.xml', adminSitemap ?? xml)
writeFileSync('public/robots.txt', adminRobots ?? `User-agent: *\nAllow: /\n\nSitemap: ${base}/sitemap.xml\n`)
if (adminLlms) writeFileSync('public/llms.txt', adminLlms)
console.log(`sitemap.xml: ${adminSitemap ? 'from the admin' : `bundled, ${urls.length} URLs`}; robots.txt: ${adminRobots ? 'from the admin' : 'default'}; llms.txt: ${adminLlms ? 'from the admin' : 'none'}`)
