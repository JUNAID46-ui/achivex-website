// Site wide settings managed from the admin panel (site_settings table).
// These defaults are used until the database values load.
export const settings = {
  seo: {
    titleTemplate: '%title% | AchiveX LLC',
    homeTitle: 'AchiveX LLC | Empowering Businesses Through Digital Innovation',
    defaultDescription: 'AchiveX LLC is a Wyoming based marketing and software solutions company offering digital, data, cloud, security and business process services.',
    ogImage: '',
    twitterHandle: '@achivexllc',
    googleVerification: '',
    bingVerification: '',
  },
  schema: {
    type: 'Organization',
    name: 'AchiveX LLC',
    url: 'https://www.achivex.com',
    logo: '',
    phone: '+1 307 622 1140',
    email: 'info@achivex.com',
    region: 'WY',
    country: 'US',
  },
}

// Per URL SEO overrides keyed by path, for example "/services/".
export const seoMeta = {}

// Active redirects: [{ from, to, status }]
export const redirects = []
