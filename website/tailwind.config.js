/** @type {import('tailwindcss').Config} */
// The palette is locked to the brand colors (#082032, #334756, #F0A500, #000000), white and one light tint of #334756 for backgrounds.
// Tailwind's default colors are intentionally replaced, not extended.
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      white: '#FFFFFF',
      navy: '#082032',
      slate: '#334756',
      ink: '#000000',
      accent: '#F0A500',
      mist: '#EEF1F3',
    },
    extend: {
      fontFamily: {
        sans: ['Manrope', 'system-ui', 'sans-serif'],
        display: ['Geist', 'Manrope', 'system-ui', 'sans-serif'],
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
