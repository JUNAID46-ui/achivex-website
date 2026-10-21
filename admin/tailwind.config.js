import forms from '@tailwindcss/forms'
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/views/**/*.blade.php', './public/js/**/*.js', './app/**/*.php', './config/**/*.php'],
  theme: {
    extend: {
      colors: {
        // White workspace, black sidebar, and neon colours for figures and charts.
        ink: '#0A0A0A',
        slate2: '#262626',
        accent: { DEFAULT: '#A855F7', 50: '#FAF5FF', 100: '#F3E8FF', 200: '#D8B4FE', 300: '#C084FC', 600: '#9333EA' },
        neon: { purple: '#A855F7', green: '#22C55E', blue: '#3B82F6', red: '#EF4444' },
        // Amber is kept only for warnings.
        amber: { DEFAULT: '#F59E0B', 50: '#FFFBEB', 600: '#D97706' },
        wp: { bg: '#FFFFFF', bar: '#FFFFFF', side: '#000000', sideHover: '#141414' },
      },
      fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
    },
  },
  plugins: [forms],
}
