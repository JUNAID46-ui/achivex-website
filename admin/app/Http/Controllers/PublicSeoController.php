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

    // Served here so the website build can copy it. 404 until one is written in the admin.
    public function llms()
    {
        $content = (string) (SiteSetting::group('llms')['content'] ?? '');
        abort_if(trim($content) === '', 404);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
