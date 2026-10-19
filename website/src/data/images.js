// All imagery is served from Unsplash as placeholders.
// Swap any id (or return your own URL) to replace an image across the site.
export const img = (id, w = 1600) =>
  `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&q=80`
