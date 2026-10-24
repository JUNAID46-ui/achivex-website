<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Content\SiteSetting;
use App\Services\SiteRoutes;
use Illuminate\Http\Request;

// Blog categories: a plain list of names. There are no category pages on the website; the names
// label posts and fill the filter on the Blogs page.
class BlogCategoriesController extends Controller
{
    public function edit()
    {
        return view('admin.blog.categories', [
            'categories' => array_values(SiteRoutes::blogCategories()),
            'used' => SiteRoutes::blogCategoriesInUse(),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'categories' => 'required|array|min:1|max:30',
            'categories.*' => 'required|string|max:40|distinct',
        ]);
        $names = array_values(array_unique(array_map('trim', $data['categories'])));
        SiteSetting::put('blog', ['categories' => $names]);
        ActivityLog::record('settings_updated', 'Blog categories', 'Blog categories', implode(', ', $names), ['module' => 'Blog']);

        return back()->with('status', 'Categories saved.');
    }
}
