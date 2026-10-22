import { useMemo, useState } from 'react'
import { ChevronDown } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Breadcrumb from '../../components/Breadcrumb'
import InsightCard from '../../components/InsightCard'
import CTA from '../../components/CTA'
import { caseStudies } from '../../data/caseStudies'
import { getService } from '../../data/services'
import { industries } from '../../data/industries'
import { pageContent } from '../../data/pageContent'
import { permalink } from '../../lib/permalinks'

function Select({ id, label, value, onChange, options }) {
  return (
    <div className="relative">
      <label htmlFor={id} className="sr-only">{label}</label>
      <select
        id={id}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="w-full appearance-none border border-white/30 bg-navy py-3 pl-5 pr-12 text-[15px] font-semibold text-white outline-none transition-colors hover:border-accent focus:border-accent sm:w-72"
      >
        <option value="">{label}</option>
        {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
      </select>
      <ChevronDown className="pointer-events-none absolute right-5 top-1/2 h-4 w-4 -translate-y-1/2 text-accent" aria-hidden="true" />
    </div>
  )
}

export default function CaseStudies() {
  useSeo('Case Studies', 'Examples of how AchiveX approaches client challenges across digital, data, cloud, security and business process services.')
  const pc = pageContent['/insights/case-studies/']
  const [service, setService] = useState('')
  const [industry, setIndustry] = useState('')

  const serviceOptions = useMemo(() => {
    const slugs = [...new Set(caseStudies.flatMap((c) => c.services))]
    return slugs.map((s) => ({ value: s, label: getService(s)?.name || s })).sort((a, b) => a.label.localeCompare(b.label))
  }, [])
  const industryOptions = industries
    .filter((i) => caseStudies.some((c) => c.industry === i.slug))
    .map((i) => ({ value: i.slug, label: i.name }))

  const list = caseStudies.filter((c) => (!service || c.services.includes(service)) && (!industry || c.industry === industry))

  return (
    <>
      <section className="bg-navy pb-14 pt-12 text-white">
        <div className="container-site">
          <Breadcrumb light items={[{ label: 'Insights' }, { label: 'Case Studies' }]} />
          <div className="mt-14 grid gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-end">
            <FadeUp><h1 className="h-display text-5xl text-white md:text-7xl">{pc.heroTitle}</h1></FadeUp>
            <FadeUp delay={0.1}>
              <p className="text-lg leading-relaxed text-white/80">
                {pc.heroText}
              </p>
            </FadeUp>
          </div>
          <div className="mt-12 flex flex-col gap-3 border-t border-white/15 pt-8 sm:flex-row">
            <Select id="f-service" label={pc.allServicesLabel} value={service} onChange={setService} options={serviceOptions} />
            <Select id="f-industry" label={pc.allIndustriesLabel} value={industry} onChange={setIndustry} options={industryOptions} />
            {(service || industry) && (
              <button onClick={() => { setService(''); setIndustry('') }} className="px-4 text-sm font-semibold text-white/80 underline decoration-accent underline-offset-4 hover:text-accent">
                {pc.clearFiltersText}
              </button>
            )}
          </div>
        </div>
      </section>

      <section className="bg-canvas py-16 md:py-24">
        <div className="container-site">
          <p className="mb-12 max-w-3xl border-l-2 border-accent pl-4 text-[15px] text-body/65">
            {pc.note}
          </p>
          {list.length === 0 ? (
            <p className="py-16 text-center text-lg text-body/60">{pc.emptyText}</p>
          ) : (
            <div className="grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
              {list.map((c, i) => (
                <FadeUp key={c.slug} delay={(i % 3) * 0.06}>
                  <InsightCard
                    to={permalink(`/insights/case-studies/${c.slug}/`)}
                    image={c.image.replace('w=2000', 'w=900')}
                    kicker={c.clientType}
                    title={c.title}
                    text={c.summary}
                  />
                </FadeUp>
              ))}
            </div>
          )}
        </div>
      </section>

      <CTA />
    </>
  )
}
