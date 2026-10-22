import { settings } from '../data/settings'

// Address prefixes of the content types. The admin can change them under Settings, Permalinks.
// Links in the code use the default prefix and go through permalink(), which swaps in the saved one.
export const DEFAULT_BASES = {
  blogs: '/insights/blogs/',
  caseStudies: '/insights/case-studies/',
  newsroom: '/insights/newsroom/',
  whitepapers: '/insights/whitepapers/',
}

const valid = (p) => typeof p === 'string' && /^\/[a-z0-9-]+(\/[a-z0-9-]+)*\/$/.test(p)

export function base(type) {
  const saved = settings.permalinks?.[type]
  return valid(saved) ? saved : DEFAULT_BASES[type]
}

// "/insights/blogs/my-post/" becomes "/blog/my-post/" when the blog prefix is "/blog/".
export function permalink(path) {
  for (const [type, def] of Object.entries(DEFAULT_BASES)) {
    if (path.startsWith(def)) return base(type) + path.slice(def.length)
  }
  return path
}

// Route path without slashes, for React Router: "/blog/" becomes "blog".
export const routePath = (type) => base(type).replace(/^\/|\/$/g, '')

// For old links after a prefix change: the new address, or null if the path is not under an old prefix.
export function fromOldBase(pathname) {
  const p = pathname.endsWith('/') ? pathname : `${pathname}/`
  for (const [type, def] of Object.entries(DEFAULT_BASES)) {
    const now = base(type)
    if (now !== def && p.startsWith(def)) return now + p.slice(def.length)
  }
  return null
}
