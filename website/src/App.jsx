import { lazy } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/Layout'
import Home from './pages/Home'
const WhoWeAre = lazy(() => import('./pages/about/WhoWeAre'))
const ServicesOverview = lazy(() => import('./pages/services/ServicesOverview'))
const ServiceResolver = lazy(() => import('./pages/services/ServiceResolver'))
const ServiceDetail = lazy(() => import('./pages/services/ServiceDetail'))
const Industry = lazy(() => import('./pages/Industry'))
const Industries = lazy(() => import('./pages/Industries'))
const Insights = lazy(() => import('./pages/insights/Insights'))
const CaseStudies = lazy(() => import('./pages/insights/CaseStudies'))
const CaseStudyDetail = lazy(() => import('./pages/insights/CaseStudyDetail'))
const Newsroom = lazy(() => import('./pages/insights/Newsroom'))
const NewsDetail = lazy(() => import('./pages/insights/NewsDetail'))
const Whitepapers = lazy(() => import('./pages/insights/Whitepapers'))
const WhitepaperDetail = lazy(() => import('./pages/insights/WhitepaperDetail'))
const Blogs = lazy(() => import('./pages/insights/Blogs'))
const BlogPost = lazy(() => import('./pages/insights/BlogPost'))
const Contact = lazy(() => import('./pages/Contact'))
const Careers = lazy(() => import('./pages/Careers'))
const PrivacyPolicy = lazy(() => import('./pages/legal/PrivacyPolicy'))
const Terms = lazy(() => import('./pages/legal/Terms'))
import NotFound from './pages/NotFound'
const RootResolver = lazy(() => import('./pages/RootResolver'))
import { routePath, listRoutePath, isRoot } from './lib/permalinks'

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<Home />} />

        <Route path="about-us">
          <Route index element={<WhoWeAre />} />
          {/* The Alliance pages were removed; old links go to the services page. */}
          <Route path="alliances/*" element={<Navigate to="/services/" replace />} />
          {/* About Us is a single page; older sub pages send visitors back to it. */}
          <Route path="*" element={<Navigate to="/about-us/" replace />} />
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
