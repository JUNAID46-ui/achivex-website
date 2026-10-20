import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ShareButtons from '../../components/ShareButtons'
import BlogCard from '../../components/BlogCard'
import CTA from '../../components/CTA'
import NotFound from '../NotFound'
import { blogs, getBlog, readTime } from '../../data/blogs'
import { formatDate } from '../../utils/format'
import jumpTo from '../../utils/jumpTo'

export default function BlogPost() {
  const { slug } = useParams()
  const post = getBlog(slug)
  const [activeId, setActiveId] = useState(null)
  useSeo(post?.title, post?.excerpt)

  // Highlight the section currently in view in the table of contents.
  useEffect(() => {
    if (!post) return
    const obs = new IntersectionObserver(
      (entries) => entries.forEach((e) => e.isIntersecting && setActiveId(e.target.id)),
      { rootMargin: '-30% 0px -60% 0px' },
    )
    post.sections.forEach((s) => {
      const el = document.getElementById(s.id)
      if (el) obs.observe(el)
    })
    return () => obs.disconnect()
  }, [post])

  if (!post) return <NotFound />
  const related = blogs.filter((b) => b.slug !== slug && b.category === post.category)
    .concat(blogs.filter((b) => b.slug !== slug && b.category !== post.category))
    .slice(0, 3)

  return (
    <>
      <article className="bg-canvas pb-20 pt-12">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Insights' }, { label: 'Blogs', to: '/insights/blogs/' }, { label: post.category }]} />
        </div>

        <header className="mx-auto mt-12 max-w-read px-5 text-center">
          <h1 className="h-display text-4xl md:text-6xl">{post.title}</h1>
          <p className="mt-6 text-sm text-body/60">
            By <span className="font-semibold text-white">{post.author}</span> / <time dateTime={post.date}>{formatDate(post.date)}</time> / {readTime(post)} min read / <span className="font-semibold text-accent">{post.category}</span>
          </p>
        </header>

        <FadeUp className="container-site mt-12">
          <Img src={post.image} alt={post.title} className="aspect-[21/9] w-full object-cover" />
        </FadeUp>

        <div className="container-site mt-14 grid gap-12 lg:grid-cols-[220px_minmax(0,720px)_1fr]">
          <aside className="lg:sticky lg:top-28 lg:self-start">
            <nav aria-label="Table of contents" className="bg-mist p-6 lg:bg-transparent lg:p-0">
              <p className="text-sm font-bold text-white">In this article</p>
              <ol className="mt-4 space-y-3 border-l-2 border-mist text-sm">
                {post.sections.map((s) => (
                  <li key={s.id}>
                    <a
                      href={`#${s.id}`} onClick={(e) => jumpTo(e, s.id)}
                      className={`-ml-0.5 block border-l-2 pl-4 transition-colors hover:text-accent ${activeId === s.id ? 'border-accent font-semibold text-white' : 'border-transparent text-body/60'}`}
                    >
                      {s.heading}
                    </a>
                  </li>
                ))}
              </ol>
            </nav>
          </aside>

          <div className="prose-body">
            <p className="!text-xl !leading-relaxed">
              <strong className="font-bold text-white">{post.intro.keyword}</strong>{post.intro.text}
            </p>
            {post.sections.map((s) => (
              <section key={s.id}>
                <h2 id={s.id}>{s.heading}</h2>
                {s.paragraphs.map((p, i) => <p key={i}>{p}</p>)}
                {s.list && <ul>{s.list.map((li) => <li key={li}>{li}</li>)}</ul>}
              </section>
            ))}
            <div className="mt-12 border-t border-mist pt-6">
              <ShareButtons title={post.title} />
            </div>
          </div>
        </div>
      </article>

      <section className="bg-mist py-20">
        <div className="container-site">
          <h2 className="h-display text-4xl">Related articles</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {related.map((b) => <BlogCard key={b.slug} post={b} />)}
          </div>
        </div>
      </section>

      <CTA variant="split" title="Want to talk this through?" text="If this article raised questions about your own setup, book a free consultation with our team." />
    </>
  )
}
