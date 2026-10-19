<?php

namespace App\Http\Controllers;

use App\Models\Content\SiteSetting;
use App\Services\Sitemap;

// Serves sitemap.xml and robots.txt so they can be linked or proxied from the website domain.
class PublicSeoController extends Controller
{
    public function sitemap()
    {
        return response(Sitemap::xml(), 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        $content = SiteSetting::group('robots')['content'] ?? "User-agent: *\nAllow: /";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
