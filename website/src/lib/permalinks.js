import { settings } from '../data/settings.js'

// Address prefixes of the content types. The admin can change them under Settings, Permalinks.
// Links in the code use the default prefix and go through permalink(), which swaps in the saved one.
// A prefix of "/" puts items at the root of the site (/my-post/); the list page then keeps its default address.
export const DEFAULT_BASES = {
  blogs: '/insights/blogs/',
  caseStudies: '/insights/case-studies/',
  newsroom: '/insights/newsroom/',
  whitepapers: '/insights/whitepapers/',
}

// Service addresses: "category" /services/cloud/managed-services/, "flat" /services/managed-services/,
// "root" /managed-services/. Category pages stay at /services/cloud/ in every mode.
export const SERVICE_MODES = ['category', 'flat', 'root']

const valid = (p) => typeof p === 'string' && /^\/([a-z0-9-]+\/)*$/.test(p)

export function base(type) {
  const saved = settings.permalinks?.[type]
  return valid(saved) ? saved : DEFAULT_BASES[type]
}

export const isRoot = (type) => base(type) === '/'

// Address of the list page (blog index and so on).
export const listPath = (type) => (isRoot(type) ? DEFAULT_BASES[type] : base(type))

export function serviceMode() {
  const saved = settings.permalinks?.services
  return SERVICE_MODES.includes(saved) ? saved : 'category'
}

// "/insights/blogs/my-post/" becomes "/blog/my-post/" when the blog prefix is "/blog/".
export function permalink(path) {
  for (const [type, def] of Object.entries(DEFAULT_BASES)) {
    if (path === def) return listPath(type)
    if (path.startsWith(def)) return base(type) + path.slice(def.length)
  }
  return path
}

// Route path without slashes, for React Router: "/blog/" becomes "blog".
export const routePath = (type) => base(type).replace(/^\/|\/$/g, '')
export const listRoutePath = (type) => listPath(type).replace(/^\/|\/$/g, '')

// For old links after a prefix change: the new address, or null if the path is not under an old prefix.
// Service addresses are handled by the service page itself, which knows the service list.
export function fromOldBase(pathname) {
  const p = pathname.endsWith('/') ? pathname : `${pathname}/`
  for (const [type, def] of Object.entries(DEFAULT_BASES)) {
    const now = base(type)
    if (now !== def && p !== def && p.startsWith(def)) return now + p.slice(def.length)
  }
  return null
}
