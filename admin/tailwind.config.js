import forms from '@tailwindcss/forms'
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/views/**/*.blade.php', './public/js/**/*.js', './app/**/*.php', './config/**/*.php'],
  theme: {
    extend: {
      colors: {
        // Classic palette: jet black and white, with a royal purple accent kept to about 10% of the screen.
        ink: '#111111',
        slate2: '#2B2B2F',
        accent: { DEFAULT: '#6B3FA0', 50: '#F5F0FA', 100: '#E6DAF3', 200: '#C9B2E6', 600: '#55308A' },
        // Amber is kept only for warnings (badges, "needs work" scores, unsaved changes).
        amber: { DEFAULT: '#D99A00', 50: '#FFF8E6', 600: '#A86F00' },
        wp: { bg: '#F6F6F7', bar: '#0A0A0A', side: '#111111', sideHover: '#232327' },
      },
      fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
    },
  },
  plugins: [forms],
}
