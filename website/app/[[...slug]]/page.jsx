import SiteRoot from '../../src/SiteRoot'
import { allPaths, getContent, metaFor } from '../../src/lib/build'

export const dynamicParams = false

const toPath = (slug) => `/${(slug || []).join('/')}${slug?.length ? '/' : ''}`

export async function generateStaticParams() {
  return (await allPaths()).map((p) => ({ slug: p.split('/').filter(Boolean) }))
}

export async function generateMetadata({ params }) {
  const path = toPath((await params).slug)
  const m = await metaFor(path)
  return {
    title: m.title,
    description: m.description,
    alternates: { canonical: m.canonical || path },
    robots: m.noindex ? { index: false, follow: false } : undefined,
    openGraph: { title: m.title, description: m.description, url: path, type: 'website', images: m.image ? [m.image] : undefined },
    twitter: { card: 'summary_large_image', title: m.title, description: m.description },
  }
}

export default async function Page({ params }) {
  const rows = await getContent()
  return <SiteRoot path={toPath((await params).slug)} rows={rows} />
}
