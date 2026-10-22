import { img } from './images'
import { permalink } from '../lib/permalinks'

export const insightsLinks = [
  // "to" is read when the menu renders, after the saved permalinks have loaded.
  { label: 'Case Studies', get to() { return permalink('/insights/case-studies/') }, text: 'How we approach client problems' },
  { label: 'Newsroom', get to() { return permalink('/insights/newsroom/') }, text: 'Company announcements' },
  { label: 'Whitepapers and eBooks', get to() { return permalink('/insights/whitepapers/') }, text: 'Guides you can download' },
  { label: 'Blogs', get to() { return permalink('/insights/blogs/') }, text: 'Practical articles from our team' },
]

// Featured panel inside the Services mega menu.
export const servicesFeature = {
  image: img('1526779259212-939e64788e3c', 800),
  title: 'What a data modernization project actually involves',
  get to() { return permalink('/insights/blogs/what-a-data-modernization-project-involves/') },
}
