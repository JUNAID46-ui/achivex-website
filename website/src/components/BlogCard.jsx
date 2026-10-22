import { Link } from 'react-router-dom'
import Img from './Img'
import { formatDate } from '../utils/format'
import { readTime } from '../data/blogs'
import { permalink } from '../lib/permalinks'

export default function BlogCard({ post }) {
  const to = permalink(`/insights/blogs/${post.slug}/`)
  return (
    <article className="group flex h-full flex-col">
      <Link to={to} className="block overflow-hidden">
        <Img src={post.image} alt={post.title} className="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
      </Link>
      <div className="flex flex-1 flex-col pt-5">
        <h3 className="text-lg font-bold leading-snug text-white">
          <Link to={to} className="transition-colors hover:text-accent">{post.title}</Link>
        </h3>
        <div className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] text-body/60">
          <span className="font-semibold text-accent">{post.category}</span>
          <span aria-hidden="true">/</span>
          <time dateTime={post.date}>{formatDate(post.date)}</time>
          <span aria-hidden="true">/</span>
          <span>{readTime(post)} min read</span>
        </div>
        <p className="mt-3 text-[15px] leading-relaxed text-body/70">{post.excerpt}</p>
      </div>
    </article>
  )
}
