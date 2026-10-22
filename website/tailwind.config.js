/** @type {import('tailwindcss').Config} */
// The palette is locked to the brand colors: #0B0D12 black (about 40%), #FFFFFF white sections (25%),
// #0F2C9C blue sections (20%), #C9CED6 silver text and cards (10%) and #B6F500 green for CTAs, numbers and icons (5%).
// Tailwind's default colors are intentionally replaced, not extended.
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      // Roles read CSS variables, so white sections (bg-mist) can flip text to dark. See index.css.
      white: 'rgb(var(--c-fg) / <alpha-value>)',
      body: 'rgb(var(--c-body) / <alpha-value>)',
      slate: 'rgb(var(--c-body) / <alpha-value>)',
      canvas: 'rgb(var(--c-canvas) / <alpha-value>)',
      accent: 'rgb(var(--c-accent) / <alpha-value>)',
      onaccent: 'rgb(var(--c-on-accent) / <alpha-value>)',
      // Fixed brand colours, also overridable from the admin (Settings, Colors and Fonts).
      navy: 'rgb(var(--c-navy) / <alpha-value>)',
      mist: 'rgb(var(--c-mist) / <alpha-value>)',
      // Home hero keeps its original white and black cards on the dark background.
      ink: 'rgb(var(--c-ink) / <alpha-value>)',
      cloud: 'rgb(var(--c-cloud) / <alpha-value>)',
    },
    extend: {
      fontFamily: {
        sans: ['var(--font-sans)'],
        display: ['var(--font-display)'],
      },
      maxWidth: {
        site: '1280px',
        read: '720px',
      },
      letterSpacing: {
        tightest: '-0.03em',
      },
    },
  },
  plugins: [],
}
