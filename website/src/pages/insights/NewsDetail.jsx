import { useParams } from 'react-router-dom'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ShareButtons from '../../components/ShareButtons'
import ArrowLink from '../../components/ArrowLink'
import NotFound from '../NotFound'
import { news, getNews } from '../../data/news'
import { formatDate } from '../../utils/format'

export default function NewsDetail() {
  const { slug } = useParams()
  const n = getNews(slug)
  useSeo(n?.title, n?.summary)
  if (!n) return <NotFound />
  const more = news.filter((x) => x.slug !== slug).slice(0, 3)

  return (
    <article className="bg-canvas pb-24 pt-12">
      <div className="container-site">
        <Breadcrumb items={[{ label: 'Insights' }, { label: 'Newsroom', to: '/insights/newsroom/' }, { label: 'Article' }]} />
      </div>
      <header className="mx-auto mt-14 max-w-read px-5">
        <h1 className="text-4xl font-extrabold leading-tight tracking-tight md:text-5xl">{n.title}</h1>
        <time dateTime={n.date} className="mt-4 block text-sm font-semibold text-accent">{formatDate(n.date)}</time>
        <p className="mt-6 text-xl leading-relaxed text-body/70">{n.summary}</p>
      </header>
      <FadeUp className="mx-auto mt-12 max-w-4xl px-5">
        <Img src={n.image} alt={n.title} className="aspect-[16/9] w-full object-cover" />
      </FadeUp>
      <div className="prose-body mx-auto mt-12 max-w-read px-5">
        {n.body.map((p, i) => <p key={i}>{p}</p>)}
      </div>
      <div className="mx-auto mt-10 max-w-read border-y border-mist px-5 py-6">
        <ShareButtons title={n.title} />
      </div>
      <aside className="mx-auto mt-14 max-w-read px-5">
        <h2 className="text-lg font-bold">More from the newsroom</h2>
        <ul className="mt-4 space-y-3">
          {more.map((m) => (
            <li key={m.slug}><ArrowLink to={`/insights/newsroom/${m.slug}/`} className="text-white">{m.title}</ArrowLink></li>
          ))}
        </ul>
      </aside>
    </article>
  )
}
