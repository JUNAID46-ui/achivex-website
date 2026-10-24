// QuickPaste: turns an article pasted from Google Docs, Word, ChatGPT or plain Markdown into the
// blog post fields. window.parseQuickPaste(input, isHtml) returns
// { title, intro_keyword, intro_text, excerpt, description, sections, faqs, seo, stats, warnings }.
// Sections and FAQs come back in the shape the form repeaters use (paragraphs and lists as text).
;(function () {
  const FAQ_HEADING = /^(faqs?|frequently asked questions|common questions)\s*[:?]?$/i
  const clean = (s) => (s || '').replace(/ /g, ' ').replace(/[ \t]+/g, ' ').replace(/\s*\n\s*/g, ' ').trim()
  const plain = (s) => clean((s || '').replace(/\*\*([^*]+)\*\*/g, '$1').replace(/\[([^\]]+)\]\([^)]+\)/g, '$1'))
  const words = (s) => (plain(s) ? plain(s).split(/\s+/).length : 0)
  const cut = (s, max) => {
    s = plain(s)
    if (s.length <= max) return s
    const part = s.slice(0, max - 1)
    return part.slice(0, part.lastIndexOf(' ')).replace(/[,;:]$/, '') + '…'
  }

  // ---- HTML: block list with inline Markdown for bold and links ----
  const isBold = (el) => {
    const tag = el.tagName
    const weight = (el.style && el.style.fontWeight) || ''
    if (weight === 'normal' || weight === '400') return false
    return tag === 'STRONG' || (tag === 'B' && !(el.id || '').startsWith('docs-internal')) || weight === 'bold' || Number(weight) >= 600
  }
  function inline(node) {
    let out = ''
    node.childNodes.forEach((n) => {
      if (n.nodeType === 3) { out += n.textContent; return }
      if (n.nodeType !== 1) return
      if (n.tagName === 'BR') { out += ' '; return }
      if (/^(SCRIPT|STYLE|IMG|SVG|BUTTON)$/.test(n.tagName)) return
      const inner = inline(n)
      if (n.tagName === 'A') {
        const href = (n.getAttribute('href') || '').trim()
        out += /^(https?:\/\/|\/|mailto:)/i.test(href) && clean(inner) ? `[${clean(inner).replace(/\*\*/g, '')}](${href})` : inner
      } else if (isBold(n) && clean(inner)) {
        out += ` **${clean(inner).replace(/\*\*/g, '')}** `
      } else out += inner
    })
    return out
  }
  const fixSpaces = (s) => clean(s).replace(/\*\*\s+/g, '**').replace(/\s+\*\*/g, '**').replace(/\*\*(\S[^*]*?)\*\*/g, ' **$1** ').replace(/\s+([.,;:!?)])/g, '$1').replace(/^\s+|\s+$/g, '').replace(/\s{2,}/g, ' ')
  const BLOCK = /^(H[1-6]|P|LI|UL|OL|DIV|BLOCKQUOTE|TABLE|SECTION|ARTICLE|PRE|FIGURE|IMG)$/
  // Images are kept only with a web address (https:// or a site path). Images pasted from
  // Word or Google Docs have no address the website can use, so they are dropped with a warning.
  const imageSrc = (src) => (/^(https:\/\/|\/)[^\s"()]+$/.test(src || '') ? src : '')
  const imageText = (s) => clean(s).replace(/[\[\]"()]/g, '')
  // An image as it is stored in a section: ![alt](url "caption")
  const imageMd = (b) => `![${imageText(b.alt)}](${b.src}${b.caption ? ` "${imageText(b.caption)}"` : ''})`
  const IMAGE_MD = /^!\[([^\]]*)\]\(((?:https:\/\/|\/)[^)\s"]+)(?:\s+"([^"]*)")?\)$/
  function htmlBlocks(html) {
    const body = new DOMParser().parseFromString(html, 'text/html').body
    const blocks = []
    const walk = (el) => {
      el.childNodes.forEach((n) => {
        if (n.nodeType === 3) { if (clean(n.textContent)) blocks.push({ type: 'p', text: fixSpaces(n.textContent) }); return }
        if (n.nodeType !== 1 || /^(SCRIPT|STYLE|META|LINK|TITLE)$/.test(n.tagName)) return
        const tag = n.tagName
        if (tag === 'FIGURE' || tag === 'IMG') {
          const img = tag === 'IMG' ? n : n.querySelector('img')
          const src = imageSrc(img && img.getAttribute('src'))
          if (src) blocks.push({ type: 'img', src, alt: img.getAttribute('alt') || '', caption: clean((n.querySelector('figcaption') || {}).textContent || '') })
          else if (img) blocks.push({ type: 'img', src: '', alt: '', caption: '' })
          return
        }
        if (/^H[1-6]$/.test(tag)) { const t = plain(inline(n)); if (t) blocks.push({ type: tag === 'H1' ? 'h1' : tag === 'H2' ? 'h2' : 'h3', text: t }); return }
        if (tag === 'UL' || tag === 'OL') {
          const items = [...n.children].filter((c) => c.tagName === 'LI').map((li) => fixSpaces(inline(li))).filter(Boolean)
          if (items.length) blocks.push({ type: 'list', items })
          return
        }
        const hasBlockChild = [...n.children].some((c) => BLOCK.test(c.tagName))
        if (hasBlockChild) { walk(n); return }
        const text = fixSpaces(inline(n))
        if (text) blocks.push({ type: 'p', text })
      })
    }
    walk(body)
    return blocks
  }

  // ---- Plain text and Markdown ----
  function textBlocks(text) {
    const blocks = []
    let para = []
    const flush = () => { if (para.length) { blocks.push({ type: 'p', text: fixSpaces(para.join(' ')) }); para = [] } }
    text.replace(/\r/g, '').split('\n').forEach((raw) => {
      const line = raw.trim()
      if (!line) { flush(); return }
      const h = line.match(/^(#{1,6})\s+(.*)$/)
      if (h) { flush(); blocks.push({ type: h[1].length === 1 ? 'h1' : h[1].length === 2 ? 'h2' : 'h3', text: plain(h[2]) }); return }
      const li = line.match(/^(?:[-*•]|\d+[.)])\s+(.*)$/)
      if (li) {
        flush()
        const last = blocks[blocks.length - 1]
        if (last && last.type === 'list') last.items.push(fixSpaces(li[1]))
        else blocks.push({ type: 'list', items: [fixSpaces(li[1])] })
        return
      }
      para.push(line)
    })
    flush()
    return blocks
  }

  function parse(input, isHtml, opts = {}) {
    const all = isHtml ? htmlBlocks(input) : textBlocks(input)
    const blocks = all.filter((b) => b.type === 'list' || b.text || (b.type === 'img' && b.src))
    const warnings = []
    const lost = all.filter((b) => b.type === 'img' && !b.src).length
    if (lost) warnings.push(`${lost} pasted image(s) were left out. Add them with the Image button so they are uploaded to the media library.`)
    if (all.some((b) => b.type === 'img' && b.src && !clean(b.alt))) warnings.push('An image has no description (alt text). Click the image to add one.')

    // Title: the first H1, otherwise the first short line (not in the article editor, which has its own title).
    let title = ''
    let i = blocks.findIndex((b) => b.type === 'h1')
    if (i === -1 && !opts.noTitle && blocks[0] && blocks[0].type !== 'list' && words(blocks[0].text) <= 16) i = 0
    if (i !== -1) { title = plain(blocks[i].text); blocks.splice(i, 1) }
    else if (!opts.noTitle) warnings.push('No title found. Add an H1 at the top of the article.')

    // Opening paragraph: the first paragraph before any H2. Its leading bold text is the keyword.
    let introKeyword = ''
    let introText = ''
    const pre = []
    while (blocks.length && blocks[0].type !== 'h2') pre.push(blocks.shift())
    const firstP = pre.findIndex((b) => b.type === 'p')
    if (pre.slice(0, firstP).some((b) => b.type === 'img')) warnings.push('Put images after the first paragraph. The post\'s main image is set in the Featured image field.')
    if (firstP !== -1) {
      const [intro] = pre.splice(firstP, 1)
      const m = intro.text.match(/^\*\*([^*]+)\*\*(.*)$/)
      if (m) { introKeyword = plain(m[1]); introText = m[2].replace(/^\s*/, ' ').replace(/\*\*/g, '') }
      else { introText = ' ' + intro.text.replace(/\*\*/g, ''); warnings.push('The first paragraph does not start with a bold keyword. Fill in the opening keyword.') }
    } else warnings.push('No opening paragraph found before the first H2.')

    // Sections from H2 headings; a FAQ heading switches to questions and answers.
    const sections = []
    const faqs = []
    let cur = null
    let inFaq = false
    let q = null
    if (pre.length) { cur = { heading: 'Overview', paragraphs: [], list: [] }; sections.push(cur); warnings.push('Text before the first H2 was put in a section called "Overview". Rename it if needed.') }
    const addToSection = (b) => {
      if (b.type === 'img') cur.paragraphs.push(imageMd(b))
      else if (b.type === 'h3') cur.paragraphs.push('### ' + b.text)
      else if (b.type === 'list') cur.list.push(...b.items)
      else cur.paragraphs.push(b.text)
    }
    pre.forEach(addToSection)
    blocks.forEach((b) => {
      if (b.type === 'h2') {
        if (FAQ_HEADING.test(b.text)) { inFaq = true; cur = null; return }
        inFaq = false
        cur = { heading: b.text, paragraphs: [], list: [] }
        sections.push(cur)
        return
      }
      if (inFaq) {
        if (b.type === 'img') return
        const boldOnly = b.type === 'p' && /^\*\*[^*]+\*\*$/.test(b.text)
        if (b.type === 'h3' || boldOnly || (b.type === 'p' && plain(b.text).endsWith('?') && words(b.text) <= 25)) {
          if (q) faqs.push(q)
          q = { q: plain(b.text), a: '' }
        } else if (q) {
          q.a = (q.a + ' ' + (b.type === 'list' ? b.items.map(plain).join('. ') : plain(b.text))).trim()
        }
        return
      }
      if (cur) addToSection(b)
    })
    if (q) faqs.push(q)
    const emptyFaq = faqs.filter((f) => !f.a).length
    if (emptyFaq) warnings.push(`${emptyFaq} question(s) in the FAQs have no answer.`)
    if (!sections.length) warnings.push('No H2 headings found, so there are no sections.')

    const formSections = sections.map((s) => ({ heading: s.heading, paragraphs: s.paragraphs.join('\n\n'), list: s.list.join('\n') }))
    const isImage = (p) => IMAGE_MD.test(p)
    const allText = [introKeyword + introText, ...sections.map((s) => [s.heading, ...s.paragraphs.filter((p) => !isImage(p)), ...s.list].join(' ')), ...faqs.map((f) => f.q + ' ' + f.a)].join(' ')
    const excerpt = cut(introKeyword + introText, 300)
    const description = cut(introKeyword + introText, 158)
    return {
      title,
      intro_keyword: introKeyword,
      intro_text: introText,
      excerpt,
      description,
      sections: formSections,
      faqs,
      seo: {
        text: plain(allText),
        raw: allText,
        headings: sections.map((s) => s.heading),
        first: plain(introKeyword + introText),
        paragraphs: sections.flatMap((s) => s.paragraphs.filter((p) => !p.startsWith('### ') && !isImage(p))),
      },
      stats: { title, sections: sections.length, faqs: faqs.length, images: sections.reduce((n, s) => n + s.paragraphs.filter(isImage).length, 0), words: words(allText) },
      warnings,
    }
  }

  // ---- Article editor helpers ----
  const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
  // Inline Markdown (bold, links) to safe HTML: the text is escaped first, links keep only safe schemes.
  const mdInline = (s) => esc(s)
    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    .replace(/\[([^\]]+)\]\(((?:https?:\/\/|\/|#|mailto:)[^)\s"]*)\)/g, '<a href="$2">$1</a>')
  // In the editor an image is a block that cannot be typed into; clicking it edits its text.
  const figureHtml = (b) => `<figure contenteditable="false"><img src="${esc(b.src)}" alt="${esc(b.alt || '')}">${b.caption ? `<figcaption>${esc(b.caption)}</figcaption>` : ''}</figure>`
  const blocksToHtml = (blocks) => blocks.map((b) => {
    if (b.type === 'img') return b.src ? figureHtml(b) : ''
    if (b.type === 'list') return '<ul>' + b.items.map((li) => `<li>${mdInline(li)}</li>`).join('') + '</ul>'
    if (b.type === 'h1') return `<h1>${esc(b.text)}</h1>`
    if (b.type === 'h2' || b.type === 'h3') return `<${b.type}>${esc(b.text)}</${b.type}>`
    return `<p>${mdInline(b.text)}</p>`
  }).join('')
  // Pasted HTML or text, reduced to headings, paragraphs, lists, bold and links.
  const cleanPaste = (input, isHtml) => blocksToHtml((isHtml ? htmlBlocks(input) : textBlocks(input)).filter((b) => b.type === 'list' || b.text))
  // Saved post fields back to editor HTML.
  const toEditorHtml = ({ intro_keyword, intro_text, sections }) => {
    let html = ''
    if (intro_keyword || intro_text) html += `<p>${intro_keyword ? `<strong>${esc(intro_keyword)}</strong>` : ''}${mdInline(intro_text || '')}</p>`
    ;(sections || []).forEach((s) => {
      html += `<h2>${esc(s.heading || '')}</h2>`
      const paras = Array.isArray(s.paragraphs) ? s.paragraphs : String(s.paragraphs || '').split(/\n\s*\n/)
      paras.filter((p) => p && p.trim()).forEach((p) => {
        const img = p.trim().match(IMAGE_MD)
        if (img) html += figureHtml({ alt: img[1], src: img[2], caption: img[3] || '' })
        else if (p.startsWith('### ')) {
          const [head, ...rest] = p.split('\n')
          html += `<h3>${esc(head.slice(4).trim())}</h3>`
          if (rest.join(' ').trim()) html += `<p>${mdInline(rest.join(' ').trim())}</p>`
        } else html += `<p>${mdInline(p.trim())}</p>`
      })
      const list = Array.isArray(s.list) ? s.list : String(s.list || '').split('\n')
      if (list.filter((l) => l.trim()).length) html += '<ul>' + list.filter((l) => l.trim()).map((li) => `<li>${mdInline(li.trim())}</li>`).join('') + '</ul>'
    })
    return html || '<p><br></p>'
  }

  window.parseQuickPaste = parse
  // A paste into the article editor: the H1 becomes the post title and a trailing FAQs part becomes
  // the FAQ list, so only the article itself goes into the editor.
  const pasteForEditor = (input, isHtml) => {
    const all = isHtml ? htmlBlocks(input) : textBlocks(input)
    const lost = all.filter((b) => b.type === 'img' && !b.src).length
    const blocks = all.filter((b) => b.type === 'list' || b.text || (b.type === 'img' && b.src))
    const h1 = blocks.findIndex((b) => b.type === 'h1')
    const title = h1 !== -1 ? blocks.splice(h1, 1)[0].text : ''
    const faqAt = blocks.findIndex((b) => b.type === 'h2' && FAQ_HEADING.test(b.text))
    const faqs = faqAt !== -1 ? parse(blocksToHtml(blocks.slice(faqAt)), true, { noTitle: true }).faqs : []
    return { html: blocksToHtml(faqAt !== -1 ? blocks.slice(0, faqAt) : blocks), title, faqs, lost }
  }

  window.articleEditor = { cleanPaste, toEditorHtml, pasteForEditor, words, figureHtml, imageSrc }
})()
