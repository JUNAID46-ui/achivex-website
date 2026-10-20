import { useState } from 'react'
import { motion } from 'framer-motion'
import { Phone, Mail, MapPin, CheckCircle2, ChevronDown, ExternalLink } from 'lucide-react'
import useSeo from '../hooks/useSeo'
import FadeUp from '../components/FadeUp'
import Breadcrumb from '../components/Breadcrumb'
import Button from '../components/Button'
import { site } from '../data/site'
import { categories } from '../data/categories'
import { getService } from '../data/services'
import { sendContactMessage } from '../lib/content'
import { pageContent } from '../data/pageContent'
import { safeHref } from '../utils/safeHref'

export default function Contact() {
  useSeo('Contact Us', 'Contact AchiveX LLC by phone at +1 307 622 1140, by email at info@achivex.com, or send a message using our contact form.')
  const c = pageContent['/contact-us/']
  const [form, setForm] = useState({ name: '', email: '', phone: '', service: '', message: '' })
  const [sent, setSent] = useState(false)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value })

  // Messages are saved to the contact_submissions table in the database.
  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      await sendContactMessage(form)
      setSent(true)
    } catch {
      setError(`Your message could not be sent. Please try again, or email us at ${site.email}.`)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <section className="bg-canvas pb-16 pt-12 text-white md:pb-20">
        <div className="container-site">
          <Breadcrumb light items={[{ label: 'Contact us' }]} />
          <FadeUp className="mt-14 grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-end">
            <h1 className="h-display text-5xl text-white md:text-7xl">{c.heroTitle}</h1>
            <p className="text-lg leading-relaxed text-white/75">{c.heroText}</p>
          </FadeUp>
        </div>
      </section>

      <section className="bg-mist py-16 md:py-24">
        <div className="container-site grid gap-8 lg:grid-cols-[1.4fr_1fr]">
          <FadeUp className="bg-canvas p-8 md:p-12">
            {sent ? (
              <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} role="status" className="py-10">
                <CheckCircle2 className="h-12 w-12 text-accent" aria-hidden="true" />
                <h2 className="mt-6 font-display text-4xl">Thanks, {form.name.split(' ')[0]}.</h2>
                <p className="mt-4 max-w-md text-lg leading-relaxed text-body/70">
                  {c.thanksText} {form.email}.
                </p>
              </motion.div>
            ) : (
              <form onSubmit={submit}>
                <h2 className="font-display text-4xl">{c.formTitle}</h2>
                <div className="mt-8 grid gap-5 sm:grid-cols-2">
                  <div>
                    <label htmlFor="c-name" className="label">Name</label>
                    <input id="c-name" required autoComplete="name" value={form.name} onChange={set('name')} className="field" />
                  </div>
                  <div>
                    <label htmlFor="c-email" className="label">Email</label>
                    <input id="c-email" type="email" required autoComplete="email" value={form.email} onChange={set('email')} className="field" />
                  </div>
                  <div>
                    <label htmlFor="c-phone" className="label">Phone</label>
                    <input id="c-phone" type="tel" autoComplete="tel" value={form.phone} onChange={set('phone')} className="field" />
                  </div>
                  <div className="relative">
                    <label htmlFor="c-service" className="label">Service</label>
                    <select id="c-service" value={form.service} onChange={set('service')} className="field appearance-none pr-10">
                      <option value="">Select a service</option>
                      {categories.map((c) =>
                        c.single ? (
                          <option key={c.slug} value={c.slug}>{c.name}</option>
                        ) : (
                          <optgroup key={c.slug} label={c.name}>
                            {c.services.map((s) => <option key={s} value={s}>{getService(s).name}</option>)}
                          </optgroup>
                        ),
                      )}
                      <option value="other">Something else</option>
                    </select>
                    <ChevronDown className="pointer-events-none absolute bottom-3.5 right-4 h-4 w-4 text-accent" aria-hidden="true" />
                  </div>
                  <div className="sm:col-span-2">
                    <label htmlFor="c-message" className="label">Message</label>
                    <textarea id="c-message" required rows={6} value={form.message} onChange={set('message')} className="field resize-y" />
                  </div>
                </div>
                {error && <p role="alert" className="mt-6 border-l-2 border-accent bg-mist px-4 py-3 text-sm text-body">{error}</p>}
                <Button type="submit" className="mt-8" disabled={sending}>{sending ? 'Sending...' : c.formButton}</Button>
              </form>
            )}
          </FadeUp>

          <FadeUp delay={0.1} className="flex flex-col gap-8">
            <div className="bg-navy p-8 text-white md:p-10">
              <h2 className="font-display text-3xl text-white">{c.detailsTitle}</h2>
              <ul className="mt-8 space-y-6">
                <li>
                  <a href={site.phoneHref} className="group flex items-start gap-4">
                    <span className="flex h-11 w-11 shrink-0 items-center justify-center bg-white/10 text-accent"><Phone className="h-5 w-5" aria-hidden="true" /></span>
                    <span><span className="block text-sm text-white/60">Phone</span><span className="text-lg font-semibold group-hover:text-accent">{site.phone}</span></span>
                  </a>
                </li>
                <li>
                  <a href={`mailto:${site.email}`} className="group flex items-start gap-4">
                    <span className="flex h-11 w-11 shrink-0 items-center justify-center bg-white/10 text-accent"><Mail className="h-5 w-5" aria-hidden="true" /></span>
                    <span><span className="block text-sm text-white/60">Email</span><span className="text-lg font-semibold group-hover:text-accent">{site.email}</span></span>
                  </a>
                </li>
                <li className="flex items-start gap-4">
                  <span className="flex h-11 w-11 shrink-0 items-center justify-center bg-white/10 text-accent"><MapPin className="h-5 w-5" aria-hidden="true" /></span>
                  <span><span className="block text-sm text-white/60">Location</span><span className="text-lg font-semibold">{site.location}</span></span>
                </li>
              </ul>
              <a
                href={safeHref(site.googleProfile)}
                target="_blank"
                rel="noopener noreferrer"
                className="mt-10 inline-flex items-center gap-2 bg-accent px-6 py-3 text-[15px] font-semibold text-onaccent transition-colors hover:bg-white hover:text-onaccent"
              >
                {c.googleButton} <ExternalLink className="h-4 w-4" aria-hidden="true" />
              </a>
            </div>
            <div className="min-h-[300px] flex-1 overflow-hidden">
              <iframe title="Map of Wyoming, USA" src={site.mapEmbed} className="h-full min-h-[300px] w-full" loading="lazy" referrerPolicy="no-referrer-when-downgrade" />
            </div>
          </FadeUp>
        </div>
      </section>
    </>
  )
}
