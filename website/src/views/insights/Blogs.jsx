import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { Search, ArrowRight, ChevronLeft, ChevronRight } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Img from '../../components/Img'
import { InnerHero } from '../../components/inner/InnerSections'
import BlogCard from '../../components/BlogCard'
import CTA from '../../components/CTA'
import { blogs, blogCategories, readTime } from '../../data/blogs'
import { formatDate } from '../../utils/format'
import { pageContent } from '../../data/pageContent'
import { permalink } from '../../lib/permalinks'

const PER_PAGE = 6

export default function Blogs() {
  useSeo('Blogs', 'Practical articles from AchiveX on data, cloud, security, strategy and business operations.')
  const pc = pageContent['/insights/blogs/']
  const featured = blogs.find((b) => b.featured) || blogs[0]
  const [cat, setCat] = useState('All')
  const [q, setQ] = useState('')
  const [page, setPage] = useState(1)

  const filtered = useMemo(() => {
    const term = q.trim().toLowerCase()
    return blogs
      .filter((b) => b.slug !== featured.slug)
      .filter((b) => cat === 'All' || b.category === cat)
      .filter((b) => !term || `${b.title} ${b.excerpt}`.toLowerCase().includes(term))
  }, [cat, q, featured.slug])

  const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE))
  const current = Math.min(page, pages)
  const shown = filtered.slice((current - 1) * PER_PAGE, current * PER_PAGE)
  const change = (fn) => (v) => { fn(v); setPage(1) }

  return (
    <>
      <InnerHero crumbs={[{ label: 'Insights', to: '/insights/' }, { label: 'Blogs' }]} eyebrow="Blog" title={pc.heroTitle} text={pc.heroText || 'Practical articles on websites, software, AI automation and marketing, written in plain language by the AchiveX team.'} />
      <section className="bg-canvas pb-16 pt-4">
        <div className="container-site">

          <FadeUp delay={0.1} className="mt-8">
            <Link to={permalink(`/insights/blogs/${featured.slug}/`)} className="group grid overflow-hidden rounded-[28px] bg-[#F3F5F7] lg:grid-cols-[1.3fr_1fr]">
              <div className="overflow-hidden">
                <Img src={featured.image} alt={featured.title} className="aspect-[16/10] h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
              </div>
              <div className="flex flex-col justify-center p-8 md:p-12">
                <h2 className="h-display text-4xl leading-tight md:text-5xl">{featured.title}</h2>
                <p className="mt-3 text-sm text-body/80">
                  <span className="font-semibold text-accent">{pc.featuredLabel} / {featured.category}</span> / {formatDate(featured.date)} / {readTime(featured)} min read
                </p>
                <p className="mt-5 text-lg leading-relaxed text-body/80">{featured.excerpt}</p>
                <span className="mt-8 inline-flex items-center gap-2 font-semibold text-white group-hover:text-accent">
                  {pc.readArticleText} <ArrowRight className="h-4 w-4 text-accent transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </span>
              </div>
            </Link>
          </FadeUp>
        </div>
      </section>

      <section className="bg-mist py-16 md:py-20">
        <div className="container-site">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div role="tablist" aria-label="Filter by category" className="flex flex-wrap gap-6">
              {['All', ...blogCategories].map((c) => (
                <button
                  key={c}
                  role="tab"
                  aria-selected={cat === c}
                  onClick={() => change(setCat)(c)}
                  className={`border-b-2 pb-1 text-sm font-semibold transition-colors ${cat === c ? 'border-accent text-white' : 'border-transparent text-body/80 hover:text-accent'}`}
                >
                  {c === 'All' ? pc.allCategoriesLabel : c}
                </button>
              ))}
            </div>
            <div className="relative w-full lg:w-80">
              <label htmlFor="blog-search" className="sr-only">{pc.searchPlaceholder}</label>
              <Search className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-white/65" aria-hidden="true" />
              <input
                id="blog-search"
                type="search"
                value={q}
                onChange={(e) => change(setQ)(e.target.value)}
                placeholder={pc.searchPlaceholder}
                className="w-full rounded-full border border-[#D5D9E0] bg-[#FFFFFF] py-3 pl-11 pr-5 text-[15px] outline-none focus:border-accent"
              />
            </div>
          </div>

          {shown.length === 0 ? (
            <p className="py-20 text-center text-lg text-body/80">{pc.emptyText}</p>
          ) : (
            <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {shown.map((b, i) => (
                <FadeUp key={b.slug} delay={(i % 3) * 0.05}><BlogCard post={b} /></FadeUp>
              ))}
            </div>
          )}

          {pages > 1 && (
            <nav aria-label="Pagination" className="mt-14 flex items-center justify-center gap-2">
              <button
                onClick={() => setPage(current - 1)}
                disabled={current === 1}
                aria-label="Previous page"
                className="flex h-11 w-11 items-center justify-center rounded-full bg-canvas text-white transition-colors hover:bg-accent hover:text-onaccent disabled:opacity-40 disabled:hover:bg-canvas disabled:hover:text-white"
              >
                <ChevronLeft className="h-5 w-5" />
              </button>
              {Array.from({ length: pages }, (_, i) => i + 1).map((n) => (
                <button
                  key={n}
                  onClick={() => setPage(n)}
                  aria-current={n === current ? 'page' : undefined}
                  className={`h-11 w-11 rounded-full text-sm font-semibold transition-colors ${n === current ? 'bg-accent text-onaccent' : 'bg-canvas text-white hover:bg-accent hover:text-onaccent'}`}
                >
                  {n}
                </button>
              ))}
              <button
                onClick={() => setPage(current + 1)}
                disabled={current === pages}
                aria-label="Next page"
                className="flex h-11 w-11 items-center justify-center rounded-full bg-canvas text-white transition-colors hover:bg-accent hover:text-onaccent disabled:opacity-40 disabled:hover:bg-canvas disabled:hover:text-white"
              >
                <ChevronRight className="h-5 w-5" />
              </button>
            </nav>
          )}
        </div>
      </section>

      <CTA />
    </>
  )
}
