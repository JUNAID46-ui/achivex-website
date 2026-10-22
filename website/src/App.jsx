import { Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/Layout'
import Home from './pages/Home'
import WhoWeAre from './pages/about/WhoWeAre'
import Accomplishments from './pages/about/Accomplishments'
import Esg from './pages/about/Esg'
import Leadership from './pages/about/Leadership'
import GlobalPresence from './pages/about/GlobalPresence'
import ServicesOverview from './pages/services/ServicesOverview'
import ServiceResolver from './pages/services/ServiceResolver'
import ServiceDetail from './pages/services/ServiceDetail'
import Industry from './pages/Industry'
import Industries from './pages/Industries'
import Insights from './pages/insights/Insights'
import CaseStudies from './pages/insights/CaseStudies'
import CaseStudyDetail from './pages/insights/CaseStudyDetail'
import Newsroom from './pages/insights/Newsroom'
import NewsDetail from './pages/insights/NewsDetail'
import Whitepapers from './pages/insights/Whitepapers'
import WhitepaperDetail from './pages/insights/WhitepaperDetail'
import Blogs from './pages/insights/Blogs'
import BlogPost from './pages/insights/BlogPost'
import Contact from './pages/Contact'
import Careers from './pages/Careers'
import PrivacyPolicy from './pages/legal/PrivacyPolicy'
import Terms from './pages/legal/Terms'
import NotFound from './pages/NotFound'
import RootResolver from './pages/RootResolver'
import { routePath, listRoutePath, isRoot } from './lib/permalinks'

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<Home />} />

        <Route path="about-us">
          <Route index element={<WhoWeAre />} />
          <Route path="accomplishments" element={<Accomplishments />} />
          <Route path="esg" element={<Esg />} />
          <Route path="leadership" element={<Leadership />} />
          <Route path="global-presence" element={<GlobalPresence />} />
          {/* The Alliance pages were removed; old links go to the services page. */}
          <Route path="alliances/*" element={<Navigate to="/services/" replace />} />
        </Route>

        <Route path="services">
          <Route index element={<ServicesOverview />} />
          <Route path=":slug" element={<ServiceResolver />} />
          <Route path=":category/:slug" element={<ServiceDetail />} />
        </Route>

        <Route path="industries" element={<Industries />} />
        <Route path="industries/:slug" element={<Industry />} />

        {/* Content addresses follow the prefixes saved under Settings, Permalinks in the admin. */}
        <Route path="insights" element={<Insights />} />
        {Object.entries({ caseStudies: [CaseStudies, CaseStudyDetail], newsroom: [Newsroom, NewsDetail], whitepapers: [Whitepapers, WhitepaperDetail], blogs: [Blogs, BlogPost] })
          .flatMap(([type, [List, Detail]]) => [
            <Route key={type} path={listRoutePath(type)} element={<List />} />,
            // A root prefix ("/") has no route of its own: RootResolver below picks the item.
            isRoot(type) ? null : <Route key={`${type}-item`} path={`${routePath(type)}/:slug`} element={<Detail />} />,
          ])}

        <Route path="contact-us" element={<Contact />} />
        <Route path="careers" element={<Careers />} />
        <Route path="privacy-policy" element={<PrivacyPolicy />} />
        <Route path="terms-of-service" element={<Terms />} />
        <Route path=":slug" element={<RootResolver />} />
        <Route path="*" element={<NotFound />} />
      </Route>
    </Routes>
  )
}
