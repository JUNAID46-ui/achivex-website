import { useState } from 'react'

// Image with lazy loading and a neutral fallback if a placeholder URL fails.
export default function Img({ src, alt, className = '', ...rest }) {
  const [failed, setFailed] = useState(false)
  if (!src || failed) {
    return (
      <div role="img" aria-label={alt} className={`bg-mist ${className}`} />
    )
  }
  return <img src={src} alt={alt} loading="lazy" decoding="async" onError={() => setFailed(true)} className={className} {...rest} />
}
