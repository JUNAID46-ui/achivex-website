<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    use Publishable;

    protected $table = 'public.job_openings';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['published' => 'boolean', 'publish_at' => 'datetime'];
}
