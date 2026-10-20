import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const SUPABASE_URL = process.env.VITE_SUPABASE_URL || 'https://pktnqerzatrdndhbtsqp.supabase.co'
const SUPABASE_KEY = process.env.VITE_SUPABASE_ANON_KEY || 'sb_publishable_r9C0dMpa0Djj4Hf4tyn_rw_Nptd2QdD'

const escapeAttr = (value) => String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;')

// Search Console and Bing read the raw HTML without running JavaScript, so the verification
// codes saved in the admin (SEO, Webmaster Tools) are written into index.html at build time.
// SITE_GOOGLE_VERIFICATION and SITE_BING_VERIFICATION override them, mainly for local builds.
function verificationTags() {
  let seo = {}
  return {
    name: 'achivex-verification-tags',
    apply: 'build',
    async buildStart() {
      try {
        const res = await fetch(`${SUPABASE_URL}/rest/v1/site_settings?key=eq.seo&select=value`, {
          headers: { apikey: SUPABASE_KEY },
          signal: AbortSignal.timeout(8000),
        })
        seo = (await res.json())?.[0]?.value || {}
      } catch (e) {
        this.warn(`Could not read verification codes from Supabase: ${e.message}`)
      }
    },
    transformIndexHtml(html) {
      const google = process.env.SITE_GOOGLE_VERIFICATION || seo.googleVerification
      const bing = process.env.SITE_BING_VERIFICATION || seo.bingVerification
      const tags = [
        google && `<meta name="google-site-verification" content="${escapeAttr(google)}" />`,
        bing && `<meta name="msvalidate.01" content="${escapeAttr(bing)}" />`,
      ].filter(Boolean)
      return tags.length ? html.replace('</head>', `    ${tags.join('\n    ')}\n  </head>`) : html
    },
  }
}

export default defineConfig({
  plugins: [react(), verificationTags()],
  build: {
    // Libraries go in their own files so browsers keep them cached between site updates.
    rollupOptions: {
      output: {
        manualChunks: {
          react: ['react', 'react-dom', 'react-router-dom'],
          motion: ['framer-motion'],
          supabase: ['@supabase/supabase-js'],
          icons: ['lucide-react'],
        },
      },
    },
  },
})
