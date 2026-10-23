import '../src/index.css'
import { getContent } from '../src/lib/build'
import { settings } from '../src/data/settings'
import { themeCss, FONTS } from '../src/lib/theme'

const FONT_URL = 'https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Manrope:wght@400;500;600;700&display=swap'

export const metadata = {
  metadataBase: new URL(process.env.SITE_URL || 'https://www.achivex.com'),
  icons: { icon: '/favicon.png' },
}

export const viewport = { width: 'device-width', initialScale: 1, themeColor: '#FFFFFF' }

export default async function RootLayout({ children }) {
  await getContent()
  const { css, load } = themeCss(settings.theme)
  const extra = load.length ? `https://fonts.googleapis.com/css2?${load.map((n) => `family=${n.replace(/ /g, '+')}:wght@${FONTS[n]}`).join('&')}&display=swap` : null
  const google = process.env.SITE_GOOGLE_VERIFICATION || settings.seo.googleVerification
  const bing = process.env.SITE_BING_VERIFICATION || settings.seo.bingVerification
  return (
    <html lang="en" data-theme="light">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link rel="stylesheet" href={FONT_URL} />
        {extra && <link rel="stylesheet" href={extra} />}
        {css && <style id="site-theme" dangerouslySetInnerHTML={{ __html: css }} />}
        {google && <meta name="google-site-verification" content={google} />}
        {bing && <meta name="msvalidate.01" content={bing} />}
      </head>
      <body>{children}</body>
    </html>
  )
}
