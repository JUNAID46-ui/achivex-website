import { useParams } from 'react-router-dom'
import { getCategory } from '../../data/categories'
import ServiceCategory from './ServiceCategory'
import ServiceDetail from './ServiceDetail'
import NotFound from '../NotFound'

// /services/:slug is either a category page or a single page service.
export default function ServiceResolver() {
  const { slug } = useParams()
  const category = getCategory(slug)
  if (!category) return <NotFound />
  return category.single ? <ServiceDetail singleSlug={slug} /> : <ServiceCategory category={category} />
}
