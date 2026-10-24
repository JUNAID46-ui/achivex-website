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
import { permalink } from '../../lib/permalinks'
import { faqSchema } from '../../lib/schema'

const pad = (n) => String(n).padStart(2, '0')

// Inline formatting in article text: [label](url) becomes a link and **text** becomes bold.
// Built as React elements, so nothing from the database is ever inserted as raw HTML.
const INLINE = /\[([^\]]+)\]\(((?:https?:\/\/|\/|#|mailto:)[^)\s]*)\)|\*\*([^*]+)\*\*/g
function Inline({ text }) {
  const out = []
  let last = 0
  for (const m of String(text).matchAll(INLINE)) {
    if (m.index > last) out.push(String(text).slice(last, m.index))
    if (m[3]) out.push(<strong key={m.index} className="font-semibold text-site-heading">{m[3]}</strong>)
    else if (m[2].startsWith('/')) out.push(<Link key={m.index} to={m[2]} className="text-site-brand underline underline-offset-4">{m[1]}</Link>)
    else out.push(<a key={m.index} href={m[2]} target={m[2].startsWith('http') ? '_blank' : undefined} rel={m[2].startsWith('http') ? 'noopener noreferrer' : undefined} className="text-site-brand underline underline-offset-4">{m[1]}</a>)
    last = m.index + m[0].length
  }
  if (last < String(text).length) out.push(String(text).slice(last))
  return out
}

// Paragraph blocks from the admin. A block that starts with "### " is an H3 heading;
// any lines after it in the same block are a normal paragraph. A block written as
// ![alt](url "caption") is an image, shown wider than the text.
const IMAGE = /^!\[([^\]]*)\]\(((?:https:\/\/|\/)[^)\s"]+)(?:\s+"([^"]*)")?\)$/
function Figure({ src, alt, caption }) {
  return (
    <figure className="my-12 lg:-mx-24">
      <div className="overflow-hidden rounded-[24px] bg-site-grey">
        <Img src={src} alt={alt} sizes="(min-width: 1024px) 960px, 100vw" className="w-full object-cover" />
      </div>
      {caption && <figcaption className="mx-auto mt-4 max-w-[640px] text-center text-[14px] leading-relaxed text-site-text">{caption}</figcaption>}
    </figure>
  )
}
function Paragraphs({ items }) {
  return (items || []).map((p, i) => {
    const img = p.trim().match(IMAGE)
    if (img) return <Figure key={i} alt={img[1]} src={img[2]} caption={img[3]} />
    if (!p.startsWith('### ')) return <p key={i}><Inline text={p} /></p>
    const [head, ...rest] = p.split('\n')
    return (
      <div key={i}>
        <h3>{head.slice(4).trim()}</h3>
        {rest.join(' ').trim() && <p><Inline text={rest.join(' ').trim()} /></p>}
      </div>
    )
  })
}

function Bullets({ items }) {
  return (
    <ul className="mb-7 mt-2 space-y-3">
      {items.map((li) => (
        <li key={li} className="flex gap-3 text-[17px] leading-[1.7] text-site-text">
          <span className="mt-[5px] grid h-5 w-5 shrink-0 place-items-center rounded-full bg-navy/10 text-navy">
            <Check className="h-3 w-3" strokeWidth={3} aria-hidden="true" />
          </span>
          <span><Inline text={li} /></span>
        </li>
      ))}
    </ul>
  )
}

// Blog article template: dark editorial hero with an overlapping image, one reading column with
// numbered H2 sections, H3 sub sections and wide images, FAQs, share links, author note and
// related posts. Articles end with the footer; there is no call to action block.
export default function BlogPost() {
  const { slug } = useParams()
  const post = getBlog(slug)
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
  // Fill the reading bar at the top as the article scrolls.
  useEffect(() => {
    if (!post) return
    const onScroll = () => {
      const el = bodyRef.current
      if (!el) return
      const { top, height } = el.getBoundingClientRect()
      setProgress(Math.min(1, Math.max(0, (window.innerHeight * 0.4 - top) / height)))
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [post])

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
              <div className="absolute inset-0 bg-site-black/65" aria-hidden="true" />
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
            <div className="mx-auto max-w-[1120px] overflow-hidden rounded-[28px] bg-site-grey shadow-[0_30px_80px_-40px_rgba(0,0,0,0.45)]">
              <Img priority sizes="(min-width: 1280px) 1200px, 100vw" src={post.image} alt={post.title} className="motion-settle aspect-[16/9] w-full object-cover md:aspect-[21/9]" />
            </div>
          </div>
        )}

        {/* Body: one reading column; images in the text are shown wider than it. */}
        <div className="pb-24 pt-14 md:pt-20">
          <div ref={bodyRef} className="container-site">
            <div className="mx-auto max-w-[740px]">
              <p className="text-[21px] leading-[1.7] text-site-heading">
                <strong className="font-semibold text-site-heading">{post.intro.keyword}</strong><Inline text={post.intro.text} />
              </p>

              {post.sections.map((s, i) => (
                <section key={s.id} className="mt-16">
                  <div className="flex items-center gap-4">
                    <span className="text-[13px] font-semibold tabular-nums tracking-[0.12em] text-navy">{pad(i + 1)}</span>
                    <span className="h-px flex-1 bg-site-line" aria-hidden="true" />
                  </div>
                  <h2 id={s.id} className="mt-5 scroll-mt-28 font-display text-[28px] font-medium leading-tight tracking-tight text-site-heading md:text-[36px]">{s.heading}</h2>
                  <div className="prose-body mt-5">
                    <Paragraphs items={s.paragraphs} />
                  </div>
                  {s.list?.length > 0 && <Bullets items={s.list} />}
                  {(s.subsections || []).map((sub, j) => (
                    <div key={j} className="mt-8 border-l-2 border-navy/15 pl-6">
                      <h3 className="font-display text-[21px] font-semibold leading-snug tracking-tight text-site-heading md:text-[23px]">{sub.heading}</h3>
                      <div className="prose-body mt-3">
                        <Paragraphs items={sub.paragraphs} />
                      </div>
                    </div>
                  ))}
                </section>
              ))}

              {/* FAQs as on the home page: an open list that expands, no box around it. */}
              {faqs.length > 0 && (
                <section className="mt-16">
                  <div className="flex items-center gap-4">
                    <span className="text-[13px] font-semibold tabular-nums tracking-[0.12em] text-navy">{pad(post.sections.length + 1)}</span>
                    <span className="h-px flex-1 bg-site-line" aria-hidden="true" />
                  </div>
                  <h2 id="faqs" className="mt-5 scroll-mt-28 font-display text-[28px] font-medium leading-tight tracking-tight text-site-heading md:text-[36px]">Frequently asked questions</h2>
                  <div className="mt-6"><FAQ items={faqs} renderAnswer={(a) => <Inline text={a} />} /></div>
                </section>
              )}

              <div className="mt-16 flex flex-wrap items-center justify-between gap-4 border-y border-site-line py-6">
                <Link to={permalink('/insights/blogs/')} className="inline-flex items-center gap-2 text-[15px] font-semibold text-site-heading hover:text-navy">
                  <ArrowLeft className="h-4 w-4" aria-hidden="true" /> All articles
                </Link>
                <ShareButtons title={post.title} />
              </div>

              <div className="mt-10 flex flex-col gap-6 sm:flex-row sm:items-center">
                <span className="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-ink">
                  <img src={`/brand/mark-light.png`} alt="" width="58" height="28" className="h-7 w-auto" />
                </span>
                <div className="flex-1">
                  <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-site-text">Written by</p>
                  <p className="mt-1 text-[18px] font-semibold text-site-heading">{post.author}</p>
                  <p className="mt-1 text-[15px] leading-relaxed text-site-text">Practical notes on websites, software, AI automation and growth marketing from the AchiveX team.</p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </article>

      {related.length > 0 && (
        <section className="bg-site-grey py-20 md:py-24">
          <div className="container-site">
            <div className="flex flex-wrap items-end justify-between gap-4">
              <h2 className="text-[34px] font-medium tracking-[-0.02em] text-site-heading md:text-[44px]">Keep reading</h2>
              <Link to={permalink('/insights/blogs/')} className="inline-flex items-center gap-2 text-[15px] font-semibold text-site-heading hover:text-navy">
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
