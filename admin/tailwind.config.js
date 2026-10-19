import forms from '@tailwindcss/forms'
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/views/**/*.blade.php', './public/js/**/*.js', './app/**/*.php', './config/**/*.php'],
  theme: {
    extend: {
      colors: {
        navy: '#082032',
        slate2: '#334756',
        amber: { DEFAULT: '#F0A500', 50: '#FFF7E0', 600: '#C98A00' },
        wp: { bg: '#F0F2F4', bar: '#0B1620', side: '#082032', sideHover: '#0F2B42' },
      },
      fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
    },
  },
  plugins: [forms],
}
