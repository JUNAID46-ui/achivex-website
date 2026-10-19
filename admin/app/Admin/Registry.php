<?php

namespace App\Admin;

use App\Admin\Resources;

// All content types available under /admin/r/{slug}.
class Registry
{
    public static function map(): array
    {
        return [
            'blog-posts' => Resources\BlogPostResource::class,
            'case-studies' => Resources\CaseStudyResource::class,
            'news' => Resources\NewsResource::class,
            'whitepapers' => Resources\WhitepaperResource::class,
            'jobs' => Resources\JobResource::class,
            'team' => Resources\TeamResource::class,
            'milestones' => Resources\MilestoneResource::class,
            'locations' => Resources\LocationResource::class,
            'redirects' => Resources\RedirectResource::class,
            'users' => Resources\UserResource::class,
        ];
    }

    public static function get(string $slug): Resource
    {
        $class = static::map()[$slug] ?? abort(404);

        return new $class;
    }

    public static function all(): array
    {
        return array_map(fn ($c) => new $c, static::map());
    }
}
