// Applies the colours and fonts saved in the admin (Settings, Colors and Fonts) before the first render.
// Only #RRGGBB colours and fonts from a fixed list are used, so saved values cannot inject CSS.

// Google Fonts the admin offers, with the weights each one has.
export const FONTS = {
  Manrope: '400;500;600;700;800',
  Geist: '400;500;600;700',
  Inter: '400;500;600;700;800',
  'DM Sans': '400;500;600;700',
  'Plus Jakarta Sans': '400;500;600;700;800',
  Montserrat: '400;500;600;700;800',
  Poppins: '400;500;600;700;800',
  Raleway: '400;500;600;700;800',
  'Work Sans': '400;500;600;700;800',
  Outfit: '400;500;600;700;800',
  Sora: '400;500;600;700;800',
  'Space Grotesk': '400;500;600;700',
  'IBM Plex Sans': '400;500;600;700',
  'Source Sans 3': '400;500;600;700;800',
  'Open Sans': '400;500;600;700;800',
  Roboto: '400;500;700',
  Lato: '400;700',
  'Playfair Display': '400;500;600;700;800',
  Lora: '400;500;600;700',
  Merriweather: '400;700',
}
const PRELOADED = ['Manrope', 'Geist'] // loaded by index.html

const hex = (v) => (typeof v === 'string' && /^#[0-9a-fA-F]{6}$/.test(v) ? v : null)
const rgb = (v) => [1, 3, 5].map((i) => parseInt(v.slice(i, i + 2), 16)).join(' ')

// The CSS for the saved colours and fonts, and any extra Google fonts to load. Used on the server
// (rendered into the page head) and in the browser (applyTheme), so both agree.
export function themeCss(theme) {
  const c = theme?.colors || {}
  const f = theme?.fonts || {}
  const vars = (pairs) => pairs.filter(([, v]) => hex(v)).map(([k, v]) => `${k}: ${rgb(v)};`).join(' ')
  const root = vars([['--c-navy', c.brandBlue], ['--c-mist', c.lightBg], ['--c-ink', c.heroCard], ['--c-cloud', c.panel]])
  const dark = vars([['--c-fg', c.darkHeading], ['--c-body', c.darkText], ['--c-canvas', c.darkBg], ['--c-accent', c.accent], ['--c-on-accent', c.onAccent]])
  const light = vars([['--c-fg', c.lightHeading], ['--c-body', c.lightText], ['--c-canvas', c.lightCards], ['--c-accent', c.lightAccent], ['--c-on-accent', c.lightOnAccent]])
  const body = FONTS[f.body] ? f.body : null
  const heading = FONTS[f.heading] ? f.heading : null
  const fonts = [
    body && `--font-sans: '${body}', system-ui, sans-serif;`,
    heading && `--font-display: '${heading}', system-ui, sans-serif;`,
  ].filter(Boolean).join(' ')

  const css = [
    (root || fonts) && `:root { ${root} ${fonts} }`,
    dark && `:root, [class~='bg-navy'], [class~='bg-ink'] { ${dark} }`,
    light && `[class~='bg-mist'] { ${light} }`,
  ].filter(Boolean).join('\n')
  return { css, load: [...new Set([body, heading])].filter((name) => name && !PRELOADED.includes(name)) }
}

export function applyTheme(theme) {
  const { css, load } = themeCss(theme)
  if (css) {
    const style = document.createElement('style')
    style.id = 'site-theme'
    style.textContent = css
    document.head.appendChild(style)
  }

  if (load.length) {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = `https://fonts.googleapis.com/css2?${load.map((n) => `family=${n.replace(/ /g, '+')}:wght@${FONTS[n]}`).join('&')}&display=swap`
    document.head.appendChild(link)
  }
}
