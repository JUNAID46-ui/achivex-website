import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import Breadcrumb from './Breadcrumb'
import Img from './Img'

// Shared hero for inner pages (services, areas and new pages): breadcrumb, heading and
// text on the left, a large rounded image on the right. Without an image the text spans the row.
export default function PageHero({ crumbs, eyebrow, title, text, image, alt, action = { label: 'Book a free consultation', to: '/contact-us/' } }) {
  return (
    <section className="overflow-hidden bg-canvas pb-16 pt-8 md:pb-20 md:pt-10">
      <div className="container-site">
        {crumbs && <Breadcrumb items={crumbs} />}
        <div className={`mt-10 grid items-center gap-12 ${image ? 'lg:grid-cols-[1fr_1.05fr] lg:gap-14' : ''}`}>
          <div>
            {eyebrow && <p className="text-[13px] font-semibold uppercase tracking-[0.18em] text-navy">{eyebrow}</p>}
            <h1 className="mt-4 text-[40px] font-medium leading-[1.08] tracking-[-0.03em] text-[#111111] sm:text-[52px] lg:text-[60px]">{title}</h1>
            {text && <p className="mt-6 max-w-[520px] text-[17px] leading-[1.65] text-[#5B616B]">{text}</p>}
            {action && (
              <Link
                to={action.to}
                className="mt-8 inline-flex w-fit items-center gap-4 rounded-full bg-[#2B2927] py-1.5 pl-6 pr-1.5 text-[15px] text-[#FFFFFF] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#141414]"
              >
                {action.label}
                <span className="grid h-10 w-10 place-items-center rounded-full bg-[#FFFFFF] text-[#141414]">
                  <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
                </span>
              </Link>
            )}
          </div>
          {image && (
            <div>
              <div className="overflow-hidden rounded-[28px] bg-[#E9ECF0]">
                <Img priority sizes="(min-width: 1024px) 640px, 100vw" src={image} alt={alt || title} className="aspect-[5/4] w-full object-cover" />
              </div>
            </div>
          )}
        </div>
      </div>
    </section>
  )
}
