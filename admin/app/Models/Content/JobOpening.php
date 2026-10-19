<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    protected $table = 'public.job_openings';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['published' => 'boolean'];
}
