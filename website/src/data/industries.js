import { img } from './images'

// Industry pages. Every text field is editable in the admin under Pages (Edit Page on each industry).
// intro: paragraphs under the hero. challenges: cards. helpParagraphs and helpList: the image section.
export const industries = [
  {
    slug: 'financial-services',
    name: 'Financial Services',
    image: img('1611974789855-9c2a0a7236a3'),
    summary: 'Banks, credit unions, lenders, accountants and fintech teams that need reliable systems, clean data and strong controls.',
    intro: [
      'Financial services firms run on trust. Clients expect accurate statements, quick answers and a website that feels as secure as the business behind it. Yet many teams still re key data between systems by hand.',
      'We help lenders, credit unions, advisory firms, accountants and fintech startups modernise the digital side of their business without putting compliance or client data at risk.',
    ],
    challenges: [
      { title: 'Manual data entry', text: 'Applications, onboarding forms and documents are often copied by hand between systems, which slows service and creates errors.' },
      { title: 'Slow client response', text: 'Simple questions about rates, documents or status tie up staff who should be working on higher value tasks.' },
      { title: 'Security expectations', text: 'Clients and regulators expect careful handling of personal and financial data at every step.' },
      { title: 'Lead quality', text: 'Marketing spend can attract plenty of visitors but too few people who are ready to apply or book a meeting.' },
    ],
    helpTitle: 'How we help financial services teams',
    helpParagraphs: [
      'We start by mapping how a client moves from first enquiry to an active account. That shows where time is lost and where an automation, a better form or a clearer page would make the biggest difference.',
      'Everything we build follows the security practices your firm already uses. We agree on how data is stored, who can access it and how it is backed up before any work begins, and we document the setup so your compliance team can review it.',
    ],
    helpList: [
      'Secure websites and client portals',
      'Automated onboarding and document collection',
      'CRM setup that tracks every application and follow up',
      'SEO and paid search focused on qualified enquiries',
    ],
    focus: ['Client portals', 'Onboarding automation', 'CRM and pipeline', 'Search and ads'],
    services: ['custom-software', 'crm-and-lead-management', 'ai-automation'],
    faqs: [
      { q: 'Can you work within our compliance requirements?', a: 'Yes. We follow the data handling, access and retention rules your firm sets, and we document how each system is configured so your compliance team can review it.' },
      { q: 'Do you build client portals?', a: 'We build secure portals where clients can upload documents, check the status of an application and message your team, connected to the systems you already use.' },
      { q: 'Can automation reduce our onboarding time?', a: 'In most firms, yes. Forms, document requests and reminders can run automatically, and data can flow into your CRM without being typed in again.' },
    ],
  },
  {
    slug: 'healthcare',
    name: 'Healthcare',
    image: img('1576091160399-112ba8d25d1d'),
    summary: 'Clinics, practices, therapists and health service providers that handle sensitive records and busy patient operations.',
    intro: [
      'Healthcare teams spend a large part of every day on calls, bookings, reminders and paperwork. Patients want to find information quickly, book online and get answers outside office hours, while staff need systems that are simple and reliable.',
      'We help clinics, dental and medical practices, therapists and health service providers improve the patient journey online. That includes websites that make booking easy, automations that send reminders and collect forms before a visit, AI assistants that answer common questions, and local SEO that helps new patients find you.',
    ],
    challenges: [
      { title: 'Busy phone lines', text: 'Front desk teams answer the same questions about hours, insurance and directions many times a day.' },
      { title: 'Missed appointments', text: 'No shows cost time and revenue when reminders and confirmations are handled manually.' },
      { title: 'Paperwork before visits', text: 'Intake forms completed on paper or at the desk slow down every appointment.' },
      { title: 'Being found locally', text: 'Patients search on Google Maps and AI assistants, and practices with weak listings lose them to nearby competitors.' },
    ],
    helpTitle: 'How we help healthcare providers',
    helpParagraphs: [
      'We look at the patient journey from the first search to the follow up after a visit, then improve the steps that cause the most friction for patients and staff.',
      'Privacy comes first. We work within the rules your organisation follows, such as HIPAA for providers in the United States, keep patient information out of tools that are not approved for it, and agree on data handling before a project starts.',
    ],
    helpList: [
      'Websites with clear service pages and online booking',
      'Automated reminders, confirmations and intake forms',
      'AI assistants for common, non clinical questions',
      'Local SEO and Google Business Profile management',
    ],
    focus: ['Online booking', 'Patient reminders', 'Intake automation', 'Local SEO'],
    services: ['ai-automation', 'web-development', 'seo'],
    faqs: [
      { q: 'Can an AI assistant answer patient questions?', a: 'It can answer common, non clinical questions such as opening hours, location, accepted insurance and how to book. Clinical questions are always passed to your staff.' },
      { q: 'How do you handle patient data?', a: 'We agree on data handling before we start and keep patient information inside systems approved for it. We do not use patient data to train AI tools.' },
      { q: 'Will local SEO help us get more patients?', a: 'For most practices it is one of the most effective channels. A complete Google Business Profile, accurate listings and clear service pages help nearby patients choose you.' },
    ],
  },
  {
    slug: 'retail-and-ecommerce',
    name: 'Retail and Ecommerce',
    image: img('1441986300917-64674bd600d8'),
    summary: 'Store owners and online sellers who want better storefronts, smarter marketing and fewer manual tasks.',
    intro: [
      'Online shoppers compare quickly and leave just as quickly. A slow product page, a confusing checkout or an unanswered question can send them to a competitor in seconds. Behind the scenes, store owners juggle orders, stock, returns and marketing across several tools.',
      'We help retailers and ecommerce brands build faster storefronts, connect their systems and grow sales through search, social media and paid ads. Most of our retail work is on WooCommerce and custom builds, with automations that keep orders, inventory and customer messages in step.',
    ],
    challenges: [
      { title: 'Abandoned carts', text: 'Shoppers leave at checkout when pages load slowly, costs appear late or payment options are limited.' },
      { title: 'Disconnected tools', text: 'Orders, stock and customer records live in separate systems that do not update each other.' },
      { title: 'Rising ad costs', text: 'Paid campaigns get more expensive each year, so wasted spend hurts margins quickly.' },
      { title: 'Repeat questions', text: 'Customers ask about delivery times, returns and order status, often outside working hours.' },
    ],
    helpTitle: 'How we help retail and ecommerce brands',
    helpParagraphs: [
      'We start with the numbers that matter most for a store: conversion rate, average order value and cost per sale. Then we fix the pages and steps that hold them back.',
      'Once the storefront is in good shape, we connect it to the rest of the business, so orders, stock and customer messages update automatically, and we run marketing that is measured against real sales rather than clicks.',
    ],
    helpList: [
      'Fast WooCommerce and custom storefronts',
      'Inventory, order and email automations',
      'Product SEO and shopping campaigns',
      'Social media content and paid social ads',
    ],
    focus: ['Online storefronts', 'Order automation', 'Shopping ads', 'Social media'],
    services: ['web-development', 'ads-management', 'social-media-management'],
    faqs: [
      { q: 'Do you work with WooCommerce?', a: 'Yes. We build and improve WooCommerce stores, and we also build custom stores when a business needs features that off the shelf platforms cannot provide.' },
      { q: 'Can you move our store from another platform?', a: 'Yes. We plan the move so products, customers, orders and search rankings carry across, and we set up redirects so old links keep working.' },
      { q: 'How do you measure marketing results for a store?', a: 'We track sales, revenue and cost per sale from each channel, not just traffic or clicks, and report on them every month.' },
    ],
  },
  {
    slug: 'manufacturing',
    name: 'Manufacturing',
    image: img('1504917595217-d4dc5ebe6122'),
    summary: 'Producers and suppliers that want connected data, planning tools and a stronger pipeline of trade enquiries.',
    intro: [
      'Manufacturers often run on a mix of ERP software, spreadsheets and email. Quotes, orders and production updates pass through many hands, and sales teams can find it hard to keep track of every trade enquiry.',
      'We help manufacturers and industrial suppliers connect their systems, build internal tools that fit how the shop floor works, and generate more qualified leads from distributors and buyers. The aim is less time spent chasing information and more time making and selling products.',
    ],
    challenges: [
      { title: 'Data in spreadsheets', text: 'Production, stock and order figures are kept in separate files that are hard to keep current.' },
      { title: 'Slow quoting', text: 'Preparing a quote can mean gathering prices and lead times from several people and systems.' },
      { title: 'Untracked enquiries', text: 'Trade enquiries arrive by email, phone and web forms with no single place to follow them up.' },
      { title: 'Low online visibility', text: 'Buyers research suppliers online, but many manufacturing websites say little about capabilities.' },
    ],
    helpTitle: 'How we help manufacturers',
    helpParagraphs: [
      'We work alongside your operations and sales teams to understand how information flows today. Then we build the tools and connections that remove the slowest manual steps.',
      'On the sales side, we set up a CRM that captures every enquiry and quote, and we improve your website and search presence so buyers can see what you make, the standards you work to and how to request a quote.',
    ],
    helpList: [
      'Dashboards and internal tools connected to your ERP',
      'Quote and order workflows with automatic updates',
      'CRM pipelines for distributors and trade buyers',
      'Capability focused websites and industrial SEO',
    ],
    focus: ['ERP connections', 'Quoting tools', 'CRM pipeline', 'Industrial SEO'],
    services: ['custom-software', 'ai-automation', 'crm-and-lead-management'],
    faqs: [
      { q: 'Can you connect to our existing ERP?', a: 'In most cases, yes. Many ERP systems offer an API or export options that we can use to share data with dashboards, CRMs and other tools.' },
      { q: 'Do you build custom internal tools?', a: 'Yes. We build web applications for quoting, scheduling, stock checks and reporting, designed around how your team works.' },
      { q: 'How can our website bring in trade enquiries?', a: 'By clearly describing capabilities, materials, certifications and lead times, and making it easy to request a quote. We then support it with search and targeted ads.' },
    ],
  },
  {
    slug: 'logistics',
    name: 'Logistics and Transportation',
    image: img('1586528116311-ad8dd3c8310d'),
    summary: 'Carriers, warehouses and distributors that depend on accurate tracking and quick responses.',
    intro: [
      'In logistics, every delay creates a phone call. Customers want to know where their shipment is, drivers need clear instructions and dispatch teams switch between several systems to keep everything moving.',
      'We help carriers, freight brokers, warehouses and distributors connect their systems, give customers self service tracking and reduce the manual updates that fill the working day. We also help logistics firms win more business through search and a stronger online presence.',
    ],
    challenges: [
      { title: 'Status calls and emails', text: 'Customers contact the office for updates that could be shown to them automatically.' },
      { title: 'Systems that do not talk', text: 'Transport, warehouse and accounting tools hold separate copies of the same information.' },
      { title: 'Manual paperwork', text: 'Proof of delivery, invoices and rate confirmations are often handled by hand.' },
      { title: 'Winning new accounts', text: 'Shippers compare providers online, and firms with thin websites are easy to overlook.' },
    ],
    helpTitle: 'How we help logistics teams',
    helpParagraphs: [
      'We look at the questions your team answers most often and the data your systems already hold. Many of those questions can be answered by a tracking page, an automated message or an AI assistant.',
      'We then connect the systems involved, so a status change in one place updates the others, and build reports that show performance across routes, customers and teams.',
    ],
    helpList: [
      'Customer tracking pages and automated status updates',
      'Integrations between transport, warehouse and accounting tools',
      'Document automation for invoices and proof of delivery',
      'Websites and SEO that help shippers find you',
    ],
    focus: ['Shipment tracking', 'System integration', 'Document automation', 'Lead generation'],
    services: ['custom-software', 'ai-automation', 'crm-and-lead-management'],
    faqs: [
      { q: 'Can customers track shipments on our website?', a: 'Yes. We can build a tracking page or portal that reads status data from your systems and shows it to customers securely.' },
      { q: 'Do you integrate with transport management systems?', a: 'Where the system offers an API or data export, we can connect it to your website, CRM, accounting software or reporting tools.' },
      { q: 'Can AI help our dispatch team?', a: 'AI assistants can answer routine customer questions and draft updates, which frees dispatchers to focus on planning and exceptions.' },
    ],
  },
  {
    slug: 'education',
    name: 'Education',
    image: img('1523050854058-8df90110c9f1'),
    summary: 'Schools, training providers and edtech teams that serve students and staff across many channels.',
    intro: [
      'Education providers serve several audiences at once: prospective students, current learners, parents, staff and partners. Each group needs different information, and enquiries arrive through the website, email, phone and social media.',
      'We help schools, colleges, training providers and edtech companies build clear websites, automate admissions and enquiry handling, and reach new students through search and social media. We keep accessibility and data protection in mind on every project.',
    ],
    challenges: [
      { title: 'Hard to find information', text: 'Course details, fees and deadlines are spread across pages that are difficult to navigate.' },
      { title: 'Busy admissions teams', text: 'Enquiries and applications pile up during peak periods and follow ups get missed.' },
      { title: 'Many channels to manage', text: 'Social media, email and the website all need regular, consistent updates.' },
      { title: 'Accessibility', text: 'Learners and parents use many devices and assistive tools, so content needs to work for everyone.' },
    ],
    helpTitle: 'How we help education providers',
    helpParagraphs: [
      'We organise content around what each audience needs to do, whether that is comparing courses, applying, paying fees or contacting a tutor, and we build pages that work well on phones and with assistive technology.',
      'Behind the website, we connect enquiry forms to a CRM, automate reminders and follow ups, and run search and social campaigns timed around your intake dates.',
    ],
    helpList: [
      'Accessible websites with clear course and admissions pages',
      'Enquiry and application tracking in a CRM',
      'Automated follow ups and reminders for applicants',
      'SEO and social media campaigns around intake dates',
    ],
    focus: ['Course websites', 'Admissions automation', 'Student enquiries', 'Social media'],
    services: ['web-development', 'seo', 'social-media-management'],
    faqs: [
      { q: 'Can you make our website more accessible?', a: 'Yes. We follow accessibility guidelines for structure, contrast, keyboard use and alternative text, and check pages with assistive tools.' },
      { q: 'Can admissions enquiries be tracked automatically?', a: 'Enquiry and application forms can feed straight into a CRM, with automatic replies and reminders so no applicant is missed.' },
      { q: 'Do you manage social media for schools and training providers?', a: 'Yes. We plan, create and schedule content, and run paid campaigns around open days and intake dates.' },
    ],
  },
]
