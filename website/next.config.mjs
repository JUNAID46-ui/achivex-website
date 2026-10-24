// Next.js builds every page to plain HTML files (output: 'export'), so the site runs on any host,
// including Hostinger shared hosting, and search engines get the full page without running JavaScript.
/** @type {import('next').NextConfig} */
export default {
  output: 'export',
  trailingSlash: true,
  images: { unoptimized: true },
  reactStrictMode: true,
  poweredByHeader: false,
}
