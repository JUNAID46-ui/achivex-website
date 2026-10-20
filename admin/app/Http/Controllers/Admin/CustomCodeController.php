<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\SiteSetting;
use Illuminate\Http\Request;

// HTML, CSS and JavaScript added to the public website (site_settings key "custom_code").
// Administrators only, because this code runs in every visitor's browser.
class CustomCodeController extends Controller
{
    public const FIELDS = [
        'head' => ['Header code, all pages', 'Added inside <head>. For analytics, tag manager and pixel snippets.'],
        'bodyStart' => ['Body start code, all pages', 'Added right after <body> opens. For the <noscript> part of Google Tag Manager.'],
        'footer' => ['Footer code, all pages', 'Added before </body>. For chat widgets and scripts that can load last.'],
        'homeHead' => ['Header code, home page only', 'Added inside <head> on the home page only.'],
        'homeFooter' => ['Footer code, home page only', 'Added before </body> on the home page only.'],
        'css' => ['Custom CSS', 'Plain CSS without <style> tags. Applies to every page.'],
        'js' => ['Custom JavaScript', 'Plain JavaScript without <script> tags. Runs on every page.'],
    ];

    public function index()
    {
        return view('admin.custom-code.index', ['code' => SiteSetting::group('custom_code'), 'fields' => self::FIELDS]);
    }

    public function save(Request $request)
    {
        $data = $request->validate(array_fill_keys(array_keys(self::FIELDS), 'nullable|string|max:50000'));
        SiteSetting::put('custom_code', array_map(fn ($v) => (string) $v, $data));
        ActivityLog::record('updated', 'Custom Code', 'Website custom code');

        return back()->with('status', 'Custom code saved. It shows on the website on the next page load.');
    }
}
