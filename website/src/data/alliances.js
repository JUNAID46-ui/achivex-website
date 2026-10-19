import { img } from './images'

// Alliance list for the About menu and alliance pages.
// Remove any entry that does not apply and it disappears from the menu and routes.
// Logos are placeholders: drop an official logo file in /public/logos and set `logo`.
export const alliances = [
  {
    slug: 'microsoft',
    name: 'Microsoft',
    logo: null,
    image: img('1633419461186-7d40a38105ec'),
    intro: 'Many of our clients already run their day on Microsoft tools. We help them get more out of what they own before suggesting anything new.',
    work: [
      { title: 'Microsoft 365 setup and governance', text: 'Tenant configuration, user and license management, Teams and SharePoint structure, and sensible retention settings.' },
      { title: 'Azure hosting and migration', text: 'Moving workloads to Azure, setting up resource groups and access policies, and keeping an eye on monthly spend.' },
      { title: 'Power Platform and Dynamics 365', text: 'Building small apps and automated flows with Power Apps and Power Automate, and configuring Dynamics 365 for sales and service teams.' },
      { title: 'Reporting with Power BI', text: 'Connecting data sources, modeling the data and publishing dashboards that people actually open.' },
    ],
    services: ['business-applications', 'cloud-operations-and-migration', 'advanced-analytics'],
  },
  {
    slug: 'sap',
    name: 'SAP',
    logo: null,
    image: img('1460925895917-afdab827c52f'),
    intro: 'SAP sits at the center of finance, supply chain and operations for many companies. Our work focuses on the data and processes around it.',
    work: [
      { title: 'Process review', text: 'Mapping how finance, procurement and inventory processes run today and where SAP is or is not being used as intended.' },
      { title: 'Data extraction and reporting', text: 'Pulling SAP data into a reporting layer so teams can analyse it without overloading the core system.' },
      { title: 'Integration', text: 'Connecting SAP with ecommerce, CRM and warehouse systems through well documented interfaces.' },
      { title: 'Support', text: 'Ongoing help for users, change requests and small enhancements.' },
    ],
    services: ['business-applications', 'data-management', 'cloud-application-development-and-integration'],
  },
  {
    slug: 'temenos',
    name: 'Temenos',
    logo: null,
    image: img('1563986768609-322da13575f3'),
    intro: 'Temenos provides core banking and digital banking software. We support financial institutions with the surrounding data, testing and integration work.',
    work: [
      { title: 'Integration support', text: 'Connecting core banking modules with payment gateways, channels and internal tools.' },
      { title: 'Data migration', text: 'Cleaning, mapping and validating customer and account data before and after a move.' },
      { title: 'Testing', text: 'Functional and regression test planning around releases and configuration changes.' },
      { title: 'Reporting', text: 'Regulatory and management reporting built on reliable extracts.' },
    ],
    services: ['data-modernization', 'cloud-application-development-and-integration', 'security'],
  },
  {
    slug: 'salesforce',
    name: 'Salesforce',
    logo: null,
    image: img('1552664730-d307ca884978'),
    intro: 'Salesforce works best when it reflects how your sales and service teams really operate. We configure it around your process, not the other way round.',
    work: [
      { title: 'Sales Cloud and Service Cloud setup', text: 'Objects, fields, page layouts, permissions and automation set up for your pipeline and case handling.' },
      { title: 'Data cleanup', text: 'Removing duplicates, fixing ownership and setting validation rules so the data stays tidy.' },
      { title: 'Integrations', text: 'Linking Salesforce with marketing tools, ERP systems and your website forms.' },
      { title: 'Admin support', text: 'A named admin resource for change requests, reports and user training.' },
    ],
    services: ['business-applications', 'digital-marketing', 'data-management'],
  },
  {
    slug: 'ibm',
    name: 'IBM',
    logo: null,
    image: img('1558494949-ef010cbdcc31'),
    intro: 'Some organisations rely on IBM infrastructure and software that has been in place for years. We help keep it stable and plan what comes next.',
    work: [
      { title: 'Infrastructure assessment', text: 'Reviewing existing servers, storage and middleware to understand risk and cost.' },
      { title: 'Hybrid cloud planning', text: 'Deciding what stays on premises and what moves to the cloud, and in what order.' },
      { title: 'Data and AI tooling', text: 'Evaluating IBM data and AI products against your use case and existing stack.' },
      { title: 'Operations support', text: 'Monitoring, patching and incident response for supported systems.' },
    ],
    services: ['digital-infrastructure-services', 'managed-services', 'emerging-technologies'],
  },
  {
    slug: 'aws',
    name: 'AWS',
    logo: null,
    image: img('1451187580459-43490279c0fa'),
    intro: 'Amazon Web Services offers a very wide set of building blocks. We help teams pick the right ones and run them with care.',
    work: [
      { title: 'Account and landing zone setup', text: 'Organisations, accounts, identity and access, logging and guardrails configured from day one.' },
      { title: 'Migration', text: 'Rehosting or refactoring applications to EC2, containers or serverless services based on what makes sense.' },
      { title: 'Data platforms', text: 'Storage, pipelines and analytics using services such as S3, Glue, Redshift and Athena.' },
      { title: 'Cost and operations', text: 'Tagging, budgets, rightsizing and monitoring so the bill stays predictable.' },
    ],
    services: ['cloud-operations-and-migration', 'data-modernization', 'managed-services'],
  },
]
