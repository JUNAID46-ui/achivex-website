import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import Img from './Img'

// Used for case studies (and mixed insight lists on the home page).
export default function InsightCard({ to, image, kicker, title, text, cta = 'Read Case Study' }) {
  return (
    <article className="group flex h-full flex-col">
      <Link to={to} className="block overflow-hidden">
        <Img src={image} alt={title} className="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
      </Link>
      <div className="flex flex-1 flex-col pt-5">
        <h3 className="text-xl font-bold leading-snug text-white">
          <Link to={to} className="transition-colors hover:text-accent">{title}</Link>
        </h3>
        <p className="mt-2 text-sm font-semibold text-accent">{kicker}</p>
        {text && <p className="mt-3 flex-1 text-[15px] leading-relaxed text-body/80">{text}</p>}
        <Link to={to} className="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-white transition-colors hover:text-accent">
          {cta}
          <ArrowRight className="h-4 w-4 text-accent transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
        </Link>
      </div>
    </article>
  )
}
