import forms from '@tailwindcss/forms'
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/views/**/*.blade.php', './public/js/**/*.js', './app/**/*.php', './config/**/*.php'],
  theme: {
    extend: {
      colors: {
        // Minimal monochrome interface. Neon colours appear only in figures and charts.
        ink: '#0A0A0A',
        slate2: '#262626',
        neon: { purple: '#A855F7', green: '#22C55E', blue: '#3B82F6', red: '#EF4444' },
        wp: { bg: '#FFFFFF', bar: '#FFFFFF', side: '#0A0A0A', sideHover: '#161616', sideActive: '#1F1F1F' },
      },
      fontFamily: {
        sans: ['Manrope', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
      },
    },
  },
  plugins: [forms],
}
