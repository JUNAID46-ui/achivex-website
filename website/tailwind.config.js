/** @type {import('tailwindcss').Config} */
// The palette is locked to the brand colors: #0B0D12 black (about 40%), #FFFFFF white sections (25%),
// #0F2C9C blue sections (20%), #C9CED6 silver text and cards. The light theme in index.css uses blue as the only accent.
// Tailwind's default colors are intentionally replaced, not extended.
export default {
  content: ['./app/**/*.{js,jsx}', './src/**/*.{js,jsx}'],
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
      // Site palette (see :root in index.css), editable in the admin.
      site: {
        hero: 'rgb(var(--s-hero) / <alpha-value>)',
        dark: 'rgb(var(--s-dark) / <alpha-value>)',
        black: 'rgb(var(--s-black) / <alpha-value>)',
        grey: 'rgb(var(--s-grey) / <alpha-value>)',
        heading: 'rgb(var(--s-heading) / <alpha-value>)',
        text: 'rgb(var(--s-text) / <alpha-value>)',
        muted: 'rgb(var(--s-muted) / <alpha-value>)',
        brand: 'rgb(var(--s-brand) / <alpha-value>)',
        card: 'rgb(var(--s-card) / <alpha-value>)',
        line: 'rgb(var(--s-line) / <alpha-value>)',
      },
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
