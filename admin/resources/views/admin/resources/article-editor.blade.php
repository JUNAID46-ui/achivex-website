{{-- Article editor for blog posts: one writing area like the WordPress block editor. Typing or pasting
     here fills the opening keyword, the opening paragraph and the sections behind the scenes
     (public/js/quickpaste.js), so the website template stays the same. FAQs keep their own box. --}}
@php
  $oldSections = old('sections');
  $initial = [
    'intro_keyword' => old('intro_keyword', $record->intro_keyword ?? ''),
    'intro_text' => old('intro_text', $record->intro_text ?? ''),
    'sections' => $oldSections !== null ? (json_decode($oldSections, true) ?: []) : ($record->sections ?? []),
  ];
@endphp
<script src="{{ asset('js/quickpaste.js') }}?v={{ @filemtime(public_path('js/quickpaste.js')) }}"></script>
<style>
  .article-editor { min-height: 420px; outline: none; font-size: 17px; line-height: 1.75; color: #2a2f37; }
  .article-editor:empty::before { content: attr(data-placeholder); color: #9aa0a9; }
  .article-editor > * + * { margin-top: 1em; }
  .article-editor h2 { font-size: 26px; line-height: 1.25; font-weight: 600; color: #111; margin-top: 1.6em; }
  .article-editor h3 { font-size: 20px; line-height: 1.3; font-weight: 600; color: #111; margin-top: 1.3em; }
  .article-editor ul, .article-editor ol { padding-left: 1.4em; }
  .article-editor ul { list-style: disc; } .article-editor ol { list-style: decimal; }
  .article-editor li + li { margin-top: .35em; }
  .article-editor a { color: #0f2c9c; text-decoration: underline; }
  .article-editor strong, .article-editor b { font-weight: 700; color: #111; }
</style>
<div x-data="{
       count: 0, warnings: [], timer: null,
       init() {
         this.$refs.box.innerHTML = window.articleEditor.toEditorHtml(@js($initial))
         this.sync()
       },
       cmd(c, v = null) { this.$refs.box.focus(); document.execCommand(c, false, v); this.changed() },
       block(tag) { this.cmd('formatBlock', tag) },
       link() {
         const url = prompt('Link address (https://... or /page/)')
         if (url === null) return
         if (url.trim() === '') return this.cmd('unlink')
         if (!/^(https?:\/\/|\/|#|mailto:)/i.test(url.trim())) return alert('Use a full address starting with https:// or a site path starting with /')
         this.cmd('createLink', url.trim())
       },
       onPaste(e) {
         e.preventDefault()
         const html = e.clipboardData.getData('text/html')
         const text = e.clipboardData.getData('text/plain')
         const p = window.articleEditor.pasteForEditor(html || text, !!html)
         const form = this.$root.closest('form')
         const title = form.querySelector(`[name='title']`)
         if (p.title && title && (!title.value.trim() || confirm('Use the pasted heading as the post title?'))) {
           title.value = p.title
           title.dispatchEvent(new Event('input', { bubbles: true }))
         }
         if (p.html) document.execCommand('insertHTML', false, p.html)
         if (p.faqs.length) window.dispatchEvent(new CustomEvent('quickpaste', { detail: { faqs: p.faqs } }))
         this.changed()
       },
       changed() { clearTimeout(this.timer); this.timer = setTimeout(() => this.sync(), 250) },
       sync() {
         const r = window.parseQuickPaste(this.$refs.box.innerHTML, true, { noTitle: true })
         this.$refs.kw.value = r.intro_keyword
         this.$refs.intro.value = r.intro_text
         this.$refs.sections.value = JSON.stringify(r.sections)
         this.count = r.stats.words
         this.warnings = r.warnings.concat(r.faqs.length ? ['A FAQs heading was found in the article. Put questions in the FAQs box below instead.'] : [])
         const title = this.$root.closest('form').querySelector(`[name='title']`)?.value || ''
         window.dispatchEvent(new CustomEvent('quickpaste', { detail: { seo: r.seo, title } }))
       },
     }">
  <label class="label">Article</label>
  <input type="hidden" name="intro_keyword" x-ref="kw">
  <input type="hidden" name="intro_text" x-ref="intro">
  <input type="hidden" name="sections" x-ref="sections">

  <div class="border border-neutral-300 bg-white">
    <div class="sticky top-12 z-10 flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-white px-2 py-1.5">
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="block('p')" title="Paragraph">P</button>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="block('h2')" title="Heading 2 (a section)">H2</button>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="block('h3')" title="Heading 3">H3</button>
      <span class="mx-1 h-5 w-px bg-neutral-200"></span>
      <button type="button" class="btn-ghost btn-sm font-bold" @mousedown.prevent @click="cmd('bold')" title="Bold">B</button>
      <button type="button" class="btn-ghost btn-sm underline" @mousedown.prevent @click="link()" title="Link">Link</button>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="cmd('insertUnorderedList')" title="Bullet list">• List</button>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="cmd('insertOrderedList')" title="Numbered list">1. List</button>
      <span class="mx-1 h-5 w-px bg-neutral-200"></span>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="cmd('undo')" title="Undo">↶</button>
      <button type="button" class="btn-ghost btn-sm" @mousedown.prevent @click="cmd('redo')" title="Redo">↷</button>
      <button type="button" class="btn-ghost btn-sm ml-auto" @mousedown.prevent @click="cmd('removeFormat')" title="Clear bold and links from the selection">Clear format</button>
    </div>
    <div class="mx-auto max-w-[760px] px-6 py-8">
      <div x-ref="box" contenteditable="true" spellcheck="true" class="article-editor"
           data-placeholder="Start writing, or paste your article from Google Docs, Word or ChatGPT. Start the first paragraph with the bold keyword and use H2 for each section."
           @input="changed()" @paste="onPaste($event)"></div>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-neutral-200 px-4 py-2 text-xs text-neutral-500">
      <span x-text="`${count.toLocaleString()} words, ${Math.max(1, Math.round(count / 200))} minute read`"></span>
      <span>Bold text at the very start of the first paragraph is the opening keyword. Each H2 starts a new section.</span>
    </div>
  </div>
  <ul x-show="warnings.length" class="mt-2 space-y-1 text-[13px] text-amber-700">
    <template x-for="w in warnings"><li x-text="w"></li></template>
  </ul>
  @error('intro_keyword')<p class="error">Start the first paragraph with the opening keyword in bold.</p>@enderror
  @error('intro_text')<p class="error">The article needs an opening paragraph.</p>@enderror
</div>
