import { img } from './images'

// Case studies describe how AchiveX approaches a typical engagement for each type of client.
// `results` lists the measures we agree with the client to track. Client names and figures are
// only published with written permission. The live site reads these from the database.
export const caseStudies = [
  {
    slug: 'retail-storefront-rebuild',
    title: 'Rebuilding an online store around mobile shoppers',
    clientType: 'Regional retail business',
    industry: 'retail-and-ecommerce',
    services: ['web-development', 'seo', 'ads-management'],
    image: img('1556740749-887f6717d7e4', 2000),
    summary: 'A new storefront and checkout connected to inventory, with tracking to show what drives sales.',
    overview:
      'A retailer selling through a few physical stores and an online shop that has grown in pieces over several years.',
    challenge: [
      'Most visitors arrive on mobile, but the checkout was designed for desktop and asks for the same details more than once.',
      'Stock levels online are updated by hand from the store system, which leads to orders for items that are out of stock.',
    ],
    solution: [
      'We map the buying journey with your team and redesign the key pages for small screens first.',
      'The store moves to a maintained commerce platform connected to the inventory system, so stock updates automatically.',
      'Analytics and conversion tracking show the team where shoppers drop off.',
    ],
    results: ['Checkout completion rate on mobile', 'Orders placed for out of stock items', 'Staff time spent updating stock by hand'],
  },
  {
    slug: 'finance-reporting-consolidation',
    title: 'Consolidating finance reporting into one trusted model',
    clientType: 'Financial services firm',
    industry: 'financial-services',
    services: ['custom-software', 'ai-automation'],
    image: img('1590650153855-d9e808231d41', 2000),
    summary: 'Monthly reports built from a single data model instead of spreadsheets maintained by different teams.',
    overview:
      'A finance team that builds monthly management reports by combining exports from several systems in spreadsheets.',
    challenge: [
      'Different teams use different definitions for the same metrics, so numbers rarely match in leadership meetings.',
      'Preparing the monthly pack relies on a few people and a lot of manual work.',
    ],
    solution: [
      'We run workshops to agree a shared set of metric definitions with finance and operations.',
      'Source data loads into a cloud data warehouse through automated pipelines with reconciliation checks.',
      'A reporting layer and a set of dashboards replace the manual pack.',
    ],
    results: ['Days needed to produce the monthly report', 'Number of metric definitions in use across teams', 'Manual steps left in the reporting process'],
  },
  {
    slug: 'logistics-cloud-migration',
    title: 'Moving a logistics platform to the cloud in planned stages',
    clientType: 'Logistics and distribution company',
    industry: 'logistics',
    services: ['custom-software', 'ai-automation'],
    image: img('1494412574643-ff11b0a5c1c3', 2000),
    summary: 'A staged migration of tracking and dispatch systems with tested rollback plans for each wave.',
    overview:
      'A distributor running shipment tracking and dispatch applications on ageing servers in a single location.',
    challenge: [
      'Hardware is nearing end of support and any outage affects drivers and customers directly.',
      'Several applications share a database, which makes a single big move risky.',
    ],
    solution: [
      'We document every application and dependency and group them into migration waves.',
      'A cloud landing zone with networking, identity and monitoring is set up before any workload moves.',
      'Each wave moves outside peak hours with a rehearsed cutover and rollback plan.',
    ],
    results: ['Unplanned downtime during each migration wave', 'Monthly infrastructure cost before and after', 'Time to restore a service from backup'],
  },
  {
    slug: 'healthcare-patient-support',
    title: 'Extending patient support hours with a trained contact team',
    clientType: 'Healthcare services provider',
    industry: 'healthcare',
    services: ['ai-automation'],
    image: img('1519494026892-80bbd2d6fd0d', 2000),
    summary: 'A dedicated contact team and a searchable knowledge base for appointment and billing questions.',
    overview:
      'A provider whose front desk staff answer patient calls while also managing in person visits.',
    challenge: [
      'Callers wait on hold during busy clinic hours, and messages left after hours pile up.',
      'Answers to common billing questions vary depending on who picks up the phone.',
    ],
    solution: [
      'We set up a contact team trained on your scheduling and billing policies.',
      'A knowledge base written with your team is connected to an internal search assistant for agents.',
      'Daily reports on contact reasons go to the practice manager.',
    ],
    results: ['Average time callers wait on hold', 'Messages answered outside clinic hours', 'Consistency of answers in quality reviews'],
  },
  {
    slug: 'manufacturing-equipment-monitoring',
    title: 'Connecting shop floor equipment to a live monitoring view',
    clientType: 'Mid sized manufacturer',
    industry: 'manufacturing',
    services: ['ai-automation', 'custom-software'],
    image: img('1565514020179-026b92b84bb6', 2000),
    summary: 'A pilot that brings machine status data into one dashboard for supervisors.',
    overview:
      'A plant where supervisors rely on walking the floor and paper logs to know which machines are running.',
    challenge: [
      'Machines from different vendors store data in separate systems or not at all.',
      'The plant network has grown without a plan and is not ready for more connected devices.',
    ],
    solution: [
      'We review the network and segment it to separate production equipment from office traffic.',
      'A pilot connects a small group of machines through gateways to a cloud monitoring service.',
      'Supervisors get a dashboard and alerts for stoppages.',
    ],
    results: ['Time to notice a machine stoppage', 'Share of machines reporting live status', 'Planned versus unplanned maintenance'],
  },
  {
    slug: 'education-security-program',
    title: 'Building a practical security baseline for a training provider',
    clientType: 'Education and training provider',
    industry: 'education',
    services: ['custom-software', 'crm-and-lead-management'],
    image: img('1523240795612-9a054b0db644', 2000),
    summary: 'An assessment, prioritised fixes and ongoing monitoring sized for a small IT team.',
    overview:
      'An organisation with a small IT team supporting staff and students across several locations.',
    challenge: [
      'There is no clear view of which risks matter most or where to start.',
      'Some accounts are shared and multi factor authentication is not in use.',
    ],
    solution: [
      'We run an assessment against the CIS Controls and rank findings by risk.',
      'Multi factor authentication, patching routines and backup testing come first.',
      'Ongoing monitoring and quarterly reviews run through managed services.',
    ],
    results: ['Accounts protected by multi factor authentication', 'Systems patched within the agreed window', 'Backups tested and restored successfully'],
  },
]

export const getCaseStudy = (slug) => caseStudies.find((c) => c.slug === slug)
