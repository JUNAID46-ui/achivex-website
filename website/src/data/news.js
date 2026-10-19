import { img } from './images'

// Newsroom announcements. The live site reads these from the database (news_posts table).
export const news = [
  {
    slug: 'achivex-launches-new-website',
    date: '2026-10-19',
    title: 'AchiveX LLC launches its new website',
    summary: 'The new site brings all AchiveX services, insights and contact options together in one place.',
    image: img('1504711434969-e33886168f5c'),
    body: [
      'AchiveX LLC has launched its new website at achivex.com. The site describes the full range of services the company offers, from digital strategy and data to cloud, security and business process support.',
      'Each service now has its own page with a plain description of what is included, how the work is carried out and answers to common questions. Visitors can also read articles, download free guides and book a free consultation.',
      'Questions about the new site or any of the services can be sent to info@achivex.com or by phone on +1 307 622 1140.',
    ],
  },
  {
    slug: 'free-guides-library',
    date: '2026-10-19',
    title: 'Free guides now available to download',
    summary: 'Four practical guides on cloud migration, data quality, generative AI and security are now free to download.',
    image: img('1495020689067-958852a7765e'),
    body: [
      'AchiveX has published a library of free guides written for small and mid sized businesses. The first four cover cloud migration planning, data quality, choosing generative AI use cases and a basic security baseline.',
      'Each guide focuses on practical steps a team can take without a large budget. They are available from the Whitepapers and eBooks section of the website.',
    ],
  },
  {
    slug: 'seven-service-areas',
    date: '2026-10-19',
    title: 'AchiveX outlines its seven service areas',
    summary: 'Digital, Data and Analytics, Cloud, Digital Infrastructure, Security, Emerging Technologies and Business Process Services.',
    image: img('1540575467063-178a50c2df87'),
    body: [
      'AchiveX organises its work into seven service areas: Digital, Data and Analytics, Cloud, Digital Infrastructure Services, Security, Emerging Technologies and Business Process Services.',
      'Clients can start with a single service and add others as their needs grow. Every engagement begins with a free consultation to understand goals, current systems and budget.',
    ],
  },
]

export const getNews = (slug) => news.find((n) => n.slug === slug)
