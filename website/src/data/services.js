import { img } from './images'
import { categories } from './categories'
import { serviceMode } from '../lib/permalinks'

// Every individual service page is generated from this list.
// To add a service: add an object here and add its slug to the matching area in categories.js.
export const services = [
  /* ---------------- Web and Software ---------------- */
  {
    slug: 'web-development',
    name: 'Web Development',
    category: 'web-and-software',
    short: 'Fast, mobile friendly websites built to turn visitors into customers.',
    heroImage: img('1517694712202-14dd9538aa97', 2000),
    images: [img('1498050108023-c5249f4df085'), img('1555421689-491a97ff2040')],
    heroLine: 'WordPress, WooCommerce, Laravel and React websites that load quickly and make it easy to get in touch or buy.',
    overview: [
      'A website has one main job: help the right visitors take the next step, whether that is calling you, booking a meeting or placing an order. We plan each site around that step, then build it to load quickly and work properly on every phone.',
      'We build on WordPress and WooCommerce when you want to edit content yourself, and on custom PHP, Laravel or React when the site needs features an off the shelf setup cannot handle. Redesigns of existing sites, speed work and ongoing maintenance are part of the service.',
    ],
    included: [
      'Page structure and content plan based on your goals',
      'Design for desktop, tablet and phone',
      'WordPress and WooCommerce builds you can edit yourself',
      'Custom PHP, Laravel and React development',
      'Speed optimization and basic technical SEO setup',
      'Hosting setup, backups and ongoing maintenance',
    ],
    benefits: [
      { title: 'Built to convert', text: 'Clear pages, simple forms and visible calls to action make it easy for visitors to contact you or buy.' },
      { title: 'Fast on every device', text: 'Images, code and hosting are tuned so pages load quickly on phones as well as desktops.' },
      { title: 'Easy to update', text: 'You can change text, images and products yourself without calling a developer for every edit.' },
      { title: 'Looked after', text: 'Updates, backups and fixes continue after launch, so the site keeps working.' },
    ],
    process: [
      { title: 'Plan', text: 'Agree goals, pages, content and the features the site needs.' },
      { title: 'Design', text: 'Layouts for key pages, reviewed with you before development starts.' },
      { title: 'Build', text: 'Development, content entry and testing on real devices.' },
      { title: 'Launch and care', text: 'Go live, monitor and keep the site updated and backed up.' },
    ],
    faqs: [
      { q: 'How long does a new website take?', a: 'Most business websites take four to ten weeks. Online stores and custom builds can take longer. We give you a timeline after the first consultation.' },
      { q: 'Can you redesign our existing site?', a: 'Yes. We can keep what works, improve what does not and move your content across without losing pages that already rank.' },
      { q: 'WordPress or custom code?', a: 'WordPress suits most marketing sites and stores. Custom Laravel or React suits sites with complex features. We recommend one based on what you need, not on habit.' },
      { q: 'How much does a website cost?', a: 'It depends on the number of pages, features and content. We quote a fixed price after a free consultation.' },
      { q: 'Will we be able to update the website ourselves?', a: 'Yes. We set up an editor that suits your team, show you how to change text, images and products, and leave written notes for later.' },
    ],
    whoFor: [
      'Businesses whose current website is slow, dated or hard to update',
      'Companies launching a new brand, product or location that needs its own site',
      'Store owners who want to start selling online or move to WooCommerce',
      'Teams that need a site connected to their CRM, booking system or payment provider',
    ],
    tools: ['WordPress', 'WooCommerce', 'Laravel', 'React', 'Tailwind CSS', 'Google Analytics 4', 'Google Search Console', 'Cloudflare'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'custom-software',
    name: 'Custom Software',
    category: 'web-and-software',
    short: 'Web apps, SaaS platforms, dashboards, admin panels and APIs built around your business.',
    heroImage: img('1531297484001-80022131f5a1', 2000),
    images: [img('1515879218367-8466d910aaa4'), img('1551288049-bebda4e38f71')],
    heroLine: 'Software shaped around how your team works, built on Laravel and React.',
    overview: [
      'Off the shelf tools cover common needs, but many businesses end up stitching together spreadsheets and apps that do not talk to each other. Custom software replaces that with one system built for your process.',
      'We build web apps, SaaS platforms, internal systems, dashboards, admin panels and APIs on Laravel and React. Each project starts with how your team works today and grows in small, usable releases.',
    ],
    included: [
      'Discovery workshops and written requirements',
      'Web applications and SaaS platforms',
      'Internal systems and admin panels',
      'Dashboards and reporting screens',
      'APIs and integrations with your existing tools',
      'Testing, deployment and ongoing support',
    ],
    benefits: [
      { title: 'Fits your process', text: 'Screens and steps follow how your team works instead of forcing a new routine.' },
      { title: 'One source of truth', text: 'Data lives in one system rather than scattered spreadsheets and apps.' },
      { title: 'You own it', text: 'The code and data belong to you, with no per seat licence for the core system.' },
      { title: 'Grows with you', text: 'New features are added in small releases as the business changes.' },
    ],
    process: [
      { title: 'Discover', text: 'Map the current process, users and must have features.' },
      { title: 'Design', text: 'Screens and data structure agreed before coding starts.' },
      { title: 'Build in releases', text: 'Usable versions delivered every few weeks for feedback.' },
      { title: 'Support', text: 'Hosting, monitoring, fixes and new features after launch.' },
    ],
    faqs: [
      { q: 'How long does a custom system take?', a: 'A first usable version usually takes eight to sixteen weeks. Larger platforms are built in stages after that.' },
      { q: 'Which technologies do you use?', a: 'Mainly Laravel for the back end and React for the front end, with standard hosting and databases.' },
      { q: 'Can it connect to the tools we already use?', a: 'Usually yes, through APIs. We check each tool during discovery.' },
      { q: 'Who owns the code?', a: 'You do. The code and data are handed over and documented.' },
      { q: 'Who owns the code you write for us?', a: 'You do. The code, the data and the hosting accounts belong to your business, and we hand over full access and documentation.' },
    ],
    whoFor: [
      'Teams that run important processes on spreadsheets and email',
      'Founders building a SaaS product or customer portal',
      'Businesses whose off the shelf tools no longer fit how they work',
      'Companies that need their systems connected through an API',
    ],
    tools: ['Laravel', 'React', 'PHP', 'MySQL', 'PostgreSQL', 'REST APIs', 'Supabase', 'AWS and Vercel hosting'],
    caseStudy: 'finance-reporting-consolidation',
  },
  /* ---------------- AI and Automation ---------------- */
  {
    slug: 'ai-automation',
    name: 'AI & Automation',
    category: 'ai-and-automation',
    short: 'Chatbots, document parsing, lead scoring, AI agents and a 24/7 AI receptionist, connected to the tools you already use.',
    heroImage: img('1677442136019-21780ecad995', 2000),
    images: [img('1620712943543-bcc4688e7485'), img('1551434678-e076c223a692')],
    heroLine: 'Practical AI and automation built on your own data, aimed at the work that takes your team the most time.',
    overview: [
      'Much of the work in a growing business is repetitive: reading documents, sorting enquiries, answering the same questions and copying information from one system to another. We find those tasks and build AI tools and automations that handle them the same way every time.',
      'Our work covers document parsing, prediction and lead scoring, retrieval based AI and custom chatbots that answer from your own documents, integration between your tools, AI agents for routine tasks, and a 24/7 AI receptionist that answers calls, books appointments and qualifies leads.',
    ],
    included: [
      'Review of tasks where AI and automation can save time',
      'Document parsing, prediction and lead scoring',
      'Custom chatbots and retrieval based AI on your own data',
      'Integration between your apps, forms, email and CRM',
      'AI agents and workflow automation for routine tasks',
      '24/7 AI receptionist for calls, appointments and lead qualification',
    ],
    benefits: [
      { title: 'Hours back each week', text: 'Repetitive reading, sorting and copying is handled by the system.' },
      { title: 'Answers from your data', text: 'Chatbots and assistants respond using your own documents and records.' },
      { title: 'No missed calls', text: 'The AI receptionist answers out of hours and books appointments.' },
      { title: 'Connected systems', text: 'Your tools share information instead of working in isolation.' },
    ],
    process: [
      { title: 'Find the task', text: 'Map your processes and pick the tasks with clear value.' },
      { title: 'Prepare', text: 'Gather the documents, data and tool access the system needs.' },
      { title: 'Build and test', text: 'Build the AI tools and automations and test them on real examples.' },
      { title: 'Launch and improve', text: 'Go live, monitor results and adjust as your process changes.' },
    ],
    faqs: [
      { q: 'Will our data be used to train public AI models?', a: 'No. We set up systems so your data stays within the services and accounts you control.' },
      { q: 'Which tools can you connect?', a: 'Most modern tools with an API, such as CRMs, booking systems, email, forms and spreadsheets. We confirm each one at the start.' },
      { q: 'How does the AI receptionist work?', a: 'It answers calls with a script you approve, answers common questions, books appointments in your calendar and passes qualified leads to your team.' },
      { q: 'What does it cost?', a: 'It depends on scope. We usually start with one focused tool or automation, quoted after a free consultation.' },
      { q: 'Is our data safe with AI tools?', a: 'We only connect tools you approve, limit what each one can see, and avoid sending sensitive information to services that are not set up for it.' },
    ],
    whoFor: [
      'Teams that answer the same customer questions every day',
      'Businesses copying data between forms, spreadsheets and other tools by hand',
      'Companies that want a chatbot or assistant trained on their own information',
      'Owners who want reports and follow ups to happen without someone remembering them',
    ],
    tools: ['OpenAI and Claude models', 'Zapier', 'Make', 'n8n', 'WhatsApp Business', 'Google Workspace', 'Microsoft 365', 'Custom APIs'],
    caseStudy: 'healthcare-patient-support',
  },
  {
    slug: 'crm-and-lead-management',
    name: 'CRM & Lead Management',
    category: 'ai-and-automation',
    short: 'Custom CRM systems, lead capture and automated follow ups so no lead is lost.',
    heroImage: img('1553877522-43269d4ea984', 2000),
    images: [img('1556745753-b2904692b3cd'), img('1522071820081-009f0129c71c')],
    heroLine: 'Capture every enquiry, follow up on time and see where each lead stands.',
    overview: [
      'Leads often arrive through many channels: website forms, calls, emails, ads and social media. Without one place to track them, follow ups are missed and good enquiries go cold.',
      'We set up or build CRM systems that capture leads from every channel, nurture them with timely messages and automate follow ups, so your team always knows who to contact next.',
    ],
    included: [
      'Custom CRM systems or setup of an existing CRM',
      'Lead capture from forms, calls, ads and email',
      'Pipelines and stages that match your sales process',
      'Automated follow up emails and reminders',
      'Lead nurturing sequences',
      'Reports on lead sources and conversion',
    ],
    benefits: [
      { title: 'No lost leads', text: 'Every enquiry is captured and assigned automatically.' },
      { title: 'Timely follow ups', text: 'Reminders and automated messages keep leads warm.' },
      { title: 'Clear pipeline', text: 'See every lead, its stage and the next action at a glance.' },
      { title: 'Know what works', text: 'Reports show which channels bring leads that turn into customers.' },
    ],
    process: [
      { title: 'Review', text: 'Understand how leads arrive and how your team follows up today.' },
      { title: 'Set up', text: 'Configure or build the CRM, pipelines and lead capture.' },
      { title: 'Automate', text: 'Add follow up sequences, reminders and assignments.' },
      { title: 'Train and report', text: 'Train your team and set up regular reports.' },
    ],
    faqs: [
      { q: 'Do we need a custom CRM or an existing one?', a: 'Many businesses do well with an established CRM. We recommend a custom system only when your process needs it.' },
      { q: 'Can you move our existing contacts?', a: 'Yes. We import contacts and history from spreadsheets or your current system.' },
      { q: 'Will our team find it easy to use?', a: 'We keep screens simple and train your team before launch.' },
      { q: 'How long does it take?', a: 'A CRM setup usually takes two to six weeks depending on channels and automations.' },
      { q: 'Can you move our data from our current CRM?', a: 'Yes. We map your contacts, deals and history into the new system, test the import and keep the old data available until you are happy.' },
    ],
    whoFor: [
      'Sales teams that track leads in spreadsheets or inboxes',
      'Businesses losing enquiries because follow ups depend on memory',
      'Companies that want to see where leads come from and which ones close',
      'Teams moving from one CRM to another without losing data',
    ],
    tools: ['HubSpot', 'Zoho CRM', 'GoHighLevel', 'Pipedrive', 'Salesforce', 'Zapier', 'Calendly', 'Email and SMS tools'],
    caseStudy: 'retail-storefront-rebuild',
  },
  /* ---------------- Growth Marketing ---------------- */
  {
    slug: 'seo',
    name: 'SEO',
    category: 'growth-marketing',
    short: 'Technical, on-page, local and off-page SEO with monthly reporting.',
    heroImage: img('1432888498266-38ffec3eaf0a', 2000),
    images: [img('1551288049-bebda4e38f71'), img('1504868584819-f8e8b4b6d7e3')],
    heroLine: 'Help the right customers find you on Google, then turn that traffic into enquiries.',
    overview: [
      'Search engine optimization is steady work across the whole site: making it easy for Google to read, making each page useful for the people searching, and building trust through mentions and links from other sites.',
      'We cover technical, on-page, local and off-page SEO, optimize your Google Business Profile and send a monthly report, so you can see how rankings, organic traffic and qualified leads are changing.',
    ],
    included: [
      'Technical SEO audit and fixes',
      'Keyword research and on-page optimization',
      'Local SEO and Google Business Profile optimization',
      'Content planning for pages and articles',
      'Off-page SEO and quality link building',
      'Monthly ranking, traffic and lead reports',
    ],
    benefits: [
      { title: 'More qualified traffic', text: 'Pages target the searches your customers actually make.' },
      { title: 'Local visibility', text: 'An optimized Google Business Profile helps you show up in local results and maps.' },
      { title: 'A healthy site', text: 'Technical fixes make the site faster and easier for search engines to read.' },
      { title: 'Clear reporting', text: 'Monthly reports show what changed and what we are doing next.' },
    ],
    process: [
      { title: 'Audit', text: 'Review the site, rankings, competitors and Google Business Profile.' },
      { title: 'Plan', text: 'Agree priority keywords, fixes and content for the coming months.' },
      { title: 'Improve', text: 'Fix technical issues, optimize pages and publish content.' },
      { title: 'Report', text: 'Monthly reports on rankings, traffic and leads.' },
    ],
    faqs: [
      { q: 'How long until we see results?', a: 'Technical fixes can help within weeks. Ranking for competitive searches usually takes several months of steady work.' },
      { q: 'Can you guarantee first place on Google?', a: 'No one can honestly guarantee a ranking. We focus on the work that improves visibility and report on progress every month.' },
      { q: 'Do you write content?', a: 'Yes. We plan and write pages and articles, or work with content your team provides.' },
      { q: 'How is SEO priced?', a: 'Usually as a monthly retainer based on the size of the site and the competition. We quote after a free consultation.' },
      { q: 'How long does SEO take to show results?', a: 'Technical fixes can help within weeks, but steady growth in rankings and traffic usually takes three to six months of consistent work.' },
    ],
    whoFor: [
      'Businesses that rely on Google to bring in new customers',
      'Local companies that want to appear in Google Maps results',
      'Websites that have lost traffic after a redesign or update',
      'Stores that want product and category pages to rank',
    ],
    tools: ['Google Search Console', 'Google Analytics 4', 'Google Business Profile', 'Ahrefs', 'Semrush', 'Screaming Frog', 'PageSpeed Insights', 'Schema markup'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'aeo-and-geo',
    name: 'AEO & GEO',
    category: 'growth-marketing',
    short: 'Visibility in featured snippets, voice search and AI answers from ChatGPT, Gemini and Perplexity.',
    heroImage: img('1639322537228-f710d846310a', 2000),
    images: [img('1677442136019-21780ecad995'), img('1531545514256-b1400bc00f31')],
    heroLine: 'Make your business easy for search engines and AI assistants to understand, quote and recommend.',
    overview: [
      'More people now get answers straight from Google AI Overviews, voice assistants and tools like ChatGPT, Gemini and Perplexity. Answer engine optimization (AEO) and generative engine optimization (GEO) help your business appear in those answers.',
      'We structure your content to answer real questions clearly, add schema markup so machines understand your pages, work towards featured snippets and voice search results, and track how your brand appears in AI answers over time.',
    ],
    included: [
      'Audit of how AI tools currently describe your brand',
      'Question based content that answers searches directly',
      'Schema markup for your business, services and FAQs',
      'Optimization for featured snippets and voice search',
      'Brand mentions and consistent business information across the web',
      'Regular checks of visibility in AI answers',
    ],
    benefits: [
      { title: 'Seen in AI answers', text: 'Your business is more likely to be named when people ask AI tools for recommendations.' },
      { title: 'Featured snippets', text: 'Clear answers can earn the highlighted box at the top of Google.' },
      { title: 'Voice search ready', text: 'Content is written to answer spoken questions directly.' },
      { title: 'Builds on SEO', text: 'The same work strengthens your regular search rankings too.' },
    ],
    process: [
      { title: 'Check', text: 'See how search engines and AI tools describe you today.' },
      { title: 'Structure', text: 'Add schema markup and organise content around real questions.' },
      { title: 'Publish', text: 'Create and update pages that answer questions clearly.' },
      { title: 'Track', text: 'Check visibility in AI answers and snippets regularly.' },
    ],
    faqs: [
      { q: 'What is the difference between AEO and GEO?', a: 'AEO focuses on direct answers such as featured snippets and voice search. GEO focuses on being cited in AI generated answers from tools like ChatGPT and Gemini.' },
      { q: 'Can you guarantee ChatGPT will mention us?', a: 'No. AI tools decide their own answers. We improve the signals they rely on and track changes over time.' },
      { q: 'Do we still need SEO?', a: 'Yes. AEO and GEO build on a site that is already well optimized for search.' },
      { q: 'How long does it take?', a: 'Schema and content changes can be done in weeks. Visibility in AI answers builds gradually over months.' },
      { q: 'How is this different from normal SEO?', a: 'SEO focuses on ranking in search results. AEO and GEO focus on being quoted and recommended inside AI answers, which needs clear, structured and well sourced content.' },
    ],
    whoFor: [
      'Businesses that want to be mentioned when people ask ChatGPT, Gemini or Perplexity for a recommendation',
      'Companies whose content is strong but rarely appears in featured snippets or AI answers',
      'Brands that want to know how AI tools describe them today',
      'Teams already investing in SEO who want to cover the next way people search',
    ],
    tools: ['ChatGPT', 'Google Gemini', 'Perplexity', 'Microsoft Copilot', 'Google Search Console', 'Schema markup', 'llms.txt', 'Brand mention tracking'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'social-media-management',
    name: 'Social Media Management',
    category: 'growth-marketing',
    short: 'Content planning, reels, graphics, scheduling, engagement and reporting.',
    heroImage: img('1611162617213-7d7a39e9b1d7', 2000),
    images: [img('1557838923-2985c318be48'), img('1522071820081-009f0129c71c')],
    heroLine: 'Regular, on brand posts and a responsive presence, without your team having to manage it daily.',
    overview: [
      'Social media works best when it is consistent: a clear plan, posts that look and sound like your brand, and quick replies to comments and messages. That takes time most small teams do not have.',
      'We plan your content, create reels and graphics, schedule posts, engage with your community and send a performance report, so your accounts stay active and you can see what is working.',
    ],
    included: [
      'Monthly content plan and calendar',
      'Reels, short videos and graphics',
      'Captions and hashtags written for each platform',
      'Post scheduling and publishing',
      'Community engagement and replies',
      'Monthly performance reports',
    ],
    benefits: [
      { title: 'Consistent presence', text: 'Posts go out on schedule every week.' },
      { title: 'On brand content', text: 'Graphics and captions follow your style and tone.' },
      { title: 'Engaged followers', text: 'Comments and messages get timely replies.' },
      { title: 'Measured results', text: 'Reports show reach, engagement and the posts that performed best.' },
    ],
    process: [
      { title: 'Plan', text: 'Agree platforms, goals, tone and a monthly content calendar.' },
      { title: 'Create', text: 'Produce reels, graphics and captions for your approval.' },
      { title: 'Publish and engage', text: 'Schedule posts and respond to your community.' },
      { title: 'Report', text: 'Monthly results and ideas for the next month.' },
    ],
    faqs: [
      { q: 'Which platforms do you manage?', a: 'Usually Facebook, Instagram, LinkedIn and TikTok. We recommend the ones your customers actually use.' },
      { q: 'Do we approve posts before they go live?', a: 'Yes. You review the content calendar and posts before publishing.' },
      { q: 'Do you film videos?', a: 'We create reels from your photos, clips and our graphics. On site filming can be arranged separately.' },
      { q: 'How is it priced?', a: 'As a monthly package based on platforms and the number of posts. We quote after a free consultation.' },
      { q: 'Do we need to be on every platform?', a: 'No. We recommend the two or three platforms where your customers actually spend time and do those well, instead of spreading effort thin.' },
    ],
    whoFor: [
      'Businesses that know they should post regularly but never find the time',
      'Brands with accounts that look inconsistent or out of date',
      'Companies that want social media to bring enquiries, not just likes',
      'Teams launching a product, event or campaign that needs steady content',
    ],
    tools: ['Meta Business Suite', 'Instagram', 'Facebook', 'LinkedIn', 'TikTok', 'Canva', 'Buffer', 'Native analytics'],
    caseStudy: 'retail-storefront-rebuild',
  },
  {
    slug: 'ads-management',
    name: 'Ads Management',
    category: 'growth-marketing',
    short: 'Google Ads and Meta Ads with retargeting, conversion tracking and ROI reporting.',
    heroImage: img('1504868584819-f8e8b4b6d7e3', 2000),
    images: [img('1551288049-bebda4e38f71'), img('1556742049-0cfed4f6a45d')],
    heroLine: 'Paid campaigns set up to bring in leads and sales, with tracking that shows what each one returns.',
    overview: [
      'Paid ads can bring in customers quickly, but only when the targeting, budget and tracking are set up properly. Without conversion tracking it is hard to know which ads pay for themselves.',
      'We run Google Ads and Meta Ads campaigns, set up retargeting for people who already visited your site, install conversion tracking and report on return on ad spend, so budget moves to what works.',
    ],
    included: [
      'Account setup or audit of existing campaigns',
      'Google Search, Display and Performance Max campaigns',
      'Facebook and Instagram ads through Meta',
      'Retargeting campaigns',
      'Conversion tracking for calls, forms and sales',
      'Monthly ROI reporting and optimization',
    ],
    benefits: [
      { title: 'Spend where it works', text: 'Budget shifts towards campaigns that bring leads and sales.' },
      { title: 'Second chances', text: 'Retargeting reminds past visitors to come back.' },
      { title: 'Accurate tracking', text: 'Conversions are measured, so results are based on real numbers.' },
      { title: 'Clear reports', text: 'See cost per lead and return on ad spend each month.' },
    ],
    process: [
      { title: 'Audit', text: 'Review goals, current accounts, tracking and competitors.' },
      { title: 'Set up', text: 'Build campaigns, audiences, ads and conversion tracking.' },
      { title: 'Optimize', text: 'Test ads, adjust bids and move budget to what performs.' },
      { title: 'Report', text: 'Monthly ROI reports and next steps.' },
    ],
    faqs: [
      { q: 'How much should we spend on ads?', a: 'It depends on your market and goals. We suggest a starting budget after reviewing your situation and adjust it based on results.' },
      { q: 'Is the ad budget included in your fee?', a: 'No. Ad spend is paid directly to Google or Meta. Our management fee is separate.' },
      { q: 'How soon will we see results?', a: 'Ads can bring traffic within days. Campaigns usually need a few weeks of data to settle and improve.' },
      { q: 'Can you take over our existing campaigns?', a: 'Yes. We start with an audit and keep what is working.' },
      { q: 'What budget do we need for ads?', a: 'It depends on your market and goals. We recommend a starting budget during the consultation and only scale it once campaigns show a return.' },
    ],
    whoFor: [
      'Businesses spending on ads without a clear view of what it returns',
      'Companies starting paid advertising for the first time',
      'Stores that want shopping and retargeting campaigns',
      'Teams whose ad accounts need a full audit and rebuild',
    ],
    tools: ['Google Ads', 'Meta Ads', 'LinkedIn Ads', 'Microsoft Ads', 'Google Merchant Center', 'Google Tag Manager', 'Google Analytics 4', 'Looker Studio'],
    caseStudy: 'retail-storefront-rebuild',
  },
]

// Services and areas from the earlier catalog, mapped to the closest current page.
// Case studies saved in the database still use the old slugs, and old links may be bookmarked or indexed.
export const legacyServices = {
  // Two services that were merged into AI & Automation
  'ai-and-intelligent-systems': 'ai-automation',
  'automation-and-integration': 'ai-automation',
  'digital-consulting-and-strategy': 'web-development',
  'digital-commerce': 'web-development',
  'business-applications': 'custom-software',
  'data-modernization': 'custom-software',
  'advanced-analytics': 'ai-automation',
  'connected-intelligence': 'ai-automation',
  'data-management': 'custom-software',
  'generative-ai': 'ai-automation',
  'cloud-operations-and-migration': 'custom-software',
  'cloud-application-development-and-integration': 'custom-software',
  'managed-services': 'custom-software',
  'digital-infrastructure-services': 'custom-software',
  security: 'custom-software',
  'emerging-technologies': 'ai-automation',
  'contact-center': 'ai-automation',
  'digital-marketing': 'seo',
  'finance-and-accounting': 'ai-automation',
  'staff-augmentation': 'custom-software',
  'legal-process-outsourcing': 'ai-automation',
  'recruitment-process-outsourcing': 'crm-and-lead-management',
}
export const legacyAreas = {
  digital: 'web-and-software',
  'data-and-analytics': 'ai-and-automation',
  cloud: 'web-and-software',
  'business-process-services': 'ai-and-automation',
}

export const getService = (slug) => services.find((s) => s.slug === slug) || services.find((s) => s.slug === legacyServices[slug])

// Address the service had before permalinks could change. Saved page edits are keyed by it.
export const defaultServicePath = (service) =>
  service.category ? `/services/${service.category}/${service.slug}/` : `/services/${service.slug}/`

// Path helper keeps URLs consistent across menus, cards and the search. Follows Settings, Permalinks.
export const servicePath = (service) => {
  const mode = serviceMode()
  if (mode === 'root') return `/${service.slug}/`
  if (mode === 'flat') return `/services/${service.slug}/`
  return defaultServicePath(service)
}

export const categoryPath = (category) => `/services/${category.slug}/`

export const servicesByCategory = (slug) => services.filter((s) => s.category === slug)

export const allServicesFlat = () =>
  categories.flatMap((c) => (c.single ? [getService(c.slug)] : c.services.map(getService))).filter(Boolean)
