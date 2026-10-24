import SiteRoot from '../src/SiteRoot'
import { getContent } from '../src/lib/build'

export const metadata = { title: 'Page Not Found | AchiveX LLC', robots: { index: false } }

// Served for any address without its own HTML file. The app reads the real address in the browser,
// so a post published after the last build still opens, and anything else shows the 404 page.
export default async function NotFound() {
  const rows = await getContent()
  return <SiteRoot path={null} rows={rows} />
}
