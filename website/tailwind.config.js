/** @type {import('tailwindcss').Config} */
// The palette is locked to the brand colors: #0B0D12 background, #0F2C9C sections and cards,
// #FFFFFF headings, #C9CED6 body text and #B6F500 for CTA buttons, numbers and icons.
// Tailwind's default colors are intentionally replaced, not extended.
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      white: '#FFFFFF',
      canvas: '#0B0D12',
      navy: '#0F2C9C',
      mist: '#0F2C9C',
      body: '#C9CED6',
      slate: '#C9CED6',
      accent: '#B6F500',
      // Home hero keeps its original white and black cards on the dark background.
      ink: '#000000',
      cloud: '#EEF1F3',
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
