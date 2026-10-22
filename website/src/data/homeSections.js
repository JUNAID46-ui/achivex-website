import { img } from './images'

// Home page sections, editable from the admin Page Builder (page_sections table).
// `key` picks the layout, `data` holds the text. Order and visibility come from `sort` and `visible`.
export const homeSections = [
  {
    key: 'hero', label: 'Hero', sort: 0, visible: true,
    data: {
      pill: 'Marketing and Software Solutions',
      heading: 'Our Tailored Services',
      text: 'From strategy and data to cloud, security and daily operations, we put together the right mix of services to help your business grow.',
      image: '/images/hero-robot.webp',
      imageAlt: 'Robotic hand in front of a glowing purple ring',
      buttonText: 'Book Free Consultation',
      buttonLink: '/contact-us/',
    },
  },
  {
    key: 'intro', label: 'Intro statement', sort: 1, visible: true,
    data: {
      before: 'We bring strategy, data, cloud, security and day to day business support together, so growing companies get',
      highlight: 'one dependable partner',
      after: 'instead of a long list of vendors.',
      linkText: 'More about AchiveX',
      linkTo: '/about-us/',
    },
  },
  {
    key: 'explorer', label: 'What we do (service explorer)', sort: 2, visible: true,
    data: { title: 'What we do', text: 'Eight services in three areas, one team. Pick one or combine several.' },
  },
  {
    key: 'why', label: 'Why AchiveX', sort: 3, visible: true,
    data: {
      title: 'Why AchiveX',
      text: 'A practical partner that explains every step and focuses on work that makes a real difference.',
      items: [
        { title: 'Your business comes first', text: 'Every engagement starts with how your company works today. Recommendations follow from that, not from a product list.' },
        { title: 'One partner, fewer handoffs', text: 'Strategy, data, cloud, security and process support sit under one roof, so nothing falls between vendors.' },
        { title: 'Plain language, honest estimates', text: 'Scope, cost and risk are written so your whole leadership team can follow them.' },
        { title: 'Built to hand over', text: 'We document what we build and train your people, so you are never locked in.' },
      ],
    },
  },
  {
    key: 'spotlights', label: 'Service spotlights', sort: 4, visible: true,
    data: {
      items: [
        {
          title: 'AI and automation that save real time',
          text: 'We point AI at the repetitive work that slows your team down, from reading documents to answering calls, and connect your tools so leads never slip through.',
          points: ['Chatbots and assistants that answer from your own data', 'A 24/7 AI receptionist for calls and bookings', 'CRM follow ups that run on their own'],
          image: img('1526628953301-3e589a6a8b74', 1400),
          alt: 'Screens showing data dashboards',
          to: '/services/ai-and-automation/',
          link: 'Explore AI and Automation',
        },
        {
          title: 'Growth you can measure',
          text: 'SEO, AI search visibility, social media and ads, run together and reported every month, so you can see which channels bring in customers.',
          points: ['Technical, local and on-page SEO', 'Visibility in ChatGPT, Gemini and AI Overviews', 'Google and Meta Ads with conversion tracking'],
          image: img('1558494949-ef010cbdcc31', 1400),
          alt: 'Marketing results on a laptop screen',
          to: '/services/growth-marketing/',
          link: 'Explore Growth Marketing',
        },
      ],
    },
  },
  {
    key: 'process', label: 'How we work', sort: 5, visible: true,
    data: {
      title: 'How we work',
      text: 'The same four steps on every engagement, whatever its size.',
      steps: [
        { title: 'Consult', text: 'A free conversation about your goals, constraints and current setup.' },
        { title: 'Plan', text: 'A written proposal with scope, timeline, team and cost.' },
        { title: 'Deliver', text: 'Short cycles with regular demos and progress updates.' },
        { title: 'Support', text: 'Handover, training and optional ongoing support.' },
      ],
    },
  },
  {
    key: 'industries', label: 'Industries', sort: 6, visible: true,
    data: { title: 'Industries we serve', text: 'Each sector has its own rules and pressures. We adapt our services to fit them.' },
  },
  {
    key: 'featured_case', label: 'Featured case study', sort: 8, visible: true,
    data: { caseSlug: 'retail-storefront-rebuild' },
  },
  {
    key: 'insights', label: 'Latest insights', sort: 9, visible: true,
    data: { title: 'Latest insights', text: 'Case studies, practical articles and guides from our team.' },
  },
  {
    key: 'faq', label: 'Common questions', sort: 11, visible: true,
    data: {
      title: 'Common questions',
      text: 'Do not see your question here? Call or email us and we will answer it directly.',
      items: [
        { q: 'What size of business do you work with?', a: 'We work with small and mid sized companies as well as larger teams that need extra capacity. The scope of each engagement is set to fit your size and budget.' },
        { q: 'Can we start with one service and add more later?', a: 'Yes. Many clients start with a single need, such as a new website or an SEO plan, and add other services as they grow.' },
        { q: 'Do you work with clients outside Wyoming?', a: 'Yes. Our headquarters is in Wyoming, USA, and engagements can be delivered remotely. Contact us to discuss your location and needs.' },
        { q: 'How does the free consultation work?', a: 'It is a conversation with our team about your goals and current setup. There is no obligation, and if we are not the right fit we will tell you.' },
        { q: 'Who owns the work you deliver?', a: 'Ownership terms are set in each client agreement. In most cases code, accounts and documentation are held in your name.' },
      ],
    },
  },
  { key: 'contact_strip', label: 'Contact strip', sort: 12, visible: true, data: {} },
  {
    key: 'cta', label: 'Call to action', sort: 10, visible: true,
    data: {
      title: 'Have a project in mind?',
      text: 'Book a free consultation and we will talk through your goals, your current setup and where we can help.',
      button: 'Book a Free Consultation',
    },
  },
]
