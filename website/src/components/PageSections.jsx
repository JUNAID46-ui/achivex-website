import { homeLayouts } from '../pages/Home'
import { genericLayouts } from './sections/GenericSections'

// Looked up at render time: Home imports this file too, so homeLayouts is not ready at load.
const layoutFor = (type) => genericLayouts[type] || homeLayouts[type]

// Renders Page Builder sections in order. Used by the home page, custom pages,
// and for extra sections added to the bottom of any fixed page.
export default function PageSections({ sections = [] }) {
  return (
    <>
      {[...sections].filter((s) => s.visible !== false).sort((a, b) => a.sort - b.sort).map((s, i) => {
        const Layout = layoutFor(s.type || s.key)
        return Layout ? <Layout key={s.id || s.key || i} d={s.data || {}} /> : null
      })}
    </>
  )
}
