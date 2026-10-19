import { img } from './images'

// Service categories in menu order.
// `single: true` means the category is itself a service page with no sub items.
export const categories = [
  {
    slug: 'digital',
    name: 'Digital',
    short: 'Strategy, commerce and business applications that fit the way your teams already work.',
    description:
      'Digital work starts with a clear picture of where you are and where you want to be. We help you set priorities, build the online channels your customers use and configure the business applications your staff rely on every day.',
    image: img('1531482615713-2afd69097998'),
    heroImage: img('1517245386807-bb43f82c33c4', 2000),
    services: ['digital-consulting-and-strategy', 'digital-commerce', 'business-applications'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'data-and-analytics',
    name: 'Data and Analytics',
    short: 'Reliable data foundations, useful reporting and practical uses of AI.',
    description:
      'Good decisions need data people can trust. We clean up and modernise data platforms, build reporting that answers real questions, connect operational data from devices and systems, and help you apply generative AI where it makes sense.',
    image: img('1551288049-bebda4e38f71'),
    heroImage: img('1526628953301-3e589a6a8b74', 2000),
    services: ['data-modernization', 'advanced-analytics', 'connected-intelligence', 'data-management', 'generative-ai'],
    caseStudy: 'finance-reporting-consolidation',
  },
  {
    slug: 'cloud',
    name: 'Cloud',
    short: 'Migration, cloud native development and day to day operations.',
    description:
      'Moving to the cloud is only the first step. We plan and carry out migrations, build and integrate applications designed for cloud platforms, and look after the environment once it is live so your team can focus on the product.',
    image: img('1544197150-b99a580bb7a8'),
    heroImage: img('1558346490-a72e53ae2d4f', 2000),
    services: ['cloud-operations-and-migration', 'cloud-application-development-and-integration', 'managed-services'],
    caseStudy: 'logistics-cloud-migration',
  },
  {
    slug: 'digital-infrastructure-services',
    name: 'Digital Infrastructure Services',
    single: true,
    short: 'Networks, end user computing and data center support.',
    image: img('1597852074816-d933c7d2b988'),
  },
  {
    slug: 'security',
    name: 'Security',
    single: true,
    short: 'Assessments, monitoring and practical controls to reduce risk.',
    image: img('1550751827-4bd374c3f58b'),
  },
  {
    slug: 'emerging-technologies',
    name: 'Emerging Technologies',
    single: true,
    short: 'Careful evaluation of new technology before you commit budget.',
    image: img('1535223289827-42f1e9919769'),
  },
  {
    slug: 'business-process-services',
    name: 'Business Process Services',
    short: 'Dedicated teams for customer support, marketing, finance, legal and hiring.',
    description:
      'Some work is essential but does not need to sit inside your core team. We provide trained people and clear processes for customer contact, marketing, accounting, legal support, staffing and recruitment, with reporting so you always know what is happening.',
    image: img('1600880292203-757bb62b4baf'),
    heroImage: img('1556761175-b413da4baf72', 2000),
    services: [
      'contact-center',
      'digital-marketing',
      'finance-and-accounting',
      'staff-augmentation',
      'legal-process-outsourcing',
      'recruitment-process-outsourcing',
    ],
    caseStudy: 'healthcare-patient-support',
  },
]

export const getCategory = (slug) => categories.find((c) => c.slug === slug)
export const multiCategories = categories.filter((c) => !c.single)
