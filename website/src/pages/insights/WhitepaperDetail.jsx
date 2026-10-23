import { useState } from 'react'
import { useParams } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Check, CheckCircle2 } from 'lucide-react'
import useSeo from '../../hooks/useSeo'
import FadeUp from '../../components/FadeUp'
import Breadcrumb from '../../components/Breadcrumb'
import Button from '../../components/Button'
import ArrowLink from '../../components/ArrowLink'
import NotFound from '../NotFound'
import { BookCover } from './Whitepapers'
import { whitepapers, getWhitepaper } from '../../data/whitepapers'
import { requestWhitepaper } from '../../lib/content'
import { site } from '../../data/site'
import { permalink } from '../../lib/permalinks'

const fields = [
  { name: 'name', label: 'Full name', type: 'text', auto: 'name' },
  { name: 'email', label: 'Work email', type: 'email', auto: 'email' },
  { name: 'company', label: 'Company', type: 'text', auto: 'organization' },
  { name: 'title', label: 'Job title', type: 'text', auto: 'organization-title' },
]

export default function WhitepaperDetail() {
  const { slug } = useParams()
  const paper = getWhitepaper(slug)
  const [form, setForm] = useState({ name: '', email: '', company: '', title: '' })
  const [sent, setSent] = useState(false)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  useSeo(paper?.title, paper?.description)
  if (!paper) return <NotFound />

  // Requests are saved to the whitepaper_requests table in the database.
  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      await requestWhitepaper(paper.slug, form)
      setSent(true)
    } catch {
      setError(`Your request could not be sent. Please try again, or email us at ${site.email}.`)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <section className="bg-canvas pb-24 pt-12">
        <div className="container-site">
          <Breadcrumb items={[{ label: 'Insights' }, { label: 'Whitepapers and eBooks', to: permalink('/insights/whitepapers/') }, { label: paper.title }]} />
          <div className="mt-12 grid gap-14 lg:grid-cols-2 lg:gap-20">
            <FadeUp>
              <div className="flex flex-col gap-10 sm:flex-row lg:flex-col xl:flex-row">
                <BookCover paper={paper} className="aspect-[3/4] w-56 shrink-0" />
                <div>
                  <h1 className="h-display text-4xl md:text-5xl">{paper.title}</h1>
                  <p className="mt-5 text-lg leading-relaxed text-body/80">{paper.description}</p>
                </div>
              </div>
              <div className="mt-12 bg-mist p-8">
                <h2 className="text-xl font-bold">What is inside</h2>
                <ul className="mt-5 space-y-3">
                  {paper.inside.map((i) => (
                    <li key={i} className="flex gap-3 text-body/80">
                      <Check className="mt-0.5 h-5 w-5 shrink-0 text-accent" aria-hidden="true" />{i}
                    </li>
                  ))}
                </ul>
              </div>
            </FadeUp>

            <FadeUp delay={0.1}>
              <div className="bg-navy p-8 text-white md:p-12 lg:sticky lg:top-28">
                {sent ? (
                  <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} role="status">
                    <CheckCircle2 className="h-12 w-12 text-accent" aria-hidden="true" />
                    <h2 className="mt-6 font-display text-4xl text-white">Thank you, {form.name.split(' ')[0]}.</h2>
                    <p className="mt-4 text-lg leading-relaxed text-white/80">
                      Your request for {paper.title} has been received. We will send it to {form.email}.
                    </p>
                    {paper.file && (
                      <a href={paper.file} download className="mt-8 inline-flex bg-accent px-6 py-3 font-semibold text-onaccent hover:bg-white hover:text-onaccent">
                        Download now
                      </a>
                    )}
                  </motion.div>
                ) : (
                  <form onSubmit={submit} noValidate={false}>
                    <h2 className="font-display text-4xl text-white">Get your free copy</h2>
                    <p className="mt-3 text-white/75">Fill in your details and we will send the document to your inbox.</p>
                    <div className="mt-8 space-y-5">
                      {fields.map((f) => (
                        <div key={f.name}>
                          <label htmlFor={`wp-${f.name}`} className="mb-2 block text-sm font-semibold text-white">{f.label}</label>
                          <input
                            id={`wp-${f.name}`}
                            type={f.type}
                            required
                            autoComplete={f.auto}
                            value={form[f.name]}
                            onChange={(e) => setForm({ ...form, [f.name]: e.target.value })}
                            className="field"
                          />
                        </div>
                      ))}
                    </div>
                    <p className="mt-5 text-xs leading-relaxed text-white/60">
                      By submitting, you agree to our handling of your details as described in the Privacy Policy.
                    </p>
                    {error && <p role="alert" className="mt-5 border-l-2 border-accent bg-white/10 px-4 py-3 text-sm text-white">{error}</p>}
                    <Button type="submit" variant="light" className="mt-6" disabled={sending}>{sending ? 'Sending...' : 'Download'}</Button>
                  </form>
                )}
              </div>
            </FadeUp>
          </div>
        </div>
      </section>
      <section className="bg-mist py-14">
        <div className="container-site flex flex-wrap items-center gap-x-8 gap-y-3">
          <span className="font-semibold text-white">More guides:</span>
          {whitepapers.filter((w) => w.slug !== slug).map((w) => (
            <ArrowLink key={w.slug} to={permalink(`/insights/whitepapers/${w.slug}/`)} className="text-white">{w.title}</ArrowLink>
          ))}
        </div>
      </section>
    </>
  )
}
