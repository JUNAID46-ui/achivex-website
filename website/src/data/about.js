export const aboutLinks = [
  { label: 'Who We Are', to: '/about-us/' },
  { label: 'Our Accomplishments', to: '/about-us/accomplishments/' },
  { label: 'Our Commitment to ESG', to: '/about-us/esg/' },
  { label: 'Our Leadership', to: '/about-us/leadership/' },
  { label: 'Our Global Presence', to: '/about-us/global-presence/' },
]

// Milestones. The live site reads these from the database (milestones table).
export const milestones = [
  { date: '2026', title: 'New website and service catalog', text: 'AchiveX launched its new website describing seven service areas and twenty individual services.' },
]

// Leadership team. The live site reads these from the database (team_members table).
export const leaders = []

// Office locations. The first entry is headquarters. Add more objects to list new locations.
export const locations = [
  { city: 'Wyoming', country: 'USA', type: 'Headquarters', address: null, phone: '+1 307 622 1140', email: 'info@achivex.com', hq: true },
]

// Open positions. The live site reads these from the database (job_openings table).
export const careers = {
  positions: [],
}

// Small figures shown on the home page hero cards. The live site reads these from the database (hero_stats table).
export const heroStats = [
  { value: '20', label: 'Specialist services under one roof' },
  { value: '7', label: 'Service areas, from strategy to support' },
  { value: 'Free', label: 'First consultation for every new client' },
]
