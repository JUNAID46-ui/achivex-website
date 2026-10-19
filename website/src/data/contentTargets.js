import { pageContent } from './pageContent.js'
import { services, servicePath, categoryPath } from './services.js'
import { categories } from './categories.js'
import { industries } from './industries.js'
import { alliances } from './alliances.js'

// Every fixed page whose text the admin can edit, mapped to the object the page reads from.
// Saved edits from the pages table are merged into these objects at startup.
export function contentTargets() {
  const targets = { ...pageContent }
  categories.filter((c) => !c.single).forEach((c) => { targets[categoryPath(c)] = c })
  services.forEach((s) => { targets[servicePath(s)] = s })
  industries.forEach((i) => { targets[`/industries/${i.slug}/`] = i })
  alliances.forEach((a) => { targets[`/about-us/alliances/${a.slug}/`] = a })
  return targets
}
