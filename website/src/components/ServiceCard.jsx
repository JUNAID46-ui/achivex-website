import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import Img from './Img'
import { servicePath } from '../data/services'

export default function ServiceCard({ service }) {
  return (
    <Link to={servicePath(service)} className="group flex h-full flex-col border-t border-body/15 pt-6">
      <div className="overflow-hidden">
        <Img src={service.heroImage.replace('w=2000', 'w=900')} alt={service.name} className="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
      </div>
      <div className="mt-6 flex items-start justify-between gap-4">
        <h3 className="text-lg font-semibold leading-snug text-white transition-colors group-hover:text-accent">{service.name}</h3>
        <ArrowUpRight className="mt-1 h-5 w-5 shrink-0 text-accent transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" aria-hidden="true" />
      </div>
      <p className="mt-2 text-[15px] leading-relaxed text-body/80">{service.short}</p>
    </Link>
  )
}
