<?php

// Sidebar menu. Each item needs the user's role to allow its "area".
return [
    'site_url' => env('SITE_URL', 'http://localhost:5173'),
    'site_public_url' => env('SITE_PUBLIC_URL', 'https://www.achivex.com'),

    // Largest file the media library accepts, in MB. Files go straight to Supabase Storage.
    'max_upload_mb' => (int) env('MEDIA_MAX_UPLOAD_MB', 2048),

    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'gauge', 'route' => 'admin.dashboard', 'area' => 'dashboard'],
        ['label' => 'Pages', 'icon' => 'layout', 'area' => 'builder', 'children' => [
            ['label' => 'All Pages', 'route' => 'admin.pages.index', 'area' => 'builder'],
            ['label' => 'Add New Page', 'route' => 'admin.pages.create', 'area' => 'builder'],
            ['label' => 'Page Builder', 'route' => 'admin.builder', 'area' => 'builder'],
        ]],
        ['label' => 'Content', 'icon' => 'file', 'area' => 'posts', 'children' => [
            ['label' => 'Blog Posts', 'resource' => 'blog-posts', 'area' => 'posts'],
            ['label' => 'Case Studies', 'resource' => 'case-studies', 'area' => 'content'],
            ['label' => 'Newsroom', 'resource' => 'news', 'area' => 'content'],
            ['label' => 'Whitepapers', 'resource' => 'whitepapers', 'area' => 'content'],
            ['label' => 'Careers', 'resource' => 'jobs', 'area' => 'content'],
            ['label' => 'Team', 'resource' => 'team', 'area' => 'content'],
            ['label' => 'Milestones', 'resource' => 'milestones', 'area' => 'content'],
            ['label' => 'Locations', 'resource' => 'locations', 'area' => 'content'],
        ]],
        ['label' => 'Media', 'icon' => 'image', 'route' => 'admin.media.index', 'area' => 'media'],
        ['label' => 'Inbox', 'icon' => 'inbox', 'area' => 'inbox', 'badge' => 'unread', 'children' => [
            ['label' => 'Messages', 'route' => 'admin.inbox.messages', 'area' => 'inbox'],
            ['label' => 'Guide Requests', 'route' => 'admin.inbox.requests', 'area' => 'inbox'],
        ]],
        ['label' => 'SEO', 'icon' => 'search', 'area' => 'seo', 'children' => [
            ['label' => 'SEO Overview', 'route' => 'admin.seo.overview', 'area' => 'seo'],
            ['label' => 'Pages and Posts', 'route' => 'admin.seo.pages', 'area' => 'seo'],
            ['label' => 'Search Appearance', 'route' => 'admin.seo.appearance', 'area' => 'seo'],
            ['label' => 'Sitemap', 'route' => 'admin.seo.sitemap', 'area' => 'seo'],
            ['label' => 'Robots.txt', 'route' => 'admin.seo.robots', 'area' => 'seo'],
            ['label' => 'Redirects', 'resource' => 'redirects', 'area' => 'seo'],
            ['label' => '404 Monitor', 'route' => 'admin.seo.notfound', 'area' => 'seo'],
            ['label' => 'Local SEO', 'route' => 'admin.seo.schema', 'area' => 'seo'],
            ['label' => 'Webmaster Tools', 'route' => 'admin.seo.webmaster', 'area' => 'seo'],
            ['label' => 'SEO Audit', 'route' => 'admin.seo.audit', 'area' => 'seo'],
            ['label' => 'AI Visibility', 'route' => 'admin.seo.ai', 'area' => 'seo'],
        ]],
        ['label' => 'Analytics', 'icon' => 'chart', 'route' => 'admin.analytics', 'area' => 'analytics'],
        ['label' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings', 'area' => 'settings'],
        ['label' => 'Users', 'icon' => 'users', 'resource' => 'users', 'area' => 'users'],
        ['label' => 'Activity Log', 'icon' => 'activity', 'route' => 'admin.activity', 'area' => 'activity'],
        ['label' => 'Security Center', 'icon' => 'shield', 'route' => 'admin.security', 'area' => 'security'],
        ['label' => 'Tools', 'icon' => 'refresh', 'area' => 'tools', 'children' => [
            ['label' => 'Backup and Restore', 'route' => 'admin.backup', 'area' => 'tools'],
            ['label' => 'Cache', 'route' => 'admin.cache', 'area' => 'tools'],
            ['label' => 'Custom Code', 'route' => 'admin.custom-code', 'area' => 'tools'],
        ]],
    ],
];
