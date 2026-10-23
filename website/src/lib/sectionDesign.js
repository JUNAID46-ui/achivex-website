// Turns the Design options saved for a Page Builder section into scoped CSS.
// Only values that pass these checks are used, so nothing unsafe reaches the page.
const COLOR = /^#[0-9a-fA-F]{3,8}$/
const FONTS = ['Geist', 'Manrope', 'Inter', 'Poppins', 'DM Sans', 'Montserrat', 'Playfair Display', 'Lora', 'Space Grotesk']
const SIZES = ['28', '36', '48', '60', '72']
const SPACING = { none: '0px', compact: '48px', spacious: '128px' }

const color = (v) => (COLOR.test(v || '') ? v : null)
const font = (v) => (FONTS.includes(v) ? v : null)
const imageUrl = (v) => (/^https:\/\/[^\s"'()<>]+$/.test(v || '') ? v : null)

// Loads a Google font once, the first time a section uses it.
function loadFont(name) {
  if (typeof document === 'undefined' || ['Geist', 'Manrope'].includes(name)) return
  const id = `font-${name.replace(/\s+/g, '-')}`
  if (document.getElementById(id)) return
  const link = document.createElement('link')
  link.id = id
  link.rel = 'stylesheet'
  link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(name)}:wght@400;500;600;700&display=swap`
  document.head.appendChild(link)
}

export function designCss(scope, d = {}) {
  const sel = `[data-sec="${scope}"] > *`
  return buildCss(d, { box: sel, light: sel, all: sel, heading: ':is(h1, h2)' })
}

// Design saved on a fixed page (Edit Page). Background and colours go to the light sections only,
// so black and blue blocks keep readable text; fonts, heading size and alignment apply to the page.
export function pageDesignCss(d = {}) {
  const light = '#main section:not(.bg-ink):not(.bg-navy):not(.cta-shine)'
  return buildCss({ ...d, spacing: '' }, { box: light, light, all: '#main', heading: 'h1', image: '#main > section:first-of-type' })
}

function buildCss(d, s) {
  const rules = []
  const sel = s.box
  const bg = color(d.bg)
  const img = imageUrl(d.bgImage)
  if (bg) rules.push(`${sel} { background-color: ${bg} !important; }`)
  if (img) rules.push(`${s.image || sel} { background-image: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)), url("${img}") !important; background-size: cover !important; background-position: center !important; }`)
  const heading = color(d.heading)
  if (heading) rules.push(`${s.light} :is(h1, h2, h3, h4) { color: ${heading} !important; }`)
  const text = color(d.text)
  if (text) rules.push(`${s.light} :is(p, li, span, blockquote) { color: ${text} !important; }`)
  const accent = color(d.accent)
  if (accent) rules.push(`${s.light} a:not([class*="bg-"]) { color: ${accent} !important; } ${s.light} :is(a, button)[class*="bg-"] { background-color: ${accent} !important; border-color: ${accent} !important; }`)
  const hf = font(d.headingFont)
  if (hf) { loadFont(hf); rules.push(`${s.all} :is(h1, h2, h3, h4) { font-family: "${hf}", sans-serif !important; }`) }
  const bf = font(d.bodyFont)
  if (bf) { loadFont(bf); rules.push(`${s.all} :is(p, li, span, a, button, blockquote) { font-family: "${bf}", sans-serif !important; }`) }
  if (SIZES.includes(String(d.headingSize))) {
    const px = Number(d.headingSize)
    rules.push(`${s.all} ${s.heading} { font-size: clamp(${Math.round(px * 0.7)}px, 6vw, ${px}px) !important; line-height: 1.1 !important; }`)
  }
  if (SPACING[d.spacing]) rules.push(`${sel} { padding-top: ${SPACING[d.spacing]} !important; padding-bottom: ${SPACING[d.spacing]} !important; }`)
  if (['left', 'center', 'right'].includes(d.align)) rules.push(`${s.all} :is(h1, h2, h3, h4, p) { text-align: ${d.align} !important; }`)
  return rules.join('\n')
}
