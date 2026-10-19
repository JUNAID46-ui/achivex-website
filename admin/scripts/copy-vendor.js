// Copies browser builds of the admin JS libraries into public/vendor.
import { copyFileSync, mkdirSync } from 'node:fs'
mkdirSync('public/vendor', { recursive: true })
const files = {
  'node_modules/alpinejs/dist/cdn.min.js': 'public/vendor/alpine.min.js',
  'node_modules/chart.js/dist/chart.umd.js': 'public/vendor/chart.umd.js',
  'node_modules/sortablejs/Sortable.min.js': 'public/vendor/sortable.min.js',
}
for (const [from, to] of Object.entries(files)) copyFileSync(from, to)
console.log('vendor files copied')
