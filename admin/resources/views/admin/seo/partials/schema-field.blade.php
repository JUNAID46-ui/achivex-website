{{-- Custom JSON-LD schema box with ready templates. Expects $name, $value (array or null), $pageUrl, $pageTitle. --}}
@php
  $initial = old($name, $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '');
@endphp
<div x-data="schemaBox(@js($initial), @js($pageUrl), @js($pageTitle))" class="space-y-3">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <label for="f-{{ $name }}" class="label mb-0">Schema (JSON-LD)</label>
    <span class="text-xs" :class="status.ok ? 'text-green-600' : 'text-red-600'" x-text="status.text"></span>
  </div>
  <p class="help mt-0">The website already adds Organization and Breadcrumb schema, plus Article and FAQ schema on blog posts and Service and FAQ schema on service pages. Add extra schema here; it is placed on this page next to the automatic ones.</p>
  <div class="flex flex-wrap gap-1.5">
    <template x-for="t in Object.keys(templates)" :key="t">
      <button type="button" class="btn-secondary btn-sm" @click="add(t)" x-text="'+ ' + t"></button>
    </template>
    <button type="button" class="btn-ghost btn-sm text-red-600" x-show="text.trim()" @click="text = ''">Clear</button>
  </div>
  <textarea id="f-{{ $name }}" name="{{ $name }}" x-model="text" rows="12" spellcheck="false" class="input font-mono text-[12.5px] leading-relaxed" placeholder='{ "@@context": "https://schema.org", "@type": "..." }'></textarea>
  <p class="help mt-0">Click a template to add it, then replace the example values. You can add more than one; they are kept as a list. Test the page later in Google's Rich Results Test.</p>
  @error($name)<p class="error">{{ $message }}</p>@enderror
</div>

@once
@push('scripts')
<script>
  function schemaBox(initial, url, title) {
    const site = { name: 'AchiveX LLC', url: url.replace(/^(https?:\/\/[^/]+).*$/, '$1') + '/' }
    return {
      text: initial || '',
      templates: {
        Article: () => ({ '@@context': 'https://schema.org', '@type': 'Article', headline: title, url, author: { '@type': 'Organization', name: site.name }, publisher: { '@type': 'Organization', name: site.name }, datePublished: new Date().toISOString().slice(0, 10), image: '' }),
        FAQ: () => ({ '@@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: [{ '@type': 'Question', name: 'Your question?', acceptedAnswer: { '@type': 'Answer', text: 'Your answer.' } }] }),
        Service: () => ({ '@@context': 'https://schema.org', '@type': 'Service', name: title, url, serviceType: title, provider: { '@type': 'Organization', name: site.name, url: site.url }, areaServed: 'United States' }),
        LocalBusiness: () => ({ '@@context': 'https://schema.org', '@type': 'ProfessionalService', name: site.name, url: site.url, telephone: '+1 307 622 1140', email: 'info@achivex.com', address: { '@type': 'PostalAddress', addressRegion: 'WY', addressCountry: 'US' } }),
        HowTo: () => ({ '@@context': 'https://schema.org', '@type': 'HowTo', name: title, step: [{ '@type': 'HowToStep', name: 'Step one', text: 'What to do first.' }] }),
        Product: () => ({ '@@context': 'https://schema.org', '@type': 'Product', name: title, description: '', brand: { '@type': 'Brand', name: site.name }, offers: { '@type': 'Offer', price: '0.00', priceCurrency: 'USD', url } }),
        Event: () => ({ '@@context': 'https://schema.org', '@type': 'Event', name: title, startDate: new Date().toISOString().slice(0, 10), eventAttendanceMode: 'https://schema.org/OnlineEventAttendanceMode', location: { '@type': 'VirtualLocation', url }, organizer: { '@type': 'Organization', name: site.name, url: site.url } }),
        Video: () => ({ '@@context': 'https://schema.org', '@type': 'VideoObject', name: title, description: '', thumbnailUrl: '', uploadDate: new Date().toISOString().slice(0, 10), contentUrl: '' }),
      },
      get status() {
        if (!this.text.trim()) return { ok: true, text: 'No custom schema' }
        try {
          const v = JSON.parse(this.text)
          const list = Array.isArray(v) ? v : [v]
          if (!list.length || list.some((s) => !s || typeof s !== 'object' || !s['@type'])) return { ok: false, text: 'Each item needs an "@type"' }
          return { ok: true, text: 'Valid JSON-LD: ' + list.map((s) => s['@type']).join(', ') }
        } catch (e) {
          return { ok: false, text: 'Not valid JSON yet' }
        }
      },
      add(name) {
        const item = this.templates[name]()
        let list = []
        try { const v = this.text.trim() ? JSON.parse(this.text) : []; list = Array.isArray(v) ? v : [v] } catch (e) { list = [] }
        list.push(item)
        this.text = JSON.stringify(list.length === 1 ? list[0] : list, null, 2)
      },
    }
  }
</script>
@endpush
@endonce
