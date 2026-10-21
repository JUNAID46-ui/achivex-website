// Shared helpers for the AchiveX admin.
window.adminFetch = async (url, options = {}) => {
  const token = document.querySelector('meta[name="csrf-token"]').content
  const res = await fetch(url, {
    ...options,
    headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json', ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}), ...(options.headers || {}) },
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.message || 'Something went wrong. Please try again.')
  return data
}

// Opens the media library and calls onSelect(url) with the chosen file.
window.pickMedia = (onSelect) => window.dispatchEvent(new CustomEvent('media-pick', { detail: { onSelect } }))

window.copyText = async (text, el) => {
  try {
    await navigator.clipboard.writeText(text)
    if (el) { const old = el.innerText; el.innerText = 'Copied'; setTimeout(() => (el.innerText = old), 1500) }
  } catch { window.prompt('Copy this text:', text) }
}

window.slugify = (s) => String(s || '').toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

// Toast messages for AJAX saves
window.toast = (message, type = 'success') => window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }))

// Live SEO checks used by the SEO box on edit screens.
window.seoChecks = ({ title, description, keyword, slug, content }) => {
  const k = (keyword || '').trim().toLowerCase()
  const t = (title || '').trim()
  const d = (description || '').trim()
  const text = (content || '').toLowerCase()
  const words = text.split(/\s+/).filter(Boolean)
  const checks = []
  const add = (ok, warn, label) => checks.push({ status: ok ? 'good' : warn ? 'ok' : 'bad', label })
  add(t.length >= 30 && t.length <= 60, t.length > 0, `SEO title length is ${t.length} characters (aim for 30 to 60).`)
  add(d.length >= 120 && d.length <= 160, d.length > 0, `Meta description length is ${d.length} characters (aim for 120 to 160).`)
  if (!k) {
    checks.push({ status: 'bad', label: 'No focus keyword set.' })
  } else {
    const kw = (ok, where, soft) => checks.push({ status: ok ? 'good' : soft ? 'ok' : 'bad', label: ok ? `Focus keyword appears in ${where}.` : `Focus keyword is missing from ${where}.` })
    kw(t.toLowerCase().includes(k), 'the SEO title')
    kw(d.toLowerCase().includes(k), 'the meta description')
    if (slug !== undefined) kw(slug.toLowerCase().includes(window.slugify(k)), 'the URL', true)
    if (words.length) {
      const hits = text.split(k).length - 1
      const density = (hits * k.split(/\s+/).length * 100) / words.length
      add(density >= 0.5 && density <= 3, hits > 0, `Keyword density is ${density.toFixed(1)}% (aim for 0.5% to 3%).`)
    }
  }
  if (words.length) add(words.length >= 300, words.length >= 150, `Content length is ${words.length} words (300 or more is better).`)
  const score = Math.round((checks.reduce((s, c) => s + (c.status === 'good' ? 1 : c.status === 'ok' ? 0.5 : 0), 0) / checks.length) * 100)
  return { checks, score }
}

// Repeater fields: list values become multi-line text while editing and are split again on the server.
let repeaterKey = 0
window.repeater = (items, fields) => ({
  fields,
  items: (items || []).map((it) => {
    const row = { _k: ++repeaterKey, _open: false }
    fields.forEach((f) => {
      const v = it[f.name]
      row[f.name] = Array.isArray(v) ? v.join(f.type === 'paragraphs' ? '\n\n' : '\n') : v ?? ''
    })
    return row
  }),
  add() {
    const row = { _k: ++repeaterKey, _open: true }
    this.fields.forEach((f) => (row[f.name] = ''))
    this.items.push(row)
  },
  remove(i) {
    if (confirm('Remove this item?')) this.items.splice(i, 1)
  },
  move(i, d) {
    const j = i + d
    if (j < 0 || j >= this.items.length) return
    const [row] = this.items.splice(i, 1)
    this.items.splice(j, 0, row)
  },
  get json() {
    return JSON.stringify(this.items.map(({ _k, _open, ...rest }) => rest))
  },
})

// Shared by the Page Builder and the Edit Page screen: turns saved content into form values and back.
window.contentForm = {
  toForm(fields, data) {
    const out = {}
    ;(fields || []).forEach((f) => {
      const v = (data || {})[f.name]
      if (f.type === 'lines') out[f.name] = Array.isArray(v) ? v.join('\n') : v || ''
      else if (f.type === 'images') out[f.name] = Array.isArray(v) ? [...v] : v ? [v] : []
      else if (f.type === 'repeater') out[f.name] = (Array.isArray(v) ? v : []).map((item) => ({ ...contentForm.toForm(f.fields, item), _open: false }))
      else if (f.type === 'group') out[f.name] = contentForm.toForm(f.fields, v || {})
      else out[f.name] = v ?? ''
    })
    return out
  },
  fromForm(fields, form) {
    const out = {}
    ;(fields || []).forEach((f) => {
      const v = form[f.name]
      if (f.type === 'lines') out[f.name] = String(v || '').split('\n').map((s) => s.trim()).filter(Boolean)
      else if (f.type === 'images') out[f.name] = (v || []).filter(Boolean)
      else if (f.type === 'repeater') out[f.name] = (v || []).map((item) => contentForm.fromForm(f.fields, item))
      else if (f.type === 'group') out[f.name] = contentForm.fromForm(f.fields, v || {})
      else out[f.name] = v
    })
    return out
  },
  // Methods mixed into an Alpine component that renders admin.partials.content-fields.
  methods: {
    addItem(f, list) {
      const row = { _open: true }
      f.fields.forEach((sf) => (row[sf.name] = sf.type === 'images' ? [] : ''))
      list.push(row)
      this.dirty = true
    },
    moveItem(list, i, d) {
      const j = i + d
      if (j < 0 || j >= list.length) return
      const [row] = list.splice(i, 1)
      list.splice(j, 0, row)
      this.dirty = true
    },
  },
}

// Uploads files to the media library and returns the saved items. With image storage connected,
// each file goes from the browser straight to Supabase, so large files work (Vercel itself only
// accepts 4.5 MB per request). Without it, files go through the admin, for local testing.
window.uploadMedia = async (files, onProgress = () => {}) => {
  const cfg = window.adminMedia || {}
  const list = [...files]
  const saved = []
  for (const [index, file] of list.entries()) {
    const step = (percent) => onProgress({ index, total: list.length, name: file.name, percent })
    step(0)
    if (!cfg.cloud) {
      const fd = new FormData()
      fd.append('files[]', file)
      saved.push(...(await adminFetch(cfg.store, { method: 'POST', body: fd })))
      step(100)
      continue
    }
    const link = await adminFetch(cfg.sign, { method: 'POST', body: JSON.stringify({ filename: file.name, size: file.size }) })
    await new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest()
      xhr.open('PUT', link.url)
      xhr.setRequestHeader('Content-Type', link.mime)
      xhr.setRequestHeader('cache-control', 'max-age=31536000')
      xhr.setRequestHeader('x-upsert', 'false')
      xhr.upload.onprogress = (e) => e.lengthComputable && step(Math.round((e.loaded / e.total) * 100))
      xhr.onload = () => (xhr.status < 300 ? resolve() : reject(new Error(uploadError(xhr, file))))
      xhr.onerror = () => reject(new Error(`The upload of ${file.name} was interrupted. Check the connection and try again.`))
      xhr.send(file)
    })
    saved.push(await adminFetch(cfg.complete, { method: 'POST', body: JSON.stringify({ path: link.path, filename: file.name, ...(await imageSize(file)) }) }))
  }
  return saved
}

function uploadError(xhr, file) {
  let message = ''
  try { message = JSON.parse(xhr.responseText).message || '' } catch {}
  if (xhr.status === 413 || /maximum allowed size|too large/i.test(message)) {
    return `${file.name} is larger than your Supabase storage allows. The Free plan accepts up to 50 MB per file.`
  }
  return `${file.name} could not be uploaded${message ? ': ' + message : '.'}`
}

async function imageSize(file) {
  if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || !window.createImageBitmap) return {}
  try {
    const bitmap = await createImageBitmap(file)
    const size = { width: bitmap.width, height: bitmap.height }
    bitmap.close()
    return size
  } catch {
    return {}
  }
}

// Forms marked data-unsaved-warning ask before leaving with unsaved changes.
// Buttons marked data-loading show progress and block double submits.
document.addEventListener('DOMContentLoaded', () => {
  let dirty = false
  document.querySelectorAll('form[data-unsaved-warning]').forEach((form) => {
    form.addEventListener('input', () => { dirty = true })
    form.addEventListener('change', () => { dirty = true })
    form.addEventListener('submit', () => { dirty = false })
  })
  window.addEventListener('beforeunload', (e) => {
    if (!dirty) return
    e.preventDefault()
    e.returnValue = ''
  })
})

document.addEventListener('submit', (e) => {
  if (e.defaultPrevented) return
  const button = e.submitter && e.submitter.matches('[data-loading]') ? e.submitter : e.target.querySelector('button[data-loading]:not([name])')
  if (!button) return
  // Let the browser send the form first, then lock the button.
  setTimeout(() => {
    button.disabled = true
    button.dataset.label = button.innerHTML
    button.textContent = 'Working…'
  }, 0)
})
