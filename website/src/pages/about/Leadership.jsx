import { Users, MessageSquare, Layers } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Breadcrumb from '../../components/Breadcrumb'
import Img from '../../components/Img'
import CTA from '../../components/CTA'
import { SocialIcon } from '../../components/SocialIcons'
import { leaders } from '../../data/about'
import { pageContent } from '../../data/pageContent'

const icons = [MessageSquare, Layers, Users]

export default function Leadership() {
  useSeo('Our Leadership', 'The leadership team at AchiveX LLC and how they work with clients.')
  const c = pageContent['/about-us/leadership/']
  const principles = (c.principles || []).map((p, i) => ({ ...p, icon: icons[i % icons.length] }))
  return (
    <>
      <section className="bg-mist pb-16 pt-12 md:pb-20">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'About', to: '/about-us/' }, { label: 'Our Leadership' }]} />
          <div className="mt-14 flex flex-col justify-between gap-8 md:flex-row md:items-end">
            <FadeUp><h1 className="h-display text-5xl md:text-7xl">{c.heroTitle}</h1></FadeUp>
            <FadeUp delay={0.1}><p className="max-w-md text-lg leading-relaxed text-body/70">{c.heroText}</p></FadeUp>
          </div>
        </div>
      </section>

      {leaders.length > 0 && (
        <section className="bg-canvas py-20 md:py-28">
          <div className="container-site">
            <ul className="grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
              {leaders.map((p, i) => (
                <FadeUp as="li" key={p.name} delay={(i % 3) * 0.06} className="group">
                  <div className="overflow-hidden bg-mist">
                    <Img src={p.photo} alt={`Portrait of ${p.name}`} className="aspect-[4/5] w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
                  </div>
                  <div className="mt-6 flex items-start justify-between gap-4">
                    <div>
                      <h2 className="text-xl font-semibold">{p.name}</h2>
                      <p className="mt-1 text-body/60">{p.role}</p>
                    </div>
                    {p.linkedin && (
                      <a href={p.linkedin} target="_blank" rel="noopener noreferrer" aria-label={`${p.name} on LinkedIn`} className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy text-white transition-colors hover:bg-accent hover:text-onaccent">
                        <SocialIcon name="LinkedIn" />
                      </a>
                    )}
                  </div>
                </FadeUp>
              ))}
            </ul>
          </div>
        </section>
      )}

      <section className="bg-canvas py-20 md:py-28">
        <div className="container-site">
          <FadeUp><h2 className="h-display max-w-2xl text-4xl md:text-5xl">{c.principlesTitle}</h2></FadeUp>
          <div className="mt-14 grid gap-10 md:grid-cols-3">
            {principles.map(({ icon: Icon, title, text }, i) => (
              <FadeUp key={title} delay={i * 0.06} className="border-t border-body/15 pt-6">
                <Icon className="h-6 w-6 text-accent" aria-hidden="true" />
                <h3 className="mt-5 text-xl font-semibold">{title}</h3>
                <p className="mt-3 leading-relaxed text-body/60">{text}</p>
              </FadeUp>
            ))}
          </div>
        </div>
      </section>

      <CTA variant="split" title={c.ctaTitle} text={c.ctaText} />
    </>
  )
}
