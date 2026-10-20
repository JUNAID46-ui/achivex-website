import ArrowLink from '../ArrowLink'
import { industries } from '../../data/industries'

export default function IndustriesMenu({ onNavigate }) {
  return (
    <ul className="w-72 space-y-1 p-4">
      {industries.map((i) => (
        <li key={i.slug}>
          <ArrowLink to={`/industries/${i.slug}/`} onClick={onNavigate} className="w-full justify-between px-3 py-2.5 text-[15px] text-body hover:bg-mist">
            {i.name}
          </ArrowLink>
        </li>
      ))}
    </ul>
  )
}
