import { useEffect, useMemo, useState } from 'react'
import Breadcrumb from './Breadcrumb'
import jumpTo from '../utils/jumpTo'

// Shared layout for Privacy Policy and Terms of Service: sticky side table of contents.
const slug = (t) => String(t || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')

export default function LegalPage({ title, updated, intro, sections: raw }) {
  // Sections added in the admin may have no id, so one is made from the title for the anchor links.
  const sections = useMemo(() => raw.map((s, i) => ({ ...s, id: s.id || slug(s.title) || `section-${i + 1}` })), [raw])
  const [active, setActive] = useState(sections[0]?.id)
  useEffect(() => {
    const obs = new IntersectionObserver(
      (entries) => entries.forEach((e) => e.isIntersecting && setActive(e.target.id)),
      { rootMargin: '-25% 0px -65% 0px' },
    )
    sections.forEach((s) => {
      const el = document.getElementById(s.id)
      if (el) obs.observe(el)
    })
    return () => obs.disconnect()
  }, [sections])

  return (
    <>
      <section className="border-b border-mist bg-mist pb-14 pt-12">
        <div className="container-site">
          <Breadcrumb items={[{ label: title }]} />
          <h1 className="h-display mt-10 text-5xl md:text-7xl">{title}</h1>
          <p className="mt-4 text-sm text-ink/60">Last updated: {updated}</p>
        </div>
      </section>
      <section className="bg-white py-16 md:py-20">
        <div className="container-site grid gap-12 lg:grid-cols-[260px_minmax(0,760px)]">
          <aside className="lg:sticky lg:top-28 lg:self-start">
            <nav aria-label="On this page">
              <p className="text-sm font-bold text-navy">On this page</p>
              <ol className="mt-4 space-y-1 text-sm">
                {sections.map((s, i) => (
                  <li key={s.id}>
                    <a
                      href={`#${s.id}`} onClick={(e) => jumpTo(e, s.id)}
                      className={`flex gap-3 px-3 py-2 transition-colors hover:text-accent ${active === s.id ? 'bg-mist font-semibold text-navy' : 'text-ink/60'}`}
                    >
                      <span className={active === s.id ? 'text-accent' : ''}>{i + 1}.</span>{s.title}
                    </a>
                  </li>
                ))}
              </ol>
            </nav>
          </aside>
          <div className="prose-body">
            <p className="!text-lg">{intro}</p>
            {sections.map((s, i) => (
              <section key={s.id}>
                <h2 id={s.id}>{i + 1}. {s.title}</h2>
                {(s.paragraphs || []).map((p, j) => <p key={j}>{p}</p>)}
                {s.bullets?.length > 0 && <ul>{s.bullets.map((li) => <li key={li}>{li}</li>)}</ul>}
              </section>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
