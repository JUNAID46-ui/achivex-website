import { img } from './images'

// Service areas in menu order. Each area groups the individual services listed in services.js.
// `single: true` would mean the area is itself a service page with no sub items (none at the moment).
export const categories = [
  {
    slug: 'web-and-software',
    name: 'Web and Software',
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
    name: 'AI and Automation',
    short: 'AI, automation and CRM systems that take repetitive work off your team.',
    description:
      'Many hours in a week go into copying data, chasing leads and answering the same questions. We build AI systems, automations and CRM setups that handle that work and keep every lead moving.',
    image: img('1677442136019-21780ecad995'),
    heroImage: img('1620712943543-bcc4688e7485', 2000),
    services: ['ai-automation', 'crm-and-lead-management'],
    overview: [
      "AI and automation are most useful when they take over the dull, repeated parts of a job. Answering the same questions, copying data between tools, chasing leads and sending reminders are all tasks software can handle reliably.",
      "We start by listing the tasks that take the most time each week, then automate the ones with the clearest return. Every assistant and workflow is tested with real examples and monitored after launch, so you can trust it.",
    ],
    outcomes: [
      { title: "Hours saved each week", text: "Routine tasks run automatically instead of waiting for someone to do them." },
      { title: "Faster replies", text: "Customers get answers to common questions at any hour." },
      { title: "No lost leads", text: "Every enquiry is captured, assigned and followed up on time." },
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
    slug: 'growth-marketing',
    name: 'Growth Marketing',
    short: 'SEO, AI search visibility, social media and ads that bring in qualified leads.',
    description:
      'Customers find businesses through Google, AI assistants, social media and ads. We work across all of them, with reporting that shows what is bringing in leads and what is not.',
    image: img('1557838923-2985c318be48'),
    heroImage: img('1551288049-bebda4e38f71', 2000),
    services: ['seo', 'aeo-and-geo', 'social-media-management', 'ads-management'],
    overview: [
      "Good marketing brings in people who are ready to buy, not just visitors. We focus on the channels where your customers already look for businesses like yours: Google search, Google Maps, AI assistants, social media and paid ads.",
      "Every channel is measured against enquiries and sales. You receive a plain monthly report that shows what was done, what it cost and what it returned, so decisions about budget are easy to make.",
    ],
    outcomes: [
      { title: "Be found in search", text: "Higher rankings in Google and Google Maps for the searches that matter." },
      { title: "Appear in AI answers", text: "Content that AI assistants can understand, quote and recommend." },
      { title: "A steady social presence", text: "Regular, on brand posts that build trust and bring enquiries." },
      { title: "Ads that pay back", text: "Campaigns managed against cost per lead and return on spend." },
    ],
    faqs: [
      { q: "Which marketing channel should we start with?", a: "Usually search, because people looking for your service are already close to buying. We look at your market and budget before recommending a mix." },
      { q: "How do you report on results?", a: "A monthly report covering traffic, rankings, enquiries, sales and spend for each channel, with notes on what we will do next." },
      { q: "Is there a minimum contract?", a: "Most marketing work runs month to month after an initial setup period. We explain the terms clearly in every proposal." },
      { q: "Can you work with our existing marketing team?", a: "Yes. We can take on one channel, support your team with strategy and reporting, or run the full programme, whichever suits you." },
    ],
    caseStudy: 'retail-storefront-rebuild',
  },
]

export const getCategory = (slug) => categories.find((c) => c.slug === slug)
export const multiCategories = categories.filter((c) => !c.single)
