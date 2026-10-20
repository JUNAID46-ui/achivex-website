import { Link } from 'react-router-dom'
import ArrowLink from '../ArrowLink'
import { aboutLinks } from '../../data/about'
import { alliances } from '../../data/alliances'

export default function AboutMenu({ onNavigate }) {
  return (
    <div className="mx-auto grid max-w-site grid-cols-2 gap-16 px-10 py-12 xl:px-24">
      <ul className="space-y-6">
        {aboutLinks.map((l) => (
          <li key={l.to}>
            <Link to={l.to} onClick={onNavigate} className="text-lg font-semibold text-body transition-colors hover:text-accent">{l.label}</Link>
          </li>
        ))}
      </ul>
      <div>
        <p className="text-lg font-semibold text-body">Alliances</p>
        <ul className="mt-4 space-y-3">
          {alliances.map((a) => (
            <li key={a.slug}>
              <ArrowLink to={`/about-us/alliances/${a.slug}/`} onClick={onNavigate} className="text-[16px] text-body">{a.name}</ArrowLink>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}
