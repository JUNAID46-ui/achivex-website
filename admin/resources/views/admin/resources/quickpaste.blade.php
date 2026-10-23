{{-- QuickPaste (RankX): paste a whole article and fill the blog post fields. The pasted HTML is only
     read by the parser (public/js/quickpaste.js); it is never inserted into the page. --}}
<script src="{{ asset('js/quickpaste.js') }}?v={{ @filemtime(public_path('js/quickpaste.js')) }}"></script>
<div class="card" x-data="{
       open: false, text: '', result: null,
       onPaste(e) {
         const html = e.clipboardData.getData('text/html')
         if (!html) return
         e.preventDefault()
         this.text = e.clipboardData.getData('text/plain')
         this.result = window.parseQuickPaste(html, true)
       },
       parseText() { this.result = this.text.trim() ? window.parseQuickPaste(this.text, false) : null },
       reset() { this.text = ''; this.result = null },
       apply() {
         const p = this.result
         const form = this.$root.closest('form')
         const hasContent = ['title', 'intro_text'].some((n) => (form.querySelector(`[name='${n}']`)?.value || '').trim())
         if (hasContent && !confirm('Replace the title, opening paragraph, sections and FAQs with the pasted article?')) return
         const set = (name, value) => {
           const el = form.querySelector(`[name='${name}']`)
           if (!el || value === undefined) return
           el.value = value
           el.dispatchEvent(new Event('input', { bubbles: true }))
         }
         set('title', p.title)
         set('intro_keyword', p.intro_keyword)
         set('intro_text', p.intro_text)
         if (!(form.querySelector(`[name='excerpt']`)?.value || '').trim()) set('excerpt', p.excerpt)
         window.dispatchEvent(new CustomEvent('quickpaste', { detail: p }))
         this.open = false
         this.reset()
       },
     }">
  <div class="card-header">
    <div>
      <h2 class="card-title">QuickPaste</h2>
      <p class="mt-0.5 text-xs text-neutral-500">Paste a full article from Google Docs, Word or ChatGPT. Headings, sections, lists, links and FAQs are filled in for you.</p>
    </div>
    <button type="button" class="btn-secondary" @click="open = true">Paste full article</button>
  </div>

  <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false" @click.self="open = false">
    <div class="max-h-[90vh] w-full max-w-3xl overflow-auto bg-white p-6 shadow-xl">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-lg font-semibold text-ink">Paste full article</h3>
          <p class="help">Use an H1 for the title, start the first paragraph with the bold keyword, use H2 for sections and H3 for smaller headings. A heading called "FAQs" turns the questions below it into the FAQ list.</p>
        </div>
        <button type="button" class="btn-ghost btn-sm" @click="open = false" aria-label="Close">✕</button>
      </div>

      <template x-if="!result">
        <div class="mt-4">
          <textarea x-model="text" @paste="onPaste($event)" rows="12" class="input font-mono text-[13px]" placeholder="Paste here (Ctrl+V). Markdown also works: # Title, ## Section, ### Smaller heading, - list item, **bold**, [link](https://...)"></textarea>
          <div class="mt-3 flex gap-2">
            <button type="button" class="btn-primary" @click="parseText()" :disabled="!text.trim()">Read article</button>
            <button type="button" class="btn-ghost" @click="open = false">Cancel</button>
          </div>
        </div>
      </template>

      <template x-if="result">
        <div class="mt-4 space-y-4">
          <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="border border-neutral-200 p-3"><p class="text-xs text-neutral-500">Sections</p><p class="text-xl font-semibold text-ink" x-text="result.stats.sections"></p></div>
            <div class="border border-neutral-200 p-3"><p class="text-xs text-neutral-500">FAQs</p><p class="text-xl font-semibold text-ink" x-text="result.stats.faqs"></p></div>
            <div class="border border-neutral-200 p-3"><p class="text-xs text-neutral-500">Words</p><p class="text-xl font-semibold text-ink" x-text="result.stats.words"></p></div>
            <div class="border border-neutral-200 p-3"><p class="text-xs text-neutral-500">Keyword</p><p class="truncate text-sm font-semibold text-ink" x-text="result.intro_keyword || '(missing)'"></p></div>
          </div>
          <div>
            <p class="label">Title</p>
            <p class="text-[15px] font-semibold text-ink" x-text="result.title || '(missing)'"></p>
          </div>
          <div>
            <p class="label">Sections</p>
            <ol class="list-decimal space-y-1 pl-5 text-[13.5px] text-neutral-700">
              <template x-for="s in result.sections"><li x-text="s.heading"></li></template>
            </ol>
          </div>
          <div x-show="result.faqs.length">
            <p class="label">FAQs</p>
            <ul class="list-disc space-y-1 pl-5 text-[13.5px] text-neutral-700">
              <template x-for="f in result.faqs"><li x-text="f.q"></li></template>
            </ul>
          </div>
          <ul x-show="result.warnings.length" class="space-y-1 border border-amber-500 bg-amber-50 p-3 text-[13px] text-amber-800">
            <template x-for="w in result.warnings"><li x-text="w"></li></template>
          </ul>
          <div class="flex gap-2">
            <button type="button" class="btn-primary" @click="apply()">Fill the form</button>
            <button type="button" class="btn-ghost" @click="reset()">Paste again</button>
          </div>
          <p class="help">Nothing is saved until you press Save on the post.</p>
        </div>
      </template>
    </div>
  </div>
</div>
