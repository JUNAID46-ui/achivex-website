import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import BlogCard from './BlogCard'
import { blogs } from '../data/blogs'
import { permalink } from '../lib/permalinks'

// Reading band placed between a closing call to action and the footer, so the black CTA and
// the black footer never touch. It shows the three newest articles. Layout adds it when needed.
export default function ClosingStrip() {
  const latest = [...blogs].sort((a, b) => String(b.date).localeCompare(String(a.date))).slice(0, 3)
  if (!latest.length) return null
  return (
    <section className="bg-[#EEF0F3] py-20 md:py-24">
      <div className="container-site">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div>
            <p className="text-[13px] font-semibold uppercase tracking-[0.16em] text-navy">From the blog</p>
            <h2 className="mt-3 text-[32px] font-medium leading-tight tracking-[-0.02em] text-[#111111] md:text-[40px]">Latest insights</h2>
          </div>
          <Link to={permalink('/insights/blogs/')} className="inline-flex items-center gap-1.5 text-[15px] font-semibold text-[#111111] hover:text-navy">
            View all articles <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
          </Link>
        </div>
        <div className="mt-10 grid gap-8 md:grid-cols-3">
          {latest.map((b) => <BlogCard key={b.slug} post={b} />)}
        </div>
      </div>
    </section>
  )
}
