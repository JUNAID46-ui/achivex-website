import { useEffect, useRef, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, ArrowUpRight, Check } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import Breadcrumb from '../../components/Breadcrumb'
import ShareButtons from '../../components/ShareButtons'
import BlogCard from '../../components/BlogCard'
import FAQ from '../../components/FAQ'
import NotFound from '../NotFound'
import { blogs, getBlog, readTime } from '../../data/blogs'
import { formatDate } from '../../utils/format'
import jumpTo from '../../utils/jumpTo'
import { permalink } from '../../lib/permalinks'
import { faqSchema } from '../../lib/schema'

const pad = (n) => String(n).padStart(2, '0')

// Paragraph blocks from the admin. A block that starts with "### " is an H3 heading;
// any lines after it in the same block are a normal paragraph.
function Paragraphs({ items }) {
  return (items || []).map((p, i) => {
    if (!p.startsWith('### ')) return <p key={i}>{p}</p>
    const [head, ...rest] = p.split('\n')
    return (
      <div key={i}>
        <h3>{head.slice(4).trim()}</h3>
        {rest.join(' ').trim() && <p>{rest.join(' ').trim()}</p>}
      </div>
    )
  })
}

function Bullets({ items }) {
  return (
    <ul className="mb-7 mt-2 space-y-3">
      {items.map((li) => (
        <li key={li} className="flex gap-3 text-[17px] leading-[1.7] text-[#3F4550]">
          <span className="mt-[5px] grid h-5 w-5 shrink-0 place-items-center rounded-full bg-navy/10 text-navy">
            <Check className="h-3 w-3" strokeWidth={3} aria-hidden="true" />
          </span>
          {li}
        </li>
      ))}
    </ul>
  )
}

// Blog article template: dark editorial hero with an overlapping image, a sticky contents list that
// tracks the reader, numbered H2 sections with H3 sub sections, FAQs, author note and
// related posts. Articles end with the footer; there is no call to action block.
export default function BlogPost() {
  const { slug } = useParams()
  const post = getBlog(slug)
  const [activeId, setActiveId] = useState(null)
  const [progress, setProgress] = useState(0)
  const bodyRef = useRef(null)
  const faqs = (post?.faqs || []).filter((f) => f.q && f.a)
  useSeo(post?.title, post?.excerpt, post && [
    {
      '@context': 'https://schema.org',
      '@type': 'BlogPosting',
      headline: post.title,
      description: post.excerpt,
      image: post.image || undefined,
      datePublished: post.date,
      author: { '@type': 'Organization', name: post.author },
      publisher: { '@type': 'Organization', name: 'AchiveX LLC' },
      articleSection: post.category,
    },
    faqSchema(faqs),
  ])
  const toc = post ? [...post.sections, ...(faqs.length ? [{ id: 'faqs', heading: 'Frequently asked questions' }] : [])] : []

  // Highlight the section in view and fill the reading bar as the article scrolls.
  useEffect(() => {
    if (!post) return
    const obs = new IntersectionObserver(
      (entries) => entries.forEach((e) => e.isIntersecting && setActiveId(e.target.id)),
      { rootMargin: '-30% 0px -60% 0px' },
    )
    toc.forEach(({ id }) => {
      const el = document.getElementById(id)
      if (el) obs.observe(el)
    })
    const onScroll = () => {
      const el = bodyRef.current
      if (!el) return
      const { top, height } = el.getBoundingClientRect()
      const done = (window.innerHeight * 0.4 - top) / height
      setProgress(Math.min(1, Math.max(0, done)))
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => {
      obs.disconnect()
      window.removeEventListener('scroll', onScroll)
    }
  }, [post]) // eslint-disable-line react-hooks/exhaustive-deps

  if (!post) return <NotFound />
  const related = blogs.filter((b) => b.slug !== slug && b.category === post.category)
    .concat(blogs.filter((b) => b.slug !== slug && b.category !== post.category))
    .slice(0, 3)
  const minutes = readTime(post)

  return (
    <>
      <div className="fixed inset-x-0 top-0 z-[60] h-[3px] bg-transparent" aria-hidden="true">
        <div className="h-full origin-left bg-navy transition-transform duration-150" style={{ transform: `scaleX(${progress})` }} />
      </div>

      <article className="bg-canvas">
        {/* Hero: dark editorial band with the featured image overlapping its lower edge. */}
        <header
          className="relative overflow-hidden bg-ink text-white"
          style={post.heroColor ? { backgroundColor: post.heroColor } : undefined}
        >
          {post.heroImage && (
            <>
              <Img src={post.heroImage} alt="" className="absolute inset-0 h-full w-full object-cover" />
              <div className="absolute inset-0 bg-[#000000]/65" aria-hidden="true" />
            </>
          )}
          <div className={`container-site relative pt-8 md:pt-10 ${post.image ? 'pb-[150px] md:pb-[230px]' : 'pb-20'}`}>
            <Breadcrumb light items={[{ label: 'Blog', to: permalink('/insights/blogs/') }, { label: post.title }]} />
            <div className="motion-rise mx-auto mt-14 max-w-[920px] text-center md:mt-20">
              <Link
                to={permalink('/insights/blogs/')}
                className="inline-flex items-center rounded-full border border-white/20 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.16em] text-white/80 transition-colors hover:border-white hover:text-white"
              >
                {post.category}
              </Link>
              <h1 className="mt-7 text-[36px] font-medium leading-[1.1] tracking-[-0.03em] text-white sm:text-[48px] xl:text-[60px]">{post.title}</h1>
              {post.excerpt && <p className="mx-auto mt-6 max-w-[640px] text-[18px] leading-[1.65] text-white/65">{post.excerpt}</p>}
              <div className="mt-9 inline-flex items-center gap-3 text-left">
                <span className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#FFFFFF]">
                  <img src={`/brand/mark-dark.png`} alt="" width="37" height="18" className="h-[18px] w-auto" />
                </span>
                <span>
                  <span className="block text-[15px] font-semibold text-white">{post.author}</span>
                  <span className="block text-[13px] text-white/65">
                    <time dateTime={post.date}>{formatDate(post.date)}</time>
                    <span className="mx-2" aria-hidden="true">&middot;</span>{minutes} min read
                  </span>
                </span>
              </div>
            </div>
          </div>
        </header>
        {post.image && (
          <div className="container-site relative -mt-[110px] md:-mt-[190px]">
            <div className="mx-auto max-w-[1120px] overflow-hidden rounded-[28px] bg-[#E9ECF0] shadow-[0_30px_80px_-40px_rgba(0,0,0,0.45)]">
              <Img priority sizes="(min-width: 1280px) 1200px, 100vw" src={post.image} alt={post.title} className="motion-settle aspect-[16/9] w-full object-cover md:aspect-[21/9]" />
            </div>
          </div>
        )}

        {/* Body */}
        <div className="pb-24 pt-14 md:pt-20">
          <div className="container-site grid gap-12 lg:grid-cols-[250px_minmax(0,1fr)] xl:grid-cols-[270px_minmax(0,720px)_1fr]">
            <aside className="lg:sticky lg:top-28 lg:self-start">
              <nav aria-label="Table of contents" className="rounded-[22px] border border-[#E6E9EE] bg-[#F7F8FA] p-6">
                <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-[#6E747E]">In this article</p>
                <ol className="mt-5 space-y-1">
                  {toc.map((s, i) => {
                    const on = activeId === s.id
                    return (
                      <li key={s.id}>
                        <a
                          href={`#${s.id}`} onClick={(e) => jumpTo(e, s.id)}
                          className={`flex gap-3 rounded-xl px-3 py-2.5 text-[14px] leading-snug transition-colors ${on ? 'bg-[#FFFFFF] font-semibold text-[#111111] shadow-[0_4px_14px_-8px_rgba(0,0,0,0.2)]' : 'text-[#5B616B] hover:text-[#111111]'}`}
                        >
                          <span className={`tabular-nums ${on ? 'text-navy' : 'text-[#6E747E]'}`}>{pad(i + 1)}</span>
                          {s.heading}
                        </a>
                      </li>
                    )
                  })}
                </ol>
                <div className="mt-5 h-1 overflow-hidden rounded-full bg-[#E6E9EE]" aria-hidden="true">
                  <div className="h-full rounded-full bg-navy transition-[width] duration-150" style={{ width: `${Math.round(progress * 100)}%` }} />
                </div>
              </nav>
              <div className="mt-6 hidden lg:block"><ShareButtons title={post.title} /></div>
            </aside>

            <div ref={bodyRef} className="min-w-0">
              <p className="text-[21px] leading-[1.7] text-[#2A2F37]">
                <strong className="font-semibold text-[#111111]">{post.intro.keyword}</strong>{post.intro.text}
              </p>

              {post.sections.map((s, i) => (
                <section key={s.id} className="mt-14">
                  <p className="text-[13px] font-semibold tracking-[0.12em] text-navy">{pad(i + 1)}</p>
                  <h2 id={s.id} className="mt-2 scroll-mt-28 font-display text-[28px] font-medium leading-tight tracking-tight text-[#111111] md:text-[34px]">{s.heading}</h2>
                  <div className="prose-body mt-5">
                    <Paragraphs items={s.paragraphs} />
                  </div>
                  {s.list?.length > 0 && <Bullets items={s.list} />}
                  {(s.subsections || []).map((sub, j) => (
                    <div key={j} className="mt-8 border-l-2 border-navy/15 pl-6">
                      <h3 className="font-display text-[21px] font-semibold leading-snug tracking-tight text-[#111111] md:text-[23px]">{sub.heading}</h3>
                      <div className="prose-body mt-3">
                        {(sub.paragraphs || []).map((p, k) => <p key={k}>{p}</p>)}
                      </div>
                      {sub.list?.length > 0 && <Bullets items={sub.list} />}
                    </div>
                  ))}
                </section>
              ))}

              {faqs.length > 0 && (
                <section className="mt-16 rounded-[26px] bg-[#EEF0F3] p-7 md:p-10">
                  <p className="text-[13px] font-semibold tracking-[0.12em] text-navy">{pad(post.sections.length + 1)}</p>
                  <h2 id="faqs" className="mt-2 scroll-mt-28 font-display text-[28px] font-medium leading-tight tracking-tight text-[#111111] md:text-[34px]">Frequently asked questions</h2>
                  <div className="mt-6"><FAQ items={faqs} /></div>
                </section>
              )}

              <div className="mt-14 flex flex-col gap-6 rounded-[26px] border border-[#E6E9EE] p-7 sm:flex-row sm:items-center md:p-8">
                <span className="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-ink">
                  <img src={`/brand/mark-light.png`} alt="" width="58" height="28" className="h-7 w-auto" />
                </span>
                <div className="flex-1">
                  <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-[#6E747E]">Written by</p>
                  <p className="mt-1 text-[18px] font-semibold text-[#111111]">{post.author}</p>
                  <p className="mt-1 text-[15px] leading-relaxed text-[#5B616B]">Practical notes on websites, software, AI automation and growth marketing from the AchiveX team.</p>
                </div>
              </div>

              <div className="mt-8 flex flex-wrap items-center justify-between gap-4">
                <Link to={permalink('/insights/blogs/')} className="inline-flex items-center gap-2 text-[15px] font-semibold text-[#111111] hover:text-navy">
                  <ArrowLeft className="h-4 w-4" aria-hidden="true" /> All articles
                </Link>
                <div className="lg:hidden"><ShareButtons title={post.title} /></div>
              </div>
            </div>
          </div>
        </div>
      </article>

      {related.length > 0 && (
        <section className="bg-[#EEF0F3] py-20 md:py-24">
          <div className="container-site">
            <div className="flex flex-wrap items-end justify-between gap-4">
              <h2 className="text-[34px] font-medium tracking-[-0.02em] text-[#111111] md:text-[44px]">Keep reading</h2>
              <Link to={permalink('/insights/blogs/')} className="inline-flex items-center gap-2 text-[15px] font-semibold text-[#111111] hover:text-navy">
                View all articles <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
              </Link>
            </div>
            <div className="mt-10 grid gap-8 md:grid-cols-3">
              {related.map((b) => <BlogCard key={b.slug} post={b} />)}
            </div>
          </div>
        </section>
      )}
    </>
  )
}
