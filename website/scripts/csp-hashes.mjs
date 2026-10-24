// Runs after `next build`. Next.js writes a few small inline scripts into every page, which is why the
// Content-Security-Policy header in public/.htaccess still allows 'unsafe-inline'. This adds a stricter
// policy to each page as a <meta> tag that allows only that page's own inline scripts, by their SHA-256
// hash. Browsers apply both policies, so an injected script that is not in the list is blocked.
import { createHash } from 'node:crypto'
import { readdirSync, readFileSync, statSync, writeFileSync } from 'node:fs'
import { join } from 'node:path'

const files = []
const walk = (dir) => {
  for (const f of readdirSync(dir)) {
    const p = join(dir, f)
    if (statSync(p).isDirectory()) walk(p)
    else if (p.endsWith('.html')) files.push(p)
  }
}
walk('out')

const hash = (code) => `'sha256-${createHash('sha256').update(code, 'utf8').digest('base64')}'`

let count = 0
for (const file of files) {
  let html = readFileSync(file, 'utf8')
  if (html.includes('http-equiv="Content-Security-Policy"')) continue
  const hashes = new Set()
  for (const [, attrs, code] of html.matchAll(/<script(?![^>]*\bsrc=)([^>]*)>([\s\S]*?)<\/script>/g)) {
    const type = (attrs.match(/\btype="([^"]+)"/) || [])[1]
    if (type && !/javascript|module/i.test(type)) continue // JSON data is not run, so it needs no hash
    hashes.add(hash(code))
  }
  const policy = `script-src 'self' ${[...hashes].join(' ')}; object-src 'none'; base-uri 'self'`
  const meta = `<meta http-equiv="Content-Security-Policy" content="${policy}"/>`
  const at = html.indexOf('<meta charSet="utf-8"/>')
  if (at < 0) throw new Error(`No charset tag in ${file}`)
  const end = at + '<meta charSet="utf-8"/>'.length
  html = html.slice(0, end) + meta + html.slice(end)
  writeFileSync(file, html)
  count++
}
console.log(`Script hashes added to ${count} pages`)
