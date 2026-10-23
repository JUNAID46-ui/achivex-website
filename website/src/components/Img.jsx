import { useState } from 'react'

// Unsplash images get a srcset, so phones download a small copy instead of the 2000px original.
const WIDTHS = [480, 768, 1080, 1440, 2000]
function responsive(src) {
  if (!/^https:\/\/images\.unsplash\.com\//.test(src || '')) return {}
  const url = (w) => src.replace(/([?&])w=\d+/, `$1w=${w}`).replace(/^([^?]+)$/, `$1?w=${w}`)
  return { srcSet: WIDTHS.map((w) => `${url(w)} ${w}w`).join(', ') }
}

// Image with lazy loading, a neutral fallback if a URL fails, and explicit width and height so
// the browser can reserve space. `priority` is for the main image at the top of a page.
export default function Img({ src, alt, className = '', sizes = '100vw', width = 1600, height = 1000, priority = false, ...rest }) {
  const [failed, setFailed] = useState(false)
  if (!src || failed) {
    return (
      <div role="img" aria-label={alt} className={`bg-mist ${className}`} />
    )
  }
  return (
    <img
      src={src}
      {...responsive(src)}
      sizes={sizes}
      alt={alt}
      width={width}
      height={height}
      loading={priority ? 'eager' : 'lazy'}
      fetchPriority={priority ? 'high' : undefined}
      decoding="async"
      onError={() => setFailed(true)}
      className={className}
      {...rest}
    />
  )
}
