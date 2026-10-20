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
      image: img('1460925895917-afdab827c52f', 1600),
      imageAlt: 'Laptop showing marketing analytics charts',
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
    data: { title: 'What we do', text: 'Seven service areas, one team. Pick one area or combine several.' },
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
          title: 'Data and AI, used carefully',
          text: 'Clean, well governed data comes first. From there we build reporting people trust and apply generative AI only where it saves real time.',
          points: ['Data platforms that replace manual spreadsheets', 'Dashboards built around real decisions', 'Generative AI pilots with clear success measures'],
          image: img('1526628953301-3e589a6a8b74', 1400),
          alt: 'Screens showing data dashboards',
          to: '/services/data-and-analytics/',
          link: 'Explore Data and Analytics',
        },
        {
          title: 'Cloud you can run with confidence',
          text: 'We plan migrations workload by workload, test every cutover and keep the environment healthy once it is live.',
          points: ['Migration plans with tested rollback', 'Monitoring, backups and patching', 'Cost reviews that keep bills predictable'],
          image: img('1558494949-ef010cbdcc31', 1400),
          alt: 'Server room with network cabling',
          to: '/services/cloud/',
          link: 'Explore Cloud',
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
    key: 'platforms', label: 'Platforms', sort: 7, visible: true,
    data: { title: 'Platforms we work with', note: 'Product names are trademarks of their owners.' },
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
    key: 'faq', label: 'Common questions', sort: 10, visible: true,
    data: {
      title: 'Common questions',
      text: 'Do not see your question here? Call or email us and we will answer it directly.',
      items: [
        { q: 'What size of business do you work with?', a: 'We work with small and mid sized companies as well as larger teams that need extra capacity. The scope of each engagement is set to fit your size and budget.' },
        { q: 'Can we start with one service and add more later?', a: 'Yes. Many clients start with a single need, such as a cloud migration or contact center support, and add other services as they grow.' },
        { q: 'Do you work with clients outside Wyoming?', a: 'Yes. Our headquarters is in Wyoming, USA, and engagements can be delivered remotely. Contact us to discuss your location and needs.' },
        { q: 'How does the free consultation work?', a: 'It is a conversation with our team about your goals and current setup. There is no obligation, and if we are not the right fit we will tell you.' },
        { q: 'Who owns the work you deliver?', a: 'Ownership terms are set in each client agreement. In most cases code, accounts and documentation are held in your name.' },
      ],
    },
  },
  { key: 'contact_strip', label: 'Contact strip', sort: 11, visible: true, data: {} },
  {
    key: 'cta', label: 'Call to action', sort: 12, visible: true,
    data: {
      title: 'Have a project in mind?',
      text: 'Book a free consultation and we will talk through your goals, your current setup and where we can help.',
      button: 'Book a Free Consultation',
    },
  },
]
