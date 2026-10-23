import { homeLayouts } from '../pages/Home'
import { genericLayouts } from './sections/GenericSections'
import { designCss } from '../lib/sectionDesign'

// Looked up at render time: Home imports this file too, so homeLayouts is not ready at load.
const layoutFor = (type) => genericLayouts[type] || homeLayouts[type]

// Renders Page Builder sections in order. Used by the home page, custom pages,
// and for extra sections added to the bottom of any fixed page.
export default function PageSections({ sections = [] }) {
  return (
    <>
      {[...sections].filter((s) => s.visible !== false).sort((a, b) => a.sort - b.sort).map((s, i) => {
        const Layout = layoutFor(s.type || s.key)
        if (!Layout) return null
        const key = s.id || s.key || i
        const css = designCss(key, s.data?.design)
        if (!css) return <Layout key={key} d={s.data || {}} />
        // Sections with Design options get a scoped style block.
        return (
          <div key={key} data-sec={key}>
            <style>{css}</style>
            <Layout d={s.data || {}} />
          </div>
        )
      })}
    </>
  )
}
