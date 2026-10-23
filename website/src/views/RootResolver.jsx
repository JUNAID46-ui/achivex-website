import { useParams } from 'react-router-dom'
import { blogs } from '../data/blogs'
import { caseStudies } from '../data/caseStudies'
import { news } from '../data/news'
import { whitepapers } from '../data/whitepapers'
import { getService } from '../data/services'
import { isRoot, serviceMode } from '../lib/permalinks'
import BlogPost from './insights/BlogPost'
import CaseStudyDetail from './insights/CaseStudyDetail'
import NewsDetail from './insights/NewsDetail'
import WhitepaperDetail from './insights/WhitepaperDetail'
import ServiceDetail from './services/ServiceDetail'
import NotFound from './NotFound'

// One-segment addresses such as /my-post/ when Settings, Permalinks puts a content type or the
// services at the root of the site. The admin keeps these slugs unique. Anything else (custom
// pages, redirects, real 404s) goes on to NotFound.
const TYPES = [
  ['blogs', blogs, BlogPost],
  ['caseStudies', caseStudies, CaseStudyDetail],
  ['newsroom', news, NewsDetail],
  ['whitepapers', whitepapers, WhitepaperDetail],
]

export default function RootResolver() {
  const { slug } = useParams()
  for (const [type, items, Page] of TYPES) {
    if (isRoot(type) && items.some((x) => x.slug === slug)) return <Page />
  }
  if (serviceMode() === 'root' && getService(slug)) return <ServiceDetail singleSlug={slug} />
  return <NotFound />
}
