import FadeUp from '../FadeUp'
import Img from '../Img'
import Button from '../Button'

// General purpose sections for pages built in the admin Page Builder.
// Each receives `d`, the section data saved in the page_sections table.

const isExternal = (to) => /^https?:/.test(to || '')
const LinkButton = ({ text, to, variant }) =>
  text && to ? (isExternal(to) ? <Button href={to} variant={variant}>{text}</Button> : <Button to={to} variant={variant}>{text}</Button>) : null

const paragraphs = (v) => (Array.isArray(v) ? v : String(v || '').split(/\n{2,}/)).filter((p) => String(p).trim())

export function PageHero({ d }) {
  const dark = d.style === 'dark'
  return (
    <section className={dark ? 'bg-canvas text-white' : 'bg-canvas'}>
      <div className={`grid ${d.image ? 'lg:min-h-[560px] lg:grid-cols-2' : ''}`}>
        {d.image && (
          <div className="relative min-h-[320px] overflow-hidden lg:order-2">
            <Img src={d.image} alt={d.imageAlt || ''} className="absolute inset-0 h-full w-full object-cover" />
          </div>
        )}
        <FadeUp className={`${d.image ? 'px-5 py-14 sm:px-8 lg:py-20 lg:pl-[max(2.5rem,calc((100vw-1280px)/2+2.5rem))] lg:pr-16' : 'container-site py-20 md:py-28'} flex flex-col justify-center`}>
          <h1 className={`h-display text-5xl md:text-6xl ${dark ? 'text-white' : ''}`}>{d.heading}</h1>
          {d.text && <p className={`mt-8 max-w-2xl text-xl leading-relaxed ${dark ? 'text-white/75' : 'text-body/80'}`}>{d.text}</p>}
          {d.buttonText && <div className="mt-10"><LinkButton text={d.buttonText} to={d.buttonLink} variant={dark ? 'light' : 'primary'} /></div>}
        </FadeUp>
      </div>
    </section>
  )
}

export function RichText({ d }) {
  return (
    <section className={d.background === 'grey' ? 'bg-mist py-20 md:py-28' : 'bg-canvas py-20 md:py-28'}>
      <div className="container-site grid gap-12 lg:grid-cols-[1fr_1.5fr]">
        <FadeUp>
          {d.heading && <h2 className="h-display text-4xl md:text-5xl">{d.heading}</h2>}
          {d.heading && <span className="mt-6 block h-1 w-14 bg-accent" aria-hidden="true" />}
        </FadeUp>
        <FadeUp delay={0.1} className="space-y-6 text-[17px] leading-relaxed text-body/80">
          {paragraphs(d.paragraphs).map((p, i) => <p key={i} className="whitespace-pre-line">{p}</p>)}
          {d.buttonText && <div className="pt-2"><LinkButton text={d.buttonText} to={d.buttonLink} variant="outline" /></div>}
        </FadeUp>
      </div>
    </section>
  )
}

export function ImageText({ d }) {
  const right = d.imageSide === 'right'
  return (
    <section className={d.background === 'grey' ? 'bg-mist' : 'bg-canvas'}>
      <div className="grid lg:grid-cols-2">
        <div className={`relative min-h-[340px] overflow-hidden ${right ? 'lg:order-2' : ''}`}>
          <Img src={d.image} alt={d.imageAlt || ''} className="absolute inset-0 h-full w-full object-cover" />
        </div>
        <FadeUp className="px-5 py-16 sm:px-10 md:py-24 lg:px-16 xl:px-24">
          {d.heading && <h2 className="h-display text-4xl md:text-5xl">{d.heading}</h2>}
          <div className="mt-6 space-y-5 text-lg leading-relaxed text-body/80">
            {paragraphs(d.text).map((p, i) => <p key={i} className="whitespace-pre-line">{p}</p>)}
          </div>
          {d.buttonText && <div className="mt-8"><LinkButton text={d.buttonText} to={d.buttonLink} /></div>}
        </FadeUp>
      </div>
    </section>
  )
}

export function Cards({ d }) {
  const items = d.items || []
  return (
    <section className={d.background === 'grey' ? 'bg-mist py-20 md:py-28' : 'bg-canvas py-20 md:py-28'}>
      <div className="container-site">
        {(d.heading || d.text) && (
          <FadeUp className="max-w-3xl">
            {d.heading && <h2 className="h-display text-4xl md:text-5xl">{d.heading}</h2>}
            {d.text && <p className="mt-6 text-lg leading-relaxed text-body/80">{d.text}</p>}
          </FadeUp>
        )}
        <ul className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item, i) => (
            <FadeUp as="li" key={i} delay={(i % 3) * 0.06} className="border border-white/10 bg-canvas">
              {item.image && <Img src={item.image} alt={item.title || ''} className="aspect-[16/10] w-full object-cover" />}
              <div className="p-8">
                {!item.image && <span className="block h-1 w-8 bg-accent" aria-hidden="true" />}
                <h3 className={`${item.image ? '' : 'mt-6'} text-xl font-bold`}>{item.title}</h3>
                {item.text && <p className="mt-3 leading-relaxed text-body/80">{item.text}</p>}
              </div>
            </FadeUp>
          ))}
        </ul>
      </div>
    </section>
  )
}

export function Gallery({ d }) {
  const images = (d.images || []).filter(Boolean)
  return (
    <section className="bg-canvas py-20 md:py-28">
      <div className="container-site">
        {d.heading && <FadeUp><h2 className="h-display text-4xl md:text-5xl">{d.heading}</h2></FadeUp>}
        <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {images.map((src, i) => (
            <FadeUp key={src + i} delay={(i % 3) * 0.05} className="overflow-hidden">
              <Img src={src} alt={d.heading ? `${d.heading} ${i + 1}` : ''} className="aspect-[4/3] w-full object-cover transition-transform duration-700 hover:scale-105" />
            </FadeUp>
          ))}
        </div>
      </div>
    </section>
  )
}

export const genericLayouts = {
  page_hero: PageHero,
  rich_text: RichText,
  image_text: ImageText,
  cards: Cards,
  gallery: Gallery,
}
