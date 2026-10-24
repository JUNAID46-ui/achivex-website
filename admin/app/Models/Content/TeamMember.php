<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use Publishable;

    protected $table = 'public.team_members';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['published' => 'boolean', 'publish_at' => 'datetime'];
}
