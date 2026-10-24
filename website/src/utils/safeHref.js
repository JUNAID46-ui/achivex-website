// Links that come from the admin (settings, page builder) only render when they use a safe scheme,
// so a stored "javascript:" or "data:" URL can never run in a visitor's browser.
export const safeHref = (url) => {
  const value = String(url ?? '').trim()
  return /^(https?:\/\/|mailto:|tel:|\/(?![/\\])|#)/i.test(value) ? value : undefined
}
