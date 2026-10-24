import { img } from './images'

// Service areas in menu order. Each area groups the individual services listed in services.js.
// `single: true` would mean the area is itself a service page with no sub items (none at the moment).
export const categories = [
  {
    slug: 'web-and-software',
    name: 'Web & Software Development',
    short: 'Websites, web apps and internal systems built around how your business works.',
    description:
      'Your website is often the first thing a customer sees, and your internal tools shape how your team spends its day. We design, build and maintain both, using WordPress, WooCommerce, Laravel and React.',
    image: img('1498050108023-c5249f4df085'),
    heroImage: img('1517694712202-14dd9538aa97', 2000),
    services: ['web-development', 'custom-software'],
    overview: [
      "Some clients come to us for a new marketing website. Others need a portal for their customers, a dashboard for their managers or an API that connects two systems. Many need a mix of these over time, and it helps when the same team builds all of them.",
      "Every project starts with how your business works today. We then pick the simplest technology that will do the job well, build it in short stages that you review, and hand it over with training and documentation.",
    ],
    outcomes: [
      { title: "More enquiries and sales", text: "Clear pages and simple forms turn more visitors into customers." },
      { title: "Faster pages", text: "Sites that load quickly on phones and rank better in search." },
      { title: "Less manual work", text: "Internal tools that replace spreadsheets and repeated data entry." },
      { title: "Systems that connect", text: "Websites and apps that share data with the tools you already use." },
    ],
    faqs: [
      { q: "Do we need a website or custom software?", a: "If you need to attract and inform customers, start with a website. If you need to run a process, such as quotes, bookings or reporting, custom software is usually the better fit." },
      { q: "Which technologies do you use?", a: "Mostly WordPress and WooCommerce for websites, and Laravel and React for custom software, chosen to suit the project and your team." },
      { q: "Can you improve a site someone else built?", a: "Yes. We review what is there, fix the problems that matter most and only rebuild when it is the better option." },
      { q: "How long does a typical project take?", a: "Most business websites take four to ten weeks. Custom software usually takes two to four months, delivered in stages you can use along the way." },
    ],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'ai-and-automation',
    name: 'AI & Business Automation',
    short: 'Chatbots, AI agents, workflow automation and a 24/7 AI receptionist that take repetitive work off your team.',
    description:
      'Many hours in a week go into copying data, chasing leads and answering the same questions. We build AI assistants and automations that handle that work, connected to the tools you already use.',
    image: img('1677442136019-21780ecad995'),
    heroImage: img('1620712943543-bcc4688e7485', 2000),
    services: ['ai-automation'],
    overview: [
      "AI and automation are most useful when they take over the dull, repeated parts of a job. Answering the same questions, copying data between tools, chasing leads and sending reminders are all tasks software can handle reliably.",
      "We start by listing the tasks that take the most time each week, then automate the ones with the clearest return. Every assistant and workflow is tested with real examples and monitored after launch, so you can trust it.",
    ],
    outcomes: [
      { title: "Hours saved each week", text: "Routine tasks run automatically instead of waiting for someone to do them." },
      { title: "Faster replies", text: "Customers get answers to common questions at any hour." },
      { title: "Answers at any hour", text: "A 24/7 AI receptionist answers calls, books appointments and qualifies leads." },
      { title: "Cleaner data", text: "Information moves between tools without typing errors." },
    ],
    faqs: [
      { q: "Will AI replace our staff?", a: "That is not the goal. Automation removes repetitive work so your team can spend more time with customers and on work that needs judgement." },
      { q: "Which tasks are best to automate first?", a: "Tasks that happen often, follow the same steps and take real time, such as lead follow up, data entry, booking confirmations and routine questions." },
      { q: "Do we need a CRM for this?", a: "Not always, but most automations work better with one. We can set up a CRM as part of the project if you do not have one." },
      { q: "How quickly can we see results?", a: "Simple automations often go live within two to three weeks, and the time savings show up from the first week they run." },
    ],
    caseStudy: 'healthcare-patient-support',
  },
  {
    slug: 'crm-and-lead-generation',
    name: 'CRM & Lead Generation',
    short: 'CRM systems, lead capture and automated follow ups so no enquiry goes cold.',
    description:
      'Leads arrive through forms, calls, emails, ads and social media. We set up one place to capture them all, follow up on time and see which sources bring in real customers.',
    image: img('1556745753-b2904692b3cd'),
    heroImage: img('1553877522-43269d4ea984', 2000),
    services: ['crm-and-lead-management'],
    overview: [
      "Without a single place to track enquiries, follow ups are missed and good leads go cold. Sales teams spend their time searching inboxes instead of talking to customers.",
      "We set up an existing CRM or build a custom one, connect every lead source to it, and automate reminders and follow up messages. Reports show where leads come from and how many turn into sales.",
    ],
    outcomes: [
      { title: "No lost leads", text: "Every enquiry is captured and assigned automatically." },
      { title: "Faster follow up", text: "Reminders and messages go out on time without manual work." },
      { title: "A clear pipeline", text: "Stages that match your sales process show where each deal stands." },
      { title: "Known lead sources", text: "Reports show which channels bring in customers." },
    ],
    faqs: [
      { q: "Do you build a custom CRM or use an existing one?", a: "Either. We set up a ready made CRM when it fits your process, and build a custom one when you need features an off the shelf tool cannot handle." },
      { q: "Can you connect our website forms and ads?", a: "Yes. Leads from forms, calls, email, ads and social media can flow into the CRM automatically." },
      { q: "Will our team need training?", a: "We show your team how to use the CRM and leave written notes, so it becomes part of daily work." },
    ],
    caseStudy: 'healthcare-patient-support',
  },
  {
    slug: 'seo-and-ai-search',
    name: 'SEO & AI Search Optimization',
    short: 'SEO, AEO and GEO so customers find you on Google and in AI answers.',
    description:
      'Customers look for businesses on Google, Google Maps and in AI assistants such as ChatGPT, Gemini and Perplexity. We help your business show up in all of them, with monthly reporting.',
    image: img('1432888498266-38ffec3eaf0a'),
    heroImage: img('1639322537228-f710d846310a', 2000),
    services: ['seo', 'aeo-and-geo'],
    overview: [
      "Search is changing. People still use Google, but more of them now get answers straight from AI Overviews, voice assistants and chat tools. A business needs to be easy to find and easy to quote in both.",
      "We cover technical, on-page, local and off-page SEO, and add answer focused content and schema markup so AI tools understand your pages. A monthly report shows rankings, traffic and leads.",
    ],
    outcomes: [
      { title: "Be found in search", text: "Higher rankings in Google and Google Maps for the searches that matter." },
      { title: "Appear in AI answers", text: "Content that AI assistants can understand, quote and recommend." },
      { title: "More qualified traffic", text: "Visitors who are looking for exactly what you offer." },
      { title: "Clear monthly reports", text: "Rankings, traffic and leads in plain language." },
    ],
    faqs: [
      { q: "What is the difference between SEO, AEO and GEO?", a: "SEO helps you rank in search results. AEO and GEO help your business appear in direct answers from Google, voice assistants and AI tools such as ChatGPT and Gemini." },
      { q: "How long does SEO take to show results?", a: "Technical fixes can help within weeks. Rankings for competitive searches usually build over several months of steady work." },
      { q: "Do you work on Google Business Profile?", a: "Yes. Local SEO and Google Business Profile optimization are part of the service." },
    ],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'digital-marketing-and-ads',
    name: 'Digital Marketing & Paid Ads',
    short: 'Social media management and Google and Meta ads that bring in leads and sales.',
    description:
      'A steady social presence builds trust, and paid ads bring in customers quickly when they are set up and tracked properly. We run both and report on what each one returns.',
    image: img('1611162617213-7d7a39e9b1d7'),
    heroImage: img('1557838923-2985c318be48', 2000),
    services: ['social-media-management', 'ads-management'],
    overview: [
      "Social media works best when it is consistent, and ads work best when every click and sale is tracked. Both take time that most small teams do not have.",
      "We plan and create your social content, schedule posts and reply to your community. We also run Google Ads and Meta Ads with retargeting and conversion tracking, so budget moves to what works.",
    ],
    outcomes: [
      { title: "A steady social presence", text: "Regular, on brand posts that build trust and bring enquiries." },
      { title: "Ads that pay back", text: "Campaigns managed against cost per lead and return on spend." },
      { title: "Every sale tracked", text: "Conversion tracking for calls, forms and purchases." },
      { title: "Monthly reporting", text: "What was spent, what it returned and what we will do next." },
    ],
    faqs: [
      { q: "Which platforms do you manage?", a: "Mainly Facebook, Instagram, LinkedIn and TikTok for social media, and Google Ads and Meta Ads for paid campaigns, chosen to suit your customers." },
      { q: "Is there a minimum ad budget?", a: "No fixed minimum. We recommend a budget based on your market and goals, and explain it before anything is spent." },
      { q: "Is there a minimum contract?", a: "Most marketing work runs month to month after an initial setup period. The terms are written in every proposal." },
    ],
    caseStudy: 'retail-storefront-rebuild',
  },
]

export const getCategory = (slug) => categories.find((c) => c.slug === slug)
export const multiCategories = categories.filter((c) => !c.single)
