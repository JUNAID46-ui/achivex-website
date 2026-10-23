import { Link } from 'react-router-dom'
import { isLight } from '../theme'

// Brand logo. `light` uses the white version for dark backgrounds.
// `variant="full"` shows the stacked logo with the tagline (used in the footer).
export default function Logo({ light = !isLight, variant = 'compact', onClick }) {
  const tone = light ? 'light' : 'dark'
  return (
    <Link to="/" onClick={onClick} className="flex shrink-0 items-center gap-3" aria-label="AchiveX LLC home">
      {variant === 'full' ? (
        <img src={`/brand/logo-${tone}.png`} alt="AchiveX LLC, Marketing and Software Solutions" width="175" height="112" className="h-28 w-auto" />
      ) : (
        <>
          <img src={`/brand/mark-${tone}.png`} alt="" width="66" height="32" className="h-8 w-auto" />
          <img src={`/brand/wordmark-${tone}.png`} alt="AchiveX LLC" width="119" height="18" className="hidden h-[18px] w-auto sm:block" />
        </>
      )}
    </Link>
  )
}
