import { img } from '../images.js'

export default {
  heroTitle: 'Responsible by design',
  heroText: 'Environmental, social and governance commitments that match the size and nature of our business. We list what we do today and leave space for goals we will publish as we set them.',
  sections: [
    {
      key: 'environment',
      title: 'Environment',
      image: img('1466611653911-95081537e5b7'),
      text: 'We are a services business, so most of our footprint comes from offices, travel and the computing we use. We favour remote collaboration where it works, recommend right sized cloud resources to clients, and plan hardware refreshes so devices are used for their full life.',
      commitments: ['Prefer video meetings over travel where practical', 'Recommend efficient, right sized cloud setups', 'Extend device life and recycle responsibly'],
    },
    {
      key: 'social',
      title: 'Social',
      image: img('1529156069898-49953e39b3ac'),
      text: 'Our people do the work, so we look after them. That means fair pay, clear expectations, time for learning and a workplace where people from different backgrounds can do their best work.',
      commitments: ['Structured training time for every team member', 'Fair and consistent hiring practices', 'Flexible working where roles allow'],
    },
    {
      key: 'governance',
      title: 'Governance',
      image: img('1556484687-30636164638b'),
      text: 'Clients trust us with data and access to their systems. We follow documented policies for information security, confidentiality and conflicts of interest, and we review them regularly.',
      commitments: ['Written information security and privacy policies', 'Confidentiality agreements for all staff and contractors', 'Least privilege access to client systems'],
    },
  ],
  ctaTitle: 'Questions about our policies?',
  ctaText: 'Contact us for copies of our policies or to discuss how we handle data and access for your project.',
  ctaButton: 'Contact Us',
}
