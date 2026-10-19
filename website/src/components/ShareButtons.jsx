import { useState } from 'react'
import { Link2, Check } from 'lucide-react'
import { SocialIcon } from './SocialIcons'

export default function ShareButtons({ title, vertical = false }) {
  const [copied, setCopied] = useState(false)
  const url = typeof window !== 'undefined' ? window.location.href : ''
  const enc = encodeURIComponent
  const links = [
    { name: 'LinkedIn', href: `https://www.linkedin.com/sharing/share-offsite/?url=${enc(url)}` },
    { name: 'X', href: `https://x.com/intent/post?url=${enc(url)}&text=${enc(title)}` },
    { name: 'Facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${enc(url)}` },
  ]
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(url)
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    } catch {
      /* clipboard not available */
    }
  }
  const btn = 'flex h-10 w-10 items-center justify-center rounded-full border border-navy/20 text-navy transition-colors hover:border-accent hover:bg-accent hover:text-ink'
  return (
    <div className={`flex items-center gap-2 ${vertical ? 'lg:flex-col' : ''}`}>
      <span className="mr-1 text-sm font-semibold text-navy">Share</span>
      {links.map((l) => (
        <a key={l.name} href={l.href} target="_blank" rel="noopener noreferrer" aria-label={`Share on ${l.name}`} className={btn}>
          <SocialIcon name={l.name} />
        </a>
      ))}
      <button onClick={copy} aria-label="Copy link" className={btn}>
        {copied ? <Check className="h-4 w-4" /> : <Link2 className="h-4 w-4" />}
      </button>
    </div>
  )
}
