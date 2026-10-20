import { Link } from 'react-router-dom'
import ArrowLink from '../ArrowLink'
import Img from '../Img'
import { categories } from '../../data/categories'
import { getService, servicePath, categoryPath } from '../../data/services'
import { servicesFeature } from '../../data/menus'

function CategoryBlock({ category, onNavigate }) {
  const to = category.single ? servicePath(getService(category.slug)) : categoryPath(category)
  return (
    <div className="mb-8">
      <Link to={to} onClick={onNavigate} className="text-lg font-bold text-body transition-colors hover:text-accent">
        {category.name}
      </Link>
      {!category.single && (
        <ul className="mt-3 space-y-2.5">
          {category.services.map((slug) => {
            const s = getService(slug)
            return (
              <li key={slug}>
                <ArrowLink to={servicePath(s)} onClick={onNavigate} className="text-[15px] text-body/75">{s.name}</ArrowLink>
              </li>
            )
          })}
        </ul>
      )}
    </div>
  )
}

export default function ServicesMenu({ onNavigate }) {
  const colA = categories.filter((c) => ['digital', 'data-and-analytics', 'cloud'].includes(c.slug))
  const colB = categories.filter((c) => !colA.includes(c))
  return (
    <div className="grid grid-cols-[minmax(260px,30%)_1fr]">
      <aside className="bg-cloud px-10 py-10">
        <p className="font-display text-3xl text-body">Services</p>
        <Link to={servicesFeature.to} onClick={onNavigate} className="group mt-6 block">
          <div className="overflow-hidden">
            <Img src={servicesFeature.image} alt="Featured article on data modernization" className="aspect-[16/9] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
          </div>
          <p className="mt-5 text-lg font-bold leading-snug text-body">{servicesFeature.title}</p>
          <span className="mt-4 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-body group-hover:text-accent">
            Learn More <span className="text-accent transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
          </span>
        </Link>
        <Link to="/services/" onClick={onNavigate} className="mt-8 block border-t border-white/15 pt-5 text-sm font-semibold text-body hover:text-accent">
          View all services
        </Link>
      </aside>
      <div className="grid grid-cols-2 gap-10 px-12 py-10">
        <div>{colA.map((c) => <CategoryBlock key={c.slug} category={c} onNavigate={onNavigate} />)}</div>
        <div>{colB.map((c) => <CategoryBlock key={c.slug} category={c} onNavigate={onNavigate} />)}</div>
      </div>
    </div>
  )
}
