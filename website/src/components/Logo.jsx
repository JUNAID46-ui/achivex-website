import { Link } from 'react-router-dom'

// Brand logo. `light` uses the white version for dark backgrounds.
// `variant="full"` shows the stacked logo with the tagline (used in the footer).
export default function Logo({ light = true, variant = 'compact', onClick }) {
  const tone = light ? 'light' : 'dark'
  return (
    <Link to="/" onClick={onClick} className="flex shrink-0 items-center gap-3" aria-label="AchiveX LLC home">
      {variant === 'full' ? (
        <img src={`${import.meta.env.BASE_URL}brand/logo-${tone}.png`} alt="AchiveX LLC, Marketing and Software Solutions" className="h-28 w-auto" />
      ) : (
        <>
          <img src={`${import.meta.env.BASE_URL}brand/mark-${tone}.png`} alt="" className="h-8 w-auto" />
          <img src={`${import.meta.env.BASE_URL}brand/wordmark-${tone}.png`} alt="AchiveX LLC" className="hidden h-[18px] w-auto sm:block" />
        </>
      )}
    </Link>
  )
}
