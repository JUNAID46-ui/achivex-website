import { useEffect, useRef, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowRight, ArrowUpRight, Check } from 'lucide-react'
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
// ![alt](url "caption") is an image across the text column, and a block that starts with
// "> " is a pull quote.
const IMAGE = /^!\[([^\]]*)\]\(((?:https:\/\/|\/)[^)\s"]+)(?:\s+"([^"]*)")?\)$/
function Figure({ src, alt, caption }) {
  return (
    <FadeUp as="figure" className="my-14">
      <div className="overflow-hidden rounded-[24px] bg-site-grey">
        <Img src={src} alt={alt} sizes="(min-width: 1024px) 720px, 100vw" className="w-full object-cover" />
      </div>
      {caption && <figcaption className="mt-4 text-[14px] leading-relaxed text-site-text">{caption}</figcaption>}
    </FadeUp>
  )
}
function Paragraphs({ items }) {
  return (items || []).map((p, i) => {
    const img = p.trim().match(IMAGE)
    if (img) return <Figure key={i} alt={img[1]} src={img[2]} caption={img[3]} />
    if (p.startsWith('> ')) {
      return (
        <blockquote key={i} className="my-12 border-l-[3px] border-site-brand pl-7 font-display text-[24px] font-medium leading-[1.4] tracking-[-0.01em] text-site-heading md:text-[30px]">
          <Inline text={p.slice(2).trim()} />
        </blockquote>
      )
    }
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

// The big section number in the left margin. It stays in view while its section is read.
function Marker({ n, total }) {
  return (
    <div className="mb-4 flex items-baseline gap-3 lg:sticky lg:top-32 lg:mb-0 lg:block lg:self-start">
      <span className="font-display text-[44px] font-medium leading-none tracking-[-0.04em] text-site-brand/15 lg:text-[88px]">{pad(n)}</span>
      <span className="text-[12px] font-semibold uppercase tracking-[0.16em] text-site-text lg:mt-3 lg:block">of {pad(total)}</span>
    </div>
  )
}

// One part of the article: the number in the margin and the text beside it.
function Part({ n, total, children, id }) {
  return (
    <section aria-labelledby={id} className="mx-auto mt-20 grid max-w-[960px] lg:mt-28 lg:grid-cols-[200px_minmax(0,1fr)]">
      <Marker n={n} total={total} />
      <div className="min-w-0 max-w-[720px]">{children}</div>
    </section>
  )
}

const h2 = 'scroll-mt-28 font-display text-[30px] font-medium leading-[1.12] tracking-[-0.025em] text-site-heading md:text-[42px]'

// Blog article template: a dark editorial hero with the featured image overlapping it, then the
// article in one column with the section number held in the left margin, a drop cap, pull quotes,
// wide images, FAQs, share links, the author, a large "Up next" link and related posts.
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
  const others = blogs.filter((b) => b.slug !== slug && b.category === post.category)
    .concat(blogs.filter((b) => b.slug !== slug && b.category !== post.category))
  const [next, ...rest] = others
  const related = rest.slice(0, 3)
  const minutes = readTime(post)
  const total = post.sections.length + (faqs.length ? 1 : 0)

  return (
    <>
      <div className="fixed inset-x-0 top-0 z-[60] h-[3px] bg-transparent" aria-hidden="true">
        <div className="h-full origin-left bg-site-brand transition-transform duration-150" style={{ transform: `scaleX(${progress})` }} />
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


        <div ref={bodyRef} className="container-site pb-24 pt-16 md:pt-24">
          {/* Opening paragraph with a drop cap, in line with the text column. */}
          <div className="mx-auto max-w-[960px] lg:grid lg:grid-cols-[200px_minmax(0,1fr)]">
            <div className="hidden lg:block" />
            <p className="max-w-[720px] text-[21px] leading-[1.75] text-site-heading first-letter:float-left first-letter:mr-3 first-letter:mt-1 first-letter:font-display first-letter:text-[76px] first-letter:font-medium first-letter:leading-[0.8] first-letter:text-site-brand md:text-[23px]">
              <strong className="font-semibold text-site-heading">{post.intro.keyword}</strong><Inline text={post.intro.text} />
            </p>
          </div>

          {post.sections.map((s, i) => (
            <Part key={s.id} n={i + 1} total={total} id={s.id}>
              <h2 id={s.id} className={h2}>{s.heading}</h2>
              <div className="article-body mt-6">
                <Paragraphs items={s.paragraphs} />
              </div>
              {s.list?.length > 0 && <Bullets items={s.list} />}
              {(s.subsections || []).map((sub, j) => (
                <div key={j} className="mt-10">
                  <h3 className="font-display text-[22px] font-semibold leading-snug tracking-tight text-site-heading md:text-[24px]">{sub.heading}</h3>
                  <div className="article-body mt-3">
                    <Paragraphs items={sub.paragraphs} />
                  </div>
                </div>
              ))}
            </Part>
          ))}

          {faqs.length > 0 && (
            <Part n={total} total={total} id="faqs">
              <h2 id="faqs" className={h2}>Frequently asked questions</h2>
              <div className="mt-8"><FAQ items={faqs} renderAnswer={(a) => <Inline text={a} />} /></div>
            </Part>
          )}

          <div className="mx-auto mt-20 max-w-[960px] lg:grid lg:grid-cols-[200px_minmax(0,1fr)]">
            <div className="hidden lg:block" />
            <div className="max-w-[720px]">
              <div className="flex flex-wrap items-center justify-between gap-6 border-t border-site-line pt-8">
                <div className="flex items-center gap-4">
                  <span className="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-ink">
                    <img src="/brand/mark-light.png" alt="" width="50" height="24" className="h-6 w-auto" />
                  </span>
                  <div>
                    <p className="text-[12px] font-semibold uppercase tracking-[0.16em] text-site-text">Written by</p>
                    <p className="mt-0.5 text-[17px] font-semibold text-site-heading">{post.author}</p>
                  </div>
                </div>
                <ShareButtons title={post.title} />
              </div>
            </div>
          </div>
        </div>
      </article>

      {/* The next article as one large link: image, title and an arrow. */}
      {next && (
        <section className="bg-canvas pb-20 md:pb-24">
          <div className="container-site">
            <Link to={permalink(`/insights/blogs/${next.slug}/`)} className="group relative block overflow-hidden rounded-[28px] bg-ink">
              {next.image && <Img src={next.image} alt="" sizes="(min-width: 1280px) 1200px, 100vw" className="absolute inset-0 h-full w-full object-cover opacity-55 transition-transform duration-[1200ms] ease-out group-hover:scale-[1.04]" />}
              <div className="absolute inset-0 bg-gradient-to-r from-site-black/85 via-site-black/55 to-transparent" aria-hidden="true" />
              <div className="relative flex min-h-[320px] flex-col justify-end p-8 md:min-h-[420px] md:p-14">
                <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-white/70">Up next</p>
                <div className="mt-4 flex items-end justify-between gap-8">
                  <h2 className="max-w-[760px] text-[30px] font-medium leading-[1.1] tracking-[-0.03em] text-[#FFFFFF] md:text-[48px]">{next.title}</h2>
                  <span className="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-[#FFFFFF] text-site-heading transition-transform duration-500 group-hover:-rotate-45 md:h-16 md:w-16">
                    <ArrowRight className="h-6 w-6" aria-hidden="true" />
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </section>
      )}

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
