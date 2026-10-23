import { Link } from 'react-router-dom'
import { services, servicePath } from '../../data/services'

// Services dropdown: a flat full width panel with each service and a one line description.
export default function ServicesMenu({ onNavigate }) {
  return (
    <ul className="container-site grid grid-cols-4 gap-x-10 gap-y-8 py-10">
      {services.map((s) => (
        <li key={s.slug}>
          <Link to={servicePath(s)} onClick={onNavigate} className="group block">
            <span className="block text-[15px] font-semibold text-white transition-colors group-hover:text-navy">{s.name}</span>
            <span className="mt-1.5 block text-[13.5px] leading-relaxed text-body/80">{s.short}</span>
          </Link>
        </li>
      ))}
    </ul>
  )
}
