// Editable text and images for the fixed pages, keyed by URL.
// The admin "Edit Page" screen saves changes to the pages table; content.js merges them in at startup.
// Naming: keys ending in Image, Images, Photo, Cover or Logo are treated as images by the admin.
import whoWeAre from './content/whoWeAre.js'
import servicesOverview from './content/servicesOverview.js'
import caseStudiesPage from './content/caseStudiesPage.js'
import newsroom from './content/newsroom.js'
import whitepapersPage from './content/whitepapersPage.js'
import blogsPage from './content/blogsPage.js'
import contact from './content/contact.js'
import insightsPage from './content/insightsPage.js'
import industriesPage from './content/industriesPage.js'
import careers from './content/careers.js'
import privacyPolicy from './content/privacyPolicy.js'
import terms from './content/terms.js'

export const pageContent = {
  '/about-us/': whoWeAre,
  '/services/': servicesOverview,
  '/insights/case-studies/': caseStudiesPage,
  '/insights/newsroom/': newsroom,
  '/insights/whitepapers/': whitepapersPage,
  '/insights/blogs/': blogsPage,
  '/contact-us/': contact,
  '/insights/': insightsPage,
  '/industries/': industriesPage,
  '/careers/': careers,
  '/privacy-policy/': privacyPolicy,
  '/terms-of-service/': terms,
}
