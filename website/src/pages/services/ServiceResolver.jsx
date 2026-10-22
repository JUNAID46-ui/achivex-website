import { Navigate, useParams } from 'react-router-dom'
import { getCategory } from '../../data/categories'
import { getService, legacyAreas } from '../../data/services'
import ServiceCategory from './ServiceCategory'
import ServiceDetail from './ServiceDetail'
import NotFound from '../NotFound'

// /services/:slug is a category page, a single page service, or a service when permalinks
// leave out the category (ServiceDetail sends it on to the current address).
export default function ServiceResolver() {
  const { slug } = useParams()
  const category = getCategory(slug)
  if (legacyAreas[slug]) return <Navigate to={`/services/${legacyAreas[slug]}/`} replace />
  if (category && !category.single) return <ServiceCategory category={category} />
  return getService(slug) ? <ServiceDetail singleSlug={slug} /> : <NotFound />
}
