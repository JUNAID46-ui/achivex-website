import { img } from './images'

// Whitepapers and eBooks. Set `file` to the PDF path (for example '/downloads/cloud-checklist.pdf')
// once the document exists in /public/downloads.
export const whitepapers = [
  {
    slug: 'cloud-migration-planning-guide',
    title: 'Cloud Migration Planning Guide',
    type: 'eBook',
    description: 'A practical walk through of discovery, wave planning and cutover for small and mid sized teams.',
    cover: img('1532012197267-da84d127e765', 900),
    inside: [
      'How to build an application and dependency inventory',
      'Choosing between rehost, replatform and refactor',
      'A sample wave plan and cutover checklist',
      'Cost questions to answer before you move',
    ],
    file: null,
  },
  {
    slug: 'data-quality-starter-kit',
    title: 'Data Quality Starter Kit',
    type: 'Whitepaper',
    description: 'Simple routines for measuring and improving data quality without a large governance programme.',
    cover: img('1481627834876-b7833e8f5570', 900),
    inside: [
      'The six dimensions of data quality explained plainly',
      'How to pick the first data area to fix',
      'Assigning data owners in a small organisation',
      'A template for a monthly quality review',
    ],
    file: null,
  },
  {
    slug: 'generative-ai-use-case-workbook',
    title: 'Generative AI Use Case Workbook',
    type: 'eBook',
    description: 'Questions and scoring sheets to help you choose sensible first projects for generative AI.',
    cover: img('1512820790803-83ca734da794', 900),
    inside: [
      'Spotting tasks that suit language models',
      'Scoring value, risk and data readiness',
      'Planning a small proof of concept',
      'Questions to ask model providers about data use',
    ],
    file: null,
  },
  {
    slug: 'small-business-security-baseline',
    title: 'Small Business Security Baseline',
    type: 'Whitepaper',
    description: 'The core controls that address common attacks, mapped to the CIS Controls Implementation Group 1.',
    cover: img('1543002588-bfa74002ed7e', 900),
    inside: [
      'Why identity and multi factor authentication come first',
      'Patching and asset inventory routines',
      'Backup testing that actually proves recovery',
      'A one page incident response starter plan',
    ],
    file: null,
  },
]

export const getWhitepaper = (slug) => whitepapers.find((w) => w.slug === slug)
