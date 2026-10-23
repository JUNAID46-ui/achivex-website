import { img } from './images'

// Blog posts. `intro.keyword` is rendered in bold at the very start of the first paragraph.
// Sections become the table of contents automatically.
export const blogCategories = ['Data', 'Cloud', 'Security', 'Strategy', 'Operations']

export const blogs = [
  {
    slug: 'what-a-data-modernization-project-involves',
    title: 'What a data modernization project actually involves',
    category: 'Data',
    date: '2026-09-28',
    author: 'AchiveX Editorial Team',
    featured: true,
    image: img('1526374965328-7f61d4dc18c5', 2000),
    excerpt: 'A plain account of the stages, the decisions and the people you need for a data platform upgrade.',
    intro: {
      keyword: 'Data modernization',
      text: ' is the process of moving reporting and analysis away from scattered spreadsheets and ageing databases onto a platform designed for it. The phrase sounds large, but most projects follow the same handful of stages.',
    },
    sections: [
      {
        id: 'start-with-an-inventory',
        heading: 'Start with an inventory',
        paragraphs: [
          'Before choosing any technology, list every data source that feeds a report people rely on. Note who owns it, how often it changes and which reports depend on it. This list will drive the rest of the project.',
          'Teams are often surprised by how many sources turn up. A single monthly report can depend on a CRM export, two spreadsheets and a query someone wrote years ago.',
        ],
      },
      {
        id: 'choose-a-target-platform',
        heading: 'Choose a target platform',
        paragraphs: [
          'Cloud data warehouses and lakehouse platforms from the major providers all handle typical business volumes well. The right choice usually depends on the cloud you already use, the skills on your team and how you prefer to pay.',
        ],
        list: ['Which cloud provider do you already use?', 'Who will maintain pipelines after launch?', 'Do you need to store raw files as well as tables?'],
        subsections: [
          { heading: 'Warehouse or lakehouse', paragraphs: ['A warehouse suits teams whose data is mostly tables from business systems. A lakehouse also stores raw files, which helps when you plan to keep logs, documents or exports alongside your reports.'] },
          { heading: 'Who runs it day to day', paragraphs: ['Pick a platform your team or your partner already knows. A familiar tool that someone can maintain is worth more than a newer one nobody owns.'] },
        ],
      },
      {
        id: 'migrate-in-waves',
        heading: 'Migrate in waves',
        paragraphs: [
          'Moving everything at once is risky. A better approach is to pick one subject area, such as sales, move its sources, rebuild its reports and reconcile the numbers against the old version before moving on.',
          'Reconciliation is where trust is earned. If finance sees the same totals in the new reports as the old ones, adoption follows.',
        ],
      },
      {
        id: 'plan-the-handover',
        heading: 'Plan the handover',
        paragraphs: [
          'A platform is only useful if someone can run it. Document pipelines, set up alerts for failures and train the people who will look after it. Then switch off the old pieces so there is only one place to look.',
        ],
      },
    ],
    faqs: [
      { q: 'How long does a data modernization project take?', a: 'It depends on how many sources feed your reports. A small business with a handful of sources can often finish in a few months, while larger estates are usually moved in stages.' },
      { q: 'Do we need to replace every system at once?', a: 'No. Most teams move one group of reports at a time, starting with the ones people rely on most, and keep the old setup running until each part is checked.' },
    ],
  },
  {
    slug: 'cloud-costs-five-habits',
    title: 'Five habits that keep cloud costs predictable',
    category: 'Cloud',
    date: '2026-08-30',
    author: 'AchiveX Editorial Team',
    image: img('1607799279861-4dd421887fb3'),
    excerpt: 'Simple routines for tagging, budgets and reviews that stop surprise bills.',
    intro: {
      keyword: 'Cloud cost management',
      text: ' is less about clever tools and more about steady habits. These five are simple to adopt and make a noticeable difference.',
    },
    sections: [
      { id: 'tag-everything', heading: 'Tag everything', paragraphs: ['Every resource should carry tags for owner, environment and project. Without tags, nobody can say who is responsible for a line on the bill.'] },
      { id: 'set-budgets-and-alerts', heading: 'Set budgets and alerts', paragraphs: ['All major providers let you set budgets with email alerts. Set them per project and per environment so problems show up early.'] },
      { id: 'switch-off-what-is-idle', heading: 'Switch off what is idle', paragraphs: ['Development and test environments rarely need to run overnight or at weekends. Scheduled shutdowns are one of the easiest savings available.'] },
      { id: 'rightsize-regularly', heading: 'Rightsize regularly', paragraphs: ['Instances are often sized for a launch that never needed that capacity. Review utilisation monthly and resize where usage is consistently low.'] },
      { id: 'review-commitments', heading: 'Review commitments', paragraphs: ['Reserved capacity and savings plans can lower costs for steady workloads. Commit only once usage has been stable for a while.'] },
    ],
  },
  {
    slug: 'multi-factor-authentication-first',
    title: 'Why multi factor authentication should come first',
    category: 'Security',
    date: '2026-07-14',
    author: 'AchiveX Editorial Team',
    image: img('1563986768494-4dee2763ff3f'),
    excerpt: 'Of all the security controls a small business can add, this one gives the most protection for the effort.',
    intro: {
      keyword: 'Multi factor authentication',
      text: ' asks users for a second proof of identity, such as an app prompt or a hardware key, in addition to a password. It is one of the most effective controls against account takeover.',
    },
    sections: [
      { id: 'passwords-get-stolen', heading: 'Passwords get stolen', paragraphs: ['Phishing, reused passwords and data breaches at other sites all expose credentials. A second factor means a stolen password alone is not enough to sign in.'] },
      { id: 'where-to-turn-it-on', heading: 'Where to turn it on', paragraphs: ['Start with email, remote access and administrator accounts, then extend to every business application that supports it.'], list: ['Email and office suite accounts', 'VPN and remote desktop', 'Cloud provider consoles', 'Finance and payroll systems'] },
      { id: 'choose-stronger-methods', heading: 'Choose stronger methods', paragraphs: ['App based prompts and hardware security keys resist phishing better than SMS codes. Use them where possible, especially for administrators.'] },
    ],
  },
  {
    slug: 'writing-a-technology-roadmap',
    title: 'How to write a technology roadmap people will follow',
    category: 'Strategy',
    date: '2026-06-03',
    author: 'AchiveX Editorial Team',
    image: img('1434030216411-0b793f4b4173'),
    excerpt: 'Roadmaps fail when they are too vague or too detailed. Here is a middle path.',
    intro: {
      keyword: 'A technology roadmap',
      text: ' is a short, shared plan that links business goals to specific technology work over the next year or two. The best ones fit on a few pages.',
    },
    sections: [
      { id: 'tie-each-item-to-a-goal', heading: 'Tie each item to a goal', paragraphs: ['If an initiative cannot be linked to a business goal, question whether it belongs on the roadmap at all.'] },
      { id: 'be-honest-about-capacity', heading: 'Be honest about capacity', paragraphs: ['A roadmap that assumes the team can do everything at once will slip. Plan around the people you actually have.'] },
      { id: 'review-it-quarterly', heading: 'Review it quarterly', paragraphs: ['Priorities change. A short quarterly review keeps the roadmap useful instead of becoming a document nobody opens.'] },
    ],
  },
  {
    slug: 'outsourcing-customer-support-checklist',
    title: 'A checklist before you outsource customer support',
    category: 'Operations',
    date: '2026-05-11',
    author: 'AchiveX Editorial Team',
    image: img('1600267185393-e158a98703de'),
    excerpt: 'The questions to answer internally so an outsourced team can represent you well.',
    intro: {
      keyword: 'Outsourcing customer support',
      text: ' works well when the outside team has the same information your best internal agent would have. Use this checklist to get ready.',
    },
    sections: [
      { id: 'document-common-questions', heading: 'Document common questions', paragraphs: ['Pull the most frequent contact reasons from your inbox or helpdesk and write clear answers for each one.'] },
      { id: 'agree-escalation-paths', heading: 'Agree escalation paths', paragraphs: ['Decide which issues the outsourced team can resolve and which must come back to your staff, and how quickly.'] },
      { id: 'define-what-good-looks-like', heading: 'Define what good looks like', paragraphs: ['Set measures such as response time, resolution rate and quality scores before launch, so both sides know the target.'] },
    ],
  },
  {
    slug: 'generative-ai-pilot-questions',
    title: 'Questions to answer before a generative AI pilot',
    category: 'Data',
    date: '2026-04-02',
    author: 'AchiveX Editorial Team',
    image: img('1531538606174-0f90ff5dce83'),
    excerpt: 'Clear answers to these questions make the difference between a useful pilot and an expensive demo.',
    intro: {
      keyword: 'A generative AI pilot',
      text: ' should test whether a specific task gets faster or better with a language model. Answer these questions first.',
    },
    sections: [
      { id: 'what-task-are-you-testing', heading: 'What task are you testing?', paragraphs: ['Name the task, who does it today and how long it takes. Vague goals such as "use AI more" lead to vague results.'] },
      { id: 'what-data-will-it-use', heading: 'What data will it use?', paragraphs: ['Decide which documents or records the model can see and confirm the provider terms for how that data is handled.'] },
      { id: 'how-will-you-judge-it', heading: 'How will you judge it?', paragraphs: ['Prepare a set of test questions with known good answers and agree a pass mark before the pilot begins.'] },
    ],
  },
  {
    slug: 'month-end-close-faster',
    title: 'Small changes that speed up the month end close',
    category: 'Operations',
    date: '2026-02-19',
    author: 'AchiveX Editorial Team',
    image: img('1554224154-26032ffc0d07'),
    excerpt: 'Practical steps finance teams can take without new software.',
    intro: {
      keyword: 'The month end close',
      text: ' often takes longer than it should because work piles up in the last few days. Spreading it out helps.',
    },
    sections: [
      { id: 'reconcile-weekly', heading: 'Reconcile weekly', paragraphs: ['Bank and card reconciliations done each week leave far less to do at month end.'] },
      { id: 'use-a-checklist', heading: 'Use a checklist', paragraphs: ['A written close checklist with owners and due dates prevents steps from being missed or repeated.'] },
      { id: 'agree-cutoffs', heading: 'Agree cutoffs', paragraphs: ['Set clear cutoff dates for expense claims and invoices and share them with the whole company.'] },
    ],
  },
  {
    slug: 'hybrid-infrastructure-basics',
    title: 'Hybrid infrastructure basics for growing companies',
    category: 'Cloud',
    date: '2025-12-08',
    author: 'AchiveX Editorial Team',
    image: img('1580894894513-541e068a3e2b'),
    excerpt: 'Not everything needs to move to the cloud. Here is how to decide what stays.',
    intro: {
      keyword: 'Hybrid infrastructure',
      text: ' means running some systems in your own locations and others in the cloud, with secure connections between them.',
    },
    sections: [
      { id: 'what-often-stays-local', heading: 'What often stays local', paragraphs: ['Systems tied to physical equipment, very low latency needs or specific licensing terms are common reasons to keep workloads on site.'] },
      { id: 'connect-them-securely', heading: 'Connect them securely', paragraphs: ['Use encrypted site to cloud connections and a single identity system so users and administrators sign in the same way everywhere.'] },
      { id: 'monitor-both-sides', heading: 'Monitor both sides', paragraphs: ['One monitoring view across local and cloud systems avoids blind spots when something fails.'] },
    ],
  },
]

export const getBlog = (slug) => blogs.find((b) => b.slug === slug)

export const readTime = (post) => {
  const words = [post.intro.text, ...post.sections.flatMap((s) => [...s.paragraphs, ...(s.list || [])])]
    .join(' ')
    .split(/\s+/).length
  return Math.max(2, Math.round(words / 200))
}
