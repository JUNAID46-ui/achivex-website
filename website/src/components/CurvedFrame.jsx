import { useId } from 'react'

// The brand image frame: rounded corners with a smooth S shaped cut out on the lower left,
// first used in the home hero. Drawn on a 550 x 515 grid and scaled to the box with
// objectBoundingBox, so the curve keeps its shape at every width.
const FRAME_PATH =
  'M36 0H514A36 36 0 0 1 550 36V479A36 36 0 0 1 514 515H120A36 36 0 0 1 84 479V395C84 320 0 330 0 255V36A36 36 0 0 1 36 0Z'

export default function CurvedFrame({ className = '', children }) {
  const id = `frame-${useId().replace(/:/g, '')}`
  return (
    <>
      <svg width="0" height="0" className="absolute" aria-hidden="true">
        <clipPath id={id} clipPathUnits="objectBoundingBox" transform="scale(0.0018182 0.0019417)">
          <path d={FRAME_PATH} />
        </clipPath>
      </svg>
      <div className={`relative aspect-[550/515] w-full bg-[#E9ECF0] ${className}`} style={{ clipPath: `url(#${id})` }}>
        {children}
      </div>
    </>
  )
}
